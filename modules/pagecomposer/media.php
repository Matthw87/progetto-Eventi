<?php


$id = $_GET['id'];
$type = $_GET['type'];
$file = $_GET['file'];
$file = 'media/'.$type."/".$file;
if( file_exists($file) ){
	
	echo file_get_contents($file);
}
exit;
		

?>