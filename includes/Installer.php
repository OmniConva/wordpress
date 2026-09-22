<?php
/**
 * The Installer class.
 * Install all dependency from here while activating the plugin.
 *
 * @package OmniConva\AiSalesAgentConnector\Installer
 */

namespace OmniConva\AiSalesAgentConnector;

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
        $this->create_tables();
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

    /**
     * Create necessary database tables.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function create_tables() {
        if ( ! function_exists( 'dbDelta' ) ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        
    }

    
}
