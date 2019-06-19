<?php
define('IN_PHPSTATS',true);

                 if(!isset($_GET)) $_GET=$HTTP_GET_VARS;
            if(isset($_GET['ip'])) $ip=addslashes($_GET['ip']);     else $ip='';
          if(isset($_GET['page'])) $page=addslashes($_GET['page']); else $page='';
          if(isset($_GET['what'])) $what=addslashes($_GET['what']); else $what='';
              if(!isset($_COOKIE)) $_COOKIE=$HTTP_COOKIE_VARS;
$style=''; // In caso di register globals=on

// DEFINIZIONE VARIABILI PRINCIPALI
define ('__OPTIONS_FILE__','option/php-stats-options.php');
define ('__LOCK_FILE__','option/options_lock.php');

// Verifica presenza del file di opzioni libero altrimenti aspetto ed inclusione funzioni principali
if (file_exists(__LOCK_FILE__)) sleep(2);
if (!include(__OPTIONS_FILE__)) die("<b>ERRORE</b>: File di config non accessibile.");

if(!include('inc/main_func.inc.php')) die('<b>ERRORE</b>: File main_func.inc.php non accessibile.');
if(!include('inc/admin_func.inc.php')) die('<b>ERRORE</b>: File admin_func.inc.php non accessibile.');
if(!include('inc/user_func.inc.php')) die('<b>ERRORE</b>: File user_func.inc.php non accessibile.');
//
$dwn_clk=false;


if($option['prefix']=='') $option['prefix']='php_stats';
  db_connect();


if($option['use_pass']) if(!user_is_logged_in()) { header("Location: $option[script_url]/admin.php?action=login"); die(); }
if($option['template']=='') $option['template']='default';
$template_path='templates/'.$option['template'];

include('lang/'.$option['language'].'/main_lang.inc');
include($template_path.'/def.php');

