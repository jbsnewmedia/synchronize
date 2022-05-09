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
class JBSNM_Sync_Log extends JBSNM_Sync_Object {

	public function write($header, $data, $file='') {
		if ($file=='') {
			$file='sync-log-'.date('Ymd').'.html';
		}
		$fp=fopen($file, 'a+');
		ob_start();
		$out=ob_get_contents();
		ob_end_clean();
		fwrite($fp, '<div style="border:1px solid #000; background-color:#eee; width:100%; margin-bottom:10px; font-size:13px;"><div style="background-color:#ddd; font-size:15px; font-weight:bold; padding:5px;">'.date('Y.m.d H:i:s ').$header.'</div><div style="background-color:#eee; font-size:13px; padding:5px;"><pre>'.$out.'</pre></div></div>');
		fclose($fp);
	}

	/**
	 *
	 * @return JBSNM_Sync_Log
	 */
	public static function getInstance() {
		return parent::getInstance();
	}
}

?>