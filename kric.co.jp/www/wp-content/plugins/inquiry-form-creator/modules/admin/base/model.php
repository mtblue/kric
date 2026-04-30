<?php
class IQFM_BaseModel {
	public function get_forms_id() {
		global $wpdb;
	
		$resultcnt = $wpdb->get_results("SELECT count(*) as cnt FROM ".$wpdb->prefix."iqfm_inquiryform WHERE delete_flg = 0 ");
		if ($resultcnt[0]->cnt == "0") {
			return false;
		} else {
			$result = $wpdb->get_results("SELECT form_id, form_name FROM ".$wpdb->prefix."iqfm_inquiryform WHERE delete_flg = 0 ORDER BY form_id", ARRAY_A);
			return $result;
		}
	}

	public function get_max_form_id() {
		global $wpdb;
	
		$result = $wpdb->get_results("SELECT max(form_id) as maxid FROM ".$wpdb->prefix."iqfm_inquiryform");
		if ($result[0]->maxid == 0) {
			return 1;
		} else {
			return ($result[0]->maxid+1);
		}
	}
	
	static function iqfm_get_url() {
		return str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
	}

	static function iqfm_set_select_element($field_id, $form_id, $field_type) {
		$select = '';
		$select = '
		<select class="addcomponent'.$form_id.'_'.$field_id.'" name="addcomponent'.$field_id.'" onclick="IQFM_editElementModel.displayComponentEdit('.$form_id.', '.$field_id.');" onchange="IQFM_editElementModel.displayComponentEdit('.$form_id.', '.$field_id.');">
			<option value="default">選択してください</option>
			<option value="text'.$form_id.'" '.(($field_type=='text')?'selected=selected':'').'>テキストボックス</option>
			<option value="radio'.$form_id.'" '.(($field_type=='radio')?'selected=selected':'').'>ラジオボタン</option>
			<option value="select'.$form_id.'" '.(($field_type=='selectbox')?'selected=selected':'').'>セレクトボックス</option>
			<option value="checkbox'.$form_id.'" '.(($field_type=='checkbox')?'selected=selected':'').'>チェックボックス</option>
			<option value="textarea'.$form_id.'" '.(($field_type=='textarea')?'selected=selected':'').'>テキストエリア</option>
			<option value="email'.$form_id.'" '.(($field_type=='email')?'selected=selected':'').'>メールアドレス</option>
			<option value="tel'.$form_id.'" '.(($field_type=='tel')?'selected=selected':'').'>電話番号</option>
			<option value="zip'.$form_id.'" '.(($field_type=='zip')?'selected=selected':'').'>住所</option>
			<option value="file'.$form_id.'" '.(($field_type=='file')?'selected=selected':'').'>ファイル添付</option>
		</select>';

		return $select;
	}
}
?>