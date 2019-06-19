<?php
// <!-- SECURITY ISSUES -->
if(!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');

//-------------------------------------------------------------------------------------------------
// 		SALVATAGGIO / CARICAMENTO DELL'ULTIMA MODALITA' DI VISUALIZZAZIONE UTILIZZATA
//-------------------------------------------------------------------------------------------------
if ( user_is_logged_in() && $option['keep_view_mode'])
{
	foreach ($_GET as $key => $value)
	{
		if ($key != 'action' && $key!= 'start')
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
$start=(isset($_GET['start']) ? (int)$_GET['start'] : 0);
$mode=(isset($_GET['mode']) ? (int)$_GET['mode'] : 1);
$filter_number='100';


// <!-- PAGE FUNCTION -->
function details()
{
	global $db,$string,$error,$style,$option,$varie,$start,$modulo,$phpstats_title,$mode,$pref,$filter_number;

// <!-- DATA ACQUISITION -->

	switch($filter_number)
	{
  	case '100': $filter_agents="WHERE (os!='Spider' AND os!='')"; break;
	//  case '010': $filter_agents="WHERE os='Grabber'"; break;
  	case '001': $filter_agents="WHERE (os='Spider' OR os='')"; break;
	//  case '110': $filter_agents="WHERE os!='Spider'"; break;
	//  case '101': $filter_agents="WHERE os!='Grabber'"; break;
	//  case '011': $filter_agents="WHERE os REGEXP 'Spider|Grabber'"; break;
  	default: $filter_agents='';
	}

	//$filter_params='';//($filter_number{0}==='1' ? '&show_bw=1' : '').($filter_number{1}==='1' ? '&show_gr=1' : '').($filter_number{2}==='1' ? '&show_sp=1' : '');

	do
	{
        $dataFound=FALSE;

        $res=sql_query("SELECT count(DISTINCT visitor_id) FROM $option[prefix]_details $filter_agents");
        list($totalEntries)=mysql_fetch_row($res);
        $totalEntries=(int)$totalEntries;
        if($totalEntries===0) break;

        $recordPerPage=10;
        $pageNumber=ceil($totalEntries/$recordPerPage);
        $currentPage=ceil(($start/$recordPerPage)+1);

        $data_details=Array();

        $visitors_id=Array();
        $res=sql_query("SELECT visitor_id FROM $option[prefix]_details $filter_agents GROUP BY visitor_id ORDER BY date DESC LIMIT $start,$recordPerPage");
        if(mysql_num_rows($res)<0) break;
        while($row=mysql_fetch_row($res)) $visitors_id[]=$row[0];

        for($i=0,$tot=count($visitors_id);$i<$tot;++$i)
        {
                $visitor_id=$visitors_id[$i];

                $res=sql_query("SELECT ip,host,os,bw,lang,date,referer,currentPage,reso,colo,titlePage,tld,rets,last_return FROM $option[prefix]_details WHERE visitor_id='$visitor_id' ORDER BY date ASC");
                $tmpPages=Array();
                $tmpRow=NULL;
                while($row=mysql_fetch_row($res))
                {
                        list($details_ip,$details_host,$details_os,$details_bw,$details_language,$details_date,$details_referer,$details_currentPage,$details_reso,$details_colo,$details_titlePage,$domain,$returns,$last_return)=$row;
                        
						/*** patch Francesco Mortara - fmortara@mfweb.it - 2012-01-06 */
						$details_currentPage = addslashes($details_currentPage);
						/*** */
                        if($details_titlePage==='?' && $option['page_title'] && $option['refresh_page_title']==0)
                        {
                                $tmpRes=sql_query("SELECT titlePage FROM $option[prefix]_pages WHERE data='$details_currentPage' LIMIT 1");
                                if(mysql_num_rows($tmpRes)>0) list($details_titlePage)=mysql_fetch_row($tmpRes);
                        }
                        else if(substr($details_currentPage,0,3)==='dwn')
                        {
			            		list($dummy,$id)=explode('|',$details_currentPage);
                                $tmpRes=sql_query("SELECT nome FROM $option[prefix]_downloads WHERE id='$id' LIMIT 1");
                                if(mysql_num_rows($tmpRes)>0)
                                {
                                        list($name)=mysql_fetch_row($tmpRes);
//                                      $details_titlePage.=$details_currentPage.' - '.stripslashes($name);
                                        $details_titlePage .= ' - '.stripslashes($name);
                                }
                        }
                        else if(substr($details_currentPage,0,3)==='clk')
                        {
                                list($dummy,$id)=explode('|',$details_currentPage);
                                $tmpRes=sql_query("SELECT nome FROM $option[prefix]_clicks WHERE id='$id' LIMIT 1");
                                if(mysql_num_rows($tmpRes)>0)
                                {
                                        list($name)=mysql_fetch_row($tmpRes);
//                                      $details_titlePage.=$details_currentPage.'|'.stripslashes($name);
                                        $details_titlePage .= ' - '.stripslashes($name);
                                }
                        }

                        $tmpPages[]=Array($details_currentPage,$details_titlePage,(int)$details_date);

                        if($tmpRow===NULL) 
                        	$tmpRow=Array($details_ip,$details_host,$details_os,$details_bw,$details_language,$details_referer,$details_reso,$details_colo,$domain,$returns,$last_return);
                }
                $tmpRow[]=$tmpPages;

                $data_details[]=$tmpRow;
        }
        $dataFound=TRUE;
	} while(FALSE);


	// <!-- DATA PROCESSING -->
	if($dataFound)
	{
        include("lang/$option[language]/domains_lang.php");
        include("lang/$option[language]/bw_lang.php");

        $arrayTypeConn=Array(
                null,
                'Dial-Up',
                'ADSL',
                'Lan',
                'Wireline',
                'GPRS',
                'UMTS',
                'WI-FI'
        );

        $rangeMacro=Array('Spider','Grabber');

        $processed_details=Array();
        for($i=0,$tot=count($data_details);$i<$tot;++$i)
        {
                list($ip,$host,$os,$bw,$lang,$referer,$reso,$colo,$domain,$returns,$last_return,$pages)=$data_details[$i];

                $isSpiderGrabber=in_array($os,$rangeMacro);

                if($isSpiderGrabber)
                {
                        $language=$country=$isp=$host=$resolution=$color=$referer=$engine=null;

                        //dottedIP
                        $dottedIP=long2ip($ip);

                        //os
                        $os=((empty($os) || $os==='?') ? null : $os);

                        //browser
                        $bw=((empty($bw) || $bw==='?') ? null : $bw);

                        //pages
                        for($x=0,$totx=count($pages);$x<$totx;++$x)
                        {
                                list($pageUrl,$pageTitle,$pageDate)=$pages[$x];
                                $pageUrl=htmlspecialchars($pageUrl);
                                $pageTitle=stripslashes(trim($pageTitle));
                                $pages[$x]=Array($pageUrl,$pageTitle,$pageDate);
                        }
                }
                else
                {
                        //language
                        $language=(!isSet($bw_lang[$lang]) ? null : $bw_lang[$lang]);

                        //country
                        $country=(!isSet($domain_name[$domain]) ? null : $domain_name[$domain]);

                        //dottedIP
                        $dottedIP=long2ip($ip);

                        //host
                        $host=((empty($host) || $host===$dottedIP) ? null : $host);

                        //os
                        $os=((empty($os) || $os==='?') ? null : $os);

                        //browser
                        $bw=((empty($bw) || $bw==='?') ? null : $bw);

                        //resolution
                        $resolution=(empty($reso) ? null : $reso);

                        //color
                        $color=((empty($colo) || $colo==='?') ? null : "$colo bit");

                        //referrer
                        if(empty($referer)) $referer=null;
                        else if(substr($referer,0,4)==='http') $referer=htmlspecialchars($referer);
                        else
                        {
                                list($engineName,$engineDomain,$engineQuery,$engineResultPage,$engineUrl)=explode('|',$referer);
                                $engineImage='images/engines.php?q='.str_replace(' ','-',$engineName);
                                $engineUrl=htmlspecialchars($engineUrl);
                                $engineDomain=$domain_name[$engineDomain];
                                $engineQuery=stripslashes($engineQuery);

                                $referer=Array($engineName,$engineDomain,$engineQuery,$engineResultPage,$engineUrl,$engineImage);
                        }
                }
                $processed_details[]=Array($isSpiderGrabber,$bw,$os,$language,$resolution,$color,$country,$isp,$host,$dottedIP,$referer,$returns,$last_return,$pages);
        }
        unset($data_details,$bw_lang,$domain_name);
	}

	// <!-- PRE-OUTPUT PROCESSING -->
	if($dataFound)
	{
        $output_details=Array();

        for($i=0,$tot=count($processed_details);$i<$tot;++$i)
        {
                list($isSpiderGrabber,$bw,$os,$language,$resolution,$color,$country,$isp,$host,$dottedIP,$referer,$returns,$last_return,$pages)=$processed_details[$i];

                $mainDate=$pages[0][2];
                $mainDate=formatdate($mainDate,3).' - '.formattime($mainDate);

                if($bw===NULL) $bw='?';
                if($os===NULL) $os='?';
                if($isSpiderGrabber)
                	$screenInfo='N/A';
                else
                {
                        if($resolution===null && $color===null)
                        	$screenInfo='?';
                        else
                        {
                                if($resolution===NULL) $resolution='?';
                                if($color===NULL) $color='?';
                                $screenInfo="$resolution $color";
                        }
                }

                if($referer!==NULL)
                {
                        if(count($referer)===1)
                        	$referer=formaturl($referer,'',80,50,-25);
                        else
                        {
                                list($engineName,$engineDomain,$engineQuery,$engineResultPage,$engineUrl,$engineImage)=$referer;
                                $engineLabel="$engineName ($engineDomain)";
                                if($engineResultPage>0) $engineLabel.=", $string[se_page]: $engineResultPage";
                                $referer=Array($engineLabel,$engineQuery,$engineImage,$engineUrl);
                        }
                }

                $visitedPages=count($pages);

                //pages
                for($x=0;$x<$visitedPages;++$x)
                {
                        list($pageUrl,$pageTitle,$pageDate)=$pages[$x];

                        $tmp=substr($pageUrl,0,3);

                        switch($tmp)
                        {
                                case 'clk':
//                                list($dummy,$id,$name)=explode('|',$pageTitle);
//                                $pageDisplay=(isSet($name) ? str_replace('%NAME%',$name,$string['details_click']) : str_replace('%ID%',$id,$string['details_click']));
                                $pageDisplay=formaturl($pageUrl,$pageTitle,80,50,-25,$pageTitle,1);
                                break;

                                case 'dwn':
//                                list($dummy,$id,$name)=explode('|',$pageTitle);
//                                $pageDisplay=(isSet($name) ? str_replace('%NAME%',$name,$string['details_down']) : str_replace('%ID%',$id,$string['details_down']));
                                $pageDisplay=formaturl($pageUrl,$pageTitle,80,50,-25,$pageTitle,1);
                                break;

                                default:
                                //echo "$pageTitle<br>";
                                $pageUrl=htmlspecialchars($pageUrl);
                                //$pageUrl = str_replace( parse_url($pageUrl, PHP_URL_FRAGMENT), '', $pageUrl);
                                
                                $pageTitle=stripslashes(trim($pageTitle));

								/*** I had troubles converting Unicode-encoded data in $_GET (like this: %u05D8%u05D1%u05E2) which is generated by JavaScript's escape() function to UTF8 for server-side processing. */
								$pageTitle = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", $pageTitle);

                                $pageDisplay=formaturl($pageUrl,$pageTitle,80,50,-25,$pageTitle,$mode);
                                break;
                        }
                        $pageDate=formattime($pageDate);
                        $pages[$x]=Array($pageDisplay,$pageDate);
                }
                $visitedPages=str_replace('%VISITEDPAGES%',$visitedPages,$string['details_pageviewed']);

				if ($returns > 1)
					$visitedPages.=  "<font color=\"#00ff00\">  -  ".$string['details_returns']." $returns (".date('j/m/y G:i', $last_return).")</font>";
				//str_replace('%RETURNS%',$returns,$string['details_returns']);
				//$visitedPages.= date('j/m/y G:i', $last_return);
                $output_details[]=Array($isSpiderGrabber,$mainDate,$screenInfo,$bw,$os,$language,$country,$isp,$host,$dottedIP,$referer,$pages,$visitedPages);
		}
        $startingRecord=(1+(($currentPage-1)*$recordPerPage));
        unset($processed_details);
	}


	// <!-- OUTPUT CREATION -->
	$return='';

	// Page title (also shown in admin)
	$phpstats_title=$string['details_title'];

	$return=
	"<script>".
	"function whois(url){test=window.open(url,'nome','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=450,HEIGHT=600,LEFT=0,TOP=0');}".
	"</script>\n";

	if($dataFound)
	{
        $return.="<span class=\"pagetitle\">$phpstats_title</span>";

        if($pageNumber>1)
        	$return.="<div align=\"right\"><span class=\"testo\">".str_replace(Array('%current%','%total%'),Array($currentPage,$pageNumber),$varie['pag_x_y'])."&nbsp;&nbsp;</span></div>";

        $return.="\n\n<ol start=$startingRecord>";
		
        for($i=0,$tot=count($output_details);$i<$tot;++$i)
        {
                list($isSpiderGrabber,$mainDate,$screenInfo,$bw,$os,$language,$country,$isp,$host,$dottedIP,$referer,$pages,$visitedPages)=$output_details[$i];
                $return.=
                "\n\n<!--  NEW VISITOR-->".
                "\n<br>\n<li class=\"testo\"><span class=\"testo\">$mainDate</span><br>".
                "\n<table width=\"90%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" bordercolor=\"#000000\" class=\"tableborder\">\n\t<tr>\n\t\t<td bordercolor=\"#d9dbe9\">".
                "\n\t\t<table $style[table_header] width=\"100%\">".
                "\n\t\t\t<tr>".
                "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[details_os]</center></span></td>".
                "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[details_browser]</center></span></td>".
                "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[details_reso]</center></span></td>".
                "<td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[details_ip]</center></span></td>".
                "</tr>".
                "\n\t\t\t<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$os</span></td>".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$bw</span></td>".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$screenInfo</span></td>".
                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".
/***            "<a href=\"".($option['ext_whois']==='' ? "javascript:whois('whois.php?IP=$dottedIP');" : str_replace('%IP%',$dottedIP,$option['ext_whois']))."\" target=\"_BLANK\">$dottedIP</a>". ***/
                "<a href=\"".($option['ext_whois']==='' ? "javascript:whois('whois.php?IP=$dottedIP');" : str_replace('%IP%',$dottedIP,$option['ext_whois']))."\">$dottedIP</a>".
                "</span></td>".
                "</tr>".
                "\n\t\t</table>";
				
                $table='';
                if($language!==NULL)
                {
                        $table.=
                        "\n\t\t\t<tr>".
                        "<td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_lang]&nbsp;</center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$language</span></td>".
                        "</tr>";
                }

                if($country!==NULL)
                {
                        $table.=
                        "\n\t\t\t<tr>".
                        "<td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_country]&nbsp;</center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$country</span></td>".
                        "</tr>";
                }

                if($isp!==NULL)
                {
                        list($ispConnType,$ispName,$ispDesc)=$isp;
                        $table.=
                        "\n\t\t\t<tr>".
                        "<td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_isp]&nbsp;</center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$ispName</span></td>".
                        "</tr>";
                        if($ispConnType!==NULL) $table.="\n\t\t\t<tr><td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_connection]&nbsp;</center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$ispConnType</span></td></tr>";
                        if($ispDesc!==NULL) $table.="\n\t\t\t<tr><td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_descr]&nbsp;</center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$ispDesc</span></td></tr>";
                }

                if($host!==NULL)
                {
                        $table.=
                        "\n\t\t\t<tr>".
                        "<td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_server]&nbsp;</center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$host</span></td>".
                        "</tr>";
                }

                if($referer!==NULL)
                {
                        $table.="\n\t\t\t<tr>";
                        if(count($referer)===1) 
                        	$table.="<td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_referer]&nbsp;</center></span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$referer</span></td></tr>";
                        else
                        {
                                list($engineLabel,$engineQuery,$engineImage,$engineUrl)=$referer;
                                $engineQuery=stripslashes($engineQuery);
/*** */							$engineQuery=utf8_decode($engineQuery);
                                $table.=
                                "<td bgcolor=$style[table_title_bgcolor] width=\"5%\" nowrap><span class=\"tabletitle\"><center>&nbsp;$string[details_referer]&nbsp;</center></span></td>".
                                "<td bgcolor=$style[table_bgcolor]>".
                                "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">".
                                "<tr>".
                                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"$engineImage\" align=\"absmiddle\"> $engineLabel</span></td>".
                                "<td bgcolor=$style[table_title_bgcolor] width=\"50\" nowrap><span class=\"tabletitle\"><center>$string[details_query]</center></span></td>".
                                "<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$engineQuery</span></td>".
                                "<td bgcolor=$style[table_bgcolor] width=\"11\"><a href=\"$engineUrl\" target=\"_BLANK\"><img src=\"templates/$option[template]/images/icon_viewlink.gif\" border=0 ALT=\"$string[alt_visitlink]\"></a></td>".
                                "</tr>".
                                "</table>".
                                "</span></td></tr>";
                        }
                }

                if(!empty($table))
                {
                        $return.=
                        "\n\t\t<table $style[table_header] width=\"100%\">".
                        $table.
                        "\n\t\t</table>";
                }


                $return.=
                "\n\t\t<table border=\"0\" $style[table_header] width=\"100%\">".
                "\n\t\t\t<tr><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[details_ora]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$visitedPages</center></span></td></tr>";
                for($x=0,$totx=count($pages);$x<$totx;++$x)
                {
                        list($pageDisplay,$pageDate)=$pages[$x];
                        $return.="\n\t\t\t<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this,'$style[table_bgcolor]', '$style[table_bgcolor]')\"><td bgcolor=$style[table_bgcolor] width=\"10%\"><span class=\"tabletextA\">&nbsp;$pageDate&nbsp;</span></td><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$pageDisplay</span></td></tr>";
                }
                $return.="</table></td></tr></table>";
        }
        $return.="</ol>";

        if($pageNumber>1)
        {
                $return.=
                "<br><table border=\"0\" $style[table_header] width=\"90%\" align=\"center\">".
                "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] nowrap></td></tr>".
                "<tr><td bgcolor=$style[table_bgcolor] height=\"20\" nowrap>".
                pag_bar('admin.php?action=details&mode='.$mode,$currentPage,$pageNumber,$recordPerPage).
                "</td></tr>".
                "<tr><td height=\"1\"bgcolor=$style[table_title_bgcolor] nowrap></td></tr>".
                "</table>";
        }
        else
        		$return.=info_box($string['information'],$error['details']);

        // SELEZIONE MODALITA'
		$return.="<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		if($mode!==0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=details&mode=0\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_0]</span></a></td></tr>";
		if($mode!==1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=details&mode=1\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_1]</span></a></td></tr>";
		if($mode!==2) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=details&mode=2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_2]</span></a></td></tr>";
		$return.="</table></center>";

/*		if($modulo[11])
		{
			$return.=
			"<form name=\"filter_agents\" action=\"admin.php\" method=\"GET\">".
			"<input type=\"hidden\" name=\"action\" value=\"details\">".
			"<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
			"<br><table border=\"0\" $style[table_header] width=\"416\" align=\"center\">".
			"<tr><td bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[rf_title]</span></td></tr>".
			"<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center>".
			"<input type=\"checkbox\" name=\"show_bw\" value=\"1\"".($filter_number{0}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_browsers]&nbsp;&nbsp;&nbsp;&nbsp;".
	//        "<input type=\"checkbox\" name=\"show_gr\" value=\"1\"".($filter_number{1}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_grabbers]&nbsp;&nbsp;&nbsp;&nbsp;".
			"<input type=\"checkbox\" name=\"show_sp\" value=\"1\"".($filter_number{2}==='1' ? ' checked' : '').">&nbsp;<span class='testo'>$string[rf_spiders]</center></span></td></tr>".
			"<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><center><input type=\"submit\" value=\"$string[rf_submit]\"></center></span></td></tr>".
			"</table>".
			"</form>";
		}*/
		return($return);
	}
}
?>
