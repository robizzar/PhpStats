<?php
//////////////////////////////////////////////////////////
// page = p : url della pagina							//		<- Necessario, se omesso usa la pagine corrente
// width = N : Larghezza del grafico (in pixel)         //		<- se omesso s'intende 320
// height = N : Altezza del grafico (in pixel)          //		<- se omesso s'intende 240
// title = tt : Titolo del grafico						//		<- se omesso non visualizza alcun titolo
//////////////////////////////////////////////////////////
//
// ESEMPIO:
//	<script type="text/javascript" src="/stats/page.js.php?page=https://bizzarri.altervista.org/blog/"></script>
//

define('IN_PHPSTATS',true);

if(isset($_GET['page'])) $page=urldecode($_GET['page']); else $page=$_SERVER['HTTP_REFERER'];
if(isset($_GET['width'])) $width=$_GET['width']; else $width=320;
if(isset($_GET['height'])) $height=$_GET['height']; else $height=240;
if(isset($_GET['title'])) $title=addslashes($_GET['title']); else $title='';

require('option/php-stats-options.php');
require('inc/main_func_stats.inc.php');
require('inc/admin_func.inc.php');

if(!isset($option['prefix']))
	$option['prefix']='php_stats';

// Connessione a MySQL e selezione database
db_connect();

$last = array();

$res=sql_query("SELECT COUNT(*) AS hits, FROM_UNIXTIME(date, '%Y-%m-%d') as datetime FROM $option[prefix]_details WHERE currentPage='$page' GROUP BY datetime ORDER BY datetime LIMIT 10");

// Se non ci sono almeno due dati non genera il grafico
if(mysql_num_rows($res)<2) {
	mysql_close();
	exit();
}
?>
document.write( "<script type=\"text/javascript\" src=\"https://www.google.com/jsapi\"></script>" );
document.write( "<script type=\"text/javascript\">" );
document.write( "google.load('visualization', '1', {'packages':['corechart']});" );
document.write( "google.setOnLoadCallback(drawChart);" );

document.write( "function drawChart() {					\n\
	var data = new google.visualization.DataTable();	\
	data.addColumn('number', 'Hits');					\
	data.addColumn('date', 'Date');");

document.write( "data.addRows([		\n\
<?php
while($row = mysql_fetch_row($res))
{
	echo "[$row[0], new Date('$row[1]')]," . '\n\\' . "\n";
}
echo "]);" . '");';
?>

document.write("var options = {	enableInteractivity: false,					\n\
								backgroundColor: 'transparent',				\n\
								colors: ['blue'],							\n\
		                		//vAxis: {minValue: 0},						\n\
								vAxis: {format: '0'},						\n\
								//hAxis: {format: 'd'},					\n\
								hAxis: {slantedText: false},				\n\
								//hAxis: {slantedTextAngle: 90},				\n\
		                        width: <?php echo $width ?>, 				\n\
		                        height: <?php echo $height ?>,				\n\
		                        title: '<?php echo $title ?>',				\n\
		                        legend: {position: 'none'},					\n\
		                        };											\n\
\
	var chart = new google.visualization.ColumnChart(document.getElementById('page_chart_div'));	\n\
	var view = new google.visualization.DataView(data);										\n\
	view.setColumns([1,0]);	\n\
	chart.draw(view, options);	\n\
}	\
</script>");

document.write("<div id=\"page_chart_div\"></div>");
<?php

mysql_close();
?>
