<?php

include("../engines/spyc.php");

$scaffoldconfig = "../config/config.yaml";
$appconfig		= "../config/app.yaml";

$config = Spyc::YAMLLoad($scaffoldconfig); 
$app	= Spyc::YAMLLoad($appconfig);

$appname = $app[0][0];

///////////////////////////////////////////////////

$result = array();

$result["name"] = $appname;
$result["objectcount"] = count($config);

echo json_encode($result);

?>