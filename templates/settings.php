<?php
/**
 * Stock Ticker General Settings page template
 *
 * @category Template
 * @package Stock Ticker
 * @author Aleksandar Urosevic
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link http://urosevic.net
 */

?>
<div class="wrap" id="stock_ticker_settings">
	<h2><?php esc_attr_e( 'Stock Ticker Settings', 'wpaust' ); ?></h2>
	<form method="post" action="options.php">
		<?php @settings_fields( 'default_settings' ); ?>
		<?php @settings_fields( 'advanced_settings' ); ?>
		<?php @do_settings_sections( 'wpau_stock_ticker' ); ?>
		<?php @submit_button(); ?>
	</form>
	<h2><?php esc_attr_e( 'Help', 'wpaust' ); ?></h2>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top" style="float:right">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="98DNTKSUMAM5Q">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
	<p><?php printf( esc_attr__( 'You also can use shortcode %s where:', 'wpaust' ), '<code>[stock_ticker symbols="" show="" zero="" minus="" plus="" static="" nolink=""]</code>' ); ?>
		<ul>
			<li><code>symbols</code> <?php esc_attr_e( 'represent array of stock symbols (default from this settings page used if no custom set by shortcode)', 'wpaust' ); ?></li>
			<li><code>show</code> <?php printf( esc_attr__( 'can be %s to represent company with Company Name (default), or %s to represent company with Stock Symbol', 'wpaust' ), '<code>name</code>', '<code>symbol</code>' ); ?></li>
			<li><code>zero</code> <?php esc_attr_e( 'is HEX or RGBA colour for unchanged quote', 'wpaust' ); ?></li>
			<li><code>minus</code> <?php esc_attr_e( 'is HEX or RGBA colour for negative change of quote', 'wpaust' ); ?></li>
			<li><code>plus</code> <?php esc_attr_e( 'is HEX or RGBA colour for positive change of quote', 'wpaust' ); ?></li>
			<li><code>static</code> <?php printf( esc_attr__( 'disables scrolling ticker and makes it static if set to %s or %s', 'wpaust' ), '<code>1</code>', '<code>true</code>' ); ?></li>
			<li><code>nolink</code> <?php printf( esc_attr__( 'to disable link of quotes to Google Finance page set to %s or %s', 'wpaust' ), '<code>1</code>', '<code>true</code>' ); ?></li>
		</ul>
	</p>

	<h2><?php esc_attr_e( 'Notice', 'wpaust' ); ?></h2>
	<p>If you wish to insert quotes as inline elements in your posts or pages, consider using our related plugin <a href="https://wordpress.org/plugins/stock-quote/" target="_blank">Stock Quote</a>.</p>
	<h2><?php esc_attr_e( 'Disclaimer', 'wpaust' ); ?></h2>
	<p class="description">Data for Stock Ticker has provided by Google Finance and per their disclaimer,
it can only be used at a noncommercial level. Please also note that Google has stated
Finance API as deprecated and has no exact shutdown date.<br />
<br />
<a href="http://www.google.com/intl/en-US/googlefinance/disclaimer/#disclaimers">Google Finance Disclaimer</a><br />
<br />
Data is provided by financial exchanges and may be delayed as specified
by financial exchanges or our data providers. Google does not verify any
data and disclaims any obligation to do so.
<br />
Google, its data or content providers, the financial exchanges and
each of their affiliates and business partners (A) expressly disclaim
the accuracy, adequacy, or completeness of any data and (B) shall not be
liable for any errors, omissions or other defects in, delays or
interruptions in such data, or for any actions taken in reliance thereon.
Neither Google nor any of our information providers will be liable for
any damages relating to your use of the information provided herein.
As used here, “business partners” does not refer to an agency, partnership,
or joint venture relationship between Google and any such parties.
<br />
You agree not to copy, modify, reformat, download, store, reproduce,
reprocess, transmit or redistribute any data or information found herein
or use any such data or information in a commercial enterprise without
obtaining prior written consent. All data and information is provided “as is”
for personal informational purposes only, and is not intended for trading
purposes or advice. Please consult your broker or financial representative
to verify pricing before executing any trade.
<br />
Either Google or its third party data or content providers have exclusive
proprietary rights in the data and information provided.
<br />
Please find all listed exchanges and indices covered by Google along with
their respective time delays from the table on the left.
<br />
Advertisements presented on Google Finance are solely the responsibility
of the party from whom the ad originates. Neither Google nor any of its
data licensors endorses or is responsible for the content of any advertisement
or any goods or services offered therein.</p>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
	$('.wpau-color-field').wpColorPicker();
});
</script>
