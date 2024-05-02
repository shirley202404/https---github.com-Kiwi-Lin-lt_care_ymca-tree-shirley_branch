<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Other_change_auth extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('social_worker_model'); // 社工基本資料檔
    $this->load->model('other_change_log_h_model'); // 餐點異動資料
    $this->load->model('other_change_auth_model'); // 異動單審核紀錄檔
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','異動單審核紀錄檔');
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
    $this->tpl->assign('tv_add_link',be_url().'other_change_auth/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."other_change_auth/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/other_change_auth_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
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
  //  設計日期: 2021-01-04
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
    $is_check = "unchecked";
    $action['checked'] = '';
    $action['unchecked'] = "active"; 
    if(isset($get_data['que_check_type'])) {
      if($get_data['que_check_type'] == 'checked') {
        $is_check = "checked";
        $action['checked'] = "active"; 
        $action['unchecked'] = ""; 
      }
      if($get_data['que_check_type'] == 'unchecked') {
        $is_check = "unchecked";
        $action['unchecked'] = "active"; 
      }
    }
    
    list($other_change_auth_row,$row_cnt) = $this->other_change_auth_model->get_que($q_str,$pg); // 列出異動單審核紀錄檔
    // u_var_dump($other_change_auth_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','異動單審核紀錄檔瀏覽');
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'other_change_auth/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'other_change_auth/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'other_change_auth/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'other_change_auth/upd/');
    $this->tpl->assign('tv_del_link',be_url().'other_change_auth/del/');
    $this->tpl->assign('tv_prn_link',be_url().'other_change_auth/prn/');
    $this->tpl->assign('tv_que_link',be_url()."other_change_auth/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'other_change_auth/save/');
    $this->tpl->assign('tv_sw_auth_link',be_url().'other_change_auth/sw_auth/'); // 社工確認
    $this->tpl->assign('tv_wo_auth_link',be_url().'other_change_auth/wo_auth/'); // 核銷人員確認
    $this->tpl->assign('tv_me_auth_link',be_url().'other_change_auth/me_auth/'); // 製餐確認
    $this->tpl->assign('tv_checked_link',be_url().'other_change_auth/?que_check_type=checked');
    $this->tpl->assign('tv_unchecked_link',be_url().'other_change_auth/');
    $this->tpl->assign('tv_other_change_auth_row',$other_change_auth_row);
    $this->tpl->assign('tv_action',$action);
    $this->tpl->assign('tv_is_check',$is_check);
    $config['base_url'] = be_url()."other_change_auth/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/other_change_auth/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

    $this->tpl->display("be/other_change_auth.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $other_change_auth_row = $this->other_change_auth_model->get_one($s_num); // 列出單筆明細資料
    $ocl_s_num = $other_change_auth_row->ocl_s_num;
    $other_change_log_h_row = $this->other_change_log_h_model->get_one($ocl_s_num); // 列出單筆明細資料
    $ocl01_arr = explode(",", $other_change_log_h_row->ocl01);
    $ocl01_str = '';
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
            if($ocl01_str != '') {
              $ocl01_str .= "、身分別異動";
            }
            else {
              $ocl01_str .= "身分別異動";
            }
          }
        break;
        case 2: // 失能等級異動
          $other_change_log_disabled_row = $this->other_change_log_h_model->get_disabled_by_s_num($ocl_s_num); // 列出失能異動資料
          if(NULL != $other_change_log_disabled_row) {
            $other_change_log_disabled_row->ocl_d01_before_arr = explode(",", $other_change_log_disabled_row->ocl_d01_before);
            $other_change_log_disabled_row->ocl_d01_after_arr = explode(",", $other_change_log_disabled_row->ocl_d01_after);
            if($ocl01_str != '') {
              $ocl01_str .= "、失能等級異動";
            }
            else {
              $ocl01_str .= "失能等級異動";
            }
          }
        break;  
        case 3: // 送餐路線異動
          $other_change_log_route_row = $this->other_change_log_h_model->get_route_by_s_num($ocl_s_num); // 列出路線異動資料
          if(NULL != $other_change_log_route_row) {
            if($ocl01_str != '') {
              $ocl01_str .= "、送餐路線異動";
            }
            else {
              $ocl01_str .= "送餐路線異動";
            }
          }
        break;  
        case 4: // 服務現況異動
          $other_change_log_service_row = $this->other_change_log_h_model->get_service_by_s_num($ocl_s_num); // 列出固定暫停資料
          if(NULL != $other_change_log_service_row) {
            if($ocl01_str != '') {
              $ocl01_str .= "、服務現況異動";
            }
            else {
              $ocl01_str .= "服務現況異動";
            }
          }
        break;  
        case 5: // 變更基礎資料
          if($ocl01_str != '') {
            $ocl01_str .= "、變更基礎資料";
          }
          else {
            $ocl01_str .= "變更基礎資料";
          }
        break;    
        case 6: // 照會營養師
          if($ocl01_str != '') {
            $ocl01_str .= "、照會營養師";
          }
          else {
            $ocl01_str .= "照會營養師";
          }
        break;
        case 7: // 其他問題
          if($ocl01_str != '') {
            $ocl01_str .= "、其他問題";
          }
          else {
            $ocl01_str .= "其他問題";
          }
        break;
      } 
      $other_change_log_h_row->ocl01_str = $ocl01_str;
    }
    // u_var_dump($other_change_auth_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_ocl01_arr',$ocl01_arr);
    $this->tpl->assign('tv_other_change_auth_row',$other_change_auth_row);
    $this->tpl->assign('tv_other_change_log_h_row',$other_change_log_h_row);
    $this->tpl->assign('tv_other_change_log_identity_row',$other_change_log_identity_row);
    $this->tpl->assign('tv_other_change_log_disabled_row',$other_change_log_disabled_row);
    $this->tpl->assign('tv_other_change_log_route_row',$other_change_log_route_row);
    $this->tpl->assign('tv_other_change_log_service_row',$other_change_log_service_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."other_change_auth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_auth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_auth/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'other_change_auth/upd/');
    $this->tpl->display("be/other_change_auth_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function add() { // 新增
    $msel = 'add';
    $other_change_auth_row = NULL;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_other_change_auth_row',$other_change_auth_row);
    $this->tpl->assign('tv_save_link',be_url()."other_change_auth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_auth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_auth/"); // 上一層連結位置
    $this->tpl->display("be/other_change_auth_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function cpy($s_num) {
    $msel = 'cpy';
    $other_change_auth_row = $this->other_change_auth_model->get_one($s_num);
    //u_var_dump($other_change_auth_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_other_change_auth_row',$other_change_auth_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."other_change_auth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_auth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_auth/"); // 上一層連結位置
    $this->tpl->display("be/other_change_auth_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function upd($s_num) {
    $msel = 'upd';
    $other_change_auth_row = $this->other_change_auth_model->get_one($s_num);
    //u_var_dump($other_change_auth_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_other_change_auth_row',$other_change_auth_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."other_change_auth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."other_change_auth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."other_change_auth/"); // 上一層連結位置
    $this->tpl->display("be/other_change_auth_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->other_change_auth_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'other_change_auth/', 'refresh');
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
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "add":
      case "cpy":
        $this->other_change_auth_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->other_change_auth_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->other_change_auth_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
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
    ////redirect(be_url()."other_change_auth/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."other_change_auth/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }

  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
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
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function prn() {
    $msel = 'prn';
    return;
  }

  // **************************************************************************
  //  函數名稱: sw_auth()
  //  函數功能: 社工核准
  //  程式設計: Kiwi
  //  設計日期: 2021/01/02
  // **************************************************************************
  public function sw_auth($s_num)  {
    $rtn_msg = $this->other_change_auth_model->save_sw_auth($s_num); // 確定
    $this->chk_auth_times($s_num);
    header('location: '.$_SERVER['HTTP_REFERER']);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: wo_auth()
  //  函數功能: 核銷人員核准
  //  程式設計: Kiwi
  //  設計日期: 2021/01/02
  // **************************************************************************
  public function wo_auth($s_num)  {
    $rtn_msg = $this->other_change_auth_model->save_wo_auth($s_num); // 確定
    $this->chk_auth_times($s_num);
    header('location: '.$_SERVER['HTTP_REFERER']);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: me_auth()
  //  函數功能: 製餐人員核准
  //  程式設計: Kiwi
  //  設計日期: 2021/01/02
  // **************************************************************************
  public function me_auth($s_num)  {
    $rtn_msg = $this->other_change_auth_model->save_me_auth($s_num); // 確定
    $this->chk_auth_times($s_num);
    header('location: '.$_SERVER['HTTP_REFERER']);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: chk_auth_times()
  //  函數功能: 檢查核准次數是否有三次
  //  程式設計: Kiwi
  //  設計日期: 2021/01/02
  // **************************************************************************
  public function chk_auth_times($s_num)  {
    $chk_row = $this->other_change_auth_model->get_one($s_num); // 確定
    $ocl_s_num = $chk_row->ocl_s_num;
    $this->other_change_log_h_model->upd_change_by_s_num($ocl_s_num);
  }
  
  function __destruct() {
    $url_str[] = 'be/other_change_auth/save';
    $url_str[] = 'be/other_change_auth/del';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
