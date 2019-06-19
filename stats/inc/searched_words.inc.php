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
$date = time() - $option['timezone'] * 3600;
list($month, $year) = explode('-', date('m-Y', $date));

$selected_month = (isset($_POST['selected_month']) ? addslashes($_POST['selected_month']) : $month);
$selected_month = str_pad($selected_month, 2, '0', STR_PAD_LEFT);
$selected_year  = (isset($_POST['selected_year']) ? addslashes($_POST['selected_year']) : $year);

$mode = (isset($_GET['mode']) ? (int) addslashes($_GET['mode']) : ($modulo[4] < 2 ? 0 : 1));

$group = (isset($_GET['group']) ? (int) addslashes($_GET['group']) : 0);
$sim_p = (isset($_GET['sim_p']) ? (int) addslashes($_GET['sim_p']) : 85);

// <!-- PAGE FUNCTION -->
function searched_words()
{
	global $db, $option, $style, $string, $varie, $error, $modulo, $mode, $phpstats_title;
	global $month, $year, $selected_year, $selected_month;
	global $group, $sim_p;
	
	$clause = ($mode === 0 ? "WHERE mese='$selected_year-$selected_month'" : '');
	
	do {
		$dataFound         = false;
		$previousDataFound = false;
		
		//minor date
		$res = sql_query("SELECT min(data) FROM $option[prefix]_daily");
		if (mysql_num_rows($res) < 1)
			break;
		list($min_date) = mysql_fetch_row($res);
		$min_date = (int) substr($min_date, 0, 4);
		
		
		//creation of $data_words
		$data_words = Array();
		$res        = sql_query("SELECT data,visits FROM $option[prefix]_query $clause");
		if (mysql_num_rows($res) < 1)
			break;
		while ($row = mysql_fetch_row($res)) {
			list($query_data, $query_visits) = $row;
			$query_data   = utf8_decode($query_data);
			$data_words[] = Array(
				$query_data,
				(int) $query_visits
			);
		}
		$dataFound = true;
		
		//creation of $data_previousWords
		if ($mode !== 0)
			break; //no division by month
		$data_previousWords = Array();
		$previous_month     = date('Y-m', mktime(0, 0, 0, $selected_month - 1, 1, $selected_year));
		
		$res = sql_query("SELECT data,visits FROM $option[prefix]_query WHERE mese='$previous_month'");
		if (mysql_num_rows($res) < 1)
			break;
		while ($row = mysql_fetch_row($res)) {
			list($query_data, $query_visits) = $row;
			$query_data           = utf8_decode($query_data);
			$data_previousWords[] = Array(
				$query_data,
				(int) $query_visits
			);
		}
		$previousDataFound = true;
	} while (false);
	mysql_free_result($res);
	
	// <!-- DATA PROCESSING -->
	
	if ($dataFound) {
		$min_characters = ($group === 1 ? 3 : 2);
		
		$tmp_words = processWords($data_words, $min_characters);
		unset($data_words);
		if ($group === 1)
			$tmp_words = similar($tmp_words, $sim_p);
		
		if ($previousDataFound) {
			$tmp_previousWords = processWords($data_previousWords, $min_characters);
			unset($data_previousWords);
			if ($group === 1)
				$tmp_previousWords = similar($tmp_previousWords, $sim_p);
			foreach ($tmp_words as $word => $visits) {
				if (isSet($tmp_previousWords[$word])) {
					$prev      = $tmp_previousWords[$word];
					$increment = round(($visits - $prev) / $prev * 100, 1);
				} else {
					$increment = null;
					$prev      = null;
				}
				
				$processed_words[] = Array(
					$word,
					$visits,
					$prev,
					$increment
				);
			}
		} else {
			foreach ($tmp_words as $word => $visits)
				$processed_words[] = Array(
					$word,
					$visits,
					null,
					null
				);
		}
	}
	
	
	// <!-- PRE-OUTPUT PROCESSING -->
	
	if ($dataFound) {
		for ($i = 0, $tot = count($processed_words); $i < $tot; ++$i) {
			list($word, $visits, $previousvisits, $increment) = $processed_words[$i];
			
			if ($increment === null) {
				$level   = 'new';
				$alt_img = '';
			} else {
				if ($increment < -15)
					$level = '1';
				elseif ($increment < -5)
					$level = '2';
				elseif ($increment < 5)
					$level = '3';
				elseif ($increment < 15)
					$level = '4';
				else
					$level = '5';
				
				if ($increment > 0)
					$increment = '+' . $increment;
				$increment .= ' %';
				
				$alt_img = str_replace('%HITS%', $previousvisits, $string['searched_words_last_m']);
				$alt_img .= ' ' . str_replace('%VARIAZIONE%', $increment, $string['searched_words_last_v']);
			}
			
			$output_words[] = Array(
				$word,
				$visits,
				$level,
				$alt_img
			);
		}
		unset($processed_words);
	}
	
	
	// <!-- OUTPUT CREATION -->
	$return = '';
	// Page title (also show in admin)
	if ($mode === 0)
		$phpstats_title = str_replace(Array(
			'%MESE%',
			'%ANNO%'
		), Array(
			formatmount($selected_month),
			$selected_year
		), $string['searched_words_title_2']);
	else
		$phpstats_title = $string['searched_words_title'];
	
	if ($dataFound) {
		$return .= "<span class=\"pagetitle\">$phpstats_title</span><br><br>" . "\n<table $style[table_header] width=\"90%\"  class=\"tableborder\">";
		if ($mode === 0) {
			$return .= "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>";
			for ($i = 0, $tot = MIN(count($output_words), 100); $i < $tot; ++$i) {
				list($word, $visits, $level, $alt_img) = $output_words[$i];
				$return .= "\n<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . "\n\t<td bgcolor=$style[table_bgcolor] width=\"30\" align=\"right\" nowrap><span class=\"tabletextA\">" . ($i + 1) . "</span></td>" . "\n\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$word</span></td>" . "\n\t<td bgcolor=$style[table_bgcolor] width=\"30\" nowrap><span class=\"tabletextA\">$visits</span></td>" . "\n\t<td bgcolor=$style[table_bgcolor] nowrap width=\"16\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/icon_level_$level.gif\" title=\"$alt_img\" alt=\"\"></span></td>" . "</tr>";
			}
			//                $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>";
		} else {
			//                $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>";
			for ($i = 0, $tot = MIN(count($output_words), 100); $i < $tot; ++$i) {
				list($word, $visits, $level, $alt_img) = $output_words[$i];
				$return .= "\n<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">" . "\n\t<td bgcolor=$style[table_bgcolor] width=\"30\" align=\"right\" nowrap><span class=\"tabletextA\">" . ($i + 1) . "</span></td>" . "\n\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$word</span></td>" . "\n\t<td bgcolor=$style[table_bgcolor] width=\"30\" nowrap><span class=\"tabletextA\">$visits</span></td>" . "</tr>";
			}
			//                $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>";
			
		}
		$return .= "</table>";
	} else { //no data
		if ($mode === 1)
			$return .= info_box($string['information'], $error['searched_words']);
		else {
			$tmp = str_replace(Array(
				'%MESE%',
				'%ANNO%'
			), Array(
				formatmount($selected_month),
				$selected_year
			), $error['searched_words_2']);
			$return .= info_box($string['information'], $tmp);
		}
	}
	if ($modulo[4] == 2) {
		$new_group = ($group === 0 ? 1 : 0);
		if ($mode == 0) {
			$dateSelected = "&amp;selected_month=$selected_month&amp;selected_year=$selected_year";
			$return .= "<br><center>" . "<FORM action='./admin.php?action=searched_words&amp;mode=$mode&amp;group=$group' method='POST' name=form1>" . "&nbsp;<span class=\"tabletextA\">$string[calendar_view]</span><SELECT name=selected_month>"; // MONTH SELECTION
			for ($i = 1; $i < 13; ++$i)
				$return .= "<OPTION value='$i'" . ($selected_month == $i ? ' SELECTED' : '') . ">" . $varie['mounts'][$i - 1] . "</OPTION>";
			$return .= "</SELECT>" . "<SELECT name=selected_year>";
			if (!isSet($min_date))
				$min_date = $year;
			for ($i = $min_date; $i <= $year; ++$i)
				$return .= "<OPTION value='$i'" . ($selected_year == $i ? ' SELECTED' : '') . ">$i</OPTION>";
			$return .= "</SELECT>" . "&nbsp;<input type=\"submit\" value=\"$string[go]\">" . "</FORM>\n" . "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n" . "<tr><td><span class=\"testo\"><a href=\"admin.php?action=searched_words&amp;mode=1&amp;group=$group\"><img src=\"templates/$option[template]/images/icon_change.gif\" border=\"0\" align=\"middle\" hspace=\"1\" vspace=\"1\" alt=\"\" > $string[searched_words_query_vis_glob]</a></span></td></tr>\n";
		} else {
			$dateSelected = '';
			$return .= "<br><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n" . "<tr><td><span class=\"testo\"><a href=\"admin.php?action=searched_words&amp;mode=0&amp;group=1\"><img src=\"templates/$option[template]/images/icon_change.gif\" border=\"0\" align=\"middle\" hspace=\"1\" vspace=\"1\" alt=\"\" > $string[searched_words_query_vis_mens]</a></span></td></tr>\n";
		}
		//INIZIO RAGGRUPPAMENTO PAROLE SIMILI Mod By Dapuzz
		$return .= "<tr>\n<td><span class=\"testo\"><a href=\"admin.php?action=searched_words&amp;mode=$mode&amp;group=$new_group{$dateSelected}\">" . "<img src=\"templates/$option[template]/images/icon_changevis.gif\" border=\"0\" align=\"middle\" hspace=\"1\" vspace=\"1\" alt=\"\">";
		$return .= ($group != 0 ? $string['searched_words_nogroup'] : $string['searched_words_group']);
		$return .= "</a></span></td>\n</tr>\n"; //FINE RAGGRUPPO
		$return .= "</table></center>\n";
	}
	
	// RESTITUISCO OUTPUT
	return ($return);
}

