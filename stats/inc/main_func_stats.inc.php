<?php
// SECURITY ISSUES
if (!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');
define('__RANGE_MACRO__', '-Spider,Grabber-');

require_once('inc/php7support.inc.php');

// CONNESSIONE DATABASE
function db_connect()
{
	global $option;
	$error['no_connection'] = '<b>ERRORE</b>: Non riesco a connttermi a MySQL! Controllare config.php .';
	$error['no_database']   = '<b>ERRORE</b>: Il database indicato nel config.php non esiste! Il database va creato prima di effettuare l\'installazione.';
	if ($option['persistent_conn'] == 1) {
		$db = mysql_pconnect($option['host'], $option['user_db'], $option['pass_db']);
		if ($db == false) {
			logerrors("DB-PCONN\t" . time() . "\tFAILED");
			die($error['no_connection']);
		}
	} else {
		$db = mysql_connect($option['host'], $option['user_db'], $option['pass_db']);
		if ($db == false) {
			logerrors("DB-CONN" . "|" . date("d/m/Y H:i:s") . "|FAILED");
			die($error['no_connection']);
		}
	}
	$db_sel = mysql_select_db($option[database]);
	if ($db_sel == false) {
		logerrors("DB-SELECT" . "|" . date("d/m/Y H:i:s") . "|FAILED");
		die($error['no_database']);
	}
}

// ESECUZIONE QUERY
function sql_query($query)
{
	global $option, $db, $return, $error;
	$return = mysql_query($query);
	if ($return == false) {
		$error['debug_level']       = 1;
		$error['debug_level_error'] = "<b>QUERY:</b><br>$query<br><br><b>MySql ERROR:</b><br>" . mysql_errno() . ": " . mysql_error();
		logerrors("QUERY|" . date("d/m/Y H:i:s") . "|" . $query . "|" . mysql_error());
	}
	return ($return);
}

// Traduzione caratteri speciali
function unhtmlentities($string)
{
	return strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES)));
}

function filter_func($var)
{
	$var = strtolower($var);
	if (strpos($var, $GLOBALS['filter_val']) === FALSE)
		return ($var);
}

// Filtratura variabili
function filter_urlvar($url, $var)
{
	$queryPos = strpos($url, '?');
	if ($queryPos === FALSE)
		return ($url);
	$GLOBALS['filter_val'] = $var . '=';
	$url                   = str_replace('&amp;', '&', $url);
	$queryArgs             = explode('&', substr($url, $queryPos + 1));
	$query_array           = array_filter($queryArgs, 'filter_func');
	unSet($GLOBALS['filter_val']);
	if (count($query_array) === 0)
		return substr($url, 0, $queryPos);
	return (substr($url, 0, $queryPos + 1) . implode('&', $query_array));
}

function getbrowser($arg)
{
	$my_bcap = $GLOBALS['my_bcap'];
	
	if ($my_bcap->Browser)
		return $my_bcap->Browser . ' ' . $my_bcap->Version;
	else
		return '?';
}

function getos($arg)
{
	$my_bcap = $GLOBALS['my_bcap'];
	if ($my_bcap->Platform != 'unknown')
		return $my_bcap->Platform;
	else
		return '?';
}

function getfromip($arg, $nome_os, $nome_bw)
{
	$my_bcap = $GLOBALS['my_bcap'];
	if ($my_bcap->Crawler)
		return (Array(
			'Spider',
			$my_bcap->Parent,
			TRUE
		));
	else
		return (Array(
			$nome_os,
			$nome_bw,
			FALSE
		)); // Se non è un crawler lascia i valori inalterati
}

