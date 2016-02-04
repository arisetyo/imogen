<?php

include("../engines/spyc.php");
$dbconfig = "../config/db.config.php";

///////////////////////////////////////////////////

$result = array();

if(!is_file($dbconfig)){

	$result["status"] = "FAILED";
	$result["message"] = "Berkas konfigurasi database `root/config/db.config.php` tidak tersedia.";

}else{

	$result["status"] = "SUCCESS";
	$result["message"] = "Berkas konfigurasi database tersedia.";

}

echo json_encode($result);

?>