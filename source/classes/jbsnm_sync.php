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
class JBSNM_Sync extends JBSNM_Sync_Object {

	private $base='';

	private $directories=array();

	private $ignored=array();

	private $result=array();

	private $conf=array();

	private $version_this='2.0.4';

	private $version_this_release='stable';

	private $version_current=array();

	private $project='';

	private $conf_project=array();

	private $synclist=array();

	function __construct() {
		$this->setCurrentVersion($this->version_this_release);
	}

	public function setProject($project) {
		$conf=$this->getConf($project);
		if ($conf!==false) {
			$this->project=$project;
			$this->setProjectConf($conf);
			unset($this->conf['']);
			$this->init($conf['base'], $conf['directories'], $conf['ignored_files'], $conf['ignored_dirs']);
			return true;
		} else {
			$this->project='';
			$this->setProjectConf();
			$this->init($conf['base'], $conf['directories'], $conf['ignored_files'], $conf['ignored_dirs']);
			return false;
		}
	}

	public function getProject() {
		return $this->project;
	}

	public function setProjectConf($conf=array()) {
		$this->conf_project=array();
		$this->conf_project['name']='default';
		$this->conf_project['base']='../';
		$this->conf_project['showcode']=array('js','css','json','php','txt');
		$this->conf_project['showdiff']=array('js','css','json','php','txt');
		$this->conf_project['directories']=array();
		$this->conf_project['chmod_files']=0644;
		$this->conf_project['chmod_dirs']=0755;
		$this->conf_project['ignored_files']=array();
		$this->conf_project['ignored_dirs']=array();
		$this->conf_project['master_link']='';
		$this->conf_project['slave_link']='';
		$this->conf_project['token']='';
		$this->conf_project['pass']='';
		$this->conf_project['htuser']='';
		$this->conf_project['htpass']='';
		$this->conf_project['chunk_size']=1024*1024*1;
		if ($conf!=array()) {
			foreach ($conf as $key => $value) {
				$this->conf_project[$key]=$value;
			}
		}
	}

	public function getProjectConf() {
		return $this->conf_project;
	}

	public function setProjectConfValue($key, $value) {
		$this->conf_project[$key]=$value;
	}

	public function getProjectConfValue($key) {
		if (isset($this->conf_project[$key])) {
			return $this->conf_project[$key];
		}
		return '';
	}

	private function init($base='./', $directories=array('./'), $ignored_files=array(), $ignored_dirs=array()) {
		$this->base=$base;
		$this->directories=$directories;
		$this->ignored['files']=array();
		$this->ignored['files_pattern']=array();
		$this->ignored['dirs']=array();
		$this->ignored['dirs_pattern']=array();
		if (!empty($ignored_files)) {
			foreach ($ignored_files as $node) {
				if (substr($node, 0, 1)=='/') {
					$this->ignored['files'][]=$node;
				} else {
					$this->ignored['files_pattern'][]=$node;
				}
			}
		}
		if (!empty($ignored_dirs)) {
			foreach ($ignored_dirs as $node) {
				if (substr($node, 0, 1)=='/') {
					if (substr($node, -1)!='/') {
						$node.='/';
					}
					$this->ignored['dirs'][]=$node;
				} else {
					$this->ignored['dirs_pattern'][]=$node;
				}
			}
		}
	}

	public function finish($msg='') {
		die($msg);
	}

	private function checkViewDiff($type, $node) {
		if ($type=='dir') {
			return false;
		}
		if (in_array(pathinfo($node, PATHINFO_EXTENSION), JBSNM_Sync::getInstance()->getProjectConfValue('showdiff'))) {
			return true;
		}
		return false;
	}

	private function checkViewCode($type, $node) {
		if ($type=='dir') {
			return false;
		}
		if (in_array(pathinfo($node, PATHINFO_EXTENSION), JBSNM_Sync::getInstance()->getProjectConfValue('showcode'))) {
			return true;
		}
		return false;
	}

	private function checkViewDiffMode($master, $slave) {
		if ($master>$slave) {
			return 'master';
		}
		if ($slave>$master) {
			return 'slave';
		}
		return 'unset';
	}

