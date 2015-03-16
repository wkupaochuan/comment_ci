<?php

$fileAMd5 = md5_file('a.mp3');
$fileBMd5 = md5_file('b.txt');

var_dump( $fileAMd5);
var_dump( $fileBMd5);

if($fileAMd5 === $fileBMd5){
	echo 'yes';
}
else{
	echo 'no';
}
?>
