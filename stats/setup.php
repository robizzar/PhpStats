<?php
/*  ___ _  _ ___       ___ _____ _ _____ ___
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|       |___/ |_/_/ \_\_| |___/
 */


define('IN_PHPSTATS',true);
$php_stats_error='';
$style=''; // In caso di register globals=on
$page='';

//error_reporting(E_ERROR);

     if(isset($_POST['password'])) $password=$_POST['password']; else $password='';
       if(isset($_POST['action'])) $action=$_POST['action']; else $action='';
         if(isset($_POST['lang'])) $lang=$_POST['lang']; else if(isset($_GET['lang'])) $lang=$_GET['lang']; else $lang='';
      if(isset($_POST['license'])) $license=$_POST['license']; else $license=0;

         if(isset($_POST['host'])) $host=$_POST['host']; else $host='localhost';
     if(isset($_POST['database'])) $database=$_POST['database']; else $database='my_database';
      if(isset($_POST['user_db'])) $user_db=$_POST['user_db']; else $user_db='database';
      if(isset($_POST['pass_db'])) $pass_db=$_POST['pass_db']; else $pass_db='1234';
   if(isset($_POST['script_url'])) $script_url=$_POST['script_url']; else $script_url=curPageURL();


//echo "$action<br>";
 
if ($action>0 && $license!=1) { header("location: http://www.robertobizzarri.net/php-stats"); exit(); }

if ($action == 2)		// Sono stati inseriti i dati per il database: salva
{
	$host = str_replace(array('\'', '"'), '', $host);
	$database = str_replace(array('\'', '"'), '', $database);
	$user_db = str_replace(array('\'', '"'), '', $user_db);
	$pass_db = str_replace(array('\'', '"'), '', $pass_db);
	$script_url = str_replace(array('\'', '"'), '', $script_url);

	$save = "<?php
      \$option['host']='$host';						// Your mySQL o IP server address (usually: localhost)
  \$option['database']='$database';					// mySQL database Name
   \$option['user_db']='$user_db';					// mySQL database User
   \$option['pass_db']='$pass_db';					// mySQL database User Password
\$option['script_url']='$script_url';				// Php-Stats full URL
?>";
	file_put_contents('config_db.php', $save);
}


// Scelta lingua browser
$HTTP_ACCEPT_LANGUAGE=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
$lang_user=explode(',', getenv('HTTP_ACCEPT_LANGUAGE'));
$lang_user=explode('-',strtolower($lang_user[0]));
$lang_user=strtolower($lang_user[0]);
if($lang=='')
  {
  $lang=$lang_user;
  if(file_exists("setup_files/lang/$lang/setup_lang.inc")) $lang=$lang_user; else $lang='en';
  }
// inclusione delle principali funzioni esterne
if(!include('config.php')) die('<b>ERRORE</b>: File config.php non accessibile.');
if(!include('inc/main_func.inc.php')) die('<b>ERRORE</b>: File main_func.inc.php non accessibile.');
if(!include('inc/admin_func.inc.php')) die('<b>ERRORE</b>: File admin_func.inc.php non accessibile.');
if(!include('inc/user_func.inc.php')) die('<b>ERRORE</b>: File admin_func.inc.php non accessibile.');

if($option['prefix']=='') $option[prefix]='php_stats';

if ($action > 2)
{
	// Connessione a MySQL e selezione database
	db_connect();

	/*** Aggiunto controllo versioni ***/
	$mysql_ver = mysql_get_server_info();
	if (version_compare($mysql_ver, '4.0.0','<'))
		die('WARNING!<br>Database mySQL '.$mysql_ver.' not supported!<br>Required al least version 4.<br>');
	$php_ver = phpversion();
	if (version_compare($php_ver, '5.0.0','<'))
		die('WARNING!<br>PHP version '.$php_ver.' not supported!<br>Required al least version 5.<br>');
}

// Include lingua
include("setup_files/lang/$lang/setup_lang.inc");

// CONTROLLO SE ESISTE IL FILE php-stats.lock
$fp=fopen("php-stats.lock", "r");

if($fp!=False)
	$action="locked";

