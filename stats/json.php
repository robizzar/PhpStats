<?php
define('IN_PHPSTATS', true);
include('lang/it/main_lang.inc');

if (!include('option/php-stats-options.php'))
  die("<b>ERRORE</b>: File di config non accessibile.");
if(!include('inc/main_func.inc.php'))
	die('<b>ERRORE</b>: File main_func.inc.php non accessibile.');
if(!include('inc/admin_func.inc.php'))
	die('<b>ERRORE</b>: File admin_func.inc.php non accessibile.');
if(!include('inc/user_func.inc.php'))
	die('<b>ERRORE</b>: File user_func.inc.php non accessibile.');

// Connessione a MySQL e selezione database
db_connect();

global $db,$option,$style,$string,$varie,$modulo,$tabelle,$_SERVER,$phpstats_title;

// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['main_title'];

// Var definition
$hits_questo_mese=$visite_questo_mese=0;
list($date_G,$date_i,$date_m,$date_d,$date_Y)=explode('-',date('G-i-m-d-Y'));
$date_G-=$option['timezone'];
$oggi=date('Y-m-d',mktime($date_G,$date_i,0,$date_m,$date_d,$date_Y));
$this_year=date('Y',mktime($date_G,$date_i,0,$date_m,$date_d,$date_Y));
$ieri=date('Y-m-d',mktime($date_G,$date_i,0,$date_m,$date_d-1,$date_Y));
$questo_mese=date('Y-m-',mktime($date_G,$date_i,0,$date_m,$date_d,$date_Y));
$scorso_mese=date('Y-m-',(($date_d-0)<25 ? mktime($date_G,$date_i,0,$date_m-1,$date_d,$date_Y) : mktime($date_G,$date_i,0,$date_m,$date_d-31,$date_Y)));
// ACCESSI TOTALI
$result=sql_query("SELECT hits,visits,no_count_hits,no_count_visits FROM $option[prefix]_counters LIMIT 1");
list($hits_totali,$visite_totali,$total_spider_hits,$total_spider_visits)=mysql_fetch_row($result);
// AGGIUNGO ACCESSI DI PARTENZA
$hits_totali_glob=$hits_totali+$option['starthits'];
$visite_totali_glob=$visite_totali+$option['startvisits'];
// MODULO 6
if($modulo[6]):

/*** HO ELIMINATO I MOTORI DI RICERCA DAI CONTEGGI ***/
  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data='$oggi'");
  list($hits_oggi,$visite_oggi, $spider_hits_oggi, $spider_visits_oggi)=mysql_fetch_row($result);
  if(!isset($hits_oggi)) $hits_oggi=0;
  if(!isset($visite_oggi)) $visite_oggi=0;
  if(!isset($spider_hits_oggi)) $spider_hits_oggi = 0;
  if(!isset($spider_visits_oggi)) $spider_visits_oggi = 0;
  $hits_oggi   -= $spider_hits_oggi;
  $visite_oggi -= $spider_visits_oggi;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data='$ieri'");
  list($hits_ieri,$visite_ieri, $spider_hits_ieri, $spider_visits_ieri)=mysql_fetch_row($result);
  if(!isset($hits_ieri)) $hits_ieri=0;
  if(!isset($visite_ieri)) $visite_ieri=0;
  if(!isset($spider_hits_ieri)) $spider_hits_ieri = 0;
  if(!isset($spider_visits_ieri)) $spider_visits_ieri = 0;
  $hits_ieri   -= $spider_hits_ieri;
  $visite_ieri -= $spider_visits_ieri;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '$questo_mese%'");
  list($hits_questo_mese,$visite_questo_mese, $spider_hits_questo_mese, $spider_visits_questo_mese)=mysql_fetch_row($result);
  if(!isset($hits_questo_mese)) $hits_questo_mese=0;
  if(!isset($visite_questo_mese)) $visite_questo_mese=0;
  if(!isset($spider_hits_questo_mese)) $spider_hits_questo_mese = 0;
  if(!isset($spider_visits_questo_mese)) $spider_visits_questo_mese = 0;
  $hits_questo_mese   -= $spider_hits_questo_mese;
  $visite_questo_mese -= $spider_visits_questo_mese;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '$scorso_mese%'");
  list($hits_scorso_mese,$visite_scorso_mese, $spider_hits_scorso_mese, $spider_visits_scorso_mese)=mysql_fetch_row($result);
  if(!isset($hits_scorso_mese)) $hits_scorso_mese=0;
  if(!isset($visite_scorso_mese)) $visite_scorso_mese=0;
  if(!isset($spider_hits_scorso_mese)) $spider_hits_scorso_mese = 0;
  if(!isset($spider_visits_scorso_mese)) $spider_visits_scorso_mese = 0;
  $hits_scorso_mese   -= $spider_hits_scorso_mese;
  $visite_scorso_mese -= $spider_visits_scorso_mese;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '%$this_year%'");
  list($hits_this_year,$visite_this_year, $spider_hits_this_year, $spider_visits_this_year)=mysql_fetch_row($result);
  if(!isset($hits_this_year)) $hits_this_year=0;
  if(!isset($visite_this_year)) $visite_this_year=0;
  if(!isset($spider_hits_this_year)) $spider_hits_this_year = 0;
  if(!isset($spider_visits_this_year)) $spider_visits_this_year = 0;
  $hits_this_year   -= $spider_hits_this_year;
  $visite_this_year -= $spider_visits_this_year;
