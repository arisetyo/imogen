<?php
require_once("Scaffold.php");
$scaffold = new Scaffold();

printf($scaffold->initScaffoldMessage);
printf($scaffold->initConfigMessage);

include("spyc.php");
//
#CHECKING APP CONFIG FILE
$appconfig = "../config/app.yaml";
if(is_file($appconfig)) $appinfo = Spyc::YAMLLoad($appconfig);
else die($scaffold->mainConfigErrorMessage);
//

// = = = = = = = = = = = = = = = = = =
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

// = = = = = = = = = = = = = = = = = =
// = = = = = = = = = = = = = = = = = =
printf($scaffold->openFileClassMessage);
require_once("../utils/Fileoperation.php");
$fo = new Fileoperation();
// = = = = = = = = = = = = = = = = = =

//DEFINE LOCATIONS TO PUT GENERATED FILES
$htmlview_dir		= "../../views/";
$jscontrol_dir		= "../../controllers/";
$htmlmain_dir		= "../../";

// = = = = = = = = = = = = = = = = = =
// MAIN APP FILE
$appclass_file 		= $fo->OpenFile('../template/','app.js.tmpl','r');
$appclass_content 	= $fo->FileContent($appclass_file);

$appclass_content  	= preg_replace('/PATAPICONFIGPAT/', "http://localhost/$appname/api/index.php/", $appclass_content);
$appclass_content  	= preg_replace('/PATAPPNAMEPAT/', $appname, $appclass_content);

//

// MAIN INDEX.HTML
$appindex_file 		= $fo->OpenFile('../template/','index.html.tmpl','r');
$appindex_content 	= $fo->FileContent($appindex_file);

$appindex_content  	= preg_replace('/PATAPPNAMEPAT/', $appname, $appindex_content);

// MENU.HTML
$html_menu_file 	= $fo->OpenFile('../template/','menu.html.tmpl','r');
$html_menu_content 	= $fo->FileContent($html_menu_file);
	
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

