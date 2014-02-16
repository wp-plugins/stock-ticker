<?php
if(!class_exists('WPAU_Stock_Ticker_Settings'))
{
	class WPAU_Stock_Ticker_Settings
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
            add_action('admin_init', array(&$this, 'admin_init'));
        	add_action('admin_menu', array(&$this, 'add_menu'));
		} // END public function __construct
		
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
        	// register your plugin's settings
            register_setting('wpau_stock_ticker-group', 'st_symbols');
            register_setting('wpau_stock_ticker-group', 'st_show');
            register_setting('wpau_stock_ticker-group', 'st_quote_zero');
            register_setting('wpau_stock_ticker-group', 'st_quote_minus');
        	register_setting('wpau_stock_ticker-group', 'st_quote_plus');

        	// add your settings section
        	add_settings_section(
        	    'wpau_stock_ticker-section', 
        	    __('Default Settings','wpaust'), 
        	    array(&$this, 'settings_section_wpau_stock_ticker'), 
        	    'wpau_stock_ticker'
        	);
        	
            // add your setting's fields
            add_settings_field(
                'wpau_stock_ticker-symbols', 
                __('Stock Symbols','wpaust'), 
                array(&$this, 'settings_field_input_text'), 
                'wpau_stock_ticker', 
                'wpau_stock_ticker-section',
                array(
                    'field' => 'st_symbols',
                    'description' => __('Enter stock symbols separated with comma','wpaust'),
                    'class' => 'widefat'
                )
            );
            add_settings_field(
                'wpau_stock_ticker-show', 
                __('Show Company as','wpaust'), 
                array(&$this, 'settings_field_select'), 
                'wpau_stock_ticker', 
                'wpau_stock_ticker-section',
                array(
                    'field' => 'st_show',
                    'description' => __('What to show as Company identifier by default','wpaust'),
                    'items' => array(
                        "name" => __("Company Name",'wpaust'),
                        "symbol" => __("Stock Symbol",'wpaust')
                    )
                )
            );
            // Color pickers
            add_settings_field( // unchanged
                'wpau_stock_ticker-quote_zero', 
                __('Unchanged Quote','wpaust'), 
                array(&$this, 'settings_field_colour_picker'), 
                'wpau_stock_ticker', 
                'wpau_stock_ticker-section',
                array(
                    'field' => 'st_quote_zero',
                    'description' => __('Set colour for unchanged quote','wpaust'),
                    'value' => get_option('st_quote_zero','#454545')
                )
            );
            add_settings_field( // minus
                'wpau_stock_ticker-quote_minus', 
                __('Netagive Change','wpaust'), 
                array(&$this, 'settings_field_colour_picker'), 
                'wpau_stock_ticker', 
                'wpau_stock_ticker-section',
                array(
                    'field' => 'st_quote_minus',
                    'description' => __('Set colour for negative change','wpaust'),
                    'value' => get_option('st_quote_minus','#D8442F')
                )
            );
            add_settings_field( // plus
                'wpau_stock_ticker-quote_plus', 
                __('Positive Change','wpaust'), 
                array(&$this, 'settings_field_colour_picker'), 
                'wpau_stock_ticker', 
                'wpau_stock_ticker-section',
                array(
                    'field' => 'st_quote_plus',
                    'description' => __('Set colour for positive change','wpaust'),
                    'value' => get_option('st_quote_plus','#009D59')
                )
            );

            // Possibly do additional admin_init tasks
        } // END public static function activate
        
        public function settings_section_wpau_stock_ticker()
        {
            // Think of this as help text for the section.
            echo __('Predefine default settings for Stock Ticker. Here you can set stock symbols and how you wish to present companies in ticker.','wpaust');
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // Get description
            $description = $args['description'];
            // Get field class (widefat)
            $class = $args['class'];
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" class="%s" /><p class="description">%s</p>', $field, $field, $value, $class, $description);
        } // END public function settings_field_input_text($args)

        /**
         * This function provides select for settings fields
         */
        public function settings_field_select($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // Get description
            $description = $args['description'];
            // Get select items
            $items = $args['items'];

            $html = sprintf('<select id="%s" name="%s">',$field,$field);
            foreach ($items as $key=>$val)
            {
                $selected = ($value==$key) ? 'selected="selected"' : '';
                $html .= sprintf('<option %s value="%s">%s</option>',$selected,$key,$val);
            }
            $html .= sprintf('</select><p class="description">%s</p>',$description);
            echo $html;
        } // END public function settings_field_input_text($args)

        public function settings_field_colour_picker($args)
        {
            extract( $args );
            $html = sprintf('<input type="text" name="%s" id="%s" value="%s" class="wpau-color-field" />',$field, $field, $value);
            $html .= (!empty($description)) ? ' <p class="description">'.$description.'</p>' : '';
            echo $html;
        }
        /**
         * add a menu
         */		
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
        	add_options_page(
        	    __('Stock Ticker Settings','wpaust'), 
        	    __('Stock Ticker','wpaust'), 
        	    'manage_options', 
        	    'wpau_stock_ticker', 
        	    array(&$this, 'plugin_settings_page')
        	);
        } // END public function add_menu()
    
        /**
         * Menu Callback
         */		
        public function plugin_settings_page()
        {
        	if(!current_user_can('manage_options'))
        	{
        		wp_die(__('You do not have sufficient permissions to access this page.'));
        	}
	
        	// Render the settings template
        	include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class WPAU_Stock_Ticker_Settings
} // END if(!class_exists('WPAU_Stock_Ticker_Settings'))
