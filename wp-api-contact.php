<?php

/*
Plugin Name: WP REST API Contact
Plugin URI: https://github.com/danpaul/wp_api_contact
Description: Plugin to expose endpoints to allow API based contact form
Version: 1.0.0
Author: Dan Breczinski
Author URI: http://dan.breczinski.com
License: GPLv2 or later
*/
$WP_API_CONTACT_DEFAULT_SUBJECT = 'New Contact Message: '. get_bloginfo('url');
$WP_API_CONTACT_SUBJECT_LIMIT = 200;
$WP_API_CONTACT_MESSAGE_LIMIT = 10000;

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {  return die(); }

function wp_api_contact_send_message( WP_REST_Request $request ) {

	global $WP_API_CONTACT_DEFAULT_SUBJECT,
		   $WP_API_CONTACT_SUBJECT_LIMIT,
		   $WP_API_CONTACT_MESSAGE_LIMIT;

	$response = new stdClass;
	$subject = $request->get_param('subject'); 
	$message = $request->get_param('message');

	if( empty($subject)  ){
		$subject = $WP_API_CONTACT_DEFAULT_SUBJECT;
	} else {
		$subject = sanitize_text_field($subject);
		if( strlen($subject) > $WP_API_CONTACT_SUBJECT_LIMIT ){
			$response->error = 'Subject can not be more than '. $WP_API_CONTACT_SUBJECT_LIMIT. ' characters.';
			return $response;
		}
	}

	if( empty($message)  ){
		$response->error = 'A message is required';
		return $response;
	}

	if( strlen($message) > $WP_API_CONTACT_MESSAGE_LIMIT ){
		$response->error = 'Subject can not be more than '. $WP_API_CONTACT_MESSAGE_LIMIT. ' characters.';
		return $response;
	}

	if( !wp_mail(get_option('admin_email'), $subject, $message) ){
		$response->error = 'An error occurred when trying to send your message. Message was not sent';
		return $response;
	}
	return $response;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'wp-api-contact/v1', '/send', array(
		'methods' => 'POST',
		'callback' => 'wp_api_contact_send_message',
	) );
} );