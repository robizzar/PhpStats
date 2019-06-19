<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die("Php-Stats internal file.");

// Troncatura URL - URL Trunc
function formaturl($url, $title='', $maxwidth=60, $width1=15, $width2=-20, $link_title='', $mode=0){
  global $option,$short_url,$style;
  $iconType='None';

  if(trim($title)==='') $title='?'; //titolo pagina
/***
patch Francesco Mortara - fmortara@mfweb.it - 2012-01-06
    $longurl=(preg_match("/[a-z]:\/\//si", $url) ? $url : "http://$url"); //url lunga (per i link)
***/
    $longurl=(preg_match("/[a-z]:\/\//si", $url)) ?stripslashes($url) : stripslashes("http://$url"); //url lunga (per i link)


  $url=stripslashes($url);
  $title=stripslashes($title);

  $tmp=explode("\n",$option['server_url']);
  for($i=0,$tot=count($tmp);$i<$tot;++$i)
    {
    $server=trim($tmp[$i]);
    if($server=='') continue;
    if(strpos($url,$server)!==0) continue;
    if($option['short_url']) $url=str_replace($server,'',$url); //troncatura url - URL Trunc
    if(strpos($url,'.swf?page=')>0) $iconType='Flash';
    else $iconType='Home';
    break;
    }

  if($iconType=='None')
  {
  	if (strpos($longurl,'http://www.google')===0 || strpos($longurl,'http://google')===0)
  		$iconType='Google';
  	else if(strpos($longurl,'http://dwn|')===0)
  	 	$iconType='Download';
  	else if(strpos($longurl,'http://clk|')===0)
  	 	$iconType='Click';
  }

  if($url=='') $url='/';

  switch($iconType){
    default:
    case 'None': $icon=''; break;
    case 'Home': $icon="<img src=\"templates/$option[template]/images/icon_home.gif\" border=\"0\">"; break;
    case 'Flash': $icon="<img src=\"templates/$option[template]/images/icon_flash.gif\" border=\"0\">"; break;
    case 'Google': $icon="<img src=\"templates/$option[template]/images/icon_google.gif\" border=\"0\">"; break;
    case 'Download': $icon="<img src=\"templates/$option[template]/images/icon_download.gif\" border=\"0\">"; break;
    case 'Click': $icon="<img src=\"templates/$option[template]/images/icon_link.gif\" border=\"0\">"; break;
  }

  switch($mode){
    default:
    case 0://visualizza url - show url
//      $linktext=(strlen($url)>$maxwidth ? substr($url,0,$width1).'...'.substr($url,$width2) : $url);
      if ($width2==0)
       	$linktext=(strlen($url)>$maxwidth ? substr($url,0,$maxwidth).'...' : $url);
	  else
      	$linktext=(strlen($url)>$maxwidth ? substr($url,0,$width1).'...'.substr($url,$width2) : $url);
      break;
    case 1://visualizza titolo - show title
      if ($width2==0)
       	$linktext=(strlen($title)>$maxwidth ? substr($title,0,$maxwidth).'...' : $title);
	  else
      	$linktext=(strlen($title)>$maxwidth ? substr($title,0,$width1).'...'.substr($title,$width2) : $title);
	  break;
    case 2://visualizza titolo (url) - show title (url)
      $maxwidth-=3;//considero lo spazio e le parentesi

      $pos=strpos($url,'?');//cerco la query string  - find query
      if($pos!==FALSE) $url=substr($url,0,$pos);//taglio la query string  - cut query

      $titlelength=strlen($title);
      $urllength=strlen($url);
      if($titlelength+$urllength>$maxwidth){//controllo se titolo e url sono più lunghe di maxwidth - check if titles ad url lenght > maxwidth
        $tmp=floor($maxwidth/3);
        if($titlelength<$tmp*2){//uso lo spazio risparmiato per l'url
          $tmp=$maxwidth-$titlelength;//spazio disponibile per url
          $width1=floor(($tmp-3)/2);
	      if ($width2==0)
          	$url=substr($url,0,$width1).'...';
          else
          {
	        $width2=-$width1;
	        $url=substr($url,0,$width1).'...'.substr($url,$width2);
	      }
        }
        else if($urllength<$tmp){//uso lo spazio risparmiato per il title
          $tmp=$maxwidth-$urllength;//spazio disponibile per title
          $width1=floor(($tmp-3)/2);
	      if ($width2==0)
          	$title=substr($title,0,$width1).'...';
          else
          {
            $width2=-$width1;
          	$title=substr($title,0,$width1).'...'.substr($title,$width2);
          }
        }
        else{//title 2/3 di spazio, url 1/3 di spazio
          $width1=floor(($tmp-3)/2);
          //$url=substr($url,0,$width1).'...'.substr($url,$width2);
          $width1=floor($tmp-3);//($tmp-3)*2/2
	      if ($width2==0)
          	$title=substr($title,0,$width1).'...';
          else
          {
            $width2=-$width1;
          	$title=substr($title,0,$width1).'...'.substr($title,$width2);
          }
        }
      }
      $linktext="$title (<em>$url</em>)";
  }
/***  return "<a href=\"$longurl\" title=\"".stripslashes($link_title)."\" target=\"_blank\">$icon ".str_replace('\\"', '"', htmlentities($linktext))."</a>";*/
if ($iconType == 'Download' || $iconType == 'Click')
	return "$icon ".str_replace('\\"', '"', $linktext);
else
	return "<a href=\"$longurl\" title=\"".stripslashes($link_title)."\" target=\"_blank\">$icon ".str_replace('\\"', '"', $linktext)."</a>";
}

