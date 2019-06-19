<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

if(!isset($_SERVER)) $_SERVER=$HTTP_SERVER_VARS;
function main() {
global $db,$option,$style,$string,$varie,$modulo,$tabelle,$_SERVER,$phpstats_title;

// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['main_title'];

// Var definition
$hits_questo_mese=$visite_questo_mese=0;
list($date_G,$date_i,$date_m,$date_d,$date_Y)=explode('-',date('G-i-m-d-Y'));
$date_G-=$option['timezone'];
$oggi=date('Y-m-d',mktime($date_G,$date_i,0,$date_m,$date_d,$date_Y));
$this_year=date('Y',mktime($date_G,$date_i,0,$date_m,$date_d,$date_Y));
$ieri=date('Y-m-d',mktime($date_G,$date_i,0,$date_m,$date_d-1,$date_Y));
$questo_mese=date('Y-m-',mktime($date_G,$date_i,0,$date_m,$date_d,$date_Y));
$scorso_mese=date('Y-m-',(($date_d-0)<25 ? mktime($date_G,$date_i,0,$date_m-1,$date_d,$date_Y) : mktime($date_G,$date_i,0,$date_m,$date_d-31,$date_Y)));
// ACCESSI TOTALI
$result=sql_query("SELECT hits,visits,no_count_hits,no_count_visits FROM $option[prefix]_counters LIMIT 1");
list($hits_totali,$visite_totali,$total_spider_hits,$total_spider_visits)=mysql_fetch_row($result);
// AGGIUNGO ACCESSI DI PARTENZA
$hits_totali_glob=$hits_totali+$option['starthits'];
$visite_totali_glob=$visite_totali+$option['startvisits'];
// MODULO 6
if($modulo[6]):

/*** HO ELIMINATO I MOTORI DI RICERCA DAI CONTEGGI ***/
  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data='$oggi'");
  list($hits_oggi,$visite_oggi, $spider_hits_oggi, $spider_visits_oggi)=mysql_fetch_row($result);
  if(!isset($hits_oggi)) $hits_oggi=0;
  if(!isset($visite_oggi)) $visite_oggi=0;
  if(!isset($spider_hits_oggi)) $spider_hits_oggi = 0;
  if(!isset($spider_visits_oggi)) $spider_visits_oggi = 0;
  $hits_oggi   -= $spider_hits_oggi;
  $visite_oggi -= $spider_visits_oggi;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data='$ieri'");
  list($hits_ieri,$visite_ieri, $spider_hits_ieri, $spider_visits_ieri)=mysql_fetch_row($result);
  if(!isset($hits_ieri)) $hits_ieri=0;
  if(!isset($visite_ieri)) $visite_ieri=0;
  if(!isset($spider_hits_ieri)) $spider_hits_ieri = 0;
  if(!isset($spider_visits_ieri)) $spider_visits_ieri = 0;
  $hits_ieri   -= $spider_hits_ieri;
  $visite_ieri -= $spider_visits_ieri;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '$questo_mese%'");
  list($hits_questo_mese,$visite_questo_mese, $spider_hits_questo_mese, $spider_visits_questo_mese)=mysql_fetch_row($result);
  if(!isset($hits_questo_mese)) $hits_questo_mese=0;
  if(!isset($visite_questo_mese)) $visite_questo_mese=0;
  if(!isset($spider_hits_questo_mese)) $spider_hits_questo_mese = 0;
  if(!isset($spider_visits_questo_mese)) $spider_visits_questo_mese = 0;
  $hits_questo_mese   -= $spider_hits_questo_mese;
  $visite_questo_mese -= $spider_visits_questo_mese;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '$scorso_mese%'");
  list($hits_scorso_mese,$visite_scorso_mese, $spider_hits_scorso_mese, $spider_visits_scorso_mese)=mysql_fetch_row($result);
  if(!isset($hits_scorso_mese)) $hits_scorso_mese=0;
  if(!isset($visite_scorso_mese)) $visite_scorso_mese=0;
  if(!isset($spider_hits_scorso_mese)) $spider_hits_scorso_mese = 0;
  if(!isset($spider_visits_scorso_mese)) $spider_visits_scorso_mese = 0;
  $hits_scorso_mese   -= $spider_hits_scorso_mese;
  $visite_scorso_mese -= $spider_visits_scorso_mese;

  $result=sql_query("SELECT SUM(hits),SUM(visits),SUM(no_count_hits),SUM(no_count_visits) FROM $option[prefix]_daily WHERE data LIKE '%$this_year%'");
  list($hits_this_year,$visite_this_year, $spider_hits_this_year, $spider_visits_this_year)=mysql_fetch_row($result);
  if(!isset($hits_this_year)) $hits_this_year=0;
  if(!isset($visite_this_year)) $visite_this_year=0;
  if(!isset($spider_hits_this_year)) $spider_hits_this_year = 0;
  if(!isset($spider_visits_this_year)) $spider_visits_this_year = 0;
  $hits_this_year   -= $spider_hits_this_year;
  $visite_this_year -= $spider_visits_this_year;
/*** HO ELIMINATO I MOTORI DI RICERCA DAI CONTEGGI ***/

  // PRESET VALUE
  $giorno_inizio='';

  //  Giorni trascorsi
  $result=sql_query("SELECT data FROM $option[prefix]_daily ORDER BY data ASC LIMIT 0,1");
  if(mysql_affected_rows()>0)
    {
    while($row=mysql_fetch_row($result))
      {
      $giorno_inizio=$row[0]; // escludi oggi e primo giorno // by -=JackNight=- (Lucas Meier)
      list($anno_y,$mese_y,$giorno_y)=explode('-',$row[0]);
      $started=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($mese_y),$giorno_y,$anno_y),$varie['date_format']);
      }
    list($anno_t,$mese_t,$giorno_t)=explode('-',$oggi);
    $trascorsi=(mktime(0,0,0,$mese_t,$giorno_t,$anno_t)-mktime (0,0,0,$mese_y,$giorno_y,$anno_y))/86400;
    }
  else
    {
    $trascorsi=0;
    $started='-';
    }