// <!-- ADDITIONAL FUNCTIONS -->

function processWords($data_array, $min_characters)
{
	$search  = Array(
		'"',
		'\'',
		'+',
		' AND ',
		' OR ',
		'+',
		'(',
		')',
		':',
		'.',
		'[',
		']',
		'\\',
		'/'
	);
	$replace = Array(
		'',
		'',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' ',
		' '
	);
	
	$processed_words  = Array();
	$this_query_words = Array();
	
	for ($i = 0, $tot = count($data_array); $i < $tot; ++$i) {
		list($query, $visits) = $data_array[$i];
		// ELIMINO CARATTERI NON UTILI
		$query = str_replace($search, $replace, $query);
		$query = eregi_replace('( ){2,}', ' ', $query);
		$query = explode(' ', $query);
		for ($x = 0, $totx = count($query); $x < $totx; ++$x) {
			$word = $query[$x];
			
			if (strlen($word) <= $min_characters)
				continue;
			
			if (isset($processed_words[$word]))
				$processed_words[$word] += $visits;
			else
				$processed_words[$word] = $visits;
		}
	}
	arsort($processed_words);
	return $processed_words;
}

function similar($word_list, $percent) //MOD PAROLE SIMILIBy Dapuzz & TheCas
{
	$words    = Array();
	$toSearch = Array();
	$count    = 0;
	
	foreach ($word_list as $key => $value) {
		$words[]    = array(
			strtolower($key),
			$value
		); //array di record
		$toSearch[] = $count++; //array degli indici da cercare
	}
	$word_list = Array(); //non mi serve più, per ora lo elimino
	while (count($toSearch) > 0) {
		$newToSearch = Array(); //inizializzo il prossimo array degli indici di ricerca
		list($curWord, $curValue) = $words[$toSearch[0]]; //prendo la parola, ne estraggo i valori...
		for ($i = 1, $tot = count($toSearch); $i < $tot; ++$i) //e la controllo con tutte le altre indicate dall'array toSearch
			{
			list($tmpWord, $tmpValue) = $words[$toSearch[$i]];
			similar_text($curWord, $tmpWord, $p);
			//  echo "confrontata $curWord con $tmpWord - similarità del $p% <br>";
			if ($p >= $percent) //&& $p <100)
				{
				$curValue += $tmpValue; //parola simile (il <100 a che serviva?)
				//$tmpasd[$tmpWord] = " => $curWord";
			} else
				$newToSearch[] = $toSearch[$i]; //parola diversa, la metto nell'array della prossima ricerca
		}
		$word_list["$curWord"] = $curValue; //inserisco il valore nel nuovo array
		$toSearch              = $newToSearch; //aggiorno l'array con gli indici di ricerca
	}
	//$word_list = array_merge($word_list,$tmpasd);
	arsort($word_list); //riordina spostando tutti gli associati alla fine.
	return ($word_list);
}

?>
