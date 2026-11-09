<?php
/**
 * The chat widget's look, edited from WordPress.
 * Class Appearance
 *
 * @package OmniConva\AiSalesAgentConnector\Admin
 */

namespace OmniConva\AiSalesAgentConnector\Admin;

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Appearance
 *
 * Agent name, status text, avatar shape, colors and greeting. These live in
 * OmniConva, not in WordPress: the widget fetches them by Site Key, and the
 * dashboard and the mobile app edit the same values. So this section reads
 * them from the API when the settings page opens and writes them back on
 * save, and nothing is stored locally.
 *
 * Each request is signed with the Identity Secret (HMAC-SHA256 over the
 * timestamp, method, resource and exact body; see sign()), the only
 * credential the plugin has. Without a saved secret the section is not shown.
 */
class Appearance {
	/**
	 * Preset header colors, matching the OmniConva dashboard.
	 *
	 * @since 1.1.0
	 *
	 * @var string[]
	 */
	const COLORS = array( '#16a34a', '#2563eb', '#7c3aed', '#db2777', '#ea580c', '#0f172a' );

	/**
	 * Preset text colors, matching the OmniConva dashboard.
	 *
	 * @since 1.1.0
	 *
	 * @var string[]
	 */
	const TEXT_COLORS = array( '#ffffff', '#0f172a' );

	/**
	 * The resource the signature is bound to; the API checks the same string.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	const RESOURCE = '/wordpress/widget-settings';

	/**
	 * Appearance constructor.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_post_omniconva_save_appearance', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'omniconva_ai_sales_connector_settings_page_after', array( $this, 'render' ) );
	}

	/**
	 * Messages for the ?omniconva_notice= a save redirects back with.
	 *
	 * @since 1.1.0
	 *
	 * @return array Notice code => array( type, message ).
	 */
	public static function notices() {
		return array(
			'appearance_saved'   => array( 'success', __( 'Chat widget appearance saved. Your site shows the changes on the next page load.', 'omniconva-ai-sales-agent-connector' ) ),
			'appearance_invalid' => array( 'error', __( 'The agent name can\'t be empty. Nothing was saved.', 'omniconva-ai-sales-agent-connector' ) ),
			'appearance_denied'  => array( 'error', __( 'OmniConva refused the change. The Identity Secret may have been rotated: click Reconnect, then try again.', 'omniconva-ai-sales-agent-connector' ) ),
			'appearance_failed'  => array( 'error', __( 'Could not save the appearance to OmniConva. Please try again, or edit it in your OmniConva dashboard.', 'omniconva-ai-sales-agent-connector' ) ),
		);
	}

