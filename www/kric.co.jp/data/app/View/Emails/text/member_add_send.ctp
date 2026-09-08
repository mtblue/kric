<?php print(isset($company_name) == true ? $company_name.' ' : ''); ?><?php print(isset($member_name) == true ? $member_name : ''); ?>様

会員認証されました。

下記、URL/ID/パスワード よりログイン願います。

<?php print(isset($url) == true ? $url : ''); ?>

ID：<?php print(isset($id) == true ? $id : ''); ?>

パスワード：<?php print(isset($pass) == true ? $pass : ''); ?>

パスワードの変更は、IISサイト個別設定にて行ってください。

