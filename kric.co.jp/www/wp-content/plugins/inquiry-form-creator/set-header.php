<?php
$wp_inquiry_ajax = '';
$wp_inquiry_file = '';

if ( isset($_GET['wp_inquiry_ajax']) )
	$wp_inquiry_ajax = htmlspecialchars($_GET['wp_inquiry_ajax']);
	
if ( isset($_GET['wp_inquiry_file']) )
	$wp_inquiry_file = htmlspecialchars($_GET['wp_inquiry_file']);

if ( $wp_inquiry_ajax === 'true' ) {
	if ( is_multisite() )
		wp_cookie_constants( );

	require_once ABSPATH . WPINC . '/pluggable.php';
	if ( is_user_logged_in() && current_user_can(get_option('iqfm_edit_level')) ) 
		require_once (WPIQF_PLUGIN_DIR.'/async/wp-inquiry-ajax.php');
		
	exit;
}

if ( $wp_inquiry_file === 'true' ) {
	require_once (WPIQF_PLUGIN_DIR.'/async/file-upload.php');
	exit;
}

function wpiqf_admin_enqueue_scripts() {
	global $plugin_page;
	if ( ! isset( $plugin_page ) || (WPIQF_QUERY_EDIT != $plugin_page && WPIQF_QUERY_ADMIN != $plugin_page) )
		return;

	wp_enqueue_script( 'yugajquery', '/wp-content/plugins/inquiry-form-creator/js/yuga.js_0.7.1/js/jquery.js');
//	wp_enqueue_script( 'acrodion', '/wp-content/plugins/inquiry-form-creator/js/accordion.js' ,array('yugajquery'));
	wp_enqueue_script( 'yuga', '/wp-content/plugins/inquiry-form-creator/js/yuga.js_0.7.1/js/yuga.js' ,array('yugajquery'));
	wp_enqueue_script( 'wp-inquiry', '/wp-content/plugins/inquiry-form-creator/js/wp-inquiry.js');
	wp_enqueue_script( 'uicore', '/wp-content/plugins/inquiry-form-creator/js/jquery.ui.core.js');
	wp_enqueue_script( 'datepicker', '/wp-content/plugins/inquiry-form-creator/js/jquery.ui.datepicker.js');
	wp_enqueue_script( 'datepicker-ja', '/wp-content/plugins/inquiry-form-creator/js/jquery.ui.datepicker-ja.js');
	wp_enqueue_script( 'datepicker-ja', '/wp-content/plugins/inquiry-form-creator/js/jquery.ui.jquery.form.js');
	wp_enqueue_script( 'superbox', '/wp-content/plugins/inquiry-form-creator/js/jquery.lightbox_me.js');
	wp_enqueue_script( 'flexigrid', '/wp-content/plugins/inquiry-form-creator/js/flexigrid.js');
	wp_enqueue_script( 'tablednd', '/wp-content/plugins/inquiry-form-creator/js/jquery.tablednd_0_5.js');
}

add_action('admin_print_scripts', 'wpiqf_admin_enqueue_scripts');

function wpiqf_admin_enqueue_styles() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || (WPIQF_QUERY_EDIT != $plugin_page && WPIQF_QUERY_ADMIN != $plugin_page))
		return;

	wp_enqueue_style( 'tab', '/wp-content/plugins/inquiry-form-creator/css/tab.css');
	wp_enqueue_style( 'jquery-ui-1.8.2.custom', '/wp-content/plugins/inquiry-form-creator/css/jquery-ui-1.8.2.custom.css');
	wp_enqueue_style( 'lightbox_me', '/wp-content/plugins/inquiry-form-creator/css/jquery.lightbox_me.css');
	wp_enqueue_style( 'table', '/wp-content/plugins/inquiry-form-creator/css/table.css');
	wp_enqueue_style( 'flexigrid', '/wp-content/plugins/inquiry-form-creator/css/flexigrid.css');

}

add_action( 'admin_print_styles', 'wpiqf_admin_enqueue_styles' );

// 管理メニューに追加するフック
add_action('admin_menu', 'menu_inquiry_add_pages');

// 上のフックに対するaction関数
function menu_inquiry_add_pages() {
	add_menu_page('お問い合わせ', 'お問い合わせ', get_option( 'iqfm_edit_level' ), WPIQF_QUERY_EDIT, 'inquiry_form_edit');
	add_submenu_page('iqfm-edit', '編集', '編集', get_option( 'iqfm_edit_level' ), WPIQF_QUERY_EDIT, 'inquiry_form_edit');
	add_submenu_page('iqfm-edit', '管理', '管理', get_option( 'iqfm_edit_level' ), WPIQF_QUERY_ADMIN, 'inquiry_form_admin');
	add_submenu_page('iqfm-edit', '環境設定', '環境設定', get_option( 'iqfm_edit_level' ), WPIQF_QUERY_CONFIG, 'inquiry_form_config');

}

function inquiry_form_admin() {
	require_once (WPIQF_PLUGIN_DIR.'/modules/admin/admin/view.php');
	$view = new IQFM_AdminView;
	$view->show();
}

function inquiry_form_edit() {
	require_once (WPIQF_PLUGIN_DIR.'/modules/admin/edit/view.php');
	$view = new IQFM_EditView;
	$view->show();
}

function inquiry_form_config() {
	require_once (WPIQF_PLUGIN_DIR.'/modules/admin/config/view.php');
	$view = new IQFM_ConfigView;
	$view->show();
}

