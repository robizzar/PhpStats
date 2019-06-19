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


$script="
<script type='text/javascript'>
        var menus = [\n";

$menuCounter=2;
if($modulo[1]>0) ++$menuCounter; // Sistemi
if(($modulo[5]>0) || $modulo[6]) ++$menuCounter; // Statistiche
if($modulo[4]>0) ++$menuCounter; // Motori
if($modulo[6] || $modulo[7] || $modulo[8] || $modulo[9] || $modulo[10] || $modulo[2]) ++$menuCounter; // Varie

$menuTitle=Array();
$menuTitle[]='generale';
$menuCount=1;
// GENERALE
$menuScroll[$menuCount]=1;
if($modulo[3]>0) $menuScroll[1]=$menuScroll[$menuCount]+3;
if($modulo[4]>0) ++$menuScroll[$menuCount];
if($modulo[0]) ++$menuScroll[$menuCount];

// Sistemi
if($modulo[1]>0) { ++$menuCount; $menuScroll[$menuCount]=3; $menuTitle[]='sistemi'; }

// Motori Di Ricerca
if($modulo[4]>0) { ++$menuCount; $menuScroll[$menuCount]=3; $menuTitle[]='motori'; }

if($modulo[5]>0 || $modulo[6]){
// Statistiche
++$menuCount;
$menuScroll[$menuCount]=0;
if($modulo[5]>0) ++$menuScroll[$menuCount];
if($modulo[6]) $menuScroll[$menuCount]=$menuScroll[$menuCount]+5;
$menuTitle[]='statistiche';
}

if($modulo[10] || $modulo[9] || $modulo[7] || $modulo[8] || $modulo[6] || $modulo[2]){
// Varie
++$menuCount;
$menuScroll[$menuCount]=0;
if($modulo[7])++$menuScroll[$menuCount];
if($modulo[8]) ++$menuScroll[$menuCount];
if($modulo[9]) ++$menuScroll[$menuCount];
if($modulo[10]) ++$menuScroll[$menuCount];
if($modulo[6]) ++$menuScroll[$menuCount];
if($modulo[2]) ++$menuScroll[$menuCount];
if($option['link_logger'] == 1) ++$menuScroll[$menuCount];
$menuTitle[]='varie';
}

// OPZIONI
++$menuCount;
$menuScroll[$menuCount]=8;
if($modulo[8]) ++$menuScroll[$menuCount];
if($modulo[9]) ++$menuScroll[$menuCount];
if($option['logerrors']) ++$menuScroll[$menuCount];
$menuTitle[]='opzioni';

$titleTextMenu=Array(
'generale'=>"$admin_menu[menu_general]",
'sistemi'=>"$admin_menu[menu_sistems]",
'motori'=>"$admin_menu[menu_engines]",
'statistiche'=>"$admin_menu[menu_stats]",
'varie'=>"$admin_menu[menu_others]",
'opzioni'=>"$admin_menu[menu_options]");

for($i=1;$i<=$menuCounter;++$i)
$script.="                new ypSlideOutMenu('menu$i', 'down', ".((68-(6-$menuCounter))*(7-$menuCounter)+95*($i-1)).", 80, 140, ".(21+19*($menuScroll[$i]-1))."),\n";
$script=substr($script,0,-2)."\n";
$script.="        ]

        for (var i = 0; i < menus.length; i++) {
                menus[i].onactivate = new Function(\"document.getElementById('act\" + i + \"').className='active';\");
                menus[i].ondeactivate = new Function(\"document.getElementById('act\" + i + \"').className='';\");
        }

  ypSlideOutMenu.writeCSS();
</script>";

$menu_script=$script;

$script=
"
<div id=\"menubar\">\n";
for($i=0;$i<$menuCounter;++$i)
   $script.="        &nbsp;&nbsp;&nbsp;<a id=\"act$i\" href=\"admin.php\" onmouseover=\"ypSlideOutMenu.showMenu('menu".($i+1)."')\" onmouseout=\"ypSlideOutMenu.hideMenu('menu".($i+1)."')\" title=\"".$titleTextMenu[$menuTitle[$i]]."\">".$titleTextMenu[$menuTitle[$i]]."</a>&nbsp;&nbsp;&nbsp;\n";
//$script=substr($script,0,-26)."\n";
$script.=
'
</div>';
$i=0;
if(in_array('generale',$menuTitle)){
++$i;
$script.="
<div id=\"menu".$i."Container\">
        <div id=\"menu".$i."Content\" class=\"menu\">
                <div class=\"options\">
                        <a href=\"admin.php?action=main\">$admin_menu[main]</a>";
       if($modulo[3]>0)
         {
         $script.="
                        <a href=\"admin.php?action=pages\">$admin_menu[pages]</a>
                        <a href=\"admin.php?action=time_pages\">$admin_menu[time_pages]</a>
                        <a href=\"admin.php?action=percorsi\">$admin_menu[percorsi]</a>
                        ";
         }
         $script.=
         ($modulo[4]>0 ? "<a href=\"admin.php?action=referer\">$admin_menu[referer]</a>\n                        " : '').
           ($modulo[0] ? "<a href=\"admin.php?action=details\">$admin_menu[details]</a>\n                        " : '');
$script.='</div>
        </div>
</div>';
}

if(in_array('sistemi',$menuTitle)){
++$i;
$script.="
<div id=\"menu".$i."Container\">
        <div id=\"menu".$i."Content\" class=\"menu\">
                <div class=\"options\">
                        <a href=\"admin.php?action=os_browser\">$admin_menu[os_browser]</a>
                        <a href=\"admin.php?action=reso\">$admin_menu[reso]</a>
                        <a href=\"admin.php?action=systems\">$admin_menu[systems]</a>
                </div>
        </div>
</div>";
}

if(in_array('motori',$menuTitle)){
++$i;
$script.="
<div id=\"menu".$i."Container\">
        <div id=\"menu".$i."Content\" class=\"menu\">
                <div class=\"options\">
                        <a href=\"admin.php?action=engines\">$admin_menu[engines]</a>
                        <a href=\"admin.php?action=query\">$admin_menu[query]</a>
                        <a href=\"admin.php?action=searched_words\">$admin_menu[searched_words]</a>
                </div>
        </div>
</div>";
}

if(in_array('statistiche',$menuTitle)){
++$i;
$script.="
<div id=\"menu".$i."Container\">
        <div id=\"menu".$i."Content\" class=\"menu\">
                <div class=\"options\">\n                        ".
         ($modulo[5] ? "<a href=\"admin.php?action=hourly\">$admin_menu[hourly]</a>\n                        " : '');
         if($modulo[6])
           {
           $script.="
                        <a href=\"admin.php?action=daily\">$admin_menu[daily]</a>
                        <a href=\"admin.php?action=weekly\">$admin_menu[weekly]</a>
                        <a href=\"admin.php?action=monthly\">$admin_menu[monthly]</a>
                        <a href=\"admin.php?action=compare\">$admin_menu[compare]</a>
                        <a href=\"admin.php?action=calendar\">$admin_menu[calendar]</a>
                        ";
           }
$script.='               </div>
        </div>
</div>';
}

if(in_array('varie',$menuTitle)){
++$i;
$script.="
<div id=\"menu".$i."Container\">
        <div id=\"menu".$i."Content\" class=\"menu\">
                <div class=\"options\">\n                        ".
         ($modulo[10] ? "<a href=\"admin.php?action=ip\">$admin_menu[ip]</a>\n                        " : '').
          ($modulo[7] ? "<a href=\"admin.php?action=country\">$admin_menu[country]</a>\n                        " : '').
          ($modulo[2] ? "<a href=\"admin.php?action=bw_lang\">$admin_menu[bw_lang]</a>\n                        " : '').

($option['link_logger'] == 1 ? "<a href=\"admin.php?action=links\">$admin_menu[links]</a>\n                     " : '').

          ($modulo[8] ? "<a href=\"admin.php?action=downloads\">$admin_menu[downloads]</a>\n                        " : '').
          ($modulo[9] ? "<a href=\"admin.php?action=clicks\">$admin_menu[clicks]</a>\n                        " : '').
          ($modulo[6] ? "<a href=\"admin.php?action=trend\">$admin_menu[trend]</a>\n                        " : '');
$script.='</div>
        </div>
</div>';
}

if(in_array('opzioni',$menuTitle)){
++$i;
$script.="
<div id=\"menu".$i."Container\">
        <div id=\"menu".$i."Content\" class=\"menu\">
                <div class=\"options\">
                        <a href=\"admin.php?action=preferenze\">$admin_menu[options]</a>
                        <a href=\"admin.php?action=modify_config\">$admin_menu[modifyconfig]</a>
                        <a href=\"admin.php?action=esclusioni\">$admin_menu[esclusioni]</a>
                        <a href=\"admin.php?action=optimize_tables\">$admin_menu[optimize_tables]</a>\n".
         ($modulo[8] ? "<a href=\"admin.php?action=downadmin\">$admin_menu[downadmin]</a>\n                        " : '').
         ($modulo[9] ? "<a href=\"admin.php?action=clicksadmin\">$admin_menu[clicksadmin]</a>\n                        " : '').
                       "<a href=\"admin.php?action=backup\">$admin_menu[backup]</a>
                        <a href=\"admin.php?action=resett\">$admin_menu[reset]</a>\n                        ".
($option['logerrors'] ? "<a href=\"admin.php?action=viewerrorlog\">$admin_menu[errorlogviewer]</a>\n                        " : '').
                        "<a href=\"admin.php?action=$admin_menu[status_rev]\">$admin_menu[status]</a>
                </div>
        </div>
</div>";
}

$script.="\n";

//END OPTIONS
$menu_script2=$script;

//////////////////////////////////
// Generazione HTML da template //
//////////////////////////////////
eval("\$template=\"".gettemplate("$template_path/admin.tpl")."\";");
?>