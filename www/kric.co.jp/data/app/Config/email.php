<?php
/**
 * This is email configuration file.
 *
 * Use it to configure email transports of Cake.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 2.0.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 *
 * Email configuration class.
 * You can specify multiple configurations for production, development and testing.
 *
 * transport => The name of a supported transport; valid options are as follows:
 *		Mail 		- Send using PHP mail function
 *		Smtp		- Send using SMTP
 *		Debug		- Do not send the email, just return the result
 *
 * You can add custom transports (or override existing transports) by adding the
 * appropriate file to app/Network/Email. Transports should be named 'YourTransport.php',
 * where 'Your' is the name of the transport.
 *
 * from =>
 * The origin email. See CakeEmail::from() about the valid values
 *
 */
class EmailConfig {

	public function __construct() {
		// 本番以外ではメールを送信しない。
		// Debug transport は実送信せず内容をログに出すだけなので、
		// Docker・STG・開発環境のいずれでも会員へメールが飛ぶことがなくなる。
		//
		// 2026-10-18: STG の侵害対応で全環境を見直した際、アプリが使う9設定が
		// すべて 'Smtp'（localhost:25）で、php.ini の sendmail_path 無効化を
		// 迂回できる状態だったことが判明したため、対象をプロファイル全件に広げた。
		$isProduction = (getenv('APP_ENV') !== 'docker')
			&& defined('DEVELOPMENT_FLAG') && DEVELOPMENT_FLAG === '';
		if (!$isProduction) {
			foreach (get_object_vars($this) as $key => $val) {
				if (is_array($val) && isset($val['transport'])) {
					$this->{$key}['transport'] = 'Debug';
				}
			}
		}
	}

	public $default = array(
		'transport' => 'Mail',
		'from' => 'you@localhost',
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

	public $smtp = array(
		'transport' => 'Smtp',
		'from' => array('site@localhost' => 'My Site'),
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'log' => false,
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

	public $fast = array(
		'from' => 'you@localhost',
		'sender' => null,
		'to' => null,
		'cc' => null,
		'bcc' => null,
		'replyTo' => null,
		'readReceipt' => null,
		'returnPath' => null,
		'messageId' => true,
		'subject' => null,
		'message' => null,
		'headers' => null,
		'viewRender' => null,
		'template' => false,
		'layout' => false,
		'viewVars' => null,
		'attachments' => null,
		'emailFormat' => null,
		'transport' => 'Smtp',
		'host' => 'localhost',
		'port' => 25,
		'timeout' => 30,
		'username' => 'user',
		'password' => 'secret',
		'client' => null,
		'log' => true,
		//'charset' => 'utf-8',
		//'headerCharset' => 'utf-8',
	);

	public $member_new_send = array(
		'transport' =>		'Smtp',
		'template' =>		'member_new_send',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS への招待メールです',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

	public $member_add_send = array(
		'transport' =>		'Smtp',
		'template' =>		'member_add_send',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS への招待が認証されました',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

	public $member_edit_send = array(
		'transport' =>		'Smtp',
		'template' =>		'member_edit_send',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS 会員情報が更新されました',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);


	public $reminder_send = array(
		'transport' =>		'Smtp',
		'template' =>		'reminder_send',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS パスワード再発行メールです',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

	public $reminder_auth = array(
		'transport' =>		'Smtp',
		'template' =>		'reminder_auth',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS パスワード再発行完了メールです',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

	public $information_send = array(
		'transport' =>		'Smtp',
		'template' =>		'information_send',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS 事務局からのお知らせ',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

	public $bd_send = array(
		'transport' =>		'Smtp',
		'template' =>		'bd_send',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS ビジネスダイレクトのお知らせ',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

	public $company_edit_send = array(
		'transport' =>		'Smtp',
		'template' =>		'company_edit_send',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS 会社情報が更新されました',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

	public $member_first_login = array(
		'transport' =>		'Smtp',
		'template' =>		'member_first_login',
		'emailFormat' =>	'text',
		'subject' =>		'KRIC IIS 初回ログイン通知',
		'from' =>			array('info@kric.co.jp' => 'KRIC'),
		'charset' =>		'ISO-2022-JP',
		'headerCharset' =>	'jis',
        'host' => 			'kric.sakura.ne.jp',
        'port' => 			25,
        'username' => 		'info@kric.co.jp',
        'password' => 		'vu6syutusm',
	);

//
//	public $member_new_send = array(
//		'transport' =>		'Mail',
//		'template' =>		'member_new_send',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS への招待メールです',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//	public $member_add_send = array(
//		'transport' =>		'Mail',
//		'template' =>		'member_add_send',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS への招待が認証されました',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//	public $member_edit_send = array(
//		'transport' =>		'Mail',
//		'template' =>		'member_edit_send',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS 会員情報が更新されました',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//
//	public $reminder_send = array(
//		'transport' =>		'Mail',
//		'template' =>		'reminder_send',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS パスワード再発行メールです',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//	public $reminder_auth = array(
//		'transport' =>		'Mail',
//		'template' =>		'reminder_auth',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS パスワード再発行完了メールです',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//	public $information_send = array(
//		'transport' =>		'Mail',
//		'template' =>		'information_send',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS 事務局からのお知らせ',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//	public $bd_send = array(
//		'transport' =>		'Mail',
//		'template' =>		'bd_send',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS ビジネスダイレクトのお知らせ',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//	public $company_edit_send = array(
//		'transport' =>		'Mail',
//		'template' =>		'company_edit_send',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS 会社情報が更新されました',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//
//	public $member_first_login = array(
//		'transport' =>		'Mail',
//		'template' =>		'member_first_login',
//		'emailFormat' =>	'text',
//		'subject' =>		'KRIC IIS 初回ログイン通知',
//		'from' =>			array('info@kric.co.jp' => 'KRIC'),
//		'charset' =>		'ISO-2022-JP',
//		'headerCharset' =>	'jis',
//	);
//



}
