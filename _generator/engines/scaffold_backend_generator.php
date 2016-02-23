<?php
require_once("Scaffold.php");
$scaffold = new Scaffold();

printf($scaffold->initScaffoldMessage);
printf($scaffold->initConfigMessage);

#CHECKING DB CONFIG FILE
$dbconfig = "../config/db.config.php";
if(is_file($dbconfig)) include($dbconfig);
else die($scaffold->dbConfigErrorMessage);

include("spyc.php");
//
#CHECKING APP CONFIG FILE
$appconfig = "../config/app.yaml";
if(is_file($appconfig)) $appinfo = Spyc::YAMLLoad($appconfig);
else die($scaffold->mainConfigErrorMessage);
//

//APP'S MAIN CONFIG FILE, INCLUDING DATABASE CREDENTIALS
$appname = $appinfo[0][0];
printf($scaffold->mainConfigLoadedMessage." ".$appname."<br/>");

#CHECKING YAML CONFIG FILE
#MOST IMPORTANT FILE IN THE PROCESS: config.yaml
$yamlconfig = "../config/config.yaml";
if(is_file($yamlconfig)) $CONFIG = Spyc::YAMLLoad($yamlconfig);
else die($scaffold->appConfigErrorMessage);
printf($scaffold->configArrayLoadedMessage);

//INITIALIZING PROCESS
printf($scaffold->objectArrayInitMessage);
$tablecount = count($CONFIG);
if($tablecount==0) die($scaffold->noTableInDbErrorMessage);

#############################
# DATABASE TABLES CREATION
#############################

for($i=0;$i<$tablecount;$i++){

	$tablename = $CONFIG[$i]['table'];
	$fields = $CONFIG[$i][0];
	$fieldcount = count($fields);
	foreach($fields as $field){
		$fieldtext[] = $field[0];
		$fieldname[] = $field[1];
		$fieldtype[] = $field[2];
	}
	
	$drop_existing = "DROP TABLE ".$tablename;
	mysql_query($drop_existing);
	
	$field_list = "";
	$create_query = "CREATE TABLE IF NOT EXISTS ".DATABASE_NAME.".".$tablename." (id INT(4) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
	for($j=0;$j<$fieldcount;$j++){
		switch($fieldtype[$j]){
			case "FK": $realfieldtype = "INT(8)"; break;
			case "int": $realfieldtype = "INT(4)"; break;
			case "boolean": $realfieldtype = "BOOL"; break;
			case "varchar": $realfieldtype = "VARCHAR(128)"; break;
			case "text": $realfieldtype = "TEXT"; break;
			case "date": $realfieldtype = "DATE"; break;
			case "double": $realfieldtype = "DOUBLE"; break;
			case "timestamp": $realfieldtype = "TIMESTAMP"; break;
		}
		$field_list .= $fieldname[$j]." ".$realfieldtype." NOT NULL ,";
	}
	$create_query .= substr($field_list, 0, -1);
	$create_query .= ") ENGINE = MYISAM";
	
	mysql_query($create_query);
	
	unset($fields);
	unset($fieldcount);
	unset($fieldtext);
	unset($fieldname);
	unset($fieldtype);
	unset($field_list);
	unset($create_query);
}


printf($scaffold->openFileClassMessage);
require_once("../utils/Fileoperation.php");
$fo = new Fileoperation();

//DEFINE LOCATIONS TO PUT GENERATED FILES
$slimapp_dir	= "../../api/";
$slimmodel_dir	= "../../api/model/";

// MAIN APP FILE
$appclass_file 		= $fo->OpenFile('../template/','slim_index.php.tmpl','r');
$appclass_content 	= $fo->FileContent($appclass_file);

$appdbconfig 		= "R::setup('mysql:host=localhost;dbname=".DATABASE_NAME."','".DATABASE_USERNAME."','".DATABASE_PASSWORD."');";
$appclass_content  	= preg_replace('/PATDBCONFIGPAT/', $appdbconfig, $appclass_content);

