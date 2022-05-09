<?php

/**
 *
 * @author Juergen Schwind
 * @copyright Copyright (c), JBS New Media GmbH
 * @package JBS New Media - Synchronize
 * @link https://jbs-newmedia.de
 * @license MIT License
 *
 */
class JBSNM_Sync_Object {

	private static $instances=array();

	function __construct() {
	}

	function __destruct() {
	}

	public static function getInstance() {
		$class=get_called_class();
		if (array_key_exists($class, self::$instances)===false) {
			self::$instances[$class]=new $class();
		}
		return self::$instances[$class];
	}
}

?>