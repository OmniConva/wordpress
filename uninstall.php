<?php
/**
 * Removes the plugin's options when it is deleted from the Plugins screen.
 *
 * @package OmniConva\AiSalesAgentConnector
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'omniconva_ai_sales_connector_settings' );
delete_option( 'omniconva_ai_sales_agent_connector_installed' );
delete_option( 'omniconva_ai_sales_agent_connector_version' );
