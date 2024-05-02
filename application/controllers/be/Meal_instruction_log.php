<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Meal_instruction_log extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('meal_model'); // 餐點資料
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('meal_instruction_log_h_model'); // 餐點異動資料
    $this->load->model('meal_instruction_auth_model'); // 異動單審核紀錄檔
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
    $this->tpl->assign('tv_add_link',be_url().'meal_instruction_log/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."meal_instruction_log/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/meal_instruction_log_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_service_case_link',be_url()."service_case/"); // 開案服務
    $this->tpl->assign('tv_meal_instruction_auth_link',be_url()."meal_instruction_auth/"); // 餐食異動審核
    $this->tpl->assign('tv_que_client_data_link',be_url().'meal_instruction_log/que_client_data/');
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
    if(!isset($get_data['que_type'])) {
      $get_data['que_type'] = NULL;
    }
    if(!isset($get_data['que_mil01_start'])) {
      $get_data['que_mil01_start'] = NULL;
    }
    if(!isset($get_data['que_mil01_end'])) {
      $get_data['que_mil01_end'] = NULL;
    }
    if(!isset($get_data['que_mil02'])) {
      $get_data['que_mil02'] = NULL;
      $get_data['que_mil02_arr'] = NULL;
    }
    else {
      $get_data['que_mil02_arr'] = explode(",", $get_data['que_mil02']);
    }
    list($meal_instruction_log_h_row,$row_cnt) = $this->meal_instruction_log_h_model->get_que($q_str,$pg); // 列出餐點異動資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_title','餐點異動資料瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'meal_instruction_log/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'meal_instruction_log/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'meal_instruction_log/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'meal_instruction_log/upd/');
    $this->tpl->assign('tv_del_link',be_url().'meal_instruction_log/del/');
    $this->tpl->assign('tv_prn_link',be_url().'meal_instruction_log/prn/');
    $this->tpl->assign('tv_que_data_link',be_url().'meal_instruction_log/que_data/');
    $this->tpl->assign('tv_que_link',be_url()."meal_instruction_log/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_type',$get_data['que_type']); // 查詢種類
    $this->tpl->assign('tv_que_mil01_start',$get_data['que_mil01_start']); // 查詢種類
    $this->tpl->assign('tv_que_mil01_end',$get_data['que_mil01_end']); // 查詢種類
    $this->tpl->assign('tv_que_mil02',$get_data['que_mil02']); // 異動查詢類別
    $this->tpl->assign('tv_que_mil02_arr',$get_data['que_mil02_arr']); // 異動查詢類別
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'meal_instruction_log/save/');
    $this->tpl->assign('tv_meal_instruction_log_h_row',$meal_instruction_log_h_row);
    $config['base_url'] = be_url()."meal_instruction_log/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/meal_instruction_log/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

    $this->tpl->display("be/meal_instruction_log.html");
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
    $meal_instruction_log_h_row = $this->meal_instruction_log_h_model->get_one($s_num); // 列出單筆明細資料
    $mil_h_s_num = $meal_instruction_log_h_row->s_num;
    $mil02_arr = explode(",", $meal_instruction_log_h_row->mil02);
    $meal_instruction_log_m_row = $this->meal_instruction_log_h_model->get_m_by_s_num($mil_h_s_num); // 列出餐點異動資料
    $meal_instruction_log_mp_row = $this->meal_instruction_log_h_model->get_mp_by_s_num($mil_h_s_num); // 列出代餐異動資料
    $meal_instruction_log_s_row = $this->meal_instruction_log_h_model->get_s_by_s_num($mil_h_s_num); // 列出停餐異動資料
    $meal_instruction_log_p_row = $this->meal_instruction_log_h_model->get_p_by_s_num($mil_h_s_num); // 列出固定暫停資料
    $meal_instruction_log_i_row = $this->meal_instruction_log_h_model->get_i_by_s_num($mil_h_s_num); // 列出固定暫停資料
    $meal_instruction_log_d_row = $this->meal_instruction_log_h_model->get_d_by_s_num($mil_h_s_num); // 列出補班日出餐(一次性出餐)資料  
    // u_var_dump($meal_instruction_log_h_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_mil02_arr',$mil02_arr);
    $this->tpl->assign('tv_meal_instruction_log_h_row',$meal_instruction_log_h_row);
    $this->tpl->assign('tv_meal_instruction_log_m_row',$meal_instruction_log_m_row);
    $this->tpl->assign('tv_meal_instruction_log_mp_row',$meal_instruction_log_mp_row);
    $this->tpl->assign('tv_meal_instruction_log_s_row',$meal_instruction_log_s_row);
    $this->tpl->assign('tv_meal_instruction_log_p_row',$meal_instruction_log_p_row);
    $this->tpl->assign('tv_meal_instruction_log_i_row',$meal_instruction_log_i_row);
    $this->tpl->assign('tv_meal_instruction_log_d_row',$meal_instruction_log_d_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."meal_instruction_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."meal_instruction_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."meal_instruction_log/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'meal_instruction_log/upd/');
    $this->tpl->display("be/meal_instruction_log_disp.html");
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
    $meals_row = $this->meal_model->get_all(); // 餐點 
    $mil02_arr = array();
    $meal_instruction_log_row = NULL;
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

    $meal_instruction_log_i_row = NULL;
    $meal_instruction_log_d_row = NULL;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_mil02_arr',$mil02_arr);
    $this->tpl->assign('tv_meals_row',$meals_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_meal_instruction_log_row',$meal_instruction_log_row);
    $this->tpl->assign('tv_meal_instruction_log_m_row',(object) $meal_instruction_log_m_row);
    $this->tpl->assign('tv_meal_instruction_log_mp_row',(object) $meal_instruction_log_mp_row);
    $this->tpl->assign('tv_meal_instruction_log_s_row',(object) $meal_instruction_log_s_row);
    $this->tpl->assign('tv_meal_instruction_log_p_row',(object) $meal_instruction_log_p_row);
    $this->tpl->assign('tv_meal_instruction_log_i_row',$meal_instruction_log_i_row);
    $this->tpl->assign('tv_meal_instruction_log_d_row',$meal_instruction_log_d_row);
    $this->tpl->assign('tv_save_link',be_url()."meal_instruction_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."meal_instruction_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."meal_instruction_log/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_sec_link',be_url()."service_case/que_sec"); // 搜尋案主開案資料
    $this->tpl->assign('tv_que_hist_link',be_url()."meal_instruction_log/que_hist/"); // 搜尋異動歷史資料
    $this->tpl->display("be/meal_instruction_log_edit.html");
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
    $meal_instruction_log_row = $this->meal_instruction_log_model->get_one($s_num);
    //u_var_dump($meal_instruction_log_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_meals_row',$meals_row);
    $this->tpl->assign('tv_meal_instruction_log_row',$meal_instruction_log_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."meal_instruction_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."meal_instruction_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."meal_instruction_log/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_sec_link',be_url()."service_case/que_sec"); // 搜尋案主開案資料
    $this->tpl->display("be/meal_instruction_log_edit.html");
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
    $meals_row = $this->meal_model->get_all(); //  餐點資料
    $meal_instruction_log_h_row = $this->meal_instruction_log_h_model->get_one($s_num); // 列出單筆明細資料
    $mil_h_s_num = $meal_instruction_log_h_row->s_num;
    $mil02_arr = explode(",", $meal_instruction_log_h_row->mil02);
    $mil02_str = '';
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
    $meal_instruction_log_i_row = NULL;
    $meal_instruction_log_d_row = NULL;
    foreach ($mil02_arr as $v) {
      switch ($v) {  
        case 1: // 餐點異動
          $meal_instruction_log_m_row = $this->meal_instruction_log_h_model->get_m_by_s_num($mil_h_s_num); // 列出餐點異動資料
          if(NULL != $meal_instruction_log_m_row ) {
            $meal_instruction_log_m_row->mil_m01_1_before_arr = explode(",", $meal_instruction_log_m_row->mil_m01_1_before);
            $meal_instruction_log_m_row->mil_m01_2_before_arr = explode(",", $meal_instruction_log_m_row->mil_m01_2_before);
            $meal_instruction_log_m_row->mil_m01_3_before_arr = explode(",", $meal_instruction_log_m_row->mil_m01_3_before);
            $meal_instruction_log_m_row->mil_m01_4_before_arr = explode(",", $meal_instruction_log_m_row->mil_m01_4_before);
            $meal_instruction_log_m_row->mil_m01_5_before_arr = explode(",", $meal_instruction_log_m_row->mil_m01_5_before);
            $meal_instruction_log_m_row->mil_m01_1_arr = explode(",", $meal_instruction_log_m_row->mil_m01_1);
            $meal_instruction_log_m_row->mil_m01_2_arr = explode(",", $meal_instruction_log_m_row->mil_m01_2);
            $meal_instruction_log_m_row->mil_m01_3_arr = explode(",", $meal_instruction_log_m_row->mil_m01_3);
            $meal_instruction_log_m_row->mil_m01_4_arr = explode(",", $meal_instruction_log_m_row->mil_m01_4);
            $meal_instruction_log_m_row->mil_m01_5_arr = explode(",", $meal_instruction_log_m_row->mil_m01_5);
          }
        break;
        case 2: // 代餐異動
          $meal_instruction_log_mp_row = $this->meal_instruction_log_h_model->get_mp_by_s_num($mil_h_s_num); // 列出代餐異動資料
          if(NULL != $meal_instruction_log_mp_row) {
            $meal_instruction_log_mp_row->mil_mp01_type_before_arr = explode(",", $meal_instruction_log_mp_row->mil_mp01_type_before);
            $meal_instruction_log_mp_row->mil_mp01_type_arr = explode(",", $meal_instruction_log_mp_row->mil_mp01_type);
          }
        break;  
        case 3: // 停復餐異動
          $meal_instruction_log_s_row = $this->meal_instruction_log_h_model->get_s_by_s_num($mil_h_s_num); // 列出停餐異動資料
          if(NULL != $meal_instruction_log_s_row) {
            $meal_instruction_log_s_row->mil_s01_reason_before_arr = explode(",", $meal_instruction_log_s_row->mil_s01_reason_before);
            $meal_instruction_log_s_row->mil_s01_reason_arr = explode(",", $meal_instruction_log_s_row->mil_s01_reason);
          }
        break;  
        case 4: // 固定暫停
          $meal_instruction_log_p_row = $this->meal_instruction_log_h_model->get_p_by_s_num($mil_h_s_num); // 列出固定暫停資料
          if(NULL != $meal_instruction_log_p_row) {
            $meal_instruction_log_p_row->mil_p01_before_arr = explode(",", $meal_instruction_log_p_row->mil_p01_before);
            $meal_instruction_log_p_row->mil_p01_arr = explode(",", $meal_instruction_log_p_row->mil_p01);
          }
        break;  
        case 5: // 自費戶
          $meal_instruction_log_i_row = $this->meal_instruction_log_h_model->get_i_by_s_num($mil_h_s_num); // 列出自費戶
        break;  
        case 6: // 補班日出餐(一次性出餐)
          $meal_instruction_log_d_row = $this->meal_instruction_log_h_model->get_d_by_s_num($mil_h_s_num); // 列出自費戶
        break; 
      } 
      $meal_instruction_log_h_row->mil02_str = $mil02_str;
    }     
    $meal_instruction_log_h_row->mih_cnt = $this->meal_instruction_log_h_model->get_by_sec_s_num($meal_instruction_log_h_row->sec_s_num)->cnt;
    // u_var_dump($meal_instruction_log_row);
    // u_var_dump($meal_instruction_log_mp_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_meals_row',$meals_row);
    $this->tpl->assign('tv_mil02_arr',$mil02_arr);
    $this->tpl->assign('tv_meal_instruction_log_h_row',(object) $meal_instruction_log_h_row);
    $this->tpl->assign('tv_meal_instruction_log_m_row',(object) $meal_instruction_log_m_row);
    $this->tpl->assign('tv_meal_instruction_log_mp_row',(object) $meal_instruction_log_mp_row);
    $this->tpl->assign('tv_meal_instruction_log_s_row',(object) $meal_instruction_log_s_row);
    $this->tpl->assign('tv_meal_instruction_log_p_row',(object) $meal_instruction_log_p_row);
    $this->tpl->assign('tv_meal_instruction_log_i_row',(object) $meal_instruction_log_i_row);
    $this->tpl->assign('tv_meal_instruction_log_d_row',(object) $meal_instruction_log_d_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."meal_instruction_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."meal_instruction_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."meal_instruction_log/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_sec_link',be_url()."service_case/que_sec"); // 搜尋案主開案資料
    $this->tpl->assign('tv_que_hist_link',be_url()."meal_instruction_log/que_hist/"); // 搜尋異動歷史資料
    $this->tpl->display("be/meal_instruction_log_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->meal_instruction_log_h_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'meal_instruction_log/', 'refresh');
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
        $this->meal_instruction_log_h_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->meal_instruction_log_h_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->meal_instruction_log_h_model->save_is_available(); // 上下架儲存
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
    ////redirect(be_url()."meal_instruction_log/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."meal_instruction_log/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
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
  //  函數名稱: que_client_data()
  //  函數功能: 查詢案主資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_client_data()	{
    // 因為可以任意生產選擇哪天的訂單，進而修改過異動的程式加上produce_date
    $v = $this->input->post();
    switch ($v['que_type']) {   
      case 1: // 餐點 
        $data_row = $this->meal_instruction_log_h_model->get_last_m_by_s_num($v['sec_s_num']); 
        break; 
      case 2: // 代餐  
        $data_row = $this->meal_instruction_log_h_model->get_last_mp_by_s_num($v['sec_s_num']); 
        break; 
      case 3: // 停付餐  
        $data_row = $this->meal_instruction_log_h_model->get_last_s_by_s_num($v['sec_s_num']); 
        break; 
      case 4: // 固定暫停      
        $data_row = $this->meal_instruction_log_h_model->get_last_p_by_s_num($v['sec_s_num']); 
        break; 
      case 5: // 自費       
        $data_row = $this->meal_instruction_log_h_model->get_last_i_by_s_num($v['sec_s_num']); 
        break; 
    }
    echo json_encode($data_row);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: que_hist()
  //  函數功能: 查詢案主資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_hist($type)	{
    // 因為可以任意生產選擇哪天的訂單，進而修改過異動的程式加上produce_date
    $v = $this->input->post();
    $hist_list = "";
    
    switch ($type) {   
      case "meal": // 餐點 
        $data_row = $this->meal_instruction_log_h_model->get_all_m_by_sec($v['sec_s_num']); 
        foreach ($data_row as $k => $v) {
            // 訂單內容調整 Begin
            $hist_list .= "<tr class='get_hist cursorpointer' 
                               data-dismiss='modal'
                               data-type='{$type}'
                               data-ml_s_num='{$v['ml_s_num']}'
                               data-mil_m01_1='{$v['mil_m01_1']}'
                               data-mil_m01_2='{$v['mil_m01_2']}'
                               data-mil_m01_3='{$v['mil_m01_3']}'
                               data-mil_m01_4='{$v['mil_m01_4']}'
                               data-mil_m01_5='{$v['mil_m01_5']}'
                           >
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_m02']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['ml01']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_m01_1_str']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_m01_2_str']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_m01_3_str']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_m01_4_str']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_m01_5_str']}</td>
                           </tr>
                          ";
        }
      break; 
      case "meal_replacement": // 代餐  
        $data_row = $this->meal_instruction_log_h_model->get_all_mp_by_sec($v['sec_s_num']); 
        if(NULL != $data_row) {
          foreach ($data_row as $k => $v) {
            $hist_list .= "<tr class='get_hist cursorpointer' 
                               data-dismiss='modal'
                               data-type='{$type}'
                               data-mil_mp01='{$v['mil_mp01']}' 
                               data-mil_mp01_type='{$v['mil_mp01_type']}' > 
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_mp02']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_mp01_str']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_mp01_type_str']}</td>
                           </tr>
                          ";
          }
        }
      break; 
      case "stop": // 停付餐  
        $data_row = $this->meal_instruction_log_h_model->get_all_s_by_sec($v['sec_s_num']); 
        if(NULL != $data_row) {
          foreach ($data_row as $k => $v) {
            $hist_list .= "<tr class='get_hist cursorpointer' 
                               data-dismiss='modal'
                               data-type='{$type}'
                               data-mil_s01='{$v['mil_s01']}' 
                               data-mil_s01_reason='{$v['mil_s01_reason']}' > 
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_s02']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_s01_str']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_s01_reason_str']}</td>
                           </tr>
                          ";
          }
        }
      break; 
      case "pause": // 固定暫停      
        $data_row = $this->meal_instruction_log_h_model->get_all_p_by_sec($v['sec_s_num']); 
        foreach ($data_row as $k => $v) {
            $hist_list .= "<tr class='get_hist cursorpointer' 
                                       data-dismiss='modal'
                                       data-type='{$type}'
                                       data-mil_p01='{$v['mil_p01']}'>
                                     <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_p02']}</td>
                                     <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_p01_str']}</td>
                                   </tr>
                                  ";
        }
      break; 
      case "identity": // 自費       
        $data_row = $this->meal_instruction_log_h_model->get_all_i_by_sec($v['sec_s_num']); 
        foreach ($data_row as $k => $v) {
            $hist_list .= "<tr class='get_hist cursorpointer' 
                               data-dismiss='modal'
                               data-type='{$type}'
                               data-mil_i01='{$v['mil_i01']}' 
                               data-mil_i01_reason='{$v['mil_i01_reason']}' > 
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_i02']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_i01_str']}</td>
                             <td data-toggle='tooltip' data-placement='top' title='選此異動紀錄'>{$v['mil_i01_reason']}</td>
                           </tr>
                          ";
        }
      break; 
    }

    if('' == $hist_list) {
      $hist_list .= "<tr><td colspan='99' class='bg-warning text-dark'>查無資料</td></tr>";
    }
    echo $hist_list;
    return;
  }
  
  // **************************************************************************
  //  函數名稱: que_data()
  //  函數功能: 查詢異動資料、停餐名單
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_data() {
    $hist_list = '';
    $post_data = $this->input->post();
    switch($post_data['que_type']) {
      case 1: // 停餐名單
        $data_row = $this->meal_instruction_log_h_model->get_stop_by_date(); 
        $hist_list = '<thead>
                        <tr class="thead-light">
                          <th style="width: 10%">停餐日期</th>
                          <th style="width: 10%">路線</th>
                          <th style="width: 10%">個案名稱</th>
                          <th style="width: 10%">服務現況</th>
                          <th style="width: 10%">餐別</th>
                        </tr>
                      </thead>
                      <tbody>
                     ';
        if(NULL != $data_row) {
          foreach ($data_row as $k => $v) {
            $reh01 = '';
            $route_row = $this->route_model->que_client_route($v['reh_type'] , $v['ct_s_num']);
            if(NULL != $route_row) {
              $reh01 = $route_row->reh01;
            }
            $hist_list .= "<tr> 
                             <td>{$v['mil01']}</td>
                             <td>{$reh01}</td>
                             <td>{$v['ct01']}{$v['ct02']}</td>
                             <td>{$v['sec01_str']}</td>
                             <td>{$v['sec04_str']}</td>
                           </tr>
                          ";
          }
        }
        break;
      case 2: // 異動統計
        $mil_h_row = $this->meal_instruction_log_h_model->get_all_by_mil03(); 
        $hist_list = '<thead>
                        <tr class="thead-light">
                          <th style="width: 33.3%">生效日期區間</th>
                          <th style="width: 33.3%">異動項目</th>
                          <th style="width: 33.3%">筆數</th>
                        </tr>
                      </thead>
                      <tbody>
                     ';
        if(NULL != $mil_h_row) {
          $mil_stat[1] = array("mil02_name" => "餐點異動", "mil02_total" => 0);
          $mil_stat[2] = array("mil02_name" => "代餐異動", "mil02_total" => 0);
          $mil_stat[3] = array("mil02_name" => "停復餐異動", "mil02_total" => 0);
          $mil_stat[4] = array("mil02_name" => "固定暫停異動", "mil02_total" => 0);
          $mil_stat[5] = array("mil02_name" => "自費異動", "mil02_total" => 0);
          $mil_stat[6] = array("mil02_name" => "一次性出餐異動", "mil02_total" => 0);
          foreach ($mil_h_row as $k => $v) {
            $mil02_arr = explode(",", $v['mil02']);
            if(NULL != $mil02_arr) {
              for ($i = 0; $i < count($mil02_arr) ; $i++) { 
                $mil_stat[$mil02_arr[$i]]['mil02_total'] += 1;
              }
            }
          }
          foreach ($mil_stat as $k => $v) {
            $hist_list .= "<tr> 
                             <td>{$post_data['que_mil01_stop_start']}～{$post_data['que_mil01_stop_end']}</td>
                             <td>{$v['mil02_name']}</td>
                             <td>{$v['mil02_total']}</td>
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
    $url_str[] = 'be/meal_instruction_log/save';
    $url_str[] = 'be/meal_instruction_log/del';
    $url_str[] = 'be/meal_instruction_log/que_client_data';
    $url_str[] = 'be/meal_instruction_log/que_hist';
    $url_str[] = 'be/meal_instruction_log/que_data';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}