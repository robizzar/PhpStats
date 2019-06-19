<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die("Php-Stats internal file.");

if(isset($_POST['selected_page'])) $selected_page=$_POST['selected_page'];
      if(isset($_GET['confirm'])) $confirm=addslashes($_GET['confirm']); else $confirm=0;

function unlock_pages() {
global $db,$option,$error,$confirm,$string,$style,$selected_page,$refresh,$url,$phpstats_title,$page_list,$admin_menu;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['unlock_pages_title'];
$return='';
if($confirm==1)
  {
  $unlockedArray=Array();
  array_pad($unlockedArray,23,0);
  $unclockedModules='';
  if(is_array($selected_page))
    {
    foreach($selected_page as $val) $unlockedArray[$val]=1;
    for($i=0;$i<24;++$i) $unclockedModules.=($unlockedArray[$i]==1 ? '1|' : '0|');
    }
  if($unclockedModules==='') $unclockedModules='0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|0|';
  sql_query("UPDATE $option[prefix]_config SET value='$unclockedModules' WHERE name='unlock_pages'");
  create_option_file();
  $return.=info_box($string['information'],$string['unlock_pages_done']);
  $refresh=1;
  $url="$option[script_url]/admin.php?action=preferenze";
  }
else{
$result=sql_query("SELECT value FROM $option[prefix]_config WHERE name='unlock_pages'");
list($unlockedModules)=mysql_fetch_array($result);
$unlockedPages=explode('|',$unlockedModules);
$return.=
"<script>\n".
"function setCheckboxes(the_form, do_check)\n".
"  {\n".
"    var elts=document.forms[the_form].elements['selected_page[]'];\n".
"    var elts_cnt=elts.length;\n".
"    for (var i=0; i < elts_cnt; i++) {\n".
"      elts[i].checked=do_check;\n".
"      }\n".
"    return true;\n".
"  }\n".
"</script>\n".
"<br>".
"\n<form method=\"POST\" action=\"admin.php?action=unlock_pages&confirm=1\" name=\"formcheck\">".
"\n<TABLE $style[table_header] width=\"250\">".
"\n\t<tr><td bgcolor=\"$style[table_title_bgcolor]\" colspan=\"2\"><span class=\"tabletitle\"><center>$string[unlock_pages_title]</center></span></td></tr>";

$stop_list=0;
for($i=0,$endValue=count($page_list);$i<$endValue;++$i)
   $return.=
   "\n\t<tr><td bgcolor=$style[table_bgcolor]><input type=\"checkbox\" name=\"selected_page[]\" value=\"$i\" class=\"checkbox\"".($unlockedPages[$i] ? ' checked' : '')."></td>".
   "\n\t<td bgcolor=$style[table_bgcolor] width=\"100%\"><span class=\"tabletextA\">".$admin_menu[$page_list[$i]]."</span></td></tr>";

$return.=
"<tr><td bgcolor=$style[table_bgcolor] colSpan=2><span class=\"testo\"><img src=\"templates/$option[template]/images/arrow_sx_up.gif\"><a href=\"popup_unlock_pages.inc.php\" onclick=\"setCheckboxes('formcheck', true); return false;\">$string[unlock_pages_selall]</a> / <a href=\"popup_unlock_pages.inc.php\" onclick=\"setCheckboxes('formcheck', false); return false;\">$string[unlock_pages_desall]</a></span>".
"<br><br><center><input type=\"submit\" value=\"$string[unlock_pages_do]\"></center><br></td></tr>".
"\n</table>".
"</form>";
}
return($return);
}
?>