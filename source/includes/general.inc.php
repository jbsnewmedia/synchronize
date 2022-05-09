<?php

/**
 *
 * @author Juergen Schwind
 * @copyright Copyright (c), JBS New Media GmbH
 * @package JBS New Media - Synchronize
 * @link http://jbs-newmedia.de
 * @license MIT License
 *
 */

date_default_timezone_set('Europe/Berlin');

include abs_path.'includes/debuglib.inc.php';

/* Autoloader */
function autoloader($classname) {
	$filename=abs_path.'classes/'.strtolower($classname).'.php';
	if (file_exists($filename)) {
		require $filename;
	} else {
		JBSNM_Sync::getInstance()->finish('Class '.$classname.' not found at '.$filename);
	}
}

spl_autoload_register('autoloader');

# http://php.net/manual/de/function.utf8-decode.php#83051
function _utf8_decode($string) {
	$tmp = $string;
	$count = 0;
	while (mb_detect_encoding($tmp)=="UTF-8") {
		$tmp = utf8_decode($tmp);
		$count++;
	}

	for ($i = 0; $i < $count-1 ; $i++) {
		$string = utf8_decode($string);
	}
	return $string;
}

?>