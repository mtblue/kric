<?php
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/base/model.php');
class IQFM_EditModel extends IQFM_BaseModel {

	public function get_element($form_id) {
		global $wpdb;

		$ret = array();

		$result = $wpdb->get_results('SELECT form_component_id, field_name, field_type ,field_subject, field_sort FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0 and form_id='.$form_id.' order by field_sort', ARRAY_A);

		$ret[0] = count($result);
		$ret[1] = array();
		foreach($result as $val) {
			$ret[1][$val['form_component_id']] = '<tr id='.$val['form_component_id'].'><td>'.$val['field_sort'].'</td><td>'.( $val['field_type'] === 'zip' ? '住所':$val['field_subject'] ).'</td><td>'.IQFM_BaseModel::iqfm_set_select_element($val['form_component_id'], $form_id, $val['field_type']).'</td><td><input type="button" class="button-secondary" value="削除" onclick="IQFM_editElementModel.deleteElement('.$val['form_component_id'].', \''.IQFM_BaseModel::iqfm_get_url().'\')"></td></tr>';
		}
	
		return $ret;
	}
}
?>