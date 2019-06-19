<?php
// SECURITY ISSUES
if (!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');

if (isset($_GET['mounth1']))
	$mounth1 = addslashes($_GET['mounth1']);
else
	$mounth1 = 'last';
if (isset($_GET['year1']))
	$year1 = addslashes($_GET['year1']);
else
	$year1 = 'last';
if (isset($_GET['mounth2']))
	$mounth2 = addslashes($_GET['mounth2']);
else
	$mounth2 = 'last';
if (isset($_GET['year2']))
	$year2 = addslashes($_GET['year2']);
else
	$year2 = 'last';
if (isset($_GET['view_graphics']))
	$view_graphics = addslashes($_GET['view_graphics']);
else
	$view_graphics = 0;

/*if (isset($_GET['mode']))
	$mode = addslashes($_GET['mode']) - 0;
else*/
	$mode = 1;

function compare()
{
	global $db, $option, $string, $error, $varie, $style, $mounth1, $year1, $mounth2, $year2, $view_graphics, $modulo, $mode, $phpstats_title;
	if (!isset($modulo))
		$modulo = explode('|', $option['moduli']);
	// Titolo pagina (riportata anche nell'admin)
	$phpstats_title   = $string['compare_title'];
	//
	$totali_accessi_1 = $totali_accessi_2 = $totali_visite_1 = $totali_visite_2 = 0;
	$result           = sql_query("SELECT data FROM $option[prefix]_daily ORDER BY data ASC LIMIT 0,1");
	list($mounth_now, $year_now) = explode('-', date('n-Y'));
	if (mysql_affected_rows() > 0)
		while ($row = mysql_fetch_row($result))
			list($anno_y, $mese_y, $giorno_y) = explode('-', $row[0]);
	else
		$anno_y = $year_now;
	$mounth1_sel = ($mounth1 == 'last' ? $mounth_now : $mounth1);
	$year1_sel   = ($year1 == 'last' ? $year_now : $year1);
	$mounth2_sel = ($mounth2 == 'last' ? $mounth_now - 1 : $mounth2);
	$year2_sel   = ($year2 == 'last' ? $year_now : $year2);
	if ($mounth2_sel == 0) {
		$mount2_sel = 12;
		$year2_sel--;
	}
	
	$return = "<br><br><center><form action='./admin.php' method='GET' name=form1><span class='testo'>$string[compare_comp]</span>" . '<input name="action" type="hidden" value="compare">' . '<SELECT name=mounth1>';
	for ($i = 1; $i <= 12; ++$i)
		$return .= "<OPTION value='$i'" . ($mounth1_sel == $i ? ' SELECTED' : '') . '>' . $varie['mounts'][$i - 1] . '</OPTION>';
	$return .= "</SELECT><SELECT name=year1>";
	for ($i = $anno_y; $i <= $year_now; ++$i)
		$return .= "<OPTION value='$i'" . ($year1_sel == $i ? ' SELECTED' : '') . ">$i</OPTION>";
	$return .= "</SELECT><span class='testo'> $string[compare_with] </span><SELECT name=mounth2>";
	for ($i = 1; $i <= 12; ++$i)
		$return .= "<OPTION value='$i'" . ($mounth2_sel == $i ? ' SELECTED' : '') . '>' . $varie['mounts'][$i - 1] . '</OPTION>';
	$return .= '</SELECT><SELECT name=year2>';
	for ($i = $anno_y; $i <= $year_now; ++$i)
		$return .= "<OPTION value='$i'" . ($year2_sel == $i ? ' SELECTED' : '') . ">$i</OPTION>";
	$return .= "</SELECT><input name=\"view_graphics\" type=\"hidden\" value=\"1\">" . "&nbsp;<input type=\"submit\" value=\"$string[go]\">" . "</FORM>" . "</center>";
/*	
	switch ($mode) {
		case 0:
			if ($modulo[11]) {
				$string['compare_access'] .= $string['compare_string_mode_0'];
				$string['compare_visits'] .= $string['compare_string_mode_0'];
			}
			break;
		case 1:
			$string['compare_access'] .= $string['compare_string_mode_1'];
			$string['compare_visits'] .= $string['compare_string_mode_1'];
			break;
		case 2:
			$string['compare_access'] .= $string['compare_string_mode_2'];
			$string['compare_visits'] = $string['compare_string_mode_2'];
			break;
	}
*/	
	if ($view_graphics == 1) {
		$total_visits = $max_accessi = $max_visite = $day = 0;
		$return .= "<span class=\"pagetitle\">$string[compare_access]<br><br></span>";
		for ($i = 0; $i <= 31; ++$i) {
			$lista_accessi_1[$i] = $lista_visite_1[$i] = $lista_accessi_2[$i] = $lista_visite_2[$i] = 0;
			// $giorno=date("Y-m-d",mktime(date("G")-$option['timezone'],date("i"),0,date("m"),date("d")-$i,date("Y")));
			if (checkdate($mounth1, $i, $year1)) {
				$giorno             = date('Y-m-d', mktime(0, 0, 0, $mounth1, $i, $year1));
				$lista_giorni_1[$i] = $giorno;
				$result             = sql_query("SELECT hits,visits,no_count_hits,no_count_visits FROM $option[prefix]_daily WHERE data='$giorno'");
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					switch ($mode) {
						case 0:
							$lista_accessi_1[$i] = $row['hits'];
							$lista_visite_1[$i]  = $row['visits'];
							if ($row['hits'] > $max_accessi)
								$max_accessi = $row['hits'];
							if ($row['visits'] > $max_visite)
								$max_visite = $row['visits'];
							break;
						case 1:
							$lista_accessi_1[$i] = $row['hits'] - $row['no_count_hits'];
							$lista_visite_1[$i]  = $row['visits'] - $row['no_count_visits'];
							if (($row['hits'] - $row['no_count_hits']) > $max_accessi)
								$max_accessi = ($row['hits'] - $row['no_count_hits']);
							if (($row['visits'] - $row['no_count_visits']) > $max_visite)
								$max_visite = ($row['visits'] - $row['no_count_visits']);
							break;
						case 2:
							$lista_accessi_1[$i] = $row['no_count_hits'];
							$lista_visite_1[$i]  = $row['no_count_visits'];
							if ($row['no_count_hits'] > $max_accessi)
								$max_accessi = $row['no_count_hits'];
							if ($row['no_count_visits'] > $max_visite)
								$max_visite = $row['no_count_visits'];
							break;
					}
				}
			}
			if (checkdate($mounth2, $i, $year2)) {
				$giorno             = date('Y-m-d', mktime(0, 0, 0, $mounth2, $i, $year2));
				$lista_giorni_2[$i] = $giorno;
				$result             = sql_query("select hits,visits,no_count_hits,no_count_visits from $option[prefix]_daily where data='$giorno'");
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
					switch ($mode) {
						case 0:
							$lista_accessi_2[$i] = $row['hits'];
							$lista_visite_2[$i]  = $row['visits'];
							if ($row['hits'] > $max_accessi)
								$max_accessi = $row['hits'];
							if ($row['visits'] > $max_visite)
								$max_visite = $row['visits'];
							break;
						case 1:
							$lista_accessi_2[$i] = $row['hits'] - $row['no_count_hits'];
							$lista_visite_2[$i]  = $row['visits'] - $row['no_count_visits'];
							if (($row['hits'] - $row['no_count_hits']) > $max_accessi)
								$max_accessi = ($row['hits'] - $row['no_count_hits']);
							if (($row['visits'] - $row['no_count_visits']) > $max_visite)
								$max_visite = ($row['visits'] - $row['no_count_visits']);
							break;
						case 2:
							$lista_accessi_2[$i] = $row['no_count_hits'];
							$lista_visite_2[$i]  = $row['no_count_visits'];
							if ($row['no_count_hits'] > $max_accessi)
								$max_accessi = $row['no_count_hits'];
							if ($row['no_count_visits'] > $max_visite)
								$max_visite = $row['no_count_visits'];
							break;
					}
				}
			}
		}
		////////////////////////////////////
		// GENERO IL GRAFICO IN VERTICALE //
		////////////////////////////////////
		
		/////////////////////
		// PAGINE VISITATE //
		/////////////////////
		$max_v = ($max_accessi < 30 ? 30 : $max_accessi);
		$tmp   = round($max_v / 6, 0);
		$max_v = $tmp * 6;
		$return .= "<table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\">" . "<tr><td><table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 5) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 4) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 3) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 2) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 1) . "</span></td></tr>" . "</table></td>";
		for ($i = 1; $i <= 31; ++$i)
			$return .= "<td height=\"200\" width=\"15\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"><img src=\"templates/$option[template]/images/style_bar_3.gif\"\" width=\"5\" height=\"" . ($lista_accessi_1[$i] / $max_v * 187) . "\"  title=\"$lista_accessi_1[$i]\"><img src=\"templates/$option[template]/images/style_bar_4.gif\"\" width=\"5\" height=\"" . ($lista_accessi_2[$i] / $max_v * 187) . "\" title=\"$lista_accessi_2[$i]\"></td>";
		$return .= "<td height=\"200\" width=\"1\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"></td>" . "</td></tr><tr><td><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"></td>";
		for ($i = 1; $i <= 31; ++$i) {
			list($giorno, $weekday) = explode('-', date('d-w', mktime(0, 0, 0, $mounth1, $i, $year1)));
			if (checkdate($mounth1, $i, $year1))
				$return .= "<td><span class=\"" . ($weekday == 0 ? 'tabletextB' : 'tabletextA') . "\">$giorno</span></td>";
		}
		$return .= "</tr><td><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"></td>";
		for ($i = 1; $i <= 31; ++$i) {
			list($giorno, $weekday) = explode('-', date('d-w', mktime(0, 0, 0, $mounth2, $i, $year2)));
			if (checkdate($mounth2, $i, $year2))
				$return .= "<td><span class=\"" . ($weekday == 0 ? 'tabletextB' : 'tabletextA') . "\">$giorno</span></td>";
		}
		$return .= "</tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"32\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"32\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> " . $varie['mounts'][$mounth1 - 1] . " $year1 <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> " . $varie['mounts'][$mounth2 - 1] . " $year2 </span></center></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"32\" nowrap></td></tr>" . "</table>";
		////////////////
		// VISITATORI //
		////////////////
		$max_v = ($max_visite < 30 ? 30 : $max_visite);
		$tmp   = round($max_v / 6, 0);
		$max_v = $tmp * 6;
		$return .= "<br><span class=\"pagetitle\">$string[compare_visits]<br><br></span>" . "<table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\">" . "<tr><td><table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 5) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 4) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 3) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 2) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 1) . "</span></td></tr>" . "</table></td>";
		for ($i = 1; $i <= 31; ++$i)
			$return .= "<td height=\"200\" width=\"15\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"><img src=\"templates/$option[template]/images/style_bar_3.gif\"\" width=\"5\" height=\"" . ($lista_visite_1[$i] / $max_v * 187) . "\"  title=\"$lista_visite_1[$i]\"><img src=\"templates/$option[template]/images/style_bar_4.gif\"\" width=\"5\" height=\"" . ($lista_visite_2[$i] / $max_v * 187) . "\" title=\"$lista_visite_2[$i]\"></td>";
		$return .= "<td height=\"200\" width=\"1\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"></td>" . "</td></tr><tr><td><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"></td>";
		for ($i = 1; $i <= 31; ++$i) {
			list($giorno, $weekday) = explode('-', date('d-w', mktime(0, 0, 0, $mounth1, $i, $year1)));
			if (checkdate($mounth1, $i, $year1))
				$return .= "<td><span class=\"" . ($weekday == 0 ? 'tabletextB' : 'tabletextA') . "\">$giorno</span></td>";
		}
		$return .= "</tr><td><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"></td>";
		for ($i = 1; $i <= 31; ++$i) {
			list($giorno, $weekday) = explode('-', date('d-w', mktime(0, 0, 0, $mounth2, $i, $year2)));
			if (checkdate($mounth2, $i, $year2))
				$return .= "<td><span class=\"" . ($weekday == 0 ? 'tabletextB' : 'tabletextA') . "\">$giorno</span></td>";
		}
		$return .= "</tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"32\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"32\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> " . $varie['mounts'][$mounth1 - 1] . " $year1 <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> " . $varie['mounts'][$mounth2 - 1] . " $year2 </span></center></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"32\" nowrap></td></tr>" . "</table>";
		
		//////////////////////////////////////
		// GENERO IL GRAFICO IN ORIZZONTALE //
		//////////////////////////////////////
		$return .= "<br><span class=\"pagetitle\">$string[compare_both]<br><br></span>" . "<table border=\"0\" $style[table_header] width=\"90%\">" . "<tr>" . "<td bgcolor=$style[table_title_bgcolor] colspan=\"2\" nowrap><span class=\"tabletitle\"><center>$string[compare_hits]</center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>" . $varie['mounts_1'][$mounth1 - 1] . " $year1</center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>" . $varie['mounts_1'][$mounth2 - 1] . " $year2</center></span></td>" . "<td bgcolor=$style[table_title_bgcolor] colspan=\"2\" nowrap><span class=\"tabletitle\"><center>$string[compare_visites]</center></span></td>" . "</tr>";
		for ($i = 0; $i <= 31; ++$i) {
			$return .= "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">";
			list($giorno, $weekday) = explode('-', date('d-w', mktime(0, 0, 0, $mounth1, $i, $year1)));
			if ((checkdate($mounth1, $i, $year1)) | (checkdate($mounth2, $i, $year2))) {
				$max = max($max_accessi, 1);
				$return .= "<td bgcolor=$style[table_bgcolor] align=\"right\" width=\"170\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"" . ($lista_accessi_1[$i] / $max * 170) . "\" height=\"7\"></span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"" . ($lista_accessi_2[$i] / $max * 170) . "\" height=\"7\"></span></td><td bgcolor=$style[table_bgcolor] align=\"left\"><span class=\"tabletextA\"><b>$lista_accessi_1[$i]</b></span><br><span class=\"tabletextA\"><b>$lista_accessi_2[$i]</b></span></td>";
				if (checkdate($mounth1, $i, $year1))
					$return .= "<td bgcolor=$style[table_bgcolor] align=\"center\"><span class=\"" . ($weekday == 0 ? 'tabletextB' : 'tabletextA') . "\">$giorno</span></td>";
				else
					$return .= "<td bgcolor=$style[table_bgcolor]></td>";
			} else
				$return .= "<td bgcolor=$style[table_bgcolor]></td><td bgcolor=$style[table_bgcolor]></td><td bgcolor=$style[table_bgcolor]></td>";
			
			list($giorno, $weekday) = explode('-', date('d-w', mktime(0, 0, 0, $mounth2, $i, $year2)));
			if ((checkdate($mounth1, $i, $year1)) | (checkdate($mounth2, $i, $year2))) {
				$max = max($max_visite, 1);
				if (checkdate($mounth2, $i, $year2))
					$return .= "<td bgcolor=$style[table_bgcolor] align=\"center\"><span class=\"" . ($weekday == 0 ? 'tabletextB' : 'tabletextA') . "\">$giorno</span></td>";
				else
					$return .= "<td bgcolor=$style[table_bgcolor]></td>";
				$return .= "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$lista_visite_1[$i]</b></span><br><span class=\"tabletextA\"><b>$lista_visite_2[$i]</b></span></td><td bgcolor=$style[table_bgcolor] align=\"left\" width=\"170\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"" . ($lista_visite_1[$i] / $max * 170) . "\" height=\"7\"></span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"" . ($lista_visite_2[$i] / $max * 170) . "\" height=\"7\"></span></td>";
			} else
				$return .= "<td bgcolor=$style[table_bgcolor]></td><td bgcolor=$style[table_bgcolor]></td><td bgcolor=$style[table_bgcolor]></td>";
			
			$totali_accessi_1 += $lista_accessi_1[$i];
			$totali_accessi_2 += $lista_accessi_2[$i];
			$totali_visite_1 += $lista_visite_1[$i];
			$totali_visite_2 += $lista_visite_2[$i];
		}
		$return .= "<tr><td bgcolor=$style[table_bgcolor]></td><td bgcolor=$style[table_bgcolor] align=\"left\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> <b>$totali_accessi_1</b></span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> <b>$totali_accessi_2</b></span></td>" . "<td bgcolor=$style[table_bgcolor] align=\"center\" colspan=\"2\" nowrap><span class=\"tabletextA\"><b>$string[compare_total]</b></span></td>" . "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$totali_visite_1</b> <img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"></span><br><span class=\"tabletextA\"><b>$totali_visite_2</b> <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"></span></td><td bgcolor=$style[table_bgcolor]></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"6\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> " . $varie['mounts'][$mounth1 - 1] . " $year1 <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> " . $varie['mounts'][$mounth2 - 1] . " $year2 </span></center></td></tr>" . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>" . "</table>";
	}