endif;

if($modulo[3]):
  // Tempi medi di visita sito-pagine
  $tocount_pages=$visits_pages=$presence_pages=0;
  $result=sql_query("SELECT SUM(tocount),SUM(visits),SUM(no_count_visits),SUM(presence) FROM $option[prefix]_pages");
  list($tocount_pages,$visits_pages,$no_count_visits_pages,$presence_pages)=mysql_fetch_row($result);
  if($tocount_pages>0) $tempo_pagina=round(($presence_pages/$tocount_pages),0); else $tempo_pagina=0;
  $tempo_visita=round((($hits_totali-$total_spider_hits)/max(1,($visite_totali-$total_spider_visits)))*$tempo_pagina,0);

  // Utenti On-Line
  $tmp=($option['online_timeout']>0 ? $option['online_timeout']*60 : $tempo_pagina*1.3);
  $date=(time()-($option['timezone']*3600)-($tmp));
  $result_ol=sql_query("SELECT data FROM $option[prefix]_cache WHERE data>$date AND notbrowser=0");
  $online=mysql_num_rows($result_ol);
endif;

if($modulo[4]):
  $result=sql_query("SELECT SUM(visits) FROM $option[prefix]_referer");
  list($total_referer)=mysql_fetch_row($result);
  if(!$total_referer) $total_referer=0;

  $result=sql_query("SELECT SUM(visits) FROM $option[prefix]_query");
  list($total_engine)=mysql_fetch_row($result);
  if(!$total_engine) $total_engine=0;
