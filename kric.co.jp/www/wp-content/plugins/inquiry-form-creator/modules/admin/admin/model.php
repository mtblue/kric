<?php
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/base/model.php');
class IQFM_AdminModel extends IQFM_BaseModel {

	public function get_element($form_id) {
		global $wpdb;

		$resultcnt = $wpdb->get_results($wpdb->prepare('SELECT count(*) as cnt FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d', $form_id));

		if ($resultcnt[0]->cnt == 0) {
			return false;
		} else {
			$result = $wpdb->get_results($wpdb->prepare('SELECT form_component_id, field_type, field_name, field_subject, field_html, field_option, field_validation, attention_message FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d ORDER BY field_sort', $form_id), ARRAY_A);
			return $result;
		}
	}
}
?>