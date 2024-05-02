<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Other_change_log extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('meal_model'); // 餐點資料
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('other_change_log_h_model'); // 餐點異動資料
    $this->load->model('other_change_auth_model'); // 異動單審核紀錄檔
    $this->load->model('verification_person_model'); // 核銷人員資料檔
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','餐點異動資料');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
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
    $this->tpl->assign('tv_add_link',be_url().'other_change_log/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."other_change_log/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/other_change_log_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_service_case_link',be_url()."service_case/"); // 開案服務
    $this->tpl->assign('tv_other_change_auth_link',be_url().'other_change_auth/');
    $this->tpl->assign('tv_que_client_data_link',be_url().'clients/que_client_data/');
    $this->tpl->assign('tv_que_client_route_data_link',be_url().'route/que_client_route_data/');
    $this->tpl->assign('tv_que_client_service_data_link',be_url().'service_case/que_client_service_data/');

    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    return;
  }

  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
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
    list($other_change_log_h_row,$row_cnt) = $this->other_change_log_h_model->get_que($q_str,$pg); // 列出餐點異動資料
    // u_var_dump($other_change_log_h_row);
    if(NULL != $other_change_log_h_row) {
      foreach ($other_change_log_h_row as $k => $v) {
        $ocl01_str_arr = NULL;
        $ocl01 = explode(",", $v["ocl01"]);
        foreach ($ocl01 as $v) {
          switch ($v) {  
            case 1: // 身分別異動
              $ocl01_str_arr[] = "身分別異動";
              break;
            case 2: // 失能等級
              $ocl01_str_arr[] = "失能等級異動";
              break;  
            case 3: // 送餐路線異動
              $ocl01_str_arr[] = "送餐路線異動";
              break;  
            case 4: // 服務現況
              $ocl01_str_arr[] = "服務現況異動";
              break;    
            case 5: // 變更基礎資料
              $ocl01_str_arr[] = "變更基礎資料";
              break;    
            case 6: // 照會營養師
              $ocl01_str_arr[] = "照會營養師";
              break;
            case 7: // 其他問題
              $ocl01_str_arr[] = "其他問題";
              break;
          } 
        } 
        $other_change_log_h_row[$k]["ocl01_str"] = join("、", $ocl01_str_arr);
      } 
    }
    
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_title','餐點異動資料瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'other_change_log/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'other_change_log/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'other_change_log/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'other_change_log/upd/');
    $this->tpl->assign('tv_del_link',be_url().'other_change_log/del/');
    $this->tpl->assign('tv_prn_link',be_url().'other_change_log/prn/');
    $this->tpl->assign('tv_que_data_link',be_url().'other_change_log/que_data/');
    $this->tpl->assign('tv_que_link',be_url()."other_change_log/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'other_change_log/save/');
    $this->tpl->assign('tv_other_change_log_h_row',$other_change_log_h_row);
    $config['base_url'] = be_url()."other_change_log/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/other_change_log/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

    $this->tpl->display("be/other_change_log.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $other_change_log_h_row = $this->other_change_log_h_model->get_one($s_num); // 列出單筆明細資料
    $ocl_s_num = $other_change_log_h_row->s_num;
    $ocl01_arr = explode(",", $other_change_log_h_row->ocl01);
    $ocl01_str_arr = NULL;
    $other_change_log_identity_row = NULL;
    $other_change_log_disabled_row = NULL;
    $other_change_log_route_row = NULL;
    $other_change_log_service_row = NULL;
    foreach ($ocl01_arr as $v) {
      switch ($v) {  
        case 1: // 身分別異動
          $other_change_log_identity_row = $this->other_change_log_h_model->get_identity_by_s_num($ocl_s_num); // 列出餐點異動資料
          if(NULL != $other_change_log_identity_row ) {
            $other_change_log_identity_row->ocl_i01_before_arr = explode(",", $other_change_log_identity_row->ocl_i01_before	);
            $other_change_log_identity_row->ocl_i01_after_arr = explode(",", $other_change_log_identity_row->ocl_i01_after);
            $ocl01_str_arr[] = "身分別異動";
          }
          break;
        case 2: // 失能等級異動
          $other_change_log_disabled_row = $this->other_change_log_h_model->get_disabled_by_s_num($ocl_s_num); // 列出失能異動資料
          if(NULL != $other_change_log_disabled_row) {
            $other_change_log_disabled_row->ocl_d01_before_arr = explode(",", $other_change_log_disabled_row->ocl_d01_before);
            $other_change_log_disabled_row->ocl_d01_after_arr = explode(",", $other_change_log_disabled_row->ocl_d01_after);
            $ocl01_str_arr[] = "失能等級異動";
          }
          break;  
        case 3: // 送餐路線異動
          $other_change_log_route_row = $this->other_change_log_h_model->get_route_by_s_num($ocl_s_num); // 列出路線異動資料
          if(NULL != $other_change_log_route_row) {
            $ocl01_str_arr[] = "送餐路線異動";
          }
          break;  
        case 4: // 服務現況異動
          $other_change_log_service_row = $this->other_change_log_h_model->get_service_by_s_num($ocl_s_num); // 列出固定暫停資料
          if(NULL != $other_change_log_service_row) {
            $ocl01_str_arr[] = "服務現況異動";
          }
          break;  
        case 5: // 變更基礎資料
          $ocl01_str_arr[] = "變更基礎資料";
          break;    
        case 6: // 照會營養師
          $ocl01_str_arr[] = "照會營養師";
          break;
        case 7: // 其他問題
          $ocl01_str_arr[] = "其他問題";
          break;
      } 
      $other_change_log_h_row->ocl01_str = join("、", $ocl01_str_arr);
    }     

    // u_var_dump($other_change_log_identity_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_ocl01_arr',$ocl01_arr);
    $this->tpl->assign('tv_other_change_log_h_row',$other_change_log_h_row);
    $this->tpl->assign('tv_other_change_log_identity_row',$other_change_log_identity_row);
    $this->tpl->assign('tv_other_change_log_disabled_row',$other_change_log_disabled_row);
    $this->tpl->assign('tv_other_change_log_route_row',$other_change_log_route_row);
    $this->tpl->assign('tv_other_change_log_service_row',$other_change_log_service_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."other_change_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_log/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'other_change_log/upd/');
    $this->tpl->display("be/other_change_log_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function add() { // 新增
    $msel = 'add';
    $ct_s_num = 0;
    $ct_name = '';
    $get_data = $this->input->get();
    if(isset($get_data['que_ct_s_num'])) {
      $clients_row = $this->clients_model->get_one($get_data['que_ct_s_num']);
      if(NULL != $clients_row) {
        $ct_s_num = $clients_row->s_num;
        $ct_name = "{$clients_row->ct01}{$clients_row->ct02}";
      }
    }

    $meals_row = $this->meal_model->get_all(); // 餐點 
    $ocl01_arr = array();
    $other_change_log_row = NULL;
    $route_h_row = $this->route_model->get_all(); // 路徑資料
    $verification_person_row = $this->verification_person_model->get_all_is_available();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_ct_name',$ct_name);
    $this->tpl->assign('tv_ct_s_num',$ct_s_num);
    $this->tpl->assign('tv_ocl01_arr',$ocl01_arr);
    $this->tpl->assign('tv_meals_row',$meals_row);
    $this->tpl->assign('tv_route_h_row',$route_h_row);
    $this->tpl->assign('tv_verification_person_row',$verification_person_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_other_change_log_row',$other_change_log_row);
    $this->tpl->assign('tv_save_link',be_url()."other_change_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_log/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_sec_link',be_url()."service_case/que_sec"); // 搜尋案主開案資料
    $this->tpl->display("be/other_change_log_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function cpy($s_num) {
    $msel = 'cpy';
    $meals_row = $this->meal_model->get_all(); //  餐點資料
    $other_change_log_row = $this->other_change_log_model->get_one($s_num);
    $verification_person_row = $this->verification_person_model->get_all_is_available();
    
    //u_var_dump($other_change_log_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_meals_row',$meals_row);
    $this->tpl->assign('tv_other_change_log_row',$other_change_log_row);
    $this->tpl->assign('tv_verification_person_row',$verification_person_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."other_change_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_log/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_sec_link',be_url()."service_case/que_sec"); // 搜尋案主開案資料
    $this->tpl->display("be/other_change_log_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function upd($s_num) {
    $msel = 'upd';
    $other_change_log_h_row = $this->other_change_log_h_model->get_one($s_num); // 列出單筆明細資料
    $ocl_s_num = $other_change_log_h_row->s_num;
    $ocl01_arr = explode(",", $other_change_log_h_row->ocl01);
    $ocl01_str_arr = NULL;
    $other_change_log_identity_row = NULL;
    $other_change_log_disabled_row = NULL;
    $other_change_log_route_row = NULL;
    $other_change_log_service_row = NULL;
    foreach ($ocl01_arr as $v) {
      switch ($v) {  
        case 1: // 身分別異動
          $other_change_log_identity_row = $this->other_change_log_h_model->get_identity_by_s_num($ocl_s_num); // 列出餐點異動資料
          if(NULL != $other_change_log_identity_row ) {
            $other_change_log_identity_row->ocl_i01_before_arr = explode(",", $other_change_log_identity_row->ocl_i01_before	);
            $other_change_log_identity_row->ocl_i01_after_arr = explode(",", $other_change_log_identity_row->ocl_i01_after);
            $ocl01_str_arr[] = "身分別異動";
          }
          break;
        case 2: // 失能等級異動
          $other_change_log_disabled_row = $this->other_change_log_h_model->get_disabled_by_s_num($ocl_s_num); // 列出失能異動資料
          if(NULL != $other_change_log_disabled_row) {
            $ocl01_str_arr[] = "失能等級異動";
          }
          break;  
        case 3: // 送餐路線異動
          $other_change_log_route_row = $this->other_change_log_h_model->get_route_by_s_num($ocl_s_num); // 列出路線異動資料
          if(NULL != $other_change_log_route_row) {
            $ocl01_str_arr[] = "送餐路線異動";
          }
          break;  
        case 4: // 服務現況異動
          $other_change_log_service_row = $this->other_change_log_h_model->get_service_by_s_num($ocl_s_num); // 列出固定暫停資料
          if(NULL != $other_change_log_service_row) {
            $ocl01_str_arr[] = "服務現況異動";
          }
          break;  
        case 5: // 變更基礎資料
          $ocl01_str_arr[] = "變更基礎資料";
          break;    
        case 6: // 照會營養師
          $ocl01_str_arr[] = "照會營養師";
          break;
        case 7: // 其他問題
          $ocl01_str_arr[] = "其他問題";
          break;
      } 
      $other_change_log_h_row->ocl01_str = join("、", $ocl01_str_arr);
    }
    $route_h_row = $this->route_model->get_all(); // 路徑資料
    $verification_person_row = $this->verification_person_model->get_all_is_available();
    //u_var_dump($other_change_log_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_ocl01_arr',$ocl01_arr);
    $this->tpl->assign('tv_route_h_row',$route_h_row);
    $this->tpl->assign('tv_verification_person_row',$verification_person_row);
    $this->tpl->assign('tv_other_change_log_h_row',$other_change_log_h_row);
    $this->tpl->assign('tv_other_change_log_identity_row',$other_change_log_identity_row);
    $this->tpl->assign('tv_other_change_log_disabled_row',$other_change_log_disabled_row);
    $this->tpl->assign('tv_other_change_log_route_row',$other_change_log_route_row);
    $this->tpl->assign('tv_other_change_log_service_row',$other_change_log_service_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."other_change_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_log/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_sec_link',be_url()."service_case/que_sec"); // 搜尋案主開案資料
    $this->tpl->display("be/other_change_log_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->other_change_log_h_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'other_change_log/', 'refresh');
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "add":
      case "cpy":
        $this->other_change_log_h_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->other_change_log_h_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->other_change_log_h_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
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
    ////redirect(be_url()."other_change_log/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."other_change_log/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }

  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function prn() {
    $msel = 'prn';
    return;
  }

  // **************************************************************************
  //  函數名稱: que_data()
  //  函數功能: 查詢停餐名單
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_data() {
    $hist_list = '';
    $post_data = $this->input->post();
    switch($post_data['que_type']) {
      case 1: // 異動統計
        $ocl_h_row = $this->other_change_log_h_model->get_all_by_b_date(); 
        $hist_list = '<thead>
                        <tr class="thead-light">
                          <th style="width: 33.3%">登打日期區間</th>
                          <th style="width: 33.3%">異動項目</th>
                          <th style="width: 33.3%">筆數</th>
                        </tr>
                      </thead>
                      <tbody>
                     ';
        if(NULL != $ocl_h_row) {
          $ocl_stat[1] = array("ocl01_name" => "身分別異動", "ocl01_total" => 0);
          $ocl_stat[2] = array("ocl01_name" => "失能等級異動", "ocl01_total" => 0);
          $ocl_stat[3] = array("ocl01_name" => "更改路線異動", "ocl01_total" => 0);
          $ocl_stat[4] = array("ocl01_name" => "服務現況異動", "ocl01_total" => 0);
          $ocl_stat[5] = array("ocl01_name" => "變更基礎資料", "ocl01_total" => 0);
          $ocl_stat[6] = array("ocl01_name" => "照會營養師", "ocl01_total" => 0);
          $ocl_stat[7] = array("ocl01_name" => "其他問題", "ocl01_total" => 0);
          foreach ($ocl_h_row as $k => $v) {
            $ocl01_arr = explode(",", $v['ocl01']);
            if(NULL != $ocl01_arr) {
              for ($i = 0; $i < count($ocl01_arr) ; $i++) { 
                $ocl_stat[$ocl01_arr[$i]]['ocl01_total'] += 1;
              }
            }
          }
          foreach ($ocl_stat as $k => $v) {
            $hist_list .= "<tr> 
                             <td>{$post_data['que_b_date_start']}～{$post_data['que_b_date_end']}</td>
                             <td>{$v['ocl01_name']}</td>
                             <td>{$v['ocl01_total']}</td>
                           </tr>
                          ";
          }
        }
        break;
    }

    if('' == $hist_list) {
      $hist_list .= "<tr><td colspan='99' class='bg-warning text-dark'>查無資料</td></tr>";
    }
    echo $hist_list."</tbody>";
    return;
  }

  function __destruct() {
    $url_str[] = 'be/other_change_log/save';
    $url_str[] = 'be/other_change_log/del';
    $url_str[] = 'be/other_change_log/que_data';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