// Restituisco nome del motore e query dall'url passato //
function getengine($reffer)
{
	
	$standardse = TRUE;
	if (strpos($reffer, '?') === FALSE) {
		if (substr_count($reffer, '/') > 5)
			$standardse = FALSE; //riconoscimento motori con url rewriting
		else
			return FALSE; //se non c'è querystring e non è url rewriting non è un motore;
	}
	if ($standardse)
		include($append_path . 'def/search_engines.dat');
	else
		$search_engines_def = Array(
			Array(
				'MetaCrawler',
				'metacrawler.com',
				'us',
				3,
				4,
				20
			),
			Array(
				'Dogpile',
				'www.dogpile.com',
				'us',
				3,
				4,
				20
			),
			Array(
				'Excite',
				'excite.com',
				'us',
				3,
				4,
				20
			)
		);
	
	$reffer          = str_replace('&amp;', '§§§', $reffer); // Il carattere &amp; può dare problemi => rimpiazzo con §§§
	$reffer          = unhtmlentities($reffer); // DECODIFICO CARATTERI SPECIALI
	$URLdata         = parse_url($reffer); // estraggo le informazioni dall'url
	$URLdata['host'] = strtolower($URLdata['host']); // Metto l'host in caratteri minuscoli
	$URLdata['path'] = strtolower($URLdata['path']); // Metto il path in caratteri minuscoli
	
	//se non contiene querystring bypass
	$nome       = ''; //Default
	$query      = ''; //Default
	$domain     = 'unknown'; //Default
	$resultPage = 0; //Default
	
	for ($i = 0, $tot = count($search_engines_def); $i < $tot; ++$i) {
		list($name, $searchstring, $forcedDomain, $queryKW, $pageKW, $recordPerPage) = $search_engines_def[$i];
		
		//decido a seconda del $searchstring su cosa effettuare la ricerca
		$whatToSearch = (strpos($searchstring, '/') === FALSE ? $URLdata['host'] : $URLdata['host'] . $URLdata['path']);
		
		if (strpos($searchstring, '*') === FALSE) { //controllo se il domain è incluso o se c'è un carattere jolly
			if (strpos($whatToSearch, $searchstring) === FALSE)
				continue; //cerco la stringa per identificare il motore di ricerca
			$tmpdomain      = $forcedDomain; //il dominio è quello forzato
			$includedDomain = TRUE;
		} else {
			$pattern = '/' . str_replace(Array(
				'/',
				'.',
				'*'
			), Array(
				'\\/',
				'\\.',
				'(.+)'
			), $searchstring) . '/';
			if (preg_match($pattern, $whatToSearch, $tmpdomain) === FALSE)
				continue; //cerco la stringa per identificare il motore di ricerca e trovo il dominio
			if ($tmpdomain[0] == '')
				continue;
			$tmpdomain      = $tmpdomain[1];
			$includedDomain = FALSE;
		}
		
		if ($standardse)
			parse_str($URLdata['query'], $queryArgs); //metto le variabili della query in un array associativo
		else
			$queryArgs = explode('/', substr($URLdata['path'], 1)); //divido le variabili e le inserisco in un array
		
		if (isSet($queryArgs[$queryKW]))
			$tmpquery = urldecode($queryArgs[$queryKW]);
		else
			continue; //il punto di query non è stato trovato
		
		if (strpos($tmpquery, 'cache:') !== FALSE)
			continue; //non considero la cache di google
		
		//a questo punto il record è quello giusto
		
		if ($pageKW !== null) { //riconoscimento della pagina
			if (!isSet($queryArgs[$pageKW]))
				$resultPage = 1; //il punto di query non è stato trovato, quindi è la prima pagina
			else {
				$recordNumber = $queryArgs[$pageKW] - 0; //registro il numero di record
				if ($recordPerPage === null)
					$recordPerPage = 10; //il default di record per pagina è 10
				$resultPage = intval($recordNumber / $recordPerPage) + 1;
			}
		}
		
		$query = str_replace('+', ' ', $tmpquery); //tolgo i + dalle keywords
		
		if (strpos($tmpdomain, '.') !== FALSE) { //è un dominio multiplo Es. google.com.ar
			$tmp = explode('.', $tmpdomain);
			for ($i = count($tmp); $i >= 0; $i--) { //in ordine inverso perchè i domini significativi sono sempre alla fine
				if (!$tmp[$i])
					continue; //esistono domini come 'google.com.'
				$tmpdomain = $tmp[$i];
				break;
			}
		}
		
		if (strpos($tmpdomain, '-') !== FALSE) { //per i domini con il tratto, es. ch-fr.altavista.com
			$tmp       = explode('-', $tmpdomain);
			$tmpdomain = $tmp[0];
		}
		
		if ($forcedDomain && ($tmpdomain == 'www' || $tmpdomain == 'com' || $tmpdomain == 'net' || $tmpdomain == 'org'))
			$domain = $forcedDomain; //se è presente il dominio forzato lo uso al posto di com,org,net
		else
			$domain = $tmpdomain;
		
		$nome = $name; //imposto il nome
		
		/*** RICONOSCIMENTO DEDICATO A GOOGLE-IMAGES CHE HA UNA QUERY FUORI STANDARD ***/
		if ($nome == 'Google-images') {
			parse_str($query, $tmp);
			$query = $tmp['/search?q'];
		}
		return Array(
			$nome,
			$domain,
			$query,
			$resultPage
		);
	}
	return FALSE;
}

