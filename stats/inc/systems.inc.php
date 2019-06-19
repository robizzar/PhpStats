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
		if ($key != 'action')
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

$selected_month=(isset($_GET['selected_month']) ? (int)$_GET['selected_month'] : (int)$month);
$selected_month=str_pad($selected_month,2,'0',STR_PAD_LEFT);

$selected_year=(isset($_GET['selected_year']) ? (int)$_GET['selected_year'] : $year);
//$mode=(isset($_GET['mode']) ? (int)$_GET['mode'] : ($modulo[1]<2 ? 1 : 0));
$mode=(isset($_GET['mode']) ? (int)$_GET['mode'] : 1);

$filter_number='100';

// <!-- PAGE FUNCTION -->
function systems() {
global $db,$string,$error,$style,$option,$varie,$modulo,$phpstats_title,$filter_number,$mode,$selected_year,$selected_month;

// <!-- DATA ACQUISITION -->

switch($filter_number)
  {
  case '100': $filterAgents="AND (os!='Spider' AND os!='')"; break;
//  case '010': $filterAgents="AND os='Grabber'"; break;
  case '001': $filterAgents="AND (os='Spider' OR os='')"; break;
//  case '110': $filterAgents="AND os!='Spider'"; break;
//  case '101': $filterAgents="AND os!='Grabber'"; break;
//  case '011': $filterAgents="AND os REGEXP 'Spider|Grabber'"; break;
  default: $filterAgents='';
  }
//$filterParams=($filter_number{0}==='1' ? '&show_bw=1' : '').($filter_number{1}==='1' ? '&show_gr=1' : '').($filter_number{2}==='1' ? '&show_sp=1' : '');

$dateFilter=($mode===0 ? "WHERE mese='$selected_year-$selected_month' AND os<>''" : "WHERE os<>''");


do{
        $dataFound=FALSE;

        //minor date
        $res=sql_query("SELECT min(data) FROM $option[prefix]_daily");
        if(mysql_num_rows($res)<1) break;
        list($min_date)=mysql_fetch_row($res);
        $min_date=(int)substr($min_date,0,4);

        $res=sql_query("SELECT sum(hits),sum(visits) FROM $option[prefix]_systems $dateFilter $filterAgents");
        list($totalHits,$totalVisits)=mysql_fetch_row($res);
        $totalHits=(int)$totalHits;
        $totalVisits=(int)$totalVisits;
        if($totalHits===0 && $totalVisits===0) break;

        $data_systems=Array();
        $limit=50;
        $res=sql_query("SELECT os,bw,colo,reso,SUM(hits) AS sumhits,SUM(visits) AS sumvisits FROM $option[prefix]_systems $dateFilter $filterAgents GROUP BY os,bw,reso,colo ORDER BY sumhits DESC LIMIT $limit");
        if(mysql_num_rows($res)<1) break;
        while($row=mysql_fetch_row($res)){
                list($systems_os,$systems_bw,$systems_colo,$systems_reso,$systems_sumhits,$systems_sumvisits)=$row;

                $data_systems[]=Array($systems_os,$systems_bw,$systems_colo,$systems_reso,(int)$systems_sumhits,(int)$systems_sumvisits);
        }

        $dataFound=TRUE;
}while(FALSE);


// <!-- DATA PROCESSING -->
//not needed



// <!-- PRE-OUTPUT PROCESSING -->
if($dataFound){
        $output_systems=Array();

        for($i=0,$tot=count($data_systems);$i<$tot;++$i){
                list($os,$bw,$colorDepth,$resolution,$sumhits,$sumvisits)=$data_systems[$i];

                switch($os){
                        default:        $typeofbw=0; break;
                        case 'Spider':  $typeofbw=1; break;
                        case 'Grabber': $typeofbw=2; break;
                }

                $osImage='images/os.php?q='.($os==='?' ? 'unknown' : str_replace(' ','-',$os));
                $bwImage='images/browsers.php?q='.($bw==='?' ? 'unknown' : str_replace(' ','-',$bw)).($typeofbw===0 ? '' : "&type=$typeofbw");

                $colorDepth=(($typeofbw===0 && $colorDepth!=='?') ? $colorDepth.' bit' : '?');

                $hitsRep=$sumhits/$totalHits;
                $hitsPercent=round($hitsRep*100,2);
                $hitsBarLength=round($hitsRep*180);

                $visitsRep=$sumvisits/$totalVisits;
                $visitsPercent=round($visitsRep*100,2);
                $visitsBarLength=round($visitsRep*180);

                $output_systems[]=Array($os,$bw,$colorDepth,$resolution,$sumhits,$sumvisits,$hitsPercent,$hitsBarLength,$visitsPercent,$visitsBarLength,$osImage,$bwImage);
        }
        unset($data_systems);
}



// <!-- OUTPUT CREATION -->
$return='';

// Admin Page Title
if($mode===0) $phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['systems_title_2']);
else $phpstats_title=$string['systems_title'];
//

