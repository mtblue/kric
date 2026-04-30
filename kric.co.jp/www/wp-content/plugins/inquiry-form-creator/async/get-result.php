<?php
/**************ユーザーファイルダウンロード******************/
if ( maybe_serialize( stripslashes_deep( htmlspecialchars(@$_GET['mode']) ) ) == 'user_dl' ) {
	$file_name = @$_GET['file'];
	$full_file_name = WPIQF_PLUGIN_DIR . '/file/' . $file_name;
	
	$file_size = @filesize($full_file_name);
    ini_set('zlib.output_compression','Off');
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream ");
    header("Content-Disposition: attachment; filename=".iqfm_get_file_short_name($file_name));
    header("Content-Transfer-Encoding: binary");
    header("Content-size: binary");
    header("Content-Length: ".$file_size);
    @readfile($full_file_name);
    exit;
}
/**************ユーザーファイルダウンロード******************/

/***************csvダウンロード******************************/
if ( maybe_serialize( stripslashes_deep( htmlspecialchars(@$_POST['mode']) ) ) == 'csv' ) {
	$form_id = maybe_serialize( stripslashes_deep( $_GET['form_id'] ) );
	
	$resultcnt = $wpdb->get_row($wpdb->prepare(
	  'SELECT count(*) as cnt FROM '.$wpdb->prefix.'iqfm_inquiryform_result WHERE delete_flg = 0 and form_id=%d ORDER BY result_id desc',
	   $form_id
	 ), ARRAY_A);
	
	if ( $resultcnt['cnt'] !== '0' ) {
		$components = $wpdb->get_results($wpdb->prepare(
		  'SELECT form_id, field_name, field_type, field_subject FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d ORDER BY field_sort',
		   $form_id
		), ARRAY_A);
	
		$resultid = $wpdb->get_results($wpdb->prepare(
		  'SELECT result_id, status, message,regist_dt FROM '.$wpdb->prefix.'iqfm_inquiryform_result WHERE delete_flg = 0 and form_id=%d ORDER BY result_id desc',
		   $form_id
		 ), ARRAY_A);
		
		$results = $wpdb->get_results($wpdb->prepare(
		  'SELECT result_id, field_name, field_data, regist_dt FROM '.$wpdb->prefix.'iqfm_inquiryform_result_detail WHERE delete_flg = 0 and form_id=%d order by result_id desc',
		   $form_id
		 ), ARRAY_A);

		$tmp = array();
		foreach ($results as $result) {
			$tmp[$result['result_id']][$result['field_name']] = $result['field_data'];
		}
	
		foreach ($resultid as $result) {
			$tmp[$result['result_id']]['message']   = $result['message'];
			$tmp[$result['result_id']]['status']    = iqfm_get_status_message_text($result['status']);
			$tmp[$result['result_id']]['regist_dt'] = $result['regist_dt'];
		}

		$csv = array();
		foreach ( $components as $component ) {
			if( $component['field_type'] === 'zip' ) {
				$csv[0][] = '住所';
			} else {
				$csv[0][] = $component['field_subject'];
			}
		}
		$csv[0][] = '対応メモ';
		$csv[0][] = 'ステータス';
		$csv[0][] = '問い合わせ日時';
	
		$cnt = 1;

		foreach ($tmp as $val) {
			foreach ( $components as $component ) {
				$csv[$cnt][] = @$val[$component['field_name']];
			}
			$csv[$cnt][] = $val['message'];
			$csv[$cnt][] = $val['status'];
			$csv[$cnt][] = $val['regist_dt'];
			$cnt++;
		}
		mb_convert_variables('SJIS', 'UTF-8', $csv);
		
		$file_name = 'result_'.time().'.csv';
		$full_file_name = WPIQF_PLUGIN_DIR.'/csv/'.$file_name;
	
		$out = fopen($full_file_name, 'w');
		foreach($csv as $val) {
			fputcsv($out, $val);
		}
		fclose($out);
		$file_size = @filesize($full_file_name);
//	    ob_end_clean();
	    ini_set('zlib.output_compression','Off');
	    header("Pragma: public");
	    header("Expires: 0");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	    header("Cache-Control: public");
	    header("Content-Description: File Transfer");
	    header("Content-Type: application/octet-stream ");
	    header("Content-Disposition: attachment; filename=".$file_name);
	    header("Content-Transfer-Encoding: binary");
	    header("Content-size: binary");
	    header("Content-Length: ".$file_size);
	    @readfile($full_file_name);
	    @unlink($full_file_name);
	}
    exit;
}
/***************csvダウンロード******************************/

