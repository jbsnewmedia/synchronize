<?php

## rename to "update_custom.php" for execution after update process

// version <2
$filename='./classes/osw_object.php';
if (file_exists($filename)) {
	unlink($filename);
}

// version <2
$filename='./classes/osw_output.php';
if (file_exists($filename)) {
	unlink($filename);
}

// version <2
$filename='./classes/osw_session.php';
if (file_exists($filename)) {
	unlink($filename);
}

// version <2
$filename='./classes/osw_sync.php';
if (file_exists($filename)) {
	unlink($filename);
}

$filename='./conf/demo.inc.php';
if (file_exists($filename)) {
	unlink($filename);
}

$filename='./demo.php';
if (file_exists($filename)) {
	unlink($filename);
}

$filename='./htaccess.php';
if (file_exists($filename)) {
	unlink($filename);
}

$filename='./password.php';
if (file_exists($filename)) {
	unlink($filename);
}

$filename='./_update_custom.php';
if (file_exists($filename)) {
	unlink($filename);
}

?>