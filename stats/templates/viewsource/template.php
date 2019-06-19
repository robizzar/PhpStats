<?php
// SECURITY ISSUES
if(!defined('IN_PHPSTATS')) die('Php-Stats internal file.');

////////////////////////////////////////////////
// Preparazione varibili HTML per il template //
////////////////////////////////////////////////
$autorefresh='';
$option['nomesito']=stripcslashes($option['nomesito']);
if(isset($option['autorefresh']) && $option['autorefresh']>0) $option['autorefresh']=$option['autorefresh']*60000;
else $option['autorefresh']=600000;
$meta="<META NAME='ROBOTS' CONTENT='NONE'>";
$phpstats_title="Php-Stats - $phpstats_title";
if($refresh) $meta.="\n<META HTTP-EQUIV=\"refresh\" CONTENT=\"5;URL=$url\">"; // Refresh pagina breve
else if(!in_array($trad_action,$norefresh_action) && $option['autorefresh']>0) // Alcune pagine sono escluse dal refresh
$autorefresh=
"<script type='text/javascript'>
function selfRefresh(){
  location.href='".$option['script_url'].'/admin.php?'.$QUERY_STRING."';
}
setTimeout('selfRefresh()',$option[autorefresh]);
</script>";
if($update_msg) $meta.="\n".$update_msg;
//$generation_time=str_replace('%TOTALTIME%',round($end_time-$start_time,3),$varie['page_time']);

$total_queries=str_replace('%TOTALQUERIES%',$GLOBALS['totalqueries'],$varie['total_queries']);
//$server_time=str_replace('%SERVER_TIME%',date($varie['time_format']),$varie['server_time']);
$server_time=str_replace('%SERVER_TIME%',date('j/m/y H:i'),$varie['server_time']);

$handle = @fopen('browscap/browscap.ini', 'r');
if ($handle) {
	$i = 0;
    while ($i <= 40) {
		$buffer = fgets($handle);
		if (strpos($buffer, 'Released') !== false) {
        	$server_time .=  ' - Browscap rel.: '.substr($buffer, 14, 11);
        	break;
        }
    	$i++;
    }
	fclose($handle);
}


$script=
'
/****************************************************
* (c) Ger Versluis 2000 version simple 4 April 2002 *
* You may use this script on non commercial sites.  *
* For info write to menus@burmees.nl                *
* You may remove all comments for faster loading    *
*****************************************************/
';

$menuCounter=2;
if($modulo[1]>0) ++$menuCounter; // Sistemi
if(($modulo[5]>0) || ($modulo[6])) ++$menuCounter; // Statistiche
if($modulo[4]>0) ++$menuCounter; // Motori
if(($modulo[6]) || ($modulo[7]) || ($modulo[8]) || ($modulo[9]) || ($modulo[10]) || ($modulo[2])) ++$menuCounter; // Varie

$script.=
'var NoOffFirstLineMenus='.$menuCounter.';
var LowBgColor="#666699";
var HighBgColor="#8F90B7";
var FontLowColor="white";
var FontHighColor="white";
var BorderColor="black";
var BorderWidth=1;
var BorderBtwnElmnts=1;
var FontFamily="verdana,comic sans ms,technical,arial";
var FontSize=8;
var FontBold=0;
var FontItalic=0;
var MenuTextCentered="center";
var MenuCentered="center";
var MenuVerticalCentered="top";
var ChildOverlap=0.1;
var ChildVerticalOverlap=0.1;
var StartTop=65;
var StartLeft=0;
var VerCorrect=0;
var HorCorrect=0;
var LeftPaddng=3;
var TopPaddng=2;
var FirstLineHorizontal=1;
var MenuFramesVertical=1;
var DissapearDelay=1000;
var TakeOverBgColor=0.1;
var FirstLineFrame="navig";
var SecLineFrame="space";
var DocTargetFrame="space";
var TargetLoc="";
var UnfoldsOnClick=0;
var BaseHref="";
var Arrws=[BaseHref+"",5,10,BaseHref+"",10,5];
var MenuUsesFrames=0;
var PartOfWindow=.8;
var MenuSlide="";
var MenuSlide="progid:DXImageTransform.Microsoft.GradientWipe(duration=.3, wipeStyle=1)";
var MenuShadow="";
var MenuShadow="progid:DXImageTransform.Microsoft.DropShadow(color=#888888, offX=1, offY=1, positive=1)";
var MenuOpacity="";
var MenuOpacity="progid:DXImageTransform.Microsoft.Alpha(opacity=90)";
function BeforeStart(){return}
function AfterBuild(){return}
function BeforeFirstOpen(){return}
function AfterCloseAll(){return}'."\n";

