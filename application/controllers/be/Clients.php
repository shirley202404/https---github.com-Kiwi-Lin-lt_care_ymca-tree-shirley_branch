<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('beacon_model'); // Beacon資料
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('service_case_model'); // 開案服務資料
    $this->load->model('other_change_log_h_model'); // 餐點異動資料
    $this->load->model('meal_instruction_log_h_model'); // 餐點異動資料
    $this->load->model('home_interview_model'); // 家訪紀錄資料
    $this->load->model('phone_interview_model'); // 電訪紀錄資料
    $this->load->model('clients_hlth_normal_model'); // 營養評估表資料
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
    $this->tpl->assign('tv_add_link',be_url().'clients/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."clients/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/clients_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_chk_ct03_link', be_url()."clients/chk_ct03");
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
    list($clients_row, $row_cnt, $is_available_1_row_cnt, $is_available_0_row_cnt) = $this->clients_model->get_que($q_str,$pg); // 列出案主資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','案主資料瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'clients/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'clients/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'clients/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'clients/upd/');
    $this->tpl->assign('tv_del_link',be_url().'clients/del/');
    $this->tpl->assign('tv_prn_link',be_url().'clients/prn/');
    $this->tpl->assign('tv_que_link',be_url()."clients/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'clients/save/');
    $this->tpl->assign('tv_clients_row',$clients_row);
    $config['base_url'] = be_url()."clients/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/clients/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->assign('tv_is_available_1_row_cnt',$is_available_1_row_cnt);
    $this->tpl->assign('tv_is_available_0_row_cnt',$is_available_0_row_cnt);

    $this->tpl->display("be/clients.html");
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
    $clients_row = $this->clients_model->get_one($s_num); // 列出單筆明細資料
    $route_row = $this->route_model->get_all_by_ct($s_num);
    $clients_identity_row = $this->clients_model->get_identity_log($s_num); // 列出案主身別案異動紀錄
    $service_case_row = $this->service_case_model->get_que_by_ct($s_num);
    $home_interview_row = $this->home_interview_model->get_all_by_ct_s_num($s_num, $limit=3); // 家訪紀錄
    $phone_interview_row = $this->phone_interview_model->get_all_by_ct_s_num($s_num, $limit=3); // 電訪記錄
    $clients_hlth_normal_row = $this->clients_hlth_normal_model->get_all_by_ct_s_num($s_num, $limit=3); // 營養評估表

    if(NULL != $clients_row->ct95) {
      $clients_row->ct95_base64 = $this->zi_my_func->img_to_base64('clients', $clients_row->ct95);
    } 
    if(NULL != $clients_row->ct96) {
      $clients_row->ct96_base64 = $this->zi_my_func->img_to_base64('clients', $clients_row->ct96);
    } 
    
    if($service_case_row != NULL) {
      foreach ($service_case_row as $k => $v) {
        $service_case_row = $this->service_case_model->get_que_by_ct($s_num);
        if($service_case_row != NULL) {
          foreach ($service_case_row as $k => $v) {
            $service_case_row[$v['s_num']]["m"] = $this->meal_instruction_log_h_model->get_all_m_by_sec($v['s_num']); // 取得最後一筆餐點異動資料
            $service_case_row[$v['s_num']]["mp"] = $this->meal_instruction_log_h_model->get_all_mp_by_sec($v['s_num']); // 取得最後一筆代餐異動資料
            $service_case_row[$v['s_num']]["s"] = $this->meal_instruction_log_h_model->get_all_s_by_sec($v['s_num']); // 取得最後一筆停餐異動資料
            $service_case_row[$v['s_num']]["p"] = $this->meal_instruction_log_h_model->get_all_p_by_sec($v['s_num']); // 取得最後一筆固定暫停資料
            $service_case_row[$v['s_num']]["i"] = $this->meal_instruction_log_h_model->get_all_i_by_sec($v['s_num']); // 取得最後一筆固定暫停資料
            $service_case_row[$v['s_num']]["d"] = $this->meal_instruction_log_h_model->get_all_d_by_sec($v['s_num']); // 取得最後一筆固定暫停資料
          }
        }
      }
    }
    
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients/save/{$msel}");
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_clients_row',$clients_row);
    $this->tpl->assign('tv_service_case_row',$service_case_row);
    $this->tpl->assign('tv_home_interview_row',$home_interview_row);
    $this->tpl->assign('tv_phone_interview_row',$phone_interview_row);
    $this->tpl->assign('tv_clients_identity_row',$clients_identity_row);
    $this->tpl->assign('tv_clients_hlth_normal_row',$clients_hlth_normal_row);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'clients/upd/');
    $this->tpl->assign('tv_del_identity_link',be_url().'clients/del_identity/');
    $this->tpl->assign('tv_home_interview_link',be_url().'home_interview/?que_ct_s_num=');
    $this->tpl->assign('tv_home_interview_disp_link',be_url().'home_interview/disp/');
    $this->tpl->assign('tv_phone_interview_link',be_url().'phone_interview/?que_ct_s_num=');
    $this->tpl->assign('tv_phone_interview_disp_link',be_url().'phone_interview/disp/');
    $this->tpl->assign('tv_clients_hlth_normal_link',be_url().'clients_hlth_normal/?que_ct_s_num=');
    $this->tpl->assign('tv_clients_hlth_normal_disp_link',be_url().'clients_hlth_normal/disp/');
    $this->tpl->assign('tv_meal_instruction_log_link',be_url().'meal_instruction_log/?que_sec_s_num=');
    $this->tpl->display("be/clients_disp.html");
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
    // u_var_dump($clients_row);
    $clients_row['ct21_arr'] = array();
    $clients_row['ct31_arr'] = array();
    $clients_row['ct34_go_arr'] = array();
    $clients_row['ct34_fo_arr'] = array();
    $clients_row['ct35_type_arr'] = array();
    $clients_row['ct37_arr'] = array();
    $clients_row['ct38_1_arr'] = array();
    $clients_row['ct38_2_arr'] = array();

    $clients_row = (object) $clients_row;
    $beacon_row = $this->beacon_model->get_all();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_beacon_row',$beacon_row);
    $this->tpl->assign('tv_clients_row',$clients_row);
    $this->tpl->assign('tv_save_link',be_url()."clients/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients/"); // 上一層連結位置
    $this->tpl->assign('tv_route_link',be_url()."route/upd/"); // 上一層連結位置
    $this->tpl->display("be/clients_edit.html");
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
    $clients_row = $this->clients_model->get_one($s_num);
    $beacon_row = $this->beacon_model->get_all();
    //u_var_dump($clients_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_row',$clients_row);
    $this->tpl->assign('tv_beacon_row',$beacon_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients/"); // 上一層連結位置
    $this->tpl->display("be/clients_edit.html");
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
    $clients_row = $this->clients_model->get_one($s_num);
    // u_var_dump($clients_row);
    $clients_row->ct21_arr = explode(",", $clients_row->ct21);
    $clients_row->ct31_arr = explode(",", $clients_row->ct31);
    $clients_row->ct34_go_arr = explode(",", $clients_row->ct34_go);
    $clients_row->ct34_fo_arr = explode(",", $clients_row->ct34_fo);
    $clients_row->ct35_type_arr = explode(",", $clients_row->ct35_type);
    $clients_row->ct37_arr = explode(",", $clients_row->ct37);
    $clients_row->ct38_1_arr = explode(",", $clients_row->ct38_1);
    $clients_row->ct38_2_arr = explode(",", $clients_row->ct38_2);
    $beacon_row = $this->beacon_model->get_all();
    // u_var_dump($clients_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_row',$clients_row);
    $this->tpl->assign('tv_beacon_row',$beacon_row);
    $this->tpl->assign('tv_disp_link',be_url().'clients/disp/');
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients/"); // 上一層連結位置
    $this->tpl->assign('tv_other_change_log',be_url()."other_change_log/add?que_ct_s_num={$s_num}"); // 上一層連結位置
    $this->tpl->display("be/clients_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->clients_model->del($s_num); // 刪除
    $this->zi_my_func->web_api_data("client", "del");
    if($rtn_msg) {
      redirect(be_url().'clients/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: del_identity()
  //  函數功能: 刪除案主身分別資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function del_identity($s_num=NULL) {
    $this->load->library('user_agent');
    $rtn_url = $this->agent->referrer();
    $rtn_msg = $this->clients_model->del_identity(); // 刪除
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
        $this->clients_model->save_add(); // 新增儲存
        break;
      case "upd":
        $fd_msel = "upd";
        $this->clients_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $fd_msel = "stop";
        $this->clients_model->save_is_available(); // 上下架儲存
        break;
    }
    $this->zi_my_func->web_api_data("client", $fd_msel);
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
    ////redirect(be_url()."clients/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."clients/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
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
  //  函數名稱: que_ct()
  //  函數功能: 查詢案主資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
	public function que_ct()	{
	  $rtn_msg = $this->clients_model->que_ct();
	  echo $rtn_msg;
	  return;
  }
  // **************************************************************************
  //  函數名稱: que_ct_disp()
  //  函數功能: 查詢案主資料
  //  程式設計: kiwi
  //  設計日期: 2023-02-28
  // **************************************************************************
  public function que_ct_disp()	{
    $clients_row = $this->clients_model->get_one($_POST['ct_s_num']);
    echo json_encode($clients_row);
    return;
  }
  // **************************************************************************
  //  函數名稱: que_client_data()
  //  函數功能: 查詢案主資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_client_data()	{
    $clients_row = $this->clients_model->que_client_data();
    if(1 == $_POST['que_type']) {
      if(NULL != $clients_row) {
        $ct_identity_row = $this->clients_model->get_identity_log_latest($_POST['ct_s_num'], date("Y-m-d"));
        $service_case_row = $this->service_case_model->get_que_by_ct($_POST['ct_s_num']);
        $clients_row->ct34_go = $ct_identity_row->ct_il02;
        if(NULL != $service_case_row) {
          foreach ($service_case_row as $k => $v) {
            $clients_row->sec09 = $v['sec09'];
          }
        }
      }
    }
    echo json_encode($clients_row);
    return;
  }
  // **************************************************************************
  //  函數名稱: chk_ct03()
  //  函數功能: 查詢案主資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function chk_ct03()	{
    $clients_row = $this->clients_model->chk_ct03();
    if($clients_row != NULL) {
      echo "false";
      return;
    }
    echo "true";
    return;
  }
  // **************************************************************************
  //  函數名稱: address_convert()
  //  函數功能: 地址轉換
  //  程式設計: Kiwi
  //  設計日期: 2021/06/28
  // **************************************************************************
  public function address_convert() {
    $v = $this->input->post();
    $latitude = 0;
    $longitude = 0;
    //地址轉碼
    $address = urlencode($v['address']);
    $address = $v['address'];
    $map_api_key = 'AIzaSyA7M7Hqze-Zf-0D4UmC4iCt8YpeIEiJ7h8';
    $url = "https://maps.googleapis.com/maps/api/geocode/json?key={$map_api_key}&sensor=true&language=zh-TW&region=tw&address={$address}";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    $response = json_decode(curl_exec($curl), true);

    if ($response['status'] == 'OK') {
      //取得需要的重要資訊
      $latitude = $response['results'][0]['geometry']['location']['lat']; // 緯度
      $longitude = $response['results'][0]['geometry']['location']['lng']; // 經度
    }
    echo json_encode(array("latitude"=>$latitude , "longitude"=>$longitude));
    return;
  }
  // **************************************************************************
  //  函數名稱: reh_que_ct()
  //  函數功能: 查詢案主資料(路徑資料用)
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function reh_que_ct() {
    $clients_row = $this->clients_model->reh_que_ct();
    $rtn_str = '';
    if(NULL != $clients_row) {
      foreach ($clients_row as $k => $v) {
        $snum = $v['s_num'];
        $date = date_create($v['ct05']);
        $date_str = date_format($date, 'Y-m-d');
        $rtn_str .= "<tr id='route_b_clone_tr_{$snum}' class='route_b_clone_tr reb_clone_item' data-item='{$snum}'>";
        $rtn_str .= "  <td class='text-left'>";
        $rtn_str .= "    <span class='form-group'>";
        $rtn_str .= "      <span id='ct_name_str_clone_{$snum}'>{$v['ct01']}{$v['ct02']}</span>";
        $rtn_str .= "      <input type='hidden' id='ct_s_num_clone_{$snum}' value='{$v['s_num']}'>";
        $rtn_str .= "      <input type='hidden' id='ct_name_clone_{$snum}' value='{$v['ct01']}{$v['ct02']}'>";
        $rtn_str .= "    </span>";
        $rtn_str .= "  </td>";
        $rtn_str .= "  <td class='text-left'>";
        $rtn_str .= "    <span class='form-group'>";
        $rtn_str .= "      <span>{$v['ct03']}</span>";
        $rtn_str .= "    </span>";
        $rtn_str .= "  </td>";
        $rtn_str .= "  <td class='text-left'>";
        $rtn_str .= "    <span class='form-group'>";
        $rtn_str .= "      <span>{$date_str}</span>";
        $rtn_str .= "    </span>";
        $rtn_str .= "  </td>";
        $rtn_str .= "  <td class='text-left'>";
        $rtn_str .= "    <span class='form-group'>";
        $rtn_str .= "      <span class='form-control form-control-sm'>";
        $rtn_str .= "        <input type='radio' name='is_new_clone_{$snum}' value='Y'> 未知";
        $rtn_str .= "        <input type='radio' name='is_new_clone_{$snum}' value='N' checked> 已知";
        $rtn_str .= "      </span>";
        $rtn_str .= "    </span>";
        $rtn_str .= "  </td>";
        $rtn_str .= "</tr>";
        $snum++;
      }
    }
    else {
      $rtn_str .= "<tr>";
      $rtn_str .= "  <td colspan='99' class='alert alert-warning'>";
      $rtn_str .= "    查無資料!!!";
      $rtn_str .= "  </td>";
      $rtn_str .= "</tr>";
    }
    echo json_encode($rtn_str);
    return;
  }

  function __destruct() {
    $url_str[] = 'be/clients/save';
    $url_str[] = 'be/clients/del';
    $url_str[] = 'be/clients/que_ct';
    $url_str[] = 'be/clients/que_ct_disp';
    $url_str[] = 'be/clients/reh_que_ct';
    $url_str[] = 'be/clients/que_client_data';
    $url_str[] = 'be/clients/chk_ct03';
    $url_str[] = 'be/clients/address_convert';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}