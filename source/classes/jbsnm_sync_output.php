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
class JBSNM_Sync_Output extends JBSNM_Sync_Object {

	public function header($title='JBS New Media - Synchronize', $part='sync') {
		echo '<!doctype html>';
		echo '<html lang="en">';
		echo '<head>';
		echo '<meta charset="utf-8">';
		echo '<title>'.$title.'</title>';
		echo '<meta name="author" content="osWFrame.com">';
		echo '<link rel="stylesheet" href="./css/layout.css">';
		echo '<script src="./js/jquery.js"></script>';
		echo '<script src="./js/update.js"></script>';
		if ($part=='diff') {
			echo '<script src="./js/diff.js"></script>';
		} else {
			echo '<script src="./js/jquery.tablesorter.min.js"></script>';
			echo '<script src="./js/sync.js"></script>';
		}
		echo '</head>';
		echo '<body>';
	}

	public function footer() {
		echo '</body>';
		echo '</html>';
	}

	public function setLoader() {
		echo '<div style="display:none;" id="loader"><div><img src="img/loader.gif" alt="loader"></div></div>';
	}

	public function tableheader() {
		echo '<div id="synctable">';
		echo '<table>';
		echo '<tr class="header">';
		echo '<td>';
		echo '<span class="project_logo"><a href="https://jbs-newmedia.de" target="_blank"><img src="img/jbs_logo.png" alt="jbs_newmedia logo" title="JBS New Media"></a></span>';
		echo '<span class="project_name"><strong>JBS</strong> New Media - Synchronize</span>';
		echo '<span class="project_copyright">Copyright: <a href="https://juergen-schwind.de" target="_blank">Juergen Schwind</a> - <a href="https://jbs-newmedia.de/synchronize" target="_blank">JBS New Media GmbH</a></span>';
		echo '</td>';
		echo '</tr>';
	}

	public function tablefooter() {
		echo '</table>';
		echo '</div>';
	}

	public function viewdiff($node, $rendered_diff, $exec_time, $rendering_time, $diff_len) {
		$this->tableheader();
		echo '<tr class="option_project">';
		echo '<td>';
		echo '<span class="project_select">Project: ['.JBSNM_Sync::getInstance()->getProject().']</span>';
		if (JBSNM_Sync::getInstance()->getProject()!='') {
			echo '<span class="side">';
			echo 'Master: '.JBSNM_Sync::getInstance()->getProjectConfValue('master_label').' <strong>↔</strong> Slave:'.JBSNM_Sync::getInstance()->getProjectConfValue('slave_label');
			echo '</span>';
		}
		echo '<span class="project_version">';
		echo 'Version: '.JBSNM_Sync::getInstance()->getVersion();
		echo '<tr class="blank">';
		echo '<td>&nbsp;</td>';
		echo '</tr>';
		echo '<tr class="viewdiff_node">';
		echo '<td>';
		echo 'File: '.$node;
		echo '</td>';
		echo '</tr>';
		echo '<tr class="viewdiff_header">';
		echo '<td>';
		echo 'Stats: <span class="viewdiff_header_stats">(diff: '.$exec_time.' sec, rendering: '.$rendering_time.' sec, diff len: '.$diff_len.' chars)</span><span class="right">finediff by Raymond Hill [<a href="http://www.raymondhill.net/finediff/" target="_blank">http://www.raymondhill.net/finediff/</a>]</span>';
		echo '</td>';
		echo '</tr>';
		echo '<tr class="viewdiff_header">';
		echo '<td>';
		echo 'Help: use "left arrow" or "right arrow" for jumping to the changes';
		echo '</td>';
		echo '</tr>';
		echo '<tr class="viewdiff_content">';
		echo '<td>';
		echo $rendered_diff;
		echo '</td>';
		echo '</tr>';
		$this->tablefooter();
	}

	public function viewcode($node, $rendered_code) {
		$this->tableheader();
		echo '<tr class="option_project">';
		echo '<td>';
		echo '<span class="project_select">Project: ['.JBSNM_Sync::getInstance()->getProject().']</span>';
		if (JBSNM_Sync::getInstance()->getProject()!='') {
			echo '<span class="side">';
			echo 'Master: '.JBSNM_Sync::getInstance()->getProjectConfValue('master_label').' <strong>↔</strong> Slave:'.JBSNM_Sync::getInstance()->getProjectConfValue('slave_label');
			echo '</span>';
		}
		echo '<span class="project_version">';
		echo 'Version: '.JBSNM_Sync::getInstance()->getVersion();
		echo '</span>';
		echo '</td>';
		echo '</tr>';
		$this->updateBlock();
		echo '<tr class="blank">';
		echo '<td>&nbsp;</td>';
		echo '</tr>';
		echo '<tr class="viewcode_node">';
		echo '<td>';
		echo 'File: '.$node;
		echo '</td>';
		echo '</tr>';
		echo '<tr class="viewcode_content">';
		echo '<td>';
		echo $rendered_code;
		echo '</td>';
		echo '</tr>';
		$this->tablefooter();
	}

