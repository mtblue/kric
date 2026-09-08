<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function iqfm_delete_plugin() {
	global $wpdb;

	delete_option( 'iqfm_installed' );
	delete_option( 'iqfm_installed_0211 ');
	delete_option( 'iqfm_installed_0212' );
	delete_option( 'iqfm_installed_03' );
	delete_option( 'iqfm_installed_032' );
	delete_option( 'iqfm_edit_level' );

	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."iqfm_inquiryform" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."iqfm_inquiryform_component" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."iqfm_inquiryform_mail" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."iqfm_inquiryform_result" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."iqfm_inquiryform_result_detail" );
	$wpdb->query( "DROP TABLE IF EXISTS ".$wpdb->prefix."iqfm_resultid_sequence" );
}

iqfm_delete_plugin();

?>