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
list($month,$year)=explode('-',date('m-Y',time()-$option['timezone']*3600));

$selected_month=(isset($_GET['selected_month']) ? (int)$_GET['selected_month'] : (int)$month);
$selected_month=str_pad($selected_month,2,'0',STR_PAD_LEFT);

$selected_year=(isset($_GET['selected_year']) ? (int)$_GET['selected_year'] : (int)$year);

/*** ORIGINALE: $mode=(isset($_GET['mode']) ? addslashes($_GET['mode'])-0 : ($modulo[1]<2 ? 1 : 0)); ***/
$mode=(isset($_GET['mode']) ? addslashes($_GET['mode'])-0 : ($modulo[1]<2 ? 0 : 1));
/*** ORIGINALE: $group=(isset($_GET['group']) ? addslashes($_GET['group'])-0 : 0); ***/
$group=(isset($_GET['group']) ? addslashes($_GET['group'])-0 : 1);
/*
$filter_number=
($_GET['show_bw']=='1' ? '1' : '0').
'0'.
//($_GET['show_sp']=='1' ? '1' : '0');
'0';
if($filter_number==='000') 
*/
$filter_number='100';

// <!-- PAGE FUNCTION -->
function os_browser() {
global $db,$string,$error,$style,$option,$mode,$group,$varie,$modulo,$phpstats_title,$filter_number;
global $selected_year,$selected_month;


if(!isset($modulo)) $modulo=explode('|',$option['moduli']);
/*
switch($filter_number)
  {
  case '100': $filter_agents="AND (os!='Spider' AND os!='')"; break;
//  case '010': $filter_agents="AND os='Grabber'"; break;
  case '001': $filter_agents="AND (os='Spider' OR os='')"; break;
//  case '110': $filter_agents="AND os!='Spider'"; break;
  //case '101': $filter_agents="AND os!='Grabber'"; break;
//  case '011': $filter_agents="AND os REGEXP 'Spider|Grabber'"; break;
  default: $filter_agents='';
  }
  */
$filter_agents="AND (os!='Spider' AND os!='')";  
  
//$filter_params=($filter_number{0}==='1' ? '&show_bw=1' : '').($filter_number{1}==='1' ? '&show_gr=1' : '').($filter_number{2}==='1' ? '&show_sp=1' : '');
$filter_params = '&show_bw=1';

$clause=($mode==0 ? "WHERE mese='$selected_year-$selected_month' AND os<>''" : "WHERE os<>''");

do{
        $dataFound=false;

        //total hits and visits for os
        $res=sql_query("SELECT sum(hits),sum(visits) FROM $option[prefix]_systems $clause $filter_agents");
        list($total_hits,$total_visits)=mysql_fetch_row($res);
        $total_hits=(int)$total_hits;
        $total_visits=(int)$total_visits;

        if($total_hits===0 && $total_visits===0) break;

        //minor date
        $res=sql_query("SELECT min(data) FROM $option[prefix]_daily");
        if(mysql_num_rows($res)<1) break;
        list($min_date)=mysql_fetch_row($res);
        $min_date=(int)substr($min_date,0,4);

        //creation of $data_os
        $data_os=Array();
        $res=sql_query("SELECT os,SUM(hits) AS sumhits,SUM(visits) AS sumvisits FROM $option[prefix]_systems $clause $filter_agents GROUP BY os ORDER BY 'sumhits' DESC");
        if(mysql_num_rows($res)<1) break;
        while($row=mysql_fetch_row($res)){
                list($systems_os,$systems_sumhits,$systems_sumvisits)=$row;
                $data_os[]=Array($systems_os,(int)$systems_sumhits,(int)$systems_sumvisits);
        }

        //creation of $data_bw
        $data_bw=Array();
        $res=sql_query("SELECT os,bw,SUM(hits) AS sumhits,SUM(visits) AS sumvisits FROM $option[prefix]_systems $clause $filter_agents GROUP BY bw ORDER BY 'sumhits' DESC");
        if(mysql_num_rows($res)<1) break;
        while($row=mysql_fetch_row($res)){
                list($systems_os,$systems_bw,$systems_sumhits,$systems_sumvisits)=$row;

                switch ($systems_os){
                        default:        $typeofbw=0; break;
                        case 'Spider':  $typeofbw=1; break;
                        case 'Grabber': $typeofbw=2; break;
                }

                $data_bw[]=Array($systems_bw,(int)$systems_sumhits,(int)$systems_sumvisits,(int)$typeofbw);
        }

        $dataFound=true;
}while(false);
mysql_free_result($res);



if($dataFound){
        //creation of $processed_os
        $processed_os=Array();
        if($group===0){
                for($i=0,$tot=count($data_os);$i<$tot;++$i){
                        list($os_name,$os_hits,$os_visits)=$data_os[$i];
                        $processed_os[]=Array((int)$os_hits,(int)$os_visits,$os_name);
                }
                unset($data_os);
				usort($processed_os, '_osbw_sort_cmp');		/*** ORDINA IN BASE AL NUMERO DI VISITATORI ***/
        }
        else{											/* Ordina raggruppando la prima parola del nome (ignorando quindi n° di versione) */
                $osnames=Array();
                $count=0;
                for($i=0,$tot=count($data_os);$i<$tot;++$i){
                        list($os_name,$os_hits,$os_visits)=$data_os[$i];
                        $tmp=explode(' ',$os_name);
                        $tmpname=$tmp[0];

                        if(isSet($osnames[$tmpname])){
                                $processed_os[$osnames[$tmpname]][0]+=$os_hits;
                                $processed_os[$osnames[$tmpname]][1]+=$os_visits;
                        }
                        else{
                                $processed_os[]=Array($os_hits,$os_visits,$tmpname);
                                $osnames[$tmpname]=$count;
                                ++$count;
                        }
                }
                unset($data_os,$osnames);
                rsort($processed_os);
        }

        //creation of $processed_bw
        $processed_bw=Array();
        if($group===0){
                for($i=0,$tot=count($data_bw);$i<$tot;++$i){
                        list($bw_name,$bw_hits,$bw_visits,$typeofbw)=$data_bw[$i];
                        $processed_bw[]=Array($bw_hits,$bw_visits,$bw_name,$typeofbw);
                }
                unset($data_bw);
				usort($processed_bw, '_osbw_sort_cmp');		/*** ORDINA IN BASE AL NUMERO DI VISITATORI ***/
        }
        else{											/*** Ordina raggruppando la prima parola del nome (ignorando quindi n° di versione) ***/
                $bwnames=Array();
                $count=0;
                for($i=0,$tot=count($data_bw);$i<$tot;++$i){
                        list($bw_name,$bw_hits,$bw_visits)=$data_bw[$i];
                        $tmp=explode(' ',$bw_name);
                        $tmpname=$tmp[0];

                        if(isSet($bwnames[$tmpname])){
                                $processed_bw[$bwnames[$tmpname]][0]+=$bw_hits;
                                $processed_bw[$bwnames[$tmpname]][1]+=$bw_visits;
                        }
                        else{
                                $processed_bw[]=Array($bw_hits,$bw_visits,$tmpname);
                                $bwnames[$tmpname]=$count;
                                ++$count;
                        }
                }
                unset($data_bw,$bwnames);
                rsort($processed_bw);
        }


}



if($dataFound){
        $tmphits=MAX($total_hits,1);
        $tmpvisits=MAX($total_visits,1);

        $output_os=Array();
        for($i=0,$tot=count($processed_os);$i<$tot;++$i){
                list($hits,$visits,$name)=$processed_os[$i];
                if($name==='?'){ $name=$string['os_unknown']; $picurl='images/os.php?q=unknown'; }
                else $picurl='images/os.php?q='.str_replace(' ','-',$name);

                $hitsrep=$hits/$tmphits;
                $visitsrep=$visits/$tmpvisits;

                $output_os[]=Array($name,$hits,$visits,round($hitsrep*100,2),round($visitsrep*100,2),(int)($hitsrep*330),(int)($visitsrep*330),$picurl);
        }
        unset($processed_os);

        $output_bw=Array();
        for($i=0,$tot=count($processed_bw);$i<$tot;++$i){
                list($hits,$visits,$name,$typeofbw)=$processed_bw[$i];
                if($name==='?'){ $name=$string['browser_unknown']; $picurl='images/browsers.php?q=unknown'; }
                else $picurl='images/browsers.php?q='.str_replace(' ','-',$name).($typeofbw===0 ? '' : "&type=$typeofbw");

                $hitsrep=$hits/$tmphits;
                $visitsrep=$visits/$tmpvisits;

                $output_bw[]=Array($name,$hits,$visits,round($hitsrep*100,2),round($visitsrep*100,2),(int)($hitsrep*330),(int)($visitsrep*330),$picurl);
        }
        unset($processed_bw);
}


// <!-- OUTPUT CREATION -->

// Admin Page Title
switch($group){
        case 0:
                if($mode===0) $phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['os_browser_title_2']);
                else $phpstats_title=$string['os_browser_title'];
                break;
        case 1:
                if($mode===0) $phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['os_browser_title_2a']);
                else $phpstats_title=$string['os_browser_titlea'];
                break;
        case 2:
                if($mode===0) $phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['os_browser_title_2b']);
                else $phpstats_title=$string['os_browser_titleb'];
                break;
}

