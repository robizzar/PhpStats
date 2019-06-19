<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

if(isset($_GET['analizzati'])) $analizzati=addslashes($_GET['analizzati'])-0; else $analizzati=0;
   if(isset($_GET['rimossi'])) $rimossi=addslashes($_GET['rimossi'])-0; else $rimossi=0;
if(isset($_GET['oldrimossi'])) $oldrimossi=addslashes($_GET['oldrimossi'])-0; else $oldrimossi=0;
     if(isset($_GET['start'])) $start=addslashes($_GET['start'])-0; else $start=0;
    if(isset($_GET['totali'])) $totali=addslashes($_GET['totali'])-0; else $totali=-1;

function refresh(){
global $option,$error,$style,$string,$modulo,$refresh,$url,$start,$analizzati,$rimossi,$oldrimossi,$totali,$phpstats_title;
$phpstats_title=$string['refr_title'];

// Inizializzo le variabili necessarie
$rimossi=0;
$num_refers=100; // Referer analizzati alla volta
$date=time();
$body='';

// Per evitare il timeout dello script. NOTA: Non ha effetto se php è in safe-mode.
set_time_limit(1200);

if($totali==-1){
        $restotali=sql_query("SELECT COUNT(1) FROM $option[prefix]_referer");
        list($totali)=mysql_fetch_row($restotali);
        $totali-=0;
}

$result=sql_query("SELECT data,visits,date FROM $option[prefix]_referer LIMIT $start,$num_refers");
while($row=mysql_fetch_row($result)){
        list($referer_data,$referer_visits,$referer_date)=$row;
        $refer=addslashes(stripslashes($referer_data));
        ++$analizzati;
        $engineResult=getengine($refer);
        if($engineResult!==FALSE){
                list($nome_motore,$domain,$query,$page)=$engineResult;
                $result2=sql_query("DELETE FROM $option[prefix]_referer WHERE data='$refer'");
                if(mysql_affected_rows()<1) $body.="$string[refr_err] ".formaturl($refer, '', 50, 20, -25).'<br>';
                else{
                        ++$rimossi;
                        if($modulo[4]==2){
                                $mese=date('Y-m',$referer_date); // determino il mese in base al time() del referer
                                $result2=sql_query("UPDATE $option[prefix]_query SET visits=visits+$referer_visits WHERE data='$query' AND engine='$nome_motore' AND domain='$domain' AND page='$page' AND mese='$mese'");
                        }
                        else $result2=sql_query("UPDATE $option[prefix]_query SET visits=visits+$referer_visits WHERE data='$query' AND engine='$nome_motore' AND domain='$domain' AND page='$page'");
                        if(mysql_affected_rows()<1){
                                if($modulo[4]==2) $result3=sql_query("INSERT INTO $option[prefix]_query VALUES('$query','$nome_motore','$domain','$page','$referer_visits',$date,'$mese')");
                                else $result3=sql_query("INSERT INTO $option[prefix]_query VALUES('$query','$nome_motore','$domain','$page','$referer_visits',$date,'')");
                        }
                }
        }
        //$result2=sql_query("UPDATE $option[prefix]_referer SET data='$refer' WHERE data='$row[data]'");
}
$body.=str_replace(Array('%analizzati%','%rimossi%'),Array($analizzati,$rimossi+$oldrimossi),$string['refr_summary']);
$start_tmp=$start+$num_refers-$rimossi;
$result2=sql_query("SELECT * FROM $option[prefix]_referer LIMIT $start_tmp,$num_refers");
// $righe=mysql_result(sql_query("SELECT COUNT(1) AS num FROM $option[prefix]_referer"), 0, "num");
// if($analizzati>=$righe) {
if(mysql_num_rows($result2)<=0) $body.='<br><br>'.$string['refr_end'];
else{
  $refresh=1;
  $oldrimossi=$oldrimossi+$rimossi;
  $url='admin.php?action=refresh&start='.($start+$num_refers-$rimossi)."&analizzati=$analizzati&rimossi=$rimossi&oldrimossi=$oldrimossi&totali=$totali";
  $body.='<br><br>'.str_replace(Array('%HOWMANY%','%URL%'),Array($num_refers,$url),$string['refr_next']);
  
}
$percent=round(200*($analizzati/$totali));
$body.="<br><br><table cellpadding=0 cellspacing=0 border=1 width=200><tr><td><img src=\"templates/$option[template]/images/style_bar_1.gif\" width=$percent height=7></td></tr></table>";
$return=info_box($string['refr_title'],$body);
return($return);
}
?>