	public function getSyncTable() {
		if (empty($this->synclist)) {
			$postdata=array();
			$postdata['action']='getlist';
			$postdata['project']=$this->getProject();

			$synclist_master=json_decode($this->exec($this->getProjectConfValue('master').'index.php', $postdata), true);
			$synclist_slave=json_decode($this->exec($this->getProjectConfValue('slave').'index.php', $postdata), true);

			$this->setProjectConfValue('sync_error_master', false);
			$this->setProjectConfValue('sync_error_slave', false);

			if (($synclist_master===null)||($synclist_slave===null)) {
				if ($synclist_master===null) {
					$this->setProjectConfValue('sync_error_master', true);
				}
				if ($synclist_slave===null) {
					$this->setProjectConfValue('sync_error_slave', true);
				}
				return false;
			}

			if (count($synclist_master)>0) {
				foreach ($synclist_master as $node=>$node_details) {
					if (isset($synclist_slave[$node])) {
						if ($node_details['sha1']==$synclist_slave[$node]['sha1']) {
							$node_details['master']=3;
							$node_details['slave']=3;
							$node_details['slave_time']=$synclist_slave[$node]['time'];
							$node_details['viewdiff']=$this->checkViewDiff($node_details['type'], $node);
							$node_details['viewdiffmode']=$this->checkViewDiffMode($node_details['master'], $node_details['slave']);
							$node_details['viewcode']=false;
							$node_details['viewcodemode']='unset';
							$this->synclist[$node]=$node_details;
						} elseif ($node_details['time']>=$synclist_slave[$node]['time']) {
							$node_details['master']=1;
							$node_details['slave']=2;
							$node_details['slave_time']=$synclist_slave[$node]['time'];
							$node_details['viewdiff']=$this->checkViewDiff($node_details['type'], $node);
							$node_details['viewdiffmode']=$this->checkViewDiffMode($node_details['master'], $node_details['slave']);
							$node_details['viewcode']=false;
							$node_details['viewcodemode']='unset';
							$this->synclist[$node]=$node_details;
						} else {
							$node_details['master']=2;
							$node_details['slave']=1;
							$node_details['slave_time']=$synclist_slave[$node]['time'];
							$node_details['viewdiff']=$this->checkViewDiff($node_details['type'], $node);
							$node_details['viewdiffmode']=$this->checkViewDiffMode($node_details['master'], $node_details['slave']);
							$node_details['viewcode']=false;
							$node_details['viewcodemode']='unset';
							$this->synclist[$node]=$node_details;
						}
					} else {
						$node_details['master']=1;
						$node_details['slave']=0;
						$node_details['slave_time']=0;
						$node_details['viewdiff']=false;
						$node_details['viewdiffmode']='unset';
						$node_details['viewcode']=$this->checkViewCode($node_details['type'], $node);
						$node_details['viewcodemode']='master';
						$this->synclist[$node]=$node_details;
					}
				}
			}

			if (count($synclist_slave)>0) {
				foreach ($synclist_slave as $node=>$node_details) {
					if (!isset($this->synclist[$node])) {
						$node_details['master']=0;
						$node_details['slave']=1;
						$node_details['slave']=1;
						$node_details['slave_time']=$node_details['time'];
						$node_details['time']=0;
						$node_details['viewdiff']=false;
						$node_details['viewdiffmode']='unset';
						$node_details['viewcode']=$this->checkViewCode($node_details['type'], $node);
						$node_details['viewcodemode']='slave';
						$this->synclist[$node]=$node_details;
					}
				}
			}
			ksort($this->synclist);
		}
		return $this->synclist;
	}

	public function getVersion() {
		return $this->version_this;
	}

	public function getRelease() {
		return $this->version_this_release;
	}

	private function setCurrentVersion($release='stable') {
		if ((!file_exists('./update.'.$release.'.version'))||(filemtime('./update.'.$release.'.version')<(time()-3600))) {
			$options=array('http' => array('user_agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0'));
			$context=stream_context_create($options);
			$file='https://jbs-newmedia.de/getsynchronizereleases';
			$content=file_get_contents($file, false, $context);
			$json=json_decode($content, true);
			$this->version_current[$release]='0.0.0';
			foreach ($json as $git) {
				if (($release=='beta')&&($git['prerelease']===true)) {
					$this->version_current[$release]=$git['tag_name'];
				}
				if (($release=='stable')&&($git['prerelease']===false)) {
					$this->version_current[$release]=$git['tag_name'];
				}
			}
			file_put_contents('./update.'.$release.'.version', $this->version_current[$release]);
			return true;
		}
		$this->version_current[$release]=file_get_contents('./update.'.$release.'.version');
		return true;
	}