// Formattazione mese //
function formatmount($mount,$mode=0){ // 0 -> MESE NORMALE 1 -> MESE ABBREVIATO  -  0 -> LONG MOUNTH 1 -> SHORT MOUNTH
global $varie;
return($mode==0 ? $varie['mounts'][$mount-1] : $varie['mounts_1'][$mount-1]);
}

// Formattazione ora //
function formattime($time){
return($time!=0 ? date("H:i:s",$time) : "");
}

function formatdate($date,$mode=0){
   global $varie;
   $mode=$mode-0;
   if($date==0) return '';
   switch($mode)
   {
    case 0:
       list($date_n,$date_j,$date_Y)=explode('-',date('n-j-Y',$date));
       return str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($date_n),$date_j,$date_Y),$varie['date_format']);
    case 1:
       list($anno,$mese)=explode('-',$date);
       return str_replace(Array('%mount%','%year%'),Array(formatmount($mese),$anno),$varie['date_format_2']);
    default:
    case 2:
       list($date_m,$date_d,$date_y)=explode('-',date('m-d-y',$date));
       return str_replace(Array('%mount%','%day%','%year%'),Array($date_m,$date_d,$date_y),$varie['date_format_3']);
/*** Modalità aggiunta e usata nei dettagli - visualizza la data col nome del giorno ***/
    case 3:
       list($date_N,$date_n,$date_j,$date_Y)=explode('-',date('N-n-j-Y',$date));
       return str_replace(Array('%wday%','%mount%','%day%','%year%'),Array($varie['days'][$date_N-1],formatmount($date_n),$date_j,$date_Y),'%wday% %day% %mount% %year%');
   }
}

function formatperm($value,$mode=1){
global $varie;
$value=round($value,0);
if($mode==1)
  {
  $minuti=floor($value/60);
  $secondi=$value-($minuti*60);
  if($secondi<10) $secondi='0'.$secondi;
  if($minuti<10) $minuti='0'.$minuti;
  return str_replace(Array('%minutes%','%seconds%'),Array($minuti,$secondi),$varie['perm_format']);
  }

$ore=floor($value/3600);
$value=$value-($ore*3600);
$minuti=floor($value/60);
$secondi=$value-($minuti*60);
if($ore<10) $ore='0'.$ore;
if($secondi<10) $secondi='0'.$secondi;
if($minuti<10) $minuti='0'.$minuti;
return str_replace(Array('%hours%','%minutes%','%seconds%'),Array($ore,$minuti,$secondi),$varie['perm_format_2']);
}


// Verifica l'esistenza di un file sul server
function checkfile($url) {
global $string,$option;
$url=chop($url);
$url=str_replace(' ','%20',$url);
if($option['check_links']){
  $check=false;
  $check=fopen($url,'r');
  if($check==false) return("<img src=\"templates/$option[template]/images/icon_bullet_red.gif\" title=\"$string[link_broken]\">");
               else return("<img src=\"templates/$option[template]/images/icon_bullet_green.gif\" title=\"$string[link_ok]\">");
  }
  else return("<img src=\"templates/$option[template]/images/icon_bullet_orange.gif\" title=\"$string[link_not_checked]\">");
}

