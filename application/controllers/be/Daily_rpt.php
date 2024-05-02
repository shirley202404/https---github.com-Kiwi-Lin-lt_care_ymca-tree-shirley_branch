<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Daily_rpt extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('meal_model'); // 餐點資料
    $this->load->model('route_model'); // 送餐資料
    $this->load->model('daily_work_model'); // 配送單資料
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('daily_production_order_model'); // 餐條順序設定資料
    $this->load->model('meal_instruction_log_h_model'); // 餐條異動資料
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
    $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."daily_rpt/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"YMCA基督教青年會");
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}    
    return;
  }
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 輸入畫面
  //  程式設計: Tony
  //  設計日期: 2020/4/13
  // **************************************************************************
  public function index() {
    $msel = 'list';
    $route_row = $this->route_model->get_all();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','YMCA基督教青年會');
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_group_s_num',$_SESSION['group_s_num']);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_prn_link',be_url().'daily_rpt/prn/');
    $this->tpl->display("be/daily_rpt_input.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: prn
  //  函數功能: 列印資料
  //  程式設計: Kiwi
  //  設計日期: 2020/12/08
  // **************************************************************************
  public function prn() {
    $msel = 'prn';
    $v = $this->input->post();
    $menu_title = null;
    $download_link = null;
    $type       = $v['type'];
    $rpt_type   = $v['rpt'];
    $reh_s_num  = $v['reh_s_num'];
    $dlvry_date = $v['dlvry_date'];

    switch($rpt_type) {
      case "1":
       $menu_title = "配送單(外)"; // 下午等於晚上
       $prn_send_data = $this->daily_work_model->get_send_data(); // 配送單資料  
       $this->tpl->assign('tv_save_link', be_url().'daily_rpt/save/punch'); // 更新成功!!
       $this->tpl->assign('tv_prn_send_data', $prn_send_data);
      break;
      case "2":
        $menu_title = "餐點生產單";
        $download_link = be_url()."daily_rpt/download/?download_type={$type}&download_date={$dlvry_date}&reh_s_num={$reh_s_num}&type={$type}";
        $prn_meal_data = $this->daily_work_model->get_meal_data($dlvry_date, $type, $reh_s_num);
        $this->tpl->assign('tv_prn_meal_data', $prn_meal_data);
        break;
      case "3":
        $menu_title = "餐點統計表";
        $download_link = be_url()."daily_rpt/download_stats/?download_date={$dlvry_date}";
        $prn_stats_data = $this->_generate_stats_data($dlvry_date);
        $this->tpl->assign('tv_prn_stats_data', $prn_stats_data);
        break;
    }
        
    $this->tpl->assign('tv_breadcrumb3', $this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_menu_title', $menu_title);
    $this->tpl->assign('tv_msel', $msel);
    $this->tpl->assign('tv_post_data', $v);
    $this->tpl->assign('tv_rpt_type', $rpt_type);
    $this->tpl->assign('tv_dlvry_date', $dlvry_date);
    $this->tpl->assign('tv_prn_date', date('Y-m-d H:i:s'));
    $this->tpl->assign('tv_prn_emp', $_SESSION['acc_name']);
    $this->tpl->assign('tv_save_ok', $this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_download_link', $download_link);
    $this->tpl->assign('tv_prn_link', be_url().'daily_rpt/prn/');
    $this->tpl->assign('tv_exit_link', be_url().'daily_rpt/');
    $this->tpl->display("be/daily_rpt_prn_{$rpt_type}.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: download_stats() 
  //  函數功能: 處理餐點數量統計表資訊
  //  程式設計: Kiwi
  //  設計日期: 2023/11/06
  // **************************************************************************
  public function download_stats() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $time_start = date('Y-m-d H:i:s');

    $download_date = $this->input->get('download_date');
    $stats_data = $this->_generate_stats_data($download_date);

    list($year, $month, $date) = explode("-", $download_date);
    $tw_year = $year - 1911;

    $weekday = date('w', strtotime($download_date));
    $week_list = array('日', '一', '二', '三', '四', '五', '六');

    $sample_file = FCPATH."pub/sample/statics_sample.xlsx";
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
    $objSpreadsheet->setActiveSheetIndex(0);
    $objSpreadsheet->getActiveSheet()->setCellValue("A6", "{$tw_year}/{$month}/{$date}({$week_list[$weekday]}) 餐食統計表"); // 路線\餐食種類

    if(!empty($stats_data)) {
      $row = 10; 
      $col_begin = "A";
      $col_end = "M";
      foreach ($stats_data as $stats_key => $data) {
        if(1 == $stats_key) { // 晚餐不用產報表
          continue;
        }
        foreach ($data['statics'] as $k => $v) {
          $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $v['col_1']); // 路線\餐食種類
          $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v['col_2']);  // 一般  
          $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $v['col_3_normal']);  // 飯-一般
          $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $v['col_3_veg']);  // 飯-素食
          $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $v['col_4_normal']);  // 白粥-一般
          $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $v['col_4_veg']);  // 白粥-素食     
          $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $v['col_6_L_total']);  // 飯糰總數-大     
          $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", $v['col_6_M_total']);  // 飯糰總數-中
          $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $v['col_6_S_total']);  // 飯糰總數-小
          $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", $v['col_7_R']);  // 菜盒-正常-飯 
          $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", $v['col_7_P']);  // 菜盒-正常-粥
          $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}", $v['col_8_R']);  // 菜盒-碎食-飯     
          $objSpreadsheet->getActiveSheet()->setCellValue("M{$row}", $v['col_8_P']);  // 菜盒-碎食-粥
          $objSpreadsheet = $this->_set_stats_row_style($objSpreadsheet, $col_begin, $col_end, $row);
          $row++; 
        }
        $objSpreadsheet->getActiveSheet()->mergeCells("E{$row}:F{$row}");
        $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", "總數"); // 總數
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", "=SUM(E10:F{$row})"); // 白粥
        $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", "=SUM(G10:G{$row})"); // 飯糰總數-大
        $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", "=SUM(H10:H{$row})"); // 飯糰總數-中
        $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", "=SUM(I10:I{$row})"); // 飯糰總數-小
        $objSpreadsheet = $this->_set_stats_row_style($objSpreadsheet, $col_begin, $col_end, $row);
      }
    }

    $ch_filename = "{$tw_year}{$month}{$date}餐食統計表.xlsx";
    $en_filename = "statics_data_".date('Y-m-d H-i-s').".xlsx";
    
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
  //  函數名稱: _generate_stats_data() 
  //  函數功能: 處理餐點數量統計表資訊
  //  程式設計: Kiwi
  //  設計日期: 2023/11/06
  // **************************************************************************
  public function _generate_stats_data($dlvry_date) {
    $route_row = $this->route_model->get_all($order="dp07_start");
    $stats_meal_row = $this->daily_work_model->get_meal_data($dlvry_date);

    $stats_data = NULL;
    if(!empty($route_row)) {
      foreach ($route_row as $k => $v) {
        $stats_data[$v['reh05']]['info']['reh05_str'] = $v['reh05_str']; // 路線
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_1'] = $v['reh01']; // 路線
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_2'] = 0; // 一般餐
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_3_normal'] = 0; // 飯-一般
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_3_veg'] = 0; // 飯-素食
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_4_normal'] = 0; // 白粥-一般
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_4_veg'] = 0; // 白粥-素食
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_5'] = 0; // 素食
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_L_total'] = 0; // 飯糰數量-大-總數
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_L_R'] = 0; // 飯糰數量-大-飯
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_L_P'] = 0; // 飯糰數量-大-粥
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_L_V'] = 0; // 飯糰數量-大-素食
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_M_total'] = 0; // 飯糰數量-中-總數
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_M_R'] = 0; // 飯糰數量-中-飯
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_M_P'] = 0; // 飯糰數量-中-粥
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_M_V'] = 0; // 飯糰數量-中-素食
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_S_total'] = 0; // 飯糰數量-小-總數
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_S_R'] = 0; // 飯糰數量-小-飯
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_S_P'] = 0; // 飯糰數量-小-粥
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_6_S_V'] = 0; // 飯糰數量-小-素食
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_7_total'] = 0; // 菜盒正常-總數
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_7_R'] = 0; // 菜盒正常-飯
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_7_P'] = 0; // 菜盒正常-粥
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_8_total'] = 0; // 菜盒碎食-總數
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_8_R'] = 0; // 菜盒碎食-飯
        $stats_data[$v['reh05']]['statics'][$v['s_num']]['col_8_P'] = 0; // 菜盒碎食-粥
      }
    }
      // 飯要要分一般、素食
      // 白粥要分一般、素食
      // 素食紀錄有幾個素食餐盒
      // 飯糰要分一般、素食、粥
      // 菜盒要分一般、粥
    if(!empty($stats_meal_row)) {
      foreach ($stats_meal_row as $k => $v) {
        if('一般餐' == $v['dyp03']) {
          $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_2'] += 1;
        }
        if(strpos($v['dyp03'], "飯") !== false) {
          if(strpos($v['dyp03'], "素") === false) {
            $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_3_normal'] += 1;
            if('普通' == $v['dyp04_3']) {
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_7_total'] += 1;
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_7_R'] += 1;
            }
            if('碎食' == $v['dyp04_3']) {
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_8_total'] += 1;
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_8_R'] += 1;
            }
          }
          else {
            $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_3_veg'] += 1;
          }
        }
        if(strpos($v['dyp03'], "粥") !== false) {
          if(strpos($v['dyp03'], "素") === false) {
            $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_4_normal'] += 1;
            if('普通' == $v['dyp04_3']) {
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_7_total'] += 1;
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_7_P'] += 1;
            }
            if('碎食' == $v['dyp04_3']) {
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_8_total'] += 1;
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_8_P'] += 1;
            }
          }
          else {
            $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_4_veg'] += 1;
          }
        }
        if(strpos($v['dyp03'], "素") !== false) {
          $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_5'] += 1;
        }
        switch($v['dyp04_1']) {
          case "大":
            $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_L_total'] += 1; // 飯糰數量-大
            if(strpos($v['dyp03'], "素") !== false) {
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_L_V'] += 1;
            }
            else {
              if(strpos($v['dyp03'], "飯") !== false) {
                $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_L_R'] += 1;
              }
              if(strpos($v['dyp03'], "粥") !== false) {
                $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_L_P'] += 1;
              }
            }
            break;
          case "中":
            $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_M_total'] += 1; // 飯糰數量-中
            if(strpos($v['dyp03'], "素") !== false) {
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_M_V'] += 1;
            }
            else {
              if(strpos($v['dyp03'], "飯") !== false) {
                $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_M_R'] += 1;
              }
              if(strpos($v['dyp03'], "粥") !== false) {
                $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_M_P'] += 1;
              }
            }
            break;
          case "小":
            $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_S_total'] += 1; // 飯糰數量-小
            if(strpos($v['dyp03'], "素") !== false) {
              $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_S_V'] += 1;
            }
            else {
              if(strpos($v['dyp03'], "飯") !== false) {
                $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_S_R'] += 1;
              }
              if(strpos($v['dyp03'], "粥") !== false) {
                $stats_data[$v['dyp09']]['statics'][$v['reh_s_num']]['col_6_S_P'] += 1;
              }
            }
            break;
        }
      }
    }

    if(!empty($stats_data)) {
      sort($stats_data);
    }

    return $stats_data;
  }
  // **************************************************************************
  //  函數名稱: _set_stats_row_style() 
  //  函數功能: 設定統計資料表每列的樣式
  //  程式設計: Kiwi
  //  設計日期: 2023/11/7
  // **************************************************************************
  public function _set_stats_row_style($objSpreadsheet, $col_begin, $col_end, $row) {
    $objSpreadsheet->getActiveSheet()->getRowDimension("{$row}")->setRowHeight(21.6);
    $objSpreadsheet->getActiveSheet()->getStyle("{$col_begin}{$row}:{$col_end}{$row}")->getFont()->setName('微軟正黑體')->setSize(16);
    $objSpreadsheet->getActiveSheet()->getStyle("{$col_begin}{$row}:{$col_end}{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $objSpreadsheet->getActiveSheet()->getStyle($row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $objSpreadsheet->getActiveSheet()->getStyle($row)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    return $objSpreadsheet;
  }
  // **************************************************************************
  //  函數名稱: save() 
  //  函數功能: 儲存手動打卡資訊
  //  程式設計: Kiwi
  //  設計日期: 2021/11/21
  // **************************************************************************
  public function save($kind=NULL)	{
    switch($kind) {
      case "punch":
        $this->daily_work_model->save_punch(); // 新增儲存
        break;
    }
    return;
  }

  function __destruct() {
    $url_str[] = '';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // ???? foot
    }
  }
}