//LOOPED VALUES STRING: LEVEL 1
$html_classinjections = "";
$html_viewroutes = "";
$html_controllerfiles = "";
$html_menulinks = "";

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
	}

	//LOOP OVER THE TABLES ADDITIONAL INFO. E.G. NODE NAME (FOR MENU/HEADER), FIND COLUMN
	foreach($nodes as $node) {
		$nodename 	= $node[0];	//store each table names' node name (name to be used in the menus)
		$itemname	= $node[1]; //singular form of the table name. for API node purposes
		$findcolumn = $node[2];	//default column to be used on a search
	}
	
	///// ///// ///// ///// ///// ///// ///// ///// ///// ///// ///// /////
	// STRINGS FOR MAIN FILES
	//app.js
	$html_classinjections .= ",'$objectname'";
	$html_viewroutes .=	"\t\t.when('/$objectname', {\r".
						"\t\t\tcontroller:'$objectname"."Controller',\r".
						"\t\t\ttemplateUrl:'views/$objectname/list.html'\r".
						"\t\t})\r".
						"\t\t.when('/$objectname"."_entri', {\r".
						"\t\t\tcontroller:'$objectname"."Controller',\r".
						"\t\t\ttemplateUrl:'views/$objectname/entry.html'\r".
						"\t\t})\r".
						"\t\t.when('/$objectname"."_edit/:id', {\r".
						"\t\t\tcontroller:'$objectname"."Controller',\r".
						"\t\t\ttemplateUrl:'views/$objectname/edit.html'\r".
						"\t\t})\r\r";	
	//index.html
	$html_controllerfiles .= "\t<script src=\"controllers/$objectname.js\"></script>\r";
	//menu.html
	$html_menulinks .= "<li><a class=\"menu-color\" href=\"#/$objectname\"><i class=\"fa fa-asterisk\"></i> $nodename</a></li>";
	
	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	
	//1
	#############################
	# GENERATE JS : CONTROLLER
	#############################
	
	$js_controller_file 		= $fo->OpenFile('../template/','controller.js.tmpl','r');
	$js_controller_content 		= $fo->FileContent($js_controller_file);
	
	$js_controller_content  	= preg_replace('/PATobjectnamePAT/', $objectname, $js_controller_content);
	
	//2
	#############################
	# GENERATE HTML : LIST PAGE
	#############################
	$html_list_file 		= $fo->OpenFile('../template/','list.html.tmpl','r');
	$html_list_content 		= $fo->FileContent($html_list_file);
	
	$html_list_content  	= preg_replace('/PATnodenamePAT/', $nodename, $html_list_content);
	$html_list_content  	= preg_replace('/PATmaincolumnPAT/', $findcolumn, $html_list_content);
	$html_list_content  	= preg_replace('/PATmaincolumnnamePAT/', $nodename, $html_list_content);
	$html_list_content  	= preg_replace('/PATobjectnamePAT/', $objectname, $html_list_content);
	
	//3
	#############################
	# GENERATE HTML : INSERT PAGE
	#############################
	$html_ins_file 		= $fo->OpenFile('../template/','entry.html.tmpl','r');
	$html_ins_content 	= $fo->FileContent($html_ins_file);
	
	$html_ins_content  	= preg_replace('/PATnodenamePAT/', $nodename, $html_ins_content);
	$html_ins_content  	= preg_replace('/PATobjectnamePAT/', $objectname, $html_ins_content);
	
	//4
	#############################
	# GENERATE HTML : UPDATE PAGE
	#############################
	$html_upd_file 		= $fo->OpenFile('../template/','edit.html.tmpl','r');
	$html_upd_content 	= $fo->FileContent($html_upd_file);
	
	$html_upd_content  	= preg_replace('/PATnodenamePAT/', $nodename, $html_upd_content);
	$html_upd_content  	= preg_replace('/PATobjectnamePAT/', $objectname, $html_upd_content);

	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	
	//LOOPED VALUES STRING: LEVEL 2
	//1
	$js_insert_pairing = "";
	$js_load_pairing = "";
	$js_update_pairing = "";
	//2
	$html_ins_inputcomponents = "";
	//3
	$html_list_detail = "";
	//4
	$html_upd_inputcomponents = "";
	
	//
	//LOOP OVER THE FIELDS INSIDE EACH OBJECT (TABLES/CLASSES)
	for( $j=0; $j < $fieldcount; $j++ ) {
		//1
		$js_insert_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".ins_$fieldname[$j],\r";
		$js_load_pairing .= "\t\t\t".'$scope'.".upd_$fieldname[$j] = item.$fieldname[$j];\r";
		$js_update_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".upd_$fieldname[$j],\r";
		//2
		$html_ins_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
                					 "\t\t\t\t<input class=\"form-control\" placeholder=\"$fieldtext[$j]\" ng-model=\"ins_$fieldname[$j]\">\r".
            						 "\t\t\t</div>\r";
		//3
		$html_list_detail .= "\t\t\t\t\t\t$fieldtext[$j]: ".'{{item.'."$fieldname[$j]}}<br/>\r";
		//4
		$html_upd_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
                					 "\t\t\t\t<input class=\"form-control\" placeholder=\"$fieldtext[$j]\" ng-model=\"upd_$fieldname[$j]\">\r".
            						 "\t\t\t</div>\r";
	}
	
	//1
	$js_controller_content  	= preg_replace('/PATinputpairingPAT/', $js_insert_pairing, $js_controller_content);
	$js_controller_content  	= preg_replace('/PATloadpairingPAT/', $js_load_pairing, $js_controller_content);
	$js_controller_content  	= preg_replace('/PATupdatepairingPAT/', $js_update_pairing, $js_controller_content);
	//2
	$html_ins_content		= preg_replace('/PATinputcomponentsPAT/', $html_ins_inputcomponents, $html_ins_content);
	//3
	$html_list_content  	= preg_replace('/PATitemdetailPAT/', $html_list_detail, $html_list_content);
	//4
	$html_upd_content		= preg_replace('/PATeditcomponentsPAT/', $html_upd_inputcomponents, $html_upd_content);
	// - - - - - -
	
	//1
	$fo->CreateFile($js_controller_content, $objectname.".js", $jscontrol_dir);
	//2
	$fo->CreateFile($html_ins_content, "entry.html", $htmlview_dir.$objectname."/");
	//3
	$fo->CreateFile($html_list_content, "list.html", $htmlview_dir.$objectname."/");
	//4
	$fo->CreateFile($html_upd_content, "edit.html", $htmlview_dir.$objectname."/");
	
	// - - - - - -
	
	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
		
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
	
	//1
	// = = = = = = = = = = = = 
	unset($html_list_detail);
	//
	unset($html_list_file);
	unset($html_list_content);
	
	//2
	// = = = = = = = = = = = = 
	unset($html_ins_inputcomponents);
	//
	unset($html_ins_file);
	unset($html_ins_content);
	
	//3
	// = = = = = = = = = = = = 
	unset($js_insert_pairing);
	unset($js_load_pairing);
	unset($js_update_pairing);
	//
	unset($js_controller_file);
	unset($js_controller_content);
	
	//4
	// = = = = = = = = = = = =
	unset($html_upd_inputcomponents);
	//
	unset($html_upd_file);
	unset($html_upd_content);
	
}
// END OF : LOOP OVER ALL TABLES DEFINED IN YAML FILE. 

// = = = = = = = = = = = = = = = = =

$html_menu_content  = preg_replace('/PATMENULINKSPAT/', $html_menulinks, $html_menu_content);

$fo->CreateFile($html_menu_content, "menu.html", $htmlview_dir);
printf("<strong>Berkas HTML untuk menu navigasi selesai.</strong><br/>");

// = = = = = = = = = = = = = = = = =

//MAIN APP app.js CONTENT REPLACEMENT
$appclass_content  	= preg_replace('/PATCLASSINJECTIONPAT/', $html_classinjections, $appclass_content);
$appclass_content  	= preg_replace('/PATVIEWROUTESPAT/', $html_viewroutes, $appclass_content);

$fo->CreateFile($appclass_content, "app.js", $htmlmain_dir);
printf("<strong>Berkas JavaScript utama (app.js) selesai.</strong><br/>");

// = = = = = = = = = = = = = = = = =

$appindex_content  	= preg_replace('/PATCONTROLLERFILESPAT/', $html_controllerfiles, $appindex_content);

$fo->CreateFile($appindex_content, "index.html", $htmlmain_dir);
printf("<strong>Berkas HTML utama (index.html) selesai.</strong><br/>");



#############################
# END MESSAGE
#############################
printf($scaffold->completionMessageF);

?>