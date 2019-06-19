<?php
//////////////////////////////////////////////////////////
// days = N : Mostra gli ultimi N giorni				//		<- se omesso s'intende 7
// mode = 0 : Mostra tutto					            //		<- se omesso s'intende 0
// mode = 1 : Mostra n° di pagine visitate				//
// mode = 2 : Mostra n° di visitatori		            //
// mode = 3 : Mostra n° visitatori di ritorno           //
// width = N : Larghezza del grafico (in pixel)         //		<- se omesso s'intende 240
// height = N : Altezza del grafico (in pixel)          //		<- se omesso s'intende 120
// title = tt : Titolo del grafico						//		<- se omesso non visualizza alcun titolo
//////////////////////////////////////////////////////////
//
// ESEMPIO:
//	<script type="text/javascript" src="/stats/chart.js.php?width=250&height=100"></script>
//

define('IN_PHPSTATS',true);

if(isset($_GET['days'])) $days=$_GET['days']; else $days=7;
if(isset($_GET['mode'])) $mode=$_GET['mode']; else $mode=0;
if(isset($_GET['width'])) $width=$_GET['width']; else $width=240;
if(isset($_GET['height'])) $height=$_GET['height']; else $height=120;
if(isset($_GET['title'])) $title=addslashes($_GET['title']); else $title='';

require('option/php-stats-options.php');
require('inc/main_func_stats.inc.php');
require('inc/admin_func.inc.php');

if(!isset($option['prefix']))
	$option['prefix']='php_stats';

// Connessione a MySQL e selezione database
db_connect();

$last = array();
reset($last);

$res=sql_query("SELECT (hits-no_count_hits) AS hits,(visits-no_count_visits) AS visits, rets FROM $option[prefix]_daily WHERE 1 ORDER BY data DESC LIMIT $days");
$i = $days;
while($row=mysql_fetch_row($res))
{
	$last[] = "$i,".$row[0].','.$row[1].','.$row[2];
	$i--;
}
?>
document.write("<script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>");
document.write("<script>	\
google.load('visualization', '1.0', {'packages':['corechart']});	\
google.setOnLoadCallback(drawChart);	\
\
function drawChart() {	\
	var data = new google.visualization.DataTable();	\
	data.addColumn('number', 'Giorno');					\
	data.addColumn('number', 'Pagine');					\
	data.addColumn('number', 'Visitatori');				\
	data.addColumn('number', 'Ritorni');");
<?php

foreach($last as $val)
{
	$data .= "[$val],";
}
echo "document.write(\"data.addRows( [$data] );\");\n";
?>
document.write("var options = {	curveType: \"function\",					\
								enableInteractivity: false,					\
								backgroundColor: 'transparent',				\
								series: [{color: 'blue'}, {color: 'red'}, {color: 'green'}],	\
		                		vAxis: {minValue: 0},						\
								vAxis: {textPosition: 'left'},				\
								hAxis: {textPosition: 'none'},				\
		                        width: <?php echo $width ?>, 				\
		                        height: <?php echo $height ?>,				\
		                        title: '<?php echo $title ?>',				\
		                        legend: {position: '<?php $x = ($mode==0) ? 'bottom' : 'none'; echo $x; ?>'}};	\
\
	var chart = new google.visualization.LineChart(document.getElementById('chart_div'));	\
	var view = new google.visualization.DataView(data);	\
<?php
	if ($mode == 1)
		echo '    view.setColumns([0,1]);		\ ';
	elseif ($mode == 2)
		echo '    view.setColumns([0,2]);		\ ';
	elseif ($mode == 3)
		echo '    view.setColumns([0,3]);		\ ';
	else
		echo '    view.setColumns([0,1,2,3]);	\ ';
?>
chart.draw(view, options);	\
}	\
</script>");
document.write("<div id=\"chart_div\"></div>");
<?php

// Chiusura connessione a MySQL se necessario.
if($option['persistent_conn']!=1)
	mysql_close();
unset($option);
?>