// Auto-login se necessario
switch ($action)
{
        case '0':
                $page.=
                "<h3>Php-Stats - ".$string['install']."</h3>\n".
                "<table border=\"1\" bordercolor=#000000 cellpadding=\"0\" cellspacing=\"0\" width=\"400\"><tr><td bordercolor=#EEEEEE>".
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" width=\"100%\">".
                "<tr><td bgcolor=#EEEEEE align=\"center\">".
                "<br><span class=\"testo\">$string[license]</span>".
                "<div align=\"center\"><form action=\"setup.php\" method=\"post\">\n".
                "<textarea name=\"text\" readonly cols=\"80\" rows=\"24\">\n";
                $page .= file_get_contents("setup_files/lang/$lang/license");
                $page.=
                "</textarea><br><br>".
                "<input type=\"radio\" name=\"license\" value=\"1\" class=\"radio\"><span class=\"testo\">$string[license_ok]</span>     <input type=\"radio\" name=\"license\" value=\"0\" class=\"radio\" checked><span class=\"testo\">$string[license_no]</span>".
                "<br><br>".
                "<input type=\"hidden\" name=\"action\" value=\"1\">".
                "<input type=\"hidden\" name=\"lang\" value=\"$lang\">".
                "<input type=\"submit\" name=\"op\" value=\"$string[next]\"><br><br>".
                "</td></tr>".
                "</table>".
                "</td></tr>".
                "</table>".
                "</form></div>\n";
        break;

        case '1':
                $page.=
                "<h3>Php-Stats - ".$string['install']." (step 1/5)</h3>\n".
                "<FORM action=\"setup.php\" method=post name=regform>".
                "<table border=\"1\" bordercolor=#000000 cellpadding=\"0\" cellspacing=\"0\" width=\"600\"><tr><td bordercolor=#EEEEEE>".
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" width=\"100%\">".
                "<tr><td bgcolor=#EEEEEE align=\"center\">".
                "<br>".
                "<table width=\"100%\">".
                "<tr><td align=\"right\"><span class=\"testo\">mySQL address</span></td><td><INPUT name=\"host\" tabIndex=\"1\" type=\"text\" value=\"$host\" style=\"width:90%;\"></td></tr>".
                "<tr><td align=\"right\"><span class=\"testo\">Database name</span></td><td><INPUT name=\"database\" tabIndex=\"2\" type=\"text\" value=\"$database\" style=\"width:90%;\"></td></tr>".
                "<tr><td align=\"right\"><span class=\"testo\">Database User</span></td><td><INPUT name=\"user_db\" tabIndex=\"3\" type=\"text\" value=\"$user_db\" style=\"width:90%;\"></td></tr>".
                "<tr><td align=\"right\"><span class=\"testo\">Database User Password</span></td><td><INPUT name=\"pass_db\" tabIndex=\"4\" type=\"text\" value=\"$pass_db\" autocomplete=\"off\" style=\"width:90%;\"></td></tr>".
                "<tr><td align=\"right\"><span class=\"testo\">Php-Stats full URL</span></td><td><INPUT name=\"script_url\" tabIndex=\"5\" type=\"text\" value=\"$script_url\" style=\"width:90%;\"></td></tr>".
                "</table>".
                "<center>".
                "<br>".
                "<input type=\"hidden\" name=\"action\" value=\"2\">".
                "<input type=\"hidden\" name=\"lang\" value=\"$lang\">".
                "<input type=\"hidden\" name=\"license\" value=\"$license\">".
                "<input type=\"submit\" name=\"op\" value=\"$string[next]\"><br><br>".
                "</center>".
                "</td></tr>".
                "</table>".
                "</td></tr>".
                "</table>".
                "</FORM>";
        break;

        case '2':
                $page.=
                "<h3>Php-Stats - ".$string['install']." (step 2/5)</h3>\n".
                "<form action=\"setup.php\" method=\"post\">".
                "<table border=\"1\" bordercolor=#000000 cellpadding=\"0\" cellspacing=\"0\" width=\"400\"><tr><td bordercolor=#EEEEEE>".
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" width=\"100%\">".
                "<tr><td bgcolor=#EEEEEE align=\"center\">".
                "<br><span class=\"testo\">$string[step_1_1]".
                "<br><br><br><input type=\"checkbox\" name=\"reset_mysql\" value=\"1\">$string[step_1_2] $string[step_1_3]".
                "</span><br><br><br>".
                "<input type=\"hidden\" name=\"action\" value=\"3\">".
                "<input type=\"hidden\" name=\"lang\" value=\"$lang\">".
                "<input type=\"hidden\" name=\"license\" value=\"$license\">".
                "<input type=\"submit\" name=\"op\" value=\"$string[next]\"><br><br>".
                "</td></tr>".
                "</table>".
                "</td></tr>".
                "</table>".
                "</form>";
        break;

        case '3':
                $page.=
                "<h3>Php-Stats - ".$string['install']." (step 3/5)</h3>\n".
                "<table border=\"1\" bordercolor=#000000 cellpadding=\"0\" cellspacing=\"0\" width=\"400\"><tr><td bordercolor=#EEEEEE>".
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" width=\"100%\">".
                "<tr><td bgcolor=#EEEEEE align=\"center\">";
 				if ($_POST['reset_mysql'] == '1') {
                	exec_sql_lines("setup_files/sql/reset_tables.sql");
                	$ok_1=exec_sql_lines("setup_files/sql/tables.sql");
                }
                else {
                	$ok_1=exec_sql_lines("setup_files/sql/tables.sql");
                }
                if($ok_1==false)
                   $page.="<span class=\"testo\">".$error['error_updating']."<br><br><br>$php_stats_error</span>";
                else
                {
                  $page.=
                  "<br><img style=\"vertical-align: middle; padding-right: 20px;\" src=\"setup_files/done.png\"><span class=\"testo\">".$string['done']."</span><br><br>".
                  "<div align=\"center\"><form action=\"setup.php\" method=\"post\">\n".
                  "<input type=\"hidden\" name=\"action\" value=\"4\" />\n".
                  "<input type=\"hidden\" name=\"lang\" value=\"$lang\" />\n".
                  "<input type=\"hidden\" name=\"license\" value=\"$license\">".
                  "<input type=\"submit\" name=\"op\" value=\"$string[next]\" />\n".
                  "</form></div>\n";

				  /*** Creazione copia di backup di browscap.ini ***/
				  copy('browscap/browscap.ini', 'browscap/browscap_bk.ini');

				  /*** QUI POSSO CORREGGERE LE TABELLE PER ADEGUARLE ALLA VERSIONE ATTUALE ***/

				  // V1.3: controllo se i nuovi campi introdotti con la v1.3 esistono, altrimenti li creo.
				  if (mysql_query("SELECT rets FROM $option[prefix]_details WHERE 1") === FALSE)
				  {
					mysql_query("ALTER TABLE $option[prefix]_details ADD rets INT(11) NOT NULL DEFAULT '0'");
					mysql_query("ALTER TABLE $option[prefix]_details ADD last_return INT(11) UNSIGNED NOT NULL DEFAULT '0'");
				  }
				  if (mysql_query("SELECT rets FROM $option[prefix]_daily WHERE 1") === FALSE)
					mysql_query("ALTER TABLE $option[prefix]_daily ADD rets INT(11) NOT NULL DEFAULT '0'");

				  // v1.5.2
				  // Adeguamento del campo 'host' per contenere 80 caratteri invece di 50 (utile nel caso il database sia stato aggiornato da versioni molto vecchie di PHP-Stats).
				  mysql_query("ALTER TABLE $option[prefix]_details CHANGE host host varchar(80) NOT NULL DEFAULT ''");
				  // Le versioni precedenti posso aver inserito delle query vuote: qui vengono eliminate.
				  mysql_query("DELETE FROM $option[prefix]_query WHERE data=''");
				  /***************************************************************************/
					
					logerrors(date('d/m/y H:i') . ' ** PHP-Stats Installed.');

                  }
                  $page.=
                  '</td></tr>'.
                  '</table>'.
                  '</td></tr>'.
                  '</table>';
        break;

        case '4':
                $page.=
                "<SCRIPT language=\"Javascript\" src=\"setup_files/lang/$lang/check.js\"></SCRIPT>".
                "<h3>Php-Stats - ".$string['install']." (step 4/5)</h3>\n".
                "<FORM action=\"setup.php\" method=post name=regform>".
                "<table border=\"1\" bordercolor=#000000 cellpadding=\"0\" cellspacing=\"0\" width=\"400\"><tr><td bordercolor=#EEEEEE>".
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" width=\"100%\">".
                "<tr><td bgcolor=#EEEEEE align=\"center\">".
                "<br>".
                "<table>".
                "<tr><td align=\"right\"><span class=\"testo\">$string[step_3_1]</span></td><td><INPUT maxLength=\"30\" name=\"password\" tabIndex=\"1\" type=\"password\"></td></tr>".
                "<tr><td align=\"right\"><span class=\"testo\">$string[step_3_2]</span></td><td><INPUT maxLength=\"30\" name=\"password2\" tabIndex=\"2\" type=\"password\"></td></tr>".
                "</table>".
                "<center>".
                "<br>".
                "<input type=\"hidden\" name=\"lang\" value=\"$lang\" />\n".
                "<input type=\"hidden\" name=\"license\" value=\"$license\">".
                "<input type=\"hidden\" name=\"action\" value=\"5\" />".
                "<INPUT name=Submit onclick=GlobalCheck() tabIndex=21 type=button value=\"$string[next]\"><br><br>".
                "</center>".
                "</td></tr>".
                "</table>".
                "</td></tr>".
                "</table>".
                "</FORM>";

        break;

        case '5':
                user_change_password($password);
                sql_query("UPDATE $option[prefix]_config SET value='$lang' WHERE name='language'");
                $error_config=FALSE;
                $page_list=Array('main','details','os_browser','reso','systems','pages','percorsi','time_pages','referer','engines','query','searched_words','hourly','daily','weekly','monthly','calendar','compare','ip','country','bw_lang','downloads','clicks','trend');
                $error_config=create_option_file();
                $fp=fopen("php-stats.lock", "w+");
                if($fp!=False) $locked=1; else $locked=0;
                $page.=
                "<h3>Php-Stats - ".$string['install']." (step 5/5)</h3>\n".
                "<form action=\"admin.php?action=preferenze\" method=\"post\">".
                "<table border=\"1\" bordercolor=#000000 cellpadding=\"0\" cellspacing=\"0\" width=\"400\"><tr><td bordercolor=#EEEEEE>".
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" width=\"100%\">".
                "<tr><td bgcolor=#EEEEEE align=\"center\">".
                //($locked===0 ? "<font color=\"#FF0600\">$string[step_4_1]</font><br><br>" : '').
                "<br><span class=\"testo\">$string[step_4_2]</span><br>".
                "<textarea readonly name=\"mainscript\" cols=\"100%\" rows=\"6\" style=\"border-color: green; border-width: 3px; margin: 10px; font-size: 12px;\">".
                "<script type=\"text/javascript\" src=\"$option[script_url]/php-stats.js.php\"></script>\n<noscript><img src=\"$option[script_url]/php-stats.php\" border=\"0\" alt=\"\"></noscript>".
                "</textarea><br><br>\n".
                "<span class=\"testo\">$string[step_4_3]</span><br>".
                "<textarea readonly name=\"mainscript\" cols=\"100%\" rows=\"4\"style=\"border-color: red; border-width: 3px; margin: 10px; font-size: 12px;\">";

                if(isset($_SERVER['SCRIPT_FILENAME']) && strpos($_SERVER['SCRIPT_FILENAME'],'/cgi-bin/')===FALSE)
                	$path_include=dirname($_SERVER['SCRIPT_FILENAME']).'/';
                else
                {
                    $path_include=(!isset($_SERVER['DOCUMENT_ROOT']) ? dirname($_SERVER['PATH_TRANSLATED']) : $_SERVER['DOCUMENT_ROOT'].str_replace('\\','/',dirname($_SERVER['PHP_SELF']))).'/';
                    $path_include=str_replace(Array('//','\\\\'),'/',$path_include);
                }
                if(strpos($path_include,':')===1)
                	$path_include=substr($path_include,2);

                $page.=
                "&lt;?php\ndefine('__PHP_STATS_PATH__','$path_include');\ninclude(__PHP_STATS_PATH__.'php-stats.redir.php');\n?&gt;".
                "</textarea><br><br>\n".
                ($error_config ? "<input type=\"hidden\" name=\"action\" value=\"error_config\" />" : '').
                "<input type=\"submit\" name=\"op\" value=\"$string[end]\"><br><br>".
                "</td></tr>".
                "</table>".
                "</td></tr>".
                "</table>".
                "</form></div>\n";
        break;

        case'locked':
                $page.=
                "<h3>Php-Stats: $string[install].</h3>\n".
                "<br><table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\" width=\"300\">".
                "<tr><td bgcolor=\"#707888\" nowrap><span class=\"tabletitle\">".$string['box_title']."</span></td>".
                "<tr><td align=\"center\" valign=\"middle\" bgcolor=\"#EEEEEE\" nowrap>".
                "<table width=\"100%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"5\" border=\"0\"><tr><td align=\"center\" valign=\"middle\"  nowrap>".
                "<span class=\"testo\">".$error['locked']."</span>".
                "</td></tr></table>".
                "</table><br><br><br>";
        break;

        case'error_config':
                $page.=
                "<h3>Php-Stats: $string[install].</h3>\n".
                "<br><table border=\"0\" cellpadding=\"1\" cellspacing=\"1\" align=\"center\" width=\"300\">".
                "<tr><td bgcolor=\"#707888\" nowrap><span class=\"tabletitle\">".$string['box_title']."</span></td>".
                "<tr><td align=\"center\" valign=\"middle\" bgcolor=\"#EEEEEE\" nowrap>".
                "<table width=\"100%\" height=\"100%\" cellpadding=\"0\" cellspacing=\"5\" border=\"0\"><tr><td align=\"center\" valign=\"middle\"  nowrap>".
                "<span class=\"testo\">".$error['error_config']."</span>".
                "</td></tr></table>".
                "</table><br><br><br>";
        break;

        default:
                $page.=
                "<h3>Php-Stats - ".$string['install']."</h3>".
                "<form action=\"setup.php\" method=\"post\">".
                "<table border=\"1\" bordercolor=#000000 cellpadding=\"0\" cellspacing=\"0\" width=\"300\"><tr><td bordercolor=#EEEEEE>".
                "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" width=\"100%\">".
                "<tr><td bgcolor=#EEEEEE align=\"center\">".
                "<br><span class=\"testo\">$string[lang_select]  </span>".
                "<select name=\"lang\" onchange=\"window.location=('setup.php?lang='+this.options[this.selectedIndex].value)\">";

                // Inizio lettura directory LINGUE
                $location="setup_files/lang/";
                $hook=opendir($location);
                while(($file=readdir($hook))!==false)
                {
                	if($file!="." && $file!="..")
                    {
                       $path=$location . "/" . $file;
                       if(is_dir($path)) $elenco0[]=$file;
                    }
                }
                closedir($hook);
                natsort($elenco0);
                // Fine lettura directory LANG

                while(list ($key, $val)=each ($elenco0))
                {
                     $val=chop($val);
                     // Leggo il nome della lingua
                     $language_name=file("setup_files/lang/$val/lang.name");
                     $page.="<option value=\"$val\""; if($lang==$val) { $page.="selected"; } $page.=">$language_name[0]</option>";
                }

				// Modifica permessi dei file (non tutti i server lo consentono, in questi casi va fatto a mano)
				chmod('config.php', 0666);
				chmod('option', 0777);
				chmod('browscap', 0777);
				chmod('browscap/browscap.ini', 0666);

				$perm1 = substr(sprintf("%o\n", fileperms('config.php')), -4);
				$perm2 = substr(sprintf("%o\n", fileperms('option')), -4);
				$perm3 = substr(sprintf("%o\n", fileperms('browscap')), -4);
				$perm4 = substr(sprintf("%o\n", fileperms('browscap/browscap.ini')), -4);

				$permicon1 = ($perm1 == 666 ? 'ok.png' : 'error.png');
				$permicon2 = ($perm2 == 777 ? 'ok.png' : 'error.png');
				$permicon3 = ($perm3 == 777 ? 'ok.png' : 'error.png');
				$permicon4 = ($perm4 == 666 ? 'ok.png' : 'error.png');

                $page.=
                "</select>".
                "<input type=\"hidden\" name=\"action\" value=\"0\"><br><br>".
                "<input type=\"submit\" name=\"op\" value=\"$string[next]\"><br><br>".
                "</td></tr>".
                "</table>".
                "</td></tr>".
                "</table>".
                "</form>\n".
                "<br><hr><br>".
                '<span class="testo">' . $string['permits_0'].
				"<table border=\"1\" cellpadding=\"2\" cellspacing=\"2\">
				  <tbody>
					<tr>
					  <td style=\"text-align: center;\"><span class=\"tablereph\">{$string['permits_1']}</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablereph\">{$string['permits_2']}</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablereph\">{$string['permits_3']}</span></td>
					  <td style=\"text-align: center;\"></td>
					</tr>
					<tr>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">config.php</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">".$perm1."</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">666</span></td>
					  <td style=\"text-align: center;\"><img src=\"setup_files/".$permicon1."\"></td>
					</tr>
					<tr>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">option/</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">".$perm2."</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">777</span></td>
					  <td style=\"text-align: center;\"><img src=\"setup_files/".$permicon2."\"></td>
					</tr>
					<tr>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">browscap/</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">".$perm3."</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">777</span></td>
					  <td style=\"text-align: center;\"><img src=\"setup_files/".$permicon3."\"></td>
					</tr>
					<tr>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">browscap/browscap.ini</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">".$perm4."</span></td>
					  <td style=\"text-align: center;\"><span class=\"tablerepb\">666</span></td>
					  <td style=\"text-align: center;\"><img src=\"setup_files/".$permicon4."\"></td>
					</tr>
				  </tbody>
				</table>";

       break;
}

