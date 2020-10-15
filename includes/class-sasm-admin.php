<?php

class SASMAdmin {

	public function __construct(){

        add_action( 'init', array( $this, 'register_post_type' ), -1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_action( 'wp_ajax_sasm_enable_logs', array( $this, 'enable_logs' ));

        add_action( 'wp_ajax_sasm_clear_logs', array( $this, 'clear_logs' ));

        add_action( 'wp_ajax_sasm_send_test', array( $this, 'send_test' ));

    }

    public function register_post_type() {
 
        $log_args = array(
            'labels'            => array( 'name' => __( 'Logs', 'wp-logging' ) ),
            'public'            => false,
            'query_var'         => false,
            'rewrite'           => false,
            'capability_type'   => 'post',
            'supports'          => array( 'title', 'editor' ),
            'can_export'        => false
        ); 

        register_post_type( 'sasm_logs', $log_args );
 
    }

    public function admin_scripts() {

       	wp_register_style( 'sasm_admin', SASM_PLUGIN_URL . '/assets/admin.css', false, '1.0.0' );
        
        wp_enqueue_style( 'sasm_admin' );

        wp_register_script( 'sasm_admin', SASM_PLUGIN_URL . '/assets/admin.js',  array('jquery'), '1.0.0', true );
        
        wp_enqueue_script( 'sasm_admin' );
        
        wp_localize_script( 'sasm_admin', 'sasm_admin', 
        	array( 
        		'ajaxurl' => admin_url( 'admin-ajax.php' ),
        		'nonce' => wp_create_nonce("sasm_admin_nonce")
        	)
        );        
    
    }
 
    public function admin_menu() {

        add_management_page( 'SES Mail Logs', 'SES Mail Logs', 'install_plugins', 'output_logs', array( $this, 'output_logs' ), '' );
    
    }

    public function output_logs() {
    	
    	?>
    	<div class = "wrap">
			
			<h1>
				<?php esc_html_e( 'Simple AWS SES Mail Plugin logs.', 'my-plugin-textdomain' ); ?>
			</h1>

			<p>
				<?php if(get_option( 'sasm_enable_logs' )){ ?>

					<button id="ses-enable-logs"><?php _e( 'Disable logs', 'simple-aws-ses-mail' ); ?></button>
					<button id="ses-send-test-email"><?php _e( 'Send Test Email', 'simple-aws-ses-mail' ); ?></button>
					<button id="ses-clear-logs"><?php _e( 'Clear Logs', 'simple-aws-ses-mail' ); ?></button>

					<div class="ses-logs">
						<?php 

                            $the_query = new WP_Query( array(
                                'post_parent'    => 0,
                                'post_type'      => 'sasm_logs',
                                'posts_per_page' => 100,
                                'post_status'    => 'publish'
                            ) );
 
                            // The Loop
                            if ( $the_query->have_posts() ) {
        
                                while ( $the_query->have_posts() ) {

                                    $the_query->the_post();
                                    
                                    echo get_the_content() . '<br>';
                                
                                }
 
                            } else {
                                    
                                _e( 'No logs...', 'simple-aws-ses-mail' );

                            }

                            wp_reset_postdata();

                        ?>
					</div>

				<?php }else{ ?>

					<button id="ses-enable-logs"><?php _e( 'Enable logs', 'simple-aws-ses-mail' ); ?></button>

				<?php } ?>
			</p>

		</div>
		<?php
    }

    public function enable_logs() {
    	
    	if ( !wp_verify_nonce( $_REQUEST['nonce'], 'sasm_admin_nonce')) {
	      	
	      	exit('No naughty business please');

	   	}   

	   	$sasm_enable_logs = get_option( 'sasm_enable_logs' );

	   	if($sasm_enable_logs){

	   		update_option( 'sasm_enable_logs', false );

	   		wp_send_json(array(
	            'status' => false, 
	            'message' => 'Logs disabled'
	        ));

	   	}else{

	   		update_option( 'sasm_enable_logs', true );

	   		wp_send_json(array(
	            'status' => false, 
	            'message' => 'Logs enabled'
	        ));

	   	}

    }

    public function clear_logs() {
    	
    	if ( !wp_verify_nonce( $_REQUEST['nonce'], 'sasm_admin_nonce')) {
	      	
	      	exit('No naughty business please');

	   	}   

        $logs = get_posts(array(
            'post_type' => 'sasm_logs',
            'numberposts' => -1 
        ));
        
        foreach ($logs as $log) {
            
            wp_delete_post( $log->ID, true );
        
        }

        wp_send_json(array(
            'status' => true, 
            'message' => 'Logs cleared'
        ));

    }

    public function send_test() {
    	
    	if ( !wp_verify_nonce( $_REQUEST['nonce'], 'sasm_admin_nonce')) {
	      	
	      	exit('No naughty business please');

	   	}   

	   	$to = SASM_FROM_EMAIL;
		$subject = 'SES TEST EMAIL';
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light dark" />
    <meta name="supported-color-schemes" content="light dark" />
    <title></title>
    <style type="text/css" rel="stylesheet" media="all">@import url(https://fonts.googleapis.com/css?family=Nunito+Sans:400,700&amp;display=swap);body{width:100%!important;height:100%;margin:0;-webkit-text-size-adjust:none}a{color:#3869d4}a img{border:none}td{word-break:break-word}.preheader{display:none!important;visibility:hidden;mso-hide:all;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden}body,td,th{font-family:"Nunito Sans",Helvetica,Arial,sans-serif}h1{margin-top:0;color:#333;font-size:22px;font-weight:700;text-align:left}h2{margin-top:0;color:#333;font-size:16px;font-weight:700;text-align:left}h3{margin-top:0;color:#333;font-size:14px;font-weight:700;text-align:left}td,th{font-size:16px}blockquote,ol,p,ul{margin:.4em 0 1.1875em;font-size:16px;line-height:1.625}p.sub{font-size:13px}.align-right{text-align:right}.align-left{text-align:left}.align-center{text-align:center}.button{background-color:#3869d4;border-top:10px solid #3869d4;border-right:18px solid #3869d4;border-bottom:10px solid #3869d4;border-left:18px solid #3869d4;display:inline-block;color:#fff;text-decoration:none;border-radius:3px;box-shadow:0 2px 3px rgba(0,0,0,.16);-webkit-text-size-adjust:none;box-sizing:border-box}.button--green{background-color:#22bc66;border-top:10px solid #22bc66;border-right:18px solid #22bc66;border-bottom:10px solid #22bc66;border-left:18px solid #22bc66}.button--red{background-color:#ff6136;border-top:10px solid #ff6136;border-right:18px solid #ff6136;border-bottom:10px solid #ff6136;border-left:18px solid #ff6136}@media only screen and (max-width:500px){.button{width:100%!important;text-align:center!important}}.attributes{margin:0 0 21px}.attributes_content{background-color:#f4f4f7;padding:16px}.attributes_item{padding:0}.related{width:100%;margin:0;padding:25px 0 0 0;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0}.related_item{padding:10px 0;color:#cbcccf;font-size:15px;line-height:18px}.related_item-title{display:block;margin:.5em 0 0}.related_item-thumb{display:block;padding-bottom:10px}.related_heading{border-top:1px solid #cbcccf;text-align:center;padding:25px 0 10px}.discount{width:100%;margin:0;padding:24px;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0;background-color:#f4f4f7;border:2px dashed #cbcccf}.discount_heading{text-align:center}.discount_body{text-align:center;font-size:15px}.social{width:auto}.social td{padding:0;width:auto}.social_icon{height:20px;margin:0 8px 10px 8px;padding:0}.purchase{width:100%;margin:0;padding:35px 0;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0}.purchase_content{width:100%;margin:0;padding:25px 0 0 0;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0}.purchase_item{padding:10px 0;color:#51545e;font-size:15px;line-height:18px}.purchase_heading{padding-bottom:8px;border-bottom:1px solid #eaeaec}.purchase_heading p{margin:0;color:#85878e;font-size:12px}.purchase_footer{padding-top:15px;border-top:1px solid #eaeaec}.purchase_total{margin:0;text-align:right;font-weight:700;color:#333}.purchase_total--label{padding:0 15px 0 0}body{background-color:#f2f4f6;color:#51545e}p{color:#51545e}.email-wrapper{width:100%;margin:0;padding:0;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0;background-color:#f2f4f6}.email-content{width:100%;margin:0;padding:0;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0}.email-masthead{padding:25px 0;text-align:center}.email-masthead_logo{width:94px}.email-masthead_name{font-size:16px;font-weight:700;color:#a8aaaf;text-decoration:none;text-shadow:0 1px 0 #fff}.email-body{width:100%;margin:0;padding:0;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0}.email-body_inner{width:570px;margin:0 auto;padding:0;-premailer-width:570px;-premailer-cellpadding:0;-premailer-cellspacing:0;background-color:#fff}.email-footer{width:570px;margin:0 auto;padding:0;-premailer-width:570px;-premailer-cellpadding:0;-premailer-cellspacing:0;text-align:center}.email-footer p{color:#a8aaaf}.body-action{width:100%;margin:30px auto;padding:0;-premailer-width:100%;-premailer-cellpadding:0;-premailer-cellspacing:0;text-align:center}.body-sub{margin-top:25px;padding-top:25px;border-top:1px solid #eaeaec}.content-cell{padding:45px}@media only screen and (max-width:600px){.email-body_inner,.email-footer{width:100%!important}}@media (prefers-color-scheme:dark){.email-body,.email-body_inner,.email-content,.email-footer,.email-masthead,.email-wrapper,body{background-color:#333!important;color:#fff!important}blockquote,h1,h2,h3,ol,p,ul{color:#fff!important}.attributes_content,.discount{background-color:#222!important}.email-masthead_name{text-shadow:none!important}}:root{color-scheme:light dark;supported-color-schemes:light dark}body{font-family:"Nunito Sans",Helvetica,Arial,sans-serif}body{background-color:#f2f4f6;color:#51545e}</style>
  </head>
  <body style="width: 100% !important; height: 100%; -webkit-text-size-adjust: none; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; background-color: #F2F4F6; color: #51545E; margin: 0;" bgcolor="#F2F4F6">
    <span class="preheader" style="display: none !important; visibility: hidden; mso-hide: all; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden;">This is example text for the preheader set via the YAML front-matter for each email.</span>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; -premailer-width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; background-color: #F2F4F6; margin: 0; padding: 0;" bgcolor="#F2F4F6">
      <tr>
        <td align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
          <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; -premailer-width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; margin: 0; padding: 0;">
            <tr>
              <td class="email-masthead" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px; text-align: center; padding: 25px 0;" align="center">
                <a href="https://example.com" class="f-fallback email-masthead_name" style="color: #A8AAAF; font-size: 16px; font-weight: bold; text-decoration: none; text-shadow: 0 1px 0 white;">
                SES TEST
              </a>
              </td>
            </tr>
            <!-- Email Body -->
            <tr>
              <td class="email-body" width="570" cellpadding="0" cellspacing="0" style="word-break: break-word; margin: 0; padding: 0; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px; width: 100%; -premailer-width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0;">
                <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="width: 570px; -premailer-width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; background-color: #FFFFFF; margin: 0 auto; padding: 0;" bgcolor="#FFFFFF">
                  <!-- Body content -->
                  <tr>
                    <td class="content-cell" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px; padding: 45px;">
                      <div class="f-fallback">
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Escaped Handlebars Brackets</h1>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">Working with templates, youll occasionally need to put some <a href="https://github.com/wildbit/mustachio" style="color: #3869D4;">Mustachio</a> code in your Handlebars templates. To prevent the Handlebars processing from attempting to process your Mustachio code, youll need to escape the curly braces by adding a backslash just before the opening curly braces.</p>
                        <br />
                        <br />
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Headers</h1>
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Header 1</h1>
                        <h2 style="margin-top: 0; color: #333333; font-size: 16px; font-weight: bold; text-align: left;" align="left">Header 2</h2>
                        <h2 style="margin-top: 0; color: #333333; font-size: 16px; font-weight: bold; text-align: left;" align="left">Header 3</h2>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Paragraphs &amp; Formatting</h1>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">Transactional email is fun for the whole family! You can design it, write it, code it, and test it. And test it. And test it. And send it. And find a bug.</p>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">This paragraph has some <b>bold text</b> and <strong>strong text</strong> along with <i>italicized text</i> and <em>emphasized text</em>.</p>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Lists</h1>
                        <ul style="font-size: 16px; line-height: 1.625; margin: .4em 0 1.1875em;">
                          <li>Unordered list item 1</li>
                          <li>Unordered list item 2</li>
                          <li>Unordered list item 3</li>
                        </ul>
                        <ol style="font-size: 16px; line-height: 1.625; margin: .4em 0 1.1875em;">
                          <li>Ordered list item 1</li>
                          <li>Ordered list item 2</li>
                          <li>Ordered list item 3</li>
                        </ol>
                        <hr />
                        <!-- Action -->
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Action Buttons</h1>
                        <table class="body-action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; -premailer-width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; text-align: center; margin: 30px auto; padding: 0;">
                          <tr>
                            <td align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                              <!-- Border based button
     https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
                              <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
                                <tr>
                                  <td align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                                    <a href="http://example.com" class="f-fallback button button--red" target="_blank" style="color: #FFF; border-color: #ff6136; border-style: solid; border-width: 10px 18px; background-color: #FF6136; display: inline-block; text-decoration: none; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); -webkit-text-size-adjust: none; box-sizing: border-box;">Danger Button</a>
                                  </td>
                                </tr>
                              </table>
                              <br />
                              <!-- Border based button
     https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
                              <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
                                <tr>
                                  <td align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                                    <a href="http://example.com" class="f-fallback button button--green" target="_blank" style="color: #FFF; border-color: #22bc66; border-style: solid; border-width: 10px 18px; background-color: #22BC66; display: inline-block; text-decoration: none; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); -webkit-text-size-adjust: none; box-sizing: border-box;">Success button</a>
                                  </td>
                                </tr>
                              </table>
                              <br />
                              <!-- Border based button
           https://litmus.com/blog/a-guide-to-bulletproof-buttons-in-email-design -->
                              <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
                                <tr>
                                  <td align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                                    <a href="http://example.com" class="f-fallback button" target="_blank" style="color: #FFF; border-color: #3869d4; border-style: solid; border-width: 10px 18px; background-color: #3869D4; display: inline-block; text-decoration: none; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); -webkit-text-size-adjust: none; box-sizing: border-box;">Default button</a>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Attribute List</h1>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">If your email client baseline is sufficiently modern, you can achieve the same effects with list much more succinctly. We chose to use tables for these lists to accommodate Outlook 2007, 2010, and 2013.</p>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Option List</h1>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">For the most part, option lists are just like attribute lists. They just use line breaks to create some separation between the items.</p>
                        <table class="attributes" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 0 21px;">
                          <tr>
                            <td class="attributes_container" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                              <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                  <td class="attributes_item" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px; padding: 0;"><strong><a href="{{trial_extension_url}}" style="color: #3869D4;">Restart your trial</a></strong> - If you didnt get a chance to fully try out the product or need a little more time to evaluate, just let us know. Simply reply to this email and well extend your trial period.
                                    <br />
                                    <br />
                                  </td>
                                </tr>
                                <tr>
                                  <td class="attributes_item" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px; padding: 0;"><strong><a href="{{feedback_url}}" style="color: #3869D4;">Share feedback</a></strong> - If SES TEST isnt right for you, let us know what you were looking for and we might be able to suggest some alternatives that might be a better fit.</td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Example Closing</h1>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">Thanks,
                          <br />[Sender Name] and the SES TEST Team</p>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;"><strong>P.S.</strong> Need help getting started? Check out our help documentation. Or, just reply to this email with any questions or issues you have. The SES TEST support team is always excited to help you.</p>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Discount Code</h1>
                        <!-- Discount -->
                        <table class="discount" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 0; padding: 24px; border: 2px dashed #cbcccf; -premailer-width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; background-color: #F4F4F7;" bgcolor="#F4F4F7">
                          <tr>
                            <td align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                              <h1 class="f-fallback discount_heading" style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: center;" align="center">10% off your next purchase!</h1>
                              <p class="f-fallback discount_body" style="font-size: 15px; line-height: 1.625; text-align: center; color: #51545E; margin: .4em 0 1.1875em;" align="center">Thanks for your support! Heres a coupon for 10% off your next purchase if used by {{expiration_date}}.</p>
                              <!-- Border based button
                              <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
                                <tr>
                                  <td align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                                    <a href="http://example.com" class="f-fallback button button--green" target="_blank" style="color: #FFF; border-color: #22bc66; border-style: solid; border-width: 10px 18px; background-color: #22BC66; display: inline-block; text-decoration: none; border-radius: 3px; box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16); -webkit-text-size-adjust: none; box-sizing: border-box;">Use this discount now...</a>
                                  </td>
                                </tr>
                              </table>
                            </td>
                          </tr>
                        </table>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Related Items</h1>
                        <hr />
                        <h1 style="margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left;" align="left">Sub-text</h1>
                        <p style="font-size: 16px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">Sub-text is for any content that needs to be included at the bottom of the email but doesnt need to stand out. This can be good for disclaimers and text alternatives.</p>
                        <!-- Sub copy -->
                        <table class="body-sub" role="presentation" style="margin-top: 25px; padding-top: 25px; border-top-width: 1px; border-top-color: #EAEAEC; border-top-style: solid;">
                          <tr>
                            <td style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                              <p class="sub" style="font-size: 13px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;">If you’re having trouble clicking the confirm account button, copy and paste the URL below into your web browser.
                              </p>
                              <p class="sub" style="font-size: 13px; line-height: 1.625; color: #51545E; margin: .4em 0 1.1875em;"><a href="{{action_url}}" style="color: #3869D4;">{{action_url}}</a></p>
                            </td>
                          </tr>
                        </table>
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
            <tr>
              <td style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px;">
                <table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" style="width: 570px; -premailer-width: 570px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; text-align: center; margin: 0 auto; padding: 0;">
                  <tr>
                    <td class="content-cell" align="center" style="word-break: break-word; font-family: &quot;Nunito Sans&quot;, Helvetica, Arial, sans-serif; font-size: 16px; padding: 45px;">
                      <p class="f-fallback sub align-center" style="font-size: 13px; line-height: 1.625; text-align: center; color: #A8AAAF; margin: .4em 0 1.1875em;" align="center">© 2019 SES TEST. All rights reserved.</p>
                      <p class="f-fallback sub align-center" style="font-size: 13px; line-height: 1.625; text-align: center; color: #A8AAAF; margin: .4em 0 1.1875em;" align="center">
                        [Company Name, LLC]
                        <br />1234 Street Rd.
                        <br />Suite 1234
                      </p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
  </body>
</html>';
		$headers = array('Content-Type: text/html; charset=UTF-8');

		wp_mail( $to, $subject, $body, $headers );

		wp_send_json(array(
            'status' => true, 
            'message' => 'Test Done Check Logs!'
        ));

    }

}

$init = new SASMAdmin();