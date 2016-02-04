<?php

include("../config/db.config.php");

$result = array();

if(mysql_query("SHOW TABLES")) {

	$result["status"] = "SUCCESS";
	$result["message"] = "Koneksi database berhasil.";

} else {

	$result["status"] = "FAILED";
	$result["message"] = "Koneksi database tidak berhasil.";

}

echo json_encode($result);

?>