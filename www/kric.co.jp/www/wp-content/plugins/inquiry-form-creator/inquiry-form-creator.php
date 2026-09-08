<?php
/*
Plugin Name: inquiry form creator
Plugin URI: http://horike.lolipop.jp/iqfm/
Description: This plugin is no longer supported and is not recommended.
Author: horike takahiro
Version: 0.7.8
Author URI: http://horike.lolipop.jp/iqfm/
*/ 

define( 'WPIQF_VERSION', '0.7' );

if ( ! defined( 'WPIQF_PLUGIN_BASENAME' ) )
	define( 'WPIQF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WPIQF_PLUGIN_NAME' ) )
	define( 'WPIQF_PLUGIN_NAME', trim( dirname( WPIQF_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WPIQF_PLUGIN_DIR' ) )
	define( 'WPIQF_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPIQF_PLUGIN_NAME );

if ( ! defined( 'WPIQF_PLUGIN_URL' ) )
	define( 'WPIQF_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPIQF_PLUGIN_NAME );

if ( ! defined( 'WPIQF_QUERY_EDIT' ) )
	define( 'WPIQF_QUERY_EDIT','iqfm-edit' );

if ( ! defined( 'WPIQF_QUERY_ADMIN' ) )
	define( 'WPIQF_QUERY_ADMIN','iqfm-admin' );

if ( ! defined( 'WPIQF_QUERY_CONFIG' ) )
	define( 'WPIQF_QUERY_CONFIG','iqfm-config' );

if ( ! defined( 'WPIQF_CSS_URL' ) )
	define( 'WPIQF_CSS_URL', WPIQF_PLUGIN_URL . '/css' );

if (! defined( 'WPIQF_TOKEN_PREFIX' ) )
	define('WPIQF_TOKEN_PREFIX', 'inquiry_form_creator_' );
	
function iqfm_admin_notice() {
	global $plugin_page;

	if ( ! isset( $plugin_page ) || (WPIQF_QUERY_EDIT != $plugin_page && WPIQF_QUERY_ADMIN != $plugin_page && WPIQF_QUERY_CONFIG != $plugin_page) )
		return;
    ?>
    <div class="error">
        <p>このプラグインは開発が終了しており、今後のメンテナンスやサポートは行わないため、使用を推奨しません。代わりにこのプラグインの後継である<a href="http://wordpress.org/plugins/trust-form/">Trust Form</a>をご利用下さい。</p>
    </div>
    <?php
}
add_action( 'admin_notices', 'iqfm_admin_notice' );

$iqfm_zip_data = array(
'選択して下さい',
'北海道',
'青森県',
'岩手県',
'宮城県',
'秋田県',
'山形県',
'福島県',
'茨城県',
'栃木県',
'群馬県',
'埼玉県',
'千葉県',
'東京都',
'神奈川県',
'新潟県',
'富山県',
'石川県',
'福井県',
'山梨県',
'長野県',
'岐阜県',
'静岡県',
'愛知県',
'三重県',
'滋賀県',
'京都府',
'大阪府',
'兵庫県',
'奈良県',
'和歌山県',
'鳥取県',
'島根県',
'岡山県',
'広島県',
'山口県',
'徳島県',
'香川県',
'愛媛県',
'高知県',
'福岡県',
'佐賀県',
'長崎県',
'熊本県',
'大分県',
'宮崎県',
'鹿児島県',
'沖縄県');

function iqfm_unicode_decode($str) {
	return preg_replace_callback("/\\\\u([0-9a-zA-Z]{4})/", "iqfm_decode_callback", $str);
}

function iqfm_decode_callback($matches) {
	$char = mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UTF-16");
	return $char;
}

function iqfm_get_file_short_name($name) {
	$pos = strrpos( $name, '.' );
	return substr( $name, 0, $pos );
}

function iqfm_edit_zip( $element ) {
	$zip = array();
	$field_id = $element['form_component_id'];

	if ( array_key_exists('field_html', $element) ) {
		$field_html = explode('#', $element['field_html']);
		foreach($field_html as $val) {
			$val = explode("\n", $val);
			foreach($val as $val_1) {
				$data = explode(':', $val_1);
				$zip[$data[0]] = $data[1];
			}
		}
	}
		
	if ( array_key_exists('field_option', $element) ) {
		$field_option = explode("\n", $element['field_option']);
		foreach($field_option as $val) {
			$val = explode("\n", $val);
			foreach($val as $val_1) {
				$data = explode(':', $val_1);
				$zip[$data[0]] = $data[1];
			}
		}
	}
	
	if ( array_key_exists('field_subject', $element) ) {
		$field_subject = explode("\n", $element['field_subject']);
		foreach($field_subject as $val) {
			$val = explode("\n", $val);
			foreach($val as $val_1) {
				$data = explode(':', $val_1);
				$zip[$data[0]] = $data[1];
			}
		}
	}
	
	if ( array_key_exists('attention_message', $element) ) {
		$attention_message = explode("\n", $element['attention_message']);
		foreach($attention_message as $val) {
			$val = explode("\n", $val);
			foreach($val as $val_1) {
				$data = explode(':', $val_1);
				$zip[$data[0]] = $data[1];
			}
		}
	}
	
	if ( array_key_exists('field_validation', $element) ) {
		$field_validation = explode("\n", $element['field_validation']);
		foreach($field_validation as $val) {
			$val = explode("\n", $val);
			foreach($val as $val_1) {
				$data = explode(':', $val_1);
				$zip[$data[0]] = $data[1];
			}
		}
	}
	
	if ($zip['code_type'] === '2') {
		$size = explode( '_', $zip["zip_code_size1_$field_id"] );
		$max  = explode( '_', $zip["zip_code_max1_$field_id"] );
		$zip['zip_code_size_front'] = $size[0];
		$zip['zip_code_size_back']  = $size[1];
		$zip['zip_code_max_front'] = $max[0];
		$zip['zip_code_max_back']  = $max[1];
	}
	return $zip;
}

require_once(WPIQF_PLUGIN_DIR.'/iqfm-installer.php');
register_activation_hook(__FILE__, 'iqfm_install');

require_once(WPIQF_PLUGIN_DIR.'/set-header.php');

require_once(WPIQF_PLUGIN_DIR.'/set-modules.php');

function iqfm_googleanalytics($form_id) {
	global $wpdb;
	
	$result = $wpdb->get_row($wpdb->prepare("SELECT ga_conversion_input, ga_conversion_confirm, ga_conversion_finish FROM ".$wpdb->prefix."iqfm_inquiryform WHERE delete_flg = 0 and form_id = %d", $form_id), ARRAY_A);
	
	if (array_key_exists('inqirymode', $_POST) && $_POST['inqirymode'] == 'input') {
		$err  = iqfm_execute_validation();

		if ($err === false) {
			echo ", '/".$result['ga_conversion_confirm']."'";
		} else {
			echo ", '/".$result['ga_conversion_input']."'";
		}
	} elseif (array_key_exists('inquirymode', $_POST) && $_POST['inquirymode'] == 'confirm') {
		$err  = iqfm_execute_validation();

		if ($err === false) {
			echo ", '/".$result['ga_conversion_finish']."'";
		} else {
			echo ", '/".$result['ga_conversion_input']."'";
		}
	} else {
		echo ", '/".$result['ga_conversion_input']."'";
	}
}
?>
