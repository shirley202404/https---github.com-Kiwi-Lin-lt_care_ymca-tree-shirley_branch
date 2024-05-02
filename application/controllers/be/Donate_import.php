<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Donate_import extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('donate_import_model'); // 捐款資料(線下資料匯入)
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','捐款徵信資料匯入');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_list_btn',$this->lang->line('list')); // 瀏覽按鈕文字
    $this->tpl->assign('tv_disp_btn',$this->lang->line('disp')); // 明細按鈕文字
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
    $this->tpl->assign('tv_import_btn',$this->lang->line('import')); // 匯入按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."donate_import/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
    $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
    $this->tpl->assign('tv_import_ok',$this->lang->line('import_ok')); // 匯入資料成功!!
    $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
    $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
    $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
    $this->tpl->assign('tv_import_ng',$this->lang->line('import_ng')); // 匯入資料失敗!!
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/donate_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
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
    if(!isset($get_data['que_str'])) {
      $get_data['que_str'] = NULL;
    }
    if(!isset($get_data['que_type'])) {
      $get_data['que_type'] = NULL;
    }
    if(!isset($get_data['start'])) {
      $get_data['start'] = NULL;
    }
    if(!isset($get_data['end'])) {
      $get_data['end'] = NULL;
    }
    if(!isset($get_data['que_dei03'])) {
      $get_data['que_dei03'] = NULL;
    }
    list($donate_import_row,$row_cnt) = $this->donate_import_model->get_que($q_str,$pg); // 列出捐款資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','捐款徵信資料匯入瀏覽');
    $this->tpl->assign('tv_start',$get_data['start']);
    $this->tpl->assign('tv_end',$get_data['end']);
    $this->tpl->assign('tv_que_dei03',$get_data['que_dei03']);
    $this->tpl->assign('tv_que_type',$get_data['que_type']);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_import_old_excel_link',be_url().'donate_import/import_old_excel');
    $this->tpl->assign('tv_import_excel_link',be_url().'donate_import/import_excel');
    $this->tpl->assign('tv_que_link',be_url()."donate_import/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_donate_import_row',$donate_import_row);
    $config['base_url'] = be_url()."donate_import/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/donate_import/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/donate_import.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $donate_row = $this->donate_model->get_one($s_num); // 列出單筆明細資料
    //u_var_dump($donate_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_donate_row',$donate_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."donate_import/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."donate_import/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."donate_import/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'donate_import/upd/');
    $this->tpl->display("be/donate_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除)
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "import":
        $this->donate_model->save_import(); // 匯入儲存
      case "import_excel":
        $this->donate_import_model->save_import_excel(); // 匯入儲存
        break;
      case "import_old_excel":
        $this->donate_import_model->save_import_old_excel(); // 匯入儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function que($q_str) {
    ////$data = $this->input->post(); // POST 用 Mark by Tony 2020/7/27
    ////u_var_dump($data);
    ////exit;
    ////if('que'==$data['que_kind']) {
    ////  $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    ////}
    ////$_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    ////$_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    ////redirect(be_url()."donate_import/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."donate_import/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  private function _que_start($q_str) {
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }
  // **************************************************************************
  //  函數名稱: import_excel()
  //  函數功能: 匯入
  //  程式設計: Shirley
  //  設計日期: 2022/11/23
  // **************************************************************************
  public function import_excel()  {
    $msel = 'import';
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 匯入
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('import_ok')); // 匯入成功!!
    $this->tpl->assign('tv_save_link',be_url()."donate_import/save/import_excel");
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/donate_import_/xls'); // 僅接受xls檔案
    $this->tpl->display("be/donate_import_excel.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: import_old_excel()
  //  函數功能: 匯入
  //  程式設計: Shirley
  //  設計日期: 2022/12/05
  // **************************************************************************
  public function import_old_excel()  {
    $msel = 'import';
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 匯入
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('import_ok')); // 匯入成功!!
    $this->tpl->assign('tv_save_link',be_url()."donate_import/save/import_old_excel");
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/donate_import_/xls'); // 僅接受xls檔案
    $this->tpl->display("be/donate_import_old_excel.html");
    return;
  }

  function __destruct() {
    // ajax 用，如果有回傳 echo 於畫面上的，就加上
    $url_str[] = 'be/donate_import/save';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
?>