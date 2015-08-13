<?php
Route::get('runAction',function(){
    return Utils::runAction(Input::get('_c'),\Input::get('_v',null),Input::all(), Input::get('_l',null));
});