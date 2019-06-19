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
		if ($key != 'action' && $key != 'start' && $key != 'query_details' && $key != 'engine_details' && $key != 'domain_details')
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

/*** ORIGINALE
     if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else if($modulo[4]<2) $mode=1; else $mode=0;
***/
     if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else if($modulo[4]<2) $mode=0; else $mode=3;

     if(isset($_GET['mese'])) list($sel_anno,$sel_mese)=explode('-',addslashes($_GET['mese']));
     if(isset($_GET['sort'])) $sort=addslashes($_GET['sort']); else $sort=1;
    if(isset($_GET['order'])) $order=addslashes($_GET['order']); else $order=0; // Default order
        if(isset($_GET['q'])) $q=addslashes($_GET['q']); else { if(isset($_POST['q'])) $q=addslashes($_POST['q']); else $q=''; }
if(isset($_GET['query_details'])) $query_details=urldecode(str_replace('&amp;','&',$_GET['query_details'])); else $query_details='';
if(isset($_GET['engine_details'])) $engine_details=urldecode(str_replace('&amp;','&',$_GET['engine_details'])); else $engine_details='';
if(isset($_GET['domain_details'])) $domain_details=urldecode(str_replace('&amp;','&',$_GET['domain_details'])); else $domain_details='';

function query() {
global $db,$string,$error,$varie,$style,$option,$start,$mode,$modulo,$q,$pref,$phpstats_title;
global $mese,$anno,$sel_anno,$sel_mese,$sort,$order,$query_details,$engine_details,$domain_details;

include("lang/$option[language]/domains_lang.php");

$return='';
$rec_pag=50; // risultati visualizzati per pagina
$max_hits=$total_hits=0;
if(strlen("$sel_mese")<2) $sel_mese='0'.$sel_mese;

$query_details_parse=addslashes(function_exists(html_entity_decode) ? html_entity_decode($query_details) : strtr($query_details,array_flip(get_html_translation_table(HTML_ENTITIES))));
if($mode==0){
        if($q==''){
                $clause="WHERE mese='$sel_anno-$sel_mese'";
                $clause_expl="WHERE data='$query_details_parse' AND engine='$engine_details' AND domain='$domain_details' AND mese='$sel_anno-$sel_mese'";
        }
        else{
                $clause="WHERE mese='$sel_anno-$sel_mese' AND data LIKE '%$q%'";
                $clause_expl="WHERE data='$query_details_parse' AND engine='$engine_details' AND domain='$domain_details' AND mese='$sel_anno-$sel_mese' AND data LIKE '%$q%'";
        }
}
else if($mode==2){
        if($q==''){
                $clause="WHERE mese='$sel_anno-$sel_mese'";
                $clause_expl="WHERE data='$query_details_parse' AND mese='$sel_anno-$sel_mese'";
        }
        else{
                $clause="WHERE mese='$sel_anno-$sel_mese' AND data LIKE '%$q%'";
                $clause_expl="WHERE data='$query_details_parse' AND mese='$sel_anno-$sel_mese' AND data LIKE '%$q%'";
        }
}
else{
        if($q==''){
                $clause='';
                $clause_expl="WHERE data='$query_details_parse'";
        }
        else{
                $clause="WHERE data LIKE '%$q%'";
                $clause_expl="WHERE data='$query_details_parse' AND data LIKE '%$q%'";
        }
}

// Titolo pagina (riportata anche nell'admin)
if($mode==0 || $mode==2) $phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$string['query_title_2']);
else $phpstats_title=$string['query_title'];
// INTESTAZIONE ("pagina X di Y")
if($mode==0 || $mode==1) $query_tot=sql_query("SELECT SUM(visits) FROM $option[prefix]_query $clause GROUP BY data,engine,domain");
else $query_tot=sql_query("SELECT SUM(visits),data FROM $option[prefix]_query $clause GROUP BY data");
$num_totale=mysql_num_rows($query_tot);

