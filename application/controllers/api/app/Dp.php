<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dp extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->load->model('clients_model');   // 案主資料
    $this->load->model('delivery_person_model');
    $this->load->model('daily_work_model');
    $this->load->model('work_q_model');
    $this->load->model('punch_log_model');
  }

  // **************************************************************************
  //  函數名稱: index()                                                            
  //  函數功能:                                                                    
  //  程式設計: Tony                                                               
  //  設計日期: 2018/4/24                                                          
  // **************************************************************************
  public function index() {
    return;
  }
    
  // **************************************************************************
  //  函數名稱: get_client()
  //  函數功能: 獲取案主資料(外送員、社工共用)
  //  程式設計: Kiwi
  //  設計日期: 2020/12/15
  // **************************************************************************
  public function get_client() {
    list($identity, $dp_s_num) = token_descry();

    if(!empty($dp_s_num)) {
      $client_row = $this->daily_work_model->get_clients($dp_s_num);
      foreach ($client_row as $k => $v) {
        $client_row[$k]["ct_name"] = "{$v['ct01']}{$v['ct02']}";
        $client_row[$k]["ct_address"] = "{$v['ct09']}{$v['ct10']}{$v['ct11']}";
      } 
      echo json_encode($client_row);
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: save_punch()                                                       
  //  函數功能: 儲存打卡資料                                                             
  //  程式設計: Kiwi                                                               
  //  設計日期: 2020/12/15                                                         
  // **************************************************************************
  public function save_punch() {
    list($identity, $dp_s_num) = token_descry();
    if(!empty($dp_s_num)) {
      $this->punch_log_model->save_add_api($dp_s_num); // 儲存打卡資訊
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: save_web_punch()                                                       
  //  函數功能: 儲存打卡資料                                                             
  //  程式設計: Kiwi                                                               
  //  設計日期: 2020/12/15                                                         
  // **************************************************************************
  public function save_web_punch() {
    list($identity, $dp_s_num) = token_descry();
    if(!empty($dp_s_num)) {
      $this->punch_log_model->save_web_add_api($dp_s_num); // 儲存打卡資訊
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: get_daily_shipment()                                                       
  //  函數功能: 獲取今日訂單                                                          
  //  程式設計: Kiwi                                                               
  //  設計日期: 2020/12/27                                                         
  // **************************************************************************
  public function get_daily_shipment() {
    list($identity, $dp_s_num) = token_descry();
    if(empty($dp_s_num)) {
      return;
    }
      
    $rs = null;
    $v = $this->input->post();
      
    $daily_shipment_row_Y = $this->daily_work_model->get_daily_shipment($dp_s_num, "Y"); //  餐點配送單
    $daily_shipment_row_N = $this->daily_work_model->get_daily_shipment($dp_s_num, "N"); //  餐點配送單
    // $daily_shipment_abnormal_row = $this->daily_work_model->get_daily_shipment_abnormal($dp_s_num); // 異動單
    // u_var_dump($daily_shipment_row);
    $stop_str = '';
    $restore_str = '';
    $today = date("Y-m-d");
    $prev_3_day = date('Y-m-d', strtotime(' -3 day'));
    $daily_shipment_arr = [];
    // 轉換資料格式
    if(NULL != $daily_shipment_row_Y) {        
      foreach($daily_shipment_row_Y as $k => $v) {
        $daily_abnormal_row = $this->daily_work_model->get_daily_abnormal($dp_s_num, $v['sec_s_num']); // 停復餐名單
        if(NULL != $daily_abnormal_row) {
          if($daily_abnormal_row->mil_s01 == "Y") {
            if($daily_abnormal_row->mil_s02 >= $prev_3_day && $daily_abnormal_row->mil_s02 <= $today)  {
              if(strpos($restore_str , "{$v['ct_name']}({$v['dys02']})") === false) {
                if($restore_str != '') {
                  $restore_str .= "、";
                }
                $restore_str .= "{$v['ct_name']}({$v['dys02']})";
              }
            }
            array_push($daily_shipment_arr , $v);            
          } 
        }
      }
    }
      
    if(NULL != $daily_shipment_row_N) {
      foreach ($daily_shipment_row_N as $k => $v) {
        $daily_abnormal_row = $this->daily_work_model->get_daily_abnormal($dp_s_num, $v['sec_s_num']); // 停復餐名單
        if(NULL != $daily_abnormal_row and $daily_abnormal_row->mil_s01 == "N") {
          if(strpos($stop_str , "{$v['ct_name']}({$v['dys02']})") === false) {
            if($stop_str != '') {
            $stop_str .= "、";
            }
            $stop_str .= "{$v['ct_name']}({$v['dys02']})";
          }
        }
      }
    }
      
    $rs["daily_shipment"] = $daily_shipment_arr;
    $rs["stop"] = $stop_str;
    $rs["restore"] = $restore_str;
    echo json_encode($rs); 
    return;
  }
  
  // **************************************************************************
  //  函數名稱: get_work_q()                                                       
  //  函數功能: 獲取問卷資料                                                            
  //  程式設計: Kiwi                                                               
  //  設計日期: 2021/01/02                                                         
  // **************************************************************************
  public function get_work_q() {
    list($identity, $dp_s_num) = token_descry();
    if(!empty($dp_s_num)) {
      switch (date("w", strtotime(date("Y-m-d")))) { // 判斷今天是禮拜幾，產生相對應陣列index
        case 1: // 禮拜一
        case 2: // 禮拜二
        case 3: // 禮拜三
        case 4: // 禮拜四      
          $mt_type_code = 1;   
          break;  
        case 5: // 禮拜五
        case 6: // 禮拜六
        case 0: // 禮拜日
          $mt_type_code = 2;
          break;
      }
      $mt_type[1] = array(1, 2, 5, 6, 7, 8); // 非熟代，週一至週四送
      $mt_type[2] = array(3, 4, 9); // 熟代，週五送

      $work_h_data = $this->work_q_model->get_daily_work_q($dp_s_num);
      $daily_work_q = null;
      $daily_work_q_arr = array();
      if($work_h_data != null) {
        foreach($work_h_data as $k => $v) {
          if(NULL == $v["s_num"]) {
            continue;
          } 
          
          if(!isset($daily_work_q[$v["s_num"]])) {
            $daily_work_q[$v["s_num"]]['wqh_s_num'] = $v["s_num"];
            $daily_work_q[$v["s_num"]]['qh_s_num'] = $v["qh_s_num"];
            $daily_work_q[$v["s_num"]]['qh01'] = $v["qh01"];
            $daily_work_q[$v["s_num"]]['ct_s_num'] = $v["ct_s_num"];
            $daily_work_q[$v["s_num"]]['ct_name'] = $v["ct_name"];
            $daily_work_q[$v["s_num"]]['dys02'] = $v["dys02"]; // 餐別(午餐、中晚餐、晚餐)
          }
          
          $chk_row = $this->daily_work_model->chk_meal_replacemet($v['ct_s_num'] , $v['sec_s_num']);
          if($v["qb_order"] == 3 or $v["qb_order"] == 4 or $v["qb_order"] == 5) { // 判斷是否要帶代餐問題
            if(NULL != $chk_row) {
              if(in_array($chk_row->ct_mp02 , $mt_type[$mt_type_code])) {
                if($chk_row->ct_mp04 == "N") { // 當代餐未送達的時候
                  $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb_s_num'] = $v["qb_s_num"]; // 題目s_num
                  $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb_order'] = $v["qb_order"]; // 題目排序
                  $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb01'] = $v["qb01"]; // 題目
                  $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb02'] = $v["qb02"]; // 題目類型
                  // 切割問題選項
                  if(null != $v["qb03"]) {
                    $qb03 = explode(",", $v["qb03"]);
                    foreach($qb03 as $kq => $vq) {
                      $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb03'][$kq] = $qb03[$kq]; // 選項 
                    }
                  }
                  else {
                    $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb03'] = null; // 選項 
                  }
                }
              }
            }
          }
          else {
            $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb_s_num'] = $v["qb_s_num"]; // 題目s_num
            $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb_order'] = $v["qb_order"]; // 題目排序
            $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb01'] = $v["qb01"]; // 題目
            $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb02'] = $v["qb02"]; // 題目類型
            // 切割問題選項
            if(null != $v["qb03"]) {
              $qb03 = explode(",", $v["qb03"]);
              foreach($qb03 as $kq => $vq) {
                $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb03'][$kq] = $qb03[$kq]; // 選項 
              }
            }
            else {
              $daily_work_q[$v["s_num"]]["qb"][$v["qb_order"]]['qb03'] = null; // 選項 
            }      
          }

          ksort($daily_work_q[$v["s_num"]]);
        }

        // 整理成array格式
        if(NULL != $daily_work_q) {
          foreach($daily_work_q as $k => $v) {
            array_push($daily_work_q_arr , $v);
          }
        
          // 將問題選項再拉出來整理成陣列
          foreach($daily_work_q_arr as $k => $v) {
            $daily_work_q_arr[$k]["qb"] = array();
            foreach($v["qb"] as $kqb => $vqb) {
              array_push($daily_work_q_arr[$k]["qb"], $vqb);
            }
          }
        }
      }     
    }
    
    echo json_encode($daily_work_q_arr);
    return;
  } 
  
  // **************************************************************************
  //  函數名稱: save_work_q()                                                       
  //  函數功能: 儲存工作問卷資料                                                             
  //  程式設計: Kiwi                                                               
  //  設計日期: 2020/12/15                                                         
  // **************************************************************************
  public function save_work_q() {
    list($identity, $dp_s_num) = token_descry();
    if(!empty($dp_s_num)) {
      $this->work_q_model->save_daily_work_q($dp_s_num); // 儲存工作問卷資料
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: upd_client_gps()                                                       
  //  函數功能: 更新案主GPS                                                             
  //  程式設計: Kiwi                                                               
  //  設計日期: 2021/09/17                                                   
  // **************************************************************************
  public function upd_client_gps() {
    $file = ITM_FILE_PATH.'gps_correction.txt';
    $open_flag = file_get_contents($file);
    if("N" == $open_flag) { // 如果是"N"就暫時關閉
      return;
    }

    list($identity, $dp_s_num) = token_descry();
    if(!empty($dp_s_num)) {
      $this->clients_model->save_upd_gps_by_api($dp_s_num); // 儲存打卡資訊
    }
    return;
  }
  
  function __destruct() {
    return;
  }
  
}