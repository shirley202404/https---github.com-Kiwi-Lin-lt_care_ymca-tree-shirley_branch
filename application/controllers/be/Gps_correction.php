<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Gps_correction extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_download_btn',$this->lang->line('download')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."gps_correction/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
    $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"弗傳慈心基金會");
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}  
  }
  
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 輸入畫面
  //  程式設計: Kiwi
  //  設計日期: 2021/10/15
  // **************************************************************************
  public function index() {
    $msel = 'list';

    $file = ITM_FILE_PATH.'gps_correction.txt';
    $open_flag = file_get_contents($file);

    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 下載
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','Gps校正');
    $this->tpl->assign('tv_open_flag',$open_flag);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_change_link',be_url()."gps_correction/change"); // 更改檔案狀態
    $this->tpl->display("be/gps_correction.html");
    return;
  }

  // **************************************************************************
  //  函數名稱: change()
  //  函數功能: 更改開關狀態
  //  程式設計: Kiwi
  //  設計日期: 2022/09/11
  // **************************************************************************
	public function change() {
    $file = ITM_FILE_PATH.'gps_correction.txt';
    $openfile_w = fopen($file,'w');
    fseek($openfile_w, 0);                  
    fwrite($openfile_w, $_POST['f_is_available']);    
    fclose($openfile_w);      
    echo "ok";
    return;
  }

  function __destruct() {
    $url_str[] = 'be/gps_correction/change';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // page foot
    }
  }
}
