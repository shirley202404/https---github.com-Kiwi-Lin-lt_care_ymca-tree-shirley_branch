<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Rpt_pay extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->library('zip');
    $this->load->model('route_model'); // 送餐資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('service_case_model'); // 服務資料
    $this->load->model('meal_order_model'); // 配送單資料
    $this->load->model('meal_order_date_type_model'); // 訂單日期類型紀錄
    $this->load->model('seal_model'); // 印章設定
    $this->load->model('rpt_pay_model'); // 繳費資料
    $this->load->model('other_change_log_h_model'); // 非餐食異動
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_download_btn',$this->lang->line('download')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."rpt_pay/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"YMCA");
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}  
    $this->fd_tbl_col = '';
    // 補助戶，分成長照案和公所案
    $this->que_data['ownexpense'][0]['sec01'] = 4; // 自費戶
    $this->que_data['subsidy_1'][1]['sec01'] = 1; // 長照案長照案
    $this->que_data['subsidy_2'][2]['sec01'] = 2; // 長照案公所案
    return;
  }
  
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 輸入畫面
  //  程式設計: Kiwi
  //  設計日期: 2021/10/15
  // **************************************************************************
  public function index() {
    $msel = 'download';
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 下載
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','繳費資料');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_prn_link',be_url().'rpt_pay/prn/');
    $this->tpl->assign('tv_download_link',be_url().'rpt_pay/download/');
    $this->tpl->display("be/rpt_pay_input.html");
    return;
  }

  // **************************************************************************
  //  函數名稱: prn
  //  函數功能: 列印資料
  //  程式設計: Kiwi
  //  設計日期: 2021/10/18
  // **************************************************************************
  public function prn() {
    set_time_limit(3600); // 限制處理時間60分鐘
    ini_set('memory_limit', '3072M');
    $msel = 'prn';
    $menu_title = "";
    $q = $this->input->post();
    $get_data = $this->input->get();
    if(isset($get_data['f_que'])){
      $q = $get_data;
    }
    $rpt_type = $q['rpt_type'];
    switch ($rpt_type) {
      case 'subsidy_1':
        $this->fd_tbl_col = 's';
        $menu_title = '補助戶(長照案)收費名冊';
        break;
      case 'subsidy_2':
        $this->fd_tbl_col = 's';
        $menu_title = '補助戶(公所案)收費名冊';
        break;
      case 'ownexpense':
        $this->fd_tbl_col = 'o';
        $menu_title = '自費戶收費名冊';
        break;
    }
    $route_row = $this->route_model->get_all_without_test();
    if(isset($get_data['f_que'])){ // 查詢
      $rpt_pay_data = $this->rpt_pay_model->get_rpt_data_by_month();
      $this->tpl->assign('tv_f_que',$q['f_que']);
    }
    else{
      $rpt_pay_data = $this->_generate_data($q['rpt_pay_month'] , $q['rpt_type']);
      if(NULL != $rpt_pay_data) {
        if($this->rpt_pay_model->save_rpt_data($rpt_pay_data)) {
          $rpt_pay_data = $this->rpt_pay_model->get_rpt_data_by_month();
        }
        else {
          $rpt_pay_data = NULL;
        }
      }
    }
    list($rpt_year, $rpt_month) = explode('-', $q['rpt_pay_month']);
    $taiwan_year = $rpt_year - 1911;
    $receipt_date = $taiwan_year.date('md');
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 下載
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title',$menu_title);
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_rpt_type' ,$rpt_type);
    $this->tpl->assign('tv_fd_tbl_col',$this->fd_tbl_col);
    $this->tpl->assign('tv_menu_title',$menu_title);
    $this->tpl->assign('tv_rpt_pay_data',$rpt_pay_data);
    $this->tpl->assign('tv_prn_date',date('Y-m-d H:i:s'));
    $this->tpl->assign('tv_prn_emp',$_SESSION['acc_name']);
    $this->tpl->assign('tv_rpt_pay_month',$q['rpt_pay_month']);
    $this->tpl->assign('tv_rpt_type',$q['rpt_type']);
    $this->tpl->assign('tv_receipt_date',$receipt_date);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url().'rpt_pay/');
    $this->tpl->assign('tv_upd_reh_link',be_url().'rpt_pay/upd_reh');
    $this->tpl->assign('tv_download_link',be_url()."rpt_pay/download/?rpt_pay_month={$q['rpt_pay_month']}&rpt_type={$rpt_type}");
    $this->tpl->assign('tv_que_link',be_url()."rpt_pay/prn/");
    $this->tpl->display("be/rpt_pay_prn.html");
  }
  // **************************************************************************
  //  函數名稱: upd_reh
  //  函數功能: 更新路徑資料
  //  程式設計: Kiwi
  //  設計日期: 2021/11/11
  // **************************************************************************
  public function upd_reh() {
    $this->rpt_pay_model->upd_reh();
    return;
  }
  // **************************************************************************
  //  函數名稱: download
  //  函數功能: 下載資料
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function download() {
    set_time_limit(3600); // 限制處理時間60分鐘
    ini_set('memory_limit', '3072M');
    $msel = 'prn';
    $get_data = $this->input->get(); 
    switch ($get_data['rpt_type']) {
      case 'subsidy_1': // 補助戶(長照案)
      case 'subsidy_2': // 補助戶(公所案)
        $this->_subsidy();
        break;
      case 'ownexpense':
        $this->_ownexpense();
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: _subsidy
  //  函數功能: 補助戶繳費名冊
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _subsidy() {
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');
    $get_data = $this->input->get(); 
    $subsidy_data = $this->rpt_pay_model->get_rpt_data_by_month();

    if(NULL != $subsidy_data) {
      
      $sub_filename = "{$get_data['rpt_pay_month']}_sub_register_data.xlsx";
      $sub_envelope_filename = "{$get_data['rpt_pay_month']}_sub_envelope_data.xlsx";
      $sub_receipe_filename = "{$get_data['rpt_pay_month']}_sub_receipe_data.xlsx";
      
      $fd_tbl_col = 's';
      list($sec09_1 , $sec09_2 , $sec09_3) = $this->_judge_sec09($fd_tbl_col , $subsidy_data);
      // $route_data = $this->_judge_route($fd_tbl_col , $sec09_1); // 只需要到府收費的

      // 1. 先處理繳費名冊 BEGIN //
      // 長照案繳費名冊不需顯示繳費金額為0元的案主
      $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/subsidy_rigister_sample.xlsx");
      $objSpreadsheet = $this->_download_sub_rigister_data($objSpreadsheet , 0 , $subsidy_data); // 產生下載資料
      $objSpreadsheet = $this->_download_sub_rigister_data($objSpreadsheet , 1 , $sec09_1); // 產生下載資料 page 1 = 到府
      $objSpreadsheet = $this->_download_sub_rigister_data($objSpreadsheet , 2 , $sec09_2); // 產生下載資料 page 2 = 條碼
      $objSpreadsheet = $this->_download_sub_rigister_data($objSpreadsheet , 3 , $sec09_3); // 產生下載資料 page 3 = 本會
      $this->_save_to_server($sub_filename , $objSpreadsheet , 1);
      // 1. 先處理繳費名冊 END // 
      
      // 2. 處理信封 BEGIN //
      // $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      // $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/subsidy_envelope_sample.xlsx");
      // $objSpreadsheet = $this->_download_sub_envelope_data($objSpreadsheet , $get_data['rpt_pay_month'] , $route_data);
      // $this->_save_to_server($sub_envelope_filename , $objSpreadsheet , 2);
      // 2. 處理信封 END //

      // 3. 處理收據 BEGIN //
      // 長照案需開0元收據
      $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/subsidy_receipe_sample.xlsx");
      $subsidy_data = $this->rpt_pay_model->get_rpt_data_by_month('receipe');
      $objSpreadsheet = $this->_download_sub_receipe_data($objSpreadsheet , $get_data['rpt_pay_month'] , $subsidy_data, $fd_tbl_col);
      
      // $this->_save_to_server($sub_receipe_filename , $objSpreadsheet , 3);
      $this->_save_to_server($sub_receipe_filename , $objSpreadsheet , 2);
      // 3. 處理收據 END //
     
      $time_end = date('Y-m-d H:i:s');
      $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
      if($time_diff>=60) {
        $time_diff = round($time_diff/60,1).' 分'; // 分鐘
      }
      else {
        $time_diff = $time_diff.' 秒'; // 秒
      }   
      
      $this->zip->read_file(FCPATH."export_file/{$sub_filename}" , false);
      // $this->zip->read_file(FCPATH."export_file/{$sub_envelope_filename}" , false);
      $this->zip->read_file(FCPATH."export_file/{$sub_receipe_filename}" , false);
      $this->zip->archive(FCPATH."export_file/{$get_data['rpt_pay_month']}_sub.zip"); 
      if('subsidy_1' == $get_data['rpt_type']) {
        $show_file_name = base64url_encode("{$get_data['rpt_pay_month']} 補助戶(長照案)繳費資料.zip");
      }
      else {
        $show_file_name = base64url_encode("{$get_data['rpt_pay_month']} 補助戶(公所案)繳費資料.zip");
      }
      $download_zip_big5_en = base64url_encode("{$get_data['rpt_pay_month']}_sub.zip");
      $be_url = be_url()."rpt_pay/download_file/{$show_file_name}/";
      $rtn_msg .= "<table class='table table-bordered table-hover'>";
      $rtn_msg .= "  <thead>";
      $rtn_msg .= "    <tr>";
      $rtn_msg .= "      <th width='20%'>項目</th>";
      $rtn_msg .= "      <th width='80%'>說明</th>";
      $rtn_msg .= "    </tr>";
      $rtn_msg .= "  </thead>";
      $rtn_msg .= "  <tbody>";
      $rtn_msg .= "    <tr>";
      $rtn_msg .= "      <td>資料下載</td>"; // {$subsidy_filename}&nbsp; 
      if('subsidy_1' == $get_data['rpt_type']) {
        $rtn_msg .= "      <td>{$get_data['rpt_pay_month']} 補助戶(長照案)繳費資料&nbsp <button class='btn btn-C3 btn-sm' type='button' onclick='location.href=\"{$be_url}{$download_zip_big5_en}\"'>檔案下載</button></td>";
      }
      else {
        $rtn_msg .= "      <td>{$get_data['rpt_pay_month']} 補助戶(公所案)繳費資料&nbsp <button class='btn btn-C3 btn-sm' type='button' onclick='location.href=\"{$be_url}{$download_zip_big5_en}\"'>檔案下載</button></td>";
      }
      $rtn_msg .= "    </tr>";
      $rtn_msg .= "    <tr>";
      $rtn_msg .= "      <td>處理時間</th>";
      $rtn_msg .= "      <td>{$time_diff}</td>";
      $rtn_msg .= "    </tr>";
      $rtn_msg .= "  </tbody>";
      $rtn_msg .= "</table>";
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _ownexpense
  //  函數功能: 自費戶繳費名冊
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _ownexpense() {
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');
    $get_data = $this->input->get(); 
    $ownexpense_data = $this->rpt_pay_model->get_rpt_data_by_month();
    
    if(NULL != $ownexpense_data) {
      $own_filename = "{$get_data['rpt_pay_month']}_own_register_data.xlsx";
      $own_envelope_filename = "{$get_data['rpt_pay_month']}_own_envelope_data.xlsx";
      $own_receipe_filename = "{$get_data['rpt_pay_month']}_own_receipe_data.xlsx";
      $fd_tbl_col = 'o';
      list($sec09_1 , $sec09_2 , $sec09_3) = $this->_judge_sec09($fd_tbl_col , $ownexpense_data);
      // $route_data = $this->_judge_route($fd_tbl_col , $sec09_1); // 只需要到府收費的
      
      
      // 1. 先處理繳費名冊 BEGIN //
      $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/ownexpense_rigister_sample.xlsx");
      $objSpreadsheet = $this->_download_own_rigister_data($objSpreadsheet , 0 , $ownexpense_data); // 產生下載資料
      $objSpreadsheet = $this->_download_own_rigister_data($objSpreadsheet , 1 , $sec09_1); // 產生下載資料 page 1 = 到府
      $objSpreadsheet = $this->_download_own_rigister_data($objSpreadsheet , 2 , $sec09_2); // 產生下載資料 page 2 = 條碼
      $objSpreadsheet = $this->_download_own_rigister_data($objSpreadsheet , 3 , $sec09_3); // 產生下載資料 page 3 = 本會
      $this->_save_to_server($own_filename , $objSpreadsheet , 1);
      // 1. 先處理繳費名冊 END // 
      
      // 2. 處理信封 BEGIN //
      // $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      // $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/ownexpense_envelope_sample.xlsx");
      // $objSpreadsheet = $this->_download_own_envelope_data($objSpreadsheet , $get_data['rpt_pay_month'] , $route_data);
      // $this->_save_to_server($own_envelope_filename , $objSpreadsheet , 2);
      // 2. 處理信封 END //
      
      // 3. 處理收據 BEGIN //
      $ownexpense_data = $this->rpt_pay_model->get_rpt_data_by_month('receipe');
      $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/ownexpense_receipe_sample.xlsx");
      $objSpreadsheet = $this->_download_own_receipe_data($objSpreadsheet , $get_data['rpt_pay_month'] , $ownexpense_data, $fd_tbl_col);
      // $this->_save_to_server($own_receipe_filename , $objSpreadsheet , 3);
      $this->_save_to_server($own_receipe_filename , $objSpreadsheet , 2);
      // 3. 處理收據 END //
      
      $time_end = date('Y-m-d H:i:s');
      $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
      if($time_diff>=60) {
        $time_diff = round($time_diff/60,1).' 分'; // 分鐘
      }
      else {
        $time_diff = $time_diff.' 秒'; // 秒
      }   
      
      $this->zip->read_file(FCPATH."export_file/{$own_filename}" , false);
      // $this->zip->read_file(FCPATH."export_file/{$own_envelope_filename}" , false);
      $this->zip->read_file(FCPATH."export_file/{$own_receipe_filename}" , false);
      $this->zip->archive(FCPATH."export_file/{$get_data['rpt_pay_month']}_own.zip"); 
      
      $show_file_name = '';
      $show_file_name = base64url_encode("{$get_data['rpt_pay_month']} 自費戶繳費資料.zip");
      $download_zip_big5_en = base64url_encode("{$get_data['rpt_pay_month']}_own.zip");
      $be_url = be_url()."rpt_pay/download_file/{$show_file_name}/";
      $rtn_msg .= "<table class='table table-bordered table-hover'>";
      $rtn_msg .= "  <thead>";
      $rtn_msg .= "    <tr>";
      $rtn_msg .= "      <th width='20%'>項目</th>";
      $rtn_msg .= "      <th width='80%'>說明</th>";
      $rtn_msg .= "    </tr>";
      $rtn_msg .= "  </thead>";
      $rtn_msg .= "  <tbody>";
      $rtn_msg .= "    <tr>";
      $rtn_msg .= "      <td>資料下載</td>"; // {$subsidy_filename}&nbsp; 
      $rtn_msg .= "      <td>{$get_data['rpt_pay_month']} 自費戶繳費資料&nbsp <button class='btn btn-C3 btn-sm' type='button' onclick='location.href=\"{$be_url}{$download_zip_big5_en}\"'>檔案下載</button></td>";
      $rtn_msg .= "    </tr>";
      $rtn_msg .= "    <tr>";
      $rtn_msg .= "      <td>處理時間</th>";
      $rtn_msg .= "      <td>{$time_diff}</td>";
      $rtn_msg .= "    </tr>";
      $rtn_msg .= "  </tbody>";
      $rtn_msg .= "</table>";
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _judge_filename
  //  函數功能: 產生下載繳費資料
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _judge_filename($filename) {
    switch(ENVIRONMENT) {
      case 'development':
      case 'testing':
        return iconv('big5','utf-8',$filename);
      break;
      case 'production':
        return $filename;
      break;
    }
  }
  
  // **************************************************************************
  //  函數名稱: _judge_sec09()
  //  函數功能: 判斷繳費方式
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _judge_sec09($fd_tbl_col , $rpt_pay_data) {
    $sec09_1 = NULL;
    $sec09_2 = NULL;
    $sec09_3 = NULL;
    foreach ($rpt_pay_data as $k => $v) {
      switch ($v["rp{$fd_tbl_col}03_sec09"]) {  
        case "到府收費":       
          $sec09_1[] = $v;
        break;  
        case "條碼繳費":       
        case "條碼繳費(給社工)":       
          $sec09_2[] = $v;
        break;
        case "到本會繳費":       
        case "到本會繳(鼎傳)":       
        case "到本會繳(慈心)":       
          $sec09_3[] = $v;
        break;
      } 
    } 
    return(array($sec09_1 , $sec09_2 , $sec09_3));
  }
  
   // **************************************************************************
  //  函數名稱: _judge_route
  //  函數功能: 將同一個路線整理再一起
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _judge_route($fd_tbl_col , $subsidy_data) {
    $route_data = NULL;
    foreach ($subsidy_data as $k => $v) {
      if(!isset($route_data[$v["rp{$fd_tbl_col}04_reh01"]])) {
        $route_data[$v["rp{$fd_tbl_col}04_reh01"]] = NULL;
      }
      $route_data[$v["rp{$fd_tbl_col}04_reh01"]][] = $v;
    } 
    return $route_data;
  }
  
  // **************************************************************************
  //  函數名稱: _generate_data
  //  函數功能: 產生繳費資料
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _generate_data($rpt_pay_month , $rpt_type) {
    // 先處理每天訂單類型 BEGIN//
    $last_date = NULL;
    $first_date = NULL;
    $date_type_arr = NULL;
    $num = 1;
    $date_arr = get_month_date($rpt_pay_month);
    $date_cnt = count($date_arr);
    list($rpt_year, $rpt_month) = explode('-', $rpt_pay_month);
    $taiwan_year = $rpt_year - 1911;
    foreach ($date_arr as $k => $date) {
      $meal_order_type_row = $this->meal_order_date_type_model->get_meal_order_date_type($date);
      if(NULL != $meal_order_type_row) {
        $date_type_arr[$date] = $meal_order_type_row->mlo_dt02;
      }
    }
    $first_date = $date_arr[0] ; // 每個月第一天
    $last_date = $date_arr[$date_cnt-1] ; // 每個月最後一天
    // 先處理每天訂單類型 END//

    // 根據定義好的身分別跑訂單資料 BEGIN //
    $rpt_pay_data = NULL;
    $fd_tbl_col = $this->fd_tbl_col;
    foreach ($this->que_data[$rpt_type] as $k => $v) {
      $clients_row = $this->service_case_model->get_all_by_sec01($rpt_pay_month, $rpt_type, $last_date, $v['sec01']); 
      if(NULL != $clients_row) {
        foreach ($clients_row as $k_ct => $v_ct) {
          $index = NULL;
          $eatdays = NULL;
          $meal_price = $v_ct['sec07'];
          $change_log = "N";
          // switch($rpt_type) {
            // case "subsidy_1": // 長照案
            // case "subsidy_2": // 公所案
              // $meal_price = 100;
              // break;
            // case "ownexpense": // 自費戶
              // if(NULL != $v_ct['sec07']) {
                // $meal_price = $v_ct['sec07'];
              // }
              // break;
          // }

          $que_first_date = $first_date;
          $que_last_date = $last_date;
          $identity_log_first_row = $this->clients_model->get_identity_log_latest($v_ct['ct_s_num'], $first_date);
          $identity_log_last_row = $this->clients_model->get_identity_log_latest($v_ct['ct_s_num'], $last_date);

          if('ownexpense' != $rpt_type) {
            // 如果利用本月第一天和最後一天查詢的異動身分別資料不動，可以判斷說他的身分別在這個月有異動
            if(NULL == $identity_log_first_row or NULL == $identity_log_last_row) { // 如果其中一筆為空的話，要確認有資料的那筆是否有符合正確的身分別，並且要修改起始查詢日
              if(NULL != $identity_log_first_row) {
                $que_first_date = $identity_log_first_row->ct_il01;
              }
              if(NULL != $identity_log_last_row) {
                $que_first_date = $identity_log_last_row->ct_il01;
              }
            }
            else {
              // 同一筆資料
              if($identity_log_first_row->s_num == $identity_log_last_row->s_num) {
                $ocl_row = $this->other_change_log_h_model->get_identity_by_sec($v_ct['ct_s_num'], $identity_log_first_row->ct_il02, 'before');
                if(NULL != $ocl_row) {
                  $v_ct['sec09_str'] = $this->chk_sec09($ocl_row, $v_ct['sec09_str']);
                }
              }
              else {
                $change_log = 'Y'; // 是否有更改身分別(Y=有，N=無)
                // 變更前身分，最後查詢日修改為ct_il01的前一天
                $que_first_date = $first_date;
                $que_last_date = date('Y-m-d', strtotime("-1 day", strtotime($identity_log_last_row->ct_il01)));
                $ocl_row = $this->other_change_log_h_model->get_identity_by_sec($v_ct['ct_s_num'], $identity_log_first_row->ct_il02, 'before');
                if(NULL != $ocl_row) {
                  // $v_ct['sec09_str'] = $this->chk_sec09($ocl_row, $v_ct['sec09_str']);
                  $v_ct['sec09_str'] = $ocl_row->ocl_sec09_before_str;
                }
                if(!empty($identity_log_first_row->scca01)) {
                  $meal_price = $identity_log_first_row->scca01;
                }
              }
            }
          }
          
          // 紀錄身分未變更或變更前資料
          if(!isset($rpt_pay_data[$num])) {
            $rpt_pay_data[$num]['b_empno'] = $_SESSION['acc_s_num'];
            $rpt_pay_data[$num]['b_date'] = date('Y-m-d H:i:s');
            $rpt_pay_data[$num]["ct_s_num"] = $v_ct['ct_s_num'];
            $rpt_pay_data[$num]["reh_s_num"] = 0;
            $rpt_pay_data[$num]["sec_s_num"] = $v_ct['s_num'];
            $rpt_pay_data[$num]["rp{$fd_tbl_col}01"] = $rpt_pay_month;
            $rpt_pay_data[$num]["rp{$fd_tbl_col}02_ct_name"] = $v_ct['ct_name'];
            $rpt_pay_data[$num]["rp{$fd_tbl_col}02_ct14"] = mb_substr($v_ct['ct14'] , 0 , 2 ,"utf-8");
            $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec01"] = $v_ct['sec01'];
            $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec04"] = $v_ct['sec04'];
            $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec09"] = $v_ct['sec09_str'];
            $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec99"] = str_replace("&nbsp;", '', trim(strip_tags($v_ct['sec99'])));
            $rpt_pay_data[$num]["rp{$fd_tbl_col}04_reh01"] = '';
            $rpt_pay_data[$num]["rp{$fd_tbl_col}05"] = 0; // 午餐餐數
            $rpt_pay_data[$num]["rp{$fd_tbl_col}06"] = 0; // 晚餐餐數
            $rpt_pay_data[$num]["rp{$fd_tbl_col}07"] = ''; // 用餐日期
            $rpt_pay_data[$num]["rp{$fd_tbl_col}08"] = $meal_price; // 餐點價格
            $rpt_pay_data[$num]["rp{$fd_tbl_col}09"] = ''; // 用餐種類
            $rpt_pay_data[$num]["rp{$fd_tbl_col}10"] = $change_log;
            if(empty($identity_log_first_row->ct_il02_str)){
              $rpt_pay_data[$num]["rp{$fd_tbl_col}11"] = $v_ct['ct34_go_str'];
            }else{
              $rpt_pay_data[$num]["rp{$fd_tbl_col}11"] = $identity_log_first_row->ct_il02_str;
            }
          }

          $meal_order_row = $this->meal_order_model->get_data_by_subsidy($v_ct['s_num'] , $que_first_date , $que_last_date);
          if(NULL != $meal_order_row) {
            switch($v_ct['sec04']) {
              case 1:
                $type = "rp{$fd_tbl_col}05";
              break;
              case 2:
              case 3:
                $type = "rp{$fd_tbl_col}06";
              break;
            }
            foreach ($meal_order_row as $ko => $vo) {
              if($date_type_arr[$vo['mlo01']] == 2) { // 如果為送餐-代餐，沒有代餐的就要去掉
                if($vo['mlo05'] != "Y") {
                  unset($meal_order_row[$ko]);
                  continue;
                }
              }
              if('ownexpense' == $rpt_type) {
                $mlo01 = new DateTime($vo['mlo01']);
                $eatdays[] = $mlo01->modify("-1911 year")->format("m/d");
              }
            }
            if(NULL != $eatdays) {
              $rpt_pay_data[$num]["rp{$fd_tbl_col}07"] = join("," , $eatdays);
            }
            $rpt_pay_data[$num][$type] += count($meal_order_row); 
          }
          $num++;
          // 紀錄有身分變更後的個案資料
          if('Y' == $change_log){
            // 身分有變更
            // switch($identity_log_last_row->ct_il02) {
              // case "3": // 身分別為中低收
              // case "7": // 身分別為中低收
                // $meal_price = 100 * 0.1;
                // break;
              // case "4": // 身分別為低收
              // case "5": // 身分別為低收
                // $meal_price = 0;
                // break;
            // }
            $que_first_date = $identity_log_last_row->ct_il01;
            $que_last_date = $last_date;
            $ocl_row = $this->other_change_log_h_model->get_identity_by_sec($v_ct['ct_s_num'], $identity_log_last_row->ct_il02, 'after');
            if(NULL != $ocl_row) {
              // $v_ct['sec09_str'] = $this->chk_sec09($ocl_row, $v_ct['sec09_str']);
              $v_ct['sec09_str'] = $ocl_row->ocl_sec09_after_str;
            }
            if(!empty($identity_log_last_row->scca01)) {
              $meal_price = $identity_log_last_row->scca01;
            }
            if(!isset($rpt_pay_data[$num])) {
              $rpt_pay_data[$num]['b_empno'] = $_SESSION['acc_s_num'];
              $rpt_pay_data[$num]['b_date'] = date('Y-m-d H:i:s');
              $rpt_pay_data[$num]["ct_s_num"] = $v_ct['ct_s_num'];
              $rpt_pay_data[$num]["reh_s_num"] = 0;
              $rpt_pay_data[$num]["sec_s_num"] = $v_ct['s_num'];
              $rpt_pay_data[$num]["rp{$fd_tbl_col}01"] = $rpt_pay_month;
              $rpt_pay_data[$num]["rp{$fd_tbl_col}02_ct_name"] = $v_ct['ct_name'];
              $rpt_pay_data[$num]["rp{$fd_tbl_col}02_ct14"] = mb_substr($v_ct['ct14'] , 0 , 2 ,"utf-8");
              $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec01"] = $v_ct['sec01'];
              $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec04"] = $v_ct['sec04'];
              $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec09"] = $v_ct['sec09_str'];
              $rpt_pay_data[$num]["rp{$fd_tbl_col}03_sec99"] = str_replace("&nbsp;", '', trim(strip_tags($v_ct['sec99'])));
              $rpt_pay_data[$num]["rp{$fd_tbl_col}04_reh01"] = '';
              $rpt_pay_data[$num]["rp{$fd_tbl_col}05"] = 0; // 午餐餐數
              $rpt_pay_data[$num]["rp{$fd_tbl_col}06"] = 0; // 晚餐餐數
              $rpt_pay_data[$num]["rp{$fd_tbl_col}07"] = ''; // 用餐日期
              $rpt_pay_data[$num]["rp{$fd_tbl_col}08"] = $meal_price; // 餐點價格
              $rpt_pay_data[$num]["rp{$fd_tbl_col}09"] = ''; // 用餐種類
              $rpt_pay_data[$num]["rp{$fd_tbl_col}10"] = $change_log;
              $rpt_pay_data[$num]["rp{$fd_tbl_col}11"] = $identity_log_last_row->ct_il02_str;
            }
            $meal_order_row = $this->meal_order_model->get_data_by_subsidy($v_ct['s_num'] , $que_first_date , $que_last_date);
            if(NULL != $meal_order_row) {
              switch($v_ct['sec04']) {
                case 1:
                  $type = "rp{$fd_tbl_col}05";
                break;
                case 2:
                case 3:
                  $type = "rp{$fd_tbl_col}06";
                break;
              }
              foreach ($meal_order_row as $ko => $vo) {
                if($date_type_arr[$vo['mlo01']] == 2) { // 如果為送餐-代餐，沒有代餐的就要去掉
                  if($vo['mlo05'] != "Y") {
                    unset($meal_order_row[$ko]);
                    continue;
                  }
                }
                // if('ownexpense' == $rpt_type) {
                  // $mlo01 = new DateTime($vo['mlo01']);
                  // $eatdays[] = $mlo01->modify("-1911 year")->format("m/d");
                // }
              }
              if(NULL != $eatdays) {
                $rpt_pay_data[$num]["rp{$fd_tbl_col}07"] = join("," , $eatdays);
              }
              $rpt_pay_data[$num][$type] += count($meal_order_row); 
            }
            $num++;
          }
        } 
      }
    }
    // 根據定義好的身分別跑訂單資料 END //
    // 檢查是否有午餐、晚餐都為0的案主 BEGIN //
    // $sort = NULL; // 排序資料
    if(NULL != $rpt_pay_data) {
      foreach ($rpt_pay_data as $k => $v) {
        if($v["rp{$fd_tbl_col}05"] == 0 and $v["rp{$fd_tbl_col}06"] == 0) {
          unset($rpt_pay_data[$k]);
          continue;
        }
        // $sort[$k] = '';
        $route_row = NULL;
        $meal_type = NULL;
        if($v["rp{$fd_tbl_col}05"] != 0) {
          $meal_type[] = '午餐';
          $route_row = $this->route_model->que_client_route(1 , $v['ct_s_num']);
        }
        else {
          if($v["rp{$fd_tbl_col}03_sec04"] == 2) { // 午晚餐也算晚餐，但是送餐路線不是晚餐
            $route_row = $this->route_model->que_client_route(1 , $v['ct_s_num']);
          }
          else {
            $route_row = $this->route_model->que_client_route(2 , $v['ct_s_num']);
          }
        }
        if($v["rp{$fd_tbl_col}06"] != 0) {
          $meal_type[] = '晚餐';
        }
        if(NULL != $route_row) {
          $sort[$k] = $route_row->reh01;
          $rpt_pay_data[$k]["rp{$fd_tbl_col}04_reh01"] = $route_row->reh01;
        }
        else {
          $route_row = $this->rpt_pay_model->get_ct_reh($v['sec_s_num'] , $v['ct_s_num']); // 如果沒有就找找看之前的紀錄
          if(NULL != $route_row) {
            $fd = "rp{$fd_tbl_col}04_reh01";
            $rpt_pay_data[$k]["rp{$fd_tbl_col}04_reh01"] = $route_row->$fd;
          }
        }
        $rpt_pay_data[$k]["rp{$fd_tbl_col}09"] = join("、" , $meal_type);
      }
      // array_multisort($sort, SORT_ASC, $rpt_pay_data); 
    }
    // 檢查是否有午餐、晚餐都為0的案主 END //
    return $rpt_pay_data;
  }
  // **************************************************************************
  //  函數名稱: _download_sub_rigister_data
  //  函數功能: 產生下載繳費資料(補助)
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _download_sub_rigister_data($objSpreadsheet , $active_index , $subsidy_data) {
    if(NULL == $subsidy_data) {
      return $objSpreadsheet;
    }
    $objSpreadsheet->setActiveSheetIndex($active_index); 
    switch ($active_index) {  
      case 2: // 條碼繳費
        $objSpreadsheet->getActiveSheet()->getTabColor()->setRGB('95B3D7');
      break;
      case 3: // 到本會繳費
      $objSpreadsheet->getActiveSheet()->getTabColor()->setRGB('fff200');
      break;
    } 
    
    $i = 1;
    $row = 2; // 資料從二列開始
    $begin_char = "A";
    $end_char = "L";
    foreach ($subsidy_data as $k => $v) {
      if("Y" == $v['rps10']) {
        $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('ffd700');
      }
      $rps03_sec99 = $v['rps03_sec99'];
      if(!empty($v['ct20'])){
        $rps03_sec99 .= "\n匯款尾碼：".$v['ct20'];
      }
      list($rpt_year, $rpt_month) = explode('-', $v['rps01']);
      $taiwan_year = $rpt_year - 1911;
      $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 19.8 , 14 , $begin_char , $end_char);
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , $i); // 序號
      $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $taiwan_year.date('md').str_pad($i,3,'0',STR_PAD_LEFT)); // 收據編號
      $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$v['rps04_reh01']}"); // 送餐路線
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "{$v['rps02_ct_name']}"); // 個案姓名
      $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "{$v['rps03_sec09']}"); // 繳費方式
      $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "{$v['rps05']}"); // 午餐數
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "{$v['rps06']}"); // 晚餐數
      $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "=F{$row}+G{$row}"); // 總餐數
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=H{$row}*{$v['rps08']}"); // 合計費用
      $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "{$v['rps09']}"); // 餐種
      $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}" , "{$v['rps12']}"); // 收款日期
      $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , $v['rps03_sec99']); // 備註
      if($v['rps03_sec09'] == "條碼繳費" or $v['rps03_sec09'] == "條碼繳費(給社工)") {
        $objSpreadsheet->getActiveSheet()->getStyle("E{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('95B3D7');
      }
      $i++;
      $row++;
    }
    $last_row = $row - 1;
    $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 19.8 , 14 , $begin_char , $end_char);
    $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "=SUM(F2:F{$last_row})"); // 全部午餐數
    $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "=SUM(G2:G{$last_row})"); // 全部晚餐數
    $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "=SUM(H2:H{$last_row})"); // 總參數
    $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=SUM(I2:I{$last_row})"); // 總計
    $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:E{$row}")->setCellValue("A{$row}" , "總計"); 
    $objSpreadsheet->getActiveSheet()->getStyle("I{$row}")->getNumberFormat() ->setFormatCode('_-$* #,##0_-;-$* #,##0_-;_-$* "-"??_-;_-@_-'); 
    return $objSpreadsheet;
  }
  // **************************************************************************
  //  函數名稱: _download_sub_envelope_data
  //  函數功能: 產生下載繳費資料(補助)
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _download_sub_envelope_data($objSpreadsheet , $rpt_pay_month , $route_data) {
    if(NULL != $route_data) {
      $cnt = 1;
      $row = 1;
      $rpt_type_str = '';
      if("subsidy_1" == $_GET['rpt_type']) {
        $rpt_type_str = '長照案';
      }
      else {
        $rpt_type_str = '公所案';
      }
      $taiwan_date = explode("-", $rpt_pay_month);
      $taiwan_year = $taiwan_date[0] - 1911;
      $taiwan_month = $taiwan_date[1];
      $next_month_end_date = date('Y/m/d',strtotime(date('Y-m-1',strtotime($rpt_pay_month)).'+2 month -1 day'));
      $next_month_date = explode("/", $next_month_end_date);
      $next_month_year = $next_month_date[0] - 1911;
      $next_month_month = $next_month_date[1];
      $next_month_day = $next_month_date[2];
      
      $begin_char = "A";
      $end_char = "M";
      $objSpreadsheet->setActiveSheetIndex(0); 
      $objSpreadsheet->getActiveSheet()->setTitle("{$taiwan_month}月份");
      foreach ($route_data as $k1 => $v1) {
        if(NULL != $v1) {
          // 標題
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 25.2 , 12 , $begin_char , $end_char);
          $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:M{$row}")->setCellValue("A{$row}" , "{$taiwan_year}年{$taiwan_month}月份弗傳慈心老人和身障個案需負擔8元{$rpt_type_str}收費明細表"); // title
          $row++;
          
          // 欄位名稱
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 25.2 , 12 , $begin_char , $end_char);
          $objSpreadsheet->getActiveSheet()->getStyle("K{$row}")->getFont()->setBold(true); 
          $objSpreadsheet->getActiveSheet()->getStyle("L{$row}")->getFont()->setBold(true); 
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:M{$row}")->getAlignment()->setShrinkToFit(true);
          $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , "編號"); // 序號
          $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , "區域"); // 區域
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "送餐路線"); // 送餐路線
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "個案姓名"); // 個案姓名
          $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "繳費方式"); // 繳費方式
          $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "午餐數"); // 午餐數
          $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "晚餐數"); // 晚餐數
          $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "總餐數"); // 總餐數
          $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "合計費用"); // 合計費用
          $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "餐種"); // 餐種
          $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}" , "實收"); // 實收
          $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , "入帳日"); // 入帳日
          $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , "備註");  // 備註
          $row++;
          
          $first_row = $row;
          foreach ($v1 as $k2 => $v2) {
            $remark = "";
            if(!empty($v['ct20'])){
              $remark = "匯款尾碼：".$v['ct20'];
            }
            $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 25.2 , 14 , $begin_char , $end_char);
            $objSpreadsheet->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setShrinkToFit(true);
            $objSpreadsheet->getActiveSheet()->getStyle("C{$row}")->getAlignment()->setShrinkToFit(true);
            $objSpreadsheet->getActiveSheet()->getStyle("E{$row}")->getAlignment()->setShrinkToFit(true);
            $objSpreadsheet->getActiveSheet()->getStyle("J{$row}")->getAlignment()->setShrinkToFit(true);
            $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , $cnt); // 序號
            $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , mb_substr($v2['rps02_ct14'] , 0 , 2 ,"utf-8")); // 區域
            $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$v2['rps04_reh01']}"); // 送餐路線
            $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "{$v2['rps02_ct_name']}"); // 個案姓名
            $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "{$v2['rps03_sec09']}"); // 繳費方式
            $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "{$v2['rps05']}"); // 午餐數
            $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "{$v2['rps06']}"); // 晚餐數
            $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "=F{$row}+G{$row}"); // 總餐數
            $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=H{$row}*{$v2['rps08']}"); // 合計費用
            $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "{$v2['rps09']}"); // 餐種
            $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}" , ""); 
            $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , ""); 
            $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $remark); 
            if("Y" == $v2['rps10']) {
              $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:M{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('ffd700');
            }
            $row++;
            $cnt++;
          } 
          
          // 收款人
          $last_row = $row - 1;
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 33 , 16 , $begin_char , $end_char);
          $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:B{$row}")->setCellValue("A{$row}" , "送餐員"); 
          // $objSpreadsheet->getActiveSheet()->mergeCells("C{$row}:D{$row}");
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "收款人"); 
          $objSpreadsheet->getActiveSheet()->mergeCells("E{$row}:H{$row}");
          $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=SUM(I{$first_row}:I{$last_row})"); 
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setShrinkToFit(true);
          $objSpreadsheet->getActiveSheet()->getStyle("I{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
          $objSpreadsheet->getActiveSheet()->getStyle("I{$row}")->getNumberFormat() ->setFormatCode('$#,##0;-$#,##0'); 
          $row++;
          
          // FOOTER
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 25.2 , 12 , $begin_char , $end_char);
          $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:M{$row}")->setCellValue("A{$row}" , "請於{$next_month_year}/{$next_month_month}/{$next_month_day}日收款完畢，收回款項請盡速交回會計結帳,謝謝!"); // footer
          // $objSpreadsheet->getActiveSheet()->setBreak("A{$row}", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
          $row++;
          
          // 空白行
          $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(16.2);
          $row++;
        }
      }
    }
    return $objSpreadsheet;
  }
  
  // **************************************************************************
  //  函數名稱: _download_sub_receipe_data
  //  函數功能: 產生下載繳費資料(補助)
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _download_sub_receipe_data($objSpreadsheet , $rpt_pay_month , $subsidy_data, $fd_tbl_col) {
    
    $taiwan_date = explode("-", $rpt_pay_month);
    $taiwan_year = $taiwan_date[0] - 1911;
    $taiwan_month = $taiwan_date[1];
    if($taiwan_date[1] != 10) {
      $taiwan_month = str_replace(0, '' , $taiwan_date[1]);
    }
    $objSpreadsheet->setActiveSheetIndex(0); 
    $objSpreadsheet->getActiveSheet()->setTitle("{$taiwan_year}年{$taiwan_month}月");
    if(NULL == $subsidy_data) {
      return $objSpreadsheet;
    }
     
    $i = 1;
    $row = 1;
    $page = 1;
    $date = date("md");
    $today = date('Y-m-t', strtotime($rpt_pay_month));
    $recipe_format= "{$taiwan_year}{$date}";
    foreach ($subsidy_data as $k => $v) {
      $row++; 
      
      // 服務費、收據編號
      $receipe_num = $taiwan_year.date('md').str_pad($i,3,'0',STR_PAD_LEFT); 
      $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "{$taiwan_year}年{$taiwan_month}月服務費"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "{$receipe_num}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("P{$row}" , "{$taiwan_year}年{$taiwan_month}月服務費"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("U{$row}" , "{$receipe_num}");
      $objSpreadsheet->getActiveSheet()->setCellValue("AA{$row}" , "{$taiwan_year}年{$taiwan_month}月服務費"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("AF{$row}" , "{$receipe_num}"); 
      $row++; 
      
      // 名字、路線
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'02_ct_name']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , $v['rp'.$fd_tbl_col.'04_reh01']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , "{$i}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'02_ct_name']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("T{$row}" , $v['rp'.$fd_tbl_col.'04_reh01']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'02_ct_name']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("AE{$row}" , $v['rp'.$fd_tbl_col.'04_reh01']); 
      $row++; 
            
      // 數量、單價、小計、備註
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "備註：{$v['rp'.$fd_tbl_col.'11']}");
      $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "備註：{$v['rp'.$fd_tbl_col.'11']}");
      $objSpreadsheet->getActiveSheet()->setCellValue("AC{$row}" , "備註：{$v['rp'.$fd_tbl_col.'11']}");
      $row++;
      
      // CMS
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "CMS：{$v['ct37_str']}"); // CMS
      $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "CMS：{$v['ct37_str']}"); // CMS
      $objSpreadsheet->getActiveSheet()->setCellValue("AC{$row}" , "CMS：{$v['ct37_str']}"); // CMS
      
      // 若午餐或晚餐數量為0，則不需列出
      // 午餐
      if(0 !=$v['rp'.$fd_tbl_col.'05']){
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , $v['rp'.$fd_tbl_col.'05_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , $v['rp'.$fd_tbl_col.'05_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("X{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , $v['rp'.$fd_tbl_col.'05_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $row++;
        // 身分證
        $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "身分證：{$v['ct03']}");
        $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "身分證：{$v['ct03']}"); 
        $objSpreadsheet->getActiveSheet()->setCellValue("AC{$row}" , "身分證：{$v['ct03']}");
        
        if(0 !=$v['rp'.$fd_tbl_col.'06']){ // 晚餐
          $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $v['rp'.$fd_tbl_col.'09']);
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
          $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $v['rp'.$fd_tbl_col.'09']);
          $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
          $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
          $objSpreadsheet->getActiveSheet()->setCellValue("X{$row}" , $v['rp'.$fd_tbl_col.'09']);
          $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
          $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
        }
        $row++;
      }
      else{ // 晚餐
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("X{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $row++;
        // 身分證
        $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "身分證：{$v['ct03']}");
        $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "身分證：{$v['ct03']}"); 
        $objSpreadsheet->getActiveSheet()->setCellValue("AC{$row}" , "身分證：{$v['ct03']}");
        $row++;
      }
      
      // 合計
      $total  = $v['rp'.$fd_tbl_col.'05']+$v['rp'.$fd_tbl_col.'06'];
      $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$total}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
      $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , "{$total}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
      $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , "{$total}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
      $row++;

      $row++; 
      
      // 實收金額、日期
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "{$today}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("T{$row}" , "{$today}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("AE{$row}" , "{$today}"); 
      $row++; 
      
      // 空白行
      $row++; 
      
      // 空白行 3 行
      $row = $row + 3;
      $i++;
      
      // 換頁
      if($i > 4 and $i % 4 == 1) {
        $page++;
        $row = $row - 1;
      }
    }
    
    $print_rage = 51 * $page;
    $objSpreadsheet->getActiveSheet()->getPageSetup()->setPrintArea("A1:AG{$print_rage}");
    $objSpreadsheet->getActiveSheet()->setCellValue("A1" , "");
    return $objSpreadsheet;
  }
  
  // **************************************************************************
  //  函數名稱: _download_own_rigister_data
  //  函數功能: 產生下載繳費資料(自費)
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _download_own_rigister_data($objSpreadsheet , $active_index , $subsidy_data) {
    if(NULL == $subsidy_data) {
      return $objSpreadsheet;
    }
                             
    $objSpreadsheet->setActiveSheetIndex($active_index); 
    switch ($active_index) {  
      case 2: // 條碼繳費
        $objSpreadsheet->getActiveSheet()->getTabColor()->setRGB('95B3D7');
      break;
      case 3: // 到本會繳費
      $objSpreadsheet->getActiveSheet()->getTabColor()->setRGB('fff200');
      break;
    } 
    
    $i = 1;
    $row = 2; // 資料從二列開始
    $begin_char = "A";
    $end_char = "M";    
    foreach ($subsidy_data as $k => $v) {
      $rpo03_sec99 = $v['rpo03_sec99'];
      if(!empty($v['ct20'])){
        $rpo03_sec99 .= "\n匯款尾碼：".$v['ct20'];
      }
      list($rpt_year, $rpt_month) = explode('-', $v['rpo01']);
      $taiwan_year = $rpt_year - 1911;
      $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 19.8 , 14 , $begin_char , $end_char);
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , $i); // 序號
      $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $taiwan_year.date('md').str_pad($i,3,'0',STR_PAD_LEFT)); // 收據編號
      $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$v['rpo04_reh01']}"); // 送餐路線
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "{$v['rpo02_ct_name']}"); // 個案姓名
      $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "{$v['rpo03_sec09']}"); // 繳費方式
      $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "{$v['rpo05']}"); // 午餐數
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "{$v['rpo06']}"); // 晚餐數
      $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "{$v['rpo08']}"); // 餐點價格
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=F{$row}+G{$row}"); // 總餐數
      $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "=H{$row}*I{$row}"); // 合計費用
      $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}" , "{$v['rpo09']}"); // 餐種
      $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , "{$v['rpo12']}"); // 收款日期
      $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $rpo03_sec99); // 備註
      if("Y" == $v['rpo10']) {
        $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:L{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('ffd700');
      }
      if($v['rpo03_sec09'] == "條碼繳費" or $v['rpo03_sec09'] == "條碼繳費(給社工)") {
        $objSpreadsheet->getActiveSheet()->getStyle("E{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('95B3D7');
      }
      $i++;
      $row++;
    }
    $last_row = $row - 1;
    $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 19.8 , 14 , $begin_char , $end_char);
    $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "=SUM(F2:F{$last_row})"); // 全部午餐數
    $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "=SUM(G2:G{$last_row})"); // 全部晚餐數
    $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=SUM(I2:I{$last_row})"); // 總參數
    $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "=SUM(J2:J{$last_row})"); // 總計
    $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:E{$row}")->setCellValue("A{$row}" , "總計"); 
    // $objSpreadsheet->getActiveSheet()->getStyle("I{$row}")->getNumberFormat() ->setFormatCode('_-$* #,##0_-;-$* #,##0_-;_-$* "-"??_-;_-@_-'); 
    $objSpreadsheet->getActiveSheet()->getStyle("J{$row}")->getNumberFormat() ->setFormatCode('_-$* #,##0_-;-$* #,##0_-;_-$* "-"??_-;_-@_-'); 
    return $objSpreadsheet;
  }
  // **************************************************************************
  //  函數名稱: _download_own_envelope_data
  //  函數功能: 產生下載繳費資料(自費)
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _download_own_envelope_data($objSpreadsheet , $rpt_pay_month , $route_data) {
    if(NULL != $route_data) {
      $cnt = 1;
      $row = 1;
      $every_page = NULL;
      $taiwan_date = explode("-", $rpt_pay_month);
      $taiwan_year = $taiwan_date[0] - 1911;
      $taiwan_month = $taiwan_date[1];
      
      $begin_char = "A";
      $end_char = "F";
      $objSpreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(7.56);
      $objSpreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(13.22);
      $objSpreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(12.50);
      $objSpreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(12.50);
      $objSpreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(12.50);
      $objSpreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15.67);
      foreach ($route_data as $k1 => $v1) {
        $cnt = 1;
        if(NULL != $v1) {
          // 標題
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 28.8 , 14 , $begin_char , $end_char , "own_title");
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getFont()->setBold(true); 
          $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:D{$row}")->setCellValue("A{$row}" , "{$taiwan_year}/{$taiwan_month}月份善耕自費戶收款明細"); // title
          $objSpreadsheet->getActiveSheet()->mergeCells("E{$row}:F{$row}")->setCellValue("E{$row}" , "{$k1}線"); // title
          $objSpreadsheet->getActiveSheet()->getStyle("E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // 設置水平靠右
          $row++;
          
          // 欄位名稱
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 21.9 , 14 , $begin_char , $end_char , "own");
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getFont()->setBold(true); 
          $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , "姓名"); // 姓名
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "金額"); // 金額
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "實收金額"); // 實收金額
          $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "入帳日"); // 入帳日
          $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "備註"); // 備註
          $row++;
          
          $first_row = $row;
          foreach ($v1 as $k2 => $v2) {
            $total_price = ($v2['rpo05'] + $v2['rpo06']) * $v2['rpo08'];
            $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 21.9 , 14 , $begin_char , $end_char , "own");
            $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getFont()->setBold(true); 
            $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , $cnt); // 序號
            $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , "{$v2['rpo02_ct_name']}"); // 姓名
            $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$total_price}"); // 金額
            $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , ""); // 實收金額
            $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , ""); // 入帳日
            $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , ""); // 備註
            if("Y" == $v2['rpo10']) {
              $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('ffd700');
            }
            $row++;
            $cnt++;
          } 
          
          $last_row = $row - 1;
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 25.8 , 16 , $begin_char , $end_char , "own");
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getFont()->setBold(true); 
          $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:B{$row}")->setCellValue("A{$row}" , "合計"); 
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT); // 設置水平靠右
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "=SUM(C{$first_row}:C{$last_row})"); 
          $objSpreadsheet->getActiveSheet()->getStyle("C{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
          $objSpreadsheet->getActiveSheet()->getStyle("C{$row}")->getNumberFormat() ->setFormatCode('$#,##0;-$#,##0'); 
          $row++;
          
          // 收款人
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 33.8 , 14 , $begin_char , $end_char , "own");
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getFont()->setBold(true); 
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT); // 設置水平靠右
          $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:C{$row}")->setCellValue("A{$row}" , "送餐員簽名："); 
          $objSpreadsheet->getActiveSheet()->mergeCells("D{$row}:E{$row}")->setCellValue("D{$row}" , "經手人："); 
          $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "日期："); 
          $row++;
          
          // FOOTER
          $objSpreadsheet = $this->set_default_style($objSpreadsheet , $row , 21 , 14 , $begin_char , $end_char , "own");
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:F{$row}")->getFont()->setBold(true); 
          $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:F{$row}")->setCellValue("A{$row}" , "請於款項收齊後,請繳交給出納，謝謝你。"); // footer
          $row++;
          
          // 空白行
          $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(16.8);
          $every_page[] = $row;
          $row++;
        }
      }
    }
    /*
    $print_area_str = '';
    if(NULL != $every_page) {
      foreach ($every_page as $k => $v) {
        if($k == 0) {
          $print_area_str .= "A1:G{$v}";
        }
        else {
          $prev_end_row = $every_page[$k-1] + 1;
          $print_area_str .= ",A{$prev_end_row}:G{$v}";
        } 
      } 
    }
    */
    $objSpreadsheet->getActiveSheet()->getPageSetup()->setPrintArea("A1:G{$row}");
    return $objSpreadsheet;
  }
  
  // **************************************************************************
  //  函數名稱: _download_own_receipe_data
  //  函數功能: 產生下載繳費資料(自費)
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _download_own_receipe_data($objSpreadsheet , $rpt_pay_month , $subsidy_data, $fd_tbl_col) {
    
    $taiwan_date = explode("-", $rpt_pay_month);
    $taiwan_year = $taiwan_date[0] - 1911;
    $taiwan_month = $taiwan_date[1];
    if($taiwan_date[1] != 10) {
      $taiwan_month = str_replace(0, '' , $taiwan_date[1]);
    }
    $objSpreadsheet->setActiveSheetIndex(0); 
    $objSpreadsheet->getActiveSheet()->setTitle("{$taiwan_year}年{$taiwan_month}月");
    if(NULL == $subsidy_data) {
      return $objSpreadsheet;
    }
     
    $i = 1;
    $row = 1;
    $page = 1;
    $date = date("md");
    $today = date('Y-m-t', strtotime($rpt_pay_month));
    $recipe_format= "{$taiwan_year}{$date}";
    foreach ($subsidy_data as $k => $v) {
      $row++; 
      
      // 服務費、收據編號
      $receipe_num = $taiwan_year.date('md').str_pad($i,3,'0',STR_PAD_LEFT); 
      $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "{$taiwan_year}年{$taiwan_month}月服務費"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "{$receipe_num}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("P{$row}" , "{$taiwan_year}年{$taiwan_month}月服務費"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("U{$row}" , "{$receipe_num}");
      $objSpreadsheet->getActiveSheet()->setCellValue("AA{$row}" , "{$taiwan_year}年{$taiwan_month}月服務費"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("AF{$row}" , "{$receipe_num}"); 
      $row++; 
      
      // 名字、路線
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'02_ct_name']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , $v['rp'.$fd_tbl_col.'04_reh01']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , "{$i}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'02_ct_name']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("T{$row}" , $v['rp'.$fd_tbl_col.'04_reh01']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'02_ct_name']); 
      $objSpreadsheet->getActiveSheet()->setCellValue("AE{$row}" , $v['rp'.$fd_tbl_col.'04_reh01']); 
      $row++; 
            
      // 數量、單價、小計、備註
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "備註：{$v['rp'.$fd_tbl_col.'11']}");
      $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "備註：{$v['rp'.$fd_tbl_col.'11']}");
      $objSpreadsheet->getActiveSheet()->setCellValue("AC{$row}" , "備註：{$v['rp'.$fd_tbl_col.'11']}");
      $row++;

      // 若午餐或晚餐數量為0，則不需列出
      // 午餐
      if(0 !=$v['rp'.$fd_tbl_col.'05']){
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , $v['rp'.$fd_tbl_col.'05_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , $v['rp'.$fd_tbl_col.'05_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("X{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , $v['rp'.$fd_tbl_col.'05_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $row++;
        if(0 !=$v['rp'.$fd_tbl_col.'06']){ // 晚餐
          $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $v['rp'.$fd_tbl_col.'09']);
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
          $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $v['rp'.$fd_tbl_col.'09']);
          $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
          $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
          $objSpreadsheet->getActiveSheet()->setCellValue("X{$row}" , $v['rp'.$fd_tbl_col.'09']);
          $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
          $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
        }
        $row++;
      }
      else{ // 晚餐
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $objSpreadsheet->getActiveSheet()->setCellValue("X{$row}" , $v['rp'.$fd_tbl_col.'09']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , $v['rp'.$fd_tbl_col.'06_total']);
        $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
        $row++;
        $row++;
      }
      
      // 合計
      $total  = $v['rp'.$fd_tbl_col.'05']+$v['rp'.$fd_tbl_col.'06'];
      $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$total}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['rp'.$fd_tbl_col.'08']);
      $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , "{$total}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , $v['rp'.$fd_tbl_col.'08']);
      $objSpreadsheet->getActiveSheet()->setCellValue("Y{$row}" , "{$total}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("Z{$row}" , $v['rp'.$fd_tbl_col.'08']);
      $row++;

      $row++; 
      
      // 實收金額、日期
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "{$today}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("T{$row}" , "{$today}"); 
      $objSpreadsheet->getActiveSheet()->setCellValue("AE{$row}" , "{$today}"); 
      $row++; 
      
      // 空白行
      $row++; 
      
      // 空白行 3 行
      $row = $row + 3;
      $i++;
      
      // 換頁
      if($i > 4 and $i % 4 == 1) {
        $page++;
        $row = $row - 1;
      }
    }
    
    $print_rage = 51 * $page;
    $objSpreadsheet->getActiveSheet()->getPageSetup()->setPrintArea("A1:AG{$print_rage}");
    return $objSpreadsheet;
  }
  
  // **************************************************************************
  //  函數名稱: set_default_style
  //  函數功能: 設定列高、線框
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function set_default_style($objSpreadsheet , $row , $row_height , $font_size , $start_row_char , $end_row_char , $rpt_type = NULL) {
    $allBorders = array('borders' => 
                        array('allBorders' => 
                              array('borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => array('rgb' => '000000')
                                   ),
                             )
                       );
    if(NULL != $rpt_type) {
      $allBorders['borders']['allBorders']['borderStyle'] = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK;
    }
    if('own_title' != $rpt_type) { 
      $objSpreadsheet->getActiveSheet()->getStyle("{$start_row_char}{$row}:{$end_row_char}{$row}")->applyFromArray($allBorders);
    }
    $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight($row_height);
    $objSpreadsheet->getActiveSheet()->getStyle("{$start_row_char}{$row}:{$end_row_char}{$row}")->getFont()->setName('標楷體')->setSize($font_size);
    $objSpreadsheet->getActiveSheet()->getStyle("{$start_row_char}{$row}:{$end_row_char}{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER) //設置垂直居中
                                                                                                                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); //設置水平居中
    return $objSpreadsheet;
  }
  
  // **************************************************************************
  //  函數名稱: set_seal
  //  函數功能: 加上印章
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function set_seal($objSpreadsheet , $row) {
    $master_row = $this->seal_model->get_by_sl01(1);
    $ceo_row = $this->seal_model->get_by_sl01(2);
    $accountant_row = $this->seal_model->get_by_sl01(3);
    $cashier_row = $this->seal_model->get_by_sl01(4);
    $number_row = $this->seal_model->get_by_sl01(5); // 統編

    $drawing_master = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing_master->setPath(FCPATH."upload_files/seal/{$master_row->sl02}");
    $drawing_master->setName('董事長');
    $drawing_master->setCoordinates("B{$row}");
    $drawing_master->setWidthAndHeight(50, 50);
    $drawing_master->setOffsetY(-8);
    $drawing_master->setWorksheet($objSpreadsheet->getActiveSheet());
    
    $drawing_ceo = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing_ceo->setPath(FCPATH."upload_files/seal/{$ceo_row->sl02}");
    $drawing_ceo->setName('執行長');
    $drawing_ceo->setCoordinates("D{$row}");
    $drawing_ceo->setWidthAndHeight(50, 50);
    $drawing_ceo->setOffsetY(-8);
    $drawing_ceo->setWorksheet($objSpreadsheet->getActiveSheet());
    
    $drawing_accountant = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing_accountant->setPath(FCPATH."upload_files/seal/{$accountant_row->sl02}");
    $drawing_accountant->setName('會計');
    $drawing_accountant->setCoordinates("F{$row}");
    $drawing_accountant->setWidthAndHeight(50, 50);
    $drawing_accountant->setOffsetY(-8);
    $drawing_accountant->setWorksheet($objSpreadsheet->getActiveSheet());
    
    $drawing_cashier = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing_cashier->setPath(FCPATH."upload_files/seal/{$cashier_row->sl02}");
    $drawing_cashier->setName('出納');
    $drawing_cashier->setCoordinates("H{$row}");
    $drawing_cashier->setWidthAndHeight(60, 60);
    $drawing_cashier->setOffsetY(-10);
    $drawing_cashier->setWorksheet($objSpreadsheet->getActiveSheet());
    
    $number_img_row = $row - 5;
    $drawing_number = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $drawing_number->setPath(FCPATH."upload_files/seal/{$number_row->sl02}");
    $drawing_number->setName('統編');
    $drawing_number->setCoordinates("H{$number_img_row}");
    $drawing_number->setWidthAndHeight(160, 160);
    $drawing_number->setOffsetX(30);
    $drawing_number->setOffsetY(-42);
    $drawing_number->setWorksheet($objSpreadsheet->getActiveSheet());
    
    return $objSpreadsheet;
  }
  // **************************************************************************
  //  函數名稱: _save_to_server
  //  函數功能: 存到server
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _save_to_server($filename , $objSpreadsheet , $cnt) {
    if($cnt > 1) {
      ob_end_clean();
      header("Content-type: text/html; charset=utf-8");
      header("Content-Type: application/vnd.ms-excel");
      header("Content-Disposition: attachment;filename=" . $filename);
    }
    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
    $objWriter->save(FCPATH."export_file/{$filename}"); // 儲存到server
    $objSpreadsheet->disconnectWorksheets(); // 清除資料
    unset($objSpreadsheet); // 清除資料
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

  // **************************************************************************
  //  函數名稱: chk_sec09()
  //  函數功能: 檢查付費方式
  //  程式設計: Kiwi
  //  設計日期: 2022-09-07
  // **************************************************************************
  public function chk_sec09($ocl_row, $sec09_str) {  
    if(NULL == $ocl_row) {
      return $sec09_str;
    }
    if('不變' == $ocl_row->ocl_sec09_before_str && '不變' == $ocl_row->ocl_sec09_after_str) {
      return $sec09_str;
    }
    if('' == $ocl_row->ocl_sec09_before_str or '不收費' == $ocl_row->ocl_sec09_before_str or '不變' == $ocl_row->ocl_sec09_before_str) {
      if('' == $ocl_row->ocl_sec09_after_str) {
        return $sec09_str;
      }
      else {
        return $ocl_row->ocl_sec09_after_str;
      }
    }
    // else {
    //   return $ocl_row->ocl_sec09_before_str;
    // }

    if('' == $ocl_row->ocl_sec09_after_str or '不收費' == $ocl_row->ocl_sec09_after_str or '不變' == $ocl_row->ocl_sec09_after_str) {
      if('' == $ocl_row->ocl_sec09_before_str) {
        return $sec09_str;
      }
      else {
        return $ocl_row->ocl_sec09_before_str;
      }
    }
    // else {
    //   return $ocl_row->ocl_sec09_after_str;
    // }

    return $sec09_str;
  }
  
  function __destruct() {
    $url_str[] = 'be/rpt_pay/upd_reh';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // page foot
    }
  }
}
