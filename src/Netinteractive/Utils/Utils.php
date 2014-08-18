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
	 * @param $dir directory to
	 * @param $extension
	 * @param null $path
	 * @param array $extra array of additional files
	 * @return string
	 */

	static function glueFiles($dir, $extension, $path=null, array $extra=array()){
		$text='';
		if(!is_array($extension)){
			$extension=array($extension);
		}
		$files=array_merge($extra,self::scanDir($dir,$extension,true));
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

	static function scanDir($path, $type = array('f','d'), $scanSubDirs=false, $order = null){
		if(!is_array($type)){
			$type=array($type);
		}
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

			$add=false;

			if(is_dir($path.DIRECTORY_SEPARATOR.$item)  && $scanSubDirs!==false){
				$result=self::scanDir($path.DIRECTORY_SEPARATOR.$item,$type,$result,$order);
			}

			if(is_file($path.DIRECTORY_SEPARATOR.$item)){
				$extension=strval(pathinfo($item, PATHINFO_EXTENSION));
				if(in_array('.'.$extension,$type) || in_array('f',$type)){
					$add=true;
				}
			}

			if(is_dir($path.DIRECTORY_SEPARATOR.$item) && in_array('d',$type)){
				$add=true;
			}

			if($add){
				if($scanSubDirs===false){
					$result[] = $item;
				}
				else{
					$result[]=$path.DIRECTORY_SEPARATOR.$item;
				}
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