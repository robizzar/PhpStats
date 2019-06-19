<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS'))
	die('Php-Stats internal file.');

if(isset($_POST['mode']))
	$mode=addslashes($_POST['mode']);
else $mode='';

function viewerrorlog()
{
	global $option,$mode,$string,$opzioni,$style,$phpstats_title;

	$phpstats_title=$string['viewerrlog_title'];
//	$return='';
//	$log='';

	if($mode=='reset')
	{
		// Reset del file
		$fp=fopen('php-stats.log','w');
		if($fp)
		{
			$return=info_box($string['information'],$string['viewerrlog_reset_done']);
			fclose($fp);
		}
  		else
  			$return=info_box($string['error'],$string['viewerrlog_reset_error']);
	}
	else
	{
		// Visualizzazione del file
		if(!is_readable('php-stats.log'))
			$return=info_box($string['error'],$string['viewerrlog_nr']);
  		elseif(!is_writable('php-stats.log'))
  			$return=info_box($string['error'],$string['viewerrlog_nw']);
  		else
		{
/*    		$fp=fopen('php-stats.log','r');
    		while(!feof($fp)) $log.=fgets($fp,1024);
    		fclose($fp);
*/
			$logarray = file('php-stats.log');							// Carica le linee del file in un array

			if ( count($logarray) > 500 )								// Se il log è diventato troppo lungo lo tronca
    		   	$logarray = array_slice($logarray, -500);				// Conserva solo i primi N elementi dell'array
			file_put_contents('php-stats.log', $logarray);

			$logarray = array_reverse($logarray);						// Inverte l'ordine dell'array
			foreach ($logarray as $value)
				$log.=$value;

    		if($log != '')
    		{
				$return.=
				"<span class=\"pagetitle\">$phpstats_title</span><br><br>".
				"\n<center><textarea style=\"font-size: 11px; font-family: monospace;\" name=\"text\" wrap=\"OFF\" readonly cols=\"85\" rows=\"40\">\n".$log."\n</textarea><br><br>".
				"<form action=\"admin.php?action=viewerrorlog\" method=\"post\"><input type=\"hidden\" name=\"mode\" value=\"reset\"><input type=\"submit\" value=\"".$string['viewerrlog_reset']."\"></center></form>";
			}
			else
				$return=info_box($string['information'],$string['viewerrlog_void']);
			}
  		}
	return($return);
}
?>
