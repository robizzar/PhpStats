<?php
      //////////////////////////////////////////////////////////////
      // MODE 0 -> Utenti on-line (default)                       //
      // MODE 1 -> Visitatori di OGGI                             //
      // MODE 2 -> Pagine visitate di OGGI                        //
      // MODE 3 -> Visitatori TOTALI                              //
      // MODE 4 -> Pagine visitate TOTALI                         //
      // MODE 5 -> Visitatori di Ritorno OGGI                     //
      // MODE 6 -> Visitatori pagine corrente                     //
      // MODE 8 -> Downloads (bisogna specificare l'ID)           //
      // MODE 9 -> Clicks (bisogna specificare l'ID)              //
      //////////////////////////////////////////////////////////////

define('IN_PHPSTATS',true);


  if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else $mode=0;
 if(isset($_GET['style'])) $style=addslashes($_GET['style']); else $style="";
if(isset($_GET['digits'])) $digits=addslashes($_GET['digits']); else $digits="";
    if(isset($_GET['id'])) $id=addslashes($_GET['id']);

// Rilevo il tipo di modalità utilizzata: no_write o write
//include('option/php-stats_mode.php');
//if(!isset($NowritableServer))
//	$NowritableServer=1;

//if ($NowritableServer == 0)
	require('option/php-stats-options.php');
//else
//	require('config.php');

require('inc/main_func_stats.inc.php');
require('inc/admin_func.inc.php');

if(!isset($option['prefix']))
	$option['prefix']='php_stats';

// Connessione a MySQL e selezione database
db_connect();
/*
if($NowritableServer===1)
{
  	// Lettura variabili
  	$result=sql_query("SELECT name,value FROM $option[prefix]_config");
	while($row=mysql_fetch_array($result)) $option[$row[0]]=$row[1];
}
*/
if($digits=="")
	$digits=$option['cifre'];
if($style=="")
	$style=$option['stile'];