$return='';
if($dataFound){
        // OS section

        // OS title
        if($mode===0) $tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['os_title_2']);
        else $tmp=$string['os_title'];
        $return.="<span class=\"pagetitle\">$tmp</span>";
        //

        $return.=
        "<br><br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">".
        "<tr>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[os_os]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[os_hits]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>".
        "</tr>";
        for($i=0,$tot=count($output_os);$i<$tot;++$i){
                list($name,$hits,$visits,$percenthits,$percentvisits,$hitsbarlength,$visitsbarlength,$picurl)=$output_os[$i];
                $return.=
                "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                "<td bgcolor=$style[table_bgcolor] width=\"14\"><img src=\"$picurl\"></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$name</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$hits</b></span><br><span class=\"tabletextA\"><b>$visits</b></span></td>".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"$hitsbarlength\" height=\"7\"> ($percenthits%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"$visitsbarlength\" height=\"7\"> ($percentvisits%)</span></td>".
                "</tr>";
        }
        unset($output_os);
        $return.=
        "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] colspan=\"4\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>".
        "</table>".
        "<br><br>";

        // Browser section

        // Browser title
        if($mode===0) $tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$string['browser_title_2']);
        else $tmp=$string['browser_title'];
        $return.="<span class=\"pagetitle\">$tmp</span>";
        //

        $return.=
        "<br><br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\">".
        "<tr>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[browser_bw]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[browser_hits]</center></span></td>".
        "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td>".
        "</tr>";
        for($i=0,$tot=count($output_bw);$i<$tot;++$i){
                list($name,$hits,$visits,$percenthits,$percentvisits,$hitsbarlength,$visitsbarlength,$picurl)=$output_bw[$i];

                $return.=
                "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                "<td bgcolor=$style[table_bgcolor] width=\"14\"><img src=\"$picurl\"></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$name</span></td>".
                "<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$hits</b></span><br><span class=\"tabletextA\"><b>$visits</b></span></td>".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"$hitsbarlength\" height=\"7\"> ($percenthits%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"$visitsbarlength\" height=\"7\"> ($percentvisits%)</span></td>".
                "</tr>";
        }
        unset($output_bw);
        $return.=
        "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>".
        "<tr><td bgcolor=$style[table_bgcolor] colspan=\"4\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>".
