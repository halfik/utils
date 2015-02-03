<?php

namespace Netinteractive\Utils;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use MyProject\Proxies\__CG__\OtherProject\Proxies\__CG__\stdClass;


/**
 * Klasa do formatowania 
 * 
 * @category   Helpers
 * @package    Netinteractive/Utils
 * @author     Michal Smike Szapowalow michal.szapowalow@netinteractive.pl
 * @author     Kamil Pietrzak <kamil.pietrzak@netinteractive.pl>
 * @version    1.0
 * @link       https://bitbucket.org/niteam/laravel-utils
 */
class Utils
{


    /**
     * Łaczenie plików z wybranego katalogu
     * 
     * @param string $dir directory to folder z ktorego ma wybrac pliki
     * @param string/array $extension rozszerzenie plikow(moze byc arrayem)(
     * @param string $path lokalizacja pliku wyjsciowy
     * @param array $extra dodatkowe pliki z poza wskazanego katalogu
     * @return string tresc pliku
     */
    static function glueFiles($dir, $extension, $path = null, array $extra = array())
    {
        $text = '';
        if (!is_array($extension))
        {
            $extension = array($extension);
        }
        $files = array_merge($extra, self::scanDir($dir, $extension, true));
        $files = array_unique($files);
        foreach ($files as $file)
        {
            $text.=file_get_contents($file);
        }

        if ($path)
        {
            file_put_contents($path, $text);
        }
        return $text;
    }


    /**
     * Tworzy folder rekursywnie, jezeli jeszcze nie istnieje
     * 
     * @param $path sciezka folderu
     * @param int $mode uprawnienia
     * @throws Exception
     */
    static function makeDir($path, $mode = 0777)
    {
        if(!file_exists($path))
        {
            mkdir ($path, $mode, true);
        }
        elseif(!is_dir($path))
        {
            throw new Exception("File exists but isn't a directory(" . $path . ")!");
        }
    }


    /**
     * Skanowanie folderu z mozliwoscia podania filtrow, mozliwosc skanowania rekursywnego, mozliwosc wyboru sposobu sortowania
     * 
     * @param $path sciezka do foldeur
     * @param string $type f-only files, d-only directories. .gif only gif-files
     * @param bool $scanSubDirs czy ma skanowac rekursywie
     * @param null $order odwolanie do stalych z natywnego scandir()
     * @return array nazwy plikow
     */
    static function scanDir($path, $type = array('f', 'd'), $scanSubDirs = false, $order = null)
    {
        if (!is_array($type))
        {
            $type = array($type);
        }
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $dir = scandir($path, $order);


        if ($scanSubDirs && is_array($scanSubDirs))
        {
            $result = $scanSubDirs;
        }
        else
        {
            $result = array();
        }

        foreach ($dir as $item)
        {

            if ($item == '.' || $item == '..')
            {
                continue;
            }

            $add = false;

            if (is_dir($path . DIRECTORY_SEPARATOR . $item) && $scanSubDirs !== false)
            {
                $result = self::scanDir($path . DIRECTORY_SEPARATOR . $item, $type, $result, $order);
            }

            if (is_file($path . DIRECTORY_SEPARATOR . $item))
            {
                $extension = strval(pathinfo($item, PATHINFO_EXTENSION));
                if (in_array('.' . $extension, $type) || in_array('f', $type))
                {
                    $add = true;
                }
            }

            if (is_dir($path . DIRECTORY_SEPARATOR . $item) && in_array('d', $type))
            {
                $add = true;
            }

            if ($add)
            {
                if ($scanSubDirs === false)
                {
                    $result[] = $item;
                }
                else
                {
                    $result[] = $path . DIRECTORY_SEPARATOR . $item;
                }
            }
        }
        return $result;
    }


    /**
     * Wygodniejsza opcja robienia substirnga
     * 
     * @param type $string
     * @param type $shift
     * @return type
     */
    static function subString($string, $shift)
    {
        if ($shift > 0)
        {
            return mb_substr($string, $shift, mb_strlen($string));
        }
        else
        {
            return mb_substr($string, 0, mb_strlen($string) + $shift);
        }
    }


    /**
     * Robi print_r i dodaje tag HTML <pre>
     * 
     * @param type $v
     */
    static function printR($v)
    {
        ?><pre><?php print_r($v) ?></pre><?php
    }


    /**
     * Robi var_dump i dodaje tag HTML <pre>
     * 
     * @param type $v
     */
    static function varDump($v)
    {
        ?><pre><?php var_dump($v) ?></pre><?php
    }


    /**
     * Robi model z tablicy/std object
     * 
     * @param $raw
     * @param null $rootClass
     * @return array|mixed|object
     */
    static public function array2Model($raw, $rootClass = null)
    {
        $raw = (array) $raw;
        $data = array();
        $attributes = array();
        if ($rootClass)
        {
            $rootObject = \App::make($rootClass);
        }
        else
        {
            $rootObject = \App::make('stdClass');
        }

        foreach ($raw as $key => $val)
        {
            if ($key == ucfirst($key))
            {
                $arrKey = explode('_', $key);
                $modelClass = array_shift($arrKey);
                if (!isset($data[$modelClass]))
                {
                    $data[$modelClass] = array();
                }
                $data[$modelClass][implode('_', $arrKey)] = $raw[$key];
            }
            else
            {
                $attributes[$key] = $val;
            }
        }


        if ($rootClass)
        {
            $rootObject->fill($attributes);
        }
        else
        {
            foreach ($attributes as $k => $v)
            {
                $rootObject->$k = $v;
            }
        }
        foreach ($data as $modelClass => $attr)
        {
            $Model = \App::make($modelClass);
            $Model->fill($attr);
            $rootObject->$modelClass = $Model;
        }

        return $rootObject;
    }


