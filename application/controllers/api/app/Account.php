<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->load->model('gps_log_model');  // gps紀錄
    $this->load->model('work_log_model'); // 工作紀錄
    $this->load->model('work_time_model'); // 工作時段
    $this->load->model('daily_work_model'); // 每日工作
    $this->load->model('delivery_person_model'); // 外送員基本資料檔
    $this->load->model('social_worker_model'); // 社工基本資料檔
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
  //  函數名稱: login()
  //  函數功能: api登入
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function login() {
    $v = $this->input->post();
    $state = '';
    $acc_row = NULL;
    $json_data = NULL;
    
    list($state, $sw_acc_row) = $this->social_worker_model->chk_api_login();
    if(0 == $state) {
      $acc_row = $sw_acc_row;
    }
    else {
      list($state, $dp_acc_row) = $this->delivery_person_model->chk_api_login();
      if(0 == $state) {
        $acc_row = $dp_acc_row;

        // 取得目前的班別
        $now_work_time = 1;
        $today_work_time = array();
        $work_time_row = $this->work_time_model->get_by_time();
        if(!empty($work_time_row)) {
          $now_work_time = $work_time_row->s_num;
        }    

        // 取得送餐員當日班別
        $work_time_arr = $this->work_time_model->get_all_is_available();
        if(!empty($work_time_arr)) {
          $today_work_time_arr = array();
          foreach ($work_time_arr as $k => $v) {
            $_POST['type'] = $v['s_num'];
            $daily_shipment_arr = $this->daily_work_model->get_daily_shipment($acc_row['dp_s_num'], "Y");
            if(!empty($daily_shipment_arr)) {
              $today_work_time[] = array($v['s_num'] => $v['wt01']);
              $today_work_time_arr[] = $v['s_num'];
            }
          }
          sort($today_work_time);
          
          // 判斷當前的班別是否有在送餐員今日的班別，如果沒有的話回傳最後一個班別回去
          if(!empty($today_work_time_arr)) {
            if(!in_array($now_work_time, $today_work_time_arr)) {
              $now_work_time = end($today_work_time_arr);
            }
          }
        }

        $acc_row['now_work_time'] = $now_work_time;
        $acc_row['today_work_time'] = $today_work_time;
        unset($acc_row['dp_s_num']);
      }
    }

    if(!empty($acc_row)) {
      $json_data = $acc_row;
    }
    else {
      $json_data['state'] = $state; 
    }
    
    echo json_encode($json_data, JSON_NUMERIC_CHECK);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: save_work_log()
  //  函數功能: 新增工作紀錄
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function save_worklog() {
    list($identity, $verify_s_num) = token_descry();
    
    $work_log_pic = null;      
    if(!empty($_FILES['WorkLogPicture'])) {
      $config['upload_path'] = FCPATH.'pub/uploads/work_log/';
      $config['allowed_types'] = '*'; // jpg|png|jpeg|bmp; 
      $config['max_size'] = 6100;
      $config['max_width'] = 0;
      $config['max_height'] = 0;
      $config['encrypt_name'] = TRUE;
      $this->load->library('upload', $config);                                                            
      if(!$this->upload->do_upload('WorkLogPicture')) {
        $error = array('error' => $this->upload->display_errors());                
        echo $error['error'];
        return; 
      }
      $fileMetaData = $this->upload->data();     
      $work_log_pic = $fileMetaData['file_name'];
    }
    
    $this->work_log_model->save_add_api($work_log_pic, $verify_s_num); // 儲存工作紀錄  
    return;
  }
  
  // **************************************************************************
  //  函數名稱: save_gps()
  //  函數功能: 儲存gps紀錄
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function save_gps() {
    $now = date('Y-m-d H:i:s') ;
    $today = date('Y-m-d') ;
    $start_time = "{$today} 09:00:00";
    $end_time = "{$today} 18:30:00";

    if(strtotime($end_time) >= strtotime($now) && strtotime($now) >= strtotime($start_time)) {
      list($identity, $verify_s_num) = token_descry();
      if(!empty($verify_s_num)) {
        $this->gps_log_model->save_add_api($verify_s_num); // 外送員獲取該路徑上的案主資料
      }
    }
    return;
  }
  
  // **************************************************************************
  //  函數名稱: save_web_gps()
  //  函數功能: 儲存gps紀錄
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function save_web_gps() {
    $now = date('Y-m-d H:i:s') ;
    $today = date('Y-m-d') ;
    $start_time = "{$today} 08:00:00";
    $end_time = "{$today} 22:30:00";
    
    if(strtotime($end_time) >= strtotime($now) && strtotime($now) >= strtotime($start_time)) {
      list($identity, $verify_s_num) = token_descry();
      if(!empty($verify_s_num)) {
      	$this->gps_log_model->save_add_web_api($verify_s_num); // 外送員獲取該路徑上的案主資料
      }
    }
    return;
  }

  // **************************************************************************
  //  函數名稱: logout()
  //  函數功能: APP登出
  //  程式設計: Kiwi
  //  設計日期: 2023/11/15
  // **************************************************************************
  public function logout() {
    list($identity, $verify_s_num) = token_descry();
    switch($identity) {
      case SW_AUTH_CODE:
        $this->social_worker_model->save_acc_logout($verify_s_num);
        break;
      case DP_AUTH_CODE:
        $this->delivery_person_model->save_acc_logout($verify_s_num);
        break;
    }
  }

  function __destruct() {
    return;
  }
}
