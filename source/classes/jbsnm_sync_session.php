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
class JBSNM_Sync_Session extends JBSNM_Sync_Object {

	public $data=array();

	function __construct() {
		$this->clear();
		if ($this->load()===false) {
			$this->check();
		}
	}

	function __destruct() {
		$this->save(true);
	}

	private function setNullSession() {
		$this->data=array();
	}

	public function start() {
		$this->setNullSession();
		$this->data['id']=md5(microtime(true).$_SERVER['REMOTE_ADDR'].time().$_SERVER['HTTP_USER_AGENT']);
		$this->data['addr']=$_SERVER['REMOTE_ADDR'];
		$this->data['ua']=$_SERVER['HTTP_USER_AGENT'];
		$this->data['time']=time();
		$this->data['uri']=$_SERVER['REQUEST_URI'];
		$this->save();
	}

	public function getId() {
		if (!isset($this->data['id'])) {
			return '';
		}
		return $this->data['id'];
	}

	public function load() {
		if ((!isset($_GET['session']))&&((!isset($_POST['session'])))) {
			return false;
		}
		if (isset($_POST['session'])) {
			$id=$_POST['session'];
		} else {
			$id=$_GET['session'];
		}

		if (!preg_match('/^[a-f0-9]{32}$/', $id)) {
			return false;
		}

		$filename=abs_path.'.session/'.$id;
		if (!file_exists($filename)) {
			return false;
		}

		$this->data=unserialize(file_get_contents($filename));
		return true;
	}

	public function save($check=false) {
		$filename=abs_path.'.session/'.$this->getId();
		if ($check===true) {
			if (!file_exists($filename)) {
				return false;
			}
		}
		file_put_contents($filename, serialize($this->data));
		$filename=abs_path.'.session/auth_'.$this->getId();
		file_put_contents($filename, time());
		return true;
	}

	public function delete() {
		$filename=abs_path.'.session/'.$this->getId();
		if (file_exists($filename)) {
			return unlink($filename);
		}
		return true;
	}

	public function clear() {
		$exclude_list=array('.','..','.htaccess');
		$directories=array_diff(scandir(abs_path.'.session/'), $exclude_list);
		foreach ($directories as $file) {
			$filename=abs_path.'.session/'.$file;
			if (filemtime($filename)<time()-(60*30)) {
				unlink($filename);
			}
		}
	}

	public function check() {
		if ((!isset($_GET['session']))&&((!isset($_POST['session'])))) {
			$this->start();
			return false;
		}

		if (isset($_POST['session'])) {
			$id=$_POST['session'];
		} else {
			$id=$_GET['session'];
		}

		if ((@$this->data['id']==$id)&&($this->data['addr']==$_SERVER['REMOTE_ADDR'])&&($this->data['ua']==$_SERVER['HTTP_USER_AGENT'])) {
			return true;
		}
		$this->start();
		return false;
	}

	public function set($key, $val) {
		if (!isset($this->data['values'])) {
			$this->data['values']=array();
		}
		$this->data['values'][$key]=$val;
		$this->save();
	}

	public function get($key) {
		if (!isset($this->data['values'][$key])) {
			return '';
		}
		return $this->data['values'][$key];
	}

	/**
	 *
	 * @return JBSNM_Sync_Session
	 */
	public static function getInstance() {
		return parent::getInstance();
	}
}

?>