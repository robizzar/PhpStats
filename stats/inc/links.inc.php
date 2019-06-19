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
		if ($key != 'action' && $key != 'start')
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
$mese=date('m',$date);
$anno=date('Y',$date);

if(isset($_POST['sel_mese'])) $sel_mese=addslashes($_POST['sel_mese']); else $sel_mese=$mese;
if(isset($_POST['sel_anno'])) $sel_anno=addslashes($_POST['sel_anno']); else $sel_anno=$anno;
    if(isset($_GET['start'])) $start=addslashes($_GET['start']); else $start=0;


     if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else if($modulo[4]<2) $mode=0; else $mode=1;

     if(isset($_GET['mese'])) list($sel_anno,$sel_mese)=explode('-',addslashes($_GET['mese']));
     if(isset($_GET['sort'])) $sort=addslashes($_GET['sort']); else $sort=1;
    if(isset($_GET['order'])) $order=addslashes($_GET['order']); else $order=0; // Default order

 if(isset($_GET['link_int'])) $link_int=addslashes($_GET['link_int']); else $link_int=0;
 if(isset($_GET['link_ext'])) $link_ext=addslashes($_GET['link_ext']); else $link_ext=0;
/*  
$filter_number = ($_GET['link_int']=='1' ? '1' : '0') . ($_GET['link_ext']=='1' ? '1' : '0');
if($filter_number==='00')
	$filter_number='10';
*/

function links()
{
	global $db,$string,$error,$varie,$style,$option,$start,$mode,$modulo,$pref,$phpstats_title;//,$filter_number;
	global $mese,$anno,$sel_anno,$sel_mese,$sort,$order,$link_int,$link_ext;

	$return='';
	$rec_pag=50; // risultati visualizzati per pagina

	if(strlen("$sel_mese")<2)
		$sel_mese='0'.$sel_mese;

	if($mode==0)
	{
    	$clause="WHERE data LIKE '$sel_anno-$sel_mese%'";
	}
	else
	{
        $clause="WHERE data LIKE '%'";
	}

	if ($link_int && $link_ext)
		$clause .= '';													// Tutti i link
	else if ($link_int)
		$clause .= "AND url LIKE '%" .$option['server_url']. "%'"; 	// Solo link interni
	else 
	{	// Default
		$link_ext = 1;
		$clause .= "AND url NOT LIKE '%" .$option['server_url']. "%'"; // Solo link esterni
	}
		
	// Titolo pagina (riportata anche nell'admin)
	if($mode==0)
		$phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$string['links_title_2']);
	else
		$phpstats_title=$string['links_title'];

// INTESTAZIONE ("pagina X di Y")
	$query_tot=sql_query("SELECT COUNT(url) FROM $option[prefix]_links $clause GROUP BY url");

	$num_totale=mysql_num_rows($query_tot);

	$numero_pagine=ceil($num_totale/$rec_pag);
	$pagina_corrente=ceil(($start/$rec_pag)+1);
	while($row=mysql_fetch_row($query_tot))
        $total_hits+=$row[0];


	if($total_hits>0)
	{
        $return.="<span class=\"pagetitle\">$phpstats_title</span><br>\n";

        if($numero_pagine>1)
        {
        	$tmp=str_replace(Array('%current%','%total%'),Array($pagina_corrente,$numero_pagine),$varie['pag_x_y']);
            $return.="<div align=\"right\"><span class=\"testo\">$tmp&nbsp;&nbsp;</span></div>";
        }

     	$return.="<br>\n<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">\n";

        $tables=Array('url'=>'url','hits'=>'COUNT(url)');
        $modes=Array('0'=>'DESC','1'=>'ASC');
        $q_sort=(isset($tables[$sort]) ? $tables[$sort] : 'COUNT(url)');
        $q_order=(isset($modes[$order]) ? $modes[$order] : 'ASC');
        $q_append2="$q_sort $q_order";

		if ($mode==0)
		{
	        $return.=
	        "<tr>\n".
	        draw_table_title($string['links_url'],'url',"admin.php?action=links&mode=$mode&mese=$sel_anno-$sel_mese",$tables,$q_sort,$q_order)."\n".
	        draw_table_title($string['links_hits'],'hits',"admin.php?action=links&mode=$mode&mese=$sel_anno-$sel_mese",$tables,$q_sort,$q_order)."\n".
	        "</tr>\n";
	    }
	    else
		{
	        $return.=
	        "<tr>\n".
	        draw_table_title($string['links_url'],'url',"admin.php?action=links&mode=$mode&link_int=$link_int&link_ext=$link_ext",$tables,$q_sort,$q_order)."\n".
	        draw_table_title($string['links_hits'],'hits',"admin.php?action=links&mode=$mode&link_int=$link_int&link_ext=$link_ext",$tables,$q_sort,$q_order)."\n".
	        "</tr>\n";
	    }


        $result=sql_query("SELECT url,COUNT(url) FROM $option[prefix]_links $clause GROUP BY url ORDER BY $q_append2 LIMIT $start,$rec_pag");
        while($row=mysql_fetch_row($result))
        {
        	list($links_url,$links_hits)=$row;
//        	$links_url = stripslashes($links_url);
			//$links_url=formaturl($links_url, '', );
			$links_url = formaturl($links_url, '', 200, 200, 0, '', 0);


            $return.="<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">\n".
            " <td bgcolor=$style[table_bgcolor] align=\"left\"><span class=\"tabletextA\">$links_url</span></td>\n".
            " <td bgcolor=$style[table_bgcolor] align=\"center\"><span class=\"tabletextA\">$links_hits</span></td>\n".
            "</tr>\n\n";
        }

        $mode-=0;
        switch($mode)
        {
                case 0:
                $colspan=6;
                $new_mode=1;
                break;

                case 1:
                $colspan=6;
                $new_mode=0;
                break;
        }
//        $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"$colspan\" nowrap></td></tr>";

        if($numero_pagine>1)
        {
        	if ($mode == 0)
        	{
                $return.=
                "<tr>\n<td bgcolor=$style[table_bgcolor] colspan=\"$colspan\" height=\"20\" nowrap>".
                pag_bar("admin.php?action=links&mode=$mode&mese=$sel_anno-$sel_mese&sort=$sort&order=$order",$pagina_corrente,$numero_pagine,$rec_pag).
                "</td>\n</tr>\n";
            }
            else
        	{
                $return.=
                "<tr>\n<td bgcolor=$style[table_bgcolor] colspan=\"$colspan\" height=\"20\" nowrap>".
                pag_bar("admin.php?action=links&mode=$mode&link_int=$link_int&link_ext=$link_ext&sort=$sort&order=$order",$pagina_corrente,$numero_pagine,$rec_pag).
                "</td>\n</tr>\n";
            }
        }
        $return.=
        "</td>\n</tr>\n".
        "</table><br>\n";
	}
	else
	{
        // Controllo se provengo da una ricerca o se proprio non ci sono dati!
        if($mode===1)
            	$return.=info_box($string['information'],$error['links']);
        else
	        {
                $tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$error['links']);
                $return.=info_box($string['information'],$tmp);
            }
	}

