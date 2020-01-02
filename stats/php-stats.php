<?php
/*  ___ _  _ ___       ___ _____ _ _____ ___ 
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|       |___/ |_/_/ \_\_| |___/
 */

define('IN_PHPSTATS', true);
require_once('inc/php7support.inc.php');

// VARIABILI ESTERNE
if (isset($_COOKIE['php_stats_esclusion']))
	$php_stats_esclusion = $_COOKIE['php_stats_esclusion'];
else
	$php_stats_esclusion = '';
if (isset($_SERVER['REMOTE_ADDR'])) {
	$ip       = getUserIP();
	$hostname = gethostbyaddr($ip);
	/*** L'HOSTNAME LO PRENDO QUI UNA VOLTA PER TUTTE ***/
}

require('option/php-stats-options.php');

// Se necessario invio subito l'immagine così posso effettuare gli exit() in qualunque momento
if ($option['callviaimg']) {
	// Immagine fittizia 1 pixel x 1 pixel trasparente
	header('Content-Type: image/gif');
	echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
	flush();
}

// Controllo esclusione tramite cookie prima per evitare operazioni inutili
if (strpos($php_stats_esclusion, "|$option[script_url]|") !== FALSE)
	exit();

if ($option['stats_disabled']) // Statistiche attive?
	exit();

require('inc/main_func_stats.inc.php');

if (!isset($option['prefix']))
	$option['prefix'] = 'php_stats';

$append = 'LIMIT 1';

db_connect();
/*** */

$s = urldecode(urlencode('§§')); //Codifica e decodifica del sepatarore

$title = '?';

if ($option['page_title'] && isset($_GET['t'])) {
	$tmpTitle = htmlspecialchars(mysql_real_escape_string(urldecode($_GET['t'])));
	if ($tmpTitle != '\\\\\\&quot; t \\\\\\&quot;')
		$title = $tmpTitle;
}

$ip = $nip = sprintf('%u', ip2long($ip)) - 0;


// ESCLUSIONE SIP (IP statico)
for ($i = 0; $i < $countExcSip; ++$i) {
	$from = substr($excsips[$i], 0, 10) - 0;
	$to   = substr($excsips[$i], 10) - 0;
	if ($from <= $nip && $nip <= $to) {
		logerrors(date('d/m/y H:i') . " ** IP exclusion: " . long2ip($ip));
		exit();
	}
}

/*** ESCLUSIONE DIP (IP dinamico) by robiz
Esclude gruppi di host; sarà confrontata la stringa a partire da destra, quindi è possibile escludere
un intero dominio inserendo ad esempio ".google.com".
***/
for ($i = 0; $i < $countExcDip; ++$i) {
	$excdips[$i] = trim($excdips[$i]);
	/* Elimina eventuali spazi o \n di troppo */
	$my_len      = strlen($excdips[$i]);
	if ($excdips[$i] == substr($hostname, 0 - $my_len, $my_len)) {
		/*		if($option['logerrors'])
		file_put_contents('php-stats.log', date('d/m/y H:i')." ** Hostname exclusion: $hostname\n", FILE_APPEND);*/
		logerrors(date('d/m/y H:i') . " ** Hostname exclusion: $hostname");
		exit();
	}
	/* Confronto stringa esatta invece di parte dell'hostname come sopra
	if ($hostname == $excdips[$i])
	exit(); */
}

$GLOBALS['bcap_auto_update'] = $option['bcap_auto_update'];
/*** Dall'interno della classe di browscap posso accedere solo a variabili superglobal ***/
/***
QUESTA PARTE DI CODICE E' CONTENUTA SIA IN PHP-STATS.PHP (CODICE HTML PER IL MONITORAGGIO)
SIA IN PHP-STATS.REDIR.PHP (CODICE PHP PER IL MONITORAGGIO)
***/
require('browscap/Browscap.php');
$bcap    = new Browscap('browscap/'); // Creates a new Browscap object (loads or creates the cache)
$my_bcap = $bcap->getBrowser(); // Gets information about the current browser's user agent

