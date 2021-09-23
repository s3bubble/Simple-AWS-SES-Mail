<?php

use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;

/**
 * Sends the AWS email with SES
 */
class SASMMail {

	public function __construct(){


    }

    /**
     * Overide wp_mail and send email with AWS SES Service
     *
     * @param null
     * @return null
    */
    public function send_raw_wp_mail( $to, $subject, $message, $headers = [], $attachments = [] ) {

        if(get_option( 'sasm-encrypted-data' )){

            $details = $this->encrypted_ses_data(get_option( 'sasm-encrypted-data' ));

            $email = empty($details['email']) ? '' : $details['email']; 

            $name = empty($details['name']) ? '' : $details['name']; 

            $region = empty($details['region']) ? '' : $details['region'];

            $key = empty($details['key']) ? '' : $details['key'];

            $secret = empty($details['secret']) ? '' : $details['secret'];

        }else{ 

            $email = !defined( 'SASM_FROM_EMAIL' ) ? '' : SASM_FROM_EMAIL;

            $name = !defined( 'SASM_FROM_NAME' ) ? '' : SASM_FROM_NAME;

            $region = !defined( 'SASM_REGION' ) ? '' : SASM_REGION; 

            $key = !defined( 'SASM_KEY' ) ? '' : SASM_KEY;

            $secret = !defined( 'SASM_SECRET' ) ? '' : SASM_SECRET;

        }

        if ( 
            empty($email) || 
            empty($name) ||
            empty($region) ||
            empty($key) ||
            empty($secret)
        ){

            $this->logs('You have not defined the required values in wp-config.php or you having added them in the tools menu under SES Simple Email make sure these values are set. SASM_FROM_EMAIL SASM_FROM_NAME SASM_REGION SASM_KEY SASM_SECRET');

            return;

        }

        if ( file_exists( ABSPATH . WPINC . '/PHPMailer/PHPMailer.php' ) ) {
            
            require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
            
            require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
            
            $phpmailer = new \PHPMailer\PHPMailer\PHPMailer();
        
        } else {
        
            require_once ABSPATH . WPINC . '/class-phpmailer.php';
        
            $phpmailer = new \PHPMailer( true );
        
        }

        $phpmailer->setFrom($email, $name);

        $phpmailer->addAddress($to);
        
        $phpmailer->Subject = $subject;
        
        $phpmailer->Body = $message;
        
        $phpmailer->AltBody = $message;

        if(!empty($attachments)){

            foreach ($attachments as $attachment) {
                
                $phpmailer->addAttachment($attachment);

            }

        }

        if (!$phpmailer->preSend()) {

            $this->logs($phpmailer->ErrorInfo);

            return;
    
        } else {
    
            $mimeMessage = $phpmailer->getSentMIMEMessage();

        }

        try {


            $client = new SesClient([
                'version'     => 'latest',
                'region'      => $region,
                'credentials' => [
                    'key'    => $key,
                    'secret' => $secret
                ]
            ]);

            $result = $client->sendRawEmail([
                'RawMessage' => [
                    'Data' => $mimeMessage
                ]
            ]);

            $messageId = $result->get('MessageId');

            $this->logs("Email sent! Message ID: $messageId");

            return;

         } catch (Exception $e) {
            
            $this->logs($e->getAwsErrorMessage());

            return;

        }

        return true;

    }

    /**
     * Log any output data here
     *
     * @param null
     * @return null
    */
    public function logs($message) {

        $ses_enable_logs = get_option( 'sasm_enable_logs' );

        if(!$ses_enable_logs){
            
            return;

        }

        if(is_array($message)) { 
        
            $message = json_encode($message); 
        
        }
 
        // store the log entry
        $log_id = wp_insert_post( array(
            'post_type'   => 'sasm_logs',
            'post_status' => 'publish',
            'post_parent' => 0,
            'post_title' => 'log',
            'post_content'=> $message
        ) );

    }

    /**
     * Enables logging
     *
     * @param null
     * @return json
     */
    public function encrypted_ses_data($data) {

        $encoded_string = $data;

        // Store the cipher method 
        $ciphering = "AES-128-CTR"; 

        $options = 0;

        // Non-NULL Initialization Vector for decryption 
        $decryption_iv = '1234567891011121'; 
          
        // Store the decryption key 
        $decryption_key = AUTH_SALT; 
          
        // Use openssl_decrypt() function to decrypt the data 
        $decryption = openssl_decrypt ($encoded_string, $ciphering,  
                $decryption_key, $options, $decryption_iv); 

        $user_data = unserialize($decryption);

        return $user_data;

    }

}
