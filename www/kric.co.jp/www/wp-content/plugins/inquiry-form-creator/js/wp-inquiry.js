var IQFM_mailModel = {
  //件名のテキストボックスの状態管理
  subjectFlg: [
    {cc:false, bcc:false}
  ],
  
  //subjectFlgの状態変更を行う
  changeSubjectFlg: function(type, flg) {
    this.subjectFlg[0][type] = flg;
  },
  
  //件名のテキストボックスを追加する
  addSubject: function(formid, type) {
    var type = type;
	if(0 < $("#subject"+type+"tr"+formid).size()){
		this.changeSubjectFlg(type, true);
	}
	if (this.subjectFlg[0][type] == false) {
		$("#subject"+formid).after('<tr id="subject'+type+'tr'+formid+'"><th>'+type+'宛の件名：</th><td><input type="text" name="subject'+type+'" id="subject'+type+formid+'" value="" /><input type="button" id="subject'+type+'del'+formid+'" class="button-secondary" value="削除" onclick="IQFM_mailModel.removeSubject('+formid+', \''+type+'\')" /></td></tr>');
	}
	this.changeSubjectFlg(type, true);
  },
  
  //件名のテキストボックスを削除する
  removeSubject: function(formid, type) {
	$("#subject"+type+"tr"+formid).remove();
	this.changeSubjectFlg(type, false);
  },

  //ポップアップにhtmlを記述する
  winOpen: function(x,y,msg) {
	var Win1=window.open('','Subwin','scrollbars=1,resizable=1,width='+x+',height='+y+'');
	if(navigator.appVersion.charAt(0)>=3){Win1.focus()};
    Win1.document.clear();
	Win1.document.write("<html><head><meta http-equiv=Content-Type content='text/html; charset=utf-8'><style type='text/css'>div.block { border-style: inset; solid #990000;}</style><title>プレビュー</title></head>");
	Win1.document.write("<body>");
	Win1.document.write(msg);
	Win1.document.write("<p align=center><input type=button value='閉じる' onClick='window.close()'></p>");
	Win1.document.write("</body></html>");
	Win1.document.close();
  },

  //プレニューを表示する
  showPreview: function(tabNumber, uri, type) {
	$.get(uri,
		{form_id:tabNumber, wp_inquiry_ajax:true, wp_inquiry_ajaxtype:"get-formname"},
		function(data){
		var id = type+"form1";
		
			html = '<blockquote><b>件名</b><div class="block"><p>'+$("#subject"+type+tabNumber).val()+'</p></div><br /><b>本文</b><div class="block">';
			if (document.getElementById(type+"form1_"+tabNumber).checked){
				html += "<p>■以下のリンクをクリックすると管理画面が開きます<br /><a href="+uri+">"+uri+"</a></p>";
			}
			//お問い合わせの削除機能対応
			/*
			if (document.getElementById(type+"form2_"+tabNumber).checked){
				html += "<p>■以下のリンクをクリックするとこのお問い合わせが管理画面から削除されます<br /><a href="+uri+">お問い合わせを削除する</a></p>";
			}
			*/
			for (i=0;i<data.length;i++) {
				if (document.getElementById(type+"form_"+tabNumber+"_"+data[i].field_name).checked) {
					html += "<p>■"+data[i].field_subject+"<br /> __"+data[i].field_name+"__</p>";
				}
			}
			html += '</div></blockquote>';
			IQFM_mailModel.winOpen(700, 400, html);
		},'json');
  },
  
  //サンキューメールの項目を取得
  showThankyouMail: function(field_id, form_id, uri) {
    if ($('#user_mail'+field_id).attr('checked') === true) {
      $.get(uri,
	  {form_id:form_id, wp_inquiry_ajax:true, wp_inquiry_ajaxtype:'get-formname'},
	  function(data){
		var html = '<div>';
		for (i=0;i<data.length;i++) {
		  html += "<p>■"+data[i].field_subject+"<br /> __"+data[i].field_name+"__</p>";
	    }
	    html += '</div>';
	    $('#user_mail_body_header'+field_id).after(html);
	  },'json');
    } else {
      $('#user_mail_body_header'+field_id+'+div').remove();
    }
  },
  
  //サンキューメールのテスト送信
  sendTestThankyouMail: function(field_id, form_id, uri) {
    if(window.confirm('メールの送信先は「'+$('#test-address'+field_id).val()+'」でよろしいですか？')){
      $.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=send-user-test-mail',
	  {
	    form_id:form_id,
	    to:$('#test-address'+field_id).val(),
	    from_name:$('#user_mail_fromname'+field_id).val(),
	    from_address:$('#user_mail_from'+field_id).val(),
        subject:$('#user_mail_subject'+field_id).val(),
        body_header:$('#user_mail_body_header'+field_id).val(),
		body_footer:$('#user_mail_body_footer'+field_id).val()
	  },
	  function(){
	    alert('送信完了しました');
	  });
	}
	else{
	  return false;
	}

  },
  
  //送信項目が空であれば全項目にチェックを付ける
  checkSendElement: function(form_id, checkbox) {
    if($(checkbox).attr('checked') === true) {
      var flg = true
      var checkboxes = $('#iqfm-table-'+form_id+" input:checkbox");
      $.each(checkboxes, function(i, val){
        if ($(val).attr('checked') === true) {
          flg = false;
        }
      });
      if(flg === true) {
        $.each(checkboxes, function(i, val){
          $(val).attr('checked', 'checked');
        });
      }
    }
  }
}

