<?php

// Version 2.0

define('IN_PHPSTATS', true);

require_once('inc/php7support.inc.php');

//include('lang/en/main_lang.inc');

if (!include('option/php-stats-options.php'))
	die("<b>ERRORE</b>: File di config non accessibile.");
if (!include('inc/main_func.inc.php'))
	die('<b>ERRORE</b>: File main_func.inc.php non accessibile.');
if (!include('inc/admin_func.inc.php'))
	die('<b>ERRORE</b>: File admin_func.inc.php non accessibile.');
if (!include('inc/user_func.inc.php'))
	die('<b>ERRORE</b>: File user_func.inc.php non accessibile.');
if (!include("lang/$option[language]/main_lang.inc"))
	die("<b>ERRORE</b>: File $option[language]/main_lang.inc non accessibile."); // Language file

// Connessione a MySQL e selezione database
db_connect();

global $db, $option, $style, $string, $varie, $modulo, $tabelle, $_SERVER, $phpstats_title;

// Titolo pagina (riportata anche nell'admin)
$phpstats_title = $string['main_title'];

// Var definition
$hits_questo_mese = $visite_questo_mese = 0;
list($date_G, $date_i, $date_m, $date_d, $date_Y) = explode('-', date('G-i-m-d-Y'));
$date_G -= $option['timezone'];
$oggi        = date('Y-m-d', mktime($date_G, $date_i, 0, $date_m, $date_d, $date_Y));
$this_year   = date('Y', mktime($date_G, $date_i, 0, $date_m, $date_d, $date_Y));
$ieri        = date('Y-m-d', mktime($date_G, $date_i, 0, $date_m, $date_d - 1, $date_Y));
$questo_mese = date('Y-m-', mktime($date_G, $date_i, 0, $date_m, $date_d, $date_Y));
$scorso_mese = date('Y-m-', (($date_d - 0) < 25 ? mktime($date_G, $date_i, 0, $date_m - 1, $date_d, $date_Y) : mktime($date_G, $date_i, 0, $date_m, $date_d - 31, $date_Y)));
// ACCESSI TOTALI
$result      = sql_query("SELECT hits,visits,no_count_hits,no_count_visits FROM $option[prefix]_counters LIMIT 1");
list($hits_totali, $visite_totali, $total_spider_hits, $total_spider_visits) = mysql_fetch_row($result);
// AGGIUNGO ACCESSI DI PARTENZA
$hits_totali_glob   = $hits_totali + $option['starthits'];
$visite_totali_glob = $visite_totali + $option['startvisits'];

$result = sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data='$oggi'");
list($hits_oggi, $visite_oggi, $spider_hits_oggi, $spider_visits_oggi) = mysql_fetch_row($result);
if (!isset($hits_oggi))
	$hits_oggi = 0;
if (!isset($visite_oggi))
	$visite_oggi = 0;
if (!isset($spider_hits_oggi))
	$spider_hits_oggi = 0;
if (!isset($spider_visits_oggi))
	$spider_visits_oggi = 0;
$hits_oggi -= $spider_hits_oggi;
$visite_oggi -= $spider_visits_oggi;

$result = sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data='$ieri'");
list($hits_ieri, $visite_ieri, $spider_hits_ieri, $spider_visits_ieri) = mysql_fetch_row($result);
if (!isset($hits_ieri))
	$hits_ieri = 0;
if (!isset($visite_ieri))
	$visite_ieri = 0;
if (!isset($spider_hits_ieri))
	$spider_hits_ieri = 0;
if (!isset($spider_visits_ieri))
	$spider_visits_ieri = 0;
$hits_ieri -= $spider_hits_ieri;
$visite_ieri -= $spider_visits_ieri;

$result = sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '$questo_mese%'");
list($hits_questo_mese, $visite_questo_mese, $spider_hits_questo_mese, $spider_visits_questo_mese) = mysql_fetch_row($result);
if (!isset($hits_questo_mese))
	$hits_questo_mese = 0;
if (!isset($visite_questo_mese))
	$visite_questo_mese = 0;
