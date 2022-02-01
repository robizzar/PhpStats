<html>
<head>
<title>$phpstats_title</title>
$meta
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!--meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"-->
<META http-equiv=Page-Enter content=blendTrans(duration=0.5)>
<META http-equiv=page-exit content=blendtrans(duration=0.5)>
<link rel='stylesheet' href='./templates/Airkine/styles.css' type='text/css'>
<script src='templates/default/functions.js' type='text/javascript' language='javascript'></script>
$autorefresh
</head>
<body>
<table width="90%" height="100%" bgcolor="black" cellspacing="0" cellpadding="0" align="center">
  <tr>
   <td height="30" colspan="2" bgcolor="#e8e8e8">
      <table width="100%">
	    <tr>
		  <td><span class='nomesito'><strong>$option[nomesito]</strong></span></td>
		  <td align="right" valign="bottom"><!-- <span class='nomesito'>$option[nomesito]</span> --></td>
		</tr>
      </table>
	</td>
  </tr>
<tr>
 <td width="150" valign="top" bgcolor="#ffffff" nowrap><br>
        <p class="menu">· <a href="admin.php?action=main">$admin_menu[main]</a></p>

        <!--Begin details-->
        <p class="menu">· <a href="admin.php?action=details">$admin_menu[details]</a></p>
        <!--End details-->

        <!--Begin systems-->
        <p class="menu">· <a href="admin.php?action=os_browser">$admin_menu[os_browser]</a></p>
        <p class="menu">· <a href="admin.php?action=reso">$admin_menu[reso]</a></p>
        <p class="menu">· <a href="admin.php?action=systems">$admin_menu[systems]</a></p>
        <!--End sytems-->

        <!--Begin pages_time-->
        <p class="menu">· <a href="admin.php?action=pages">$admin_menu[pages]</a></p>
        <p class="menu">· <a href="admin.php?action=percorsi">$admin_menu[percorsi]</a></p>
        <p class="menu">· <a href="admin.php?action=time_pages">$admin_menu[time_pages]</a></p>
        <!--End pages_time-->

        <!--Begin referer_engines-->
        <p class="menu">· <a href="admin.php?action=referer">$admin_menu[referer]</a></p>
        <p class="menu">· <a href="admin.php?action=engines">$admin_menu[engines]</a></p>
        <p class="menu">· <a href="admin.php?action=query">$admin_menu[query]</a></p>
        <p class="menu">· <a href="admin.php?action=searched_words">$admin_menu[searched_words]</a></p>
        <!--End referer_engines-->

        <!--Begin hourly-->
        <p class="menu">· <a href="admin.php?action=hourly">$admin_menu[hourly]</a></p>
        <!--End hourly-->

        <!--Begin daily_monthly-->
        <p class="menu">· <a href="admin.php?action=daily">$admin_menu[daily]</a></p>
        <p class="menu">· <a href="admin.php?action=weekly">$admin_menu[weekly]</a></p>
        <p class="menu">· <a href="admin.php?action=monthly">$admin_menu[monthly]</a></p>
        <p class="menu">· <a href="admin.php?action=calendar">$admin_menu[calendar]</a></p>
        <p class="menu">· <a href="admin.php?action=compare">$admin_menu[compare]</a></p>
        <!--End daily_monthly-->

        <!--Begin ip-->
        <p class="menu">· <a href="admin.php?action=ip">$admin_menu[ip]</a></p>
        <!--End ip-->

        <!--Begin country-->
        <p class="menu">· <a href="admin.php?action=country">$admin_menu[country]</a></p>
        <!--End country-->

        <!--Begin bw_lang-->
        <p class="menu">· <a href="admin.php?action=bw_lang">$admin_menu[bw_lang]</a></p>
        <!--End bw_lang-->

        <!--Begin links-->
        <p class="menu">· <a href="admin.php?action=links">$admin_menu[links]</a></p>
        <!--End links-->

        <!--Begin downloads-->
        <p class="menu">· <a href="admin.php?action=downloads">$admin_menu[downloads]</a></p>
        <!--End downloads-->

        <!--Begin clicks-->
        <p class="menu">· <a href="admin.php?action=clicks">$admin_menu[clicks]</a></p>
        <!--End clicks-->

	<!--Begin daily_monthly-->
	<p class="menu">· <a href="admin.php?action=trend">$admin_menu[trend]</a></p>
	<!--End daily_monthly-->
        <br>
        <!--Begin is_loged_in-->
        	<p class="menu">· <a href="admin.php?action=preferenze">$admin_menu[options]</a></p>
            <!--Begin modify config-->
	        <p class="menu">· <a href="admin.php?action=modify_config">$admin_menu[modifyconfig]</a></p>
	        <!--End modify config-->
			<p class="menu">· <a href="admin.php?action=esclusioni">$admin_menu[esclusioni]</a></p>
			<p class="menu">· <a href="admin.php?action=optimize_tables">$admin_menu[optimize_tables]</a></p>
	        <!--Begin downloads-->
	        <p class="menu">· <a href="admin.php?action=downadmin">$admin_menu[downadmin]</a></p>
	        <!--End downloads-->
	        <!--Begin clicks-->
	        <p class="menu">· <a href="admin.php?action=clicksadmin">$admin_menu[clicksadmin]</a></p>
	        <!--End clicks-->
	        <p class="menu">· <a href="admin.php?action=backup">$admin_menu[backup]</a></p>
	        <p class="menu">· <a href="admin.php?action=resett">$admin_menu[reset]</a></p>
                <!--Begin errorlogviewer-->
	        <p class="menu">· <a href="admin.php?action=viewerrorlog">$admin_menu[errorlogviewer]</a></p>
                <!--End errorlogviewer-->
        <!--End is_loged_in-->
        <p class="menu">· <a href="admin.php?action=$admin_menu[status_rev]">$admin_menu[status]</a></p>
  </font>
  </td>
  <td width="90%" valign="top" bgcolor="#ffffff">
  $action
  <br>
   </td>
   </tr>
   <TR>
   <td height='10' bgColor='#C1C1C1' colSpan='2' align="center><a href="https://bizzarri.altervista.org/php-stats/"><span class='copyright'><b>v{$option[phpstats_ver]}</b> &#169; Roberto Bizzarri - $server_time</span></a>
 </td>  </TR>
</table>
</body>
</html>