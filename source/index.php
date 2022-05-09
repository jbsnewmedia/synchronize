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

/* $_POST-Parameter ueberarbeiten */
if (count($_POST)>0) {
	foreach ($_POST as $key=>$value) {
		$_POST[$key]=urldecode($value);
	}
}

/* Absoluter Pfad definieren */
define('abs_path', dirname(__FILE__).'/');

/* Allegeime Dinge einbinden */
require abs_path.'includes/general.inc.php';

/* Action setzen/laden */
if (!isset($_POST['action'])) {
	if (isset($_GET['action'])) {
		$_POST['action']=$_GET['action'];
	} else {
		$_POST['action']='';
	}
}

if (isset($_POST['project'])) {
	if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {

		if ((strlen(JBSNM_Sync::getInstance()->getProjectConfValue('token'))>0)&&(!in_array($_POST['action'], array('setauth', 'syncnode', 'synctable', 'updatemaster', 'updateslave')))) {
			if (!isset($_POST['protect'])) {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			} else {
				$project=$_POST['protect'];
				unset($_POST['protect']);

				if ($project!=sha1(serialize($_POST).JBSNM_Sync::getInstance()->getProjectConfValue('token'))) {
					JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
				}
			}
		}

		if ((strlen(JBSNM_Sync::getInstance()->getProjectConfValue('pass'))>0)&&(!in_array($_POST['action'], array('setauth', 'setsessionauth', 'syncnode', 'synctable', 'updatemaster', 'updateslave')))) {
			if (!isset($_POST['sessionid'])) {
				JBSNM_Sync::getInstance()->finish('error '.$_POST['action']);
			}

			$filename=abs_path.'.session/auth_'.$_POST['sessionid'];
			if (file_exists($filename)) {
				if (intval(file_get_contents($filename))<(time()-(60*30))) {
					JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
				}
				file_put_contents($filename, time());
			} else {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			}
		}
	} else {
		$_POST['action']='';
	}
}

/*
 * status	0 dosn't exists 1 new 2 old 3 in sync
 */
