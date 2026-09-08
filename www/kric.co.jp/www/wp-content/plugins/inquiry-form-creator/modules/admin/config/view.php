<?php
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/base/view.php');
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/config/model.php');

class IQFM_ConfigView extends IQFM_BaseView {

private $model = null;

public function __construct() {
	$this->model = new IQFM_ConfigModel;
}

public function show() {

	echo 
	'<div class="wrap nosubsub">
		<div class="icon32" id="icon-edit-pages"><br></div>
		<h2>環境設定</h2>';
		if(@$_POST['posted'] == 'Y') {
			echo '<div class="updated"><p><strong>変更を保存しました</strong></p></div>';
		}
		echo 
		'
		<form method="post" id="iqfm_config_form" action="'.IQFM_BaseModel::iqfm_get_url().'" >
			<input type="hidden" name="posted" value="Y">
			<table class="form-table">
				<tr>
					<th scope="row" style="white-space:nowrap;">プラグインを操作できる権限レベルの設定<br />( 管理者：10 編集者：7 投稿者：2 寄稿者：1 購読者：0 )</th>
					<td>
						<select  name="iqfm_edit_level">
							<option value="10" '.( (get_option( 'iqfm_edit_level' ) == 10)?"selected=\'selected\'":"" ).'>10
							<option value="9" '.( (get_option( 'iqfm_edit_level' ) == 9)?"selected=\'selected\'":"" ).'>9
							<option value="8" '.( (get_option( 'iqfm_edit_level' ) == 8)?"selected=\'selected\'":"" ).'>8
							<option value="7" '.( (get_option( 'iqfm_edit_level' ) == 7)?"selected=\'selected\'":"" ).'>7
							<option value="6" '.( (get_option( 'iqfm_edit_level' ) == 6)?"selected=\'selected\'":"" ).'>6
							<option value="5" '.( (get_option( 'iqfm_edit_level' ) == 5)?"selected=\'selected\'":"" ).'>5
							<option value="4" '.( (get_option( 'iqfm_edit_level' ) == 4)?"selected=\'selected\'":"" ).'>4
							<option value="3" '.( (get_option( 'iqfm_edit_level' ) == 3)?"selected=\'selected\'":"" ).'>3
							<option value="2" '.( (get_option( 'iqfm_edit_level' ) == 2)?"selected=\'selected\'":"" ).'>2
							<option value="1" '.( (get_option( 'iqfm_edit_level' ) == 1)?"selected=\'selected\'":"" ).'>1
							<option value="0" '.( (get_option( 'iqfm_edit_level' ) == 0)?"selected=\'selected\'":"" ).'>0
						</select>以上
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="hidden" name="iqfm_config" value="1">
				<input type="submit" name="submit" class="button-primary" value="変更を保存">
			</p>
	</div>';
}
}
?>