if ($my_bcap->Browser == 'Default Browser') // Se non viene riconosciuto (ad es. il browser è troppo recente e non ancora incluso nel database), visualizza '?'
	{
	$my_bcap->Browser = '?';
	$my_bcap->Version = '';
}
$GLOBALS['my_bcap'] = $my_bcap;
/*** ***/

if ($modulo[11] == 0 && $my_bcap->Crawler == true) // Se è un motore di ricerca e le statistiche non sono attive, esce
	exit();


// PREPARARO VARIABILI
$loaded = '?';
if (isset($HTTP_REFERER))
	$loaded = htmlspecialchars(addslashes($HTTP_REFERER));
else {
	if (isset($_GET['NS_url'])) {
		$tmp = htmlspecialchars(addslashes(urldecode($_GET['NS_url'])));
		if ($tmp != '' && strpos($tmp, 'NS_url') === FALSE)
			$loaded = str_replace($s, '&', $tmp); // Pagina visualizzata
	}
}

if ($loaded != '?') {
	if ($option['www_trunc']) {
		if (strtolower(substr($loaded, 0, 11)) === 'www.')
			$loaded = '' . substr($loaded, 11);
	}
	$loadedLC = strtolower($loaded);
	
	// ESCLUSIONE CARTELLE e URL
	if ($option['exc_fol'] !== '') {
		for ($i = 0; $i < $countExcFol; ++$i) {
			if (strpos($loadedLC, $excf[$i]) !== FALSE) {
				logerrors(date('d/m/y H:i') . " ** Folder exclusion: $loadedLC");
				exit();
			}
		}
		$tmp = '/' . strtolower(basename($loadedLC));
		if (in_array($tmp, $default_pages))
			$loaded = substr($loaded, 0, -strlen($tmp));
		$loaded = filter_urlvar($loaded, 'sid'); // ELIMINO VARIABILI SPECIFICHE NELLE PAGINE VISITATE (esempio il session-id)
	}
}

if ($loaded != '?' && !ereg('^[[:alnum:]._-]{2,}', $loaded))
	$loaded = '?';

$date = time() - $option['timezone'] * 3600;
list($date_Y, $date_m, $date_d, $date_G) = explode('-', date('Y-m-d-G', $date));
$mese_oggi = $date_Y . '-' . $date_m; // Y-m
$data_oggi = $mese_oggi . '-' . $date_d; // Y-m-d
$ora       = $date_G;

$secondi = $date - 3600 * $option['ip_timeout']; // CALCOLO LA SCADENZA DELLA CACHE
/////////////////////////////////////////////////////////////////////////////////////////////
// VERIFICO SE L'IP E' PRESENTE NELLA CACHE: SE NECESSARIO LO INSERISCO OPPURE LO AGGIORNO //
/////////////////////////////////////////////////////////////////////////////////////////////
$cache_cleared = 0; // Flag -> La cache ha subito una pulizia
$do_update     = 0; // Flag -> Devo eseguire l'update della cache
$do_insert     = 0; // Flag -> Devo eseguire l'inserimento nella cache
$domain        = '';

// Riconoscimento immediato agent per evitare operazioni inutili con spider e per poterli raggruppare
$HTTP_USER_AGENT = $nome_os = $nome_bw = '?';
if (isset($_SERVER['HTTP_USER_AGENT'])) {
	$tmp             = htmlspecialchars(addslashes($_SERVER['HTTP_USER_AGENT']));
	$HTTP_USER_AGENT = str_replace(' ', '', $tmp);
}
$spider_agent = $ip_agent_cached = false;

$temp_bw = getbrowser($HTTP_USER_AGENT);
$temp_os = getos($HTTP_USER_AGENT);
$result  = sql_query("SELECT data,lastpage,user_id,visitor_id,reso,colo,os,bw,tld,giorno,level FROM $option[prefix]_cache WHERE (user_id='$ip' AND bw='$temp_bw' AND os='$temp_os') LIMIT 1");

if (mysql_affected_rows() > 0)
	$ip_agent_cached = true;