endif;
/*UTILIZZARE QUESTO CODICE QUANDO SI VORRA' DIFFERENZIARE TRA SPIDER E GRABBER
  $result=sql_query("SELECT sum(hits),sum(visits) FROM $option[prefix]_systems WHERE os='Spider'");
  list($total_spider_hits,$total_spider_visits)=mysql_fetch_row($result);
  if(!$total_spider_hits) $total_spider_hits=0;
  if(!$total_spider_visits) $total_spider_visits=0;
  $result=sql_query("SELECT sum(hits),sum(visits) FROM $option[prefix]_systems WHERE os='Grabber'");
  list($total_grabber_hits,$total_grabber_visits)=mysql_fetch_row($result);
  $total_spider_hits+=$total_grabber_hits;
  $total_spider_visits+=$total_grabber_visits;
*/
if($modulo[6]):
// GIORNO "MIGLIORE" (pagine visitate)
$result=sql_query("SELECT data, hits FROM $option[prefix]_daily ORDER BY hits DESC LIMIT 1");
if(mysql_num_rows($result)>0)
  {
  list($max_hits_data,$max_hits)=mysql_fetch_row($result);
  $data=explode('-',$max_hits_data);
  $max_hits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $max_hits=$max_hits_data='-';

// GIORNO "MIGLIORE" (utenti unici) by -=JackNight=-
$result=sql_query("SELECT data,visits FROM $option[prefix]_daily ORDER BY visits DESC LIMIT 1");
if(mysql_num_rows($result)>0)
  {
  list($max_visits_data,$max_visits)=mysql_fetch_row($result);
  $data=explode('-',$max_visits_data);
  $max_visits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $max_visits=$max_visits_data='-';

// GIORNO "PEGGIORE" (pagine visitate)
$result=sql_query("SELECT data,hits FROM $option[prefix]_daily WHERE data!=NOW() AND data!='$giorno_inizio' ORDER BY hits ASC LIMIT 1"); // escludi oggi & primo giorno // by -=JackNight=-
if(mysql_num_rows($result)>0)
  {
  list($min_hits_data,$min_hits)=mysql_fetch_row($result);
  $data=explode('-',$min_hits_data);
  $min_hits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $min_hits=$min_hits_data='-';
endif;

// LIST OF MODS BY by -=JackNight=- (Lucas Meier)
// GIORNO "PEGGIORE" ((utenti unici)
$result=sql_query("SELECT data,visits FROM $option[prefix]_daily WHERE data!=NOW() AND data!='$giorno_inizio' ORDER BY visits ASC LIMIT 1"); // escludi oggi & primo giorno // by -=JackNight=-
if(mysql_num_rows($result)>0)
  {
  list($min_visits_data,$min_visits)=mysql_fetch_row($result);
  $data=explode('-',$min_visits_data);
  $min_visits_data=str_replace(Array('%mount%','%day%','%year%'),Array(formatmount($data[1]),$data[2],$data[0]),$varie['date_format']);
  }
else $min_visits=$min_visits_data='-';

// QUERY PIU' CERCATA
$result=sql_query("SELECT data,SUM(visits) AS dummy FROM $option[prefix]_query GROUP BY data ORDER BY dummy DESC LIMIT 1");
if(mysql_num_rows($result)>0) list($most_query_word,$most_query_word_volte)=mysql_fetch_row($result);
else $most_query_word=$most_query_word_volte='-';

// MOTORE PIU' USATO
$result=mysql_query("SELECT engine,SUM(visits) AS dummy FROM $option[prefix]_query GROUP BY engine ORDER BY dummy DESC");
if(mysql_num_rows($result)>0)
{
	list($most_used_engine,$most_used_engine_volte)=mysql_fetch_row($result);
	$most_query_word = utf8_decode($most_query_word);		/*** */
}
else
	$most_used_engine=$most_used_engine_volte='-';

// CALCOLO PERCENTUALE DEGLI ACCESSI DA MOTORI (dal totale escluso numero impostato in partenza)

$percent_engine_hits = ((100 * $total_engine)/($visite_totali-$total_spider_visits));
$percent_engine_hits = round($percent_engine_hits,1);

// CALCOLO PERCENTUALE DEGLI ACCESSI DA SITI ESTERNI (referer) (dal totale escluso numero impostato in partenza)

$percent_referer = ((100 * $total_referer)/($visite_totali-$total_spider_visits));
$percent_referer = round($percent_referer,1);

//TOTALE ACCESSI DIRETTI

$total_direct = $visite_totali-$total_engine/*-$total_referer*/-$total_spider_visits;

// CALCOLO PERCENTUALE DEGLI ACCESSI DIRETTI

$percent_direct = ((100 * $total_direct)/($visite_totali-$total_spider_visits));
$percent_direct = round($percent_direct,1);

// TOTALE ACCESSI DA MOTORI + REFERER

$total_extern = ($total_referer + $total_engine);

// CALCOLO PERCENTUALE DEGLI ACCESSI DA MOTORI + REFERER (escluso numero di partenza)

$percent_total_extern = ((100 * $total_extern)/($visite_totali-$total_spider_visits));
$percent_total_extern = round($percent_total_extern,1);
//END -=JackNight=- MODS

////////////////////////////////////
// VISUALIZZO I DATI DEL SOMMARIO //
////////////////////////////////////

$return=
"\n<center>\n<table width=\"95%\">\n<tr>\n<td valign=\"top\" align=\"center\">".
"\n\n<!--  SHOW STATS SUMMARY -->".
"\n<br>".
"\n<TABLE $style[table_header] width=\"100%\" class=\"tableborder\">".
"\n\t<TR><TD bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\"><center>$string[sommario]</center></span></TD></TR>".

/*** SOSTITUITE QUESTE 2 RIGHE ORIGINALI...
"\n\t<TR><TD align=right bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_tot]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$hits_totali_glob</span></TD>".
"\n\t<TR><TD align=right bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visitors_tot]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$visite_totali_glob</span></TD>".
...CON QUESTE 2 QUI SOTTO ***/
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_tot]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".($hits_totali_glob-$total_spider_hits)."</span></TD>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visitors_tot]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".($visite_totali_glob-$total_spider_visits)."</span></TD>";

/*if($modulo[11]):
$return.=
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_tot_hits_spider]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$total_spider_hits</span></TD>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_tot_visits_spider]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$total_spider_visits</span></TD>";
endif;*/

if($modulo[6]):
/*** Preleva dal batabase i visitatori di ritorno di oggi ***/
$result=sql_query("SELECT rets FROM $option[prefix]_daily WHERE data='$oggi' LIMIT 1");
list($returns_oggi)=mysql_fetch_row($result);
if(!isset($returns_oggi)) $returns_oggi=0;

$return.=
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_oggi]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$hits_oggi</span></TD></TR>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visitors_oggi]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$visite_oggi</span></TD></TR>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\" style=\"color: #00a000;\">$string[returns_oggi]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$returns_oggi</span></TD></TR>".  /*** VISITATORI DI OGGI ***/
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_ieri]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$hits_ieri</span></TD></TR>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visitors_ieri]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$visite_ieri</span></TD></TR>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_qm]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$hits_questo_mese</span></TD></TR>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visitors_qm]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$visite_questo_mese</span></TD></TR>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_sm]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$hits_scorso_mese</span></TD></TR>".
"\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visitors_sm]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$visite_scorso_mese</span></TD></TR>";

