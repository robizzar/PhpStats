<?php

define('IN_PHPSTATS', true);
$loaded=$colres=$titlepage=FALSE;

require("option/php-stats-options.php");
if(isset($_GET['ip'])) $ip=$_GET['ip']; else exit();
if(isset($_GET['visitor_id'])) $visitor_id=$_GET['visitor_id']; else exit();
$referer=TRUE;
if(isset($_GET['loaded'])) if($_GET['loaded']==1) $loaded=TRUE;
if(isset($_GET['colres'])) if($_GET['colres']==1) $colres=TRUE;
if(isset($_GET['titlepage'])) { if(($_GET['titlepage']==1) && $option['page_title']) $titlepage=TRUE; }
if(isset($_GET['date'])) $date=$_GET['date']; else exit();

// IN QUESTA BETA NON FACCIO CONTROLLI SULLA COERENZA DELL'IP E VISITOR_ID
$appendjs="ip=$ip&amp;visitor_id=$visitor_id";
$errorjs='inutil';
$s=urlencode("§§");
$return='var rand=Math.round(100000*Math.random());
var inutil=\'\';
';
$return.="if(document.referrer){
  var f=document.referrer;
}else{
  var f=top.document.referrer;
}
f=escape(f);
f=f.replace(/&/g,'$s');
if((f=='null') || (f=='unknown') || (f=='undefined')) f='';";
if($colres===TRUE)
  {
$return.='
var w=screen.width;
var h=screen.height;
var browser=navigator.appName;
if(browser!="Netscape") c=screen.colorDepth; else c=screen.pixelDepth;';
$appendjs.='&amp;w="+w+"&amp;h="+h+"&amp;c="+c+"';
$errorjs.=',w,h,c';
  }
$appendjs.='&amp;f="+f+"';
$errorjs.=',f';
if ($loaded===TRUE)
  {
  $return.="\nvar NS_url=\"\";
NS_url=document.URL;
NS_url=escape(NS_url);
NS_url=NS_url.replace(/&/g,'$s');";
  $appendjs.='&amp;NS_url="+NS_url+"';
  $errorjs.=',NS_url';
  }
if ($titlepage===TRUE)
  {
  $return.="\nvar t=escape(document.title);";
  $appendjs.='&amp;t="+t+"';
  $errorjs.=',t';
  }

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

/*
Nel monitoraggio con stringa HTML i cookies vengono salvati nel browser subito perchè si comincia da questo script;
php-stats.php è chiamato successivamente e pertanto i valori che si ritrova su $_COOKIES contengono già i nuovi dati.
Quindi è necessario passare _rettime e _returns come "argomenti" alla chiamata di "php-stats.php".
*/
if($option['callviaimg'])
   $return.="\nvar sc1=\"<img src='$option[script_url]/php-stats.recjs.php?$appendjs&amp;date=$date&amp;ps_rettime=\"+rettime+\"&amp;ps_returns=\"+returns+\"&amp;ps_newret=\"+newret+\"' border='0' alt='' width='1' height='1'>\";";
  else
  $return.="\nvar sc1=\"<scr\"+\"ipt type='text/javascript' src='$option[script_url]/php-stats.recjs.php?$appendjs&amp;date=$date&amp;ps_rettime=\"+rettime+\"&amp;ps_returns=\"+returns+\"&amp;ps_newret=\"+newret+\"'></scr\"+\"ipt>\";";
$return.="\ndocument.write(sc1);";

/*** ***/
echo $return;
?>
