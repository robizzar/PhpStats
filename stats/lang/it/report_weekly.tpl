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
Come richiesto ecco un promemoria degli accessi sul sito $site_url
<br>
<br>
<pre style="font: 14px monospace;">
                Pagine visitate totali: $hits_totali
                     Visitatori totali: $visite_totali

Pagine visitate della scorsa settimana: $hits_this_week
     Visitatori della scorsa settimana: $visite_this_week

</pre>
<br>
<b>Dettagli visite:</b>
<br><br>
<table style="border: 1px solid #000; margin-left: 40px;" cellpadding="3" cellspacing="0" border="1">
	<tr>
		<td><b>Data</b></td>
		<td><b>Visitatori</b></td>
		<td><b>Pagine</b></td>
	</tr>
               $dettagli
</table>
<br>
<hr>
<br>
<b>Referers esterni (Top 25):</b>
<br>
<pre>$site_referers</pre>
<i>(del mese corrente se l'opzione è attiva, totali altrimenti)</i>
<hr>
<br>
<b>Accessi dai motori di ricerca (Top 25):</b>
<pre>$site_engines</pre>
<i>(del mese corrente se l'opzione è attiva, totali altrimenti)</i>
<hr>
<br>
<br>
Report generato da Php-Stats $ver
<br><br>
</body>
</html>
