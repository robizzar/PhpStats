<?php

/**
 *  ___ _  _ ___       ___ _____ _ _____ ___ 
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|0.1.9.2|___/ |_/_/ \_\_| |___/
 *
 * Author:     Roberto Valsania - Webmaster76
 *
 * Staff:      Matrix - Massimiliano Coppola
 *             Viewsource
 *             PaoDJ - Paolo Antonio Tremadio
 *             Fabry - Fabrizio Tomasoni
 *             theCAS - Carlo Alberto Siti
 *
 * Version:    0.1.9.2
 *
 * Site:       http://php-stats.com/
 *             http://phpstats.net/
 *
 **/

// Per ragioni di sicurezza i file inclusi avranno un controllo di provenienza
define('IN_PHPSTATS', true);

                if(!isset($_POST)) $_POST=$HTTP_POST_VARS;
              if(!isset($_COOKIE)) $_COOKIE=$HTTP_COOKIE_VARS;
               if(!isset($_FILES)) $_FILES=$HTTP_POST_FILES;
    if(isset($_POST['operation'])) $operation=addslashes($_POST['operation']); else $operation='';
     if(isset($_POST['compress'])) $compress=addslashes($_POST['compress']); else $compress=0;
 if(isset($_POST['selected_tbl'])) $selected_tbl=$_POST['selected_tbl'];
$primary="";
$return="";
// inclusione delle principali funzioni esterne
include("../config.php");
include("./main_func.inc.php");
include("./admin_func.inc.php");
include("./user_func.inc.php");
if($option['prefix']=='') $option['prefix']='php_stats';

// Connessione a MySQL e selezione database
db_connect();

// Leggo le variabili
$result=sql_query("SELECT name,value FROM $option[prefix]_config");
while($row=mysql_fetch_array($result))
  {
  $option[$row[0]]=$row[1];
  }
// Controllo che l'utente abbia i permessi necessari altrimenti LOGIN
if(!user_is_logged_in()){
   if($option['persistent_conn']!=1) mysql_close();
   header("Location: $option[script_url]/admin.php?action=login"); exit();
}
// Inclusioni
include("../lang/$option[language]/main_lang.inc");
// Per evitare il timeout dello script
set_time_limit(1200);

$memory_limit=trim(ini_get('memory_limit'));
// Setto 3 MB di base
if(empty($memory_limit)) $memory_limit=3*1024*1024;

    if(strtolower(substr($memory_limit,-1))=='m') $memory_limit=(int)substr($memory_limit,0,-1)*1024*1024;
    elseif(strtolower(substr($memory_limit,-1))=='k') $memory_limit=(int)substr($memory_limit,0,-1)*1024;
    elseif(strtolower(substr($memory_limit,-1))=='g') $memory_limit=(int)substr($memory_limit,0,-1)*1024*1024*1024;
    else $memory_limit=(int)$memory_limit;
    if($memory_limit>1500000) $memory_limit-=1500000;
    $memory_limit*=2/3;

// Imposto il limite di guardia di 1/2 MB sotto il buffer
 $limit_memory_guard=$memory_limit-floor((1*1024*1024)/2);
 $data_buffer='';
 $data_buffer_lenght=0;

$date=date("Y-m-d");
if($compress==1) {
// ob_start();
// ob_implicit_flush(0);
header("Content-Type: application/x-gzip; name=\"php-stats[$date].sql.gz\"");
header("Content-disposition: attachment; filename=php-stat[$date].sql.gz");
}
else { if($compress!=0) exit(); else {
header("Content-Type: text/x-delimtext; name=\"php-stats[$date].sql\"");
header("Content-disposition: attachment; filename=php-stats[$date].sql");
}}

        $sql="select version() as version";
        $result=sql_query($sql,$db);
        $statrow=mysql_fetch_array($result);
        $version=$statrow["version"];
$dump_code=md5("code:$option[phpstats_ver]");
$str="
#---------------------------------------------------------
#
# Php-Stats Dump
#
# Host: $option[host]   Database: $option[database]
#---------------------------------------------------------
# Server version        $version
#---------------------------------------------------------
# Dump code: $dump_code
#---------------------------------------------------------

";

foreach($selected_tbl as $val)
{
	$str .= backup_tables($val);
	write_file_dump($str);
	$str='';
}
empty_buffer();

// Chiusura connessione a MySQL se necessario.
if($option['persistent_conn']!=1) mysql_close();

function write_file_dump($line)
{
 global $data_buffer,$limit_memory_guard,$compress;
 $data_buffer.=$line;
 $data_buffer_lenght=strlen($data_buffer);
 if($data_buffer_lenght>$limit_memory_guard)
 {
 	if($compress==1) $data_buffer=gzencode($data_buffer);
 	echo $data_buffer;
 	$data_buffer='';
 	$data_buffer_lenght=0;
 }
}

function empty_buffer()
{
 global $data_buffer,$compress;
 if($compress==1) $data_buffer=gzencode($data_buffer);
 echo $data_buffer;
}

/* backup the db OR just a table */
function backup_tables($table)
{
    $result = mysql_query('SELECT * FROM '.$table);
    $num_fields = mysql_num_fields($result);
    
    $return.= 'DROP TABLE '.$table.';';
    $row2 = mysql_fetch_row(mysql_query('SHOW CREATE TABLE '.$table));
    $return.= "\n\n".$row2[1].";\n\n";
    
    for ($i = 0; $i < $num_fields; $i++) 
    {
      while($row = mysql_fetch_row($result))
      {
        $return.= 'INSERT INTO '.$table.' VALUES(';
		$columns = count($row);
		for($j=0; $j<$columns; $j++)
		{
          $row[$j] = addslashes($row[$j]);
          $row[$j] = ereg_replace("\n","\\n",$row[$j]);
          if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
          if ($j<($num_fields-1)) { $return.= ','; }
        }
        $return.= ");\n";
      }
    }
    $return.="\n\n\n";
  return($return);
}
?>
