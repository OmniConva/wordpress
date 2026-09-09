<?php
/**
 * Frontend handler class
 * 
 * @package BitByte\AiSalesAgentConnector\Frontend
 */

namespace BitByte\AiSalesAgentConnector;

/**
 * Frontend handler class
 */
class Frontend {
  
    /**
     * Frontend constructor.
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function __construct() {
        new Frontend\Shortcode();
    }
}