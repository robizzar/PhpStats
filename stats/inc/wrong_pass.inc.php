<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

function wrong_pass() {
global $db,$string,$option,$refresh,$url,$style,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['wrong_pass_title'];
$body="<img src=\"templates/$option[template]/images/icon_warning.gif\" align=\"middle\"><span class=\"tabletextB\">&nbsp;$string[wrong_pass]</span>";
$return=info_box($string['error'],$body);
$refresh=1;
$url=$option['script_url'].'/admin.php?action=login';
return($return);
}
?>
