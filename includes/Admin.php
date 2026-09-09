<?php
/**
 * The admin class
 * 
 * @package BitByte\AiSalesAgentConnector\Admin
 */

namespace BitByte\AiSalesAgentConnector;

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