$result=sql_query("SELECT min(data) FROM $option[prefix]_daily");
$row=mysql_fetch_row($result);
$ini_year=substr($row[0],0,4);
if($this_year!=$ini_year && $ini_year!='')
  {
  $string['hits_qa']=str_replace('%THIS_YEAR%',$this_year,$string['hits_qa']);
  $string['visitors_qa']=str_replace('%THIS_YEAR%',$this_year,$string['visitors_qa']);
  $return.=
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_qa]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$hits_this_year</span></TD></TR>".
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visitors_qa]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$visite_this_year</span></TD></TR>";
  }
endif;
if($modulo[3]):
  $return.=
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[perm_site]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".formatperm($tempo_visita)."</span></TD>".
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[perm_page]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".formatperm($tempo_pagina)."</span></TD>";
endif;
if($modulo[6]):
  $return.=
//  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_per_day]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round(($hits_totali-$hits_oggi)/max(1,$trascorsi),1)."</span></TD>".
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[hits_per_day]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round(($hits_totali_glob-$hits_oggi-$total_spider_hits)/max(1,$trascorsi),1)."</span></TD>".

//  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visits_per_day]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round(($visite_totali-$visite_oggi)/max(1,$trascorsi),1)."</span></TD>".
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[visits_per_day]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round(($visite_totali_glob-$visite_oggi-$total_spider_visits)/max(1,$trascorsi),1)."</span></TD>".
  
//  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[pages_per_day]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round($hits_totali/max(1,$visite_totali),1)."</span></TD>".
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[pages_per_day]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round(($hits_totali_glob-$total_spider_hits)/max(1,$visite_totali-$total_spider_visits),1)."</span></TD>".

  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[stats_start]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$started</span></TD>".
// GIORNO MIGLIORE/PEGGIORE (visite) // by -=JackNight=- (Lucas Meier)
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".str_replace('%NUM%',$max_visits ,$string['max_visits'])."</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$max_visits_data</span></TD>".
///////////
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".str_replace('%NUM%',$min_visits ,$string['min_visits'])."</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$min_visits_data</span></TD>".
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".str_replace('%NUM%',$min_hits ,$string['min_hits'])."</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$min_hits_data</span></TD>".
  "\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".str_replace('%NUM%',$max_hits ,$string['max_hits'])."</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$max_hits_data</span></TD>";
