<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpt_service extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('route_model'); // 路線資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('region_setting_model'); // 區域設定
    $this->load->model('daily_shipment_model'); // 配送單資料
    $this->load->model('delivery_person_model'); // 外送員資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->load->helper('zi_option'); // 載入helper
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."rpt_service/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"社團法人南投縣基督教青年會");
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
    $region_setting_row = $this->region_setting_model->get_all_is_available();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','清冊');
    $this->tpl->assign('tv_region_setting_row',$region_setting_row);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_download_link',be_url().'rpt_service/download/');
    $this->tpl->display("be/rpt_service_input.html");
    return;
  }
  
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載execl檔案
  //  程式設計: loyenhsiang(處理資料)
  //  設計日期: 2020/9/2
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');

    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');
    $rpt_download_month = str_replace("-", "_", $this->input->post('rpt_download_month'));
    $v=$this->input->post();

    // 開始處理資料 Begin //
    switch($v["download_district"]) {
      case '':
        $not_work = 1;
        break;
      case "1":  // 長照案
        $sample_file = FCPATH."pub/sample/112老人印領清冊(長照)_sample.xlsx";
        $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
        $result = $this->_row_content_1($objSpreadsheet, $v["rpt_download_month"]);
        $objSpreadsheet = $result['objSpreadsheet'];
        $not_work = $result['not_work'];
        break;
      case "2":  // 朝清、公所案
        $sample_file = FCPATH."pub/sample/112老人印領清冊(公所、朝清)_sample.xlsx";
        $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
        $result = $this->_row_content_2($objSpreadsheet, $v["rpt_download_month"]);
        $objSpreadsheet = $result['objSpreadsheet'];
        $not_work = $result['not_work'];
        break;
    }

    // 開始處理資料 end //

    if ($not_work == 1){
      $rtn_msg = "查無資料";
      echo $rtn_msg;
      return;
    }
    $ch_filename = "{$rpt_download_month} 印領清冊.xlsx";
    $en_filename = "{$_POST['rpt_download_month']}_rpt_service.xlsx";

    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=" . $en_filename);
    
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objSpreadsheet , 'Xlsx');
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
  //  函數名稱: _row_content_1()
  //  函數功能: 長照案每列的內容
  //  程式設計: loyenhsiang
  //  設計日期: 2023/11/21
  // **************************************************************************
  public function _row_content_1($objSpreadsheet, $year_month) {
    // 粗體樣式
    $boldStyle = [
      'font' => [
        'bold' => true,
      ],
    ];

    // 紅色樣式
    $redStyle = [
      'font' => [
        'color' => ['rgb' => 'FF0000'],
      ],
    ];

    $objSpreadsheet->setActiveSheetIndex(0);  // 第一個工作表
    $not_work = 0;
    $all_clients = $this->clients_model->get_all_is_available_by_ct34_go(date('Y-m-t', strtotime($year_month)));
    $year = date('Y', strtotime($year_month));
    $month = date('m', strtotime($year_month));
    $price = hlp_get_subsidy_price($type='meal', $year - 1911);

    // 設置
    $sheetName = "{$year}{$month}午晚餐";
    $objSpreadsheet->getActiveSheet()->setTitle($sheetName);
    $cell = $objSpreadsheet->getActiveSheet()->getCell('A1');
    $new_value = str_replace('112', $year, $cell->getValue());
    $new_value = str_replace('十', hlp_opt_setup('rpt_se01', $month), $cell->getValue());
    $cell->setValue($new_value);

    // 定義一個比較函數，用於按照 ct03 列的值進行排序
    function sortByCt03($a, $b) {
      return strcmp($a['ct03'], $b['ct03']);
    }
    // 使用 usort 函數對 $all_clients 數組按照 ct03 列的值進行排序
    usort($all_clients, 'sortByCt03');
    
    $row = 3; // 從第三行開始寫入數據
    foreach ($all_clients as $client) {
      if ($client['ct_il02'] == 1){
        continue;
      }
      $time = $this->meal_order_model->how_many_times_by_month($year_month, 1, $client['s_num']);  // 長照案1
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $row-2);  // 序號
      $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $client['ct03']);  // 身分證字號
      $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $client['ct01'] . $client['ct02']);  // 姓名
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $client['ct05']);  // 出身年月日
      $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $client['ct04']);  // 性別
      $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", hlp_opt_setup("ct34_go", $client['ct_il02']));  // 類別
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $client['ct13'].$client['ct14'].$client['ct15']);  // 住址
      // 將 F 列和 G 列的對齊方式設置為自動換行
      $objSpreadsheet->getActiveSheet()->getStyle("F{$row}:G{$row}")->getAlignment()->setWrapText(true);
      if ($client['ct06_telephone'] == NULL){
        $phone_number = $client['ct06_homephone'];
      } else {
        $phone_number = $client['ct06_telephone'];
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", $phone_number);  // 電話
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $price);  // 給(支)付價格
      if (isset($time[0]['delivery_count']) && $time[0]['delivery_count'] != NULL) {
        $frequency = $time[0]['delivery_count'];  // 次數
      }
      else {
        $frequency = 0;
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", $frequency);  // 次數
      $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", '=IF(OR("'.$client['ct_il02'].'"="4", "'.$client['ct_il02'].'"="5"), "0%", IF(OR("'.$client['ct_il02'].'"="1", "'.$client['ct_il02'].'"="3", "'.$client['ct_il02'].'"="7"), "10%", ""))');  // 部分負擔率
      $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}", '=IF(K'.$row.' = "0%", 0, IF(K'.$row.' = "10%", I'.$row.'*J'.$row.'*0.1, ""))');  // 部分負擔費用
      $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}", "=I{$row}*J{$row}-L{$row}");  // 申請費用
      $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}", "");  // 蓋章
      // 可以根據實際情況添加更多列
      // 置中對齊
      $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:N{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      // 統一行高
      $objSpreadsheet->getActiveSheet()->getRowDimension($row)->setRowHeight(150);
      $row++;
    }
    // 合併A~H
    $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:H{$row}");
    $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", "合計");
    $last_row = $row - 1;
    $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", "=SUM(J3:J{$last_row})");
    $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}", "=SUM(M3:M{$last_row})");
    $objSpreadsheet->getActiveSheet()->getStyle("M{$row}")->applyFromArray($redStyle);
    // 置中對齊
    $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:N{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // 添加框線
    $objSpreadsheet->getActiveSheet()->getStyle("A3:N{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    // 粗體字
    $objSpreadsheet->getActiveSheet()->getStyle("A1:A{$row}")->applyFromArray($boldStyle);
    $objSpreadsheet->getActiveSheet()->getStyle("M1:M{$row}")->applyFromArray($boldStyle);
    // 在 C2、D2、E2 上添加篩選
    $objSpreadsheet->getActiveSheet()->setAutoFilter('C2:E2');
    // 設定欄寬
    $columns = range('A', 'N');
    foreach ($columns as $column) {
      // 將像素轉換為點
      $column_width = hlp_opt_setup('rpt_se02',$column);
      $point_width = $column_width * 0.12;
      $objSpreadsheet->getActiveSheet()->getColumnDimension($column)->setAutoSize(false);
      $objSpreadsheet->getActiveSheet()->getColumnDimension($column)->setWidth($point_width);
    }
    $objSpreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
    // 設定列印範圍
    $objSpreadsheet->getActiveSheet()->getPageSetup()->setPrintArea("A1:N{$row}");
    for ($i = 14; $i <= $row; $i = $i + 12){
      $objSpreadsheet->getActiveSheet()->setBreak("A{$i}", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
    }
    $result = array(
      'objSpreadsheet' => $objSpreadsheet,
      'not_work' => $not_work
    );
    return $result;
  }
  // **************************************************************************
  //  函數名稱: _row_content_2()
  //  函數功能: 朝清、公所案每列的內容
  //  程式設計: loyenhsiang
  //  設計日期: 2023/11/21
  // **************************************************************************
  public function _row_content_2($objSpreadsheet, $year_month) {
    // 粗體樣式
    $boldStyle = [
      'font' => [
          'bold' => true,
      ],
    ];
    $objSpreadsheet->setActiveSheetIndex(0);  // 第一個工作表
    $not_work = 0;
    $all_clients = $this->clients_model->get_all_is_available_by_ct34_go(date('Y-m-t', strtotime($year_month)));
    $year = date('Y', strtotime($year_month));
    $month = date('m', strtotime($year_month));
    $price = hlp_get_subsidy_price($type='meal', $year - 1911);

    // 設置Title
    $sheetName = "{$year}{$month}月公所";
    $objSpreadsheet->getActiveSheet()->setTitle($sheetName);
    $cell = $objSpreadsheet->getActiveSheet()->getCell('A1');
    $new_value = str_replace('112', $year, $cell->getValue());
    $new_value = str_replace('十', hlp_opt_setup('rpt_se01', $month), $cell->getValue());
    $cell->setValue($new_value);

    // 定義一個比較函數，用於按照 ct03 列的值進行排序
    function sortByCt03($a, $b) {
      return strcmp($a['ct03'], $b['ct03']);
    }
    // 使用 usort 函數對 $all_clients 數組按照 ct03 列的值進行排序
    usort($all_clients, 'sortByCt03');

    $row = 3; // 從第三行開始寫入數據
    foreach ($all_clients as $client) {
      if ($client['ct_il02'] == 1){
        continue;
      }
      $time = $this->meal_order_model->how_many_times_by_month($year_month, 2, $client['s_num']);  // 公所案2
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $row-2);  // 序號
      $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $client['ct01'] . $client['ct02']);  // 姓名
      $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $client['ct05']);  // 出生年月日
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $client['ct04']);  // 性別
      $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $client['ct03']);  // 身分證字號
      $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $client['ct13'].$client['ct14'].$client['ct15']);  // 住址
      if ($client['ct06_telephone'] == NULL){
        $phone_number = $client['ct06_homephone'];
      } else {
        $phone_number = $client['ct06_telephone'];
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $phone_number);  // 電話
      if (isset($time[0]['delivery_count']) && $time[0]['delivery_count'] != NULL) {
        $frequency = $time[0]['delivery_count'];  // 次數
      }
      else {
        $frequency = 0;
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", $frequency);  // 次數
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $price);  // 單價
      $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", "=H{$row}*I{$row}");  // 金額
      $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", "");  // 蓋章
      // 可以根據實際情況添加更多列
      // 置中對齊
      $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      // 統一行高
      $objSpreadsheet->getActiveSheet()->getRowDimension($row)->setRowHeight(31.5);
      $row++;
    }
    // 合併A~H
    $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:G{$row}");
    $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", "合計");
    $last_row = $row - 1;
    $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", "=SUM(H3:H{$last_row})");
    $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", "=SUM(J3:J{$last_row})");
    // 置中對齊
    $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // 添加框線
    $objSpreadsheet->getActiveSheet()->getStyle("A3:K{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    // 粗體字
    $objSpreadsheet->getActiveSheet()->getStyle("A1:A{$row}")->applyFromArray($boldStyle);
    $objSpreadsheet->getActiveSheet()->getStyle("H1:H{$row}")->applyFromArray($boldStyle);
    $objSpreadsheet->getActiveSheet()->getStyle("J{$row}")->applyFromArray($boldStyle);
    // 在 A2~K2 上添加篩選
    $objSpreadsheet->getActiveSheet()->setAutoFilter('A2:K2');
    // 設定欄寬
    $columns = range('A', 'K');
    foreach ($columns as $column) {
      // 將像素轉換為點
      $column_width = hlp_opt_setup('rpt_se04',$column);
      $point_width = $column_width * 0.12;
      $objSpreadsheet->getActiveSheet()->getColumnDimension($column)->setAutoSize(false);
      $objSpreadsheet->getActiveSheet()->getColumnDimension($column)->setWidth($point_width);
    }
    // 設定行高
    $objSpreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
    // 設定列印範圍
    $objSpreadsheet->getActiveSheet()->getPageSetup()->setPrintArea("A1:K{$row}");
    for ($i = 14; $i <= $row; $i = $i + 12){
      $objSpreadsheet->getActiveSheet()->setBreak("A{$i}", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
    }

    $objSpreadsheet->setActiveSheetIndex(1);  // 第二個工作表
    // 設置Title
    $sheetName = "{$year}{$month}月朝清宮";
    $objSpreadsheet->getActiveSheet()->setTitle($sheetName);
    $cell = $objSpreadsheet->getActiveSheet()->getCell('A1');
    $new_value = str_replace('112', $year, $cell->getValue());
    $new_value = str_replace('九', hlp_opt_setup('rpt_se01', $month), $cell->getValue());
    $cell->setValue($new_value);

    $row = 3; // 從第三行開始寫入數據
    foreach ($all_clients as $client) {
      if ($client['ct_il02'] == 1){
        continue;
      }
      $time = $this->meal_order_model->how_many_times_by_month($year_month, 3, $client['s_num']);  // 朝清案3
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $row-2);  // 序號
      $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $client['ct01'] . $client['ct02']);  // 姓名
      $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $client['ct05']);  // 出生年月日
      $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $client['ct04']);  // 性別
      $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $client['ct03']);  // 身分證字號
      $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $client['ct13'].$client['ct14'].$client['ct15']);  // 住址
      if ($client['ct06_telephone'] == NULL){
        $phone_number = $client['ct06_homephone'];
      } else {
        $phone_number = $client['ct06_telephone'];
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $phone_number);  // 電話
      // 將 F 列和 G 列的對齊方式設置為自動換行
      $objSpreadsheet->getActiveSheet()->getStyle("F{$row}:G{$row}")->getAlignment()->setWrapText(true);
      if (isset($time[0]['delivery_count']) && $time[0]['delivery_count'] != NULL) {
        $frequency = $time[0]['delivery_count'];  // 次數
      }
      else {
        $frequency = 0;
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", $frequency);  // 次數
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $price);  // 單價
      $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", "=H{$row}*I{$row}");  // 金額
      $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", "");  // 蓋章
      // 可以根據實際情況添加更多列
      // 置中對齊
      $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
      // 統一行高
      $objSpreadsheet->getActiveSheet()->getRowDimension($row)->setRowHeight(31.5);
      $row++;
    }
    // 合併A~G
    $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:G{$row}");
    $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", "合計");
    $last_row = $row - 1;
    $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", "=SUM(H3:H{$last_row})");
    $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", "=SUM(J3:J{$last_row})");
    // 置中對齊
    $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    // 添加框線
    $objSpreadsheet->getActiveSheet()->getStyle("A3:K{$row}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    // 粗體字
    $objSpreadsheet->getActiveSheet()->getStyle("A1:A{$row}")->applyFromArray($boldStyle);
    $objSpreadsheet->getActiveSheet()->getStyle("H1:J{$row}")->applyFromArray($boldStyle);
    // 在 A2~K2 上添加篩選
    $objSpreadsheet->getActiveSheet()->setAutoFilter('A2:K2');
    // 設定欄寬
    $columns = range('A', 'K');
    foreach ($columns as $column) {
      // 將像素轉換為點
      $column_width = hlp_opt_setup('rpt_se03',$column);
      $point_width = $column_width * 0.12;
      $objSpreadsheet->getActiveSheet()->getColumnDimension($column)->setAutoSize(false);
      $objSpreadsheet->getActiveSheet()->getColumnDimension($column)->setWidth($point_width);
    }
    $objSpreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
    // 設定列印範圍
    $objSpreadsheet->getActiveSheet()->getPageSetup()->setPrintArea("A1:K{$row}");
    for ($i = 14; $i <= $row; $i = $i + 12){
      $objSpreadsheet->getActiveSheet()->setBreak("A{$i}", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
    }
    $result = array(
      'objSpreadsheet' => $objSpreadsheet,
      'not_work' => $not_work
    );
    return $result;
  }
  
  function __destruct() {
    $url_str[] = '';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // ???? foot
    }
  }
}