if (!isset($spider_hits_questo_mese))
	$spider_hits_questo_mese = 0;
if (!isset($spider_visits_questo_mese))
	$spider_visits_questo_mese = 0;
$hits_questo_mese -= $spider_hits_questo_mese;
$visite_questo_mese -= $spider_visits_questo_mese;

$result = sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '$scorso_mese%'");
list($hits_scorso_mese, $visite_scorso_mese, $spider_hits_scorso_mese, $spider_visits_scorso_mese) = mysql_fetch_row($result);
if (!isset($hits_scorso_mese))
	$hits_scorso_mese = 0;
if (!isset($visite_scorso_mese))
	$visite_scorso_mese = 0;
if (!isset($spider_hits_scorso_mese))
	$spider_hits_scorso_mese = 0;
if (!isset($spider_visits_scorso_mese))
	$spider_visits_scorso_mese = 0;
$hits_scorso_mese -= $spider_hits_scorso_mese;
$visite_scorso_mese -= $spider_visits_scorso_mese;

$result = sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '%$this_year%'");
list($hits_this_year, $visite_this_year, $spider_hits_this_year, $spider_visits_this_year) = mysql_fetch_row($result);
if (!isset($hits_this_year))
	$hits_this_year = 0;
if (!isset($visite_this_year))
	$visite_this_year = 0;
if (!isset($spider_hits_this_year))
	$spider_hits_this_year = 0;
if (!isset($spider_visits_this_year))
	$spider_visits_this_year = 0;
$hits_this_year -= $spider_hits_this_year;
$visite_this_year -= $spider_visits_this_year;
/*** HO ELIMINATO I MOTORI DI RICERCA DAI CONTEGGI ***/

// PRESET VALUE
$giorno_inizio = '';

//  Giorni trascorsi
$result = sql_query("SELECT data FROM $option[prefix]_daily ORDER BY data ASC LIMIT 0,1");
if (mysql_affected_rows() > 0) {
	while ($row = mysql_fetch_row($result)) {
		$giorno_inizio = $row[0]; // escludi oggi e primo giorno // by -=JackNight=- (Lucas Meier)
		list($anno_y, $mese_y, $giorno_y) = explode('-', $row[0]);
		$started = str_replace(Array(
			'%mount%',
			'%day%',
			'%year%'
		), Array(
			formatmount($mese_y),
			$giorno_y,
			$anno_y
		), $varie['date_format']);
	}
	list($anno_t, $mese_t, $giorno_t) = explode('-', $oggi);
	$trascorsi = (mktime(0, 0, 0, $mese_t, $giorno_t, $anno_t) - mktime(0, 0, 0, $mese_y, $giorno_y, $anno_y)) / 86400;
} else {
	$trascorsi = 0;
	$started   = '-';
}



// Tempi medi di visita sito-pagine
$tocount_pages = $visits_pages = $presence_pages = 0;
$result        = sql_query("SELECT SUM(tocount),SUM(visits),SUM(no_count_visits),SUM(presence) FROM $option[prefix]_pages");
list($tocount_pages, $visits_pages, $no_count_visits_pages, $presence_pages) = mysql_fetch_row($result);
if ($tocount_pages > 0)
	$tempo_pagina = round(($presence_pages / $tocount_pages), 0);
else
	$tempo_pagina = 0;
$tempo_visita = round((($hits_totali - $total_spider_hits) / max(1, ($visite_totali - $total_spider_visits))) * $tempo_pagina, 0);
// Utenti On-Line
$tmp       = ($option['online_timeout'] > 0 ? $option['online_timeout'] * 60 : $tempo_pagina * 1.3);
$date      = (time() - ($option['timezone'] * 3600) - ($tmp));
$result_ol = sql_query("SELECT data FROM $option[prefix]_cache WHERE data>$date AND notbrowser=0");
$online    = mysql_num_rows($result_ol);



$result = sql_query("SELECT SUM(visits) FROM $option[prefix]_referer");
list($total_referer) = mysql_fetch_row($result);
if (!$total_referer)
	$total_referer = 0;

