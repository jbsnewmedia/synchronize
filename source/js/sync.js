/**
 * @author Juergen Schwind
 * @copyright Copyright (c), JBS New Media UG
 * @package JBS New Media - Synchronize
 * @link http://jbs-newmedia.de
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 *
 */

$(function() {
	//updateStatus();
});

function selectproject() {
	$('#loader').show();
	project=$('#option_selectproject').val();
	password=$('#option_password').val();
	setAuth(project, password);
	/*loadSyncTable(project);*/
	updateStatus();
	$('#loader').hide();
	$("#synctable_table").tablesorter({
		sortList: [[1,0]],
		headers: {
			0: {
				sorter: false,
			},
			1: {
				sorter: "text",
			},
			2: {
				sorter: false,
			},
			3: {
				sorter: false,
			},
			4: {
				sorter: "text",
			},
			5: {
				sorter: "text",
			},
			6: {
				sorter: "text",
			},
			7: {
				sorter: "text",
			},
			8: {
				sorter: false,
			},
		}
	}); 
}

function setAuth(project, password) {
	$.ajax({
		type: "POST",
		url: "index.php",
		async: false,
		data: {
			action: 'setauth',
			session: session_id,
			project: project,
			password: password
		},
		success: function (data) {
			loadSyncTable(project);
		}
	});
}

function clearAuth() {
	$.ajax({
		type: "POST",
		url: "index.php",
		async: false,
		data: {
			action: 'delauth',
			session: session_id
		}
	});
}

function loadSyncTable(project) {
	$.ajax({
		type: "POST",
		url: "index.php",
		async: false,
		data: {
			action: 'synctable',
			session: session_id,
			project: project
		},
		success: function(result){
			$('#synctable').html(result);
		}
	});
}

function updateStatus() {
	deletemaster_value=$('#chk_deletemaster').is(':checked');
	deleteslave_value=$('#chk_deleteslave').is(':checked');	

	$('table > tbody  > tr.node').each(function() {
		setSyncOptions(this.id);
	});
	
	$("#option_password").keydown(function(e) {
	    if (e.which == 13) {
	        selectproject();
	    }
	});

}

function updateSkipAll() {
	value=$('#chk_skipall').is(':checked');

	$('table > tbody  > tr.node').each(function() {
		changeSkip(this.id, value);
	});
}

function updateSkipAll() {
	value=$('#chk_skipall').is(':checked');

	$('table > tbody  > tr.node').each(function() {
		changeSkip(this.id, value);
	});
}

function clearStatusShort() {
	$('table > tbody  > tr.node').each(function() {
		$('#'+this.id+'_status_short').html('');
	});
}

function setNodeCss(node, side, type) {
	if ((side!='master')&&(side!='slave')) {
		return false;
	}
	if ((type!='create')&&(type!='update')&&(type!='delete')&&(type!='skip')&&(type!='')) {
		return false;
	}

	if (type=='') {
		$('#'+node+'_'+side).removeClass('create update delete skip');
	} else {
		$('#'+node+'_'+side).removeClass('create update delete skip').addClass(type);
	}
}