/*** HO ELIMINATO I MOTORI DI RICERCA DAI CONTEGGI ***/

  // PRESET VALUE
  $giorno_inizio='';

  //  Giorni trascorsi
  $result=sql_query("SELECT data FROM $option[prefix]_daily ORDER BY data ASC LIMIT 0,1");
  if(mysql_affected_rows()>0)
    {
    while($row=mysql_fetch_row($result))
      {
      $giorno_inizio=$row[0]; // escludi oggi e primo giorno // by -=JackNight=- (Lucas Meier)
      list($anno_y,$mese_y,$giorno_y)=explode('-',$row[0]);
      $started=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($mese_y),$giorno_y,$anno_y),$varie['date_format']);
      }
    list($anno_t,$mese_t,$giorno_t)=explode('-',$oggi);
    $trascorsi=(mktime(0,0,0,$mese_t,$giorno_t,$anno_t)-mktime (0,0,0,$mese_y,$giorno_y,$anno_y))/86400;
    }
  else
    {
    $trascorsi=0;
    $started='-';
    }

endif;

if($modulo[3]):
  // Tempi medi di visita sito-pagine
  $tocount_pages=$visits_pages=$presence_pages=0;
  $result=sql_query("SELECT SUM(tocount),SUM(visits),SUM(no_count_visits),SUM(presence) FROM $option[prefix]_pages");
  list($tocount_pages,$visits_pages,$no_count_visits_pages,$presence_pages)=mysql_fetch_row($result);
  if($tocount_pages>0) $tempo_pagina=round(($presence_pages/$tocount_pages),0); else $tempo_pagina=0;
  $tempo_visita=round((($hits_totali-$total_spider_hits)/max(1,($visite_totali-$total_spider_visits)))*$tempo_pagina,0);

  // Utenti On-Line
  $tmp=($option['online_timeout']>0 ? $option['online_timeout']*60 : $tempo_pagina*1.3);
  $date=(time()-($option['timezone']*3600)-($tmp));
  $result_ol=sql_query("SELECT data FROM $option[prefix]_cache WHERE data>$date AND notbrowser=0");
  $online=mysql_num_rows($result_ol);
endif;

if($modulo[4]):
  $result=sql_query("SELECT SUM(visits) FROM $option[prefix]_referer");
  list($total_referer)=mysql_fetch_row($result);
  if(!$total_referer) $total_referer=0;

  $result=sql_query("SELECT SUM(visits) FROM $option[prefix]_query");
  list($total_engine)=mysql_fetch_row($result);
  if(!$total_engine) $total_engine=0;
