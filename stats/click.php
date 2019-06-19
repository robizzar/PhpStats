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

define('IN_PHPSTATS',true);

                  if(!isset($_GET)) $_GET=$HTTP_GET_VARS;
             if(isset($_GET['id'])) $id=$_GET['id']; else $id="";
               if(!isset($_SERVER)) $_SERVER=$HTTP_SERVER_VARS;
if(isset($_SERVER['HTTP_REFERER'])) $HTTP_REFERER=$_SERVER['HTTP_REFERER'];
 if(isset($_SERVER['REMOTE_ADDR'])) $ip=(isset($_SERVER['HTTP_PC_REMOTE_ADDR']) ? $_SERVER['HTTP_PC_REMOTE_ADDR'] : $_SERVER['REMOTE_ADDR']);

// Security Issues
if (!preg_match('@^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$@D',$ip)) die("Die"); 

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

$HTTP_USER_AGENT='?';
if(!isset($_SERVER)) $_SERVER=$HTTP_SERVER_VARS;
if(isset($_SERVER['HTTP_USER_AGENT']) && $HTTP_USER_AGENT==='?'){
  $tmp=htmlspecialchars(addslashes($_SERVER['HTTP_USER_AGENT']));
  $HTTP_USER_AGENT=str_replace(' ','',$tmp);
  }
$spider_agent=false;
if($HTTP_USER_AGENT!='?'){
  getbrowser($HTTP_USER_AGENT);
  if(strpos(__RANGE_MACRO__,$GLOBALS['cat_macro'])) $spider_agent=true;
  }

$get='';
if($option['prefix']=='') $option['prefix']='php_stats';
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
if(!include("lang/$option[language]/main_lang.inc")) die("<b>ERRORE</b>: File $option[language]/main_lang.inc non accessibile."); // Language file

// Statistiche attive?
if($option['stats_disabled']) die($string['click_stats_disabled']);

// Controllo la validità dell'id (Per evitare SQL injection!)
if(!ereg('(^[0-9]*[0-9]$)',$id)) die("$error[click_errs_id]");
if($id!='')
  {
  $result=sql_query("SELECT url FROM $option[prefix]_clicks WHERE id='$id' LIMIT 1");
  if(mysql_affected_rows()>0)
    list($get)=mysql_fetch_row($result);
    else
    echo"<br><br><center>$error[click_noid]</center>";
  }
if($get!="")
  {
  $get=str_replace(" ","%20",$get);
  //$check=fopen($get,"r");
  $check=fsockopen($get);		/*** SU ALTERVISTA NON FUNZIONAVA LA fopen() ***/
  if($check!=false)
    {
   	fclose($check);				/*** MANCAVA CHIUSURA FILE ***/
    header("location: $get");
    if(!escluso($ip)) sql_query("UPDATE $option[prefix]_clicks SET clicks=clicks+1 WHERE id='$id'");
    }
    else
    {
    $tmp=str_replace("%filename%",$get,$error['click_down']);
    echo"<br><br><center>$tmp</center>";
    }
  if($modulo[0] && !escluso($ip)):
  // INSERISCO NEI DETTAGLI IL CLICK
  $result=sql_query("SELECT visitor_id,os FROM $option[prefix]_cache WHERE user_id='$ip' LIMIT 1");
  if(mysql_num_rows($result)>0)
    {
    list($visitor_id,$os)=mysql_fetch_row($result);
    $date=time()-$option['timezone']*3600;
    $loaded="clk|$id";
//  sql_query("INSERT INTO $option[prefix]_details VALUES ('$visitor_id','$ip','','','','','$date','','$loaded','','','','')");
/*** Aggiunto campi rets e last_return e valore os altrimenti veniva interpretato come motore di ricerca */
	sql_query("INSERT INTO $option[prefix]_details VALUES ('$visitor_id','$ip','','$os','','','$date','','$loaded','','','CLICK ID: $id','','','')");
    }
  endif;
  }

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
