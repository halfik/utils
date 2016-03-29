<?php namespace Netinteractive\Utils;

/**
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
     * @param string /array $extension rozszerzenie plikow(moze byc arrayem)(
     * @param string $path lokalizacja pliku wyjsciowy
     * @param array $extra dodatkowe pliki z poza wskazanego katalogu
     * @return string tresc pliku
     */
    static function glueFiles($dir, $extension, $path = null, array $extra = array())
    {
        $text = '';
        if (!is_array($extension)) {
            $extension = array($extension);
        }
        $files = array_merge($extra, self::scanDir($dir, $extension, true));
        $files = array_unique($files);
        foreach ($files as $file) {
            $text .= file_get_contents($file);
        }

        if ($path) {
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
        if (!file_exists($path)) {
            mkdir($path, $mode, true);
        } elseif (!is_dir($path)) {
            throw new Exception("File exists but isn't a directory(" . $path . ")!");
        }
    }


    /**
     * Skanowanie folderu z mozliwoscia podania filtrow, mozliwosc skanowania rekursywnego, mozliwosc wyboru sposobu sortowania
     *
     * @param string $path sciezka do foldeur
     * @param string $type f-only files, d-only directories. .gif only gif-files
     * @param bool $scanSubDirs czy ma skanowac rekursywie
     * @param null $order odwolanie do stalych z natywnego scandir()
     * @return array nazwy plikow
     */
    static function scanDir($path, $type = array('f', 'd'), $scanSubDirs = false, $order = null)
    {
        if (!is_array($type)) {
            $type = array($type);
        }
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        $dir = scandir($path, $order);


        if ($scanSubDirs && is_array($scanSubDirs)) {
            $result = $scanSubDirs;
        } else {
            $result = array();
        }

        foreach ($dir as $item) {

            if ($item == '.' || $item == '..') {
                continue;
            }

            $add = false;

            if (is_dir($path . DIRECTORY_SEPARATOR . $item) && $scanSubDirs !== false) {
                $result = self::scanDir($path . DIRECTORY_SEPARATOR . $item, $type, $result, $order);
            }

            if (is_file($path . DIRECTORY_SEPARATOR . $item)) {
                $extension = strval(pathinfo($item, PATHINFO_EXTENSION));
                if (in_array('.' . $extension, $type) || in_array('f', $type)) {
                    $add = true;
                }
            }

            if (is_dir($path . DIRECTORY_SEPARATOR . $item) && in_array('d', $type)) {
                $add = true;
            }

            if ($add) {
                if ($scanSubDirs === false) {
                    $result[] = $item;
                } else {
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
        if ($shift > 0) {
            return mb_substr($string, $shift, mb_strlen($string));
        } else {
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
        ?>
        <pre><?php print_r($v) ?></pre><?php
    }


    /**
     * Robi var_dump i dodaje tag HTML <pre>
     *
     * @param type $v
     */
    static function varDump($v)
    {
        ?>
        <pre><?php var_dump($v) ?></pre><?php
    }


    /**
     * Uruchamia wskazany kontroler, laczy go z widokiem i przekazuje mu parametry
     *
     * @param $controllerAction "Controller::akcja"
     * @param array $params parametry do kontrolera
     * @return mixed
     */
    public static function runAction($controllerAction=null, $params = array())
    {

        $controller=null;
        $action=null;
        $controllerAction = str_replace('@', '::', $controllerAction);

        //Jezeli jest wskazany kontroller i ackcja
        if($controllerAction){
            #Tworzymy objekt controllera
            $arr=explode('::', $controllerAction);
            $controller=array_get($arr,0);
            $action=array_get($arr,1);
            $controller=str_replace('.','\\',$controller);

            $controller='\\App\\Http\\Controllers\\'.$controller;
            #Jak niema takiego kontrollera
            if (!class_exists($controller)) {
                return null;
            }
            $controller = \App::make($controller);
        }

        $result=array();
        if($controller){
            $result=$controller->$action($params);
        }

        return $result;
    }

    /**
     * Twory routa
     *
     * @param $controllerAction
     * @param null $defaultView
     * @param null $alias
     * @return array
     */
    public static function makeRoute($controllerAction, $defaultView=null, $alias=null)
    {

        if(!$alias){
            $alias=$controllerAction;
        }

        return array(
            'as'=>$alias,
            'uses'=>function()use($controllerAction, $defaultView){
                return static::runAction($controllerAction,array_get(\Input::all(),'view',$defaultView),\Input::all());
            }
        );

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
     * @param int $desiredLength
     * @return string
     */
    public static function excerptString($text, $desiredLength)
    {
        $text_length = strlen($text);

        if ($text_length > $desiredLength) {
            // skrocenie
            $text = substr($text, 0, $desiredLength);

            // wyrownanie do ostatniego slowa(tak aby nie bylo slowa ucietego w pol)
            $text = substr($text, 0, strrpos($text, ' '));

            // dodanie "..." na koncu
            $text .= '...';
        }

        return $text;
    }


}
