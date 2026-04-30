<?php

//-------------------------------------------------------------------------------------------------
// Use the DS to separate the directories in other defines
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}
//-------------------------------------------------------------------------------------------------
// IISの確認	※環境によって調整が必要かもしれない
if (isset($_SERVER['SERVER_SOFTWARE']) == true && preg_match('/IIS/i', $_SERVER['SERVER_SOFTWARE']) == true) {
	define('DEF_SERVER_ADDR', $_SERVER['LOCAL_ADDR']);
} else {
	define('DEF_SERVER_ADDR', $_SERVER['SERVER_ADDR']);
}
// 環境の確認
//20211130 if (DEF_SERVER_ADDR == '192.168.0.40' || preg_match('/nisserver\.local/i', $_SERVER['HTTP_HOST']) == true || preg_match('/nisserver\.local/i', $_SERVER['PWD']) == true) {
if (getenv('APP_ENV') === 'docker') {

	// Docker環境
	define('DEVELOPMENT_FLAG', 'DOCKER');
} elseif (DEF_SERVER_ADDR == '192.168.0.40' || preg_match('/nisserver\.local/i', $_SERVER['HTTP_HOST']) == true || preg_match('/nisserver\.local/i', getcwd()) == true) {

	// 開発環境
	define('DEVELOPMENT_FLAG', 'DEV');
//20211130 } elseif (preg_match('/kric7\.hp-check\.jp/i', $_SERVER['HTTP_HOST']) == true || preg_match('/kric7\.hp-check\.jp/i', $_SERVER['PWD']) == true) {
} elseif (preg_match('/kric7\.hp-check\.jp/i', $_SERVER['HTTP_HOST']) == true || preg_match('/kric7\.hp-check\.jp/i', getcwd()) == true) {

	// テスト環境
	define('DEVELOPMENT_FLAG', 'TEST');
} else {
	// 本番環境
	define('DEVELOPMENT_FLAG', '');
}
//-------------------------------------------------------------------------------------------------
// 環境別設定　※環境に合わせて編集（必須）
if (DEVELOPMENT_FLAG == 'DOCKER') {
	// Docker環境
	define('APPROOT_DIR', 'data');
	if (!defined('ROOT')) {
		define('ROOT', DS . 'var' . DS . 'www');
	}
} elseif (DEVELOPMENT_FLAG == 'DEV') {
	// 開発環境
	// ルートからのcakePHP用フォルダまでの名称　※前後のスラッシュなし	例）data, cgi-bin, htdocs/cgi-bin, www/data
	define('APPROOT_DIR', 'data');
	// The full path to the directory which holds "app", WITHOUT a trailing DS.
	if (!defined('ROOT')) {
		define('ROOT', 'G:' . DS . 'htdocs' . DS . 'www' . DS . 'az-japan' . DS . 'kric');
	}
} elseif (DEVELOPMENT_FLAG == 'TEST') {
	// テスト環境
	// ルートからのcakePHP用フォルダまでの名称　※前後のスラッシュなし	例）data, cgi-bin, htdocs/cgi-bin, www/data
	define('APPROOT_DIR', 'data');
	// The full path to the directory which holds "app", WITHOUT a trailing DS.
	if (!defined('ROOT')) {
		//define('ROOT', '/home/users/1/azj'. DS . 'web' . DS . 'wit' . DS . 'kric7');
		//define('ROOT', DS.'home'.DS.'users'.DS.'1'.DS.'azj'. DS . 'web' . DS . 'wit' . DS . 'kric7');
		define('ROOT', DS.'home'.DS.'kric2021'.DS.'www'.DS.'kric.co.jp');
	}
} else {
	//+++++++++++++++++++++++++++++++++++++++++++++++++++
	// 本番環境
	// ルートからのcakePHP用フォルダまでの名称　※前後のスラッシュなし	例）data, cgi-bin, htdocs/cgi-bin, www/data
	define('APPROOT_DIR', 'data');
	// The full path to the directory which holds "app", WITHOUT a trailing DS.
	if (!defined('ROOT')) {
		//define('ROOT', DS . 'var' . DS . 'www' . DS . 'vhost');
		//define('ROOT', DS . 'home' . DS . 'sites' . DS . 'heteml' . DS . 'users161' . DS . 'a' . DS . 'z' . DS . 'j' . DS . 'azjp' . DS . 'web' . DS . 'check-machine.com' . DS . 'wit' . DS . 'wit-kric');
		//define('ROOT', DS . 'home' . DS . 'wit-project02' . DS . 'www' . DS . 'kric2');
		//define('ROOT', DS.'home'.DS.'users'.DS.'1'.DS.'azj'. DS . 'web' . DS . 'wit' . DS . 'kric7');
		define('ROOT', DS.'home'.DS.'kric2021'.DS.'www'.DS.'kric.co.jp');
		//define('ROOT', DS . 'home' . DS . 'kric');
	}
	//+++++++++++++++++++++++++++++++++++++++++++++++++++
}
//-------------------------------------------------------------------------------------------------
// 特に編集の必要なし
// The actual directory name for the "app".
if (!defined('APP_DIR')) {
	define('APP_DIR', APPROOT_DIR . DS . 'app');
}
// The absolute path to the "cake" directory, WITHOUT a trailing DS.
if (!defined('CAKE_CORE_INCLUDE_PATH')) {
	define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . APPROOT_DIR . DS . 'lib');
}
if (!defined('WEBROOT_DIR')) {
	define('WEBROOT_DIR', basename(dirname(__FILE__)));
}
if (!defined('WWW_ROOT')) {
	define('WWW_ROOT', ROOT . DS . WEBROOT_DIR . DS);
}
//-------------------------------------------------------------------------------------------------


