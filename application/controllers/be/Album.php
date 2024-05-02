<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Album extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('album_model');
    $this->load->model('sys_language_model'); // 語系
	  $mrand_str = $this->config->item('rand_str_8');
	  $this->tpl->assign('tv_rand_str',$mrand_str);
	  $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
	  $this->tpl->assign('tv_menu_title','相本管理');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_list_btn',$this->lang->line('list')); // 瀏覽按鈕文字
    $this->tpl->assign('tv_disp_btn',$this->lang->line('disp')); // 明細按鈕文字
	  $this->tpl->assign('tv_add_btn',$this->lang->line('add')); // 新增按鈕文字
	  $this->tpl->assign('tv_upd_btn',$this->lang->line('upd')); // 修改按鈕文字
	  $this->tpl->assign('tv_del_btn',$this->lang->line('del')); // 刪除按鈕文字
	  $this->tpl->assign('tv_pdf_flag','N'); // 使否顯示pdf按鈕
	  $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
	  $this->tpl->assign('tv_download_execl_flag','N'); // 使否顯示下載execl按鈕
	  $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
	  $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
	  $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
	  $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
	  $this->tpl->assign('tv_return_link',be_url()."album/"); // return 預設到瀏覽畫面
	  $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
	  $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
	  $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
	  $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
	  $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
	  $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
	  //u_var_dump($_SERVER);
	  return;
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2018/5/9
  // **************************************************************************
	public function index($pg=1,$que=NULL)	{
	  $this->load->library('pagination');
	  $msel = 'list';
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_title','最新消息瀏覽');
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'album/add/');
    $this->tpl->assign('tv_upd_link',be_url().'album/upd/');
    $this->tpl->assign('tv_del_link',be_url().'album/del/');
    $this->tpl->assign('tv_pdf_link',be_url().'album/pdf/');
    $this->tpl->assign('tv_download_execl_link',be_url().'album/download_execl/');
    $this->tpl->assign('tv_upload_path',pub_url('').'uploads/');
    $this->tpl->assign('tv_file_upload_link',be_url().'album/file_upload/');
    $this->tpl->assign('tv_que_link',be_url().'album/p/1/q/');
    $this->tpl->assign('tv_f_que',rawurldecode($que));
	  $this->tpl->assign('tv_save_link',be_url().'album/save/');
	  list($album_row,$row_cnt) = $this->album_model->get_que($que,$pg);
	  $this->tpl->assign('tv_album_row',$album_row);

    $config['base_url'] = be_url()."album/p/";
    $config['suffix'] = "/q/{$que}";
    $config['first_url'] = be_url()."/album/p/1/q/{$que}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
	  $this->tpl->display("be/album.html");
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
	  $album_row = NULL;
	  $album_object_checked['0']='checked'; // 全部
	  $album_object_checked['1']=''; // 對象1
	  $album_object_checked['2']=''; // 對象2
	  $album_object_checked['3']=''; // 對象3
	  $album_object_checked['4']=''; // 對象4
	  $album_object_checked['5']=''; // 對象5
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_album_row',$album_row);
	  $this->tpl->assign('tv_album_object_checked_0',$album_object_checked['0']);
	  $this->tpl->assign('tv_album_object_checked_1',$album_object_checked['1']);
	  $this->tpl->assign('tv_album_object_checked_2',$album_object_checked['2']);
	  $this->tpl->assign('tv_album_object_checked_3',$album_object_checked['3']);
	  $this->tpl->assign('tv_album_object_checked_4',$album_object_checked['4']);
	  $this->tpl->assign('tv_album_object_checked_5',$album_object_checked['5']);
	  $this->tpl->assign('tv_save_link',be_url()."album/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."album/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."album/"); // 上一層連結位置
	  $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/album_/');
    $this->tpl->display("be/album_edit.html");
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
    $album_row = $this->album_model->get_one($s_num); // 列出帳號資料
	  $album_object = explode(',',$album_row->album_object);
	  $album_object_checked['0']=''; // 全部
	  $album_object_checked['1']=''; // 管理員
	  $album_object_checked['2']=''; // 行政人員
	  $album_object_checked['3']=''; // 教師
	  $album_object_checked['4']=''; // 教學助理
	  $album_object_checked['5']=''; // 學生
	  for($i=0;$i<count($album_object);$i++) {
	    $checked = $album_object[$i];
	    $album_object_checked[$checked]='checked';
	  }
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_album_row',$album_row);
	  $this->tpl->assign('tv_album_object_checked_0',$album_object_checked['0']);
	  $this->tpl->assign('tv_album_object_checked_1',$album_object_checked['1']);
	  $this->tpl->assign('tv_album_object_checked_2',$album_object_checked['2']);
	  $this->tpl->assign('tv_album_object_checked_3',$album_object_checked['3']);
	  $this->tpl->assign('tv_album_object_checked_4',$album_object_checked['4']);
	  $this->tpl->assign('tv_album_object_checked_5',$album_object_checked['5']);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 新增成功!!
	  $this->tpl->assign('tv_save_link',be_url()."album/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."album/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."album/"); // 上一層連結位置
	  $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/album_/');
    $this->tpl->display("be/album_edit.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: file_upload()
  //  函數功能: 相片上傳
  //  程式設計: Tony
  //  設計日期: 2018/8/13
  // **************************************************************************
	public function file_upload($s_num=NULL)	{
	  $msel = 'upload';
	  $file_list = NULL;
	  $this->tpl->assign('tv_breadcrumb3','相片上傳'); // 
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_s_num',$s_num);
	  $this->tpl->assign('tv_file_list',$file_list);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 新增成功!!
	  $this->tpl->assign('tv_save_link',be_url()."album/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."album/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."album/"); // 上一層連結位置
	  $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/album_upload/origin/img/');
    $this->tpl->display("be/album_upload.html");
	  return;
	}


  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
	public function del($s_num=NULL)	{
	  $rtn_msg = $this->album_model->del($s_num); // 刪除
	  if($rtn_msg) {
	    redirect(be_url().'album/', 'refresh');
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
        $this->album_model->save_add(); // 新增儲存
	      break;
	    case "upd":
        $this->album_model->save_upd(); // 修改儲存
	      break;
	    case "upd_is_available":
        $this->album_model->save_is_available(); // 上下架儲存
	      break;
	  }
	  return;
	}

  function __destruct() {
    $url_str[] = 'be/album/save';
    $url_str[] = 'be/album/del';
    $url_str[] = 'be/album/download_execl';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
