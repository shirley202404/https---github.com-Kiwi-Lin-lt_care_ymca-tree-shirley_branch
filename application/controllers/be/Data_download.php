<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data_download extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('route_model'); // 路線資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('meal_order_date_type_model'); // 訂單日期類型資料
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
    $this->tpl->assign('tv_return_link',be_url()."data_download/"); // return 預設到瀏覽畫面
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
  //  程式設計: loyenhsiang
  //  設計日期: 2024/02/20
  // **************************************************************************
  public function index() {
    $msel = 'download';
    $this->load->library('pagination');
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','核銷報表');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_download_link',be_url().'data_download/download/');
    $this->tpl->display("be/data_download_input.html");
    return;
  }
  
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載execl檔案
  //  程式設計: loyenhsiang
  //  設計日期: 2020/9/2
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');
    $v = $this->input->post();
    
    $download_data = NULL;
    $date_str = str_replace("-", "", $v['start_month']);
    list($start_year, $start_month) = explode("-", $v['start_month']);
    $tw_start_year = $start_year - 1911;
    list($end_year, $end_month) = explode("-", $v['end_month']);
    $tw_end_year = $end_year - 1911;


    $sample_file = FCPATH."pub/sample/data_download_sample.xlsx";
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);

    $start_col = 'B';
    $col = $start_col;
    $col_index = PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($col);
    for ($month = $start_month; $month <= $end_month; $month++){
      for ($i = 1; $i <= 2; $i++){
        $all_people = $this->meal_order_model->get_by_month($start_year."-".$month, $i);
        $how_many_people = 0;
        if (is_array($all_people)) {
          $how_many_people = count($all_people);
        }
        $row = $i + 2;
        if ($row == 3){
          $objSpreadsheet->getActiveSheet()->setCellValue($col.strval($row-1), intval($month)."月份");
        }
        $objSpreadsheet->getActiveSheet()->setCellValue($col.strval($row), $how_many_people);
      }
      if ($month < $end_month){
        $col_index++;
        $col = PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_index);
        $objSpreadsheet->getActiveSheet()->insertNewColumnBefore($col); // 插入一行
      }
    }
    // $objSpreadsheet->getActiveSheet()->mergeCells("{$start_col}1:{$col}1"); // 合併

    // $meal_order_date_type_row = $this->meal_order_date_type_model->get_all_by_month($year, $month, $date_type='1,3');
    // $date_arr = array_column($meal_order_date_type_row, 'mlo_dt01');

    // foreach ($date_arr as $k => $date) {
    //   $daily_shipment_row = $this->meal_order_model->get_service_data_by_dys01($date); // 列出今日送餐資料資料
    //   if(NULL != $daily_shipment_row) {
    //     foreach ($daily_shipment_row as $k => $v) {
    //       if(empty($v['vp_s_num'])) {
    //         continue;
    //       }
    //       if(1 != $v['sec01']) {
    //         continue;
    //       }
    //       if(!isset($download_data[$v['mlo02']][$v['vp_s_num']])) {
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['vp_name'] = $v['vp_name'];
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['unit_price'] = 100;
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['total_price'] = 0;
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'] = NULL;
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['route_arr'] = array();
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['delivery_day_cnt'] = 0;
    //       }
    //       if(!in_array($v['reh_s_num'], $download_data[$v['mlo02']][$v['vp_s_num']]['route_arr'])) {
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['route_arr'][] = $v['reh_s_num'];
    //       }
    //       if(!isset($download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'][$date])) {
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['delivery_day_cnt'] += 1;
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'][$date]['date'] = $date;
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'][$date]['delivery_num'] = 0;
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'][$date]['client_arr'] = array();
    //       }
    //       if(!in_array($v['ct_s_num'], $download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'][$date]['client_arr'])) {
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'][$date]['delivery_num'] += 1;
    //         $download_data[$v['mlo02']][$v['vp_s_num']]['date_arr'][$date]['client_arr'][] = $v['ct_s_num'];
    //       }
    //     } 
    //   }
    // } 

    // foreach ($download_data as $k => $mlo02) {
    //   foreach ($mlo02 as $vp_s_num => $vp_data) {
    //     $date_arr = array_column($vp_data['date_arr'], 'date');
    //     $download_data[$k][$vp_s_num]['date_str'] = $this->_date_str($date_arr)[0];
    //     $download_data[$k][$vp_s_num]['route_str'] = $this->_route_str($vp_data['route_arr']);
    //     $download_data[$k][$vp_s_num]['total_price'] = $vp_data['unit_price'] * $vp_data['delivery_day_cnt'];
    //     $download_data[$k][$vp_s_num]['max_delivery_num'] = max(array_column($vp_data['date_arr'], 'delivery_num'));
    //     unset($download_data[$k][$vp_s_num]['date_arr'], $download_data[$k][$vp_s_num]['route_arr']);
    //   }
    // }
    
    // foreach ($download_data as $k => $sec_data) {
    //   $cnt = 1;
    //   $row = 5; 
    //   switch ($k) {
    //     case 1:
    //       $objSpreadsheet->setActiveSheetIndex(0);
    //       $objSpreadsheet->getActiveSheet()->setTitle("{$month}月-午餐");
    //       $objSpreadsheet->getActiveSheet()->setCellValue("A1", "臺中市政府衛生局{$tw_year}年{$month}月   午餐  財團法人臺中市私立弗傳慈心社會福利慈善事業基金會   長期照顧十年計畫－營養餐飲服務");
    //       break;
    //     case 2:
    //       $objSpreadsheet->setActiveSheetIndex(1);
    //       $objSpreadsheet->getActiveSheet()->setTitle("{$month}月-晚餐");
    //       $objSpreadsheet->getActiveSheet()->setCellValue("A1", "臺中市政府衛生局{$tw_year}年{$month}月   晚餐  財團法人臺中市私立弗傳慈心社會福利慈善事業基金會   長期照顧十年計畫－營養餐飲服務");
    //       break;
    //   }
    //   $row_cnt = count($sec_data);
    //   for ($i=0; $i < $row_cnt-3; $i++) { 
    //     $objSpreadsheet->getActiveSheet()->insertNewRowBefore(6); // insert one row after the 43+$i row
    //   }
    //   foreach ($sec_data as $k_vp => $vp_data) {
    //     $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $cnt);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $vp_data['vp_name']);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $vp_data['date_str']);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $vp_data['delivery_day_cnt']);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $vp_data['unit_price']);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $vp_data['total_price']);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", "=F{$row}*0.2");
    //     $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", "=F{$row}*0.8");
    //     $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $vp_data['route_str']);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", $vp_data['max_delivery_num']);
    //     $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", '');
    //     $objSpreadsheet->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
    //     $cnt++;
    //     $row++;
    //   }
    //   $last_row = $row - 1;
    //   $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", "=SUM(F5:F{$last_row})");
    //   $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", "=SUM(G5:G{$last_row})");
    //   $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", "=SUM(H5:H{$last_row})");
    //   $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", "=SUM(J5:J{$last_row})");

    //   $pic_row = $row + 2;
    //   $drawing_number = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    //   $drawing_number->setPath(FCPATH."upload_files/car_fare/unit_area.png");
    //   $drawing_number->setName('');
    //   $drawing_number->setCoordinates("J{$pic_row}");
    //   $drawing_number->setWidthAndHeight(160, 160);
    //   $drawing_number->setWorksheet($objSpreadsheet->getActiveSheet());
    // }
    
    $ch_filename = "{$date_str}數據下載.xlsx";
    $en_filename = "{$date_str}_data_download.xlsx";

    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=" . $en_filename);
    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
    $objWriter->save(FCPATH."export_file/{$en_filename}"); // 儲存到server

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff >= 60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }   
    
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename, $time_diff); 
    echo json_encode($rtn_msg);
    return;
  }
  // **************************************************************************
  //  函數名稱: _date_str
  //  函數功能: 日期字串格式處理
  //  程式設計: loyenhsiang
  //  設計日期: 2022/03/21
  // **************************************************************************
  public function _date_str($date_arr) {
    $date_str_arr = NULL;
    foreach ($date_arr as $k => $v) {
      $year_w = date("oW", strtotime($v));
      if(!isset($date_str_arr[$year_w])) {
        $date_str_arr[$year_w]['date'] = NULL;
      }
      $date_str_arr[$year_w]['date'][] = date("n/j", strtotime($v));
      usort($date_str_arr[$year_w]['date'], function ($a, $b) {
        return strtotime($a) - strtotime($b);
      });
    }

    $date_str = NULL;
    $date_cnt = count($date_arr);
    foreach($date_str_arr as $k => $week) {
      foreach ($week as $k2 => $date) {
        if($date != NULL) {
          $last_date_index = count($date);
          if($date[0] != $date[$last_date_index - 1]) {
            $date_str[] = "{$date[0]}-{$date[$last_date_index - 1]}";
          }
          else {
            $date_str[] = "{$date[0]}";
          }
        }
      }
    }
    $date_str = join("\n", $date_str);
    return array($date_str, $date_cnt);
  }
  // **************************************************************************
  //  函數名稱: _route_str
  //  函數功能: 路線字串處理
  //  程式設計: loyenhsiang
  //  設計日期: 2023/04/19
  // **************************************************************************
  public function _route_str($route_arr) {
    $route_str_arr = NULL;
    if(!empty($route_arr)) {
      foreach ($route_arr as $k => $reh_s_num) {
        $route_row = $this->route_model->get_one($reh_s_num);
        $route_str_arr[] = $route_row->reh02;
      }
    }
    $route_str = join(",", $route_str_arr);
    return $route_str;
  }

  function __destruct() {
    $url_str[] = 'be/data_download/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // page foot
    }
  }
}
