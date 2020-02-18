function doUpdateAll(client, master, slave) {
	out='';
	if (client==1) {
		out=out+'Client: '+doUpdateClient(true)+'\n';
	}
	if (master==1) {
		out=out+'Master: '+doUpdateMaster(true)+'\n';
	}
	if (slave==1) {
		out=out+'Slave: '+doUpdateSlave(true)+'\n';
	}
	alert(out);
	location.reload(true);
}

function doUpdateClient(ret=false) {
	out='';
	$.ajax({
		type: "POST",
		url: "index.php",
		async: false,
		data: {
			action: 'updateclient',
			session: session_id
		},
		success: function(result){
			if (ret==true) {
				out=result;
			} else {
				alert(result);
				location.reload(true);
			}
		}
	});
	return out;
}

function doUpdateMaster(ret=false) {
	out='';
	$.ajax({
		type: "POST",
		url: "index.php",
		async: false,
		data: {
			action: 'updatemaster',
			session: session_id,
			project: project
		},
		success: function(result){
			if (ret==true) {
				out=result;
			} else {
				alert(result);
				location.reload(true);
			}
		}
	});
	return out;
}

function doUpdateSlave(ret=false) {
	out='';
	$.ajax({
		type: "POST",
		url: "index.php",
		async: false,
		data: {
			action: 'updateslave',
			session: session_id,
			project: project
		},
		success: function(result){
			if (ret==true) {
				out=result;
			} else {
				alert(result);
				location.reload(true);
			}
		}
	});
	return out;
}