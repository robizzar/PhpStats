<?php
                      if(!isset($_COOKIE)) $_COOKIE=$HTTP_COOKIE_VARS;
                         if(!isset($_GET)) $_GET=$HTTP_GET_VARS;
          if(isset($_GET['currentstyle'])) $currentstyle=addslashes($_GET['currentstyle']); else $currentstyle='';

// Per ragioni di sicurezza i file inclusi avranno un controllo di provenienza
define('IN_PHPSTATS', true);
$style=''; // In caso di register globals=on

// inclusione delle principali funzioni esterne
if(!include('../config.php')) die('<b>ERRORE</b>: File config.php non accessibile.');
if(!include('main_func.inc.php')) die('<b>ERRORE</b>: File main_func.inc.php non accessibile.');
if(!include('user_func.inc.php')) die('<b>ERRORE</b>: File user_func.inc.php non accessibile.');

// Connessione a MySQL e selezione database
db_connect();

//Leggo le variabili di configurazione.
$result=sql_query("SELECT name,value FROM $option[prefix]_config");
while($row=mysql_fetch_row($result)) $option[$row[0]]=$row[1];
if($option['use_pass']) if(!user_is_logged_in()) { header("Location: $option[script_url]/admin.php?action=login"); die(); }
if($option['template']=='') $option['template']='default';
if(!is_dir('templates/'.$option['template'])) $template_path='../templates/default'; else $template_path='../templates/'.$option['template'];
include('../lang/'.$option['language'].'/main_lang.inc');
include("$template_path/def.php");

$return=
'
<script language="JavaScript">
function vai(RadioObject){
	var sel=RadioObject.value;
	window.opener.location="../admin.php?action=preferenze&newstyle="+sel;
	self.close();
}
</script>';

$return.=
"\n<form method=\"POST\" action=\"../admin.php?action=preferenze\" name=\"formradio\">".
"\n<TABLE $style[table_header] width=\"300\">".
"\n\t<tr><td bgcolor=\"$style[table_title_bgcolor]\" colspan=\"3\"><span class=\"tabletitle\"><center>".$pref['popup_select_tit']."</center></span></td></tr>".
"\n\t<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>0</b></td><td bgcolor=$style[table_bgcolor]><input type=\"radio\" name=\"newstyle\" value=\"0\"  onclick=\"javascript:vai(this);\" class=\"radio\"".
($option['stile']=='0' ? 'checked' : '').
"></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pref[style_2]</span></td></tr>";

// Inizio lettura directory STILI
$location='../stili/';
$hook=opendir($location);
while(($file=readdir($hook))!==false)
   {
   if($file!='.' && $file!='..')
     {
     $path=$location.'/'.$file;
     if(is_dir($path)) $elenco[]=$file;
     }
   }
closedir($hook);
natsort($elenco);
// Fine lettura directory STILI

if($currentstyle!='') $option['stile']=$currentstyle;
while(list($key,$val)=each($elenco))
  {
  $val=chop($val);
  $return.=
  "\n\t<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$val</b></td><td bgcolor=$style[table_bgcolor] width=\"10\"><input type=\"radio\" name=\"newstyle\" value=\"$val\"".
  ($val==$option['stile'] ? 'checked' : '').
  " onclick=\"javascript:vai(this);\" class=\"radio\"></span></td><td bgcolor=$style[table_bgcolor]>";
  for($i=0; $i<10; $i=$i+1) $return.="<IMG SRC=\"../stili/$val/$i.gif\">";
  $return.="</tr></td>";
  }
$return.="\n</table>";

echo
"<html>\n<head>\n<title>".$pref['popup_select_tit']."</title>".
"\n<link rel=\"stylesheet\" href=\"$template_path/styles.css\" type=\"text/css\">".
"\n<META NAME=\"ROBOTS\" CONTENT=\"NONE\">".
"\n</head>".
"\n<body bgcolor=\"$style[bg_pops]\" onload=\"self.focus()\">".
$return.
"\n</body>\n</html>";
?>
