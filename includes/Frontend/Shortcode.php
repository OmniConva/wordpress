<?php
/**
 * Class Shortcode
 * 
 * @package BitByte\AiSalesAgentConnector\Frontend\Shortcode
 */

namespace BitByte\AiSalesAgentConnector\Frontend;

/**
 * Class Shortcode
 */
class Shortcode {

    /**
     * Constructor class
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function __construct() {
        add_shortcode( 'bbt_ai_sales_agent_connector', [ $this, 'render_frontend' ] );
    }

    /**
     * Render frontend app
     *
     * @param array $atts
     * @param string $content
     * 
     * @since 1.0.0
     *
     * @return string
     */
    public function render_frontend( $atts, $content = '' ) {
        // wp_enqueue_style( 'frontend' );
        // wp_enqueue_script( 'frontend' );

        $content .= 'Hello World!';

        return $content;
    }
}
