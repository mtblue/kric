会員情報が<?php print $member_action; ?>されました。

会社名：
<?php print(isset($company_name) == true ? $company_name.' ' : ''); ?>

名前：
<?php print(isset($member_name) == true ? $member_name : ''); ?>

<?php print $member_action; ?>日時：
<?php echo date( "Y/m/d (D) H:i:s", time() ) ?>