else {
	if ($HTTP_USER_AGENT != '?') {
		$nome_bw = getbrowser($HTTP_USER_AGENT);
		$nome_os = getos($HTTP_USER_AGENT);
	}
	if ($spider_agent === FALSE && ($nome_os == '?' || $nome_bw == '?')) {
		/***    $spider_agent = TRUE;	*/ // Non riconosciuto: lo considero come motore di ricerca */
		list($nome_os, $nome_bw, $spider_agent) = getfromip($nip, $nome_os, $nome_bw);
	}
	if ($spider_agent === true) {
		$result = sql_query("SELECT data,lastpage,user_id,visitor_id,reso,colo,os,bw,tld,giorno,level FROM $option[prefix]_cache WHERE os='$nome_os' AND bw='$nome_bw' LIMIT 1");
		if (mysql_affected_rows() > 0)
			$ip_agent_cached = true;
	}
}

if ($ip_agent_cached) {
	list($last_page_time, $last_page_url, $user_id, $visitor_id, $reso, $c, $nome_os, $nome_bw, $domain, $giorno, $level) = mysql_fetch_row($result);
	$ip = $user_id;
	if ($spider_agent === false) {
		if (strpos(__RANGE_MACRO__, $nome_os))
			$spider_agent = true;
	}
	
	// Aggiornamento tempo di permanenza dell'ultima pagina
	if ($modulo[3] && ($spider_agent === false)) {
		$tmp = $date - $last_page_time;
		
		/*** */
		$last_page_url = mysql_real_escape_string($last_page_url);
		if ($tmp < $option['page_timeout'])
			sql_query("UPDATE $option[prefix]_pages SET presence=presence+$tmp,tocount=tocount+1,date=$date WHERE data='$last_page_url' $append");
	}
	
	// VERIFICO SCADENZA PAGINA IN CASO DI IP IDENTICI
	if ($last_page_time < $secondi) { // SCADUTO
		$cache_cleared = do_clear($visitor_id, 1); // PULIZIA TOTALE
		$do_insert     = 1; // DEVO INSERIRE IL NUOVO VISITATORE
	} else { // NON SCADUTO
		if ($data_oggi != $giorno) // Controllo visite a cavallo di 2 giorni
			$cache_cleared = do_clear($visitor_id, 0); // PULIZIA PARZIALE, NON CANCELLO
		$do_update = 1; // Ma aggiorno sempre un dato non scaduto
	}
} else
	$do_insert = 1; // Se non trovo l'IP nella cache inserisco.

if ($do_update) // AGGIORNAMENTO CACHE
	{
	sql_query("UPDATE $option[prefix]_cache SET data='$date',lastpage='$loaded',giorno='$data_oggi',hits=hits+1" . ($spider_agent ? '' : ',level=level+1') . " WHERE user_id='$ip' $append");
	$is_uniqe = 0;
	++$level;
	$update_hv = 'hits=hits+1' . ($spider_agent ? ',no_count_hits=no_count_hits+1' : '');
}