	public function synclist() {
		echo '<script type="text/javascript">var session_id=\''.JBSNM_Sync_Session::getInstance()->getId().'\';</script>';
		$this->tableheader();
		echo '<tr class="option_project">';
		echo '<td>';
		echo '<span class="project_select">Project: <select onchange="selectproject();" id="option_selectproject">';
		foreach (JBSNM_Sync::getInstance()->getConfArray() as $file=>$conf) {
			if ($file==JBSNM_Sync::getInstance()->getProject()) {
				echo '<option value="'.$file.'" selected="selected">'.$conf['name'].'</option>';
			} else {
				echo '<option value="'.$file.'">'.$conf['name'].'</option>';
			}
		}
		echo '</select>';
		echo '</span>';

		if (JBSNM_Sync::getInstance()->getProject()!='') {
			echo '<span class="side">';
			echo 'Master: '.JBSNM_Sync::getInstance()->getProjectConfValue('master_label').' <strong>↔</strong> Slave:'.JBSNM_Sync::getInstance()->getProjectConfValue('slave_label');
			echo '</span>';
		}
		echo '<span class="project_version">';
		echo 'Version: '.JBSNM_Sync::getInstance()->getVersion();
		echo '</span>';
		echo '</td>';
		echo '</tr>';
		$this->updateBlock();
		echo '<tr class="blank">';
		echo '<td>&nbsp;</td>';
		echo '</tr>';
		echo '</table>';
		echo '<table id="synctable_table" class="tablesorter">';
		echo '<thead>';
		echo '<tr class="node_header">';
		echo '<th colspan="1" class="node_status_short">&nbsp;</th>';
		echo '<th colspan="1" class="node_resources">Resources</th>';
		echo '<th colspan="1" class="node_sources">Sources</th>';
		echo '<th colspan="1" class="node_skip">Skip</th>';
		echo '<th colspan="1" class="node_master">Master</th>';
		echo '<th colspan="1" class="node_slave">Slave</th>';
		echo '<th colspan="1" class="node_localtime">Local Time</th>';
		echo '<th colspan="1" class="node_remotetime">Remote Time</th>';
		echo '<th colspan="1" class="node_status">Status</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		if ((JBSNM_Sync::getInstance()->getProject()!='')&&(JBSNM_Sync::getInstance()->getProjectConfValue('pass')!='')&&(JBSNM_Sync_Session::getInstance()->get(JBSNM_Sync::getInstance()->getProject().'_enabled')!==true)) {
			echo '<tr class="blank">';
			echo '<td colspan="9">Password required, please enter password <input id="option_password" name="input_password" type="password" /> and press "Enter" or "Reload project"</td>';
			echo '</tr>';
		} else {
			$i=0;
			if ((JBSNM_Sync::getInstance()->getProject()!='')&&(JBSNM_Sync::getInstance()->getSyncTable()!==array())&&(JBSNM_Sync::getInstance()->getSyncTable()!==false)) {
				foreach (JBSNM_Sync::getInstance()->getSyncTable() as $node=>$node_details) {
					if (($node_details['master']!=3)&&($node_details['slave']!=3)) {
						$i++;
						echo '<input type="hidden" id="node_'.$i.'_type" name="h_node_'.$i.'_type" value="'.$node_details['type'].'"/>';
						echo '<input type="hidden" id="node_'.$i.'_master_value" name="h_node_'.$i.'_master_value" value="'.$node_details['master'].'"/>';
						echo '<input type="hidden" id="node_'.$i.'_slave_value" name="h_node_'.$i.'_slave_value" value="'.$node_details['slave'].'"/>';
						echo '<tr id="node_'.$i.'" class="node">';
						echo '<td id="node_'.$i.'_status_short" class="node_status_short">';
						if ($node_details['viewdiff']===true) {
							echo '<td id="node_'.$i.'_resources" class="node_resources">'.$node.'';
							echo '<td id="node_'.$i.'_viewdiff" class="node_sources"><a target="_blank" href="index.php?action=shownodediff&node='.urlencode($node).'&diff='.$node_details['viewdiffmode'].'&type='.$node_details['type'].'&project='.JBSNM_Sync::getInstance()->getProject().'&session='.JBSNM_Sync_Session::getInstance()->getId().'">show</a>';
						} elseif ($node_details['viewcode']===true) {
							echo '<td id="node_'.$i.'_resources" class="node_resources">'.$node.'';
							echo '<td id="node_'.$i.'_viewcode" class="node_sources"><a target="_blank" href="index.php?action=shownodecode&node='.urlencode($node).'&code='.$node_details['viewcodemode'].'&&type='.$node_details['type'].'&project='.JBSNM_Sync::getInstance()->getProject().'&session='.JBSNM_Sync_Session::getInstance()->getId().'">show</a>';
						} else {
							echo '<td id="node_'.$i.'_resources" class="node_resources">'.$node.'</td>';
							echo '<td id="node_'.$i.'_resources" class="node_sources">-</td>';

						}
						echo '<td id="node_'.$i.'_skip" class="node_skip"><input onchange="updateSkip('.$i.'); updateStatus();" type="checkbox" name="node_'.$i.'_skip" id="chk_node_'.$i.'_skip"/></td>';
						echo '<td id="node_'.$i.'_master" class="node_master"></td>';
						echo '<td id="node_'.$i.'_slave" class="node_slave"></td>';
						echo '<td id="node_'.$i.'_localtime" class="node_localtime">'.JBSNM_Sync::getInstance()->getTimeString($node_details['time']).'</td>';
						echo '<td id="node_'.$i.'_remotetime" class="node_remotetime">'.JBSNM_Sync::getInstance()->getTimeString($node_details['slave_time']).'</td>';
						echo '<td id="node_'.$i.'_status" class="node_status">-</td>';
						echo '</tr>';
					}
				}
				if ($i==0) {
					echo '<tr class="blank">';
					echo '<td colspan="9">All files are completely in sync!</td>';
					echo '</tr>';
				}
			} elseif ((JBSNM_Sync::getInstance()->getSyncTable()===false)&&(JBSNM_Sync::getInstance()->getProject()!='')) {
				echo '<tr class="blank">';
				if (((JBSNM_Sync::getInstance()->getProjectConfValue('master_label')=='server_master')&&(JBSNM_Sync::getInstance()->getProjectConfValue('slave_label')=='server_slave'))||((JBSNM_Sync::getInstance()->getProjectConfValue('sync_error_master')===true)&&(JBSNM_Sync::getInstance()->getProjectConfValue('sync_error_slave')===true))) {
					echo '<td colspan="9">Master and Slave are not available!</td>';
				} elseif ((JBSNM_Sync::getInstance()->getProjectConfValue('master_label')=='server_master')||(JBSNM_Sync::getInstance()->getProjectConfValue('sync_error_master')===true)) {
					echo '<td colspan="9">Master is not available!</td>';
				} elseif ((JBSNM_Sync::getInstance()->getProjectConfValue('slave_label')=='server_slave')||(JBSNM_Sync::getInstance()->getProjectConfValue('sync_error_slave')===true)) {
					echo '<td colspan="9">Slave is not available!</td>';
				} else {
					echo '<td colspan="9">Syncerror</td>';
				}
				echo '</tr>';
			} else {
				echo '<tr class="blank">';
				echo '<td colspan="9">---</td>';
				echo '</tr>';
			}
		}
		echo '</tbody>';
		echo '</table>';
		echo '<table>';
		if (JBSNM_Sync::getInstance()->getProject()!='') {
			echo '<tr colspan="2" class="blank">';
			echo '<td colspan="2">&nbsp;</td>';
			echo '</tr>';
			echo '<tr class="option_header">';
			echo '<td colspan="2">Options</td>';
			echo '</tr>';
			echo '<tr class="option">';
			echo '<td width="1%"><input onclick="updateSkipAll();updateStatus();" type="checkbox" name="chk_skipall" id="chk_skipall" checked="checked"/></td>';
			echo '<td class="skipall">Skip all files</td>';
			echo '</tr>';
			echo '<tr class="option">';
			echo '<td width="1%"><input onclick="updateStatus();" type="checkbox" name="chk_deletemaster" id="chk_deletemaster"/></td>';
			echo '<td class="deletemaster">Delete orphaned files on Master</td>';
			echo '</tr>';
			echo '<tr class="option">';
			echo '<td width="1%"><input onclick="updateStatus();" type="checkbox" name="chk_deleteslave" id="chk_deleteslave"/></td>';
			echo '<td class="deletemaster">Delete orphaned files on Slave</td>';
			echo '</tr>';
			echo '<tr class="option">';
			echo '<td width="1%"><input onclick="" type="checkbox" name="chk_autoscroll" id="chk_autoscroll"/></td>';
			echo '<td class="autoscroll">Autoscroll</td>';
			echo '</tr>';
			echo '<tr class="option">';
			echo '<td width="1%"><input onclick="" type="checkbox" name="chk_asynchronously" id="chk_asynchronously"/></td>';
			echo '<td class="autoscroll">Synchronize asynchronously</td>';
			echo '</tr>';
			echo '<tr class="option">';
			echo '<td colspan="2" class="sync"><button onclick="sync();" name="btn_sync" id="btn_sync" type="button" value="">Synchronize</button> <button onclick="selectproject();" name="btn_reload" id="btn_reload" type="button" value="">Reload project</button></td>';
			echo '</tr>';
			echo '<script>updateSkipAll();updateStatus();</script>';
		}
		$this->tablefooter();
	}

	public function updateBlock() {
		$output_array=array();
		$update_array=array();
		$update_array['client']='0';
		$update_array['master']='0';
		$update_array['slave']='0';
		$update_count=0;
		if (JBSNM_Sync_Update::getInstance()->checkUpdate()===true) {
			$update_array['client']='1';
			$update_count++;
			$output_string='';
			$output_string.='Client: New version available ('.JBSNM_Sync::getInstance()->getVersion().' => '.JBSNM_Sync::getInstance()->getCurrentVersion(JBSNM_Sync::getInstance()->getRelease()).') [';
			$output_string.='<a href="https://jbs-newmedia.de/getsynchronizedownload" target="_blank">Download</a>';
			if (file_exists(abs_path.'update.php')) {
				$output_string.='|<a onclick="doUpdateClient()" target="_blank">Update</a>';
			}
			$output_string.=']';
			$output_array[]=$output_string;
		}
		if (JBSNM_Sync::getInstance()->getProject()!=='') {
			$version=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', array('action'=>'version'));
			if ((strlen($version)<4)||(strlen($version)>13)) {
				$version='0.0.0';
			}
			$release=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('master').'index.php', array('action'=>'release'));
			if ((strlen($release)<4)||(strlen($release)>6)) {
				$release='stable';
			}
			if (($version!='0.0.0')&&(JBSNM_Sync_Update::getInstance()->checkUpdate($version, JBSNM_Sync::getInstance()->getCurrentVersion($release))===true)) {
				$update_array['master']='1';
				$update_count++;
				$output_string='';
				$output_string.='Master: New version available ('.$version.' => '.JBSNM_Sync::getInstance()->getCurrentVersion($release).') [';
				if (strstr($version, 'RC')) {
					$output_string.='<a href="https://jbs-newmedia.de/getsynchronizebeta" target="_blank">Download</a>';
				} else {
					$output_string.='<a href="https://jbs-newmedia.de/getsynchronize" target="_blank">Download</a>';
				}
				if (file_exists(abs_path.'update.php')) {
					$output_string.='|<a onclick="doUpdateMaster()" target="_blank">Update</a>';
				}
				$output_string.=']';
				$output_array[]=$output_string;
			}
			$version=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', array('action'=>'version'));
			if ((strlen($version)<4)||(strlen($version)>13)) {
				$version='0.0.0';
			}
			$release=JBSNM_Sync::getInstance()->exec(JBSNM_Sync::getInstance()->getProjectConfValue('slave').'index.php', array('action'=>'release'));
			if ((strlen($release)<4)||(strlen($release)>6)) {
				$release='stable';
			}
			if (($version!='0.0.0')&&(JBSNM_Sync_Update::getInstance()->checkUpdate($version, JBSNM_Sync::getInstance()->getCurrentVersion($release))===true)) {
				$update_array['slave']='1';
				$update_count++;
				$output_string='';
				$output_string.='Slave: New version available ('.$version.' => '.JBSNM_Sync::getInstance()->getCurrentVersion($release).') [';
				if (strstr($version, 'RC')) {
					$output_string.='<a href="https://jbs-newmedia.de/getsynchronizebeta" target="_blank">Download</a>';
				} else {
					$output_string.='<a href="https://jbs-newmedia.de/getsynchronize" target="_blank">Download</a>';
				}
				if (file_exists(abs_path.'update.php')) {
					$output_string.='|<a onclick="doUpdateSlave()" target="_blank">Update</a>';
				}
				$output_string.=']';
				$output_array[]=$output_string;
			}
		}

		if ($output_array!=array()) {
			echo '<tr class="update">';
			echo '<td>';
			echo implode(' | ', $output_array);
			if ($update_count>1) {
				echo ' | [<a onclick="doUpdateAll('.implode(',', $update_array).')" target="_blank">Update all</a>]';
			}
			echo '</td>';
			echo '</tr>';
		}
	}

	/**
	 *
	 * @return JBSNM_Sync_Output
	 */
	public static function getInstance() {
		return parent::getInstance();
	}
}

?>
