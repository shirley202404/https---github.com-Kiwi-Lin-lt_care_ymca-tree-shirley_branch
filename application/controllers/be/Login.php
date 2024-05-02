<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller { // 登入
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('social_worker_model');
    $this->load->model('delivery_person_model');
    $this->load->model('sys_account_model');
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 登入畫面
  //  程式設計: Tony
  //  設計日期: 2017/7/14
  // **************************************************************************
  public function index($vlogin_ng='N') { 
    $date = date("Y-m-d");
    $morning_login_time_start = "{$date} 08:00:00";
    $morning_login_time_end = "{$date} 13:00:00";
    $afternoon_login_time_start = "{$date} 14:30:00";
    $afternoon_login_time_end = "{$date} 17:30:00";
    $this->zi_init->captcha(); // 產生驗證碼
    $this->tpl->assign('tv_login_ng',$vlogin_ng);
    $this->tpl->assign('tv_action',base_url().'be/login/chk_login/');
    $this->tpl->assign('tv_cap_path',base_url().'pub/captcha/');
    $this->tpl->assign('tv_cap_img',$_SESSION['cap']['filename']);
    $this->tpl->assign('tv_morning_login_time_start',$morning_login_time_start);
    $this->tpl->assign('tv_morning_login_time_end',$morning_login_time_end);
    $this->tpl->assign('tv_afternoon_login_time_start',$afternoon_login_time_start);
    $this->tpl->assign('tv_afternoon_login_time_end',$afternoon_login_time_end);
    $this->tpl->assign('tv_captcha_link',base_url().'be/login/captcha/');
    $this->tpl->assign('tv_base_url',base_url());
    $this->tpl->display("be/login.html");
    return;
  }

  // **************************************************************************
  //  函數名稱: chk_login()
  //  函數功能: 登入檢查
  //  程式設計: Tony
  //  設計日期: 2017/11/14
  // **************************************************************************
  public function chk_login()  {
    $v = $this->input->post();
    if(!isset($v['acc_kind'])) {
      redirect(base_url().'be/login', 'refresh');
    }
    $acc_kind = $this->db->escape_like_str($v['acc_kind']); // 身份別
    switch ($acc_kind) {
      case "SW": // 社工
        if($this->social_worker_model->chk_login()) {
          redirect(base_url().'be/main/', 'refresh');
        }
        else {
          $this->index('Y'); // 回登入頁面
        }
        break;
      case "DP": // 外送員
        if($this->delivery_person_model->chk_login()) {
          redirect(base_url().'be/main/', 'refresh');
        }
        else {
          $this->index('Y'); // 回登入頁面
        }
        break;
      case "ELEC": // 電子看板          
      case "MAN": // 系統管理者
        if($this->sys_account_model->chk_login()) {
          redirect(base_url().'be/main/', 'refresh');
        }
        else {
          $this->index('Y'); // 回登入頁面
        }
        break;
    }
    return;
  }

  // **************************************************************************
  //  函數名稱: captcha()
  //  函數功能: 驗證碼
  //  程式設計: Tony
  //  設計日期: 2017/7/14
  // **************************************************************************
  public function captcha() {
    $this->zi_init->captcha(); // 產生驗證碼
    //var_dump($_SESSION['cap']);
    echo $_SESSION['cap']['filename'];
    return;
  }

  // **************************************************************************
  //  函數名稱: logout()
  //  函數功能: 登出，清除session
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
  public function logout()  { 
    session_destroy(); // 移除所有 session
    redirect(base_url().'be/login', 'refresh');
    return;
  }

  function __destruct() {
    $url_str[] = 'be/login/captcha';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
