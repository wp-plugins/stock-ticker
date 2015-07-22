<?php
/*
Plugin Name: Stock Ticker
Plugin URI: http://urosevic.net/wordpress/plugins/stock-ticker/
Description: Easy add customizable moving ticker tapes with stock information
Version: 0.1.5
Author: Aleksandar Urosevic
Author URI: http://urosevic.net
License: GNU GPL3
*/
/*
Copyright 2014-2015 Aleksandar Urosevic (urke.kg@gmail.com)

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

/*
Google Finance Disclaimer <http://www.google.com/intl/en-US/googlefinance/disclaimer/>

Data is provided by financial exchanges and may be delayed as specified
by financial exchanges or our data providers. Google does not verify any
data and disclaims any obligation to do so.

Google, its data or content providers, the financial exchanges and
each of their affiliates and business partners (A) expressly disclaim
the accuracy, adequacy, or completeness of any data and (B) shall not be
liable for any errors, omissions or other defects in, delays or
interruptions in such data, or for any actions taken in reliance thereon.
Neither Google nor any of our information providers will be liable for
any damages relating to your use of the information provided herein.
As used here, “business partners” does not refer to an agency, partnership,
or joint venture relationship between Google and any such parties.

You agree not to copy, modify, reformat, download, store, reproduce,
reprocess, transmit or redistribute any data or information found herein
or use any such data or information in a commercial enterprise without
obtaining prior written consent. All data and information is provided “as is”
for personal informational purposes only, and is not intended for trading
purposes or advice. Please consult your broker or financial representative
to verify pricing before executing any trade.

Either Google or its third party data or content providers have exclusive
proprietary rights in the data and information provided.

Please find all listed exchanges and indices covered by Google along with
their respective time delays from the table on the left.

Advertisements presented on Google Finance are solely the responsibility
of the party from whom the ad originates. Neither Google nor any of its
data licensors endorses or is responsible for the content of any advertisement
or any goods or services offered therein.

 */

