<?php

if (array_key_exists('delete_edit', $_POST)) {
	require_once('modules/admin/action/edit/delete-edit.php');
}
if (array_key_exists('mail_edit', $_POST)) {
	require_once('modules/admin/action/edit/mail-edit.php');
}

if (array_key_exists('result_edit', $_POST)) {
	require_once('modules/admin/action/admin/result-edit.php');
}

if (array_key_exists('result_delete_id', $_POST)) {
	require_once('modules/admin/action/admin/result-delete.php');
}

if (array_key_exists('iqfm_config', $_POST)) {
	require_once('modules/admin/action/config/save.php');
}

require_once('modules/front/inquiry-short-code.php');
?>