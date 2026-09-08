<?php
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/base/view.php');
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/admin/model.php');

class IQFM_AdminView extends IQFM_BaseView {

private $model = null;

public function __construct() {
	$this->model = new IQFM_AdminModel;
}

public function show() {
	echo $this->setbackground_close_x();
	echo $this->set_default_tab();
	echo 
	'<div class="wrap nosubsub">
	<div class="icon32" id="icon-edit-pages"><br></div>
	<h2>お問い合わせ管理</h2>';
	$form_ids = $this->model->get_forms_id();
	if ($form_ids === false) {
		echo '<div>お問い合わせが存在しません</div>'; 
	} else {
		echo '<ul class="tabNav">';
		foreach ($form_ids as $form) {
			echo '<li><a id="tabtitle'.$form['form_id'].'" href="#tab'.$form['form_id'].'">'.$form['form_name'].'</a></li>';
		}
		echo '</ul>';
		echo '<div class="tabContents"> ';
		foreach ($form_ids as $form) {
			$this->_show_tab_boby($form['form_id']);
		}
		echo '<!--/ .tabContents--></div>';
	}
	echo '<!--/ .wrap nosubsub--></div>';
}

private function _show_tab_boby($form_id) {
	echo '
	<div id="tab'.$form_id.'">
		<div class="wrap">';
	
	$components = $this->model->get_element($form_id);
	
	if ($components === false) {
		echo '<div>お問い合わせがありません</div>';
	} else {
		echo '
		<script type="text/javascript">
		<!--
		$(document).ready(function() {
			$("#result_table'.$form_id.'").flexigrid(
			{
			url: "'.IQFM_BaseModel::iqfm_get_url().'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=get-result&form_id='.$form_id.'",
			dataType: "json",
			colModel : [';
			echo '{display: \'更新\', name : \'update\', width : 100, sortable : true, align: "center"},';
			echo '{display: \'対応メモ\', name : \'message\', width : 150, sortable : true, align: "center"},';
			echo '{display: \'ステータス\', name : \'status\', width : 50, sortable : true, align: "center"},';
			echo '{display: \'問い合わせ日時\', name : \'inquirydate\', width : 120, sortable : true, align: "center"},';
			$param = '';
			foreach($components as $component) {
				if ($component['field_type'] === 'zip') {
						$param .= '{display: "住所", name : \''.$component["field_name"].'address\', width : 150, sortable : true, align: "center"}, ';
				} else {
					$param .= '{display: \''.$component["field_subject"].'\', name : \''.$component["field_name"].'\', width : 150, sortable : true, align: "center"}, ';
				}
			}
			echo substr($param, 0, -2);
			echo '],
			sortorder: "desc",
			usepager: true,
			singleSelect: true,
			useRp: true,
			rpOptions: [10,15,25,50,100],
			rp: 10,
			showTableToggleBtn: true,
			width: 1150,
			height: 500,
			nowrap: false,
			onSuccess:function(){
				$(".updateInquiryResult'.$form_id.'").click(function() {
					$("#resultedit'.$form_id.'").lightbox_me({
						centered: true, 
						onLoad: function() {
							$("#resultedit'.$form_id.'").find("input:first").focus();
						}
					});
					return false;
				});
				}
			}
			);
			
		}
		);
		-->
		</script>
		<div id="resultedit'.$form_id.'" class="lightbox_me_default">
			<div class="wrap nosubsub">
				<div class="icon32" id="icon-edit-pages"><br></div>
				<h2>お問い合わせ対応</h2>
				<a class="close close_x sprited" href="#">close</a>
			</div>
				<form method="post" id="resulteditform'.$form_id.'" name="resulteditform"  action='.IQFM_BaseModel::iqfm_get_url().'&iqfm-formid='.$form_id.' >
				<table class="form-table">
					<tr valign="top">
						<th scope="row">ステータス</th>
						<td><select id="resultStatus'.$form_id.'" name="resultStatus">
								<option value="0">未対応</option>
								<option value="1">対応済み</option>
								<option value="2">保留</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">対応メモ</th>
						<td>
							<textarea name="message" id="result_message'.$form_id.'" rows="10" cols="40"></textarea>
						</td>
					</tr>
				</table>
				<br />
				<input type="hidden" id="hiddenresult'.$form_id.'" name="result_edit" value="0" />
				<input type="submit" id="resultsubmit'.$form_id.'" class="button-primary" value="保存" />
			</form>
		</div>
		<form method="post" id="delresult'.$form_id.'" name="delresult"  action='.IQFM_BaseModel::iqfm_get_url().'&iqfm-formid='.$form_id.' >
			<input type="hidden" id="hiddendleteresult'.$form_id.'" name="result_delete_id" value="0" />
			<table id="result_table'.$form_id.'" style="display:none"></table> 
		</form><br />
		<form method="post" name="csvdl" action="'.IQFM_BaseModel::iqfm_get_url().'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=get-result&form_id='.$form_id.'" >		
			<input type="hidden" name="mode" value="csv" >
			<input type="submit" class="button-primary" value="お問い合わせをCSVダウンロード">
		</form>
		';
	}
	echo '</div>
	</div>';
}
}
?>