//////////////
// Generale //
//////////////
$menuCounter=1;
if($modulo[3]>0) $menuCounter=$menuCounter+3;
if($modulo[4]>0) ++$menuCounter;
if($modulo[0]) ++$menuCounter;

$menuIndex=1;
$menuChild=1;
$script.='Menu'.$menuChild.'=new Array("'.$admin_menu['menu_general'].'","","",'.$menuCounter.',20,120);'."\n";
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['main'].'","admin.php?action=main","",0,20,120);'."\n";
++$menuIndex;
if($modulo[3]>0)
  {
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['pages'].'","admin.php?action=pages","",0,20,120);'."\n";
  ++$menuIndex;
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['time_pages'].'","admin.php?action=time_pages","",0,20,120);'."\n";
  ++$menuIndex;
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['percorsi'].'","admin.php?action=percorsi","",0,20,120);'."\n";
  ++$menuIndex;
  }
if($modulo[4]>0)
  {
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['referer'].'","admin.php?action=referer","",0,20,120);'."\n";
  ++$menuIndex;
  }
if($modulo[0]) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['details'].'","admin.php?action=details","",0,20,120);'."\n"; ++$menuIndex;}
$script.=''."\n";

/////////////
// Sistemi //
/////////////
if($modulo[1]>0)
  {
  $menuCounter=3;
  ++$menuChild;
  $menuIndex=1;
  $script.='Menu'.$menuChild.'=new Array("'.$admin_menu['menu_sistems'].'","","",'.$menuCounter.',20,120);'."\n";
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['os_browser'].'","admin.php?action=os_browser","",0,20,120);'."\n";
  ++$menuIndex;
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['reso'].'","admin.php?action=reso","",0,20,120);'."\n";
  ++$menuIndex;
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['systems'].'","admin.php?action=systems","",0,20,120);'."\n";
  ++$menuIndex;
  $script.=''."\n";
  }

///////////////////////
// Motori Di Ricerca //
///////////////////////
if($modulo[4]>0)
  {
  $menuCounter=3;
  ++$menuChild;
  $menuIndex=1;
  $script.='Menu'.$menuChild.'=new Array("'.$admin_menu['menu_engines'].'","","",'.$menuCounter.',20,120);'."\n";
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['engines'].'","admin.php?action=engines","",0,20,120);'."\n";
  ++$menuIndex;
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['query'].'","admin.php?action=query","",0,20,120);'."\n";
  ++$menuIndex;
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['searched_words'].'","admin.php?action=searched_words","",0,20,120);'."\n";
  ++$menuIndex;
  }


/////////////////
// Statistiche //
/////////////////
$menuCounter=0;
if($modulo[5]>0) ++$menuCounter;
if($modulo[6]) $menuCounter=$menuCounter+5;
if(($modulo[5]>0) or ($modulo[6])) {
$menuIndex=1;
++$menuChild;

$script.='Menu'.$menuChild.'=new Array("'.$admin_menu['menu_stats'].'","","",'.$menuCounter.',20,120);'."\n";
if($modulo[5]>0) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['hourly'].'","admin.php?action=hourly","",0,20,120);'."\n";
++$menuIndex;}
  if($modulo[6]) {
                                        $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['daily'].'","admin.php?action=daily","",0,20,120);'."\n";
                                        ++$menuIndex;
                                        $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['weekly'].'","admin.php?action=weekly","",0,20,120);'."\n";
                                        ++$menuIndex;
                                        $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['monthly'].'","admin.php?action=monthly","",0,20,120);'."\n";
                                        ++$menuIndex;
                                        $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['compare'].'","admin.php?action=compare","",0,20,120);'."\n";
                                        ++$menuIndex;
                                        $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['calendar'].'","admin.php?action=calendar","",0,20,120);'."\n";
                                        ++$menuIndex;
  }
}


