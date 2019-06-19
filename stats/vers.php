<?php
require('config.php');

$db = mysql_connect($option['host'],$option['user_db'],$option['pass_db']);
echo 'PHP version: ' . phpversion();
echo '<br>';
echo 'mySQL version: ' . mysql_get_server_info();
mysql_close($db);
?>
