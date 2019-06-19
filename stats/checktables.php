<?php
// Per ragioni di sicurezza i file inclusi avranno un controllo di provenienza
define('IN_PHPSTATS', true);

// Rilevo il tipo di modalità utilizzata: no_write o write
//include('option/php-stats_mode.php');
//if(!isset($NowritableServer)) $NowritableServer=1;

// Richiamo variabili esterne - Call external vars
if(!isset($_COOKIE)) $_COOKIE=$HTTP_COOKIE_VARS;
if(!isset($_POST)) $_POST=$HTTP_POST_VARS;
if(isset($_POST['pswd'])) $pswd=addslashes($_POST['pswd']); else $pswd='';

// inclusione delle principali funzioni esterne
require('config.php');
require('inc/main_func.inc.php');
require('inc/user_func.inc.php');
if($option['prefix']=='') $option['prefix']='php_stats';

// Connessione a MySQL e selezione database
db_connect();

$tabelle=Array('clicks','config','counters','daily','details','domains','downloads','hourly','ip','langs','pages','query','referer','systems','cache');

//if($NowritableServer==1) array_push($tabelle,'options');

if(user_is_logged_in() || user_login(false, $pswd)) {

$page=
'<html>
<head>
<title>Php-Stats - Check Tables Utility</title>
</head>
<body>
<center>
<h3>Php-Stats - Check Tables Utility</h3>
<table border="0">';
for($i=0,$tot=count($tabelle);$i<$tot;++$i)
  {
  $page.=checktable($option['prefix'].'_'.$tabelle[$i], $option['prefix']);
  }
$page.=
'
</table>
</center>
</body>
</html>';
echo $page;
}
else{
    $return=
    '<html><title>:: Php-Stats - Check Tables Utility ::</title><body>'.
    '<center><br><br>'.
    '<form action="checktables.php" method="post">'.
    'Php-Stats Password: <input name="pswd" type="password" value=""><br><br>'.
    '<input type="submit" value="Invia - Send">'.
    '</center>'.
    '</body></html>';
    echo $return;
    }

function checktable($table, $prefix) {
$error=FALSE;
$return='';
$result=sql_query('CHECK TABLE '.$table);
while($row=mysql_fetch_row($result))
  {

  $row[0] = substr_replace(str_replace($prefix . '_', '', $row[0]), '', 0, strpos($row[0], '.') + 1);
  if($row[2]==='error')
    {
        if($row[3]==='The handler for the table doesn\'t support check/repair')
          {
            $row[2]='status';
            $row[3]='N/A';
            $errorcode='This table does not support check/repair';
          }
        else
          {
            $errorcode=$row[3];
            $row[2]='status';
            $row[3]='error';
          }
        $error=TRUE;
        $return.=
        "\n<tr>".
        "\n\t<td>$row[0]</td>".
        "\n\t<td>$row[1]</td>".
        "\n\t<td>$row[2]</td>".
        "\n\t<td bgcolor='red'>$row[3]</td>".
        "\n</tr>".
        "\n<tr>\n\t<td colspan=4 align='center'>Error: $errorcode</td>\n</tr>";
    }
  elseif($row[2]==='warning')
    {
        $errorcode=$row[3];
        $row[2]='status';
        $row[3]='warning';
        $error=TRUE;
        $return.=
        "\n<tr>".
        "\n\t<td>$row[0]</td>".
        "\n\t<td>$row[1]</td>".
        "\n\t<td>$row[2]</td>".
        "\n\t<td bgcolor='yellow'>$row[3]</td>".
        "\n</tr>".
        "\n<tr>\n\t<td colspan=4 align='center'>Warning: $errorcode</td>\n</tr>";
    }
  else
    {
      $return.=
      "\n<tr>".
      "\n\t<td>$row[0]</td>".
      "\n\t<td>$row[1]</td>".
      "\n\t<td>$row[2]</td>".
      "\n\t<td bgcolor='green'>$row[3]</td>".
      "\n</tr>";
    }
  }
if($error)
  {
    $result2=sql_query('REPAIR TABLE '.$table);
    while($row2=mysql_fetch_row($result2))
      {
        if($row2[3]!='OK')
        $return.="\n<tr>\n\t<td colspan=4 align='center' bgcolor='red'>REPAIR FAILED</td>\n</tr>";
        else
        $return.="\n<tr>\n\t<td colspan=4 align='center' bgcolor='green'>REPAIRED</td>\n</tr>";
      }
  }
return $return;
}
?>
