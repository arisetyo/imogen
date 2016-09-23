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
$htmlmain_dir		= "../../app/";
$htmlview_dir		= "../../app/views/";
$jscontrol_dir	= "../../app/controllers/";
$jsservice_dir	= "../../app/services/";

if( !file_exists ($htmlmain_dir) )  mkdir($htmlmain_dir, 0755);
if( !file_exists ($htmlview_dir) )  mkdir($htmlview_dir, 0755);
if( !file_exists ($jscontrol_dir) ) mkdir($jscontrol_dir, 0755);
if( !file_exists ($jsservice_dir) ) mkdir($jsservice_dir, 0755);

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
$html_servicefiles = "";
$html_menulinks = "";

for( $i=0; $i < $tablecount; $i++ ) {

	$objectname = $CONFIG[$i]['table'];	//store table names
	$fields 	= $CONFIG[$i][0];		//store fields information in each tables
	$nodes 		= $CONFIG[$i][1];
	
	//LOOP OVER THE COLUMNS (FIELDS OF THIS TABLE AND STORE IT IN $fields)	
	$fieldcount = count($fields);	//store fields size in each tables

	foreach($fields as $field) {
		$fieldtext[] = $field[0];		//store fields text (for use in form/report) in each tables
		$fieldname[] = $field[1];		//store fields names (for use in SQL) in each tables
		$fieldtype[] = $field[2];		//store fields datatype (for use in SQL) in each tables
		
		if($field[2]=="FK")				//collect FK's array values
			$fieldisFK[] = $field[3];
		else
			$fieldisFK[] = [];
	}

	//LOOP OVER THE TABLES ADDITIONAL INFO. E.G. NODE NAME (FOR MENU/HEADER), FIND COLUMN
	foreach($nodes as $node) {
		$nodename = $node[0]; //store each table names' node name (name to be used in the menus)
		$itemname = $node[1]; //singular form of the table name.
		$findname = $node[2]; //default column to be used on a search
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
	$html_servicefiles .= "\t<script src=\"services/$objectname.js\"></script>\r";
	//menu.html
	$html_menulinks .= "<li><a href=\"#/$objectname\"><i class=\"fa fa-asterisk\"></i> $nodename</a></li>\r";
	
	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	// = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =
	
	//1
	#####################################
	# GENERATE JS : CONTROLLER & SERVICES
	#####################################
	
	$js_controller_file 			= $fo->OpenFile('../template/','controller.js.tmpl','r');
	$js_controller_content 		= $fo->FileContent($js_controller_file);
	
	$js_controller_content  	= preg_replace('/PATobjectnamePAT/', $objectname, $js_controller_content);
	$js_controller_content  	= preg_replace('/PATitemnamePAT/', $itemname, $js_controller_content);
	$js_controller_content  	= preg_replace('/PATclassnamePAT/', ucfirst($objectname), $js_controller_content);
	
	///
	$js_service_file 			= $fo->OpenFile('../template/','service.js.tmpl','r');
	$js_service_content 	= $fo->FileContent($js_service_file);

	$js_service_content  	= preg_replace('/PATobjectnamePAT/', $objectname, $js_service_content);
	$js_service_content  	= preg_replace('/PATclassnamePAT/', ucfirst($objectname), $js_service_content);

	//2
	#############################
	# GENERATE HTML : LIST PAGE
	#############################
	$html_list_file 		= $fo->OpenFile('../template/','list.html.tmpl','r');
	$html_list_content 		= $fo->FileContent($html_list_file);
	
	$html_list_content  	= preg_replace('/PATnodenamePAT/', $nodename, $html_list_content);
	$html_list_content  	= preg_replace('/PATmaincolumnPAT/', $itemname, $html_list_content);
	$html_list_content  	= preg_replace('/PATfindcolumnnamePAT/', $findname, $html_list_content);
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
	$inject_module = "";
	$inject_class = "";
	$inject_object = "";

	//1
	$js_insert_pairing = "";
	$js_load_pairing = "";
	$js_update_pairing = "";
	//2
	$html_list_detail = "";
	//3
	$html_ins_inputcomponents = "";
	//4
	$html_upd_inputcomponents = "";
	
	//SPECIAL TYPE INPUTS
	$js_forComboInit = "";
	$js_forLoadInit = "";
	//
	$js_forDateInit = "";
	$js_forDateToday = "";
	$js_forDateCode = "";
	
	//
	//LOOP OVER THE FIELDS INSIDE EACH OBJECT (TABLES/CLASSES)
	for( $j=0; $j < $fieldcount; $j++ ) {
		
		//CHECK FOR FOREIGN KEYS & GENERATE COMBOBOX
		if(count($fieldisFK[$j])>0) {
			$fk_reftable  = $fieldisFK[$j][0];
			$fk_refcolumn = $fieldisFK[$j][1];
		}
		
		//fieldtypes: text, default
		switch( $fieldtype[$j] ) {
			case "text":
				//1
				/*HTML untuk menampilkan value sebuah field database di halaman 'list'*/
				$html_list_detail .= "\t\t\t\t\t\t$fieldtext[$j]: ".'{{item.'."$fieldname[$j]}}<br/>\r";
		
				//2
				//$js_insert_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".ins_$fieldname[$j],\r";
				//$js_update_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".upd_$fieldname[$j],\r";
		
				/*PAIRING SAAT LOAD VALUE ORIGINAL DI HALAMAN 'Edit'*/
				//$js_load_pairing .= "\t\t\t".'$scope.upd_'."$fieldname[$j] = item.$fieldname[$j];\r";
				
				//3 - HTML untuk pembuatan input component di halaman 'entry'
				$html_ins_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
		                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
		                					 "\t\t\t\t<textarea class=\"form-control\" placeholder=\"$fieldtext[$j]\" ng-model=\"new_item.$fieldname[$j]\"></textarea>\r".
		            						 "\t\t\t</div>\r";

				//4 - HTML untuk pembuatan input component di halaman 'edit'
				$html_upd_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
		                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
		                					 "\t\t\t\t<textarea class=\"form-control\" placeholder=\"$fieldtext[$j]\" ng-model=\"loaded_item.$fieldname[$j]\"></textarea>\r".
		            						 "\t\t\t</div>\r";
		        //
				break;
			case "date":
				//1
				/*HTML untuk menampilkan value sebuah field database di halaman 'list'*/
				$html_list_detail .= "\t\t\t\t\t\t$fieldtext[$j]: ".'{{item.'."$fieldname[$j]}}<br/>\r";
				
				//2
				//$js_insert_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".ins_$fieldname[$j],\r";
				//$js_update_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".upd_$fieldname[$j],\r";
				
				//TYPE SPECIFIC JS
				$js_forDateInit .=	"\t\t".'$scope.ins_'."$fieldname[$j] = new Date(year, month, day);\r";
				$js_forDateToday.=	"\t\t".'$scope.ins_'."$fieldname[$j] = new Date();\r";
				$js_forDateCode .=	"\t".'$scope.open_date_'."$fieldname[$j]".' = function($event) { $scope.date_'."$fieldname[$j]_isOpened = true };\r".
									"\t".'$scope.date_'."$fieldname[$j]_isOpened = false;\r";
				
				/*PAIRING SAAT LOAD VALUE ORIGINAL DI HALAMAN 'Edit'*/
				//$js_load_pairing .= "\t\t\t".'$scope.upd_'."$fieldname[$j] = new Date(item.$fieldname[$j]);\r";
				$js_load_pairing .= "\t\t\t".'$scope.loaded_item'."$fieldname[$j] = new Date(item.$fieldname[$j]);\r";

				//3 - HTML untuk pembuatan input component di halaman 'entry'
				$html_ins_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
		                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
		                					 "\t\t\t\t\t<div class=\"input-group\">\r".
											 "\t\t\t\t\t<input type=\"date\" class=\"form-control\" uib-datepicker-popup ng-model=\"new_item.$fieldname[$j]\" is-open=\"date_$fieldname[$j]_isOpened\" min-date=\"'2000-01-01'\" max-date=\"'2099-12-31'\" datepicker-options=\"{formatYear: 'yy',startingDay: 1}\" ng-required=\"true\" close-text=\"Tutup\" />\r".
											 "\t\t\t\t\t<span class=\"input-group-btn\">\r".
											 "\t\t\t\t\t<button type=\"button\" class=\"btn btn-default\" ng-click=\"open_date_$fieldname[$j](".'$event'.")\"><i class=\"fa fa-calendar\"></i></button>\r".
											 "\t\t\t\t\t</span>\r".
											 "\t\t\t\t\t</div>\r".
		            						 "\t\t\t</div>\r";
				
				//4 - HTML untuk pembuatan input component di halaman 'edit'
				$html_upd_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
		                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
		                					 "\t\t\t\t\t<div class=\"input-group\">\r".
											 "\t\t\t\t\t<input type=\"date\" class=\"form-control\" uib-datepicker-popup ng-model=\"loaded_item.$fieldname[$j]\" is-open=\"date_$fieldname[$j]_isOpened\" min-date=\"'2000-01-01'\" max-date=\"'2099-12-31'\" datepicker-options=\"{formatYear: 'yy',startingDay: 1}\" ng-required=\"true\" close-text=\"Tutup\" />\r".
											 "\t\t\t\t\t<span class=\"input-group-btn\">\r".
											 "\t\t\t\t\t<button type=\"button\" class=\"btn btn-default\" ng-click=\"open_date_$fieldname[$j](".'$event'.")\"><i class=\"fa fa-calendar\"></i></button>\r".
											 "\t\t\t\t\t</span>\r".
											 "\t\t\t\t\t</div>\r".
		            						 "\t\t\t</div>\r";
				
				//
				break;
			case "FK":
				//INJECTION
				$inject_module .= " ,'".$fk_reftable."Service'";
				$inject_class .= " ,'".ucfirst($fk_reftable)."Srv'";
				$inject_object .= " ,".ucfirst($fk_reftable)."Srv";

				//1
				/*HTML untuk menampilkan value sebuah field database di halaman 'list'*/
				$html_list_detail .= "\t\t\t\t\t\t$fieldtext[$j]: ".'{{item.'."$fk_refcolumn}}<br/>\r";
				
				//2
				//$js_insert_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope.selectItem_'."$fk_reftable.selected.id,\r";
				//$js_update_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope.selectItem_'."$fk_reftable.selected.id,\r";

				$js_insert_pairing .= '$scope.new_item.'.$fieldname[$j].' = $scope.selectItem_'."$fk_reftable.selected.id;\r";
				$js_update_pairing .= '$scope.loaded_item.'.$fieldname[$j].' = $scope.selectItem_'."$fk_reftable.selected.id;\r";
				
				//TYPE SPECIFIC JS
				$js_forComboInit .= "".
									"\t".'$scope.selectItem_'.$fk_reftable.'_Array = [];'."\r".
									"\t".'$scope.selectItem_'.$fk_reftable.' = {};'."\r".
									"\t".'$scope.loadFK_'.$fk_reftable.' = function() {'."\r".
									"\t\t".'var rst = '.ucfirst($fk_reftable).'Srv.retrieveAll();'.   //'$http.get(API_URL+"'.$fk_reftable.'").success(function(result) {'."\r".
									"\t\t".'rst.then(function(req) {'."\r".
									"\t\t\t".'var tmp = req.data;'."\r".
									"\t\t\t".'for(var i=0; i<tmp.length; i++) {'."\r".
									"\t\t\t\t".'var obj = {};'."\r".
									"\t\t\t\t".'obj["id"]   = tmp[i][\'id\'];'."\r".
									"\t\t\t\t".'obj["name"] = tmp[i][\''.$fk_refcolumn.'\'];'."\r".
									"\t\t\t\t".'$scope.selectItem_'.$fk_reftable.'_Array.push(obj);'."\r".
									"\t\t\t}\r".
									"\t\t});\r".
									"\t}\r";

				/*PAIRING SAAT LOAD VALUE ORIGINAL DI HALAMAN 'Edit'*/
				$js_load_pairing .= "\t\t\t".'$scope'.".selectItem_$fk_reftable.selected = {'id':item.$fk_reftable"."_id,'name':item.$fk_refcolumn};\r";

				//3 - HTML untuk pembuatan input component di halaman 'entry'
				$html_ins_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
											 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
											 "\t\t\t\t<ui-select ng-model=\"selectItem_$fk_reftable.selected\">\r".
											 "\t\t\t\t\t<ui-select-match>{{".'$select'.".selected.name}}</ui-select-match>\r".
											 "\t\t\t\t\t<ui-select-choices repeat=\"selectItem_$fk_reftable in (selectItem_".$fk_reftable."_Array | filter: ".'$select'.".search) track by selectItem_$fk_reftable.id\">\r".
											 "\t\t\t\t\t\t<span ng-bind=\"selectItem_$fk_reftable.name\"></span>\r".
											 "\t\t\t\t\t</ui-select-choices>\r".
											 "\t\t\t\t</ui-select>\r".
											 "\t\t\t</div>\r";

				//4 - HTML untuk pembuatan input component di halaman 'edit'
				$html_upd_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
											 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
											 "\t\t\t\t<ui-select ng-model=\"selectItem_$fk_reftable.selected\">\r".
											 "\t\t\t\t\t<ui-select-match>{{".'$select'.".selected.name}}</ui-select-match>\r".
											 "\t\t\t\t\t<ui-select-choices repeat=\"selectItem_$fk_reftable in (selectItem_".$fk_reftable."_Array | filter: ".'$select'.".search) track by selectItem_$fk_reftable.id\">\r".
											 "\t\t\t\t\t\t<span ng-bind=\"selectItem_$fk_reftable.name\"></span>\r".
											 "\t\t\t\t\t</ui-select-choices>\r".
											 "\t\t\t\t</ui-select>\r".
											 "\t\t\t</div>\r";
				
				$js_forLoadInit .= "\t\t".'$scope.loadFK_'.$fk_reftable."();\r";
				//
				break;
			default:
				//1
				/*HTML untuk menampilkan value sebuah field database di halaman 'list'*/
				$html_list_detail .= "\t\t\t\t\t\t$fieldtext[$j]: ".'{{item.'."$fieldname[$j]}}<br/>\r";
				
				//2
				//$js_insert_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".ins_$fieldname[$j],\r";
				//$js_update_pairing .= "\t\t\t\"$fieldname[$j]\" : ".'$scope'.".upd_$fieldname[$j],\r";
				
				/*PAIRING SAAT LOAD VALUE ORIGINAL DI HALAMAN 'Edit'*/
				//$js_load_pairing .= "\t\t\t".'$scope.upd_'."$fieldname[$j] = item.$fieldname[$j];\r";
				
				//3 - HTML untuk pembuatan input component di halaman 'entry'
				$html_ins_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
		                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
		                					 "\t\t\t\t<input class=\"form-control\" placeholder=\"$fieldtext[$j]\" ng-model=\"new_item.$fieldname[$j]\">\r".
		            						 "\t\t\t</div>\r";

				//4 - HTML untuk pembuatan input component di halaman 'edit'
				$html_upd_inputcomponents .= "\t\t\t<div class=\"form-group\">\r".
		                					 "\t\t\t\t<label>$fieldtext[$j]</label>\r".
		                					 "\t\t\t\t<input class=\"form-control\" placeholder=\"$fieldtext[$j]\" ng-model=\"loaded_item.$fieldname[$j]\">\r".
		            						 "\t\t\t</div>\r";
				//
				break;
		}
		
	}//End of LOOP OVER THE FIELDS INSIDE EACH OBJECT (TABLES/CLASSES)
	
	//1
	$js_controller_content  	= preg_replace('/PATinputpairingPAT/', $js_insert_pairing, $js_controller_content);
	$js_controller_content  	= preg_replace('/PATloadpairingPAT/', $js_load_pairing, $js_controller_content);
	$js_controller_content  	= preg_replace('/PATupdatepairingPAT/', $js_update_pairing, $js_controller_content);
	//
	$js_controller_content		= preg_replace('/PATjsforComboInitPAT/', $js_forComboInit, $js_controller_content);
	$js_controller_content		= preg_replace('/PATiniFKsPAT/', $js_forLoadInit, $js_controller_content);
	//
	$js_controller_content		= preg_replace('/PATjsforDateInitPAT/', $js_forDateInit, $js_controller_content);
	$js_controller_content		= preg_replace('/PATjsforDateTodayPAT/', $js_forDateToday, $js_controller_content);
	$js_controller_content		= preg_replace('/PATjsforDateCodePAT/', $js_forDateCode, $js_controller_content);
	//
	$js_controller_content		= preg_replace('/PATinjectmodulePAT/', $inject_module, $js_controller_content);
	$js_controller_content		= preg_replace('/PATinjectclassPAT/', $inject_class, $js_controller_content);
	$js_controller_content		= preg_replace('/PATinjectobjectPAT/', $inject_object, $js_controller_content);

	//2
	$html_list_content  	= preg_replace('/PATitemdetailPAT/', $html_list_detail, $html_list_content);
	//3
	$html_ins_content		= preg_replace('/PATinputcomponentsPAT/', $html_ins_inputcomponents, $html_ins_content);
	//4
	$html_upd_content		= preg_replace('/PATeditcomponentsPAT/', $html_upd_inputcomponents, $html_upd_content);
	
	// - - - - - -
	
	//1
	$fo->CreateFile($js_controller_content, $objectname.".js", $jscontrol_dir);
	$fo->CreateFile($js_service_content, $objectname.".js", $jsservice_dir);
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
	//
	unset($fields);
	unset($fieldcount);
	unset($fieldtext);
	unset($fieldname);
	unset($fieldtype);
	unset($fieldisFK);
	//
	unset($fk_reftable);
	unset($fk_refcolumn);
	//
	unset($nodes);
	unset($nodename);
	unset($findname);
	
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
	unset($js_service_file);
	unset($js_service_content);
	//4
	// = = = = = = = = = = = =
	unset($html_upd_inputcomponents);
	//
	unset($html_upd_file);
	unset($html_upd_content);
	
	//OCCASIONAL
	unset($js_forComboInit);
	unset($js_forLoadInit);
	//
	unset($inject_module);
	unset($inject_class);
	unset($inject_object);
	//
	unset($js_forDateInit);
	unset($js_forDateToday);
	unset($js_forDateCode);
	
}
// END OF : LOOP OVER ALL TABLES DEFINED IN YAML FILE. 

// = = = = = = = = = = = = = = = = =

$html_menu_content  = preg_replace('/PATMENULINKSPAT/', $html_menulinks, $html_menu_content);

$fo->CreateFile($html_menu_content, "menu.html", $htmlview_dir);
printf("<strong>Berkas HTML untuk menu navigasi selesai.</strong><br/>");

// = = = = = = = = = = = = = = = = =
//HOME.HTML
$html_home_file 	 = $fo->OpenFile('../template/','home.html.tmpl','r');
$html_home_content = $fo->FileContent($html_home_file);

$fo->CreateFile($html_home_content, "home.html", $htmlview_dir);
printf("<strong>Berkas HTML untuk halaman beranda selesai.</strong><br/>");
// = = = = = = = = = = = = = = = = =

//MAIN APP app.js CONTENT REPLACEMENT
$appclass_content  	= preg_replace('/PATCLASSINJECTIONPAT/', $html_classinjections, $appclass_content);
$appclass_content  	= preg_replace('/PATVIEWROUTESPAT/', $html_viewroutes, $appclass_content);

$fo->CreateFile($appclass_content, "app.js", $htmlmain_dir);
printf("<strong>Berkas JavaScript utama (app.js) selesai.</strong><br/>");

// = = = = = = = = = = = = = = = = =

$appindex_content  	= preg_replace('/PATCONTROLLERFILESPAT/', $html_controllerfiles, $appindex_content);
$appindex_content  	= preg_replace('/PATSERVICEFILESPAT/', $html_servicefiles, $appindex_content);

$fo->CreateFile($appindex_content, "index.html", $htmlmain_dir);
printf("<strong>Berkas HTML utama (index.html) selesai.</strong><br/>");



#############################
# END MESSAGE
#############################
printf($scaffold->completionMessageF);

?>