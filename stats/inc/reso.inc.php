<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');

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

$date=time()-$option['timezone']*3600;
list($mese,$anno)=explode('-',date('m-Y',$date));

if(isset($_GET['sel_mese'])) $sel_mese=addslashes($_GET['sel_mese']); else $sel_mese=$mese;
if(isset($_GET['sel_anno'])) $sel_anno=addslashes($_GET['sel_anno']); else $sel_anno=$anno;
     if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else /*if($modulo[1]<2) $mode=1; else*/ $mode=1;

function reso()
{
	global $db,$string,$error,$style,$option,$mode,$varie,$modulo,$phpstats_title;
	global $mese,$anno,$sel_anno,$sel_mese;

	// Titolo pagina (riportata anche nell'admin)
	if($mode==0)
		$phpstats_title=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$string['reso_colo_title_2']);
	else
		$phpstats_title=$string['reso_colo_title'];

//$return='';

	if(strlen("$sel_mese")<2)
		$sel_mese='0'.$sel_mese;
	$clause = ($mode==0 ? "WHERE mese='$sel_anno-$sel_mese' AND reso<>''" : "WHERE reso<>''")." AND os NOT REGEXP 'Spider|Grabber'";
	$query_bas = sql_query("SELECT sum(hits),sum(visits) FROM $option[prefix]_systems $clause");
	list($total_hits,$total_accessi) = mysql_fetch_row($query_bas);
	$query_tot = sql_query("SELECT reso,hits,visits FROM $option[prefix]_systems $clause");
	$num_totale = mysql_num_rows($query_tot);

	if($num_totale>0)
	{
// Titolo sezione Risoluzione
  		if($mode==0)
  			$tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$string['reso_title_2']);
  		else
  			$tmp=$string['reso_title'];
  		$return.="<span class=\"pagetitle\">$tmp</span>";

  		$result=sql_query("SELECT reso,SUM(hits) AS sumhits,SUM(visits) as sumvisits FROM $option[prefix]_systems $clause GROUP BY reso ORDER BY sumvisits DESC");
  		$return.=
  			"<br><br><table border=\"0\" width=\"90%\" $style[table_header] align=\"center\" class=\"tableborder\">".
  			"<tr><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[reso_reso]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[reso_hits]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td></tr>";
		$count=0;
  		$altre = Array('hits'=>0,'visits'=>0); //inizializzazione

  		while($row=mysql_fetch_array($result,MYSQL_ASSOC))
    	{
    		if($count<10 && $row['reso']!=='?')
      		{
      			$return.=
      				"<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
      				"<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$row[reso]</span></td>".
      				"<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$row[sumhits]</b></span><br><span class=\"tabletextA\"><b>$row[sumvisits]</b></span></td>".
      				"<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($row['sumhits']/MAX($total_hits,1)*350)."\" height=\"7\"> (".round($row['sumhits']*100/MAX($total_hits,1),1)."%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"".($row['sumvisits']/MAX($total_accessi,1)*350)."\" height=\"7\"> (".round($row['sumvisits']*100/MAX($total_accessi,1),1)."%)</span></td>".
      				"</tr>";
      		}
    		elseif ($row['reso']!=='?')
      		{
      			$altre['hits']+=$row['sumhits'];
      			$altre['visits']+=$row['sumvisits'];
      		}
    		++$count;
    	}
  		if($altre['hits']>0)
  			$return.="<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\"><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[reso_altre]</span></td><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$altre[hits]</b></span><br><span class=\"tabletextA\"><b>$altre[visits]</b></span></td><td  bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($altre['hits']/$total_hits * 350)."\" height=\"7\"> (".round($altre['hits']*100/$total_hits,1)."%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"".($altre['visits']/$total_accessi * 350)."\" height=\"7\"> (".round($altre['visits']*100/$total_accessi,1)."%)</span></td></tr>";

  		$return.=
  			"<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>".
  			"<tr><td bgcolor=$style[table_bgcolor] colspan=\"3\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>".
//  			"<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>".
  			"</table>".
  			"<br><br>";