///////////
// Varie //
///////////
$menuCounter=0;

if($modulo[7]) ++$menuCounter;
if($modulo[8]) ++$menuCounter;
if($modulo[9]) ++$menuCounter;
if($modulo[10]) ++$menuCounter;
if($modulo[6]) ++$menuCounter;
if($modulo[2]) ++$menuCounter;

if(($modulo[10]) or  ($modulo[9]) or ($modulo[7]) or ($modulo[8]) or ($modulo[6]) or ($modulo[2]))
  {
  $menuChild=$menuChild + 1;
  $menuIndex=1;
  $script.='Menu'.$menuChild.'=new Array("'.$admin_menu['menu_others'].'","","",'.$menuCounter.',20,120);'."\n";
  if($modulo[10]) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['ip'].'","admin.php?action=ip","",0,20,120);'."\n";
  ++$menuIndex;}
  if($modulo[7]) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['country'].'","admin.php?action=country","",0,20,120);'."\n";
  ++$menuIndex;}
  if($modulo[2]) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['bw_lang'].'","admin.php?action=bw_lang","",0,20,120);'."\n";
  ++$menuIndex;}

  if($option['link_logger'] == 1) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['links'].'","admin.php?action=links","",0,20,120);'."\n";
  ++$menuIndex;}


  if($modulo[8]) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['downloads'].'","admin.php?action=downloads","",0,20,120);'."\n";
  ++$menuIndex;}
  if($modulo[9]) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['clicks'].'","admin.php?action=clicks","",0,20,120);'."\n";
  ++$menuIndex;}
  if($modulo[6]) {$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['trend'].'","admin.php?action=trend","",0,20,120);'."\n";
    ++$menuIndex;}

  $script.=''."\n";
  }

/////////////
// OPTIONS //
/////////////

$menuChild=$menuChild+1;
$menuIndex=1;

//$menuCounter=8;
$menuCounter=7;

if($modulo[8]) ++$menuCounter;
if($modulo[9]) ++$menuCounter;
if($option['logerrors']) ++$menuCounter;

$script.='Menu'.$menuChild.'=new Array("'.$admin_menu['menu_options'].'","","",'.$menuCounter.',20,120);'."\n";
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['options'].'","admin.php?action=preferenze","",0,20,120);'."\n";
++$menuIndex;
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['modifyconfig'].'","admin.php?action=modify_config","",0,20,120);'."\n";
++$menuIndex;
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['esclusioni'].'","admin.php?action=esclusioni","",0,20,120);'."\n";
++$menuIndex;
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['optimize_tables'].'","admin.php?action=optimize_tables","",0,20,120);'."\n";
++$menuIndex;
//$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['update_def'].'","admin.php?action=update_def","",0,20,120);'."\n";
//++$menuIndex;
if($modulo[8])
  {
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['downadmin'].'","admin.php?action=downadmin","",0,20,120);'."\n";
  ++$menuIndex;
  }
if($modulo[9])
  {
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['clicksadmin'].'","admin.php?action=clicksadmin","",0,20,120);'."\n";
  ++$menuIndex;
  }
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['backup'].'","admin.php?action=backup","",0,20,120);'."\n";
++$menuIndex;
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['reset'].'","admin.php?action=resett","",0,20,120);'."\n";
++$menuIndex;
if($option['logerrors'])
  {
  $script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['errorlogviewer'].'","admin.php?action=viewerrorlog","",0,20,120);'."\n";
  ++$menuIndex;
  }
$script.='        Menu'.$menuChild.'_'.$menuIndex.'=new Array("'.$admin_menu['status'].'","admin.php?action='.$admin_menu['status_rev'].'","",0,20,120);'."\n";
$script.="\n";

//END OPTIONS
$menu_script=$script;

//////////////////////////////////
// Generazione HTML da template //
//////////////////////////////////
eval("\$template=\"".gettemplate("$template_path/admin.tpl")."\";");
?>