// INIZIO FORM
	$return.=
	"<br><center>".
	"<form action='./admin.php?action=links&mode=$mode' method='GET' name=form1>";
	if($modulo[4]==2)
	{
        switch($mode)
        {
                case 0: $new_mode1=1; break;
                case 1: $new_mode1=0; break;
        }
        if($mode==0)
        {
                // SELEZIONE MESE DA VISUALIZZARE
                $return.="&nbsp;<span class=\"tabletextA\">$string[calendar_view]</span><SELECT name=sel_mese>";
                for($i=1;$i<13;++$i)
                	$return.="<OPTION value='$i'".($sel_mese==$i ? ' SELECTED' : '').'>'.$varie['mounts'][$i-1]."</OPTION>";
                $return.='</SELECT>';

                $result=sql_query("SELECT min(data) FROM $option[prefix]_daily");
                $row=mysql_fetch_row($result);
                $ini_y=substr($row[0],0,4);
                if($ini_y=='')
                	$ini_y=$anno;
                $return.='<SELECT name=sel_anno>';
                for($i=$ini_y;$i<=$anno;++$i)
                	$return.="<OPTION value='$i'".($sel_anno==$i ? ' SELECTED' : '').">$i</OPTION>";
                $return.='</SELECT>';

                $return.=
                "&nbsp;<br><input type=\"submit\" value=\"$string[go]\">".
//                "</FORM>".
                "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n".
                "<tr>\n<td><span class=\"testo\"><a href=\"admin.php?action=links&mode=$new_mode1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[links_vis_glob]</a></span></td>\n</tr></table>";
        }
        else
        {
                $return.=

/*** temporaneamente eliminata suddivisione mensile
                "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n".
                "<tr>\n<td><span class=\"testo\"><a href=\"admin.php?action=links&mode=$new_mode1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[links_vis_mens]</a></span></td>\n</tr></table>".
***/

        "<input type=\"hidden\" name=\"action\" value=\"links\">".
        "<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
        "<br><table border=\"0\" $style[table_header] width=\"416\" align=\"center\">".
        "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[rf_title]</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center>".

        "<input type=\"checkbox\" name=\"link_ext\" value=\"1\"".($link_ext==1 ? ' checked' : '').">&nbsp;<span class='testo'>Link esterni&nbsp;&nbsp;&nbsp;&nbsp;".
        "<input type=\"checkbox\" name=\"link_int\" value=\"1\"".($link_int==1 ? ' checked' : '').">&nbsp;<span class='testo'>Link interni".

        "<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center><input type=\"submit\" value=\"$string[rf_submit]\"></center></span></td></tr>".
        "</table>";


        }
        $return.="</center></form>";
	}
	return($return);
}
?>
