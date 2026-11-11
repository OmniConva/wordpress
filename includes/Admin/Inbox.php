<?php
/**
 * The OmniConva inbox inside wp-admin.
 * Class Inbox
 *
 * @package OmniConva\AiSalesAgentConnector\Admin
 */

namespace OmniConva\AiSalesAgentConnector\Admin;

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inbox
 *
 * The Inbox screen: the OmniConva dashboard's chat inbox in an iframe.
 *
 * Nothing here signs anybody in. The frame starts signed out and shows a
 * Sign in button, which opens OmniConva's own sign-in in a popup; the person
 * signs in with their OmniConva account, and only a member of the store this
 * site is connected to gets in. So a WordPress admin account alone never
 * opens the store's conversations, and no credential is sent to the browser:
 * the frame's URL carries only the public Site Key.
 *
 * OmniConva only lets this store's own sites frame the page (the Site Key's
 * site domains), so wp-admin must be served from one of them.
 */
class Inbox {
	/**
	 * Inbox page slug.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'omniconva-ai-sales-agent-inbox';

	/**
	 * Settings key for who may open the Inbox.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	const ACCESS_SETTING = 'inbox_access';

	/**
	 * Default Inbox access: administrators only.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	const ACCESS_ADMINS = 'administrators';

	/**
	 * Inbox access for WooCommerce shop managers too.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	const ACCESS_SHOP_MANAGERS = 'shop_managers';

	/**
	 * Hook suffix of the Inbox page, set when the menu is registered.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	private $hook_suffix = '';

	/**
	 * Inbox constructor.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function __construct() {
		// After Menu registers the top-level page this sits under.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * The Inbox access choices this site offers: value => label. Shop
	 * managers only exist with WooCommerce.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public static function access_choices() {
		$choices = array(
			self::ACCESS_ADMINS => __( 'Administrators', 'omniconva-ai-sales-agent-connector' ),
		);
		if ( class_exists( 'WooCommerce' ) ) {
			$choices[ self::ACCESS_SHOP_MANAGERS ] = __( 'Administrators and shop managers', 'omniconva-ai-sales-agent-connector' );
		}
		return $choices;
	}

	/**
	 * The saved Inbox access, or the default when it isn't one this site
	 * offers (for example, shop managers after WooCommerce is deactivated).
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function access() {
		$settings = (array) get_option( Menu::OPTION_NAME, array() );
		$access   = isset( $settings[ self::ACCESS_SETTING ] ) ? (string) $settings[ self::ACCESS_SETTING ] : '';
		return array_key_exists( $access, self::access_choices() ) ? $access : self::ACCESS_ADMINS;
	}

	/**
	 * The capability that opens the Inbox. Shop managers have
	 * manage_woocommerce, as do administrators.
	 *
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public static function capability() {
		return self::ACCESS_SHOP_MANAGERS === self::access() ? 'manage_woocommerce' : 'manage_options';
	}

	/**
	 * Register Settings and Inbox under the top-level menu.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		// The first submenu entry replaces the top-level one's duplicate, so
		// the menu reads Settings, Inbox rather than the plugin name twice.
		add_submenu_page(
			Menu::PAGE_SLUG,
			__( 'OmniConva AI Sales Agent Connector', 'omniconva-ai-sales-agent-connector' ),
			__( 'Settings', 'omniconva-ai-sales-agent-connector' ),
			'manage_options',
			Menu::PAGE_SLUG
		);

		$this->hook_suffix = (string) add_submenu_page(
			Menu::PAGE_SLUG,
			__( 'OmniConva Inbox', 'omniconva-ai-sales-agent-connector' ),
			__( 'Inbox', 'omniconva-ai-sales-agent-connector' ),
			// Someone with this but not manage_options sees the top-level menu
			// leading straight here: WordPress links a parent they can't open
			// to their first submenu entry they can.
			self::capability(),
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Full-height frame, on the Inbox page only.
	 *
	 * @since 1.2.0
	 *
	 * @param string $hook_suffix Current admin page.
	 *
	 * @return void
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( '' === $this->hook_suffix || $this->hook_suffix !== $hook_suffix ) {
			return;
		}
		wp_register_style( 'omniconva-ai-sales-agent-connector-inbox', false, array(), OMNICONVA_AI_SALES_CONNECTOR_VERSION );
		wp_enqueue_style( 'omniconva-ai-sales-agent-connector-inbox' );
		wp_add_inline_style(
			'omniconva-ai-sales-agent-connector-inbox',
			'.omniconva-inbox-header{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin:0 0 12px}'
			. '.omniconva-inbox-header h1{margin:0;padding:0}'
			. '.omniconva-inbox-frame{display:block;width:100%;height:calc(100vh - 32px - 110px);min-height:520px;border:1px solid #c3c4c7;border-radius:4px;background:#fff}'
		);
	}

	/**
	 * The Inbox page: the frame, or Connect first.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( self::capability() ) ) {
			return;
		}
		$settings = (array) get_option( Menu::OPTION_NAME, array() );
		$site_key = isset( $settings['site_key'] ) ? (string) $settings['site_key'] : '';
		$base     = omniconva_ai_sales_connector()->assets->get_dashboard_base();
		?>
		<div class="wrap">
			<div class="omniconva-inbox-header">
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<a class="page-title-action" href="<?php echo esc_url( $base . '/chat' ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Open in OmniConva', 'omniconva-ai-sales-agent-connector' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'omniconva-ai-sales-agent-connector' ); ?></span>
				</a>
			</div>
			<?php if ( '' === $site_key ) : ?>
				<div class="notice notice-info inline">
					<p>
						<?php esc_html_e( 'Connect this site to OmniConva first. Your conversations show up here once it is connected.', 'omniconva-ai-sales-agent-connector' ); ?>
						<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<a href="<?php echo esc_url( Menu::page_url() ); ?>"><?php esc_html_e( 'Go to Settings', 'omniconva-ai-sales-agent-connector' ); ?></a>
						<?php else : ?>
							<?php esc_html_e( 'Ask an administrator to connect it.', 'omniconva-ai-sales-agent-connector' ); ?>
						<?php endif; ?>
					</p>
				</div>
			<?php else : ?>
				<iframe
					class="omniconva-inbox-frame"
					title="<?php esc_attr_e( 'OmniConva inbox', 'omniconva-ai-sales-agent-connector' ); ?>"
					src="<?php echo esc_url( add_query_arg( 'siteKey', rawurlencode( $site_key ), $base . '/embed/wordpress' ) ); ?>"
					sandbox="allow-scripts allow-same-origin allow-forms allow-popups allow-popups-to-escape-sandbox allow-downloads"
					allow="clipboard-write; microphone"
					referrerpolicy="strict-origin"
				></iframe>
			<?php endif; ?>
		</div>
		<?php
	}
}