$numero_pagine=ceil($num_totale/$rec_pag);
$pagina_corrente=ceil(($start/$rec_pag)+1);
while($row=mysql_fetch_row($query_tot)){
        if($row[0]>$max_hits) $max_hits=$row[0];
        $total_hits+=$row[0];
}
if($total_hits>0){
        $return.="<span class=\"pagetitle\">$phpstats_title</span><br>";
        if($q!=''){
                $string['pages_results']=str_replace(Array('%query%','%trovati%','%hits%'),Array($q,$num_totale,$total_hits),$string['pages_results']);
                $return.="<br>$string[pages_results]<br>";
        }
        if($numero_pagine>1){
                $tmp=str_replace(Array('%current%','%total%'),Array($pagina_corrente,$numero_pagine),$varie['pag_x_y']);
                $return.="<div align=\"right\"><span class=\"testo\">$tmp&nbsp;&nbsp;</span></div>";
        }
        $return.="<br>\n<table $style[table_header] width=\"90%\" class=\"tableborder\">";
        if($mode==0 || $mode==1){
                /////////////////////////////////
                // MODALITA' DIVISA PER MOTORE //
                /////////////////////////////////
                $tables=array('query'=>'data','hits'=>'dummy','motore'=>'engine');
                $modes=array('0'=>'DESC','1'=>'ASC');
                if(isset($tables[$sort])) $q_sort=$tables[$sort]; else $q_sort='dummy';
                if(isset($modes[$order])) $q_order=$modes[$order]; else $q_order='DESC';
                $q_append2="$q_sort $q_order";
                $return.=
                "<tr>".
                draw_table_title('').
                draw_table_title($string['query'],'query',"admin.php?action=query&mode=$mode&mese=$sel_anno-$sel_mese&q=$q",$tables,$q_sort,$q_order).
                draw_table_title($string['query_hits'],'hits',"admin.php?action=query&mode=$mode&mese=$sel_anno-$sel_mese&q=$q",$tables,$q_sort,$q_order).
                draw_table_title($string['query_engine'],'motore',"admin.php?action=query&mode=$mode&mese=$sel_anno-$sel_mese&q=$q",$tables,$q_sort,$q_order).
                ($mode==0 ? draw_table_title('') : '').
                draw_table_title('').
                "</tr>";

                if($mode==0){
                        // MEMORIZZO LE QUERY DEL MESE PRECEDENTE PER I CONFRONTI
                        $mese_prec=date('Y-m',mktime(0,0,0,$sel_mese-1,1,$sel_anno));
                        $result=sql_query("SELECT data,engine,domain,SUM(visits) as dummy FROM $option[prefix]_query WHERE mese='$mese_prec' GROUP BY data,engine,domain");
                        while($row=mysql_fetch_row($result)){
                                list($query_data,$query_engine,$query_domain,$query_sumvisits)=$row;
                                $query_data=htmlspecialchars($query_data);
                                $query[$query_data.'|'.$query_engine.'|'.$query_domain]=$query_sumvisits;
                        }
                }
                $result=sql_query("SELECT data,engine,domain,SUM(visits) as dummy FROM $option[prefix]_query $clause GROUP BY engine,domain,data ORDER by $q_append2, 'date' DESC LIMIT $start,$rec_pag");
                while($row=mysql_fetch_row($result)){
                        list($query_data,$query_engine,$query_domain,$query_sumvisits)=$row;
                        $query_data=htmlspecialchars($query_data);
/*** */
$query_data=utf8_decode($query_data);

                        $image='images/engines.php?q='.str_replace(' ','-',$query_engine);
                        $return.="<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">";
                        if($query_data===$query_details && $query_engine===$engine_details && $query_domain===$domain_details) $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=query&mode=$mode&sort=$sort&order=$order&mese=$sel_anno-$sel_mese&start=$start&q=$q\"><img src=\"templates/$option[template]/images/icon_collapse.gif\" border=\"0\" title=\"$string[query_collapse_alt]\"></a></td>";
                        else $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=query&mode=$mode&query_details=".urlencode(str_replace('&','&amp;',$query_data))."&engine_details=$query_engine&domain_details=$query_domain&sort=$sort&order=$order&mese=$sel_anno-$sel_mese&start=$start&q=$q\"><img src=\"templates/$option[template]/images/icon_expand.gif\" border=\"0\" title=\"$string[query_expand_alt]\"></a></td>";
                        $return.=
                        "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$query_data</span></td><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$query_sumvisits</b></span></td>".
                        "<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"$image\" align=\"absmiddle\"> $query_engine (".@$domain_name[$query_domain].")</span></td>".
                        "<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($query_sumvisits/$max_hits*100)."\" height=\"7\"> (".round($query_sumvisits*100/$total_hits,2)."%)</span></td>";

                        if($mode==0){
                                if(isset($query[$query_data.'|'.$query_engine.'|'.$query_domain])){
                                        $prec=$query[$query_data.'|'.$query_engine.'|'.$query_domain];
                                        $variazione=round(($query_sumvisits-$prec)/$prec*100,1);
                                        if($variazione<-15) $level='1';
                                        else if($variazione<-5) $level='2';
                                        else if($variazione<5) $level='3';
                                        else if($variazione<15) $level='4';
                                        else $level='5';
                                        if($variazione>0) $variazione='+'.$variazione;
                                        $variazione.=' %';
                                        $alt_img=str_replace('%HITS%',$prec,$string['query_last_m']);
                                        $alt_img.="\n".str_replace('%VARIAZIONE%',$variazione,$string['query_last_v']);
                                        $img="templates/$option[template]/images/icon_level_{$level}.gif";
                                        $return.="<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"$img\" title=\"$alt_img\"></span></td>";
                                }
                                else $return.="<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/icon_level_new.gif\" title=\"\"></span></td>";
                        }
                        $return.="</tr>";
                        if(addslashes($query_data)===$query_details && $query_engine===$engine_details && $query_domain===$domain_details){
                                $return.=
                                "\n\n<!-- QUERY DETAILS -->".
                                "\n<tr>".
                                "\n\t<td bgcolor=$style[table_bgcolor] nowrap=\"1\" colspan=\"6\">".
                                "<img src=\"templates/$option[template]/images/arrow_dx_dw.gif\" border=\"0\"> <span class=\"tabletextA\">".$string['query_details']."</span>".
                                "\n\t\t<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\" width=\"90%\" bgcolor=\"$style[bg_pops]\">".
                                "\n\t\t<tr>".
                                draw_table_title($string['se_query']).
                                draw_table_title($string['se_page']).
                                draw_table_title($string['se_hits']).
                                "</tr>";
                                $result_expl=sql_query("SELECT data,page,SUM(visits) AS dummy FROM $option[prefix]_query $clause_expl GROUP BY engine,domain,page ORDER BY dummy DESC");
                                while($row_expl=mysql_fetch_row($result_expl)){
                                        list($expl_data,$expl_page,$expl_sumvisits)=$row_expl;
                                        $return.=
                                        "\n\t\t<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                                        "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$expl_data</span></td>".
                                        "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".($expl_page>0 ? $expl_page : $string['se_unknown'])."</span></td>".
                                        "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$expl_sumvisits</span></td>".
                                        "\n\t\t</tr>";
                                }
                                $return.=
                                "\n\t\t<tr>\n\t\t\t<td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>".
                                "\n\t\t</table><br>".
                                "\n\t</td>".
                                "\n</tr>".
                                "\n\n<!-- END QUERY DETAILS -->";
                        }
                }
        }
        else{
                /////////////////////////////////////
                // MODALITA' NON DIVISA PER MOTORE //
                /////////////////////////////////////
                $tables=Array('query'=>'data','hits'=>'dummy');
                $modes=Array('0'=>'DESC','1'=>'ASC');
                $q_sort=(isset($tables[$sort]) ? $tables[$sort] : 'dummy');
                $q_order=(isset($modes[$order]) ? $modes[$order] : 'DESC');
                $q_append2="$q_sort $q_order";
                $return.=
                "<tr>".
                draw_table_title('').
                draw_table_title($string['query'],'query',"admin.php?action=query&mode=$mode&mese=$sel_anno-$sel_mese&q=$q",$tables,$q_sort,$q_order).
                draw_table_title($string['query_hits'],'hits',"admin.php?action=query&mode=$mode&mese=$sel_anno-$sel_mese&q=$q",$tables,$q_sort,$q_order).
                draw_table_title("").
                ($mode==2 ? draw_table_title('') : '').
                "</tr>";

                if($mode==2){
                        // MEMORIZZO LE QUERY DEL MESE PRECEDENTE PER I CONFRONTI
                        $mese_prec=date('Y-m',mktime(0,0,0,$sel_mese-1,1,$sel_anno));
                        $result=sql_query("SELECT data,SUM(visits) as dummy FROM $option[prefix]_query WHERE mese='$mese_prec' GROUP BY data");
                        while($row=mysql_fetch_row($result)) $query[$row[0]]=$row[1];
                }

                $result=sql_query("SELECT data,SUM(visits) AS dummy ,date FROM $option[prefix]_query $clause GROUP BY data ORDER BY $q_append2 LIMIT $start,$rec_pag");
                while($row=mysql_fetch_row($result)){
                        list($query_data,$query_sumvisits,$query_date)=$row;
	$query_data=utf8_decode($query_data);
                        $return.="<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">";
                        if($query_data===$query_details) $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=query&mode=$mode&sort=$sort&order=$order&mese=$sel_anno-$sel_mese&start=$start&q=$q\"><img src=\"templates/$option[template]/images/icon_collapse.gif\" border=\"0\" title=\"$string[query_collapse_alt]\"></a></td>";
                        else $return.="\n\t<td bgcolor=$style[table_bgcolor] align=\"right\" valign=\"middle\" width=\"16\"><a href=\"admin.php?action=query&mode=$mode&query_details=".urlencode(str_replace('&','&amp;',$query_data))."&sort=$sort&order=$order&mese=$sel_anno-$sel_mese&start=$start&q=$q\"><img src=\"templates/$option[template]/images/icon_expand.gif\" border=\"0\" title=\"$string[query_expand_alt]\"></a></td>";
                        $return.=
                        "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$query_data</span></td>".
                        "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$query_sumvisits</b></span></td>".
                        "<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($query_sumvisits/$max_hits*100)."\" height=\"7\"> (".round($query_sumvisits*100/$total_hits,2)."%)</span></td>";

                        if($mode==2){
                                if(isset($query[$query_data])){
                                        $prec=$query[$query_data];
                                        $variazione=round(($query_sumvisits-$prec)/$query_sumvisits*100,1);
                                        if($variazione<-15)  $level='1';
                                        else if($variazione<-5) $level='2';
                                        else if($variazione<5) $level='3';
                                        else if($variazione<15) $level='4';
                                        else $level='5';
                                        if($variazione>0) $variazione='+'.$variazione;
                                        $variazione.=' %';
                                        $alt_img=str_replace('%HITS%',$prec,$string['query_last_m']);
                                        $alt_img.="\n".str_replace('%VARIAZIONE%',$variazione,$string['query_last_v']);
                                        $img="templates/$option[template]/images/icon_level_{$level}.gif";
                                        $return.="<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"$img\" title=\"$alt_img\"></span></td>";
                                }
                                else $return.="<td bgcolor=$style[table_bgcolor] nowrap=\"1\"><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/icon_level_new.gif\"></span></td>";
                        }

                        $return.="\n</tr>";

                        if(addslashes($query_data)===$query_details){
                                $return.=
                                "\n\n<!-- QUERY DETAILS -->".
                                "\n<tr>".
                                "\n\t<td bgcolor=$style[table_bgcolor] nowrap=\"1\" colspan=\"5\">".
                                "<img src=\"templates/$option[template]/images/arrow_dx_dw.gif\" border=\"0\"> <span class=\"tabletextA\">".$string['query_details']."</span>".
                                "\n\t\t<table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\" width=\"90%\" bgcolor=\"$style[bg_pops]\">".
                                "\n\t\t<tr>".
                                draw_table_title('').
                                draw_table_title($string['query_engine']).
                                draw_table_title($string['se_page']).
                                draw_table_title($string['se_hits']).
                                "</tr>";
                                $result_expl=sql_query("SELECT engine,domain,page,SUM(visits) AS dummy FROM $option[prefix]_query $clause_expl GROUP BY engine,domain,page ORDER BY dummy DESC");
                                while($row_expl=mysql_fetch_row($result_expl)){
                                        list($expl_engine,$expl_domain,$expl_page,$expl_sumvisits)=$row_expl;
                                        $expl_engine=htmlspecialchars($expl_engine);
                                        $image='images/engines.php?q='.str_replace(' ','-',$expl_engine);
                                        $return.=
                                        "\n\t\t<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                                        "\n\t\t\t<td bgcolor=$style[table_bgcolor] width=\"16\"><span class=\"tabletextA\"><img src=\"".$image."\"></span></td>".
                                        "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$expl_engine (".$domain_name[$expl_domain].")</span></td>".
                                        "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".($expl_page>0 ? $expl_page : $string['se_unknown'])."</span></td>".
                                        "\n\t\t\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$expl_sumvisits</span></td>".
                                        "\n\t\t</tr>";
                                }
                                $return.=
                                "\n\t\t<tr>\n\t\t\t<td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"5\" nowrap></td></tr>".
                                "\n\t\t</table><br>".
                                "\n\t</td>".
                                "\n</tr>".
                                "\n\n<!-- END QUERY DETAILS -->";
                        }
                        $return.="</tr>";
                }
        }
        $mode-=0;
        switch($mode){
                case 0:
                $colspan=6;
                $tipo=$string['query_mode_2'];
                $new_mode=2;
                //$print_url="print.php?what=query-mens&mese=$sel_anno-$sel_mese";
                break;

                case 1:
                $colspan=5;
                $tipo=$string['query_mode_2'];
                $new_mode=3;
                //$print_url="print.php?what=query&mese=$sel_anno-$sel_mese";
                break;

                case 2:
                $colspan=5;
                $tipo=$string['query_mode_1'];
                $new_mode=0;
                //$print_url="print.php?what=query-tot-mens&mese=$sel_anno-$sel_mese";
                break;

                default:
                case 3:
                $colspan=4;
                $tipo=$string['query_mode_1'];
                $new_mode=1;
                //$print_url="print.php?what=query-tot&mese=$sel_anno-$sel_mese";
                break;
        }
        $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"$colspan\" nowrap></td></tr>";
        if($numero_pagine>1){
                $return.=
                "<tr><td bgcolor=$style[table_bgcolor] colspan=\"$colspan\" height=\"20\" nowrap>".
                pag_bar("admin.php?action=query&mode=$mode&mese=$sel_anno-$sel_mese&sort=$sort&order=$order&q=$q",$pagina_corrente,$numero_pagine,$rec_pag).
                "</td></tr>";
//                "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"$colspan\" nowrap></td></tr>";
        }
        $return.=
        "</td></tr>".
        "</table><br>";
}
else{
        // Controllo se provengo da una ricerca o se proprio non ci sono dati!
        if($q!=''){
                $body="$string[no_pages]<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
                $return.=info_box($string['information'],$body);
        }
        else{
                if($mode===1 || $mode===3) $return.=info_box($string['information'],$error['query']);
                else{
                        $tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$error['query_2']);
                        $return.=info_box($string['information'],$tmp);
                }
        }
}
// INIZIO FORM
$return.=
"<br><center>".
"<form action='./admin.php?action=query&mode=$mode' method='POST' name=form1>".
"<span class=\"testo\">$string[search]:". // Box di ricerca
"<input name=\"q\" type=\"text\" size=\"30\" maxlength=\"50\" value=\"$q\">";
if($modulo[4]==2){
        switch($mode){
                case 0: $new_mode1=1; break;
                case 1: $new_mode1=0; break;
                case 2: $new_mode1=3; break;
                case 3: $new_mode1=2; break;
        }
        if($mode==0 || $mode==2){
                // SELEZIONE MESE DA VISUALIZZARE
                $return.="&nbsp;<span class=\"tabletextA\">$string[calendar_view]</span><SELECT name=sel_mese>";
                for($i=1;$i<13;++$i) $return.="<OPTION value='$i'".($sel_mese==$i ? ' SELECTED' : '').'>'.$varie['mounts'][$i-1]."</OPTION>";
                $return.='</SELECT>';

                $result=sql_query("SELECT min(data) FROM $option[prefix]_daily");
                $row=mysql_fetch_row($result);
                $ini_y=substr($row[0],0,4);
                if($ini_y=='') $ini_y=$anno;
                $return.='<SELECT name=sel_anno>';
                for($i=$ini_y;$i<=$anno;++$i) $return.="<OPTION value='$i'".($sel_anno==$i ? ' SELECTED' : '').">$i</OPTION>";
                $return.='</SELECT>';

                $return.=
                "&nbsp;<br><input type=\"submit\" value=\"$string[go]\">".
                "</FORM>".
                "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">".
                "<tr><td><span class=\"testo\"><a href=\"admin.php?action=query&mode=$new_mode1&q=$q\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[query_vis_glob]</a></span></td></tr>";
        }
        else{
                $return.=
                "<input type=\"submit\" value=\"$string[go]\">".
                "</FORM>".
                "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">".
                "<tr><td><span class=\"testo\"><a href=\"admin.php?action=query&mode=$new_mode1&q=$q\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[query_vis_mens]</a></span></td></tr>";
        }
        if($total_hits>0){
                $return.=
                "<tr><td><span class=\"testo\"><a href=\"admin.php?action=query&mode=$new_mode&mese=$sel_anno-$sel_mese&q=$q\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $tipo</a></span></td></tr>";
//                "<tr><td><span class=\"testo\"><a href=\"$print_url\"><img src=templates/$option[template]/images/icon_print.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[printable]</a></span></td></tr>";
        }
        $return.="</table></center>";
}
else{
        if($total_hits>0) {
                $return.=
                "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">".
                "<tr><td><span class=\"testo\"><a href=\"admin.php?action=query&mode=$new_mode&mese=$sel_anno-$sel_mese&q=$q\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $tipo</a></span></td></tr>".
               // "<tr><td><span class=\"testo\"><a href=\"$print_url\"><img src=templates/$option[template]/images/icon_print.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[printable]</a></span></td></tr>".
                "</table>";
        }
}
return($return);
}
?>