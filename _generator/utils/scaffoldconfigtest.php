<?php

$scaffoldconfig = "../config/config.yaml";

///////////////////////////////////////////////////

$result = array();

if(!is_file($scaffoldconfig)){

	$result["status"] = "FAILED";
	$result["message"] = "Berkas konfigurasi aplikasi `root/config/config.yaml` tidak tersedia.";

}else{

	$result["status"] = "SUCCESS";
	$result["message"] = "Berkas konfigurasi aplikasi tersedia.";

}

echo json_encode($result);

?>