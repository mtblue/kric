<?php print(isset($company_name) == true ? $company_name.' ' : ''); ?><?php print(isset($new_member_name) == true ? $new_member_name : ''); ?>様

<?php print(isset($member_name) == true ? $member_name : '会社管理者'); ?>様より、IISサイトへの招待がされました。
下記URLより、会員認証をお願いいたします。

<?php print(isset($url) == true ? $url : ''); ?>