function setSyncOptions(node) {
	master_value=$('#'+node+'_master_value').val();
	slave_value=$('#'+node+'_slave_value').val();
	skip_value=$('#chk_'+node+'_skip').is(':checked');

	if (skip_value==true) {
		$('#'+node+'_master').html('(skip)');
		setNodeCss(node, 'master', 'skip');
		$('#'+node+'_slave').html('(skip)');
		setNodeCss(node, 'slave', 'skip');
		return true;
	}
	
	if ((master_value==2)&&(slave_value==1)) {
		$('#'+node+'_master').html('update it');
		setNodeCss(node, 'master', 'update');
		$('#'+node+'_slave').html('');
		setNodeCss(node, 'slave', '');
		return true;
	}
	if ((master_value==1)&&(slave_value==2)) {
		$('#'+node+'_master').html('');
		setNodeCss(node, 'master', '');
		$('#'+node+'_slave').html('update it');
		setNodeCss(node, 'slave', 'update');
		return true;
	}	
	if ((master_value==1)&&(slave_value==0)) {
		if (deletemaster_value==true) {
			$('#'+node+'_master').html('delete it');
			setNodeCss(node, 'master', 'delete');
			$('#'+node+'_slave').html('');
			setNodeCss(node, 'slave', '');
			return true;
		} else {
			$('#'+node+'_master').html('&nbsp;');
			setNodeCss(node, 'master', '');
			$('#'+node+'_slave').html('create it');
			setNodeCss(node, 'slave', 'create');
			return true;
		}
	}
	if ((master_value==0)&&(slave_value==1)) {
		if (deleteslave_value==true) {
			$('#'+node+'_master').html('&nbsp;');
			setNodeCss(node, 'master', '');
			$('#'+node+'_slave').html('delete it');
			setNodeCss(node, 'slave', 'delete');
			return true;
		} else {
			$('#'+node+'_master').html('create it');
			setNodeCss(node, 'master', 'create');
			$('#'+node+'_slave').html('&nbsp;');
			setNodeCss(node, 'slave', '');
			return true;
		}
	}
}

function updateSkip(id) {
	if ($('#node_'+id+'_type').val()=='dir') {
		dir=$('#node_'+id+'_resources').html();
		len=dir.length;
		val=$('#chk_node_'+id+'_skip').is(':checked');
		$('table').find('.node').each(function() {
			if ($('#'+this.id+'_resources').html().substr(0, len)==dir) {
				changeSkip(this.id, val);
			}
		});
	}
}

function changeSkip(id, value) {
	if (value==true) {
		$('#chk_'+id+'_skip').prop('checked', true);
	} else {
		$('#chk_'+id+'_skip').prop('checked', false);
	}
}

function sync() {
	clearStatusShort();
	$('table').find('.node').each(function(){
		val=$('#chk_'+this.id+'_skip').is(':checked');
		if (val===false) {
			syncNode(this.id);
		}
	});
}

function syncNode(node) {
	master_value=$('#'+node+'_master_value').val();
	slave_value=$('#'+node+'_slave_value').val();
	deletemaster_value=$('#chk_deletemaster').is(':checked');
	deleteslave_value=$('#chk_deleteslave').is(':checked');
	autoscroll_value=$('#chk_autoscroll').is(':checked');
	asynchronously_value=$('#chk_asynchronously').is(':checked');
	type=$('#'+node+'_type').val();
	name=$('#'+node+'_resources').html();

	if ((master_value==2)&&(slave_value==1)) {
		sync_side='master';
		sync_status='update';
	}
	if ((master_value==1)&&(slave_value==2)) {
		sync_side='slave';
		sync_status='update';
	}

	if ((master_value==1)&&(slave_value==0)) {
		if (deletemaster_value==true) {
			sync_side='master';
			sync_status='delete';
		} else {
			sync_side='slave';
			sync_status='create';
		}
	}
	if ((master_value==0)&&(slave_value==1)) {
		if (deleteslave_value==true) {
			sync_side='slave';
			sync_status='delete';
		} else {
			sync_side='master';
			sync_status='create';
		}
	}
	
	project = $('#option_selectproject').val();

	$('#'+node+'_status_short').html('S');
	
	$.ajax({
		type: "POST",
		url: "index.php",
		async: asynchronously_value,
		data: {
			action: 'syncnode',
			session: session_id,
			node: name,
			type: type,
			side: sync_side,
			status: sync_status,
			project: project
		},
		success: function(result){
			if (result=='sync ok') {
				$('#'+node+'_status_short').html('✔');
			} else {
				$('#'+node+'_status_short').html('✘');
			}
		},
		error: function(result){
			$('#'+node+'_status_short').html('✘');
		}
	});
	
	if (autoscroll_value==true) {
		window.location.hash = $('#'+node).attr("id");
	}
}

$(window).bind('beforeunload', function(){
	clearAuth();
});