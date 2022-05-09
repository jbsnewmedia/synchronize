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
define('abs_path', dirname(__FILE__).'/');

$abs_path=abs_path;
$authname='JBS New Media - Synchronize';
$username='test';
$password='sync';

$data="AuthType Basic\nAuthName \"$authname\"\nAuthUserFile $abs_path.htpasswd\nrequire valid-user\n";
file_put_contents(abs_path.'.htaccess', $data);
chmod(abs_path.'.htaccess', 0644);

$password=crypt($password);
$data="$username:$password\n";
file_put_contents(abs_path.'.htpasswd', $data);
chmod(abs_path.'.htpasswd', 0644);

die('htaccess created successfully');

?>