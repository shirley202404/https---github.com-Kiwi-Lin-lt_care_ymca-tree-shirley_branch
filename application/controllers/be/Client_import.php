<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_import extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('clients_model'); // 案主暫存資料
    $this->load->model('client_import_model'); // 案主資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','案主資料');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_group_s_num',$_SESSION['group_s_num']);
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
    $this->tpl->assign('tv_contract_btn',$this->lang->line('contract')); // 契約書按鈕文字
    $this->tpl->assign('tv_add_link',be_url().'client_import/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."client_import/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
    $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
    $this->tpl->assign('tv_import_ok',$this->lang->line('import_ok')); // 匯入資料成功!!
    $this->tpl->assign('tv_convert_ok',$this->lang->line('convert_ok')); // 轉換成功!!
    $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
    $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
    $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
    $this->tpl->assign('tv_import_ng',$this->lang->line('import_ng')); // 匯入資料失敗!!
    $this->tpl->assign('tv_convert_ng',$this->lang->line('convert_ng')); // 轉換失敗!!
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/clients_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_address_convert_link', be_url()."clients/address_convert");
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    return;
  }

  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
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
    list($client_import_row,$row_cnt) = $this->client_import_model->get_que($q_str,$pg); // 列出案主資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','案主資料瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'client_import/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'client_import/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'client_import/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'client_import/upd/');
    $this->tpl->assign('tv_del_link',be_url().'client_import/del/');
    $this->tpl->assign('tv_prn_link',be_url().'client_import/prn/');
    $this->tpl->assign('tv_file_upload_link',be_url().'client_import/file_upload/');
    $this->tpl->assign('tv_que_link',be_url()."client_import/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'client_import/save/');
    $this->tpl->assign('tv_client_import_row',$client_import_row);
    $config['base_url'] = be_url()."client_import/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/client_import/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

    $this->tpl->display("be/client_import.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $client_import_row = $this->client_import_model->get_one($s_num); // 列出單筆明細資料

    $client_import_row->ct21_arr = explode(",", $client_import_row->ct21);
    $client_import_row->ct31_arr = explode(",", $client_import_row->ct31);
    $client_import_row->ct34_go_arr = explode(",", $client_import_row->ct34_go);
    $client_import_row->ct34_fo_arr = explode(",", $client_import_row->ct34_fo);
    $client_import_row->ct35_level_arr = explode(",", $client_import_row->ct35_level);
    $client_import_row->ct35_type_arr = explode(",", $client_import_row->ct35_type);
    $client_import_row->ct36_arr = explode(",", $client_import_row->ct36);
    $client_import_row->ct37_arr = explode(",", $client_import_row->ct37);
    $client_import_row->ct38_1_arr = explode(",", $client_import_row->ct38_1);
    $client_import_row->ct38_2_arr = explode(",", $client_import_row->ct38_2);

    if(NULL != $client_import_row->ct95) {
      $client_import_row->ct95_base64 = $this->zi_my_func->img_to_base64('clients', $client_import_row->ct95);
    } 
    if(NULL != $client_import_row->ct96) {
      $client_import_row->ct96_base64 = $this->zi_my_func->img_to_base64('clients', $client_import_row->ct96);
    } 
        
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."client_import/save/{$msel}");
    $this->tpl->assign('tv_client_import_row',$client_import_row);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."client_import/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."client_import/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'client_import/upd/');
    $this->tpl->assign('tv_del_identity_link',be_url().'client_import/del_identity/');
    $this->tpl->assign('tv_home_interview_link',be_url().'home_interview/?que_ct_s_num=');
    $this->tpl->assign('tv_home_interview_disp_link',be_url().'home_interview/disp/');
    $this->tpl->assign('tv_phone_interview_link',be_url().'phone_interview/?que_ct_s_num=');
    $this->tpl->assign('tv_phone_interview_disp_link',be_url().'phone_interview/disp/');
    $this->tpl->assign('tv_clients_hlth_normal_link',be_url().'clients_hlth_normal/?que_ct_s_num=');
    $this->tpl->assign('tv_clients_hlth_normal_disp_link',be_url().'clients_hlth_normal/disp/');
    $this->tpl->assign('tv_meal_instruction_log_link',be_url().'meal_instruction_log/?que_sec_s_num=');
    $this->tpl->display("be/client_import_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function add() { // 新增
    $msel = 'add';
    // u_var_dump($client_import_row);
    $client_import_row['ct21_arr'] = array();
    $client_import_row['ct31_arr'] = array();
    $client_import_row['ct34_go_arr'] = array();
    $client_import_row['ct34_fo_arr'] = array();
    $client_import_row['ct35_type_arr'] = array();
    $client_import_row['ct37_arr'] = array();
    $client_import_row['ct38_1_arr'] = array();
    $client_import_row['ct38_2_arr'] = array();

    $client_import_row = (object) $client_import_row;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_client_import_row',$client_import_row);
    $this->tpl->assign('tv_save_link',be_url()."client_import/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."client_import/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."client_import/"); // 上一層連結位置
    $this->tpl->assign('tv_route_link',be_url()."route/upd/"); // 上一層連結位置
    $this->tpl->display("be/client_edit_import.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function cpy($s_num) {
    $msel = 'cpy';
    $client_import_row = $this->client_import_model->get_one($s_num);
    //u_var_dump($client_import_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_client_import_row',$client_import_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."client_import/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."client_import/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."client_import/"); // 上一層連結位置
    $this->tpl->display("be/client_edit_import.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function upd($s_num) {
    $msel = 'upd';
    $client_import_row = $this->client_import_model->get_one($s_num);

    $client_import_row->ct21_arr = explode(",", $client_import_row->ct21);
    $client_import_row->ct31_arr = explode(",", $client_import_row->ct31);
    $client_import_row->ct34_go_arr = explode(",", $client_import_row->ct34_go);
    $client_import_row->ct34_fo_arr = explode(",", $client_import_row->ct34_fo);
    $client_import_row->ct35_type_arr = explode(",", $client_import_row->ct35_type);
    $client_import_row->ct37_arr = explode(",", $client_import_row->ct37);
    $client_import_row->ct38_1_arr = explode(",", $client_import_row->ct38_1);
    $client_import_row->ct38_2_arr = explode(",", $client_import_row->ct38_2);
    // u_var_dump($client_import_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_client_import_row',$client_import_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."client_import/save/convert");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."client_import/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."client_import/"); // 上一層連結位置
    $this->tpl->assign('tv_chk_ct03_link', be_url()."clients/chk_ct03");
    $this->tpl->display("be/client_import_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->client_import_model->del($s_num); // 刪除
    $this->zi_my_func->web_api_data("client", "del");
    if($rtn_msg) {
      redirect(be_url().'client_import/', 'refresh');
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
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "add":
      case "cpy":
        $fd_msel = "add";
        $this->client_import_model->save_add(); // 新增儲存
        break;
      case "upd":
        $fd_msel = "upd";
        $this->client_import_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $fd_msel = "stop";
        $this->client_import_model->save_is_available(); // 上下架儲存
        break;
      case "import":
        $fd_msel = "import";
        $this->html_upload(); // 案主資料上傳
        break;
      case "convert":
        $fd_msel = "convert";
        $this->client_import_model->save_convert(); // 轉入為正式資料
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function que($q_str) {
    ////$data = $this->input->post(); // POST 用
    //////u_var_dump($data);
    //////exit;
    ////if('que'==$data['que_kind']) {
    ////  $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    ////}
    ////$_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    ////$_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    ////redirect(be_url()."client_import/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."client_import/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }

  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  private function _que_start($q_str) {
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }

  // **************************************************************************
  //  函數名稱: prn()
  //  函數功能: 列印
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function prn() {
    $msel = 'prn';
    return;
  }
  // **************************************************************************
  //  函數名稱: file_upload()
  //  函數功能: 案主資料上傳
  //  程式設計: Kiwi
  //  設計日期: 2023/08/22
  // **************************************************************************
  public function file_upload($s_num=NULL)  {
    $this->load->helper('directory');
    $msel = 'upload';
    $source_path = FCPATH . "upload_files/clients_html/";
    $upload_files = directory_map($source_path, 1);
    $rm_name_arr = array('cust\\', 'thumbnail\\');
    if(NULL != $upload_files) {
      foreach ($upload_files as $k_file_name => $v_file_name) {
        if(in_array($v_file_name, $rm_name_arr)) {
          unset($upload_files[$k_file_name]);
        }
      }
    }

    $this->tpl->assign('tv_breadcrumb3','檔案上傳'); // 
    $this->tpl->assign('tv_save_ok',$this->lang->line('convert_ok')); // 轉入成功!!
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_link',be_url()."client_import/save/import");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',$this->agent->referrer()); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."client_import/"); // 上一層連結位置
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/html_upload/origin/html/');
    $this->tpl->assign('tv_cron_job_link',base_url()."cron_job/");
    $this->tpl->display("be/client_import_upload_file.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: html_upload()
  //  函數功能: HTML轉換匯入
  //  程式設計: Kiwi
  //  設計日期: 2023/08/22
  // **************************************************************************
  public function html_upload() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $time_start = date('Y-m-d H:i:s');

    $this->load->helper('directory');
    $this->load->library('zi_client_html');

    $source_path = FCPATH . "upload_files/clients_html/";
    $upload_files = directory_map($source_path, 1);
    $rm_name_arr = array('cust\\', 'thumbnail\\');
    if(NULL != $upload_files) {
      foreach ($upload_files as $k_file_name => $v_file_name) {
        if(in_array($v_file_name, $rm_name_arr)) {
          unset($upload_files[$k_file_name]);
        }
      }
    }
    
    $rtn_file_res_arr['success'] = [];
    $rtn_file_res_arr['error'] = [];
    foreach ($upload_files as $file_name) {
      $client_html = FCPATH."upload_files/clients_html/{$file_name}";
      $client_data = $this->zi_client_html->get_client_data($client_html);
      unset($client_data['ct_address1'], $client_data['ct_address2']);
      if(!empty($client_data)) {
        if($this->client_import_model->save_import($client_data)) {
          $rtn_file_res_arr['success'][] = $file_name;
          if(file_exists($client_html)) {
            unlink($client_html);
          } 
        }
        else {
          $rtn_file_res_arr['error'][] = $file_name;
        }
      }
    }

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff >= 60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }   

    $error_file_str = join("、", $rtn_file_res_arr['error']);
    $success_file_str = join("、", $rtn_file_res_arr['success']);

    $rtn_str = ''; 
    $rtn_str .= "<table class='table table-bordered table-hover'>";
    $rtn_str .= "  <thead>";
    $rtn_str .= "    <tr>";
    $rtn_str .= "      <th width='20%'>項目</th>";
    $rtn_str .= "      <th width='80%'>說明</th>";
    $rtn_str .= "    </tr>";
    $rtn_str .= "  </thead>";
    $rtn_str .= "  <tbody>";
    $rtn_str .= "    <tr>";
    $rtn_str .= "      <td>處理時間</th>";
    $rtn_str .= "      <td>{$time_diff}</td>";
    $rtn_str .= "    </tr>";
    $rtn_str .= "    <tr>";
    $rtn_str .= "      <td>轉入成功</th>";
    $rtn_str .= "      <td>".count($rtn_file_res_arr['success'])." 筆<br/>{$success_file_str}</td>";
    $rtn_str .= "    </tr>";
    $rtn_str .= "    <tr>";
    $rtn_str .= "      <td>轉入失敗</th>";
    $rtn_str .= "      <td>".count($rtn_file_res_arr['error'])." 筆<br/>{$error_file_str}</td>";
    $rtn_str .= "    </tr>";
    $rtn_str .= "  </tbody>";
    $rtn_str .= "</table>";
    echo json_encode($rtn_str);
  }

  function __destruct() {
    $url_str[] = 'be/client_import/save';
    $url_str[] = 'be/client_import/del';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
