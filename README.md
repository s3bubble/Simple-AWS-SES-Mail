# Simple-AWS-SES-Mail
Send all your WordPress emails through the powerful AWS SES Mail service

Add the code below to your wp-config.php file and your good to go.
```php
define( 'SASM_FROM_EMAIL', 'hello@example.com' );
define( 'SASM_FROM_NAME', 'Testing' );
define( 'SASM_REGION', 'us-east-1' );
define( 'SASM_KEY', '' );
define( 'SASM_SECRET', '' );
```