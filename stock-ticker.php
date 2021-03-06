<?php
/**
Plugin Name: Stock Ticker
Plugin URI: http://urosevic.net/wordpress/plugins/stock-ticker/
Description: Easy add customizable moving or static ticker tapes with stock information for custom stock symbols.
Version: 0.1.6
Author: Aleksandar Urosevic
Author URI: http://urosevic.net
License: GNU GPL3
 * @package Stock Ticker
 */

/**
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

/**
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

define( 'WPAU_STOCK_TICKER_VER', '0.1.6' );

if ( ! class_exists( 'WPAU_STOCK_TICKER' ) ) {

	/**
	 * WPAU_STOCK_TICKER Class provide main plugin functionality
	 *
	 * @category Class
	 * @package Stock Ticker
	 * @author Aleksandar Urosevic
	 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
	 * @link http://urosevic.net
	 */
	class WPAU_STOCK_TICKER
	{
		/**
		 * Keep array of block ID's for animated ticker
		 * @var null
		 */
		public static $wpau_stock_ticker_ids = null;

		/**
		 * Keep inline CSS styles for all customized blocks
		 * @var null
		 */
		public static $wpau_stock_ticker_css = null;

		/**
		 * Global default options
		 * @var null
		 */
		public static $defaults = null;

		/**
		 * Construct the plugin object
		 */
		public function __construct() {
			// Initialize default settings
			self::$defaults = self::defaults();

			// Installation and uninstallation hooks.
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

			// Add Settings page link to plugin actions cell.
			$plugin_file = plugin_basename( __FILE__ );
			add_filter( "plugin_action_links_$plugin_file", array( $this, 'plugin_settings_link' ) );

			// Update links in plugin row on Plugins page.
			add_filter( 'plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2 );

			// Load colour picker scripts on plugin settings page and on widgets/customizer.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_colour_picker' ) );

			// Enqueue frontend scripts.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// Add dynamic scripts and styles to footer
			add_action( 'wp_footer', array( $this, 'wp_footer' ) );

			// Register stock_ticker shortcode.
			add_shortcode( 'stock_ticker', array( $this, 'shortcode' ) );

			// Initialize Settings.
			require_once( sprintf( '%s/inc/settings.php', dirname( __FILE__ ) ) );
			// Initialize Widget.
			require_once( sprintf( '%s/inc/widget.php', dirname( __FILE__ ) ) );

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
				'template'      => '%company% %price% %change% %changep%',
				'error_message' => 'Unfortunately, we could not get stock quotes this time.',
				'legend'        => "AAPL;Apple Inc.\nFB;Facebook, Inc.\nCSCO;Cisco Systems, Inc.\nGOOG;Google Inc.\nINTC;Intel Corporation\nLNKD;LinkedIn Corporation\nMSFT;Microsoft Corporation\nTWTR;Twitter, Inc.\nBABA;Alibaba Group Holding Limited\nIBM;International Business Machines Corporationn\n.DJI;Dow Jones Industrial Average\nEURGBP;Euro (€) ⇨ British Pound Sterling (£)",
				'style'         => 'font-family:"Open Sans",Helvetica,Arial,sans-serif;font-weight:normal;font-size:14px;',
			);
			$options = wp_parse_args( get_option( 'stock_ticker_defaults' ), $defaults );
			return $options;
		} // END public static function defaults()

		/**
		 * Activate the plugin
		 */
		public static function activate() {
			// Transit old settings to new format.
			$defaults = self::$defaults;
			if ( get_option( 'st_symbols' ) ) {
				$defaults['symbols'] = get_option( 'st_symbols' );
				delete_option( 'st_symbols' );
			}
			if ( get_option( 'st_show' ) ) {
				$defaults['show'] = get_option( 'st_show' );
				delete_option( 'st_show' );
			}
			if ( get_option( 'st_quote_zero' ) ) {
				$defaults['zero'] = get_option( 'st_quote_zero' );
				delete_option( 'st_quote_zero' );
			}
			if ( get_option( 'st_quote_minus' ) ) {
				$defaults['minus'] = get_option( 'st_quote_minus' );
				delete_option( 'st_quote_minus' );
			}
			if ( get_option( 'st_quote_plus' ) ) {
				$defaults['plus'] = get_option( 'st_quote_plus' );
				delete_option( 'st_quote_plus' );
			}
			update_option( 'stock_ticker_defaults', $defaults );
		} // END public static function activate()

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate() {
			// Do nothing.
		} // END public static function deactivate()

		/**
		 * Enqueue the colour picker
		 */
		public static function enqueue_colour_picker($hook) {
			if ( in_array( $hook, array( 'settings_page_wpau_stock_ticker', 'widgets.php' ) ) ) {
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
			}
		} // END function wpau_enqueue_colour_picker()

		/**
		 * Enqueue frontend assets
		 */
		public static function enqueue_scripts() {
			wp_enqueue_script(
				'jquery-ticker',
				plugin_dir_url( __FILE__ ) . 'assets/js/jquery.webticker.min.js',
				array( 'jquery' ),
				WPAU_STOCK_TICKER_VER
			);
			wp_enqueue_style(
				'stock-ticker',
				plugin_dir_url( __FILE__ ) .'assets/css/stock-ticker.css',
				array(),
				WPAU_STOCK_TICKER_VER
			);
		} // END public static function enqueue_scripts()


		/**
		 * Output jQuery for animated tickers and prepare custom styling
		 */
		public static function wp_footer() {

			// Output script for animated tickers.
			if ( ! empty( self::$wpau_stock_ticker_ids ) ) {
				echo '<script type="text/javascript">jQuery(document).ready(function(){jQuery("' . implode( ',', self::$wpau_stock_ticker_ids ) . '").webTicker();});</script>';
			}

			// Compose and output custom CSS.
			if ( ! empty( self::$wpau_stock_ticker_css ) ) {

				// Start CSS block.
				$css = '<style type="text/css">';

				// First generate global style and colours.
				if ( ! empty( self::$defaults['style'] ) ) {
					$css .= 'ul.stock_ticker li .sqitem{' . self::$defaults['style'] . '}';
					if ( ! empty( self::$defaults['zero'] ) ) {
						$css .= 'ul.stock_ticker li.zero .sqitem,ul.stock_ticker li.zero .sqitem:hover {color:' . self::$defaults['zero'] . '}';
					}
					if ( ! empty( self::$defaults['minus'] ) ) {
						$css .= 'ul.stock_ticker li.minus .sqitem,ul.stock_ticker li.minus .sqitem:hover {color:' . self::$defaults['minus'] . '}';
					}
					if ( ! empty( self::$defaults['plus'] ) ) {
						$css .= 'ul.stock_ticker li.plus .sqitem,ul.stock_ticker li.plus .sqitem:hover {color:' . self::$defaults['plus'] . '}';
					}
				}

				// Then add ticker specific colours if they are different than defaults
				foreach ( self::$wpau_stock_ticker_css as $ticker_colours ) {
					list( $id, $zero, $minus, $plus ) = explode( ',', $ticker_colours );

					if ( $zero !== self::$defaults['zero'] ) {
						$css .= "ul#{$id}.stock_ticker li.zero .sqitem,ul#{$id}.stock_ticker li.zero .sqitem:hover {color:{$zero}}";
					}

					if ( $minus !== self::$defaults['minus'] ) {
						$css .= "ul#{$id}.stock_ticker li.minus .sqitem,ul#{$id}.stock_ticker li.minus .sqitem:hover {color:{$minus}}";
					}

					if ( $plus !== self::$defaults['plus'] ) {
						$css .= "ul#{$id}.stock_ticker li.plus .sqitem,ul#{$id}.stock_ticker li.plus .sqitem:hover {color:{$plus}}";
					}
				}

				// Close CSS block.
				$css .= '</style>';

				// Output generated CSS block
				echo $css;
			}

		} // END public static function wp_footer()
		/**
		 * Generate and output stock ticker block
		 * @param  string $symbols Comma separated array of symbols.
		 * @param  string $show    What to show (name or symbol).
		 * @param  string $zero    HEX colour for unchanged quote.
		 * @param  string $minus   HEX colour for negative changed quote.
		 * @param  string $plus    HEX colour for positive changed quote.
		 * @param  bool   $static  Request for static (non-animated) block.
		 * @param  bool   $nolink  Request for unlinked quotes.
		 * @return string          Composed HTML for block.
		 */
		public static function stock_ticker($symbols, $show, $zero, $minus, $plus, $static, $nolink) {

			if ( ! empty( $symbols ) ) {
				// Get fresh or from transient cache stock quote.
				$st_transient_id = 'st_json_' . md5( $symbols );

				// Get legend for company names.
				$defaults = self::$defaults;

				$matrix = explode( "\n", $defaults['legend'] );
				$msize = count( $matrix );
				for ( $m = 0; $m < $msize; ++$m ) {
					$line = explode( ';', $matrix[ $m ] );
					$legend[ strtoupper( trim( $line[0] ) ) ] = trim( $line[1] );
				}
				unset( $m, $msize, $matrix, $line );

				// Check if cache exists.
				if ( false === ( $json = get_transient( $st_transient_id ) ) || empty( $json ) ) {
					// If does not exist, get new cache.
					// Clean and prepare symbols for query.
					$exc_symbols = preg_replace( '/\s+/', '', $symbols );
					// Adapt ^DIJ to .DJI symbol format.
					$exc_symbols = preg_replace( '/\^/', '.', $exc_symbols );
					// Replace amp with code.
					$exc_symbols = str_replace( '&', '%26', $exc_symbols );
					// Adapt currency symbols EURGBP=X to CURRENCY:EURGBP symbol format.
					$exc_symbols = preg_replace( '/([a-zA-Z]*)\=X/i', 'CURRENCY:$1', $exc_symbols );
					// Compose URL.
					$exc_url = "http://finance.google.com/finance/info?client=ig&q={$exc_symbols}";

					// Set timeout.
					$wparg = array(
						'timeout' => 2, // Two seconds only.
					);
					// Get stock from Google.
					$response = wp_remote_get( $exc_url, $wparg );
					// Get content from response.
					$data = wp_remote_retrieve_body( $response );
					// Convert a string with ISO-8859-1 characters encoded with UTF-8 to single-byte ISO-8859-1.
					$data = utf8_decode( $data );
					// Remove newlines from content.
					$data = str_replace( "\n", '', $data );
					// Remove // from content.
					$data = trim( str_replace( '/', '', $data ) );

					// Decode data to JSON.
					$json = json_decode( $data );
					// Now cache array for N minutes.
					if ( ! defined( 'WPAU_STOCK_TICKER_CACHE_TIMEOUT' ) ) {
						define( 'WPAU_STOCK_TICKER_CACHE_TIMEOUT', $defaults['cache_timeout'] );
					}
					set_transient( $st_transient_id, $json, WPAU_STOCK_TICKER_CACHE_TIMEOUT );

					// Free some memory: destroy all vars that we temporary used here.
					unset( $exc_symbols, $exc_url, $reponse );
				}

				// Prepare ticker.
				$id = 'stock_ticker_' . substr( md5( mt_rand() ), 0, 4 );
				$class = ( ! empty( $static ) && 1 == $static ) ? ' static' : '';
				$out = "<ul id=\"{$id}\" class=\"stock_ticker{$class}\">";

				// Process quotes.
				if ( ! empty( $json ) && ! is_null( $json[0]->id ) ) {
					// Start ticker string.
					$q = '';

					// Parse results and extract data to display.
					foreach ( $json as $quote ) {
						// Assign object elements to vars.
						$q_change  = $quote->c;
						$q_price   = $quote->l;
						$q_name    = $quote->t; // No nicename in Google Finance so use Symbol instead.
						$q_changep = $quote->cp;
						$q_symbol  = $quote->t;
						$q_ltrade  = $quote->lt;
						$q_exch    = $quote->e;

						// Define class based on change.
						if ( $q_change < 0 ) {
							$chclass = 'minus';
						} elseif ( $q_change > 0 ) {
							$chclass = 'plus';
						} else {
							$chclass = 'zero';
							$q_change = '0.00';
						}

						// Get custom company name if exists.
						if ( ! empty( $legend[ $q_exch . ':' . $q_symbol ] ) ) {
							// First in format EXCHANGE:SYMBOL.
							$q_name = $legend[ $q_exch.':'.$q_symbol ];
						} elseif ( ! empty( $legend[ $q_symbol ] ) ) {
							// Then in format SYMBOL.
							$q_name = $legend[ $q_symbol ];
						}

						// What to show: Symbol or Company Name?
						if ( 'name' == $show ) {
							$company_show = $q_name;
						} else {
							$company_show = $q_symbol;
						}
						// Open stock quote item.
						$q .= "<li class=\"{$chclass}\">";

						// Do not print change, volume and change% for currencies.
						if ( 'CURRENCY' == $q_exch ) {
							$company_show = ( $q_symbol == $q_name ) ? $q_name . '=X' : $q_name;
							$url_query = $q_symbol;
							$quote_title = $q_name;
						} else {
							$url_query = $q_exch . ':' . $q_symbol;
							$quote_title = $q_name . ' (' . $q_exch . ' Last trade ' . $q_ltrade . ')';
						}

						// Value template.
						$template = $defaults['template'];
						$template = str_replace( '%company%', $company_show, $template );
						$template = str_replace( '%symbol%', $q_symbol, $template );
						$template = str_replace( '%exch_symbol%', $url_query, $template );
						$template = str_replace( '%price%', $q_price, $template );
						$template = str_replace( '%change%', $q_change, $template );
						$template = str_replace( '%changep%', "{$q_changep}%", $template );

						// Quote w/ or w/o link.
						if ( empty( $nolink ) ) {
							$q .= '<a href="https://www.google.com/finance?q=' . $url_query
							   . '" class="sqitem" target="_blank" title="' . $quote_title
							   . '">' . $template . '</a>';
						} else {
							$q .= '<span class="sqitem" title="' . $quote_title . '">' . $template . '</span>';
						}

						// Close stock quote item.
						$q .= '</li>';

					}
				}

				// No results were returned.
				if ( empty( $q ) ) {
					$q = "<li class=\"error\">{$defaults['error_message']}</li>";
				}

				$out .= $q;

				$out .= '</ul>';

				// Prepare vars in format: ID,zero,minus,plus
				self::$wpau_stock_ticker_css[] = "{$id},{$zero},{$minus},{$plus}";

				// Append ticker ID for initializing scrolling tickers.
				// Do not append if static is enabled for this isntance.
				if ( empty( $static ) ) {
					self::$wpau_stock_ticker_ids[] = "#{$id}";
				}

				unset( $q, $id, $css, $defaults, $legend );

				// Print ticker content.
				return $out;

			}
		} // END public static function stock_ticker()

		/**
		 * Shortcode processor for Stock Ticker
		 * @param  array $atts    Array of shortcode parameters.
		 * @return string         Generated HTML output for block.
		 */
		public static function shortcode($atts) {

			$st_defaults = self::$defaults;
			extract(shortcode_atts(array(
				'symbols' => $st_defaults['symbols'],
				'show'    => $st_defaults['show'],
				'zero'    => $st_defaults['zero'],
				'minus'   => $st_defaults['minus'],
				'plus'    => $st_defaults['plus'],
				'static'  => false,
				'nolink'  => false,
			), $atts));

			if ( ! empty( $symbols ) ) {
				$symbols = strip_tags( $symbols );
				return self::stock_ticker( $symbols, $show, $zero, $minus, $plus, $static, $nolink );
			}

		} // END public static function shortcode()

		/**
		 * Add link to official plugin pages
		 * @param array $links  Array of existing plugin row links.
		 * @param string $file  Path of current plugin file.
		 * @return array        Array of updated plugin row links
		 */
		public static function add_plugin_meta_links( $links, $file ) {
			if ( 'stock-ticker/stock-ticker.php' === $file ) {
				return array_merge(
					$links,
					array(
						sprintf(
							'<a href="https://wordpress.org/support/plugin/stock-ticker" target="_blank">%s</a>',
							__( 'Support' )
						),
						sprintf(
							'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=Q6Q762MQ97XJ6" target="_blank">%s</a>',
							__( 'Donate' )
						),
					)
				);
			}
			return $links;
		} // END public static function add_plugin_meta_links()

		/**
		 * Generate Settings link on Plugins page listing
		 * @param  array $links Array of existing plugin row links.
		 * @return array        Updated array of plugin row links with link to Settings page
		 */
		public static function plugin_settings_link( $links ) {
			$settings_link = '<a href="options-general.php?page=wpau_stock_ticker">Settings</a>';
			array_unshift( $links, $settings_link );
			return $links;
		} // END public static function plugin_settings_link()

	} // END class WPAU_STOCK_TICKER

} // END if(!class_exists('WPAU_STOCK_TICKER'))

if ( class_exists( 'WPAU_STOCK_TICKER' ) ) {

	// Instantiate the plugin class.
	$wpau_stock_ticker = new WPAU_STOCK_TICKER();

} // END class_exists('WPAU_STOCK_TICKER')
