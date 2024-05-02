<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Mp_rpt extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->library('zip');
    $this->load->model('route_model'); // 路線資料
    $this->load->model('daily_work_model'); // 配送單資料
    $this->load->model('service_case_model'); // 開結案資料
    $this->load->model('meal_instruction_log_h_model'); // 異動單資料
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
    $this->tpl->assign('tv_return_link',be_url()."mp_rpt/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"弗傳慈心基金會");
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    $this->test_route = array('3', '48', '49', '50', '51', '53');
    $this->this_sun = date("Y-m-d",strtotime("this Sunday", strtotime(date('Y-m-d')))); // 熟代，以當周六前的最新異動
    $this->next_sun = date("Y-m-d",strtotime("+1 weeks Sunday", strtotime(date('Y-m-d')))); // 非熟代，以下周六前的最新異動
    return;
  }
  
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 輸入畫面
  //  程式設計: Tony
  //  設計日期: 2020/4/13
  // **************************************************************************
  public function index($type='now') {
    $msel = 'list';
    $type = 'now'; // 預設為當下
    $version = 'now'; // 預設為當下
    $meal_b_date_1 = ''; // 熟代午餐資料產生時間
    $meal_b_date_2 = ''; // 熟代午晚/晚資料產生時間
    $item_b_date_1 = ''; // 非熟代午餐資料產生時間
    $item_b_date_2 = ''; // 非熟代午晚/晚資料產生時間
    // $get_data = $this->input->get();
    // if(isset($get_data['type'])) {
    //   $type = $get_data['type'];
    // }
    // if(isset($get_data['version'])) {
    //   $version = $get_data['version'];
    // }
    // $meal_replacement_hist_date_row = $this->daily_work_model->get_meal_replacement_hist_date(); // 取得代餐歷史資料的時間

    
    $route_row = $this->route_model->get_all_without_test();
    unset($route_row['51']); // 移除德路線 
    unset($route_row['53']); // 移除傳路線
    
    list($meal_replacement_list,
         $item_replacement_list,
         $meal_replacement_data,
         $item_replacement_data,
         $meal_b_date_1,
         $meal_b_date_2,
         $item_b_date_1,
         $item_b_date_2) = $this->_generate_data();
         
    $each_route_cnt = NULL;     
    foreach ($route_row as $kr => $vr) {
      $each_route_cnt[$vr['s_num']]['reh01'] = $vr['reh01'];
      $each_route_cnt[$vr['s_num']]['total'] = count((array) $this->route_model->get_route_b($vr['s_num']));
      $each_route_cnt[$vr['s_num']]['send'] = 0;
    } 
          
    $meal_replacement_row = $this->daily_work_model->get_meal_replacement_data();
    if(NULL != $meal_replacement_row) {
      foreach ($meal_replacement_row as $k => $v) {
        if(NULL != $v['reh_s_num'] and !in_array($v['reh_s_num'] , $this->test_route)) {
          $each_route_cnt[$v['reh_s_num']]['send'] += 1;
        }
      } 
    } 
    
    // u_var_dump($meal_replacement_data);
    // u_var_dump($item_replacement_data);
    // u_var_dump($prn_meal_data);
    // u_var_dump($meal_replacement_list);
    // u_var_dump($item_replacement_list);
    // u_var_dump($each_route_cnt);

    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_group_s_num',$_SESSION['group_s_num']);
    $this->tpl->assign('tv_version',$version);
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_this_sun',$this->this_sun);
    $this->tpl->assign('tv_next_sun',$this->next_sun);
    $this->tpl->assign('tv_meal_b_date_1',$meal_b_date_1);
    $this->tpl->assign('tv_meal_b_date_2',$meal_b_date_2);
    $this->tpl->assign('tv_item_b_date_1',$item_b_date_1);
    $this->tpl->assign('tv_item_b_date_2',$item_b_date_2);
    $this->tpl->assign('tv_each_route_cnt',$each_route_cnt);
    $this->tpl->assign('tv_meal_replacement_data',$meal_replacement_data);
    $this->tpl->assign('tv_item_replacement_data',$item_replacement_data);
    $this->tpl->assign('tv_meal_replacement_list',$meal_replacement_list);
    $this->tpl->assign('tv_item_replacement_list',$item_replacement_list);
    $this->tpl->assign('tv_download_link',be_url().'mp_rpt/download/'); // 下載代餐資料
    $this->tpl->assign('tv_download3_link',be_url().'mp_rpt/download3/'); // 下載案主代餐資料
    $this->tpl->assign('tv_produce_meal_replacement_link',be_url().'mp_rpt/produce_meal_replacement?type=');
    $this->tpl->assign('tv_exit_link',be_url().'mp_rpt/');
    $this->tpl->assign('tv_que_link',be_url().'mp_rpt');
    $this->tpl->display("be/mp_rpt_prn.html");
    // $this->tpl->assign('tv_meal_replacement_hist_date_row',$meal_replacement_hist_date_row);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: download
  //  函數功能: 下載資料
  //  程式設計: Kiwi
  //  設計日期: 2021/06/10
  // **************************************************************************
  public function download() {
    $rtn_msg = '';
    $time_start = date('Y-m-d H:i:s');

    $route_row = $this->route_model->get_all_without_test();
    unset($route_row['51']); // 移除德路線 
    unset($route_row['53']); // 移除傳路線
    
    list($meal_replacement_list,
         $item_replacement_list,
         $meal_replacement_data,
         $item_replacement_data,
         $meal_b_date_1,
         $meal_b_date_2,
         $item_b_date_1,
         $item_b_date_2) = $this->_generate_data();

    // 處理統計資料 BEGIN //
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/mp_rpt_sample.xlsx"); // 載入樣板資料

    if(NULL != $meal_replacement_data) {
      foreach ($meal_replacement_data as $k => $v) {
        $col = 3; // 從C開始
        $row = 1; // 檔頭資料從第一列開始
        // if($k == 1) { // 熟代(早)
        //   $page = 0;
        // }
        // if($k == 2) { // 熟代(晚)
        //   continue;
        // }
        switch ($k) {   
          case 1:
            $page = 0; // 非熟代(午)
            $reh_type = 1;
            $meal_b_date = $meal_b_date_1;
            break;
          case 2:
            $page = 1; // 非熟代(午晚)
            $reh_type = 1;
            $meal_b_date = $meal_b_date_2;
            break;
          case 3:
            $page = 2; // 非熟代(晚)
            $reh_type = 2;
            $meal_b_date = $meal_b_date_2;
            break;
        }
        $objSpreadsheet->setActiveSheetIndex($page);  
        foreach ($route_row as $kr => $vr) {
          if($vr['reh05'] == $reh_type) {
            $objSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col , $row , $vr['reh01']); // Column & Row
            $col++;
          }
        }
        
        $row = 2;
        foreach ($v as $k2 => $v2) {
          if($k2 == $reh_type) {
            foreach ($v2 as $k_type => $v_type) {
              $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", hlp_opt_setup("mil_mp01_type", $k_type)); // 代餐種類
              $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v_type['total']); // 代碼
              $col = 3; // 從C開始
              foreach ($v_type['each'] as $kitem => $vitem) {
                $objSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col , $row , $vitem['num']); // Column & Row
                $col++;
              } 
              $row++;
            }
          }
          $objSpreadsheet->getActiveSheet()->setCellValue("A". ($row + 1) , "資料建立時間：".$meal_b_date); // 資料建立時間
          $objSpreadsheet->getActiveSheet()->setCellValue("A". ($row + 2) , "資料判斷日期：".$this->this_sun); // 異動日期
        }
      } 
    }
    
    if(NULL != $item_replacement_data) {
      foreach ($item_replacement_data as $k => $v) {
        switch ($k) {   
          case 1:
            $page = 3; // 非熟代(午)
            $reh_type = 1;
            $sun_date = $this->next_sun;
            $item_b_date = $item_b_date_1;
          break;
          case 2:
            $page = 4; // 非熟代(午晚)
            $reh_type = 1;
            $sun_date = $this->this_sun;
            $item_b_date = $item_b_date_2;
          break;
          case 3:
            $page = 5; // 非熟代(晚)
            $reh_type = 2;
            $sun_date = $this->this_sun;
            $item_b_date = $item_b_date_2;
          break;
        }  
        $col = 3; // 從C開始
        $row = 1; // 檔頭資料從第一列開始 
        $objSpreadsheet->setActiveSheetIndex($page);  
        foreach ($route_row as $kr => $vr) {
          if($vr['reh05'] == $reh_type) {
            $objSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col , $row , $vr['reh01']); // Column & Row
            $col++;
          }
        }
        
        $row = 2;
        foreach ($v as $k2 => $v2) {
          if($k2 == $reh_type) {
            foreach ($v2 as $k_type => $v_type) {
              if($k == 2 or $k == 3) { // 如果是中晚或晚餐
                if($k_type == 6 or $k_type == 7) { // 如果是每周一米或每周二米就跳過
                  continue;
                }
              } 
              
              $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", hlp_opt_setup("mil_mp01_type", $k_type)); // 代餐種類
              $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v_type['total']); // 代碼
              $col = 3; // 從C開始
              foreach ($v_type['each'] as $kitem => $vitem) {
                $objSpreadsheet->getActiveSheet()->setCellValueByColumnAndRow($col , $row , $vitem['num']); // Column & Row
                $col++;
              } 
              $row++;
            }
          }
          $objSpreadsheet->getActiveSheet()->setCellValue("A". ($row + 1) , "資料建立時間：".$item_b_date); // 資料建立時間
          $objSpreadsheet->getActiveSheet()->setCellValue("A". ($row + 2) , "資料判斷日期：".$sun_date); // 異動日期
        }
      }
    }
    
    $filename = "meal_replacement.xlsx";
    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
    $objWriter->save(FCPATH."export_file/{$filename}"); // 儲存到server
    $objSpreadsheet->disconnectWorksheets(); // 清除資料
    // $objWriter->save('php://output');
    // 處理統計資料 END //

    // 處理名單資料 BEGIN //
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(FCPATH."pub/sample/mp_rpt_list_sample.xlsx"); // 載入樣板資料
    if(NULL != $meal_replacement_list) { // 熟代名單
      foreach ($meal_replacement_list as $k => $v) {
        $row = 2;
        switch ($k) {   
          case 1:
            $page = 0; // 非熟代(午)
          break;
          case 2:
            $page = 1; // 非熟代(午晚)
          break;
          case 3:
            $page = 2; // 非熟代(晚)
          break;
        }  
        $objSpreadsheet->setActiveSheetIndex($page);  
        foreach($v as $k2 => $v2) {
          foreach ($v2 as $k_detail => $v_detail) {
            if(NULL != $v_detail['ct_mp02']) {
              $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $v_detail['reh01']); // 送餐路線
              $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v_detail['reb01']); // 送餐順序
              $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $v_detail['ct_name']); // 案主名稱
              $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", hlp_opt_setup("mil_mp01_type", $v_detail['ct_mp02'])); // 代餐種類
              $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $v_detail['ct_mp01_str']); // 是否出餐
              $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $v_detail['ct_mp03_str']); // 是否送代餐
              $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $v_detail['ct_mp04_time']); // 送達時間
              $row++;
            }
          }
        }
      }
    }

    if(NULL != $item_replacement_list) { // 非熟代名單
      foreach ($item_replacement_list as $k => $v) {
        $row = 2;
        switch ($k) {   
          case 1:
            $page = 3; // 非熟代(午)
          break;
          case 2:
            $page = 4; // 非熟代(午晚)
          break;
          case 3:
            $page = 5; // 非熟代(晚)
          break;
        }  
        $objSpreadsheet->setActiveSheetIndex($page);  
        foreach($v as $k2 => $v2) {
          foreach ($v2 as $k_detail => $v_detail) {
            if(NULL != $v_detail['ct_mp02']) {
              $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $v_detail['reh01']); // 送餐路線
              $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v_detail['reb01']); // 送餐順序
              $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $v_detail['ct_name']); // 案主名稱
              $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", hlp_opt_setup("mil_mp01_type", $v_detail['ct_mp02'])); // 代餐種類
              $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", $v_detail['ct_mp01_str']); // 是否出餐
              $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $v_detail['ct_mp03_str']); // 是否送代餐
              $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $v_detail['ct_mp04_time']); // 送達時間
              $row++;
            }
          }
        }
      }
    }

    $list_filename = "meal_replacement_list.xlsx";
    $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objSpreadsheet);
    $objWriter->save(FCPATH."export_file/{$list_filename}"); // 儲存到server
    $objSpreadsheet->disconnectWorksheets(); // 清除資料
    // 處理名單資料 END //

    $this->zip->read_file(FCPATH."export_file/{$filename}" , false);
    $this->zip->read_file(FCPATH."export_file/{$list_filename}" , false);
    $this->zip->archive(FCPATH."export_file/mp_rpt.zip"); 
    
    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment;filename=mp_rpt.zip");
    
    $ch_filename = "代餐資料.zip";
    $en_filename = "mp_rpt.zip";
    $time_end = date('Y-m-d H:i:s');
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename, time_cnt($time_start, $time_end)); 
    
    echo $rtn_msg;
    return;
  }
    
  // **************************************************************************
  //  函數名稱: produce_meal_replacement
  //  函數功能: 產生這周代餐
  //  程式設計: Kiwi
  //  設計日期: 2020/12/08
  // **************************************************************************
  public function produce_meal_replacement()  {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $mp_data = NULL;
    $mp_type = NULL;
    $sun_date = NULL;

    switch($_GET['type']) {
      case 1: // 熟代-午餐
      case 4: // 熟代-午晚/晚餐
        $mp_type = array(3, 4, 9); // 熟代
        $sun_date = $this->this_sun;
        break;
      case 2:
        $mp_type = array(1, 2, 5, 6, 7, 8); // 非熟代-午晚/晚餐
        $sun_date = $this->this_sun;
        break;
      case 3:
        $mp_type = array(1, 2, 5, 6, 7, 8); // 非熟代-午餐
        $sun_date = $this->next_sun;
        break;
    }

    $i = 0;
    $service_case_row = $this->service_case_model->get_all_by_sec04($sun_date);    
    foreach($service_case_row as $k => $v) {
      // 非熟代先判斷服務類型:1=送餐(午);2=送餐(午晚);3=送餐(晚)
      if($_GET['type'] == 2 and $v['sec04'] == 1) { // 當產生代餐種類是 非熟代-中晚/晚餐 , 服務類型是 午餐 就跳過
        continue;
      }
      if($_GET['type'] == 3 and $v['sec04'] != 1) { // 當產生代餐種類是 非熟代-午餐 , 服務類型是 中晚/晚餐 就跳過
        continue;
      }
      // 熟代先判斷服務類型:1=送餐(午);2=送餐(午晚);3=送餐(晚)
      if($_GET['type'] == 1 and $v['sec04'] != 1) { // 當產生代餐種類是 熟代-午餐 , 服務類型是 中晚/晚餐 就跳過
        continue;
      }
      if($_GET['type'] == 4 and $v['sec04'] == 1) { // 當產生代餐種類是 熟代-中晚/晚餐 , 服務類型是 午餐 就跳過
        continue;
      }

      $route_row = $this->route_model->que_client_route($v['reh_type'] , $v['ct_s_num']);
      $meal_instruction_log_mp_row = $this->meal_instruction_log_h_model->get_last_mp_by_s_num($v['s_num'], $sun_date); // 取得最後一筆代餐異動資料
      $meal_instruction_log_s_row = $this->meal_instruction_log_h_model->get_last_s_by_s_num($v['s_num'], $sun_date); // 取得最後一筆停餐異動資料
      
      if($meal_instruction_log_s_row != NULL) {
        // 先判斷有沒有代餐異動資料
        if(!isset($meal_instruction_log_mp_row)) {
          continue;
        }
        // 先判斷有沒有代餐和是否為這次代餐產生的種類
        if(NULL == $meal_instruction_log_mp_row->mil_mp01_type or !in_array($meal_instruction_log_mp_row->mil_mp01_type, $mp_type)) { 
          continue;
        }
        if($route_row != NULL) {
          if($meal_instruction_log_s_row->mil_s01 == "Y") {  // 先判斷是否停餐
            if($meal_instruction_log_mp_row->mil_mp01 == "Y" and $meal_instruction_log_mp_row->mil_mp01_type != NULL) {  // 先判斷是否代餐及代餐種類不為空
              $meal_replacement_row = $this->daily_work_model->get_one_meal_replacement($v['s_num'], $sun_date); // 取得尚未更新前的資料
              $mp_data[$i]['b_date'] = date("Y-m-d H:i:s");
              $mp_data[$i]['b_empno'] = $_SESSION['acc_s_num'];
              $mp_data[$i]['ct_s_num'] = $v['ct_s_num'];
              $mp_data[$i]['ct_name'] = "{$v['ct_name']}";
              $mp_data[$i]['sec_s_num'] = $v['s_num'];
              $mp_data[$i]['sec03'] = $v['sec03'];
              $mp_data[$i]['reh_s_num'] = $route_row->s_num;
              $mp_data[$i]['reh01'] = $route_row->reh01;
              $mp_data[$i]['reb01'] = $route_row->reb01;
              $mp_data[$i]['ct_mp01'] = $meal_instruction_log_s_row->mil_s01;
              $mp_data[$i]['ct_mp02'] = $meal_instruction_log_mp_row->mil_mp01_type;
              $mp_data[$i]['ct_mp03'] = $meal_instruction_log_mp_row->mil_mp01;
              $mp_data[$i]['ct_mp04'] = "N";
              $mp_data[$i]['ct_mp04_time'] = NULL;
              $mp_data[$i]['ct_mp05'] = '';
              $mp_data[$i]['ct_mp06'] = 'N';
              $mp_data[$i]['ct_mp07'] = $v['sec04'];
              $mp_data[$i]['ct_mp08'] = $sun_date;
              // 如果有meal_replacement_row，代表在當周，如果沒有帶表示下周了
              if(NULL != $meal_replacement_row) {
                $mp_data[$i]['ct_mp04'] = $meal_replacement_row->ct_mp04;
                $mp_data[$i]['ct_mp04_time'] = $meal_replacement_row->ct_mp04_time;
                $mp_data[$i]['ct_mp05'] = $meal_replacement_row->ct_mp05;
                $mp_data[$i]['ct_mp06'] = $meal_replacement_row->ct_mp06;
              }
              $i++;
            }
          }
        }
      }
    }
    $this->daily_work_model->save_meal_replacement_data($mp_data);   
    return;
  }

  // **************************************************************************
  //  函數名稱: download3
  //  函數功能: 下載全部代餐資料
  //  程式設計: Kiwi
  //  設計日期: 2022-02-05
  // **************************************************************************
  public function download3() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $time_start = date('Y-m-d H:i:s');

    $sample_file = FCPATH."pub/sample/mp_clients_sample.xlsx";
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
    $objSpreadsheet->setActiveSheetIndex(0);

    $sort_reh = array();
    $sort_reb = array();
    $today = date("Y-m-d");
    $service_case_row = $this->service_case_model->get_download3_data("mp_rpt"); 
    if(NULL != $service_case_row) {
      foreach($service_case_row as $k => $v) {
        $route_row = $this->route_model->que_client_route($v['reh_type'] , $v['ct_s_num']);
        $meal_instruction_log_s_row = $this->meal_instruction_log_h_model->get_last_s_by_s_num($v['s_num'], $today); // 取得最後一筆停餐異動資料
        $meal_instruction_log_m_row = $this->meal_instruction_log_h_model->get_last_m_by_s_num($v['s_num'], $today); // 取得最後一筆餐點異動資料
        $meal_instruction_log_mp_row = $this->meal_instruction_log_h_model->get_last_mp_by_s_num($v['s_num'], $today); // 取得最後一筆代餐異動資料
        $meal_instruction_log_i_row = $this->meal_instruction_log_h_model->get_last_i_by_s_num($v['s_num'], $today); // 列出自費資料
        $service_case_row[$k]['reh01'] = @$route_row->reh01;
        $service_case_row[$k]['reb01'] = @$route_row->reb01;
        $service_case_row[$k]['ml01'] = @$meal_instruction_log_m_row->ml01;
        $service_case_row[$k]['mil_mp01_type'] = "";
        $service_case_row[$k]['mil_mp01'] = @str_replace(array("Y", "N"), array("有代餐", "無代餐"), $meal_instruction_log_mp_row->mil_mp01);
        $service_case_row[$k]['mil_i01'] = @str_replace(array("Y", "N"), array("是", "否"), $meal_instruction_log_i_row->mil_i01);
        $service_case_row[$k]['mil_s01'] = @str_replace(array("Y", "N"), array("出餐", "停餐"), $meal_instruction_log_s_row->mil_s01);
        $service_case_row[$k]["ct38_1_str"] = $v["ct38_1_str"];
        $service_case_row[$k]["ct38_2_str"] = $v["ct38_2_str"];
        $service_case_row[$k]['mil_mp01_type'] = @$meal_instruction_log_mp_row->mil_mp01_type_str;
        $sort_reh[$k] = @$route_row->reh01;
        $sort_reb[$k] = @$route_row->reb01;
      }
    }
    array_multisort($sort_reh, SORT_ASC, $sort_reb, SORT_ASC, $service_case_row); // 陣列排序，先排路線，在排順序 

    if(NULL != $service_case_row) {
      $row = 2; 
      foreach ($service_case_row as $k => $v) {
        $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $v['reh01']);                                        // 路線
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v['reb01']);                                        // 順序
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $v['ct_name']);                                      // 案主名稱
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $v['ct06_telephone']);                               // 案主電話
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", "{$v['ct12']}{$v['ct13']}{$v['ct14']}{$v['ct15']}"); // 聯絡地址
        $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", $v['mil_i01']);                                      // 是否自費
        $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", $v['sec04_str']);                                    // 餐別
        $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", $v['ml01']);                                         // 餐種
        $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $v['mil_mp01_type']);                                // 代餐種類
        $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", $v['mil_mp01']);                                     // 是否送代餐
        $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", $v['mil_s01']);                                     // 是否出餐
        $objSpreadsheet->getActiveSheet()->setCellValue("L{$row}", "1.疾病-1: {$v['ct38_1_str']}\n2.疾病-2: {$v['ct38_2_str']}\n3.其他: {$v['ct38_memo']}"); // 疾病
        $row++;
      }
    }

    $ch_filename = "全部代餐資料.xlsx";
    $en_filename = "mp_clients_data_".date('Y-m-d H-i-s').".xlsx";
    
    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=" . $en_filename);
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objSpreadsheet , 'Xlsx');
    $writer->save(FCPATH."export_file/{$en_filename}"); // 儲存到server

    $time_end = date('Y-m-d H:i:s');
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename, time_cnt($time_start, $time_end)); 
    echo json_encode($rtn_msg);
    return;
  }
  // **************************************************************************
  //  函數名稱: _generate_data
  //  函數功能: 產生資料
  //  程式設計: Kiwi
  //  設計日期: 2022-02-11
  // **************************************************************************
  function _generate_data() {
    $meal_b_date_1 = ''; // 熟代資料產生時間
    $meal_b_date_2 = ''; // 熟代資料產生時間
    $item_b_date_1 = ''; // 非熟代午餐資料產生時間
    $item_b_date_2 = ''; // 非熟代午晚/晚資料產生時間

    $meal_replacement_list = NULL; // 熟代名單
    $item_replacement_list = NULL; // 非熟代名單
    $meal_replacement_data = NULL; // 熟代統計資料
    $item_replacement_data = NULL; // 非熟代統計資料

    $sec04 = array(1,2,3); // 服務類型: 午餐，午晚，晚
    $meal_replace_arr = array(3,4,9); // 熟代
    $meal_replace_type = @hlp_opt_setup("mil_mp01_type", null, "get"); 

    $route_row = $this->route_model->get_all_without_test();
    unset($route_row['51']); // 移除德路線 
    unset($route_row['53']); // 移除傳路線

    foreach ($route_row as $kr => $vr) {
      foreach ($meal_replace_type as $k => $v) {
        if(in_array($k , $meal_replace_arr)) { // 如果是熟代
          foreach ($sec04 as $v_sec) {
            $meal_replacement_list[$v_sec] = NULL;
            $meal_replacement_data[$v_sec][$vr['reh05']][$k]["total"] = 0;
            $meal_replacement_data[$v_sec][$vr['reh05']][$k]["each"][$vr['s_num']]["reh01"] = $vr['reh01'];
            $meal_replacement_data[$v_sec][$vr['reh05']][$k]["each"][$vr['s_num']]["num"] = 0;
          }
        }
        else {
          foreach ($sec04 as $v_sec) {
            $item_replacement_list[$v_sec] = NULL;
            $item_replacement_data[$v_sec][$vr['reh05']][$k]["total"] = 0;
            $item_replacement_data[$v_sec][$vr['reh05']][$k]["each"][$vr['s_num']]["reh01"] = $vr['reh01'];
            $item_replacement_data[$v_sec][$vr['reh05']][$k]["each"][$vr['s_num']]["num"] = 0;
          } 
        }
      } 
    }

    $meal_replacement_row = $this->daily_work_model->get_meal_replacement_data();
    if(NULL != $meal_replacement_row) {
      foreach ($meal_replacement_row as $k => $v) {
        if(NULL != $v['reh_s_num'] and !in_array($v['reh_s_num'] , $this->test_route)) {
          if(in_array($v['ct_mp02'] , $meal_replace_arr)) { // 如果是熟代
            if($v['ct_mp07'] == 1) {
              $meal_b_date_1 = $v['b_date'];
            }
            else {
              $meal_b_date_2 = $v['b_date'];
            }
            $meal_replacement_list[$v['ct_mp07']][$v['reh05']][] = $v;
            $meal_replacement_data[$v['ct_mp07']][$v['reh05']][$v['ct_mp02']]["each"][$v['reh_s_num']]["num"] += 1;
            $meal_replacement_data[$v['ct_mp07']][$v['reh05']][$v['ct_mp02']]["total"] += 1;
          }
          else {
            if($v['ct_mp07'] == 1) {
              $item_b_date_1 = $v['b_date'];
            }
            else {
              $item_b_date_2 = $v['b_date'];
            }
            $item_replacement_list[$v['ct_mp07']][$v['reh05']][] = $v;
            $item_replacement_data[$v['ct_mp07']][$v['reh05']][$v['ct_mp02']]["each"][$v['reh_s_num']]["num"] += 1;
            $item_replacement_data[$v['ct_mp07']][$v['reh05']][$v['ct_mp02']]["total"] += 1;
          }
        }
      } 
    }

    return array($meal_replacement_list,
                 $item_replacement_list,
                 $meal_replacement_data,
                 $item_replacement_data,
                 $meal_b_date_1,
                 $meal_b_date_2,
                 $item_b_date_1,
                 $item_b_date_2
                );
  }
  // **************************************************************************
  //  函數名稱: sort_by_reb01
  //  函數功能: 排序順序
  //  程式設計: Kiwi
  //  設計日期: 2022-02-05
  // **************************************************************************
  function sort_by_reb01($a, $b) {
    return $a['reb01'] - $b['reb01'];
  }

  function __destruct() {
    $url_str[] = '';
    $url_str[] = 'be/mp_rpt/download';
    $url_str[] = 'be/mp_rpt/download3';
    $url_str[] = 'be/mp_rpt/produce_meal_replacement';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // ???? foot
    }
  }
}
