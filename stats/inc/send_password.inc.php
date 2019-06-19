<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');
if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else $mode='send';
if(isset($_GET['key'])) $key=addslashes($_GET['key']); else $key='';

function send_password(){
global $db,$option,$style,$string,$varie,$modulo,$mode,$key,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['mail_sent_title'];
$return='';
if($mode=='send')
  {
  $user_pass_new=get_random(7);
  $user_pass_key=get_random(30);
  $site=explode("\n",$option['server_url']);
  $site_url=str_replace(Array('http://','https://'),'',$site[0]);

  $user_email=explode("\n",$option['user_mail']);
  $user_email=$user_email[0];
  $return_path='error@inutile.it';
  $activation_link=$option['script_url'].'/admin.php?action=send_password&mode=activate&key='.$user_pass_key;
  eval ("\$mail_messaggio=\"".gettemplate("lang/$option[language]/send_password.tpl")."\";");
  $intestazioni=
  "From: Php-Stats at $site_url<$user_email>\n".
  "X-Sender: <$user_email>\n".
  "X-Mailer: PHP-STATS\n"; // mailer
  "X-Priority: 1\n"; // Messaggio urgente!
  "Return-Path: <$return_path>\n";  // Indirizzo di ritorno per errori
  $ok=TRUE;
  $ok=mail($user_email,$string['mail_subject'],$mail_messaggio,$intestazioni);
  if($ok===FALSE) $ok=mail($user_email,$string['mail_subject'],$mail_messaggio);
  if($ok!==FALSE)
    {
    // Aggiorno pass e key di attivazione nel database
    sql_query("UPDATE $option[prefix]_config SET value='$user_pass_new' WHERE name='user_pass_new'");
    sql_query("UPDATE $option[prefix]_config SET value='$user_pass_key' WHERE name='user_pass_key'");
    $return.=info_box($string['mail_sent_title'],$string['mail_sent']);
    }
  else
    {
    $body="<img src=\"templates/$option[template]/images/icon_warning.gif\" align=\"middle\"><span class=\"tabletextB\">&nbsp;$string[mail_error]</span>";
    $return.=info_box($string['error'],$body);
    }
  }
if($mode=='activate')
  {
  $key=trim($key);
  $result=sql_query("SELECT name,value FROM $option[prefix]_config WHERE name like 'user_pass_%'");
  while($row=mysql_fetch_row($result)) $option[$row[0]]=$row[1];
  if(($key==$option['user_pass_key']) && ($option['user_pass_key']!='') && (strlen($key)==30))
    {
    /*** ***/
	user_change_password($option[user_pass_new]);
	sql_query("UPDATE $option[prefix]_config SET value='' WHERE name='user_pass_new'");
	sql_query("UPDATE $option[prefix]_config SET value='' WHERE name='user_pass_key'");
/***sql_query("UPDATE $option[prefix]_config SET value='$option[user_pass_new]' WHERE name='admin_pass'"); ***/
    create_option_file();
    $return.=info_box($string['new_pass_activ_title'],$string['new_pass_activ']);
    }
  else
    {
    $body="<img src=\"templates/$option[template]/images/icon_warning.gif\" align=\"middle\"><span class=\"tabletextB\">&nbsp;$string[new_pass_error]</span>";
    $return.=info_box($string['error'],$body);
    }
  }
return($return);
}
?>