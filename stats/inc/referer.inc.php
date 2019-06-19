<?php
// <!-- SECURITY ISSUES -->
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

// <!-- INCOMING DATA PROCESSING -->
$date=time()-$option['timezone']*3600;
list($month,$year)=explode('-',date('m-Y',$date));

$selected_month=(isset($_GET['selected_month']) ? str_pad((int)$_GET['selected_month'],2,'0',STR_PAD_LEFT) : $month);
$selected_year=(isset($_GET['selected_year']) ? (int)$_GET['selected_year'] : $year);
$start=(isset($_GET['start']) ? (int)$_GET['start'] : 0);
$sort=(isset($_GET['sort']) ? addslashes($_GET['sort']) : 'visits'); // Default sort
$order=(isset($_GET['order']) ? addslashes($_GET['order']) : 'DESC'); // Default order
if(isset($_GET['q'])) $q=addslashes($_GET['q']);
else if(isset($_POST['q'])) $q=addslashes($_POST['q']);
else $q='';
/*** ORIGINALE
$mode=(isset($_GET['mode']) ? (int)$_GET['mode'] : ($modulo[4]<2 ? 1 : 0));
***/
$mode=(isset($_GET['mode']) ? (int)$_GET['mode'] : 1);

$group=(isset($_GET['group']) ? (int)$_GET['group'] : 1);
$delpage=(isset($_GET['delpage']) ? $_GET['delpage'] : '');

// <!-- PAGE FUNCTION -->
function referer() {
global $db,$string,$error,$varie,$style,$option,$start,$q,$pref,$sort,$order,$group,$mode,$selected_year,$selected_month,$phpstats_title,$delpage;
$return='';

// Page delete if needed
do{
        if($delpage=='' || !user_is_logged_in()) break;
        $$delpage=rawurldecode($delpage);
        $delpage2=rawurldecode($delpage);
        $delpage3=urldecode($delpage);
        $delValuepage=Array($delpage,addslashes($delpage),stripslashes($delpage),$delpage2,addslashes($delpage2),stripslashes($delpage2),$delpage3,addslashes($delpage3),stripslashes($delpage3));
        for($i=0,$tot=count($delValuepage);$i<$tot;++$i){
                sql_query("DELETE FROM $option[prefix]_pages WHERE data='$delValuepage[$i]' LIMIT 1");
                if(mysql_affected_rows()>0) break(2);
        }
}while(FALSE);


// <!-- DATA ACQUISITION -->

$dateFilter=($mode===0 ? "WHERE mese='$selected_year-$selected_month'" : '');
if(empty($q)) $qFilter='';
else $qFilter=($mode===0 ? "AND data LIKE '%$q%'" : "WHERE data LIKE '%$q%'");

$tables=Array('pagina'=>'data','visits'=>'sumvisits','date'=>'date');
$modes=Array('0'=>'DESC','1'=>'ASC');
$sortBy=(isset($tables[$sort]) ? $tables[$sort] : 'sumvisits');
$orderBy=(isset($modes[$order]) ? $modes[$order] : 'DESC');

$whatToSearch=($group===0 ? 'data' : 'SUBSTRING_INDEX(data,\'/\',3)');

do{
        $dataFound=FALSE;

        $recordPerPage=100;

        $res=sql_query("SELECT count(DISTINCT($whatToSearch)) FROM $option[prefix]_referer $dateFilter $qFilter");
        list($totalEntries)=mysql_fetch_row($res);
        $totalEntries=(int)$totalEntries;
        if($totalEntries===0) break;

        $res=sql_query("SELECT min(data) FROM $option[prefix]_daily");
        if(mysql_num_rows($res)<1) break;
        list($min_date)=mysql_fetch_row($res);
        $min_date=(int)substr($min_date,0,4);

        $pageNumber=ceil($totalEntries/$recordPerPage);
        $currentPage=ceil(($start/$recordPerPage)+1);

        $data_referer=Array();

        $res=sql_query("SELECT $whatToSearch as dom,SUM(visits) as sumvisits,MAX(date) as date FROM $option[prefix]_referer $dateFilter $qFilter GROUP BY dom ORDER BY $sortBy $orderBy LIMIT $start,$recordPerPage");

        $rowNumber=mysql_num_rows($res);
        if($rowNumber<1) break;
        while($row=mysql_fetch_row($res)){
                list($referer_dom,$referer_sumvisits,$referer_date)=$row;
                $data_referer[]=Array($referer_dom,(int)$referer_sumvisits,$referer_date);
        }
        $dataFound=TRUE;
}while(FALSE);


// <!-- DATA PROCESSING -->
if($dataFound && !empty($q)){
        $qHits=0;
        for($i=0,$tot=count($data_referer);$i<$tot;++$i) $qHits+=$data_referer[$i][1];
}
//no more needed



// <!-- PRE-OUTPUT PROCESSING -->
if($dataFound){
        $output_referer=Array();
        for($i=0,$tot=count($data_referer);$i<$tot;++$i){
                list($dom,$sumvisits,$date)=$data_referer[$i];

                $rawUrl=urlencode($dom);
                $url=formaturl(htmlspecialchars($dom),'',45,45,0);
                $dateDate=formatdate($date,3);
                $dateTime=formattime($date);

                $output_referer[]=Array($rawUrl,$url,$sumvisits,$dateDate,$dateTime);
        }
        unset($data_referer);
}



// <!-- OUTPUT CREATION -->
$return='';

switch($group){
        case 0: $phpstats_title=($mode===0 ? str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['refers_title_2']) : $string['refers_title']); break;
        case 1: $phpstats_title=($mode===0 ? str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['refers_group_title_2']) : $string['refers_group_title']); break;
}

