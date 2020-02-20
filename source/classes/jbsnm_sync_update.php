<?php

/**
 *
 * @author Juergen Schwind
 * @copyright Copyright (c), JBS New Media GmbH
 * @package JBS New Media - Synchronize
 * @link https://jbs-newmedia.de
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 *
 */
class JBSNM_Sync_Update extends JBSNM_Sync_Object {

	public function doUpdate() {
		if ($this->checkUpdate()!==true) {
			return false;
		}
		$currentversion=JBSNM_Sync::getInstance()->getCurrentVersion(JBSNM_Sync::getInstance()->getRelease());
		$filename=strtolower('synchronize-'.$currentversion.'.zip');
		file_put_contents($filename, file_get_contents('https://github.com/jbs-newmedia/synchronize/archive/'.$currentversion.'.zip'));
		$this->unpackDir($filename, './');
		unlink($filename);
		if (file_exists('./update_custom.php')) {
			include './update_custom.php';
		}
		return true;
	}

	public function checkUpdate($version='', $currentversion='') {
		if ($version=='') {
			$version=JBSNM_Sync::getInstance()->getVersion();
		}
		if ($currentversion=='') {
			$currentversion=JBSNM_Sync::getInstance()->getCurrentVersion(JBSNM_Sync::getInstance()->getRelease());
		}

		if (version_compare($currentversion, $version, '>')) {
			return true;
		}
		return false;
	}

	function unpackDir($file, $dir, $chmod_dir=0755, $chmod_file=0644) {
		$currentversion=JBSNM_Sync::getInstance()->getCurrentVersion(JBSNM_Sync::getInstance()->getRelease());
		$name=md5($file.'###'.$dir);
		$data[$name]['zip']=new ZipArchive();
		$data[$name]['zip']->open($file);
		if ($data[$name]['zip']->numFiles>0) {
			if (!is_dir($dir)) {
				mkdir($dir);
			}
			chmod($dir, $chmod_dir);
			for ($i=0; $i<$data[$name]['zip']->numFiles; $i++) {
				$stat=$data[$name]['zip']->statIndex($i);
				if ('synchronize-'.$currentversion.'/source/'==substr($stat['name'], 0, strlen('synchronize-'.$currentversion.'/source/'))) {
					$stat['name']=str_replace('synchronize-'.$currentversion.'/source/', '', $stat['name']);
					if ($stat['name']!='') {
						if (($stat['crc']==0)&&($stat['size']==0)) {
							// dir
							if (!is_dir($dir.$stat['name'])) {
								mkdir($dir.$stat['name']);
							}
							chmod($dir.$stat['name'], $chmod_dir);
						} else {
							// file
							$_data=$data[$name]['zip']->getFromIndex($i);
							file_put_contents($dir.$stat['name'], $_data);
							chmod($dir.$stat['name'], $chmod_file);
						}
					}
				}
			}
		}
	}

	/**
	 *
	 * @return JBSNM_Sync_Update
	 */
	public static function getInstance() {
		return parent::getInstance();
	}
}

?>