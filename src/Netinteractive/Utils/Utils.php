<?php
namespace Netinteractive\Utils;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;

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

	/**
	 * @param $raw
	 * @param null $rootClass
	 * @return array|mixed|object
	 */
	static public function array2Model($raw, $rootClass=null){
		$raw=(array)$raw;
		$data=array();
		$attributes=array();
		if($rootClass){
			$rootObject=\App::make($rootClass);
		}
		else{
			$rootObject=\App::make('stdClass');
		}

		foreach($raw as $key=>$val){
			if($key==ucfirst($key)){
				$arrKey=explode('_',$key);
				$modelClass=array_shift($arrKey);
				if(!isset($data[$modelClass])){
					$data[$modelClass]=array();
				}
				$data[$modelClass][implode('_',$arrKey)]=$raw[$key];
			}
			else{
				$attributes[$key]=$val;
			}
		}


		if($rootClass){
			$rootObject->fill($attributes);
		}
		else{
			foreach($attributes as $k=>$v){
				$rootObject->$k=$v;
			}
		}
		foreach($data as $modelClass=>$attr){
			$Model=\App::make($modelClass);
			$Model->fill($attr);
			$rootObject->$modelClass=$Model;
		}

		return $rootObject;

	}

	/**
	 * @param $collection
	 * @param $modelClass
	 * @return array
	 */
	static function records2Models($collection, $modelClass=null){
		$result=[];
		foreach($collection as $record){
			$result[]=self::array2Model($record, $modelClass);
		}
		return $result;
	}



	/**
	 * @param $ControllerAction
	 * @param $view
	 * @param array $params
	 * @return \Illuminate\View\View
	 */
	public static function runPlugin($ControllerAction, $view=null, $params=array()){
		$ControllerAction=explode('::',$ControllerAction);
		$Controller=\App::make($ControllerAction[0]);
		if($view){
			$result=\View::make($view,$Controller->$ControllerAction[1]($params));
		}
		else{
			$result=$Controller->$ControllerAction[1]($params);
		}

		return $result;
	}

	public static function mergeArr($arr1, $arr2){
		foreach($arr2 as $k=>$v){
			if(is_array($v) && isset($arr1[$k]) && is_array($arr1[$k])){
				$arr1[$k]=static::mergeArr($arr1[$k], $arr2[$k]);
			}
			else{
				$arr1[$k]=$arr2[$k];
			}
		}
		return $arr1;
	}


	public static function modelToInputs(\Cartalyst\Sentry\Elegant $Model, array $inputs=array(), array $values=array()){
		$fields=$Model->getFields();

		foreach($inputs as $key=>&$input){
			$field=array_get($fields,$key);

			if(!array_get($input,'attr')){
				$input['attr']=array();
			}

			if(array_get($values,$key)){
				$input['value']=$values[$key];
			}

			if(!isset($input['attr']['title'])){
				$input['attr']['title']=$field['title'];
			}

			if(!isset($input['name'])){
				$input['name']=$key;
			}

			if(!isset($input['type'])){
				switch($field['type']){
					case 'text':
					case 'html':
						$input['type']='textarea';
					break;

					case 'email':
						$input['type']='email';
						break;

					case 'bool':
						$input['type']='checkbox';
					break;

					default:
						$input['type']='text';
					break;
				}
				if($key==$Model->getKeyName()){
					$input['type']='hidden';
				}
			}

			if(!isset($input['attr']['class'])){
				switch($field['type']){
					case 'date':
					case 'dateTime':
						$input['attr']['class']='plg-ni_ui_dateBox form-control ';
					break;


					case 'bool':
						$input['attr']['class']='plg-ni_ui_checkbox form-control';
					break;

					case 'html':
						$input['attr']['class']='plg-ni_ui_editor form-control';
					break;
					default:
						$input['attr']['class']='form-control';
					break;
				}
			}

			if (!array_get($input,'html')) {
				switch ($input['type']) {
					case 'checkbox':
						$input['html'] = \Form::checkbox($input['name'], null, array_get($input, 'value'), $input['attr']);
						break;

					case 'select':
						$input['html'] = \Form::select($input['name'], $input['list'], array_get($input, 'value'), $input['attr']);
						break;

					case 'textarea':
						$input['html'] = \Form::textarea($input['name'], array_get($input, 'value'), $input['attr']);
						break;

					default:
						$input['html'] = \Form::input($input['type'], $input['name'], array_get($input, 'value'), $input['attr']);
						break;
				}
			}


		}
		return $inputs;
	}

	public static function paramToArray($param, $delimiter=','){
		if(!is_array($param)){
			$param=explode($delimiter,$param);
			foreach($param as &$v){
				$v=trim($v);
			}
		}
		return $param;
	}
}