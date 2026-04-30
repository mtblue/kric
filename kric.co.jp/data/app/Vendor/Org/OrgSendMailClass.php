<?php
/**
 *
 * Program_Name        :  メール送信処理
 * File_Name           :  OrgSendMailClass.php
 * Producter           :  nakamoto
 * Programming_Date    :  2009/04/27
 * Comment             :  ※送信直前にオブジェクトを生成してください。
 *						  関数呼出：require_once('OrgSendMailClass.php');
 *									Send_mail(
 *										送信先,
 *										送信元,
 *										送信者名,
 *										件名,
 *										内容,
 *										Return-Path/Errors-To アドレス
 *										Bccアドレス,　※複数の場合「,」区切り
 *										添付ファイル相対パス　※複数の場合「,」区切り
 *									);
 *						  出力情報：false：送信エラー
 *									true：送信正常終了
 * History:
 * Date           Name               Title
 * 2009/04/27     nakamoto           new
 * 2012/06/05     nakamoto           edit mb_internal_encoding add
 *
*/

class OrgSendMail {

	public	$to;
	public	$from;
	public	$from_name;
	public	$subject;
	public	$sendCode;
	public	$message;
	public	$encode_message;
	public	$systemRootMail;
	public	$bccMail;
	public	$attachment;
	public	$typeList;

	public	$Return_Path;
	public	$Errors_To;

