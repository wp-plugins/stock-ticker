<?php
/**
 * Stock Ticker Widget
 *
 * @category WPAU_Stock_Ticker_Widget
 * @package Stock Ticker
 * @author Aleksandar Urosevic
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link http://urosevic.net
 */

/**
 * WPAU_Stock_Ticker_Widget Class provide widget settings and output for Stock Ticker plugin
 *
 * @category Class
 * @package Stock Ticker
 * @author Aleksandar Urosevic
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link http://urosevic.net
 */
class WPAU_Stock_Ticker_Widget extends WP_Widget
{
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		// Widget actual processes.
		parent::__construct(
			'stock_ticker', // Base ID.
			__( 'Stock Ticker', 'wpaust' ), // Name.
			array( 'description' => __( 'Show ticker with stock trends', 'wpaust' ) ) // Args.
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args Array of widget parameters.
	 * @param array $instance Array of widget settings.
	 */
	public function widget($args, $instance) {
		// Use cached widget in customizer.
		if ( ! $this->is_preview() ) {
			$cached = wp_cache_get( $args['widget_id'] );
			if ( ! empty( $cached ) ) {
				echo $cached;
				return;
			}
			ob_start();
		}

		// Get defaults in instance is empty (for customizer).
		if ( empty( $instance ) ) {
			$instance = WPAU_STOCK_TICKER::defaults();
			$instance['title'] = __( 'Stock Ticker', 'wpaust' );
		}

		// Outputs the content of the widget.
		if ( ! empty( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
		}

		if ( empty( $instance['symbols'] ) ) {
			return;
		}

		$symbols = $instance['symbols'];
		$show    = $instance['show'];
		$zero    = $instance['zero'];
		$minus   = $instance['minus'];
		$plus    = $instance['plus'];
		$static  = empty( $instance['static'] ) ? '0' : '1';
		$nolink  = empty( $instance['nolink'] ) ? '0' : '1';

		// Output live stock ticker widget.
		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		echo WPAU_STOCK_TICKER::stock_ticker( $symbols, $show, $zero, $minus, $plus, $static, $nolink );

		echo $args['after_widget'];

		// End cache in customizer.
		if ( ! $this->is_preview() ) {
			$cached = ob_get_flush();
			wp_cache_set( $args['widget_id'], $cached );
		}
	}

	/**
	 * Ouputs the options form on admin
	 *
	 * @param array $instance The widget options.
	 */
	public function form( $instance ) {

		// Get defaults.
		$defaults = WPAU_STOCK_TICKER::defaults();

		// Outputs the options form on admin.
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Stock Ticker', 'wpaust' );
		}
		if ( isset( $instance['symbols'] ) ) {
			$symbols = $instance['symbols'];
		} else {
			$symbols = $defaults['symbols'];
		}
		if ( isset( $instance['show'] ) ) {
			$show = $instance['show'];
		} else {
			$show = $defaults['show'];
		}

		if ( isset( $instance['zero'] ) ) {
			$zero = $instance['zero'];
		} else {
			$zero = $defaults['zero'];
		}

		if ( isset( $instance['minus'] ) ) {
			$minus = $instance['minus'];
		} else {
			$minus = $defaults['minus'];
		}

		if ( isset( $instance['plus'] ) ) {
			$plus = $instance['plus'];
		} else {
			$plus = $defaults['plus'];
		}

		if ( isset( $instance['static'] ) ) {
			$static = $instance['static'];
		} else {
			$static = '0';
		}

		if ( isset( $instance['nolink'] ) ) {
			$nolink = $instance['nolink'];
		} else {
			$nolink = '0';
		}

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_attr_e( 'Title' ); ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'symbols' ); ?>"><?php esc_attr_e( 'Stock Symbols', 'wpaust' ); ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'symbols' ); ?>" name="<?php echo $this->get_field_name( 'symbols' ); ?>" type="text" value="<?php echo esc_attr( $symbols ); ?>" title="<?php esc_html_e( 'For currencies use format EURGBP=X; for Dow Jones use ^DJI; for specific stock exchange use format EXCHANGE:SYMBOL like LON:FFX', 'wpaust' ); ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'show' ); ?>"><?php esc_attr_e( 'Represent Company as', 'wpaust' ); ?>:</label>
		<select class="widefat" id="<?php echo $this->get_field_id( 'show' ); ?>" name="<?php echo $this->get_field_name( 'show' ); ?>">
			<option <?php echo ('name' == $show) ? 'selected="selected"' : ''; ?> value="name"><?php esc_attr_e( 'Company Name', 'wpaust' ); ?></option>
			<option <?php echo ('symbol' == $show) ? 'selected="selected"' : ''; ?> value="symbol"><?php esc_attr_e( 'Stock Symbol', 'wpaust' ); ?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'zero' ); ?>"><?php esc_attr_e( 'Unchanged Quote', 'wpaust' ); ?>:</label><br />
		<input class="wpau-color-field" id="<?php echo $this->get_field_id( 'zero' ); ?>" name="<?php echo $this->get_field_name( 'zero' ); ?>" type="text" value="<?php echo esc_attr( $zero ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'minus' ); ?>"><?php esc_attr_e( 'Negative Change', 'wpaust' ); ?>:</label><br />
		<input class="wpau-color-field" id="<?php echo $this->get_field_id( 'minus' ); ?>" name="<?php echo $this->get_field_name( 'minus' ); ?>" type="text" value="<?php echo esc_attr( $minus ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'plus' ); ?>"><?php esc_attr_e( 'Positive Change', 'wpaust' ); ?>:</label><br />
		<input class="wpau-color-field" id="<?php echo $this->get_field_id( 'plus' ); ?>" name="<?php echo $this->get_field_name( 'plus' ); ?>" type="text" value="<?php echo esc_attr( $plus ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'static' ); ?>">
		<input class="checkbox" id="<?php echo $this->get_field_id( 'static' ); ?>" name="<?php echo $this->get_field_name( 'static' ); ?>" type="checkbox" value="1" <?php checked( $static, true, true ); ?> />
		<?php esc_attr_e( 'Make this ticker static (disable scrolling)', 'wpaust' ); ?>
		</label>
		<br />
		<label for="<?php echo $this->get_field_id( 'nolink' ); ?>">
		<input class="checkbox" id="<?php echo $this->get_field_id( 'nolink' ); ?>" name="<?php echo $this->get_field_name( 'nolink' ); ?>" type="checkbox" value="1" <?php checked( $nolink, true, true ); ?> />
		<?php esc_attr_e( 'Do not link quotes', 'wpaust' ); ?>
		</label>
		</p>

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($)
{
	$('#widgets-right .wpau-color-field').each(function(){
		if ( $(this).parent().attr('class') != 'wp-picker-input-wrap' )
		{
			$(this).wpColorPicker();
		}
	});
});
// now deal with fresh added widget
jQuery('#widgets-right .widgets-sortables').on('sortstop', function(event,ui){
	jQuery(this).find('div[id*="stock_ticker"]').each(function(){
		var ticker_id = jQuery(this).attr('id');
		if ( jQuery(ticker_id).find('.wpau-color-field').parent().attr('class') != 'wp-picker-input-wrap' )
		{
			jQuery(ticker_id).find('.wpau-color-field').wpColorPicker();
		}
	});
});
//]]>
</script>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options.
	 * @param array $old_instance The previous options.
	 */
	public function update($new_instance, $old_instance) {
		// Processes widget options to be saved.
		$instance = array();
		$instance['title']   = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['symbols'] = ( ! empty( $new_instance['symbols'] ) ) ? strip_tags( $new_instance['symbols'] ) : '';
		$instance['show']    = ( ! empty( $new_instance['show'] ) ) ? strip_tags( $new_instance['show'] ) : '';
		$instance['zero']    = ( ! empty( $new_instance['zero'] ) ) ? strip_tags( $new_instance['zero'] ) : '';
		$instance['minus']   = ( ! empty( $new_instance['minus'] ) ) ? strip_tags( $new_instance['minus'] ) : '';
		$instance['plus']    = ( ! empty( $new_instance['plus'] ) ) ? strip_tags( $new_instance['plus'] ) : '';
		$instance['static']  = ( ! empty( $new_instance['static'] ) ) ? '1' : '0';
		$instance['nolink']  = ( ! empty( $new_instance['nolink'] ) ) ? '1' : '0';

		return $instance;
	}
}

/**
 * Register widget
 */
function stock_ticker_init() {
	register_widget( 'WPAU_Stock_Ticker_Widget' );
}
add_action( 'widgets_init', 'stock_ticker_init' );