//        "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>".
        "</table>";
}
else{
        if($mode==1) $return.=info_box($string['information'],$error['os_bw']);
        else{
                $tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($selected_month),$selected_year),$error['os_bw_2']);
                $return.=info_box($string['information'],$tmp);
        }
}

if($modulo[1]===2){
        $return.=
        "<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">".
        "<tr><td colspan=\"2\"><span class=\"testo\">";
        if($mode===0){
                // SELEZIONE MESE DA VISUALIZZARE
                $return.=
                "<form action='./admin.php' method='GET' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>".
                "<input type=\"hidden\" name=\"action\" value=\"os_browser\">".
                "<input type=\"hidden\" name=\"group\" value=\"$group\">".
                "<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
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
                "</td></tr>".
                "<tr><td><a href=\"admin.php?action=os_browser&mode=1&group=$group{$filter_params}\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_vis_glob]</span></a></td></tr>".
                "</FORM>";
        }
        else $return.="<a href=\"admin.php?action=os_browser&mode=0&group=$group{$filter_params}\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_vis_mens]</span></a></td></tr>";
        if($group!==0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=os_browser&group=0{$filter_params}".($mode===0 ? "&mode=0&selected_month=$selected_month&selected_year=$selected_year" : '&mode=1')."\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_mode_0]</span></a></td></tr>";
        if($group!==1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=os_browser&group=1{$filter_params}".($mode===0 ? "&mode=0&selected_month=$selected_month&selected_year=$selected_year" : '&mode=1')."\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[os_mode_1]</span></a></td></tr>";

        $return.="</table>";
    if($modulo[11]){
                $return.=
                "<form name=\"filter_agents\" action=\"admin.php\" method=\"GET\">".
                "<input type=\"hidden\" name=\"action\" value=\"os_browser\">".
                "<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
                "<input type=\"hidden\" name=\"group\" value=\"$group\">".
                "<input type=\"hidden\" name=\"selected_month\" value=\"$selected_month\">".
                "<input type=\"hidden\" name=\"selected_year\" value=\"$selected_year\">".
               // "<br><table border=\"0\" $style[table_header] width=\"416\" align=\"center\">".
               // "<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[rf_title]</span></td></tr>".
               // "<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center>".
              //  "<input type=\"checkbox\" name=\"show_bw\" value=\"1\"".($filter_number{0}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_browsers]&nbsp;&nbsp;&nbsp;&nbsp;".
               // "<input type=\"checkbox\" name=\"show_sp\" value=\"1\"".($filter_number{2}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_spiders]</center></span></td></tr>".
              //  "<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center><input type=\"submit\" value=\"$string[rf_submit]\"></center></span></td></tr>".
                //"</table>".
                "</form>";
        }
        $return.="</center>";
}
return($return);
}

/*** ***/
function _osbw_sort_cmp($a, $b)
{
    if ($a[1] == $b[1]) {
        return 0;
    }
    return ($a[1] < $b[1]) ? 1 : -1;
}
?>