$result = sql_query("SELECT SUM(visits) FROM $option[prefix]_query");
list($total_engine) = mysql_fetch_row($result);
if (!$total_engine)
	$total_engine = 0;



// GIORNO "MIGLIORE" (pagine visitate)
$result = sql_query("SELECT data, hits FROM $option[prefix]_daily ORDER BY hits DESC LIMIT 1");
if (mysql_num_rows($result) > 0) {
	list($max_hits_data, $max_hits) = mysql_fetch_row($result);
	$data          = explode('-', $max_hits_data);
	$max_hits_data = str_replace(Array(
		'%mount%',
		'%day%',
		'%year%'
	), Array(
		formatmount($data[1]),
		$data[2],
		$data[0]
	), $varie['date_format']);
} else
	$max_hits = $max_hits_data = '-';

// GIORNO "MIGLIORE" (utenti unici)
$result = sql_query("SELECT data,visits FROM $option[prefix]_daily ORDER BY visits DESC LIMIT 1");
if (mysql_num_rows($result) > 0) {
	list($max_visits_data, $max_visits) = mysql_fetch_row($result);
	$data            = explode('-', $max_visits_data);
	$max_visits_data = str_replace(Array(
		'%mount%',
		'%day%',
		'%year%'
	), Array(
		formatmount($data[1]),
		$data[2],
		$data[0]
	), $varie['date_format']);
} else
	$max_visits = $max_visits_data = '-';

// GIORNO "PEGGIORE" (pagine visitate)
$result = sql_query("SELECT data,hits FROM $option[prefix]_daily WHERE data!=NOW() AND data!='$giorno_inizio' ORDER BY hits ASC LIMIT 1"); // escludi oggi & primo giorno // by -=JackNight=-
if (mysql_num_rows($result) > 0) {
	list($min_hits_data, $min_hits) = mysql_fetch_row($result);
	$data          = explode('-', $min_hits_data);
	$min_hits_data = str_replace(Array(
		'%mount%',
		'%day%',
		'%year%'
	), Array(
		formatmount($data[1]),
		$data[2],
		$data[0]
	), $varie['date_format']);
} else
	$min_hits = $min_hits_data = '-';


// GIORNO "PEGGIORE" ((utenti unici)
$result = sql_query("SELECT data,visits FROM $option[prefix]_daily WHERE data!=NOW() AND data!='$giorno_inizio' ORDER BY visits ASC LIMIT 1"); // escludi oggi & primo giorno // by -=JackNight=-
if (mysql_num_rows($result) > 0) {
	list($min_visits_data, $min_visits) = mysql_fetch_row($result);
	$data            = explode('-', $min_visits_data);
	$min_visits_data = str_replace(Array(
		'%mount%',
		'%day%',
		'%year%'
	), Array(
		formatmount($data[1]),
		$data[2],
		$data[0]
	), $varie['date_format']);
} else
	$min_visits = $min_visits_data = '-';

// QUERY PIU' CERCATA
$result = sql_query("SELECT data,SUM(visits) AS dummy FROM $option[prefix]_query GROUP BY data ORDER BY dummy DESC LIMIT 1");
if (mysql_num_rows($result) > 0)
	list($most_query_word, $most_query_word_volte) = mysql_fetch_row($result);
else
	$most_query_word = $most_query_word_volte = '-';

// MOTORE PIU' USATO
$result = mysql_query("SELECT engine,SUM(visits) AS dummy FROM $option[prefix]_query GROUP BY engine ORDER BY dummy DESC");
if (mysql_num_rows($result) > 0) {
	list($most_used_engine, $most_used_engine_volte) = mysql_fetch_row($result);
	$most_query_word = utf8_decode($most_query_word);
	/*** */
} else
	$most_used_engine = $most_used_engine_volte = '-';


// DATI DEL SOMMARIO

/*** Preleva dal database i visitatori di ritorno di oggi ***/ 
$result = sql_query("SELECT rets FROM $option[prefix]_daily WHERE data='$oggi' LIMIT 1");
list($returns_oggi) = mysql_fetch_row($result);
if (!isset($returns_oggi))
	$returns_oggi = 0;

