<?php
/**
 * The admin class
 * 
 * @package OmniConva\AiSalesAgentConnector\Admin
 */

namespace OmniConva\AiSalesAgentConnector;

// Don't call the file directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin class
 */
class Admin {

    /**
     * Initialize the class.
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function __construct() {
        $this->dispatch_actions();
        new Admin\Menu();
        new Admin\Connect();
        new Admin\Appearance();
    }

    /**
     * Dispatch and bind actions.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function dispatch_actions() {
        
    }

}
