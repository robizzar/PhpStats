<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die("Php-Stats internal file.");

function optimize_tables(){
global $option,$string,$style,$phpstats_title;
// Titolo pagina (riportata anche nell'admin)
$phpstats_title=$string['optm_title'];

$tabelle=array("$option[prefix]_clicks",
               "$option[prefix]_config",
               "$option[prefix]_counters",
               "$option[prefix]_daily",
               "$option[prefix]_details",
               "$option[prefix]_domains",
               "$option[prefix]_downloads",
               "$option[prefix]_hourly",
               "$option[prefix]_ip",
               "$option[prefix]_langs",				 
               "$option[prefix]_pages",
               "$option[prefix]_query",
               "$option[prefix]_referer",
               "$option[prefix]_systems",
               "$option[prefix]_cache");

$query="OPTIMIZE TABLES ";
$count=0;
foreach ($tabelle as $value) 
  { 
  if($count>0)
    $query.=",$value";
	else
	$query.="$value";
  ++$count;
  }
$tmp=sql_query($query);
if(mysql_error()!="")
$return=info_box($string['error'],$string['optm_er']);
else
$return=info_box($string['information'],$string['optm_ok']);
return($return);
}
?>