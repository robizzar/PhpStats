<?php
// <!-- SECURITY ISSUES -->
if (!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');

// <!-- INCOMING DATA PROCESSING -->

//$mode=(isset($_GET['mode']) ? (int)$_GET['mode'] : 1);
$mode = 1;

if (!isset($modulo))
	$modulo = explode('|', $option['moduli']);

// <!-- PAGE FUNCTION -->
function daily()
{
	global $db, $option, $string, $error, $varie, $style, $mode, $modulo, $phpstats_title;
	
	// <!-- DATA ACQUISITION -->
	$curtime   = time() - $option['timezone'] * 3600;
	$startTime = strtotime('-29 days', $curtime);
	$endTime   = $curtime;
	$startDate = date('Y-m-d', $startTime);
	$endDate   = date('Y-m-d', $endTime);
	
	$data_days = Array();
	
	switch ($mode) {
		case 0:
			$what = 'data,hits,visits';
			break;
		case 1:
			$what = 'data,(hits-no_count_hits) AS hits,(visits-no_count_visits) AS visits';
			break;
		case 2:
			$what = 'data,no_count_hits AS hits,no_count_visits AS visits';
			break;
	}
	
	$res = sql_query("SELECT MIN(data) FROM $option[prefix]_daily");
	list($statsMinDate) = mysql_fetch_row($res);
	$tmp          = explode('-', $statsMinDate);
	$statsMinTime = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);
	
	
	$res      = sql_query("SELECT $what FROM $option[prefix]_daily WHERE data>='$startDate' AND data<='$endDate' ORDER BY data ASC");
	$lastTime = $startTime;
	while ($row = mysql_fetch_row($res)) {
		list($daily_data, $daily_hits, $daily_visits) = $row;
		
		while ($lastTime <= $endTime) {
			$expectedDay = date('Y-m-d', $lastTime);
			if ($daily_data === $expectedDay)
				break;
			
			$tmp    = explode('-', $expectedDay);
			$tmp[0] = (int) $tmp[0];
			$tmp[1] = (int) $tmp[1];
			$tmp[2] = (int) $tmp[2];
			
			if ($expectedDay < $statsMinDate)
				$data_days[] = Array(
					$tmp,
					null,
					null
				);
			else
				$data_days[] = Array(
					$tmp,
					0,
					0
				);
			$lastTime = strtotime('+1 day', $lastTime);
		}
		$daily_data    = explode('-', $daily_data);
		$daily_data[0] = (int) $daily_data[0];
		$daily_data[1] = (int) $daily_data[1];
		$daily_data[2] = (int) $daily_data[2];
		
		$data_days[] = Array(
			$daily_data,
			(int) $daily_hits,
			(int) $daily_visits
		);
		
		$lastTime = strtotime('+1 day', $lastTime);
	}
	
	
	
	// <!-- DATA PROCESSING -->
	
	$processed_days = Array();
	
	$maxHits = $maxVisits = 0;
	
	$lastVisits = NULL;
	
	for ($i = 0, $tot = count($data_days); $i < $tot; ++$i) {
		list($date, $hits, $visits) = $data_days[$i];
		
		if ($hits === NULL) {
			$processed_days[] = Array(
				$date,
				null,
				null,
				null
			);
			$lastVisits       = null;
			continue;
		}
		
		if ($hits > $maxHits)
			$maxHits = $hits;
		if ($visits > $maxVisits)
			$maxVisits = $visits;
		if ($lastVisits !== NULL && $lastVisits > 0)
			$visitsVariation = round(($visits - $lastVisits) / $lastVisits * 100, 1);
		else
			$visitsVariation = NULL;
		
		$lastVisits = $visits;
		
		$processed_days[] = Array(
			$date,
			$hits,
			$visits,
			$visitsVariation
		);
	}
	unset($data_days);
	
	
	
	
	// <!-- PRE-OUTPUT PROCESSING -->
	$output_days = Array();
	
	$lastMonth = NULL;
	
	for ($i = 29; $i >= 0; --$i) {
		list($date, $hits, $visits, $visitsVariation) = $processed_days[$i];
		
		$dayLabel = str_replace(Array(
			'%mount%',
			'%day%',
			'%year%'
		), Array(
			formatmount($date[1]),
			$date[2],
			$date[0]
		), $varie['date_format']);
		$monthDay = $date[2];
		
		$monthBreak = ($lastMonth !== NULL && $date[1] !== $lastMonth);
		$lastMonth  = $date[1];
		
		$isSunday = (date('w', mktime(0, 0, 0, $date[1], $date[2], $date[0])) == 0);
		
		if ($hits === NULL) {
			$output_days[] = Array(
				$dayLabel,
				$monthDay,
				$monthBreak,
				$isSunday,
				'-',
				'-',
				'-',
				0,
				0,
				0,
				0,
				'unkn'
			);
			continue;
		}
		
		if ($visitsVariation === NULL) {
			$visitsVariation = '-';
			$level           = ($visits > 0 ? '5' : 'unkn');
		} else {
			if ($visitsVariation < -15)
				$level = '1';
			else if ($visitsVariation < -5)
				$level = '2';
			else if ($visitsVariation < 5)
				$level = '3';
			else if ($visitsVariation < 15)
				$level = '4';
			else
				$level = '5';
			
			if ($visitsVariation > 0)
				$visitsVariation = '+' . $visitsVariation;
			$visitsVariation .= ' %';
		}
		
		$hitsRep        = $hits / $maxHits;
		$hitsVBarLength = round($hitsRep * 187);
		$hitsHBarLength = round($hitsRep * 250);
		
		$visitsRep        = $visits / $maxVisits;
		$visitsVBarLength = round($visitsRep * 187);
		$visitsHBarLength = round($visitsRep * 250);
		
		$output_days[] = Array(
			$dayLabel,
			$monthDay,
			$monthBreak,
			$isSunday,
			$hits,
			$visits,
			$visitsVariation,
			$hitsVBarLength,
			$hitsHBarLength,
			$visitsVBarLength,
			$visitsHBarLength,
			$level
		);
	}
	unset($processed_days);
	
	$step = $maxHits / 6;
	if ((int) round($step) === 0) {
		$Hstep1 = $Hstep2 = $Hstep3 = $Hstep4 = $Hstep5 = '';
		$Hstep6 = max($maxHits, 1);
	} else {
		$Hstep1 = round($step);
		$Hstep2 = round($step * 2);
		$Hstep3 = round($step * 3);
		$Hstep4 = round($step * 4);
		$Hstep5 = round($step * 5);
		$Hstep6 = $maxHits;
	}
	
	$step = $maxVisits / 6;
	if ((int) round($step) === 0) {
		$Vstep1 = $Vstep2 = $Vstep3 = $Vstep4 = $Vstep5 = '';
		$Vstep6 = max($maxVisits, 1);
	} else {
		$Vstep1 = round($step);
		$Vstep2 = round($step * 2);
		$Vstep3 = round($step * 3);
		$Vstep4 = round($step * 4);
		$Vstep5 = round($step * 5);
		$Vstep6 = $maxVisits;
	}
	
	
	
	// <!-- OUTPUT CREATION -->
	$return = '';
	
	// Page title (also shown in admin)
	$phpstats_title = $string['daily_title'];
	
	
	
	$return = "<span class=\"pagetitle\">$phpstats_title<br><br></span>";
/*	
	switch ($mode) {
		case 0:
			if (!$modulo[11])
				break;
			$string['hits'] .= $string['daily_string_mode_0'];
			$string['visite'] .= $string['daily_string_mode_0'];
			break;
		
		case 1:
			$string['hits'] .= $string['daily_string_mode_1'];
			$string['visite'] .= $string['daily_string_mode_1'];
			break;
		
		case 2:
			$string['hits'] .= $string['daily_string_mode_2'];
			$string['visite'] .= $string['daily_string_mode_2'];
			break;
	}
*/	
	//////////////////////////////
	// CREATING VERTICAL GRAPHS //
	//////////////////////////////
	
	$return .= "<table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\" class=\"tableborder\">" . "<tr><td valign=\"top\"><table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">" . "<tr><td height=\"30\"><span class=\"testo\">$Hstep6</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Hstep5</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Hstep4</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Hstep3</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Hstep2</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Hstep1</span></td></tr>" . '</table></td>';
	for ($i = 0, $tot = count($output_days); $i < $tot; ++$i) {
		list($dayLabel, $monthDay, $monthBreak, $isSunday, $hits, $visits, $visitsVariation, $hitsVBarLength, $hitsHBarLength, $visitsVBarLength, $visitsHBarLength, $level) = $output_days[$i];
		
		$return .= "<td height=\"200\" width=\"15\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"><img src=\"templates/$option[template]/images/style_bar_3.gif\" width=\"7\" height=\"$hitsVBarLength\"  title=\"$hits\"></td>";
	}
	
	$return .= "<td height=\"200\" width=\"1\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"></td>" . "</td></tr><tr><td></td>";
	
	for ($i = 0, $tot = count($output_days); $i < $tot; ++$i) {
		list($dayLabel, $monthDay, $monthBreak, $isSunday, $hits, $visits, $visitsVariation, $hitsVBarLength, $hitsHBarLength, $visitsVBarLength, $visitsHBarLength, $level) = $output_days[$i];
		$return .= ($isSunday ? "<td><span class=\"tabletextB\">$monthDay</span></td>" : "<td><span class=\"tabletextA\">$monthDay</span></td>");
	}
	
	$return .= '</tr>' . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"32\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"32\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits]</center></td></tr>" . 
		'</table><br>' . "<table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\" class=\"tableborder\">" . "<tr><td valign=\"top\"><table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">" . "<tr><td height=\"30\"><span class=\"testo\">$Vstep6</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Vstep5</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Vstep4</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Vstep3</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Vstep2</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">$Vstep1</span></td></tr>" . '</table></td>';
	for ($i = 0, $tot = count($output_days); $i < $tot; ++$i) {
		list($dayLabel, $monthDay, $monthBreak, $isSunday, $hits, $visits, $visitsVariation, $hitsVBarLength, $hitsHBarLength, $visitsVBarLength, $visitsHBarLength, $level) = $output_days[$i];
		
		$return .= "<td height=\"200\" width=\"15\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"><img src=\"templates/$option[template]/images/style_bar_4.gif\"\" width=\"7\" height=\"$visitsVBarLength\" title=\"$visits\"></td>";
	}
	
	$return .= "<td height=\"200\" width=\"1\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"></td>" . "</td></tr><tr><td></td>";
	
	for ($i = 0, $tot = count($output_days); $i < $tot; ++$i) {
		list($dayLabel, $monthDay, $monthBreak, $isSunday, $hits, $visits, $visitsVariation, $hitsVBarLength, $hitsHBarLength, $visitsVBarLength, $visitsHBarLength, $level) = $output_days[$i];
		$return .= ($isSunday ? "<td><span class=\"tabletextB\">$monthDay</span></td>" : "<td><span class=\"tabletextA\">$monthDay</span></td>");
	}
	
	$return .= '</tr>' . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"32\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"32\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>" . 
		'</table>';
	
	///////////////////////////////
	// CREATING HORIZONTAL GRAPH //
	///////////////////////////////
	$return .= "<br><br><table border=\"0\" width=\"90%\" $style[table_header] class=\"tableborder\">" . '<tr>' . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>Data</center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>Accessi</center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>" . '</tr>';
	
	for ($i = 0, $tot = count($output_days); $i < $tot; ++$i) {
		list($dayLabel, $monthDay, $monthBreak, $isSunday, $hits, $visits, $visitsVariation, $hitsVBarLength, $hitsHBarLength, $visitsVBarLength, $visitsHBarLength, $level) = $output_days[$i];
		
		if ($monthBreak)
			$return .= "<tr><td  bgcolor=$style[table_bgcolor] height=\"1\" colspan=\"5\"></td></tr>";
		
		$return .= "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\"><td bgcolor=$style[table_bgcolor] align=\"right\">" . "<span class=\"" . ($isSunday ? 'tabletextB' : 'tabletextA') . "\">$dayLabel</span></td>" . "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$hits</b></span><br><span class=\"tabletextA\"><b>$visits</b></span></td><td bgcolor=$style[table_bgcolor] width=\"300\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"$hitsHBarLength\" height=\"7\"></span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"$visitsHBarLength\" height=\"7\"></span></td><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$visitsVariation</span></td><td bgcolor=$style[table_bgcolor] width=\"16\"><img src=\"templates/$option[template]/images/icon_level_{$level}.gif\"></td></tr>";
	}
	$return .= "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"5\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"5\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>" . 
		"</table>";
	
/*	if ($modulo[11]) {
		// SELEZIONE MODALITA'
		$return .= "<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		if ($mode != 0)
			$return .= "<tr><td><span class=\"testo\"><a href=\"admin.php?action=daily&mode=0\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[daily_mode_0]</span></a></td></tr>";
		if ($mode != 1)
			$return .= "<tr><td><span class=\"testo\"><a href=\"admin.php?action=daily&mode=1\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[daily_mode_1]</span></a></td></tr>";
		if ($mode != 2)
			$return .= "<tr><td><span class=\"testo\"><a href=\"admin.php?action=daily&mode=2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[daily_mode_2]</span></a></td></tr>";
		$return .= "</table></center>";
	}*/
	return ($return);
}
?>
