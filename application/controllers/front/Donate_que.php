<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Donate_que extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('front'); // 網頁 head
    $this->load->model('donate_model'); // 捐款資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','捐款資料');
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_return_link',front_url()."donate_que/"); // return 預設到瀏覽畫面
    $this->tpl->assign("tv_pub_url" , pub_url('front'));
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    return;
  }
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function index($pg=1,$q_str=NULL) {
    $msel = 'list';
    $this->load->library('pagination');
    if(NULL==$q_str) { // 沒有帶入參數，就開一個新的session
      $this->load->helper('string');
      $q_str = random_string('alnum', 10); // 取得亂數10碼
    }
    if(!isset($_SESSION[$q_str]['que_str'])) {
      $this->_que_start($q_str);
    }
    //u_var_dump($_SESSION[$q_str]);
    $get_data = $this->input->get(); // GET 用
    if(!isset($get_data['start'])) {
      $get_data['start'] = NULL;
    }
    if(!isset($get_data['end'])) {
      $get_data['end'] = NULL;
    }
    if(!isset($get_data['que_addr'])) {
      $get_data['que_addr'] = NULL;
    }
    if(!isset($get_data['que_de03_phone'])) {
      $get_data['que_de03_phone'] = NULL;
    }
    if(!isset($get_data['que_de10'])) {
      $get_data['que_de10'] = NULL;
    }
    list($donate_row,$row_cnt) = $this->donate_model->get_que_front($q_str,$pg); // 列出捐款資料
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_que_link',front_url()."donate_que/que/{$q_str}");
    // $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_start',$get_data['start']);
    $this->tpl->assign('tv_end',$get_data['end']);
    $this->tpl->assign('tv_que_de10',$get_data['que_de10']);
    $this->tpl->assign('tv_que_addr',$get_data['que_addr']);
    $this->tpl->assign('tv_que_de03_phone',$get_data['que_de03_phone']);
    $this->tpl->assign('tv_donate_row',$donate_row);
    $config['base_url'] = front_url()."donate_que/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = front_url()."/donate_que/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 3;
    $config['per_page'] = 20; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("front/donate_que.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function que($q_str)	{
    ////$data = $this->input->post(); // POST 用 Mark by Tony 2020/7/27
    ////u_var_dump($data);
    ////exit;
    ////if('que'==$data['que_kind']) {
    ////  $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    ////}
    ////$_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    ////$_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    ////redirect(front_url()."donate_que/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
     redirect(front_url()."donate_que/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  private function _que_start($q_str)	{
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }

  function __destruct() {
  }
}
?>