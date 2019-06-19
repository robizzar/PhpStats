<?php
      ////////////////////////////////////////////////////////////////////
      // mode = 0 : Mostra titolo della pagina							//		<- se omesso s'intende 0
      // mode = 1 : Mostra titolo della pagina con n° visite tra ()		//
      // mode = 2 : Mostra URL 					                       	//
      // mode = 3 : Mostra URL con n° visite tra ()						//
      // link = 1 : Il testo è anche link all'URL 						//		<- se omesso s'intende 0
      // pos = N  : Mostra la pagina in posizione N (0=prima posizione)	//		<- se omesso s'intende 0
      // num = N  : Mostra le prime N pagine							//		<- se omesso s'intende 10
      ////////////////////////////////////////////////////////////////////

define('IN_PHPSTATS',true);

if(isset($_GET['mode']))  $mode=addslashes($_GET['mode']); else $mode='0';
if(isset($_GET['link'])) $link=addslashes($_GET['link']); else $link='0';
if(isset($_GET['pos']))  $pos= addslashes($_GET['pos']); else $pos='0';
if(isset($_GET['num']))  $num= addslashes($_GET['num']); else $num='10';

require('option/php-stats-options.php');
require('inc/main_func_stats.inc.php');
require('inc/admin_func.inc.php');

if(!isset($option['prefix']))
	$option['prefix']='php_stats';

// Connessione a MySQL e selezione database
db_connect();

$result=sql_query("SELECT data,(hits-no_count_hits) AS realhits,titlePage FROM $option[prefix]_pages WHERE titlePage like '%%' AND hits<>0 ORDER BY realhits DESC LIMIT $pos,$num");
while ($row = mysql_fetch_array($result, MYSQL_NUM))
{
	$toshow='';

	switch($mode)
	{
	case '0':
		$toshow  = $row[2];											// Title
		break;
	case '1':
		$toshow  = $row[2];											// Title (Hits)
		$toshow .= ' <small><em>('.$row[1].')</em></small>';
		break;
	case '2':
		$toshow  = $row[0];											// URL
		break;
	case '3':
		$toshow  = $row[0];											// URL (Hits)
		$toshow .= ' <small><em>('.$row[1].')</em></small>';
		break;
	}

	if ($link=='1')
		$toshow = '<a href="'.$row[0].'">'.$toshow.'</a>';

	$toshow = stripslashes($toshow);
	$toshow = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", $toshow);
    $toshow = rawurlencode($toshow);
    echo "document.write(unescape('$toshow<br>'));";
}

// Chiusura connessione a MySQL se necessario.
if($option['persistent_conn']!=1)
	mysql_close();
unset($option);
?>