switch ($_POST['action']) {
	/*
	 * Setzt die Authentifikation
	 */
	case 'setauth':
		if (!isset($_POST['password'])) {
			JBSNM_Sync::getInstance()->finish('password unset');
		}
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('project unset');
		}
		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			$password=$_POST['password'];
			$project=$_POST['project'];
			if (strlen($password)>0) {
				if (JBSNM_Sync::getInstance()->validatePassword($password, JBSNM_Sync::getInstance()->getProjectConfValue('pass'))===true) {
					JBSNM_Sync_Session::getInstance()->set($project.'_enabled', true);

					$postdata=array();
					$postdata['action']='setsessionauth';
					$postdata['password']=$password;
					$postdata['project']=$project;

					$data=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', $postdata);
					$data=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', $postdata);
					if ($data=='ok') {
						JBSNM_Sync::getInstance()->finish('ok');
					}
				}
			}
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	/*
	 * Schreibt die Authentifikation in die Session
	 */
	case 'setsessionauth':
		if (!isset($_POST['password'])) {
			JBSNM_Sync::getInstance()->finish('password unset');
		}
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('project unset');
		}
		if (!isset($_POST['sessionid'])) {
			JBSNM_Sync::getInstance()->finish('sessionid unset');
		}
		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			$password=$_POST['password'];
			$project=$_POST['project'];
			$sessionid=$_POST['sessionid'];
			if (strlen($password)>0) {
				if (JBSNM_Sync::getInstance()->validatePassword($password, JBSNM_Sync::getInstance()->getProjectConfValue('pass'))===true) {
					$filename=abs_path.'.session/auth_'.$sessionid;
					file_put_contents($filename, time());
					JBSNM_Sync::getInstance()->finish('ok');
				}
			}
		}
		JBSNM_Sync::getInstance()->finish('error');
		break;
	case 'delauth':
		if (!isset($_POST['session'])) {
			JBSNM_Sync::getInstance()->finish('session unset');
		}
		if (JBSNM_Sync_Session::getInstance()->delete()===true) {
			JBSNM_Sync::getInstance()->finish('ok');
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	case 'shownodediff':
		if (!isset($_GET['node'])) {
			JBSNM_Sync::getInstance()->finish('node unset');
		}
		if (!isset($_GET['type'])) {
			JBSNM_Sync::getInstance()->finish('type unset');
		}
		if (!isset($_GET['diff'])) {
			JBSNM_Sync::getInstance()->finish('diff unset');
		}
		if (!isset($_GET['project'])) {
			JBSNM_Sync::getInstance()->finish('project unset');
		}
		if (false!==(JBSNM_Sync::getInstance()->setProject($_GET['project']))) {

			if ((JBSNM_Sync::getInstance()->getProject()!='')&&(JBSNM_Sync::getInstance()->getProjectConfValue('pass')!='')&&(JBSNM_Sync_Session::getInstance()->get(JBSNM_Sync::getInstance()->getProject().'_enabled')!==true)) {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			}

			$node=$_GET['node'];
			$type=$_GET['type'];
			$diff=$_GET['diff'];
			$project=$_GET['project'];

			$postdata=array();
			$postdata['action']='getnodedata';
			$postdata['project']=$project;
			$postdata['node']=$node;
			$postdata['chunk_part']=1;

			if ($diff=='master') {
				$data_from=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', $postdata), true);
				if ($data_from['chunk_last']=='0') {
					JBSNM_Sync::getInstance()->finish('file is too large for diff');
				}
				$content_from=base64_decode($data_from['content']);
				$data_to=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', $postdata), true);
				$content_to=base64_decode($data_to['content']);
			} elseif ($diff=='slave') {
				$data_to=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', $postdata), true);
				if ($data_to['chunk_last']=='0') {
					JBSNM_Sync::getInstance()->finish('file is too large for diff');
				}
				$content_to=base64_decode($data_to['content']);
				$data_from=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', $postdata), true);
				$content_from=base64_decode($data_from['content']);
			} else {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			}

			if ((sha1($content_from)!=$data_from['sha1'])||(sha1($content_to)!=$data_to['sha1'])) {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			}

			#$content_from=_utf8_decode($content_from);
			#$content_to=_utf8_decode($content_to);

			$from_len=strlen($content_from);
			$to_len=strlen($content_to);
			$start_time=gettimeofday(true);
			$finediff=new FineDiff($content_from, $content_to, FineDiff::$paragraphGranularity);
			$edits=$finediff->getOps();
			$exec_time=gettimeofday(true)-$start_time;
			$rendered_diff=$finediff->renderDiffToHTML();
			$rendering_time=gettimeofday(true)-$start_time;
			$diff_len=strlen($finediff->getOpcodes());
			$rendered_diff=str_replace("\r", '', $rendered_diff);
			$_rendered_diff=explode("\n", $rendered_diff);

			$i=0;
			$line=array();
			$code=array();
			$count=true;
			foreach ($_rendered_diff as $_rendered_diff_line) {
				if (strstr($_rendered_diff_line, '<del>')) {
					$count=false;
				}
				if (strstr($_rendered_diff_line, '</del>')) {
					$count=true;
				}
				if ($count==true) {
					$i++;
					$line[]='<div class="line_number">'.$i.'</div>';
				} else {
					$line[]='<div class="line_number">&nbsp;</div>';
				}
				$code[]=$_rendered_diff_line;
			}

			$rendered_diff='<table id="diff_container">';
			$rendered_diff.='<tr>';
			$rendered_diff.='<td class="diff_container_left">';
			$rendered_diff.=implode('', $line);
			$rendered_diff.='</td>';
			$rendered_diff.='<td class="diff_container_right">';
			foreach ($code as $line) {
				$rendered_diff.=htmlspecialchars_decode($line).'<br/>';
			}
			$rendered_diff.='</td>';
			$rendered_diff.='</tr>';
			$rendered_diff.='</table>';

			JBSNM_Sync_Output::getInstance()->header('Project: '.JBSNM_Sync::getInstance()->getProjectConfValue('name').' | File: '.$node, 'diff');
			JBSNM_Sync_Output::getInstance()->setLoader();

			$exec_time=number_format($exec_time, 3, '.', '');
			$rendering_time=number_format($rendering_time, 3, '.', '');

			JBSNM_Sync_Output::getInstance()->viewdiff($node, $rendered_diff, $exec_time, $rendering_time, $diff_len);

			JBSNM_Sync_Output::getInstance()->footer();
			JBSNM_Sync::getInstance()->finish();
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	case 'shownodecode':
		if (!isset($_GET['node'])) {
			JBSNM_Sync::getInstance()->finish('node unset');
		}
		if (!isset($_GET['type'])) {
			JBSNM_Sync::getInstance()->finish('type unset');
		}
		if (!isset($_GET['code'])) {
			JBSNM_Sync::getInstance()->finish('code unset');
		}
		if (!isset($_GET['project'])) {
			JBSNM_Sync::getInstance()->finish('project unset');
		}
		if (false!==(JBSNM_Sync::getInstance()->setProject($_GET['project']))) {

			if ((JBSNM_Sync::getInstance()->getProject()!='')&&(JBSNM_Sync::getInstance()->getProjectConfValue('pass')!='')&&(JBSNM_Sync_Session::getInstance()->get(JBSNM_Sync::getInstance()->getProject().'_enabled')!==true)) {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			}

			$node=$_GET['node'];
			$type=$_GET['type'];
			$code=$_GET['code'];
			$project=$_GET['project'];

			$postdata=array();
			$postdata['action']='getnodedata';
			$postdata['project']=$project;
			$postdata['node']=$node;
			$postdata['chunk_part']=1;

			if ($code=='master') {
				$data=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', $postdata), true);
				if ($data['chunk_last']=='0') {
					JBSNM_Sync::getInstance()->finish('file is too large for diff');
				}
				$content=base64_decode($data['content']);
			} elseif ($code=='slave') {
				$data=json_decode(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', $postdata), true);
				if ($data['chunk_last']=='0') {
					JBSNM_Sync::getInstance()->finish('file is too large for diff');
				}
				$content=base64_decode($data['content']);
			} else {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			}

			if (sha1($content)!=$data['sha1']) {
				JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
			}

			$content=utf8_decode($content);
			$content=str_replace("\r", '', $content);
			$_rendered_code=explode("\n", $content);

			$i=0;
			$line=array();
			$code=array();
			$count=true;
			foreach ($_rendered_code as $_rendered_code_line) {
				$i++;
				$line[]='<div class="line_number">'.$i.'</div>';
				$code[]=$_rendered_code_line;
			}

			$rendered_code='<table id="code_container">';
			$rendered_code.='<tr>';
			$rendered_code.='<td class="code_container_left">';
			$rendered_code.=implode('', $line);
			$rendered_code.='</td>';
			$rendered_code.='<td class="code_container_right">';
			foreach ($code as $line) {
				$rendered_code.=htmlspecialchars($line).'<br/>';
			}
			$rendered_code.='</td>';
			$rendered_code.='</tr>';
			$rendered_code.='</table>';

			JBSNM_Sync_Output::getInstance()->header('Project: '.JBSNM_Sync::getInstance()->getProjectConfValue('name').' | File: '.$node, 'code');
			JBSNM_Sync_Output::getInstance()->setLoader();

			JBSNM_Sync_Output::getInstance()->viewcode($node, $rendered_code);

			JBSNM_Sync_Output::getInstance()->footer();
			JBSNM_Sync::getInstance()->finish();
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	case 'getnodedata':
		if (!isset($_POST['node'])) {
			JBSNM_Sync::getInstance()->finish('getnodedata: node unset');
		}
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('getnodedata: project unset');
		}
		if (!isset($_POST['chunk_part'])) {
			JBSNM_Sync::getInstance()->finish('getnodedata: chunk_part unset');
		}
		$_POST['chunk_part']=intval($_POST['chunk_part']);
		if ($_POST['chunk_part']==0) {
			JBSNM_Sync::getInstance()->finish('getnodedata: chunk_part set 0');
		}
		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			$node=$_POST['node'];
			$file=JBSNM_Sync::getInstance()->getProjectConfValue('base').$node;
			if (file_exists($file)) {
				$result=array();

				$file_size=filesize($file);

				if ((($_POST['chunk_part'])*JBSNM_Sync::getInstance()->getProjectConfValue('chunk_size'))>$file_size) {
					$result['chunk_last']=1;
				} else {
					$result['chunk_last']=0;
				}

				$file_content=file_get_contents($file, false, null, ($_POST['chunk_part']-1)*JBSNM_Sync::getInstance()->getProjectConfValue('chunk_size'), JBSNM_Sync::getInstance()->getProjectConfValue('chunk_size'));
				$result['sha1_file']=sha1_file($file);
				$result['sha1']=sha1($file_content);
				$result['content']=base64_encode($file_content);

				JBSNM_Sync::getInstance()->finish(json_encode($result));
			}
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	case 'checknodedata':
		if (!isset($_POST['node'])) {
			JBSNM_Sync::getInstance()->finish('checknodedata: node unset');
		}
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('checknodedata: project unset');
		}
		if (!isset($_POST['chunk_part'])) {
			JBSNM_Sync::getInstance()->finish('checknodedata: chunk_part unset');
		}
		$_POST['chunk_part']=intval($_POST['chunk_part']);
		if ($_POST['chunk_part']==0) {
			JBSNM_Sync::getInstance()->finish('checknodedata: chunk_part set 0');
		}
		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			$node=$_POST['node'];
			$file=JBSNM_Sync::getInstance()->getProjectConfValue('base').$node;
			if (file_exists($file)) {
				$result=array();

				$file_size=filesize($file);

				if ((($_POST['chunk_part'])*JBSNM_Sync::getInstance()->getProjectConfValue('chunk_size'))>$file_size) {
					$result['chunk_last']=1;
				} else {
					$result['chunk_last']=0;
				}

				$file_content=file_get_contents($file, false, null, ($_POST['chunk_part']-1)*JBSNM_Sync::getInstance()->getProjectConfValue('chunk_size'), JBSNM_Sync::getInstance()->getProjectConfValue('chunk_size'));
				$result['sha1']=sha1($file_content);

				JBSNM_Sync::getInstance()->finish(json_encode($result));
			}
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	case 'setnodedata':
		if (!isset($_POST['node'])) {
			JBSNM_Sync::getInstance()->finish('setnodedata: node unset');
		}
		if (!isset($_POST['type'])) {
			JBSNM_Sync::getInstance()->finish('setnodedata: type unset');
		}
		if (!isset($_POST['status'])) {
			JBSNM_Sync::getInstance()->finish('setnodedata: status unset');
		}
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('setnodedata: project unset');
		}

		$node=$_POST['node'];
		$type=$_POST['type'];
		$status=$_POST['status'];

		if (($type=='file')&&($status!='delete')) {
			if (!isset($_POST['chunk_part'])) {
				JBSNM_Sync::getInstance()->finish('setnodedata: chunk_part unset');
			}
			if (!isset($_POST['chunk_last'])) {
				JBSNM_Sync::getInstance()->finish('setnodedata: chunk_last unset');
			}
			$_POST['chunk_part']=intval($_POST['chunk_part']);
			if ($_POST['chunk_part']==0) {
				JBSNM_Sync::getInstance()->finish('setnodedata: chunk_part set 0');
			}
		}

		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			if ($type=='dir') {
				$dir=explode('/', substr($node, 1, -1));
				$create_dir=JBSNM_Sync::getInstance()->getProjectConfValue('base');
				if ($status=='create') {
					foreach ($dir as $_node) {
						$create_dir=$create_dir.$_node.'/';
						if (!is_dir($create_dir)) {
							mkdir($create_dir);
						}
						if (!is_dir($create_dir)) {
							JBSNM_Sync::getInstance()->finish('setnodedata: sync dcreate error');
						}
						chmod($create_dir, JBSNM_Sync::getInstance()->getProjectConfValue('chmod_dirs'));
					}
					JBSNM_Sync::getInstance()->finish('sync ok');
				}

				if ($status=='delete') {
					$dir=JBSNM_Sync::getInstance()->getProjectConfValue('base').substr($node, 1);
					JBSNM_Sync::getInstance()->removeDir($dir);
					if (is_dir($dir)) {
						JBSNM_Sync::getInstance()->finish('setnodedata: sync ddelete error');
					}
					JBSNM_Sync::getInstance()->finish('sync ok');
				}
				JBSNM_Sync::getInstance()->finish('setnodedata: dir error');
			}

			if ($type=='file') {
				if (($status=='create')||($status=='update')) {
					if (!isset($_POST['sha1'])) {
						JBSNM_Sync::getInstance()->finish('setnodedata: sha1 unset');
					}
					if (!isset($_POST['content'])) {
						JBSNM_Sync::getInstance()->finish('setnodedata: content unset');
					}

					$sha1=$_POST['sha1'];
					$sha1_file=$_POST['sha1_file'];
					$content=$_POST['content'];
					$project=$_POST['project'];

					$content=base64_decode($content);
					if (sha1($content)==$sha1) {
						$dir=explode('/', substr($node, 1));
						array_pop($dir);
						$create_dir=JBSNM_Sync::getInstance()->getProjectConfValue('base');
						foreach ($dir as $_node) {
							$create_dir=$create_dir.$_node.'/';
							if (!is_dir($create_dir)) {
								mkdir($create_dir);
							}
							chmod($create_dir, JBSNM_Sync::getInstance()->getProjectConfValue('chmod_dirs'));
						}
						$file=JBSNM_Sync::getInstance()->getProjectConfValue('base').substr($node, 1);

						$path_parts=pathinfo($file);
						$file_part=$path_parts['dirname'].'/~part.'.$path_parts['basename'];

						if ($_POST['chunk_part']==1) {
							file_put_contents($file_part, $content);
							chmod($file_part, JBSNM_Sync::getInstance()->getProjectConfValue('chmod_files'));
						} else {
							file_put_contents($file_part, $content, FILE_APPEND);
						}

						if ($_POST['chunk_last']=='1') {
							rename($file_part, $file);
							if (sha1_file($file)==$sha1_file) {
								JBSNM_Sync::getInstance()->finish('sync ok');
							}
						} else {
							if (sha1($content)==$sha1) {
								JBSNM_Sync::getInstance()->finish('sync ok');
							}
						}
					}
					if ($status=='create') {
						unlink($file);
						JBSNM_Sync::getInstance()->finish('setnodedata: sync fcreate error');
					}
					if ($status=='update') {
						unlink($file);
						JBSNM_Sync::getInstance()->finish('setnodedata: sync fupdate error');
					}
				}

				if ($status=='delete') {
					$file=JBSNM_Sync::getInstance()->getProjectConfValue('base').substr($node, 1);
					if (file_exists($file)) {
						unlink($file);
						if (file_exists($file)) {
							JBSNM_Sync::getInstance()->finish('setnodedata: sync fdelete error');
						}
					}
					JBSNM_Sync::getInstance()->finish('sync ok');
				}
			}
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	case 'getlist':
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('project unset');
		}

		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			JBSNM_Sync::getInstance()->finish(json_encode(JBSNM_Sync::getInstance()->getListData()));
		}
		JBSNM_Sync::getInstance()->finish('error ('.__LINE__.')');
		break;
	case 'syncnode':
		if (!isset($_POST['node'])) {
			JBSNM_Sync::getInstance()->finish('node unset');
		}
		if (!isset($_POST['type'])) {
			JBSNM_Sync::getInstance()->finish('type unset');
		}
		if (!isset($_POST['side'])) {
			JBSNM_Sync::getInstance()->finish('side unset');
		}
		if (!isset($_POST['status'])) {
			JBSNM_Sync::getInstance()->finish('status unset');
		}
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('project unset');
		}

		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			$node=$_POST['node'];
			$type=$_POST['type'];
			$side=$_POST['side'];
			$status=$_POST['status'];
			$project=$_POST['project'];

			$postdata=array();
			$postdata['project']=$project;
			$postdata['node']=$node;
			$postdata['type']=$type;
			$postdata['status']=$status;

			if (($side=='master')||($side=='slave')) {
				$postdata['action']='setnodedata';

				if ($type=='dir') {
					if ($status=='create') {
						$data=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue($side).'index.php', $postdata);
						JBSNM_Sync::getInstance()->finish($data);
					}

					if ($status=='delete') {
						$data=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue($side).'index.php', $postdata);
						JBSNM_Sync::getInstance()->finish($data);
					}
				}

				if ($type=='file') {
					if (($status=='create')||($status=='update')) {
						$data=JBSNM_Sync::getInstance()->transferFile($side, $postdata);
						JBSNM_Sync::getInstance()->finish($data);
					}

					if ($status=='delete') {
						$data=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue($side).'index.php', $postdata);
						JBSNM_Sync::getInstance()->finish($data);
					}
				}
				JBSNM_Sync::getInstance()->finish('sync error');
			}

			JBSNM_Sync::getInstance()->finish('side error');
		}
		JBSNM_Sync::getInstance()->finish('project error');
	/*
	 * Liefert die Daten der Synctabellen als HTML
	 */
	case 'synctable':
		if (!isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->finish('project unset');
		}

		if (false!==(JBSNM_Sync::getInstance()->setProject($_POST['project']))) {
			JBSNM_Sync_Output::getInstance()->synclist();
			JBSNM_Sync::getInstance()->finish();
		}
		break;
	/*
	 * Liefert die aktuelle Version
	 */
	case 'version':
		JBSNM_Sync::getInstance()->finish(JBSNM_Sync::getInstance()->getVersion());
		break;
	/*
	 * Liefert das aktuelle Release
	 */
	case 'release':
		JBSNM_Sync::getInstance()->finish(JBSNM_Sync::getInstance()->getRelease());
		break;
	/*
	 * Aktualisiert das Programm (Client)
	 */
	case 'updateclient':
		if (JBSNM_Sync_Update::getInstance()->doUpdate()===true) {
			JBSNM_Sync::getInstance()->finish('synchronize updated successfully ('.JBSNM_Sync::getInstance()->getVersion().' => '.JBSNM_Sync::getInstance()->getCurrentVersion(JBSNM_Sync::getInstance()->getRelease()).')');
		}
		JBSNM_Sync::getInstance()->finish('synchronize is up2date ('.JBSNM_Sync::getInstance()->getVersion().')');
		break;
	/*
	 * Aktualisiert das Programm (Master)
	 */
	case 'updatemaster':
		JBSNM_Sync::getInstance()->finish(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', array('action'=>'updateclient')));
		break;
	/*
	 * Aktualisiert das Programm (Slave)
	 */
	case 'updateslave':
		JBSNM_Sync::getInstance()->finish(JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', array('action'=>'updateclient')));
		break;
	default :
		if (isset($_POST['project'])) {
			JBSNM_Sync::getInstance()->setProject($_POST['project']);
		}

		JBSNM_Sync_Output::getInstance()->header();
		JBSNM_Sync_Output::getInstance()->setLoader();
		JBSNM_Sync_Output::getInstance()->synclist();
		JBSNM_Sync_Output::getInstance()->footer();
		JBSNM_Sync::getInstance()->finish();
		break;
}

?>