$result   = sql_query("SELECT min(data) FROM $option[prefix]_daily");
$row      = mysql_fetch_row($result);
$ini_year = substr($row[0], 0, 4);
if ($this_year != $ini_year && $ini_year != '') {
	$string['hits_qa']     = str_replace('%THIS_YEAR%', $this_year, $string['hits_qa']);
	$string['visitors_qa'] = str_replace('%THIS_YEAR%', $this_year, $string['visitors_qa']);
}


list($max_ol, $time_ol) = explode('|', $option['instat_max_online']);
if ($max_ol) {
	$tmp = str_replace(Array(
		'%DATA%',
		'%ORA%'
	), Array(
		formatdate($time_ol, 3),
		formattime($time_ol, 3)
	), $string['main_max_ol']);
	$return .= "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$tmp</span></TD><TD bgcolor=$style[table_bgcolor] valign=\"top\"><span class=\"tabletextB\">$max_ol</span></TD>";
}



////////////////////
// Utenti On-Line //
////////////////////
$online = mysql_num_rows($result_ol);
$tmp    = ($option['online_timeout'] > 0 ? $option['online_timeout'] * 60 : $tempo_pagina * 1.3);
$date   = (time() - ($option['timezone'] * 3600) - ($tmp)); /*** Aggiunto 'AND notbrowser=0' per non visualizzare gli Spider come utenti online ***/ 
$result = sql_query("SELECT user_id,data,lastpage FROM $option[prefix]_cache WHERE data>$date AND notbrowser=0 ORDER BY user_id ASC");
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
	if ($option['page_title'] == 1) {
		/*** patch Francesco Mortara - fmortara@mfweb.it - 2012-01-06 */
		$row['lastpage'] = addslashes($row['lastpage']);
		/*** */
		$result_title    = sql_query("SELECT titlePage FROM $option[prefix]_pages WHERE data='$row[lastpage]' LIMIT 1");
		if (mysql_affected_rows() > 0) {
			list($title_page) = mysql_fetch_row($result_title);
			$title_page = stripslashes($title_page);
		} else
			$title_page = '';
	}
	$dottedIP = long2ip($row['user_id']);
	$return .= "\n\t<tr>" . "\n\t<td align=\"right\" bgcolor=$style[table_bgcolor] width=\"50\" nowrap><span class=\"tabletextA\">";
	if ($option['ext_whois'] == '')
		$return .= "<a href=\"javascript:whois('whois.php?IP=$dottedIP');\">$dottedIP</a>";
	else
		$return .= "<a href=\"" . str_replace('%IP%', $dottedIP, $option['ext_whois']) . "\" target=\"_BLANK\">$dottedIP</a>";
}


//////////////////
// Utente Medio //
//////////////////
$result  = sql_query("SELECT os,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE os!='Spider' AND os!='Grabber' AND os!='?' GROUP BY os ORDER BY dummy DESC LIMIT 1");
$au_os   = '-';
$icon_os = '';
if (mysql_num_rows($result) > 0) {
	list($tmp, $dummy) = mysql_fetch_row($result);
	$au_os   = $tmp;
	$icon_os = '<img src="images/os.php?q=' . str_replace(' ', '-', $au_os) . '">';
}

$result  = sql_query("SELECT bw,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE os!='Spider' AND os!='Grabber' AND bw!='?' GROUP BY bw ORDER BY dummy DESC LIMIT 2");
$au_bw   = '-';
$icon_bw = '';
if (mysql_num_rows($result) > 0) {
	list($tmp, $dummy) = mysql_fetch_row($result);
	$au_bw   = $tmp;
	$icon_bw = '<img src="images/browsers.php?q=' . str_replace(' ', '-', $au_bw) . '">';
}

$result        = sql_query("SELECT reso,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE reso!='?' GROUP BY reso ORDER BY dummy DESC LIMIT 1");
$au_resolution = '-';
if (mysql_num_rows($result) > 0) {
	list($tmp, $dummy) = mysql_fetch_row($result);
	$au_resolution = $tmp;
}

