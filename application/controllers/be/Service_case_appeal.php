<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Service_case_appeal extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 header
    $this->load->model('route_model'); // 路線資料
    $this->load->model('service_case_appeal_model'); // 申訴處理單-社工
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8'); 
    $this->tpl->assign('tv_rand_str',$mrand_str); // html載入的js與css增加亂數，避免抓到cache的檔案
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','申訴處理單-社工');
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
    $this->tpl->assign('tv_add_link',be_url().'service_case_appeal/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."service_case_appeal/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/service_case_appeal_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
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
  //  設計日期: 2022-09-14
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
    list($service_case_appeal_row,$row_cnt) = $this->service_case_appeal_model->get_que($q_str,$pg); // 列出申訴處理單-社工
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','申訴處理單-社工瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'service_case_appeal/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'service_case_appeal/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'service_case_appeal/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'service_case_appeal/upd/');
    $this->tpl->assign('tv_del_link',be_url().'service_case_appeal/del/');
    $this->tpl->assign('tv_prn_link',be_url().'service_case_appeal/prn/');
    $this->tpl->assign('tv_download_link',be_url().'service_case_appeal/download/');
    $this->tpl->assign('tv_que_link',be_url()."service_case_appeal/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'service_case_appeal/save/');
    $this->tpl->assign('tv_service_case_appeal_row',$service_case_appeal_row);
    $config['base_url'] = be_url()."service_case_appeal/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/service_case_appeal/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/service_case_appeal.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $service_case_appeal_row = $this->service_case_appeal_model->get_one($s_num); // 列出單筆明細資料
    $service_case_appeal_track_row = (array) $this->service_case_appeal_model->get_track($s_num);

    //u_var_dump($service_case_appeal_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_service_case_appeal_row',$service_case_appeal_row);
    $this->tpl->assign('tv_service_case_appeal_track_row',$service_case_appeal_track_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."service_case_appeal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case_appeal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case_appeal/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'service_case_appeal/upd/');
    $this->tpl->display("be/service_case_appeal_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function add()	{ // 新增
    $msel = 'add';
    $service_case_appeal_row = NULL;
    $service_case_appeal_track_row = array();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_service_case_appeal_row',$service_case_appeal_row);
    $this->tpl->assign('tv_service_case_appeal_track_row',$service_case_appeal_track_row);
    $this->tpl->assign('tv_save_link',be_url()."service_case_appeal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case_appeal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case_appeal/"); // 上一層連結位置
    $this->tpl->display("be/service_case_appeal_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function cpy($s_num)	{
    $msel = 'cpy';
    $service_case_appeal_row = $this->service_case_appeal_model->get_one($s_num);
    $service_case_appeal_track_row = (array) $this->service_case_appeal_model->get_track($s_num);

    //u_var_dump($service_case_appeal_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_service_case_appeal_row',$service_case_appeal_row);
    $this->tpl->assign('tv_service_case_appeal_track_row',$service_case_appeal_track_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."service_case_appeal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case_appeal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case_appeal/"); // 上一層連結位置
    $this->tpl->display("be/service_case_appeal_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function upd($s_num)	{
    $msel = 'upd';
    $service_case_appeal_row = $this->service_case_appeal_model->get_one($s_num);
    $service_case_appeal_track_row = (array) $this->service_case_appeal_model->get_track($s_num);
    //u_var_dump($service_case_appeal_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_service_case_appeal_row',$service_case_appeal_row);
    $this->tpl->assign('tv_service_case_appeal_track_row',$service_case_appeal_track_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."service_case_appeal/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."service_case_appeal/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."service_case_appeal/"); // 上一層連結位置
    $this->tpl->display("be/service_case_appeal_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function del($s_num=NULL)	{
    $rtn_msg = $this->service_case_appeal_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'service_case_appeal/', 'refresh');
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function save($kind=NULL)	{
    switch($kind) {
      case "add":
      case "cpy":
        $this->service_case_appeal_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->service_case_appeal_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->service_case_appeal_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
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
    ////redirect(be_url()."service_case_appeal/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."service_case_appeal/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function prn()	{
    $msel = 'prn';
    return;
  }
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載追蹤紀錄表
  //  程式設計: kiwi
  //  設計日期: 2022-09-15
  // **************************************************************************
  public function download() {
    $msel = 'download';
    $source_str = '';
    $file_time = date("YmdHis");
    $fd['checkbox']["seca01"] = array(1, 2, 3); // 客訴類型 (1=長照；2=老人；3=身障)

    $sample_file = FCPATH."pub/sample/111_service_case_appeal.docx";
    $save_path = FCPATH."export_file/service_case_appeal_{$file_time}.docx";
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    
    $v = $this->input->post();
    $service_case_appeal_row = $this->service_case_appeal_model->get_one($v['s_num']); // 列出單筆明細資料
    $service_case_appeal_track_row = (array) $this->service_case_appeal_model->get_track($v['s_num']);

    $reh01_str = '';
    $route_row = $this->route_model->get_all_by_ct($service_case_appeal_row->ct_s_num);
    if(NULL != $route_row) {
      $reh01_arr = array_column($route_row, "reh01");
      $reh01_str = join("、", $reh01_arr);
    }
    if(NULL != $service_case_appeal_row) {
      $templateProcessor->setValue('reh01', "{$reh01_str}");
      $templateProcessor->setValue('ct14', "{$service_case_appeal_row->ct14}");
      $templateProcessor->setValue('ct_name', "{$service_case_appeal_row->ct01}{$service_case_appeal_row->ct02}");
      $templateProcessor->setValue('seca11', "{$service_case_appeal_row->seca11}");
      $templateProcessor->setValue('seca12', "{$service_case_appeal_row->seca12}");
      $templateProcessor->setValue('seca13', "{$service_case_appeal_row->seca13}");
      $templateProcessor->setValue('seca14', "{$service_case_appeal_row->seca14}");
      $templateProcessor->setValue('seca34', "{$service_case_appeal_row->seca34}");
      $templateProcessor->setValue('seca35', "{$service_case_appeal_row->seca35}");
      foreach ($fd as $k_type => $v_type) {
        foreach($v_type as $k => $v) {
          foreach($v as $each_optiion) {
            if('checkbox' == $k_type) {
              if($service_case_appeal_row->$k == $each_optiion) {
                $templateProcessor->setValue("{$k}_{$each_optiion}", "☒");
              }
              else {
                $templateProcessor->setValue("{$k}_{$each_optiion}", "☐");
              }
            }
          }
        }
      }
    }

    $i = 1;
    if(NULL != $service_case_appeal_track_row) {
      $templateProcessor->cloneBlock('secta_block', count($service_case_appeal_track_row), true, true);
      foreach ($service_case_appeal_track_row as $k => $v_secat) {
        $templateProcessor->setValue("secat01#{$i}", "{$v_secat['secat01']}");
        $templateProcessor->setValue("secat02#{$i}", "{$v_secat['secat02']}");
        $templateProcessor->setValue("secat03#{$i}", "{$v_secat['secat03']}");
        if("Y" == $v_secat['secat11']) {
          list($year, $month) = explode("-", $v_secat['secat11_date']);
          $templateProcessor->setValue("secat11_Y#{$i}", "☒");
          $templateProcessor->setValue("secat11_N#{$i}", "☐");
          $templateProcessor->setValue("secat11_date_yy#{$i}", $year);
          $templateProcessor->setValue("secat11_date_mm#{$i}", $month);
        }
        else {
          $templateProcessor->setValue("secat11_Y#{$i}", "☐");
          $templateProcessor->setValue("secat11_N#{$i}", "☒");
          $templateProcessor->setValue("secat11_date_yy#{$i}", '');
          $templateProcessor->setValue("secat11_date_mm#{$i}", '');
        }
        $i++;
      }
    }
    else {
      $templateProcessor->deleteBlock('secta_block');
    }

    $templateProcessor->saveAs($save_path);
    $ch_filename = "{$service_case_appeal_row->ct01}{$service_case_appeal_row->ct02}_客訴處理單(其他).docx"; 
    $en_filename = "service_case_appeal_{$file_time}.docx";
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename); 
    echo json_encode($rtn_msg);
    return;
  }

  function __destruct() {
    // ajax 用，如果有回傳 echo 於畫面上的，就加上
    $url_str[] = 'be/service_case_appeal/save';
    $url_str[] = 'be/service_case_appeal/del';
    $url_str[] = 'be/service_case_appeal/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 footer
    }
  }
}
?>