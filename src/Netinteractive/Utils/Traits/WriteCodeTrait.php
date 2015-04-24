<?php
/**
 * Created by PhpStorm.
 * User: emil
 * Date: 24.04.15
 * Time: 13:47
 */
namespace Netinteractive\Utils;

/*
Trait może być używany jedynie w klasach, które extendują \Illuminate\Console\Command
*/
trait WriteCodeTrait {

    protected function writeCode($path, $code){
        if(!is_file($path) || (is_file($path) && $this->ask($path.' already exists! Replace? (y/n)')=='y')){
            $info=pathinfo($path);
            if(!\File::isDirectory($info['dirname'])){
                \File::makeDirectory($info['dirname'],0755,true);
            }
            file_put_contents($path, $code);
            $this->info($path.' generated!');
        }
    }

}