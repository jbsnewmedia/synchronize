<?php

/* Config */
$_config=array();
$_config['name']='demo';
$_config['base']='../';
$_config['showcode']=array('js','css','json','php','txt');
$_config['showdiff']=array('js','css','json','php','txt');
$_config['directories']=array('../dir/');
$_config['chmod_files']=0644;
$_config['chmod_dirs']=0755;
$_config['ignored_files']=array();
$_config['ignored_dirs']=array('/sync');
$_config['master_link']='http://server_master/sync/';
$_config['slave_link']='http://server_slave/sync/';
$_config['token']='secretstring';
$_config['pass']='';
$_config['htuser']='test';
$_config['htpass']='sync';
$_config['chunk_size']=1024*1024*4;

?>