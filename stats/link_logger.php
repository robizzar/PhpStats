<?php
define('IN_PHPSTATS',true);

if(isset($_COOKIE['php_stats_esclusion'])) 
	$php_stats_esclusion=$_COOKIE['php_stats_esclusion'];
else
	$php_stats_esclusion='';

require('option/php-stats-options.php');

if(strpos($php_stats_esclusion,"|$option[script_url]|")===FALSE || $option['stats_disabled'])
{
	require('inc/main_func_stats.inc.php');

	db_connect();

	$date = date('Y-m-d');//time()-$option['timezone']*3600;
	$d_url = urldecode($_GET['url']);
	$d_url = mysql_real_escape_string($d_url);
//	file_put_contents('link_logger.log', "$date|$d_url\n", FILE_APPEND);
	sql_query("INSERT DELAYED INTO $option[prefix]_links VALUES('$date','$d_url')");
}
$url = $_GET['url'];
header("Location: $url");
?>
