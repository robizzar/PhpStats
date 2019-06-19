<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

//-------------------------------------------------------------------------------------------------
// 		SALVATAGGIO / CARICAMENTO DELL'ULTIMA MODALITA' DI VISUALIZZAZIONE UTILIZZATA
//-------------------------------------------------------------------------------------------------
if ( user_is_logged_in() && $option['keep_view_mode'])
{
	foreach ($_GET as $key => $value)
	{
		if ($key != 'action' && $key != 'start' && $key != 'engine_details' && $key != 'domain_details')
			$save_cfg .= "\$_GET['$key']='$value';\r\n";
	}

	if ($save_cfg)
	{
		file_put_contents('option/'.$_GET['action'].'.cfg', $save_cfg);
	}
	else if (count($_GET)==1)
	{
		$settings = file_get_contents('option/'.$_GET['action'].'.cfg');
	    eval( $settings );
	}
}
//-------------------------------------------------------------------------------------------------

$date=time()-$option['timezone']*3600;
list($mese,$anno)=explode('-',date('m-Y',$date));

     if(isset($_POST['sel_mese'])) $sel_mese=addslashes($_POST['sel_mese']); else $sel_mese=$mese;
     if(isset($_POST['sel_anno'])) $sel_anno=addslashes($_POST['sel_anno']); else $sel_anno=$anno;

/*** ORIGINALE
          if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else if($modulo[4]<2) $mode=1; else $mode=0;
***/
          if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else if($modulo[4]<2) $mode=0; else $mode=1;

    if(isset($_GET['enginemode'])) $enginemode=addslashes($_GET['enginemode'])-0; else $enginemode=0;
          if(isset($_GET['mese'])) list($sel_anno,$sel_mese)=explode('-',addslashes($_GET['mese']));
          if(isset($_GET['sort'])) $sort=addslashes($_GET['sort']); else $sort=1;
         if(isset($_GET['order'])) $order=addslashes($_GET['order']); else $order=0; // Default order
if(isset($_GET['engine_details'])) $engine_details=addslashes($_GET['engine_details']); else $engine_details='';
if(isset($_GET['domain_details'])) $domain_details=addslashes($_GET['domain_details']); else $domain_details='';
         if(isset($_GET['start'])) $start=addslashes($_GET['start']); else $start=0;

function engines() {
global $db,$string,$error,$varie,$style,$option,$start,$mode,$enginemode,$modulo;
global $mese,$anno,$sel_anno,$sel_mese,$sort,$order,$engine_details,$domain_details,$phpstats_title;

include("lang/$option[language]/domains_lang.php");

$return='';
$max_hits=$total_hits=0;
if(strlen($sel_mese)<2) $sel_mese='0'.$sel_mese;
if($mode==0)
  {
  switch($enginemode)
    {
    case 0:
      $clause="WHERE mese='$sel_anno-$sel_mese'";
      $clause_expl="WHERE engine='$engine_details' AND domain='$domain_details' AND mese='$sel_anno-$sel_mese'";
    break;
    case 1:
      $clause="WHERE mese='$sel_anno-$sel_mese'";
      $clause_expl="WHERE engine='$engine_details' AND mese='$sel_anno-$sel_mese'";
    break;
    case 2:
      $clause="WHERE mese='$sel_anno-$sel_mese'";
      $clause_expl="WHERE domain='$domain_details' AND mese='$sel_anno-$sel_mese'";
    break;
    }
  }
else
  {
  switch($enginemode)
    {
    case 0:
      $clause='';
      $clause_expl="WHERE engine='$engine_details' AND domain='$domain_details'";
    break;
    case 1:
      $clause='';
      $clause_expl="WHERE engine='$engine_details'";
    break;
    case 2:
      $clause='';
      $clause_expl="WHERE domain='$domain_details'";
    break;
    }
  }

// Titolo pagina (riportata anche nell'admin)
if($mode==0) $phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$string['se_title_2']);
else $phpstats_title=$string['se_title'];
//

