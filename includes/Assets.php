<?php
/**
 * Scripts and Styles Class.
 *
 * @package OmniConva\AiSalesAgentConnector\Assets
 */

namespace OmniConva\AiSalesAgentConnector;

use OmniConva\AiSalesAgentConnector\Admin;

/**
 * Scripts and Styles Class
 */
class Assets {

	/**
	 * API base URL used when OMNICONVA_API_BASE is not defined.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const DEFAULT_API_BASE = 'https://api.omniconva.com';

	/**
	 * Assets constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'register' ), 5 );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'register' ), 5 );
		}
	}

	/**
	 * Register our app scripts and styles
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register() {
		$this->register_scripts( $this->get_scripts() );
		$this->register_styles( $this->get_styles() );

		if ( ! is_admin() ) {
			$this->enqueue_widget();
		}
	}

	/**
	 * Enqueue the frontend widget script when a Site Key is configured.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_widget() {
		$settings = get_option( Admin\Menu::OPTION_NAME );
		$site_key = isset( $settings['site_key'] ) ? $settings['site_key'] : '';

		if ( empty( $site_key ) ) {
			return;
		}

		$config = array(
			'siteKey' => $site_key,
			'apiBase' => $this->get_api_base(),
		);

		$customer = $this->get_customer( $settings );

		if ( $customer ) {
			$config['customer'] = $customer;
		}

		wp_enqueue_script( 'omniconva-ai-sales-agent-connector-widget' );

		wp_add_inline_script(
			'omniconva-ai-sales-agent-connector-widget',
			sprintf( 'window.OmniConvaChat = %s;', wp_json_encode( $config ) ),
			'before'
		);
	}

	/**
	 * Resolve the API base URL the widget talks to.
	 *
	 * Sites can point the widget at another backend, a self-hosted instance or a
	 * local development API, by defining OMNICONVA_API_BASE in wp-config.php.
	 * Anything else falls back to the hosted API.
	 *
	 * @since 1.0.0
	 *
	 * @return string API base URL without a trailing slash.
	 */
	public function get_api_base() {
		$api_base = self::DEFAULT_API_BASE;

		if ( defined( 'OMNICONVA_API_BASE' ) && is_string( OMNICONVA_API_BASE ) && '' !== trim( OMNICONVA_API_BASE ) ) {
			$api_base = trim( OMNICONVA_API_BASE );
		}

		return untrailingslashit( esc_url_raw( $api_base ) );
	}

	/**
	 * Build the signed identity payload for the logged-in user.
	 *
	 * Returns null when no user is logged in or no Identity Secret is configured.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings Plugin settings.
	 *
	 * @return array|null
	 */
	private function get_customer( $settings ) {
		$identity_secret = isset( $settings['identity_secret'] ) ? $settings['identity_secret'] : '';

		if ( empty( $identity_secret ) || ! is_user_logged_in() ) {
			return null;
		}

		$user = wp_get_current_user();
		$id   = (string) $user->ID;

		return array(
			'id'         => $id,
			'name'       => $user->display_name,
			'email'      => $user->user_email,
			'phone'      => get_user_meta( $user->ID, 'billing_phone', true ),
			'address'    => get_user_meta( $user->ID, 'billing_address_1', true ),
			'avatar_url' => get_avatar_url( $user->ID ),
			'hash'       => hash_hmac( 'sha256', $id, $identity_secret ),
		);
	}

	/**
	 * Register scripts
	 *
	 * @param array $scripts
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function register_scripts( $scripts ) {
		foreach ( $scripts as $handle => $script ) {
			$deps      = isset( $script['deps'] ) ? $script['deps'] : false;
			$in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
			$version   = isset( $script['version'] ) ? $script['version'] : OMNICONVA_AI_SALES_CONNECTOR_VERSION;

			wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
		}
	}

	/**
	 * Register styles
	 *
	 * @param array $styles
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_styles( $styles ) {
		foreach ( $styles as $handle => $style ) {
			$deps = isset( $style['deps'] ) ? $style['deps'] : false;

			wp_register_style( $handle, $style['src'], $deps, OMNICONVA_AI_SALES_CONNECTOR_VERSION );
		}
	}

	/**
	 * Get all registered scripts
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_scripts() {
		$plugin_js_assets_path = OMNICONVA_AI_SALES_CONNECTOR_ASSETS . '/js/';

		$scripts = array(
			'omniconva-ai-sales-agent-connector-widget' => array(
				'src'       => $plugin_js_assets_path . 'widget.min.js',
				'deps'      => array(),
				'in_footer' => true,
			),
		);

		return $scripts;
	}

	/**
	 * Get registered styles
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_styles() {
		$plugin_css_assets_path = OMNICONVA_AI_SALES_CONNECTOR_ASSETS . '/css/';

		$styles = array();

		return $styles;
	}
}
