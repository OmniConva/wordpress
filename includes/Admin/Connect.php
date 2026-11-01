<?php
/**
 * One-click connection to OmniConva.
 * Class Connect
 *
 * @package OmniConva\AiSalesAgentConnector\Admin
 */

namespace OmniConva\AiSalesAgentConnector\Admin;

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Connect
 *
 * The "Connect to OmniConva" button: an authorization-code grant with PKCE,
 * so the Site Key and Identity Secret arrive without being copied by hand.
 *
 * 1. Start: this server registers a connect request with the OmniConva API
 *    (site URL, return URL, state, code challenge) and sends the admin to
 *    the OmniConva dashboard, where they sign in or sign up and approve.
 * 2. Callback: the dashboard sends the admin back with a one-time code; this
 *    server checks the state and trades the code plus the PKCE verifier for
 *    the keys. The Identity Secret only ever travels in that server-to-server
 *    response, never through the browser.
 */
class Connect {
	/**
	 * Per-user transient holding the state and PKCE verifier between the
	 * start and the callback.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'omniconva_ai_sales_connector_connect_';

	/**
	 * How long a connect may take, matching the API's request lifetime.
	 *
	 * @since 1.1.0
	 *
	 * @var int
	 */
	const TRANSIENT_TTL = 1800;

	/**
	 * Connect constructor.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_post_omniconva_connect_start', array( $this, 'start' ) );
		add_action( 'admin_post_omniconva_connect_callback', array( $this, 'callback' ) );
		add_action( 'admin_post_omniconva_disconnect', array( $this, 'disconnect' ) );
	}

	/**
	 * Register a connect request and send the admin to the OmniConva dashboard.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function start() {
		$this->authorize( 'omniconva_connect_start' );

		$verifier = $this->base64url( random_bytes( 32 ) );
		$state    = $this->base64url( random_bytes( 24 ) );

		$response = wp_remote_post(
			$this->api_base() . '/wordpress/connect/requests',
			array(
				'timeout' => 15,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'siteUrl'       => home_url( '/' ),
						'returnUrl'     => admin_url( 'admin-post.php?action=omniconva_connect_callback' ),
						'state'         => $state,
						'codeChallenge' => $this->base64url( hash( 'sha256', $verifier, true ) ),
						'siteName'      => wp_strip_all_tags( get_bloginfo( 'name' ) ),
					)
				),
			)
		);

		$body        = $this->json_body( $response );
		$connect_url = isset( $body['connectUrl'] ) ? esc_url_raw( $body['connectUrl'] ) : '';
		if ( '' === $connect_url ) {
			$this->back_to_settings( 'unreachable' );
		}

		set_transient(
			self::TRANSIENT_PREFIX . get_current_user_id(),
			array(
				'state'    => $state,
				'verifier' => $verifier,
			),
			self::TRANSIENT_TTL
		);

		// The dashboard is on another host, so wp_safe_redirect() would refuse
		// it; the URL comes from the API this site is configured to trust.
		wp_redirect( $connect_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}

	/**
	 * Where the dashboard sends the admin back to, with a code or an error.
	 *
	 * No nonce: this is a cross-site redirect. The state, kept per user, does
	 * the same job, and the code is useless without the verifier.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to connect this site.', 'omniconva-ai-sales-agent-connector' ), 403 );
		}

		$transient_key = self::TRANSIENT_PREFIX . get_current_user_id();
		$pending       = get_transient( $transient_key );
		delete_transient( $transient_key );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- verified by state, see above.
		$state = isset( $_GET['state'] ) ? sanitize_text_field( wp_unslash( $_GET['state'] ) ) : '';
		$code  = isset( $_GET['code'] ) ? sanitize_text_field( wp_unslash( $_GET['code'] ) ) : '';
		$error = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ! is_array( $pending ) || '' === $state || ! hash_equals( $pending['state'], $state ) ) {
			$this->back_to_settings( 'expired' );
		}
		if ( 'access_denied' === $error ) {
			$this->back_to_settings( 'denied' );
		}
		if ( '' === $code ) {
			$this->back_to_settings( 'failed' );
		}

		$response = wp_remote_post(
			$this->api_base() . '/wordpress/connect/exchange',
			array(
				'timeout' => 15,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode(
					array(
						'code'         => $code,
						'codeVerifier' => $pending['verifier'],
					)
				),
			)
		);
		$body     = $this->json_body( $response );
		if ( empty( $body['siteKey'] ) || empty( $body['identitySecret'] ) ) {
			$this->back_to_settings( 'failed' );
		}

		// Saved through the settings' own sanitize callback, which checks
		// both keys' shape and keeps the credit-link choice.
		$settings                    = (array) get_option( Menu::OPTION_NAME, array() );
		$settings['site_key']        = (string) $body['siteKey'];
		$settings['identity_secret'] = (string) $body['identitySecret'];
		$settings['store_name']      = isset( $body['storeName'] ) ? (string) $body['storeName'] : '';
		update_option( Menu::OPTION_NAME, $settings );

		$this->back_to_settings( 'connected' );
	}

	/**
	 * Forget the keys: the widget stops loading until the site is connected again.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function disconnect() {
		$this->authorize( 'omniconva_disconnect' );

		$settings                          = (array) get_option( Menu::OPTION_NAME, array() );
		$settings['site_key']              = '';
		$settings['clear_identity_secret'] = '1';
		$settings['store_name']            = '';
		update_option( Menu::OPTION_NAME, $settings );

		$this->back_to_settings( 'disconnected' );
	}

	/**
	 * Messages for the ?omniconva_notice= the actions redirect back with.
	 *
	 * @since 1.1.0
	 *
	 * @return array Notice code => array( type, message ).
	 */
	public static function notices() {
		return array(
			'connected'    => array( 'success', __( 'Connected to OmniConva. The chat widget is now live on your site.', 'omniconva-ai-sales-agent-connector' ) ),
			'disconnected' => array( 'success', __( 'Disconnected. The chat widget no longer loads on your site.', 'omniconva-ai-sales-agent-connector' ) ),
			'denied'       => array( 'warning', __( 'The connection was cancelled. You can try again whenever you are ready.', 'omniconva-ai-sales-agent-connector' ) ),
			'expired'      => array( 'error', __( 'That connect link expired or was already used. Please click Connect again.', 'omniconva-ai-sales-agent-connector' ) ),
			'unreachable'  => array( 'error', __( 'Could not reach OmniConva. Check your site can make outgoing requests, then try again, or set the keys up manually below.', 'omniconva-ai-sales-agent-connector' ) ),
			'failed'       => array( 'error', __( 'The connection could not be completed. Please click Connect again, or set the keys up manually below.', 'omniconva-ai-sales-agent-connector' ) ),
		);
	}

