<?php
// <!-- SECURITY ISSUES -->
if (!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');

//-------------------------------------------------------------------------------------------------
// 		SALVATAGGIO / CARICAMENTO DELL'ULTIMA MODALITA' DI VISUALIZZAZIONE UTILIZZATA
//-------------------------------------------------------------------------------------------------
if (user_is_logged_in() && $option['keep_view_mode']) {
	foreach ($_GET as $key => $value) {
		if ($key != 'action')
			$save_cfg .= "\$_GET['$key']='$value';\r\n";
	}
	
	if ($save_cfg) {
		file_put_contents('option/' . $_GET['action'] . '.cfg', $save_cfg);
	} else if (count($_GET) == 1) {
		$settings = file_get_contents('option/' . $_GET['action'] . '.cfg');
		eval($settings);
	}
}
//-------------------------------------------------------------------------------------------------

// <!-- INCOMING DATA PROCESSING -->
$viewcalendar = (isset($_GET['viewcalendar']) ? addslashes($_GET['viewcalendar']) : 'last');
//$mode=(isset($_GET['calendarmode']) ? (int)$_GET['calendarmode'] : 3);
$mode         = 3;

if (!isset($modulo))
	$modulo = explode('|', $option['moduli']);

// <!-- PAGE FUNCTION -->
function calendar()
{
	global $db, $option, $string, $error, $varie, $style, $viewcalendar, $modulo, $mode, $phpstats_title;
	
	// <!-- DATA ACQUISITION -->
	$curtime   = time() - $option['timezone'] * 3600;
	$monthDays = Array(
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
	
	$res = sql_query("SELECT MIN(data),MAX(data) FROM $option[prefix]_daily");
	list($statsMinDate, $statsMaxDate) = mysql_fetch_row($res);
	$tmp          = explode('-', $statsMinDate);
	$minYear      = (int) $tmp[0];
	$statsMinTime = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);
	$tmp          = explode('-', $statsMaxDate);
	$maxYear      = (int) $tmp[0];
	$statsMaxTime = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);
	
	if ($viewcalendar === 'last') {
		list($date_m, $date_Y) = explode('-', date('m-Y', $curtime));
		$date_m = (int) $date_m;
		$date_Y = (int) $date_Y;
		if ($date_m === 12) {
			$monthOrder = Array(
				1,
				2,
				3,
				4,
				5,
				6,
				7,
				8,
				9,
				10,
				11,
				12
			);
			
			if ($date_Y % 4 === 0)
				$monthDays[2] = 29;
			
			$startTime = mktime(0, 0, 0, 1, 1, $date_Y);
			$endTime   = mktime(0, 0, 0, 12, 31, $date_Y);
		} else {
			$year = ($date_m == 1 ? $date_Y - 1 : $date_Y);
			if ($year % 4 === 0)
				$monthDays[2] = 29;
			
			$startTime = mktime(0, 0, 0, $date_m + 1, 1, $date_Y - 1);
			$endTime   = mktime(0, 0, 0, 12, $monthDays[$date_m], $date_Y);
			
			$monthOrder = Array();
			for ($i = $date_m + 1; $i <= 12; ++$i)
				$monthOrder[] = $i;
			for ($i = 1; $i <= $date_m; ++$i)
				$monthOrder[] = $i;
		}
	} else {
		$monthOrder = Array(
			1,
			2,
			3,
			4,
			5,
			6,
			7,
			8,
			9,
			10,
			11,
			12
		);
		
		$year = (int) $viewcalendar;
		if ($year % 4 === 0)
			$monthDays[2] = 29;
		
		$startTime = mktime(0, 0, 0, 1, 1, $year);
		$endTime   = mktime(0, 0, 0, 12, 31, $year);
	}
	$startDate = date('Y-m-d', $startTime);
	$endDate   = date('Y-m-d', $endTime);
	
	switch ($mode) {
		case 0:
			$what = 'data,hits';
			break;
		case 1:
			$what = 'data,visits';
			break;
		case 2:
			$what = 'data,(hits-no_count_hits) AS hits';
			break;
		case 3:
			$what = 'data,(visits-no_count_visits) AS visits';
			break;
		case 4:
			$what = 'data,no_count_hits AS hits,no_count_visits AS visits';
			break;
		case 5:
			$what = 'data,no_count_visits AS visits';
			break;
		case 6:
			$what = 'data,rets';
			break;
			/*** AGGIUNTA SCELTA 'VISITATORI DI RITORNO' */
	}
	
	$data_days = Array();
	
	$res      = sql_query("SELECT $what FROM $option[prefix]_daily WHERE data>='$startDate' AND data<='$endDate' ORDER BY data ASC");
	$lastTime = $startTime;
	while ($row = mysql_fetch_row($res)) {
		list($daily_data, $daily_what) = $row;
		
		while ($lastTime <= $endTime) {
			$expectedDay = date('Y-m-d', $lastTime);
			if ($daily_data === $expectedDay)
				break;
			
			$expectedDay    = explode('-', $expectedDay);
			$expectedDay[0] = (int) $expectedDay[0];
			$expectedDay[1] = (int) $expectedDay[1];
			$expectedDay[2] = (int) $expectedDay[2];
			
			if ($lastTime < $statsMinTime || $lastTime > $statsMaxTime)
				$data_days[] = Array(
					$expectedDay,
					null
				);
			else
				$data_days[] = Array(
					$expectedDay,
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
			(int) $daily_what
		);
		
		$lastTime = strtotime('+1 day', $lastTime);
	}
	
	
	
	// <!-- DATA PROCESSING -->
	$processed_days = Array();
	$monthTotal     = Array(
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null
	);
	$monthMin       = Array(
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null
	);
	$monthMax       = Array(
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null
	);
	$monthIncrement = Array(
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null
	);
	$monthAverage   = Array(
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null,
		null
	);
	$monthDayCount  = Array(
		null,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0,
		0
	);
	
	for ($i = 0, $tot = count($data_days); $i < $tot; ++$i) {
		list($date, $item) = $data_days[$i];
		
		$month = $date[1];
		
		if ($item !== NULL) {
			++$monthDayCount[$month];
			$monthTotal[$month] += $item;
			if ($monthMin[$month] === null || $item < $monthMin[$month])
				$monthMin[$month] = $item;
			if ($monthMax[$month] === null || $item > $monthMax[$month])
				$monthMax[$month] = $item;
		}
	}
	
	$output_days = Array(
		null,
		Array(),
		Array(),
		Array(),
		Array(),
		Array(),
		Array(),
		Array(),
		Array(),
		Array(),
		Array(),
		Array(),
		Array()
	);
	
	for ($i = 0, $tot = count($data_days); $i < $tot; ++$i) {
		list($date, $item) = $data_days[$i];
		
		$isSunday = (date('w', mktime(0, 0, 0, $date[1], $date[2], $date[0])) == 0);
		
		$output_days[$date[1]][$date[2]] = Array(
			($item === NULL ? '-' : $item),
			$isSunday
		);
	}
	
	for ($i = 1; $i <= 12; ++$i) {
		if ($monthDayCount[$i] === 0)
			$monthTotal[$i] = $monthMin[$i] = $monthMax[$i] = $monthAverage[$i] = NULL;
		else {
			$day = $monthDayCount[$i];
			
			if (date('j') == $day && date('n') == $i && date('Y') == $year) {
				$days             = $day - 1;
				$tot              = $monthTotal[$i] - $output_days[$i][$day][0];
				$monthAverage[$i] = round($tot / $days, 1);
			} else {
				$monthAverage[$i] = round($monthTotal[$i] / $day, 1);
			}
		}
	}
	
	$lastTotal = NULL;
	for ($i = 0; $i < 12; ++$i) {
		$month = $monthOrder[$i];
		
		if ($lastTotal !== NULL && $monthTotal[$month] !== null) {
			$monthIncrement[$month] = round(($monthAverage[$month] - $lastTotal) / $lastTotal * 100, 1);
		}
		
		$lastTotal = $monthAverage[$month];
	}
	unset($monthDayCount);
	//no other processing needed
	
	
	
	// <!-- PRE-OUTPUT PROCESSING -->
	
	
	
	
	for ($i = 1; $i <= 12; ++$i) {
		if ($monthTotal[$i] === NULL)
			$monthTotal[$i] = '-';
		if ($monthMin[$i] === NULL)
			$monthMin[$i] = '-';
		if ($monthMax[$i] === NULL)
			$monthMax[$i] = '-';
		if ($monthIncrement[$i] === NULL)
			$monthIncrement[$i] = '-';
		else {
			if ($monthIncrement[$i] > 0)
				$monthIncrement[$i] = '+' . $monthIncrement[$i];
			$monthIncrement[$i] = $monthIncrement[$i] . '%';
		}
		if ($monthAverage[$i] === NULL)
			$monthAverage[$i] = '-';
	}
	unset($data_days);
	
	
	
	
	// <!-- OUTPUT CREATION -->
	$return = '';
	
	// Page Title (also shown in admin)
	$phpstats_title = $string['calendar_title'];
	
	$return = "<span class=\"pagetitle\">$phpstats_title<br><br></span>";
	
	$return .= "<table border=\"0\" $style[table_header] width=\"95%\" align=\"center\" ><tr>" . "<td bgcolor=$style[table_title_bgcolor] nowrap  class=\"tabletitle\"></td>";
	for ($i = 0; $i < 12; ++$i)
		$return .= "<td bgcolor=$style[table_title_bgcolor] nowrap class=\"tabletitle\"><center>" . formatmount($monthOrder[$i], 1) . "</center></td>";
	$return .= '</tr>';
	
	for ($d = 1; $d <= 31; ++$d) {
		$return .= "<tr><td bgcolor=$style[table_title_bgcolor] width=\"10\" nowrap class=\"tabletitle\">$d</td>";
		for ($o = 0; $o <= 11; ++$o) {
			$m = $monthOrder[$o];
			if ($d <= $monthDays[$m] && $d <= count($output_days[$m])) {
				list($item, $isSunday) = $output_days[$m][$d];
				$return .= "<td width='8%' align='right' class='text' bgcolor=$style[table_bgcolor] >" . '<span class="' . ($isSunday ? 'tabletextB' : 'tabletextA') . "\">$item</span>" . '</td>';
			} else
				$return .= "<td align='right' class='text' bgcolor='$style[table_bgcolor]'>-</td>";
		}
		$return .= '</tr>';
	}
	$return .= '</tr>' . "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"13\" height=\"1\" nowrap></td></tr>" . // Separatore
		"<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . // TOTALI
		"<td bgcolor=$style[table_bgcolor] width=\"10\" nowrap><span class=\"tabletextA\">" . $string['calendar_day_total'] . "</span></td>";
	for ($i = 0; $i < 12; ++$i)
		$return .= "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>{$monthTotal[$monthOrder[$i]]}</b></span></td>";
	
	$return .= '</tr>' . "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"13\" height=\"1\" nowrap></td></tr>" . // Separatore
		"<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . // TOTALI
		"<td bgcolor=$style[table_bgcolor] width=\"10\" nowrap><span class=\"tabletextA\">" . 'Inc.' . "</span></td>";
	for ($i = 0; $i < 12; ++$i)
		$return .= "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">{$monthIncrement[$monthOrder[$i]]}</span></td>";
	
	$return .= '</tr>' . "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . // MINIMI
		"<td bgcolor=$style[table_bgcolor] width=\"10\" nowrap><span class=\"tabletextA\">" . $string['calendar_day_worst'] . "</span></td>";
	for ($i = 0; $i < 12; ++$i)
		$return .= "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">{$monthMin[$monthOrder[$i]]}</span></td>";
	
	$return .= '</tr>' . "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . // MASSIMI
		"<td bgcolor=$style[table_bgcolor] width=\"10\" nowrap><span class=\"tabletextA\">" . $string['calendar_day_best'] . "</span></td>";
	for ($i = 0; $i < 12; ++$i)
		$return .= "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">{$monthMax[$monthOrder[$i]]}</span></td>";
	
	$return .= '</tr>' . "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . // MEDIA
		"<td bgcolor=$style[table_bgcolor]  width=\"10\" nowrap><span class=\"tabletextA\">" . $string['calendar_day_average'] . "</span></td>";
	for ($i = 0; $i < 12; ++$i)
		$return .= "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">{$monthAverage[$monthOrder[$i]]}</span></td>";
	
	$return .= '</tr>' . "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"13\" height=\"1\" nowrap></td></tr>" . // Separatore
		'</table>';
	
	// FORM CON LA SELEZIONE DELLE OPZIONI CALENDARIO
	$return .= "<br><br><center><form action='./admin.php' method='GET' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>" . '<input name="action" type="hidden" value="calendar">' . '<SELECT name=calendarmode>' . "<OPTION value='0'" . ($mode === 0 ? ' SELECTED' : '') . ">$string[hits]</OPTION>" . "<OPTION value='1'" . ($mode === 1 ? ' SELECTED' : '') . ">$string[visite]</OPTION>";
	/*if($modulo[11]){
	$return.=
	"<OPTION value='2'".($mode===2 ? ' SELECTED' : '').">$string[hits_no_spider]</OPTION>".
	"<OPTION value='3'".($mode===3 ? ' SELECTED' : '').">$string[visite_no_spider]</OPTION>".
	"<OPTION value='4'".($mode===4 ? ' SELECTED' : '').">$string[hits_spider]</OPTION>".
	"<OPTION value='5'".($mode===5 ? ' SELECTED' : '').">$string[visite_spider]</OPTION>";
	}*/
	/*** AGGIUNTA SCELTA 'VISITATORI DI RITORNO' ***/
	$return .= "<OPTION value='6'" . ($mode === 6 ? ' SELECTED' : '') . ">Visitatori di ritorno</OPTION>";
	$return .= '</SELECT>' . '<SELECT name=viewcalendar>';
	for ($i = $minYear; $i <= $maxYear; ++$i)
		$return .= "<OPTION value='$i'" . ($viewcalendar == $i ? ' SELECTED' : '') . ">$i</OPTION>";
	$return .= "<OPTION value='last'" . ($viewcalendar == 'last' ? ' SELECTED' : '') . ">$string[calendar_last]</OPTION>" . '</SELECT>' . "<input type=\"submit\" value=\"$string[go]\">" . '</FORM>' . '</center>';
	return ($return);
}
?>
