<?php
/*
 Plugin Name: Notify On Action
 Plugin URI: 
 Description: This plugin allows Admins to set up email notifications to be sent on action events in any of functions on themes/plugins. 
 Author: Luciana Bruscino
 Version: 1.0.0
 Author URI: 
 Copyright 2011 Luciana_Bruscino (email : luciana.bruscino@gmail.com)

 */

define('NOA_PLUGIN_VERSION', '1.0.0');
if (!class_exists('NotifyOnActionPlugin', false)) {
	class NotifyOnActionPlugin {
		
		const OPTIONNAME 			= 'noa_options'; 
		public $pluginDir;
		public $pluginPath;
		public $pluginUrl;
		public $pluginDomain 		= 'notify-on-action';
		
		/**
		 * NotifyMeOnActionPlugin plugin constructor
		 *
		 * @return null
		 */
		function __construct()
		{
			$this->pluginDir		= trailingslashit( basename( dirname(__FILE__) ) );
			$this->pluginPath		= trailingslashit( dirname(__FILE__) );
			$this->pluginUrl 		= WP_PLUGIN_URL.'/'.$this->pluginDir;
			
			register_activation_hook( __FILE__, array(&$this, 'activate' ));			
			register_deactivation_hook( __FILE__, array(&$this, 'deactivate' ));
			register_uninstall_hook(__FILE__,  array( &$this, 'uninstall' ));

			
			$this->add_actions();			
		}
	
		/**
		 * This function initializes NotifyMeOnActionPlugin plugin action and filter hooks
		 *
		 * @return null
		 */
		private function add_actions() {
			add_action( 'init', array( $this, 'initialize'), 1 );
			add_filter('plugin_action_links', array($this, "show_settings_link"), 10, 2);
			add_filter('admin_footer_text', array($this, "add_admin_footer"));
			add_action( 'noa_notify_on_action', array( $this, 'notify'), 10, 2 );			
		
			if ( is_admin() ){
				add_action('admin_menu', array($this, 'add_admin_page'),1);														
				add_action('wp_ajax_noa-action-delete-template', array($this, "delete_email_template"));
				add_action('wp_ajax_noa-action-edit-template', array($this, "edit_email_template"));	
				add_action( 'contextual_help', array($this, "add_contextual_help"), 10, 2 );	
			}			
		}							
		
		/**
		 * This function initializes NotifyMeOnActionPlugin plugin 
		 *
		 * @return null
		 */
		public function initialize() {
				$this->pluginName = __( 'Notify On Action', $this->pluginDomain );	
				$this->my_scripts_method();								
		}
				
		/**
		 * This function add a sub-level menu called Notify Me on Action under the Settings top-level menu in wp-admin
		 *
		 * @return null
		 */
		public function add_admin_page() {
			add_options_page('Notify On Action Options', "Notify On Action" ,'manage_options', 'notify-me-on-action-options-page',  array($this, "display_admin_page"));				
		}
		
		/**
		 * This function customizes the admin footer
		 *
		 * @return null
		 */
		function add_admin_footer() {
			echo 'Plugin Design by <a href="http://twitter.com/luciana123_2002/" title="">Luciana Bruscino </a> - ' . $this->pluginDomain;
		} 


		/**
		 * show_settings_link
		 *
		 * This function creates an Setting link in the Plugin Page.
		 *
		 * @param    string   $links
		 * @param    string   $file
		 * @return   string	  A link to the setting admin page
		 */
		public function show_settings_link($links, $file) {
			$plugin_file = basename(__FILE__);		
			if (strtolower(basename($file)) == strtolower($this->pluginDomain.'.php')) {
				$settings_link = '<a href="options-general.php?page=notify-me-on-action-options-page">'.__('Settings', $this->pluginDomain).'</a>';
				array_unshift($links, $settings_link);
			}
			return $links;
		}
		
		/**
		 * add_contextual_help
		 *
		 * This function adds plugin contextual text to the Help menu
		 *
		 * @param    string   $text
		 * @param    string   $screen
		 * @return   string	  contextual link text
		 */
		function add_contextual_help($text, $screen) {
			// Check we're only on my Settings page
			
			if (strcmp($screen, 'settings_page_notify-me-on-action-options-page') == 0 ) {
			 
						$text = __('<p> Ways to use this plugin in:
						<ul>
						<li> <h3>Send Standard Notification</h3>
						- Create a new Notification Template in the form below (i.e Notification Name is "SaluteMyFriend") <br>
						- Add this code in the Theme/Plugin function when you want the notification email to be submitted:
						<pre> if (has_action("noa_notify_on_action")){						
						 do_action( "noa_notify_on_action","SaluteMyFriend", $args);
						}</pre>
						Once the do_action executes, an email notification will be send with the information on the "SaluteMyFriend" Notification Type
						</li>
						<li> <h3>Send Notification with Application Specific Data</h3>
						You can also submit customized text to be sent in your email. For example, lets say you would like your notification email to include application specific information which varies depending on where the user clicks, then you can pass that data into the do_action function.			 			
						- Create a new Notification Template in the form below (i.e Notification Name is "SaluteMyFriend") <br>
						- Wherever you want application specific data to be in the email, create a %xxxx% tag. (i.e In Message text box include %arg1%, %arg2%)<br> 
						<pre>
			 			if (has_action("noa_notify_on_action")){
						 $args = array("arg1" => "Good Morning",
							  	"arg2"=> "Joe Doh"					
								);
						
						 do_action( "noa_notify_on_action","SaluteMyFriend", $args);
						}</pre>
			 						<br> 		
			 			In the Notification Template message add the tags %arg1%, %arg2% to substitute the values sent. <br>
			 			The name of these arguments need to match between the Notification Template and the argument sent by the application. For example, if you write in the message of your Notification type
			 			"The price of %product% is %price%", then use the code below to submit the notification email with the product and price custom information:
			 			<pre>
			 			if (has_action("noa_notify_on_action")){
						 $args = array("product" => "Computer",
							  	"price"=> "$850.99"					
								);
						
						 do_action( "noa_notify_on_action","ProductConfirmation", $args);
						}</pre>
			 			<br>
			 			<b>You can use these %xxxx% tags only in the Message field </b>.
			 			</li>
			 			<li> <h3>Send Notification to Application Specific Recipients</h3>
			 			If the recipient needs to be specified by the application, use the keyword "recipients" in the arguments array. You can send it to multiple recipients.
			 			<pre>
			 			if (has_action("noa_notify_on_action")){
								 $args = array(
								 "recipients" => array("john@gmail.com","giulia@yahoo.com"),
								 "product" => "Computer",
							  	 "price"=> "$850.99"					
								);
						
						 do_action( "noa_notify_on_action","ProductConfirmation", $args);
						}
			 			</pre>
			 			<br>
			 			This will send a notification email to John and Giulia\'s emails.			 			 
			 			</li>			 			
			 			</ul></p>');
						return $text;
					}
			// Let the default WP Dashboard help stuff through on other Admin pages
			return $text; 	 
		}

		/**
		 * saveOptions
		 *
		 * This function saves data into the options table
		 *
		 * @param    array   $opts
		 * @return   null
		 */
		public function saveOptions($opts) {
         	   if (!is_array($opts)) {
             	  	 return;
           	 	}        
			
           	  $options = $this->getOptions();
			  if (array_key_exists($opts['noa_opt_name'], $options)) {
   				 echo $opts['noa_opt_name'] . " notification template has been updated/already exists.";
			  }
			  
           	  $options[$opts['noa_opt_name']] = $opts;          	   
          	
         	  update_option(self::OPTIONNAME, $options);
       	}
        
       	/**
		 * getOptions
		 *		
		 * This function saves data into the options table
		 * @return   null
		 */	 
		public function getOptions() {        
	            return get_option(self::OPTIONNAME, array());
        	}
        
        /**
		 * getOption
		 *
		 * @param    string   $optionName
		 * @param    string   $default 
		 * 
		 * This function returns the option value requested. It also sets the default value if sent
		 * @return   string option saved
		 */	 
		public function getOption($optionName, $default = '') {
			if( ! $optionName )
				return null;			
	
			$options = $this->getOptions();
			$optionName = "noa_opt_".$optionName;
			return ( isset($options[$optionName]) ) ? $options[$optionName] : $default;
		
		}

		/**
		 * donate
		 *
		 * This function returns the pay pal donate button code
		 * @return  null
		 */	 
		function donate (){
			return '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----MIIHTwYJKoZIhvcNAQcEoIIHQDCCBzwCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYAuniVUeagS7ealbO6g3vD3f9rb9TY9Pvo017Zb7g/aydpZSW6BJ8YVe8UeeTAKVoYIaBimywaw5ousvOrcd3v+hQag8yZPlpyNLKP3Vk7qh/NWCa8IJlS+7BSTMDgzJnFg0W9BFc27xXVepeBeXrYlpTtYxYMVqqDQgk9HygLJeDELMAkGBSsOAwIaBQAwgcwGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIOXt7G/UDhZOAgahzP/M+RRJ9NVj+jtBJup7iO/B919WpvRIaSXcRMa89/3dftGvGMd3Q4zHZhhllFGFPYmhpp1UyG1op3wwMmFWNVvSH7K1pTcB0wqxTZVICBjQZw8MsTbJxijeD8SjV+Q4zqAYzhLy0iRRl/usnaW5HAMbPC8A45svruTJYWV6U/Ft+UHxTsVSbbEws/u3CT40pK0H6tLOgeHcoxNhJpYQMImKYXcTifGigggOHMIIDgzCCAuygAwIBAgIBADANBgkqhkiG9w0BAQUFADCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wHhcNMDQwMjEzMTAxMzE1WhcNMzUwMjEzMTAxMzE1WjCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJAoGBAMFHTt38RMxLXJyO2SmS+Ndl72T7oKJ4u4uw+6awntALWh03PewmIJuzbALScsTS4sZoS1fKciBGoh11gIfHzylvkdNe/hJl66/RGqrj5rFb08sAABNTzDTiqqNpJeBsYs/c2aiGozptX2RlnBktH+SUNpAajW724Nv2Wvhif6sFAgMBAAGjge4wgeswHQYDVR0OBBYEFJaffLvGbxe9WT9S1wob7BDWZJRrMIG7BgNVHSMEgbMwgbCAFJaffLvGbxe9WT9S1wob7BDWZJRroYGUpIGRMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbYIBADAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4GBAIFfOlaagFrl71+jq6OKidbWFSE+Q4FqROvdgIONth+8kSK//Y/4ihuE4Ymvzn5ceE3S/iBSQQMjyvb+s2TWbQYDwcp129OPIbD9epdr4tJOUNiSojw7BHwYRiPh58S1xGlFgHFXwrEBb3dgNbMUa+u4qectsMAXpVHnD9wIyfmHMYIBmjCCAZYCAQEwgZQwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tAgEAMAkGBSsOAwIaBQCgXTAYBgkqhkiG9w0BCQMxCwYJKoZIhvcNAQcBMBwGCSqGSIb3DQEJBTEPFw0xMTA5MDkyMzE5MTJaMCMGCSqGSIb3DQEJBDEWBBREWH2BwryIHEi60jH+Fz0hxHdA5zANBgkqhkiG9w0BAQEFAASBgMEYMcjojdzwMNRElOjYRbbVl6DRd+/mBGyf/khXUYsHIZbixL1NXWWaqsanp3642l3WbzCQ/1CebA1K72nuxWlCwkH7EAYnqqrcTwuX4PqrctorPo7Co1rcq8AR+Cg9pUF5wqY7ixuZnEWKKoUuaaa4rz/0mQKMsv/YBlopZz8r-----END PKCS7-----
">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>';
			
			
		}
		/**
		 * This function displays and processes the Admin page
		 *
		 * @return null
		 */
		public function display_admin_page(){
	
			if (!current_user_can('manage_options'))  {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			$optionArray = array();
			if(isset($_POST['noa_post_action_submit'])){
				
				$optionArray['noa_opt_name']= $_POST[ 'noa_opt_name' ];
				$optionArray['noa_opt_type']= $_POST[ 'noa_opt_type' ];
				$optionArray['noa_opt_from']= $_POST[ 'noa_opt_from' ];
				
				$recipient = '';
				if($_POST[ 'noa_opt_recipients' ]=='admin')
					$recipient = get_bloginfo('admin_email');
				if($_POST[ 'noa_opt_recipients' ]=='email')
					$recipient = preg_split("/[\r\n,]+/", trim($_POST[ 'noa_opt_recipient_list' ]), -1,PREG_SPLIT_NO_EMPTY);
					
				$optionArray['noa_opt_recipients']= $recipient;				
				$optionArray['noa_opt_subject']= $_POST[ 'noa_opt_subject' ];
				$optionArray['noa_opt_body']= $_POST[ 'noa_opt_body' ];
											
				$this->saveOptions($optionArray);				
			}
				
		?>	
		<script type="text/javascript">
		jQuery(document).ready(function() {	
			jQuery(".noa_css_edit").click(function(){			 
			 jQuery.get("<?php echo get_option('siteurl').'/wp-admin/admin-ajax.php' ?>", 
					    {action:"noa-action-edit-template",
				    	 attr: jQuery(this).attr("name"), 
				    	 _ajax_nonce: "<?php echo wp_create_nonce( 'noa-action-edit-template_content' ) ?>"},
				    	 function(data)	{			
							//reset
					    	jQuery('#noa_add_new_form').get(0).reset();	
				    		jQuery("#noa_recipient_list_display").hide();
				    		//set						    		
				    		jQuery("#noa_opt_name").val(data.noa_opt_name);
				    		jQuery("#noa_opt_from").val(data.noa_opt_from);
				    		jQuery("#noa_opt_type").val(data.noa_opt_type);
				    		jQuery("#noa_opt_subject").val(data.noa_opt_subject);
				    		jQuery("#noa_opt_body").val(data.noa_opt_body);
				    		
				    		if (data.noa_opt_recipients==""){					    		
				    			jQuery("#noa_opt_recipients").val("app");				    		
				    		}else {			
				    	 		
					    		jQuery("#noa_opt_recipients").val("email");				    		
			                   	jQuery("#noa_recipient_list_display").show();				    		
					    		jQuery("#noa_opt_recipient_list").val(data.noa_opt_recipients);
				    	 	}				    						    		
				    		 				    				    
					},"json");
			 });
		 
		 jQuery(".noa_css_delete").click(function(){					
			 jQuery.get("<?php echo get_option('siteurl').'/wp-admin/admin-ajax.php' ?>", 
					    {action:"noa-action-delete-template",
				    	 attr: jQuery(this).attr("name"), 
				    	 _ajax_nonce: "<?php echo wp_create_nonce( 'noa-action-delete-template_content' ) ?>"},
				    	 function(data)	{				    		 
							});					
			 jQuery(this).closest('tr').fadeOut("slow");
			 jQuery('form').clearForm();			
			    
			 });
		 });
		</script>
			<div class="wrap">
			<div id="icon-users" class="icon32"></div>
			<h2>
			<?php  echo _e( 'Notify On Action Settings')  ?>
			</h2>
			<table name="noa_css_table" class="options" border="0">
			<tr>
			<td colspan="2">
			<div style="float: right; position: relative; valign:top; top:-2px;"><?php echo $this->donate();?></div>
			This plugin allows you to sent email notifications whenever an action is performed on your site. Simply follow the steps below: 
			<ul>
			<ol>* Create Notification Template using the form below</ol>
			<ol>* Copy & Paste the following code in the theme/plugin code where you want the email notification to be sent.<br>
			<pre>if (has_action("noa_notify_on_action")) do_action( 'noa_notify_on_action','&lt;Enter_Notification_Name&gt;', null);
			</pre>
			</ol>
			</ul>
			You can customize the Notification Template. See Help (top right corner) for more options on how to use the plugin
			</td>			
			</tr>
			<tr>
			<td valign="top" >
			<?php
					$this->add_new_notification();
			?></td>
			<td valign="top"><?php 
					$this->show_notifications();
			?></td></tr></table>
			</div>
			<?php 		
		}
		
		/**
		 * This function deletes the notification type template selected on the AJAX call
		 *
		 * @return null
		 */
		function delete_email_template(){		
			 
			//Verifies the AJAX request to prevent processing requests external of the blog.
			check_ajax_referer( "noa-action-delete-template_content" );
						
			 $type = $_REQUEST[ 'attr' ];			
			 $options = $this->getOptions();
			 unset($options[$type]);
			 delete_option(self::OPTIONNAME);	
			 add_option(self::OPTIONNAME, $options);				 			
			die();
			
		}
		
		
		/**
		 * This function will return the recipient email(s)
		 *
		 * @return null
		 */
		function get_recipients($recipients){		
			if(empty($recipients)){
				return "";
			}	
			
			if(is_array($recipients)){
				return implode(",", $recipients);
			}

			return (string) $recipients;
		}
		
		/**
		 * This function edits the notification type template selected on the AJAX call
		 *
		 * @return null
		 */
		function edit_email_template(){		
									
			//Verifies the AJAX request to prevent processing requests external of the blog.
			check_ajax_referer( "noa-action-edit-template_content" );
						
			 $type = $_REQUEST[ 'attr' ];			 
			 $options = $this->getOptions();
			 $option = $options[$type];	
		
			 echo json_encode($option);
			
			die();
			
		}
				
		/**
		 * This function displays table of saved Notification Types
		 *
		 * @return null
		 */
		public function show_notifications(){
		
			?>	
			<table name="noa_templates_table" class="options">	
			<tr>
			<td colspan="2"><span class="noa_css_admin_header"><h3><?php  echo _e( 'Notification Templates') ?></h3> </span></td>
			</tr>	
			</table>		
			<table class="widefat">
				<thead>
				<tr>
					<th><?php  echo _e( 'Name') ?> </th>
					<th><?php  echo _e( 'Sent To') ?> </th>
					<th><?php  echo _e( 'From') ?> </th>
					<th><?php  echo _e( 'Subject') ?> </th>					
					<th><?php  echo _e( 'Type') ?> </th>					
				</tr>			
				</thead>
				<tbody>
				<?php
				$types = $this->getOptions();
						
				//print_r($types);
				foreach ($types as &$type)
				{
					?>
				<tr class="noa_css_table_row">
					<td class="noa_css_table_column"><?php echo $type['noa_opt_name'];?>
					<div class="row-actions">
						<span class="edit"><a title="Edit this template" class="noa_css_edit" name="<?php echo $type['noa_opt_name'];?>">Edit</a>  </span>
						<?php if($type['noa_opt_name'] != 'Default') {?>
						| <span class="trash"><a title="Delete this template" class="noa_css_delete" name="<?php echo $type['noa_opt_name'];?>">Delete</a>  </span>
						<?php }?>
					</div>
					</td>	
					<td class="noa_css_table_column"><?php $rec = $this->get_recipients($type['noa_opt_recipients']); echo empty($rec)?"app provided":$rec;?></td>	
					<td class="noa_css_table_column"><?php echo $type['noa_opt_from'];?></td>	
					<td class="noa_css_table_column"><?php echo $type['noa_opt_subject'];?></td>
					<td class="noa_css_table_column"><?php echo $type['noa_opt_type'];?></td>		
							
				</tr>
				
				<?php
				}
				?>
				</tr>
				</tbody>
			</table>
			<?php
		}
		
		public function add_new_notification(){	
			
		?>
		<style>
		.noa_css_input {
			width: 290px;
		}
		</style>
		<form class="wrap" id="noa_add_new_form" name="noa_add_new_form" method="post" action="">
		<table name="noa_add_new_table" class="options">	
			<tr>
			<td colspan="2"><span class="noa_css_admin_header"><h3><?php  echo _e( 'Create Notification Template') ?></h3> </span></td>
			</tr>		
			<tr class="noa_css_table_row">
				<td class="noa_css_table_label"><?php  echo _e("Notificaton Name:")?>
				</td>
				<td class="noa_css_table_input">
					<input 				
					value=""
					name="noa_opt_name"
					id="noa_opt_name"
					class="noa_css_input">
					<br>
						<span style="font-size: smaller"><?php  echo _e('(the notification name must be unit.)') ?> </span>	
				</td>
			</tr>
			<tr class="noa_css_table_row">
				<td class="noa_css_table_label"><?php  echo _e(" Recipient:")?>
				</td>
				<td class="noa_css_table_input">					
					<select id="noa_opt_recipients" name="noa_opt_recipients">
						<option value="admin">Admin</option>										
						<option value="email">Email List</option>
						<option value="app">Application Provides</option>
					</select>
					 <br>					
					<div id="noa_recipient_list_display">
						<input class="noa_css_input" id="noa_opt_recipient_list" name="noa_opt_recipient_list" />
						<br>
						<span style="font-size: smaller"><?php  echo _e('(enter email addresses separated by comma)') ?> </span>					
					</div>
				</td>
			</tr>
			<tr class="noa_css_table_row">
				<td class="noa_css_table_label"><?php  echo _e(" From:")?>
				</td>
				<td class="noa_css_table_input">
					<input class="noa_css_input" id="noa_opt_from" name="noa_opt_from" value="<?php echo get_bloginfo('name').' <'. get_bloginfo('admin_email').'>'?>" />							
				</td>
			</tr>
				<td class="noa_css_table_label"><?php  echo _e(" Email Type:")?>
				</td>
				<td class="noa_css_table_input">					
					<select name="noa_opt_type" id="noa_opt_type">
						<option value="plaintext">Plaintext</option>
						<option value="html">HTML</option>						
					</select>
					
				</td>
			</tr>
			<tr class="noa_css_table_row">
				<td class="noa_css_table_label"><?php  echo _e(" Subject:")?>
				</td>
				<td class="noa_css_table_input">
					<input 				
					value=""
					name="noa_opt_subject"
					id="noa_opt_subject"
					class="noa_css_input">
				</td>
			</tr>
			<tr class="noa_css_table_row">
				<td class="noa_css_table_label"><?php  echo _e(" Message:")?>
				</td>
				<td class="noa_css_table_input">
					<textarea name="noa_opt_body" id="noa_opt_body" cols="40" rows="12"></textarea>
					<br>
					<span style="font-size: smaller"><?php  echo _e('(you can customize the text shown on the message. See Help for details.)') ?> </span>	

				</td>
			</tr>						
			<tr>
				<td>			
				<input id="noa_post_action_submit" class="button-primary" type="submit" name="noa_post_action_submit" value="Save Notification">
				</td>
				<td>			
				<input id="noa_post_action_clear" class="button-primary" type="button" name="noa_post_action_clear" value="Clear Notification">
				</td>
			</tr>
		</table>
		</form>
		<?php 
		}
		
		/**
		 * This function loads plugin assets
		 *
		 * @return null
		 */
		function my_scripts_method() {
    		wp_enqueue_script('jquery');  
    		wp_register_script( 'noa_script_file', plugins_url('/js/notify-me-on-action.js', __FILE__),  array('jquery'));
			wp_enqueue_script('noa_script_file');          
		}    
 
		
		/**
		 * This function prepares data to call send email
		 *
		 * @return null
		 */
		function notify($type, $args= array()){
			
			//Get the type of notification saved in the options 
			$options = $this->getOptions();
			if(empty($options)){
				return false;
			}
					
			//if recipient list is set to app - get it from application			
			$option = $options[$type];
			if(empty($option)){
				$option = $options['Default'];					
			}
			
			$sender = $option['noa_opt_from'];
			$recipients = $this->get_recipients($option['noa_opt_recipients']);
			$subject = $option['noa_opt_subject'];
			$type = $option['noa_opt_type'];
			$body = $option['noa_opt_body'];
					
			$this->send_mail($recipients, $sender,  $subject,  $body,  $type, $args);
		}
	
		/**
		 * This function sends email
		 *
		 * Returns number of recipients addressed in emails or false on internal error.
		 */
		function send_mail($recipients, $sender, $subject = '', $message = '', $type = 'plaintext', $args) {
			
			
	
			$headers  = "From: \"$sender_name\" <$sender_email>\n";
			$headers .= "Return-Path: <" . $sender_email . ">\n";
			$headers .= "Reply-To: \"" . $sender_name . "\" <" . $sender_email . ">\n";
			$headers .= "X-Mailer: PHP" . phpversion() . "\n";
		
			$subject = stripslashes($subject);
			$message = stripslashes($message);					

			if ('html' == $type) {
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: " . get_bloginfo('html_type') . "; charset=\"". get_bloginfo('charset') . "\"\n";
				$body = "<html><head><title>" . $subject . "</title></head><body>" . $message . "</body></html>";
			} else {
				$headers .= "MIME-Version: 1.0\n";
				$headers .= "Content-Type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
				$message = preg_replace('|&[^a][^m][^p].{0,3};|', '', $message);
				$message = preg_replace('|&amp;|', '&', $message);
				$body = wordwrap(strip_tags($message), 80, "\n");					
			}

			if(!empty($args)){
				$b= '';
				foreach($args as $key => $value){
					if($key=="recipients"){
						$recipients = $value;
					}else{
						$b = str_replace("%".$key."%", $value , $body);
						$body  = $b;
					}
				}							
			}
			
			if ( (empty($recipients)) ) { return false; }
				
			//$headers .= "To: " . implode(',', $recipients) . "\n\n";
			$headers .= "To: " . $recipients . "\n\n";
			$headers .= "From: " . $sender . "\n\n";		
			if(@wp_mail($recipients, $subject, $body, $headers)){
			
				return true;
			}						
			
			return false;
		}
				
		
		/**
		 * activate
		 *
		 * This function creates settings options (settings) entries in wp_options and mydomainlist table in mysql
		 * return null
		 */
		public function activate() {					
			$options = array(
       		         'Default' => array(
       		         			'noa_opt_type'=>'plaintext',
								'noa_opt_name'=>'Default',
								'noa_opt_from' => get_bloginfo('admin_email'),
								'noa_opt_recipients' => get_bloginfo('admin_email'),
								'noa_opt_subject' => 'Wordpress notification email',
								'noa_opt_body' =>'Hi, this is the default email notification message.'
							)			 		
				 );
	
			 add_option(self::OPTIONNAME, $options);		     		     						     
		}

		/**
		 * deactivate
		 *
		 * This function cleans up the entries (settings) created in the wp_options table in mysql.
		 *
		 */
		public function deactivate() {
					delete_option(self::OPTIONNAME);
		}

		/**
		 * uninstall
		 *
		 * This function drops the mydomainlist table in mysql. Domains will no longer be available
		 *
		 */
		public function uninstall(){
				
		}
		
		

		
			
		
	}//end class
}
global $noa;
$noa = new NotifyOnActionPlugin;

?>