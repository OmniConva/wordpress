<?php
/**
 * The Installer class.
 * Install all dependency from here while activating the plugin.
 *
 * @package BitByte\AiSalesAgentConnector\Installer
 */

namespace BitByte\AiSalesAgentConnector;

/**
 * Class Installer
 * @package BitByte\AiSalesAgentConnector
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
        $installed = get_option( 'bbt_ai_sales_agent_connector_installed' );

        if ( ! $installed ) {
            update_option( 'bbt_ai_sales_agent_connector_installed', time() );
        }

        update_option( 'bbt_ai_sales_agent_connector_version', BBT_AI_SALES_CONNECTOR_VERSION );
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