$result    = sql_query("SELECT colo,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE colo!='?' GROUP BY colo ORDER BY dummy DESC LIMIT 1");
$au_colors = '-';
if (mysql_num_rows($result) > 0) {
	list($tmp, $dummy) = mysql_fetch_row($result);
	$au_colors = $tmp . ' bit';
}

$result     = sql_query("SELECT tld,area,visits AS dummy FROM $option[prefix]_domains WHERE hits>0 AND tld!='unknown' AND tld!='lan' ORDER BY dummy DESC LIMIT 1");
$au_country = '-';
if (mysql_num_rows($result) > 0) {
	list($tmp, $au_area, $dummy) = mysql_fetch_row($result);
	include("lang/$option[language]/domains_lang.php");
	$au_area    = $domain_name['area_' . $au_area];
	$au_area    = substr($au_area, 0, -1);
	$au_country = $domain_name[$tmp] . ' (' . $au_area . ')';
}


$result  = sql_query("SELECT lang,hits AS dummy FROM $option[prefix]_langs WHERE lang!='unknown' AND hits>0 ORDER BY dummy DESC LIMIT 1");
$au_lang = '-';
if (mysql_num_rows($result) > 0) {
	list($tmp, $dummy) = mysql_fetch_row($result);
	include("lang/$option[language]/bw_lang.php");
	$au_lang = $bw_lang[$tmp];
}


/////////////////
// Server Info //
/////////////////
$mysql_ver = mysql_get_server_info();
if ($mysql_ver == '')
	$mysql_ver = $string[main_no_mysql_ver];


//------------------------------------------------------------------------------------------------------------------------------------------------------------


// TOP PAGES
$topPages = array();

$result = sql_query("SELECT data,(hits-no_count_hits) AS realhits,titlePage FROM $option[prefix]_pages WHERE hits<>0 ORDER BY realhits DESC LIMIT 0,100");
$i = 0;

while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
	if ($row[2] != "") {
		$toshow = $row[2];
	} else {
		$toshow = ">no title<";
	}
	
	$toshow = preg_replace('/%u([0-9A-F]+)/', '&#x$1;', $toshow);
	$toshow = utf8_encode($toshow);
	
	$toshow = html_entity_decode($toshow, ENT_COMPAT, 'UTF-8');
	$key = sprintf('page%02d', $i);
	$topPages[$key] = /*utf8_encode*/ ("$toshow\t" . $row[0] . "\t" . $row[1]);
	$i++;
}




// QUERY AI MOTORI
$topQuery = array();
$i=0;
$query_tot = sql_query("SELECT SUM(visits) AS realhits, data FROM $option[prefix]_query GROUP BY data ORDER BY realhits DESC LIMIT 0,100");
while ($row = mysql_fetch_row($query_tot)) {
	$toshow = $row[1] . "\t" . $row[0];
	$toshow = preg_replace('/%u([0-9A-F]+)/', '&#x$1;', $toshow);
	$toshow = utf8_encode($toshow);
	
	$toshow = html_entity_decode($toshow, ENT_COMPAT, 'UTF-8');
	$key = sprintf('query%02d', $i);
	$topQuery[$key] = $toshow;
	$i++;
}


// *** v2 ***

// DAILY
$daily = array();
//$hits = array();
//$visits = array();
$result = sql_query("SELECT data, (hits-no_count_hits) AS hits, (visits-no_count_visits) AS visits, rets FROM $option[prefix]_daily WHERE 1 ORDER BY data DESC LIMIT 30");

$i = 0;
while ($row = mysql_fetch_row($result)) {
	$day = date_parse($row[0]);
	$day = sprintf('%02dday%02d', $i, $day['day']);
	
	$daily[$day] = $row[1] ."\t". $row[2] ."\t". $row[3];
	$i++;
}



// WEEKLY (hits, visits, hits%, visits%)
$weekly = array();
$hits = array();
$visits = array();
$result = sql_query("SELECT data,hits,visits,no_count_hits,no_count_visits from $option[prefix]_daily WHERE 1");

