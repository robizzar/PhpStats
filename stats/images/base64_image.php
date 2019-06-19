<html>
<body>
<?php
/* Codifica un'immagine gif 16x16 per Php-Stat (/stats/images/).

NB: deve essere una GIF !!!
*/

$filename = key($_GET);
if ( empty($filename) )
{ exit('Missing filename! (script.php?filename - no .gif extension )'); }

$image = file_get_contents("$filename.gif");
echo base64_encode($image);
?>
</body>
</html>