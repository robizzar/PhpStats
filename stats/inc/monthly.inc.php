<?php
// SECURITY ISSUES
if (!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');

//if(isset($_GET['mode'])) $mode=addslashes($_GET['mode'])-0; else $mode=1;
$mode = 1;

function monthly()
{
	global $db, $option, $string, $style, $error, $modulo, $mode, $phpstats_title;
	if (!isset($modulo))
		$modulo = explode('|', $option['moduli']);
	// Titolo pagina (riportata anche nell'admin)
	$phpstats_title = $string['monthly_title'];
	// Titolo
	$return         = "<span class=\"pagetitle\">$phpstats_title<br><br></span>";
	//
	$max            = 0;
	$giorni         = Array(
		null,
		31,
		28,
		31,
		30,
		31,
		30,
		31,
		31,
		30,
		31,
		30,
		31
	);
	list($date_G, $date_i, $date_m, $date_d, $date_Y) = explode('-', date('G-i-m-d-Y'));
	$anno = date('Y', mktime($date_G - $option['timezone'], $date_i, 0, $date_m, $date_d, $date_Y));
	if (($anno % 4) == 0)
		$giorni[2] = 29; // Anno bisestile????
/*	
	switch ($mode) {
		case 0:
			if ($modulo[11]) {
				$string['hits'] .= $string['monthly_string_mode_0'];
				$string['visite'] .= $string['monthly_string_mode_0'];
			}
			break;
		case 1:
			$string['hits'] .= $string['monthly_string_mode_1'];
			$string['visite'] .= $string['monthly_string_mode_1'];
			break;
		case 2:
			$string['hits'] .= $string['monthly_string_mode_2'];
			$string['visite'] = $string['monthly_string_mode_2'];
			break;
	}
*/	
	for ($i = 0; $i < 13; ++$i) {
		$lista_accessi[$i] = $lista_visite[$i] = 0;
		$mese              = date('Y-m', mktime(0, 0, 0, $date_m - $i, 1, $date_Y));
		$lista_mesi[$i]    = $mese;
		$result            = sql_query("SELECT hits,visits,no_count_hits,no_count_visits FROM $option[prefix]_daily WHERE data LIKE '$mese%'");
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			switch ($mode) {
				case 0:
					$lista_accessi[$i] += $row['hits'];
					$lista_visite[$i] += $row['visits'];
					break;
				case 1:
					$lista_accessi[$i] += $row['hits'] - $row['no_count_hits'];
					$lista_visite[$i] += $row['visits'] - $row['no_count_visits'];
					break;
				case 2:
					$lista_accessi[$i] += $row['no_count_hits'];
					$lista_visite[$i] += $row['no_count_visits'];
					break;
			}
			if ($lista_accessi[$i] > $max)
				$max = $lista_accessi[$i];
		}
	}
	if ($max < 1)
		$max = 1; // Per evitare il warning di "Division by Zero"
	$return .= "<table border=\"0\" width=\"90%\" $style[table_header]  align=\"center\" class=\"tableborder\">" . '<tr>' . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[monthly_nome]</center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[monthly_hits]</center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>" . '</tr>';
	for ($i = 0; $i < 12; ++$i) {
		if ($lista_visite[$i + 1] > 0) {
			if ($i == 0) {
				list($mese, $giorno) = explode('-', date('m-d', mktime($date_G - $option['timezone'], $date_i, 0, $date_m, $date_d, $date_Y)));
				$variazione = round((($lista_visite[$i] * ($giorni[$mese - 0] / $giorno)) - $lista_visite[$i + 1]) / $lista_visite[$i + 1] * 100, 1);
			} else
				$variazione = round(($lista_visite[$i] - $lista_visite[$i + 1]) / $lista_visite[$i + 1] * 100, 1);
			
			if ($variazione < -15)
				$level = '1';
			elseif ($variazione < -5)
				$level = '2';
			elseif ($variazione <= 5)
				$level = '3';
			elseif ($variazione < 15)
				$level = '4';
			else
				$level = '5';
			
			if ($variazione > 0)
				$variazione = '+' . $variazione;
			$variazione .= ' %';
			if ($i == 0)
				$variazione = "($variazione)"; // Metto tra parentesi il mese corrente
		} else {
			$variazione = '-';
			$level      = ($lista_visite[$i] > 0 ? '5' : 'unkn');
		}
		$img = "templates/$option[template]/images/icon_level_{$level}.gif";
		$return .= "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\"><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">" . formatdate($lista_mesi[$i], 1) . "</span></td><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$lista_accessi[$i]</b></span><br><span class=\"tabletextA\"><b>$lista_visite[$i]</b></span></td><td bgcolor=$style[table_bgcolor] width=\"300\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"" . ($lista_accessi[$i] / $max * 300) . "\" height=\"7\"></span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"" . ($lista_visite[$i] / $max * 300) . "\" height=\"7\"></span></td><td bgcolor=$style[table_bgcolor] align=\"center\"><span class=\"tabletextA\">" . $variazione . "</span></td><td bgcolor=$style[table_bgcolor] width=\"16\"><img src=\"$img\"></td></tr>";
	}
	$return .= "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"5\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"5\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>" . '</table>';
	/*if($modulo[11]){
	// SELEZIONE MODALITA'
	$return.="<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	if($mode!=0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=monthly&mode=0\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[monthly_mode_0]</span></a></td></tr>";
	if($mode!=1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=monthly&mode=1\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[monthly_mode_1]</span></a></td></tr>";
	if($mode!=2) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=monthly&mode=2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[monthly_mode_2]</span></a></td></tr>";
	$return.="</table></center>";
	}*/
	return ($return);
}
?>
