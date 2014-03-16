<?php
/*
Plugin Name: Stock Ticker
Plugin URI: http://urosevic.net/wordpress/plugins/stock-ticker/
Description: Easy display ticker tape with stock prices information with data provided by Yahoo Finance.
Version: 0.1.3
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
            define('WPAU_STOCK_TICKER_VER','0.1.3');

			// Initialize Settings
			require_once(sprintf("%s/inc/settings.php", dirname(__FILE__)));
			// Initialize Widget
			require_once(sprintf("%s/inc/widget.php", dirname(__FILE__)));

			$WPAU_Stock_Ticker_Settings = new WPAU_Stock_Ticker_Settings();
        } // END public function __construct

        /**
         * Defaults
         */
        public static function defaults()
        {
            $defaults = array(
                'symbols'       => 'AAPL,MSFT,INTC',
                'show'          => 'name',
                'zero'          => '#454545',
                'minus'         => '#D8442F',
                'plus'          => '#009D59',
                'cache_timeout' => '180', // 3 minutes
                'error_message' => 'Unfortunately, we could not get stock quotes this time.',
                'legend'        => "AAPL;Apple Inc.\nFB;Facebook, Inc.\nCSCO;Cisco Systems, Inc.\nGOOG;Google Inc.\nINTC;Intel Corporation\nLNKD;LinkedIn Corporation\nMSFT;Microsoft Corporation\nTWTR;Twitter, Inc.",
                'custom'        => false
            );
            $options = wp_parse_args(get_option('stock_ticker_defaults'), $defaults);
            return $options;
        }

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Transit old settings to new format
            $defaults = self::defaults();
            if ( get_option('st_symbols') ) {
                $defaults['symbols'] = get_option('st_symbols');
                delete_option('st_symbols');
            }
            if ( get_option('st_show') ) { $defaults['show'] = get_option('st_show'); delete_option('st_show'); }
            if ( get_option('st_quote_zero') ) { $defaults['zero'] = get_option('st_quote_zero'); delete_option('st_quote_zero'); }
            if ( get_option('st_quote_minus') ) { $defaults['minus'] = get_option('st_quote_minus'); delete_option('st_quote_minus'); }
            if ( get_option('st_quote_plus') ) { $defaults['plus'] = get_option('st_quote_plus'); delete_option('st_quote_plus'); }
            update_option('stock_ticker_defaults',$defaults);
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
                // get fresh or from transient cache stock quote
                $st_transient_id = "st_json_".md5($symbols);

                // get legend if custom enabled
                $defaults = self::defaults();
                if ( !empty($defaults['custom']) ){
                    $matrix = explode("\n",$defaults['legend']);
                    $msize = sizeof($matrix);
                    for($m=0; $m<$msize; $m++){
                        $line = explode(";",$matrix[$m]);
                        $legend[strtoupper(trim($line[0]))] = trim($line[1]);
                    }
                    unset($m,$msize,$matrix,$line);
                }

                // check if cache exists
                if ( false === ( $json = get_transient( $st_transient_id ) ) || empty($json) ) {
                    // if does not exist, get new cache

                    // clean and prepare symbols for YQL call
                    $yql_symbols = preg_replace('/\s+/', '', $symbols);
                    $yql_symbols = '"' . str_replace(',', '","', $yql_symbols) . '"';

                    // compose YQL URL
                    $yql_url = "http://query.yahooapis.com/v1/public/yql";
                    // $yql_query = 'select * from yahoo.finance.quotes where symbol in ('.$yql_symbols.')';
                    $yql_query = 'select Name, Symbol, LastTradePriceOnly, Change, ChangeinPercent, Volume from yahoo.finance.quotes where symbol in ('.$yql_symbols.')';
                    $yql_query_url = $yql_url . "?q=" . urlencode($yql_query);
                    $yql_query_url .= "&env=" . urlencode("store://datatables.org/alltableswithkeys");
                    $yql_query_url .= "&format=json";
                    // $yql_query_url .= "&diagnostics=false";
                    // $yql_query_url .= "&callback=";

                    // get remote JSON
                    $wprga = array(
                        'timeout' => 2 // two seconds only
                    );
                    $response = wp_remote_get($yql_query_url, $wprga);
                    $json = wp_remote_retrieve_body( $response );

                    // prepare nice array
                    // $json = json_decode($json, true);
                    $json = json_decode($json);

                    // now cache array for N minutes
                    if ( !defined('WPAU_STOCK_TICKER_CACHE_TIMEOUT') )
                    {
                        // $defaults = WPAU_STOCK_TICKER::defaults();
                        define('WPAU_STOCK_TICKER_CACHE_TIMEOUT',$defaults['cache_timeout']);
                        // unset($defaults);
                    }
                    set_transient( $st_transient_id, $json, WPAU_STOCK_TICKER_CACHE_TIMEOUT );

                    // free some memory: destroy all vars that we temporary used here
                    unset($yql_symbols, $yql_url, $yql_query, $yql_query_url, $reponse);
                }

                $id = 'stock_ticker_'. substr(md5(mt_rand()),0,8);
                $out = '<ul id="' .$id. '" class="stock_ticker">';

                // prepare ticker
                if(!empty($json) && !is_null($json->query->results)){
                    $q = "";
                    // Parse results and extract data to display
                    foreach($json->query->results->quote as $quote){
                        $q_change  = $quote->Change;
                        $q_price   = $quote->LastTradePriceOnly;
                        $q_name    = $quote->Name;
                        $q_changep = $quote->ChangeinPercent;
                        $q_symbol  = $quote->Symbol;
                        $q_volume  = $quote->Volume;

                        // Define class based on change
                        if ( $q_change < 0 ) { $chclass = "minus"; }
                        else if ( $q_change > 0 ) { $chclass = "plus"; }
                        else { $chclass = "zero"; $q_change = "0.00"; }

                        // Use custom name?
                        if ( !empty($defaults['custom']) && !empty($legend[$q_symbol]) )
                            $q_name = $legend[$q_symbol];

                        // What to show?
                        if ( $show == "name" )
                            $company_show = $q_name;
                        else
                            $company_show = $q_symbol;

                        // Do not print change, volume and change% for currencies
                        if (substr($q_symbol,-2) == "=X"){
                            $q .= '<li class="'.$chclass.'"><a href="http://finance.yahoo.com/q?s='.$q_symbol.'" target="_blank" title="'.$q_name.'">'.$company_show.' '.$q_price.'</a></li>';
                        } else {
                            $q .= '<li class="'.$chclass.'"><a href="http://finance.yahoo.com/q?s='.$q_symbol.'" target="_blank" title="'.$q_name.' (Vol: '.$q_volume.'; Ch: '.$q_changep.')">'.$company_show.' '.$q_price.' '.$q_change.'</a></li>';
                        }
                    }
                }
                // No results were returned
                if(empty($q))
                    $q = '<li class="error">Unfortunately, we could not to get stock quotes this time.</li>';

                $out .= $q;

                $out .= '</ul>';

                // print ticker content
                echo $out;

                // prepare styles
                $wpau_stock_ticker_css = "ul#{$id}.stock_ticker li.zero a, ul#{$id}.stock_ticker li.zero a:hover { color: $zero; }";
                $wpau_stock_ticker_css .= "ul#{$id}.stock_ticker li.minus a, ul#{$id}.stock_ticker li.minus a:hover { color: $minus; }";
                $wpau_stock_ticker_css .= "ul#{$id}.stock_ticker li.plus a, ul#{$id}.stock_ticker li.plus a:hover { color: $plus; }";

                // append customized styles
                if (empty($_SESSION['wpau_stock_ticker_css']))
                    $_SESSION['wpau_stock_ticker_css'] = $wpau_stock_ticker_css;
                else
                    $_SESSION['wpau_stock_ticker_css'] .= $wpau_stock_ticker_css;

                // append ticker ID
                if ( empty($_SESSION['wpau_stock_ticker_ids']) )
                    $_SESSION['wpau_stock_ticker_ids'] = "#".$id;
                else
                    $_SESSION['wpau_stock_ticker_ids'] .= ",#".$id;

                unset($q, $out, $id, $wpau_stock_ticker_css, $defaults, $legend);

            }
        }

        /**
         * Shortcode for stock ticker
         */
        public static function stock_ticker_shortcode($atts,$content=null)
        {
            $st_defaults = WPAU_STOCK_TICKER::defaults();
            extract( shortcode_atts( array(
                'symbols' => $st_defaults['symbols'],
                'show'    => $st_defaults['show'],
                'zero'    => $st_defaults['zero'],
                'minus'   => $st_defaults['minus'],
                'plus'    => $st_defaults['plus']
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
            wp_enqueue_script( 'jquery-ticker', plugin_dir_url(__FILE__) . 'assets/js/jquery.webticker.min.js', array('jquery'), WPAU_STOCK_TICKER_VER ); //'1.0.0' );
        	// wp_enqueue_script( 'stock-ticker', plugin_dir_url(__FILE__) . 'assets/js/stock-ticker.min.js', array('jquery'), '1.0.0' );
            // wp_enqueue_style( 'dashicons' );
            // dashicons (using and resource):
            // http://jameskoster.co.uk/work/using-wordpress-3-8s-dashicons-theme-plugin/
            // http://melchoyce.github.io/dashicons/
            wp_enqueue_style( 'stock-ticker', plugin_dir_url(__FILE__) .'assets/css/stock-ticker.css', array(), WPAU_STOCK_TICKER_VER ); //'1.0.0' );
            // wp_enqueue_style( 'stock-ticker', plugin_dir_url(__FILE__) .'assets/css/stock-ticker.css', array( 'dashicons' ), WPAU_STOCK_TICKER_VER ); //'1.0.0' );

            // get custom colours or set default colours
            $st_defaults    = WPAU_STOCK_TICKER::defaults();
            $st_quote_zero  = $st_defaults['zero'];
            $st_quote_minus = $st_defaults['minus'];
            $st_quote_plus  = $st_defaults['plus'];

            $wpau_stock_ticker_css = <<<EOF
ul.stock_ticker li.zero a, ul.stock_ticker li.zero a:hover { color: $st_quote_zero; }
ul.stock_ticker li.minus a, ul.stock_ticker li.minus a:hover { color: $st_quote_minus; }
ul.stock_ticker li.plus a, ul.stock_ticker li.plus a:hover { color: $st_quote_plus; }
EOF;
            // wp_add_inline_style( 'stock-ticker', $wpau_stock_ticker_css );
        }
        add_action( 'wp_enqueue_scripts', 'wpau_stock_ticker_js' );

        function wpau_stock_ticker_byshortcode()
        {
            $wpau_stock_ticker_ids = $_SESSION["wpau_stock_ticker_ids"];
            echo "<script type=\"text/javascript\">jQuery(document).ready(function(){jQuery(\"$wpau_stock_ticker_ids\").webTicker();});</script>";
            echo "<style type=\"text/css\">".$_SESSION['wpau_stock_ticker_css']."</style>";
        }
        add_action( 'wp_footer', 'wpau_stock_ticker_byshortcode' );

        // register stock_ticker shortcode
        add_shortcode( 'stock_ticker', array('WPAU_STOCK_TICKER','stock_ticker_shortcode') );
    }
}
