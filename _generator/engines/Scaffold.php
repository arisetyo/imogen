<?php

class Scaffold {
	
	var $initScaffoldMessage = "Memulai pembangkitan scaffold.<br />";//"Starting scaffold generator.<br />";
	var $initConfigMessage = "Mengakses berkas-berkas konfigurasi.<br />";
	var $dbConfigErrorMessage = "Berkas konfigurasi akses database tidak tersedia.";
	var $mainConfigErrorMessage = "Berkas konfigurasi aplikasi tidak tersedia.";
	var $mainConfigLoadedMessage = "Konfigurasi dimuat untuk aplikasi: ";
	var $appConfigErrorMessage = "Berkas konfigurasi YAML tidak tersedia.";
	var $configArrayLoadedMessage = "Array konfigurasi termuat.<br />";
	var $objectArrayInitMessage = "Membuat array obyek data.<br />";
	var $noTableInDbErrorMessage = "Tidak ditemukan tabel di konfigurasi!";
	var $openFileClassMessage = "Membuka kelas operasi berkas.<br />";
	var $classComCreateMessage = "Membuat kelas dan komponen.<br />";
	var $crudComAppendMessage = "Menambahkan komponen CRUD ke aplikasi utama.<br />";
	var $completionMessageB = "<strong>Pembangkitan scaffold BACK-END selesai.</strong><br />";
	var $completionMessageF = "<strong>Pembangkitan scaffold FRONT-END selesai.</strong><br />";
	
	function __construct(){

	}
	
}

?>