// PRUNING DELLE TABELLE
function prune($table, $offset, $limit = 2)
{
	$righe  = mysql_result(sql_query("SELECT COUNT(1) AS num FROM $table"), 0, "num");
	$to_del = $righe - $offset;
	if ($to_del > 0) {
		$to_prune = sql_query("SELECT date FROM $table ORDER BY date ASC LIMIT $to_del");
		while ($row = mysql_fetch_array($to_prune))
			sql_query("DELETE FROM $table WHERE date='$row[0]' LIMIT 1");
	}
}

// Pruning specifico per i dettagli
function prune_details($table, $offset)
{
	$righe     = mysql_result(sql_query("SELECT COUNT(1) AS num FROM $table"), 0, "num");
	$eccedenza = $righe - $offset;
	if ($eccedenza > 0) {
		sql_query("DELETE FROM $table ORDER BY date ASC LIMIT $eccedenza");
	}
}

function is_internal($ref)
{
	global $option, $countServerUrl, $serverUrl;
	for ($i = 0; $i < $countServerUrl; ++$i) {
		$tmp = $serverUrl[$i];
		if ($tmp === '')
			continue; //la riga è vuota
		if (strpos($ref, $tmp) !== 0)
			continue; //non trovato
		return TRUE; //trovato
	}
	return FALSE;
}

// FUNZIONE PER LOGGARE ERRORI
function logerrors($string)
{
	global $option;
	if ($option['logerrors']) {
		// Tento di impostare i permessi di scrittura
		if (!is_writable("php-stats.log"))
			chmod("php-stats.log", 0666);
		file_put_contents('php-stats.log', $string . "\n", FILE_APPEND);
	}
}


function decodeInt($binary)
{
	$tmp = unpack('Snum', $binary);
	return $tmp['num'];
}

function getDBPos($ip, $idxfile)
{
	$ipidx = fopen($idxfile, 'r') or die('ERRORE: Impossibile aprire ' . $idxfile . ' in lettura.');
	$tosearch = intval($ip / 100000) * 3;
	if ($tosearch > filesize($idxfile))
		return -1;
	fseek($ipidx, $tosearch);
	$pos = decodeInt(fread($ipidx, 2)) - 1;
	fclose($ipidx);
	return $pos;
}

function checkDBLine($fd, $ip, $char_line, $limit_char)
{
	$res   = fread($fd, $char_line);
	$start = substr($res, 0, 10) - 0;
	$end   = substr($res, 10, 10) - 0;
	if ($ip < $start)
		return 'unknown'; //l'ip non è nel db
	else if ($ip >= $start && $ip <= $end)
		return substr($res, 20, $limit_char); //trovato, restituire id
	else
		return 0; //non trovato, continua
}

function getIP($ip, $char_line, $dbfile, $idxfile, $limit_char)
{
	$pos = getDBPos($ip, $idxfile);
	if ($pos == -1)
		return 'unknown';
	$ipdb = fopen($dbfile, 'r') or die('ERRORE: Impossibile aprire ' . $dbfile . ' in lettura.');
	fseek($ipdb, $pos * $char_line);
	while (!feof($ipdb)) {
		$linedata = checkDBLine($ipdb, $ip, $char_line, $limit_char);
		if ($linedata !== 0)
			break;
	}
	fclose($ipdb);
	return $linedata;
}