endif;
/*UTILIZZARE QUESTO CODICE QUANDO SI VORRA' DIFFERENZIARE TRA SPIDER E GRABBER
  $result=sql_query("SELECT sum(hits),sum(visits) FROM $option[prefix]_systems WHERE os='Spider'");
  list($total_spider_hits,$total_spider_visits)=mysql_fetch_row($result);
  if(!$total_spider_hits) $total_spider_hits=0;
  if(!$total_spider_visits) $total_spider_visits=0;
  $result=sql_query("SELECT sum(hits),sum(visits) FROM $option[prefix]_systems WHERE os='Grabber'");
  list($total_grabber_hits,$total_grabber_visits)=mysql_fetch_row($result);
  $total_spider_hits+=$total_grabber_hits;
  $total_spider_visits+=$total_grabber_visits;
*/
if($modulo[6]):
// GIORNO "MIGLIORE" (pagine visitate)
$result=sql_query("SELECT data, hits FROM $option[prefix]_daily ORDER BY hits DESC LIMIT 1");
if(mysql_num_rows($result)>0)
  {
  list($max_hits_data,$max_hits)=mysql_fetch_row($result);
  $data=explode('-',$max_hits_data);
  $max_hits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $max_hits=$max_hits_data='-';

// GIORNO "MIGLIORE" (utenti unici) by -=JackNight=-
$result=sql_query("SELECT data,visits FROM $option[prefix]_daily ORDER BY visits DESC LIMIT 1");
if(mysql_num_rows($result)>0)
  {
  list($max_visits_data,$max_visits)=mysql_fetch_row($result);
  $data=explode('-',$max_visits_data);
  $max_visits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $max_visits=$max_visits_data='-';

// GIORNO "PEGGIORE" (pagine visitate)
$result=sql_query("SELECT data,hits FROM $option[prefix]_daily WHERE data!=NOW() AND data!='$giorno_inizio' ORDER BY hits ASC LIMIT 1"); // escludi oggi & primo giorno // by -=JackNight=-
if(mysql_num_rows($result)>0)
  {
  list($min_hits_data,$min_hits)=mysql_fetch_row($result);
  $data=explode('-',$min_hits_data);
  $min_hits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $min_hits=$min_hits_data='-';
endif;

// LIST OF MODS BY by -=JackNight=- (Lucas Meier)
// GIORNO "PEGGIORE" ((utenti unici)
$result=sql_query("SELECT data,visits FROM $option[prefix]_daily WHERE data!=NOW() AND data!='$giorno_inizio' ORDER BY visits ASC LIMIT 1"); // escludi oggi & primo giorno // by -=JackNight=-
if(mysql_num_rows($result)>0)
  {
  list($min_visits_data,$min_visits)=mysql_fetch_row($result);
  $data=explode('-',$min_visits_data);
  $min_visits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $min_visits=$min_visits_data='-';

// QUERY PIU' CERCATA
$result=sql_query("SELECT data,SUM(visits) AS dummy FROM $option[prefix]_query GROUP BY data ORDER BY dummy DESC LIMIT 1");
if(mysql_num_rows($result)>0) list($most_query_word,$most_query_word_volte)=mysql_fetch_row($result);
else $most_query_word=$most_query_word_volte='-';

// MOTORE PIU' USATO
$result=mysql_query("SELECT engine,SUM(visits) AS dummy FROM $option[prefix]_query GROUP BY engine ORDER BY dummy DESC");
if(mysql_num_rows($result)>0)
{
	list($most_used_engine,$most_used_engine_volte)=mysql_fetch_row($result);
	$most_query_word = utf8_decode($most_query_word);		/*** */
}
else
	$most_used_engine=$most_used_engine_volte='-';

// CALCOLO PERCENTUALE DEGLI ACCESSI DA MOTORI (dal totale escluso numero impostato in partenza)

$percent_engine_hits = ((100 * $total_engine)/($visite_totali-$total_spider_visits));
$percent_engine_hits = round($percent_engine_hits,1);

// CALCOLO PERCENTUALE DEGLI ACCESSI DA SITI ESTERNI (referer) (dal totale escluso numero impostato in partenza)

$percent_referer = ((100 * $total_referer)/($visite_totali-$total_spider_visits));
$percent_referer = round($percent_referer,1);

//TOTALE ACCESSI DIRETTI

$total_direct = $visite_totali-$total_engine-$total_referer-$total_spider_visits;

// CALCOLO PERCENTUALE DEGLI ACCESSI DIRETTI

$percent_direct = ((100 * $total_direct)/($visite_totali-$total_spider_visits));
$percent_direct = round($percent_direct,1);

// TOTALE ACCESSI DA MOTORI + REFERER

$total_extern = ($total_referer + $total_engine);

// CALCOLO PERCENTUALE DEGLI ACCESSI DA MOTORI + REFERER (escluso numero di partenza)

$percent_total_extern = ((100 * $total_extern)/($visite_totali-$total_spider_visits));
$percent_total_extern = round($percent_total_extern,1);
//END -=JackNight=- MODS

////////////////////////////////////
// VISUALIZZO I DATI DEL SOMMARIO //
////////////////////////////////////
if($modulo[6]):
/*** Preleva dal database i visitatori di ritorno di oggi ***/
$result=sql_query("SELECT rets FROM $option[prefix]_daily WHERE data='$oggi' LIMIT 1");
list($returns_oggi)=mysql_fetch_row($result);
if(!isset($returns_oggi)) $returns_oggi=0;


$result=sql_query("SELECT min(data) FROM $option[prefix]_daily");
$row=mysql_fetch_row($result);
$ini_year=substr($row[0],0,4);
if($this_year!=$ini_year && $ini_year!='')
  {
  $string['hits_qa']=str_replace('%THIS_YEAR%',$this_year,$string['hits_qa']);
  $string['visitors_qa']=str_replace('%THIS_YEAR%',$this_year,$string['visitors_qa']);
  }
endif;




if($modulo[3]==2):
  list($max_ol,$time_ol)=explode('|',$option['instat_max_online']);
  if($max_ol) {
    $tmp=str_replace(Array('%DATA%','%ORA%'),Array(formatdate($time_ol,3),formattime($time_ol,3)),$string['main_max_ol']);
    $return.="\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$tmp</span></TD><TD bgcolor=$style[table_bgcolor] valign=\"top\"><span class=\"tabletextB\">$max_ol</span></TD>";
    }
endif;



////////////////////
// Utenti On-Line //
////////////////////

if($modulo[3]):
$online=mysql_num_rows($result_ol);


  $tmp=($option['online_timeout']>0 ? $option['online_timeout']*60 : $tempo_pagina*1.3);
  $date=(time()-($option['timezone']*3600)-($tmp));
  /*** Aggiunto 'AND notbrowser=0' per non visualizzare gli Spider come utenti online ***/
  $result=sql_query("SELECT user_id,data,lastpage FROM $option[prefix]_cache WHERE data>$date AND notbrowser=0 ORDER BY user_id ASC");
  while($row=mysql_fetch_array($result,MYSQL_ASSOC))
    {
    if($option['page_title']==1)
      {
/*** patch Francesco Mortara - fmortara@mfweb.it - 2012-01-06 */
		$row['lastpage'] = addslashes($row['lastpage']);
/*** */
       $result_title=sql_query("SELECT titlePage FROM $option[prefix]_pages WHERE data='$row[lastpage]' LIMIT 1");
       if(mysql_affected_rows()>0)
       {
        list($title_page)=mysql_fetch_row($result_title);
        $title_page=stripslashes($title_page);
       }
      else $title_page='';
      }
      $dottedIP=long2ip($row['user_id']);
      $return.=
      "\n\t<tr>".
      "\n\t<td align=\"right\" bgcolor=$style[table_bgcolor] width=\"50\" nowrap><span class=\"tabletextA\">";
      if($option['ext_whois']=='') $return.="<a href=\"javascript:whois('whois.php?IP=$dottedIP');\">$dottedIP</a>";
      else $return.="<a href=\"".str_replace('%IP%',$dottedIP,$option['ext_whois'])."\" target=\"_BLANK\">$dottedIP</a>";
     }
endif;


if($option['show_average_user']==1)
  {
//////////////////
// Utente Medio //
//////////////////
$result=sql_query("SELECT os,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE os!='Spider' AND os!='Grabber' AND os!='?' GROUP BY os ORDER BY dummy DESC LIMIT 1");
$au_os='-';
$icon_os='';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_os=$tmp;
  $icon_os='<img src="images/os.php?q='.str_replace(' ','-',$au_os).'">';
  }

$result=sql_query("SELECT bw,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE os!='Spider' AND os!='Grabber' AND bw!='?' GROUP BY bw ORDER BY dummy DESC LIMIT 2");
$au_bw='-';
$icon_bw='';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_bw=$tmp;
  $icon_bw='<img src="images/browsers.php?q='.str_replace(' ','-',$au_bw).'">';
  }

$result=sql_query("SELECT reso,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE reso!='?' GROUP BY reso ORDER BY dummy DESC LIMIT 1");
$au_resolution='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_resolution=$tmp;
  }

$result=sql_query("SELECT colo,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE colo!='?' GROUP BY colo ORDER BY dummy DESC LIMIT 1");
$au_colors='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_colors=$tmp.' bit';
  }

$result=sql_query("SELECT tld,area,visits AS dummy FROM $option[prefix]_domains WHERE hits>0 AND tld!='unknown' AND tld!='lan' ORDER BY dummy DESC LIMIT 1");
$au_country='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$au_area,$dummy)=mysql_fetch_row($result);
  include("lang/$option[language]/domains_lang.php");
  $au_area=$domain_name['area_'.$au_area];
  $au_area=substr($au_area,0,-1);
  $au_country=$domain_name[$tmp].' ('.$au_area.')';
  }


