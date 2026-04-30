<?php
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/base/view.php');
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/edit/model.php');
require_once(WPIQF_PLUGIN_DIR.'/modules/admin/helper/element.php');
//編集機能のview
class IQFM_EditView extends IQFM_BaseView {

private $model = null;

public function __construct() {
	$this->model = new IQFM_EditModel;
}

public function show() {

	$posted = '';
	if (isset($_POST['posted'])) {
		$wp_inquiry_ajax = htmlspecialchars($_POST['posted']);
	}

	echo $this->setbackground_close_x();
	echo $this->set_default_tab();
	echo 
	'<div class="wrap nosubsub">
	<div class="icon32" id="icon-edit-pages"><br></div>
	<h2>お問い合わせ編集</h2>
	<div id="completed"></div>';
	if($posted === 'Y') {
		echo '<div class="updated"><p><strong>削除しました</strong></p></div>';
	}
	echo '<ul class="tabNav">'; 
	$form_ids = $this->model->get_forms_id();
	if ($form_ids === false) {
		echo '<li><a id="tabnew" href="#tab1">新規お問い合わせ</a></li>'; 
		echo '</ul><div class="tabContents"> ';
		$this->_show_tab_boby($this->model->get_max_form_id());
		echo '<script type="text/javascript">IQFM_editModel.createFristTab('.$this->model->get_max_form_id().', "'.IQFM_BaseModel::iqfm_get_url().'")</script>';
	} else {
		foreach ($form_ids as $form) {
			echo '<li><a id="tabtitle'.$form['form_id'].'" href="#tab'.$form['form_id'].'">'.$form['form_name'].'</a></li>'; 
		}
		echo '<li><a href="#tab'.($this->model->get_max_form_id()).'" onclick=\'IQFM_editModel.createTab(this,'.($this->model->get_max_form_id()).' ,"'.IQFM_BaseModel::iqfm_get_url().'")\' id="tabnew">+</a></li>';
		echo '</ul><div class="tabContents"> ';
		foreach ($form_ids as $form) {
			$this->_show_tab_boby($form['form_id']);
		}
		$this->_show_tab_boby($this->model->get_max_form_id());
	}
	echo '
	<!--/ .tabContents--></div>
	<!--/ .wrap nosubsub--></div>';
}

private function _show_tab_boby($cnt){
	global $wpdb;
	
	$result = $wpdb->get_row($wpdb->prepare("SELECT form_id, form_name, publish_flg,  publishstart_dt, publishend_dt, ga_conversion_input, ga_conversion_confirm, ga_conversion_finish FROM ".$wpdb->prefix."iqfm_inquiryform WHERE delete_flg = 0 and form_id = %d", $cnt), ARRAY_A);
	
	$mail_result = $wpdb->get_row($wpdb->prepare("SELECT send_flg, from_name, from_address, to_address, cc_address, bcc_address, to_subject, cc_subject, bcc_subject, to_item, cc_item, bcc_item FROM ".$wpdb->prefix."iqfm_inquiryform_mail WHERE delete_flg = 0 and form_id=%d", $cnt), ARRAY_A);
	$item["to"] = explode(",", $mail_result["to_item"]);
	$item["cc"] = explode(",", $mail_result["cc_item"]);
	$item["bcc"] = explode(",", $mail_result["bcc_item"]);
	echo '<div id="tab'.$cnt.'">
	<script type="text/javascript">
		jQuery(function($){
    		$("#startdt'.$cnt.'").datepicker();
    		$("#enddt'.$cnt.'").datepicker();
		});
	</script>
	<div class="wrap">
		<form method="post" id="tabform'.$cnt.'" action='.IQFM_BaseModel::iqfm_get_url().' onSubmit="return IQFM_editModel.deleteEdit()">
			<input type="hidden" name="posted" value="Y">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="inquiryname">フォームの名称</label></th>
					<td><input type="text" name="inquiryname'.$cnt.'" id="inquiryname'.$cnt.'" value="'.(($result['form_name'] == "")?"":$result['form_name']).'" class="regular-text code" /></td>
				</tr>
				<tr>
					<th scope="row">公開期間</th>
					<td><input type="checkbox" id="publishflg'.$cnt.'" name="publishflg" onclick="IQFM_editModel.changePublishTerm(this, '.$cnt.');" value="1" '.(($result['publish_flg'] == 1)?"checked=checked":"").'/> 設定する
					<div id="publishterm'.$cnt.'" style="visibility:'.(($result['publish_flg'] == 1)?"visible":"hidden").'"><input type="text"  name="startdt'.$cnt.'" id="startdt'.$cnt.'"  value="'.(($result['publish_flg'] == 1)? date("Y/m/d", strtotime($result['publishstart_dt'])):date("Y/m/d")).'"  />
						@<input type="text" name="starthh'.$cnt.'" id="starthh'.$cnt.'" maxlength="2" size="2" value="'.(($result['publishstart_dt'] == "")?"00":date("H", strtotime($result['publishstart_dt']))).'"  />
						:<input type="text" name="startmm'.$cnt.'" id="startmm'.$cnt.'" maxlength="2" size="2" value="'.(($result['publishstart_dt'] == "")?"00":date("i", strtotime($result['publishstart_dt']))).'"  />
						- <input type="text" name="enddt'.$cnt.'" id="enddt'.$cnt.'"  value="'.(($result['publish_flg'] == 1)? date("Y/m/d", strtotime($result['publishend_dt'])):date("Y/m/d")).'"  />
						@<input type="text" name="endhh'.$cnt.'" id="endhh'.$cnt.'" maxlength="2" size="2" value="'.(($result['publishend_dt'] == "")?"00":date("H", strtotime($result['publishend_dt']))).'"  />
						:<input type="text" name="endmm'.$cnt.'" id="endmm'.$cnt.'" maxlength="2" size="2" value="'.(($result['publishend_dt'] == "")?"00":date("i", strtotime($result['publishend_dt']))).'"  />
					</div>
					</td>
				</tr>
				<tr id="send'.$cnt.'">
					<td style="white-space:nowrap">
						<input type="button" id="formeditbtn'.$cnt.'" class="button-secondary action" value="フォームの編集" />
						<input id="maileditbtn'.$cnt.'" type="button" class="button-secondary action" value="管理者宛メールの編集" />
						<input id="ga-btn'.$cnt.'" type="button" class="button-secondary action" value="google analyticsでコンバージョンを設定する" />
					</td>
					<td>&nbsp&nbsp
						<input type="button" class="button-primary" onclick=IQFM_editModel.updatetab("'.IQFM_BaseModel::iqfm_get_url().'","'.$cnt.'") value="設定を更新する" />
						<input type="hidden" name="delete_edit" value="'.$cnt.'" />
						<input type="submit" class="button-primary" value="フォームを削除する" />
					</td>
			</table>
		</form>
	</div>
	<div id="formedit'.$cnt.'" class="lightbox_me_default">
		<div class="wrap nosubsub">
			<div class="icon32" id="icon-edit-pages"><br></div>
			<h2>フォームの編集</h2>';
			$this->_edit_form($cnt);
			echo '<a class="close close_x sprited" href="#">close</a>
		</div>
	</div>
	<div id="mailedit'.$cnt.'" class="lightbox_me_default">
		<script type="text/javascript">
		  $(function(){
    		$("#mailsend'.$cnt.'").click(function () {
    		  if( $("#mailsend'.$cnt.'").attr("checked") ) {
      	        $("#mail_contents'.$cnt.'").slideDown();
      		  } else {
      		    $("#mail_contents'.$cnt.'").slideUp();
      		  }
      		});
          });';
        if ($mail_result["send_flg"] == 1) {
          echo '$("#mail_contents'.$cnt.'").css("display", "block");';
        }
		echo'</script>
		<div class="wrap nosubsub">
			<div class="icon32" id="icon-edit-pages"><br></div>
			<h2>管理者宛メールの編集</h2>
			<form method="post" id="mailform'.$cnt.'" name="mailform'.$cnt.'"  action='.IQFM_BaseModel::iqfm_get_url().'&iqfm-formid='.$cnt.' >
			<table class="form-table">
				<tr valign="top">
					<th scope="row">メール送信設定</th>
					<td><input type="checkbox" name="mailsend" onclick="IQFM_mailModel.checkSendElement('.$cnt.', this)" id="mailsend'.$cnt.'" value="1" '.(($mail_result["send_flg"] == 1)?"checked=checked":"").' ><label for="mailsend'.$cnt.'"> 管理者宛メールを送信する</label></td>
				</tr>
			</table>
			<div id="mail_contents'.$cnt.'" style="display:none;">
			<table class="form-table">
				<tr id="to'.$cnt.'">
					<th scope="row">送信元:</th>
					<td>メールアドレス<br /><input type="text" name="from_address" id="fromname'.$cnt.'" value="'.(($mail_result["from_address"] == "")?"wordpress@".$_SERVER["SERVER_NAME"]:$mail_result["from_address"]).'" /></td><td>送信者名<br /><input type="text" name="from_name" id="from'.$cnt.'" value="'.(($mail_result["from_name"] == "")?"WordPress":$mail_result["from_name"]).'" /></td>
				</tr>
				<tr id="to'.$cnt.'">
					<th scope="row">to:</th>
					<td><input type="text" name="sendto" id="sendto'.$cnt.'" value="'.(($mail_result["to_address"] == "")?get_option('admin_email'):$mail_result["to_address"]).'" /></td>
				</tr>
				<tr id="cc'.$cnt.'">
					<th scope="row">cc:</th>
					<td><input type="text" name="sendcc" id="sendcc'.$cnt.'" value="'.(($mail_result["cc_address"] == "")?"":$mail_result["cc_address"]).'" /></td>
				</tr>
				<tr id="bcc'.$cnt.'">
					<th scope="row">bcc:</th>
					<td><input type="text" name="sendbcc" id="sendbcc'.$cnt.'" value="'.(($mail_result["bcc_address"] == "")?"":$mail_result["bcc_address"]).'" /></td>
				</tr>
				<tr id="subject'.$cnt.'">
					<th scope="row">件名：</th>
					<td>
						<input type="text" name="subjectto" id="subjectto'.$cnt.'" value="'.(($mail_result["to_subject"] == "")?"":$mail_result["to_subject"]).'" />
					</td>
					<td>
						<input type="button" id="subjectccadd'.$cnt.'" class="button-secondary" value="ccの件名を設定" onclick="IQFM_mailModel.addSubject('.$cnt.', \'cc\')"  /><input type="button" id="subjectbccadd'.$cnt.'" class="button-secondary" value="bccの件名を設定" onclick="IQFM_mailModel.addSubject('.$cnt.', \'bcc\')" />
					</td>
				</tr>';
					if ($mail_result["cc_subject"] != "") {
						echo '<tr id="subjectcctr'.$cnt.'"><th>cc宛の件名：</th><td><input type="text" name="subjectcc" id="subjectcc'.$cnt.'" value="'.$mail_result["cc_subject"].'" /><input type="button" id="subjectccdel'.$cnt.'" class="button-secondary" value="削除" onclick="IQFM_mailModel.removeSubject('.$cnt.', \'cc\')" /></td></tr>';
					}
					if ($mail_result["bcc_subject"] != "") {
						echo '<tr id="subjectbcctr'.$cnt.'"><th>bcc宛の件名：</th><td><input type="text" name="subjectbcc" id="subjectbcc'.$cnt.'" value="'.$mail_result["bcc_subject"].'" /><input type="button" id="subjectbccdel'.$cnt.'" class="button-secondary" value="削除" onclick="IQFM_mailModel.removeSubject('.$cnt.', \'bcc\')" /></td></tr>';
					}
				echo '
			</table>
			<br/>
			<br/>
			<table class="table-01" id="iqfm-table-'.$cnt.'">
				<tr>
					<td></td>
					<td>to</td>
					<td>cc</td>
					<td>bcc</td>
				</tr>
				<tr>
					<td>管理画面へのリンク</td>
					<td><input type="checkbox" name="toform1" id="toform1_'.$cnt.'" value="1" '.((in_array("toform1", $item["to"])===true)?"checked=checked":"").' ></td>
					<td><input type="checkbox" name="ccform1" id="ccform1_'.$cnt.'" value="1" '.((in_array("ccform1", $item["cc"])===true)?"checked=checked":"").' ></td>
					<td><input type="checkbox" name="bccform1" id="bccform1_'.$cnt.'" value="1" '.((in_array("bccform1", $item["bcc"])===true)?"checked=checked":"").' ></td>
				</tr>
				<!--<tr>
					<td>お問い合わせ削除のリンク</td>
					<td><input type="checkbox" name="toform2" id="toform2_'.$cnt.'" value="1" '.((in_array("toform2", $item["to"])===true)?"checked=checked":"").'/></td>
					<td><input type="checkbox" name="ccform2" id="ccform2_'.$cnt.'" value="1" '.((in_array("ccform2", $item["cc"])===true)?"checked=checked":"").'/></td>
					<td><input type="checkbox" name="bccform2" id="bccform2_'.$cnt.'" value="1" '.((in_array("bccform2", $item["bcc"])===true)?"checked=checked":"").'/></td>
				</tr>-->
				'.$this->_mail_component($cnt, $item).'
			</table>
			<br/>
			<input type="hidden" name="mail_edit" value="'.$cnt.'" />
			<input type="submit" id="mailsubmit'.$cnt.'" class="button-primary" value="保存" />
			<input type="button" id="mailpreview'.$cnt.'" class="button-secondary" value="to宛のメールをプレビュー" onclick="IQFM_mailModel.showPreview('.$cnt.', \''.IQFM_BaseModel::iqfm_get_url().'\', \'to\')" />
			<input type="button" id="mailpreview'.$cnt.'" class="button-secondary" value="cc宛のメールをプレビュー" onclick="IQFM_mailModel.showPreview('.$cnt.', \''.IQFM_BaseModel::iqfm_get_url().'\', \'cc\')" />
			<input type="button" id="mailpreview'.$cnt.'" class="button-secondary" value="bcc宛のメールをプレビュー" onclick="IQFM_mailModel.showPreview('.$cnt.', \''.IQFM_BaseModel::iqfm_get_url().'\', \'bcc\')" />
			</form>
			</div>
			<a class="close close_x sprited" href="#">close</a>
		</div>
	</div>
	<div id="ga-form'.$cnt.'" class="lightbox_me_default">
		<div class="wrap nosubsub">
			<div class="icon32" id="icon-edit-pages"><br></div>
			<h2>google analyticsのコンバージョン取得の設定</h2>
			<p class="elementupdated" style="display: none;"><strong class="firstChild lastChild">設定を更新しました</strong></p>
			<p>下記の入力フォームより、入力画面、確認画面、完了画面の仮想URLを設定してください</p>
			<table class="form-table" >
				<tr>
					<th>入力画面:</th>
					<td>/<input type="text" size="30" name="ga-input'.$cnt.'" id="ga-input'.$cnt.'" value="'.(($result['ga_conversion_input'] == "")?"":$result['ga_conversion_input']).'" class="regular-text code" /></td>
				</tr>
				<tr>
					<th>確認画面:</th>
					<td>/<input type="text" size="30" name="ga-confirm'.$cnt.'" id="ga-confirm'.$cnt.'" value="'.(($result['ga_conversion_confirm'] == "")?"":$result['ga_conversion_confirm']).'" class="regular-text code" /></td>
				</tr>
				<tr>
					<th>完了画面:</th>
					<td>/<input type="text" size="30" name="ga-finish'.$cnt.'" id="ga-finish'.$cnt.'" value="'.(($result['ga_conversion_finish'] == "")?"":$result['ga_conversion_finish']).'" class="regular-text code" /></td>
				</tr>
			</table>
			<p><input type="button" value="保存" class="button-primary" id="ga-submit'.$cnt.'" onclick="IQFM_editModel.updateGA(\''.IQFM_BaseModel::iqfm_get_url().'\',\''.$cnt.'\')"></p>
			<a class="close close_x sprited" href="#">close</a>
		</div>
		
		<p>google analyticsのタグの下記の場所に<b>iqfm_googleanalytics('.$cnt.');</b>を挿入してください</p>
		<p>お問い合わせページのみでiqfm_googleanalyticsが実行されるようにテーマ内で条件の切り分けを行ってください<br />下記の例ではinquiryというスラッグを設定したページのみiqfm_googleanalytics関数が実行されるように設定しています</p>
		<p><b>タグの挿入例</b></p>
		<div class="ga-node">
		&lt;script type="text/javascript"&gt<br />
			var _gaq = _gaq || [];<br />
			_gaq.push([\'_setAccount\', \'UA-XXXXX-X\']);<br />
			_gaq.push([\'_trackPageview\'<span style="background-color:yellow;"><b>&lt;?php&nbsp;if(is_page(\'inquiry\')) { iqfm_googleanalytics('.$cnt.'); } &nbsp;?&gt</b></span>]);<br /><br />
			(function() {<br />
			&nbsp;&nbsp;var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;<br />
			&nbsp;&nbsp;ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';<br />
  			&nbsp;&nbsp;(document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(ga);<br />
  			})();<br />
		&lt;/script&gt<br />
		</div>
		<p>※複数お問い合わせのコンバージョンを測定したい場合はif文等で、iqfm_googleanalytics関数を切り分けてください。</p>
	</div>
	<script type="text/javascript">
		$("#formeditbtn'.$cnt.'").click(function() {
			$("#formedit'.$cnt.'").lightbox_me({
				centered: true, 
				onLoad: function() {
					$("#formedit'.$cnt.'").find("input:first").focus();
				}
			});
			return false;
		});
		
		$("#maileditbtn'.$cnt.'").click(function() {
			$("#mailedit'.$cnt.'").lightbox_me({
				centered: true, 
				onLoad: function() {
					$("#mailedit'.$cnt.'").find("input:first").focus();
				}
			});
			return false;
		});
		
		$("#ga-btn'.$cnt.'").click(function() {
			$("#ga-form'.$cnt.'").lightbox_me({
				centered: true, 
				onLoad: function() {
					$("#ga-form'.$cnt.'").find("input:first").focus();
				}
			});
			return false;
		});
	</script>
	</div>';
}

private function _edit_form($form_id) {
	global $wpdb;

	$component = $this->model->get_element($form_id);
	$fields = $wpdb->get_results($wpdb->prepare('SELECT form_component_id,field_name,field_type FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0 and form_id=%d ORDER BY field_sort', $form_id), ARRAY_A);
	$max_field = $wpdb->get_results('SELECT max(form_component_id) as max FROM '.$wpdb->prefix.'iqfm_inquiryform_component WHERE delete_flg = 0', ARRAY_A);
	$max_field_id = ($max_field[0]['max'] + 1);

	echo '
	<script type="text/javascript">
	
		$(document).ready(function() {
			$("#dndtable'.$form_id.'").tableDnD(
				{onDrop: function(table, row) {
					setElementData('.$form_id.');
				}
			});
			setElementData('.$form_id.');
		});
	</script>
	<div class="addcomponent">
		<br />
		<table id="dndtable'.$form_id.'" class="table-01" style="width:340px">';
		foreach($component[1] as $val) {
			echo $val;
		}
		echo '</table>
		<br />
		<input type="button" class="button-secondary" onclick="addtrelement('.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" value="項目を追加" />
		<input type="button" class="button-secondary" onclick="IQFM_editElementModel.saveSortOrder('.$form_id.', \''.IQFM_BaseModel::iqfm_get_url().'\')" value="並び替える" />
		<br />
		<p>下記のタグをコピーして、ページまたは記事に貼り付けるとフォームが出来上がります。</>
		<p><input type="text" size="40" readonly="readonly" value="[inquiryform form_id='.$form_id.']"></p>
	</div>
	<div id="inquiry_component'.$form_id.'" class="inquiry_component">
	<br />
	'.iqfm_show_element_editor($form_id, $fields).'
	
	</div>
	';
}

private function _mail_component($form_id, $item) {
	global $wpdb;
	
	$result = $wpdb->get_results($wpdb->prepare("SELECT count(*) as cnt FROM ".$wpdb->prefix."iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d", $form_id));

	if ($result[0]->cnt == 0) {
		return "";
	} else {
		$results = $wpdb->get_results($wpdb->prepare("SELECT field_subject, field_type, field_name FROM ".$wpdb->prefix."iqfm_inquiryform_component WHERE new_flg = 0 and delete_flg = 0 and form_id=%d ORDER BY field_sort", $form_id), ARRAY_A);

		$html = "";
		foreach ($results as $result) {
			$html .= '<tr>
						<td>'.( $result['field_type'] === 'zip' ? '住所' : $result["field_subject"] ).'</td>
						<td><input type="checkbox" name="toform_'.$result["field_name"].'" id="toform_'.$form_id.'_'.$result["field_name"].'" value="1" '.((in_array("toform_".$result["field_name"], $item["to"])===true)?"checked=\"checked\"":"").' ></td>
						<td><input type="checkbox" name="ccform_'.$result["field_name"].'" id="ccform_'.$form_id.'_'.$result["field_name"].'" value="1" '.((in_array("ccform_".$result["field_name"], $item["cc"])===true)?"checked=\"checked\"":"").' ></td>
						<td><input type="checkbox" name="bccform_'.$result["field_name"].'" id="bccform_'.$form_id.'_'.$result["field_name"].'" value="1" '.((in_array("bccform_".$result["field_name"], $item["bcc"])===true)?"checked=\"checked\"":"").' ></td>
					</tr>';
		}
		return $html;
	}
}
}

