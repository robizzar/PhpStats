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
		if ($key != 'action' && $key != 'start' && $key != 'delpage')
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
$sort=(isset($_GET['sort']) ? addslashes($_GET['sort']) : 'hits'); // Default sort
$order=(isset($_GET['order']) ? addslashes($_GET['order']) : 'DESC'); // Default order
if(isset($_GET['q'])) $q=addslashes($_GET['q']);
else if(isset($_POST['q'])) $q=addslashes($_POST['q']);
else $q='';
$mode=(isset($_GET['mode']) ? (int)$_GET['mode'] : 1);
$mode_2=1;//(isset($_GET['mode_2']) ? (int)$_GET['mode_2'] : 1);
$delpage=(isset($_GET['delpage']) ? $_GET['delpage'] : '');

if(!isset($modulo)) $modulo=explode('|',$option['moduli']);

// <!-- PAGE FUNCTION -->
function pages(){
global $db,$string,$varie,$error,$style,$option,$start,$pref,$q,$sort,$order,$phpstats_title,$mode,$delpage,$modulo,$mode_2;

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
switch($mode){
        default:$qFilter=''; break;
        case 0: $qFilter="WHERE data like '%$q%'"; break;
        case 1: $qFilter="WHERE titlePage like '%$q%'"; break;
        case 2: $qFilter="WHERE data like '%$q%' OR titlePage like '%$q%'"; break;
}

switch($mode_2){
        default:
        case 0://browser and spiders
        $phpstats_title.=$string['monthly_string_mode_0'];
        $sumhits='SUM(hits)';
        $singlehits='hits AS realhits';
        $filterZeroHits='';
        break;

        case 1://browser
        $phpstats_title.=$string['monthly_string_mode_1'];
        $sumhits='SUM(hits)-SUM(no_count_hits)';
        $singlehits='(hits-no_count_hits) AS realhits';
        $filterZeroHits=($qFilter==='' ? 'WHERE ' : 'AND ').'hits<>0';
        break;

        case 2://spiders
        $phpstats_title.=$string['monthly_string_mode_2'];
        $sumhits='SUM(no_count_hits)';
        $singlehits='no_count_hits AS realhits';
        $filterZeroHits=($qFilter==='' ? 'WHERE ' : 'AND ').'no_count_hits<>0';
        break;
}

$tables=Array('pagina'=>'data','hits'=>'realhits','in'=>'lev_1','out'=>'outs','io'=>'io','date'=>'date');
$modes=Array('0'=>'DESC','1'=>'ASC');
$sortBy=(isset($tables[$sort]) ? $tables[$sort] : 'realhits');
$orderBy=(isset($modes[$order]) ? $modes[$order] : 'DESC');

do{
        $dataFound=false;

        $recordPerPage=50;
        $res=sql_query("SELECT count(1) FROM $option[prefix]_pages $qFilter");
        list($totalEntries)=mysql_fetch_row($res);
        $totalEntries=(int)$totalEntries;
        if($totalEntries===0) break;
        $pageNumber=ceil($totalEntries/$recordPerPage);
        $currentPage=ceil(($start/$recordPerPage)+1);


        $res=sql_query("SELECT $sumhits FROM $option[prefix]_pages");
        list($totalHits)=mysql_fetch_row($res);
        $totalHits=(int)$totalHits;


        if(!empty($q)){
                $res=sql_query("SELECT $sumhits FROM $option[prefix]_pages $qFilter"); //codice per la visualizzazione delle hits da htmlandrea
                list($totalHitsForSearchedWords)=mysql_fetch_row($res);
                $totalHitsForSearchedWords=(int)$totalHitsForSearchedWords;
        }


        $data_pages=Array();
        $res=sql_query("SELECT data,$singlehits,lev_1,outs,titlePage,(lev_1-outs),date as io FROM $option[prefix]_pages $qFilter $filterZeroHits ORDER BY $sortBy $orderBy LIMIT $start,$recordPerPage");
        if(mysql_num_rows($res)<1) break;
        while($row=mysql_fetch_row($res)){
                list($pages_data,$pages_hits,$pages_lev_1,$pages_outs,$pages_titlePage,$pages_io,$date)=$row;
                $data_pages[]=Array($pages_data,(int)$pages_hits,(int)$pages_lev_1,(int)$pages_outs,stripslashes(trim($pages_titlePage)),(int)$pages_io,$date);
        }

        $dataFound=true;
}while(false);



// <!-- DATA PROCESSING -->
//not needed



// <!-- PRE-OUTPUT PROCESSING -->
if($dataFound){
        $output_pages=Array();
        for($i=0,$tot=count($data_pages);$i<$tot;++$i){
                list($url,$hits,$ins,$outs,$pageTitle,$io,$date)=$data_pages[$i];
                $rawurl=urlencode($url);

				/*** I had troubles converting Unicode-encoded data in $_GET (like this: %u05D8%u05D1%u05E2) which is generated by JavaScript's escape() function to UTF8 for server-side processing. ***/
				$pageTitle = preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", $pageTitle);

                if ($mode == 1 && $pageTitle=='')		/*** Se in modalità visualizzazione titolo pagina, ma il titolo manca, la riga è forzata su titolo+url ***/
	                $url=formaturl($url,$pageTitle,55,45,0,$pageTitle,2);
	            else
	                $url=formaturl($url,$pageTitle,55,45,0,$pageTitle,$mode);

                $percent=round(($hits*100)/$totalHits,2);

                if($mode_2===2) $ins=$outs=$io='';
                else{
                        if($ins===0) $ins='-';
                        if($outs===0) $outs='-';
                        if($io===0) $io='-';
                }
                $output_pages[]=Array($url,$rawurl,$hits,$ins,$outs,$io,$percent, date('d/m/y H:i',$date) );
        }
        unset($data_pages);
}



// <!-- OUTPUT CREATION -->

$return='';

// Page title (also show in admin)
$phpstats_title=$string['pages_title'];

if($dataFound){
        $return.=
        "\n<script>".
        "\nfunction popup(url) {".
        "\n\ttest=window.open(url,'nome','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=320,HEIGHT=480,LEFT=0,TOP=0');".
        "\n}".
        "\n</script>";

        // title
        $return.="<span class=\"pagetitle\">$phpstats_title<br></span>";

        if(!empty($q)){
                $string['pages_results']=str_replace(Array('%query%','%trovati%','%hits%'),Array($q,$totalEntries,$totalHitsForSearchedWords),$string['pages_results']);
                $return.="<span class=\"testo\"><br>$string[pages_results]<br></span>";
        }

        if($pageNumber>1){
                $tmp=str_replace(Array('%current%','%total%'),Array($currentPage,$pageNumber),$varie['pag_x_y']);
                $return.="<div align=\"right\"><span class=\"testo\">$tmp&nbsp;&nbsp;</span></div>";
        }

        $return.=
        "<br><table border=\"0\" $style[table_header] width=\"95%\" class=\"tableborder\"><tr>".
        draw_table_title($string['pages_page'],'pagina',"admin.php?action=pages&mode=$mode&mode_2=$mode_2&q=".$q,$tables,$sortBy,$orderBy).
        draw_table_title($string['pages_hits'],'hits',"admin.php?action=pages&mode=$mode&mode_2=$mode_2&q=".$q,$tables,$sortBy,$orderBy).
        draw_table_title($string['pages_perc']).
        draw_table_title($string['pages_in'],'in',"admin.php?action=pages&mode=$mode&mode_2=$mode_2&q=".$q,$tables,$sortBy,$orderBy).
        draw_table_title($string['pages_out'],'out',"admin.php?action=pages&mode=$mode&mode_2=$mode_2&q=".$q,$tables,$sortBy,$orderBy).
        draw_table_title($string['pages_io'],'io',"admin.php?action=pages&mode=$mode&mode_2=$mode_2&q=".$q,$tables,$sortBy,$orderBy).
/*** */ draw_table_title('Data','date',"admin.php?action=pages&mode=$mode&mode_2=$mode_2&q=".$q,$tables,$sortBy,$orderBy).
        draw_table_title($string['pages_tracking']).
        draw_table_title($string['pages_delete']).
        "</tr>";

        for($i=0,$tot=count($output_pages);$i<$tot;++$i){
                list($url,$rawurl,$hits,$ins,$outs,$io,$percent,$date)=$output_pages[$i];

                $return.=
                "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
                "<td align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$url</span></td>".
                "<td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$hits</span></td>".
                "<td align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">{$percent}%</span></td>".
                "<td align=\"right\" bgcolor=$style[table_bgcolor] width=\"30\"><span class=\"tabletextA\">$ins</span></td>".
                "<td align=\"right\" bgcolor=$style[table_bgcolor] width=\"30\"><span class=\"tabletextA\">$outs</span></td>".
                "<td align=\"right\" bgcolor=$style[table_bgcolor] width=\"30\"><span class=\"tabletextA\">$io</span></td>".
/*** */         "<td align=\"right\" bgcolor=$style[table_bgcolor] width=\"30\"><span class=\"tabletextA\"><small><small>$date</small></small></span></td>".
                "<td bgcolor=$style[table_bgcolor] width=\"11\"><a href=\"javascript:popup('tracking.php?page=$rawurl');\"><img src=\"templates/$option[template]/images/icon_tracking.gif\" title=\"$string[pages_tracking_alt]\" border=0></a></td>".
                "<td bgcolor=$style[table_bgcolor] width=\"11\"><a href=\"admin.php?action=pages&q=$q&sort=$sort&order=$order&start=$start&delpage=$rawurl\" onclick=\"return confirmLink(this,'$string[pages_delete_confirm]')\"><img src=\"templates/$option[template]/images/icon_delete.gif\" title=\"$string[pages_delete_alt]\" border=0></a></td></tr>";
        }
        $return.="<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"8\" nowrap></td></tr>";

        if($pageNumber>1){
                $return.=
                "<tr><td bgcolor=$style[table_bgcolor] colspan=\"8\" height=\"20\" nowrap>".
                pag_bar("admin.php?action=pages&q=$q&mode=$mode&mode_2=$mode_2&sort=$sort&order=$order",$currentPage,$pageNumber,$recordPerPage).
                "</td></tr>";
//                "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"8\" nowrap></td></tr>";
        }
        $return.="</table>";

        // Search

        $return.=
        "<BR><center>\n".
        "<form action='./admin.php?action=pages&mode=$mode' method='POST' name=form1>\n".
        "<FONT face=verdana size=1>$string[search]:\n".
        "<input name=\"q\" type=\"text\" size=\"30\" maxlength=\"50\" value=\"$q\">".
        "<input type=\"submit\" value=\"$string[go]\">".
        "</FONT>".
        "</FORM>".
        "</center>";

        // SELEZIONE MODALITA'
        $return.="<br><center><table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
        if($mode!=0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=pages&mode=0&mode_2=$mode_2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_0]</span></a></td></tr>";
        if($mode!=1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=pages&mode=1&mode_2=$mode_2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_1]</span></a></td></tr>";
        if($mode!=2) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=pages&mode=2&mode_2=$mode_2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_2]</span></a></td></tr>";
/*        if($modulo[11]){
                if($mode_2!=0) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=pages&mode=$mode&mode_2=0\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_3]</span></a></td></tr>";
                if($mode_2!=1) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=pages&mode=$mode&mode_2=1\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_4]</span></a></td></tr>";
                if($mode_2!=2) $return.="<tr><td><span class=\"testo\"><a href=\"admin.php?action=pages&mode=$mode&mode_2=2\"><img src=templates/$option[template]/images/icon_changevis.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> $string[pages_mode_5]</span></a></td></tr>";
        }*/
        $return.="</table></center>";
}
else{
        if(empty($q)){
                $body="$string[no_pages]<br><br><br><a href=\"javascript:history.back();\"><-- $pref[back]</a>";
                $return.=info_box($string['information'],$body);
        }
        else $return.=info_box($string['information'],$error['pages']);
}
return($return);
}
?>
