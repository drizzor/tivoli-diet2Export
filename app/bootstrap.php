<?php 

require_once('app/helpers/Uploader.php');
require_once('app/helpers/tools.php');
require_once('app/helpers/CSVCreator.php');

$json = $dataToExport = [];
$csvName = "";
$file = new Uploader();
$createCSV = new CSVCreator();
$file->setDir("public/csv/specnoteid/");
$file->setMaxSize(2);
$file->setExtensions(array('csv'));
$createCSV->setPath("public/csv/export/");