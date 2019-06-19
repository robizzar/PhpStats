<?php
// SECURITY ISSUES
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

$date = time() - $option['timezone'] * 3600;
list($mese, $anno) = explode('-', date('m-Y', $date));

if (isset($_POST['sel_mese']))
	$sel_mese = addslashes($_POST['sel_mese']);
else {
	if (isset($_GET['sel_mese']))
		$sel_mese = addslashes($_GET['sel_mese']);
	else
		$sel_mese = $mese;
}
if (isset($_POST['sel_anno']))
	$sel_anno = addslashes($_POST['sel_anno']);
else {
	if (isset($_GET['sel_anno']))
		$sel_anno = addslashes($_GET['sel_anno']);
	$sel_anno = $anno;
}
if (isset($_GET['mese']))
	list($sel_anno, $sel_mese) = explode('-', addslashes($_GET['mese']));
if (isset($_GET['mode']))
	$mode = addslashes($_GET['mode']);
else
	$mode = 1;
//   if(isset($_GET['mode_2'])) $mode_2=addslashes($_GET['mode_2'])-0; else $mode_2=1;
$mode_2 = 1;

function weekly()
{
	global $db, $option, $string, $error, $varie, $style, $mode, $mode_2, $modulo, $mese, $anno, $sel_anno, $sel_mese, $phpstats_title;
	if (!isset($modulo))
		$modulo = explode('|', $option['moduli']);
	// Titolo pagina (riportata anche nell'admin)
	if ($mode == 0)
		$phpstats_title = str_replace(Array(
			'%MESE%',
			'%ANNO%'
		), Array(
			formatmount($sel_mese),
			$sel_anno
		), $string['weekly_title_2']);
	else
		$phpstats_title = $string['weekly_title'];
	//
	$accessi  = Array(
		0,
		0,
		0,
		0,
		0,
		0,
		0
	);
	$hits     = Array(
		0,
		0,
		0,
		0,
		0,
		0,
		0
	);
	$hits_tot = $accs_tot = 0;
	$hits_max = $accs_max = 1;
/*	
	switch ($mode_2) {
		case 0:
			if ($modulo[11]) {
				$string['hits'] .= $string['weekly_string_mode_0'];
				$string['visite'] .= $string['weekly_string_mode_0'];
			}
			break;
		case 1:
			$string['hits'] .= $string['weekly_string_mode_1'];
			$string['visite'] .= $string['weekly_string_mode_1'];
			break;
		case 2:
			$string['hits'] .= $string['weekly_string_mode_2'];
			$string['visite'] = $string['weekly_string_mode_2'];
			break;
	}
*/	
	// Costruzione query
	if (strlen("$sel_mese") < 2)
		$sel_mese = '0' . $sel_mese;
	$clause = ($mode == 1 ? '' : " WHERE data LIKE '$sel_anno-$sel_mese-%'");
	$result = sql_query("SELECT data,hits,visits,no_count_hits,no_count_visits from $option[prefix]_daily" . $clause);
	
	// Lettura risultati
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		list($anno, $mese, $giorno) = explode('-', $row['data']);
		$oggi = date('w', mktime(0, 0, 0, $mese, $giorno, $anno));
		switch ($mode_2) {
			case 0:
				$hits[$oggi] += $row['hits'];
				$accessi[$oggi] += $row['visits'];
				$hits_tot += $row['hits'];
				$accs_tot += $row['visits'];
				break;
			case 1:
				$hits[$oggi] += ($row['hits'] - $row['no_count_hits']);
				$accessi[$oggi] += ($row['visits'] - $row['no_count_visits']);
				$hits_tot += ($row['hits'] - $row['no_count_hits']);
				$accs_tot += ($row['visits'] - $row['no_count_visits']);
				break;
			case 2:
				$hits[$oggi] += $row['no_count_hits'];
				$accessi[$oggi] += $row['no_count_visits'];
				$hits_tot += $row['no_count_hits'];
				$accs_tot += $row['no_count_visits'];
				break;
		}
	}
	$hits_max = max($hits);
	$accs_max = max($accessi);
	
	$return = '';
	// Titolo
	$return .= "<span class=\"pagetitle\">$phpstats_title<br><br></span>";
	//
	$return .= "<table border=\"0\" width=\"90%\" $style[table_header] align=\"center\" class=\"tableborder\">" . "<tr><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[weekly_day]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[weekly_hits]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td></tr>";
	for ($i = 0; $i < 7; ++$i) {
		$oggi = $varie['days'][$i];
		$return .= "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . "<td bgcolor=$style[table_bgcolor] align=\"right\" width=\"150\"><span class=\"tabletextA\">$oggi</span></td>" . "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$hits[$i]</b></span><br><span class=\"tabletextA\"><b>$accessi[$i]</b></span></td>" . "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"" . ($hits[$i] / max($hits_max, 1) * 250) . "\" height=\"7\"> (" . round($hits[$i] * 100 / max($hits_tot, 1), 1) . "%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"" . ($accessi[$i] / max($hits_max, 1) * 250) . "\" height=\"7\"> (" . round($accessi[$i] * 100 / max($accs_tot, 1), 1) . "%)</span></td></tr>";
	}
	$return .= "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"3\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>" . 
	//"<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>".
		'</table>';
	
	// BOX SCELTA MESE
	$return .= "<br><br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	if ($mode == 0) {
		// SELEZIONE MESE DA VISUALIZZARE
		$return .= "<tr><td colspan=\"2\"><span class=\"testo\">" . "<form action='./admin.php?action=weekly" . ($modulo[11] ? "&mode_2=$mode_2" : '') . "' method='POST' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>" . '<SELECT name=sel_mese>';
		for ($i = 1; $i < 13; ++$i)
			$return .= "<OPTION value='$i'" . ($sel_mese == $i ? ' SELECTED' : '') . '>' . $varie['mounts'][$i - 1] . '</OPTION>';
		$return .= '</SELECT>' . '<SELECT name=sel_anno>';
		$result = sql_query("SELECT min(data) FROM $option[prefix]_daily");
		$row    = mysql_fetch_row($result);
		$ini_y  = substr($row[0], 0, 4);
		if ($ini_y == '')
			$ini_y = $anno;
		for ($i = $ini_y; $i <= $anno; ++$i)
			$return .= "<OPTION value='$i'" . ($sel_anno == $i ? ' SELECTED' : '') . ">$i</OPTION>";
		$return .= '</SELECT>' . "<input type=\"submit\" value=\"$string[go]\">" . '</FORM>' . '</td></tr>' . "<tr><td><span class=\"testo\"><a href=\"admin.php?action=weekly&mode=1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[weekly_vis_glob]</a></span></td></tr>";
	} else
		$return .= "<tr><td><span class=\"testo\"><a href=\"admin.php?action=weekly&mode=0\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[weekly_vis_mens]</a></span></td></tr>";
	$return .= '</table></center>';
	/*
	if($modulo[11]){
	// SELEZIONE MODALITA'
	$return.="<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	if($mode_2!=0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=weekly".($sel_mese ? "&sel_mese=$sel_mese" : '').($sel_anno ? "&sel_anno=$sel_anno" : '').($mode ? "&mode=$mode" : '')."&mode_2=0\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[weekly_mode_0]</span></a></td></tr>";
	if($mode_2!=1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=weekly".($sel_mese ? "&sel_mese=$sel_mese" : '').($sel_anno ? "&sel_anno=$sel_anno" : '').($mode ? "&mode=$mode" : '')."&mode_2=1\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[weekly_mode_1]</span></a></td></tr>";
	if($mode_2!=2) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=weekly".($sel_mese ? "&sel_mese=$sel_mese" : '').($sel_anno ? "&sel_anno=$sel_anno" : '').($mode ? "&mode=$mode" : '')."&mode_2=2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[weekly_mode_2]</span></a></td></tr>";
	$return.="</table></center>";
	}
	*/
	
	return ($return);
}
?>
