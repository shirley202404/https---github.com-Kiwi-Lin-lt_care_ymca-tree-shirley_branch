<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients_hlth_normal extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 header
    $this->load->model('route_model'); // 路線資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('service_case_model'); // 開結案資料
    $this->load->model('clients_hlth_normal_model'); // 營養師營養評估表
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8'); 
    $this->tpl->assign('tv_rand_str',$mrand_str); // html載入的js與css增加亂數，避免抓到cache的檔案
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','營養師營養評估表');
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
    $this->tpl->assign('tv_add_link',be_url().'clients_hlth_normal/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."clients_hlth_normal/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/clients_hlth_normal_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    //if('tony' != $_SESSION['acc_user']) { // 開發階段不給客戶使用的時候用
    //  die('趕工中...');
    //}
    return;
  }
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
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
    list($clients_hlth_normal_row,$row_cnt) = $this->clients_hlth_normal_model->get_que($q_str,$pg); // 列出營養師營養評估表
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','營養師營養評估表瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'clients_hlth_normal/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'clients_hlth_normal/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'clients_hlth_normal/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'clients_hlth_normal/upd/');
    $this->tpl->assign('tv_del_link',be_url().'clients_hlth_normal/del/');
    $this->tpl->assign('tv_prn_link',be_url().'clients_hlth_normal/prn/');
    $this->tpl->assign('tv_download_link',be_url().'clients_hlth_normal/download/');
    $this->tpl->assign('tv_download2_link',be_url().'clients_hlth_normal/download2/');
    $this->tpl->assign('tv_que_link',be_url()."clients_hlth_normal/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'clients_hlth_normal/save/');
    $this->tpl->assign('tv_clients_hlth_normal_row',$clients_hlth_normal_row);
    $config['base_url'] = be_url()."clients_hlth_normal/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/clients_hlth_normal/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/clients_hlth_normal.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $clients_hlth_normal_row = $this->clients_hlth_normal_model->get_one($s_num); // 列出單筆明細資料
    $clients_hlth_normal_track_row = $this->clients_hlth_normal_model->get_track($s_num); // 列出所有追蹤紀錄
    // u_var_dump($clients_hlth_normal_track_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_hlth_normal_row',$clients_hlth_normal_row);
    $this->tpl->assign('tv_clients_hlth_normal_track_row',$clients_hlth_normal_track_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth_normal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth_normal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth_normal/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'clients_hlth_normal/upd/');
    $this->tpl->display("be/clients_hlth_normal_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function add()	{ // 新增
    $msel = 'add';
    $clients_hlth_normal_row = NULL;
    $clients_hlth_normal_track_row = array(); // 列出所有追蹤紀錄
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_clients_hlth_normal_row',$clients_hlth_normal_row);
    $this->tpl->assign('tv_clients_hlth_normal_track_row',$clients_hlth_normal_track_row);
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth_normal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth_normal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth_normal/"); // 上一層連結位置
    $this->tpl->display("be/clients_hlth_normal_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function cpy($s_num)	{
    $msel = 'cpy';
    $clients_hlth_normal_row = $this->clients_hlth_normal_model->get_one($s_num);
    $clients_hlth_normal_track_row = $this->clients_hlth_normal_model->get_track($s_num); // 列出所有追蹤紀錄    
    //u_var_dump($clients_hlth_normal_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_hlth_normal_row',$clients_hlth_normal_row);
    $this->tpl->assign('tv_clients_hlth_normal_track_row',$clients_hlth_normal_track_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth_normal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth_normal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth_normal/"); // 上一層連結位置
    $this->tpl->display("be/clients_hlth_normal_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function upd($s_num)	{
    $msel = 'upd';
    $clients_hlth_normal_row = $this->clients_hlth_normal_model->get_one($s_num);
    $clients_hlth_normal_track_row = (array) $this->clients_hlth_normal_model->get_track($s_num); // 列出所有追蹤紀錄    
    //u_var_dump($clients_hlth_normal_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_hlth_normal_row',$clients_hlth_normal_row);
    $this->tpl->assign('tv_clients_hlth_normal_track_row',$clients_hlth_normal_track_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth_normal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth_normal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth_normal/"); // 上一層連結位置
    $this->tpl->display("be/clients_hlth_normal_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function del($s_num=NULL)	{
    $rtn_msg = $this->clients_hlth_normal_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'clients_hlth_normal/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除,上下架)
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function save($kind=NULL)	{
    switch($kind) {
      case "add":
      case "cpy":
        $this->clients_hlth_normal_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->clients_hlth_normal_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->clients_hlth_normal_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function que($q_str)	{
    ////$data = $this->input->post(); // POST 用 Mark by Tony 2020/7/27
    ////u_var_dump($data);
    ////exit;
    ////if('que'==$data['que_kind']) {
    ////  $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    ////}
    ////$_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    ////$_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    ////redirect(be_url()."clients_hlth_normal/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."clients_hlth_normal/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  private function _que_start($q_str)	{
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }
  // **************************************************************************
  //  函數名稱: prn()
  //  函數功能: 列印
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function prn()	{
    $msel = 'prn';
    return;
  }
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載營養評估表
  //  程式設計: kiwi
  //  設計日期: 2022-08-04(七夕ㄟ!!!!!)
  // **************************************************************************
  public function download() {
    $msel = 'download';
    $file_time = date("YmdHis");

    // 需另外處理
    $memo = array('chn13_opt_99_memo', 'chn25_memo', 'chn29_memo', 'chn73_1_memo', 'chn73_2_memo', 'chn73_3_memo', 'chn73_4_memo', 'chn73_99_memo', 'chn92_99_memo');
    $input = array('chn31', 'chn51', 'chn52', 'chn53', 'chn71', 'chn72', 'chn93', 'chn94', 'chn95');
    $other['chn13_opt_1'] = array("Y", "N");
    $other['chn13_opt_2'] = array("Y", "N");
    $other['chn13_opt_3'] = array("Y", "N");
    $other['chn13_opt_99'] = array("Y", "N");
    $other['chn14_opt_1'] = array("Y", "N");
    $other['chn14_opt_2'] = array(1, 2);
    $other['chn23_opt1'] = array("Y", "N");
    $other['chn32'] = array(1, 2, 3, 4);
    $other['chn33'] = array(1, 2, 3);

    $v = $this->input->post();
    $ct06 = '';
    $reh01 = ''; //路線
    $sec02 = ''; // 開案日期
    $howold = ''; // 幾歲
    $clients_hlth_normal_row = $this->clients_hlth_normal_model->get_one($v['s_num']); // 列出單筆明細資料

    $clients_row = $this->clients_model->get_one($clients_hlth_normal_row->chn02_ct_s_num);
    // 計算今年幾歲
    if(NULL != $clients_row->ct05) {
      list($ct05_year, $ct05_month, $ct05_date) = explode("-", $clients_row->ct05);
      $howold = date("Y") - $ct05_year;
    }

    if(NULL != $clients_row->ct06_telephone) {
      $ct06 = $clients_row->ct06_telephone;
    }
    else {
      $ct06 = $clients_row->ct06_homephone;
    }

    $service_case_row = $this->service_case_model->get_all_by_ct_s_num($clients_hlth_normal_row->chn02_ct_s_num, date("Y-m-d"));
    if(NULL != $service_case_row) {
      foreach ($service_case_row as $k => $v) {
        $route_row = $this->route_model->que_client_route($v['reh_type'] , $v['ct_s_num']);
        $reh01 = $route_row->reh01;
        $sec02 = $v['sec02'];
      }
    }

    $sample_file = FCPATH."pub/sample/111_client_hlth_normal_sample.docx";
    $save_path = FCPATH."export_file/client_hlth_normal_{$file_time}.docx";

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    // 案主資訊及填表資訊
    $templateProcessor->setValue('ct_name', "{$clients_row->ct01}{$clients_row->ct02}");
    $templateProcessor->setValue('ct14', "{$clients_row->ct14}");
    $templateProcessor->setValue('howold', "{$howold}");
    $templateProcessor->setValue('reh01', "{$reh01}");
    $templateProcessor->setValue('ct06', "{$ct06}");
    $templateProcessor->setValue('sec02', "{$sec02}");
    $templateProcessor->setValue('ct35_type_str', "{$clients_row->ct35_type_str}");

    // 判斷性別
    if("M" == $clients_row->ct04) {
      $templateProcessor->setValue('ct04_M', "■");
      $templateProcessor->setValue('ct04_Y', "□");
    }
    else {
      $templateProcessor->setValue('ct04_M', "□");
      $templateProcessor->setValue('ct04_Y', "■");
    }

    foreach (array(1, 2, 3, 4) as $v) {
      if($v == $clients_row->ct35_level) {
        $templateProcessor->setValue("ct35_level_{$v}", "■");
      }
      else {
        $templateProcessor->setValue("ct35_level_{$v}", "□");
      }
    }

    foreach ($clients_hlth_normal_row as $k => $v) {
      if(in_array($k, $memo) or in_array($k, $input) or in_array($k, $other)) {
        continue;
      }
      if("Y" == $v) {
        $templateProcessor->setValue("{$k}", "■");
      }
      else {
        $templateProcessor->setValue("{$k}", "□");
      }
    }

    foreach ($memo as $v) {
      $templateProcessor->setValue("{$v}", "{$clients_hlth_normal_row->$v}");
    }

    foreach ($input as $v) {
      $templateProcessor->setValue("{$v}", "{$clients_hlth_normal_row->$v}");
    }

    foreach ($other as $fd => $fd_val) {
      foreach ($fd_val as $k => $v) {
        if($clients_hlth_normal_row->$fd == $v) {
          $templateProcessor->setValue("{$fd}_{$v}", '●');
        }
        else {
          $templateProcessor->setValue("{$fd}_{$v}", '○');
        }
      }
    }

    $templateProcessor->saveAs($save_path);
    $ch_filename = "{$clients_row->ct01}{$clients_row->ct02}_營養評估表.docx"; 
    $en_filename = "client_hlth_normal_{$file_time}.docx";
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename); 
    echo json_encode($rtn_msg);
    return;
  }
  // **************************************************************************
  //  函數名稱: download2()
  //  函數功能: 下載營養評估表-追蹤紀錄表
  //  程式設計: kiwi
  //  設計日期: 2022-08-04(七夕ㄟ!!!!!)
  // **************************************************************************
  public function download2() {
    $msel = 'download';
    $file_time = date("YmdHis");
    $select_fd = array('chnt22_1', 'chnt22_2', 'chnt22_3', 'chnt22_4', 'chnt22_5', 'chnt22_6', 'chnt22_7',
                       'chnt23_1', 'chnt23_2', 'chnt23_3', 'chnt23_4', 'chnt23_5', 'chnt23_99'
                      );

    $v = $this->input->post();
    $howold = ''; // 幾歲

    $clients_hlth_normal_row = $this->clients_hlth_normal_model->get_one($v['s_num']); // 列出單筆明細資料
    $clients_hlth_normal_track_row = $this->clients_hlth_normal_model->get_track($v['s_num']); // 列出所有追蹤紀錄  
    $clients_row = $this->clients_model->get_one($clients_hlth_normal_row->chn02_ct_s_num);
    // 計算今年幾歲
    if(NULL != $clients_row->ct05) {
      list($ct05_year, $ct05_month, $ct05_date) = explode("-", $clients_row->ct05);
      $howold = date("Y") - $ct05_year;
    }
    
    $sample_file = FCPATH."pub/sample/111_client_hlth_normal_track_sample.docx";
    $save_path = FCPATH."export_file/client_hlth_normal_track_{$file_time}.docx";

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    $templateProcessor->setValue('ct_name', "{$clients_row->ct01}{$clients_row->ct02}");
    $templateProcessor->setValue('ct14', "{$clients_row->ct14}");
    $templateProcessor->setValue('howold', "{$howold}");
    // 判斷性別
    if("M" == $clients_row->ct04) {
      $templateProcessor->setValue('ct04_M', "■");
      $templateProcessor->setValue('ct04_Y', "□");
    }
    else {
      $templateProcessor->setValue('ct04_M', "□");
      $templateProcessor->setValue('ct04_Y', "■");
    }

    if(NULL != $clients_hlth_normal_track_row) {
      $templateProcessor->cloneBlock('chnt_block', count((array) $clients_hlth_normal_track_row), true, true);
      $i = 1;
      foreach ($clients_hlth_normal_track_row as $k_dnt => $v_dnt) {
        foreach ($v_dnt as $fd => $fd_value) {
          if($fd == 'chnt21') {
            if(1 == $fd_value) {
              $templateProcessor->setValue("chnt21_1#{$i}", "■");
              $templateProcessor->setValue("chnt21_2#{$i}", "□");
            }
            else {
              $templateProcessor->setValue("chnt21_1#{$i}", "□");
              $templateProcessor->setValue("chnt21_2#{$i}", "■");
            }
            continue;
          }
          if(!in_array($fd, $select_fd)) {
            $templateProcessor->setValue("{$fd}#{$i}", "{$fd_value}");
            // $templateProcessor->setValue("chnt11#{$i}", "{$v_dnt['chnt11']}");
            // $templateProcessor->setValue("chnt12#{$i}", "{$v_dnt['chnt12']}");
            // $templateProcessor->setValue("chnt13#{$i}", "{$v_dnt['chnt13']}");
            // $templateProcessor->setValue("chnt23_99_memo#{$i}", "{$v_dnt['chnt23_99_memo']}");
            // $templateProcessor->setValue("chnt24#{$i}", "{$v_dnt['chnt24']}");
            // $templateProcessor->setValue("chnt25#{$i}", "{$v_dnt['chnt25']}");
            // $templateProcessor->setValue("chnt26#{$i}", "{$v_dnt['chnt26']}");
          }
          else {
            if("Y" == $fd_value) {
              $templateProcessor->setValue("{$fd}#{$i}", "■");
            }
            else {
              $templateProcessor->setValue("{$fd}#{$i}", "□");
            }
          }
        }
        $i++;
      }
    }
    else {
      $templateProcessor->deleteBlock('chnt_block');
    }

    $templateProcessor->saveAs($save_path);
    $ch_filename = "{$clients_row->ct01}{$clients_row->ct02}_營養評估追蹤紀錄表.docx"; 
    $en_filename = "client_hlth_normal_track_{$file_time}.docx";
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename); 
    echo json_encode($rtn_msg);
  }

  function __destruct() {
    // ajax 用，如果有回傳 echo 於畫面上的，就加上
    $url_str[] = 'be/clients_hlth_normal/save';
    $url_str[] = 'be/clients_hlth_normal/del';
    $url_str[] = 'be/clients_hlth_normal/download';
    $url_str[] = 'be/clients_hlth_normal/download2';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 footer
    }
  }
}
?>