// Funzione per check dei campi (0=numerico,1=alfanumerico) - Chech field text function (0=numeric,1=alphanumeric)
function checktext($campo,$mode=0)
{
$ok=0;
if($mode==0) $car_permessi="_1234567890"; else $car_permessi="_abcdefghijklmnopqrstuvxyzABCDEFGHIJKLMNOPQRSTUVXYZ0123456789_";
$str_lenght=strlen($campo);
for ($i=0;$i<=$str_lenght;++$i)
  {
  $str_temp=substr($campo,$i-1,1);
  $chk=(strpos($car_permessi,$str_temp) ? strpos($car_permessi,$str_temp)+1 : 0);
  if($chk==0) $ok=1;
  }
return($ok);
}

// Prepara l'HTML dal template
function gettemplate($template) {

//$file=file($template);
//$template=implode('',$file);
$template = file_get_contents($template);
$template=str_replace('"','\"',$template);

return $template;
}

// CREA INFOBOX
function info_box($title,$body,$width=250,$cellspacing=10) {
global $style;
$return =
"<br><br><table border=\"0\" $style[table_header] width=\"$width\" class=\"info_box\">".
"<tr><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\">$title</span></td>".
"<tr><td align=\"center\" valign=\"middle\" bgcolor=$style[table_bgcolor] nowrap>".
"<table width=\"100%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"$cellspacing\" border=\"0\"><tr><td align=\"center\" valign=\"middle\"  nowrap>".
"<span class=\"tabletextA\">$body</span></td></tr>".
"</td></tr></table>".
//"<tr><td height=\"1\"bgcolor=$style[table_title_bgcolor] nowrap></td></tr>".
"</table>";
return($return);
}

function draw_table_title($titolo,$pagina='',$base_url='',$tables='',$q_sort='',$q_order='') {
global $option,$style;
$return="<td bgcolor=$style[table_title_bgcolor] nowrap>";
if($pagina==='')
  {
  $return.="<center><span class=\"tabletitle\">$titolo</span></center></td>";
  }
else
  {
  $return.="<a href=\"$base_url&sort=$pagina";
  if($q_sort==$tables["$pagina"]) $return.='&order='.($q_order==='ASC' ? '0' : '1');
  $return.=
  '">'.
  '<center><span class="tabletitle">';
  if($q_sort==$tables["$pagina"]) $return.="<img src=\"templates/$option[template]/images/".($q_order==='ASC' ? 'asc' : 'dsc').'_order.gif" border=0 align="middle"> ';
  $return.=$titolo.'</span></center></a></td>';
}
return($return);
}

// VISUALIZZA LA BARRA DELLA PAGINAZIONE
function pag_bar($base_url,$pagina_corrente,$numero_pagine,$rec_pag){
global $varie,$style;
  $return="\n\n<center><span class=\"tabletextA\">";
  if($pagina_corrente>1) $return.="\n<a href=\"$base_url&start=".(($pagina_corrente-2)*$rec_pag)."\">$varie[prev]</a>&nbsp&nbsp;";
  if($pagina_corrente>5 && $numero_pagine>6) $pi=$pagina_corrente-2; else $pi=1;
  if($pagina_corrente<($numero_pagine-3)) $pf=($numero_pagine>6 ? max(($pagina_corrente+2),6) : $numero_pagine);
  else $pf=$numero_pagine;
  if($pi>1) $return.="<a href=\"$base_url&start=0\">1</a>&nbsp;... ";
  for($pagina=$pi; $pagina<=$pf; ++$pagina)
    {
    if($pagina==$pagina_corrente) $return.="<b>$pagina</b> ";
                             else $return.="<a href=\"$base_url&start=".(($pagina-1)*$rec_pag)."\">".$pagina."</a>&nbsp;";
    }
  if(($numero_pagine-$pf)>0) $return.="... <a href=\"$base_url&start=".(($numero_pagine-1)*$rec_pag)."\">$numero_pagine</a>&nbsp;";
  if($pagina_corrente<$numero_pagine) $return.= "<a href=\"$base_url&start=".(($pagina_corrente)*$rec_pag)."\">&nbsp$varie[next]</a>";
  $return.='</span></center>';
return($return);
}

