<?php
/*
require_once('Zend/Log.php');
require_once('Zend/Log/Writer/Stream.php');
require_once('Zend/Debug.php');

    $stream = @fopen('C:\xampp\php\logs\info.log', 'a', false);
    if (! $stream) {
        throw new Exception('ストリームのオープンに失敗しました');
    }
     
    $writer = new Zend_Log_Writer_Stream($stream);
    $logger = new Zend_Log($writer);
   */
require_once(WPIQF_PLUGIN_DIR.'/modules/front/validate/validator.php');

$field_id = '';

if ( isset($_GET['field_id']) )
	$field_id = htmlspecialchars($_GET['field_id']);
	
$result = $wpdb->get_row($wpdb->prepare(
		'SELECT field_option, field_validation FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_component_id=%d order by field_sort',
		$field_id
	), ARRAY_A);

list($micro, $unixtime) = explode(" ", microtime());
$filename = $_FILES['iqfm_file']['name'];
$filename_token = $filename. '.' . $unixtime;

$pos = strrpos( $filename, '.' );
if ( $pos !== false )
	$extend = substr( $filename, ($pos + 1) );

//$logger->info(Zend_Debug::dump($extend));
$validation = explode(',', $result['field_validation']);

if ( $validation[1] === 'yes_list' ) {
	$check = explode("\n", $result['field_option']);

	if ( $extend === NULL || InquiryValidator::check_list($extend, $check) === true ) {
		$data = array('error' => 'エラー：アップロード出来るファイルの拡張子は('.str_replace("\n", ",&nbsp;", $result['field_option']).')のみです');
//$logger->info(Zend_Debug::dump($data));
		header('Content-type: text/html; charset=utf-8');
		echo json_encode($data);
		exit;
	}
} elseif ( $validation[1] === 'no_list' ) {
	$check = explode("\n", $result['field_option']);
	if ( $extend !== NULL && InquiryValidator::check_list($extend, $check) === false ) {
		$data = array('error' => 'エラー：拡張子が('.str_replace("\n", ",&nbsp;", $result['field_option']).')のファイルはアップロード出来ません');
		header('Content-type: text/html; charset=utf-8');
		echo json_encode($data);
		exit;
	}
}



//$logger->info(Zend_Debug::dump($filename));
$up_tmp_file = WPIQF_PLUGIN_DIR . '/tmp_file/' . $filename_token;
$up_tmp_url = WPIQF_PLUGIN_URL . '/tmp_file/' . $filename_token;
if ( move_uploaded_file($_FILES['iqfm_file']['tmp_name'], $up_tmp_file) ) {
    $data = array('filename'       => $filename,
                  'filename_token' => $filename_token);
	if ( file_exists($up_tmp_file) && exif_imagetype($up_tmp_file) ) {
		list($width, $height)= getimagesize($up_tmp_file); 
		$data['img_path']    = $up_tmp_url;
		$data['width']  = $width;
		$data['height'] = $height;
	}
} else {
    $data = array('error' => 'Failed to save');
}

header('Content-type: text/html; charset=utf-8');
echo json_encode($data);
exit;
