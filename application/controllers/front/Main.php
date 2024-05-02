<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('front'); // 網頁 head
    $this->load->model('donate_model'); // 捐款資料
    $this->load->model('donate_progress_model'); // 捐款進度資料
    $this->load->model('front_content_model'); // 前台內容資料
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class()); // controllers name
    $this->tpl->assign('tv_menu_title','Main');
    $this->tpl->assign('tv_source','Main');
    $this->tpl->assign("tv_fc01_40_1", $this->front_content_model->get_by_obj_fc01(40, 1)); // 衛服部字號
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2018/4/25
  // **************************************************************************
  public function index()  {
    $donate_progress_row = $this->donate_progress_model->get_one(1);
    $donate_total = $this->donate_model->get_donate_total();
    $donate_total = (int)$donate_total->total;
    
    $progess = 0;
    if($donate_total < (int)$donate_progress_row->dp01){
      $progess = round(($donate_total/(int)$donate_progress_row->dp01)*100);
    }
    else if((int)$donate_progress_row->dp01 <= $donate_total and $donate_total < (int)$donate_progress_row->dp02) {
      $progess = round(($donate_total/(int)$donate_progress_row->dp02)*100);
    }
    else{
      $progess = round(($donate_total/(int)$donate_progress_row->dp03)*100);
    }

    $fc01[30]["fc01_item"][1] = "捐款標題";
    $fc01[30]["fc01_item"][2] = "階段一文字";
    $fc01[30]["fc01_item"][3] = "階段二文字";
    $fc01[30]["fc01_item"][4] = "階段三文字";
    $fc01[35]["fc01_item"][1] = "常見問題";
    $fc01[40]["fc01_item"][1] = "衛服部字號";

    // 捐款區塊(30)
    foreach($fc01[30]["fc01_item"] as $k => $v) {
      $val_str = "fc01_30_{$k}";
      ${$val_str} = $this->front_content_model->get_by_obj_fc01(30, $k);
      $this->tpl->assign("tv_{$val_str}", ${$val_str});
    }

    // 常見問題(35)
    foreach($fc01[35]["fc01_item"] as $k => $v) {
      $val_str = "fc01_35_{$k}";
      ${$val_str} = $this->front_content_model->get_by_arr_fc01(35, $k);
      $this->tpl->assign("tv_{$val_str}", ${$val_str});
    }

    // 頁尾(40)
    // foreach($fc01[40]["fc01_item"] as $k => $v) {
    //   $val_str = "fc01_40_{$k}";
    //   ${$val_str} = $this->front_content_model->get_by_obj_fc01(40, $k);
    //   $this->tpl->assign("tv_{$val_str}", ${$val_str});
    // }

    $this->tpl->assign("tv_pub_url", pub_url('front'));
    $this->tpl->assign("tv_main_link", front_url().'main/');
    $this->tpl->assign("tv_donate_link", front_url().'donate/');
    $this->tpl->assign("tv_progress", $progess);
    $this->tpl->assign("tv_donate_total", $donate_total);
    $this->tpl->assign("tv_progress_total", round($donate_total/(int)$donate_progress_row->dp03 * 100));
    $this->tpl->assign("tv_donate_progress_row", $donate_progress_row);
    // $this->tpl->assign("tv_fc01_1", $fc01_1); // 捐款階段文字
    // $this->tpl->assign("tv_fc01_2", $fc01_2); // 常見問題
    $this->tpl->display("front/index.html");
    return;
  }

  function __destruct() {
    $this->zi_init->footer('front'); // 網頁 foot
  }
}