if($dataFound){
        $return.=
        "<span class=\"pagetitle\">$phpstats_title</span>".
        "<br><br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">".
        '<tr>'.
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[systems_os]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[systems_bw]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[systems_reso]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[systems_colo]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[os_hits]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>".
        '</tr>';

        for($i=0,$tot=count($output_systems);$i<$tot;++$i){
                list($os,$bw,$colorDepth,$resolution,$sumhits,$sumvisits,$hitsPercent,$hitsBarLength,$visitsPercent,$visitsBarLength,$osImage,$bwImage)=$output_systems[$i];
				$count = $i+1;
                $return.=
                "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$count</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"left\"><span class=\"tabletextA\"><img src=\"$osImage\" align=\"absmiddle\"> $os</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"left\"><span class=\"tabletextA\"><img src=\"$bwImage\" align=\"absmiddle\"> $bw</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"left\"><span class=\"tabletextA\">$resolution</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"left\"><span class=\"tabletextA\">$colorDepth</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$sumhits</b></span><br><span class=\"tabletextA\"><b>$sumvisits</b></span></td>".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"$hitsBarLength\" height=\"7\"> ($hitsPercent%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"$visitsBarLength\" height=\"7\"> ($visitsPercent%)</span></td>".
                '</tr>';
        }
        $return.=
        "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"7\" nowrap></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] colspan=\"7\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>".
//        "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"7\" nowrap></td></tr>".
        '</table>';
}
else $return.=info_box($string['information'],$error['os_bw']);

if($modulo[1]==2){
        $return.='<br><br><center>';
        if($mode===0){
                // SELEZIONE MESE DA VISUALIZZARE
                $return.=
                "<form action='./admin.php' method='GET' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>".
                "<input type=\"hidden\" name=\"action\" value=\"systems\">".
                "<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
                "<input type=\"hidden\" name=\"show_bw\" value=\"".$filter_number{0}."\">".
                //"<input type=\"hidden\" name=\"show_gr\" value=\"".$filter_number{1}."\">".
                //"<input type=\"hidden\" name=\"show_sp\" value=\"".$filter_number{2}."\">".
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
                "<br><br><a href=\"admin.php?action=systems&mode=1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_vis_glob]</span></a>".
                '</FORM>';
        }
        else $return.="<a href=\"admin.php?action=systems&mode=0\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_vis_mens]</span></a>";
        $return.='</center>';
}
/*
if($modulo[11]){
        $return.=
        "<form name=\"filter_agents\" action=\"admin.php\" method=\"GET\">".
        "<input type=\"hidden\" name=\"action\" value=\"systems\">".
        "<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
        "<input type=\"hidden\" name=\"selected_year\" value=\"$selected_year\">".
        "<input type=\"hidden\" name=\"selected_month\" value=\"$selected_month\">".
        "<br><table border=\"0\" $style[table_header] width=\"416\" align=\"center\">".
        "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[rf_title]</span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center>".
        "<input type=\"checkbox\" name=\"show_bw\" value=\"1\"".($filter_number{0}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_browsers]&nbsp;&nbsp;&nbsp;&nbsp;".
        "<input type=\"checkbox\" name=\"show_gr\" value=\"1\"".($filter_number{1}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_grabbers]&nbsp;&nbsp;&nbsp;&nbsp;".
        "<input type=\"checkbox\" name=\"show_sp\" value=\"1\"".($filter_number{2}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_spiders]</center></span></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center><input type=\"submit\" value=\"$string[rf_submit]\"></center></span></td></tr>".
        "</table>".
        "</form>";
}
*/
return($return);
}
?>