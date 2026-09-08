<?php
function iqfm_install(){
	global $wpdb;

	if (!get_option('iqfm_installed') ) {
		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."iqfm_inquiryform` (
					`form_id` int(5) NOT NULL AUTO_INCREMENT,
					`form_name` varchar(128) NOT NULL,
					`publish_flg` int(1) NOT NULL,
					`publishstart_dt` datetime DEFAULT NULL,
					`publishend_dt` datetime DEFAULT NULL,
					`update_dt` datetime NOT NULL,
					`regist_dt` datetime NOT NULL,
					`delete_flg` int(1) NOT NULL DEFAULT '0',
					 PRIMARY KEY (`form_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."iqfm_inquiryform_component` (
					`form_component_id` int(5) NOT NULL AUTO_INCREMENT,
					`form_id` int(5) NOT NULL,
					`field_name` varchar(32) NOT NULL,
					`field_type` varchar(32) NOT NULL,
					`field_html` varchar(256) NOT NULL,
					`field_option` text NOT NULL,
					`field_sort` int(5) NOT NULL,
					`field_subject` varchar(128) NOT NULL,
					`attention_message` text,
					`field_validation` text,
					`new_flg` int(1) NOT NULL DEFAULT '1',
					`update_dt` datetime NOT NULL,
					`regist_dt` datetime NOT NULL,
					`delete_flg` int(1) NOT NULL DEFAULT '0',
					 PRIMARY KEY (`form_component_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."iqfm_inquiryform_mail` (
					`form_mail_id` int(5) NOT NULL AUTO_INCREMENT,
					`form_id` int(5) NOT NULL,
					`send_flg` int(1) NOT NULL,
					`to_address` varchar(512) NOT NULL,
					`cc_address` varchar(512) DEFAULT NULL,
					`bcc_address` varchar(512) DEFAULT NULL,
					`to_subject` varchar(256) DEFAULT NULL,
					`cc_subject` varchar(256) DEFAULT NULL,
					`bcc_subject` varchar(256) DEFAULT NULL,
					`to_item` text NOT NULL,
					`cc_item` text,
					`bcc_item` text,
					`to_body` text NOT NULL,
					`cc_body` text,
					`bcc_body` text,
					`regist_dt` datetime NOT NULL,
					`update_dt` datetime NOT NULL,
					`delete_flg` int(1) NOT NULL DEFAULT '0',
					 PRIMARY KEY (`form_mail_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."iqfm_inquiryform_result` (
					`form_result_id` int(5) NOT NULL AUTO_INCREMENT,
					`form_id` int(5) NOT NULL,
					`result_id` int(5) NOT NULL,
					`status` int(1) NOT NULL DEFAULT '0',
					`message` text,
					`update_dt` datetime NOT NULL,
					`regist_dt` datetime NOT NULL,
					`delete_flg` int(1) NOT NULL DEFAULT '0',
					 PRIMARY KEY (`form_result_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."iqfm_inquiryform_result_detail` (
					`form_result_detail_id` int(5) NOT NULL AUTO_INCREMENT,
					`form_id` int(5) NOT NULL,
					`result_id` int(5) NOT NULL,
					`field_name` varchar(64) NOT NULL,
					`field_data` text,
					`update_dt` datetime NOT NULL,
					`regist_dt` datetime NOT NULL,
					`delete_flg` int(1) NOT NULL DEFAULT '0',
					 PRIMARY KEY (`form_result_detail_id`)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

		$wpdb->query("CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."iqfm_resultid_sequence` (
					`id` int(11) NOT NULL
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

		$wpdb->query("INSERT INTO ".$wpdb->prefix."iqfm_resultid_sequence VALUES (0);");
		
		update_option('iqfm_installed', 1);
	}
	
	if (!get_option('iqfm_installed_0211') ) {
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."iqfm_inquiryform` ADD 
					`ga_conversion_input` TEXT NULL AFTER `publishend_dt`, ADD 
					`ga_conversion_confirm` TEXT NULL AFTER `ga_conversion_input`, ADD 
					`ga_conversion_finish` TEXT NULL AFTER `ga_conversion_confirm`;");
		update_option('iqfm_installed_0211', 1);
	}
	
	if ( !get_option('iqfm_installed_0212') ) {
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."iqfm_inquiryform_mail` ADD 
					`from_name` VARCHAR(512) NULL AFTER `send_flg`, ADD 
					`from_address` VARCHAR(512) NULL AFTER `from_name`;");
		update_option('iqfm_installed_0212', 1);
	}
	
	if ( !get_option('iqfm_installed_03') ) {
		$wpdb->query("ALTER TABLE `".$wpdb->prefix."iqfm_inquiryform_component` ADD 
					`user_mail_flg` INT(1) NULL AFTER `new_flg`, ADD 
					`user_mail_from_name` VARCHAR(256) NULL AFTER `user_mail_flg`, ADD 
					`user_mail_from_address` VARCHAR(512) NULL AFTER `user_mail_from_name`, ADD 
					`user_mail_subject` VARCHAR(256) NULL AFTER `user_mail_from_address`, ADD 
					`user_mail_body_header` TEXT NULL AFTER `user_mail_subject`, ADD 
					`user_mail_body_footer` TEXT NULL AFTER `user_mail_body_header`;");
		update_option('iqfm_installed_03', 1);
	}
	
	if ( !get_option('iqfm_installed_032') ) {
		update_option( 'iqfm_edit_level', 8 );
		update_option('iqfm_installed_032', 1);
	}
}
?>