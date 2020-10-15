<?php

use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;

class SASMMail {

	public function __construct(){

    }

    public function send_raw_wp_mail( $to, $subject, $message, $headers = [], $attachments = [] ) {

        if ( 
            !defined( 'SASM_FROM_EMAIL' ) || 
            !defined( 'SASM_FROM_NAME' ) ||
            !defined( 'SASM_REGION' ) ||
            !defined( 'SASM_KEY' ) ||
            !defined( 'SASM_SECRET' )
        ){

            $this->logs('You have not defined the required values in wp-config.php make sure these values are set. SASM_FROM_EMAIL SASM_FROM_NAME SASM_REGION SASM_KEY SASM_SECRET');

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

        $phpmailer->setFrom(SASM_FROM_EMAIL, SASM_FROM_NAME);

        $phpmailer->addAddress($to);
        
        $phpmailer->Subject = $subject;
        
        $phpmailer->Body = $message;
        
        $phpmailer->AltBody = $message;

        if(!empty($attachments)){

            foreach ($attachments as $key => $attachment) {
                
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
                'region'      => SASM_REGION,
                'credentials' => [
                    'key'    => SASM_KEY,
                    'secret' => SASM_SECRET
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

    public function logs($message) {

        $ses_enable_logs = get_option( 'sasm_enable_logs' );

        if(!$ses_enable_logs){
            
            return;

        }

        if(is_array($message)) { 
        
            $message = json_encode($message); 
        
        } 

        $file = SASM_PLUGIN_PATH . 'logs/sasm_logs.log';

        if(file_exists($file)){

            $file = fopen( $file, 'a');
        
            fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $message); 
        
            fclose($file); 

        }

    }

}