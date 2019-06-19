<?php
/*  ___ _  _ ___       ___ _____ _ _____ ___
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|       |___/ |_/_/ \_\_| |___/
 */


////////////////////////////
//  M A I N    S E T U P  //
////////////////////////////
/*
      $option['host']='localhost';						// Your mySQL o IP server address (usually: localhost)
  $option['database']='my_database';					// mySQL database Name
   $option['user_db']='database';						// mySQL database User
   $option['pass_db']='1234';							// mySQL database User Password
$option['script_url']='http://yoursite.com/stats';		// Php-Stats full URL
*/

@include('config_db.php');


////////////////////////
//  ADVANCED  SETUP   //
////////////////////////
$option['prefix']='php_stats'; // Prefix for Php-Stats tables (default: php_stats)
$option['callviaimg']='0'; // 1: call Php-Stats by 1x1 pixel transparent image - 0: by javascript
$option['persistent_conn']='0'; // 1: mySQL persistent connection - 0: normal connection
$option['autorefresh']='5'; // Minutes for admin's pages refresh
$option['show_server_details']='1'; // 1: show server details in main page - 0: No
$option['show_average_user']='1'; // 1: show average user in main page - 0: No
$option['short_url']='1'; // 1: show short url when possible - 0: No
$option['ext_whois']=''; // For locked external connection, write: http://whoisservice.com/?query=%IP%
$option['online_timeout']='5'; // Minutes for user online timeout - 0: dinamic count
$option['page_title']='1'; // 1: Save page's title - 0: No
$option['refresh_page_title']='1'; // 1: refresh page's title - 0: No
$option['log_host']='1'; // 1: save hostname in details - 0: No
$option['clear_cache']='1'; // 1: continous recognize cache - 0: No
$option['full_recn']='1'; // **DEPRECATED** 1: engines and refers recognizes at every page - 0: No
$option['logerrors']='1'; // 1: save errors in php-stats.log file - 0: No
$option['check_new_version']='1'; // 1: check for php-stats new version - 0: No
$option['bcap_auto_update']='1'; // 1: auto update browscap database (systems and browsers) - 0: No
$option['www_trunc']='0'; // change http://www. in http:// - 0: No
$option['ip-zone']='1'; // 1: recognize country from IP database - 0: No
$option['down_mode']='1'; // 0: redirect - 1: force download file - 2: force download file altervista
$option['check_links']='1'; // 1: check the link - 0: No
$option['link_logger']='0'; // 1: enable link auto-stats for all links found in the page - 0: No
$option['keep_view_mode']='1'; // 1: keep last view mode - 0: No

$default_pages=array('/','/index.htm','/index.html','/default.htm','/index.php','/index.asp','/default.asp'); // Pagine di default del server, troncate considerate come la stessa

///////////////////////////////////////////
// DO NOT EDIT ANYTHING BELOW THIS POINT //
///////////////////////////////////////////

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' && substr($option['script_url'],0,5)==='http:') $option['script_url']='https:'.substr($option['script_url'],5);
if(substr($option['script_url'],-1)==='/') $option['script_url']=substr($option['script_url'],0,-1);

ini_set('display_errors', false);
error_reporting(E_ERROR);
ignore_user_abort(true);
?>
