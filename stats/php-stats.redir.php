<?php
if(!defined('__PHP_STATS_PATH__'))
	die('undefined __PHP_STATS_PATH__');

$GLOBALS['php_stats_appendVarJs']=$GLOBALS['php_stats_sendVarJs']=$GLOBALS['php_stats_script_url']='';

require(__PHP_STATS_PATH__.'php-stats.recphp.php');

if($php_stats_ok==1)
{
//	echo '<!-- PHP-STATS --><script type="text/javascript" src="'.$GLOBALS['php_stats_script_url'].'/php-stats.phpjs.php?'.$GLOBALS['php_stats_appendVarJs'].'"></script>';
	if (ob_get_length() !== FALSE)
		ob_start();

	$scriptFullUrl = $GLOBALS['php_stats_script_url'] . '/php-stats.phpjs.php?' . $GLOBALS['php_stats_appendVarJs'];
	echo '<!-- PHP-STATS -->' . '<script type="text/javascript" src="' . $scriptFullUrl . '"></script>';

	if (ob_get_length() !== FALSE)
		ob_end_clean();
}
?>