endif;

if($modulo[3]):
  $return.="\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[usr_online]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$online</span></TD>";
endif;
if($modulo[3]==2):
  list($max_ol,$time_ol)=explode('|',$option['instat_max_online']);
  if($max_ol) {
    $tmp=str_replace(Array('%DATA%','%ORA%'),Array(formatdate($time_ol,3),formattime($time_ol,3)),$string['main_max_ol']);
    $return.="\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$tmp</span></TD><TD bgcolor=$style[table_bgcolor] valign=\"top\"><span class=\"tabletextB\">$max_ol</span></TD>";
    }
endif;
$return.=
//"\n\t<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\" height=\"1\"></td></tr>".
"\n</TABLE>\n<center>";


// SEPARATORE
$return.="\n<td width=\"5%\"></td>";

///////////////////
// Database Info //
///////////////////
$return.="\n</td>\n";
  $total=0;
  $return.=
  "\n<td valign=\"top\" align=middle>".
  "\n\n<!--  SHOW TABLES DETAILS -->".
  "\n<br>".
  "\n<table $style[table_header] width=\"100%\" border=\"0\" class=\"tableborder\">".
  "\n\t<TR><TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>$string[table_name]</center></span></TD><TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>$string[db_status_recs]</center></span></TD><TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>$string[db_status_size]</center></span></TD></TR>";


//  $result=sql_query("SHOW TABLE STATUS like '$option[prefix]_%'");
  $result=sql_query( str_replace('_', '\_', "SHOW TABLE STATUS like '$option[prefix]_%'") );

/***  while($row=mysql_fetch_row($result)) ***/
  while($row=mysql_fetch_array($result))
    {
    // ATTRIBUISCO GLI SPAZI OCCUPATI DELLE TABELLE
/***
	switch($row[0])
      {
      case '$option[prefix]_browser': $tmp=2048; break;
      case '$option[prefix]_domains': $tmp=6144; break;
      case '$option[prefix]_os': $tmp=2048; break;
      default: $tmp=1024; break;
      }
    $tmp=($tmp+$row[5])/1024;
***/
    $tmp = ($row['Data_length'] + $row['Index_length']) / 1024;


    $total+=$tmp;
/***    $return.="\n\t<TR><TD align=right bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".str_replace("$option[prefix]_","",$row[0])."</span></td><TD align=right bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$row[3]</span></td><TD align=right bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round($tmp,1)." KB</span></td></tr>"; ***/
    $return.="\n\t<TR><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".str_replace("$option[prefix]_","",$row['Name'])."</span></td><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$row[3]</span></td><TD align=\"right\" bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".round($tmp,1)." KB</span></td></tr>";
    }
  //}
  $return.=
  "\n\t<tr><td bgcolor=$style[table_bgcolor] colspan=\"3\" height=\"10\" nowrap><center><span class=\"tabletextA\">$string[db_size]</span><span class=\"tabletextB\">".round($total)." KB</span></center></td></tr>".
//  "\n\t<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\"></td></tr>".
  "\n</table>".
  "\n\n</td>";

$return.="\n</tr>\n</table>";

////////////////////
// Utenti On-Line //
////////////////////

