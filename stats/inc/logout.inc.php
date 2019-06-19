<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

function logout() {
  global $string,$option,$phpstats_title;
  $phpstats_title=$string['logout_title'];
  $body="<img src=\"templates/$option[template]/images/icon_done.gif\" align=\"middle\"><span class=\"tabletextA\">&nbsp;$string[logout_done]&nbsp;</span>";
  $return=info_box("<b>$string[logout]</b>",$body);
  return($return);
}
?>