	public function getCurrentVersion($release='stable') {
		if (!isset($this->version_current[$release])) {
			$this->setCurrentVersion($release);
		}
		return $this->version_current[$release];
	}

	public function getConfArray() {
		if (empty($this->conf)) {
			$files=array_diff(scandir('./conf'), array('.','..'));
			$this->conf['']=array('name'=>'select project');
			foreach ($files as $file) {
				if (substr($file, -8)=='.inc.php') {
					include './conf/'.$file;

					$url=parse_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
					$url_1=parse_url($_config['master_link']);
					$url_2=parse_url($_config['slave_link']);

					if (!isset($url['host'])) {
						$url['host']='';
					}
					if (!isset($url_1['host'])) {
						$url_1['host']='';
					}
					if (!isset($url_2['host'])) {
						$url_2['host']='';
					}
					if (!isset($url['path'])) {
						$url['path']='';
					} else {
						$url['path']=str_replace('index.php', '', $url['path']);
					}
					if (!isset($url_1['path'])) {
						$url_1['path']='';
					}
					if (!isset($url_2['path'])) {
						$url_2['path']='';
					}

					if (($url['host']==$url_1['host'])&&($url['path']==$url_1['path'])) {
						$_config['master']=$_config['master_link'];
						$_config['slave']=$_config['slave_link'];
						if ($url_1['host']==$url_2['host']) {
							$_config['master_label']=$url_1['host'].$url_1['path'];
							$_config['slave_label']=$url_2['host'].$url_2['path'];
						} else {
							$_config['master_label']=$url_1['host'];
							$_config['slave_label']=$url_2['host'];
						}
					} elseif (($url['host']==$url_2['host'])&&($url['path']==$url_2['path'])) {
						$_config['master']=$_config['slave_link'];
						$_config['slave']=$_config['master_link'];
						if ($url_1['host']==$url_2['host']) {
							$_config['master_label']=$url_2['host'].$url_2['path'];
							$_config['slave_label']=$url_1['host'].$url_1['path'];
						} else {
							$_config['master_label']=$url_2['host'];
							$_config['slave_label']=$url_1['host'];
						}
					} else {
						$_config['master']=$_config['master_link'];
						$_config['slave']=$_config['slave_link'];
						if ($url_1['host']==$url_2['host']) {
							$_config['master_label']=$url_1['host'].$url_1['path'];
							$_config['slave_label']=$url_2['host'].$url_2['path'];
						} else {
							$_config['master_label']=$url_1['host'];
							$_config['slave_label']=$url_2['host'];
						}
					}

					$this->conf[substr($file, 0, -8)]=$_config;
				}
			}
		}
		return $this->conf;
	}

	public function getConf($project='') {
		if (empty($this->conf)) {
			$this->getConfArray();
		}
		if ((isset($this->conf[$project]))&&($project!='')) {
			return $this->conf[$project];
		}
		return false;
	}

	public function getListData() {
		if ($this->getProject()=='') {
			return false;
		}
		$this->result=array();
		foreach ($this->directories as $directory) {
			$this->getList($directory);
		}
		return $this->result;
	}

	private function getList($directory) {
		if (is_dir($directory)) {
			$handle=opendir($directory);
			while ($node=readdir($handle)) {
				if (($node!='.')&&($node!='..')) {
					$node_full=$directory.$node;
					if (is_dir($node_full)) {
						$node=str_replace($this->base, '/', $node_full.'/');
						if ((!in_array($node, $this->ignored['dirs']))&&(!in_array(basename($node), $this->ignored['dirs_pattern']))) {
							$this->result[$node]=array('type'=>'dir','time'=>filemtime($node_full),'sha1'=>'');
							$this->getList($node_full.'/');
						}
					} else {
						$node=str_replace($this->base, '/', $node_full);
						if ((!in_array($node, $this->ignored['files']))&&(!in_array(basename($node), $this->ignored['files_pattern']))) {
							$this->result[$node]=array('type'=>'file','time'=>filemtime($node_full),'sha1'=>sha1_file($node_full));
						}
					}
				}
			}
			closedir($handle);
		}
	}