if($modulo[3]):
$online=mysql_num_rows($result_ol);
if($online>0) {
  $return.=
  "\n\n<!--  SHOW USERS ONLINE DETAILS -->".
  "\n\n<script>".
  "\nfunction popup(url) {".
  "\n\tonline=window.open(url,'online','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=570,HEIGHT=350,LEFT=0,TOP=0');".
  "\n\t}".
  "\nfunction whois(url) {".
  "\nwhois2=window.open(url,'whois','SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=450,HEIGHT=600,LEFT=0,TOP=0');".
  "\n\t}".
  "\n</script>".
  "\n\n<br>".
  "\n<span class=\"pagetitle\"><center>$string[main_online_title]</center></span>".
  "\n<TABLE $style[table_header] width=\"95%\" class=\"tableborder\">".
  "\n\t<tr>".
  "\n\t".draw_table_title($string['main_online_ip']).
  "\n\t".draw_table_title($string['main_online_url']).
  "\n\t".draw_table_title($string['main_online_time']).
  "\n\t".draw_table_title($string['main_online_tracking']).
  "\n\t</tr>";

  $tmp=($option['online_timeout']>0 ? $option['online_timeout']*60 : $tempo_pagina*1.3);
  $date=(time()-($option['timezone']*3600)-($tmp));
  /*** Aggiunto 'AND notbrowser=0' per non visualizzare gli Spider come utenti online ***/
  $result=sql_query("SELECT user_id,data,lastpage FROM $option[prefix]_cache WHERE data>$date AND notbrowser=0 ORDER BY user_id ASC");
  while($row=mysql_fetch_array($result,MYSQL_ASSOC))
    {
    if($option['page_title']==1)
      {
/*** patch Francesco Mortara - fmortara@mfweb.it - 2012-01-06 */
		$row['lastpage'] = addslashes($row['lastpage']);
/*** */
       $result_title=sql_query("SELECT titlePage FROM $option[prefix]_pages WHERE data='$row[lastpage]' LIMIT 1");
       if(mysql_affected_rows()>0)
       {
        list($title_page)=mysql_fetch_row($result_title);
        $title_page=stripslashes($title_page);
       }
      else $title_page='';
      }
      $dottedIP=long2ip($row['user_id']);
      $return.=
      "\n\t<tr>".
      "\n\t<td align=\"right\" bgcolor=$style[table_bgcolor] width=\"50\" nowrap><span class=\"tabletextA\">";
      if($option['ext_whois']=='') $return.="<a href=\"javascript:whois('whois.php?IP=$dottedIP');\">$dottedIP</a>";
      else $return.="<a href=\"".str_replace('%IP%',$dottedIP,$option['ext_whois'])."\" target=\"_BLANK\">$dottedIP</a>";
      $return.=
      "</span></td>".
      "\n\t<td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">".formaturl($row['lastpage'],'',70,35,-30,$title_page)."</span></td>".
      "\n\t<td bgcolor=$style[table_bgcolor] width=\"50\"><span class=\"tabletextA\">".formattime($row['data'])."</span></td>".
      "\n\t<td bgcolor=$style[table_bgcolor] width=\"11\"><a href=\"javascript:popup('tracking.php?what=online&ip=$dottedIP');\"><img src=\"templates/$option[template]/images/icon_tracking.gif\" border=0 title=\"".$string['main_track_alt']."\"></a></td>".
      "\n\t</tr>";
    }
  $return.=
  "\n\t<tr>".
//  "\n\t<td bgcolor=$style[table_title_bgcolor] colspan=\"4\" height=\"1\"></td>".
  "\n\t</tr>".
  "\n</TABLE>";
  }
endif;

///////////////////////
// Motori Di Ricerca // by -=JackNight=- (Lucas Meier)
///////////////////////
  $return.=
  "\n\n<!-- SHOW ENGINE DETAILS -->".
  "\n<br>".
  "\n<table $style[table_header] width=\"95%\" border=\"0\" class=\"tableborder\">".
  "\n\t<TR><TD align=\"center\" bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\"><center>$string[se_title]</center></span></TD></TR>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_tot_engine] ($percent_engine_hits%)</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$total_engine</span></TD>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_tot_referer] ($percent_referer%)</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$total_referer</span></TD>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_tot_extern] ($percent_total_extern%)</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$total_extern</span></TD>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_tot_direct] ($percent_direct%)</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$total_direct</span></TD>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[searched_word_most] ($most_query_word_volte)</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$most_query_word</span></TD>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_most_engine] ($most_used_engine_volte)</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$most_used_engine</span></TD>".
//  "\n\t<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"3\"></td></tr>".
  "\n</table>";
////////////////////

if($option['show_average_user']==1)
  {
//////////////////
// Utente Medio //
//////////////////
$result=sql_query("SELECT os,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE os!='Spider' AND os!='Grabber' AND os!='?' GROUP BY os ORDER BY dummy DESC LIMIT 1");
$au_os='-';
$icon_os='';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_os=$tmp;
  $icon_os='<img src="images/os.php?q='.str_replace(' ','-',$au_os).'">';
  }

