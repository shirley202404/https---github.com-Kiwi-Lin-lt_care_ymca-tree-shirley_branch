<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH."libraries/smarty/libs/Smarty.class.php");
 
class Tpl extends Smarty {
  function __construct(){
    parent::__construct();
    //echo "Tpl_parent::__construct()<br>";
    $this->left_delimiter = "{{";
    $this->right_delimiter = "}}";
    $this->compile_dir = APPPATH . 'cache/templates_c';
    $this->template_dir = APPPATH . 'views';
    $this->cache_dir = APPPATH . 'cache/scache';
    //$this->caching = true;
    //$this->caching = false;
    //$this->cache_lifetime = 60;  // 1分鐘
    $this->debugging = false;
    //$this->debugging = true; // 開窗顯示
    $this->compile_check = true; 
    //$this->compile_locking = false;
    //$this->compile_check = false; 
    if('production'==ENVIRONMENT) { // 正式機
      $this->force_compile = false; // false=不需要每次編譯
    }
    else {
      $this->force_compile = true; // true=每次都重新編譯
    }
    //$this->allow_php_templates= true;
  }
}
?>