///////////////
// HTML PAGE //
///////////////
$html=
"\n<html>".
"\n<head>".
"\n<title>Php-Stats - ".$string['install']."</title>".
"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">".
"\n<style type=\"text/css\">".
//"\n<!--".
//"\nselect, option, textarea, input { border-right: #9dbecd 1px solid; border-top: #9dbecd 1px solid; border-bottom: #9dbecd 1px solid; border-left: #9dbecd 1px solid; color: #000000; font-size: 10px; font-family: verdana; background-color: #ffffff; }".
//"\n.radio { border-right: #9dbecd 0px solid; border-top: #9dbecd 0px solid; border-bottom: #9dbecd 0px solid; border-left: #9dbecd 0px solid; color: #000000; font-size: 10px; font-family: verdana; background-color: #eeeeee }".
"\na:visited,a:active {text-decoration:none; color:#000000; font-weight:plain;}".
"\na:hover {text-decoration:none; color:#AA0000; font-weight:plain;}".
"\na:link { text-decoration:none; color:#000000; font-weight:plain; }".
"\n.testo {  font-family: Verdana; font-size: small; color:#000000; text-align: justify; }".
"\n.tabletitle { font-family: Verdana; font-size: small; color: #FFFFFF ; font-weight: bold}".
"\n.tablereph { font-family: Verdana; font-size: small; color: #000000; font-weight: bold; }".
"\n.tablerepb { font-family: monospace; font-size: medium; color: #555555; font-weight: bold; }".
//"\n-->".
"\n</style>".
"\n</head>".
"\n<body>".
"\n<br>".
"\n<br>".
"\n<TABLE cellSpacing=\"1\" cellPadding=\"1\" width=\"760\" height=\"100%\" align=\"center\" bgColor=\"black\">".
"  <TBODY>".
"    <TR>".
"      <TD  height=30 bgColor=#c1c1c1>".
"            <TABLE width=740 cellSpacing=0 cellPadding=0>".
"            <TR>".
"              <TD><IMG alt=\"\" src=\"setup_files/logo.png\"></TD>".
"            </TR>".
"        </TABLE>".
"          </TD>".
"    </TR>".
"    <TR>".
"      <TD vAlign=top bgColor=#d9dbe9><CENTER><br><br>".
$page.
"        </CENTER>".
"  </TBODY>".
"\n</TABLE>".
"\n</body>".
"\n</html>";

