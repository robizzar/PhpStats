<?php
define('IN_PHPSTATS',true);

// Rilevo il tipo di modalità utilizzata: no_write o write
//include('option/php-stats_mode.php');
//if(!isset($NowritableServer)) $NowritableServer=1;

// DEFINIZIONE VARIABILI PRINCIPALI
//if($NowritableServer===0){
   define ('__OPTIONS_FILE__','option/php-stats-options.php');
   define ('__LOCK_FILE__','option/options_lock.php');
//   }

if(!isset($_COOKIE)) $_COOKIE=$HTTP_COOKIE_VARS;
if(!isset($_POST)) $_POST=$HTTP_POST_VARS;
if(isset($_POST['pswd'])) $pswd=addslashes($_POST['pswd']); else $pswd='';

require('config.php');
require('inc/main_func.inc.php');
require('inc/user_func.inc.php');

if($option['prefix']=='') $option['prefix']='php_stats';

db_connect();
$result=sql_query("SELECT value FROM $option[prefix]_config WHERE name='admin_pass'");
list($admin_pass)=mysql_fetch_row($result);
if(user_is_logged_in() || $pswd==$admin_pass) unlock_cache();
else{
    $return=
    '<html><title>:: Php-Stats - Unlock Cache ::</title><body>'.
    '<center><br><br>'.
    '<form action="unlock-cache.php" method="post">'.
    'Php-Stats Password: <input name="pswd" type="password" value=""><br><br>'.
    '<input type="submit" value="Invia - Send">'.
    '</center>'.
    '</body></html>';
    echo $return;
    }

if($option['persistent_conn']!=1) mysql_close();

function unlock_cache(){
global $db,$option,$default_pages;
sql_query("UNLOCK TABLES");
sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_cache_clone");
sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_cache_clone2");
sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_systems_clone");
sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_langs_clone");
sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_daily_clone");
sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_ip_clone");
sql_query("DROP TABLE /*!32300 IF EXISTS*/ $option[prefix]_domains_clone");

echo'<center>.:: OK - UNLOCK CACHE ::.</center>';
}
?>
