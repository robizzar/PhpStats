<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

function login() {
  global $option,$string,$opzioni,$style,$phpstats_title;
  $phpstats_title=$string['login_title'];
  $body='<form name="login" action="admin.php?action=enter';
  $js_confirm_msg=$string['forgot_pass_js_confirm'];
  $body.="\" method=\"post\"><img src=\"templates/$option[template]/images/lock.png\" align=\"left\">$string[insert_pass] <br><input type=\"password\" name=\"pass\"><br><input type=\"Submit\" value=\"$string[login]\"><br><br><br><a href=\"admin.php?action=send_password\" onclick=\"return confirmLink(this,'$js_confirm_msg')\">$string[forgot_pass]</a></span><SCRIPT type='text/javascript'>document.login.pass.focus();</SCRIPT>"; //focus by dapuzz
  $return=info_box($string['login'],$body);
  return($return);
}
?>