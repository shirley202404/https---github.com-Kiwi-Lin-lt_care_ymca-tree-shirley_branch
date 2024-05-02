<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Testunit extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
	  $mrand_str = $this->config->item('rand_str_8');
	  $this->tpl->assign('tv_rand_str',$mrand_str);
	  $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
	  $this->tpl->assign('tv_menu_title','unit Test');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->load->library('unit_test');
    //$this->unit->active(false);
	  return;
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2019/9/12
  // **************************************************************************
	public function index()	{
    $test_name = '測試 cal($1/$2)=1';
    $test = $this->cal(3,3);
    $expected_result = 1;
    u_var_dump($this->unit->run($test, $expected_result,$test_name));
    
    $test = $this->cal(3,3);
    $expected_result = 2;
    u_var_dump($this->unit->run($test, $expected_result,$test_name));
	  return;
	}
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 回傳計算值
  //  程式設計: Tony
  //  設計日期: 2019/9/12
  // **************************************************************************
	private function cal($a,$b)	{
	  return $a/$b;
	}
	
}