function create_option_file($create_staticJS='')
{
global $page_list,$change_mode;
//if($NowritableServer===0){
   define ('__OPTIONS_FILE__','option/php-stats-options.php');
   define ('__LOCK_FILE__','option/options_lock.php');
//   }

//if (!defined('__STATICJS_FILE__')) define ('__STATICJS_FILE__','php-stats.js');

include('config.php');

// ARRAY ORDINATA DEI VALORI CHE NON DEVONO ESSERE SCRITTI
$noWrite=Array('inadm_last_update','instat_report_w','instat_max_online','inadm_upd_available');

// Valori da memorizzare in formato stringa
$stringValue=Array('host','database','user_db','pass_db','dummy0','dummy1','prefix','ext_whois','language','server_url','admin_pass','template','nomesito','user_mail','user_pass_new','user_pass_key','phpstats_ver','exc_fol','exc_sip','exc_dip');

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

// SCRIVO L'ARRAY DEFAULT PAGE - WRITE DEFAULT ARRAY
$options_text.="\$default_pages=Array(\n";
while (list ($key, $value) = each ($default_pages)) $options_text.="'$value',\n";
$options_text=substr($options_text, 0, -2).");\n?>";

	// CREO IL FILE DI LOCK E FACCIO UNO SLEEP DI 1 SEC ALTRIMENTI NON VIENE MAI RILEVATO - CREATE LOCK FILE AND MAKE 1 SEC SLEEP
	$ok=touch(__LOCK_FILE__);
	if (!$ok) return($ok);
	sleep(1);

	// CREAZIONE FILE OPTIONS.PHP  - GENERATE OPTIONS.PHP FILE
	$optionsFile=fopen(__OPTIONS_FILE__, 'w+');
	$ok=fwrite($optionsFile,$options_text);
	if (!$ok) return($ok);
	fclose($optionsFile);

	$ok=unlink(__LOCK_FILE__);
//	if($change_mode==1) sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_options",FALSE);
/*
if($create_staticJS!=''){
$createFile=TRUE;

if(file_exists(__STATICJS_FILE__))
  {
  $staticJSFile=fopen(__STATICJS_FILE__,'r');
  if($staticJSFile)
    {
    $tmp=fread($staticJSFile,5);
    fclose($staticJSFile);
    if(($tmp=='//cvi' && $option['callviaimg']) || ($tmp!='//cvi' && !$option['callviaimg'])) $createFile=FALSE;
    }
  }

if($createFile){
$jsstatic_text='
if(document.referrer) var f=document.referrer;
else var f=top.document.referrer;
f=escape(f);
f=f.replace(/&/g,"%A7%A7");
if((f=="null") || (f=="unknown") || (f=="undefined")) f="";
var w=screen.width;
var h=screen.height;
var rand=Math.round(100000*Math.random());
var browser=navigator.appName;
var t=escape(document.title);
var NS_url="";
if(browser!="Netscape") c=screen.colorDepth; else c=screen.pixelDepth;
NS_url=document.URL;
NS_url=escape(NS_url);
NS_url=NS_url.replace(/&/g,"%A7%A7");';

if($option['callviaimg'])
{
$jsstatic_text=
'//cvi
'.$jsstatic_text.'
var sc1="<img src=\''.$option['script_url'].'/php-stats.php?w="+w+"&amp;h="+h+"&amp;c="+c+"&amp;f="+f+"&amp;NS_url="+NS_url+"&amp;t="+t+"\' border=\'0\' alt=\'\' width=\'1\' height=\'1\'>";';
}
else
{
$jsstatic_text.=
'
sc1="<scr"+"ipt type=\'text/javascript\' src=\''.$option['script_url'].'/php-stats.php?w="+w+"&amp;h="+h+"&amp;c="+c+"&amp;f="+f+"&amp;NS_url="+NS_url+"&amp;t="+t+"\'></scr"+"ipt>";';
}

$jsstatic_text.='
document.write(sc1);';
$STATICJS_FILE='php-stats.js';
$staticJSFile=fopen($STATICJS_FILE, 'w+');
if($staticJSFile){
  fwrite($staticJSFile,$jsstatic_text);
  fclose($staticJSFile);
  }
}
}
*/
return($ok);
}