$result=sql_query("SELECT lang,hits AS dummy FROM $option[prefix]_langs WHERE lang!='unknown' AND hits>0 ORDER BY dummy DESC LIMIT 1");
$au_lang='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  include("lang/$option[language]/bw_lang.php");
  $au_lang=$bw_lang[$tmp];
  }
  }

/////////////////
// Server Info //
/////////////////
$mysql_ver=mysql_get_server_info();
if($mysql_ver=='') $mysql_ver=$string[main_no_mysql_ver];




// TOP PAGES
$topPages = array();

$result=sql_query("SELECT data,(hits-no_count_hits) AS realhits,titlePage FROM $option[prefix]_pages WHERE titlePage like '%%' AND hits<>0 ORDER BY realhits DESC LIMIT 0,100");
$i=0;


while ($row = mysql_fetch_array($result, MYSQL_NUM))
{

    
    if ($row[2] != "")
    {
	    $toshow = $row[2];
	}
	else
	{
		$toshow = ">pagina senza titolo<";
	}

	$toshow = preg_replace('/%u([0-9A-F]+)/', '&#x$1;', $toshow);
  	$toshow = utf8_encode($toshow);
  
  	$toshow = html_entity_decode($toshow, ENT_COMPAT, 'UTF-8');
	$topPages["k$i"] = /*utf8_encode*/("$toshow\t".$row[0]."\t".$row[1]);
	$i++;
    //echo $toshow.'<br>';
}




