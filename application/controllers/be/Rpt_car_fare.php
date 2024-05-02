<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpt_car_fare extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('route_model'); // 路線資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('delivery_person_model'); // 送餐員資料
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
    $this->tpl->assign('tv_return_link',be_url()."rpt_car_fare/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_download_link',be_url().'rpt_car_fare/download/');
    $this->tpl->display("be/rpt_car_fare_input.html");
    return;
  }
  
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載execl檔案
  //  程式設計: loyenhsiang
  //  設計日期: 2024/1/30
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');
    $v = $this->input->post();
            
    $download_data = NULL;
    $date_str = str_replace("-", "", $v['rpt_month']);
    list($year, $month) = explode("-", $v['rpt_month']);
    $tw_year = $year - 1911;
    $first_day = new DateTime($v['rpt_month'] . '-01'); // 當月第一天
    $last_day = clone $first_day;
    $last_day->modify('last day of this month'); // 當月最後一天

    $sample_file = FCPATH."pub/sample/car_fare_sample.xlsx";
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);

    $all_delivery = $this->delivery_person_model->get_all_is_available();

    $rest_date = array();
    foreach ($all_delivery as $s_num => $delivery_person) {
      $s_num_to_find = $delivery_person['s_num'];
      $rest_dates = $this->delivery_person_model->get_rest($s_num_to_find);
      if (is_array($rest_dates)) {
        foreach($rest_dates as $s_num => $rest_data){
          $dp_s_num = null;
          $dpr01 = null;
          foreach($rest_data as $key => $value){
            if ($key == 'dp_s_num') {
              $dp_s_num = $value;
            }
            elseif ($key == 'dpr01') {
              $dpr01 = $value;
            }
          }
          if ($dp_s_num !== null && $dpr01 !== null) {
            if (!isset($rest_date[$dp_s_num])) {
              $rest_date[$dp_s_num] = array();
            }
            $rest_date[$dp_s_num][] = $dpr01;
          }
        }
      }
    }
    $all_orders = $this->meal_order_model->how_many_times_for_route_by_dys01($v['rpt_month']);

    $objSpreadsheet->setActiveSheetIndex(0);  // 第一個工作表

    $cell = $objSpreadsheet->getActiveSheet()->getCell('A2');
    $new_value = str_replace('112', $tw_year, $cell->getValue());
    $cell->setValue($new_value);
    $new_value = str_replace('01', $month, $cell->getValue());
    $cell->setValue($new_value);

    $row = 6;
    foreach ($all_delivery as $s_num => $delivery_person) {
      $s_num_to_find = $delivery_person['s_num'];
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $delivery_person['dp01'].$delivery_person['dp02']);
      $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $delivery_person['dp10_county'].$delivery_person['dp10_district'].$delivery_person['dp10_addr']);
      if ($delivery_person['dp09_teltphone'] != NULL){
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $delivery_person['dp09_teltphone']);
      }
      else{
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $delivery_person['dp09_homephone']);
      }
      $num_of_lunch_days = 0;
      $num_of_dinner_days = 0;
      if (isset($all_orders)) {
        foreach ($all_orders as $date => $order_details) {
          if (isset($order_details[$s_num_to_find])) { // 檢查是否有該外送員的訂單
            if (isset($rest_date[$s_num_to_find]) && is_array($rest_date[$s_num_to_find])) {
              if (!in_array($date, $rest_date[$s_num_to_find])){ // 檢查是否請假
                if ($order_details[$s_num_to_find]['lunch']!="0") {
                  $num_of_lunch_days++;
                }
                if ($order_details[$s_num_to_find]['dinner']!="0") {
                  $num_of_dinner_days++;
                }
              }
            }
            else{
              if ($order_details[$s_num_to_find]['lunch']!="0") {
                $num_of_lunch_days++;
              }
              if ($order_details[$s_num_to_find]['dinner']!="0") {
                $num_of_dinner_days++;
              }
            }
          }
        }
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $num_of_lunch_days+$num_of_dinner_days);
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", 125);
      $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", "=F{$row}*G{$row}");
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", "=SUM(H{$row}:H{$row})");
      $row++;
    }
    
    $objSpreadsheet->setActiveSheetIndex(1);  // 第二個工作表
    // 迴圈之外設定一次 Active Sheet
    $activeSheetIndex = $objSpreadsheet->getActiveSheetIndex();
    
    foreach ($all_delivery as $s_num => $delivery_person) {
      $s_num_to_find = $delivery_person['s_num'];
      $newSheet = clone $objSpreadsheet->getActiveSheet(); // 複製工作表
      $newSheet->setTitle($delivery_person['dp01'].$delivery_person['dp02']."志工");
      $objSpreadsheet->addSheet($newSheet);
      // 在迴圈外使用設定的 Active Sheet Index
      $objSpreadsheet->setActiveSheetIndex($activeSheetIndex);

      $cell = $newSheet->getCell('A1');  // 使用新工作表的儲存格
      $new_value = str_replace('112', $tw_year, $cell->getValue());
      $cell->setValue($new_value);
      $new_value = str_replace('01', $month, $cell->getValue());
      $cell->setValue($new_value);
      $new_value = str_replace('XXX', $delivery_person['dp01'].$delivery_person['dp02'], $cell->getValue());
      $cell->setValue($new_value);
      
      $rest_index = array();
      $totals = array(
        'B' => 0,
        'C' => 0,
        'D' => 0,
        'E' => 0,
        'F' => 0
      );
      $row = 3;
      foreach ($all_orders as $date => $order_details) {
        if (isset($order_details[$s_num_to_find])) {
          if (isset($order_details[$s_num_to_find]['lunch'])){
            $lunch_count = $order_details[$s_num_to_find]['lunch'];
          }
          else{
            $lunch_count = '';
          }
          if (isset($order_details[$s_num_to_find]['lunch'])) {
            $lunch_expense = $this->cal_expense($order_details[$s_num_to_find]['lunch']);
          }
          else {
            $lunch_expense = '';
          }
          if (isset($order_details[$s_num_to_find]['dinner'])){
            $dinner_count = $order_details[$s_num_to_find]['dinner'];
          }
          else{
            $dinner_count = '';
          }
          if (isset($order_details[$s_num_to_find]['dinner'])) {
            $dinner_expense = $this->cal_expense($order_details[$s_num_to_find]['dinner']);
          }
          else {
            $dinner_expense = '';
          }
        }
        $newSheet->setCellValue("A{$row}", $date);
        $newSheet->setCellValue("B{$row}", $lunch_count);
        $newSheet->setCellValue("C{$row}", $lunch_expense);
        $newSheet->setCellValue("D{$row}", $dinner_count);
        $newSheet->setCellValue("E{$row}", $dinner_expense);
        $newSheet->setCellValue("F{$row}", "=SUM(C{$row},E{$row})");
        if (isset($rest_date[$s_num_to_find]) && is_array($rest_date[$s_num_to_find])) {
          if (in_array($date, $rest_date[$s_num_to_find])){ // 檢查是否請假
            $newSheet->setCellValue("G{$row}", "請假");
            $rest_index[] = $row; // 請假的$row
          }
        }
        $row++;
      }
      foreach ($rest_index as $index) {
        foreach ($totals as $column => $total) {
          $totals[$column] += intval($newSheet->getCell("{$column}{$index}")->getCalculatedValue());
        }
      }
      $newSheet->setCellValue("B30", "=SUM(B3:B29) - {$totals['B']}");
      $newSheet->setCellValue("C30", "=SUM(C3:C29) - {$totals['C']}");
      $newSheet->setCellValue("D30", "=SUM(D3:D29) - {$totals['D']}");
      $newSheet->setCellValue("E30", "=SUM(E3:E29) - {$totals['E']}");
      $newSheet->setCellValue("F30", "=SUM(F3:F29) - {$totals['F']}");

    }
    // 刪掉預設模板
    $objSpreadsheet->removeSheetByIndex($activeSheetIndex);

    $objSpreadsheet->setActiveSheetIndex(1);  // 第二個工作表
    $delivery_orders = array(); // s_num => 2024-2-1 => lunch or dinner => 送了幾餐
    $add = array(); // 加給 s_num => 天數
    $total_days = 0;
    $total_rest_date = 0;
    foreach ($all_orders as $date => $orders) {
      foreach ($orders as $delivery => $order) {
        $delivery_orders[$delivery][$date] = $order;

        $addition = FALSE;
        if ($order["lunch"] !== "0" && $order["dinner"] !== "0"){ // 判斷加給
          if (!isset($add[$delivery])) {
            $add[$delivery] = 1;
            $addition = TRUE;
          }
          else {
            $add[$delivery]++;
            $addition = TRUE;
          }
        }
        if (isset($rest_date[$delivery]) && is_array($rest_date[$delivery])) {
          if (in_array($date, $rest_date[$delivery])){ // 檢查是否請假
            unset($delivery_orders[$delivery][$date]); // 移除
            if ($addition == TRUE){ // 判斷當天是否有加給
              $add[$delivery]--;
            }
          }
        }

        if ($delivery == 2){
          if ($order["lunch"] !== "0" or $order["dinner"] !== "0"){ // 判斷洪松榆總天數
            $total_days++;
          }
          if (isset($rest_date[$delivery]) && is_array($rest_date[$delivery])) {
            if (in_array($date, $rest_date[$delivery])){ // 檢查是否請假
              $total_rest_date++;
            }
          }
        }
      }
    }
    $cell = $objSpreadsheet->getActiveSheet()->getCell('A1');
    $new_value = str_replace('112', $tw_year, $cell->getValue());
    $cell->setValue($new_value);
    $new_value = str_replace('01', $month, $cell->getValue());
    $cell->setValue($new_value);
    $row = 3;
    foreach ($all_delivery as $s_num => $delivery_person) {
      $alignment = FALSE;
      $start_row = $row;
      $s_num_to_find = $delivery_person['s_num'];
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $delivery_person['dp01'].$delivery_person['dp02']);

      if ($s_num_to_find == 2){ // 洪松榆特殊計算方式
        if (isset($rest_date[$s_num_to_find])) {
          $rest_dates = $rest_date[$s_num_to_find];
          $rest_in_month = false;
          foreach ($rest_dates as $date) {
            if ($date > $first_day && $date < $last_day) {
              $rest_in_month = true;
              break;
            }
          }
          if ($rest_in_month == false){
            $expense = 2500;
          }
          else{
            $expense = 2500 / $total_days * ($total_days-$total_rest_date);
          }
        }
        else{
          $expense = 2500;
        }
        $objSpreadsheet->getActiveSheet()->mergeCells("B{$start_row}:H{$row}");
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $expense);
        // 添加框線
        $objSpreadsheet->getActiveSheet()->getStyle("A{$start_row}:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $row++;
        continue;
      }

      $only_orders = array(
        'lunch' => array(),
        'dinner' => array()
      );
      foreach ($delivery_orders[$s_num_to_find] as $date => $orders) { // 將資料整理成 一天送幾餐=>幾天
        foreach ($orders as $meal => $quantity) {
          if ($quantity !== '0'){
            if (!isset($only_orders[$meal][$quantity])) {
              $only_orders[$meal][$quantity] = 1;
            }
            else {
              $only_orders[$meal][$quantity]++;
            }
          }
        }
      }
      $combined_orders_lunch = []; // 合併後的午餐訂單
      $combined_orders_dinner = []; // 合併後的晚餐訂單

      foreach ($only_orders as $meal => $orders) {
        foreach ($orders as $quantity => $days) {
          $range = $this->get_range($quantity);
          
          // 將相同範圍的數量和天數合併
          if ($meal == 'lunch') {
            if (!isset($combined_orders_lunch[$range])) {
                $combined_orders_lunch[$range] = ['quantity' => $quantity, 'days' => $days];
            } else {
                $combined_orders_lunch[$range]['days'] += $days; // 將天數相加
            }
          }
          elseif ($meal == 'dinner') {
            if (!isset($combined_orders_dinner[$range])) {
                $combined_orders_dinner[$range] = ['quantity' => $quantity, 'days' => $days];
            } else {
                $combined_orders_dinner[$range]['days'] += $days; // 將天數相加
            }
          }
        }
      }
      foreach ($combined_orders_lunch as $range => $data) {
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $range);
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $this->cal_expense($data['quantity']));
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $data['days']);
        $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", "=D{$row}*E{$row}");
        $alignment = TRUE;
        $row++;
      }
      foreach ($combined_orders_dinner as $range => $data) {
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $range);
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $this->cal_expense($data['quantity']));
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $data['days']);
        $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", "=D{$row}*E{$row}");
        $alignment = TRUE;
        $row++;
      }
      if ($alignment == TRUE){
        $row--;
      }
      // 合併
      $objSpreadsheet->getActiveSheet()->mergeCells("A{$start_row}:A{$row}");
      $objSpreadsheet->getActiveSheet()->mergeCells("G{$start_row}:G{$row}");
      $objSpreadsheet->getActiveSheet()->mergeCells("H{$start_row}:H{$row}");
      $objSpreadsheet->getActiveSheet()->mergeCells("I{$start_row}:I{$row}");
      if (isset($add[$s_num_to_find])){
        $objSpreadsheet->getActiveSheet()->setCellValue("G{$start_row}", $add[$s_num_to_find] * 50);
      }
      else{
        $objSpreadsheet->getActiveSheet()->setCellValue("G{$start_row}", 0);
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("H{$start_row}", "=SUM(F{$start_row}:F{$row})+G{$start_row}");
      // 添加框線
      $objSpreadsheet->getActiveSheet()->getStyle("A{$start_row}:I{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
      $row++;
    }

    $ch_filename = "{$date_str}志工交通費印領清冊.xlsx";
    $en_filename = "{$date_str}_car_fare.xlsx";

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
  //  函數名稱: get_range()
  //  函數功能: 判斷每個餐的範圍
  //  程式設計: loyenhsiang
  //  設計日期: 2024/2/8
  // **************************************************************************
  function get_range($quantity) {
    if ($quantity >= 1 && $quantity <= 10) {
        return "1~10";
    } elseif ($quantity >= 11 && $quantity <= 15) {
        return "11~15";
    } elseif ($quantity >= 16 && $quantity <= 20) {
        return "16~20";
    } elseif ($quantity >= 21 && $quantity <= 25) {
        return "21~25";
    } elseif ($quantity >= 26 && $quantity <= 30) {
        return "26~30";
    } elseif ($quantity >= 31 && $quantity <= 40) {
        return "31~40";
    } elseif ($quantity >= 41 && $quantity <= 50) {
        return "41~50";
    } else {
        // 如果超出上述範圍，返回原始數量
        return (string) $quantity;
    }
}

  // **************************************************************************
  //  函數名稱: cal_expense()
  //  函數功能: 計算每個送餐員該月每天的訂單與車馬費
  //  程式設計: loyenhsiang
  //  設計日期: 2024/2/6
  // **************************************************************************
  function cal_expense($num){
    $num = intval($num);
    if ($num == 0) {
      return 0;
    }
    elseif ($num >= 1 && $num <= 10) {
      return 125;
    }
    elseif ($num >= 11 && $num <= 15) {
      return 175;
    }
    elseif ($num >= 16 && $num <= 20) {
      return 225;
    }
    elseif ($num >= 21 && $num <= 25) {
      return 275;
    }
    elseif ($num >= 26 && $num <= 30) {
      return 325;
    }
    elseif ($num >= 31 && $num <= 40) {
      return 425;
    }
    elseif ($num >= 41 && $num <= 50) {
      return 525;
    }
    return;
  }

  // **************************************************************************
  //  函數名稱: cal_order_expense_1()
  //  函數功能: 計算每個送餐員該月每天的訂單與車馬費
  //  程式設計: loyenhsiang
  //  設計日期: 2024/1/31
  // **************************************************************************
  function cal_order_expense_1($orders, $rest_dates){
    $num_of_orders = array();
    $expenses = array();
    $dates = array();
    foreach ($orders as $date => $orders_by_delivery) {
      // var_dump($date);
      foreach ($orders_by_delivery as $delivery => $num) {
        // 檢查是否是請假日
        $is_rest_day = false;
        foreach ($rest_dates as $deliver => $rest_dates_by_delivery) {
          if (in_array($date, $rest_dates_by_delivery)) {
            $is_rest_day = true;
            break;
          }
        }
        if ($is_rest_day && $delivery == $deliver) {
          continue;
        }

        // var_dump($delivery);
        if (!isset($dates[$delivery])) {
          $dates[$delivery] = array();
        }
        $dates[$delivery][] = $date;

        if (!isset($num_of_orders[$delivery])) {
          $num_of_orders[$delivery] = 0;
        }
        $num_of_orders[$delivery] += $num;

        if (!isset($expenses[$delivery])) {
          $expenses[$delivery] = 0;
        }
        if ($num == 0) {
          $expenses[$delivery] += 0;
        }
        elseif ($num >= 1 && $num <= 10) {
          $expenses[$delivery] += 125;
        }
        elseif ($num >= 11 && $num <= 15) {
          $expenses[$delivery] += 175;
        }
        elseif ($num >= 16 && $num <= 20) {
          $expenses[$delivery] += 225;
        }
        elseif ($num >= 21 && $num <= 25) {
          $expenses[$delivery] += 275;
        }
        elseif ($num >= 26 && $num <= 30) {
          $expenses[$delivery] += 325;
        }
        elseif ($num >= 31 && $num <= 40) {
          $expenses[$delivery] += 425;
        }
        elseif ($num >= 41 && $num <= 50) {
          $expenses[$delivery] += 525;
        }
      }
    }
    return array($num_of_orders, $expenses, $dates);
  }

  function __destruct() {
    $url_str[] = 'be/rpt_car_fare/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // page foot
    }
  }
}