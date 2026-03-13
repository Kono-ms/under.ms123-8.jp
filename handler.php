<?php

function error_handler($errno, $errstr, $errfile, $errline) {

  $type = array(
    '1' => 'E_ERROR'
   ,'2' => 'E_WARNING'
   ,'4' => 'E_PARSE'
   ,'8' => 'E_NOTICE'
   ,'16' => 'E_CORE_ERROR'
   ,'32' => 'E_CORE_WARNING'
   ,'64' => 'E_COMPILE_ERROR'
   ,'128' => 'E_COMPILE_WARNING'
   ,'256' => 'E_USER_ERROR'
   ,'512' => 'E_USER_WARNING'
   ,'1024' => 'E_USER_NOTICE'
   ,'2048' => 'E_STRICT'
   ,'4096' => 'E_RECOVERABLE_ERROR'
   ,'8192' => 'E_DEPRECATED'
   ,'16384' => 'E_USER_DEPRECATED'
  );

}
function shutdown_handler() {

  $flg = false;
  if($e = error_get_last()){
    switch($e['type']){
      case E_ERROR:
      case E_PARSE:
      case E_CORE_ERROR:
      case E_CORE_WARNING:
      case E_COMPILE_ERROR:
      case E_COMPILE_WARNING:
        $isError = true;
        break;
    }
  }
  if($flg){
    error_handler($e['type'], $e['message'], $e['file'], $e['line'], null);
  }
}
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_error_handler('error_handler', E_ALL);
register_shutdown_function('shutdown_handler');