if(!class_exists('WPAU_STOCK_TICKER'))
{
    class WPAU_STOCK_TICKER
    {
        public static $wpau_stock_ticker_ids = NULL;
        public static $wpau_stock_ticker_css = NULL;

        /**
         * Construct the plugin object
         */
        public function __construct() {
            define('WPAU_STOCK_TICKER_VER','0.1.5');

            // Initialize Settings
            require_once(sprintf("%s/inc/settings.php", dirname(__FILE__)));
            // Initialize Widget
            require_once(sprintf("%s/inc/widget.php", dirname(__FILE__)));

            $WPAU_Stock_Ticker_Settings = new WPAU_Stock_Ticker_Settings();
        } // END public function __construct()

        /**
         * Defaults
         */
        public static function defaults() {
            $defaults = array(
                'symbols'       => 'AAPL,MSFT,INTC',
                'show'          => 'name',
                'zero'          => '#454545',
                'minus'         => '#D8442F',
                'plus'          => '#009D59',
                'cache_timeout' => '180', // 3 minutes
                'error_message' => 'Unfortunately, we could not get stock quotes this time.',
                'legend'        => "AAPL;Apple Inc.\nFB;Facebook, Inc.\nCSCO;Cisco Systems, Inc.\nGOOG;Google Inc.\nINTC;Intel Corporation\nLNKD;LinkedIn Corporation\nMSFT;Microsoft Corporation\nTWTR;Twitter, Inc.\nBABA;Alibaba Group Holding Limited\nIBM;International Business Machines Corporation\n.DJI;Dow Jones Industrial Average\nEURGBP;Euro (€) ⇨ British Pound Sterling (£)",
                'style'         => 'font-family: "Open Sans", Helvetica, Arial, sans-serif; font-weight: normal; font-size: 14px;',
                'template'      => '%company% %price% %change% %changep%' // $company_show.' '.$q_price.' '.$q_change.' '.$q_changep.'%
            );
            $options = wp_parse_args(get_option('stock_ticker_defaults'), $defaults);
            return $options;
        } // END public static function defaults()

        /**
         * Activate the plugin
         */
        public static function activate() {
            // Transit old settings to new format
            $defaults = self::defaults();
            if ( get_option('st_symbols') )
            {
                $defaults['symbols'] = get_option('st_symbols');
                delete_option('st_symbols');
            }
            if ( get_option('st_show') ) { $defaults['show'] = get_option('st_show'); delete_option('st_show'); }
            if ( get_option('st_quote_zero') ) { $defaults['zero'] = get_option('st_quote_zero'); delete_option('st_quote_zero'); }
            if ( get_option('st_quote_minus') ) { $defaults['minus'] = get_option('st_quote_minus'); delete_option('st_quote_minus'); }
            if ( get_option('st_quote_plus') ) { $defaults['plus'] = get_option('st_quote_plus'); delete_option('st_quote_plus'); }
            update_option('stock_ticker_defaults', $defaults);
        } // END public static function activate()

        /**
         * Deactivate the plugin
         */
        public static function deactivate() {
            // Do nothing
        } // END public static function deactivate()

        /**
         * Ticker function for widget and shortcode
         */
        public static function stock_ticker($symbols, $show, $zero, $minus, $plus, $static, $nolink) {

            if ( ! empty($symbols) )
            {

                // get fresh or from transient cache stock quote
                $st_transient_id = "st_json_" . md5($symbols);

                // get legend for company names
                $defaults = self::defaults();
                $matrix = explode("\n",$defaults['legend']);
                $msize = sizeof($matrix);
                for($m=0; $m<$msize; $m++)
                {
                    $line = explode(";",$matrix[$m]);
                    $legend[strtoupper(trim($line[0]))] = trim($line[1]);
                }
                unset($m,$msize,$matrix,$line);

                // check if cache exists
                if ( false === ( $json = get_transient( $st_transient_id ) ) || empty($json) )
                {
                    // if does not exist, get new cache

                    // clean and prepare symbols for query
                    $exc_symbols = preg_replace('/\s+/', '', $symbols);
                    // adapt ^DIJ to .DJI
                    $exc_symbols = preg_replace('/\^/', '.', $exc_symbols);
                    // replace amp with code
                    $exc_symbols = str_replace('&', '%26', $exc_symbols);
                    // adapt currency symbols EURGBP=X to CURRENCY:EURGBP
                    $exc_symbols = preg_replace('/([a-zA-Z]*)\=X/i',"CURRENCY:$1",$exc_symbols);
                    // compose URL
                    $exc_url = "http://finance.google.com/finance/info?client=ig&q=$exc_symbols";

                    // set timeout
                    $wprga = array(
                        'timeout' => 2 // two seconds only
                    );
                    // get stock from Google
                    $response = wp_remote_get($exc_url, $wprga);
                    // get content from response
                    $data = wp_remote_retrieve_body( $response );
                    // convert a string with ISO-8859-1 characters encoded with UTF-8 to single-byte ISO-8859-1
                    $data = utf8_decode( $data );
                    // remove newlines from content
                    $data = str_replace( "\n", "", $data );
                    // remove // from content
                    $data = trim(str_replace('/', '', $data));

                    // decode data to JSON
                    $json = json_decode($data);
                    // now cache array for N minutes
                    if ( !defined('WPAU_STOCK_TICKER_CACHE_TIMEOUT') )
                    {
                        // $defaults = WPAU_STOCK_TICKER::defaults();
                        define('WPAU_STOCK_TICKER_CACHE_TIMEOUT',$defaults['cache_timeout']);
                        // unset($defaults);
                    }
                    set_transient( $st_transient_id, $json, WPAU_STOCK_TICKER_CACHE_TIMEOUT );

                    // free some memory: destroy all vars that we temporary used here
                    unset($exc_symbols, $exc_url, $reponse);
                }

                // prepare ticker
                $id = 'stock_ticker_'. substr(md5(mt_rand()),0,8);
                $class = ( ! empty($static) && $static == 1 ) ? ' static' : '';
                $out = '<ul id="' .$id. '" class="stock_ticker' . $class . '">';

                // process quotes
                if( ! empty($json) && ! is_null($json[0]->id) )
                {
                    // start ticker string
                    $q = "";

                    // Parse results and extract data to display
                    foreach($json as $quote)
                    {
                        // assign object elements to vars
                        $q_change  = $quote->c;
                        $q_price   = $quote->l;
                        $q_name    = $quote->t;
                        $q_changep = $quote->cp;
                        $q_symbol  = $quote->t;
                        $q_ltrade  = $quote->lt;
                        $q_exch    = $quote->e;

                        // Define class based on change
                        if ( $q_change < 0 ) { $chclass = "minus"; }
                        else if ( $q_change > 0 ) { $chclass = "plus"; }
                        else { $chclass = "zero"; $q_change = "0.00"; }

                        // Get custom company name if exists
                        if ( ! empty($legend[ $q_exch.':'.$q_symbol ]) ) {
                            // first in format EXCHANGE:SYMBOL
                            $q_name = $legend[ $q_exch.':'.$q_symbol ];
                        } else if ( ! empty($legend[$q_symbol]) ) {
                            // then in format SYMBOL
                            $q_name = $legend[ $q_symbol ];
                        }

                        // What to show: Symbol or Company Name?
                        if ( $show == "name" ) {
                            $company_show = $q_name;
                        } else {
                            $company_show = $q_symbol;
                        }
                        // open stock quote item
                        $q .= '<li class="'.$chclass.'">';

                        // Do not print change, volume and change% for currencies
                        if ($q_exch == "CURRENCY") {
                            $company_show = ( $q_symbol == $q_name ) ? $q_name . '=X' : $q_name;
                            $url_query = $q_symbol;
                            $quote_title = $q_name;
                        } else {
                            $url_query = $q_exch.':'.$q_symbol;
                            $quote_title = $q_name.' ('.$q_exch.' Last trade '.$q_ltrade.')';
                        }

                        // value template
                        $template = $defaults['template'];
                        $template = str_replace('%company%', $company_show, $template);
                        $template = str_replace('%symbol%', $q_symbol, $template);
                        $template = str_replace('%exch_symbol%', $url_query, $template);
                        $template = str_replace('%price%', $q_price, $template);
                        $template = str_replace('%change%', $q_change, $template);
                        $template = str_replace('%changep%', "{$q_changep}%", $template);

                        // quote w/ or w/o link
                        if ( empty($nolink) ) {
                            $q .= '<a href="https://www.google.com/finance?q='.$url_query.'" class="sqitem" target="_blank" title="'.$quote_title.'">'.$template.'</a>';
                            // $q .= '<a href="https://www.google.com/finance?q='.$url_query.'" class="sqitem" target="_blank" title="'.$quote_title.'">'.$company_show.' '.$q_price.' '.$q_change.' '.$q_changep.'%</a>';
                        } else {
                            $q .= '<span class="sqitem" title="'.$quote_title.'">'.$template.'</span>';
                            // $q .= '<span class="sqitem" title="'.$quote_title.'">'.$company_show.' '.$q_price.' '.$q_change.' '.$q_changep.'%</span>';
                        }

                        // close stock quote item
                        $q .= '</li>';

                    }
                }

                // No results were returned
                if( empty($q) )
                    $q = '<li class="error">'.$defaults['error_message'].'</li>';

                $out .= $q;

                $out .= '</ul>';

                // prepare styles
                $css = "ul#{$id}.stock_ticker li.zero .sqitem,ul#{$id}.stock_ticker li.zero .sqitem:hover { color: $zero; }";
                $css .= "ul#{$id}.stock_ticker li.minus .sqitem,ul#{$id}.stock_ticker li.minus .sqitem:hover { color: $minus; }";
                $css .= "ul#{$id}.stock_ticker li.plus .sqitem,ul#{$id}.stock_ticker li.plus .sqitem:hover { color: $plus; }";

                // append customized styles
                if ( is_null(self::$wpau_stock_ticker_css) )
                    self::$wpau_stock_ticker_css = ( empty($defaults['style']) ) ? $css : "ul.stock_ticker li .sqitem{".$defaults['style']."}$css";
                else
                    self::$wpau_stock_ticker_css .= $css;

                // append ticker ID for initializing scrolling tickers
                // do not append if static is enabled for this isntance
                if ( empty($static) && is_null(self::$wpau_stock_ticker_ids) ) {
                    self::$wpau_stock_ticker_ids = "#".$id;
                }
                else if ( empty($static) ) {
                    self::$wpau_stock_ticker_ids .= ",#".$id;
                }

                unset($q, $id, $css, $defaults, $legend);

                // print ticker content
                return $out;

            }
        } // END public static function stock_ticker()

        /**
         * Shortcode for stock ticker
         */
        public static function stock_ticker_shortcode($atts, $content=null) {

            $st_defaults = WPAU_STOCK_TICKER::defaults();
            extract( shortcode_atts( array(
                'symbols' => $st_defaults['symbols'],
                'show'    => $st_defaults['show'],
                'zero'    => $st_defaults['zero'],
                'minus'   => $st_defaults['minus'],
                'plus'    => $st_defaults['plus'],
                'static'  => false,
                'nolink'  => false
            ), $atts ) );

            if ( ! empty($symbols) ) {
                $symbols = strip_tags($symbols);
                return self::stock_ticker($symbols, $show, $zero, $minus, $plus, $static, $nolink);
            }

        } // END public static function stock_ticker_shortcode()

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
        function wpau_stock_ticker_plugin_settings_link($links) {
            $settings_link = '<a href="options-general.php?page=wpau_stock_ticker">Settings</a>';
            array_unshift($links, $settings_link);
            return $links;
        } // eof wpau_stock_ticker_plugin_settings_link()

        $plugin = plugin_basename(__FILE__);
        add_filter("plugin_action_links_$plugin", 'wpau_stock_ticker_plugin_settings_link');

        /**
         * Enqueue the colour picker
         */
        function wpau_enqueue_colour_picker() {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
        } // END function wpau_enqueue_colour_picker()
        add_action( 'admin_enqueue_scripts', 'wpau_enqueue_colour_picker' );

        // JS tool for frontend
        function wpau_stock_ticker_js() {
            wp_enqueue_script( 'jquery-ticker', plugin_dir_url(__FILE__) . 'assets/js/jquery.webticker.min.js', array('jquery'), WPAU_STOCK_TICKER_VER ); //'1.0.0' );
            wp_enqueue_style( 'stock-ticker', plugin_dir_url(__FILE__) .'assets/css/stock-ticker.css', array(), WPAU_STOCK_TICKER_VER ); //'1.0.0' );
        } // END function wpau_stock_ticker_js()
        add_action( 'wp_enqueue_scripts', 'wpau_stock_ticker_js' );

        /**
         * Output jQuery for animated tickers and prepared custom styling
         * @return string SCRIPT and STYLE in page footer
         */
        function wpau_stock_ticker_byshortcode() {

            // get class vars
            $ticker_class_vars = get_class_vars('wpau_stock_ticker');

            // output script for animated tickers
            if ( ! is_null($ticker_class_vars['wpau_stock_ticker_ids']) )
                echo "<script type=\"text/javascript\">jQuery(document).ready(function(){jQuery(\"".$ticker_class_vars['wpau_stock_ticker_ids']."\").webTicker();});</script>";

            // output custom styles
            if ( !empty($ticker_class_vars['wpau_stock_ticker_css']) )
                echo "<style type=\"text/css\">".$ticker_class_vars['wpau_stock_ticker_css']."</style>";

        } // END function wpau_stock_ticker_byshortcode()
        add_action( 'wp_footer', 'wpau_stock_ticker_byshortcode' );

        // register stock_ticker shortcode
        add_shortcode( 'stock_ticker', array('WPAU_STOCK_TICKER','stock_ticker_shortcode') );

    } // END isset($wpau_stock_ticker)

} // END class_exists('WPAU_STOCK_TICKER')