var IQFM_editModel = {

  property: [
    {tabupdated:false}
  ],

  //編集タブを生成
  createTab: function(element, tabNumber, requestUri) {
	if (this.property[0].tabupdated == false) {
		element = $(element);
		element.text("新規お問い合わせ");
		$("#send"+tabNumber).html('<td><input type="button" class="button-primary" id="doaction'+tabNumber+'" name="doaction'+tabNumber+'" value="新規作成" onclick=IQFM_editModel.updatetab("'+requestUri+'","'+tabNumber+'")></td>');
	}
  },

  //最初のタブを生成
  createFristTab: function(tabNumber, requestUri){
   $("#send"+tabNumber).html('<td><input type="button" class="button-primary" id="doaction'+tabNumber+'" name="doaction'+tabNumber+'" value="新規作成" onclick=IQFM_editModel.updatetab("'+requestUri+'","'+tabNumber+'")></td>');
  },

  //タブを更新
  updatetab: function(uri, tabNumber) {
	if(document.getElementById("publishflg"+tabNumber).checked){
		var publish = 1;
	} else {
		var publish = 0;
	}
	
	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=create-form',
	{
		formid:tabNumber,
		inquiryname:$("#inquiryname"+tabNumber).val(),
		publishflg:publish,
		startdt:$("#startdt"+tabNumber).val(),
		starthh:$("#starthh"+tabNumber).val(),
		startmm:$("#startmm"+tabNumber).val(),
		enddt:$("#enddt"+tabNumber).val(),
		endhh:$("#endhh"+tabNumber).val(),
		endmm:$("#endmm"+tabNumber).val()
	},
	function(msg){
		IQFM_editModel._getUpdateData($("#inquiryname"+tabNumber).val() ,tabNumber, msg, uri);
		if (msg == 'insert') {
			addtrelement(tabNumber, uri);
		}
	});
  },

  //private:タブの更新完了後の表示項目をセット
  _getUpdateData: function(inquiryname, tabNumber, msg, uri) {
	if (msg == 'insert') {
		this.property[0].tabupdated = true;
		$("#tabnew").text(inquiryname);
		$("#completed").prepend('<div class="updated"><p><strong>設定を保存しました</strong></p></div>');
		$("#send"+tabNumber).html('<td style="white-space:nowrap"><input type="button" id="formeditbtn'+tabNumber+'" class="button-secondary action" value="フォームの編集" /><input id="maileditbtn'+tabNumber+'" type="button" class="button-secondary action" value="管理者宛メールの編集" /><input id="ga-btn'+tabNumber+'" type="button" class="button-secondary action" value="google analyticsでコンバージョンを設定する"  onclick="IQFM_editModel.updateGA(\''+uri+'\', '+tabNumber+')" /></td><td>&nbsp&nbsp<input type="hidden" value="'+tabNumber+'" name="delete_edit" /><input type="button" class="button-primary" value="設定を更新する" onclick="IQFM_editModel.updatetab(\''+uri+'\', '+tabNumber+')" /><input type="submit" class="button-primary" value="フォームを削除する" /></td>');
		$("#formeditbtn"+tabNumber).click(function() {
			$("#formedit"+tabNumber).lightbox_me({
				centered: true, 
				onLoad: function() {
					$("#formedit"+tabNumber).find("input:first").focus();
				}
			});
			return false;
		});
		
		$("#maileditbtn"+tabNumber).click(function() {
			$("#mailedit"+tabNumber).lightbox_me({
				centered: true, 
				onLoad: function() {
					$("#mailedit"+tabNumber).find("input:first").focus();
				}
			});	
			return false;
		});
		
		$("#ga-btn"+tabNumber).click(function() {
			$("#ga-form"+tabNumber).lightbox_me({
				centered: true, 
				onLoad: function() {
					$("#ga-form"+tabNumber).find("input:first").focus();
				}
			});
			return false;
		});
		
	} else {
		$("#tabtitle"+tabNumber).text(inquiryname);
		$("#completed").prepend('<div class="updated"><p><strong>設定を保存しました</strong></p></div>');
	}
  },
  
  //表示期間の表示、非表示の切り替え
  changePublishTerm: function(checkbox, tabNumber) {
	if(checkbox.checked) {
		$('#publishterm'+tabNumber).css('visibility', 'visible');
	} else {
		$('#publishterm'+tabNumber).css('visibility', 'hidden');
	}
  },

  //お問い合わせ削除のダイアログ
  deleteEdit: function() {
	if(window.confirm('お問い合わせを削除します。よろしいですか？')){
		return true;
	}
	else{
		return false;
	}
  },
  
  //アナリの仮想URLを設定
  updateGA: function(uri, form_id) {
    $.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=ga-conversion', 
    {
      form_id:form_id,
      gainput:$('#ga-input'+form_id).val(),
      gaconfirm:$('#ga-confirm'+form_id).val(),
      gafinish:$('#ga-finish'+form_id).val()
    },
    function(){
      $(".elementupdated").css('display', 'block');
    });
  }
}


