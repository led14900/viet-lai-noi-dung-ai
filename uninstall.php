<?php
/**
 * Uninstall script — runs when plugin is deleted
 *
 * @package Viet_Lai_Noi_Dung_AI
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete all plugin options.
$options_to_delete = array(
	'aiwd_active_provider',
	'aiwd_openai_api_key',
	'aiwd_openai_model',
	'aiwd_openai_temperature',
	'aiwd_claude_api_key',
	'aiwd_claude_model',
	'aiwd_claude_temperature',
	'aiwd_compatible_api_key',
	'aiwd_compatible_endpoint',
	'aiwd_compatible_model',
	'aiwd_compatible_temperature',
	'aiwd_gemini_api_key',
	'aiwd_gemini_model',
	'aiwd_gemini_temperature',
	'aiwd_brand_name',
	'aiwd_brand_description',
	'aiwd_brand_website',
	'aiwd_brand_contact_name',
	'aiwd_brand_contact_phone',
);

foreach ( $options_to_delete as $option ) {
	delete_option( $option );
}

// Delete all plugin post meta.
global $wpdb;
$meta_keys = array(
	'_aiwd_main_keyword',
	'_aiwd_secondary_keywords',
	'_aiwd_target_word_count',
	'_aiwd_generated',
	'_aiwd_generated_date',
);
foreach ( $meta_keys as $meta_key ) {
	$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => $meta_key ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.SlowDBQuery.slow_db_query_meta_key
}