if ($do_insert) // INSERIMENTO DATI IN CACHE
	{
	$c = $reso = $lang = '?';
	if (isset($_GET['c'])) {
		$tmp = htmlspecialchars(addslashes(urldecode($_GET['c'])));
		if (strpos($tmp, 'c') === FALSE)
			$c = $tmp;
	}
	if (isset($_GET['w']) && isset($_GET['h'])) {
		$w = htmlspecialchars(addslashes(urldecode($_GET['w'])));
		$h = htmlspecialchars(addslashes(urldecode($_GET['h'])));
		if (strpos($w, 'w') === FALSE && strpos($h, 'h') === FALSE)
			$reso = $w . 'x' . $h;
	}
	
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && ($spider_agent === false)) {
		$tmp = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$tmp = strtolower($tmp[0]);
		
		$langArray = explode(',', $tmp);
		
		$res = sql_query("SELECT lang FROM php_stats_langs WHERE 1");
		while ($row = mysql_fetch_row($res)) {
			if (in_array($row[0], $langArray)) {
				$lang = $row[0];
				break;
			}
		}
	}
	
	if (($modulo[7] && $option['ip-zone'] == 0) || $option['log_host'])
		$host = $hostname;
	else
		$host = '';
	
	if ($spider_agent === FALSE && $modulo[7]) {
		$ip_number = $ip;
		if (($ip_number >= 3232235520 && $ip_number <= 3232301055) || 		//192.168.0.0 ... 192.168.255.255
			($ip_number >= 167772160 && $ip_number <= 184549375) || 		//10.0.0.0 ... 10.255.255.255
			($ip_number >= 2886729728 && $ip_number <= 2887778303) || 		//172.16.0.0 ... 172.31.255.255
			($ip_number >= 0 && $ip_number <= 16777215) || 					//0.0.0.0 ... 0.255.255.255
			($ip_number >= 4026531840 && $ip_number <= 4294967295) || 		//240.0.0.0 ... 255.255.255.255
			($ip_number == 2130706433) 										//127.0.0.1
			)
			$domain = 'lan';
		else
			switch ($option['ip-zone']) {
				default: //tramite host
					$domain = '';
					$tmp    = explode('.', $host);
					for ($i = count($tmp) - 1; $i >= 0; --$i) {
						if (!$tmp[$i])
							continue; //esistono domini come 'google.com.'
						$domain = $tmp[$i];
						break;
					}
					break;
				case 1: //tramite database su mySQL
					$result2 = sql_query("SELECT tld FROM $option[prefix]_ip_zone WHERE $ip_number BETWEEN ip_from AND ip_to");
					if (mysql_affected_rows() > 0)
						list($domain) = mysql_fetch_row($result2);
					else
						$domain = 'unknown';
					break;
					/*** DEPRECATED
					case 2: 	//tramite ip2c file
					$domain=getIP($ip_number,23,'ip-to-country.db','ip-to-country.idx',2);
					break; */
			}
	}
	
	$visitor_id = md5(uniqid(rand(), true));
	sql_query("INSERT DELAYED INTO $option[prefix]_cache (user_id,data,lastpage,visitor_id,hits,visits,reso,colo,os,bw,host,tld,lang,giorno,notbrowser,level) VALUES('$ip','$date','$loaded','$visitor_id','1','1','$reso','$c','$nome_os','$nome_bw','$host','$domain','$lang','$data_oggi'," . ($spider_agent === FALSE ? '0' : '1') . ",'1')");
	$is_uniqe  = $level = 1;
	$update_hv = 'hits=hits+1,visits=visits+1' . ($spider_agent ? ',no_count_hits=no_count_hits+1,no_count_visits=no_count_visits+1' : '');
}

//////////////////////////////////////////////////////////
// DATI NON SALVATI IN CACHE E CONTINUAMENTE AGGIORNATI //
//////////////////////////////////////////////////////////
// CONTATORI PRINCIPALI
sql_query("UPDATE $option[prefix]_counters SET $update_hv $append");

// SCRIVO LA PAGINA VISUALIZZATA
if ($modulo[3]) {
	$what = 'hits=hits+1' . ($spider_agent ? ',no_count_hits=no_count_hits+1' : '');
	if ($level < 7 && ($spider_agent === false))
		$what .= ', lev_' . $level . '=lev_' . $level . '+1';
	sql_query("UPDATE $option[prefix]_pages SET $what,date='$date' WHERE data='$loaded' $append");
	if (mysql_affected_rows() < 1) {
		$lev_1 = $lev_2 = $lev_3 = $lev_4 = $lev_5 = $lev_6 = 0;
		if ($level < 7 && ($spider_agent === false))
			eval('$lev_' . $level . '=1;');
		sql_query("INSERT DELAYED INTO $option[prefix]_pages (data,hits,visits,no_count_hits,no_count_visits,presence,tocount,date,lev_1,lev_2,lev_3,lev_4,lev_5,lev_6,outs,titlePage) VALUES('$loaded','1','$is_uniqe'," . ($spider_agent ? "'1','$is_uniqe'" : "'0','0'") . ",'0','0','$date','$lev_1','$lev_2','$lev_3','$lev_4','$lev_5','$lev_6','0','$title')");
	}
	if ($option['prune_4_on'])
		prune("$option[prefix]_pages", $option['prune_4_value']);
}

