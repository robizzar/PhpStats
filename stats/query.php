<html><title>:: Php-Stats - MySQL Query Utility ::</title>
<body>
<pre>
<br>
<?php
// Per ragioni di sicurezza i file inclusi avranno un controllo di provenienza
define('IN_PHPSTATS', true);

if(isset($_POST['pswd']))	$pswd = addslashes($_POST['pswd']);
else						$pswd = '';
if(isset($_POST['q']))		$query = $_POST['q'];
else						$query = 'SELECT * FROM php_stats_ WHERE 1 LIMIT 10';

// inclusione delle principali funzioni esterne
require('config.php');
require('inc/main_func.inc.php');
require('inc/user_func.inc.php');

// Connessione a MySQL e selezione database
db_connect();

if (user_is_logged_in() || user_login(false, $pswd))
{
	echo 'PHP version: ' . phpversion();
	echo '<br>';
	echo 'mySQL version: ' . mysql_get_server_info();
	echo '<br><br><br>';

    echo
    '<form action="query.php" method="post">'.
    'QUERY: <input style="font-family:monospace;" type="text" name="q" size="100" value="'.$query.'"><br><br>'.
    '<input type="submit" value="Query">'.
    '<input name="pswd" type="hidden" value="'.$pswd.'">'.
    '<br><br>';

	if(isset($_POST['q']))
	{
		$res = mysql_query($query);
		if (!$res) {
		    $message  = 'Invalid query: ' . mysql_error() . '<br>';
		    $message .= 'Whole query: ' . $query . '<br>';
		   // die($message);
		}

		while ($row = mysql_fetch_assoc($res)) {
		    print_r($row);
		}
	}
}
else
{
    echo
    '<center><form action="query.php" method="post">'.
    'Php-Stats Password: <input name="pswd" type="password" value=""><br><br>'.
    '<input type="submit" value="OK"></center>';
}
?>
</pre>
</body>
</html>