require_once (WPIQF_PLUGIN_DIR.'/Zend/Json.php');
$encode = Zend_Json::encode( iqfm_get_forms_result( maybe_serialize( stripslashes_deep( $_GET['form_id'] ) ) ) );
function iqfm_get_forms_result($form_id) {
	global $wpdb;
	
	$page      = htmlspecialchars(@$_POST['page']);
	$rp        = htmlspecialchars(@$_POST['rp']);
	$sortname  = htmlspecialchars(@$_POST['sortname']);
	$sortorder = htmlspecialchars(@$_POST['sortorder']);
	
	$postdata['page'] = ( !$page ) ? 1 : $page;
	$postdata['rp'] = ( !$rp ) ? 10 : $rp;
	$postdata['sortname'] = ( !$sortname ) ? 'form_result_id' : $sortname;
	$postdata['sortorder'] = ( !$sortorder ) ? 'desc' : $sortorder;


	$resultcnt = $wpdb->get_results($wpdb->prepare(
	  'SELECT count(*) as cnt FROM '.$wpdb->prefix.'iqfm_inquiryform_result WHERE delete_flg = 0 and form_id=%d',
	   $form_id
	 ));
	if ($resultcnt[0]->cnt == '0') {
		return false;
	} else {
		$components = $wpdb->get_results($wpdb->prepare(
		  'SELECT form_id, field_name, field_subject FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d ORDER BY field_sort', 
		  $form_id
		), ARRAY_A);
		$results = array();
		$result_table = array
		(
			'page' => $postdata['page'],
			'total' => $resultcnt[0]->cnt
		);

		$resultid = $wpdb->get_results($wpdb->prepare(
		  'SELECT result_id, status, message,regist_dt FROM '.$wpdb->prefix.'iqfm_inquiryform_result WHERE delete_flg = 0 and form_id=%d ORDER BY result_id '.$wpdb->escape($postdata['sortorder']).' limit %d, %d',
		   $form_id,
		   ( ( $postdata['page'] - 1 ) * $postdata['rp'] ),
		   $postdata['rp']
		 ), ARRAY_A);

		foreach ($resultid as $key => $val) {
			$results[$key] = $wpdb->get_results($wpdb->prepare(
			  'SELECT result_id, field_name, field_data, regist_dt FROM '.$wpdb->prefix.'iqfm_inquiryform_result_detail WHERE result_id = %d and delete_flg = 0 and form_id=%d order by result_id',
			  $val['result_id'],
			  $form_id
			), ARRAY_A);
		}

		$tmp = array();
		foreach ($resultid as $result) {
			$tmp[$result['result_id']]['result_id'] = '<input type="button" class="button-secondary updateInquiryResult'.$form_id.'" id="resuitid_'.$result['result_id'].'" value="更新" onclick="IQFM_editElementModel.updateInquiryResult('.$form_id.','.$result['result_id'].', \''.$_SERVER['SCRIPT_NAME'].'?page='.WPIQF_QUERY_ADMIN.'\')" />&nbsp;<input type="button" class="button-secondary" id="resuitdeleteid_'.$result['result_id'].'" value="削除" onclick="IQFM_editElementModel.deleteInquiryResult('.$form_id.','.$result['result_id'].')" />';
			$tmp[$result['result_id']]['message']   = str_replace( "\n", "<br />", $result['message'] );
			$tmp[$result['result_id']]['status']    = iqfm_get_status_message($result['status']);
			$tmp[$result['result_id']]['regist_dt'] = $result['regist_dt'];
			
			foreach ($components as $component) {
				$tmp[$result['result_id']][$component['field_name']] = '';
			}
		}
		
		foreach ($results as $val) {
//var_dump($val);
			foreach ($val as $val_2) {
				if ( strpos($val_2['field_name'], 'file') !== false && $val_2['field_data'] != '' ) {
					$tmp[$val_2['result_id']][$val_2['field_name']] = '<p>'.iqfm_get_file_short_name(esc_html($val_2['field_data'])).'<br /><input type="button" value="ダウンロード" onClick="location.href=\''.$_SERVER['SCRIPT_NAME'].'?page='.WPIQF_QUERY_ADMIN.'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=get-result&mode=user_dl&file='.$val_2['field_data'].'\'"></p>';
				} else {
					$tmp[$val_2['result_id']][$val_2['field_name']] = str_replace( "\n", "<br />", esc_html( $val_2['field_data'] ) );
				}
			}
		}
//var_dump($tmp);
		$tmp_2 = array();
		foreach($tmp as $val) {
			foreach($val as $val_2) {
				$tmp_2[] = $val_2;
			}
			$result_table['rows'][] = 
			(
				array
				(
					'cell' => $tmp_2
				)
			);
			$tmp_2 = array();
		}

		return $result_table;
	}
}

function iqfm_get_status_message($status) {
	if($status == 0) {
		return '<span style="color:#FF0000;">未対応</span>';
	} elseif($status == 1) {
		return '対応済み';
	} elseif($status == 2) {
		return '保留';
	} else {
		return 'エラー';
	}
}

function iqfm_get_status_message_text($status) {
	if($status == 0) {
		return '未対応';
	} elseif($status == 1) {
		return '対応済み';
	} elseif($status == 2) {
		return '保留';
	} else {
		return 'エラー';
	}
}

header('Content-Type: text/javascript; charset=utf-8');  
echo $encode; 
?>