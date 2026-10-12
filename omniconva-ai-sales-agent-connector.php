<?php
/**
 * Plugin Name: OmniConva AI Sales Agent Connector
 * Plugin URI: https://wordpress.org/plugins/omniconva-ai-sales-agent-connector
 * Description: Add an AI sales agent chatbot to WordPress and WooCommerce. It answers shoppers, captures leads and helps them order in live chat, 24/7.
 * Version: 1.0.0
 * Author: Nurul Amin
 * Author URI: https://omniconva.com/
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 7.0
 * Text Domain: omniconva-ai-sales-agent-connector
 * 
 * @package OmniConva AI Sales Agent Connector
 */

/**
 * Copyright (c) 2026 Nurul Amin (email: contact@omniconva.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Omniconva_ai_sales_connector class
 *
 * @class Omniconva_ai_sales_connector The class that holds the entire Omniconva_ai_sales_connector plugin
 */
final class Omniconva_ai_sales_connector {
    /**
     * Plugin version
     *
     * @var string
     * 
     * @since 1.0.0
     */
    const VERSION = '1.0.0';

    /**
     * Holds various class instances.
     *
     * @var array
     * 
     * @since 1.0.0
     */
    private $container = [];

    /**
     * Constructor for the Omniconva_ai_sales_connector class.
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     * 
     * @since 1.0.0
     */
    private function __construct() {
        $this->define_constants();

        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

        add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
    }

    /**
     * Initializes the Omniconva_ai_sales_connector() class.
     *
     * Checks for an existing Omniconva_ai_sales_connector() instance
     * and if it doesn't find one, creates it.
     * 
     * @since 1.0.0
     *
     * @return Omniconva_ai_sales_connector|bool
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Omniconva_ai_sales_connector();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     * 
     * @since 1.0.0
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     * 
     * @since 1.0.0
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    /**
     * Define the constants.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'OMNICONVA_AI_SALES_CONNECTOR_VERSION', self::VERSION );
        define( 'OMNICONVA_AI_SALES_CONNECTOR_FILE', __FILE__ );
        define( 'OMNICONVA_AI_SALES_CONNECTOR_PATH', dirname( OMNICONVA_AI_SALES_CONNECTOR_FILE ) );
        define( 'OMNICONVA_AI_SALES_CONNECTOR_INCLUDES', OMNICONVA_AI_SALES_CONNECTOR_PATH . '/includes' );
        define( 'OMNICONVA_AI_SALES_CONNECTOR_URL', plugins_url( '', OMNICONVA_AI_SALES_CONNECTOR_FILE ) );
        define( 'OMNICONVA_AI_SALES_CONNECTOR_ASSETS', OMNICONVA_AI_SALES_CONNECTOR_URL . '/assets' );
    }

    /**
     * Load the plugin after all plugis are loaded.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function init_plugin() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Placeholder for activation function.
     *
     * Nothing being called here yet.
     * 
     * @since 1.0.0
     * 
     * @return void
     */
    public function activate() {
        $installer = new OmniConva\AiSalesAgentConnector\Installer();
        $installer->run();
    }

    /**
     * Placeholder for deactivation function.
     *
     * Nothing being called here yet.
     * 
     * @since 1.0.0
     */
    public function deactivate() {

    }

    /**
     * Include the required files.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new OmniConva\AiSalesAgentConnector\Admin();
        }

        if ( $this->is_request( 'ajax' ) ) {
            // require_once OMNICONVA_AI_SALES_CONNECTOR_INCLUDES . '/class-ajax.php';
        }
    }

    /**
     * Initialize the hooks.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'init', [ $this, 'init_classes' ] );
    }

    /**
     * Instantiate the required classes.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function init_classes() {
        if ( $this->is_request( 'ajax' ) ) {
            // $this->container['ajax'] =  new OmniConva\AiSalesAgentConnector\Ajax();
        }

        $this->container['api']    = new OmniConva\AiSalesAgentConnector\Api();
        $this->container['assets'] = new OmniConva\AiSalesAgentConnector\Assets();
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     * 
     * @since 1.0.0
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();

            case 'ajax' :
                return defined( 'DOING_AJAX' );

            case 'rest' :
                return defined( 'REST_REQUEST' );

            case 'cron' :
                return defined( 'DOING_CRON' );

            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

} // Omniconva_ai_sales_connector

/**
 * Initialize the main plugin.
 * 
 * @since 1.0.0
 *
 * @return \Omniconva_ai_sales_connector|bool
 */
function omniconva_ai_sales_connector() {
    return Omniconva_ai_sales_connector::init();
}

/**
 * Kick-off the plugin.
 * 
 * @since 1.0.0
 */
omniconva_ai_sales_connector();
