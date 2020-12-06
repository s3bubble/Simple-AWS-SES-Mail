=== FREE Simple AWS SES Mail ===
Contributors: S3Bubble
Donate link: https://s3bubble.com
Tags: Amazon Web Services, SES
Requires at least: 4.0
Tested up to: 5.5.1 
Requires PHP: 5.6 
Stable tag: 0.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Amazon Simple Email Service (SES) is a cost-effective, flexible, and scalable email service

== Description ==

Amazon Simple Email Service (SES) is a cost-effective, flexible, and scalable email service that enables developers to send mail from within any application. You can configure Amazon SES quickly to support several email use cases, including transactional, marketing, or mass email communications.

== Installation ==

1. Upload or download the plugin from the WordPress repo and activate
2. Create a IAM user throught the AWS dashboard with programmatic access and a AmazonSSMFullAccess policy attached
3. Enter the key directly throught the dashboard under the tools menu.

Or enter the keys generated in the wp-config.php example below.

define( 'SASM_FROM_EMAIL', 'hello@example.com' );
define( 'SASM_FROM_NAME', 'Testing' );
define( 'SASM_REGION', 'us-east-1' );
define( 'SASM_KEY', '' );
define( 'SASM_SECRET', '' );

4. Enable logs send a test email and check logs.

== Frequently Asked Questions ==

coming soon.

== Screenshots ==

1. Dashboard 1
2. Dashboard 2
3. Dashboard 3

== Changelog ==

= 0.0.1 =

== Upgrade Notice ==

= 0.0.1 =

Upgrade notices for S3Bubble.