var IQFM_editElementModel = {
  //セレクトボックスの値に応じて編集用の項目を切り替える
  displayComponentEdit: function(form_id, field_id) {
	var element = $('select.addcomponent'+form_id+'_'+field_id+' option:selected').val();
	element = element+'_'+field_id;
	jQuery("div.componentnode").css("display", "none");
	jQuery("#"+element).css("display", "block");
	jQuery("#"+element).data('field_id', field_id);
	jQuery('.elementupdated').css("display", "none");
  },
  
  //住所フォームを設定する
  createZip: function(fieldid) {
    //初期化
    $('[id*=zip_code_]').hide();
    $('[id*=zip_style_]').hide();
    $('[class*=zip_style_]').hide();
    //郵便番号
    var code = $('[name=address1_type_'+fieldid+']').val();

    $('#zip_code_'+code+'_'+fieldid).show();
    
    //住所
    code = $('[name=address2_style_'+fieldid+']:checked').val();
    if(code == 1) {
      $('#zip_style_'+code+'_'+fieldid).show();
    } else if(code == 2) {
      code = $('[name=address2_type_'+fieldid+']').val();
      $('#zip_style_2_'+code+'_'+fieldid).show();
      code = $('[name=address3_type_'+fieldid+']').val();
      $('.zip_style_3_'+code+'_'+fieldid).show();
    }
  },

  //要素の並び替え
  saveSortOrder: function(form_id, uri) {
	var element = $('#dndtable'+form_id).find('tr');
	jQuery.each(element, function(i, val) {
		
		jQuery.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=save-field-sort',
		{
			field_id:jQuery(val).attr('id'),
			field_sort:i
		},function(){
			jQuery(val).find('td.firstChild').text(i);
		});
	});
  },

  saveText: function(fieldid, formid, uri) {

	var subject = $("#text_attr_subject"+fieldid).val();
	var size = $("#text_attr_size"+fieldid).val();
	var attention_message = $('#attention-text'+fieldid).val();
	var maxlength = $("#text_attr_maxlength"+fieldid).val();
	var validate_text_req = false;
	var validate_text_len_min_val = null;
	var validate_text_len_max_val = null;
	var validate_text_zen = 0;
	var validate_text_han = 0;
	var validate_text_confirm = null;
	var validate_text_seikivalue = null;

	if ($("#validate_text_confirm"+fieldid).attr('checked') === true) {
		validate_text_confirm = true;
	}

	if ($("#validate_text_req"+fieldid).attr('checked') === true) {
		validate_text_req = true;
	}
	
	if ($("#validate_text_len"+fieldid).attr('checked') === true) {
		if ($("#validate_text_len_min"+fieldid).attr('checked') === true) {
			validate_text_len_min_val = $("#validate_text_len_min_val"+fieldid).val();
		}
		if ($("#validate_text_len_max"+fieldid).attr('checked') === true) {
			validate_text_len_max_val = $("#validate_text_len_max_val"+fieldid).val();
		}
	}
	
	if ($("#validate_text_zen"+fieldid).attr('checked') === true) {
		validate_text_zen = $("input[name=validate_text_zen"+fieldid+"]:checked").val();
		if (validate_text_zen == 'undefined') {
		  validate_text_zen = 0;
		}
	} else if ($("#validate_text_han"+fieldid).attr('checked') === true) {
		validate_text_han = $("input[name=validate_text_han"+fieldid+"]:checked").val();
		if (validate_text_zen == 'undefined') {
		  validate_text_han = 0;
		}
	}
	
	if ($("#validate_text_seiki"+fieldid).attr('checked') === true) {
		validate_text_seikivalue = $("#validate_text_seikivalue"+fieldid).val();
	}
	
	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/text',
	{
		'fieldid':fieldid,
		'formid':formid,
		'subject':subject,
		'size':size,
		'maxlength':maxlength,
		'attention_message':attention_message,
		'validate_text_confirm':validate_text_confirm,
		'validate_text_req':validate_text_req,
		'validate_text_len_min_val':validate_text_len_min_val,
		'validate_text_len_max_val':validate_text_len_max_val,
		'validate_text_zen':validate_text_zen,
		'validate_text_han':validate_text_han,
		'validate_text_seikivalue':validate_text_seikivalue
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
		IQFM_editElementModel._showUpdateMessege();
	});
  },

  saveTextArea: function(fieldid, formid, uri) {

	var subject = $("#textarea_attr_subject"+fieldid).val();
	var cols = $("#textarea_attr_cols"+fieldid).val();
	var attention_message = $('#attention-textarea'+fieldid).val();
	var rows = $("#textarea_attr_rows"+fieldid).val();
	var validate_textarea_req = false;
	var validate_textarea_len_min_val = null;
	var validate_textarea_len_max_val = null;
	var validate_textarea_zen = 0;
	var validate_textarea_han = 0;
	var validate_textarea_seikivalue = null;

	if ($("#validate_textarea_req"+fieldid).attr('checked') === true) {
		validate_textarea_req = true;
	}
	
	if ($("#validate_textarea_len"+fieldid).attr('checked') === true) {
		if ($("#validate_textarea_len_min"+fieldid).attr('checked') === true) {
			validate_textarea_len_min_val = $("#validate_textarea_len_min_val"+fieldid).val();
		}
		if ($("#validate_textarea_len_max"+fieldid).attr('checked') === true) {
			validate_textarea_len_max_val = $("#validate_textarea_len_max_val"+fieldid).val();
		}
	}
	
	if ($("#validate_textarea_zen"+fieldid).attr('checked') === true) {
		validate_textarea_zen = $("input[name=validate_textarea_zen"+fieldid+"]:checked").val();
		if (validate_textarea_zen == 'undefined') {
		  validate_textarea_zen = 0;
		}
	} else if ($("#validate_textarea_han"+fieldid).attr('checked') === true) {
		validate_textarea_han = $("input[name=validate_textarea_han"+fieldid+"]:checked").val();
		if (validate_textarea_zen == 'undefined') {
		  validate_textarea_han = 0;
		}
	}
	
	if ($("#validate_textarea_seiki"+fieldid).attr('checked') === true) {
		validate_textarea_seikivalue = $("#validate_textarea_seikivalue"+formid).val();
	}

	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/textarea',
	{
		'fieldid':fieldid,
		'formid':formid,
		'subject':subject,
		'cols':cols,
		'rows':rows,
		'attention_message':attention_message,
		'validate_textarea_req':validate_textarea_req,
		'validate_textarea_len_min_val':validate_textarea_len_min_val,
		'validate_textarea_len_max_val':validate_textarea_len_max_val,
		'validate_textarea_zen':validate_textarea_zen,
		'validate_textarea_han':validate_textarea_han,
		'validate_textarea_seikivalue':validate_textarea_seikivalue
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
		IQFM_editElementModel._showUpdateMessege();
	});
  },

  saveSelectBox: function(fieldid, formid, uri) {

	var subject = $("#selectbox_attr_subject"+fieldid).val();
	var attention_message = $('#attention-selectbox'+fieldid).val();
	var validate_selectbox_req = false;
	var selectbox_option = $('#selectbox-option'+fieldid).val();


	if ($("#validate_selectbox_req"+fieldid).attr('checked') === true) {
		validate_selectbox_req = true;
	}

	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/selectbox',
	{
		'fieldid':fieldid,
		'formid':formid,
		'subject':subject,
		'attention_message':attention_message,
		'validate_selectbox_req':validate_selectbox_req,
		'selectbox_option':selectbox_option
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
		IQFM_editElementModel._showUpdateMessege();
	});
  },

  saveCheckBox: function(fieldid, formid, uri) {

	var subject = $("#checkbox_attr_subject"+fieldid).val();
	var attention_message = $('#attention-checkbox'+fieldid).val();
	var validate_checkbox_req = false;
	var checkbox_option = $('#checkbox-option'+fieldid).val();

	if ($("#validate_checkbox_req"+fieldid).attr('checked') === true) {
		validate_checkbox_req = true;
	}

	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/checkbox',
	{
		'fieldid':fieldid,
		'formid':formid,
		'subject':subject,
		'attention_message':attention_message,
		'validate_checkbox_req':validate_checkbox_req,
		'checkbox_option':checkbox_option
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
		IQFM_editElementModel._showUpdateMessege();
	});
  },

  saveRadio: function(fieldid, formid, uri) {

	var subject = null;
	var attention_message = null;
	var validate_radio_req = false;
	var radio_option = null;

	subject = $("#radio_attr_subject"+fieldid).val();
	
	attention_message =  $('#attention-radio'+fieldid).val();
	
	radio_option = $('#radio-option'+fieldid).val();

	if ($("#validate_radio_req"+fieldid).attr('checked') === true) {
		validate_radio_req = true;
	}

	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/radio',
	{
		'fieldid':fieldid,
		'formid':formid,
		'subject':subject,
		'attention_message':attention_message,
		'validate_radio_req':validate_radio_req,
		'radio_option':radio_option
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
		IQFM_editElementModel._showUpdateMessege();
	});
  },
  
  saveEmail: function(fieldid, formid, uri) {

	var subject = $("#email_attr_subject"+fieldid).val();
	var size = $("#email_attr_size"+fieldid).val();
	var attention_message = $('#attention-email'+fieldid).val();
	var maxlength = $("#email_attr_maxlength"+fieldid).val();
	var validate_email_option = $('#validate_email_option'+fieldid).val();
	var validate_email_confirm = false;
	var validate_email_req = false;
	var validate_email_han = false;
	var validate_email_split = false;
	var validate_email_split_rfc = false;
	var validate_email_domain = false;
	var validate_email_mx = false;
	var user_mail_flg = false
	var user_mail_from_name = $("#user_mail_fromname"+fieldid).val();
	var user_mail_from_address = $("#user_mail_from"+fieldid).val();
	var user_mail_subject = $("#user_mail_subject"+fieldid).val();
	var user_mail_body_header = $("#user_mail_body_header"+fieldid).val();
	var user_mail_body_footer = $("#user_mail_body_footer"+fieldid).val();
	
	if ($("#user_mail"+fieldid).attr('checked') === true) {
		user_mail_flg = true;
	}

	if ($("#validate_email_req"+fieldid).attr('checked') === true) {
		validate_email_req = true;
	}
	
	if ($('#validate_email_confirm'+fieldid).attr('checked') === true) {
		validate_email_confirm = true;
	}
	
	if ($("#validate_email_han"+fieldid).attr('checked') === true) {
		validate_email_han = true;
	}
	
	if ($("#validate_email_split"+fieldid).attr('checked') === true) {
		validate_email_split = true;
	}
	
	if ($("#validate_email_split_rfc"+fieldid).attr('checked') === true) {
		validate_email_split_rfc = true;
	}
	
	if ($("#validate_email_domain"+fieldid).attr('checked') === true) {
		validate_email_domain = true;
	}
	
	if ($("#validate_email_mx"+fieldid).attr('checked') === true) {
		validate_email_mx = true;
	}

	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/email',
	{
		'fieldid':fieldid,
		'formid':formid,
		'validate_email_req':validate_email_req,
		'subject':subject,
		'size':size,
		'maxlength':maxlength,
		'attention_message':attention_message,
		'validate_email_confirm':validate_email_confirm,
		'validate_email_han':validate_email_han,
		'validate_email_split':validate_email_split,
		'validate_email_split_rfc':validate_email_split_rfc,
		'validate_email_domain':validate_email_domain,
		'validate_email_mx':validate_email_mx,
		'validate_email_option':validate_email_option,
		'user_mail_flg':user_mail_flg,
		'user_mail_from_name':user_mail_from_name,
		'user_mail_from_address':user_mail_from_address,
		'user_mail_subject':user_mail_subject,
		'user_mail_body_header':user_mail_body_header,
		'user_mail_body_footer':user_mail_body_footer
		
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
		IQFM_editElementModel._showUpdateMessege();
	});
  },
  
  saveTel: function(fieldid, formid, uri) {
    var subject = $("#tel_attr_subject"+fieldid).val();
    var tel_type = 0;
    var validate_tel_req = false;
    var validate_tel_number = false;
    var validate_tel_phone = false;
    var validate_tel_mobile = false;
    var validate_tel_option = $('#validate_tel_option'+fieldid).val();
    var attention_message = $('#attention-tel'+fieldid).val();
    
    if ($("#tel_type1_"+fieldid).attr('checked') === true) {
      tel_type = 1;
    }
    
    if ($("#validate_tel_req"+fieldid).attr('checked') === true) {
	  validate_tel_req = true;
	}
	
	if ($("#validate_tel_number"+fieldid).attr('checked') === true) {
	  validate_tel_number = true;
	}
	
	if ($("#validate_tel_phone"+fieldid).attr('checked') === true) {
	  validate_tel_phone = true;
	}
	
	if ($("#validate_tel_mobile"+fieldid).attr('checked') === true) {
	  validate_tel_mobile = true;
	}
	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/tel',
	{
		'fieldid':fieldid,
		'formid':formid,
		'tel_type':tel_type,
		'validate_tel_req':validate_tel_req,
		'subject':subject,
		'attention_message':attention_message,
		'validate_tel_number':validate_tel_number,
		'validate_tel_phone':validate_tel_phone,
		'validate_tel_mobile':validate_tel_mobile,
		'validate_tel_option':validate_tel_option
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
        IQFM_editElementModel._showUpdateMessege();
	});
  },
  
  saveZip: function( fieldid, formid, uri ) {
    var zip = {
      code_type     : $( 'select[name=address1_type_'+fieldid+'] option:selected' ).val(),
      auto_complete : $( 'input:checkbox[name=zip_auto_complete_'+fieldid+']:checked' ).val(),
      gl_jusyo_type : $( 'input:radio[name=address2_style_'+fieldid+']:checked' ).val(),
      ken_type      : '0',
      jusyo_type    : '0',
      attr          : $( 'tr',$('#zip-form'+fieldid) ).filter( function (idx) {
                        return $(this).css('display') == 'table-row';
                      } )
    };
    
    zip.data = {
      title:'',
      req  :'',
      size :'',
      max  :'',
      msg  :''
    };
    
    zip.auto_complete = zip.code_type === '0' ? '0' : zip.auto_complete;
    
    if ( zip.gl_jusyo_type === '2' ) {
      zip.ken_type   = $( 'select[name=address2_type_'+fieldid+'] option:selected' ).val();
      zip.jusyo_type = $( 'select[name=address3_type_'+fieldid+'] option:selected' ).val();
    }
    
    for (var i in zip) {
      zip[i] = zip[i] === undefined ? '0' : zip[i];
    }
    
    var length = zip.attr.length;
    for ( i=1 ; i< length ; i++ ) {
      zip.data.title += $(zip.attr[i]).find('input').eq(0).attr('name')+':'+$(zip.attr[i]).find('input').eq(0).val()+"\n";
      zip.data.msg  += $(zip.attr[i]).find('input').eq(1).attr('name')+':'+$(zip.attr[i]).find('input').eq(1).val()+"\n";
      zip.data.req   += $(zip.attr[i]).find('input').eq(2).attr('name')+':'+ ( $(zip.attr[i]).find('input:checkbox:checked').eq(0).val() === undefined ? '0' : $(zip.attr[i]).find('input:checkbox:checked').eq(0).val() )+"\n";
      
      if( zip.code_type === '2' && i === 1 ) {
        zip.data.size += $(zip.attr[i]).find('input').eq(3).attr('name')+':'+$(zip.attr[i]).find('input').eq(3).val()+'_'+$(zip.attr[i]).find('input').eq(4).val()+"\n";
        zip.data.max  += $(zip.attr[i]).find('input').eq(5).attr('name')+':'+$(zip.attr[i]).find('input').eq(5).val()+'_'+$(zip.attr[i]).find('input').eq(6).val()+"\n";
      } else {
        zip.data.size += ( $(zip.attr[i]).find('input').eq(3).attr('name') === undefined ? '' : $(zip.attr[i]).find('input').eq(3).attr('name') )+':'+( $(zip.attr[i]).find('input').eq(3).val() === undefined ? '' : $(zip.attr[i]).find('input').eq(3).val() )+"\n";
        zip.data.max  += ( $(zip.attr[i]).find('input').eq(4).attr('name') === undefined ? '' : $(zip.attr[i]).find('input').eq(4).attr('name') )+':'+( $(zip.attr[i]).find('input').eq(4).val() === undefined ? '' : $(zip.attr[i]).find('input').eq(4).val() )+"\n";
      }
    }
    $.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/zip',
	{
		'fieldid'       :fieldid,
		'formid'        :formid,
		'code_type'     :zip.code_type,
		'auto_complete' :zip.auto_complete,
		'gl_jusyo_type' :zip.gl_jusyo_type,
		'ken_type'      :zip.ken_type,
		'jusyo_type'    :zip.jusyo_type,
		'title'         :zip.data.title,
		'req'           :zip.data.req,
		'size'          :zip.data.size,
		'max'           :zip.data.max,
		'msg'           :zip.data.msg,
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, '住所');
        IQFM_editElementModel._showUpdateMessege();
	});
  },
  
  saveFile: function( fieldid, formid, uri ) {
    var subject = $("#file_attr_subject"+fieldid).val(),
        validate_file_req = $("#validate_file_req"+fieldid).attr('checked') === true ? true : false,
        yes_list = $("#validate_file_yes"+fieldid).attr('checked') === true ? $("#yes_list"+fieldid).val() : false,
        no_list = $("#validate_file_no"+fieldid).attr('checked') === true ? $("#no_list"+fieldid).val() : false,
        attention_message = $('#attention-file'+fieldid).val();
        
	$.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=element/save/file',
	{
		'fieldid':fieldid,
		'formid':formid,
		'validate_file_req':validate_file_req,
		'subject':subject,
		'attention_message':attention_message,
		'yes_list':yes_list,
		'no_list':no_list
	},function(msg){
		IQFM_editElementModel._setinquirysubject(formid, fieldid, subject);
        IQFM_editElementModel._showUpdateMessege();
	});
  },

  _setinquirysubject: function(formid, fieldid, subject) {
	jQuery('#'+fieldid+' td:nth-child(2)').text(subject);
  },
  
  _showUpdateMessege: function() {
    $(".elementupdated").show();
    setTimeout(function() { 
        $(".elementupdated").hide(1000);
        clearTimeout( this );
	}, 3000);
  },

  updateInquiryResult: function(formid,resultid,uri) {
	document.getElementById("hiddenresult"+formid).value = resultid;
	$.get(uri,
		{result_id:resultid, wp_inquiry_ajax:true, wp_inquiry_ajaxtype:"get-result-data"},
		function(data){
			$("#resultStatus"+formid).val(data[0].status);
			$("#result_message"+formid).val(data[0].message);
	},"json");
  },

  deleteInquiryResult: function(formid,resultid) {
	if(window.confirm("お問い合わせを削除します。よろしいですか？")){
		jQuery("#hiddendleteresult"+formid).val(resultid);
		jQuery("#delresult"+formid).submit();
		return true;
	}else{
		return false;
	}
  },

  deleteElement: function(field_id ,uri) {
    if(window.confirm("この項目を削除します。よろしいですか？")){
		jQuery.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=delete-element',
		{
			field_id:field_id
		},function(){
			$('#'+field_id).remove();
		});
		return true;
	}else{
		return false;
	}
  }
}


function addtrelement(form_id, uri) {
  jQuery.post(uri+'&wp_inquiry_ajax=true&wp_inquiry_ajaxtype=create-new-field',
  {
    form_id:form_id
  },function(data){

    jQuery.each(data, function(i, val) {
      if (i == 0) {
        jQuery('#dndtable'+form_id).append(data[i]);
      } else {
        jQuery('#inquiry_component'+form_id).append(data[i]);
      }
    });
    setElementData(form_id);
    jQuery('#dndtable'+form_id).tableDnD(
      {onDrop: function(table, row) {
        setElementData(form_id);
      }
    });
  },'json');
}

function setElementData(form_id) {
  var element = $('#dndtable'+form_id).find('tr');
  jQuery.each(element, function(i, val) {
    jQuery.data(val, "sort", i);
  });
}

function unicodeEscape(str) {
  var code, pref = {1: '\\u000', 2: '\\u00', 3: '\\u0', 4: '\\u'};
  return str.replace(/\W/g, function(c) {
    return pref[(code = c.charCodeAt(0).toString(16)).length] + code;
  });
}
