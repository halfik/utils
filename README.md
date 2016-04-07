Netinteractive\Utils
====================

Narzedzia wspomagajace.


## Changelog

* 2.0.5:
    * change: removed from Utils::runAction:
    
        $controller='\\App\\Http\\Controllers\\'.$controller;

* 2.0.4:
    * deleted: view and layout params from Netinteractive\Utils::runAction

* 2.0.3 : 
    * deleted: removed view create from Netinteractive\Utils::runAction


```
#!php
<?php

// Skracanie tresci artykulu do x znaków
App('Utils')->excerptString($article->content, 180);

// Formatowanie czasu - np. z "24242" do "06:44:02"
App('Utils')->seconds2hours(24242);

```