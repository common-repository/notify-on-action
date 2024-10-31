=== Plugin Name ===
Plugin Name: Notify On Action 
Author: Luciana Bruscino
Description: This plugin allows Admins to set up email notification on any action performed in any of functions on themes/plugins. For example, let's you want to be notified when a user clicks a particular button on your site, or you want to notify another person when you process a request, etc This plugin can help you.
Version: 1.0.0
Copyright 2011 Luciana_Bruscino (email : luciana.bruscino@gmail.com)
Contributors: luciana123
Donate link: Paypal/luciana123_2002
Tags: notification, email, alerts, events
Requires at least: 3.1
Tested up to: 3.2.1
Stable tag: trunk

This plugin allows Admins to set up email notification on any action performed in any of functions on themes/plugins. 

 == Installation ==

Installation of the plugin is simple, please find us on worpress.org
Upload notify-on-action.php to the /wp-content/plugins/ directory
Activate the plugin
Click on the 'Settings' link on the Plugin page
Start using the plugin

== Description ==

This plugin allows you to sent email notifications whenever an action is performed on your site. For example, let's you want to be notified when a user clicks a particular button on your site, or you want to notify another person when you process a request, etc This plugin can help you.
 
Simply follow the steps below:

        * Create Notification Template using the form below
        * Copy & Paste the following code in the theme/plugin code where you want the email notification to be sent.

        if (has_action("noa_notify_on_action")) do_action( 'noa_notify_on_action','<Enter_Notification_Name>', null);
        		
There are other options on how to use the plugin. See 'How to Use' below

This plugin is available under the GPL license, which means that it's free. If you use it for a commercial web site, if you appreciate my efforts or if you want to encourage me to develop and maintain it, please consider making a donation using Paypal, a secured payment solution. You just need to click the donate button on the settings page and follow the instructions.

= What about Notify On Action? =

== How to Use it ==
Ways to use this plugin:
	* Send Standard Notification
	 - Create a new Notification Type in the form below (i.e Notification Name is "SaluteMyFriend") 
	- Add this code in the Theme/Plugin function when you want the notification to be submitted:
   <pre> if (has_action("noa_notify_on_action")){						
	do_action( "noa_notify_on_action","SaluteMyFriend", $args);
	}
   </pre>
   Once the do_action executes, an email notification will be send with the information on the "SaluteMyFriend" Notification Type
	
        * Send Notification with Application Specific Data
	  You can also submit arguments to be sent in your email. For example, lets say you would like your email template to include application specific information which varies depending on where the user clicks, then you can pass that argument into the do_action function.			 			
	 - Create a new Notification Type in the form below (i.e Notification Name is "SaluteMyFriend")
	- Wherever you want application specific data to be in the email, create a %xxxx% tag. (i.e In Message text box include %arg1%, %arg2%)
	<pre>
	 if (has_action("noa_notify_on_action")){
		$args = array("arg1" => "Good Morning",
			  "arg2"=> "Joe Doh"					
		);
						
		do_action( "noa_notify_on_action","SaluteMyFriend", $args);
	}
       </pre>
	
	In the Notification Type you the tags %arg1%, %arg2% to substitute the values sent. The name of the tag (i.e arg needs to match in the template and in the $args variable.
	
	The name of these arguments need to match between the Notification Type template and the argument sent by the application. For example, if you write in the message of your Notification type "The price of %product% is %price%", then your code will look like this:
	<pre>
	if (has_action("noa_notify_on_action")){
	 	$args = array("product" => "Computer",
		  	"price"=> "$850.99"								);
						
		do_action( "noa_notify_on_action","ProductConfirmation", $args);
	}
	</pre>
			 			

	You can use these %args% only in the Body of the message.
	
	* Send Notification to Application Specific Recipients</h3>
	
	If the recipient needs to be specified by the application, use the keyword "recipients" in the arguments array. 
		if (has_action("noa_notify_on_action")){
			 $args = array(										 "recipients" => array("john@gmail.com","giulia@yahoo.com"),
								 "product" => "Computer",
							  	 "price"=> "$850.99"					
								);
						
						 do_action( "noa_notify_on_action","ProductConfirmation", $args);
						}
			 			</pre>
			 
	This will send the Notification Type template to John and Giulia\'s emails.

== Frequently Asked Questions ==
None so far

== Changelog == 
v.10 - Notify on Action First Release

== Upgrade Notice ==
Initial version

== Screenshots == 
Screenshots are available
1. AdminPage.jpg
2. HelpScreen1.jpg
3. HelpScreen2.jpg
