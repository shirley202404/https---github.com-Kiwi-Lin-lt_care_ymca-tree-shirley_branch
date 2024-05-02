<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Rpt_sab extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->library('zip');
    $this->load->model('route_model'); // 送餐資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('service_case_model'); // 服務資料
    $this->load->model('meal_order_model'); // 配送單資料
    $this->load->model('meal_service_fee_model'); // 餐食服務費補助設定
    $this->load->model('seal_model'); // 印章設定
    $this->load->model('rpt_sab_model'); // 繳費資料
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
    $this->tpl->assign('tv_return_link',be_url()."rpt_sab/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"弗傳慈心基金會");
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}  
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
    $this->tpl->assign('tv_title','社會局月報表');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_download_link',be_url().'rpt_sab/download/');
    $this->tpl->display("be/rpt_sab_input.html");
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
    $q = $this->input->post(); 
    $this->_generate_data($q['rpt_month'] , $q['rpt_type']);
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
    $time_start = date('Y-m-d H:i:s');
    $q = $this->input->post(); 
    list($rpt_year, $rpt_month) = explode("-", $q['rpt_month']);
    $rpt_year = $rpt_year - 1911;
    $lastday = date('d', strtotime("{$q['rpt_month']}-01 +1 month -1 day"));

    $sab_rpt1 = "{$rpt_year}{$rpt_month}_sab_rpt1.xlsx";
    $sab_rpt2 = "{$rpt_year}{$rpt_month}_sab_rpt2.xlsx";
    $sab_season = "{$rpt_year}{$rpt_month}_sab_season.xlsx";
    $sab_cnt = "{$rpt_year}{$rpt_month}_sab_statistics.xlsx";

    $rpt_sab_data = $this->_generate_data($q['rpt_month']);
    if(NULL != $rpt_sab_data) {
      // 獨老案 Begin //
      if(NULL != $rpt_sab_data["rpt_type1"]) {
        $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/rpt_sab_type1_sample.xlsx");
        foreach ($rpt_sab_data["rpt_type1"] as $k_sec => $v_sec) {
          if("sec04_1" == $k_sec) {
            $objSpreadsheet->setActiveSheetIndexByName('午餐');
          }
          else {
            $objSpreadsheet->setActiveSheetIndexByName('晚餐');
          }
          if(NULL != $v_sec) {
            $cnt = 1;
            $row = 2;
            foreach ($v_sec as $k_ct => $v_ct) {
              $ct06_str = '';
              $ct06_arr = NULL;
              $identity_sort_data[$v_ct['identity_sort']] = $v_sec;
              if("無" != $v_ct['ct06_telephone'] and '' != $v_ct['ct06_telephone']) {
                $ct06_arr[] = $v_ct['ct06_telephone'];
              }
              if("無" != $v_ct['ct06_homephone'] and '' != $v_ct['ct06_homephone']) {
                $ct06_arr[] = $v_ct['ct06_homephone'];
              }
              if(NULL != $ct06_arr) {
                $ct06_str = join("、", $ct06_arr);
              }

              list($year, $month, $date) = explode("-", date('Y-m-d',strtotime($v_ct['ct05'])));
              $ct05 = $year - 1911 . ".{$month}.{$date}";
              
              $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , $cnt);                             // 序號
              $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , "{$v_ct['ct04_str']}");            // 性別
              $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$v_ct['ct01']}{$v_ct['ct02']}"); // 案主姓名
              $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "{$v_ct['ct03']}");                // 身分證字號
              $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "{$ct05}");                        // 出生年月日
              $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "{$ct06_str}");                    // 電話
              $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "{$v_ct['ct14']}");                // 行政區
              $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "{$v_ct['ct15']}");                // 住址
              $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , @"{$v_ct['ct_il02_str']}");        // 身分別
              $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "{$v_ct['meal_date_str']}");       // 送餐日期
              $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}" , "{$v_ct['meal_cnt']}");            // 午餐天數
              $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , "{$v_ct['meal_price']}");          // 餐費
              $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , "=(K{$row} * L{$row})");           // 餐費小計
              $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , "{$v_ct['mp_date_str']}");         // 代餐日期
              $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , "{$v_ct['mp_cnt']}");              // 代餐天數
              $objSpreadsheet->getActiveSheet()->setCellValue("P{$row}" , "{$v_ct['mp_price']}");            // 代餐費
              $objSpreadsheet->getActiveSheet()->setCellValue("Q{$row}" , "=(O{$row} * P{$row})");           // 代餐小計
              $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "=(K{$row} + O{$row})");           // 吃餐數
              $objSpreadsheet->getActiveSheet()->setCellValue("S{$row}" , "=(M{$row} + Q{$row})");           // 總計金額
              $objSpreadsheet->getActiveSheet()->setCellValue("T{$row}" , "{$v_ct['sec09_str']}");           // 繳費方式
              $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(33);
              $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:U{$row}")->getFont()->setName('標楷體')->setSize(12);
              $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:U{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
              $row++;
              $cnt++;
            }
          } 
          $last_row = $row - 1;
          $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}" , "=SUM(K2:K{$last_row})"); // 午餐天數小計
          $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , "=SUM(M2:M{$last_row})"); // 餐費小計
          $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , "=SUM(O2:O{$last_row})"); // 代餐天數
          $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "=SUM(R2:R{$last_row})"); // 吃餐數
          $objSpreadsheet->getActiveSheet()->setCellValue("S{$row}" , "=SUM(S2:S{$last_row})"); // 總計金額
          $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(33);
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:U{$row}")->getFont()->setName('標楷體')->setSize(12);
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:U{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }
        $this->_save_to_server($sab_rpt1, $objSpreadsheet, 1);
      }
      // 獨老案 End //
      // 身障案 Begin //
      if(NULL != $rpt_sab_data["rpt_type2"]) { 
        $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
        $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/rpt_sab_type2_sample.xlsx");
        foreach ($rpt_sab_data["rpt_type2"] as $k_sec => $v_sec) {
          if("sec04_1" == $k_sec) {
            $objSpreadsheet->setActiveSheetIndexByName('午餐');
          }
          else {
            $objSpreadsheet->setActiveSheetIndexByName('晚餐');
          }
          if(NULL != $v_sec) {
            $cnt = 1;
            $row = 2;
            foreach ($v_sec as $k_ct => $v_ct) {
              $ct06_str = '';
              $ct06_arr = NULL;
              $identity_sort_data[$v_ct['identity_sort']] = $v_sec;
              if("無" != $v_ct['ct06_telephone'] and '' != $v_ct['ct06_telephone']) {
                $ct06_arr[] = $v_ct['ct06_telephone'];
              }
              if("無" != $v_ct['ct06_homephone'] and '' != $v_ct['ct06_homephone']) {
                $ct06_arr[] = $v_ct['ct06_homephone'];
              }
              if(NULL != $ct06_arr) {
                $ct06_str = join("、", $ct06_arr);
              }

              list($year, $month, $date) = explode("-", date('Y-m-d',strtotime($v_ct['ct05'])));
              $ct05 = $year - 1911 . ".{$month}.{$date}";
              
              $change_str = radio_value("ct35_level");
              $change_arr = explode(",", $v_ct["ct35_level"]);
              $ct35_level_str = change_str($change_arr, $change_str);

              $change_str = checkbox_value("ct35_type");
              $change_arr = explode(",", $v_ct["ct35_type"]);
              $ct35_type_str = change_str($change_arr, $change_str);

              $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , $cnt);                             // 序號
              $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , "{$v_ct['ct04_str']}");            // 性別
              $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$v_ct['ct01']}{$v_ct['ct02']}"); // 案主姓名
              $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "{$v_ct['ct03']}");                // 身分證字號
              $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , "{$ct05}");                        // 出生年月日
              $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "{$ct06_str}");                    // 電話
              $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "{$v_ct['ct14']}");               // 行政區
              $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "{$v_ct['ct15']}");                // 住址
              $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , @"{$v_ct['ct_il02_str']}");        // 身分別
              $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , "{$ct35_level_str}");              // 障度
              $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}" , "{$ct35_type_str}");               // 障別
              $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , "{$v_ct['meal_date_str']}");       // 送餐日期
              $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , "{$v_ct['meal_cnt']}");            // 午餐天數
              $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , "{$v_ct['meal_price']}");          // 餐費
              $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , "=(M{$row} * N{$row})");           // 餐費小計
              $objSpreadsheet->getActiveSheet()->setCellValue("P{$row}" , "{$v_ct['mp_date_str']}");         // 代餐日期
              $objSpreadsheet->getActiveSheet()->setCellValue("Q{$row}" , "{$v_ct['mp_cnt']}");              // 代餐天數
              $objSpreadsheet->getActiveSheet()->setCellValue("R{$row}" , "{$v_ct['mp_price']}");            // 代餐費
              $objSpreadsheet->getActiveSheet()->setCellValue("S{$row}" , "=(Q{$row} * R{$row})");           // 代餐小計
              $objSpreadsheet->getActiveSheet()->setCellValue("T{$row}" , "=(M{$row} + Q{$row})");           // 吃餐數
              $objSpreadsheet->getActiveSheet()->setCellValue("U{$row}" , "=(O{$row} + S{$row})");           // 總計金額
              $objSpreadsheet->getActiveSheet()->setCellValue("V{$row}" , "{$v_ct['sec09_str']}");           // 繳費方式
              $objSpreadsheet->getActiveSheet()->setCellValue("W{$row}" , "{$v_ct['ct05_str']}");            // 年齡別
              $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(21.6);
              $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:X{$row}")->getFont()->setName('標楷體')->setSize(12);
              $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:X{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
              $row++;
              $cnt++;
            }
          } 
          $last_row = $row - 1;
          $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , "=SUM(M2:M{$last_row})"); // 午餐天數小計
          $objSpreadsheet->getActiveSheet()->setCellValue("O{$row}" , "=SUM(O2:O{$last_row})"); // 餐費小計
          $objSpreadsheet->getActiveSheet()->setCellValue("Q{$row}" , "=SUM(Q2:Q{$last_row})"); // 代餐天數
          $objSpreadsheet->getActiveSheet()->setCellValue("S{$row}" , "=SUM(S2:S{$last_row})"); // 吃餐數
          $objSpreadsheet->getActiveSheet()->setCellValue("U{$row}" , "=SUM(U2:U{$last_row})"); // 總計金額
          $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(21.6);
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:X{$row}")->getFont()->setName('標楷體')->setSize(12);
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:X{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }
        $this->_save_to_server($sab_rpt2 , $objSpreadsheet , 2);
      }
      // 身障案 End //
    }

    // 社會局補助名冊 BEGIN //
    // $identity_sort_data[1] = NULL; // 身分別 => 低收
    // $identity_sort_data[2] = NULL; // 身分別 => 中低收
    // $identity_sort_data[3] = NULL; // 身分別 => 專案
    $identity_sort_data = NULL;
    if(NULL != $rpt_sab_data) {
      if(NULL != $rpt_sab_data["rpt_type1"]) { 
        foreach ($rpt_sab_data["rpt_type1"] as $k_sec => $v_sec) {
          if(NULL != $v_sec) {
            foreach ($v_sec as $k_ct => $v_ct) {
              $identity_sort_data["{$v_ct['identity_sort']}{$v_ct['meal_sort']}1"][] = $v_ct;
            }
          }
        }
      }
      if(NULL != $rpt_sab_data["rpt_type2"]) { 
        foreach ($rpt_sab_data["rpt_type2"] as $k_sec => $v_sec) {
          if(NULL != $v_sec) {
            foreach ($v_sec as $k_ct => $v_ct) {
              $identity_sort_data["{$v_ct['identity_sort']}{$v_ct['meal_sort']}2"][] = $v_ct;
            }
          }
        }
      }
      ksort($identity_sort_data);
    }

    if(NULL != $identity_sort_data) {
      $cnt = 1; 
      $row = 4;
      $ct_s_num_arr = array();
      $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/rpt_sab_rpt_all_sample.xlsx");
      $objSpreadsheet->getActiveSheet()->setTitle("{$rpt_year}{$rpt_month}");
      $objSpreadsheet->getActiveSheet()->setCellValue("A1" , "社會局補助辦理{$rpt_year}年中低收入獨居老人暨身心障礙者營養餐飲服務個案補助名冊");                                     
      $objSpreadsheet->getActiveSheet()->setCellValue("F2" , "請領期間：{$rpt_year}年{$rpt_month}月1日至{$rpt_year}年{$rpt_month}月{$lastday}日");                                    
      foreach($identity_sort_data as $k => $v) {
        if(NULL == $v) {
          continue;
        }
        array_multisort(array_column($v, 'meal_sort'), SORT_ASC, $v);
        foreach($v as $k_ct => $v_ct) {
          if(in_array($v_ct['ct_s_num'], $ct_s_num_arr)) {
            continue;
          }
          $ct_s_num_arr[] = $v_ct['ct_s_num'];
          list($year, $month, $date) = explode("-", date('Y-m-d',strtotime($v_ct['ct05'])));
          $ct05 = $year - 1911 . ".{$month}.{$date}";
          $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , "{$cnt}");                                     // 編號
          $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , "{$v_ct['ct01']}{$v_ct['ct02']}");             // 案主姓名
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$v_ct['ct03']}");                            // 身分證字號
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "{$ct05}");                                    // 出生年月日
          $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , @"{$v_ct['ct_il02_str']}");                    // 身分別
          $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "{$v_ct['meal_date_str']}{$v_ct['meal_str']}"); // 送餐日期
          $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "{$v_ct['meal_cnt']}");                        // 餐數
          $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "{$v_ct['meal_price']}");                      // 單價
          $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=(G{$row} * H{$row})");                       // 小計
          $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:I{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

          if("X" != $v_ct['mp_date_str']) { // 如果有待餐
            $row++;
            $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , "");                         // 編號
            $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , "{$v_ct['ct01']}{$v_ct['ct02']}");           // 案主姓名
            $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , "{$v_ct['ct03']}");                          // 身分證字號
            $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , "{$ct05}");                                  // 出生年月日
            $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , @"{$v_ct['ct_il02_str']}");                  // 身分別
            $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}" , "{$v_ct['mp_date_str']}(代)");               // 代餐送餐日期
            $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , "{$v_ct['mp_cnt']}");                        // 代餐餐數
            $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , "{$v_ct['mp_price']}");                      // 代餐單價
            $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=(G{$row} * H{$row})");                     // 小計
            $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:I{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
          }
          $row++;
          $cnt++;
        }
      }
      $last_row = $row - 1;
      $objSpreadsheet->getActiveSheet()->mergeCells("A{$row}:H{$row}");
      $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , "總計"); // 午餐天數小計
      $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , "=SUM(I2:I{$last_row})"); // 午餐天數小計
      $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:I{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
      $this->_save_to_server($sab_season , $objSpreadsheet , 3);
    }
    // 社會局補助名冊 END //

    // 社會局補助統計表 BEGIN //
    if(NULL != $rpt_sab_data) {
      $excel_cnt_data = $this->_generate_excel_cnt_data($rpt_sab_data);
      $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
      $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/rpt_sab_rpt_region_cnt_sample.xlsx");
      // 
      $objSpreadsheet->setActiveSheetIndex(0);
      $objSpreadsheet->getActiveSheet()->setCellValue("A1", "{$rpt_year}年中低收入獨居老人暨身心障礙營養餐飲服務月報表");
      $objSpreadsheet->getActiveSheet()->setCellValue("A2", "填報月份:{$rpt_year}年{$rpt_month}月");
      // 
      foreach($excel_cnt_data as $k => $worksheet) {
        $objSpreadsheet->setActiveSheetIndex($k);
        $objSpreadsheet->getActiveSheet()->setCellValue("A1", "{$rpt_year}年中低收入獨居老人暨身心障礙營養餐飲服務月報表");
        $objSpreadsheet->getActiveSheet()->setCellValue("A2", "填報月份:{$rpt_year}年{$rpt_month}月");
        foreach ($worksheet as $kw => $region) {
          foreach ($region as $kregion => $vregion) {
            foreach ($vregion['excel_row'] as $kexcel_row => $vexcel_row) {
              foreach ($vexcel_row as $row => $val) {
                $objSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($vregion['excel_col'], $row, $val);
              }
            }
          }
        }
      }
      $this->_save_to_server($sab_cnt, $objSpreadsheet, 4);
    }
    // 社會局補助統計表 END //

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff >= 60) {
      $time_diff = round($time_diff / 60, 1) . ' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff . ' 秒'; // 秒
    } 

    $this->zip->read_file(FCPATH."export_file/{$sab_rpt1}" , false);
    $this->zip->read_file(FCPATH."export_file/{$sab_rpt2}" , false);
    $this->zip->read_file(FCPATH."export_file/{$sab_season}" , false);
    $this->zip->read_file(FCPATH."export_file/{$sab_cnt}" , false);
    $this->zip->archive(FCPATH."export_file/sab_rpt_{$rpt_year}{$rpt_month}.zip"); 
    
    $ch_filename = "{$rpt_year}年{$rpt_month}月月報表.zip";
    $en_filename = "sab_rpt_{$rpt_year}{$rpt_month}.zip";
    
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename, $time_diff); 
    echo json_encode($rtn_msg);
    return;
  }  
  // **************************************************************************
  //  函數名稱: _generate_data
  //  函數功能: 產生繳費資料
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _generate_data($rpt_month) {
    list($rpt_year, $rpt_only_month) = explode("-", $rpt_month);
    // 先處理每天訂單類型 BEGIN//
    $last_date = NULL;
    $first_date = NULL;
    $date_type_arr = NULL;
    $date_arr = get_month_date($rpt_month);
    $date_cnt = count($date_arr);
    foreach ($date_arr as $k => $date) {
      $meal_order_type_row = $this->meal_order_model->get_meal_order_date_type($date);
      if(NULL != $meal_order_type_row) {
        $date_type_arr[$date] = $meal_order_type_row->mlo_dt02;
        if($k == 0) { // 每個月第一天
          $first_date = $date ;
        }
        if($k+1 == $date_cnt) { // 每個月最後一天
          $last_date = $date ;
        }
      }
    }
    // 先處理每天訂單類型 END//
    $rpt_sab_data["rpt_type1"]["sec04_1"] = NULL; // 2 = 特殊-老案, 8 = 獨老案，午餐
    $rpt_sab_data["rpt_type1"]["sec04_2"] = NULL; // 2 = 特殊-老案, 8 = 獨老案，午晚+晚餐
    $rpt_sab_data["rpt_type2"]["sec04_1"] = NULL; // 5 = 身障案, 6 = 特殊-身案，午餐
    $rpt_sab_data["rpt_type2"]["sec04_2"] = NULL; // 5 = 身障案, 6 = 特殊-身案，午晚+晚餐
    // 2 = 特殊-老案, 5 = 身障案, 6 = 特殊-身案, 8 = 獨老案
    foreach (array(2, 5, 6, 8) as $k => $sec01) {
      $clients_row = $this->service_case_model->get_sec_by_sec01($sec01, 1, $rpt_month); // sec05 = 1
      if(NULL != $clients_row) {
        foreach ($clients_row as $k_ct => $v_ct) {
          list($age_type_str, $age_type_code) = $this->_judge_age_type($v_ct['ct05']); // 判斷年齡別
          list($ct_il02_str, 
               $identity_sort, 
               $meal_sort,
               $meal_str, 
               $meal_price, 
               $mp_price) = $this->_judge_identity_and_price($v_ct['ct_s_num'], $last_date, $rpt_year); // 判斷收費價格
          $clients_row[$k_ct]['mp_cnt'] = 0;
          $clients_row[$k_ct]['mp_date_str'] = 'X';
          $clients_row[$k_ct]['mp_price'] = $mp_price;
          $clients_row[$k_ct]['meal_cnt'] = 0;
          $clients_row[$k_ct]['meal_str'] = $meal_str;
          $clients_row[$k_ct]['meal_date_str'] = 'X';
          $clients_row[$k_ct]['meal_price'] = $meal_price;
          $clients_row[$k_ct]['ct_il02_str'] = $ct_il02_str; 
          $clients_row[$k_ct]['identity_sort'] = $identity_sort; 
          $clients_row[$k_ct]['meal_sort'] = $meal_sort;
          $clients_row[$k_ct]['ct05_str'] = $age_type_str; // 判斷年齡別
          $clients_row[$k_ct]['ct05_code'] = $age_type_code; // 判斷年齡別
          $meal_order_row = $this->meal_order_model->get_data_by_subsidy($v_ct['s_num'] , $first_date , $last_date);
          if(NULL != $meal_order_row) {
            $mp_date_arr = NULL;
            $meal_date_arr = NULL;
            foreach ($meal_order_row as $ko => $vo) {
              if($date_type_arr[$vo['mlo01']] == 2) { // 如果為送餐-代餐，沒有代餐的就要去掉
                if($vo['mlo05'] != "Y") {
                  unset($meal_order_row[$ko]);
                  continue;
                }
                else {
                  $mp_date_arr[] = $vo['mlo01'];
                }
              }
              else {
                $meal_date_arr[] = $vo['mlo01'];
              }
            }
            if(NULL != $mp_date_arr) {
              list($clients_row[$k_ct]['mp_date_str'], $clients_row[$k_ct]['mp_cnt']) = $this->_date_str($mp_date_arr);
            }
            if(NULL != $meal_date_arr) {
              list($clients_row[$k_ct]['meal_date_str'], $clients_row[$k_ct]['meal_cnt']) = $this->_date_str($meal_date_arr);
            }
          }
          // 2023/05/24 如果未用餐要排除掉
          if('X' == $clients_row[$k_ct]['mp_date_str'] and 'X' == $clients_row[$k_ct]['meal_date_str']) {
            continue;
          }
          if(2 == $sec01 or 8 == $sec01) {
            $rpt_sab_data["rpt_type1"]["sec04_{$v_ct['sec04_type']}"][] = $clients_row[$k_ct];
          }
          if(5 == $sec01 or 6 == $sec01) {
            $rpt_sab_data["rpt_type2"]["sec04_{$v_ct['sec04_type']}"][] = $clients_row[$k_ct];
          }
        } 
      }
    }
    
    return $rpt_sab_data;
  }
  // **************************************************************************
  //  函數名稱: _generate_excel_cnt_data
  //  函數功能: 產生excel統計表資料
  //  程式設計: Kiwi
  //  設計日期: 2022/04/20
  // **************************************************************************
  function _generate_excel_cnt_data($rpt_sab_data) {
    $sab_cnt_arr = $this->_sab_cnt_arr();
    $region_arr = array(1 => array("東區", "北區", "北屯區", "西屯區"),           // 第一區 "東區", "北區", "北屯區", "西屯區"
                        2 => array("中區", "西區", "南區", "南屯區"),             // 第二區
                        3 => array("石岡區", "東勢區", "和平區", "新社區"),        // 山線第一區
                        4 => array("豐原區","后里區","潭子區","大雅區","神岡區"),  // 山線第二區
                        5 => array("大肚區","龍井區","沙鹿區","梧棲區"),           // 海線第一區
                        6 => array("大甲區","大安區","外埔區","清水區"),           // 海線第二區
                        7 => array("霧峰區","太平區","大里區"),                   // 屯區
                      );
    if(NULL != $rpt_sab_data) {
      $ct_s_num_arr = array();
      foreach ($rpt_sab_data as $k => $v) {
        foreach ($v as $k_sec => $v_sec) { // 報表種類。rpt_type => 1 = 獨老案，2 = 身障案
          foreach ($v_sec as $k_ct => $v_ct) {
            if(is_array($v_ct['ct_il02_str']) or is_null($v_ct['ct_il02_str'])) { // 如果身分別錯誤就直接跳過
              continue;
            }
            // 1. 先判斷是哪一區，才知道是第幾個工作表 //
            // 2. 知道區域後。可以抓出在工作表是第幾區得範圍 //
            // 3. 在判斷身分別 + 男女 => 哪一欄 //
            $index1 = NULL; // 第幾個工作表
            $index2 = NULL; // 第幾區
            $index3 = NULL; // 身分別+男女
            // 1. + 2. Begin //
            foreach ($region_arr as $fd_index => $region) {
              $key = array_search($v_ct['ct14'], $region);
              if($key === false) {
                continue;
              }
              $index1 = $fd_index;
              $index2 = $key;
            }
            // 1. + 2. End //
            // 3. Begin //
            // ct04_str 性別
            switch($v_ct['ct_il02_str']) {
              case "低收":
                if("男" == $v_ct['ct04_str']) {
                  $index3 = 0; // 男
                }
                else {
                  $index3 = 1; // 女
                }
                break;
              case "中低收":
                if("男" == $v_ct['ct04_str']) {
                  $index3 = 2; // 男
                }
                else {
                  $index3 = 3; // 女
                }
                break;
              case "專案":
                if("男" == $v_ct['ct04_str']) {
                  $index3 = 4; // 男
                }
                else {
                  $index3 = 5; // 女
                }
                break;
            }
            // 3. End // 
            if(is_null($index1) or is_null($index2) or is_null($index3)) { // 如果有index沒拿到就跳過
              continue;
            }
            if("rpt_type1" == $k) { // 獨老案
              switch ($k_sec) { // 餐別
                case "sec04_1": // 午餐
                  $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][24] += 1;
                  $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][27] += (int) $v_ct['meal_cnt'] + $v_ct['mp_cnt'];
                  break;
                case "sec04_2": // 晚餐
                  $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][25] += 1;
                  $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][28] += (int) $v_ct['meal_cnt'] + $v_ct['mp_cnt'];
                  break;
              }
            }
            if("rpt_type2" == $k) { // 身障案
              if(!in_array($v_ct['ct_s_num'], $ct_s_num_arr)) {
                switch ($v_ct['ct35_level']) { // 身障等級
                  case 4: // 極重度
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][6] += 1;
                    break;
                  case 3: // 重度
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][7] += 1;
                    break;        
                  case 2: // 中度
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][8] += 1;
                    break;  
                  case 1: // 輕度
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][9] += 1;
                    break;
                }             
                switch ($v_ct['ct05_code']) { // 年齡
                  case 1830: // 18~30歲
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][16] += 1;
                    break;
                  case 3045: // 30~45歲
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][17] += 1;
                    break;        
                  case 4565: // 45~65歲
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][18] += 1;
                    break;  
                  case 65: // 65以上
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][19] += 1;
                    switch ($k_sec) { // 餐別
                      case "sec04_1": // 午餐
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][24] += 1;
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][27] += (int) $v_ct['meal_cnt'] + $v_ct['mp_cnt'];
                        break;
                      case "sec04_2": // 晚餐
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][25] += 1;
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][28] += (int) $v_ct['meal_cnt'] + $v_ct['mp_cnt'];
                        break;
                    }
                    break;
                }
              }
              else {
                switch ($v_ct['ct05_code']) { // 年齡
                  case 1830: // 18~30歲
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][16] += 1;
                    break;
                  case 3045: // 30~45歲
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][17] += 1;
                    break;        
                  case 4565: // 45~65歲
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][18] += 1;
                    break;  
                  case 65: // 65以上
                    $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][19] += 1;
                    switch ($k_sec) { // 餐別
                      case "sec04_1": // 午餐
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][24] += 1;
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][27] += (int) $v_ct['meal_cnt'] + $v_ct['mp_cnt'];
                        break;
                      case "sec04_2": // 晚餐
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][25] += 1;
                        $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type1'][28] += (int) $v_ct['meal_cnt'] + $v_ct['mp_cnt'];
                        break;
                    }
                    break;
                }
              }
              switch ($k_sec) { // 餐別
                case "sec04_1": // 午餐
                  $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][21] += 1;
                  break;
                case "sec04_2": // 晚餐
                  $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][22] += 1;
                  break;
              }
              $ct_s_num_arr[] = $v_ct['ct_s_num'];
              $sab_cnt_arr[$index1][$index2][$index3]['excel_row']['rpt_type2'][23] += (int) $v_ct['meal_cnt'] + $v_ct['mp_cnt'];
            }
          }
        }
      }
    }
    // u_var_dump($sab_cnt_arr);
    return $sab_cnt_arr;
  }

  // **************************************************************************
  //  函數名稱: _judge_identity_and_price
  //  函數功能: 身分別、餐點價格、代餐價格判斷
  //  程式設計: Kiwi
  //  設計日期: 2022/03/21
  // **************************************************************************
  function _judge_identity_and_price($ct_s_num, $last_date, $rpt_year) {
    $mp_price = 0; // 代餐
    $meal_price = 0; // 餐點
    $mil_str = ''; // 午晚分、午晚併、午
    $ct_il02_str = '';
    $meal_sort = ''; // 餐別排序
    $identity_sort = ''; // 身分別排序
    $identity_row_1 = $this->meal_service_fee_model->get_by_year($rpt_year, 1);
    $identity_row_2 = $this->meal_service_fee_model->get_by_year($rpt_year, 2);

    // 身分別 Begin //
    $latest_identity_row = $this->clients_model->get_identity_log_latest($ct_s_num, $last_date);
    if(NULL != $latest_identity_row) {
      $ct_il02_str = @radio_value("ct34_go_sab", $latest_identity_row->ct_il02);
    }
    // 身分別 End //
    
    // 餐點價格、代餐價格 Begin //
    $sec_ct_row = $this->service_case_model->get_all_by_ct_s_num($ct_s_num, $last_date);
    if(NULL != $sec_ct_row) {
      $sec04 = array_column($sec_ct_row, 'sec04');
      switch($ct_il02_str) {
        case "專案":
        case "中低收":
          if(in_array(1, $sec04) and in_array(2, $sec04)) { // 如果午餐 & 中晚餐一起送
            $mil_str = "(午晚併)";
            $meal_sort = 3;
            $mp_price = @$identity_row_2->msf13_mp;
            $meal_price = @$identity_row_2->msf13_meal; // $meal_price = 168;
          }
          else if (in_array(1, $sec04) and in_array(3, $sec04)) { // 如果分成午餐 & 晚餐
            $mil_str = "(午晚分)";
            $meal_sort = 2;
            $mp_price = @$identity_row_2->msf12_mp;
            $meal_price = @$identity_row_2->msf12_meal; // $meal_price = 184;
          }
          else if (in_array(1, $sec04)) { // 只有午餐
            $mil_str = "(午)";
            $meal_sort = 1;
            $mp_price = @$identity_row_2->msf11_mp;
            $meal_price = @$identity_row_2->msf11_meal; // $meal_price = 92;
          }
          else if (in_array(3, $sec04)) { // 只有午餐
            $mil_str = "(晚)";
            $meal_sort = 1;
            $mp_price = @$identity_row_2->msf11_mp;
            $meal_price = @$identity_row_2->msf11_meal; // $meal_price = 100;
          }
          break;
        case "低收":
          if(in_array(1, $sec04) and in_array(2, $sec04)) { // 如果午餐 & 中晚餐一起送
            $mil_str = "(午晚併)";
            $meal_sort = 3;
            $mp_price = @$identity_row_1->msf13_mp;
            $meal_price = @$identity_row_1->msf13_meal; // $meal_price = 168;
          }
          else if (in_array(1, $sec04) and in_array(3, $sec04)) { // 如果分成午餐 & 晚餐
            $mil_str = "(午晚分)";
            $meal_sort = 2;
            $mp_price = @$identity_row_1->msf12_mp;
            $meal_price = @$identity_row_1->msf12_meal; // $meal_price = 184;
          }
          else if (in_array(1, $sec04)) { // 只有午餐
            $mil_str = "(午)";
            $meal_sort = 1;
            $mp_price = @$identity_row_1->msf11_mp;
            $meal_price = @$identity_row_1->msf11_meal; // $meal_price = 100;
          }
          else if (in_array(3, $sec04)) { // 只有午餐
            $mil_str = "(晚)";
            $meal_sort = 1;
            $mp_price = @$identity_row_1->msf11_mp;
            $meal_price = @$identity_row_1->msf11_meal; // $meal_price = 100;
          }
          break;
      }

      switch($ct_il02_str) {
        case "低收":
          $identity_sort = 1;
          break;
        case "中低收":
          $identity_sort = 2;
          break;
        case "專案":
          $identity_sort = 3;
          break;
      }
    }
    // 餐點價格、代餐價格 End //
    return array($ct_il02_str, 
                 $identity_sort, 
                 $meal_sort,
                 $mil_str, 
                 $meal_price, 
                 $mp_price); 
  } 
  // **************************************************************************
  //  函數名稱: _judge_age_type
  //  函數功能: 年齡別判斷
  //  程式設計: Kiwi
  //  設計日期: 2022/03/21
  // **************************************************************************
  function _judge_age_type($birth) {
    $age_type_str = '';  
    $age_type_code = 0;  
    if('' == $birth) {
      return $age_type_str;
    }
    list($year, $month, $date) = explode('-', $birth); 
    $cm = date('n'); 
    $cd = date('j'); 
    $age = date('Y') - $year -1; 
    if ($cm > $month or $cm == $month && $cd > $date) {
      $age++;
    }
    if(18 <= $age && $age < 30) { // 18~未滿30歲
      $age_type_str = '18~未滿30歲';
      $age_type_code = 1830;
    }
    else if(30 <= $age && $age < 45) { // 30~未滿45歲
      $age_type_str = '30~未滿45歲';
      $age_type_code = 3045;
    }
    else if(45 <= $age && $age < 65) { // 45~未滿65歲
      $age_type_str = '45~未滿65歲';
      $age_type_code = 4565;
    }
    else if(65 <= $age) { // 65歲以上
      $age_type_str = '65歲以上';
      $age_type_code = 65;
    }
    return array($age_type_str, $age_type_code); 
  }
  // **************************************************************************
  //  函數名稱: _date_str
  //  函數功能: 日期字串格式處理
  //  程式設計: Kiwi
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
    $date_str = join(",", $date_str);
    return array($date_str, $date_cnt);
  }
  // **************************************************************************
  //  函數名稱: sab_cnt_arr
  //  函數功能: 產生社會局統計EXCEL陣列格式
  //  程式設計: Kiwi
  //  設計日期: 2022/04/18
  // **************************************************************************
  public function _sab_cnt_arr() {
    // 區域
    $region_arr = array(1 => array("東區", "北區", "北屯區", "西屯區"),     // 第一區 "東區", "北區", "北屯區", "西屯區"
                        2 => array("中區", "西區", "南區", "南屯區"),       // 第二區
                        3 => array("石岡區", "東勢區", "和平區", "新社區"), // 山線第一區
                        4 => array("豐原區","后里區","潭子區","大雅區","神岡區"),    // 山線第二區
                        5 => array("大肚區","龍井區","沙鹿區","梧棲區"),    // 海線第一區
                        6 => array("大甲區","大安區","外埔區","清水區"),    // 海線第二區
                        7 => array("霧峰區","太平區","大里區"),             // 屯區
                       );
    // // 身分別
    // $identity_col = array(0 => "M1", // 低收-男
    //                       1 => "F1", // 低收-女
    //                       2 => "M2", // 中低收-男
    //                       3 => "F2", // 中低收-女
    //                       4 => "M3", // 專案-男
    //                       5 => "F3"  // 專案-女
    //                      );

    // // 身障等級
    // $excel_col = array(0 => array("begin_col" => "M", "end_col" => "R"), // 表格第一個區域
    //                    1 => array("begin_col" => "T", "end_col" => "Y"), // 表格第二個區域
    //                    2 => array("begin_col" => "AA", "end_col" => "AF"), // 表格第三個區域
    //                    3 => array("begin_col" => "AH", "end_col" => "AM"), // 表格第四個區域
    //                    4 => array("begin_col" => "AO", "end_col" => "AT"), // 表格第四個區域
    //                   );

    // EXCEL 欄位區域範圍, 相對應到的是身分別陣列的1~6種身分
    $excel_col = array(0 => array("begin_col" => 13, "end_col" => 18), // 表格第一個區域
                       1 => array("begin_col" => 20, "end_col" => 25), // 表格第二個區域
                       2 => array("begin_col" => 27, "end_col" => 32), // 表格第三個區域
                       3 => array("begin_col" => 34, "end_col" => 39), // 表格第四個區域
                       4 => array("begin_col" => 41, "end_col" => 46), // 表格第四個區域
                      );
    
    // 身障等級
    // 二維陣列的數值 = excel的每列
    $excel_row = array("rpt_type2" => array(6 => "0", // 身障等級-極重度
                                            7 => "0", // 身障等級-重度
                                            8 => "0", // 身障等級-中度
                                            9 => "0", // 身障等級-小計
                                            11 => "0", // 年齡別-0~未滿3歲
                                            12 => "0", // 年齡別-3~未滿6歲
                                            13 => "0", // 年齡別-6~未滿12歲
                                            14 => "0", // 年齡別-12~未滿15歲
                                            15 => "0", // 年齡別-15~未滿18歲
                                            16 => "0", // 年齡別-18~未滿30歲
                                            17 => "0", // 年齡別-30~未滿45歲
                                            18 => "0", // 年齡別-45~未滿65歲
                                            19 => "0", // 年齡別-65歲以上
                                            21 => "0", // 供餐-午餐
                                            22 => "0", // 供餐-晚餐
                                            23 => "0"  // 供餐-服務人次
                                          ),
                        "rpt_type1" => array(24 => "0", // 供餐情形-午餐
                                             25 => "0", // 供餐情形-晚餐
                                             27 => "0", // 餐食數量(午)
                                             28 => "0"// 餐食數量(晚)
                                            )
                        );
    
    $excel_arr = NULL;
    foreach($region_arr as $k => $excel_tab) {
      foreach ($excel_tab as $kw => $region) {
        $begin_col = $excel_col[$kw]['begin_col'];
        $end_col = $excel_col[$kw]['end_col'];
        foreach (range($begin_col, $end_col) as $k_col => $col) {
          $excel_arr[$k][$kw][$k_col]['region'] = $region;
          $excel_arr[$k][$kw][$k_col]['excel_col'] = $col;
          $excel_arr[$k][$kw][$k_col]['excel_row'] = $excel_row;
        }
      }
    }
    return $excel_arr;
  }
  // **************************************************************************
  //  函數名稱: _save_to_server
  //  函數功能: 存到server
  //  程式設計: Kiwi
  //  設計日期: 2021/10/20
  // **************************************************************************
  public function _save_to_server($filename , $objSpreadsheet , $cnt) {
    if($cnt > 3) {
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

  

  function __destruct() {
    $url_str[] = 'be/rpt_sab/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // page foot
    }
  }
}
