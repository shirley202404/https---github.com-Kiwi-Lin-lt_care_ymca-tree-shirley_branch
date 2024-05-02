<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dietitian_note extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 header
    $this->load->model('dietitian_note_model'); // 照會營養師
    $this->load->model('service_case_complaint_model'); // 客訴處理單
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8'); 
    $this->tpl->assign('tv_rand_str',$mrand_str); // html載入的js與css增加亂數，避免抓到cache的檔案
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','照會營養師');
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
    $this->tpl->assign('tv_add_link',be_url().'dietitian_note/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."dietitian_note/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
    $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
    $this->tpl->assign('tv_import_ok',$this->lang->line('import_ok')); // 匯入資料成功!!
    $this->tpl->assign('tv_over_ok',$this->lang->line('over_ok')); // 結案成功!!
    $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
    $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
    $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
    $this->tpl->assign('tv_import_ng',$this->lang->line('import_ng')); // 匯入資料失敗!!
    $this->tpl->assign('tv_over_ng',$this->lang->line('over_ng')); // 結案失敗!!
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/dietitian_note_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_service_case_complaint_add_link',be_url()."service_case_complaint/add/"); // 新增客訴處理單
    $this->tpl->assign('tv_service_case_complaint_upd_link',be_url()."service_case_complaint/upd/"); // 修改客訴處理單
    //if('tony' != $_SESSION['acc_user']) { // 開發階段不給客戶使用的時候用
    //  die('趕工中...');
    //}

    $this->dnn02 = array(1, 2, 3, 4);
    $this->dnn02_str = array('無需處理', '原因', '照會單位', '連結客訴單');
    return;
  }
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
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

    $source = 'meal';
    $source_arr['meal'] = 'active';
    $source_arr['item'] = ""; 
    if(isset($get_data['que_source'])) {
      if('' != $get_data['que_source']) {
        $source = $get_data['que_source'];
        $source_arr['meal'] = ""; 
        $source_arr["{$source}"] = "active"; 
      }
    }

    list($dietitian_note_row,$row_cnt) = $this->dietitian_note_model->get_que($q_str,$pg); // 列出照會營養師
    if(NULL != $dietitian_note_row) {
      foreach ($dietitian_note_row as $k => $v) {
        $dietitian_note_row[$k]['sect'] = NULL;
        $dietitian_note_row[$k]['track_cnt'] = 0;
        $dietitian_note_row[$k]['dnn02_str'] = str_replace($this->dnn02, $this->dnn02_str, $v['dnn02']);
        if(NULL != $v['dnn_s_num']) {
          $dietitian_note_row[$k]['track_cnt'] = count((array) $this->dietitian_note_model->get_track($v['dnn_s_num']));
        }
        if(NULL != $v['dnn01_source_s_num']) {
          $dietitian_note_row[$k]['sect'] = $this->service_case_complaint_model->que_by_dnn($source, $v['dnn01_source_s_num']);
        }
      }
    } 
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_source',$source);
    $this->tpl->assign('tv_source_arr',$source_arr);
    $this->tpl->assign('tv_title','照會營養師瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'dietitian_note/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'dietitian_note/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'dietitian_note/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'dietitian_note/upd/');
    $this->tpl->assign('tv_del_link',be_url().'dietitian_note/del/');
    $this->tpl->assign('tv_prn_link',be_url().'dietitian_note/prn/');
    $this->tpl->assign('tv_over_link',be_url().'dietitian_note/over/');
    $this->tpl->assign('tv_download_link',be_url().'dietitian_note/download/');
    $this->tpl->assign('tv_que_link',be_url()."dietitian_note/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'dietitian_note/save/');
    $this->tpl->assign('tv_meal_link',be_url().'dietitian_note/?que_source=meal');
    $this->tpl->assign('tv_item_link',be_url().'dietitian_note/?que_source=item');
    $this->tpl->assign('tv_service_case_complaint_link',be_url()."service_case_complaint/disp/"); // 返回連結位置
    $this->tpl->assign('tv_dietitian_note_row',$dietitian_note_row);
    $config['base_url'] = be_url()."dietitian_note/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/dietitian_note/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/dietitian_note.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function disp($dnn01_source_s_num) {
    $msel = 'disp';
    $source = '';
    $get_data = $this->input->get(); // GET 用
    if(isset($get_data['que_source'])) {
      $source = $get_data['que_source'];
    }
    $dietitian_note_row = $this->dietitian_note_model->get_one($dnn01_source_s_num);
    $dietitian_note_row->dnn02_arr = (array) explode(",", $dietitian_note_row->dnn02);
    $service_case_complaint_row = $this->service_case_complaint_model->que_by_dnn($source, $dnn01_source_s_num);
    $dietitian_track_row = array();
    if(NULL != $dietitian_note_row->dnn_s_num) {
      $dietitian_track_row = (array) $this->dietitian_note_model->get_track($dietitian_note_row->dnn_s_num);
    }
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_source',$source);
    $this->tpl->assign('tv_dietitian_note_row',$dietitian_note_row);
    $this->tpl->assign('tv_dietitian_track_row', $dietitian_track_row);
    $this->tpl->assign('tv_service_case_complaint_row', $service_case_complaint_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."dietitian_note/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."dietitian_note/?que_source={$source}}"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."dietitian_note/?que_source={$source}"); // 上一層連結位置
    $this->tpl->assign('tv_return_link',be_url()."dietitian_note/?que_source={$source}"); // 返回連結位置
    $this->tpl->assign('tv_upd_link',be_url().'dietitian_note/upd/');
    $this->tpl->assign('tv_service_case_complaint_link',be_url()."service_case_complaint/disp/"); // 返回連結位置
    $this->tpl->display("be/dietitian_note_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function add($dnn01_source_s_num)	{ // 新增
    $msel = 'add';
    $source = '';
    $get_data = $this->input->get(); // GET 用
    if(isset($get_data['que_source'])) {
      $source = $get_data['que_source'];
    }

    $dietitian_note_row = $this->dietitian_note_model->get_one($dnn01_source_s_num);
    $dietitian_note_row->dnn02_arr = array();
    $dietitian_track_row = array();
    $service_case_complaint_row = NULL;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_source',$source);
    $this->tpl->assign('tv_dnn01_source_s_num',$dnn01_source_s_num);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_dietitian_note_row',(object) $dietitian_note_row);
    $this->tpl->assign('tv_dietitian_track_row', $dietitian_track_row);
    $this->tpl->assign('tv_service_case_complaint_row', $service_case_complaint_row);
    $this->tpl->assign('tv_save_link',be_url()."dietitian_note/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."dietitian_note/?que_source={$source}}"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."dietitian_note/?que_source={$source}"); // 上一層連結位置
    $this->tpl->assign('tv_return_link',be_url()."dietitian_note/?que_source={$source}"); // 返回連結位置
    $this->tpl->display("be/dietitian_note_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function cpy($s_num)	{
    $msel = 'cpy';
    $dietitian_note_row = $this->dietitian_note_model->get_one($s_num);
    //u_var_dump($dietitian_note_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_dietitian_note_row',$dietitian_note_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."dietitian_note/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."dietitian_note/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."dietitian_note/"); // 上一層連結位置
    $this->tpl->display("be/dietitian_note_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function upd($dnn01_source_s_num)	{
    $msel = 'upd';
    $source = '';
    $get_data = $this->input->get(); // GET 用
    if(isset($get_data['que_source'])) {
      $source = $get_data['que_source'];
    }

    $dietitian_note_row = $this->dietitian_note_model->get_one($dnn01_source_s_num);
    $dietitian_note_row->dnn02_arr = (array) explode(",", $dietitian_note_row->dnn02);
    $service_case_complaint_row = $this->service_case_complaint_model->que_by_dnn($source, $dnn01_source_s_num);
    $dietitian_track_row = array();
    if(NULL != $dietitian_note_row->dnn_s_num) {
      $dietitian_track_row = (array) $this->dietitian_note_model->get_track($dietitian_note_row->dnn_s_num);
    }
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_source',$source);
    $this->tpl->assign('tv_dnn01_source_s_num',$dnn01_source_s_num);
    $this->tpl->assign('tv_dietitian_note_row',$dietitian_note_row);
    $this->tpl->assign('tv_dietitian_track_row',$dietitian_track_row);
    $this->tpl->assign('tv_service_case_complaint_row',$service_case_complaint_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."dietitian_note/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."dietitian_note/?que_source={$source}}"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."dietitian_note/?que_source={$source}"); // 上一層連結位置
    $this->tpl->assign('tv_return_link',be_url()."dietitian_note/?que_source={$source}"); // 返回連結位置
    $this->tpl->display("be/dietitian_note_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function del($s_num=NULL)	{
    $rtn_msg = $this->dietitian_note_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'dietitian_note/', 'refresh');
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
  //  設計日期: 2022-07-20
  // **************************************************************************
  public function over($s_num=NULL) {
    $rtn_url = $this->agent->referrer();
    $rtn_msg = $this->dietitian_note_model->over($s_num); // 結案
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
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除,上下架)
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function save($kind=NULL)	{
    switch($kind) {
      case "add":
      case "cpy":
        $this->dietitian_note_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->dietitian_note_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->dietitian_note_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
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
    ////redirect(be_url()."dietitian_note/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."dietitian_note/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function prn()	{
    $msel = 'prn';
    return;
  }

  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載追蹤紀錄表
  //  程式設計: kiwi
  //  設計日期: 2022-07-27
  // **************************************************************************
  public function download() {
    $msel = 'download';
    $source_str = '';
    $file_time = date("YmdHis");
    $fd_note['checkbox']["dnn02_arr"] = array(1, 2, 3, 4); // 營養師回覆(1=無需處理, 2=原因, 3=照會單位, 4=連結)
    $fd_note['radio']["dnn02_03_opt"] = array(1, 2, 3, 4, 5, 6); // 營養師回覆(1=社工組, 2=膳務組, 3=倉管組, 4=行政組, 5=交通組, 6=志工組)
    $fd_track['checkbox']["dnt04_type"] = array(1, 99); // 是否持續追蹤(1=持續追蹤, 99=結案)

    $sample_file = FCPATH."pub/sample/111_dietitian_note_sample.docx";
    $save_path = FCPATH."export_file/dietitian_note_{$file_time}.docx";
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    
    $v = $this->input->post();
    $dietitian_note_row = $this->dietitian_note_model->get_one($v['source_s_num']);
    if(NULL != $dietitian_note_row->dnn_s_num) {
      $dietitian_track_row = (array) $this->dietitian_note_model->get_track($dietitian_note_row->dnn_s_num);
      $dietitian_note_row->dnn02_arr = explode(",", $dietitian_note_row->dnn02);
      if('meal' == $v['source']) {
        $source_str = '餐點';
        $dietitian_note_row->memo99 = htmlspecialchars($dietitian_note_row->mil99);
      }
      else {
        $source_str = '照會營養師';
        $dietitian_note_row->memo99 = htmlspecialchars($dietitian_note_row->ocl99);
      }
      if(NULL != $dietitian_track_row) {
        $templateProcessor->setValue('source', $source_str);
        $templateProcessor->setValue('b_date', "{$dietitian_note_row->b_date}");
        $templateProcessor->setValue('e_date', "{$dietitian_note_row->e_date}");
        $templateProcessor->setValue('ct_name', "{$dietitian_note_row->ct01}{$dietitian_note_row->ct02}");
        $templateProcessor->setValue('memo', "{$dietitian_note_row->memo99}");
        $templateProcessor->setValue('dnn02_2_memo', "{$dietitian_note_row->dnn02_02_memo}");
        foreach ($fd_note as $k_type => $v_type) {
          foreach($v_type as $k => $v) {
            foreach($v as $each_optiion) {
              if('checkbox' == $k_type) {
                if(in_array($each_optiion, $dietitian_note_row->$k)) {
                  $templateProcessor->setValue("{$k}_{$each_optiion}", "☒");
                }
                else {
                  $templateProcessor->setValue("{$k}_{$each_optiion}", "☐");
                }
              }
              if('radio' == $k_type) {
                if($dietitian_note_row->$k == $each_optiion) {
                  $templateProcessor->setValue("{$k}_{$each_optiion}", "●");
                }
                else {
                  $templateProcessor->setValue("{$k}_{$each_optiion}", "○");
                }
              }
            }
          }
        }

        $templateProcessor->cloneBlock('dnt_block', count($dietitian_track_row), true, true);
        $i = 1;
        foreach ($dietitian_track_row as $k_dnt => $v_dnt) {
          $templateProcessor->setValue("dnt01#{$i}", "{$v_dnt['dnt01']}");
          $templateProcessor->setValue("dnt02#{$i}", "{$v_dnt['dnt02']}");
          $templateProcessor->setValue("dnt03#{$i}", "{$v_dnt['dnt03']}");
          foreach ($fd_track as $k_type => $v_type) {
            foreach($v_type as $k => $v) {
              foreach($v as $each_optiion) {
                if('checkbox' == $k_type) {
                  if($v_dnt['dnt04_type'] == $each_optiion) {
                    $templateProcessor->setValue("{$k}_{$each_optiion}#{$i}", "☒");
                  }
                  else {
                    $templateProcessor->setValue("{$k}_{$each_optiion}#{$i}", "☐");
                  }
                }
              }
            }
          }
          $i++;
        }
      }
      else {
        $templateProcessor->deleteBlock('dnt_block');
      }
    }

    $templateProcessor->saveAs($save_path);
    $ch_filename = "{$dietitian_note_row->ct01}{$dietitian_note_row->ct02}_{$source_str}追蹤紀錄表.docx"; 
    $en_filename = "dietitian_note_{$file_time}.docx";
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename); 
    echo json_encode($rtn_msg);
    return;
  }

  function __destruct() {
    // ajax 用，如果有回傳 echo 於畫面上的，就加上
    $url_str[] = 'be/dietitian_note/save';    
    $url_str[] = 'be/dietitian_note/del';
    $url_str[] = 'be/dietitian_note/over';
    $url_str[] = 'be/dietitian_note/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 footer
    }
  }
}
?>