switch($enginemode){
        case 0: $query="SELECT SUM(visits) FROM $option[prefix]_query $clause GROUP BY engine,domain"; break;
        case 1: $query="SELECT SUM(visits) FROM $option[prefix]_query $clause GROUP BY engine"; break;
        case 2: $query="SELECT SUM(visits) FROM $option[prefix]_query $clause GROUP BY domain"; break;
}
$result=sql_query($query);
while($row=mysql_fetch_row($result))
  {
  if($row[0]>$max_hits) $max_hits=$row[0];
  $total_hits+=$row[0];
  }

if($total_hits>0)
  {
  $return.=
  "<span class=\"pagetitle\">$phpstats_title</span><br><br>".
  "\n<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">";

  $tables=Array('engine'=>'engine','domain'=>'domain','enginedomain'=>'engine,domain','hits'=>'dummy');
  $modes=Array('0'=>'DESC','1'=>'ASC');
  //
  //!!!qui ci sarà anche da inserire paese occhio a draw_table_title
  //
  $q_sort=(isset($tables[$sort]) ? $tables[$sort] : 'dummy');
  $q_order=(isset($modes[$order]) ? $modes[$order] : 'DESC');
  $q_append2="$q_sort $q_order";
  $return.=
  "<tr>".
  draw_table_title('');
  switch($enginemode)
    {
    case 0: $return.=draw_table_title($string['se_name'],'enginedomain',"admin.php?action=engines&mode=$mode&enginemode=$enginemode&mese=$sel_anno-$sel_mese",$tables,$q_sort,$q_order); break;
    case 1: $return.=draw_table_title($string['se_name'],'engine',"admin.php?action=engines&mode=$mode&enginemode=$enginemode&mese=$sel_anno-$sel_mese",$tables,$q_sort,$q_order); break;
    case 2: $return.=draw_table_title($string['se_domain'],'domain',"admin.php?action=engines&mode=$mode&enginemode=$enginemode&mese=$sel_anno-$sel_mese",$tables,$q_sort,$q_order); break;
    }
  $return.=
  draw_table_title($string['se_hits'],'hits',"admin.php?action=engines&mode=$mode&enginemode=$enginemode&mese=$sel_anno-$sel_mese",$tables,$q_sort,$q_order).
  draw_table_title('').
  draw_table_title('').
  "</tr>";
  switch($enginemode)
    {
    case 0: $query="SELECT engine,domain,SUM(visits) AS dummy FROM $option[prefix]_query $clause GROUP BY engine,domain ORDER BY $q_append2"; break;
    case 1: $query="SELECT engine,SUM(visits) AS dummy FROM $option[prefix]_query $clause GROUP BY engine ORDER BY $q_append2"; break;
    case 2: $query="SELECT domain,SUM(visits) AS dummy FROM $option[prefix]_query $clause GROUP BY domain ORDER BY $q_append2"; break;
    }
  $result=sql_query($query);
  while($row=mysql_fetch_row($result))
    {
    $display_details=FALSE;
    switch($enginemode)
      {
      case 0:
        list($query_engine,$query_domain,$query_sumvisits)=$row;
        $image='images/engines.php?q='.str_replace(' ','-',$query_engine);
        $return.=
        "\n<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "\n\t<td bgcolor=$style[table_bgcolor] width=\"16\"><img src=\"$image\"></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$query_engine (".$domain_name[$query_domain].")</span></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$query_sumvisits</b></span></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($query_sumvisits/$max_hits*100)."\" height=\"7\"> (".round($query_sumvisits*100/$total_hits,2)."%)</span></td>";
        if($query_engine===$engine_details && $query_domain===$domain_details)
          {
          $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=$enginemode&sort=$sort&order=$order&mese=$sel_anno-$sel_mese\"><img src=\"templates/$option[template]/images/icon_collapse.gif\" border=\"0\" title=\"$string[se_collapse_alt]\"></a></td>";
          $display_details=TRUE;
          }
        else $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=$enginemode&engine_details=$query_engine&domain_details=$query_domain&sort=$sort&order=$order&mese=$sel_anno-$sel_mese\"><img src=\"templates/$option[template]/images/icon_expand.gif\" border=\"0\" title=\"$string[se_expand_alt]\"></a></td>";
        $return.="\n</tr>";
      break;

      case 1:
        list($query_engine,$query_sumvisits)=$row;
        $image='images/engines.php?q='.str_replace(' ','-',$query_engine);
        $return.=
        "\n<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "\n\t<td bgcolor=$style[table_bgcolor] width=\"16\"><img src=\"$image\"></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$query_engine</span></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$query_sumvisits</b></span></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($query_sumvisits/$max_hits*100)."\" height=\"7\"> (".round($query_sumvisits*100/$total_hits,2)."%)</span></td>";
        if($query_engine===$engine_details)
          {
          $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=$enginemode&sort=$sort&order=$order&mese=$sel_anno-$sel_mese\"><img src=\"templates/$option[template]/images/icon_collapse.gif\" border=\"0\" title=\"$string[se_collapse_alt]\"></a></td>";
          $display_details=TRUE;
          }
        else $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=$enginemode&engine_details=$query_engine&sort=$sort&order=$order&mese=$sel_anno-$sel_mese\"><img src=\"templates/$option[template]/images/icon_expand.gif\" border=\"0\" title=\"$string[se_expand_alt]\"></a></td>";
        $return.="\n</tr>";
      break;

      case 2:
        list($query_domain,$query_sumvisits)=$row;
        $image='images/flags.php?q='.str_replace(' ','-',$query_domain);
        $return.=
        "\n<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "\n\t<td bgcolor=$style[table_bgcolor] width=\"16\"><img src=\"$image\"></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">".$domain_name[$query_domain]."</span></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$query_sumvisits</b></span></td>".
        "\n\t<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($query_sumvisits/$max_hits*100)."\" height=\"7\"> (".round($query_sumvisits*100/$total_hits,2)."%)</span></td>";
        if($query_domain===$domain_details)
          {
          $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=$enginemode&sort=$sort&order=$order&mese=$sel_anno-$sel_mese\"><img src=\"templates/$option[template]/images/icon_collapse.gif\" border=\"0\" title=\"$string[se_collapse_alt]\"></a></td>";
          $display_details=TRUE;
          }
        else $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=$enginemode&domain_details=$query_domain&sort=$sort&order=$order&mese=$sel_anno-$sel_mese\"><img src=\"templates/$option[template]/images/icon_expand.gif\" border=\"0\" title=\"$string[se_expand_alt]\"></a></td>";
        $return.="\n</tr>";
      break;
      }

    if(!$display_details) continue;

    $return.="\n\n<!-- QUERY DETAILS -->";
    $rec_pag=50; // risultati visualizzayi per pagina
    $query_tot_expl=sql_query("SELECT count(DISTINCT data) FROM $option[prefix]_query $clause_expl");
    list($num_totale)=mysql_fetch_row($query_tot_expl);
    $numero_pagine=ceil($num_totale/$rec_pag);
    $pagina_corrente=ceil(($start/$rec_pag)+1);
    $return.=
    "\n<tr>".
    "\n\t<td bgcolor=$style[table_bgcolor] nowrap=\"1\" colspan=\"5\">".
    "<img src=\"templates/$option[template]/images/arrow_dx_dw.gif\" border=\"0\"> <span class=\"tabletextA\">".$string['se_details']."</span>".
    "\n\t\t<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\" width=\"90%\" bgcolor=\"$style[bg_pops]\">".
    "\n\t\t<tr>".
    draw_table_title($string['se_query']).
    draw_table_title($string['se_page']).
    draw_table_title($string['se_hits']).
    "</tr>";

    $result_expl=sql_query("SELECT data,page,SUM(visits) AS dummy FROM $option[prefix]_query $clause_expl GROUP BY data,page ORDER BY dummy DESC LIMIT $start,$rec_pag");
    while($row_expl=mysql_fetch_row($result_expl))
      {
      list($query_data,$query_page,$query_sumvisits)=$row_expl;
      $query_data=stripslashes(htmlspecialchars(trim($query_data)));
      if($query_page==0) $query_page=$string['se_unknown'];
      $return.=
      "\n\t\t<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
      "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$query_data</span></td>".
      "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$query_page</span></td>".
      "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$query_sumvisits</span></td>".
      "\n\t\t</tr>";
      }
    $return.="\n\t\t<tr>\n\t\t\t<td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>";
    if($numero_pagine>1)
      {
      $return.=
      "\n\t\t<tr>\n\t\t\t<td bgcolor=$style[table_bgcolor] colspan=\"4\" height=\"15\" nowrap>".pag_bar("admin.php?action=engines&mode=$mode&enginemode=$enginemode&engine_details=$row[0]&domain_details=$query_domain&sort=$sort&order=$order&mese=$sel_anno-$sel_mese",$pagina_corrente,$numero_pagine,$rec_pag)."</td></tr>".
      "\n\t\t<tr>\n\t\t\t<td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>";
      }
    $return.=
    "\n\t\t</table><br>".
    "\n\t</td>".
    "\n</tr>".
    "\n\n<!-- END QUERY DETAILS -->";
    }
  $return.=
//  "\n<tr>\n\t<td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"5\" nowrap></td>\n</tr>".
  "\n</table>";
}
else
{
  if($mode==1) $return.=info_box($string['information'],$error['engines']);
  else
    {
    $tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$error['engines_2']);
    $return.=info_box($string['information'],$tmp);
    }
}
$return.="<br><br><center>";
if($modulo[4]==2)
  {
  if($mode==0)// SELEZIONE MESE DA VISUALIZZARE
    {
    $return.=
    "<form action='./admin.php?action=engines&enginemode=$enginemode' method='POST' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>".
    '<SELECT name=sel_mese>';
    for($i=1;$i<13;++$i) $return.="<OPTION value='$i'".($sel_mese==$i ? ' SELECTED' : '').'>'.$varie['mounts'][$i-1]."</OPTION>";
    $return.=
    '</SELECT>'.
    '<SELECT name=sel_anno>';
    $result=sql_query("SELECT min(data) FROM $option[prefix]_daily");
    $row=mysql_fetch_row($result);
    $ini_y=substr($row[0],0,4);
    if($ini_y=='') $ini_y=$anno;
    for($i=$ini_y;$i<=$anno;++$i) $return.="<OPTION value='$i'".($sel_anno==$i ? ' SELECTED' : '').">$i</OPTION>";
    $return.=
    "</SELECT>".
    "<input type=\"submit\" value=\"$string[go]\">".
    '</FORM>'.
    "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">".
    "<tr><td><span class=\"testo\"><a href=\"admin.php?action=engines&mode=1&enginemode=$enginemode\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[os_vis_glob]</a></span></td></tr>";
    }
  else
    {
    $return.=
    "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">".
    "<tr><td><span class=\"testo\"><a href=\"admin.php?action=engines&mode=0&enginemode=$enginemode\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[os_vis_mens]</a></span></td></tr>";
    }
  //$return.="<tr><td><span class=\"testo\"><a href=\"print.php?what=engines\"><img src=templates/$option[template]/images/icon_print.gif border=\"0\"> $string[printable]</a></span></td></tr>";
  $return.="</table></center>";
}
$return.="<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
if($enginemode!=0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=0&mese=$sel_anno-$sel_mese\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[se_group_engines_domains]</a></span></td></tr>";
if($enginemode!=1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=1&mese=$sel_anno-$sel_mese\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[se_group_engines]</a></span></td></tr>";
if($enginemode!=2) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=engines&mode=$mode&enginemode=2&mese=$sel_anno-$sel_mese\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[se_group_domains]</a></span></td></tr>";
$return.="</table></center>";
return($return);
}
?>