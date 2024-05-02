<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// edit 應該要針對下拉(select)自動帶值 2019/11/13
// ckedit 的欄位也應該自動帶 2019/11/13

class Sys_cr_php extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('sys_cr_php_model'); // MVC程式產生器
    $this->load->model('sys_language_model'); // 語系
	  $mrand_str = $this->config->item('rand_str_8');
	  $this->tpl->assign('tv_rand_str',$mrand_str);
	  $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
	  $this->tpl->assign('tv_menu_title','MVC產生器');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
	  $this->tpl->assign('tv_add_btn',$this->lang->line('add')); // 新增按鈕文字
	  $this->tpl->assign('tv_upd_btn',$this->lang->line('upd')); // 修改按鈕文字
	  $this->tpl->assign('tv_del_btn',$this->lang->line('del')); // 刪除按鈕文字
	  $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
	  $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
	  $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
	  $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
	  $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
	  $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
	  $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
	  $this->tpl->assign('tv_return_link',be_url()."sys_cr_php/"); // return 預設到瀏覽畫面
	  $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
	  $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
	  $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
	  $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
	  $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
	  $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    //if('tony' != $_SESSION['acc_user'] and 'daniel' != $_SESSION['acc_user']) {
    //  die('system error!!!');
    //}
	  //u_var_dump($_SERVER);
	  //u_var_dump($this->router->fetch_class());
	  //u_var_dump($this->router->fetch_method());
	  return;
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 列出所有db資料
  //  程式設計: Tony
  //  設計日期: 2018/10/10
  // **************************************************************************
	public function index()	{
	  $this->load->dbutil();
    $db_row = NULL;
    $db_all_row = $this->dbutil->list_databases();
    if(isset($_SESSION['acc_mvc_db'])) {
      if('all' == $_SESSION['acc_mvc_db']) {
        $db_row = $db_all_row;
      }
      else {
        if('' != $_SESSION['acc_mvc_db']) { // 有指定
          $mvc_db = explode(",", $_SESSION['acc_mvc_db']);
          foreach ($db_all_row as $k_dbname => $v_dbname) {
            if(in_array($v_dbname,$mvc_db)) {
              $db_row[]=$v_dbname;
              //u_var_dump($v_dbname);
            }
          } 
        }
      }
    }
	  $this->tpl->assign('tv_breadcrumb3','DB瀏覽'); // 瀏覽
	  $this->tpl->assign('tv_title','MVC產生器');
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_db_row',$db_row);
    $this->tpl->assign('tv_list_table_link',be_url().'sys_cr_php/list_table/');
    $this->tpl->display("be/sys_cr_php_db_list.html");
	  return;
	}

  // **************************************************************************
  //  函數名稱: list_table()
  //  函數功能: 列出db中的table
  //  程式設計: Tony
  //  設計日期: 2018/10/10
  // **************************************************************************
	public function list_table($db_name=DB_NAME) {
    if(isset($_SESSION['acc_mvc_db'])) {
      if('' != $_SESSION['acc_mvc_db'] and 'all' != $_SESSION['acc_mvc_db']) { // 有指定
        $mvc_db = explode(",", $_SESSION['acc_mvc_db']);
        if(!in_array($db_name,$mvc_db)) {
          die('無權限!!!!');
        }
      }
    }
	  $this->load->helper('directory');
	  $this->load->library('pagination');
	  $msel = 'list';
	  $this->tpl->assign('tv_breadcrumb3','Table瀏覽'); // 瀏覽
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_title','MVC產生器');
	  $this->tpl->assign('tv_db_name',$db_name);
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_set_link',be_url()."sys_cr_php/set/{$db_name}/");
	  $this->tpl->assign('tv_save_link',be_url().'sys_cr_php/save/');
	  $this->tpl->assign('tv_download_link',be_url().'sys_cr_php/download/'.$db_name.'/');
	  $this->tpl->assign('tv_download_v4_link',be_url().'sys_cr_php/download_v4/'.$db_name.'/');
	  $table_row = $this->sys_cr_php_model->get_db_table($db_name);
	  foreach ($table_row as $k => $v) {
	    $model_name_start = strpos($v['table_name'],'_')+1;
	    $model_name = trim(substr($v['table_name'],$model_name_start,99));
	    $std_download_path = FCPATH."cr_php/std/download/{$model_name}/";
	    $std_dir = directory_map($std_download_path, 1);
	    if(!empty($std_dir)) {
	      $table_row[$k]['download_flag']='Y';
	      $table_row[$k]['file_time'] = date('Y-m-d H:i',filemtime($std_download_path."controllers/be/".ucfirst($model_name).".php"));
	    }
	    else {
	      $table_row[$k]['download_flag']='N';
	      $table_row[$k]['file_time'] = NULL;
	    }
	    if('N'==$table_row[$k]['download_flag']) {
	      // 無語系
	      $std_download_path = FCPATH."cr_php/std/download/{$v['table_name']}/";
	      $std_dir = directory_map($std_download_path, 1);
	      if(!empty($std_dir)) {
	        $table_row[$k]['download_flag']='Y';
	        $table_row[$k]['file_time'] = date('Y-m-d H:i',filemtime($std_download_path."controllers/be/".ucfirst($v['table_name']).".php"));
	      }
	      else {
	        $table_row[$k]['download_flag']='N';
	        $table_row[$k]['file_time'] = NULL;
	      }
	    }
	    $std4_download_path = FCPATH."cr_php/std4/download/{$model_name}/";
	    $std4_dir = directory_map($std4_download_path, 1);
	    if(!empty($std4_dir)) {
	      $table_row[$k]['download_flag_v4']='Y';
	      $table_row[$k]['file_time_v4'] = date('Y-m-d H:i',filemtime($std4_download_path."controllers/be/".ucfirst($model_name).".php"));
	    }
	    else {
	      $table_row[$k]['download_flag_v4']='N';
	      $table_row[$k]['file_time_v4'] = NULL;
	    }
	    if('N'==$table_row[$k]['download_flag_v4']) {
	      // 無語系
	      $std4_download_path = FCPATH."cr_php/std4/download/{$v['table_name']}/";
	      $std4_dir = directory_map($std4_download_path, 1);
	      if(!empty($std4_dir)) {
	        $table_row[$k]['download_flag_v4']='Y';
	        $table_row[$k]['file_time_v4'] = date('Y-m-d H:i',filemtime($std4_download_path."controllers/be/".ucfirst($v['table_name']).".php"));
	      }
	      else {
	        $table_row[$k]['download_flag_v4']='N';
	        $table_row[$k]['file_time_v4'] = NULL;
	      }
	    }
	  }
	  $this->tpl->assign('tv_table_row',$table_row);
    $this->tpl->display("be/sys_cr_php_table_list.html");
	  return;
	}

  // **************************************************************************
  //  函數名稱: set()
  //  函數功能: MVC設定畫面
  //  程式設計: Tony
  //  設計日期: 2018/10/8
  // **************************************************************************
	public function set($db_name=DB_NAME,$table_name=NULL,$lang_flag='Y')	{
    if(isset($_SESSION['acc_mvc_db'])) {
      if('' != $_SESSION['acc_mvc_db'] and 'all' != $_SESSION['acc_mvc_db']) { // 有指定
        $mvc_db = explode(",", $_SESSION['acc_mvc_db']);
        if(!in_array($db_name,$mvc_db)) {
          die('無權限!!!!');
        }
      }
    }
	  $table_info_row = $this->sys_cr_php_model->get_table_info($table_name,$db_name);
	  $table_field_row = $this->sys_cr_php_model->get_table_field($table_name,$db_name);
	  //u_var_dump($table_info_row);
	  //u_var_dump($table_field_row);
	  if('Y'==$lang_flag) {
	    $model_name_start = strpos($table_info_row->Name,'_')+1;
	    $model_name = trim(substr($table_info_row->Name,$model_name_start,99));
	  }
	  else {
	    $model_name = $table_info_row->Name;
	  }
	  $input_kind = array('text','date','datetime','int','email','password','file','radio','select','select_chosen','select_multiple_chosen','content','ckedit_content');
	  $this->tpl->assign('tv_breadcrumb3','設定');
	  $this->tpl->assign('tv_save_btn','產生檔案');
	  $this->tpl->assign('tv_table_info_row',$table_info_row);
	  $this->tpl->assign('tv_table_field_row',$table_field_row);
	  $this->tpl->assign('tv_input_kind',$input_kind);
	  $this->tpl->assign('tv_model_name',$model_name);
	  $this->tpl->assign('tv_table_name',$model_name);
	  $this->tpl->assign('tv_create_date',date('Y-m-d'));
	  $this->tpl->assign('tv_save_ok','產生MVC資料成功!!!');
	  $this->tpl->assign('tv_save_link',be_url()."sys_cr_php/save/set");
	  $this->tpl->assign('tv_input_kind_link',be_url()."sys_cr_php/input_kind/");

	  $this->tpl->display("be/sys_cr_php_set.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: MVC設定檔案下載
  //  程式設計: Tony
  //  設計日期: 2018/10/9
  // **************************************************************************
	public function download($db_name=NULL,$table_name=NULL)	{
    if(isset($_SESSION['acc_mvc_db'])) {
      if('' != $_SESSION['acc_mvc_db'] and 'all' != $_SESSION['acc_mvc_db']) { // 有指定
        $mvc_db = explode(",", $_SESSION['acc_mvc_db']);
        if(!in_array($db_name,$mvc_db)) {
          die('無權限!!!!');
        }
      }
    }
	  $this->load->helper('directory');
	  $this->load->library('zip');
	  $table_info_row = $this->sys_cr_php_model->get_table_info($table_name,$db_name);
	  $table_field_row = $this->sys_cr_php_model->get_table_field($table_name,$db_name);
	  //u_var_dump($table_info_row);
	  //u_var_dump($table_field_row);
	  $model_name_start = strpos($table_info_row->Name,'_')+1;
	  $model_name = trim(substr($table_info_row->Name,$model_name_start,99));
	  $std_download_path = FCPATH."cr_php/std/download/{$model_name}/";
	  $std_dir = directory_map($std_download_path, 1);
	  if(!empty($std_dir)) {
      $this->zip->read_dir($std_download_path,FALSE);
      $this->zip->download("{$model_name}.zip");
	  }
	  else {
	    // 無語系
	    $std_download_path = FCPATH."cr_php/std/download/{$table_name}/";
	    $std_dir = directory_map($std_download_path, 1);
	    if(!empty($std_dir)) {
        $this->zip->read_dir($std_download_path,FALSE);
        $this->zip->download("{$table_name}.zip");
	    }
	    else {
	      die('查無MVC檔案，請先設定!!!');
	    }
	  }
	  return;
	}
  // **************************************************************************
  //  函數名稱: download_v4()
  //  函數功能: MVC設定檔案下載
  //  程式設計: Tony
  //  設計日期: 2018/10/9
  // **************************************************************************
	public function download_v4($db_name=NULL,$table_name=NULL)	{
    if(isset($_SESSION['acc_mvc_db'])) {
      if('' != $_SESSION['acc_mvc_db'] and 'all' != $_SESSION['acc_mvc_db']) { // 有指定
        $mvc_db = explode(",", $_SESSION['acc_mvc_db']);
        if(!in_array($db_name,$mvc_db)) {
          die('無權限!!!!');
        }
      }
    }
	  $this->load->helper('directory');
	  $this->load->library('zip');
	  $table_info_row = $this->sys_cr_php_model->get_table_info($table_name,$db_name);
	  $table_field_row = $this->sys_cr_php_model->get_table_field($table_name,$db_name);
	  //u_var_dump($table_info_row);
	  //u_var_dump($table_field_row);
	  $model_name_start = strpos($table_info_row->Name,'_')+1;
	  $model_name = trim(substr($table_info_row->Name,$model_name_start,99));
	  $std_download_path = FCPATH."cr_php/std4/download/{$model_name}/";
	  $std_dir = directory_map($std_download_path, 1);
	  if(!empty($std_dir)) {
      $this->zip->read_dir($std_download_path,FALSE);
      $this->zip->download("{$model_name}.zip");
	  }
	  else {
	    // 無語系
	    $std_download_path = FCPATH."cr_php/std4/download/{$table_name}/";
	    $std_dir = directory_map($std_download_path, 1);
	    if(!empty($std_dir)) {
        $this->zip->read_dir($std_download_path,FALSE);
        $this->zip->download("{$table_name}.zip");
	    }
	    else {
	      die('查無MVC檔案，請先設定!!!');
	    }
	  }
	  return;
	}
  // **************************************************************************
  //  函數名稱: input_kind()
  //  函數功能: 依據型態類別產生輸入的html內容
  //  程式設計: Tony
  //  設計日期: 2018/10/9
  // **************************************************************************
	public function input_kind($bootstrap_ver='3')	{
	  $rtn_str = '';
	  $data = $this->input->post();
	  if('Y'==$data['chk_required']) {
	    $required = 'required';
	    $required_str = '<span class="text-danger">*</span>';
	  }
	  else {
	    $required = '';
	    $required_str = '';
	  }
	  if('3'==$bootstrap_ver) {
	    switch ($data['input_kind']) {
	      case "text":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
                          <input type='text' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
	        break;
	      case "date":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
	                      <div class='form-group'>
	                        <label>{$data['filed_name_comment']}</label>
                          <div class='input-group date form_datetime'>
                            <span class='input-group-addon' id='basic-addon1'><span class='glyphicon glyphicon glyphicon-calendar' aria-hidden='true'></span></span>
                            <input type='text' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                          </div>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
	        break;
	      case "datetime":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
	                      <div class='form-group'>
	                        <label>{$data['filed_name_comment']}</label>
                          <div class='input-group date form_datetime'>
                            <span class='input-group-text input-group-addon'><i class='far fa-calendar-alt'></i></span>
                            <input type='text' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                          </div>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
	        break;
        case "int":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
                          <input type='number' min='0' step='any' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
        case "email":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
                          <input type='email' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
        case "file":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>檔案上傳</label><br>
                          <a href='javascript:;' class='file maT10'>選擇檔案
                            <input text='hidden' id='{$data['field_name']}' name='{$data['field_name']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]'>
                            <input type='file' name='files' data-url='[[SMARTY_LEFT]]".'$tv_upload_link'."[[SMARTY_RIGHT]]' data-att_name='{$data['field_name']}'>
                            <i class='fa fa-upload' aria-hidden='true'></i>
                          </a>
                          <div class='lesson-time'>
                            <div class='row'>
                              <div class='col-sm-4 col-xs-4 col-lg-8'>
                                <div class='list clearfix'>
                                  <div class='pull-left'><span id='{$data['field_name']}_str'>[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]</span></div>
                                  <div class='pull-right'>
                                    <button type='button' class='btn btn-C2 btn-xs' data-toggle='modal' data-target='.pop-container'>刪除</button>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
        case "password":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
                          <input type='password' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
        case "radio":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
                          <span class='form-control'>
                            <input type='radio' name='{$data['field_name']}' value='1' [[SMARTY_LEFT]]if ".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:0==1[[SMARTY_RIGHT]] checked [[SMARTY_LEFT]]/if[[SMARTY_RIGHT]]> 是
                            <input type='radio' name='{$data['field_name']}' value='0' [[SMARTY_LEFT]]if ".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:0==0[[SMARTY_RIGHT]] checked [[SMARTY_LEFT]]/if[[SMARTY_RIGHT]]> 否
                          </span>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
        case "select":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
   	  			      		    <select id='{$data['field_name']}' name='{$data['field_name']}' class='form-control' {$required}>
   	  			      		      <option value='' selected>-請選擇-</option>
   	  			      		      <option value='1'>下拉1</option>
   	  			      		      <option value='2'>下拉2</option>
   	  			      		      <option value='3'>下拉3</option>
   	  			      		    </select>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
        case "select_multiple_chosen":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
   	  			      		    <select id='{$data['field_name']}' name='{$data['field_name']}' data-placeholder='-請選擇或輸入{$data['filed_name_comment']}-' multiple class='form-control chosen-select-deselect' {$required}>
   	  			      		      <option value='' selected>-請選擇-</option>
   	  			      		      <option value='1'>下拉1</option>
   	  			      		      <option value='2'>下拉2</option>
   	  			      		      <option value='3'>下拉3</option>
   	  			      		    </select>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
        case "content":
        case "ckedit_content":
	        $rtn_str = "<div class='col-xs-12 col-sm-12'>
                        <div class='form-group'>
                          <label>{$data['filed_name_comment']}</label>
                          <textarea id='{$data['field_name']}' class='form-control' name='{$data['field_name']}' rows='5' {$required}>[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]</textarea>
                          <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                          <div class='help-block with-errors'></div>
                        </div>
                      </div>
                      <div class='clear'></div>
	                   ";
          break;
	    }
	  } // $bootstrap_ver = 3
	  if('4'==$bootstrap_ver) {
	    switch ($data['input_kind']) {
	      case "text":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
                  <input type='text' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control form-control-sm' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
	        break;
	      case "date":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
                  <div class='input-group form_date'>
                    <div class='input-group-prepend'>
                      <span class='input-group-text'><i class='far fa-calendar-alt'></i></span>
                    </div>
                    <input type='text' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control form-control-sm' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                  </div>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
	        break;
	      case "datetime":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
  					      <div class='input-group date form_datetime'>
  					      	<input type='text' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control form-control-sm' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
     				      	<span class='input-group-text input-group-addon'><i class='far fa-calendar-alt'></i></span>
  					      </div>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
	        break;
        case "int":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
  					      <input type='number' min='0' step='any' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control form-control-sm' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "email":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
  					      <input type='email' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control form-control-sm' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "file":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
                  <a href='javascript:;' class='file maT10'>選擇檔案
                    <input text='hidden' id='{$data['field_name']}' name='{$data['field_name']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]'>
                    <input type='file' name='files' data-url='[[SMARTY_LEFT]]".'$tv_upload_link'."[[SMARTY_RIGHT]]' data-att_name='{$data['field_name']}'>
                    <i class='fa fa-upload' aria-hidden='true'></i>
                  </a>
                  <div class='lesson-time'>
                    <div class='row'>
                      <div class='col-sm-12 col-xs-12 col-lg-12'>
                        <div class='list clearfix'>
                          <div class='pull-left'><span id='{$data['field_name']}_str'>[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]</span></div>
                          <div class='pull-right'>
                            <button type='button' class='btn btn-C2 btn-xs del' data-del_fd_name='{$data['field_name']}' data-toggle='modal' data-target='.pop-del_file'>刪除</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "password":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
  					      <input type='password' id='{$data['field_name']}' name='{$data['field_name']}' class='form-control form-control-sm' placeholder='{$data['input_placeholder']}' value='[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]' {$required}>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "radio":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
                  <span class='form-control form-control-sm'>
                    <input type='radio' name='{$data['field_name']}' value='1' [[SMARTY_LEFT]]if ".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:0==1[[SMARTY_RIGHT]] checked [[SMARTY_LEFT]]/if[[SMARTY_RIGHT]]> 是
                    <input type='radio' name='{$data['field_name']}' value='0' [[SMARTY_LEFT]]if ".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:0==0[[SMARTY_RIGHT]] checked [[SMARTY_LEFT]]/if[[SMARTY_RIGHT]]> 否
                  </span>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "select":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
   	  			      <select id='{$data['field_name']}' name='{$data['field_name']}' class='form-control form-control-sm' {$required}>
   	  			        <option value='' selected>-請選擇-</option>
   	  			        <option value='1'>下拉1</option>
   	  			        <option value='2'>下拉2</option>
   	  			        <option value='3'>下拉3</option>
   	  			      </select>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "select_chosen":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
   	  			      <select id='{$data['field_name']}' name='{$data['field_name']}' data-placeholder='-請選擇或輸入{$data['filed_name_comment']}-' class='form-control form-control-sm chosen-select-deselect' {$required}>
   	  			        <option value='' selected>-請選擇-</option>
   	  			        <option value='1'>下拉1</option>
   	  			        <option value='2'>下拉2</option>
   	  			        <option value='3'>下拉3</option>
   	  			      </select>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "select_multiple_chosen":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
   	  			      <select id='{$data['field_name']}' name='{$data['field_name']}' data-placeholder='-請選擇或輸入{$data['filed_name_comment']}-' multiple class='form-control form-control-sm chosen-select-deselect' {$required}>
   	  			        <option value='' selected>-請選擇-</option>
   	  			        <option value='1'>下拉1</option>
   	  			        <option value='2'>下拉2</option>
   	  			        <option value='3'>下拉3</option>
   	  			      </select>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
        case "content":
        case "ckedit_content":
          $rtn_str = "            <[[TR]] class='d-flex'>
              <[[TD]] width='[[SWIDTH_LEFT]]' class='table-secondary text-right'>{$required_str}{$data['filed_name_comment']}</[[TD]]>
              <[[TD]] width='[[SWIDTH_RIGHT]]' class='table-light'>
                <span class='form-group'>
  					      <textarea id='{$data['field_name']}' class='form-control form-control-sm' name='{$data['field_name']}' rows='5' {$required}>[[SMARTY_LEFT]]".'$tv'."_[[TABLE_NAME]]_row".'->'."{$data['field_name']}|default:''[[SMARTY_RIGHT]]</textarea>
                  <span id='helpBlock_{$data['field_name']}' class='help-block'>{$data['input_memo']}</span>
                  <div class='help-block with-errors'></div>
                </span>
              </[[TD]]>
            </[[TR]]>\n";
          break;
	    }
	  } // $bootstrap_ver = 4
	  echo $rtn_str;
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
	    case "set":
	      $data = $this->input->post();
	      $write_file_name = ucfirst($data['TABLE_NAME']);
	      //var_dump($data['QUE_FIELD_COMMENT']);
	      //var_dump($data['QUE_FIELD_KIND']);
	      //u_var_dump($data['INPUT_FIELD']);
	      $this->load->helper('file');
	      if('3'==$data['bootstrap_ver']) {
	        $std_path = FCPATH."cr_php/std/";
	        $std_download_path = FCPATH."cr_php/std/download/";
	      }
	      else {
	        $std_path = FCPATH."cr_php/std4/";
	        $std_download_path = FCPATH."cr_php/std4/download/";
	      }
	      // 建立資料夾
	      @mkdir("{$std_download_path}{$data['TABLE_NAME']}");
	      @mkdir("{$std_download_path}{$data['TABLE_NAME']}/controllers/");
	      @mkdir("{$std_download_path}{$data['TABLE_NAME']}/controllers/be");
	      @mkdir("{$std_download_path}{$data['TABLE_NAME']}/models/"); 
	      @mkdir("{$std_download_path}{$data['TABLE_NAME']}/views/");
	      @mkdir("{$std_download_path}{$data['TABLE_NAME']}/views/be");
	      
	      // controllers
        $ct_php = read_file("{$std_path}controllers/be/std.php");
        //u_var_dump($ct_php);
        $ct_php = str_replace("[[FIRST_MODEL_NAME]]",ucfirst($data['MODEL_NAME']),$ct_php);
        $ct_php = str_replace("[[MODEL_NAME]]",$data['MODEL_NAME'],$ct_php);
        $ct_php = str_replace("[[TABLE_NAME]]",$data['TABLE_NAME'],$ct_php);
        $ct_php = str_replace("[[TABLE_COMMENT]]",$data['TABLE_COMMENT'],$ct_php);
        $ct_php = str_replace("[[PROGRAME_NAME]]",$data['PROGRAME_NAME'],$ct_php);
        $ct_php = str_replace("[[CREATE_DATE]]",$data['CREATE_DATE'],$ct_php);
	      //u_var_dump($ct_php);
	      //u_var_dump("{$std_download_path}{$data['MODEL_NAME']}.php");
	      write_file("{$std_download_path}{$data['TABLE_NAME']}/controllers/be/{$write_file_name}.php", $ct_php);
	      
	      // Model
	      $que_str = "";
	      if(isset($data['QUE_FIELD_COMMENT'])) {
	        foreach ($data['QUE_FIELD_COMMENT'] as $k => $v) {
	          $que_kind = $data['QUE_FIELD_KIND'][$k];
	          switch ($que_kind) {
	            case "=":
	              $que_kind = "= '{[[S]]que_{$k}}' ";
	              break;
	            case "!=":
	              $que_kind = "!= '{[[S]]que_{$k}}' ";
	              break;
	            case "like_left":
	              $que_kind = "like '%{[[S]]que_{$k}}' ";
	              break;
	            case "like_right":
	              $que_kind = "like '{[[S]]que_{$k}}%' ";
	              break;
	            case "like_all":
	              $que_kind = "like '%{[[S]]que_{$k}}%' ";
	              break;
	            case ">=":
	              $que_kind = ">= '{[[S]]que_{$k}}' ";
	              break;
	            case "<=":
	              $que_kind = "<= '{[[S]]que_{$k}}' ";
	              break;
	            case "is_null":
	              $que_kind = "is null ";
	              break;
	            case "is_not_null":
	              $que_kind = "is not null ";
	              break;
	          }
	          $que_str .= "    if(!empty([[S]]get_data['que_{$k}'])) { // {$v}\n";
	          $que_str .= "      [[S]]que_{$k} = [[S]]get_data['que_{$k}'];\n";
	          $que_str .= "      [[S]]que_{$k} = [[S]]this->db->escape_like_str([[S]]que_{$k});\n";
	          $que_str .= "      [[S]]where .= \" and {[[S]]tbl_{$data['TABLE_NAME']}}.{$k} {$que_kind} /* {$v} */ \";\n";
	          $que_str .= "    }\n";
	        }
	        $que_str = str_replace("[[S]]",'$',$que_str);
	      }
	      $model_php = read_file("{$std_path}models/std_model.php");
	      $model_php = str_replace("[[FIRST_MODEL_NAME]]",ucfirst($data['MODEL_NAME']),$model_php);
	      $model_php = str_replace("[[MODEL_NAME]]",$data['MODEL_NAME'],$model_php);
	      $model_php = str_replace("[[TABLE_NAME]]",$data['TABLE_NAME'],$model_php);
	      $model_php = str_replace("[[TABLE_COMMENT]]",$data['TABLE_COMMENT'],$model_php);
        $model_php = str_replace("[[PROGRAME_NAME]]",$data['PROGRAME_NAME'],$model_php);
        $model_php = str_replace("[[CREATE_DATE]]",$data['CREATE_DATE'],$model_php);
        $model_php = str_replace("[[QUE_FIELD_NAME]]",trim($data['QUE_FIELD_NAME']),$model_php);
        $model_php = str_replace("[[QUE_ORDER_FIELD]]",trim($data['QUE_ORDER_FIELD']),$model_php);
        $model_php = str_replace("[[QUE_BY_GET]]",$que_str,$model_php);
	      //u_var_dump($model_php);
	      write_file("{$std_download_path}{$data['TABLE_NAME']}/models/{$write_file_name}_model.php", $model_php);
	      
	      // Views
	      // List
	      $que_input_str = ""; // 查詢欄位
	      if(isset($data['QUE_FIELD_COMMENT'])) {
	        foreach ($data['QUE_FIELD_COMMENT'] as $k => $v) {
	          $field_class = '';
	          $que_type = $data['QUE_FIELD_TYPE'][$k];
	          switch ($que_type) {
	            case "date":
	            case "datetime":
	              $field_class = 'form_date';
	              break;
	          }
            $que_input_str .= "                <tr class='d-flex'>\n";
            $que_input_str .= "                  <td class='[[SMARTY_LEFT]][[S]]swidth_left[[SMARTY_RIGHT]] table-secondary text-right'>{$v}</td>\n";
            $que_input_str .= "                  <td class='[[SMARTY_LEFT]][[S]]swidth_right[[SMARTY_RIGHT]] table-light'>\n";
            $que_input_str .= "                    <span class='form-group'>\n";
            $que_input_str .= "                      <div class='input-group input-group-sm'>\n";
            $que_input_str .= "                        <input type='text' id='que_{$k}' name='que_{$k}' class='col-7 form-control form-control-sm que_enter {$field_class}' placeholder='請輸入{$v}' value=''>\n";
            $que_input_str .= "                        <div class='input-group-append'>\n";
            $que_input_str .= "                          <span id='helpBlock_que_{$k}' class='help-block ml-2'></span>\n";
            $que_input_str .= "                        </div>\n";
            $que_input_str .= "                      </div>\n";
            $que_input_str .= "                      <div class='help-block with-errors'></div>\n";
            $que_input_str .= "                    </span>\n";
            $que_input_str .= "                  </td>\n";
            $que_input_str .= "                </tr>\n";
	        }
	        $que_input_str = str_replace("[[S]]",'$',$que_input_str);
	      }
	      $view_list_html = read_file("{$std_path}views/be/std.html");
	      $view_list_html = str_replace("[[TABLE_NAME]]",$data['TABLE_NAME'],$view_list_html);
	      $view_list_html = str_replace("[[TITLE]]",$data['TITLE'],$view_list_html);
	      $view_list_html = str_replace("[[PREV_TITLE]]",$data['PREV_TITLE'],$view_list_html);
	      $view_list_html = str_replace("[[FIELD_HEAD]]",trim($data['FIELD_HEAD']),$view_list_html);
	      $view_list_html = str_replace("[[FIELD_BODY]]",trim($data['FIELD_BODY']),$view_list_html);
	      $view_list_html = str_replace("[[QUE_FIELD_INPUT]]",$que_input_str,$view_list_html);
	      $view_list_html = str_replace("[[SMARTY_LEFT]]","{{",$view_list_html);
	      $view_list_html = str_replace("[[SMARTY_RIGHT]]","}}",$view_list_html);
	      //u_var_dump($view_list_html);
	      write_file("{$std_download_path}{$data['TABLE_NAME']}/views/be/{$data['TABLE_NAME']}.html", $view_list_html);
	      // Disp
	      $view_disp_html = read_file("{$std_path}views/be/std_disp.html");
	      $view_disp_html = str_replace("[[DISP_FIELD]]",$data['DISP_FIELD'],$view_disp_html);
	      $view_disp_html = str_replace("[[TABLE_NAME]]",$data['TABLE_NAME'],$view_disp_html);
	      $view_disp_html = str_replace("[[TITLE]]",$data['TITLE'],$view_disp_html);
	      $view_disp_html = str_replace("[[PREV_TITLE]]",$data['PREV_TITLE'],$view_disp_html);
	      $view_disp_html = str_replace("[[SWIDTH_LEFT]]",'{{$swidth_left}}',$view_disp_html);
	      $view_disp_html = str_replace("[[SWIDTH_RIGHT]]",'{{$swidth_right}}',$view_disp_html);
	      $view_disp_html = str_replace("[[SMARTY_LEFT]]","{{",$view_disp_html);
	      $view_disp_html = str_replace("[[SMARTY_RIGHT]]","}}",$view_disp_html);
	      //u_var_dump($data['DISP_FIELD']);
	      //u_var_dump($view_disp_html);
	      //exit;
	      write_file("{$std_download_path}{$data['TABLE_NAME']}/views/be/{$data['TABLE_NAME']}_disp.html", $view_disp_html);

	      // add , upd
	      $input_field = "";
	      foreach ($data['INPUT_FIELD'] as $k => $v) {
	        if('' != $v) {
	          $input_field .= $v;
	        }
	      }
	      $select_default = "";
	      foreach ($data['SELECT_DEFAULT'] as $k => $v) {
	        if('' != $v) {
	          $select_default .= "    ".$v."\n";
	        }
	      }
	      $view_edit_html = read_file("{$std_path}views/be/std_edit.html");
	      $view_edit_html = str_replace("[[CKEDIT_FD_SET]]",$data['CKEDIT_FD_SET'],$view_edit_html);
	      $view_edit_html = str_replace("[[INPUT_FIELD]]",$input_field,$view_edit_html);
	      $view_edit_html = str_replace("[[SELECT_DEFAULT]]",$select_default,$view_edit_html);
	      $view_edit_html = str_replace("[[TABLE_NAME]]",$data['TABLE_NAME'],$view_edit_html);
	      $view_edit_html = str_replace("[[TITLE]]",$data['TITLE'],$view_edit_html);
	      $view_edit_html = str_replace("[[PREV_TITLE]]",$data['PREV_TITLE'],$view_edit_html);
	      $view_edit_html = str_replace("[[TR]]","tr",$view_edit_html);
	      $view_edit_html = str_replace("[[TD]]","td",$view_edit_html);
	      $view_edit_html = str_replace("[[SWIDTH_LEFT]]",'{{$swidth_left}}',$view_edit_html);
	      $view_edit_html = str_replace("[[SWIDTH_RIGHT]]",'{{$swidth_right}}',$view_edit_html);
	      $view_edit_html = str_replace("[[SMARTY_LEFT]]","{{",$view_edit_html);
	      $view_edit_html = str_replace("[[SMARTY_RIGHT]]","}}",$view_edit_html);
	      //u_var_dump($view_edit_html);
	      write_file("{$std_download_path}{$data['TABLE_NAME']}/views/be/{$data['TABLE_NAME']}_edit.html", $view_edit_html);
	      echo "檔案產生成功。請至瀏覽頁下載程式。";
	      //exit;
	      break;
	    case "upd":
        $this->sys_cr_php_model->save_upd(); // 修改儲存
	      break;
	    case "upd_is_available":
        $this->sys_cr_php_model->save_is_available(); // 上下架儲存
	      break;
	  }
	  return;
	}

  function __destruct() {
    $url_str[] = 'be/sys_cr_php/save';
    $url_str[] = 'be/sys_cr_php/del';
    $url_str[] = 'be/sys_cr_php/download';
    $url_str[] = 'be/sys_cr_php/input_kind';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
