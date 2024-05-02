<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sys_group extends CI_Controller { // 帳號
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('sys_language_model');
    $this->load->model('sys_account_group_model');
    $this->load->model('sys_account_group_auth_model');
    $this->load->model('sys_menu_model');
	  $mrand_str = $this->config->item('rand_str_8');
	  $this->tpl->assign('tv_rand_str',$mrand_str);
	  $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
	  $this->tpl->assign('tv_menu_title','群組權限管理');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_list_btn',$this->lang->line('list')); // 瀏覽按鈕文字
    $this->tpl->assign('tv_disp_btn',$this->lang->line('disp')); // 明細按鈕文字
	  $this->tpl->assign('tv_add_btn',$this->lang->line('add')); // 新增按鈕文字
	  $this->tpl->assign('tv_upd_btn',$this->lang->line('upd')); // 修改按鈕文字
	  $this->tpl->assign('tv_del_btn',$this->lang->line('del')); // 刪除按鈕文字
	  $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
	  $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
	  $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
	  $this->tpl->assign('tv_return_link',be_url()."sys_group/"); // return 預設到瀏覽畫面
	  $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
	  $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
	  $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
	  $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
	  $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
	  $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
	  return;
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
	public function index($pg=1,$que=NULL)	{ 
	  $kind = 'list';
	  $this->tpl->assign('tv_msel',$kind); 
	  $this->tpl->assign('tv_title','群組權限瀏覽');
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'sys_group/add/');
    $this->tpl->assign('tv_upd_link',be_url().'sys_group/upd/');
    $this->tpl->assign('tv_del_link',be_url().'sys_group/del/');
	  $this->tpl->assign('tv_save_link',be_url().'sys_group/save/');
	  $acc_group_row = $this->sys_account_group_model->get_all(); // 列出所有群組資料
	  //u_var_dump($acc_group_row);
	  $this->tpl->assign('tv_acc_group_row',$acc_group_row);
	  $agu_open = $this->sys_account_group_auth_model->get_agu_open_by_acc(); // 取得目前使用者使用的CT使用權限
    $this->tpl->display("be/sys_group.html");
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
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
	  $this->tpl->assign('tv_msel',$msel);
	  $acc_group_row = NULL;
	  $acc_group_auth_row = NULL;
	  $sys_menu_row = $this->sys_menu_model->get_all(); // 列出權限資料
	  
	  foreach ($sys_menu_row as $fd_s_num => $v){
	    if(1==$v['is_available']) { // 上架顯示
	      $sys_menu_name = $sys_menu_row[$fd_s_num]['sys_menu_name'];
	      $sys_menu_level = $sys_menu_row[$fd_s_num]['sys_menu_level'];
	      $menu[$fd_s_num]['sys_menu_name'] = $sys_menu_name;
	      $menu[$fd_s_num]['sys_menu_level'] = $sys_menu_level;
	      // 權限
	      $menu[$fd_s_num]['agu_open_list']='N'; // 瀏覽
	      $menu[$fd_s_num]['agu_open_add']='N'; // 新增
	      $menu[$fd_s_num]['agu_open_upd']='N'; // 修改
	      $menu[$fd_s_num]['agu_open_del']='N'; // 刪除
	      $menu[$fd_s_num]['agu_open_que']='N'; // 查詢
	      $menu[$fd_s_num]['agu_open_prn']='N'; // 列印
	      $menu[$fd_s_num]['agu_open_download']='N'; // 下載
	      $menu[$fd_s_num]['agu_open_money']='N'; // 金額
	      $menu[$fd_s_num]['agu_open_cf']='N'; // 發單確認
	      $menu[$fd_s_num]['agu_open_cfreport']='N'; // 列印確認
	      $menu[$fd_s_num]['agu_open_list_checked']='';
	      $menu[$fd_s_num]['agu_open_add_checked']='';
	      $menu[$fd_s_num]['agu_open_upd_checked']='';
	      $menu[$fd_s_num]['agu_open_del_checked']='';
	      $menu[$fd_s_num]['agu_open_que_checked']='';
	      $menu[$fd_s_num]['agu_open_prn_checked']='';
	      $menu[$fd_s_num]['agu_open_download_checked']='';
	      $menu[$fd_s_num]['agu_open_money_checked']='';
	      $menu[$fd_s_num]['agu_open_cf_checked']='';
	      $menu[$fd_s_num]['agu_open_cfreport_checked']='';
	      if('0'==$sys_menu_level) { // 第一層
	        $menu_head[$fd_s_num]['sys_menu_name'] = $sys_menu_name;
	        $menu_head[$fd_s_num]['sys_menu_level'] = $sys_menu_level;
	      }
	      else {
	        $menu_body[$sys_menu_level]['s_num'][] = $fd_s_num;
	        $menu_body[$sys_menu_level]['sys_menu_name'][] = $sys_menu_name;
	      }
	    }
	  }
	  $this->tpl->assign('tv_acc_group_row',$acc_group_row);
	  $this->tpl->assign('tv_acc_group_auth_row',$acc_group_auth_row);
	  $this->tpl->assign('tv_menu',$menu);
	  $this->tpl->assign('tv_menu_head',$menu_head);
	  $this->tpl->assign('tv_menu_body',$menu_body);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_acc_group_row',$acc_group_row);
	  $this->tpl->assign('tv_save_link',be_url()."sys_group/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."sys_group/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."sys_group/"); // 上一層連結位置
    $this->tpl->display("be/sys_group_edit.html");
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
	  $acc_group_row = $this->sys_account_group_model->get_one($s_num); // 列出群組資料
	  $acc_group_auth_row = $this->sys_account_group_auth_model->get_auth($s_num); // 列出該群組權限資料
	  $sys_menu_row = $this->sys_menu_model->get_all(); // 列出權限資料
	  
	  foreach($sys_menu_row as $fd_s_num => $v) {
	    if(1==$v['is_available']) { // 上架顯示
	      $sys_menu_name = $sys_menu_row[$fd_s_num]['sys_menu_name'];
	      $sys_menu_level = $sys_menu_row[$fd_s_num]['sys_menu_level'];
	      $menu[$fd_s_num]['sys_menu_name'] = $sys_menu_name;
	      $menu[$fd_s_num]['sys_menu_level'] = $sys_menu_level;
	      $acc_agu_open_row = $this->sys_account_group_auth_model->get_agu_open($s_num,$fd_s_num); // 取得群組該menu權限
	      // 權限
	      $menu[$fd_s_num]['agu_open_list']='N'; // 瀏覽
	      $menu[$fd_s_num]['agu_open_add']='N'; // 新增
	      $menu[$fd_s_num]['agu_open_upd']='N'; // 修改
	      $menu[$fd_s_num]['agu_open_del']='N'; // 刪除
	      $menu[$fd_s_num]['agu_open_que']='N'; // 查詢
	      $menu[$fd_s_num]['agu_open_prn']='N'; // 列印
	      $menu[$fd_s_num]['agu_open_download']='N'; // 下載
	      $menu[$fd_s_num]['agu_open_money']='N'; // 金額
	      $menu[$fd_s_num]['agu_open_cf']='N'; // 發單確認
	      $menu[$fd_s_num]['agu_open_cfreport']='N'; // 列印確認
	      $menu[$fd_s_num]['agu_open_list_checked']='';
	      $menu[$fd_s_num]['agu_open_add_checked']='';
	      $menu[$fd_s_num]['agu_open_upd_checked']='';
	      $menu[$fd_s_num]['agu_open_del_checked']='';
	      $menu[$fd_s_num]['agu_open_que_checked']='';
	      $menu[$fd_s_num]['agu_open_prn_checked']='';
	      $menu[$fd_s_num]['agu_open_download_checked']='';
	      $menu[$fd_s_num]['agu_open_money_checked']='';
	      $menu[$fd_s_num]['agu_open_cf_checked']='';
	      $menu[$fd_s_num]['agu_open_cfreport_checked']='';
	      if(NULL<>$acc_agu_open_row) {
	        $agu_open_len = strlen($acc_agu_open_row->agu_open);
	        if('Y'==substr($acc_agu_open_row->agu_open,0,1)) {
	          $menu[$fd_s_num]['agu_open_list']='Y';
	          $menu[$fd_s_num]['agu_open_list_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,1,1)) {
	          $menu[$fd_s_num]['agu_open_add']='Y';
	          $menu[$fd_s_num]['agu_open_add_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,2,1)) {
	          $menu[$fd_s_num]['agu_open_upd']='Y';
	          $menu[$fd_s_num]['agu_open_upd_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,3,1)) {
	          $menu[$fd_s_num]['agu_open_del']='Y';
	          $menu[$fd_s_num]['agu_open_del_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,4,1)) {
	          $menu[$fd_s_num]['agu_open_que']='Y';
	          $menu[$fd_s_num]['agu_open_que_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,5,1)) {
	          $menu[$fd_s_num]['agu_open_prn']='Y';
	          $menu[$fd_s_num]['agu_open_prn_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,6,1)) {
	          $menu[$fd_s_num]['agu_open_download']='Y';
	          $menu[$fd_s_num]['agu_open_download_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,7,1)) {
	          $menu[$fd_s_num]['agu_open_money']='Y';
	          $menu[$fd_s_num]['agu_open_money_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,8,1)) {
	          $menu[$fd_s_num]['agu_open_cf']='Y';
	          $menu[$fd_s_num]['agu_open_cf_checked']='checked';
	        }
	        if('Y'==substr($acc_agu_open_row->agu_open,9,1)) {
	          $menu[$fd_s_num]['agu_open_cfreport']='Y';
	          $menu[$fd_s_num]['agu_open_cfreport_checked']='checked';
	        }

	      }
	      if('0'==$sys_menu_level) { // 第一層
	        $menu_head[$fd_s_num]['sys_menu_name'] = $sys_menu_name;
	        $menu_head[$fd_s_num]['sys_menu_level'] = $sys_menu_level;
	      }
	      else {
	        $menu_body[$sys_menu_level]['s_num'][] = $fd_s_num;
	        $menu_body[$sys_menu_level]['sys_menu_name'][] = $sys_menu_name;
	      }
	    }
	  }
	  //u_var_dump($menu);
	  $this->tpl->assign('tv_acc_group_row',$acc_group_row);
	  $this->tpl->assign('tv_acc_group_auth_row',$acc_group_auth_row);
	  $this->tpl->assign('tv_menu',$menu);
	  $this->tpl->assign('tv_menu_head',$menu_head);
	  $this->tpl->assign('tv_menu_body',$menu_body);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 新增成功!!
	  $this->tpl->assign('tv_acc_group_row',$acc_group_row);
	  $this->tpl->assign('tv_save_link',be_url()."sys_group/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."sys_group/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."sys_group/"); // 上一層連結位置
	  $agu_open = $this->sys_account_group_auth_model->get_agu_open_by_acc(); // 取得目前使用者使用的CT使用權限
    $this->tpl->display("be/sys_group_edit.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
	public function del()	{
    $rtn_msg = $this->sys_account_group_model->del(); // 刪除
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
        $this->sys_account_group_model->save_add(); // 新增儲存
	      break;
	    case "upd":
        $this->sys_account_group_model->save_upd(); // 修改儲存
	      break;
	  }
	  return;
	}
	
  function __destruct() {
    $url_str[] = 'be/sys_group/save';
    $url_str[] = 'be/sys_group/del';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
