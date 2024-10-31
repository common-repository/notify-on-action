/**
 * Notify On Action Specific JS file
 * 
 * @author Luciana Bruscino
 * @license GPL
 */

jQuery(document).ready(function() {		  
	 function checkLength( o, n, min, max ) {
		
		  if ( o.val().length > max || o.val().length < min ) {
					
					alert( "Length of " + n + " must be between " +
						min + " and " + max + "." );
					return false;
				} else {
					return true;
				}
			}
			
			function checkRegexp( o, regexp, n ) {
				if ( !( regexp.test( o.val() ) ) ) {	
					alert('email not valid');					
					return false;
				} else {
					return true;
				}
			}

		function validateEmail(email){
			if(email.val().length ==0) return true;
			var lcheck = checkLength( email, "email", 6, 80 );
			if (lcheck)
				var tcheck = checkRegexp( email, /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i, "eg. support@my-domain-list.com" );
			if(lcheck && tcheck)
				return true;
			return false;
		}

		jQuery("#noa_opt_recipient_list").blur(function(){
			var emailArray = jQuery(this).val().split(","); 
			emailArray.each(function(index) {
				if(!validateEmail(jQuery(this).text())){
					jQuery("#noa_opt_recipient_list").focus();
					alert(index + " email is has the problem");
					return false;
					}							  
			  });
				
			});

		jQuery("#noa_post_action_clear").click(function(){						
				jQuery('#noa_add_new_form').get(0).reset();
			});
		jQuery("#noa_recipient_list_display").hide();
       jQuery("#noa_opt_recipients").change(
                   function() { 
                	   jQuery("#noa_opt_recipient_list").val("");
                   	if (jQuery("#noa_opt_recipients option:selected").val() == "email"){
                   		jQuery("#noa_recipient_list_display").show();
                   	}else{
                   		jQuery("#noa_recipient_list_display").hide();
                   	}
                   });
	});//end ready
		