// PULIZIA DELLA CACHE E TRASFERIMENTO DATI DALLA CACHE AL DATABASE - CLEAN CACHE AND CACHE2DATABASE TRANSFER //
function clear_cache()
{
	global $option,$modulo,$db;

//	$query="CREATE TABLE $option[prefix]_cache_clone ( user_id double NOT NULL default '0', data int(11) NOT NULL default '0', lastpage varchar(255) NOT NULL default '0', visitor_id varchar(32) NOT NULL default '', hits tinyint(3) unsigned NOT NULL default '0', visits smallint(5) unsigned NOT NULL default '0', reso varchar(10) NOT NULL default '', colo varchar(10) NOT NULL default '', os varchar(20) NOT NULL default '', bw varchar(20) NOT NULL default '', host varchar(50) NOT NULL default '', tld varchar(7) NOT NULL default 'unknown', lang varchar(8) NOT NULL default '', giorno varchar(10) NOT NULL default '', notbrowser tinyint(1) NOT NULL default '0', level tinyint(3) unsigned NOT NULL default '0', UNIQUE KEY user_id (user_id)) TYPE=";
	/*** Aggiunto IF NOT EXISTS se la tabella esistesse già (in qualche raro caso capita), altrimenti si bloccano le statistiche */
	$query="CREATE TABLE IF NOT EXISTS $option[prefix]_cache_clone (
	  user_id int(10) unsigned NOT NULL default '0',
	  data int(11) unsigned NOT NULL default '0',
	  lastpage varchar(255) NOT NULL default '0',
	  visitor_id varchar(32) NOT NULL default '',
	  hits tinyint(3) unsigned NOT NULL default '0',
	  visits smallint(5) unsigned NOT NULL default '0',
	  reso varchar(10) NOT NULL default '',
	  colo varchar(10) NOT NULL default '',
	  os varchar(20) NOT NULL default '',
	  bw varchar(20) NOT NULL default '',
	  host varchar(80) NOT NULL default '',
	  tld varchar(7) NOT NULL default 'unknown',
	  lang varchar(8) NOT NULL default '',
	  giorno varchar(10) NOT NULL default '',
	  notbrowser tinyint(1) NOT NULL default '0',
	  level tinyint(3) unsigned NOT NULL default '0',
	  UNIQUE KEY visitor_id (visitor_id)
	  ) ENGINE=";

	$result=sql_query($query.'Heap',FALSE);
	if(!$result) {
		$result=sql_query($query.'MyISAM',FALSE);
		$typeTables='MyISAM';
	}
	else
		$typeTables='Heap';
	if(!$result)
		return false;

	$lockAvaible=TRUE;

	$result=sql_query("LOCK TABLES $option[prefix]_cache WRITE, $option[prefix]_cache_clone WRITE",FALSE);
	if(!$result)
		$lockAvaible=FALSE;
	sql_query("INSERT ".($lockAvaible===TRUE ? '' : 'HIGH_PRIORITY ')."INTO $option[prefix]_cache_clone SELECT * FROM $option[prefix]_cache WHERE hits<>0 || visits<>0");

	if(mysql_affected_rows()<1) {  		// NESSUN DATO IN CACHE
	  	sql_query("UNLOCK TABLES");
	  	sql_query("DROP TABLE $option[prefix]_cache_clone");
	  	return false;
	}

	sql_query("UPDATE $option[prefix]_cache SET hits=0,visits=0");
	if($lockAvaible===TRUE)
		sql_query("LOCK TABLES $option[prefix]_cache_clone READ");

	$date=time()-$option['timezone']*3600;

	$sqlBufferAppend='SQL_BUFFER_RESULT';

	// SISTEMI (OS,BW,RESO,COLORS)
	if($modulo[1])
	{
		if($lockAvaible===TRUE)
			sql_query("LOCK TABLES $option[prefix]_systems WRITE, $option[prefix]_systems AS systemstablephpstats READ, $option[prefix]_cache_clone READ;");
		sql_query("REPLACE INTO $option[prefix]_systems (os,bw,reso,colo,hits,visits,mese) SELECT $sqlBufferAppend $option[prefix]_cache_clone.os,$option[prefix]_cache_clone.bw,$option[prefix]_cache_clone.reso,$option[prefix]_cache_clone.colo,systemstablephpstats.hits+sum($option[prefix]_cache_clone.hits) AS sum_hits,systemstablephpstats.visits+sum($option[prefix]_cache_clone.visits) AS sum_visits,".(($modulo[1]==2) ? "substring_index($option[prefix]_cache_clone.giorno,'-',2)" : "''")." FROM $option[prefix]_cache_clone, $option[prefix]_systems AS systemstablephpstats WHERE $option[prefix]_cache_clone.os=systemstablephpstats.os AND $option[prefix]_cache_clone.bw=systemstablephpstats.bw AND $option[prefix]_cache_clone.reso=systemstablephpstats.reso AND $option[prefix]_cache_clone.colo=systemstablephpstats.colo AND systemstablephpstats.mese=".(($modulo[1]==2) ? "substring_index($option[prefix]_cache_clone.giorno,'-',2)" : "''")." GROUP BY $option[prefix]_cache_clone.os, $option[prefix]_cache_clone.bw, $option[prefix]_cache_clone.reso,$option[prefix]_cache_clone.colo,".(($modulo[1]==2) ? "substring_index($option[prefix]_cache_clone.giorno,'-',2)" : "$option[prefix]_cache_clone.giorno"));
		sql_query("INSERT IGNORE INTO $option[prefix]_systems (os,bw,reso,colo,hits,visits,mese) SELECT $sqlBufferAppend $option[prefix]_cache_clone.os,$option[prefix]_cache_clone.bw,$option[prefix]_cache_clone.reso,$option[prefix]_cache_clone.colo,sum($option[prefix]_cache_clone.hits),sum($option[prefix]_cache_clone.visits),".(($modulo[1]==2) ? "substring_index($option[prefix]_cache_clone.giorno,'-',2)" : "''")." FROM $option[prefix]_cache_clone GROUP BY $option[prefix]_cache_clone.os, $option[prefix]_cache_clone.bw, $option[prefix]_cache_clone.reso,$option[prefix]_cache_clone.colo,".(($modulo[1]==2) ? "substring_index($option[prefix]_cache_clone.giorno,'-',2)" : "$option[prefix]_cache_clone.giorno"));
	}


	  // ACCESSI GIORNALIERI
	if($modulo[6])
	{
		if($lockAvaible===TRUE)
			sql_query("LOCK TABLES $option[prefix]_daily WRITE, $option[prefix]_daily AS dailytablephpstats READ, $option[prefix]_cache_clone READ");
	/*** ORIGINALE
		sql_query("REPLACE INTO $option[prefix]_daily (data,hits,visits,no_count_hits,no_count_visits) SELECT $sqlBufferAppend $option[prefix]_cache_clone.giorno,sum($option[prefix]_cache_clone.hits)+dailytablephpstats.hits,sum($option[prefix]_cache_clone.visits)+dailytablephpstats.visits,IF(notbrowser=0,dailytablephpstats.no_count_hits,dailytablephpstats.no_count_hits+sum($option[prefix]_cache_clone.hits)),IF(notbrowser=0,dailytablephpstats.no_count_visits,dailytablephpstats.no_count_visits+sum($option[prefix]_cache_clone.visits)) FROM $option[prefix]_daily AS dailytablephpstats,$option[prefix]_cache_clone WHERE $option[prefix]_cache_clone.giorno=dailytablephpstats.data GROUP BY $option[prefix]_cache_clone.giorno");
		sql_query("REPLACE INTO $option[prefix]_daily (data,hits,visits,no_count_hits,no_count_visits) SELECT $sqlBufferAppend $option[prefix]_cache_clone.giorno,sum($option[prefix]_cache_clone.hits),sum($option[prefix]_cache_clone.visits),IF(notbrowser=0,0,0+sum($option[prefix]_cache_clone.hits)),IF(notbrowser=0,0,0+sum($option[prefix]_cache_clone.visits)) FROM $option[prefix]_cache_clone  LEFT JOIN $option[prefix]_daily AS dailytablephpstats ON $option[prefix]_cache_clone.giorno=dailytablephpstats.data WHERE dailytablephpstats.data IS NULL GROUP BY $option[prefix]_cache_clone.giorno");
	***/
		/* Crea la riga se ancora non esiste con la data corrente ed i valori a zero */
		sql_query("REPLACE INTO $option[prefix]_daily (data) SELECT $sqlBufferAppend $option[prefix]_cache_clone.giorno FROM $option[prefix]_cache_clone LEFT JOIN $option[prefix]_daily AS dailytablephpstats ON $option[prefix]_cache_clone.giorno=dailytablephpstats.data WHERE dailytablephpstats.data IS NULL GROUP BY $option[prefix]_cache_clone.giorno");
		/* Somma TUTTE le visite */
//		sql_query("REPLACE INTO $option[prefix]_daily (data,hits,visits,no_count_hits,no_count_visits) SELECT $sqlBufferAppend $option[prefix]_cache_clone.giorno, sum($option[prefix]_cache_clone.hits)+dailytablephpstats.hits, sum($option[prefix]_cache_clone.visits)+dailytablephpstats.visits, dailytablephpstats.no_count_hits, dailytablephpstats.no_count_visits FROM $option[prefix]_daily AS dailytablephpstats,$option[prefix]_cache_clone WHERE $option[prefix]_cache_clone.giorno=dailytablephpstats.data GROUP BY $option[prefix]_cache_clone.giorno");
		sql_query("REPLACE INTO $option[prefix]_daily (data,hits,visits,no_count_hits,no_count_visits,rets) SELECT $sqlBufferAppend $option[prefix]_cache_clone.giorno, sum($option[prefix]_cache_clone.hits)+dailytablephpstats.hits, sum($option[prefix]_cache_clone.visits)+dailytablephpstats.visits, dailytablephpstats.no_count_hits, dailytablephpstats.no_count_visits, dailytablephpstats.rets FROM $option[prefix]_daily AS dailytablephpstats,$option[prefix]_cache_clone WHERE $option[prefix]_cache_clone.giorno=dailytablephpstats.data GROUP BY $option[prefix]_cache_clone.giorno");
		/* Somma le visite dei MOTORI DI RICERCA */
//		sql_query("REPLACE INTO $option[prefix]_daily (data,hits,visits,no_count_hits,no_count_visits) SELECT $sqlBufferAppend $option[prefix]_cache_clone.giorno, dailytablephpstats.hits, dailytablephpstats.visits, sum($option[prefix]_cache_clone.hits)+dailytablephpstats.no_count_hits, sum($option[prefix]_cache_clone.visits)+dailytablephpstats.no_count_visits FROM $option[prefix]_daily AS dailytablephpstats,$option[prefix]_cache_clone WHERE $option[prefix]_cache_clone.giorno=dailytablephpstats.data AND $option[prefix]_cache_clone.notbrowser=1 GROUP BY $option[prefix]_cache_clone.giorno");
		sql_query("REPLACE INTO $option[prefix]_daily (data,hits,visits,no_count_hits,no_count_visits,rets) SELECT $sqlBufferAppend $option[prefix]_cache_clone.giorno, dailytablephpstats.hits, dailytablephpstats.visits, sum($option[prefix]_cache_clone.hits)+dailytablephpstats.no_count_hits, sum($option[prefix]_cache_clone.visits)+dailytablephpstats.no_count_visits, dailytablephpstats.rets FROM $option[prefix]_daily AS dailytablephpstats,$option[prefix]_cache_clone WHERE $option[prefix]_cache_clone.giorno=dailytablephpstats.data AND $option[prefix]_cache_clone.notbrowser=1 GROUP BY $option[prefix]_cache_clone.giorno");
	}

	// INDIRIZZI IP - IP ADDRESS
