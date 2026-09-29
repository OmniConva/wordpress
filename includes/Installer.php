<?php
/**
 * The Installer class.
 * Install all dependency from here while activating the plugin.
 *
 * @package OmniConva\AiSalesAgentConnector\Installer
 */

namespace OmniConva\AiSalesAgentConnector;

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Installer
 * @package OmniConva\AiSalesAgentConnector
 */
class Installer {

    /**
     * Run the installer.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function run() {
        $this->add_version();
    }

    /**
     * Add time and version on DB.
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function add_version() {
        $installed = get_option( 'omniconva_ai_sales_agent_connector_installed' );

        if ( ! $installed ) {
            update_option( 'omniconva_ai_sales_agent_connector_installed', time() );
        }

        update_option( 'omniconva_ai_sales_agent_connector_version', OMNICONVA_AI_SALES_CONNECTOR_VERSION );
    }
}