// Restituisco la pagina!
echo"$html";



/*--------------------------------------------------
  remove_remarks()
  Remove # type remarks from large sql files
  --------------------------------------------------*/
function remove_remarks($sql) {
$i=0;
while($i<strlen($sql))
  {
  if($sql[$i]=="#" and ($i==0 or $sql[$i-1]=="\n"))
    {
    $j=1;
    if(!isset($sql[$i+$j])) $sql[$i+$j]=""; // 0.1.8
    while($sql[$i+$j]!="\n")
          {
          ++$j;
      if($j+$i >= strlen($sql)) break;
          }
    $sql=substr($sql,0,$i).substr($sql,$i+$j);
    }
  ++$i;
  }
return($sql);
}



/*--------------------------------------------------
  split_sql_file()
  Split up a large sql file into individual queries
  --------------------------------------------------*/
function split_sql_file($sql, $delimiter)
{
	global $option;

	// BEGIN Cambio prefisso della tabella!!!!
	$sql=str_replace("php_stats","$option[prefix]",$sql);
	// END Cambio prefisso della tabella!!!!
	$sql=trim($sql);
	$char="";
	$last_char="";
	$ret=array();
	$in_string=true;
	for($i=0; $i<strlen($sql); ++$i)
	{
		$char=$sql[$i];

		/* if delimiter found, add the parsed part to the returned array */
		if($char == $delimiter && !$in_string)
		{
			$ret[]=substr($sql, 0, $i);
			$sql=substr($sql, $i + 1);
			$i=0;
			$last_char="";
		}

		if($last_char == $in_string && $char == ")")
			$in_string=false;
		if($char == $in_string && $last_char != "\\")
			$in_string=false;
		elseif(!$in_string && ($char == "\"" || $char == "'") && ($last_char != "\\"))
			$in_string=$char;
		$last_char=$char;
	}

	if(!empty($sql))
		$ret[]=$sql;
	return($ret);
}