	/**
	 * The swatch styles, on this plugin's settings page only.
	 *
	 * @since 1.1.0
	 *
	 * @param string $hook_suffix Current admin page.
	 *
	 * @return void
	 */
	public function enqueue_styles( $hook_suffix ) {
		if ( 'toplevel_page_' . Menu::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_register_style( 'omniconva-ai-sales-agent-connector-admin', false, array(), OMNICONVA_AI_SALES_CONNECTOR_VERSION );
		wp_enqueue_style( 'omniconva-ai-sales-agent-connector-admin' );
		wp_add_inline_style( 'omniconva-ai-sales-agent-connector-admin', $this->css() );
	}

	/**
	 * Render the section: the form, or why it can't be shown.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function render() {
		$settings = (array) get_option( Menu::OPTION_NAME, array() );
		if ( empty( $settings['site_key'] ) ) {
			return;
		}
		?>
		<hr />
		<h2><?php esc_html_e( 'Chat widget', 'omniconva-ai-sales-agent-connector' ); ?></h2>
		<?php
		if ( empty( $settings['identity_secret'] ) ) {
			?>
			<p><?php esc_html_e( 'To change the agent name, colors and greeting here, click Reconnect above or save the Identity Secret. You can always change them under Settings → Custom Chat in your OmniConva dashboard.', 'omniconva-ai-sales-agent-connector' ); ?></p>
			<?php
			return;
		}

		$current = $this->request( 'GET' );
		if ( ! is_array( $current ) ) {
			?>
			<div class="notice notice-warning inline">
				<p>
					<?php
					echo esc_html(
						401 === $current
							? __( 'OmniConva didn\'t accept this site\'s Identity Secret, so the chat widget settings can\'t be loaded. Click Reconnect above to fix it.', 'omniconva-ai-sales-agent-connector' )
							: __( 'Could not load the chat widget settings from OmniConva. Reload the page to try again, or edit them under Settings → Custom Chat in your OmniConva dashboard.', 'omniconva-ai-sales-agent-connector' )
					);
					?>
				</p>
			</div>
			<?php
			return;
		}

		$name = Menu::OPTION_NAME . '_appearance';
		$val  = function ( $key, $fallback = '' ) use ( $current ) {
			return isset( $current[ $key ] ) && is_string( $current[ $key ] ) ? $current[ $key ] : $fallback;
		};
		?>
		<p><?php esc_html_e( 'These settings are saved in OmniConva, so they match what you see in the OmniConva dashboard and app.', 'omniconva-ai-sales-agent-connector' ); ?></p>
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="omniconva-appearance">
			<input type="hidden" name="action" value="omniconva_save_appearance" />
			<?php wp_nonce_field( 'omniconva_save_appearance' ); ?>

			<h3><?php esc_html_e( 'Agent identity', 'omniconva-ai-sales-agent-connector' ); ?></h3>
			<p class="description"><?php esc_html_e( 'How your AI support agent introduces itself to customers.', 'omniconva-ai-sales-agent-connector' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="omniconva_agent_name"><?php esc_html_e( 'Agent name', 'omniconva-ai-sales-agent-connector' ); ?></label></th>
					<td>
						<input type="text" id="omniconva_agent_name" name="<?php echo esc_attr( $name ); ?>[agent_name]" value="<?php echo esc_attr( $val( 'agentName' ) ); ?>" class="regular-text" maxlength="100" required aria-describedby="omniconva_agent_name_desc" />
						<p class="description" id="omniconva_agent_name_desc"><?php esc_html_e( 'Shown in the chat header and messages.', 'omniconva-ai-sales-agent-connector' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="omniconva_status_text"><?php esc_html_e( 'Status text', 'omniconva-ai-sales-agent-connector' ); ?></label></th>
					<td>
						<input type="text" id="omniconva_status_text" name="<?php echo esc_attr( $name ); ?>[status_text]" value="<?php echo esc_attr( $val( 'statusText' ) ); ?>" class="regular-text" maxlength="255" placeholder="<?php esc_attr_e( 'We reply right away', 'omniconva-ai-sales-agent-connector' ); ?>" aria-describedby="omniconva_status_text_desc" />
						<p class="description" id="omniconva_status_text_desc"><?php esc_html_e( 'Short line shown under the agent name. Leave empty for "We reply right away".', 'omniconva-ai-sales-agent-connector' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Avatar shape', 'omniconva-ai-sales-agent-connector' ); ?></th>
					<td>
						<fieldset aria-describedby="omniconva_avatar_shape_desc">
							<legend class="screen-reader-text"><?php esc_html_e( 'Avatar shape', 'omniconva-ai-sales-agent-connector' ); ?></legend>
							<?php
							$shape  = $val( 'avatarShape', 'circle' );
							$shapes = array(
								'circle' => __( 'Rounded', 'omniconva-ai-sales-agent-connector' ),
								'square' => __( 'Square', 'omniconva-ai-sales-agent-connector' ),
							);
							foreach ( $shapes as $key => $label ) :
								?>
								<label style="margin-right:16px">
									<input type="radio" name="<?php echo esc_attr( $name ); ?>[avatar_shape]" value="<?php echo esc_attr( $key ); ?>" <?php checked( $shape, $key ); ?> />
									<?php echo esc_html( $label ); ?>
								</label>
							<?php endforeach; ?>
						</fieldset>
						<p class="description" id="omniconva_avatar_shape_desc"><?php esc_html_e( 'Applies to the avatars in the chat header and each message.', 'omniconva-ai-sales-agent-connector' ); ?></p>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Appearance', 'omniconva-ai-sales-agent-connector' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Colors used for the chat widget.', 'omniconva-ai-sales-agent-connector' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Color', 'omniconva-ai-sales-agent-connector' ); ?></th>
					<td>
						<?php
						$this->render_swatches(
							$name,
							'primary_color',
							__( 'Color', 'omniconva-ai-sales-agent-connector' ),
							self::COLORS,
							$this->color_names(),
							$val( 'primaryColor', self::COLORS[0] )
						);
						?>
						<p class="description"><?php esc_html_e( 'Header and chat bubble background color.', 'omniconva-ai-sales-agent-connector' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Text', 'omniconva-ai-sales-agent-connector' ); ?></th>
					<td>
						<?php
						$this->render_swatches(
							$name,
							'primary_text_color',
							__( 'Text', 'omniconva-ai-sales-agent-connector' ),
							self::TEXT_COLORS,
							array(
								'#ffffff' => __( 'White', 'omniconva-ai-sales-agent-connector' ),
								'#0f172a' => __( 'Black', 'omniconva-ai-sales-agent-connector' ),
							),
							$val( 'primaryTextColor', self::TEXT_COLORS[0] )
						);
						?>
						<p class="description"><?php esc_html_e( 'Text color used on top of the header color.', 'omniconva-ai-sales-agent-connector' ); ?></p>
					</td>
				</tr>
			</table>

			<h3><?php esc_html_e( 'Conversation', 'omniconva-ai-sales-agent-connector' ); ?></h3>
			<p class="description"><?php esc_html_e( 'The first message customers see when they open the chat.', 'omniconva-ai-sales-agent-connector' ); ?></p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="omniconva_greeting_message"><?php esc_html_e( 'Greeting message', 'omniconva-ai-sales-agent-connector' ); ?></label></th>
					<td>
						<textarea id="omniconva_greeting_message" name="<?php echo esc_attr( $name ); ?>[greeting_message]" rows="4" class="large-text" maxlength="2000" aria-describedby="omniconva_greeting_message_desc"><?php echo esc_textarea( $val( 'greetingMessage' ) ); ?></textarea>
						<p class="description" id="omniconva_greeting_message_desc"><?php esc_html_e( 'Sent automatically at the start of every chat. Leave empty to show no greeting.', 'omniconva-ai-sales-agent-connector' ); ?></p>
					</td>
				</tr>
			</table>

			<p class="submit">
				<?php submit_button( __( 'Save changes', 'omniconva-ai-sales-agent-connector' ), 'primary', 'submit', false ); ?>
				<a class="button" href="<?php echo esc_url( Menu::page_url() ); ?>"><?php esc_html_e( 'Cancel', 'omniconva-ai-sales-agent-connector' ); ?></a>
			</p>
		</form>
		<?php
	}

	/**
	 * Save the section to OmniConva and redirect back with a notice.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to change these settings.', 'omniconva-ai-sales-agent-connector' ), 403 );
		}
		check_admin_referer( 'omniconva_save_appearance' );

		$key = Menu::OPTION_NAME . '_appearance';
		// Each value is sanitized below, field by field.
		$input = isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$field = function ( $name ) use ( $input ) {
			return isset( $input[ $name ] ) && is_string( $input[ $name ] ) ? $input[ $name ] : '';
		};

		$agent_name = $this->truncate( sanitize_text_field( $field( 'agent_name' ) ), 100 );
		if ( '' === $agent_name ) {
			$this->back( 'appearance_invalid' );
		}

		$changes = array(
			'agentName'       => $agent_name,
			'statusText'      => $this->truncate( sanitize_text_field( $field( 'status_text' ) ), 255 ),
			'greetingMessage' => $this->truncate( sanitize_textarea_field( $field( 'greeting_message' ) ), 2000 ),
		);

		if ( in_array( $field( 'avatar_shape' ), array( 'circle', 'square' ), true ) ) {
			$changes['avatarShape'] = $field( 'avatar_shape' );
		}

		$color = $this->chosen_color( $input, 'primary_color' );
		if ( $color ) {
			$changes['primaryColor'] = $color;
		}
		$text_color = $this->chosen_color( $input, 'primary_text_color' );
		if ( $text_color ) {
			$changes['primaryTextColor'] = $text_color;
		}

		$result = $this->request( 'PATCH', $changes );
		if ( is_array( $result ) ) {
			$this->back( 'appearance_saved' );
		}
		$this->back( 401 === $result ? 'appearance_denied' : 'appearance_failed' );
	}

	/**
	 * A row of color swatches (radio buttons) plus a custom color picker.
	 *
	 * Works without JavaScript: picking "Custom" makes the picker's value the
	 * one saved.
	 *
	 * @since 1.1.0
	 *
	 * @param string $name    Form array name.
	 * @param string $field   Field key.
	 * @param string $legend  Accessible group name.
	 * @param array  $presets Preset hex colors.
	 * @param array  $labels  Hex => accessible color name.
	 * @param string $current Saved hex color.
	 *
	 * @return void
	 */
	private function render_swatches( $name, $field, $legend, $presets, $labels, $current ) {
		$current   = strtolower( $current );
		$is_preset = in_array( $current, $presets, true );
		$base      = $name . '[' . $field . ']';
		?>
		<fieldset class="omniconva-swatches">
			<legend class="screen-reader-text"><?php echo esc_html( $legend ); ?></legend>
			<?php foreach ( $presets as $hex ) : ?>
				<label class="omniconva-swatch" title="<?php echo esc_attr( isset( $labels[ $hex ] ) ? $labels[ $hex ] : $hex ); ?>">
					<input type="radio" name="<?php echo esc_attr( $base ); ?>" value="<?php echo esc_attr( $hex ); ?>" <?php checked( $current, $hex ); ?> />
					<span class="omniconva-swatch-dot" style="background:<?php echo esc_attr( $hex ); ?>"></span>
					<span class="screen-reader-text"><?php echo esc_html( isset( $labels[ $hex ] ) ? $labels[ $hex ] : $hex ); ?></span>
				</label>
			<?php endforeach; ?>
			<label class="omniconva-swatch omniconva-swatch-custom">
				<input type="radio" name="<?php echo esc_attr( $base ); ?>" value="custom" <?php checked( ! $is_preset ); ?> />
				<span class="omniconva-swatch-label"><?php esc_html_e( 'Custom', 'omniconva-ai-sales-agent-connector' ); ?></span>
			</label>
			<input type="color" name="<?php echo esc_attr( $name . '[' . $field . '_custom]' ); ?>" value="<?php echo esc_attr( $current ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: setting name, e.g. Color. */ __( 'Custom %s', 'omniconva-ai-sales-agent-connector' ), strtolower( $legend ) ) ); ?>" />
		</fieldset>
		<?php
	}

	/**
	 * The color a swatch group submitted, or null when none was valid.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $input Submitted values.
	 * @param string $field Field key.
	 *
	 * @return string|null Lowercase #rrggbb.
	 */
	private function chosen_color( $input, $field ) {
		$choice = isset( $input[ $field ] ) && is_string( $input[ $field ] ) ? $input[ $field ] : '';
		if ( 'custom' === $choice ) {
			$choice = isset( $input[ $field . '_custom' ] ) && is_string( $input[ $field . '_custom' ] ) ? $input[ $field . '_custom' ] : '';
		}
		$choice = strtolower( trim( $choice ) );
		return preg_match( '/^#[0-9a-f]{6}$/', $choice ) ? $choice : null;
	}

	/**
	 * Call the API's widget settings resource, signed with the Identity Secret.
	 *
	 * @since 1.1.0
	 *
	 * @param string     $method GET or PATCH.
	 * @param array|null $data   Body for a write.
	 *
	 * @return array|int The decoded settings, or the HTTP status (0 when the
	 *                   API couldn't be reached or answered with something else).
	 */
	private function request( $method, $data = null ) {
		$settings = (array) get_option( Menu::OPTION_NAME, array() );
		$site_key = isset( $settings['site_key'] ) ? (string) $settings['site_key'] : '';
		$secret   = isset( $settings['identity_secret'] ) ? (string) $settings['identity_secret'] : '';
		if ( '' === $site_key || '' === $secret ) {
			return 0;
		}

		$body      = null === $data ? '' : wp_json_encode( $data );
		$timestamp = (string) time();
		$headers   = array(
			'X-OmniConva-Site-Key'  => $site_key,
			'X-OmniConva-Timestamp' => $timestamp,
			'X-OmniConva-Signature' => hash_hmac( 'sha256', $this->signed_payload( $timestamp, $method, $body ), $secret ),
		);
		$args      = array(
			'method'  => $method,
			'timeout' => 10,
			'headers' => $headers,
		);
		if ( '' !== $body ) {
			$args['headers']['Content-Type'] = 'application/json';
			$args['body']                    = $body;
		}

		$response = wp_remote_request( omniconva_ai_sales_connector()->assets->get_api_base() . self::RESOURCE, $args );
		if ( is_wp_error( $response ) ) {
			return 0;
		}
		$status = (int) wp_remote_retrieve_response_code( $response );
		if ( $status < 200 || $status >= 300 ) {
			return $status;
		}
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		return is_array( $decoded ) ? $decoded : 0;
	}

	/**
	 * The string that is signed. Must match signedPayload() in the API's
	 * wordpress-widget-settings.service.ts.
	 *
	 * @since 1.1.0
	 *
	 * @param string $timestamp Unix seconds.
	 * @param string $method    HTTP method.
	 * @param string $body      Exact request body ('' for a read).
	 *
	 * @return string
	 */
	private function signed_payload( $timestamp, $method, $body ) {
		return implode( "\n", array( 'omniconva-wp-v1', $timestamp, strtoupper( $method ), self::RESOURCE, $body ) );
	}

	/**
	 * Accessible names for the preset colors.
	 *
	 * @since 1.1.0
	 *
	 * @return array Hex => name.
	 */
	private function color_names() {
		return array(
			'#16a34a' => __( 'Green', 'omniconva-ai-sales-agent-connector' ),
			'#2563eb' => __( 'Blue', 'omniconva-ai-sales-agent-connector' ),
			'#7c3aed' => __( 'Violet', 'omniconva-ai-sales-agent-connector' ),
			'#db2777' => __( 'Pink', 'omniconva-ai-sales-agent-connector' ),
			'#ea580c' => __( 'Orange', 'omniconva-ai-sales-agent-connector' ),
			'#0f172a' => __( 'Slate', 'omniconva-ai-sales-agent-connector' ),
		);
	}

	/**
	 * Cut a string to the API's length limit, multibyte-safe when possible.
	 *
	 * @since 1.1.0
	 *
	 * @param string $value  Text.
	 * @param int    $length Maximum characters.
	 *
	 * @return string
	 */
	private function truncate( $value, $length ) {
		return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $length ) : substr( $value, 0, $length );
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
	private function back( $notice ) {
		wp_safe_redirect( add_query_arg( 'omniconva_notice', $notice, Menu::page_url() ) );
		exit;
	}

	/**
	 * Swatch styles.
	 *
	 * @since 1.1.0
	 *
	 * @return string
	 */
	private function css() {
		return '.omniconva-swatches{display:flex;flex-wrap:wrap;align-items:center;gap:10px}'
			. '.omniconva-swatch{position:relative;display:inline-flex;align-items:center;cursor:pointer}'
			. '.omniconva-swatch input{position:absolute;opacity:0;width:1px;height:1px;margin:0}'
			. '.omniconva-swatch-dot{display:block;width:28px;height:28px;border-radius:50%;box-shadow:inset 0 0 0 1px rgba(0,0,0,.2);outline:2px solid transparent;outline-offset:2px}'
			. '.omniconva-swatch input:checked+.omniconva-swatch-dot{outline-color:#1d2327}'
			. '.omniconva-swatch input:focus-visible+.omniconva-swatch-dot{outline-color:#2271b1;outline-style:dashed}'
			. '.omniconva-swatch-label{padding:4px 10px;border:1px solid #8c8f94;border-radius:14px}'
			. '.omniconva-swatch input:checked+.omniconva-swatch-label{border-color:#1d2327;box-shadow:0 0 0 1px #1d2327}'
			. '.omniconva-swatch input:focus-visible+.omniconva-swatch-label{outline:2px dashed #2271b1;outline-offset:2px}'
			. '.omniconva-swatches input[type=color]{width:40px;height:30px;padding:0 2px;cursor:pointer}'
			. '.omniconva-appearance .submit a.button{margin-left:6px}';
	}
}
