<div class="wrap" id="stock_ticker_settings">
    <h2><?php _e( 'Stock Ticker Settings', 'wpaust' ); ?></h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('default_settings'); ?>
        <?php @settings_fields('advanced_settings'); ?>
        <?php //@do_settings_fields('wpau_stock_ticker', 'default_settings'); ?>

        <?php @do_settings_sections('wpau_stock_ticker'); ?>

        <?php @submit_button(); ?>
    </form>
    <h2><?php _e( 'Help', 'wpaust' ); ?></h2>
    <p><?php printf(__('You also can use shortcode <code>%s</code> where:', 'wpaust'), '[stock_ticker symbols="" show="" zero="" minus="" plus=""]'); ?>
    	<ul>
    		<li><code>symbols</code> <?php _e('represent array of stock symbols (default from this settings page used if no custom set by shortcode)', 'wpaust'); ?></li>
    		<li><code>show</code> <?php printf(__('can be <code>%s</code> to represent company with Company Name (default), or <code>%s</code> to represent company with Stock Symbol', 'wpaust'),'name','symbol'); ?></li>
            <li><code>zero</code> <?php _e('is HEX or RGBA colour for unchanged quote', 'wpaust'); ?></li>
            <li><code>minus</code> <?php _e('is HEX or RGBA colour for negative change of quote', 'wpaust'); ?></li>
            <li><code>plus</code> <?php _e('is HEX or RGBA colour for positive change of quote', 'wpaust'); ?></li>

    	</ul>
    </p>
</div>
<script type="text/javascript">
jQuery(document).ready(function($){
    $('.wpau-color-field').wpColorPicker();
});
</script>