$result=sql_query("SELECT bw,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE os!='Spider' AND os!='Grabber' AND bw!='?' GROUP BY bw ORDER BY dummy DESC LIMIT 2");
$au_bw='-';
$icon_bw='';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_bw=$tmp;
  $icon_bw='<img src="images/browsers.php?q='.str_replace(' ','-',$au_bw).'">';
  }

$result=sql_query("SELECT reso,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE reso!='?' GROUP BY reso ORDER BY dummy DESC LIMIT 1");
$au_resolution='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_resolution=$tmp;
  }

$result=sql_query("SELECT colo,SUM(visits) AS dummy FROM $option[prefix]_systems WHERE colo!='?' GROUP BY colo ORDER BY dummy DESC LIMIT 1");
$au_colors='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  $au_colors=$tmp.' bit';
  }

$result=sql_query("SELECT tld,area,visits AS dummy FROM $option[prefix]_domains WHERE hits>0 AND tld!='unknown' AND tld!='lan' ORDER BY dummy DESC LIMIT 1");
$au_country='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$au_area,$dummy)=mysql_fetch_row($result);
  include("lang/$option[language]/domains_lang.php");
  $au_area=$domain_name['area_'.$au_area];
  $au_area=substr($au_area,0,-1);
  $au_country=$domain_name[$tmp].' ('.$au_area.')';
  }


$result=sql_query("SELECT lang,hits AS dummy FROM $option[prefix]_langs WHERE lang!='unknown' AND hits>0 ORDER BY dummy DESC LIMIT 1");
$au_lang='-';
if(mysql_num_rows($result)>0)
  {
  list($tmp,$dummy)=mysql_fetch_row($result);
  include("lang/$option[language]/bw_lang.php");
  $au_lang=$bw_lang[$tmp];
  }

$return.=
"\n\n<!--  SHOW AVERAGE USER -->".
"\n<br>".
"\n<table $style[table_header] width=\"95%\" border=\"0\" class=\"tableborder\">".
"\n\t<tr><td colspan=6 bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center><b>$string[average_user]</b></center></span></td></tr>".
"\n\t<TR>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[browser_bw]</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[os_os]</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[country]</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[bw_lang]</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[systems_reso]</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\">$string[systems_colo]</span></TD>".
"\n\t</TR>".
"\n\t<TR>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_bgcolor]><table><tr><td>$icon_bw</td><td><span class=\"tabletextA\"> $au_bw</span></td></tr></table></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_bgcolor]><table><tr><td>$icon_os</td><td><span class=\"tabletextA\"> $au_os</span></td></tr></table></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$au_country</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$au_lang</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$au_resolution</span></TD>".
"\n\t\t<TD align=\"center\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$au_colors</span></TD>".
"\n\t</TR>".
//"\n\t<tr><td height=\"1\" bgcolor=$style[table_title_bgcolor] colspan=\"6\"></td></tr>".
"\n</table>";
  }
/////////////////
// Server Info //
/////////////////
if($option['show_server_details']==1)
  {
  $return.=
  "\n\n<!--  SHOW SERVER DETAILS -->".
  "\n<br>".
  "\n<TABLE $style[table_header] width=\"95%\" class=\"tableborder\">".
  "\n\t<TR><TD bgcolor=$style[table_title_bgcolor] colspan=\"2\"><span class=\"tabletitle\"><center>$string[main_sysinfo_title]</center></span></TD></TR>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_server_os]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".(php_uname()=='' ? PHP_OS : php_uname())."</span></TD>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_server_ws]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".$_SERVER["SERVER_SOFTWARE"]."</span></TD>".
  "\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_server_php]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">".phpversion()."</span></TD>";
//  if(phpversion()>'4.0.5')
//    {
    $mysql_ver=mysql_get_server_info();
    if($mysql_ver=='') $mysql_ver=$string[main_no_mysql_ver];
    $return.="\n\t<TR><TD align=\"left\" bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">$string[main_mysql_ver]</span></TD><TD bgcolor=$style[table_bgcolor]><span class=\"tabletextB\">$mysql_ver</span></TD>";
//    }
  $return.=
//  "\n\t<tr><td bgcolor=$style[table_title_bgcolor] colspan=\"2\" height=\"1\"></td></tr>".
  "\n</TABLE>";
  }

return($return);
}
?>