	public function getStatusName($status) {
		switch ($status) {
			case 0 :
				return '';
				break;
			case 1 :
				return '';
				break;
			case 2 :
				return '';
				break;
			case 3 :
				return '';
				break;
			default :
				return '';
				break;
		}
	}

	public function getSyncOption($master, $slave, $part) {
		if (($master==1)&&($slave==0)&&($part=='master')) {
			return '&nbsp;';
		}
		if (($master==1)&&($slave==0)&&($part=='slave')) {
			return 'create it';
		}
		if (($master==0)&&($slave==1)&&($part=='master')) {
			return 'create it';
		}
		if (($master==0)&&($slave==1)&&($part=='slave')) {
			return '&nbsp;';
		}
	}

	public function getTimeString($time) {
		if ($time==0) {
			return '&nbsp;';
		}
		return date('Y.m.d H:i:s', $time);
	}

	public function exec($url, $data=array()) {
		if (strlen(JBSNM_Sync::getInstance()->getProjectConfValue('pass'))>0) {
			$data['sessionid']=JBSNM_Sync_Session::getInstance()->getId();
		}
		foreach ($data as $key=>$value) {
			$data[$key]=strval($value);
		}

		if (strlen($this->getProjectConfValue('token'))>0) {
			$data['protect']=sha1(serialize($data).$this->getProjectConfValue('token'));
		}

		foreach ($data as $key=>$value) {
			$data[$key]=urlencode($value);
		}

		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		if ((strlen(JBSNM_Sync::getInstance()->getProjectConfValue('htuser'))>0)&&(strlen(JBSNM_Sync::getInstance()->getProjectConfValue('htpass'))>0)) {
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, JBSNM_Sync::getInstance()->getProjectConfValue('htuser').':'.JBSNM_Sync::getInstance()->getProjectConfValue('htpass'));
		}
		$data=curl_exec($ch);
		curl_close($ch);
		return $data;
	}

	public function removeDir($dir) {
		if (!file_exists($dir)) {
			return true;
		}
		if (!is_dir($dir)||is_link($dir)) {
			return unlink($dir);
		}
		foreach (scandir($dir) as $item) {
			if ($item=='.'||$item=='..') {
				continue;
			}
			if (!$this->removeDir($dir.'/'.$item)) {
				chmod($dir.'/'.$item, JBSNM_Sync::getInstance()->getProjectConfValue('chmod_dirs'));
				if (!$this->removeDir($dir.'/'.$item)) {
					return false;
				}
			}
		}
		return rmdir($dir);
	}

	public function validatePassword($pwblank, $pwcrypted) {
		if (($pwblank!='')&&($pwcrypted!='')) {
			$stack=explode(':', $pwcrypted);

			if (sizeof($stack)!=2) {
				return false;
			}
			if (md5($stack[1].$pwblank)==$stack[0]) {
				return true;
			}
		}
		return false;
	}

	public function transferFile($side, $postdata) {
		$postdata['chunk_last']=false;
		$postdata['chunk_part']=0;
		while ($postdata['chunk_last']=='0') {
			$postdata['chunk_part']++;
			$postdata['action']='getnodedata';

			if ($side=='master') {
				$data=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', $postdata), true);
			} else {
				$data=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', $postdata), true);
			}

			$postdata['action']='checknodedata';
			if ($data['chunk_last']!='0') {
				$postdata['chunk_last']='1';
			}

			if ($side=='master') {
				$data_check=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', $postdata), true);
			} else {
				$data_check=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', $postdata), true);
			}

			if (($data_check==null)||(!isset($data_check['sha1']))||($data==null)||(!isset($data['sha1']))||($data_check['sha1']!=$data['sha1'])) {
				$postdata['sha1']=$data['sha1'];
				$postdata['action']='setnodedata';
				$postdata['sha1_file']=$data['sha1_file'];
				$postdata['content']=$data['content'];
				$data=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue($side).'index.php', $postdata);
			} else {
				$data='sync ok';
			}
		}
		return $data;
	}

	/**
	 *
	 * @return JBSNM_Sync
	 */
	public static function getInstance() {
		return parent::getInstance();
	}
}

?>