	/**
	 * Require a settings-capable admin and this action's nonce.
	 *
	 * @since 1.1.0
	 *
	 * @param string $action Nonce action.
	 *
	 * @return void
	 */
	private function authorize( $action ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to connect this site.', 'omniconva-ai-sales-agent-connector' ), 403 );
		}
		check_admin_referer( $action );
	}

	/**
	 * Decode a JSON response body, or an empty array on any failure.
	 *
	 * @since 1.1.0
	 *
	 * @param array|\WP_Error $response wp_remote_post() result.
	 *
	 * @return array
	 */
	private function json_body( $response ) {
		if ( is_wp_error( $response ) ) {
			return array();
		}
		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 ) {
			return array();
		}
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $body ) ? $body : array();
	}

	/**
	 * Redirect to the settings page with a notice, and stop.
	 *
	 * @since 1.1.0
	 *
	 * @param string $notice Key of self::notices().
	 *
	 * @return void
	 */
	private function back_to_settings( $notice ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'             => Menu::PAGE_SLUG,
					'omniconva_notice' => $notice,
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * The OmniConva API base, the same one the widget uses.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	private function api_base() {
		return omniconva_ai_sales_connector()->assets->get_api_base();
	}

	/**
	 * Base64url without padding (RFC 7636).
	 *
	 * @since 1.1.0
	 *
	 * @param string $bytes Raw bytes.
	 *
	 * @return string
	 */
	private function base64url( $bytes ) {
		return rtrim( strtr( base64_encode( $bytes ), '+/', '-_' ), '=' ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	}
}
