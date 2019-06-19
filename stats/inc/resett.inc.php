<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die("Php-Stats internal file.");

if(isset($_POST['selected_tbl'])) $selected_tbl=$_POST['selected_tbl'];
      if(isset($_GET['confirm'])) $confirm=addslashes($_GET['confirm']); else $confirm=0;

function resett() {
global $db,$option,$error,$confirm,$string,$style,$selected_tbl,$refresh,$url,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['reset_title'];
//
$return='';
if($confirm==1)
  {
  if(is_array($selected_tbl))
    {
    foreach($selected_tbl as $val)
      {
      if($val=="01") { sql_query("UPDATE $option[prefix]_counters SET hits=0,visits=0,no_count_hits=0,no_count_visits=0");  }
      if($val=="02") { sql_query("DELETE FROM $option[prefix]_details");  }
      if($val=="03") { sql_query("DELETE FROM $option[prefix]_systems");  }
      if($val=="04") { sql_query("UPDATE $option[prefix]_langs SET hits=0,visits=0"); }
      if($val=="05") { sql_query("DELETE FROM $option[prefix]_pages"); }
      if($val=="06") { sql_query("UPDATE $option[prefix]_pages SET presence=0,tocount=0"); }
      if($val=="07") { sql_query("DELETE FROM $option[prefix]_referer"); }
      if($val=="09") { sql_query("DELETE FROM $option[prefix]_query"); }
      if($val=="10") { sql_query("DELETE FROM $option[prefix]_hourly"); }
      if($val=="11") { sql_query("UPDATE $option[prefix]_domains SET hits=0,visits=0"); }
      if($val=="12") { sql_query("DELETE FROM $option[prefix]_daily"); }
      if($val=="13") { sql_query("DELETE FROM $option[prefix]_ip"); }
	  if($val=="14") { sql_query("UPDATE $option[prefix]_config SET value='0|0' WHERE name='instat_max_online'"); }
	  if($val=="30") { sql_query("UPDATE $option[prefix]_downloads SET downloads=0"); }
	  if($val=="31") { sql_query("UPDATE $option[prefix]_clicks SET clicks=0"); }
	  if($val=="40") { sql_query("DELETE FROM $option[prefix]_links"); }
      }
    sql_query("DELETE FROM $option[prefix]_cache");
    $return.=info_box($string['information'],$string['reset_done']);
    $refresh=1;
    $url="$option[script_url]/admin.php?action=main";
    }
    else
    {
    $return.=info_box($string['information'],$string['reset_err']);
    $refresh=1;
    $url="$option[script_url]/admin.php?action=resett";
    }
  }
  else
  {
  $return.=
  "<script>\n".
  "function setCheckboxes(the_form, do_check)\n".
  "  {\n".
  "    var elts=document.forms[the_form].elements['selected_tbl[]'];\n".
  "    var elts_cnt=elts.length;\n".
  "    for (var i=0; i < elts_cnt; i++) {\n".
  "      elts[i].checked=do_check;\n".
  "      }\n".
  "    return true;\n".
  "  }\n".
  "</script>\n".
  "<br><br>".
  "<form name=\"reset\" action=\"admin.php?action=resett&confirm=1\" method=\"POST\">".
  "<table border=\"0\" $style[table_header] width=\"416\" align=\"center\">".
  "<tr><td bgcolor=$style[table_title_bgcolor] colSpan=2><span class=\"tabletitle\">$string[reset_title]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"01\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_01]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"02\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_02]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"03\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_03]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"04\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_04]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"05\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_05]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"06\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_06]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"07\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_07]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"09\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_09]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"10\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_10]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"11\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_11]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"12\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_12]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"13\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_13]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"14\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_14]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"30\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_30]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"31\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_31]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] width=\"16\"><input type=\"checkbox\" name=\"selected_tbl[]\" value=\"40\" class=\"checkbox\"></td><td bgcolor=$style[table_bgcolor] width=\"400\"><span class=\"tabletextA\">$string[reset_40]</span></td></tr>".
  "<tr><td bgcolor=$style[table_bgcolor] colSpan=2><span class=\"testo\"><img src=\"templates/$option[template]/images/arrow_sx_up.gif\"><a href=\"admin.php?action=resett\" onclick=\"setCheckboxes('reset', true); return false;\">$string[reset_selall]</a> / <a href=\"admin.php?action=resett\" onclick=\"setCheckboxes('reset', false); return false;\">$string[reset_desall]</a></span>".
  "<br><br><center><input type=\"submit\" value=\"$string[reset_do]\"></center><br></td></tr>".
  "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\" nowrap></td></tr>".
  "</table>".
  "</form>";
}
return($return);
}
?>
