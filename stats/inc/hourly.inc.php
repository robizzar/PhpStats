<?php
// SECURITY ISSUES
if (!defined('IN_PHPSTATS'))
	die("Php-Stats internal file.");

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
	else
		$sel_anno = $anno;
}
if (isset($_GET['mode']))
	$mode = addslashes($_GET['mode']);
else if ($modulo[5] < 2)
	$mode = 1;
else
	$mode = 0;

//   if(isset($_GET['mode_2'])) $mode_2=addslashes($_GET['mode_2'])-0; else $mode_2=0;
$mode_2 = 0;

function hourly()
{
	global $db, $option, $string, $error, $varie, $style, $mode, $mode_2, $modulo, $phpstats_title;
	global $mese, $anno, $sel_anno, $sel_mese;
	if (!isset($modulo))
		$modulo = explode('|', $option['moduli']);
	$return  = '';
	$max     = 0;
	$max_min = 30;
	
	// INIZIALIZZO LE VARIABILI
	for ($i = 0; $i < 24; ++$i)
		$lista_accessi[$i] = $lista_visite[$i] = 0;
	
	if (strlen("$sel_mese") < 2)
		$sel_mese = '0' . $sel_mese;
	$clause = ($mode == 0 ? "WHERE mese='$sel_anno-$sel_mese'" : '');
	
	// Titolo pagina (riportata anche nell'admin)
	if ($mode == 0)
		$phpstats_title = str_replace(Array(
			'%MESE%',
			'%ANNO%'
		), Array(
			formatmount($sel_mese),
			$sel_anno
		), $string['hourly_title_2']);
	else
		$phpstats_title = $string['hourly_title'];
	switch ($mode_2) {
		case 0:
			$phpstats_title .= $string['hourly_string_mode_0'];
			break;
		case 1:
			$phpstats_title .= $string['hourly_string_mode_1'];
			break;
		case 2:
			$phpstats_title .= $string['hourly_string_mode_2'];
			break;
	}
	//
	$return .= "<span class=\"pagetitle\">$phpstats_title</span><br><br>";
	//
	$result = sql_query("SELECT data,hits,visits,no_count_hits,no_count_visits FROM $option[prefix]_hourly $clause");
	if (mysql_num_rows($result) > 0) {
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			switch ($mode_2) {
				case 0:
					$lista_accessi[$row['data'] - 0] += $row['hits'];
					$lista_visite[$row['data'] - 0] += $row['visits'];
					break;
				case 1:
					$lista_accessi[$row['data'] - 0] += ($row['hits'] - $row['no_count_hits']);
					$lista_visite[$row['data'] - 0] += ($row['visits'] - $row['no_count_visits']);
					break;
				case 2:
					$lista_accessi[$row['data'] - 0] += $row['no_count_hits'];
					$lista_visite[$row['data'] - 0] += $row['no_count_visits'];
					break;
			}
		}
		$max = max($lista_accessi);
	}
	$max = max($max, $max_min);
	$tmp = max(round($max / 6, 0), 1);
	$max = max($tmp * 6, 1);
	$return .= "<table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\"  class=\"tableborder\">" . "<tr><td><table bgcolor=$style[table_bgcolor] border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 5) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 4) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 3) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 2) . "</span></td></tr>" . "<tr><td height=\"30\"><span class=\"testo\">" . ($tmp * 1) . "</span></td></tr>" . "</table></td>";
	for ($i = 0; $i < 24; ++$i) {
		$cur_accesso = $lista_accessi[$i];
		$cur_visita  = $lista_visite[$i];
		$return .= "<td height=\"200\" width=\"15\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"><img src=\"templates/$option[template]/images/style_bar_3.gif\"\" width=\"5\" height=\"" . ($cur_accesso / $max * 187) . "\"  title=\"$cur_accesso\"><img src=\"templates/$option[template]/images/style_bar_4.gif\"\" width=\"5\" height=\"" . ($cur_visita / $max * 187) . "\" title=\"$cur_visita\"></td>";
	}
	$return .= "<td height=\"200\" width=\"1\" valign=\"bottom\" align=\"center\" background=\"templates/$option[template]/images/table_grid.gif\"></td>" . '</td></tr><tr><td></td>';
	for ($i = 0; $i < 24; ++$i) {
		if ($i < 10)
			$count = '0' . $i;
		else
			$count = $i;
		$return .= "<td><span class=\"testo\">$count</span></td>";
	}
	$return .= '</tr>' . "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"26\" nowrap></td></tr>" . "<tr><td bgcolor=$style[table_bgcolor] colspan=\"26\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>" . 
	//"<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"26\" nowrap></td></tr>".
		'</table>';
	
	if ($modulo[5] == 2) {
		$return .= '<br><br><center>';
		if ($mode == 0) {
			// SELEZIONE MESE DA VISUALIZZARE
			$return .= "<form action='./admin.php?action=hourly' method='POST' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>" . '<SELECT name=sel_mese>';
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
			$return .= '</SELECT>' . "<input type=\"submit\" value=\"$string[go]\">" . "<br><br><a href=\"admin.php?action=hourly&mode=1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_vis_glob]</span></a>" . '</FORM>';
		} else
			$return .= "<a href=\"admin.php?action=hourly&mode=0\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_vis_mens]</span></a>";
		$return .= "</center>";
	}
	/*
	if($modulo[11]){
	// SELEZIONE MODALITA'
	$return.="<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	if($mode_2!=0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=hourly".($mode ? "&mode=$mode" : '').($sel_mese ? "&sel_mese=$sel_mese" : '').($sel_anno ? "&sel_anno=$sel_anno" : '')."&mode_2=0\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[hourly_mode_0]</span></a></td></tr>";
	if($mode_2!=1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=hourly".($mode ? "&mode=$mode" : '').($sel_mese ? "&sel_mese=$sel_mese" : '').($sel_anno ? "&sel_anno=$sel_anno" : '')."&mode_2=1\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[hourly_mode_1]</span></a></td></tr>";
	if($mode_2!=2) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=hourly".($mode ? "&mode=$mode" : '').($sel_mese ? "&sel_mese=$sel_mese" : '').($sel_anno ? "&sel_anno=$sel_anno" : '')."&mode_2=2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[hourly_mode_2]</span></a></td></tr>";
	$return.="</table></center>";
	}
	*/
	return ($return);
}
?>
