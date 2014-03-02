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
            // get default values
            $defaults = WPAU_STOCK_TICKER::defaults();

        	// register your plugin's settings
            // TODO: validate and sanitize inputs for symbols, error_message and cache_timeout
            register_setting('default_settings', "stock_ticker_defaults");
            register_setting('advanced_settings', "stock_ticker_defaults");

            // add general settings section
            add_settings_section(
                'default_settings', 
                __('Default Settings','wpaust'), 
                array(&$this, 'settings_default_section_description'), 
                'wpau_stock_ticker'
            );
        	
            // add setting's fields
            add_settings_field(
                'wpau_stock_ticker-symbols', 
                __('Stock Symbols','wpaust'), 
                array(&$this, 'settings_field_input_text'), 
                'wpau_stock_ticker', 
                'default_settings',
                array(
                    'field'       => "stock_ticker_defaults[symbols]",
                    'description' => __('Enter stock symbols separated with comma','wpaust'),
                    'class'       => 'widefat',
                    'value'       => $defaults['symbols'],
                )
            );
            add_settings_field(
                'wpau_stock_ticker-show', 
                __('Show Company as','wpaust'), 
                array(&$this, 'settings_field_select'), 
                'wpau_stock_ticker', 
                'default_settings',
                array(
                    'field'       => "stock_ticker_defaults[show]",
                    'description' => __('What to show as Company identifier by default','wpaust'),
                    'items'       => array(
                        "name"   => __("Company Name",'wpaust'),
                        "symbol" => __("Stock Symbol",'wpaust')
                    ),
                    'value' => $defaults['show'],
                )
            );
            // Color pickers
            add_settings_field( // unchanged
                'wpau_stock_ticker-quote_zero', 
                __('Unchanged Quote','wpaust'), 
                array(&$this, 'settings_field_colour_picker'), 
                'wpau_stock_ticker', 
                'default_settings',
                array(
                    'field'       => "stock_ticker_defaults[zero]",
                    'description' => __('Set colour for unchanged quote','wpaust'),
                    'value'       => $defaults['zero'],
                )
            );
            add_settings_field( // minus
                'wpau_stock_ticker-quote_minus', 
                __('Netagive Change','wpaust'), 
                array(&$this, 'settings_field_colour_picker'), 
                'wpau_stock_ticker', 
                'default_settings',
                array(
                    'field'       => "stock_ticker_defaults[minus]",
                    'description' => __('Set colour for negative change','wpaust'),
                    'value'       => $defaults['minus'],
                )
            );
            add_settings_field( // plus
                'wpau_stock_ticker-quote_plus', 
                __('Positive Change','wpaust'), 
                array(&$this, 'settings_field_colour_picker'), 
                'wpau_stock_ticker', 
                'default_settings',
                array(
                    'field'       => "stock_ticker_defaults[plus]",
                    'description' => __('Set colour for positive change','wpaust'),
                    'value'       => $defaults['plus'],
                )
            );

            // add advanced settings section
            add_settings_section(
                'advanced_settings', 
                __('Advanced Settings','wpaust'), 
                array(&$this, 'settings_advanced_section_description'), 
                'wpau_stock_ticker'
            );
            // caching timeout field
            add_settings_field(
                'wpau_stock_ticker-cache_timeout', 
                __('Cache Timeout','wpaust'), 
                array(&$this, 'settings_field_input_text'), 
                'wpau_stock_ticker', 
                'advanced_settings',
                array(
                    'field'       => "stock_ticker_defaults[cache_timeout]",
                    'description' => __('Define cache timeout for single quote set, in seconds','wpaust'),
                    'class'       => 'num',
                    'value'       => $defaults['cache_timeout'],
                )
            );
            // default error message
            add_settings_field(
                'wpau_stock_ticker-error_message', 
                __('Error Message','wpaust'), 
                array(&$this, 'settings_field_input_text'), 
                'wpau_stock_ticker', 
                'advanced_settings',
                array(
                    'field'       => "stock_ticker_defaults[error_message]",
                    'description' => __('When Stock Ticker fail to grab quote set from Yahoo Finance, display this mesage in ticker','wpaust'),
                    'class'       => 'widefat',
                    'value'       => $defaults['error_message'],
                )
            );
            // Possibly do additional admin_init tasks
        } // END public static function admin_init()
        
        public function settings_default_section_description()
        {
            // Think of this as help text for the section.
            echo __('Predefine default settings for Stock Ticker. Here you can set stock symbols and how you wish to present companies in ticker.','wpaust');
        }
        public function settings_advanced_section_description()
        {
            // Think of this as help text for the section.
            echo __('Set advanced options important for caching quote feeds.','wpaust');
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // TODO: optimize with extract()
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = $args['value']; //get_option($field);
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
            // TODO: optimize with extract()
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = $args['value']; //get_option($field);
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
        	include(sprintf("%s/../templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class WPAU_Stock_Ticker_Settings
} // END if(!class_exists('WPAU_Stock_Ticker_Settings'))
