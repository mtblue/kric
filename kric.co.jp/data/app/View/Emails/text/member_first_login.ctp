最初のログインが行われました。

会社名：
<?php print(isset($company_name) == true ? $company_name.'' : ''); ?>

名前：
<?php print(isset($member_name) == true ? $member_name.'' : ''); ?>


ログイン日時：
<?php echo date( "Y/m/d (D) H:i:s", time() ) ?>


