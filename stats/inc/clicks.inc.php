<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

if(isset($_GET['start'])) $start=addslashes($_GET['start']); else $start=0;

function clicks() {
global $db,$string,$error,$style,$option,$varie,$start,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['click_title'];
//
$rec_pag=10; // risultati visualizzayi per pagina
$query_tot=sql_query("SELECT count(id) FROM $option[prefix]_clicks");
list($num_totale)=mysql_fetch_row($query_tot);
$numero_pagine=ceil($num_totale/$rec_pag);
$pagina_corrente= ceil(($start/$rec_pag)+1);
$return=
"\n<SCRIPT>function link(id) {\n".
"document.codice.downcode.value=\"<a href='$option[script_url]/click.php?id=\"+id+\"'>Click</a>\";\n".
"}\n".
"</SCRIPT>\n";
$result=sql_query("SELECT id,nome,url,clicks FROM $option[prefix]_clicks ORDER BY clicks DESC LIMIT $start,$rec_pag");
if(mysql_num_rows($result)>0)
  {
  $return.="<span class=\"pagetitle\">$phpstats_title<br><br></span>";
  if($numero_pagine>1) $return.="<div align=\"right\"><span class=\"testo\">".str_replace(Array('%current%','%total%'),Array($pagina_corrente,$numero_pagine),$varie['pag_x_y'])."&nbsp;&nbsp;</span></div><br>";
  $current=$start;
  $return.=
  "<table border=\"0\" $style[table_header] width=\"95%\" class=\"tableborder\">".
  "<tr><td bgcolor=$style[table_title_bgcolor] nowrap></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[down_elenco_name]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[down_elenco_url]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[down_elenco_id]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[click_elenco_n]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[down_elenco_status]</center></span></td></tr>";
  while($row=mysql_fetch_row($result))
    {
    list($id,$clicks_log_nome,$clicks_log_url,$clicks_log_accessi)=$row;
    ++$current;
    $return.=
    "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\"><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$current</span></td><td bgcolor=$style[table_bgcolor] nowrap><span class=\"tabletextA\">$clicks_log_nome</span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".formaturl($clicks_log_url, "", 55, 22, -25)."</span></td>".
    "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center><a href=\"javascript:link('$id');\" onClick=\"link('$id')\">$id</a></center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">&nbsp;<b>$clicks_log_accessi</b></span></td><td bgcolor=$style[table_bgcolor]><center>".($option['check_links'] ? checkfile($clicks_log_url) : "<img src=\"templates/$option[template]/images/icon_bullet_orange.gif\" title=\"$string[down_notverify]\" alt=\"$string[down_notverify]\">")."</center></td></tr>";
    }
//  $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>";
  if($numero_pagine>1) $return.=
                       "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>".
                       "<tr><td bgcolor=$style[table_bgcolor] colspan=\"6\" height=\"20\" nowrap>".pag_bar("admin.php?action=clicks",$pagina_corrente,$numero_pagine,$rec_pag)."</td></tr>";
  if (user_is_logged_in())
	{
	  $return.=
	  "</table>".
	  "<br><br><form name=\"codice\">". // GENERA CODICE
	  "<table border=\"0\" $style[table_header] width=\"95%\" align=\"center\" class=\"tableborder\">".
	  "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[click_codescript]</span></td></tr>".
	  "<tr><td bgcolor=$style[table_bgcolor]><center><textarea name=\"downcode\" cols=\"80\" rows=\"2\">$string[click_downcli]</textarea></center></form></td></tr>".
	  "</table>";
	 }
  else
  	$return.="</table>";
  }
  else $return.=info_box($string['information'],$error['clicks']);
return($return);
}
?>