/*	if($modulo[10])
	{
		if($lockAvaible===TRUE)
			sql_query("LOCK TABLES $option[prefix]_ip WRITE, $option[prefix]_ip AS iptablephpstats READ, $option[prefix]_cache_clone READ");
//		sql_query("REPLACE INTO $option[prefix]_ip (ip,date,hits,visits) SELECT DISTINCT $sqlBufferAppend iptablephpstats.ip,$date AS data_time,iptablephpstats.hits+$option[prefix]_cache_clone.hits AS sum_hits,iptablephpstats.visits+$option[prefix]_cache_clone.visits AS sum_visits FROM $option[prefix]_cache_clone, $option[prefix]_ip AS iptablephpstats WHERE $option[prefix]_cache_clone.user_id=iptablephpstats.ip");
//		sql_query("INSERT INTO $option[prefix]_ip (ip,date,hits,visits) SELECT DISTINCT $sqlBufferAppend $option[prefix]_cache_clone.user_id,$date AS data_time,$option[prefix]_cache_clone.hits,$option[prefix]_cache_clone.visits FROM $option[prefix]_cache_clone LEFT JOIN $option[prefix]_ip AS iptablephpstats ON $option[prefix]_cache_clone.user_id=iptablephpstats.ip WHERE $option[prefix]_cache_clone.user_id NOT IN(iptablephpstats.ip)");
		$result =    sql_query("REPLACE INTO $option[prefix]_ip (ip,date,hits,visits) SELECT DISTINCT $sqlBufferAppend iptablephpstats.ip,iptablephpstats.date,iptablephpstats.hits+$option[prefix]_cache_clone.hits AS sum_hits,iptablephpstats.visits+$option[prefix]_cache_clone.visits AS sum_visits FROM $option[prefix]_cache_clone, $option[prefix]_ip AS iptablephpstats WHERE $option[prefix]_cache_clone.user_id=iptablephpstats.ip");
		if(!$result)  sql_query("INSERT INTO $option[prefix]_ip (ip,date,hits,visits) SELECT DISTINCT $sqlBufferAppend $option[prefix]_cache_clone.user_id,$option[prefix]_cache_clone.data,$option[prefix]_cache_clone.hits,$option[prefix]_cache_clone.visits FROM $option[prefix]_cache_clone LEFT JOIN $option[prefix]_ip AS iptablephpstats ON $option[prefix]_cache_clone.user_id=iptablephpstats.ip WHERE 1");
	}
*/
	// COUNTRY
	if($modulo[7])
	{
		if($lockAvaible===TRUE)
			sql_query("LOCK TABLES $option[prefix]_domains WRITE, $option[prefix]_domains AS domainstablephpstats WRITE, $option[prefix]_cache_clone READ");
		sql_query("REPLACE INTO $option[prefix]_domains (tld,hits,visits,area) SELECT $sqlBufferAppend $option[prefix]_cache_clone.tld,sum($option[prefix]_cache_clone.hits)+domainstablephpstats.hits,sum($option[prefix]_cache_clone.visits)+domainstablephpstats.visits,domainstablephpstats.area FROM $option[prefix]_domains AS domainstablephpstats,$option[prefix]_cache_clone WHERE $option[prefix]_cache_clone.tld=domainstablephpstats.tld GROUP BY $option[prefix]_cache_clone.tld");
	}