/**
 * Index
 *
 * The Front Controller for handling every request
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.webroot
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/**
 * Use the DS to separate the directories in other defines
 */
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * These defines should only be edited if you have cake installed in
 * a directory layout other than the way it is distributed.
 * When using custom settings be sure to use the DS and do not add a trailing DS.
 */

/**
 * The full path to the directory which holds "app", WITHOUT a trailing DS.
 *
 */
if (!defined('ROOT')) {
	define('ROOT', dirname(dirname(dirname(__FILE__))));
}

/**
 * The actual directory name for the "app".
 *
 */
if (!defined('APP_DIR')) {
	define('APP_DIR', basename(dirname(dirname(__FILE__))));
}

/**
 * The absolute path to the "cake" directory, WITHOUT a trailing DS.
 *
 * Un-comment this line to specify a fixed path to CakePHP.
 * This should point at the directory containing `Cake`.
 *
 * For ease of development CakePHP uses PHP's include_path. If you
 * cannot modify your include_path set this value.
 *
 * Leaving this constant undefined will result in it being defined in Cake/bootstrap.php
 *
 * The following line differs from its sibling
 * /lib/Cake/Console/Templates/skel/webroot/index.php
 */
//define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'lib');

/**
 * Editing below this line should NOT be necessary.
 * Change at your own risk.
 *
 */
if (!defined('WEBROOT_DIR')) {
	define('WEBROOT_DIR', basename(dirname(__FILE__)));
}
if (!defined('WWW_ROOT')) {
	define('WWW_ROOT', dirname(__FILE__) . DS);
}

// for built-in server
if (php_sapi_name() == 'cli-server') {
	if ($_SERVER['REQUEST_URI'] !== '/' && file_exists(WWW_ROOT . $_SERVER['REQUEST_URI'])) {
		return false;
	}
	$_SERVER['PHP_SELF'] = '/' . basename(__FILE__);
}

if (!defined('CAKE_CORE_INCLUDE_PATH')) {
	if (function_exists('ini_set')) {
		ini_set('include_path', ROOT . DS . 'lib' . PATH_SEPARATOR . ini_get('include_path'));
	}
	if (!include ('Cake' . DS . 'bootstrap.php')) {
		$failed = true;
	}
} else {
	if (!include (CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'bootstrap.php')) {
		$failed = true;
	}
}
if (!empty($failed)) {
	trigger_error("CakePHP core could not be found. Check the value of CAKE_CORE_INCLUDE_PATH in APP/webroot/index.php. It should point to the directory containing your " . DS . "cake core directory and your " . DS . "vendors root directory.", E_USER_ERROR);
}

App::uses('Dispatcher', 'Routing');

$Dispatcher = new Dispatcher();
$Dispatcher->dispatch(
	new CakeRequest(),
	new CakeResponse()
);
