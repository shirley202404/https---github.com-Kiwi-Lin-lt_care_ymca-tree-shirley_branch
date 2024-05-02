<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Work_q_route_list extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('route_model'); // 路徑資料檔
    $this->load->model('gps_log_model'); // GPS資料記錄檔
    $this->load->model('punch_log_model'); // Punch資料記錄檔
    $this->load->model('delivery_person_model'); // 送餐員資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','專案資料檔');
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
    $this->tpl->assign('tv_add_link',be_url().'work_q_route_list/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."work_q_route_list/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/project_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_return_list_link',be_url()."work_q_route_list/"); // return 預設到路線列表畫面

    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    return;
  }

  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function index($pg=1,$q_str=NULL) {
    $msel = 'list';
    $source = '';
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
    if(isset($get_data['source'])) {
      $source = $get_data['source'];
    }
    $all_route_row = $this->route_model->get_all_without_test();
    list($route_row,$row_cnt) = $this->route_model->get_que($q_str,$pg); // 列出專案資料檔
    if(NULL != $route_row) {
      foreach ($route_row as $k => $v) {
        $route_sw_str = '';
        $route_sw_arr = NULL;
        $route_sw_row = $this->route_model->get_route_sw($v['s_num']);
        if(NULL != $route_sw_row) {
          foreach ($route_sw_row as $k_sw => $v_sw) {
            $route_sw_arr[] = "{$v_sw['dp01']}{$v_sw['dp02']}";
          } 
          $route_sw_str = join("、", $route_sw_arr);
        }
        $route_row[$k]["route_sw_str"] = $route_sw_str;
      } 
    }
    $delivery_person_row = $this->delivery_person_model->get_all_is_available();
    // u_var_dump($route_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_source',$source);
    $this->tpl->assign('tv_title','專案資料檔瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'work_q_route_list/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'work_q_route_list/cpy/');
    $this->tpl->assign('tv_work_q_disp_link',be_url().'work_q/');
    $this->tpl->assign('tv_gps_log_disp_link',be_url().'gps_log/');
    $this->tpl->assign('tv_punch_log_disp_link',be_url().'punch_log/');
    $this->tpl->assign('tv_upd_link',be_url().'work_q_route_list/upd/');
    $this->tpl->assign('tv_del_link',be_url().'work_q_route_list/del/');
    $this->tpl->assign('tv_prn_link',be_url().'work_q_route_list/prn/');
    $this->tpl->assign('tv_route_history_link',be_url().'work_q_route_list/route_history/');
    $this->tpl->assign('tv_download_link',be_url()."{$source}/download");
    $this->tpl->assign('tv_que_link',be_url()."work_q_route_list/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'work_q_route_list/save/');
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_all_route_row',$all_route_row);
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $config['base_url'] = be_url()."work_q_route_list/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/work_q_route_list/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/work_q_route_list.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $project_row = $this->project_model->get_one($s_num); // 列出單筆明細資料
    //u_var_dump($project_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_project_row',$project_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."work_q_route_list/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q_route_list/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q_route_list/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'work_q_route_list/upd/');
    $this->tpl->display("be/project_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function add()  { // 新增
    $msel = 'add';
    $project_row = NULL;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_project_row',$project_row);
    $this->tpl->assign('tv_save_link',be_url()."work_q_route_list/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q_route_list/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q_route_list/"); // 上一層連結位置
    $this->tpl->display("be/project_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function cpy($s_num)  {
    $msel = 'cpy';
    $project_row = $this->project_model->get_one($s_num);
    //u_var_dump($project_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_project_row',$project_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."work_q_route_list/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q_route_list/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q_route_list/"); // 上一層連結位置
    $this->tpl->display("be/project_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function upd($s_num)  {
    $msel = 'upd';
    $project_row = $this->project_model->get_one($s_num);
    //u_var_dump($project_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_project_row',$project_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."work_q_route_list/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q_route_list/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q_route_list/"); // 上一層連結位置
    $this->tpl->display("be/project_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->project_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'work_q_route_list/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }

  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除)
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "add":
      case "cpy":
        $this->project_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->project_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->project_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function que($q_str)  {
    ////$data = $this->input->post(); // POST 用
    //////u_var_dump($data);
    //////exit;
    ////if('que'==$data['que_kind']) {
    ////  $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    ////}
    ////$_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    ////$_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    ////redirect(be_url()."work_q_route_list/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."work_q_route_list/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }

  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  private function _que_start($q_str)  {
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }

  // **************************************************************************
  //  函數名稱: prn()
  //  函數功能: 列印
  //  程式設計: Tony
  //  設計日期: 2020-12-03
  // **************************************************************************
  public function prn()  {
    $msel = 'prn';
    return;
  }

  // **************************************************************************
  //  函數名稱: route_history()
  //  函數功能: 路線歷史紀錄
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function route_history() {
    $msel = 'disp';
    $v = $this->input->get();
    $route_row = $this->route_model->get_all();
    $delivery_person_row = $this->delivery_person_model->get_one($v['dp_s_num']);
    $gps_log_row = $this->gps_log_model->get_gps_data(); // 列出單筆明細資料
    $punch_log_row = $this->punch_log_model->get_punch_data(); // 列出單筆明細資料
    // u_var_dump($punch_log_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_history_date',$v['history_date']);
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_gps_log_row',$gps_log_row);
    $this->tpl->assign('tv_punch_log_row',$punch_log_row);
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."gps_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."gps_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."gps_log/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'gps_log/upd/');
    $this->tpl->display("be/route_history.html");
    return;
  }
  
  function __destruct() {
    $url_str[] = 'be/work_q_route_list/save';
    $url_str[] = 'be/work_q_route_list/del';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
