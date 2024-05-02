<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rpt_sab_annual_assessment extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('service_case_model'); // 開結案服務
    $this->load->model('home_interview_model'); // 家訪紀錄
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."rpt_sab_annual_assessment/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
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
    $msel = 'que';
    // $cust_row = $this->cust_model->get_all_is_available();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 搜尋
    $this->tpl->assign('tv_msel',$msel);
    // $this->tpl->assign('tv_cust_row',$cust_row);
    $this->tpl->assign('tv_title','社會局年度評估總表');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_download_link',be_url().'rpt_sab_annual_assessment/download/');
    $this->tpl->assign('tv_search_link',be_url().'rpt_sab_annual_assessment/search/');
    $this->tpl->display("be/rpt_sab_annual_assessment_input.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載execl檔案
  //  程式設計: Yuna
  //  設計日期: 2022/5/7
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');

    $ct_s_num_arr = array();
    $rpt_year = $_POST['rpt_year'];
    $change_str = radio_value("ct34_go");
    $tw_year = $rpt_year - 1911;
    foreach (array(2, 5, 6, 8) as $k => $sec01) {
      $service_case_row = $this->service_case_model->get_unclosed_sec_by_sec01($sec01, 1); // sec05 = 1
      if(!empty($service_case_row)) {
        foreach ($service_case_row as $k_ct => $v_ct) {
          if(!in_array($v_ct['ct_s_num'], $ct_s_num_arr)) {
            $ct_s_num_arr[] = $v_ct['ct_s_num'];
          }
        }
      }
    }

    $xlsx_data = NULL;
    $region_arr = array('豐原區',
                        '神岡區',
                        '大雅區',
                        '潭子區',
                        '后里區',
                        '沙鹿區',
                        '梧棲區',
                        '大肚區',
                        '龍井區'
                       );

    if(!empty($ct_s_num_arr)) {
      foreach ($ct_s_num_arr as $k => $ct_s_num) {
        $client_row = $this->clients_model->get_one($ct_s_num);
        if(empty($client_row)) {
          continue;
        }

        $region_key = array_search($client_row->ct14, $region_arr);
        if($region_key === false) {
          continue;
        }

        $service_case_row = $this->service_case_model->get_que_by_ct($ct_s_num);
        $client_identity_row = $this->clients_model->get_identity_log_latest($ct_s_num, "{$rpt_year}-12-31");
        $home_interview_row = $this->home_interview_model->get_all_by_ct_s_num($ct_s_num, 1);

        $change_arr = @explode(",", $client_identity_row->ct_il02);
        $ct_il02_str = @change_str($change_arr, $change_str);
        
        $home_content = '';
        if(!empty($home_interview_row)) {
          $home_content = "一、家庭評估：".@array_column($home_interview_row, 'hew10_1_str')[0].PHP_EOL.@array_column($home_interview_row, 'hew10_1_memo')[0].PHP_EOL.
                          "二、生理狀況：".@array_column($home_interview_row, 'hew10_2_str')[0].PHP_EOL.@array_column($home_interview_row, 'hew10_2_memo')[0].PHP_EOL.
                          "三、心理評估：".@array_column($home_interview_row, 'hew10_3_str')[0].PHP_EOL.@array_column($home_interview_row, 'hew10_3_memo')[0].PHP_EOL.
                          "四、社會評估：".@array_column($home_interview_row, 'hew10_4_str')[0].PHP_EOL.@array_column($home_interview_row, 'hew10_4_memo')[0].PHP_EOL.
                          "五、環境評估：".@array_column($home_interview_row, 'hew10_6_str')[0].PHP_EOL.@array_column($home_interview_row, 'hew10_6_memo')[0].PHP_EOL;
        }

        $birthday = '';
        if(!empty($client_row->ct05) and '0000-00-00 00:00:00' != $client_row->ct05) {
          list($ct_year, $ct_month, $ct_date) = explode("-", $client_row->ct05);
          $ct_tw_year = $ct_year - 1911;
          $birthday = str_pad($ct_tw_year, 3, 0, STR_PAD_LEFT)."{$ct_month}{$ct_date}";
          $birthday = str_replace(" 00:00:00", "", $birthday);
        }

        $xlsx_data[$region_key][$ct_s_num]['ct_name'] = "{$client_row->ct01}{$client_row->ct02}";
        $xlsx_data[$region_key][$ct_s_num]['ct03'] = $client_row->ct03;
        $xlsx_data[$region_key][$ct_s_num]['ct05'] = $birthday;
        $xlsx_data[$region_key][$ct_s_num]['ct14'] = $client_row->ct14;
        $xlsx_data[$region_key][$ct_s_num]['ct31'] = $client_row->ct31_str;
        $xlsx_data[$region_key][$ct_s_num]['ct35_type'] = $client_row->ct35_type_str;
        $xlsx_data[$region_key][$ct_s_num]['ct35_level'] = $client_row->ct35_level_str;
        $xlsx_data[$region_key][$ct_s_num]['ct_identity'] = $ct_il02_str;
        $xlsx_data[$region_key][$ct_s_num]['sec_begin_date'] = @array_unique(array_column($service_case_row, 'sec02'))[0];
        $xlsx_data[$region_key][$ct_s_num]['hew01'] = @array_unique(array_column($home_interview_row, 'hew01'))[0];
        $xlsx_data[$region_key][$ct_s_num]['hew_content'] = $home_content;
      }
    }
    
    // EXCEL下載 Begin //
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/rpt_sab_annual_assessment_sample.xlsx");
    if(NULL != $xlsx_data) {
      foreach ($xlsx_data as $region_key => $region_data) {
        $ct_num = 1;
        $row = 4;
        $objSpreadsheet->setActiveSheetIndex($region_key);
        if(empty($region_data)) {
          continue;
        }
        $client_cnt = count($region_data);
        if($client_cnt > 1) {
          for($i = 0; $i < $client_cnt-1; $i++) { 
            $objSpreadsheet->getActiveSheet()->insertNewRowBefore(5);
          }
        }
        foreach ($region_data as $k => $v) {
          $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}" , $ct_num);
          $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}" , $v['ct_name']);
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}" , $v['ct03']);
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}" , $v['ct05']);
          $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}" , $v['ct14']);
          $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}" , $v['ct_identity']);
          $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}" , $v['ct35_type']);
          $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}" , $v['ct35_level']);
          $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}" , $v['ct31']);
          $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}" , $v['sec_begin_date']);
          $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}" , $v['hew01']);
          $objSpreadsheet->getActiveSheet()->setCellValue("N{$row}" , $v['hew_content']);
          $objSpreadsheet->getActiveSheet()->getStyle("N{$row}")->getAlignment()->setWrapText(true);
          $ct_num++;
          $row++;
        }
      }
    }
    $objSpreadsheet->setActiveSheetIndex(0);
    // EXCEL下載 End //

    $ch_filename = "社會局{$tw_year}年度評估總表.xlsx";
    $en_filename = "{$tw_year}-rpt_sab_annual_assessment.xlsx";
    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename={$en_filename}");
    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
    $objWriter->save(FCPATH."export_file/{$en_filename}"); // 儲存到server

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end) - strtotime($time_start); // 分鐘
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

  function __destruct() {
    $url_str[] = 'be/rpt_sab_annual_assessment/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // ???? foot
    }
  }
}