// QUERY AI MOTORI

//echo '<pre>';

$topQuery = array();

$query_tot=sql_query("SELECT SUM(visits) AS realhits, data FROM $option[prefix]_query GROUP BY data ORDER BY realhits DESC LIMIT 0,100");
while($row=mysql_fetch_row($query_tot))
{
    $toshow = $row[1]."\t".$row[0];
	$toshow = preg_replace('/%u([0-9A-F]+)/', '&#x$1;', $toshow);
  	$toshow = utf8_encode($toshow);
  
  	$toshow = html_entity_decode($toshow, ENT_COMPAT, 'UTF-8');
	$topQuery["k$i"] = $toshow;
	$i++;


//var_dump($row);
//echo "\n\r";
}


//print_r($topPages);
//die;


//-------------------------------------------------------------------------------------------------------------------------------

header('Content-Type: application/json');


$pswd = base64_decode( $_GET['psw'] );
file_put_contents('!log.txt', $_GET['psw']."\n", FILE_APPEND);
file_put_contents('!log.txt', $pswd."\n\n", FILE_APPEND);

if ( user_login(false, $pswd) == false )
	die("Password non valida!");
	

$handle = @fopen('browscap/browscap.ini', 'r');
if ($handle) {
	$i = 0;
    while ($i <= 40) {
		$buffer = fgets($handle);
		if (strpos($buffer, 'Released') !== false) {
        	$browscap_rel = substr($buffer, 14, 11);
        	break;
        }
    	$i++;
    }
	fclose($handle);
}


