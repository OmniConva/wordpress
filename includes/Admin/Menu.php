<?php
/**
 * Admin Pages Handler
 * Class Menu
 *
 * @package OmniConva\AiSalesAgentConnector\Admin
 */
namespace OmniConva\AiSalesAgentConnector\Admin;

/**
 * Class Menu
 */
class Menu {
	/**
	 * Option name used to store the settings.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const OPTION_NAME = 'omniconva_ai_sales_connector_settings';

	/**
	 * Settings page slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'omniconva-ai-sales-agent-connector';

	/**
	 * Menu constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( OMNICONVA_AI_SALES_CONNECTOR_FILE ), array( $this, 'plugin_action_links' ) );
	}

	/**
	 * Register our menu page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_options_page(
			__( 'OmniConva AI Sales Agent Connector', 'omniconva-ai-sales-agent-connector' ),
			__( 'OmniConva AI Sales Agent', 'omniconva-ai-sales-agent-connector' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'plugin_page' )
		);
	}

	/**
	 * Register settings, section and fields.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			self::OPTION_NAME,
			self::OPTION_NAME,
			array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
		);

		add_settings_section(
			'omniconva_ai_sales_connector_general',
			__( 'General Settings', 'omniconva-ai-sales-agent-connector' ),
			'__return_false',
			self::PAGE_SLUG
		);

		add_settings_field(
			'site_key',
			__( 'Site Key', 'omniconva-ai-sales-agent-connector' ),
			array( $this, 'render_site_key_field' ),
			self::PAGE_SLUG,
			'omniconva_ai_sales_connector_general'
		);

		add_settings_field(
			'identity_secret',
			__( 'Identity Secret', 'omniconva-ai-sales-agent-connector' ),
			array( $this, 'render_identity_secret_field' ),
			self::PAGE_SLUG,
			'omniconva_ai_sales_connector_general'
		);
	}

	/**
	 * Sanitize the settings before saving.
	 *
	 * @since 1.0.0
	 *
	 * @param array $settings The submitted settings.
	 *
	 * @return array
	 */
	public function sanitize_settings( $settings ) {
		$sanitized = array();

		$sanitized['site_key']        = isset( $settings['site_key'] ) ? sanitize_text_field( $settings['site_key'] ) : '';
		$sanitized['identity_secret'] = isset( $settings['identity_secret'] ) ? sanitize_text_field( $settings['identity_secret'] ) : '';

		return $sanitized;
	}

	/**
	 * Render the Site Key text field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_site_key_field() {
		$settings = get_option( self::OPTION_NAME );
		$site_key = isset( $settings['site_key'] ) ? $settings['site_key'] : '';
		?>
		<input
			type="text"
			id="omniconva_ai_sales_connector_site_key"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[site_key]"
			value="<?php echo esc_attr( $site_key ); ?>"
			class="regular-text"
		/>
		<?php
	}

	/**
	 * Render the Identity Secret field.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function render_identity_secret_field() {
		$settings        = get_option( self::OPTION_NAME );
		$identity_secret = isset( $settings['identity_secret'] ) ? $settings['identity_secret'] : '';
		?>
		<input
			type="password"
			id="omniconva_ai_sales_connector_identity_secret"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[identity_secret]"
			value="<?php echo esc_attr( $identity_secret ); ?>"
			class="regular-text"
			autocomplete="new-password"
		/>
		<p class="description">
			<?php esc_html_e( 'Used to sign logged-in customer identities (HMAC-SHA256). Keep this secret; it is never sent to the browser.', 'omniconva-ai-sales-agent-connector' ); ?>
		</p>
		<?php
	}

	/**
	 * Add a "Settings" link to the plugin's row on the Plugins screen.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Existing plugin action links.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ),
			__( 'Settings', 'omniconva-ai-sales-agent-connector' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Initialize our hooks for the admin page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Load scripts and styles for the app
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		// wp_enqueue_style( 'admin' );
		// wp_enqueue_script( 'admin' );
	}

	/**
	 * Handles the main page
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function plugin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_NAME );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
