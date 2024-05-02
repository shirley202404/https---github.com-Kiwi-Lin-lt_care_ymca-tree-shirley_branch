<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Punch_log extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('route_model'); // 服務路徑資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('delivery_person_model'); // 送餐員資料
    $this->load->model('punch_log_model'); // 打卡紀錄資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','打卡紀錄資料');
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
    $this->tpl->assign('tv_add_link',be_url().'punch_log/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."punch_log/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/punch_log_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_return_list_link',be_url()."work_q_route_list/"); // return 預設到路線列表畫面

    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    return;
  }

  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function index($pg=1,$q_str=NULL) {
    $msel = 'list';
    $reh_s_num = '';
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
    if(isset($get_data['que_reh_s_num'])) {
      $reh_s_num = $get_data['que_reh_s_num'];
    }
    if(!isset($get_data['que_phl01_start'])) {
      $get_data['que_phl01_start'] = NULL;
    }
    if(!isset($get_data['que_phl01_end'])) {
      $get_data['que_phl01_end'] = NULL;
    }
    list($punch_log_row,$row_cnt) = $this->punch_log_model->get_que($q_str,$pg); // 列出打卡紀錄資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','打卡紀錄資料瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'punch_log/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'punch_log/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'punch_log/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'punch_log/upd/');
    $this->tpl->assign('tv_del_link',be_url().'punch_log/del/');
    $this->tpl->assign('tv_prn_link',be_url().'punch_log/prn/');
    $this->tpl->assign('tv_download_link',be_url().'punch_log/download/');
    $this->tpl->assign('tv_que_link',be_url()."punch_log/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_phl01_start',$get_data['que_phl01_start']); // 查詢種類
    $this->tpl->assign('tv_que_phl01_end',$get_data['que_phl01_end']); // 查詢種類
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'punch_log/save/');
    $this->tpl->assign('tv_punch_log_row',$punch_log_row);
    $this->tpl->assign('tv_reh_s_num',$reh_s_num);
    $config['base_url'] = be_url()."punch_log/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/punch_log/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

    $this->tpl->display("be/punch_log.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $punch_log_row = $this->punch_log_model->get_one($s_num); // 列出單筆明細資料
    //u_var_dump($punch_log_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_punch_log_row',$punch_log_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."punch_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."punch_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."punch_log/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'punch_log/upd/');
    $this->tpl->display("be/punch_log_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function add() { // 新增
    $msel = 'add';
    $punch_log_row = NULL;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_punch_log_row',$punch_log_row);
    $this->tpl->assign('tv_save_link',be_url()."punch_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."punch_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."punch_log/"); // 上一層連結位置
    $this->tpl->display("be/punch_log_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function cpy($s_num) {
    $msel = 'cpy';
    $punch_log_row = $this->punch_log_model->get_one($s_num);
    //u_var_dump($punch_log_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_punch_log_row',$punch_log_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."punch_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."punch_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."punch_log/"); // 上一層連結位置
    $this->tpl->display("be/punch_log_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function upd($s_num) {
    $msel = 'upd';
    $punch_log_row = $this->punch_log_model->get_one($s_num);
    //u_var_dump($punch_log_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_punch_log_row',$punch_log_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."punch_log/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."punch_log/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."punch_log/"); // 上一層連結位置
    $this->tpl->display("be/punch_log_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->punch_log_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'punch_log/', 'refresh');
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
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "add":
      case "cpy":
        $this->punch_log_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->punch_log_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->punch_log_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
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
    ////redirect(be_url()."punch_log/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."punch_log/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }

  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
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
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function prn() {
    $msel = 'prn';
    return;
  }

  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 列印
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_msg = '';
    $msel = 'download';
    $time_start = date('Y-m-d H:i:s');
    $route_row = $this->route_model->get_one($this->input->post('reh_s_num'));
    $punch_log_row = $this->punch_log_model->que_download_data();
    
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sample_file = FCPATH."pub/sample/punch_sample.xlsx";
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
    $objSpreadsheet->setActiveSheetIndex(0);
    
    if(NULL != $punch_log_row) {
      $row = 2;
      foreach ($punch_log_row as $k => $v) {
        $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", "{$v['dp01']}{$v['dp02']}"); // 打卡人
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", "{$v['reh01']}"); // 路線
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", "{$v['ct_name']}"); // 個案姓名
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", "{$v['phl01']}"); // 打卡時間
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", "{$v['phl02_str']}"); // 打卡型態
        $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", "{$v['phl50_str']}"); // 打卡方式
        $row++;
      } 
    }
    
    $filename = "punch_data.xlsx"; // {$route_row->reh01}
    switch(ENVIRONMENT) {
      case 'development':
      case 'testing':
        $download_file_big5 = iconv('big5','utf-8',$filename);
        //$download_file = rawurlencode($download_file);
        break;
      case 'production':
        $download_file_big5 = $filename;
        break;
    }
    
    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment;filename=" . $filename);
    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
    $objWriter->save(FCPATH."export_file/{$filename}"); // 儲存到server
    // $objWriter->save('php://output');

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff >= 60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }   
    
    $zh_file_name = "";
    if(!empty($_GET['que_phl01_start'])) {
      $phl01_start = str_replace("-" , "_" , $_GET['que_phl01_start']);
      $zh_file_name .= "{$phl01_start}";
      if(!empty($_GET['que_phl01_end'])) {
        $zh_file_name .= "~";
      }
    }
    if(!empty($_GET['que_phl01_end'])) {
      $phl01_end = str_replace("-" , "_" , $_GET['que_phl01_end']);
      $zh_file_name .= "{$phl01_end}_";
    }

    $zh_file_name .= "打卡紀錄.xlsx";
    $download_file_name = base64url_encode($zh_file_name);
    $download_file_big5_en = base64url_encode($download_file_big5);
    $be_url = be_url()."punch_log/download_file/{$download_file_name}/";
    $rtn_msg .= "<table class='table table-bordered table-hover'>";
    $rtn_msg .= "  <thead>";
    $rtn_msg .= "    <tr>";
    $rtn_msg .= "      <th width='20%'>項目</th>";
    $rtn_msg .= "      <th width='80%'>說明</th>";
    $rtn_msg .= "    </tr>";
    $rtn_msg .= "  </thead>";
    $rtn_msg .= "  <tbody>";
    $rtn_msg .= "      <td>下載檔案</th>";
    $rtn_msg .= "      <td>{$zh_file_name}&nbsp; <button class='btn btn-C3 btn-sm' type='button' onclick='location.href=\"{$be_url}{$download_file_big5_en}\"'>檔案下載</button></td>";
    $rtn_msg .= "    </tr>";
    $rtn_msg .= "    <tr>";
    $rtn_msg .= "      <td>處理時間</th>";
    $rtn_msg .= "      <td>{$time_diff}</td>";
    $rtn_msg .= "    </tr>";
    $rtn_msg .= "  </tbody>";
    $rtn_msg .= "</table>";
    
    echo json_encode($rtn_msg);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: download_file()
  //  函數功能: 檔案下載
  //  程式設計: Kiwi
  //  設計日期: 2021/06/05
  // **************************************************************************
  public function download_file($download_file_name , $save_file_name) {    
    $download_file_name = base64url_decode($download_file_name);
    $save_file_name = base64url_decode($save_file_name);
    $path = FCPATH."export_file/";
    $this->zi_my_func->download_file($download_file_name , "{$path}{$save_file_name}");
    return;
  }

  function __destruct() {
    $url_str[] = 'be/punch_log/save';
    $url_str[] = 'be/punch_log/del';
    $url_str[] = 'be/punch_log/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