    /**
     * Robi model z tablicy/std object z mozliwoscia podania kolekcji rekordow
     * 
     * @param $collection
     * @param $modelClass
     * @return array
     */
    static function records2Models($collection, $modelClass = null)
    {
        $result = [];
        foreach ($collection as $record)
        {
            $result[] = self::array2Model($record, $modelClass);
        }
        return $result;
    }


    /**
     * Uruchamia wskazany kontroler, laczy go z widokiem i przekazuje mu parametry
     * 
     * @param $ControllerAction "Controller::akcja"
     * @param $view wskazany widok
     * @param array $params parametry do kontrolera
     * @return \Illuminate\View\View
     */
    public static function runPlugin($ControllerAction, $view = null, $params = array())
    {
        $ControllerAction = explode('::', $ControllerAction);
        $Controller = \App::make($ControllerAction[0]);
        if ($view)
        {
            $result = \View::make($view, $Controller->$ControllerAction[1]($params));
        }
        else
        {
            $result = $Controller->$ControllerAction[1]($params);
        }

        return $result;
    }


    /**
     * Laczy tablice rekursywnie
     * Jezeli tablice maja zaglebienia to rowniez to laczy
     * 
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    public static function mergeArr($arr1, $arr2)
    {
        foreach ($arr2 as $k => $v)
        {
            if (is_array($v) && isset($arr1[$k]) && is_array($arr1[$k]))
            {
                $arr1[$k] = static::mergeArr($arr1[$k], $arr2[$k]);
            }
            else
            {
                $arr1[$k] = $arr2[$k];
            }
        }
        return $arr1;
    }


    /**
     * Tworzy inputy z modelu - potrzebne do budowania formularzy, wykorzystywane w CRUDZIE
     * 
     * @param \Cartalyst\Sentry\Elegant $Model
     * @param array $inputs
     * @param array $values
     * @return array
     */
    public static function modelToInputs(\Netinteractive\Elegant\Elegant $Model, array $inputs = array(), array $values = array())
    {
        $fields = $Model->getFields();

        foreach ($inputs as $key => &$input)
        {
            $field = array_get($fields, $key);

            if (!array_get($input, 'attr'))
            {
                $input['attr'] = array();
            }

            if (isset($values[$key]) && !isset($input['value']))
            {
                $input['value'] = $values[$key];
            }

            if (!isset($input['attr']['title']))
            {
                $input['attr']['title'] = $field['title'];
            }

            if (!isset($input['name']))
            {
                $input['name'] = $key;
            }

            if (!isset($input['type']))
            {
                switch ($field['type'])
                {
                    case 'text':
                    case 'html':
                        $input['type'] = 'textarea';
                        break;

                    case 'email':
                        $input['type'] = 'email';
                        break;

                    case 'bool':
                        $input['type'] = 'checkbox';
                        break;

                    default:
                        $input['type'] = 'text';
                        break;
                }
                if ($key == $Model->getKeyName())
                {
                    $input['type'] = 'hidden';
                }
            }

            if (!isset($input['attr']['class']))
            {
                switch ($field['type'])
                {
                    case 'date':
                    case 'dateTime':
                        $input['attr']['class'] = 'plg-ni_ui_dateBox form-control ';
                        break;


                    case 'bool':
                        $input['attr']['class'] = 'plg-ni_ui_checkbox form-control';
                        break;

                    case 'html':
                        $input['attr']['class'] = 'plg-ni_ui_editor form-control';
                        break;
                    default:
                        $input['attr']['class'] = 'form-control';
                        break;
                }
            }

            if (!array_get($input, 'html'))
            {
                switch ($input['type'])
                {
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


    /**
     * Jakies formatowanie stringa do tablicy(trzeba sprawdzic jak to w ogole dziala)
     * 
     * @param type $param
     * @param type $delimiter
     * @return type
     */
    public static function paramToArray($param, $delimiter = ',')
    {
        if (!is_array($param))
        {
            $param = explode($delimiter, $param);
            foreach ($param as &$v)
            {
                $v = trim($v);
            }
        }
        return $param;
    }


    /**
     * Formatuje czas na HH:MM::SS z ilosci sekund
     * 
     * @param int $seconds
     * @return string "HH:MM::SS"
     */
    public static function seconds2hours($seconds)
    {
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        return str_pad($hours, 2, 0, STR_PAD_LEFT)
                . ':'
                . str_pad($minutes, 2, 0, STR_PAD_LEFT)
                . ':'
                . str_pad($seconds, 2, 0, STR_PAD_LEFT);
    }


    /**
     * Zwraca "zajawke" z dluzszego tekstu o wskazanej dlugosci
     * 
     * @param string $text
     * @param int $desired_length
     * @return string
     */
    public static function excerptString($text, $desired_length)
    {
        $text_length = strlen($text);

        if ($text_length > $desired_length)
        {
            // skrocenie
            $text = substr($text, 0, $desired_length);

            // wyrownanie do ostatniego slowa(tak aby nie bylo slowa ucietego w pol)
            $text = substr($text, 0, strrpos($text, ' '));

            // dodanie "..." na koncu
            $text .= '...';
        }

        return $text;
    }


}
