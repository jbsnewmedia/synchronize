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
define('abs_path', dirname(__FILE__).'/');

require abs_path.'includes/general.inc.php';

$dir_master='../dir/';
$dir_slave='../../sync_slave/dir/';

JBSNM_Sync::getInstance()->removeDir($dir_master.'');
JBSNM_Sync::getInstance()->removeDir($dir_slave.'');

mkdir($dir_master);
chmod($dir_master, 0755);
mkdir($dir_master.'dir_master');
chmod($dir_master.'dir_master', 0755);
mkdir($dir_master.'dir_master_blank');
chmod($dir_master.'dir_master_blank', 0755);
file_put_contents($dir_master.'master.php', "<?php\n\n# testfile\n\n?>");
chmod($dir_master.'master.php', 0644);
/*
for($i=0;$i<100;$i++) {
	file_put_contents($dir_master.'master_'.$i.'.php', "<?php\n\n# testfile\n\n?>");
	chmod($dir_master.'master_'.$i.'.php', 0644);
}
*/
file_put_contents($dir_master.'update.php', "<?php\n\n# testfile\n# new line\n# date('Y.m.d H:i:s', time())\n\n?>");
chmod($dir_master.'update.php', 0644);
file_put_contents($dir_master.'dir_master/master.php', "<?php\n\n# testfile\n\n?>");
chmod($dir_master.'dir_master/master.php', 0644);

mkdir($dir_slave);
chmod($dir_slave, 0755);
mkdir($dir_slave.'dir_slave');
chmod($dir_slave.'dir_slave', 0755);
mkdir($dir_slave.'dir_slave_blank');
chmod($dir_slave.'dir_slave_blank', 0755);
file_put_contents($dir_slave.'slave.php', "<?php\n\n# testfile\n\n?>");
chmod($dir_slave.'slave.php', 0644);
/*
for($i=0;$i<100;$i++) {
	file_put_contents('../../sync_slave/dir/slave_'.$i.'.php', "<?php\n\n# testfile\n\n?>");
	chmod('../../sync_slave/dir/slave_'.$i.'.php', 0644);
}
*/
file_put_contents($dir_slave.'update.php', "<?php\n\n# testfile\n# date('Y.m.d H:i:s', (time()-123213213))\n\n?>");
chmod($dir_slave.'update.php', 0644);
file_put_contents($dir_slave.'dir_slave/slave.php', "<?php\n\n # testfile\n\n?>");
chmod($dir_slave.'dir_slave/slave.php', 0644);

die('demo created successfully');

?>