#################################
# CLASS AND COMPONENTS CREATION
#################################

printf($scaffold->classComCreateMessage);
/*
LOOP OVER ALL TABLES DEFINED IN YAML FILE
THIS WILL LOOP ON EVERY TABLE ON THE YAML CONFIG.
I.E. IT WILL LOOP 3 TIMES IF THERE ARE 3 TABLES IN THE CONFIG.
THE LOOP INSIDE A TABLE (COLUMN LOOP) HAPPENS INSIDE THIS MAIN LOOP.
*/
$appmodel_inclusion = "";
$SLIMROUTERS = "";
for( $i=0; $i < $tablecount; $i++ ) {
	
	$objectname = $CONFIG[$i]['table'];	//store table names
	$fields 	= $CONFIG[$i][0];		//store fields information in each tables
	$nodes 		= $CONFIG[$i][1];

	//LOOP OVER THE COLUMNS (FIELDS OF THIS TABLE AND STORE IT IN $fields)	
	$fieldcount = count($fields);	//store fields size in each tables

	foreach($fields as $field) {
		$fieldtext[] = $field[0];	//store fields text (for use in form/report) in each tables
		$fieldname[] = $field[1];	//store fields names (for use in SQL) in each tables
		$fieldtype[] = $field[2];	//store fields datatype (for use in SQL) in each tables
		
		if($field[2]=="FK")			//collect FK's array values
			$fieldisFK[] = $field[3];
		else
			$fieldisFK[] = [];
	}

	//LOOP OVER THE TABLES ADDITIONAL INFO. E.G. NODE NAME (FOR MENU/HEADER), FIND COLUMN
	foreach($nodes as $node) {
		$nodename 	= $node[0];	//store each table names' node name (name to be used in the menus)
		$itemname	= $node[1]; //singular form of the table name. for API node purposes
		$findcolumn = $node[2];	//default column to be used on a search
	}
	
	#############################
	# GENERATE API MODEL
	#############################	
	$modelclass_file 	= $fo->OpenFile('../template/','slim_model.php.tmpl','r');
	$modelclass_content = $fo->FileContent($modelclass_file);
	//
	$modelclass_content = preg_replace('/PATclassnamePAT/',	ucfirst($objectname),	$modelclass_content);
	$modelclass_content = preg_replace('/PATtablenamePAT/',	$objectname, 			$modelclass_content);
	$modelclass_content = preg_replace('/PATdefaultsearchcolumnPAT/', $findcolumn,	$modelclass_content);
	/*
	CURRENTLY NOT USED IN THIS VERSION
	THIS IS USED IN CREATING AN FK ID SELECT LIST IN THE FORM
	$modelclass_content = preg_replace('/PATcblabelPAT/',	$fieldname[0],			$modelclass_content);
	*/
	
	//
	$modelcreatepairing = "";
	$modelupdatepairing = "";
	
	$foreignkey_sql_COL = "";
	$foreignkey_sql_TBL = "";
	$foreignkey_sql_WHEREI = "";
	$foreignkey_sql_WHEREF = "";
	
	//LOOP OVER THE FIELDS INSIDE EACH OBJECT (TABLES/CLASSES)
	for( $j=0; $j < $fieldcount; $j++ ){
		//INSERT FIELDS ARRAY PAIRINGS
		$modelcreatepairing .= "\t\t". 		'$item->'.$fieldname[$j].' = (string)$input->'.$fieldname[$j] .";\r";
		$modelupdatepairing .= "\t\t\t". 	'$item->'.$fieldname[$j].' = (string)$input->'.$fieldname[$j] .";\r";
		
		//CHECK FOR FOREIGN KEYS & GENERATE COMBOBOX
		if(count($fieldisFK[$j])>0) {
			$fk_reftable  = $fieldisFK[$j][0];
			$fk_refcolumn = $fieldisFK[$j][1];
		
			//for FOREIGN KEYS
			$foreignkey_sql_COL   .= ", $fk_reftable.$fk_refcolumn";
			$foreignkey_sql_TBL   .= ", $fk_reftable";
			$foreignkey_sql_WHEREI .= "WHERE ".$fk_reftable.".id = ".$objectname.".".$fieldname[$j];
			$foreignkey_sql_WHEREF .= "AND ".$fk_reftable.".id = ".$objectname.".".$fieldname[$j];
		}
	}
	
	//WRITE TO PHP MODELS
	$modelclass_content = preg_replace('/PATcreatepairingPAT/', $modelcreatepairing, $modelclass_content);
	$modelclass_content = preg_replace('/PATupdatepairingPAT/', $modelupdatepairing, $modelclass_content);
	
	$modelclass_content = preg_replace('/PATforeignkeysqlPAT_COL/', $foreignkey_sql_COL, $modelclass_content);
	$modelclass_content = preg_replace('/PATforeignkeysqlPAT_TBL/', $foreignkey_sql_TBL, $modelclass_content);
	$modelclass_content = preg_replace('/PATforeignkeysqlPAT_WHEREI/', $foreignkey_sql_WHEREI, $modelclass_content);
	$modelclass_content = preg_replace('/PATforeignkeysqlPAT_WHEREF/', $foreignkey_sql_WHEREF, $modelclass_content);
	//
	$fo->CreateFile($modelclass_content, "Model".ucfirst($objectname).".php", $slimmodel_dir);

	
	#############################
	# SLIM APP CONTENT CREATION
	#############################
	$appmodel_inclusion .= "require 'model/Model".ucfirst($objectname).".php';" ."\r";

	$slimrouter_file 	= $fo->OpenFile('../template/','slim_router_codes.php.tmpl','r');
	$slimrouter_content = $fo->FileContent($slimrouter_file);

	$slimrouter_content = preg_replace('/PATtablenamePAT/', $objectname, 			$slimrouter_content);
	$slimrouter_content = preg_replace('/PATclassnamePAT/', ucfirst($objectname),	$slimrouter_content);
	$slimrouter_content = preg_replace('/PATitemnamePAT/',  $itemname,				$slimrouter_content);
	
	// COMBINE ALL ROUTERS IN ONE STRING
	
	$SLIMROUTERS .= $slimrouter_content;

	#############################
	# EMPTY EACH VARIABLES
	#############################
	
	unset($objectname);
	unset($itemname);
	unset($findcolumn);
	//
	unset($fields);
	unset($fieldcount);
	unset($fieldtext);
	unset($fieldname);
	unset($fieldtype);
	unset($fieldisFK);
	//
	unset($nodes);
	unset($nodename);
	unset($findcolumn);
	//
	unset($modelclass_file);
	unset($modelclass_content);
	unset($modelcreatepairing);
	unset($modelupdatepairing);
	//
	unset($foreignkey_sql_COL);
	unset($foreignkey_sql_TBL);
	unset($foreignkey_sql_WHEREI);
	unset($foreignkey_sql_WHEREF);
	//
	unset($slimrouter_file);
	unset($slimrouter_content);
	//

}
// END OF : LOOP OVER ALL TABLES DEFINED IN YAML FILE. 


//MAIN APP index.php CONTENT REPLACEMENT
$appclass_content  = preg_replace('/PATMODELSINCLUSIONPAT/',	$appmodel_inclusion, 	$appclass_content);
$appclass_content  = preg_replace('/PATSLIMROUTERSPAT/', 		$SLIMROUTERS, 			$appclass_content);

$fo->CreateFile($appclass_content, "index.php", $slimapp_dir);
printf("<strong>Pembuatan database dan aplikasi backend (REST API) selesai.</strong><br/>");

#############################
# END MESSAGE
#############################
printf($scaffold->completionMessageB);
printf("<br/>");

?>