// Lettura risultati
while ($row = mysql_fetch_assoc($result)) {
	list($anno, $mese, $giorno) = explode('-', $row['data']);
	$oggi = date('w', mktime(0, 0, 0, $mese, $giorno, $anno));
	$hits[$oggi] += ($row['hits'] - $row['no_count_hits']);
	$visits[$oggi] += ($row['visits'] - $row['no_count_visits']);
	$hits_tot += ($row['hits'] - $row['no_count_hits']);
	$accs_tot += ($row['visits'] - $row['no_count_visits']);	
}

foreach($hits as $key => $value) {
	$hits_perc[$key] = round($value * 100 / max($hits_tot, 1), 1);
}
foreach($visits as $key => $value) {
	$accs_perc[$key] = round($value * 100 / max($accs_tot, 1), 1);
}

$i=0;
foreach($visits as $dummy) {
	$dayname = substr( $varie['days'][$i], 0, 3 );
	$weekly["$i$dayname"] = "{$hits[$i]}\t{$visits[$i]}\t{$hits_perc[$i]}\t{$accs_perc[$i]}";
	$i++;
}



// MONTHLY (month, hits, visits)
$giorni = Array(null,	31,		28,		31,		30,		31,		30,		31,		31,		30,		31,		30,		31);
list($date_G, $date_i, $date_m, $date_d, $date_Y) = explode('-', date('G-i-m-d-Y'));
$anno = date('Y', mktime($date_G - $option['timezone'], $date_i, 0, $date_m, $date_d, $date_Y));
if (($anno % 4) == 0)
	$giorni[2] = 29;

for ($i = 0; $i < 13; ++$i) {
	$mese              = date('Y-m', mktime(0, 0, 0, $date_m - $i, 1, $date_Y));
	$lista_accessi[$mese] = $lista_visite[$mese] = 0;
	//$lista_mesi[$i]    = $mese;
	$result            = sql_query("SELECT hits,visits,no_count_hits,no_count_visits FROM $option[prefix]_daily WHERE data LIKE '$mese%'");
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$lista_accessi[$mese] += $row['hits'] - $row['no_count_hits'];
		$lista_visite[$mese] += $row['visits'] - $row['no_count_visits'];
	}
}

foreach ($lista_accessi as $key => $dummy) {
	$monthly[$key] = $lista_accessi[$key] . "\t" . $lista_visite[$key];
}


// LATEST VIEWED PAGES (date, title, url)
$latestPages = array();

$result = sql_query("SELECT date,data,titlePage,(hits-no_count_hits) AS realhits FROM $option[prefix]_pages WHERE hits<>0 ORDER BY date DESC LIMIT 100");

while ($row = mysql_fetch_assoc($result)) {
	$toshow = $row['titlePage'] ."\t". $row['data'];
	
	$toshow = preg_replace('/%u([0-9A-F]+)/', '&#x$1;', $toshow);
	$toshow = utf8_encode($toshow);
	
	$toshow = html_entity_decode($toshow, ENT_COMPAT, 'UTF-8');
	$latestPages[$row['date']] = $toshow;///*utf8_encode*/ "$toshow\t".$row['realhits'];
}



// DETTAGLI VISITATORI (IP, Host, Os, Bw, Date, Referer, URL, PageTitle, Country, Returns)
$visitorDetails = array();

// tutti gli ultimi visitatori
$result = sql_query("SELECT * FROM $option[prefix]_details GROUP BY visitor_id ORDER BY date DESC LIMIT 10");

$visitors_id = array();

while($row=mysql_fetch_row($result)) {
	$visitors_id[] = $row[0];
}

