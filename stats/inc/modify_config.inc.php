<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS'))
	die("Php-Stats internal file.");

if(isset($_POST['mode']))
	$mode=addslashes($_POST['mode']);
else
	$mode="";
if(isset($_POST['configtext']))
	$configtext=urldecode(addslashes($_POST['configtext']));
else
	$configtext="";

function modify_config()
{
	global $option,$mode,$configtext,$string,$opzioni,$style,$phpstats_title,$option_new;
	$phpstats_title=$string['mod_config_title'];
	$return='';
	$configFile='';
	if(($mode=='modify') && isset($_POST['option_new']))
  	{
  		$configFile=join('',file('config.php'));
  		$posWrite=strpos($configFile,"\$option['prefix']=");
  		$doWrite=substr($configFile,0,$posWrite);

  		$exceptArray=Array('prefix','autorefresh','ext_whois','online_timeout','ip-zone','down_mode');  //Stringhe di testo non numeriche o a numeri>2

		foreach($option_new as $key => $value)
		{
			if(in_array($key,$exceptArray))
			{
	  			switch($key)
	  			{
	  			case 'prefix':
	  				$value = preg_replace('@\W@', '', $value);
	  			case 'ext_whois':
	       			if(!is_string($value))
	       				return(info_box($string['error'],$string['mod_config_error_option']));
	       			else
	       				$doWrite.="\$option['$key']='$value'; // ".str_replace('<br>',' - ',$string['mod_config_descr'][$key])."\n";
	       			break;
	  			case 'ip-zone':
	  			case 'down_mode':
	       			if(!ereg('^[0-2]{1}$',$value))
	       				return(info_box($string['error'],$string['mod_config_error_option']));
	       			//break;
	  			default:
	       			if(!ereg('^[0-9]{1,2}$',$value))
	       				return(info_box($string['error'],$string['mod_config_error_option']));
	       			else
	       				$doWrite.="\$option['$key']='$value'; // ".str_replace('<br>',' - ',$string['mod_config_descr'][$key])."\n";
	       			break;
	    		}
			}
			else
			{
				if(!ereg('^[0-1]{1}$',$value))
					$return=info_box($string['error'],$string['mod_config_error_option']);
		    	else
		    		$doWrite.="\$option['$key']='$value'; // ".str_replace('<br>',' - ',$string['mod_config_descr'][$key])."\n";
			}
		}

	$doWrite.='
$default_pages=array(\'/\',\'/index.htm\',\'/index.html\',\'/default.htm\',\'/index.php\',\'/index.asp\',\'/default.asp\'); // Pagine di default del server, troncate considerate come la stessa
';

$doWrite.='
/////////////////////////////////////////////////
// NON MODIFICARE NULLA DA QUESTO PUNTO IN POI //
/////////////////////////////////////////////////';

$doWrite.=
"\n\nif(isset(\$_SERVER['HTTPS']) && \$_SERVER['HTTPS']=='on' && substr(\$option['script_url'],0,5)==='http:') \$option['script_url']='https:'.substr(\$option['script_url'],5);\nif(substr(\$option['script_url'],-1)==='/') \$option['script_url']=substr(\$option['script_url'],0,-1);\n\n".
"ini_set('display_errors', false);\n".
"error_reporting(E_ERROR);\n".
"ignore_user_abort(true);\n".
"?>";

  copy('config.php','config.bak.php');
  $fp=fopen("config.php","w+");
  if($fp)
    {
    $ok=fwrite($fp,$doWrite);
    fclose($fp);
    if($ok)
      {
      unlink('config.bak.php');
      $ok=create_option_file();
      }
    }
  if($ok)
        $return=info_box($string['information'],$string['mod_config_done']);
      else
         $return=info_box($string['error'],$string['mod_config_error']);
  }
  else
  {
  // Visualizzazione del file
  if(!is_writable("config.php"))
    $return=info_box($string['error'],$string['mod_config_nw']);
  else
    {
    $configArray=Array('callviaimg','persistent_conn','autorefresh','show_server_details','show_average_user','short_url','ext_whois','online_timeout','page_title','refresh_page_title','log_host','clear_cache','logerrors','check_new_version','bcap_auto_update','www_trunc','ip-zone','down_mode','check_links','link_logger','keep_view_mode'); // Elenco option in config
    $moreString=Array('autorefresh','ext_whois'); // option che richiedi più caratteri
    $return.=
    "<span class=\"pagetitle\">$phpstats_title</span><br><br>".
    "\n<center>\n<form action=\"admin.php?action=modify_config\" method=\"post\">".
    "\n<input type=\"hidden\" name=\"option_new[prefix]\" value=\"$option[prefix]\">".
    "\n<table border=\"0\" width=\"70%\" $style[table_header]>";

    for($i=0,$tot=count($configArray);$i<$tot;++$i) {
       $return.=
       "\n<tr><td align=\"left\" bgcolor='#CCCCCC' colspan=\"2\" nowrap>".
       "<span class=\"tabletextA\"><b>".$string['mod_config_descr'][$configArray[$i]]."</b></span></td></tr>".
       "\n<tr><td bgcolor=$style[table_bgcolor]><span class=\"tabletextA\">\$option['$configArray[$i]']<span></td>".
       "\n<td bgcolor=$style[table_bgcolor]><center><input name=\"option_new[$configArray[$i]]\" type=\"text\" value=\"".$option[$configArray[$i]]."\"";
       if(!in_array($configArray[$i],$moreString)) $return.=' maxlength="1"'; else $return.=' maxlength="255"';
       $return.="></center></td></tr><tr><td colspan=\"2\"><img src=\"templates/$option[template]/images/icon_level_unkn.gif\" height=\"3\" border=0></td></tr>";
       }
    $return.="</table><br>".
    "<input type=\"hidden\" name=\"mode\" value=\"modify\"><input type=\"submit\" value=\"".$string['mod_config_modify']."\"></center></form>";
    }
  }
return($return);
}
?>
