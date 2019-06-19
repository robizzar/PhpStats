<?php
/*  ___ _  _ ___       ___ _____ _ _____ ___
 * | _ \ || | _ \_____/ __|_   _/_\_   _/ __|
 * |  _/ __ |  _/_____\__ \ | |/ _ \| | \__ \
 * |_| |_||_|_|       |___/ |_/_/ \_\_| |___/
 */

define('IN_PHPSTATS', true);
$s=urlencode("§§");
require("option/php-stats-options.php");

// Controllo subito esclusione tramite cookie per evitare operazioni inutili
if(isset($_COOKIE['php_stats_esclusion']))
	$php_stats_esclusion=$_COOKIE['php_stats_esclusion'];
else
	$php_stats_esclusion='';
if(strpos($php_stats_esclusion,"|$option[script_url]|")!==FALSE)
	exit();

$return="
if(document.referrer){
  var f=document.referrer;
}else{
  var f=top.document.referrer;
}
f=escape(f);
f=f.replace(/&/g,'$s');
if((f=='null') || (f=='unknown') || (f=='undefined')) f='';
var w=screen.width;
var h=screen.height;
var rand=Math.round(100000*Math.random());
var browser=navigator.appName;
var t=escape(document.title);
var NS_url=\"\";
if(browser!=\"Netscape\") c=screen.colorDepth; else c=screen.pixelDepth;
NS_url=document.URL;
NS_url=escape(NS_url);
NS_url=NS_url.replace(/&/g,'$s');
";

$iptimeout = $option['ip_timeout']*60*60;
$return.="
function getCookie(Name) {
	var search = Name + '=';
	if (document.cookie.length > 0) {
		offset = document.cookie.indexOf(search);
		if (offset != -1) {
			offset += search.length;
			end = document.cookie.indexOf(';', offset);
			if (end == -1)
				end = document.cookie.length;
			return unescape(document.cookie.substring(offset, end));
		}
	}
	return '0';
}

var rettime = getCookie('ps_rettime');
var returns = getCookie('ps_returns');
rettime = parseInt(rettime);	// Converte stringa in intero
returns = parseInt(returns);
var mytime = new Date().getTime();
mytime = Math.floor(mytime / 1000);
mytime = parseInt(mytime);

var newret = 0;
if (rettime==0 || returns==0)	// Prima visita
{
	rettime = mytime;
	returns = 1;
}
else
{
	if (rettime && ((mytime-rettime) > $iptimeout))
	{
		newret = 1;				// Nuovo ritorno: va incrementato il contatore giornaliero
		returns=returns+1;
	}
}

var exdate = new Date();
exdate.setDate(exdate.getDate() + 30);
document.cookie = \"ps_rettime=\"+mytime+\";path=/;expires=\"+exdate.toUTCString();
document.cookie = \"ps_returns=\"+returns+\";path=/;expires=\"+exdate.toUTCString();
";

/*** Monitoraggio automatico dei Link */
if ($option['link_logger'] == 1)
	$return.="
function my_func() {
for (var ls = document.links, numLinks = ls.length, i=0; i<numLinks; i++)
	{ ls[i].href = \"".$option['script_url']."/link_logger.php?url=\"+escape(ls[i].href); }
}
window.onload = my_func;
";

/*
Nel monitoraggio con stringa HTML i cookies vengono salvati nel browser subito perchè si comincia da questo script;
php-stats.php è chiamato successivamente e pertanto i valori che si ritrova su $_COOKIES contengono già i nuovi dati.
Quindi è necessario passare _rettime e _returns come "argomenti" alla chiamata di "php-stats.php".
*/
if($option['callviaimg'])
	$return.="\nvar sc1=\"<img src='$option[script_url]/php-stats.php?w=\"+w+\"&amp;h=\"+h +\"&amp;c=\"+c+\"&amp;f=\"+f+\"&amp;NS_url=\"+NS_url+\"&amp;t=\"+t+\"&amp;ps_rettime=\"+rettime+\"&amp;ps_returns=\"+returns+\"&amp;ps_newret=\"+newret+\"' border='0' alt='' width='1' height='1'>\";";
else
	$return.="\nvar sc1=\"<script language='javascript' src='$option[script_url]/php-stats.php?w=\"+w+\"&amp;h=\"+h+\"&amp;c=\"+c+\"&amp;f=\"+f+\"&amp;NS_url=\"+NS_url+\"&amp;t=\"+t+\"&amp;ps_rettime=\"+rettime+\"&amp;ps_returns=\"+returns+\"&amp;ps_newret=\"+newret+\"'></script>\";";
$return.="\ndocument.write(sc1);";

/*** ***/
echo $return;
?>
