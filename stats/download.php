<?php

/**
 *  ___ _  _ ___       ___ _____ _ _____ ___ 
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|0.1.9.2|___/ |_/_/ \_\_| |___/
 *
 * Author:     Roberto Valsania - Webmaster76
 *
 * Staff:      Matrix - Massimiliano Coppola
 *             Viewsource
 *             PaoDJ - Paolo Antonio Tremadio
 *             Fabry - Fabrizio Tomasoni
 *             theCAS - Carlo Alberto Siti
 *
 * Version:    0.1.9.2
 *
 * Site:       http://php-stats.com/
 *             http://phpstats.net/
 *
 **/

// SECURITY ISSUES
define('IN_PHPSTATS',true);

// Richiamo variabili esterne
                       if(!isset($_GET)) $_GET=$HTTP_GET_VARS;
              if(isset($_REQUEST['id'])) $id=$_REQUEST['id']; else $id='';
     if(isset($_SERVER['HTTP_REFERER'])) $HTTP_REFERER=$_SERVER['HTTP_REFERER'];
      if(isset($_SERVER['REMOTE_ADDR'])) $ip=(isset($_SERVER['HTTP_PC_REMOTE_ADDR']) ? $_SERVER['HTTP_PC_REMOTE_ADDR'] : $_SERVER['REMOTE_ADDR']);
                if(isset($_GET['mode'])) $mode=$_GET['mode']; else $mode='';

// Security Issues
if (!ereg('^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$',$ip)) die("Die"); 

$ip=sprintf('%u',ip2long($ip))-0;
// Rilevo il tipo di modalità utilizzata: no_write o write
//include('option/php-stats_mode.php');
//if(!isset($NowritableServer)) $NowritableServer=1;

// DEFINIZIONE VARIABILI PRINCIPALI
//if($NowritableServer===0){
   define ('__OPTIONS_FILE__','option/php-stats-options.php');
   define ('__LOCK_FILE__','option/options_lock.php');
//   }

//switch ($NowritableServer){
//  case 0:
        // Verifica presenza del file di opzioni libero altrimenti aspetto ed inclusione funzioni principali
        if (file_exists(__LOCK_FILE__)) sleep(2);
        if (!include(__OPTIONS_FILE__)) die("<b>ERRORE</b>: File di config non accessibile.");
//        break;
//default:
//        if(!include('config.php')) die('<b>ERRORE</b>: File config.php non accessibile.');
//}

if(!include('inc/main_func.inc.php')) die('<b>ERRORE</b>: File main_func.inc.php non accessibile.');
if(!include('inc/admin_func.inc.php')) die('<b>ERRORE</b>: File admin_func.inc.php non accessibile.');

if($option['prefix']=='') $option['prefix']='php_stats';
if(ini_get('zlib.output_compression'))
ini_set('zlib.output_compression', 'Off');

// Connessione a MySQL e selezione database
db_connect();
/*
if($NowritableServer===1){
  // Lettura variabili
  $result=sql_query("SELECT option_name,option_value FROM $option[prefix]_options");
  while($row=mysql_fetch_row($result)) $option2[$row[0]]=$row[1];
  eval($option2['php-stats-options']);
  }
*/
$downloads_withinterface='';
$errorDownload=false;
$result=sql_query("SELECT nome,descrizione,type,home,size,downloads,withinterface FROM $option[prefix]_downloads WHERE id='$id'");
if(mysql_num_rows($result)==0) $errorDownload=true;
else list($downloads_nome,$downloads_descrizione,$downloads_type,$downloads_home,$downloads_size,$downloads_downloads,$downloads_withinterface)=mysql_fetch_row($result);

if($option['template']=='') $option['template']='default';
  $template_path='templates/'.$option['template'];

  // Inclusioni secondarie: template della pagina e language pack.
  if(!include("lang/$option[language]/main_lang.inc")) die("<b>ERRORE</b>: File $option[language]/main_lang.inc non accessibile."); // Language file
  if(!include("$template_path/def.php")) die("<b>ERRORE</b>: File $template_path/def.php non accessibile.");                // Template defs

  // Titolo pagina
  $phpstats_title=$string['down_title'];

// VERIFICO SE DEVO EFFETTUARE IL DOWNLOAD TRAMITE INTERFACCIA O NO
if(($mode!='download' && $downloads_withinterface=='YES') || $errorDownload===true)
  {
  if($errorDownload===true) $page=info_box($string['error'],$error['down_noid']);
  else
    {
    $phpstats_title=$phpstats_title.' '.stripslashes($downloads_nome);
    $page=
    "\n<br>".
    "\n<form action=\"download.php?mode=download\" method=\"post\">".
    "\n<TABLE $style[table_header] width=\"90%\">".
    "\n\t<TR><TD bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\"><center>$string[sommario]</center></span></TD></TR>".
    "\n\t<TR><TD align=right width=\"40%\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[down_name]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".stripslashes($downloads_nome)."</span></TD>".
    "\n\t<TR><TD align=right width=\"40%\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[down_desc]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".stripslashes($downloads_descrizione)."</span></TD>".
    "\n\t<TR><TD align=right width=\"40%\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[down_type]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$downloads_type</span></TD>".
    "\n\t<TR><TD align=right width=\"40%\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[down_home]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\"><a href=\"$downloads_home\">$downloads_home</a></span></TD>".
    "\n\t<TR><TD align=right width=\"40%\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[down_size]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$downloads_size</span></TD></TR>".
    "\n\t<TR><TD align=right width=\"40%\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[down_count]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$downloads_downloads</span></TD></TR>".
    "\n\t<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><INPUT TYPE=\"hidden\" name=\"id\" value=\"$id\"><center><input type=\"Submit\" value=\"$string[down_download]\"></center></td></tr>".
    "\n</table>\n</form>";
    }
  include("$template_path/templdetails.php");
  echo $template;
  }
