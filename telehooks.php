<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('Telehooks')) {

    class Telehooks
    {
        private $options;
        
        public function __construct()
        {
            session_start();

            add_action('admin_menu', array(
                $this,
                'add_plugin_page_telehooks'
            ));
            add_action('admin_init', array(
                $this,
                'page_init_telehooks'
            ));   
        }

        public function add_plugin_page_telehooks()
        {
			//$statuses = wc_get_order_statuses();
			//echo "<pre>"; print_r($statuses); die;
			
			
            add_menu_page('Telehooks SMS', 'Telehooks SMS', 'manage_options', 'telehooks-setting-admin', array(
                $this,
                'create_admin_page'
            ), '', 20);
        }
        
        /**
         * Options page callback
         */
        public function create_admin_page()
        {
			//print_r($_SESSION); die;
            if (isset($_SESSION["errormsg"])) {
                $error = $_SESSION["errormsg"];
                echo $error;
                session_unset($_SESSION["errormsg"]);
            } else {
                $error = "";
            }
            
            if (isset($_SESSION["succesmsg"])) {
                $succesmsg = $_SESSION["succesmsg"];
                echo $succesmsg;
                session_unset($_SESSION["succesmsg"]);
            } else {
                $succesmsg = "";
            } 
			
			/* if (isset($_SESSION["errormsg_status"])) {
                $errormsg_status = $_SESSION["errormsg_status"];
                echo $errormsg_status;
                session_unset($_SESSION["errormsg_status"]);
            } else {
                $errormsg_status = "";
            }
            
			
			
            if (isset($_SESSION["succesmsg_status"])) {
                $succesmsg_status = $_SESSION["succesmsg_status"];
                echo $succesmsg_status;
                session_unset($_SESSION["succesmsg_status"]);
            } else {
                $succesmsg_status = "";
            } */
            
            // Set class property
            $this->options = get_option('my_option_name_telehooks');
?>
        <div class="wrap">
            <h2>Telehooks SMS</h2>    
            <form method="post" id="form1"  action="options.php">
            <?php
            // This prints out all hidden setting fields
            settings_fields('my_option_group');
            do_settings_sections('telehooks-setting-admin');
            submit_button();
?>			
            </form>
        </div>
        <?php
        }
        
        /**
         * Register and add settings
         */
        public function page_init_telehooks()
        {
            register_setting('my_option_group', // Option group
                'my_option_name_telehooks', // Option name
                array(
                $this,
                'sanitize_telehooks'
            ) // Sanitize
                );
            
             add_settings_section('setting_section_id', // ID
                'Connect to Telehooks', // Title
                array(
                $this,
                'print_section_info'
            ), // Callback
                'telehooks-setting-admin' // Page
                );
            
            add_settings_field('emailid', // ID
                'Username', // Title 
                array(
                $this,
                'emailid_callback'
            ), // Callback
                'telehooks-setting-admin', // Page
                'setting_section_id' // Section           
                );
            
            add_settings_field('license_key', 'License Key', array(
                $this,
                'license_key_callback'
            ), 'telehooks-setting-admin', 'setting_section_id');
			
			add_settings_field('consumer_key', 'Consumer Key', array(
                $this,
                'consumer_key_callback'
            ), 'telehooks-setting-admin', 'setting_section_id'); 
			
			add_settings_field('secret_key', 'Secret Key', array(
                $this,
                'secret_key_callback'
            ), 'telehooks-setting-admin', 'setting_section_id');
        }
        
        /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         */
        public function sanitize_telehooks($input)
        {
			$type = 'POST';
			$options  = '';
			$headers = array('Content-Type' =>'application/json');

            $new_input = array();
            
			if($_REQUEST['submit']) { 

                $data_string = array(
                    "username" 			=> $input['emailid'],
                    "license_key" 		=> $input['license_key'],
					"site_url" 			=> get_site_url(),
					"consumer_key" 		=> $input['consumer_key'],
					"secret_key" 		=> $input['secret_key'],
                );
                $data_string = json_encode($data_string);
				
				$response =  Requests::request( TELEHOOKS_REQUEST_URL, $headers, $data_string, $type, $options );
				
				$output = json_decode($response->body);

                if (isset($output->status) && strtolower($output->status) == 'success') {
                   
                    if (isset($input['emailid']))
                        $new_input['emailid'] = $input['emailid'];
                    
                    if (isset($input['license_key']))
                        $new_input['license_key'] = $input['license_key'];
					
                    if (isset($input['consumer_key']))
						$new_input['consumer_key'] = $input['consumer_key'];
				
					if (isset($input['secret_key']))
						$new_input['secret_key'] = $input['secret_key'];

                    
                    $_SESSION["succesmsg"] = '<div style="color: #fff;background: #11a739; text-align: center;  padding: 12px;font-size: 16px;margin-top: 10px;">Credentials Saved Successfully !</div>';
					 $this->addStatues_telehooks($input);
                    return $new_input;
                    
                } else {
                    
                    $a = get_option('my_option_name_telehooks');
                    
                    $username = $a['emailid'];
                    $license_key = $a['license_key'];
                    
                    if (isset($a['emailid']))
                        $new_input['emailid'] = $a['emailid'];
                    
                    if (isset($a['license_key']))
                        $new_input['license_key'] = $a['license_key'];
                    
					 if (isset($a['consumer_key']))
						$new_input['consumer_key'] = $a['consumer_key'];
				
					if (isset($a['secret_key']))
						$new_input['secret_key'] = $a['secret_key'];
                
                    
					
                    $_SESSION["errormsg"] = '<div style="color: #fff;background: #a72011;text-align: center; padding: 12px; font-size: 16px;margin-top: 10px;">Credentials Seems to be Wrong !</div>';
                                     
                    return $new_input;                  
                }            
            }     
        }
        
        /** 
         * Print the Section text
         */
        public function print_section_info()
        {
            print '<td colspan="2">
					<a href="http://telehooks.com/register" target="_blank" style="background-color: #2eade0;color: #ffffff;text-decoration: none;padding: 4px;border: thin solid #ababab;">Register here</a> 
					<span style="font-size:14px;">to get SMS Notifications.</span> </td>';
        }
        
        /** 
         * Get the settings option array and print one of its values
         */
        public function emailid_callback()
        //echo '<pre>'; print_r($_POST);die;
        {
            printf('<input type="text" id="emailid" name="my_option_name_telehooks[emailid]" value="%s"  required="required"/>', isset($this->options['emailid']) ? esc_attr($this->options['emailid']) : '');
    
        }
        
        /** 
         * Get the settings option array and print one of its values
         */
        public function license_key_callback()
        {
            printf('<input type="text" id="license_key" name="my_option_name_telehooks[license_key]" value="%s"  required="required"/>', isset($this->options['license_key']) ? esc_attr($this->options['license_key']) : '');
        } 

		/** 
         * Get the settings option array and print one of its values
         */
        public function consumer_key_callback()
        {
            printf('<input type="text" id="consumer_key" name="my_option_name_telehooks[consumer_key]" value="%s"  required="required"/>', isset($this->options['consumer_key']) ? esc_attr($this->options['consumer_key']) : '');
        }
		
		/** 
         * Get the settings option array and print one of its values
         */
        public function secret_key_callback()
        {
            printf('<input type="text" id="secret_key" name="my_option_name_telehooks[secret_key]" value="%s"  required="required"/>', isset($this->options['secret_key']) ? esc_attr($this->options['secret_key']) : '');
        }

  /**
         * Sanitize each setting field as needed
         *
         * @param array $input Contains all settings fields as array keys
         */
        public function addStatues_telehooks($request)
        {
          
			$new_input = array();
			$statuses = wc_get_order_statuses();

			$data_string = array(
				"username" => $request['emailid'],
				"license_key" => $request['license_key'],
				"app_code" => 'woocommerce'			
			);
			foreach($statuses as $key => $value){
				$data_string['statuses'][] = array('status_code' => $key,'status_name' => $value , 'topic' => 'order.updated' );
			}
			$type = 'POST';
			$options  = '';
			$headers = array('Content-Type' =>'application/json');
			$data_string = json_encode($data_string);
			$response =  Requests::request( TELEHOOKS_REQUEST_URL_ADDSTATUS, $headers, $data_string, $type, $options );
			$output = json_decode($response->body);
		 
			if ( isset($output->success) && strtolower($output->success) == '1') {
				$_SESSION["succesmsg"] =  $_SESSION["succesmsg"].'<div style="color: #fff;background: #11a739; text-align: center;  padding: 12px;font-size: 16px;margin-top: 10px;">Woocommerce Order Statues are Synched with Telehooks !</div>';
			} else {
				$_SESSION["errormsg"] =  $_SESSION["errormsg"] .'<div style="color: #fff;background: #a72011;text-align: center; padding: 12px; font-size: 16px;margin-top: 10px;">Woocommerce Order Statues are Not Synched with Telehooks !</div>';                
			}                 
        }	
    }
}