<?php print(isset($company_name) == true ? $company_name.' ' : ''); ?><?php print(isset($member_name) == true ? $member_name : ''); ?>様

ビジネスダイレクトからのお知らせ。

<?php if (isset($edit_flag) == true && $edit_flag == true) { ?>
ビジネスダイレクトが更新されました。
<?php } else { ?>
ビジネスダイレクトの投稿がありました。
<?php } ?>

<?php $zokusei = isset($zokusei) == true ? $zokusei : ''; if ($zokusei == 1) { ?>
売りたい
<?php } elseif ($zokusei == 2) { ?>
買いたい
<?php } elseif ($zokusei == 3) { ?>
貸したい
<?php } elseif ($zokusei == 4) { ?>
借りたい
<?php } ?>

<?php print(isset($title) == true ? $title : ''); ?>

<?php print(isset($doc) == true ? $doc : ''); ?>

<?php print((isset($tmp) == true && $tmp == true ) ? "
この投稿には添付ファイルがあります。
添付ファイルはIISにログインしてご確認ください。" : ''); ?>

https://kric.co.jp/iis/login