$return.=
"\n<script>\n".
"function popup(url) {\n".
"test=window.open(url,'nome','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=360,HEIGHT=480,LEFT=0,TOP=0');\n".
"}\n".
"</script>\n";

if($dataFound){
        $return.="<span class=\"pagetitle\">$phpstats_title</span><br>";
        if(!empty($q)) $return.='<br>'.str_replace(Array('%query%','%trovati%','%hits%'),Array($q,$rowNumber,$qHits),$string['pages_results']).'<br>';

        if($pageNumber>1){
                $tmp=str_replace(Array('%current%','%total%'),Array($currentPage,$pageNumber),$varie['pag_x_y']);
                $return.="<div align=\"right\"><span class=\"testo\">$tmp&nbsp;&nbsp;</span></div>";
        }



        $return.=
        "<br><table $style[table_header] width=\"95%\" align=\"center\" class=\"tableborder\"><tr>".
        draw_table_title($string['refers_url'],'pagina',"admin.php?action=referer&q=$q&group=$group&mode=$mode&selected_year=$selected_year&selected_month=$selected_month",$tables,$sortBy,$orderBy).
        draw_table_title($string['refers_date'],'date',"admin.php?action=referer&q=$q&group=$group&mode=$mode&selected_year=$selected_year&selected_month=$selected_month",$tables,$sortBy,$orderBy).
        draw_table_title($string['refers_hits'],'visits',"admin.php?action=referer&q=$q&group=$group&mode=$mode&selected_year=$selected_year&selected_month=$selected_month",$tables,$sortBy,$orderBy).
        draw_table_title($string['refers_tracking']).
        ($mode===0 ? draw_table_title($string['refers_delete']) : '').
        '</tr>';

        for($i=0,$tot=count($output_referer);$i<$tot;++$i){
                list($rawUrl,$url,$sumvisits,$dateDate,$dateTime)=$output_referer[$i];

                $return.=
                "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$url</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\" nowrap><span class=\"tabletextA\">$dateDate - $dateTime</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\" nowrap><span class=\"tabletextA\"><b>$sumvisits</b></span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\" nowrap><span class=\"tabletextA\"><a href=\"javascript:popup('tracking.php?what=referer&page=$rawUrl&selected_year=$selected_year&selected_month=$selected_month');\"><img src=\"templates/$option[template]/images/icon_tracking.gif\" border=0 title=\"$string[refers_alt_1]\"></a></td>".
                ($mode===0 ? "<td bgcolor=$style[table_bgcolor] width=\"11\"><a href=\"admin.php?action=referer&selected_year=$selected_year&selected_month=$selected_month&q=$q&sort=$sort&order=$order&group=$group&mode=$mode&start=$start&delpage=$rawUrl\" onclick=\"return confirmLink(this,'$string[refers_delete_confirm]')\"><img src=\"templates/$option[template]/images/icon_delete.gif\" title=\"$string[refers_delete_alt]\" border=0></a></td></tr>" : '').
                '</tr>';
        }

//        $return.= "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"5\" nowrap></td></tr>";

        if($pageNumber>1){
                $return.=
                "<tr><td bgcolor=$style[table_bgcolor] colspan=\"5\" height=\"20\" nowrap>".
                pag_bar("admin.php?action=referer&selected_year=$selected_year&selected_month=$selected_month&q=$q&sort=$sort&order=$order&group=$group&mode=$mode",$currentPage,$pageNumber,$recordPerPage);
                "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"5\" nowrap></td></tr>";
        }
        $return.='</table><br><center><table>';

        if($mode===0){
                $return.=
                "<tr><td colspan=\"2\"><span class=\"testo\">".
                "<form action='./admin.php' method='GET' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>".
                "<input type=\"hidden\" name=\"action\" value=\"referer\">".
                "<input type=\"hidden\" name=\"q\" value=\"$q\">".
                "<input type=\"hidden\" name=\"group\" value=\"$group\">".
                "<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
                "<input type=\"hidden\" name=\"start\" value=\"$start\">".
                "<input type=\"hidden\" name=\"sort\" value=\"$sort\">".
                "<input type=\"hidden\" name=\"order\" value=\"$order\">".
                "<SELECT name=selected_month>";
                for($i=1;$i<13;++$i) $return.="<OPTION value='$i'".($selected_month==$i ? ' SELECTED' : '').'>'.$varie['mounts'][$i-1].'</OPTION>';
                $return.=
                '</SELECT>'.
                '<SELECT name=selected_year>';
                $year=(int)date('Y',time()-$option['timezone']*3600);
                if(!isSet($min_date)) $min_date=$year;
                for($i=$min_date;$i<=$year;++$i) $return.="<OPTION value='$i'".($selected_year==$i ? ' SELECTED' : '').">$i</OPTION>";
                $return.=
                "</SELECT>".
                "<input type=\"submit\" value=\"$string[go]\">".
                '</FORM>'.
                '</td></tr>';
        }

        if($mode!==0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=referer&group=$group&mode=0\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[os_vis_mens]</a></span></td></tr>";
        if($mode!==1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=referer&group=$group&mode=1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[os_vis_glob]</a></span></td></tr>";
        if($group!==0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=referer&q=$q&sort=$sort&order=$order&group=0&mode=$mode&selected_year=$selected_year&selected_month=$selected_month\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[refers_mode_0]</a></span></td></tr>";
        if($group!==1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=referer&q=$q&sort=$sort&order=$order&group=1&mode=$mode&selected_year=$selected_year&selected_month=$selected_month\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[refers_mode_1]</a></span></td></tr>";

        $return.="</table></center>";
}
else{
        if(!empty($q)){
                $body="$string[no_pages]<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
                $return.=info_box($string['information'],$body);
        }
        else{
                $tmp=($mode===1 ? $error['referer'] : str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$error['referer_2']));
                $return.=info_box($string['information'],$tmp);
        }
        if($mode!==1) $return.="<br><br><center><span class=\"testo\"><a href=\"admin.php?action=referer&group=$group&mode=1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"> $string[os_vis_glob]</a></span></center>";
}
return($return);
}

?>
