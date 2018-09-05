<?php

namespace HM\RestrictSignupDomains\Admin;

use HM\RestrictSignupDomains;

const OPTION_SECTION = 'hm-restrictsignupdomains';

/**
 * Initialization function
 */
function bootstrap() {
	add_action( 'admin_init', __NAMESPACE__ . '\\configure_settings' );
}

/*
 * Register our settings. Add the settings section, and settings fields
 */
function configure_settings() {
	register_setting( 'general', RestrictSignupDomains\WHITELIST_OPTION, [
		'sanitize_callback' => __NAMESPACE__ . '\\sanitize_domains_field',
	] );
	register_setting( 'general', RestrictSignupDomains\BLACKLIST_OPTION, [
		'sanitize_callback' => __NAMESPACE__ . '\\sanitize_domains_field',
	] );
	register_setting( 'general', RestrictSignupDomains\HINT_MESSAGE_OPTION, [
		'sanitize_callback' => 'sanitize_text_field',
	] );
	register_setting( 'general', RestrictSignupDomains\ERROR_MESSAGE_OPTION, [
		'sanitize_callback' => 'sanitize_text_field',
	] );

	// Register option UI.
	add_settings_section(
		OPTION_SECTION,
		'Signup Domain Restrictions',
		'__return_null',
		'general'
	);
	add_settings_field(
		RestrictSignupDomains\WHITELIST_OPTION,
		'Limited Email Registrations',
		__NAMESPACE__ . '\\render_domains_input',
		'general',
		OPTION_SECTION,
		[
			'label_for' => RestrictSignupDomains\WHITELIST_OPTION,
			'option' => RestrictSignupDomains\WHITELIST_OPTION,
			'description' => 'If you want to limit site registrations to certain domains. One domain per line.',
		]
	);
	add_settings_field(
		RestrictSignupDomains\BLACKLIST_OPTION,
		'Banned Email Domains',
		__NAMESPACE__ . '\\render_domains_input',
		'general',
		OPTION_SECTION,
		[
			'label_for' => RestrictSignupDomains\BLACKLIST_OPTION,
			'option' => RestrictSignupDomains\BLACKLIST_OPTION,
			'description' => 'If you want to ban domains from site registrations. One domain per line.',
		]
	);
	add_settings_field(
		RestrictSignupDomains\HINT_MESSAGE_OPTION,
		'Hint Message',
		__NAMESPACE__ . '\\render_hint_message_input',
		'general',
		OPTION_SECTION
	);
	add_settings_field(
		RestrictSignupDomains\ERROR_MESSAGE_OPTION,
		'Error Message',
		__NAMESPACE__ . '\\render_error_message_input',
		'general',
		OPTION_SECTION
	);
}

/*
 * Output for whitelist section in form
 */
function render_whitelist_section() {
	echo '<p>If you <strong>WHITELIST</strong> a domain, then only email addresses <strong>CONTAINING</strong> that domain will be allowed.</p><strong>Enter one (1) domain per line.</strong>';

	$whitelist = get_option( RestrictSignupDomains\WHITELIST_OPTION );

	if ( ! empty( $whitelist ) ) {
		echo '<p><strong>NOTICE: Whitelist is in use. Blacklist will be ignored.</strong></p>';
	}
}

/*
 * Output for blacklist section in form
 */
function render_blacklist_section() {
	echo '<p>If you <strong>BLACKLIST</strong> a domain, then all email addresses <strong>NOT CONTAINING</strong>  that domain will be allowed.</p><strong>Enter one (1) domain per line.</strong>';

	$whitelist = get_option( RestrictSignupDomains\WHITELIST_OPTION );
	$blacklist = get_option( RestrictSignupDomains\BLACKLIST_OPTION );

	if ( empty( $whitelist ) && ! empty( $blacklist ) ) {

		echo '<p><strong>NOTICE: Blacklist is in use. Whitelist will be ignored.</strong></p>';

	}

}

/**
 * Render the domain list input
 *
 * @param array $args Arguments from setting registration.
 */
function render_domains_input( $args ) {
	$value = get_option( $args['option'], [] );
	$editable = implode( "\n", $value );

	printf(
		'<textarea id="%1$s" name="%1$s" rows="7" cols="50" type="textarea">%2$s</textarea>',
		esc_attr( $args['option'] ),
		esc_textarea( $editable )
	);

	if ( ! empty( $args['description'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
	}
}

/**
 * Render the input for the error message option.
 */
function render_hint_message_input() {
	$value = get_option( RestrictSignupDomains\HINT_MESSAGE_OPTION, '' );

	printf(
		'<input id="%1$s" name="%1$s" placeholder="%2$s" class="large-text" type="text" value="%3$s" />',
		esc_attr( RestrictSignupDomains\HINT_MESSAGE_OPTION ),
		// Default message from core
		__( 'Register For This Site' ),
		esc_attr( $value )
	);
	echo '<p class="description">Hint message to display on signup page.</p>';
}

/**
 * Render the input for the error message option.
 */
function render_error_message_input() {
	$value = get_option( RestrictSignupDomains\ERROR_MESSAGE_OPTION, '' );

	printf(
		'<input id="%1$s" name="%1$s" placeholder="%2$s" class="large-text" type="text" value="%3$s" />',
		esc_attr( RestrictSignupDomains\ERROR_MESSAGE_OPTION ),
		'This email domain is not allowed',
		esc_attr( $value )
	);
	echo '<p class="description">Error message to display if email does not meet domain requirements.</p>';
}

/**
 * Sanitize a domain list field.
 *
 * @param string $input Raw input value submitted by user.
 * @return string[] List of valid domains.
 */
function sanitize_domains_field( $input ) {
	if ( empty( $input ) ) {
		return [];
	}

	$sanitized = [];

	// Normalize string.
	$domains = explode( "\n", str_replace( "\r\n", "\n", sanitize_textarea_field( $input ) ) );

	foreach ( $domains as $domain ) {
		// Skip empty lines.
		$domain = trim( $domain );
		if ( empty( $domain ) ) {
			continue;
		}

		// Prepend scheme for validation.
		if ( ! preg_match( '#^http(s)?://#', $domain ) ) {
			$domain = 'http://' . $domain;
		}
		$parts = parse_url( $domain );

		if ( ! $parts || ! $parts['host'] ) {
			continue;
		}

		$sanitized[] = strtolower( $parts['host'] );
	}

	return $sanitized;
}
