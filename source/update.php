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
function unpackDir($file, $dir, $chmod_dir=0755, $chmod_file=0644) {
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
			if (($stat['crc']==0)&&($stat['size']==0)) {
				// dir
				if (!is_dir($dir.$stat['name'])) {
					mkdir($dir.$stat['name']);
				}
				chmod($dir.$stat['name'], $chmod_dir);
			} else {
				// ile
				$_data=$data[$name]['zip']->getFromIndex($i);
				file_put_contents($dir.$stat['name'], $_data);
				chmod($dir.$stat['name'], $chmod_file);
			}
		}
	}
}

$version='';
$release='beta';
$file_class='./classes/jbsnm_sync.php';
if (file_exists($file_class)) {
	$class_content=file_get_contents($file_class);
	preg_match('/private \$version_this=\'(([0-9]+).([0-9]+).([0-9]+)\';)/Uis', $class_content, $result);

	if (count($result)==5) {
		$release='stable';
	}
	preg_match('/private \$version_this=\'(([0-9a-zA-Z\.\-]+)\';)/Uis', $class_content, $result);
	if (isset($result[2])) {
		$version=$result[2];
	}
}

if ($release=='stable') {
	$_version=file_get_contents('https://jbs-newmedia.de/getsynchronizeversion');
	if ($_version==$version) {
		die('synchronize is up2date ('.$version.')');
	} else {
		$filename=strtolower('synchronize-'.$_version.'.zip');
		file_put_contents($filename, file_get_contents('https://jbs-newmedia.de/getsynchronize'));
		unpackDir($filename, './');
		unlink($filename);
		if (file_exists('./update_custom.php')) {
			include './update_custom.php';
		}
		die('synchronize updated successfully ('.$version.' => '.$_version.')');
	}
} else {
	$_version=file_get_contents('https://jbs-newmedia.de/getsynchronizeversionbeta');
	if ($_version==$version) {
		die('synchronize is up2date ('.$version.')');
	} else {
		$filename=strtolower('synchronize-'.$_version.'.zip');
		file_put_contents($filename, file_get_contents('https://jbs-newmedia.de/getsynchronizebeta'));
		unpackDir($filename, './');
		unlink($filename);
		if (file_exists('./update_custom.php')) {
			include './update_custom.php';
		}
		die('synchronize updated successfully ('.$version.' => '.$_version.')');
	}
}

?>