switch ($mode)
{
    // UTENTI ON-LINE
    case '0':
    	$ip=(isset($_SERVER['HTTP_PC_REMOTE_ADDR']) ? $_SERVER['HTTP_PC_REMOTE_ADDR'] : $_SERVER['REMOTE_ADDR']);
    	$ip=sprintf('%u',ip2long($ip))-0;
        if($option['online_timeout']>0)
        	$tmp=$option['online_timeout']*60;
        else
        {
      		$result=sql_query("SELECT SUM(tocount),SUM(presence) FROM $option[prefix]_pages");
      		list($tocount_pages,$presence_pages)=mysql_fetch_row($result);
      		$tempo_pagina=round(($presence_pages/$tocount_pages),0);
      		$tmp=$tempo_pagina*1.3;
       	}
        $date=(time()-($option['timezone']*3600)-($tmp));
    	$result=sql_query("SELECT data FROM $option[prefix]_cache WHERE data>$date AND notbrowser=0");
    	$online=mysql_num_rows($result);
    	$result=sql_query("SELECT * FROM $option[prefix]_cache WHERE user_id='$ip' AND data>$date AND notbrowser=0");
    	$ip_presente=mysql_num_rows($result);
    	if($ip_presente!=1)
    		++$online;
    	$toshow=$online;
    	break;

    // VISITATORI DI OGGI
    case '1':
    	$tmp1=0;
        $tmp2=0;
    	$data_oggi=date("Y-m-d",mktime(date("G")-$option['timezone'],date("i"),0,date("m"),date("d"),date("Y")));
    	$result=sql_query("SELECT visits,no_count_visits FROM $option[prefix]_daily WHERE data='$data_oggi' LIMIT 1");
    	list($tmp1,$tmp2)=mysql_fetch_row($result);
        $result=sql_query("SELECT SUM(visits) FROM $option[prefix]_cache WHERE giorno='$data_oggi' AND notbrowser=0 LIMIT 1");
        list($tmp3)=mysql_fetch_row($result);
        $toshow=$tmp1-$tmp2+$tmp3;
    	break;

    // PAGINE DI OGGI
    case '2':
        $tmp1=0;
        $tmp2=0;
    	$data_oggi=date("Y-m-d",mktime(date("G")-$option['timezone'],date("i"),0,date("m"),date("d"),date("Y")));
    	$result=sql_query("SELECT hits,no_count_hits FROM $option[prefix]_daily WHERE data='$data_oggi' LIMIT 1");
    	list($tmp1,$tmp2)=mysql_fetch_row($result);
        $result=sql_query("SELECT SUM(hits) FROM $option[prefix]_cache WHERE giorno='$data_oggi' AND notbrowser=0 LIMIT 1");
        list($tmp3)=mysql_fetch_row($result);
        $toshow=$tmp1-$tmp2+$tmp3;
    	break;

	// VISITATORI TOTALI
    case '3':
        list($visite,$no_count_visite)=mysql_fetch_row(sql_query("SELECT visits,no_count_visits FROM $option[prefix]_counters LIMIT 1"));
    	$toshow=$visite-$no_count_visite+$option['startvisits'];
        break;

    // PAGINE TOTALI
    case '4':
        list($hits,$no_count_hits)=mysql_fetch_row(sql_query("SELECT hits,no_count_hits FROM $option[prefix]_counters LIMIT 1"));
        $toshow=$hits-$no_count_hits+$option['starthits'];
        break;

    // VISITATORI DI RITORNO OGGI
    case '5':
    	$data_oggi=date("Y-m-d",mktime(date("G")-$option['timezone'],date("i"),0,date("m"),date("d"),date("Y")));
    	$result=sql_query("SELECT rets FROM $option[prefix]_daily WHERE data='$data_oggi' LIMIT 1");
    	list($toshow)=mysql_fetch_row($result);
    	$toshow = $toshow+0;
    	break;

    // N° VISITE ALLA PAGINA CORRENTE
    case '6':
	$current_url = urldecode( $_SERVER['HTTP_REFERER'] );
//file_put_contents("debug.txt", $current_url);
        list($hits,$no_count_hits)=mysql_fetch_row(sql_query("SELECT hits,no_count_hits FROM $option[prefix]_pages WHERE data='$current_url' LIMIT 1"));
        $toshow=$hits-$no_count_hits+$option['starthits'];
        break;

    // NUMERO DI DOWNLOADS DELL'ID SPECIFICATO
    case '8':
        $toshow=0;
        if(!ereg('(^[0-9]*[0-9]$)',$id))
        	die("<B>ERRORE:</B> Specificare un id numerico.");
    	$result=sql_query("SELECT downloads FROM $option[prefix]_downloads WHERE id='$id' LIMIT 1");
    	list($toshow)=mysql_fetch_row($result);
    	$toshow = $toshow+0;
    	break;

    // NUMERO DI CLICKS DELL'ID SPECIFICATO
    case '9':
        $toshow=0;
        if(!ereg('(^[0-9]*[0-9]$)',$id))
        	die("<B>ERRORE:</B> Specificare un id numerico.");
    	$result=sql_query("SELECT clicks FROM $option[prefix]_clicks WHERE id='$id' LIMIT 1");
    	list($toshow)=mysql_fetch_row($result);
    	$toshow = $toshow+0;
    	break;
  }

// VISUALIZZO
$line="document.write('";
if($style==0)
	$line.=$toshow;
else
{
	chop($toshow);
    $nb_digits=max(strlen($toshow),$digits);
    $toshow=substr("0000000000".$toshow,-$nb_digits);
    $digits=preg_split("//",$toshow);
    for($i=0;$i<=$nb_digits;++$i)
      if($digits[$i]!="")
      	$line.="<IMG SRC=\"$option[script_url]/stili/$style/$digits[$i].gif\">";
}
$line.="');";
echo $line;

// Chiusura connessione a MySQL se necessario.
if($option['persistent_conn']!=1)
	mysql_close();
unset($option);
?>
