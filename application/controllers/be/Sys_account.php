<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sys_account extends CI_Controller { // 帳號
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('sys_account_model'); // 帳號
    $this->load->model('sys_account_group_model'); // 群組
    $this->load->model('sys_language_model'); // 語系
	  $mrand_str = $this->config->item('rand_str_8');
	  $this->tpl->assign('tv_rand_str',$mrand_str);
	  $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
	  $this->tpl->assign('tv_menu_title','帳戶管理');
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
	  $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
	  $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
	  $this->tpl->assign('tv_add_link',be_url().'sys_account/add/');
	  $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
	  $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
	  $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
	  $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
	  $this->tpl->assign('tv_return_link',be_url()."sys_account/"); // return 預設到瀏覽畫面
	  $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
	  $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
	  $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
	  $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
	  $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
	  $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
	  $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
	  $this->tpl->assign('tv_acc_user',$_SESSION['acc_user']); // 登入帳號
	  return;
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
	public function index($pg=1,$q_str=NULL)	{ 
	  $this->load->library('pagination');
    if(NULL==$q_str) { // 沒有帶入參數，就開一個新的session
      $this->load->helper('string');
      $q_str = random_string('alnum', 10); // 取得亂數10碼
    }
    if(!isset($_SESSION[$q_str]['que_str'])) {
      $this->_que_start($q_str);
    }
    //u_var_dump($_SESSION[$q_str]);

	  $msel = 'list';
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_title','帳戶管理瀏覽');
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'sys_account/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'sys_account/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'sys_account/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'sys_account/upd/');
    $this->tpl->assign('tv_del_link',be_url().'sys_account/del/');
    $this->tpl->assign('tv_prn_link',be_url().'sys_account/prn/');
    $this->tpl->assign('tv_que_link',be_url()."sys_account/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$_SESSION[$q_str]['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$_SESSION[$q_str]['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
	  $this->tpl->assign('tv_save_link',be_url().'sys_account/save/');
	  list($sys_account_row,$row_cnt) = $this->sys_account_model->get_que($q_str,$pg); // 列出帳戶管理
	  $this->tpl->assign('tv_sys_account_row',$sys_account_row);
    $config['base_url'] = be_url()."sys_account/p/";
    $config['suffix'] = "/q/{$q_str}";
    $config['first_url'] = be_url()."/sys_account/p/1/q/{$q_str}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

	  $this->tpl->display("be/sys_account.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: Tony
  //  設計日期: 2019-05-21
  // **************************************************************************
	public function disp($s_num) {
	  $msel = 'disp';
    $sys_account_row = $this->sys_account_model->get_one($s_num); // 列出單筆明細資料
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_sys_account_row',$sys_account_row);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
	  $this->tpl->assign('tv_save_link',be_url()."sys_account/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."sys_account/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."sys_account/"); // 上一層連結位置
	  $this->tpl->assign('tv_upd_link',be_url().'sys_account/upd/');
    $this->tpl->display("be/sys_account_disp.html");
	  return;
	}

  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: Tony
  //  設計日期: 2017/7/16
  // **************************************************************************
	public function add()	{ // 新增
	  $msel = 'add';
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
	  $this->tpl->assign('tv_msel',$msel);
	  $acc_row = NULL;
	  $this->tpl->assign('tv_acc_row',$acc_row);
	  $is_super = '';
	  $is_not_super = 'checked';
	  $acc_group_row = $this->sys_account_group_model->get_all(); // 列出所有群組資料
	  $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_acc_group_row',$acc_group_row);
	  $this->tpl->assign('tv_disabled',''); // 新增可以改登入帳號
	  $this->tpl->assign('tv_is_super_checked',$is_super);
	  $this->tpl->assign('tv_is_not_super_checked',$is_not_super);
	  $this->tpl->assign('tv_pwd_placeholder','請輸入密碼');
	  $this->tpl->assign('tv_required','required'); // 新增要檢查密碼
	  $this->tpl->assign('tv_save_link',be_url()."sys_account/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."sys_account/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."sys_account/"); // 上一層連結位置
	  $agu_open = $this->sys_account_group_auth_model->get_agu_open_by_acc(); // 取得目前使用者使用的CT使用權限
    $this->tpl->display("be/sys_account_edit.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: Tony
  //  設計日期: 2019-05-21
  // **************************************************************************
	public function cpy($s_num)	{
	  $msel = 'cpy';
    $sys_account_row = $this->sys_account_model->get_one($s_num);
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_sys_account_row',$sys_account_row);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
	  $this->tpl->assign('tv_save_link',be_url()."sys_account/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."sys_account/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."sys_account/"); // 上一層連結位置
    //$this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/origin/'); // origin 保留上傳檔名，要注意中文的問題
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/ci_std_/xls/');
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/');
	  $this->tpl->display("be/sys_account_edit.html");
	  return;
	}

  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
	public function upd($s_num)	{
	  $msel = 'upd';
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
	  $this->tpl->assign('tv_msel',$msel);
	  $sys_account_row = $this->sys_account_model->get_one($s_num); // 列出帳號資料
	  if('root' == $sys_account_row->acc_user) {
	    if('root' != $_SESSION['acc_user']) {
	      die('system error!!!');
	    }
	  } 
	  $this->tpl->assign('tv_sys_account_row',$sys_account_row);
	  if(1==$sys_account_row->is_super) { // 超級使用者
	    $is_super = 'checked';
	    $is_not_super = '';
	  }
	  else {
	    $is_super = '';
	    $is_not_super = 'checked';
	  }
	  $acc_group_row = $this->sys_account_group_model->get_all(); // 列出所有群組資料
	  $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 新增成功!!
	  $this->tpl->assign('tv_acc_group_row',$acc_group_row);
	  $this->tpl->assign('tv_disabled','disabled'); // 修改不可以改登入帳號
	  $this->tpl->assign('tv_is_super_checked',$is_super);
	  $this->tpl->assign('tv_is_not_super_checked',$is_not_super);
	  $this->tpl->assign('tv_pwd_placeholder','空白=不修改密碼，請輸入新密碼');
	  $this->tpl->assign('tv_required',''); // 修改不一定要檢查
	  $this->tpl->assign('tv_save_link',be_url()."sys_account/save/{$msel}");
	  $this->tpl->assign('tv_del_link',be_url()."sys_account/del/");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."sys_account/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."sys_account/"); // 上一層連結位置
    $this->tpl->display("be/sys_account_edit.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: info_edit()
  //  函數功能: 修改輸入畫面
  //  程式設計: Tony
  //  設計日期: 2017/11/14
  // **************************************************************************
	public function info_edit() {
	  $msel = 'info_edit';
	  $this->tpl->assign('tv_msel',$msel);
	  $acc_row = $this->sys_account_model->get_login(); // 抓取登入的帳號資料
	  $this->tpl->assign('tv_acc_row',$acc_row);
	  $this->tpl->assign('tv_pwd_edit_link',be_url().'sys_account/pwd_edit');
	  $this->tpl->assign('tv_save_link',be_url()."sys_account/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_leave_link',be_url()."main/"); // 上一層資料 link
	  $this->tpl->assign('tv_validate_err_msg',$this->lang->line('account_validate_err')); // 請輸入正確資料!!!
	  $this->tpl->assign('tv_save_ok_msg',$this->lang->line('account_save_ok')); // 儲存成功!!!
	  $this->tpl->display("be/sys_account_info_edit.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: pwd_edit()
  //  函數功能: 密碼修改輸入畫面
  //  程式設計: Tony
  //  設計日期: 2017/11/14
  // **************************************************************************
	public function pwd_edit()	{
	  $msel = 'pwd_edit';
	  $this->tpl->assign('tv_msel',$msel);
	  $acc_row = $this->sys_account_model->get_login(); // 抓取登入的帳號資料
	  $this->tpl->assign('tv_acc_row',$acc_row);
	  $this->tpl->assign('tv_save_link',be_url()."sys_account/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_leave_link',be_url()."sys_account/info_edit/"); // 上一層資料 link
	  $this->tpl->assign('tv_validate_err_msg',$this->lang->line('account_validate_err')); // 請輸入正確資料!!!
	  $this->tpl->assign('tv_validate_pwd_err_msg',$this->lang->line('account_validate_pwd_err')); // 新密碼與再次輸入新密碼不同!!請重新輸入!!!
	  $this->tpl->assign('tv_save_ok_msg',$this->lang->line('account_save_pwd_ok')); // 密碼更新成功
	  $this->tpl->display("be/sys_account_pwd_edit.html");
	  return;
	}

  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: Tony
  //  設計日期: 2019-05-21
  // **************************************************************************
	public function del($s_num=NULL)	{
	  $rtn_msg = $this->sys_account_model->del($s_num); // 刪除
	  if($rtn_msg) {
	    redirect(be_url().'sys_account/', 'refresh');
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
  //  設計日期: 2017/7/15
  // **************************************************************************
	public function save($kind=NULL)	{
	  switch($kind) {
	    case "add":
        $this->sys_account_model->save_add(); // 新增儲存
	      break;
	    case "upd":
        $this->sys_account_model->save_upd(); // 修改儲存
	      break;
	    case "upd_is_available":
        $this->sys_account_model->save_is_available(); // 儲存上下架
	      break;
	   case "info_edit":
	      $this->sys_account_model->save_info_edit(); // 儲存個人資料
	      break;
	   case "pwd_edit":
	      $this->sys_account_model->save_pwd_edit(); // 修改個人密碼
	      break;
	  }
	  return;
	}
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: Tony
  //  設計日期: 2019-05-21
  // **************************************************************************
	public function que($q_str)	{
    $data = $this->input->post();
    //u_var_dump($data);
    $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
	  redirect(be_url()."sys_account/p/1/q/{$q_str}", 'refresh');
	  return;
  }

  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: Tony
  //  設計日期: 2019-05-21
  // **************************************************************************
	private function _que_start($q_str)	{
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
	  return;
	}

	
  function __destruct() {
    $url_str[] = 'be/sys_account/save';
    $url_str[] = 'be/sys_account/del';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
