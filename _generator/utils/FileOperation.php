<?php
/** Class Definition
 * @object File Operations
 * @version 0.1 C for FLEX use
 * @author Ari Setyo 100809
 */

class FileOperation {
	
	function __construct(){
		//
	}
	
	/*
	* Membuka file
	* return File Handler
	*/
	function OpenFile($location,$filename,$mode=NULL){
		switch($mode){
			case "read":
				$fopenmode = "r";
				break;
			case "readandwrite":
				$fopenmode = "r+";
				break;
			case "write":
				$fopenmode = "w";
				break;
			case "writeandread":
				$fopenmode = "w+";
				break;
			case "append":
				$fopenmode = "a";
				break;
			case "readandappend":
				$fopenmode = "a+";
				break;
			default:
				$fopenmode = "r";
				break;
		}
		
		$tmp = fopen($location.$filename,$fopenmode);
		return $tmp;
	}
	
	/*
	* Mengolah File Handler
	* membaca isi file
	*/
	function FileContent($fileHandler){
		$tmp="";
		while(!feof($fileHandler)){
			$buffer = fgets($fileHandler,4096);
			$tmp .= $buffer;
		}
		return $tmp;
		fclose($fileHandler);
	}
	
	/*
	* Mengolah File Handler
	* menulis pada file (APPEND)
	*/
	function AddToFile($fileHandler,$content){
		if(fputs($fileHandler,$content))
			return true;
		else
			return false;
		fclose($fileHandler);
	}
	
	/*
	* Mengolah File Handler
	* membuat file baru
	*/
	function CreateFile($content,$filename,$directory){
		if(!is_dir($directory)) mkdir($directory);
		$targetdir = $directory;
	
		$fh = fopen($targetdir.$filename,'w');
		fputs($fh,$content);
		fclose($fh);
		return true;
	}
	
	/*
	* Mengolah File Handler
	* membuat file baru (HACK agar tidak bentrok dengan generator)
	* NOTE : urutan parameter dibalik
	*/
	function CreateXMLFile($content,$location,$filename){
		if(!is_dir($location)) mkdir($location);
	
		$fh = fopen($location.$filename,'w');
		fputs($fh,$content);
		fclose($fh);
		return true;
	}
	
	/*
	function closeFile(){}
	
	function deleteFile(){}
	
	function uploadFile(){}
	
	function moveFile(){}
	*/

}
?>