/***
Questa parte l'ho spostata in fondo, prima dell'eliminazione della tabella _cache_clone perché esegue una query (errata) che ne altera il contenuto del numero di visitatori e pagine visitate.
***/
	// LINGUE (impostate dal browser)
	if($modulo[2])
	{
		if($lockAvaible===TRUE)
			sql_query("LOCK TABLES $option[prefix]_langs WRITE, $option[prefix]_langs AS langstablephpstats READ, $option[prefix]_cache_clone WRITE,$option[prefix]_cache_clone AS cacheclonetablephpstats READ");
		sql_query("REPLACE INTO $option[prefix]_cache_clone(user_id,data,hits,visits,tld,lang,giorno,notbrowser) SELECT $sqlBufferAppend cacheclonetablephpstats.user_id,cacheclonetablephpstats.data,cacheclonetablephpstats.hits,cacheclonetablephpstats.visits,cacheclonetablephpstats.tld,'unknown',cacheclonetablephpstats.giorno,cacheclonetablephpstats.notbrowser FROM $option[prefix]_cache_clone AS cacheclonetablephpstats LEFT JOIN $option[prefix]_langs AS langstablephpstats ON cacheclonetablephpstats.lang=langstablephpstats.lang  WHERE langstablephpstats.lang IS NULL");
		sql_query("REPLACE INTO $option[prefix]_langs (lang,hits,visits) SELECT $sqlBufferAppend $option[prefix]_cache_clone.lang,sum($option[prefix]_cache_clone.hits)+langstablephpstats.hits,sum($option[prefix]_cache_clone.visits)+langstablephpstats.visits FROM $option[prefix]_langs AS langstablephpstats,$option[prefix]_cache_clone WHERE $option[prefix]_cache_clone.lang=langstablephpstats.lang GROUP BY $option[prefix]_cache_clone.lang");
	}

	if($lockAvaible===TRUE)
		sql_query("UNLOCK TABLES");
	sql_query("DROP TABLE $option[prefix]_cache_clone");
	return true;
}

function size($file) {
  if(!file_exists($file)) return 'N/A';
  $size = filesize($file);
  $sizes = Array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');
  $ext = $sizes[0];
  for ($i=1; (($i < count($sizes)) && ($size >= 1024)); $i++) {
   $size = $size / 1024;
   $ext  = $sizes[$i];
  }
  return round($size, 2).$ext;
}

function relative_path($absolute,$curpath){
   $file=basename($absolute);
   $absolute=dirname($absolute);
   if(substr($absolute,-1)!=='/') $absolute.='/';
   if(substr($curpath,-1)!=='/') $curpath.='/';
   if($absolute===$curpath) return $file; //sono la stessa directory
   if(strlen($absolute)>strlen($curpath)) return substr($absolute,strlen($curpath)).$file;
   else{
      $tmp=substr($curpath,strlen($absolute));
      $backdirs=substr_count($tmp,'/');
      $result='';
      for($i=0;$i<$backdirs;++$i) $result.='../';
      return $result.$file;
   }
}
?>