// PREPARO REFERER
$reffer = $details_referer = '';
if (isset($_GET['f']) && $_GET['f'] != '') {
	$tmpreffer = $tmpreffer2 = trim(htmlspecialchars(addslashes($_GET['f'])));
	if ($option['www_trunc'])
		if (strtolower(substr($tmpreffer, 0, 11)) == 'www.')
			$tmpreffer = '' . substr($tmpreffer, 11);
	if (is_internal($tmpreffer) === FALSE) {
		$tmpreffer = str_replace($s, '&', $tmpreffer);
		if (strpos($tmpreffer, 'reffer') === FALSE)
			$reffer = filter_urlvar($tmpreffer2, 'sid'); // ELIMINO VARIABILI SPECIFICHE NEI REFERER (esempio il session-id)
	}
}

if ($reffer != '' && !ereg('^[[:alnum:]._-]{2,}', $reffer))
	$reffer = '';

// SCRIVO I MOTORI DI RICERCA, QUERY e REFERER
if ($modulo[4]) {
	if ($reffer != '') {
		if (1) {
			if (substr($reffer, -1) === '/')
				$reffer = substr($reffer, 0, -1);
			$engineResult = getengine($reffer);
			if ($engineResult !== FALSE) {
				list($nome_motore, $domain, $query, $resultPage) = $engineResult;
				/***      		$details_referer=implode('|',$engineResult).'|'.addslashes(urldecode($reffer));*/
				/*** */
				$details_referer = implode('|', $engineResult) . '|' . urldecode($reffer);
				
				// MOTORI DI RICERCA E QUERY
				/*** */
				$query = mysql_real_escape_string($query);
				if (!empty($query)) // A volte capitano query vuote o non decodificate: qui evita l'inserimento
					{
					$clause = "data='$query' AND engine='$nome_motore' AND domain='$domain' AND page='$resultPage'";
					if ($modulo[4] == 2)
						$clause .= " AND mese='$mese_oggi'";
					sql_query("UPDATE $option[prefix]_query SET visits=visits+1, date='$date' WHERE $clause $append");
					if (mysql_affected_rows() < 1) {
						$insert = "(data,engine,domain,page,visits,date,mese) VALUES('$query','$nome_motore','$domain','$resultPage','1','$date','" . ($modulo[4] == 2 ? "$mese_oggi" : '') . "')";
						sql_query("INSERT DELAYED INTO $option[prefix]_query $insert");
						if ($option['prune_3_on'])
							prune("$option[prefix]_query", $option['prune_3_value']);
					}
				}
			} else // REFERERS
				{
				/***      	$reffer_dec=addslashes(urldecode($reffer)); */
				/*** */
				$details_referer = urldecode($reffer);
				$reffer_dec      = mysql_real_escape_string($details_referer);
				/***    	$reffer_dec = mysql_real_escape_string($reffer_dec); */
				$clause          = "data='$reffer_dec'";
				if ($modulo[4] == 2)
					$clause .= " AND mese='$mese_oggi'";
				sql_query("UPDATE $option[prefix]_referer SET visits=visits+1,date='$date' WHERE $clause $append");
				if (mysql_affected_rows() < 1) {
					$insert = "(data,visits,date,mese) VALUES('$reffer_dec','1','$date','" . ($modulo[4] == 2 ? "$mese_oggi" : '') . "')";
					sql_query("INSERT DELAYED INTO $option[prefix]_referer $insert");
				}
				if ($option['prune_5_on'])
					prune("$option[prefix]_referer", $option['prune_5_value']);
			}
		}
	}
}


/***
SCRIVE NEI DETTAGLI IL NUMERO DI VISITE DEL VISITATORE E LA DATA DELL'ULTIMA VISITA
(IL TUTTO PRELEVATO DAI COOKIE)
***/

