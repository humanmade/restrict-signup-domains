<?php

namespace HM\RestrictSignupDomains;

use WP_Error;

const WHITELIST_OPTION = 'hm-restrictsignupdomains-whitelist';
const BLACKLIST_OPTION = 'hm-restrictsignupdomains-blacklist';
const HINT_MESSAGE_OPTION = 'hm-restrictsignupdomains-hint-message';
const ERROR_MESSAGE_OPTION = 'hm-restrictsignupdomains-error-message';

function bootstrap() {
	register_activation_hook( PLUGIN_FILE, __NAMESPACE__ . '\\check_on_activate' );
	add_filter( 'login_message', __NAMESPACE__ . '\\render_hint_message' );
	add_action( 'register_post', __NAMESPACE__ . '\\check_email_requirements', 9, 3 );

	Admin\bootstrap();
}

/**
 * Check requirements on activation.
 *
 * @param bool $network_wide Is the plugin being activated for the whole network?
 */
function check_on_activate( $network_wide ) {
	if ( is_multisite() || $network_wide ) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( 'This plugin cannot be activated on multisite; use the built-in sign-up options instead' );
	}
}

/**
 * Render the hint message, if set.
 */
function render_hint_message( $message ) {
	global $action;
	if ( $action !== 'register' ) {
		return $message;
	}

	$hint = get_option( HINT_MESSAGE_OPTION );
	if ( empty( $hint ) ) {
		return $message;
	}

	return sprintf( '<p class="message register">%s</p>', esc_html( $hint ) );
}

/*
 * Validates the email domain against the whitelist or blacklist
 */
function check_email_requirements( $user_login, $user_email, WP_Error $errors ) {
	$whitelist = get_option( WHITELIST_OPTION );
	$blacklist = get_option( BLACKLIST_OPTION );

	if ( empty( $whitelist ) && empty( $blacklist ) ) {
		return;
	}

	// Split the email address at the @ symbol
	$email_parts = explode( '@', $user_email );

	// Pop off everything after the @ symbol, force lowercase to ignore case
	$domain = array_pop( $email_parts );
	$domain = strtolower( trim( $domain ) );

	if ( ! empty( $whitelist ) ) {
		// Whitelist mode.
		$valid = in_array( $domain, $whitelist, true );
	} else {
		// Blacklist mode.
		$valid = ! in_array( $domain, $blacklist, true );
	}

	if ( ! $valid ) {
		add_error_message( $errors );
	}
}

/**
 * Add error message to list
 */
function add_error_message( WP_Error $errors ) {
	$message = get_option( ERROR_MESSAGE_OPTION );
	if ( empty( $message ) ) {
		$message = 'This email domain is not allowed';
	}

	return $errors->add(
		'bad_email_domain',
		sprintf(
			'<strong>Error</strong>: %s',
			$message
		)
	);
}
