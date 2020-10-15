<?php 
/**
 * Plugin Name:  Simple AWS SES Mail
 * Plugin URI:   https://github.com/s3bubble/Simple-AWS-SES-Mail.git
 * Description:  Send all your WordPress emails through the powerful AWS SES Mail service
 * Version:      0.0.1
 * Author:       SoBytes
 * Author URI:   https://github.com/s3bubble
 * License:      GPL-2.0+
 * License URI:  http://www.gnu.org/licenses/gpl-2.0.html
 */ 

if ( ! defined( 'ABSPATH' ) ) exit; 

require_once dirname( __FILE__ ) . '/vendor/autoload.php';
require_once dirname( __FILE__ ) . '/includes/class-sasm-mail.php';
require_once dirname( __FILE__ ) . '/includes/class-sasm-admin.php';

define( 'SASM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SASM_PLUGIN_URL', plugins_url('', __FILE__) ); 

if ( ! function_exists( 'wp_mail' ) ) {

	function wp_mail( $to, $subject, $message, $headers = '', $attachments = [] ) { 
		
		$mail = new SASMMail();

		$result = $mail->send_raw_wp_mail( $to, $subject, $message, $headers, $attachments );

		return $result;

	}

}