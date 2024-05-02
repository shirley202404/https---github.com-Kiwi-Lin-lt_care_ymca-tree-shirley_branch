<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_case extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->library('user_agent');
    $this->load->model('service_case_model'); // 開案服務資料
    $this->load->model('meal_model'); // 餐點資料
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('meal_instruction_log_h_model'); // 異動資料
    $this->load->model('meal_instruction_auth_model'); // 異動單審核紀錄檔
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','開結案服務');
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
    $this->tpl->assign('tv_stamp_btn',$this->lang->line('stamp')); // 蓋章表按鈕文字
    $this->tpl->assign('tv_add_link',be_url().'service_case/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."service_case/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
    $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
    $this->tpl->assign('tv_over_ok',$this->lang->line('over_ok')); // 結案成功!!
    $this->tpl->assign('tv_over_cancel_ok',$this->lang->line('over_cancel_ok')); // 取消結案成功!!
    $this->tpl->assign('tv_import_ok',$this->lang->line('import_ok')); // 匯入資料成功!!
    $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
    $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
    $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
    $this->tpl->assign('tv_over_ng',$this->lang->line('over_ng')); // 結案失敗!!
    $this->tpl->assign('tv_over_cancel_ng',$this->lang->line('over_cancel_ng')); // 取消結案失敗!!
    $this->tpl->assign('tv_import_ng',$this->lang->line('import_ng')); // 匯入資料失敗!!
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/service_case_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
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
    $meals_row = $this->meal_model->get_all(); // 餐點 
    $route_row = $this->route_model->get_all(); // 路線
    list($service_case_row, $row_cnt, $sec03_n_row_cnt, $sec03_y_row_cnt) = $this->service_case_model->get_que($q_str,$pg); // 列出開案服務資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','開案服務資料瀏覽');
    // $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'service_case/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'service_case/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'service_case/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'service_case/upd/');
    $this->tpl->assign('tv_del_link',be_url().'service_case/del/');
    $this->tpl->assign('tv_over_link',be_url().'service_case/over/');
    $this->tpl->assign('tv_prn_link',be_url().'service_case/prn/');
    $this->tpl->assign('tv_over_cancel_link',be_url().'service_case/over_cancel/');
    $this->tpl->assign('tv_index2_link',be_url().'service_case/index2/');
    $this->tpl->assign('tv_que_link',be_url()."service_case/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'service_case/save/');
    $this->tpl->assign('tv_download_link',be_url().'service_case/download/'); // 蓋章表下載
    $this->tpl->assign('tv_download3_link',be_url().'service_case/download3/'); // 開結案資料下載
    $this->tpl->assign('tv_upd_price_link',be_url().'service_case/save/upd_price'); // 餐點價格更新
    $this->tpl->assign('tv_service_case_row',$service_case_row);
    $this->tpl->assign('tv_meal_row',$meals_row);
    $this->tpl->assign('tv_route_row',$route_row);
    $config['base_url'] = be_url()."service_case/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/service_case/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->assign('tv_sec03_n_row_cnt',$sec03_n_row_cnt);
    $this->tpl->assign('tv_sec03_y_row_cnt',$sec03_y_row_cnt);
    $this->tpl->display("be/service_case.html");
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
    $tab = 'sec';
    $sec_start_month = NULL;
    $que_start_month = NULL;
    $sec_end_month = NULL;
    $que_end_month = NULL;
    $get_data = $this->input->get();
    if(isset($get_data['tab'])) {
      $tab = 'chart';
    }
    $service_case_row = $this->service_case_model->get_one($s_num); // 列出單筆明細資料
    $meal_order_row = $this->meal_order_model->get_meal_order($s_num); // 列出單筆明細資料
    $meal_order_stat_row = $this->meal_order_model->get_stats_data_by_sec_s_num($s_num); // 列出服務案每月統計出餐資料

    if(NULL != $meal_order_stat_row) {
      $i = 1;
      foreach ($meal_order_stat_row as $k => $v) {
        if(1 == $i) {
          $sec_start_month = "{$v['year']}-".str_pad($v['month'], 2, "0", STR_PAD_LEFT);
        }
        if(count($meal_order_stat_row) == $i) {
          $sec_end_month = "{$v['year']}-".str_pad($v['month'], 2, "0", STR_PAD_LEFT);
        }
        $i++;
      }
    }
    if(isset($get_data['que_start_month'])) {
      $que_start_month = $get_data['que_start_month'];
    }
    else {
      $que_start_month = $sec_start_month;
    }
    if(isset($get_data['que_end_month'])) {
      $que_end_month = $get_data['que_end_month'];
    }
    else {
      $que_end_month = Date("Y-m", strtotime("{$que_start_month} +6 Month -1 Day"));
      if(strtotime($que_end_month) > strtotime($sec_end_month)) {
        $que_end_month = $sec_end_month;
      }
    }
    $meal_order_stat_row = $this->meal_order_model->get_stats_data_by_sec_s_num($s_num, $que_start_month, $que_end_month); // 列出服務案每月統計出餐資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_tab',$tab);
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_sec_end_month',$sec_end_month);
    $this->tpl->assign('tv_sec_start_month',$sec_start_month);
    $this->tpl->assign('tv_que_end_month',$que_end_month);
    $this->tpl->assign('tv_que_start_month',$que_start_month);
    $this->tpl->assign('tv_service_case_row',$service_case_row);
    $this->tpl->assign('tv_meal_order_row',$meal_order_row);
    $this->tpl->assign('tv_meal_order_stat_row',$meal_order_stat_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_disp_link',be_url()."service_case/disp/{$s_num}");
    $this->tpl->assign('tv_save_link',be_url()."service_case/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'service_case/upd/');
    $this->tpl->display("be/service_case_disp.html");
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
    $service_case_row = (object) array('sec03' => NULL);
    $route_row = $this->route_model->get_all();
    $meals_row = $this->meal_model->get_all(); // 餐點 
    $meal_instruction_log_m_row['mil_m01_1_before_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_2_before_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_3_before_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_4_before_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_5_before_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_1_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_2_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_3_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_4_arr'] = array();
    $meal_instruction_log_m_row['mil_m01_5_arr'] = array();
    $meal_instruction_log_mp_row['mil_mp01_type_before_arr'] = array();
    $meal_instruction_log_mp_row['mil_mp01_type_arr'] = array();
    $meal_instruction_log_s_row['mil_s01_reason_before_arr'] = array();
    $meal_instruction_log_s_row['mil_s01_reason_arr'] = array();
    $meal_instruction_log_p_row['mil_p01_before_arr'] = array();
    $meal_instruction_log_p_row['mil_p01_arr'] = array();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_meals_row',$meals_row);
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_service_case_row',$service_case_row);
    $this->tpl->assign('tv_meal_instruction_log_m_row',(object) $meal_instruction_log_m_row);
    $this->tpl->assign('tv_meal_instruction_log_mp_row',(object) $meal_instruction_log_mp_row);
    $this->tpl->assign('tv_meal_instruction_log_s_row',(object) $meal_instruction_log_s_row);
    $this->tpl->assign('tv_meal_instruction_log_p_row',(object) $meal_instruction_log_p_row);
    $this->tpl->assign('tv_save_link',be_url()."service_case/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_route_link',be_url()."route/upd/"); // 上一層連結位置
    $this->tpl->display("be/service_case_edit.html");
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
    $service_case_row = $this->service_case_model->get_one($s_num);
    //u_var_dump($service_case_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_service_case_row',$service_case_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."service_case/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->display("be/service_case_edit.html");
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
    $service_case_row = $this->service_case_model->get_one($s_num);
    // u_var_dump($service_case_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_service_case_row',$service_case_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."service_case/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_route_link',be_url()."route/upd/"); // 上一層連結位置
    $this->tpl->display("be/service_case_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function del($s_num=NULL) {
    $rtn_url = $this->agent->referrer();
    $rtn_msg = $this->service_case_model->del($s_num); // 刪除
    if($rtn_msg == 'ok') {
      echo json_encode(array('rtn_msg' => $rtn_msg , 'rtn_url' => $rtn_url));
      // redirect(be_url().'service_case/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: over()
  //  函數功能: 結案
  //  程式設計: kiwi
  //  設計日期: 2021-04-09
  // **************************************************************************
  public function over($s_num=NULL) {
    $rtn_url = $this->agent->referrer();
    $rtn_msg = $this->service_case_model->over($s_num); // 結案
    if($rtn_msg) {
      echo json_encode(array('rtn_msg' => $rtn_msg , 'rtn_url' => $rtn_url));
      // redirect(be_url().'service_case/', 'refresh');
    }
    else {
      die($rtn_msg); // 結案失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: over_cancel()
  //  函數功能: 結案取消
  //  程式設計: kiwi
  //  設計日期: 2022-01-24
  // **************************************************************************
  public function over_cancel($s_num=NULL) {
    $rtn_url = $this->agent->referrer();
    $rtn_msg = $this->service_case_model->over_cancel($s_num); // 結案
    if($rtn_msg) {
      echo json_encode(array('rtn_msg' => $rtn_msg , 'rtn_url' => $rtn_url));
      // redirect(be_url().'service_case/', 'refresh');
    }
    else {
      die($rtn_msg); // 結案失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: index2()
  //  函數功能: 固定暫停表
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function index2() {
    $msel = 'list';
    $get_data = $this->input->get();
    $service_case_row = $this->service_case_model->que_by_pause();
    if(NULL != $service_case_row) {
      foreach ($service_case_row as $k => $v) {
        $service_case_row[$k]['week1'] = "N";
        $service_case_row[$k]['week2'] = "N";
        $service_case_row[$k]['week3'] = "N";
        $service_case_row[$k]['week4'] = "N";
        $service_case_row[$k]['week5'] = "N";
        $meal_instruction_log_p_row = $this->meal_instruction_log_h_model->get_last_p_by_s_num($v['s_num'] , date("Y-m-d")); // 取得最後一筆固定暫停資料
        if(NULL != $meal_instruction_log_p_row) {
          $mil_p01_arr = explode(",", $meal_instruction_log_p_row->mil_p01);
          if(!empty($_GET['que_day'])) {
            if(!in_array($_GET['que_day'], $mil_p01_arr)) {
              unset($service_case_row[$k]);
              continue;
            }
          }
          for ($i = 0; $i <count($mil_p01_arr) ; $i++) { 
            $service_case_row[$k]["week{$mil_p01_arr[$i]}"] = "Y";
          }
        }
        else {
          if(!empty($_GET['que_day'])) {
            unset($service_case_row[$k]);
          }
        }
      }
    }
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_service_case_row',$service_case_row);
    $this->tpl->assign('tv_que_ct_name',$get_data['que_ct_name']);
    $this->tpl->assign('tv_que_ct14',$get_data['que_ct14']);
    $this->tpl->assign('tv_que_sec01',$get_data['que_sec01']);
    $this->tpl->assign('tv_que_day',$get_data['que_day']);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case/"); // 上一層連結位置
    $this->tpl->assign('tv_index2_link',be_url().'service_case/index2/');
    $this->tpl->assign('tv_download_link',be_url().'service_case/download2/');
    $this->tpl->display("be/service_case_index2.html");
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
        $this->service_case_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->service_case_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->service_case_model->save_is_available(); // 上下架儲存
        break;
      case "upd_price":
        $this->service_case_model->save_upd_price(); // 餐點價格更新
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
    ////redirect(be_url()."service_case/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."service_case/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
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
  //  函數名稱: que_sec()
  //  函數功能: 查詢案主服務資料,顯示下拉選取
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_sec() {
    $service_case_row = $this->service_case_model->que_sec(); // 搜尋案主開案資料
    foreach ($service_case_row as $k => $v) {
      $mil_h_row = $this->meal_instruction_log_h_model->get_by_sec_s_num($v['s_num']);
      $service_case_row[$k]['mih_cnt'] = $mil_h_row->cnt;
    } 
    echo json_encode($service_case_row);
    return;
  }
  // **************************************************************************
  //  函數名稱: que_client_service_data()
  //  函數功能: 查詢案主資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_client_service_data()	{
    $this->service_case_model->que_client_service_data();
    return;
  }
  
  function __destruct() {
    $url_str[] = 'be/service_case/save';
    $url_str[] = 'be/service_case/del';
    $url_str[] = 'be/service_case/over';
    $url_str[] = 'be/service_case/over_cancel';
    $url_str[] = 'be/service_case/que_sec';
    $url_str[] = 'be/service_case/que_client_service_data';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
