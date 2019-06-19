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

if(isset($_GET['mode'])) $mode=addslashes($_GET['mode']); else $mode='?';

function bw_lang() {
global $db,$mode,$option,$string,$style,$error,$phpstats_title;
include("lang/$option[language]/bw_lang.php");
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['bw_lang_title'];
//
$return='';
if($mode==='hits') { $mode='hits'; $img="templates/$option[template]/images/style_bar_1.gif"; } else {$mode='visits'; $img="templates/$option[template]/images/style_bar_2.gif"; }
$total_visits=0;
$result=sql_query("select SUM($mode) from $option[prefix]_langs");
list($total_visits)=mysql_fetch_row($result);
if($total_visits>0)
  {
  $return.=
  "<span class=\"pagetitle\">$phpstats_title<br><br></span>".
  "<table border=\"0\" $style[table_header] width=\"90%\" align=\"center\" class=\"tableborder\"><tr>".
  draw_table_title($string['bw_lang']).
  draw_table_title($mode=='hits' ? $string['bw_lang_hits'] : $string['bw_lang_visits']).
  draw_table_title('').
  '</tr>';
  $result=sql_query("select lang,$mode AS value from $option[prefix]_langs WHERE $mode>0 ORDER BY $mode DESC");
  while($row=mysql_fetch_array($result,MYSQL_ASSOC))
    {
    $return.=
    "<tr onmouseover=\"setPointer(this, '$style[table_hitlight]', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
    "<td bgcolor=$style[table_bgcolor] align=\"right\" nowrap ><span class=\"tabletextA\">".$bw_lang[$row['lang']].'</span></td>'.
    "<td align=\"right\" bgcolor=$style[table_bgcolor] nowrap><span class=\"tabletextA\"><b>$row[value]</b></span></td>".
    "<td bgcolor=$style[table_bgcolor] nowrap><span class=\"tabletextA\"><img src=\"$img\" width=\"".($row['value']/$total_visits * 200)."\" height=\"7\"> (".round($row['value']*100/$total_visits,1).'%)</span></td>'.
    '</tr>';
    }
  $return.=
//  "<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"4\" nowrap></td></tr>".
  '</table>';

  if($mode==='hits') { $tipo=$string['visite']; $mode='visits';} else { $tipo=$string['hits']; $mode='hits'; }
  $return.="<br><center><img src=templates/$option[template]/images/icon_change.gif border=\"0\" align=\"absmiddle\" hspace=\"1\" vspace=\"1\"><span class='testo'> <a href=\"admin.php?action=bw_lang&mode=$mode\">".str_replace("%tipo%",$tipo,$string['mode'])."</a></span></center>";
  }
else $return.=info_box($string['information'],$error['bw_lang_none']);
return($return);
}
?>
