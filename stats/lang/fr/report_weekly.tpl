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
Récapitulatif des statistiques hebdomadaires du site: $site_url
<br>
<br>
<pre style="font: 14px monospace;">
                    Total des pages visitées: $hits_totali
                         Total des visiteurs: $visite_totali

    Total des accès de la semaine précédente: $hits_this_week
Total des visiteurs de la semaine précédente: $visite_this_week

</pre>
<b>Détails des visites:</b>
<br><br>
<table style="border: 1px solid #000; margin-left: 40px;" cellpadding="3" cellspacing="0" border="1">
	<tr>
		<td><b>Dates</b></td>
		<td><b>Visiteurs</b></td>
		<td><b>Pages</b></td>
	</tr>
               $dettagli
</table>
<br>
<hr>
<br>
<b>Référents externes (Top 25):</b>
<br>
<pre>$site_referers</pre>
<i>(Récapitulatif ou le total dépends des options enregistrées)</i>
<hr>
<br>
<b>Moteurs de recherche (Top 25):</b>
<pre>$site_engines</pre>
<i>(Récapitulatif ou le total dépends des options enregistrées)</i>
<hr>
<br>
Rapport généré par Php-Stats $ver
<br><br>
</body>
</html>
