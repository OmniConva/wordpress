<?php
/**
 * The admin class
 * 
 * @package OmniConva\AiSalesAgentConnector\Admin
 */

namespace OmniConva\AiSalesAgentConnector;

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
