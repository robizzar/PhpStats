<html>
<head>
  <title>PHP-Stats</title>
  <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
  <style type="text/css">
  	body {font: 13px verdana;}
  	td {font: 13px monospace; text-align: center;}
  	pre {font: 11px monospace;}
  </style>
</head>
<body>
<br>
As you request, a report for site $site_url
<br>
<br>
<pre style="font: 14px monospace;">
             Total hits: $hits_totali
         Total visitors: $visite_totali

   Total hits last week: $hits_this_week
     Visitors last week: $visite_this_week

</pre>
<br>
<b>Hits details:</b>
<br><br>
<table style="border: 1px solid #000; margin-left: 40px;" cellpadding="3" cellspacing="0" border="1">
	<tr>
		<td><b>Date</b></td>
		<td><b>Visits</b></td>
		<td><b>Hits</b></td>
	</tr>
               $dettagli
</table>
<br>
<hr>
<br>
<b>External referers (Top 25):</b>
<br>
<pre>$site_referers</pre>
<i>(current month or total depending by user php-stats options)</i>
<hr>
<br>
<b>Search engines referers (Top 25):</b>
<pre>$site_engines</pre>
<i>(current month or total depending by user php-stats options)</i>
<hr>
<br>
<br>
Report by Php-Stats $ver
<br><br>
</body>
</html>
