<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpt_Write_off extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('daily_shipment_model'); // 配送單資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('delivery_person_model'); // 外送員資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."daily_rpt/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"弗傳慈心基金會");
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    return;
  }
  
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 輸入畫面
  //  程式設計: Kiwi
  //  設計日期: 2021/04/09
  // **************************************************************************
  public function index() {
    $msel = 'download';
    $this->load->library('pagination');
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','?C???q??');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_download_link',be_url().'rpt_write_off/download/');
    $this->tpl->display("be/rpt_write_off_input.html");
    return;
  }
  
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載execl檔案
  //  程式設計: Kiwi
  //  設計日期: 2020/9/2
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_msg = '';
    $sw_id_number = "L225051765"; // 社工身分證
    $time_start = date('Y-m-d H:i:s');
    $v = $this->input->post();
        
    switch ($v["rpt_dys09"]) {   
      case "1": 
        $dys02 = "午間";
        $start_time_hour = "11";
        $start_time_minute = "0";
        $end_time_hour = "12";
        $end_time_minute = "0";
      break;
      case "2": 
        $dys02 = "中晚";
        $start_time_hour = "17";
        $start_time_minute = "0";
        $end_time_hour = "18";
        $end_time_minute = "30";
      break;
      case "3": 
        $dys02 = "夜間";
        $start_time_hour = "17";
        $start_time_minute = "0";
        $end_time_hour = "18";
        $end_time_minute = "30";
      break;
    }
    
    $sample_file = FCPATH."pub/sample/funding_sample.xls";
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
    $objSpreadsheet->setActiveSheetIndex(0);
    $num=2; 

    switch ($v["rpt_type"]) {   
      case "1": 
        $download_data = NULL;
        $date_arr = get_month_date($v['rpt_month_dys01']);
        $objSpreadsheet->getActiveSheet()->setTitle("{$dys02}月報表");
        foreach ($date_arr as $k => $date) {
          $daily_shipment_row = $this->meal_order_model->get_data_by_dys01($date); // 列出今日送餐資料資料
          if(NULL != $daily_shipment_row) {
            foreach ($daily_shipment_row as $k => $v) {
              if(!isset($download_data[$v['sec_s_num']])) {
                $download_data[$v['sec_s_num']] = NULL;
              }
              $download_data[$v['sec_s_num']][] = $v;
            } 
          }
        } 
        foreach ($download_data as $k => $v) {
          if(NULL != $v) {
            foreach ($v as $k2 => $v2) {
              $taiwan_date = explode("-", $v2['mlo01']);
              $taiwan_year = $taiwan_date[0] - 1911;
              $taiwan_date_str = "{$taiwan_year}{$taiwan_date[1]}{$taiwan_date[2]}";
              $objSpreadsheet->getActiveSheet()->setCellValue("A{$num}", $v2['ct03']);
              $objSpreadsheet->getActiveSheet()->setCellValue("B{$num}", $taiwan_date_str);
              $objSpreadsheet->getActiveSheet()->setCellValue("C{$num}", "OT01");
              $objSpreadsheet->getActiveSheet()->setCellValue("D{$num}", "1");
              $objSpreadsheet->getActiveSheet()->setCellValue("E{$num}", "1");
              $objSpreadsheet->getActiveSheet()->setCellValue("F{$num}", "70");
              $objSpreadsheet->getActiveSheet()->setCellValue("G{$num}", $sw_id_number);
              $objSpreadsheet->getActiveSheet()->setCellValue("H{$num}", $start_time_hour);
              $objSpreadsheet->getActiveSheet()->setCellValue("I{$num}", $start_time_minute);
              $objSpreadsheet->getActiveSheet()->setCellValue("J{$num}", $end_time_hour);
              $objSpreadsheet->getActiveSheet()->setCellValue("K{$num}", $end_time_minute);
              $objSpreadsheet->getActiveSheet()->setCellValue("L{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("M{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("N{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("O{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("P{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("Q{$num}", 1);
              $objSpreadsheet->getActiveSheet()->setCellValue("R{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("S{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("T{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("U{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("V{$num}", "");
              $objSpreadsheet->getActiveSheet()->setCellValue("W{$num}", "");
              $num++;    
            } 
          }
        } 
      break;
      case "2": 
        $daily_shipment_row = $this->meal_order_model->get_data_by_dys01($v['rpt_date_dys01']); // 列出今日送餐資料資料
        if(NULL != $daily_shipment_row) {
          $objSpreadsheet->getActiveSheet()->setTitle("{$dys02}日報表");
          foreach ($daily_shipment_row as $k => $v) {
            $taiwan_date = explode("-", $v['mlo01']);
            $taiwan_year = $taiwan_date[0] - 1911;
            $taiwan_date_str = "{$taiwan_year}{$taiwan_date[1]}{$taiwan_date[2]}";
            $objSpreadsheet->getActiveSheet()->setCellValue("A{$num}", $v['ct03']);
            $objSpreadsheet->getActiveSheet()->setCellValue("B{$num}", $taiwan_date_str);
            $objSpreadsheet->getActiveSheet()->setCellValue("C{$num}", "OT01");
            $objSpreadsheet->getActiveSheet()->setCellValue("D{$num}", "1");
            $objSpreadsheet->getActiveSheet()->setCellValue("E{$num}", "1");
            $objSpreadsheet->getActiveSheet()->setCellValue("F{$num}", "70");
            $objSpreadsheet->getActiveSheet()->setCellValue("G{$num}", $sw_id_number);
            $objSpreadsheet->getActiveSheet()->setCellValue("H{$num}", $start_time_hour);
            $objSpreadsheet->getActiveSheet()->setCellValue("I{$num}", $start_time_minute);
            $objSpreadsheet->getActiveSheet()->setCellValue("J{$num}", $end_time_hour);
            $objSpreadsheet->getActiveSheet()->setCellValue("K{$num}", $end_time_minute);
            $objSpreadsheet->getActiveSheet()->setCellValue("L{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("M{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("N{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("O{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("P{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("Q{$num}", 1);
            $objSpreadsheet->getActiveSheet()->setCellValue("R{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("S{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("T{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("U{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("V{$num}", "");
            $objSpreadsheet->getActiveSheet()->setCellValue("W{$num}", "");
            $num++;
          }
        }
      break;
    }  
    
    // $objSpreadsheet->getActiveSheet()->getStyle("A1:W{$num}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); // 水平置中
    // $objSpreadsheet->getActiveSheet()->getStyle("A1:W{$num}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER); // 垂直置中
    // $objSpreadsheet->getActiveSheet()->getStyle("A2:W{$num}")->getAlignment()->setWrapText(true); // 設定換行
        
    // $filename = "{$dys01}全區{$dys02}長照(上傳{$type}).xls";
    $filename = "write_off.xls";
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
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=" . $filename);
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objSpreadsheet , 'Xls');
    $writer->save(FCPATH."export_file/{$filename}"); // 儲存到server

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff>=60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }   
    
    $download_file_big5_en = base64url_encode($download_file_big5);
    $be_url = be_url()."daily_rpt/download_file/";
    $rtn_msg .= "<table class='table table-bordered table-hover'>";
    $rtn_msg .= "  <thead>";
    $rtn_msg .= "    <tr>";
    $rtn_msg .= "      <th width='30%'>項目</th>";
    $rtn_msg .= "      <th width='70%'>說明</th>";
    $rtn_msg .= "    </tr>";
    $rtn_msg .= "  </thead>";
    $rtn_msg .= "  <tbody>";
    $rtn_msg .= "      <td>下載檔案</th>";
    $rtn_msg .= "      <td>{$filename}&nbsp; <button class='btn btn-C3 btn-sm' type='button' onclick='location.href=\"{$be_url}{$download_file_big5_en}\"'>檔案下載</button></td>";
    $rtn_msg .= "    </tr>";
    $rtn_msg .= "    <tr>";
    $rtn_msg .= "      <td>處理時間</th>";
    $rtn_msg .= "      <td>{$time_diff}</td>";
    $rtn_msg .= "    </tr>";
    $rtn_msg .= "  </tbody>";
    $rtn_msg .= "</table>";  
    
    echo $rtn_msg;
    return;
  }
  
  function __destruct() {
    $url_str[] = '';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // ???? foot
    }
  }
}
