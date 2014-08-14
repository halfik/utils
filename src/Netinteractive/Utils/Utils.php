<?php
namespace Netinteractive\Utils;
/**
 * Created by PhpStorm.
 * User: smike
 * Date: 8/14/14
 * Time: 11:18 AM
 */

class Utils{
	/**
	 * @param $dir
	 * @param $extension
	 * @param null $path
	 * @param array $files
	 * @return string
	 */
	static function glueFiles($dir, $extension, $path=null, array $files=array()){
		$text='';
		$files=array_merge($files,self::scanDir($dir,$extension,true));
		$files=array_unique($files);
		foreach($files as $file){
			$text.=file_get_contents($file);
		}

		if($path){
			file_put_contents($path,$text);
		}
		return $text;
	}

	/**
	 * @param $path
	 * @param int $perms
	 * @throws Exception
	 */
	static function makeDir($path, $perms=0777){
		$path=str_replace('/',DIRECTORY_SEPARATOR,$path);
		$arr=explode(DIRECTORY_SEPARATOR, $path);
		$makePath=DIRECTORY_SEPARATOR;
		foreach ($arr as $dir){
			if($dir){
				$makePath.=DIRECTORY_SEPARATOR.$dir;
				if(!is_dir($makePath)){
					if(!mkdir($makePath,$perms)){
						throw new Exception("Can't make directory ".$makePath."!");
					}
				}
			}
		}
	}

	/**
	 * @param $path
	 * @param null $filter f-only files, d-only directories. .gif only gif-files
	 * @param bool $scanSubDirs
	 * @param null $order
	 * @return array
	 */
	static function scanDir($path, $filter = null, $scanSubDirs=false, $order = null){
		$path=str_replace('/',DIRECTORY_SEPARATOR,$path);
		$dir = scandir($path, $order);
		if($scanSubDirs && is_array($scanSubDirs)){
			$result=$scanSubDirs;
		}
		else{
			$result=array();
		}
		foreach ($dir as $item) {

			if ($item == '.' || $item == '..') {
				continue;
			}

			if(is_dir($path.$item)  && $scanSubDirs!==false){
				$result=self::scanDir($path.$item.'/',$filter,$result,$order);
			}

			if ($filter == 'f' && !is_file($path.$item)) {
				continue;
			}
			elseif ($filter == 'd' && !is_dir($path.$item)) {
				continue;
			}
			elseif(strpos($filter,'.')===0 && strval(pathinfo($item, PATHINFO_EXTENSION))!=strval(self::subString($filter,1))){
				continue;
			}

			if($scanSubDirs===false){
				$result[] = $item;
			}
			else{
				$result[]=$path.$item;
			}
		}
		return $result;
	}

	static function subString($string, $shift)
	{
		if($shift>0){
			return mb_substr($string,$shift,mb_strlen($string));
		}
		else{
			return mb_substr($string,0,mb_strlen($string)+$shift);
		}
	}

	static function printR($v){
		?><pre><?php print_r($v)?></pre><?php
	}

	static function varDump($v){
		?><pre><?php var_dump($v)?></pre><?php
	}
}