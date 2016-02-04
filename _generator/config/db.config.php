<?php
Define('DATABASE_SERVER', 'localhost');//most of the time, you don't have to change this
Define('DATABASE_USERNAME', 'root');//use your own setting, e.g. "root"
Define('DATABASE_PASSWORD', 'root');//use your own setting, e.g. "password"
Define('DATABASE_NAME', 'db_imogen_test');//use your own setting, e.g. "my_awesome_database"

$mysqlcnx = @mysql_connect(DATABASE_SERVER, DATABASE_USERNAME, DATABASE_PASSWORD) or die("<span class='label label-warning'><i class='fa fa-times'></i> Tidak dapat konek ke server database.</span>");
$mysqldb  = @mysql_select_db(DATABASE_NAME) or die("<span class='label label-warning'><i class='fa fa-times'></i> Tidak dapat membuka database.</span>");
?>