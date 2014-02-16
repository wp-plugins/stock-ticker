<?php
/*
Plugin Name: Stock Ticker
Plugin URI: http://urosevic.net/wordpress/plugins/stock-ticker
Description: Easy display ticker tape with stock prices information with data provided by Yahoo Finance.
Version: 0.1.1
Author: Aleksandar Urosevic
Author URI: http://urosevic.net
License: GNU GPL3
*/

/*
Copyright 2014 Aleksandar Urosevic (urke.kg@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(!class_exists('WPAU_STOCK_TICKER'))
{
    class WPAU_STOCK_TICKER
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
			// Initialize Settings
			require_once(sprintf("%s/settings.php", dirname(__FILE__)));
			// Initialize Widget
			require_once(sprintf("%s/widget.php", dirname(__FILE__)));
			$WPAU_Stock_Ticker_Settings = new WPAU_Stock_Ticker_Settings();
        } // END public function __construct

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
        } // END public static function activate

        /**
         * Deactivate the plugin
         */     
        public static function deactivate()
        {
            // Do nothing
        } // END public static function deactivate

        /**
         * Ticker function for widget and shortcode
         */
        public static function stock_ticker($symbols,$show,$zero,$minus,$plus)
        {
            if ( !empty($symbols) )
            {
                $id = 'stock_ticker_'. substr(md5(mt_rand()),0,8);
                $out = '<ul id="' .$id. '" class="stock_ticker"></ul>';
                $out .= '<script type="text'.'/javascript">';
                $wpau_stock_ticker_css = "ul#{$id}.stock_ticker li.zero a, ul#{$id}.stock_ticker li.zero a:hover { color: $zero; }";
                $wpau_stock_ticker_css .= "ul#{$id}.stock_ticker li.minus a, ul#{$id}.stock_ticker li.minus a:hover { color: $minus; }";
                $wpau_stock_ticker_css .= "ul#{$id}.stock_ticker li.plus a, ul#{$id}.stock_ticker li.plus a:hover { color: $plus; }";
                $out .= 'jQuery("head").append(\'<style type="text/css">' .$wpau_stock_ticker_css. '</style>\');';

                if ( wp_script_is( 'jquery', 'done' ) )
                    $out .= 'jQuery(document).ready(function(){wpau_stock_ticker_setup("' .$symbols. '","' .$id. '","' .$show. '");});';

                $out .= '</script>';
                echo $out;
            }
        }

        /**
         * Shortcode for stock ticker
         */
        public static function stock_ticker_shortcode($atts,$content=null)
        {
            extract( shortcode_atts( array(
                'symbols' => get_option('st_symbols'),
                'show'    => get_option('st_show'),
                'zero'    => get_option('st_quote_zero','#454545'),
                'minus'   => get_option('st_quote_minus','#D8442F'),
                'plus'    => get_option('st_quote_plus','#009D59')
            ), $atts ) );
            if ( !empty($symbols) )
                self::stock_ticker($symbols,$show,$zero,$minus,$plus);
        }
    } // END class WPAU_STOCK_TICKER
} // END if(!class_exists('WPAU_STOCK_TICKER'))

if(class_exists('WPAU_STOCK_TICKER'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('WPAU_STOCK_TICKER', 'activate'));
    register_deactivation_hook(__FILE__, array('WPAU_STOCK_TICKER', 'deactivate'));

    // instantiate the plugin class
    $wpau_stock_ticker = new WPAU_STOCK_TICKER();

    // Add a link to the settings page onto the plugin page
    if(isset($wpau_stock_ticker))
    {
        // Add the settings link to the plugins page
        function plugin_settings_link($links)
        { 
            $settings_link = '<a href="options-general.php?page=wpau_stock_ticker">Settings</a>'; 
            array_unshift($links, $settings_link); 
            return $links; 
        }

        $plugin = plugin_basename(__FILE__); 
        add_filter("plugin_action_links_$plugin", 'plugin_settings_link');

        /**
         * Enqueue the colour picker
         */
        function wpau_enqueue_colour_picker()
        {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        }
        add_action( 'admin_enqueue_scripts', 'wpau_enqueue_colour_picker' );

        // JS tool for frontend
        function wpau_stock_ticker_js()
        {
            wp_enqueue_script( 'jquery-ticker', plugin_dir_url(__FILE__) . 'assets/js/jquery.webticker.min.js', array('jquery'), '1.0.0' );
        	wp_enqueue_script( 'stock-ticker', plugin_dir_url(__FILE__) . 'assets/js/stock-ticker.js', array('jquery'), '1.0.0' );
            wp_enqueue_style( 'dashicons' );
            // dashicons (using and resource):
            // http://jameskoster.co.uk/work/using-wordpress-3-8s-dashicons-theme-plugin/
            // http://melchoyce.github.io/dashicons/
            wp_enqueue_style( 'stock-ticker', plugin_dir_url(__FILE__) .'assets/css/stock-ticker.css', array( 'dashicons' ), '1.0.0' );

            // get custom colours or set default colours
            $st_quote_zero = get_option('st_quote_zero','#454545');
            $st_quote_minus = get_option('st_quote_minus','#D8442F');
            $st_quote_plus = get_option('st_quote_plus','#009D59');
            $wpau_stock_ticker_css = <<<EOF
ul.stock_ticker li.zero a, ul.stock_ticker li.zero a:hover { color: $st_quote_zero; }
ul.stock_ticker li.minus a, ul.stock_ticker li.minus a:hover { color: $st_quote_minus; }
ul.stock_ticker li.plus a, ul.stock_ticker li.plus a:hover { color: $st_quote_plus; }
EOF;
            // wp_add_inline_style( 'stock-ticker', $wpau_stock_ticker_css );
        }
        add_action( 'wp_enqueue_scripts', 'wpau_stock_ticker_js' );

        // register stock_ticker shortcode
        add_shortcode( 'stock_ticker', array('WPAU_STOCK_TICKER','stock_ticker_shortcode') );
    }
}