/*--------------------------------------------------
  exec_sql_lines()
  takes a file and executes all its sql-queries
  uses remove_remark() and split_sql_file()
  --------------------------------------------------*/
function exec_sql_lines($sql_file, $old_string='', $new_string='')
{
	global $php_stats_error;
	$error_lev=0;
	$sql_query=isset($sql_query) ? $sql_query : "";

	if(!empty($sql_file) && $sql_file != "none")
	{
		$sql_query=fread(fopen($sql_file, "r"), filesize($sql_file));
		/* If magic_quotes_runtime is enabled, most functions that return data from any sort of external source
		   including databases and text files will have quotes escaped with a backslash.
		*/
		if(get_magic_quotes_runtime() == 1)
			$sql_query=stripslashes($sql_query);

		/* replace old_string with new_string if they are set */
		if($old_string != '')
			$sql_query=ereg_replace($old_string,$new_string,$sql_query);
	}
	$sql_query=trim($sql_query);

	if($sql_query != "")
	{
		$sql_query  =remove_remarks($sql_query);
		$pieces     =split_sql_file($sql_query,";");
		$cnt_pieces =count($pieces);

		/* run multiple queries */
		for ($i=0; $i<$cnt_pieces; ++$i)
		{
			//file_put_contents('!query.log', "$sql\n\n", FILE_APPEND);
			$sql=trim($pieces[$i]);
			if(!empty($sql) and $sql[0] != "#")
			{
				$result=sql_query($sql);
			    if($result==false)
				{
					$php_stats_error.="<font color=\"#FF0000\" size=\"1\">Error executing: <b>$sql</b><br>Error string: <b>".mysql_error()."</b></font><br><br>";
					$error_lev=1;
				}
		    }
		}
	}
	if($error_lev==0)
        return true;
    else
        return false;
}


function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on")
		$pageURL .= "s";
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].dirname($_SERVER["REQUEST_URI"]);
 	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].dirname($_SERVER["REQUEST_URI"]);
 	}
	return $pageURL;
}

?>