//$page=str_replace('§§','&',$page);
$$page=rawurldecode($page);
$trckpage=
'<html>
<head>
<title>'.$tracking['title'].'</title>
<link rel="stylesheet" href="'.$template_path.'/styles.css" type="text/css">
<META NAME="ROBOTS" CONTENT="NONE">
<script>
function whois(url) {
        test=window.open(url,\'whois\',\'SCROLLBARS=1,STATUS=NO,TOOLBAR=NO,RESIZABLE=YES,LOCATION=NO,MENU=NO,WIDTH=450,HEIGHT=600,LEFT=0,TOP=0\');
        }
</script>
<script language="javascript" src="'.$template_path.'/functions.js"></script>
</head>

<body bgcolor="'.$style['bg_pops'].'" onload="self.focus()">'."\n";

db_connect();   // CONNESSIONE MYSQL

switch($what)
  {
  case 'referer':
    $trckpage.='<span class="testo"><center>'.str_replace('%URL%',formaturl($page,'',40,10,-30),$tracking['referer']).'</center><br>';
    $result=sql_query("SELECT ip,date,referer FROM $option[prefix]_details WHERE referer='$page' ORDER BY date DESC");
    if(mysql_num_rows($result)>0)
      {
      $total=0;
      $trckpage.=
      "<table $style[table_header] width=\"400\">".
      "<table $style[table_header] width=\"260\">".
      "<tr bgcolor=$style[table_title_bgcolor]>".
      "<td width=\"70\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['referer_date']."</center></span></td>".
      "<td width=\"70\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['referer_time']."</center></span></td>".
      "<td width=\"120\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['referer_ip']."</center></span></td>".
      '</tr>'.
      '</table>';
      while($row=mysql_fetch_row($result))
        {
        ++$total;
        $dottedIP=long2ip($row[0]);
        $trckpage.=
        "<table $style[table_header] width=\"260\">".
        "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '#CCCCCC', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "<td width=\"70\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">".formatdate($row[1],3)."</span></td>".
        "<td width=\"70\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">".formattime($row[1])."</span></td>".
        "<td width=\"120\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">";
            if($option['ext_whois']=='') $trckpage.="<a href=\"javascript:whois('whois.php?IP=$dottedIP');\">$dottedIP</a>";
                                    else $trckpage.="<a href=\"".str_replace("%IP%",$dottedIP,$option['ext_whois'])."\" target=\"_BLANK\">$dottedIP</a>";
        $trckpage.=
        '</span></td>'.
        '</tr>'.
        '</table>';
        }
      $trckpage.="<center><br>".str_replace("%total%",$total,$tracking['total'])."<br><br>$tracking[fine]</center>";
      }
    else
      {
      $trckpage.="<center><span class=\"tabletextA\"><br>$tracking[noresult]<br><br>$tracking[fine]<br></span></center>";
      }
  break;

  case 'ip':
//    $longPage=ip2long($page);
/*** Because PHP's integer type is signed, and many IP addresses will result in negative integers on 32-bit architectures,
	 you need to use the "%u" formatter of sprintf() or printf() to get the string representation of the unsigned IP address. */
	$longPage = sprintf("%u", ip2long($page));

    $result=sql_query("SELECT ip,date,currentPage FROM $option[prefix]_details WHERE ip='$longPage' ORDER BY date ASC");
    if(mysql_num_rows($result)>0)
      {
      $total=0;
      $trckpage.=
      "<table $style[table_header] width=\"600\">".
      '<tr>'.
      "<td width=\"120\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>Data</center></span></td>".
      "<td width=\"440\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>Pagina visitata</center></span></td>".
      '</tr>'.
      '</table>';
      while($row=mysql_fetch_row($result))
        {
        ++$total;
        $trckpage.=
        "<table $style[table_header] width=\"600\">".
        "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '#CCCCCC', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "<td width=\"120\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">".formatdate($row[1],3).' - '.formattime($row[1]).'</span></td>'.
        "<td width=\"440\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">";

        $tmpPagename=check_details($row[2]);

        $trckpage.=($dwn_clk===false ? formaturl($row[2], '', 70, 30, -35) : $tmpPagename).'</span></td>'.
        '</tr>'.
        '</table>';
        }
      $trckpage.="<center><span class=\"tabletextA\"><br>".str_replace("%total%",$total,$tracking['total'])."<br><br>$tracking[fine]</span></center>";
      }
      else
      {
      $trckpage.="<center><span class=\"tabletextA\">$tracking[noresult]<br><br>$tracking[fine]</span></center>";
      }
  break;

  ///////////////////////////////////////
  // PAGINE VISTE DAGLI UTENTI ON LINE //
  ///////////////////////////////////////
  case 'online':
        $total=0;
        if($option['ext_whois']=='') $tmp="<a href=\"javascript:whois('whois.php?IP=$ip');\">$ip</a>";
                                else $tmp='<a href="'.str_replace('%IP%',$ip,$option['ext_whois']).'" target="_BLANK">'.$ip.'</a>';
        $trckpage.='<span class="testo"><center>'.str_replace('%IP%',$tmp,$tracking['online']).'</center><br>';
        $result=sql_query("SELECT visitor_id FROM $option[prefix]_cache WHERE user_id='".(sprintf('%u',ip2long($ip))-0)."'");
        if(mysql_num_rows($result)>0)
          {
          $trckpage.=
          "<table $style[table_header] width=\"530\">".
          "<tr bgcolor=$style[table_title_bgcolor]>".
          "<td width=\"60\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['online_time'].'</center></span></td>'.
          "<td width=\"470\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['online_url'].'</center></span></td>'.
          '</tr>'.
          '</table>';
          list($id)=mysql_fetch_row($result);
          $result=sql_query("SELECT currentPage,date,titlePage FROM $option[prefix]_details WHERE visitor_id='$id' ORDER BY 'date' ASC");
          if(mysql_num_rows($result)>0)
            {
            while($row=mysql_fetch_row($result))
              {
              ++$total;
              $trckpage.=
              "<table $style[table_header] width=\"530\">".
              "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '#CCCCCC', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
              "<td width=\"60\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">".formattime($row[1]).'</span></td>'.
              "<td width=\"470\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">";

              $tmpPagename=check_details($row[0]);

              $trckpage.=($dwn_clk===false ? formaturl($row[0],'',80,35,-40,$row[2]) : $tmpPagename).'</span></td>'.
              '</tr>'.
              '</table>';
              }
            $trckpage.='<center><br>'.str_replace("%total%",$total,$tracking['total'])."<br><br>$tracking[fine]</center>";
            }
          else
            {
            $trckpage.='<center>'.$tracking['online_err_nopage']."<br><br>$tracking[fine]</center>";
            }
          }
        else
          {
          $trckpage.="<center><span class=\"tabletextA\"><br>".$tracking['online_err_nonoline']."<br><br>$tracking[fine]<br></span></center>";
          }
  break;

  ///////////////////////////////////////
  // TRACKING DEI REFERERS PER DOMINIO //
  ///////////////////////////////////////
  case 'referer_domain':
          if(isset($_GET['domain'])) $domain=addslashes($_GET['domain']); else $domain='';
          if(isset($_GET['mese'])) $mese=addslashes($_GET['mese']); else $mese='';
        if($mese=='')
          $result=sql_query("SELECT * FROM $option[prefix]_referer WHERE data LIKE '$domain%' ORDER BY visits DESC");
          else
          $result=sql_query("SELECT * FROM $option[prefix]_referer WHERE data LIKE '$domain%' AND mese='$mese' ORDER BY visits DESC");
        if(mysql_num_rows($result)>0)
          {
          $trckpage.="<span class=\"testo\"><center>".str_replace("%DOMAIN%",$domain,$tracking['domain_title'])."</center><br>";
          if($mese!='')
            {
            list($anno,$mese)=explode('-',$mese);
            $mese=$varie['mounts'][$mese-1];
            $tmp=str_replace(Array('%MESE%','%ANNO%'),Array($mese,$anno),$tracking['domain_mese']);
            $trckpage.="<span class=\"testo\"><center>$tmp</center></span><br>";
            }
      $total=0;
      $trckpage.=
      "<table $style[table_header] width=\"530\">".
      "<tr bgcolor=$style[table_title_bgcolor]>".
      "<td width=\"500\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['domain_url']."</center></span></td>".
      "<td width=\"30\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['domain_hits']."</center></span></td>".
      "</tr>".
      "</table>";
          while($row=mysql_fetch_array($result))
            {
            ++$total;
            $trckpage.=
            "<table $style[table_header] width=\"530\">".
            "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '#CCCCCC', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
            "<td width=\"500\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">".formaturl($row[0], '', 80, 35, -40)."</span></td>".
            "<td width=\"30\" bgcolor=\"$style[table_bgcolor]\" align=\"right\"><span class=\"tabletextA\">$row[1]</span></td>".
            "</tr>".
            "</table>";
            }
          $trckpage.="<center><br>".str_replace("%total%",$total,$tracking['total'])."<br><br>$tracking[fine]</center>";
          }
          else
          {
          $trckpage.="<center><span class=\"tabletextA\"><br>".$tracking['domain_err_nopage']."<br><br>$tracking[fine]<br></span></center>";
          }
        break;

  default:
    // V1.1
        $result=sql_query("SELECT ip,date,currentPage FROM $option[prefix]_details WHERE currentPage='$page' ORDER BY date DESC");
    if(mysql_num_rows($result)>0)
      {
//      $trckpage.="<span class=\"testo\"><center>".str_replace("%url%",formaturl($page,"",40,10,-30),$tracking['pages'])."</center><br>";
      $trckpage.="<span class=\"testo\"><center>".str_replace("%url%",$page,$tracking['pages'])."</center><br>";

      $trckpage.= "<div align=\"center\">\n";
	  $trckpage.= '<script type="text/javascript" src="chart_page.js.php?page='.$page.'"></script>'."\n";
	  $trckpage.= "</div>\n";

      $total=0;
      $trckpage.=
      "<table $style[table_header] width=\"260\">".
      "<tr bgcolor=$style[table_title_bgcolor]>".
      "<td width=\"70\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['pages_date']."</center></span></td>".
      "<td width=\"70\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['pages_time']."</center></span></td>".
      "<td width=\"120\" bgcolor=$style[table_title_bgcolor]><span class=\"tabletitle\"><center>".$tracking['pages_ip']."</center></span></td>".
      "</tr>".
      "</table>";
      while($row=mysql_fetch_array($result))
        {
        ++$total;
        $dottedIP=long2ip($row[0]);
        $trckpage.=
        "<table $style[table_header] width=\"260\">".
        "<tr bgcolor=\"#B3C0D7\" onmouseover=\"setPointer(this, '#CCCCCC', '$style[table_bgcolor]')\" onmouseout=\"setPointer(this, '$style[table_bgcolor]', '$style[table_bgcolor]')\">".
        "<td width=\"70\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">".formatdate($row[1],3)."</span></td>".
        "<td width=\"70\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">".formattime($row[1])."</span></td>".
        "<td width=\"120\" bgcolor=\"$style[table_bgcolor]\"><span class=\"tabletextA\">";
            if($option['ext_whois']=="") $trckpage.="<a href=\"javascript:whois('whois.php?IP=$dottedIP');\">$dottedIP</a>";
                                    else $trckpage.="<a href=\"".str_replace("%IP%",$dottedIP,$option['ext_whois'])."\" target=\"_BLANK\">$dottedIP</a>";

        $trckpage.=
        '</span></td>'.
        '</tr>'.
        '</table>';
        }
      $trckpage.="<center><br>".str_replace("%total%",$total,$tracking['total'])."<br><br>$tracking[fine]</center>";
      }
      else
      {
      $trckpage.="<center><span class=\"tabletextA\"><br>$tracking[noresult]<br><br>$tracking[fine]<br></span></center>";
      }
  break;
  }

//$trckpage.="<script>self.focus()</script>";
$trckpage.="<br><span class=\"testo\"><center><a href=\"javascript:window.close();\">$tracking[close_window]</a></span></center>\n\n</body>\n</html>";

echo $trckpage;

// Chiusura connessione a MySQL se necessario
if($option['persistent_conn']!=1) mysql_close();

function check_details($page)
  {
  global $option,$db,$dwn_clk,$string;
  $dwn_clk=false;
  $tmp=substr($page,0,3);
  if($tmp==='dwn')
          {
          $dwn_clk=true;
          list($dummy,$id)=explode('|',$page);
          $res_name=sql_query("SELECT nome FROM $option[prefix]_downloads WHERE id='$id' LIMIT 1");
          if(mysql_num_rows($res_name)>0)
            {
            list($name)=mysql_fetch_row($res_name);
            $name=stripslashes($name);
            return(str_replace('%NAME%',$name,$string['details_down']));
            }
          else return(str_replace('%ID%',$id,$string['details_down']));
         }
       else if($tmp==='clk')
         {
         $dwn_clk=true;
         list($dummy,$id)=explode('|',$page);
         $res_name=sql_query("SELECT nome FROM $option[prefix]_clicks WHERE id='$id' LIMIT 1");
         if(mysql_num_rows($res_name)>0)
           {
           list($name)=mysql_fetch_row($res_name);
           return(str_replace('%NAME%',$name,$string['details_click']));
           }
        else return(str_replace('%ID%',$id,$string['details_click']));
        }
  return(0);
}
?>