else
  {
  if($id!='')
    {
    $result=sql_query("SELECT url FROM $option[prefix]_downloads WHERE id='$id' LIMIT 1");
    if(mysql_affected_rows()>0) list($get)=mysql_fetch_row($result);
    else
      {
      $page=info_box($string['error'],$error['down_noid']);
      include("$template_path/templdetails.php");
      echo $template;
      exit();
      }
    }
  if($get!='')
    {
    $get=str_replace(' ','%20',$get);
    $check=($option['check_links'] ? fopen($get,'r') : true);
    if($check!=false)
      {
      if(!escluso($ip)) sql_query("UPDATE $option[prefix]_downloads SET downloads=downloads+1 WHERE id='$id'");

      if($modulo[0] && !escluso($ip))
        {
        // INSERISCO NEI DETTAGLI IL DOWNLOAD DEL FILE
        $result=sql_query("SELECT visitor_id,os FROM $option[prefix]_cache WHERE user_id='$ip' LIMIT 1");
        if(mysql_num_rows($result)>0)
          {
          list($visitor_id,$os)=mysql_fetch_row($result);
          $date=time()-$option['timezone']*3600;
          $loaded="dwn|$id";
//        sql_query("INSERT INTO $option[prefix]_details VALUES ('$visitor_id','$ip','','','','','$date','','$loaded','','','','')");
/*** Aggiunto campi rets e last_return e valore os altrimenti veniva interpretato come motore di ricerca */
		  sql_query("INSERT INTO $option[prefix]_details VALUES ('$visitor_id','$ip','','$os','','','$date','','$loaded','','','DOWNLOAD ID: $id','','','')");
          }
       }

      $filename=($option['down_mode']==2 ? relative_path($get,$_SERVER['PHP_SELF']) : $get);

      $ext=substr($filename,-3);
      if($filename=='')
        {
        $page=info_box($string['error'],$error['down_noid']);
        include("$template_path/templdetails.php");
        echo $template;
        exit();
        }

     if($option['down_mode']==0) { header("location: $get"); exit(); }
     else{
      switch($ext)
        {
        case 'pdf': $ctype='application/pdf'; break;
        case 'exe': $ctype='application/octet-stream'; break;
        case 'zip': $ctype='application/zip'; break;
        case 'doc': $ctype='application/msword'; break;
        case 'xls': $ctype='application/vnd.ms-excel'; break;
        case 'ppt': $ctype='application/vnd.ms-powerpoint'; break;
        case 'gif': $ctype='image/gif'; break;
        case 'png': $ctype='image/png'; break;
        case 'jpg': $ctype='image/jpg'; break;
        default: $ctype='application/force-download';
        }

        header("Content-Type: $ctype");
        $user_agent = strtolower ($_SERVER['HTTP_USER_AGENT']);
        header('Content-Disposition: attachment; filename="'.basename($filename).'"');
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Transfer-Encoding: binary');
        //header('Content-Length: '.filesize($filename));*/
        readfile($filename);
        exit();
      }
     }
    }
  else
    {
    $page=info_box($string['error'],$error['file_down']);
    include("$template_path/templdetails.php");
    echo $template;
    exit();
    }
  if($modulo[0] && !escluso($ip))
    {
    // INSERISCO NEI DETTAGLI IL DOWNLOAD DEL FILE
    $result=sql_query("SELECT visitor_id,os FROM $option[prefix]_cache WHERE user_id='$ip' LIMIT 1");
    if(mysql_num_rows($result)>0)
      {
      list($visitor_id,$os)=mysql_fetch_row($result);
      $date=time()-$option['timezone']*3600;
      $loaded="dwn|$id";
//      sql_query("INSERT INTO $option[prefix]_details VALUES ('$visitor_id','$ip','','','','','$date','','$loaded','','','','')");
/*** Aggiunto campi rets e last_return e valore os altrimenti veniva interpretato come motore di ricerca */
	  sql_query("INSERT INTO $option[prefix]_details VALUES ('$visitor_id','$ip','',$os,'','','$date','','$loaded','','','DOWNLOAD ID: $id','','','')");
      }
    }
  }

// Chiusura connessione a MySQL se necessario.
if($option['persistent_conn']!=1) mysql_close();

function escluso($ip){
// RITORNA TRUE SE L'IP O IL VISITATORE E' ESCLUSO
// Lettura variabili
	global $option,$countExcSip,$excsips,$countExcDip,$excdips;

	if(strpos($_COOKIE['php_stats_esclusion'],"|$option[script_url]|")!==FALSE) return TRUE;	/*** Esclusione tramite cookie ***/

	//EXCLUSION MoD By aVaTaR feature theCAS
	//ESCLUSIONE SIP By aVaTaR feature theCAS
	for($i=0;$i<$countExcSip;++$i)
	{
  		$from=substr($excsips[$i],0,10);
  		$to=substr($excsips[$i],10);
  		if($from<=$nip && $nip<=$to) return TRUE; //esclusione
	}

	// ESCLUSIONE DIP By aVaTaR feature theCAS
	for($i=0;$i<$countExcDip;++$i)
	{
  		$exdip=substr($excdips[$i],2);
  		if($exdip==$nip) return TRUE;
	}
	return FALSE;
}
?>