for($i=0,$tot=count($visitors_id);$i<$tot;++$i)
{
	$visitor_id = $visitors_id[$i];
	// dettagli di ogni ultimo visitatore (può aver visitato più pagine)
	$res = sql_query("SELECT ip,host,os,bw,date,currentPage,titlePage,tld,rets FROM $option[prefix]_details WHERE visitor_id='$visitor_id' ORDER BY date ASC LIMIT 10");
    $count = 0;
    while($row=mysql_fetch_row($res))
    {
		$row[0] = long2ip($row[0]);
    	//$row[4] = formatdate($row[4],3).' '.date("H:i:s",$row[4]);
		if ($count > 0) {			// Solo nel primo metto tutti i dati, nei successivi non occorre ripeterli tutti
		$row[0] = $row[1] = $row[2] = $row[3] = "";	// ip,host,os,bw
		$row[7] = $row[8] = "";						// tld,rets
		}
		$rebuild = Array();
		foreach($row as $toshow) { 
			$toshow = preg_replace('/%u([0-9A-F]+)/', '&#x$1;', $toshow);
			$toshow = utf8_encode($toshow);
			$toshow = html_entity_decode($toshow, ENT_COMPAT, 'UTF-8');
			$rebuild[] = $toshow;
		}
		$visitorDetails[$visitor_id][] = $rebuild;
		$count++;
	}
}

	
//------------------------------------------------------------------------------------------------------------------------------------------------------------

header('Content-Type: application/json');

$pswd = base64_decode($_GET['psw']);

if (user_login(false, $pswd) == false)
	die("Password non valida!");

$preview = $_GET['preview'];

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
	"01\t"."hits_tot" => strval($hits_totali_glob - $total_spider_hits),
	"02\t"."visitors_tot" => strval($visite_totali_glob - $total_spider_visits),
	"03\t"."hits_oggi" => strval($hits_oggi),
	"04\t"."visitors_oggi" => strval($visite_oggi),
	"05\t"."returns_oggi" => strval($returns_oggi),
	"06\t"."hits_ieri" => strval($hits_ieri),
	"07\t"."visitors_ieri" => strval($visite_ieri),
	"08\t"."hits_qm" => strval($hits_questo_mese),
	"09\t"."visitors_qm" => strval($visite_questo_mese),
	"10\t"."hits_sm" => strval($hits_scorso_mese),
	"11\t"."visitors_sm" => strval($visite_scorso_mese),
	"12\t"."hits_qa" => strval($hits_this_year),
	"13\t"."visitors_qa" => strval($visite_this_year),
	"14\t"."perm_site" => strval(formatperm($tempo_visita)),
	"15\t"."perm_page" => strval(formatperm($tempo_pagina)),
	
	"16\t"."hits_per_day" => strval(round(($hits_totali_glob - $hits_oggi - $total_spider_hits) / max(1, $trascorsi), 1)),
	"17\t"."visits_per_day" => strval(round(($visite_totali_glob - $visite_oggi - $total_spider_visits) / max(1, $trascorsi), 1)),
	"18\t"."pages_per_day" => strval(round(($hits_totali_glob - $total_spider_hits) / max(1, $visite_totali - $total_spider_visits), 1)),
	
	"19\t"."average_user" => strval("$au_bw - $au_os"),
	
	"99\t"."usr_online" => strval($online),

);


$serverInfo = array(
	// Server Info
	"01\t".'main_server_os' => php_uname() == '' ? PHP_OS : php_uname(),
	"02\t".'main_server_ws' => $_SERVER['SERVER_SOFTWARE'],
	"03\t".'main_server_php' => phpversion(),
	"04\t".'main_mysql_ver' => $mysql_ver,
	"05\t".'nomesito' => $option['nomesito'],
	"06\t".'browscap_rel' => $browscap_rel,
	"07\t".'phpstats_ver' => $option['phpstats_ver'],
	"08\t".'server_timestamp' => date('j/m/Y H:i')
);


if ($preview) {
	$data = array(
    	'Version' 		=> '2.0',
    	'Daily' 		=> $daily
    	);
} else {
	$data = array(
    	'Version' 		=> '2.0',
    	'Main' 			=> $sommario,
    	'TopPages' 		=> $topPages,
    	'TopQuery' 		=> $topQuery,
    	'Daily'		 	=> $daily,
    	'Weekly'		=> $weekly,
    	'Monthly'		=> $monthly,
    	'LatestPages' 	=> $latestPages,
    	'Details' 		=> $visitorDetails,
    	'ServerInfo' 	=> $serverInfo
    	);
}

echo json_encode($data);

// Chiusura connessione a MySQL se necessario.
if ($option['persistent_conn'] != 1)
	mysql_close();

?>
