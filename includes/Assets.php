<?php
/**
 * Scripts and Styles Class.
 *
 * @package BitByte\AiSalesAgentConnector\Assets
 */

namespace BitByte\AiSalesAgentConnector;

use BitByte\AiSalesAgentConnector\Admin;

/**
 * Scripts and Styles Class
 */
class Assets {

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
			'apiBase' => 'http://localhost:4700',
		);

		$customer = $this->get_customer( $settings );

		if ( $customer ) {
			$config['customer'] = $customer;
		}

		wp_enqueue_script( 'bbt-ai-sales-agent-connector-widget' );

		wp_add_inline_script(
			'bbt-ai-sales-agent-connector-widget',
			sprintf( 'window.BBTWidget = %s;', wp_json_encode( $config ) ),
			'before'
		);
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
			$version   = isset( $script['version'] ) ? $script['version'] : BBT_AI_SALES_CONNECTOR_VERSION;

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

			wp_register_style( $handle, $style['src'], $deps, BBT_AI_SALES_CONNECTOR_VERSION );
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
		$plugin_js_assets_path = BBT_AI_SALES_CONNECTOR_ASSETS . '/js/';

		$scripts = array(
			'bbt-ai-sales-agent-connector-widget' => array(
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
		$plugin_css_assets_path = BBT_AI_SALES_CONNECTOR_ASSETS . '/css/';

		$styles = array();

		return $styles;
	}
}