// Titolo sezione Profondità di colore
  		if($mode==0)
  			$tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$string['colo_title_2']);
  		else
  			$tmp=$string['colo_title'];
  		$return.="<span class=\"pagetitle\">$tmp</span>";

		$clause=($mode==0 ? "WHERE mese='$sel_anno-$sel_mese' AND colo<>''" : "WHERE colo<>''")." AND os NOT REGEXP 'Spider|Grabber'";

  		$result=sql_query("SELECT colo,SUM(hits) AS sumhits,SUM(visits) AS sumvisits FROM $option[prefix]_systems $clause GROUP BY colo ORDER BY sumvisits DESC");
  		$return.=
  			"<br><br><table border=\"0\" width=\"90%\" $style[table_header] align=\"center\" class=\"tableborder\">".
  			"<tr><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[colo_colo]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center>$string[colo_hits]</center></span></td><td bgcolor=$style[table_title_bgcolor] nowrap><span class=\"tabletitle\"><center></center></span></td></tr>";
		$count = 0;
  		$altre = Array('hits'=>0,'visits'=>0); //inizializzazione

  		while($row=mysql_fetch_array($result,MYSQL_ASSOC))
    	{
    		if($count<10 && $row['colo']!=='?')
    		{
				$row['colo'].=' bit';
    			$return.=
    				"<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
    				"<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$row[colo]</span></td>".
    				"<td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$row[sumhits]</b></span><br><span class=\"tabletextA\"><b>$row[sumvisits]</b></span></td>".
    				"<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($row['sumhits']/MAX($total_hits,1)*350)."\" height=\"7\"> (".round($row['sumhits']*100/MAX($total_hits,1),1)."%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"".($row['sumvisits']/MAX($total_accessi,1)*350)."\" height=\"7\"> (".round($row['sumvisits']*100/MAX($total_accessi,1),1)."%)</span></td>".
    				"</tr>";
    		}
    		elseif ($row['colo']!=='?')
    		{
      			$altre['hits']+=$row['sumhits'];
      			$altre['visits']+=$row['sumvisits'];
    		}
    		++$count;
    	}
  		if($altre['hits']>0)
  			$return.="<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\"><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\">$string[colo_altre]</span></td><td bgcolor=$style[table_bgcolor] align=\"right\"><span class=\"tabletextA\"><b>$altre[hits]</b></span><br><span class=\"tabletextA\"><b>$altre[visits]</b></span></td><td  bgcolor=$style[table_bgcolor]><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"".($altre['hits']/$total_hits * 350)."\" height=\"7\"> (".round($altre['hits']*100/$total_hits,1)."%)</span><br><span class=\"tabletextA\"><img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"".($altre['visits']/$total_accessi * 350)."\" height=\"7\"> (".round($altre['visits']*100/$total_accessi,1)."%)</span></td></tr>";

  		$return.=
  			"<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>".
  			"<tr><td bgcolor=$style[table_bgcolor] colspan=\"3\" nowrap><span class=\"tabletextA\"><center><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=\"7\" height=\"7\"> $string[hits] <img src=\"templates/$option[template]/images/style_bar_2.gif\" width=\"7\" height=\"7\"> $string[visite]</span></center></td></tr>".
//  			"<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\" nowrap></td></tr>".
  			"</table>";
  	}
	else
  	{
  		if($mode==1)
  			$return.=info_box($string['information'],$error['reso']);
  		else
    	{
    		$tmp=str_replace(Array('%MESE%','%ANNO%'),Array(formatmount($sel_mese),$sel_anno),$error['reso_2']);
    		$return.=info_box($string['information'],$tmp);
    	}
  	}

	if($modulo[1]==2)
  	{
  		$return.='<br><br><center>';
  		if($mode==0)
    	{
    		// SELEZIONE MESE DA VISUALIZZARE
    		$return.=
    			"<form action='./admin.php' method='GET' name=form1><span class=\"tabletextA\">$string[calendar_view]</span>".
                "<input type=\"hidden\" name=\"action\" value=\"reso\">".
                "<input type=\"hidden\" name=\"mode\" value=\"$mode\">".
    			"<SELECT name=sel_mese>";
    		for($i=1;$i<13;++$i)
    			$return.="<OPTION value='$i'".($sel_mese==$i ? ' SELECTED' : '').'>'.$varie['mounts'][$i-1].'</OPTION>';
    		$return.=
    			'</SELECT>'.
    			'<SELECT name=sel_anno>';
    		$result=sql_query("SELECT min(data) FROM $option[prefix]_daily");
    		$row=mysql_fetch_row($result);
    		$ini_y=substr($row[0],0,4);
    		if($ini_y=='')
    			$ini_y=$anno;
    		for($i=$ini_y;$i<=$anno;++$i)
    			$return.="<OPTION value='$i'".($sel_anno==$i ? ' SELECTED' : '').">$i</OPTION>";
    		$return.=
    			"</SELECT>".
    			"<input type=\"submit\" value=\"$string[go]\">".
    			"<br><br><a href=\"admin.php?action=reso&mode=1\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'>  $string[os_vis_glob]</span></a>".
    			"</FORM>";
    	}
    else
    	$return.="<a href=\"admin.php?action=reso&mode=0\"><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'>  $string[os_vis_mens]</span></a>";
  	$return.='</center>';
	}
	return($return);
}
?>