$sommario = array(
		"hits_tot"				=> 	strval($hits_totali_glob-$total_spider_hits),
		"visitors_tot"		 	=>		strval($visite_totali_glob-$total_spider_visits),
		"hits_oggi"				=>		strval($hits_oggi),
		"visitors_oggi"	 	 	=>		strval($visite_oggi),
		"returns_oggi"		 	=>		strval($returns_oggi),
		"hits_ieri"				=>		strval($hits_ieri),
		"visitors_ieri"		 	=>		strval($visite_ieri),	
		"hits_qm"				=>		strval($hits_questo_mese),
		"visitors_qm"			=>		strval($visite_questo_mese),
		"hits_sm"				=>		strval($hits_scorso_mese),
		"visitors_sm"			=>		strval($visite_scorso_mese),
		"hits_qa"				=>		strval($hits_this_year),
		"visitors_qa"			=>		strval($visite_this_year),
		"perm_site"				=>		strval(formatperm($tempo_visita)),
		"perm_page"				=>		strval(formatperm($tempo_pagina)),

		"hits_per_day"		 	=>		strval(round(($hits_totali_glob-$hits_oggi-$total_spider_hits)/max(1,$trascorsi),1)),
		"visits_per_day"	 	=>		strval(round(($visite_totali_glob-$visite_oggi-$total_spider_visits)/max(1,$trascorsi),1)),
		"pages_per_day"	 	 	=>		strval(round(($hits_totali_glob-$total_spider_hits)/max(1,$visite_totali-$total_spider_visits),1)),
		
		"usr_online"			=>		strval($online),
		"average_user"			=>		strval("$au_bw - $au_os"),
		"average_user_bw"		=>		strval($icon_bw),
		"average_user_bw_icon"	=>		strval($au_bw),
		"average_user_os"		=>		strval($icon_os),
		"average_user_os_icon"	=>		strval($au_os),
		);


$serverInfo = array(
		// Server Info
		'main_server_os'			 =>		php_uname()=='' ? PHP_OS : php_uname(),
		'main_server_ws'			 =>		$_SERVER['SERVER_SOFTWARE'],
		'main_server_php'			 =>		phpversion(),
		'main_mysql_ver'			 =>		$mysql_ver,
		'nomesito'					 =>		$option['nomesito'],
		'browscap_rel'				 =>		$browscap_rel,
		'phpstats_ver'				 =>		$option['phpstats_ver'],
		'server_timestamp'			 =>		date('j/m/Y H:i'),
	);


		
$data = array(
	'Sommario'	=>	array($sommario),
	'TopPages' 	=>  array($topPages),
	'30Days' 	=>  array( daily() ),
	'TopQuery'	=>	array($topQuery),
	'ServerInfo' => array($serverInfo),
	);


echo json_encode($data);

// Chiusura connessione a MySQL se necessario.
if($option['persistent_conn']!=1)
	mysql_close();

//-------------------------------------------------------------------------------------------------------------------------------