// FUNCTION CLEAR CACHE
function do_clear($user_id_tmp = '', $force_del_ip = 0)
{
	global $option, $date, $append, $modulo, $secondi, $mese_oggi, $data_oggi;
	// Se specifico l'user_id e force_del_ip=0 è perchè ho un accesso a cavallo dei 2 giorni e ha priorità
	// Se force_del_ip=1 vuol dire che quell'ip è scaduto e voglio essere sicuro che sia cancellato dalla cache
	$clause = ($user_id_tmp == '' ? "WHERE data<'$secondi'" : "WHERE visitor_id='$user_id_tmp'");
	$result = sql_query("SELECT user_id,data,lastpage,visitor_id,hits,visits,reso,colo,os,bw,host,tld,lang,giorno FROM $option[prefix]_cache $clause LIMIT 1");
	if (mysql_affected_rows() < 1)
		return 1;
	
	list($Cuser_id, $data, $Clastpage, $Cvisitor_id, $Chits, $Cvisits, $Creso, $Ccolo, $Cos, $Cbw, $Chost, $Ctld, $Clang, $Cgiorno) = mysql_fetch_row($result);
	
	$spider_agent = (strpos(__RANGE_MACRO__, $Cos) == true);
	
	if (($user_id_tmp == '') || ($force_del_ip == 1)) {
		// CANCELLO IL DATO IN CACHE "SCADUTO"
		sql_query("DELETE FROM $option[prefix]_cache WHERE visitor_id='$Cvisitor_id'");
		// SCRIVO LA PAGINA DI USCITA DAL SITO
		if (($spider_agent === false) && $modulo[3]) {
			$Clastpage = mysql_real_escape_string($Clastpage);
			/*** */
			sql_query("UPDATE $option[prefix]_pages SET outs=outs+1 WHERE data='$Clastpage' $append");
		}
	}
	// DEPURO DEI DATI IMMESSI NEL DATABASE PRINCIPALE
	else
		sql_query("UPDATE $option[prefix]_cache SET hits='0',visits='0',giorno='$data_oggi' WHERE visitor_id='$Cvisitor_id' $append");
	
	// Inizio l'elaborazione
	if ($Chits == 0 && $Cvisits == 0)
		return 1; //nessuna visita o hit
	
	// SISTEMI (OS,BW,RESO,COLORS)
	if ($modulo[1]) {
		$clause = "os='$Cos' AND bw='$Cbw' AND reso='$Creso' AND colo='$Ccolo'" . (($modulo[1] == 2) ? " AND mese='$mese_oggi'" : '');
		sql_query("UPDATE $option[prefix]_systems SET visits=visits+$Cvisits,hits=hits+$Chits WHERE $clause $append");
		if (mysql_affected_rows() < 1) {
			$insert = "VALUES('$Cos','$Cbw','$Creso','$Ccolo','$Chits','$Cvisits','" . (($modulo[1] == 2) ? $mese_oggi : '') . "')";
			sql_query("INSERT DELAYED INTO $option[prefix]_systems $insert");
		}
	}
	
	// LINGUE (impostate dal browser)
	if ($spider_agent === false && $modulo[2]) {
		sql_query("UPDATE $option[prefix]_langs SET hits=hits+$Chits,visits=visits+$Cvisits WHERE lang='$Clang' $append");
		if (mysql_affected_rows() < 1)
			sql_query("UPDATE $option[prefix]_langs SET hits=hits+$Chits,visits=visits+$Cvisits WHERE lang='unknown' $append");
	}
	
	// ACCESSI GIORNALIERI
	if ($modulo[6]) {
		sql_query("UPDATE $option[prefix]_daily SET hits=hits+$Chits,visits=visits+$Cvisits" . (($spider_agent) ? ",no_count_hits=no_count_hits+$Chits,no_count_visits=no_count_visits+$Cvisits" : '') . " WHERE data='$Cgiorno' " . $append);
		if (mysql_affected_rows() < 1)
			sql_query("INSERT DELAYED INTO $option[prefix]_daily VALUES('$Cgiorno','$Chits','$Cvisits'" . (($spider_agent) ? ",'$Chits','$Cvisits'" : ",'0','0'") . ",'0')");
	}
	
	$ip_number = $Cuser_id;
	
	// COUNTRY
	if (($spider_agent === false) && $modulo[7]) {
		sql_query("UPDATE $option[prefix]_domains SET visits=visits+$Cvisits,hits=hits+$Chits WHERE tld='$Ctld' $append");
		if (mysql_affected_rows() < 1)
			sql_query("UPDATE $option[prefix]_domains SET visits=visits+$Cvisits,hits=hits+$Chits WHERE tld='unknown' $append");
	}
	// Fine trasferimento
	return 1;
}
?>
