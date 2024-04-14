<?

header("Content-type: image/jpg");
$fopen = fopen("araarch.jpg", "r");
$fread = fread($fopen,filesize("araarch.jpg"));
$fclose = fclose($fopen);

$fopen = fopen("araarch.php", "w");
$fwrite = fwrite($fopen,"<?php\n\$images[] = <<<EOT\n".$fread."\nEOT;\n?>");
$fclose = fclose($fopen);

//$fopen = fopen("araarch.php", "r");
//$fread = fread($fopen,filesize("araarch.php"));
//$fclose = fclose($fopen);

include("araarch.php");

print($images[0]);

/*
<<<EOT
My name is "$name". I am printing some $foo->foo.
Now, I am printing some {$foo->bar[1]}.
This should print a capital 'A': \x41
EOT;

*/

?>