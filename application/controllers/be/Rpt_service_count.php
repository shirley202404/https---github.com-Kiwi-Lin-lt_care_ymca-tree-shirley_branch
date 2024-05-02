<?php

use function Complex\ln;

defined('BASEPATH') OR exit('No direct script access allowed');

class Rpt_service_count extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('route_model'); // 路線資料
    $this->load->model('service_case_model'); // 開結案服務資料
    $this->load->model('meal_instruction_log_h_model'); // 餐時異動資料
    $this->load->model('meal_order_date_type_model'); // 日期類型資料
    $this->load->model('region_setting_model'); // 區域設定檔
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
    $this->tpl->assign('tv_return_link',be_url()."rpt_service_count/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"社團法人南投縣基督教青年會");
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    // if('tony' != $_SESSION['acc_user']) {
    //   die('趕工中...');
    // }
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
    $this->tpl->assign('tv_title','服務人次人數');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_download_link',be_url().'rpt_service_count/download/');
    $this->tpl->display("be/rpt_service_count_input.html");
    return;
  }
  
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載execl檔案
  //  程式設計: Kiwi loyenhsiang(處理資料)
  //  設計日期: 2020/9/2
  // **************************************************************************
  public function download() {
    $rtn_msg = '';
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');
    $rpt_download_month = str_replace("-", "_", $this->input->post('download_year'));
    $v=$this->input->post();
    $tw_year = $v["download_year"];

    // 底稿
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/service_count_sample.xlsx"); // 載入底稿
    
    // 整理資料 Begin //
    switch($v["download_type"]) {
      case '':
        $not_work = 1;
        break;
      case "1":  // 長照案
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 3);
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content_gender($objSpreadsheet, $v["download_year"], intval($v["download_type"]));
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 1);
        if ($not_work == 1) {
          break;
        }
        break;
      case "2":  // 朝清案
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 3);
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content_gender($objSpreadsheet, $v["download_year"], intval($v["download_type"]));
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 1);
        if ($not_work == 1) {
          break;
        }
        break;
      case "3":  // 公所案
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 3);
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content_gender($objSpreadsheet, $v["download_year"], intval($v["download_type"]));
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 1);
        if ($not_work == 1) {
          break;
        }
        break;
      case "4":  // 自費案
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 3);
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content_gender($objSpreadsheet, $v["download_year"], intval($v["download_type"]));
        if ($not_work == 1) {
          break;
        }
        list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], intval($v["download_type"]), 1);
        if ($not_work == 1) {
          break;
        }
        break;
      case "5":  // 全部
        for ($i = 1;$i <= 4;$i++) {
          $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/service_count_sample.xlsx");
          list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], $i, 3);
          if ($not_work == 1) {
            break;
          }
          list($objSpreadsheet, $not_work) = $this->_row_content_gender($objSpreadsheet, $v["download_year"], $i);;
          if ($not_work == 1) {
            break;
          }
          list($objSpreadsheet, $not_work) = $this->_row_content($objSpreadsheet, $v["download_year"], $i, 1);
          if ($not_work == 1) {
            break;
          }
          if ($i == 1) {
            $name = "長照案";
          }
          else if ($i == 2) {
            $name = "朝清案";
          }
          else if ($i == 3) {
            $name = "公所案";
          }
          else if ($i == 4) {
            $name = "自費案";
          }
          $en_filename = "{$tw_year}_service_count_case_{$i}.xlsx";
          $ch_filename = "{$tw_year} {$name}服務人次人數.xlsx";
          header("Content-type: text/html; charset=utf-8");
          header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
          header("Content-Disposition: attachment;filename=" . $en_filename);
          $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
          $objWriter->save(FCPATH."export_file/{$en_filename}"); // 儲存到server
          $objSpreadsheet->disconnectWorksheets(); // 清除資料
        }
        $files = $this->download_zip($v['download_year']);
        $zip_en_filename = $files["en"];
        $zip_ch_filename = $files["ch"];
        $time_end = date('Y-m-d H:i:s');
        $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
        if($time_diff >= 60) {
          $time_diff = round($time_diff/60,1).' 分'; // 分鐘
        }
        else {
          $time_diff = $time_diff.' 秒'; // 秒
        }
        
        $rtn_msg = $this->zi_my_func->download_str($zip_ch_filename, $zip_en_filename, $time_diff); 
        echo $rtn_msg;
        return;
    }
    // 整理資料 END //
    if ($not_work == 1) {
      $rtn_msg = "查無資料";
      echo $rtn_msg;
      return;
    }
    $en_filename = "{$tw_year}_service_count.xlsx";
    $ch_filename = "{$tw_year} 服務人次人數.xlsx";

    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=" . $en_filename);
    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
    $objWriter->save(FCPATH."export_file/{$en_filename}"); // 儲存到server
    $objSpreadsheet->disconnectWorksheets(); // 清除資料

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
  //  函數名稱: download_zip()
  //  函數功能: 下載excel至zip
  //  程式設計: loyenhsiang
  //  設計日期: 2023/12/14
  // **************************************************************************
  public function download_zip($tw_year) {
    $zip_en_filename = "{$tw_year}_service_count.zip";
    $zip_ch_filename = "{$tw_year} 服務人次人數.zip";
    $zip_file_path = FCPATH . "export_file/{$zip_en_filename}";

    $this->load->library('zip');
    // $this->zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    for ($i = 1; $i <= 4; $i++) {
      if ($i == 1) {
        $name = "長照案";
      }
      else if ($i == 2) {
        $name = "朝清案";
      }
      else if ($i == 3) {
        $name = "公所案";
      }
      else if ($i == 4) {
        $name = "自費案";
      }

      $en_filename = "{$tw_year}_service_count_case_{$i}.xlsx";
      $ch_filename = "{$tw_year}_{$name}服務人次人數.xlsx";
      $excelFilePath = FCPATH . "export_file/{$en_filename}";

      // 檢查檔案是否存在
      if (file_exists($excelFilePath)) {
        // 讀取檔案內容
        $fileContent = file_get_contents($excelFilePath);
        
        // 在 Zip 檔案中新增檔案
        $this->zip->add_data($en_filename, $fileContent);
        // $this->zip->add_data($ch_filename, $fileContent);
      }
    }
    $this->zip->archive($zip_file_path);

    ob_end_clean();
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=$zip_en_filename");
    header("Content-Length: " . filesize($zip_file_path));
    header("Pragma: no-cache");
    header("Expires: 0");
    $files = array(
      "en" => $zip_en_filename,
      "ch" => $zip_ch_filename
    );
    return $files;
  }
  
  // **************************************************************************
  //  函數名稱: _row_content()
  //  函數功能: 不同案件不同時段每列的內容
  //  程式設計: loyenhsiang
  //  設計日期: 2023/12/7
  // **************************************************************************
  public function _row_content($objSpreadsheet, $year, $case, $type) {
    $not_work = 0;
    $ROCyear = intval($year) - 1911;

    if ($type == 1) {
      $page = 0;
    }
    else if ($type == 3) {
      $page = 1;
    }

    $name = '';
    $a2_content = '';
    switch($case)  {
      case 1:
        $name = "長照案";
        $a2_content = "{$ROCyear}年度南投縣政府長期照顧服務-營養餐飲服務";
        break;
      case 2:
        $name = "朝清案";
        $a2_content = "{$ROCyear}112年度捐獻愛心送餐服務";
        break;
      case 3:
        $name = "公所案";
        $a2_content = "{$ROCyear}112年度老人暨身心障礙者營養餐飲服務";
        break;
      case 4:
        $name = "自費案";
        $a2_content = "{$ROCyear}112年度營養餐飲服務-自費案";
        break;
    }

    if ($type == 1) {
      $meal = "午餐";
    }
    else if ($type == 3) {
      $meal = "晚餐";
    }

    // 設置
    $sheetName = "{$ROCyear}【{$name}】【{$meal}】人數人次統計表";
    $objSpreadsheet->setActiveSheetIndex($page);
    $objSpreadsheet->getActiveSheet()->setTitle($sheetName);
    $objSpreadsheet->getActiveSheet()->setCellValue("A2", "{$a2_content}，以下以表格列明各區域各月份提供服務之情形。");

    // 表格服務項目
    $column = 3;
    $column_start = $column;
    $reh_s_num_Array = array();

    $all_routes = $this->route_model->get_all('s_num');
    foreach($all_routes as $route) {
      $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', $column)."4", $route['reh01']);
      $reh_s_num_Array[] = $route['s_num'];
      $column += 1;
    }

    // 合併C~K(如果增加路線可能會變動)
    $objSpreadsheet->getActiveSheet()->mergeCells(hlp_opt_setup('rpt_sc01', $column_start)."3:".hlp_opt_setup('rpt_sc01', $column-1)."3");
    $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', $column_start)."3", "午餐送餐");
    // 合併L~M(如果增加路線可能會變動)
    $objSpreadsheet->getActiveSheet()->mergeCells(hlp_opt_setup('rpt_sc01', $column)."3:".hlp_opt_setup('rpt_sc01', $column+1)."3");
    $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', $column)."3", "總計");
    $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', $column)."4", "小計");
    $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', $column+1)."4", "累計服務");

    // 置中對齊
    $objSpreadsheet->getActiveSheet()->getStyle(hlp_opt_setup('rpt_sc01', $column_start)."3".":".hlp_opt_setup('rpt_sc01', $column)."3")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    for ($month = 1; $month <= 12; $month++) {
      // var_dump('month:', $month);
      // 設定年份和月份
      $year_month = date('Y-m', strtotime(strval($year) . '-' . strval($month)));
      $order = $this->meal_order_model->get_order_by_month($year_month, $case, $type);  // 該月各個路線的訂單
      $stop = $this->meal_instruction_log_h_model->get_stop_by_date_sec04($year_month, $case, $type);  // 暫停人數
      $end = $this->service_case_model->get_sec03($year_month, $case, $type);  // 結案人數
      $columnNoneIndex = [0, 1, 2, 3, 4, 5, 6, 7, 8];  // 未設定的欄位(補0用)
      foreach($order as $row) {
        $columnIndex = array_search($row['reh_s_num'], $reh_s_num_Array);
        if (($key = array_search($columnIndex, $columnNoneIndex)) !== false) {
          unset($columnNoneIndex[$key]);
        }
        if ($columnIndex !== false && $row['clients_count'] !== NULL) {
          $objSpreadsheet->getActiveSheet()->setCellValue([$columnIndex + 3, 4 * $month + 1], $row['clients_count']);  // 人數
          $objSpreadsheet->getActiveSheet()->setCellValue([$columnIndex + 3, 4 * $month + 2], $row['total_orders']);  // 人次
        }
      }
      foreach($columnNoneIndex as $index){ // 剩餘位置補0
        $objSpreadsheet->getActiveSheet()->setCellValue([$index + 3, 4 * $month + 1], 0);
        $objSpreadsheet->getActiveSheet()->setCellValue([$index + 3, 4 * $month + 2], 0);
      }
      $columnNoneIndex = [0, 1, 2, 3, 4, 5, 6, 7, 8];
      if (is_array($stop)) { // 暫停人數
        foreach($stop as $row) {
          $columnIndex = array_search($row['reh_s_num'], $reh_s_num_Array);
          if (($key = array_search($columnIndex, $columnNoneIndex)) !== false) {
            unset($columnNoneIndex[$key]);
          }
          if ($columnIndex !== false && $row['clients_count'] !== NULL) {
            $objSpreadsheet->getActiveSheet()->setCellValue([$columnIndex + 3, 4 * $month + 3], $row['clients_count']);  
          }
        }
      }
      foreach($columnNoneIndex as $index){ // 剩餘位置補0
        $objSpreadsheet->getActiveSheet()->setCellValue([$index + 3, 4 * $month + 3], 0);
      }
      $columnNoneIndex = [0, 1, 2, 3, 4, 5, 6, 7, 8];
      if (is_array($end)) { // 結案人數
        foreach($end as $row) {
          if (isset($row['reh_s_num'])){
            $columnIndex = array_search($row['reh_s_num'], $reh_s_num_Array);
            if (($key = array_search($columnIndex, $columnNoneIndex)) !== false) {
              unset($columnNoneIndex[$key]);
            }
            if ($columnIndex !== false && $row['clients_count'] !== NULL) {
              $objSpreadsheet->getActiveSheet()->setCellValue([$columnIndex + 3, 4 * $month + 4], $row['clients_count']);  
            }
          }
        }
      }
      foreach($columnNoneIndex as $index){ // 剩餘位置補0
        $objSpreadsheet->getActiveSheet()->setCellValue([$index + 3, 4 * $month + 4], 0);
      }
      // 小計
      $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start).strval(4 * $month + 1), "=SUM(" . hlp_opt_setup('rpt_sc01', $column_start) . strval(4 * $month + 1) . ":" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start - 1) . strval(4 * $month + 1) . ")");
      $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start).strval(4 * $month + 2), "=SUM(" . hlp_opt_setup('rpt_sc01', $column_start) . strval(4 * $month + 2) . ":" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start - 1) . strval(4 * $month + 2) . ")");
      $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start).strval(4 * $month + 3), "=SUM(" . hlp_opt_setup('rpt_sc01', $column_start) . strval(4 * $month + 3) . ":" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start - 1) . strval(4 * $month + 3) . ")");
      $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start).strval(4 * $month + 4), "=SUM(" . hlp_opt_setup('rpt_sc01', $column_start) . strval(4 * $month + 4) . ":" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start - 1) . strval(4 * $month + 4) . ")");
      
      // 累計服務
      if ($month == 1) {
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start + 1) . strval(4 * $month + 1), 0);
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start + 1) . strval(4 * $month + 2), 0);
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start + 1) . strval(4 * $month + 3), 0);
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array) + $column_start + 1) . strval(4 * $month + 4), 0);
      }
      if ($month == 2) {
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 1), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 1) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * ($month-1) + 1) . ")");
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 2), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 2) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * ($month-1) + 2) . ")");
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 3), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 3) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * ($month-1) + 3) . ")");
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 4), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 4) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * ($month-1) + 4) . ")");
      }
      else if ($month > 2) {
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 1), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 1) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1) . strval(4 * ($month-1) + 1) . ")");
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 2), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 2) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1) . strval(4 * ($month-1) + 2) . ")");
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 3), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 3) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1) . strval(4 * ($month-1) + 3) . ")");
        $objSpreadsheet->getActiveSheet()->setCellValue(hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1).strval(4 * $month + 4), "=(" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start) . strval(4 * $month + 4) . "+" . hlp_opt_setup('rpt_sc01', count($reh_s_num_Array)+$column_start+1) . strval(4 * ($month-1) + 4) . ")");
      }
    }
    return array($objSpreadsheet, $not_work);
  }
  // **************************************************************************
  //  函數名稱: _row_content_gender()
  //  函數功能: 不同案件的男女人數人次內容
  //  程式設計: loyenhsiang
  //  設計日期: 2023/12/7
  // **************************************************************************
  public function _row_content_gender($objSpreadsheet, $year, $case) {
    $objSpreadsheet->setActiveSheetIndex(2);  // 第三個工作表
    $not_work = 0;
    $ROCyear = intval($year)-1911;
    if ($case == 1) {
      $name = "長照案";
    }
    else if ($case == 2) {
      $name = "朝清案";
    }
    else if ($case == 3) {
      $name = "公所案";
    }
    else if ($case == 4) {
      $name = "自費案";
    }
    // 設置
    $sheetName = "{$ROCyear}【{$name}】性別人數人次統計表";
    $objSpreadsheet->getActiveSheet()->setTitle($sheetName);
    $cell = $objSpreadsheet->getActiveSheet()->getCell('A1');
    $new_value = str_replace('111', $ROCyear, $cell->getValue());
    $cell->setValue($new_value);

    // 表格服務項目
    $column = 3;
    $column_start = $column;

    for ($month = 1; $month <= 12; $month++) {
      // 設定年份和月份
      $year_month = date('Y-m', strtotime(strval($year) . '-' . strval($month)));
      $order_male = $this->meal_order_model->get_order_by_month($year_month, $case, NULL, 'M');  // 該月男生的訂單
      $order_female = $this->meal_order_model->get_order_by_month($year_month, $case, NULL, 'Y');  // 該月女生的訂單

      $totalClientsCount = 0;
      $totalTotalOrders = 0;
      foreach($order_male as $row) {
        if ($row['clients_count'] !== NULL && $row['total_orders'] !== NULL) {
          $totalClientsCount += $row['clients_count']; // 將每月的 clients_count 加到總和上
          $totalTotalOrders += $row['total_orders']; // 將每月的 total_orders 加到總和上
        }
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("B".strval($month+2), $totalClientsCount);
      $objSpreadsheet->getActiveSheet()->setCellValue("D".strval($month+2), $totalTotalOrders);

      $totalClientsCount = 0;
      $totalTotalOrders = 0;
      foreach($order_female as $row) {
        if ($row['clients_count'] !== NULL && $row['total_orders'] !== NULL) {
          $totalClientsCount += $row['clients_count']; // 將每月的 clients_count 加到總和上
          $totalTotalOrders += $row['total_orders']; // 將每月的 total_orders 加到總和上
        }
      }
      $objSpreadsheet->getActiveSheet()->setCellValue("C".strval($month+2), $totalClientsCount);
      $objSpreadsheet->getActiveSheet()->setCellValue("E".strval($month+2), $totalTotalOrders);
    }
    return array($objSpreadsheet, $not_work);
  }
  function __destruct() {
    $url_str[] = '';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // ajax foot
    }
  }
}
