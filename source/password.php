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
$password='12345678';

$salt=substr(md5($password.microtime(true)), 0, 2);
$password=md5($salt.$password).':'.$salt;
echo 'password: '.$password.'<br/>';
die('password created successfully');

?>