<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

/////////////////////////////////////////////
// Preparazione varibili HTML del template //
/////////////////////////////////////////////
$autorefresh='';
$option['nomesito']=stripcslashes($option['nomesito']);
if(isset($option['autorefresh']) && $option['autorefresh']>0) $option['autorefresh']=$option['autorefresh']*60000;
else $option['autorefresh']=600000;
$meta="<META NAME='ROBOTS' CONTENT='NONE'>\n";
//$meta.='<meta http-equiv="Content-Type" content="text/html;charset=utf-8">';
$phpstats_title="Php-Stats - $phpstats_title";
if($refresh) $meta.="\n<META HTTP-EQUIV=\"refresh\" CONTENT=\"5;URL=$url\">"; // Refresh pagina breve
else if(!in_array($trad_action,$norefresh_action) && $option['autorefresh']>0) // Alcune pagine sono escluse dal refresh
$autorefresh=
"<script type='text/javascript'>
function selfRefresh(){
  location.href='".$option['script_url'].'/admin.php?'.$QUERY_STRING."';
}
setTimeout('selfRefresh()',$option[autorefresh]);
</script>";
if($update_msg) $meta.="\n".$update_msg;
//$generation_time=str_replace('%TOTALTIME%',round($end_time-$start_time,3),$varie['page_time']);

$total_queries=str_replace('%TOTALQUERIES%',$GLOBALS['totalqueries'],$varie['total_queries']);
//$server_time=str_replace('%SERVER_TIME%',date($varie['time_format']),$varie['server_time']);
$server_time=str_replace('%SERVER_TIME%',date('j/m/y H:i'),$varie['server_time']);

$handle = @fopen('browscap/browscap.ini', 'r');
if ($handle) {
	$i = 0;
    while ($i <= 10) {
		$buffer = fgets($handle);
		if (strpos($buffer, 'Released') !== false) {
        	$server_time .=  ' - Browscap rel.: '.substr($buffer, 14, 11);
        	break;
        }
    	$i++;
    }
	fclose($handle);
}

//////////////////////////////////
// Generazione HTML da template //
//////////////////////////////////
eval('$template="'.gettemplate("$template_path/admin.tpl").'";');

////////////////////////
// Ricostruzione menu //
////////////////////////

// Non visualizzo le voci che richiedono autentificazione se non si è loggati
if(!$is_loged_in) $template=preg_replace("'<!--Begin is_loged_in--[^>]*?>.*?<!--End is_loged_in-->'si","",$template);

// Non Visualizzo le voci che non sono state attivate nelle opzioni
$moduli_attivabili=Array('details','systems','bw_lang','pages_time','referer_engines','hourly','daily_monthly','country','downloads','clicks','ip');
for($i=0;$i<11;++$i)
  if(!$modulo[$i]) $template=preg_replace("'<!--Begin $moduli_attivabili[$i]--[^>]*?>.*?<!--End $moduli_attivabili[$i]-->'si",'', $template);

// Non Visualizzo la voce Links nel menù
if ($option['link_logger'] == 0)
	$template=preg_replace("'<!--Begin links--[^>]*?>.*?<!--End links-->'si",'', $template);

// Error-log viewer
if(!$option['logerrors']) $template=preg_replace("'<!--Begin errorlogviewer--[^>]*?>.*?<!--End errorlogviewer-->'si","", $template);
?>