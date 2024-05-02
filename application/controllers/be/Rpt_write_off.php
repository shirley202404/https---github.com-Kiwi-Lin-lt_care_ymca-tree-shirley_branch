<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpt_Write_off extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('daily_shipment_model'); // 配送單資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('meal_order_date_type_model'); // 日期類型資料
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
    $this->tpl->assign('tv_title','核銷報表');
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
    $type = '';
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');
    $v = $this->input->post();
        
    switch ($v["rpt_dys09"]) {   
      case "1": 
        $dys02 = "午間";
        $this->x_col_val = "2";
        $this->start_time_hour = "11";
        $this->start_time_minute = "00";
        $this->end_time_hour = "12";
        $this->end_time_minute = "00";
        break;
      case "3": 
        $dys02 = "夜間";
        $this->x_col_val = "3";
        $this->start_time_hour = "17";
        $this->start_time_minute = "00";
        $this->end_time_hour = "18";
        $this->end_time_minute = "00";
        break;
    }
    
    $sample_file = FCPATH."pub/sample/funding_sample.xls";
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
    $objSpreadsheet->setActiveSheetIndex(0);
    
    $row = 2; 
    switch ($v["rpt_type"]) {   
      case "1": 
        $type = '月報表';
        $download_data = NULL;
        $date_str = str_replace("-", "", $v['rpt_month_dys01']);
        $date_arr = get_month_date($v['rpt_month_dys01']);
        $objSpreadsheet->getActiveSheet()->setTitle("工作表1");
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
          if(empty($v)) {
            continue;
          }
          foreach ($v as $k2 => $v2) {
            $objSpreadsheet = $this->_row_content($objSpreadsheet, $row, $v2);
            $row++;  
          }
        } 
        break;
      case "2": 
        $type = '日報表';
        $date_str = str_replace("-", "", $v['rpt_date_dys01']);
        $daily_shipment_row = $this->meal_order_model->get_data_by_dys01($v['rpt_date_dys01']); // 列出今日送餐資料資料
        if(NULL != $daily_shipment_row) {
          $objSpreadsheet->getActiveSheet()->setTitle("工作表1");
          foreach ($daily_shipment_row as $k => $v) {
            $objSpreadsheet = $this->_row_content($objSpreadsheet, $row, $v);
            $row++;
          }
        }
      break;
    }  
    
    $ch_filename = "{$date_str}全區{$dys02}長照(上傳{$type}).xls";
    $en_filename = "{$date_str}-write_off.xls";

    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=" . $en_filename);
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objSpreadsheet , 'Xls');
    $writer->save(FCPATH."export_file/{$en_filename}"); // 儲存到server

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff >= 60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }   
    
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename, $time_diff); 
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _row_content()
  //  函數功能: 每列的內容
  //  程式設計: Kiwi
  //  設計日期: 2020/9/2
  // **************************************************************************
  public function _row_content($objSpreadsheet, $row, $data) {
    $taiwan_date = explode("-", $data['mlo01']);
    $taiwan_year = $taiwan_date[0] - 1911;

    $price = hlp_get_subsidy_price($type='meal', $taiwan_year);
    $x_col_val = $this->x_col_val;
    $start_time_hour = $this->start_time_hour;
    $start_time_minute = $this->start_time_minute;
    $end_time_hour = $this->end_time_hour;
    $end_time_minute = $this->end_time_minute;
    
    $taiwan_date_str = "{$taiwan_year}{$taiwan_date[1]}{$taiwan_date[2]}";
    $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $data['ct03']);
    $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $taiwan_date_str);
    $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", "OT01");
    $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", "1");
    $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", "1");
    $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", "{$price}");
    $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $data['dp03']);
    $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", $start_time_hour);
    $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $start_time_minute);
    $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", $end_time_hour);
    $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", $end_time_minute);
    $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("P{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("Q{$row}", 1);
    $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("S{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("T{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("U{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("V{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("W{$row}", "");
    $objSpreadsheet->getActiveSheet()->setCellValue("X{$row}", "{$x_col_val}");
    return $objSpreadsheet;
  }

  function __destruct() {
    $url_str[] = '';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // cancel foot
    }
  }
}