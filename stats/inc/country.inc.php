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
if(isset($_GET['mode'])) $mode=(int)addslashes($_GET['mode']); else $mode=0;

// <!-- PAGE FUNCTION -->
function country() {
	global $db,$mode,$option,$string,$style,$error,$phpstats_title;

	$what=($mode===0 ? 'visits' : 'hits');

	do{
        $dataFound=false;
        $total=0;

        //total visits/hits
        $res=sql_query("SELECT SUM($what) FROM $option[prefix]_domains");
        list($total)=mysql_fetch_row($res);
        $total=(int)$total;
        if($total===0) break;

        //sum visits/hits per area
        $data_areas=Array();
        $res=sql_query("SELECT area,SUM($what) FROM $option[prefix]_domains GROUP BY area");
        if(mysql_num_rows($res)<1) break;
        while($row=mysql_fetch_row($res)){
                list($domains_area,$domains_sumwhat)=$row;
                $data_areas[]=Array($domains_area,(int)$domains_sumwhat);
        }
        unset($domains_area,$domains_sumwhat);

        //sum visits/hits per country
        $data_countries=Array();
        $res=sql_query("SELECT tld,$what from $option[prefix]_domains WHERE $what>0 ORDER BY $what DESC");
        if(mysql_num_rows($res)<1) break;
        while($row=mysql_fetch_row($res)){
                list($domains_tld,$domains_sumwhat)=$row;
                $data_countries[]=Array($domains_tld,(int)$domains_sumwhat);
        }
        unset($domains_tld,$domains_sumwhat);

        $dataFound=true;
}while(FALSE);
mysql_free_result($res);


if($dataFound){
        include("lang/$option[language]/domains_lang.php");

        $output_areas=Array();
        for($i=0,$tot=count($data_areas);$i<$tot;++$i){
                list($area,$sumwhat)=$data_areas[$i];
                $output_areas[$area]=Array($domain_name["area_$area"],$sumwhat,round($sumwhat/$total*100,2));
        }
        $output_areas['AS'][1]+=$output_areas['GUS'][1];
        $output_areas['AS'][2]+=$output_areas['GUS'][2];
        unset($data_areas,$output_areas['GUS']);

        $output_countries=Array();
        for($i=0,$tot=count($data_countries);$i<$tot;++$i){
                list($tld,$sumwhat)=$data_countries[$i];
                $rep=$sumwhat/$total;
                $output_countries[]=Array($domain_name[$tld],$tld,$sumwhat,round($rep*100,2),(int)($rep*200));
        }
        unset($domain_name,$data_countries);
}



// <!-- OUTPUT CREATION -->

// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['country_title'];
$return='';

if($dataFound){
        //Continents chart

        list($EUname,$EUsumwhat,$EUpercent)=$output_areas['EU'];
        list($ASname,$ASsumwhat,$ASpercent)=$output_areas['AS'];
        list($AFname,$AFsumwhat,$AFpercent)=$output_areas['AF'];
        list($OZname,$OZsumwhat,$OZpercent)=$output_areas['OZ'];
        list($AMname,$AMsumwhat,$AMpercent)=$output_areas['AM'];

        $return.=
        "<span class=\"pagetitle\">$string[continent_title]<br><br></span>\n".
        "<table border=\"0\" $style[table_header] width=\"482\" height=\"259\" align=\"center\">\n".
        "<tr><td height=\"1\" bgcolor=\"$style[table_title_bgcolor]\" nowrap></td></tr>\n".
        "<tr>\n".
        "<td bgcolor=\"$style[table_bgcolor]\">\n".
        "<table width=\"482\" height=\"259\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" background=\"templates/$option[template]/images/continent_map.gif\">\n".
        "<tr>\n".
        "<td width=\"163\" rowspan=\"5\" align=\"center\" valign=\"middle\"><span class=\"tabletextA\"><b>$AMname $AMsumwhat<br>($AMpercent%)</b></span></td>\n".
        "<td width=\"162\" align=\"center\" valign=\"bottom\"><span class=\"tabletextA\"><b>$EUname $EUsumwhat<br>($EUpercent%)</b></span></td>\n".
        "<td width=\"116\"></td>\n".
        "<td width=\"41\"></td>\n".
        "</tr>\n".
        "<tr>\n".
        "<td>&nbsp;</td>\n".
        "<td align=\"left\" valign=\"top\"><span class=\"tabletextA\"><b>$ASname $ASsumwhat<br>($ASpercent%)</b></span></td>\n".
        "<td>&nbsp;</td>\n".
        "</tr>\n".
        "<tr>\n".
        "<td align=\"center\"><span class=\"tabletextA\"><b>$AFname $AFsumwhat<br>($AFpercent%)</b></span></td>\n".
        "<td>&nbsp;</td>\n".
        "<td>&nbsp;</td>\n".
        "</tr>\n".
        "<tr>\n".
        "<td>&nbsp;</td>\n".
        "<td colspan=\"2\" align=\"center\"><span class=\"tabletextA\"><b>$OZname $OZwhat<br>($OZpercent%)</b></span></td>\n".
        "</tr>\n".
        "<tr>\n".
        "<td colspan=\"3\">&nbsp;</td>\n".
        "</tr>\n".
        "</table>\n".
        "</tr>\n".
        "<tr><td height=\"1\" bgcolor=\"$style[table_title_bgcolor]\" nowrap></td></tr>\n".
        "</td></table>\n";
        unset($output_areas);

        //domain details
        $return.=
        "<br><br>\n".
        "<span class=\"pagetitle\">$string[country_title]<br><br></span>\n".
        "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\"><tr>\n".
        draw_table_title('').
        draw_table_title($string['country']).
        draw_table_title($mode===0 ? $string['country_visits'] : $string['country_hits']).
        draw_table_title('').
        "</tr>\n";
        if($mode===0){
                $img="templates/$option[template]/images/style_bar_2.gif";
                $type=str_replace('%tipo%',$string['hits'],$string['mode']);
                $new_mode='1';
        }
        else{
                $img="templates/$option[template]/images/style_bar_1.gif";
                $type=str_replace('%tipo%',$string['visite'],$string['mode']);
                $new_mode='0';
        }


        for($i=0,$tot=count($output_countries);$i<$tot;++$i){
                list($name,$tld,$sumwhat,$percent,$barlength)=$output_countries[$i];
                $return.=
                "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">\n".
                "<td bgcolor=\"$style[table_bgcolor]\" nowrap width=\"14\"><img src=\"images/flags.php?q=$tld\" align=\"absmiddle\"></td>\n".
                "<td bgcolor=\"$style[table_bgcolor]\" align=\"right\" nowrap ><span class=\"tabletextA\">$name".
                ($tld==='unknown' ? '' : " (.$tld)").
                "</span></td>\n".
                "<td align=\"right\" bgcolor=$style[table_bgcolor] nowrap><span class=\"tabletextA\"><b>$sumwhat</b></span></td>\n".
                "<td bgcolor=\"$style[table_bgcolor]\" nowrap><span class=\"tabletextA\"><img src=\"$img\" width=\"$barlength\" height=\"7\"> ($percent%)</span></td>\n".
                "</tr>\n";
        }
        $return.=
//        "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>\n".
        "</table>\n".
        "<br><center><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class=\"testo\"><a href=\"admin.php?action=country&mode=$new_mode\">$type</a></span></center>\n";
}
else $return.=info_box($string['information'],$error['country']);

return($return);
}
?>