$phpstats_rettime = intval($_GET['ps_rettime']);
$phpstats_returns = intval($_GET['ps_returns']);
$phpstats_newret  = intval($_GET['ps_newret']);

if ($phpstats_newret == 1 && $spider_agent === false) {
	sql_query("UPDATE $option[prefix]_daily SET rets=rets+1 WHERE data='" . date('y-m-d') . "'");
	if (mysql_affected_rows() < 1)
		sql_query("INSERT DELAYED INTO $option[prefix]_daily VALUES('" . date('y-m-d') . "','0','0','0','0','1')");
}



// SCRIVO I DETTAGLI
if ($modulo[0])
{
	if ($modulo[10] == 0) { $ip = 0; $host = '0.0.0.0'; }	// Se opzione di log IP non attiva, forza a zero prima di salvare

	// *** porzione prelevata dal recphp perchè mancante
	if ((!$option['refresh_page_title']) && $modulo[3] && $loaded !== '?') {
		$resultTitle = sql_query("SELECT titlePage FROM $option[prefix]_pages WHERE data='$loaded'");
		list($title) = mysql_fetch_row($resultTitle);
		$title = mysql_real_escape_string($title);
	}
	//
	if ($title == '?' || $title == '') /*** Se titolo pagina mancante usa URL ***/ 
		$title = $loaded;
	
	$details_referer = mysql_real_escape_string($details_referer);
	/*** */
	$what            = "'$visitor_id','$ip','$host','$nome_bw','$nome_os','$lang','$date','$details_referer','$loaded','$reso','$c','$title','$domain','$phpstats_returns','$phpstats_rettime'";
	sql_query("INSERT DELAYED INTO $option[prefix]_details (visitor_id,ip,host,bw,os,lang,date,referer,currentPage,reso,colo,titlePage,tld,rets,last_return) VALUES ($what)");
	if ($option['prune_0_on']) {
		$limit    = $option['prune_0_value'] * 3600;
		$secondi2 = $date - $limit;
		sql_query("DELETE FROM $option[prefix]_details WHERE date<$secondi2 LIMIT 2");
	}
	if ($option['prune_1_on'])
		prune_details("$option[prefix]_details", $option['prune_1_value']);
}

// INDIRIZZI IP
if ($modulo[10]) {
	sql_query("UPDATE $option[prefix]_ip SET hits=hits+1,visits=visits+$is_uniqe,date='$date' WHERE ip='$ip'");
	if (mysql_affected_rows() < 1)
		sql_query("INSERT DELAYED INTO $option[prefix]_ip VALUES('$ip','$date','1','1')");
	if ($option['prune_2_on'])
		prune("$option[prefix]_ip", $option['prune_2_value']);
}


// ACCESSI ORARI
if ($modulo[5]) {
	$clause = "data='$ora'";
	if ($modulo[5] == 2)
		$clause .= " AND mese='$mese_oggi'";
	sql_query("UPDATE $option[prefix]_hourly SET $update_hv WHERE $clause $append");
	if (mysql_affected_rows() < 1) {
		$insert = "(data,hits,visits,no_count_hits,no_count_visits,mese) VALUES('$ora','1','$is_uniqe'," . ($spider_agent ? "'1','$is_uniqe'" : "'0','0'") . ",'" . ($modulo[5] == 2 ? "$mese_oggi" : '') . "')";
		sql_query("INSERT DELAYED INTO $option[prefix]_hourly $insert");
	}
}
if ($modulo[3] == 2 || $option['report_w_on']) {
	$result = sql_query("SELECT name,value FROM $option[prefix]_config WHERE name LIKE 'instat_%'");
	while ($row = mysql_fetch_row($result))
		$option2[$row[0]] = $row[1];
	$option2['instat_report_w'] = intval($option2['instat_report_w']); // Mi assicuro che il dato sia un integer
}

