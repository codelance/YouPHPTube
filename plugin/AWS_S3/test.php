<?php
require_once '../../videos/configuration.php';
require_once './AWS_S3.php';

if(!User::isAdmin()){
    die("Must be admin for testing");
}

$aws = new AWS_S3();
$tmp_name = "{$global['systemRootPath']}plugin/AWS_S3/test.txt";
$filename = "test.txt";
$result = $aws->copy_to_s3($tmp_name, $filename);
$url = $aws->getURL($filename);

echo "<h1>Upload Result</h1>";
var_dump($result);
echo "<h1>URL</h1>";
echo "<a href='{$url}'>{$url}</a>";
//$aws->removeFiles("teste");