	public	$messageId;
	public	$method;
	public	$sendmailPath;

/**-----------------------------------------------
 * Function_Name       :  OrgSendMail()
 * Title               :  コンストラクタ
 * Parameter           :  
 * return value        :  
 * Comment             :  
*/
	function OrgSendMail() {

		// 環境設定
		if (DEVELOPMENT_FLAG == 'DOCKER') {
			// Docker環境：メール送信無効（実際には送信しない）
			$this->method = 0;

		} elseif (DEVELOPMENT_FLAG == 'DEV') {
			// 開発環境
			if (0) { return true; }
			// 送信方法の選択　※1:mail関数 2:popen関数 3:fsockopen関数
			$this->method = 2;
			// sendmail パス設定
			$this->sendmailPath = 'c:/usr/xmail/sendmail.exe';
			if ($this->method == 1) {
				ini_set('SMTP', 'loclhost');
				ini_set('smtp_port', '25');
				ini_set('sendmail_from', ADMIN_MAIL_ADDRESS);
			}

		} elseif (DEVELOPMENT_FLAG == 'TEST') {
			// 検証環境
			if (0) { return true; }
			// 送信方法の選択　※1:mail関数 2:popen関数 3:fsockopen関数
			$this->method = 2;
			// sendmail パス設定
			$this->sendmailPath = '/usr/sbin/sendmail';
			if ($this->method == 1) {
				ini_set('SMTP', 'loclhost');
				ini_set('smtp_port', '25');
				ini_set('sendmail_from', ADMIN_MAIL_ADDRESS);
			}

		} else {
			// 本番環境
			//--------------------------------------------------------

			// 送信方法の選択　※1:mail関数 2:popen関数 3:fsockopen関数
			$this->method = 2;

			// sendmail パス設定
			$this->sendmailPath = '/usr/sbin/sendmail';

			// mail関数を使用する場合の設定
			if ($this->method == 1) {
				ini_set('SMTP', 'loclhost');
				ini_set('smtp_port', '25');
				ini_set('sendmail_from', ADMIN_MAIL_ADDRESS);
			}

			//--------------------------------------------------------
		}

		$this->typeList = array(
			'txt'	=>	'text/plain',
			'htm'	=>	'text/html',
			'html'	=>	'text/html',
			'shtml'	=>	'text/html',
			'hdml'	=>	'text/hdml',
			'stm'	=>	'text/html',
			'xml'	=>	'text/xml',
			'csv'	=>	'text/plain',
			'css'	=>	'text/css',
			'vcf'	=>	'text/x-vcard',
			'rtf'	=>	'text/rtf',
			'rtx'	=>	'text/richtext',
			'gif'	=>	'image/gif',
			'jpeg'	=>	'image/jpeg',
			'jpg'	=>	'image/jpeg',
			'png'	=>	'image/png',
			'bmp'	=>	'image/bmp',
			'tiff'	=>	'image/tiff',
			'tif'	=>	'image/tiff',
			'ico'	=>	'image/x-icon',
			'zip'	=>	'application/zip',
			'lzh'	=>	'application/x-lzh',
			'lha'	=>	'application/x-lzh',
			'tar'	=>	'application/x-tar',
			'gz'	=>	'application/x-gzip',
			'doc'	=>	'application/msword',
			'xls'	=>	'application/vnd.ms-excel',
			'pdf'	=>	'application/pdf',
			'swf'	=>	'application/x-shockwave-flash',
			'js'	=>	'application/x-javascript',
			'mid'	=>	'addio/midi',
			'midi'	=>	'audio/midi',
			'mp2'	=>	'audio/mpeg',
			'mp3'	=>	'audio/mpeg',
			'wav'	=>	'audio/x-wav',
			'au'	=>	'audio/basic',
			'wma'	=>	'audio/x-ms-wma',
			'mpg'	=>	'video/mpeg',
			'mpeg'	=>	'video/mpeg',
			'mov'	=>	'video/quicktime',
			'avi'	=>	'video/x-msvideo',
			'qt'	=>	'video/quicktime',
		);

	}
	//function __construct() {
	//	$this->OrgSendMail();
	//}

/**-----------------------------------------------
 * Function_Name       :  sendMail()
 * Title               :  メール送信
 * Parameter           :  送信先メールアドレス, 送信元メールアドレス, タイトル, 内容
 * return value        :  
 * Comment             :  
*/
	function sendMail($to, $from, $from_name, $subject, $message, $systemRootMail='', $bccMail='', $attachment='') {

		$this->to = $to;
		$this->from = $from;
		$this->from_name = $from_name;
		$this->subject = $subject;
		$this->message = $message;
		$this->encode_message = '';
		$this->systemRootMail = $systemRootMail;
		$this->bccMail = $bccMail;
		$this->attachment = $attachment;

		if ($this->systemRootMail != '') {
			$this->Return_Path = $this->systemRootMail;
			$this->Errors_To = $this->systemRootMail;
		} elseif (ADMIN_MAIL_ADDRESS != '') {
			$this->Return_Path = ADMIN_MAIL_ADDRESS;
			$this->Errors_To = ADMIN_MAIL_ADDRESS;
		}

		// ログ
		if (LOG_DIR != '' && is_dir(LOG_DIR) == true && SENDMAILLOG_FLAG == '1') {
			$log_contents =		'■' . DEF_SCRIPT_NAME . "\n";
			$log_contents .=	'【To】' . $this->to . "\n";
			$log_contents .=	'【From】' . $this->from . ' / ' . $this->from_name . "\n";
			$log_contents .=	'【Return-Path】' . $this->Return_Path . "\n";
			$log_contents .=	'【Errors-To】' . $this->Errors_To . "\n";
			$log_contents .=	'【Bcc】' . $this->bccMail . "\n";
			$log_contents .=	'【Subject】' . $this->subject . "\n";
			$log_contents .=	'【Attachment】' . $this->attachment . "\n";
			$log_contents .=	'【Body】' . "\n" . $this->message . "\n";
			$path_fname = LOG_DIR . '/mail_log_' . date("Ym",DIFFERENCE_TIME) . '.' . (LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = @fopen($path_fname, 'a');
			@fwrite($FP, '[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "]----------------------------------------------------\n" . $log_contents . "\n");
			@fclose($FP);
			@chmod($path_fname, (LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}

		// 宛先有無チェック
		if ($this->to == '' && $this->bccMail == '') {
			return false;
		}

		//--------------------------------------------------------
		// Message-ID の生成
		$this->messageId = uniqid('', true) . '@' . $_SERVER['HTTP_HOST'];

		// boundary の生成
		mt_srand((int)(microtime(true)*10000));
		$boundaryPause = md5(uniqid(mt_rand(),1));

		//--------------------------------------------------------
		// 条件確認		※ISO-2022-JP-MS, ISO-2022-JP, UTF-8
		if (phpversion() != false && (version_compare(phpversion(),'5.2.1') >= 0)) {
			// PHPバージョン5.2.1以上による送信
			$this->sendCode = 'ISO-2022-JP-MS';
			mb_internal_encoding('JIS');
			mb_language('japanese');

		} elseif ($this->specialCharacterCheck($this->subject.$this->message) == true) {
			// 特殊文字・拡張文字を含む送信
			$this->sendCode = 'UTF-8';
			mb_internal_encoding('UTF-8');
			mb_language('uni');

		} else {
			// 通常の送信
			$this->sendCode = 'ISO-2022-JP';
			mb_internal_encoding('JIS');
			mb_language('japanese');
		}

		$tmp_form = $this->from;
		if ($this->from_name != $this->from && preg_match('/\@/', $this->from_name) == false) {
			$tmp_form = $this->mbEncodeMimeheader(inc_PageStd::mbConvertEncoding($this->from_name, $this->sendCode, PROG_CHAR_CODE), $this->sendCode) . "<$this->from>";
		}
		$tmp_subject = $this->mbEncodeMimeheader(inc_PageStd::mbConvertEncoding($this->subject, $this->sendCode, PROG_CHAR_CODE), $this->sendCode);
		$tmp_charset = "text/plain; charset=" . $this->mbPreferredMimeName($this->sendCode);
		$tmp_encoding = "base64";
		$this->encode_message = chunk_split(base64_encode(inc_PageStd::mbConvertEncoding($this->message, $this->sendCode, PROG_CHAR_CODE)), 76, "\n");

		// 配信方法の選択
		switch ($this->method) {
			case 1:	// mail()
					$this->method1($tmp_form, $tmp_subject, $tmp_charset, $tmp_encoding);
					break;

			case 2:	// popen()
					$this->method2($tmp_form, $tmp_subject, $tmp_charset, $tmp_encoding);
					break;

			case 3:	// fsockopen()
					$this->method3($tmp_form, $tmp_subject, $tmp_charset, $tmp_encoding);
					break;

			default:
				mb_internal_encoding(PROG_CHAR_CODE);
				mb_language('japanese');
				return true;
		}
		mb_internal_encoding(PROG_CHAR_CODE);
		mb_language('japanese');
		return false;

	}
	//--------------------------------------------------------
	function method1($tmp_form, $tmp_subject, $tmp_charset, $tmp_encoding) {
		// ヘッダー生成
		$header  = "Return-Path: <$this->Return_Path>\n";
		$header .= "Errors-To: <$this->Errors_To>\n";
		$header .= "From: " . $tmp_form . "\n";
		$header .= "Message-ID: <$this->messageId>\n";
		$header .= "X-Mailer: sendmail\n";
		$header .= "X-Accept-Language: ja\n";
		// メール送信
		return mb_send_mail($this->to, $this->subject, $this->encode_message, $header);
	}

	//--------------------------------------------------------
	function method2($tmp_form, $tmp_subject, $tmp_charset, $tmp_encoding) {

		mt_srand((int)(microtime(true)*10000));
		$boundaryPause = md5(uniqid(mt_rand(),1));
		//
		$header  = "Return-Path: <$this->Return_Path>\n";
		$header .= "Errors-To: <$this->Errors_To>\n";
		$header .= "To: <$this->to>\n";
		$header .= "From: " . $tmp_form . "\n";
		//
		IF ($this->bccMail != '') {
			$header .= "Bcc: $this->bccMail\n";
		}
		$header .= "Subject: " . $tmp_subject . "\n";
		$header .= "Message-ID: <$this->messageId>\n";
		$header .= "Date: " . date("r",time()) . "\n";
		$header .= "MIME-Version: 1.0\n";
		//
		if ($this->attachment != '') {
			$boundary = "----=_NextPart_" . $boundaryPause;
			$header .= 'Content-Type: multipart/mixed; boundary="' . $boundary . '"' . "\n";
		} else {
			$header .= "Content-Type: " . $tmp_charset . "\n";
			$header .= "Content-Transfer-Encoding: " . $tmp_encoding . "\n";
		}
		$header .= "X-Mailer: sendmail\n";
		$header .= "X-Accept-Language: ja\n";
		//
		if ($this->attachment != '') {
			$header .= "This is a multipart message in MIME format.\n\n";
			$header .= '--' . $boundary . "\n";
			$header .= "Content-Type: " . $tmp_charset . "\n";
			$header .= "Content-Transfer-Encoding: " . $tmp_encoding . "\n";
			$mailSauce = $header . "\n" . $this->encode_message . "\n";
			$mailSauce .= $this->multipart($boundary);
		} else {
			$mailSauce = $header . "\n" . $this->encode_message . "\n";
		}

		// メール送信
		if (!($mp = @popen($this->sendmailPath.' -t','w'))) {
			// エラー時にソースログを記録
			$this->sendMailSauceLog($mailSauce);
			return false;
		}
		fputs($mp,$mailSauce);
		pclose($mp);

		if (0) {
			// ソースログを記録 ※テスト用
			$this->sendMailSauceLog($mailSauce);
		}

		return true;
	}
	//--------------------------------------------------------
	function method3($tmp_form, $tmp_subject, $tmp_charset, $tmp_encoding) {

		$get_include_path = get_include_path();
		if (preg_match('#' . INCLUDE_DIR . 'PEAR#i', $get_include_path) == false) {
			set_include_path($get_include_path . PATH_SEPARATOR . INCLUDE_DIR . 'PEAR');
		}
		if (class_exists('Mail') == false) {
			// PEAR::Mail
			require_once "Mail.php";
			if (class_exists('Mail_Mime') == false) {
				// PEAR::Mail_Mime
				require_once("Mail/mime.php");
			}
		}

		if (file_exists(INCLUDE_DIR . 'smtp/smtp_setting.php') == false) {
			$this->sendMailSauceLog('SMTP設定ファイルがありません。');
			return false;
		}
		include(INCLUDE_DIR . 'smtp/smtp_setting.php');

		$recipients = $this->to;
		if ($this->bccMail != '') {
			$recipients .= ', ' . $this->bccMail;
			$headers['Bcc'] = $this->bccMail;
		}

		$headers['Return-Path']					= $this->Return_Path;
		$headers['Errors-To']					= $this->Errors_To;
		$headers['To']							= $this->to;
		$headers['From']						= $tmp_form;
		$headers['Subject']						= $tmp_subject;
		$headers['Message-ID']					= "<$this->messageId>";
		$headers['Date']						= date("r",time());
		$headers['MIME-Version']				= "1.0";
		$headers['X-Mailer']					= "sendmail";
		$headers['X-Accept-Language']			= "ja";

		if ($this->attachment != '') {
			// 添付ファイルがある場合
			$body = inc_PageStd::mbConvertEncoding($this->message, $this->sendCode, PROG_CHAR_CODE);

			$mimeObject = new Mail_Mime("\n");
			$mimeObject -> setTxtBody($body);
			foreach (explode(',', $this->attachment) as $fPathName) {
				$fPathName = TMP_DIR . $fPathName;
				if (file_exists($fPathName) == false) { continue; }
				$fName = basename($fPathName);
				$m_tmp = getimagesize($fPathName);
				if ($m_tmp['mime'] != '') {
					$type = $m_tmp['mime'];
				} else {
					$tmp = array();
					preg_match('/^.+\.(.+)$/i', $fName, $tmp);
					$type = $this->typeList[strtolower($tmp[1])];
				}
				$mimeObject -> addAttachment($fPathName, $type);
			}

			$bodyParam = array(
				"head_charset" =>	$this->mbPreferredMimeName($this->sendCode),
				"text_charset" =>	$this->mbPreferredMimeName($this->sendCode),
				"text_encoding" =>	'base64',
			);
			$body = $mimeObject -> get($bodyParam);

			$headers = $mimeObject -> headers($headers);

		} else {
			// 添付ファイルがない場合
			$headers['Content-Type']				= $tmp_charset;
			$headers['Content-Transfer-Encoding']	= $tmp_encoding;
			$body = $this->encode_message;
		}

		if (0) {
			// ソースログを記録 ※テスト用
			$this->sendMailSauceLog($headers . $body);
		}

		$objMail = Mail::factory('smtp', $params);
		$result = $objMail->send($recipients, $headers, $body);
		if (PEAR::isError($result)) {
			// エラー時にエラーメッセージとソースログを記録
			$this->sendMailSauceLog($result->getMessage());
			$this->sendMailSauceLog($headers . $body);
			return false;
		}
		return true;
	}
	//--------------------------------------------------------

	// MIMEヘッダーをエンコード
	function mbEncodeMimeheader($val, $sendCode) {
		return mb_encode_mimeheader($val, $sendCode);
	}

	// MIMEに適したcharset名を取得
	function mbPreferredMimeName($sendCode) {
		return mb_preferred_mime_name($sendCode);
	}

	// ソースログ　※送信エラー時にはソースログを記録
	function sendMailSauceLog($mailSauce) {
		if (LOG_DIR != '' && is_dir(LOG_DIR) == true) {
			$path_fname = LOG_DIR . '/mailsauce_log_' . date("Ym",DIFFERENCE_TIME) . '.' . (LOG_EXTENSION != '' ? LOG_EXTENSION : 'txt');
			$FP = @fopen($path_fname,'a');
			@fwrite($FP,'[' . date("Y-m-d H:i:s",DIFFERENCE_TIME) . "]----------------------------------------------------\n" . $mailSauce . "\n");
			@fclose($FP);
			@chmod($path_fname, (LOG_PERMISSION != '' ? LOG_PERMISSION : 0666));
		}
	}

	// 特殊文字・拡張文字の確認
	function specialCharacterCheck($val) {
		$code1 = inc_PageStd::mbConvertEncoding($val, 'SJIS', PROG_CHAR_CODE);
		$code2 = inc_PageStd::mbConvertEncoding($val, 'SJIS-win', PROG_CHAR_CODE);
		if ($code1 != $code2) {
			return true;	// 特殊文字・拡張文字あり
		} else {
			return false;
		}
	}

	// 添付情報の生成
	function multipart($boundary) {
		$multiBody = '';
		foreach (explode(',', $this->attachment) as $fPathName) {
			$fPathName = TMP_DIR . $fPathName;
			if (file_exists($fPathName) == false) { continue; }
			$fName = basename($fPathName);
			$m_tmp = getimagesize($fPathName);
			if ($m_tmp['mime'] != '') {
				$type = $m_tmp['mime'];
			} else {
				$tmp = array();
				preg_match('/^.+\.(.+)$/i', $fName, $tmp);
				$type = $this->typeList[strtolower($tmp[1])];
			}
			$multiBody .= '--' . $boundary . "\n";
			$multiBody .= 'Content-Type: ' . $type . '; name="' . $fName . '"' . "\n";
			$multiBody .= 'Content-Transfer-Encoding: base64' . "\n";
			$multiBody .= 'Content-Disposition: attachment; filename="' . $fName . '"' . "\n";
			$multiBody .= "\n";
			$FP = @fopen($fPathName, 'rb');
			$readFile = @fread($FP, filesize($fPathName));
			@fclose($FP);
			$multiBody .= chunk_split(base64_encode($readFile), 76, "\n");
			$multiBody .= "\n\n";
		}
		$multiBody .= '--' . $boundary . '--' . "\n";
		return $multiBody;
	}

}

?>