// MAX UTENTI ON-LINE
if ($modulo[3] == 2) {
	list($max_ol, $time_ol) = explode('|', $option2['instat_max_online']);
	$max_ol = intval($max_ol); // IMPONGO LA CONVERSIONE AD INTERO
	if ($option['online_timeout'] == 0)
		$tmp = $date - 300;
	else
		$tmp = $date - $option['online_timeout'] * 60;
	$online = 0;
	$result = sql_query("SELECT data FROM $option[prefix]_cache WHERE data>$tmp AND notbrowser=0");
	if (mysql_affected_rows() > 0)
		$online = mysql_num_rows($result);
	if ($online > $max_ol)
		sql_query("UPDATE $option[prefix]_config SET value='$online|$date' WHERE name='instat_max_online'");
}

// Se non l'ho fatto prima, se necessario pulisco, un dato in cache
if (!$cache_cleared) {
	do_clear();
	$cache_cleared = 1;
}

// VERIFICO SE DEVO SPEDIRE L' E-MAIL CON IL PROMEMORIA DEGLI ACCESSI
if ($option['report_w_on'] && $date > $option2['instat_report_w']) {
	include('inc/report.inc.php');
	report();
}

// OPTIMIZE TABLES
if ($option['auto_optimize']) {
	if (!isset($hits))
		list($hits) = mysql_fetch_row(sql_query("SELECT hits FROM $option[prefix]_counters LIMIT 1"));
	if (($hits % $option['auto_opt_every']) == 0) {
		$query = "OPTIMIZE TABLES $option[prefix]_cache";
		if ($option['prune_1_on'] || $option['prune_0_on'])
			$query .= ",$option[prefix]_details";
		if ($option['prune_2_on'])
			$query .= ",$option[prefix]_ip";
		if ($option['prune_4_on'])
			$query .= ",$option[prefix]_pages";
		if ($option['prune_3_on'])
			$query .= ",$option[prefix]_query";
		if ($option['prune_5_on'])
			$query .= ",$option[prefix]_referer";
		sql_query($query);
	}
}


/*** VERIFICA NUOVA VERSIONE ***/
$result = sql_query("SELECT * FROM $option[prefix]_config WHERE name='inadm_last_update'");
if (mysql_affected_rows() > 0) {
	$arr = mysql_fetch_row($result);
	$option['inadm_last_update'] = $arr[1];
}

// Check nuove versioni (ogni 30 giorni)
if ((time() - $option['inadm_last_update']) > (30 * 24 * 3600)) {
	$update = @file_get_contents('http://www.robertobizzarri.net/php-stats/phpstats_ver_check.php?url=' . trim($option['script_url']) . '&ver=' . trim($option['phpstats_ver']) . '&mon=html');
	if (strpos($update, '<!-- New PHP-Stats Version -->') !== false) {
		if ($option['check_new_version']) {
			$update_available = true;
			sql_query("UPDATE $option[prefix]_config SET value='1' WHERE name='inadm_upd_available'");
			
			$site     = explode("\n", $option['server_url']);
			$site_url = str_replace(Array(
				'http://',
				'https://'
			), '', $site[0]);
			
			$user_email = explode("\n", $option['user_mail']);
			$user_email = $user_email[0];
			
			$headers = "From: Php-Stats\r\n" . "MIME-Version: 1.0\r\n" . // To send HTML mail, the Content-type header must be set
						"Content-type: text/html; charset=iso-8859-1\r\n";
			
			$message = 'Site: ' . $option['server_url'] . '<br><br>' . 'A new version of PHP-Stats is available.<br><br>' . '<a href="http://www.robertobizzarri.net/php-stats/">Click here to visit the support site.</a><br>';
			
			mail($user_email, 'PHP-Stats: new version', $message, $headers);
		}
	} else {
		sql_query("UPDATE $option[prefix]_config SET value='0' WHERE name='inadm_upd_available'");
	}
	sql_query("UPDATE $option[prefix]_config SET value='" . time() . "' WHERE name='inadm_last_update'");
}


// Chiusura connessione a MySQL se necessario
if (!$option['persistent_conn'])
	mysql_close();
unset($option);
?>
