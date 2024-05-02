<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Delivery_person extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('delivery_person_model'); // 外送員基本資料檔
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','外送員基本資料檔');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_list_btn',$this->lang->line('list')); // 瀏覽按鈕文字
    $this->tpl->assign('tv_disp_btn',$this->lang->line('disp')); // 明細按鈕文字
    $this->tpl->assign('tv_add_btn',$this->lang->line('add')); // 新增按鈕文字
    $this->tpl->assign('tv_cpy_btn',$this->lang->line('cpy')); // 複製按鈕文字
    $this->tpl->assign('tv_upd_btn',$this->lang->line('upd')); // 修改按鈕文字
    $this->tpl->assign('tv_del_btn',$this->lang->line('del')); // 刪除按鈕文字
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_import_btn',$this->lang->line('import')); // 匯入按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
    $this->tpl->assign('tv_add_link',be_url().'delivery_person/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."delivery_person/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
    $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
    $this->tpl->assign('tv_import_ok',$this->lang->line('import_ok')); // 匯入資料成功!!
    $this->tpl->assign('tv_save_ok',$this->lang->line('save_ok')); // 儲存成功!!
    $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
    $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
    $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
    $this->tpl->assign('tv_import_ng',$this->lang->line('import_ng')); // 匯入資料失敗!!
    $this->tpl->assign('tv_save_ng',$this->lang->line('save_ng')); // 儲存失敗!!
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/delivery_person_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/delivery_person/');
    $this->tpl->assign('tv_chk_dp03_link', be_url()."delivery_person/chk_dp03");
    $this->tpl->assign('tv_pwd_placeholder','請輸入密碼');
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    return;
  }
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
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
    if(!isset($get_data['que_str'])) {
      $get_data['que_str'] = NULL;
    }
    list($delivery_person_row,$row_cnt) = $this->delivery_person_model->get_que($q_str,$pg); // 列出外送員基本資料檔
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','外送員基本資料檔瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'delivery_person/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'delivery_person/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'delivery_person/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'delivery_person/upd/');
    $this->tpl->assign('tv_del_link',be_url().'delivery_person/del/');
    $this->tpl->assign('tv_prn_link',be_url().'delivery_person/prn/');
    $this->tpl->assign('tv_rest_link',be_url().'delivery_person/rest/');
    $this->tpl->assign('tv_que_link',be_url()."delivery_person/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'delivery_person/save/');
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $config['base_url'] = be_url()."delivery_person/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/delivery_person/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/delivery_person.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $delivery_person_row = $this->delivery_person_model->get_one($s_num); // 列出單筆明細資料
    $delivery_person_rest_row = $this->delivery_person_model->get_rest($s_num); // 取得請假資料

    //u_var_dump($delivery_person_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $this->tpl->assign('tv_delivery_person_rest_row',$delivery_person_rest_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."delivery_person/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."delivery_person/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."delivery_person/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'delivery_person/upd/');
    $this->tpl->display("be/delivery_person_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function add()	{ // 新增
    $msel = 'add';
    $delivery_person_row = NULL;
    $delivery_person_rest_row = array();

    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $this->tpl->assign('tv_delivery_person_rest_row',$delivery_person_rest_row);
    $this->tpl->assign('tv_save_link',be_url()."delivery_person/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."delivery_person/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."delivery_person/"); // 上一層連結位置
    $this->tpl->display("be/delivery_person_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function cpy($s_num)	{
    $msel = 'cpy';
    $delivery_person_row = $this->delivery_person_model->get_one($s_num);
    $delivery_person_rest_row = array();
    //u_var_dump($delivery_person_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $this->tpl->assign('tv_delivery_person_rest_row',$delivery_person_rest_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."delivery_person/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."delivery_person/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."delivery_person/"); // 上一層連結位置
    $this->tpl->display("be/delivery_person_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function upd($s_num)	{
    $msel = 'upd';
    $delivery_person_row = $this->delivery_person_model->get_one($s_num);
    $delivery_person_rest_row = (array) $this->delivery_person_model->get_rest($s_num); // 取得請假資料

    //u_var_dump($delivery_person_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $this->tpl->assign('tv_delivery_person_rest_row',$delivery_person_rest_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."delivery_person/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."delivery_person/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."delivery_person/"); // 上一層連結位置
    $this->tpl->assign('tv_pwd_placeholder','空白=不修改密碼，請輸入新密碼');
    $this->tpl->display("be/delivery_person_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function del($s_num=NULL)	{
    $rtn_msg = $this->delivery_person_model->del($s_num); // 刪除
    $this->zi_my_func->web_api_data("delivery_person", "del");
    if($rtn_msg) {
      redirect(be_url().'delivery_person/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: rest()
  //  函數功能: 請假
  //  程式設計: kiwi
  //  設計日期: 2024-01-10
  // **************************************************************************
  public function rest()	{
    $rtn_msg = $this->delivery_person_model->rest(); // 請假
    if($rtn_msg) {
      redirect(be_url().'delivery_person/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除)
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function save($kind=NULL)	{
    switch($kind) {
      case "add":
      case "cpy":
        $fd_msel = "add";
        $this->delivery_person_model->save_add(); // 新增儲存
        break;
      case "upd":
        $fd_msel = "upd";
        $this->delivery_person_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $fd_msel = "stop";
        $this->delivery_person_model->save_is_available(); // 上下架儲存
        break;
    }
    $this->zi_my_func->web_api_data("delivery_person", $fd_msel);
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
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
    ////redirect(be_url()."delivery_person/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."delivery_person/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  private function _que_start($q_str)	{
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }
  // **************************************************************************
  //  函數名稱: prn()
  //  函數功能: 列印
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function prn()	{
    $msel = 'prn';
    return;
  }

  // **************************************************************************
  //  函數名稱: que_dp()
  //  函數功能: 查詢外送員資料
  //  程式設計: Kiwi
  //  設計日期: 2021/02/08
  // **************************************************************************
  public function que_dp() {
    $rtn_msg = $this->delivery_person_model->que_dp(); // 搜尋外送員資料
    echo $rtn_msg;
    return;
  }
  
  // ************************************************************************
  //  函數名稱: chk_dp03()
  //  函數功能: 查詢外送員資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function chk_dp03()	{
    $delivery_person_row = $this->delivery_person_model->chk_dp03();
    if($delivery_person_row != NULL) {
      echo "false";
      return;
    }
    echo "true";
    return;
  }
  
  function __destruct() {
    $url_str[] = 'be/delivery_person/save';
    $url_str[] = 'be/delivery_person/del';
    $url_str[] = 'be/delivery_person/rest';
    $url_str[] = 'be/delivery_person/que_dp';
    $url_str[] = 'be/delivery_person/chk_dp03';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
?>