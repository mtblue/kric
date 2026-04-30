<?php
$ajax_file = $_GET['wp_inquiry_ajaxtype'];
$ajax_path = WPIQF_PLUGIN_DIR.'/async/';

if ( iqfm_is_public_file( $ajax_file, $ajax_path, 'php' ) ) {
	require_once ( $ajax_path.$ajax_file.'.php' );
}

function iqfm_is_public_file( $file, $path, $extension ) {

	if ( !is_string($file) ) {
		return false;
	}
	
	if ( !preg_match("/^[a-z\/-]+$/", $file) ) {
		return false;
	}
	
	if ( file_exists( $path.$file.'.'.$extension ) === false ) {
		return false;
	}

	return true;
}
?>