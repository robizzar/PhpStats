<?php

// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

if(isset($_GET['start'])) $start=addslashes($_GET['start']); else $start=0;
 if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else $mode='?';
   if(isset($_GET['id'])) $id=addslashes($_GET['id']); else $id='?';


function downadmin() {
global $mode,$id,$string,$error,$style,$pref,$varie,$option,$refresh,$url,$start,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['down_admin_title'];
$return='';
switch ($mode) {
  case 'edit':
    $result=sql_query("SELECT nome,descrizione,type,home,url,withinterface FROM $option[prefix]_downloads WHERE id='$id'");
    if(mysql_affected_rows()<1){$return.=info_box($string['error'],$error['down_noid']);break;}

    list($downloads_nome,$downloads_descrizione,$downloads_type,$downloads_home,$downloads_url,$downloads_withinterface)=mysql_fetch_row($result);
    $real_path=parse_url($downloads_url);

    if(isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'],'/cgi-bin/')===FALSE){
      $_SERVER['DOCUMENT_ROOT']=str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['SCRIPT_FILENAME']).'/';
      }
    else{
      if(!isset($_SERVER['DOCUMENT_ROOT'])){
        $_SERVER['DOCUMENT_ROOT']=str_replace(Array('//','\\\\',$_SERVER['PHP_SELF']),'/',$_SERVER['PATH_TRANSLATED']);
        }
      }
    if(strpos($_SERVER['DOCUMENT_ROOT'],':')===1) $_SERVER['DOCUMENT_ROOT']=substr($_SERVER['DOCUMENT_ROOT'],2);

    $parsed_url=($_SERVER['HTTP_HOST']==$real_path['host'] ? str_replace($_SERVER['HTTP_HOST'],$_SERVER['DOCUMENT_ROOT'],substr($downloads_url,strpos($downloads_url,$_SERVER['HTTP_HOST']))) : $downloads_url);
    if($parsed_url==$downloads_url){
       $parsed_url=($_SERVER['SERVER_ADDR']==$real_path['host'] ? str_replace($_SERVER['SERVER_ADDR'],$_SERVER['DOCUMENT_ROOT'],substr($downloads_url,strpos($downloads_url,$_SERVER['SERVER_ADDR']))) : $downloads_url);
       }
    $downloads_size=size($parsed_url);
    $title=str_replace('%id%',$id,$string['down_title_edit']);
    $return.=
    "\n<br><br><form action=\"admin.php?action=downadmin&mode=apply&id=$id\" method=\"post\">".
    "\n<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
    "\n<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$title</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_name]\" value=\"".stripslashes($downloads_nome)."\" size=\"30\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_desc]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_desc]\" value=\"".stripslashes($downloads_descrizione)."\" size=\"80\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_type]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_type]\" value=\"".stripslashes($downloads_type)."\" size=\"30\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_home]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_home]\" value=\"$downloads_home\" size=\"30\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_size]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_size]\" value=\"$downloads_size\" size=\"30\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_url]\" value=\"$downloads_url\" size=\"80\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_withinterface]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"checkbox\" name=\"option[down_withinterface]\" value=\"YES\"".($downloads_withinterface=='YES' ? ' checked' : '')."></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_status]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]>".checkfile($downloads_url)."</td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><br><input type=\"Submit\" value=\"$string[down_salva]\"></center><br></td></tr>".
    "\n</form></table>";
  break;

  case 'delete':
    if($id==''){$return.="<br><br><center>$error[down_noid]</center><br><br>";break;} //nessun id fornito

    sql_query("DELETE FROM $option[prefix]_downloads WHERE id='$id'");

    if(mysql_affected_rows()<1) {$return.=info_box($string['error'],str_replace('%id%',$id,$error['down_no_delete']));break;} //nessuna operazione effettuata

    $return.=info_box($string['information'],$string['down_delete_ok']);
    $refresh=1;
    $url="$option[script_url]/admin.php?action=downadmin";
  break;

  case 'reset':
    if($id==''){$return.="<br><br><center>$error[down_noid]</center><br><br>";break;}

    sql_query("UPDATE $option[prefix]_downloads SET downloads='0' WHERE id='$id'");

    if(mysql_affected_rows()<1) {$return.=info_box($string['error'],str_replace('%id%',$id,$error['down_no_reset']));break;} //nessuna operazione effettuata

    $return.=info_box($string['information'],$string['down_reset_ok']);
    $refresh=1;
    $url="$option[script_url]/admin.php?action=downadmin";
  break;

  case 'new':
    if($option['down_url']=='')
      {
      $errore=$error['down_url'].="<br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
      $return.=info_box($string['error'],$errore);
      break;
      }
    $data=time();
    if($option['down_mode']==2) $option['down_url']=relative_path($option['down_url'],$_SERVER['PHP_SELF']);
    $real_path=parse_url($option['down_url']);

    if(isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'],'/cgi-bin/')===FALSE){
      $_SERVER['DOCUMENT_ROOT']=str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['SCRIPT_FILENAME']).'/';
      }
    else{
      if(!isset($_SERVER['DOCUMENT_ROOT'])){
        $_SERVER['DOCUMENT_ROOT']=str_replace(Array('//','\\\\',$_SERVER['PHP_SELF']),'/',$_SERVER['PATH_TRANSLATED']);
        }
      }
    if(strpos($_SERVER['DOCUMENT_ROOT'],':')===1) $_SERVER['DOCUMENT_ROOT']=substr($_SERVER['DOCUMENT_ROOT'],2);

    $parsed_url=($_SERVER['HTTP_HOST']==$real_path['host'] ? str_replace($_SERVER['HTTP_HOST'],$_SERVER['DOCUMENT_ROOT'],substr($option['down_url'],strpos($option['down_url'],$_SERVER['HTTP_HOST']))) : $option['down_url']);
    if($parsed_url==$option['down_url']){
      $parsed_url=($_SERVER['SERVER_ADDR']==$real_path['host'] ? str_replace($_SERVER['SERVER_ADDR'],$_SERVER['DOCUMENT_ROOT'],substr($option['down_url'],strpos($option['down_url'],$_SERVER['SERVER_ADDR']))) : $option['down_url']);
      }
    $option['down_size']=size($parsed_url);
    $option['down_withinterface']=($option['down_withinterface']=='YES' ? 'YES' : 'NO');
    $option['down_name']=(trim($option['down_name'])=='' ? $option['down_url'] : addslashes(trim($option['down_name'])));
    $option['down_desc']=addslashes(trim($option['down_desc']));
    $option['down_home']=addslashes(trim($option['down_home']));
    $option['down_type']=addslashes(trim($option['down_type']));

    if($option['down_type']==''){
      $tmpType='';
      $tmpType=explode('.',$option['down_url']);
      $option['down_type']=(is_array($tmpType) ? $tmpType[count($tmpType)-1] : '');
      }

    $result=sql_query("INSERT INTO $option[prefix]_downloads VALUES('','$option[down_name]','$option[down_desc]','$option[down_type]','$option[down_home]','$option[down_size]','$option[down_url]','$data','0','$option[down_withinterface]')");
    if(mysql_affected_rows()<1){$return.=info_box($string['error'],$error['down_no_update']);break;}

    $downloads_id=mysql_insert_id();
    $result=sql_query("SELECT nome,descrizione,type,size,url,withinterface FROM $option[prefix]_downloads WHERE id='$downloads_id'");
    list($downloads_nome,$downloads_descrizione,$downloads_type,$downloads_size,$downloads_url,$downloads_withinterface)=mysql_fetch_row($result);
    $js_confirm_msg=str_replace('%id%',"$downloads_id",$string['click_js_confirm']);
    $downloads_withinterface=($downloads_withinterface=='YES' ? $string['down_yes'] : $string['down_no']);
    $return.=
    info_box($string['information'],$string['down_new_ok'],'90%').
    "\n<br><br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
    "\n<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[down_title_summary]</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\" width=\"150\"><span class=\"tabletextA\">$string[down_id]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_id</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".stripslashes($downloads_nome)."</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_desc]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".stripslashes($downloads_descrizione)."</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_type]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".stripslashes($downloads_type)."</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_size]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_size</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_url</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_withinterface]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_withinterface</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_status]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]>".checkfile($downloads_url)."</td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_date]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"testo\">".formatdate($data)." - ".formattime($data)."</td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\" colspan=\"2\"><span class=\"testo\"><a href=\"$option[script_url]/admin.php?action=downadmin&mode=edit&id=$downloads_id\">$string[down_edit]</a>&nbsp&nbsp<a href=\"$option[script_url]/admin.php?action=downadmin&mode=delete&id=$downloads_id\" onclick=\"return confirmLink(this,'$js_confirm_msg')\">$string[down_del]</a>&nbsp</span></td></tr>".
    "\n</table>";
  break;

  case 'apply':
    if($option['down_url']=='')
      {
      $errore=$error['down_url']."\n<br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
      $return.=info_box($string['error'],$errore);
      break;
      }

    $option['down_withinterface']=($option['down_withinterface']=='YES' ? 'YES' : 'NO');
    $option['down_name']=(trim($option['down_name'])=='' ? $option['down_url'] : addslashes(trim($option['down_name'])));
    $option['down_desc']=addslashes(trim($option['down_desc']));
    $option['down_home']=addslashes(trim($option['down_home']));
    $option['down_type']=addslashes(trim($option['down_type']));

    if($option['down_type']==''){
      $tmpType='';
      $tmpType=explode('.',$option['down_url']);
      $option['down_type']=(is_array($tmpType) ? $tmpType[count($tmpType)-1] : '');
      }

    $real_path=parse_url($option['down_url']);

    if(isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'],'/cgi-bin/')===FALSE){
      $_SERVER['DOCUMENT_ROOT']=str_replace($_SERVER['SCRIPT_NAME'],'',$_SERVER['SCRIPT_FILENAME']).'/';
      }
    else{
      if(!isset($_SERVER['DOCUMENT_ROOT'])){
        $_SERVER['DOCUMENT_ROOT']=str_replace(Array('//','\\\\',$_SERVER['PHP_SELF']),'/',$_SERVER['PATH_TRANSLATED']);
        }
      }
    if(strpos($_SERVER['DOCUMENT_ROOT'],':')===1) $_SERVER['DOCUMENT_ROOT']=substr($_SERVER['DOCUMENT_ROOT'],2);

    $parsed_url=($_SERVER['HTTP_HOST']==$real_path['host'] ? str_replace($_SERVER['HTTP_HOST'],$_SERVER['DOCUMENT_ROOT'],substr($option['down_url'],strpos($option['down_url'],$_SERVER['HTTP_HOST']))) : $option['down_url']);
    $option['down_size']=size($parsed_url);

    sql_query("UPDATE $option[prefix]_downloads SET nome='$option[down_name]', descrizione='$option[down_desc]', type='$option[down_type]', home='$option[down_home]', size='$option[down_size]', url='$option[down_url]', withinterface='$option[down_withinterface]' WHERE id='$id'");

    if(mysql_affected_rows()<1) {$return.=info_box($string['warning'],$error['down_no_update']);break;}

    $result=sql_query("SELECT nome,descrizione,type,home,size,url,creazione,withinterface FROM $option[prefix]_downloads WHERE id='$id'");
    list($downloads_nome,$downloads_descrizione,$downloads_type,$downloads_home,$downloads_size,$downloads_url,$downloads_creazione,$downloads_withinterface)=mysql_fetch_row($result);
    $js_confirm_msg=str_replace('%id%',"$id",$string['click_js_confirm']);
    $downloads_withinterface=($downloads_withinterface=='YES' ? $string['down_yes'] : $string['down_no']);
    $return.=
    info_box($string['information'],$string['down_edit_ok'],'90%').
    "\n<br><br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
    "\n<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[down_title_summary]</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\" width=\"150\"><span class=\"tabletextA\">$string[down_id]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$id</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".stripslashes($downloads_nome)."</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_desc]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".stripslashes($downloads_descrizione)."</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_type]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".stripslashes($downloads_type)."</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_home]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_home</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_size]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_size</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_url</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_withinterface]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_withinterface</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_status]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]>".checkfile($downloads_url)."</td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[down_date]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><span class=\"testo\">".formatdate($downloads_creazione)." - ".formattime($downloads_creazione)."</td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] align=\"right\" colspan=\"2\"><span class=\"testo\"><a href=\"$option[script_url]/admin.php?action=downadmin&mode=edit&id=$id\">$string[down_edit]</a>&nbsp&nbsp<a href=\"$option[script_url]/admin.php?action=downadmin&mode=delete&id=$id\" onclick=\"return confirmLink(this,'$js_confirm_msg')\">$string[down_del]</a>&nbsp</span></td></tr>".
    "\n</table>";
  break;

  default:
    $rec_pag=20; // risultati visualizzati per pagina
    $query_tot=sql_query("SELECT count(id) FROM $option[prefix]_downloads");
    list($num_totale)=mysql_fetch_row($query_tot);
    $numero_pagine=ceil($num_totale/$rec_pag);
    $pagina_corrente=ceil(($start/$rec_pag)+1);
    $result=sql_query("SELECT id,nome,url FROM $option[prefix]_downloads ORDER BY id ASC LIMIT $start,$rec_pag");
    if(mysql_affected_rows()>0)
      {
      $return.="\n<span class=\"pagetitle\">$phpstats_title<br></span>";
      if($numero_pagine>1) $return.="\n<div align=\"right\"><span class=\"testo\">".str_replace(Array('%current%','%total%'),Array($pagina_corrente,$numero_pagine),$varie['pag_x_y'])."&nbsp;&nbsp;</span></div>";

      $return.="\n<br><table border=\"0\" $style[table_header] width=\"100%\">";
      $return.="\n<tr><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[down_elenco_id]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[down_elenco_name]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[down_elenco_url]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap></td><td bgcolor=$style[table_title_bgcolor] nowrap></td><td bgcolor=$style[table_title_bgcolor] nowrap></td></tr>";
      while($row=mysql_fetch_row($result))
        {
        list($downloads_id,$downloads_nome,$downloads_url)=$row;
        $js_confirm_msg_del=str_replace('%id%',$downloads_id,$string['down_js_confirm_del']);
        $js_confirm_msg_reset=str_replace('%id%',$downloads_id,$string['down_js_confirm_reset']);
        $return.=
        "\n<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "\n<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$downloads_id</span></td>".
        "\n<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".stripslashes($downloads_nome)."</span></td>".
        "\n<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".formaturl($downloads_url, '', 55, 22, -25)."</span></td>".
        "\n<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><a href=\"$option[script_url]/admin.php?action=downadmin&mode=reset&id=$downloads_id\" onclick=\"return confirmLink(this,'$js_confirm_msg_reset')\">$string[down_reset]</a></span></td>".
        "\n<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><a href=\"$option[script_url]/admin.php?action=downadmin&mode=edit&id=$downloads_id\">$string[down_edit]</a></span></td>".
        "\n<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><a href=\"$option[script_url]/admin.php?action=downadmin&mode=delete&id=$downloads_id\" onclick=\"return confirmLink(this,'$js_confirm_msg_del')\">$string[down_del]</a></span></td>".
        "\n</tr>";
        }
      $return.="\n<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>";
      if($numero_pagine>1) $return.=
                           "\n<tr><td bgcolor=$style[table_bgcolor] colspan=\"6\" height=\"20\" nowrap>".pag_bar("admin.php?action=downadmin",$pagina_corrente,$numero_pagine,$rec_pag)."</td></tr>".
                           "\n<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\" nowrap></td></tr>";
      $return.="</table>";
      }
    $return.=
    "\n<br><br><form action=\"admin.php?action=downadmin&mode=new\" method=\"post\">".
    "\n<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" >".
    "\n<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\">$string[down_title_new]</span></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_name]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_name]\" value=\"\" size=\"30\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_desc]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_desc]\" value=\"\" size=\"80\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_type]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_type]\" value=\"\" size=\"30\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_home]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_home]\" value=\"\" size=\"30\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_url]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"text\" name=\"option[down_url]\" value=\"\" size=\"80\" maxlength=\"255\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor]><center><span class=\"tabletextA\">$string[down_withinterface]</span>&nbsp</td><td bgcolor=$style[table_bgcolor]><input type=\"checkbox\" name=\"option[down_withinterface]\" value=\"YES\"></td></tr>".
    "\n<tr><td bgcolor=$style[table_bgcolor] colspan=\"2\"><center><input type=\"Submit\" value=\"$string[down_salva]\"></center></td></tr>".
    "\n</form></table>";
  break;
}
return($return);
}
?>