function daily() {
global $db,$option,$string,$error,$varie,$style,$mode,$modulo,$phpstats_title;

// <!-- DATA ACQUISITION -->
$curtime=time()-$option['timezone']*3600;
$startTime=strtotime('-29 days',$curtime);
$endTime=$curtime;
$startDate=date('Y-m-d',$startTime);
$endDate=date('Y-m-d',$endTime);

$data_days=Array();



$res=sql_query("SELECT MIN(data) FROM $option[prefix]_daily");
list($statsMinDate)=mysql_fetch_row($res);
$tmp=explode('-',$statsMinDate);
$statsMinTime=mktime(0,0,0,$tmp[1],$tmp[2],$tmp[0]);

$res=sql_query("SELECT data,(hits-no_count_hits) AS hits,(visits-no_count_visits) AS visits FROM $option[prefix]_daily WHERE data>='$startDate' AND data<='$endDate' ORDER BY data ASC");
$lastTime=$startTime;

while($row=mysql_fetch_row($res)){
        list($daily_data,$daily_hits,$daily_visits)=$row;

        while($lastTime<=$endTime){
                $expectedDay=date('Y-m-d',$lastTime);
                if($daily_data===$expectedDay) break;

                $tmp=explode('-',$expectedDay);
                $tmp[0]=(int)$tmp[0];
                $tmp[1]=(int)$tmp[1];
                $tmp[2]=(int)$tmp[2];

                if($expectedDay<$statsMinDate) $data_days[]=Array($tmp,null,null);
                else $data_days[]=Array($tmp,0,0);
                $lastTime=strtotime('+1 day',$lastTime);
        }
        $daily_data=explode('-',$daily_data);
        $daily_data[0]=(int)$daily_data[0];
        $daily_data[1]=(int)$daily_data[1];
        $daily_data[2]=(int)$daily_data[2];

        $data_days[]=Array($daily_data,(int)$daily_hits,(int)$daily_visits,);

        $lastTime=strtotime('+1 day',$lastTime);
}



// <!-- DATA PROCESSING -->

$processed_days=Array();

$maxHits=$maxVisits=0;

$lastVisits=NULL;

for($i=0,$tot=count($data_days);$i<$tot;++$i){
        list($date,$hits,$visits)=$data_days[$i];

        if($hits===NULL){
                $processed_days[]=Array($date,null,null,null);
                $lastVisits=null;
                continue;
        }

        if($hits>$maxHits) $maxHits=$hits;
        if($visits>$maxVisits) $maxVisits=$visits;
        if($lastVisits!==NULL && $lastVisits>0) $visitsVariation=round(($visits-$lastVisits)/$lastVisits*100,1);
        else $visitsVariation=NULL;

        $lastVisits=$visits;

        $processed_days[]=Array($date,$hits,$visits,$visitsVariation);

}
unset($data_days);




// <!-- PRE-OUTPUT PROCESSING -->
$output_days=Array();

$lastMonth=NULL;

for($i=29;$i>=0;--$i){
        list($date,$hits,$visits,$visitsVariation)=$processed_days[$i];

        $dayLabel=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($date[1]),$date[2],$date[0]),$varie['date_format']);
        $monthDay=$date[2];

        $monthBreak=($lastMonth!==NULL && $date[1]!==$lastMonth);
        $lastMonth=$date[1];

        $isSunday=(date('w',mktime(0,0,0,$date[1],$date[2],$date[0]))==0);

        if($hits===NULL){
                $output_days[]=Array($dayLabel,$monthDay,$monthBreak,$isSunday,'-','-','-',0,0,0,0,'unkn');
                continue;
        }

        if($visitsVariation===NULL){
                $visitsVariation='-';
                $level=($visits>0 ? '5' : 'unkn');
        }
        else{
                if($visitsVariation<-15)     $level='1';
                else if($visitsVariation<-5) $level='2';
                else if($visitsVariation<5)  $level='3';
                else if($visitsVariation<15) $level='4';
                else                         $level='5';

                if($visitsVariation>0) $visitsVariation='+'.$visitsVariation;
                $visitsVariation.=' %';
        }

        $hitsRep=$hits/$maxHits;
        $hitsVBarLength=round($hitsRep*187);
        $hitsHBarLength=round($hitsRep*250);

        $visitsRep=$visits/$maxVisits;
        $visitsVBarLength=round($visitsRep*187);
        $visitsHBarLength=round($visitsRep*250);

        $pagineArray[]=Array(strval($monthDay), strval($hits), strval($visits));
}
//echo "<pre>";
//echo "Giorno:$monthDay, Pagine:$hits, Visitatori:$visits \n";
return($pagineArray);

}



?>