/*	if ($modulo[11] && $view_graphics !== 0) {
		// SELEZIONE MODALITA'
		$return .= "<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		if ($mode != 0)
			$return .= "<tr><td><span class=\"testo\"><a href=\"admin.php?action=compare&mode=0" . ($mounth1 ? "&mounth1=$mounth1" : '') . ($year1 ? "&year1=$year1" : '') . ($mounth2 ? "&mounth2=$mounth2" : '') . ($year2 ? "&year2=$year2" : '') . ($view_graphics ? "&view_graphics=$view_graphics" : '') . "\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[compare_mode_0]</span></a></td></tr>";
		if ($mode != 1)
			$return .= "<tr><td><span class=\"testo\"><a href=\"admin.php?action=compare&mode=1" . ($mounth1 ? "&mounth1=$mounth1" : '') . ($year1 ? "&year1=$year1" : '') . ($mounth2 ? "&mounth2=$mounth2" : '') . ($year2 ? "&year2=$year2" : '') . ($view_graphics ? "&view_graphics=$view_graphics" : '') . "\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[compare_mode_1]</span></a></td></tr>";
		if ($mode != 2)
			$return .= "<tr><td><span class=\"testo\"><a href=\"admin.php?action=compare&mode=2" . ($mounth1 ? "&mounth1=$mounth1" : '') . ($year1 ? "&year1=$year1" : '') . ($mounth2 ? "&mounth2=$mounth2" : '') . ($year2 ? "&year2=$year2" : '') . ($view_graphics ? "&view_graphics=$view_graphics" : '') . "\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[compare_mode_2]</span></a></td></tr>";
		$return .= "</table></center>";
	}*/
	return ($return);
}
?>
