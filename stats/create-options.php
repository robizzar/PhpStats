<?php
define('IN_PHPSTATS',true);

// DEFINIZIONE VARIABILI PRINCIPALI
define ('__OPTIONS_FILE__','option/php-stats-options.php');
define ('__LOCK_FILE__','option/options_lock.php');

if(!isset($_COOKIE)) $_COOKIE=$HTTP_COOKIE_VARS;
if(!isset($_POST)) $_POST=$HTTP_POST_VARS;
if(isset($_POST['pswd'])) $pswd=addslashes($_POST['pswd']); else $pswd='';

require('config.php');
require('inc/main_func.inc.php');
require('inc/user_func.inc.php');

if($option['prefix']=='') $option['prefix']='php_stats';

db_connect();

if(user_is_logged_in() || user_login(false, $pswd)) create_options();
else{
    $return=
    '<html><title>:: Php-Stats - Create Option ::</title><body>'.
    '<center><br><br>'.
    '<form action="create-options.php" method="post">'.
    'Php-Stats Password: <input name="pswd" type="password" value=""><br><br>'.
    '<input type="submit" value="Invia - Send">'.
    '</center>'.
    '</body></html>';
    echo $return;
    }

if($option['persistent_conn']!=1) mysql_close();

function create_options(){
global $db,$option,$default_pages;

// ARRAY ORDINATA DEI VALORI CHE NON DEVONO ESSERE SCRITTI
$noWrite=Array('inadm_last_update','instat_report_w','instat_max_online','inadm_upd_available');

// Valori da memorizzare in formato stringa
$stringValue=Array('host','database','user_db','pass_db','dummy0','dummy1','prefix','ext_whois','language','server_url','template','nomesito','user_mail','user_pass_new','user_pass_key','phpstats_ver','exc_fol','exc_sip','exc_dip');

$options_text='<?php
if(!defined(\'IN_PHPSTATS\')) die("Php-Stats internal file.");
error_reporting(E_ERROR);
ignore_user_abort(true);

$option=Array(
';

// Scrivo le variabili presenti in config.php
while (list ($key, $value) = each ($option))
   {
   switch ($key)
     {
     case 'admin_pass':
     	continue;
     case 'script_url':
        if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' && substr($value,0,5)==='http:') $value='https:'.substr($value,5);
        if(substr($value,-1)==='/') $value=substr($value,0,-1);
        $options_text.="'$key'=>'$value',\n";
        break;
/*     case 'exc_pass':
        if($value=='pass') $value=rand();
        $options_text.=(in_array($key,$stringValue) ? "'$key'=>'$value',\n" : "'$key'=>$value,\n");
        break;*/
     default:
        $options_text.=(in_array($key,$stringValue) ? "'$key'=>'".addslashes($value)."',\n" : "'$key'=>$value,\n");
        break;
     }
    }
$result=sql_query("SELECT name,value FROM $option[prefix]_config");
while($row=mysql_fetch_row($result))
  {
  if (!(in_array($row[0],$noWrite)))
    {
    switch ($row[0])
     {
     case 'server_url':
        $tmpServerUrl=explode("\n",$row[1]);
        $options_text.="'$row[0]'=>'$row[1]',\n";
        break;
     case 'unlock_pages':
        $tmpUnlockPages=explode('|',$row[1]);
        break;
     case 'moduli':
        $tmpModuli=explode('|',$row[1]);
        break;
     case 'exc_fol':
        $options_text.="'$row[0]'=>'$row[1]',\n";
        $tmpExc_fol=explode("\n",$row[1]);
        break;
     case 'exc_sip':
        $options_text.="'$row[0]'=>'$row[1]',\n";
        $tmpExc_sip=explode("\n",$row[1]);
        break;
     case 'exc_dip':
        $options_text.="'$row[0]'=>'$row[1]',\n";
        $tmpExc_dip=explode("\n",$row[1]);
        break;
     case 'admin_pass':
     	continue;
     default:
        $options_text.=(in_array($row[0],$stringValue) ? "'$row[0]'=>'$row[1]',\n" : "'$row[0]'=>$row[1],\n");
        break;
     }
    }
  else array_shift($noWrite); // CON ARRAY_SHIFT AUMENTO LA VELOCITA' DI CREAZIONE DEL FILE
  }
$options_text=substr($options_text, 0, -2)."\n);\n\n";

$options_text.="\$modulo=Array(";
for($i=0,$tot=count($tmpModuli);$i<$tot-1;++$i) $options_text.="$tmpModuli[$i],";
$options_text=substr($options_text, 0, -1).");\n\n";

$page_list=Array('main','details','os_browser','reso','systems','pages','percorsi','time_pages','referer','engines','query','searched_words','hourly','daily','weekly','monthly','calendar','compare','ip','country','bw_lang','links','downloads','clicks','trend');

$options_text.="\$unlockedPages=Array(\n";
  if(in_array(1,$tmpUnlockPages)){
  for($i=0,$tot=count($tmpUnlockPages);$i<$tot-1;++$i) $options_text.=($tmpUnlockPages[$i]==1 ? "'$page_list[$i]',\n" : '');
  $options_text=substr($options_text, 0, -2)."\n);\n\n";
  }
  else $options_text.="''\n);\n\n";

$tot=count($tmpServerUrl);
if (($tot===1) && ($tmpServerUrl[0]=='')) $options_text.="\$countServerUrl=0;\n\n";
else {
     $options_text.="\$serverUrl=Array(\n";
     for($i=0;$i<$tot;++$i) $options_text.="'".($option['www_trunc'] ? str_replace('://www.','://',$tmpServerUrl[$i]) : $tmpServerUrl[$i])."',\n";
     $options_text=substr($options_text, 0, -2)."\n);\n\$countServerUrl=$tot;\n\n";
     }

$tot=count($tmpExc_fol);
if (($tot===1) && ($tmpExc_fol[0]=='')) $options_text.="\$countExcFol=0;\n\n";
else {
     $options_text.="\$excf=Array(\n";
     for($i=0;$i<$tot;++$i) $options_text.="'$tmpExc_fol[$i]',\n";
     $options_text=substr($options_text, 0, -1)."\n);\n\$countExcFol=$tot;\n\n";
     }

$tot=count($tmpExc_sip);
if (($tot===1) && ($tmpExc_sip[0]=='')) $options_text.="\$countExcSip=0;\n\n";
else {
     $options_text.="\$excsips=Array(\n";
     for($i=0,$tot=count($tmpExc_sip);$i<$tot;++$i) $options_text.="'$tmpExc_sip[$i]',\n";
     $options_text=substr($options_text, 0, -1)."\n);\n\$countExcSip=$tot;\n\n";
     }

$tot=count($tmpExc_dip);
if (($tot===1) && ($tmpExc_dip[0]=='')) $options_text.="\$countExcDip=0;\n\n";
else {
     $options_text.="\$excdips=Array(\n";
     for($i=0,$tot=count($tmpExc_dip);$i<$tot;++$i) $options_text.="'$tmpExc_dip[$i]',\n";
     $options_text=substr($options_text, 0, -1)."\n);\n\$countExcDip=$tot;\n\n";
     }

unset($tmpModuli,$tmpServerUrl,$tmpExc_fol,$tmpExc_sip,$tmpExc_dip);

// SCRIVO L'ARRAY DEFAULT PAGE
$options_text.="\$default_pages=Array(\n";
while (list ($key, $value) = each ($default_pages)) $options_text.="'$value',\n";
$options_text=substr($options_text, 0, -2)."\n);\n?>";

// CREO IL FILE DI LOCK E FACCIO UNO SLEEP DI 1 SEC ALTRIMENTI NON VIENE MAI RILEVATO
touch(__LOCK_FILE__);
sleep(1);

// CREAZIONE FILE OPTIONS.PHP
$optionsFile=fopen(__OPTIONS_FILE__, 'w+');
fwrite($optionsFile,$options_text);
fclose($optionsFile);

$ok=unlink(__LOCK_FILE__);

if ($ok) echo'<center>.:: OK - FILE OPTION ::.</center>'; else echo '<center><b>ERRORE - ERROR</b></center>';
}
?>
