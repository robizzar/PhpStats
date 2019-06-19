<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

if(isset($_GET['start'])) $start=addslashes($_GET['start']); else $start=0;
 if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else $mode='?';
   if(isset($_GET['id'])) $id=addslashes($_GET['id']); else $id='?';

function clicksadmin() {
global $mode,$id,$string,$error,$style,$pref,$varie,$option,$refresh,$url,$start,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['click_admin_title'];
$return='';
switch ($mode) {
  case 'edit':
    $result=sql_query("SELECT id,nome,url,creazione,clicks FROM $option[prefix]_clicks WHERE id='$id'");
    if(mysql_affected_rows()<1) $return.=info_box($string['error'],$error['click_noid']);
    else
      {
      list($id,$nome,$url,$data,$clicks)=mysql_fetch_row($result);
      $title=str_replace('%id%',$id,$string['click_title_edit']);
      $return.=
      "<br><br><form action=\"admin.php?action=clicksadmin&mode=apply&id=$id\" method=\"post\">".
      "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
      "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$title</span></td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[click_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[click_name]\" value=\"$nome\" size=\"30\" maxlength=\"50\"></td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[click_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[click_url]\" value=\"$url\" size=\"80\" maxlength=\"255\"></td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><br><input type=\"Submit\" value=\"$string[click_salva]\"></center><br></td></tr>".
      "</form></table>";
      }
  break;

  case 'delete':
    if($id!='')
      {
      sql_query("DELETE FROM $option[prefix]_clicks WHERE id='$id'");
      if(mysql_affected_rows()>0)
        {
        $return.=info_box($string['information'],$string['click_delete_ok']);
        $refresh=1;
        $url=$option['script_url'].'/admin.php?action=clicksadmin';
        }
      else
        {
        $tmp=str_replace('%id%',$id,$error['click_no_delete']);
        $return.=info_box($string['error'],$tmp);
        }
      }
    else $return.="<br><br><center>$error[click_noid]</center><br><br>";
  break;

  case 'new':
    if($option['click_url']=='')
      {
      $errore=$error['click_url']."<br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
      $return.=info_box($string['error'],$errore);
      }
    else
      {
      $data=time();
      $result=sql_query("INSERT INTO $option[prefix]_clicks (nome,url,creazione,clicks) VALUES ('$option[click_name]','$option[click_url]','$data','0')");
      if(mysql_affected_rows()>0)
        {
        $last_id=mysql_insert_id();
        $result=sql_query("SELECT id,nome,url,creazione,clicks FROM $option[prefix]_clicks WHERE id='$last_id'");
        list($id,$nome,$url,$data,$clicks)=mysql_fetch_row($result);
        $js_confirm_msg=str_replace('%id%',$id,$string['click_js_confirm']);
	$return.=info_box($string['information'],$string['click_new_ok'],'90%');
        $return.=
	"<br><br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
        "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[click_title_summary]</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] align=\"right\" width=\"150\"><span class=\"tabletextA\">$string[click_id]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$id</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$nome</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$url</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_status]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]>".checkfile($url)."</td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_date]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"testo\">".formatdate($data)." - ".formattime($data)."</td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] align=\"right\" colspan=\"2\"><span class=\"testo\"><a href=\"$option[script_url]/admin.php?action=clicksadmin&mode=edit&id=$id\">$string[click_edit]</a>&nbsp&nbsp<a href=\"$option[script_url]/admin.php?action=clicksadmin&mode=delete&id=$id\" onclick=\"return confirmLink(this,'$js_confirm_msg')\">$string[click_del]</a>&nbsp</span></td></tr>".
        "</table>";
        }
        else $return.=info_box($string['error'],$error['click_no_update']);
      }
  break;

  case 'apply':
  if($option['click_url']=='')
    {
    $errore=$error['click_url']."<br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
    $return.=info_box($string['error'],$errore);
    }
  else
    {
    $result=sql_query("UPDATE $option[prefix]_clicks SET nome='$option[click_name]', url='$option[click_url]' WHERE id='$id'");
    if(mysql_affected_rows()>0)
      {
      $result=sql_query("SELECT id,nome,url,creazione,clicks FROM $option[prefix]_clicks WHERE id='$id'");
      list($id,$nome,$url,$data,$clicks)=mysql_fetch_row($result);
      $js_confirm_msg=str_replace('%id%',$id,$string['click_js_confirm']);
      $return.=
      info_box($string['information'],$string['click_edit_ok'],'90%').
      "<br><br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
      "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[click_title_summary]</span></td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor] align=\"right\" width=\"150\"><span class=\"tabletextA\">$string[click_id]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$id</span></td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$nome</span></td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$url</span></td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_status]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]>".checkfile($url)."</td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[click_date]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"testo\">".formatdate($data)." - ".formattime($data)."</td></tr>".
      "<tr><td bgcolor=$style[table_bgcolor] align=\"right\" colspan=\"2\"><span class=\"testo\"><a href=\"$option[script_url]/admin.php?action=clicksadmin&mode=edit&id=$id\">$string[click_edit]</a>&nbsp&nbsp<a href=\"$option[script_url]/admin.php?action=clicksadmin&mode=delete&id=$id\" onclick=\"return confirmLink(this,'$js_confirm_msg')\">$string[click_del]</a>&nbsp</span></td></tr>".
      "</table>";
      }
      else $return.=info_box($string['warning'],$error['click_no_update']);
    }
  break;

  default:
    $rec_pag=10; // risultati visualizzayi per pagina
    $query_tot=sql_query("SELECT count(id) FROM $option[prefix]_clicks");
    list($num_totale)=mysql_fetch_row($query_tot);
    $numero_pagine=ceil($num_totale/$rec_pag);
    $pagina_corrente=ceil(($start/$rec_pag)+1);
    $result=sql_query("SELECT id,nome,url FROM $option[prefix]_clicks ORDER BY id ASC LIMIT $start,$rec_pag");
    if(mysql_affected_rows()>0)
      {
      $return.="<span class=\"pagetitle\">$phpstats_title<br></span>";
      if($numero_pagine>1) $return.="<div align=\"right\"><span class=\"testo\">".str_replace(Array('%current%','%total%'),Array($pagina_corrente,$numero_pagine),$varie['pag_x_y'])."&nbsp;&nbsp;</span></div>";
      $return.=
      "<br><table border=\"0\" $style[table_header] width=\"100%\">".
      "<tr><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[click_elenco_id]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[click_elenco_name]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[click_elenco_url]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap></td><td bgcolor=$style[table_title_bgcolor] nowrap></td></tr>";
      while($row=mysql_fetch_array($result,MYSQL_ASSOC))
        {
        $js_confirm_msg=str_replace('%id%',$row['id'],$string['click_js_confirm']);
        $return.=
	"<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$row[id]</span></td>".
	"<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$row[nome]</span></td>".
	"<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".formaturl($row['url'], '', 55, 22, -25)."</span></td>".
	"<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><a href=\"$option[script_url]/admin.php?action=clicksadmin&mode=edit&id=$row[id]\">$string[click_edit]</a></span></td>".
	"<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><a href=\"$option[script_url]/admin.php?action=clicksadmin&mode=delete&id=$row[id]\" onclick=\"return confirmLink(this,'$js_confirm_msg')\">$string[click_del]</a></span></td>".
	"</tr>";
        }
      $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>";
      if($numero_pagine>1) $return.=
	                   "<tr><td bgcolor=$style[table_bgcolor] colspan=\"6\" height=\"20\" nowrap>".pag_bar("admin.php?action=clicksadmin",$pagina_corrente,$numero_pagine,$rec_pag)."</td></tr>".
                           "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>";
      $return.="</table>";
      }
    $return.=
    "<br><br><form action=\"admin.php?action=clicksadmin&mode=new\" method=\"post\">".
    "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
    "<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[click_title_new]</span></td></tr>".
    "<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[click_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[click_name]\" value=\"\" size=\"30\" maxlength=\"50\"></td></tr>".
    "<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[click_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[click_url]\" value=\"\" size=\"80\" maxlength=\"255\"></td></tr>".
    "<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><input type=\"Submit\" value=\"$string[click_salva]\"></center></td></tr>".
    "</form></table>";
  break;
}
return($return);
}
?>
