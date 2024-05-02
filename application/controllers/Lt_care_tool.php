<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lt_care_tool extends CI_Controller { // 帳號
  public function __construct() {
    parent::__construct();
    if(!isset($_SESSION['acc_s_num'])) {
      die();
    }
    $this->load->model('sys_sendmail_model'); // 發信排程
    $this->load->model('service_case_model');   // 每日訂單
    $this->load->model('clients_model');   // 案主資料
    $this->load->model('meal_model');   // 每日訂單
    $this->load->model('route_model');   // 服務路徑資料
    $this->load->model('daily_work_model');   // 每日送餐資料
    $this->load->model('meal_order_model');   // 每日訂單
    $this->load->model('meal_order_date_type_model');   // 訂單日期類型
    $this->load->model('other_change_auth_model'); // 異動單審核紀錄檔
    $this->load->model('other_change_log_h_model'); // 異動單審核紀錄檔
    $this->load->model('meal_instruction_log_h_model'); // 餐點異動記錄檔
    $this->load->database();
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
  //  函數名稱: produce_meal_order()
  //  函數功能: 建立訂單資料
  //  程式設計: Kiwi
  //  設計日期: 2021/4/7
  // **************************************************************************
  public function produce_meal_order()  {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_msg = 'ok';
    $data = NULL;
    $v = $this->input->post();
    $service_case_row = $this->service_case_model->get_all_by_sec03();
    
    $i = 0;
    $chk_meal_order_row = $this->meal_order_model->get_by_date($v['produce_date']);
    if(NULL != $chk_meal_order_row) {
      echo "今日訂單已產生過!!";
      return;
    }
    foreach($service_case_row as $ks => $vs) {
      if(NULL != $vs['sec03']) {
        if(strtotime($v['produce_date']) > strtotime($vs['sec03'])) { // 當生產日期大於結案日期，代表該案已結案了
          continue;
        }
      }
      // 用服務類型判斷出餐時段
      switch ($vs['sec04']) {   
        case 1:
        case 2:
          $mlo02 = 1;         
        break;  
        case 3:
          $mlo02 = 2;         
        break;  
        default:     
      }

      $data[$i]['b_date'] = date("Y-m-d H:i:s");
      $data[$i]['b_empno'] = $_SESSION['acc_s_num'];
      $data[$i]['ct_s_num'] = $vs['ct_s_num'];
      $data[$i]['sec_s_num'] = $vs['s_num'];
      $data[$i]['mlo01'] = $v['produce_date'];
      $data[$i]['mlo02'] = $mlo02;
      $i++;
    }
  
    if(NULL != $data) {
      if(!$this->meal_order_model->add_meal_order($data)) {
        $rtn_msg = $this->lang->line('add_ng'); // 新增失敗
      }
      else {
        $this->meal_order_date_type_model->save_meal_order_date_type();
      }
    }
    
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: client_hash()
  //  函數功能: 案主加密
  //  程式設計: Kiwi
  //  設計日期: 2021/7/10
  // **************************************************************************
  public function client_hash() {
    // $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    // $aes_fd = array('ct01','ct02','ct03','ct05','ct06_telephone','ct06_homephone','ct08','ct09','ct10','ct11','ct12','ct13','ct14','ct15',); // 加密欄位
    // $db_crypt_key2 = DB_CRYPT_KEY2;
    // $clients_row = $this->clients_model->get_all();
    // foreach ($clients_row as $k => $v) {
    //   foreach ($v as $kc => $vc) {
    //     if(in_array($kc , $aes_fd)) { // 加密欄位
    //       $this->db->set($kc, "AES_ENCRYPT('{$vc}','{$db_crypt_key2}')", FALSE);
    //       unset($clients_row[$k][$kc]);
    //     }
    //   } 
    //   $this->db->where('s_num', $v['s_num']);
    //   if(!$this->db->update($tbl_clients)) {
    //     $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    //   }
    // } 
    // print_r($this->db->last_query()); 
    return;
  }
  // **************************************************************************
  //  函數名稱: upd_ct16()
  //  函數功能: 案主加密
  //  程式設計: Kiwi
  //  設計日期: 2021/7/10
  // **************************************************************************
  public function upd_ct16() {
    // $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    // $sql = "select tw_clients_20210712.* from tw_clients_20210712";
    // $rs = $this->db->query($sql)->result_array();
    // foreach ($rs as $k => $v) {
    //   $this->db->where('s_num', $v['s_num']);
    //   if(!$this->db->update($tbl_clients , array("ct16"=>$v['ct16']))) {
    //     $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    //   }
    // } 
    return;
  }
  // **************************************************************************
  //  函數名稱: upd_client_data()
  //  函數功能: 案主加密
  //  程式設計: Kiwi
  //  設計日期: 2021/7/10
  // **************************************************************************
  public function upd_client_data() {
    $db_crypt_key2 = DB_CRYPT_KEY2;
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    foreach($this->clients_model->get_all() as $k => $v) {
      $this->db->where('s_num', $v['s_num']);
      if(!$this->db->update($tbl_clients)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
        u_var_dump($rtn_msg);
      }
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: data_hash()
  //  函數功能: 案主加密
  //  程式設計: Kiwi
  //  設計日期: 2021/7/10
  // **************************************************************************
  public function data_hash() {
    // $fd_name = $this->input->get("fd_name");
    // $this->load->model("{$fd_name}_model"); // 案主資料
    // $tbl_fd = $this->zi_init->chk_tbl_no_lang("{$fd_name}"); // 案主資料
    // $aes_fd = array('ct_name',); // 加密欄位
    // $db_crypt_key2 = DB_CRYPT_KEY2;
    // $model = "{$fd_name}_model";
    // $data_row = $this->$model->get_all();
    // foreach ($data_row as $k => $v) {
    //   foreach ($v as $kc => $vc) {
    //     if(in_array($kc , $aes_fd)) { // 加密欄位
    //       $this->db->set($kc, "AES_ENCRYPT('{$vc}','{$db_crypt_key2}')", FALSE);
    //       unset($data_row[$k][$kc]);
    //     }
    //   } 
    //   $this->db->where('s_num', $v['s_num']);
    //   if(!$this->db->update($tbl_fd)) {
    //     $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    //   }
    // } 
    return;
  }
  // **************************************************************************
  //  函數名稱: table_data_hash()
  //  函數功能: 案主加密
  //  程式設計: Kiwi
  //  設計日期: 2021/7/10
  // **************************************************************************
  public function table_data_hash() {
    // Mark by Tony 2022/2/11 已經執行完畢
    //$tbl_name = $this->input->get("tbl_name");
    //$tbl_fd = $this->zi_init->chk_tbl_no_lang("{$tbl_name}");
    //$sql = "select {$tbl_fd}.* from {$tbl_fd}";
    //$rs = $this->db->query($sql)->result_array();
    //$aes_fd = array('de01','de02','de03_phone','de03_email','de04_addr','de10','de12'); // 加密欄位
    //$db_crypt_key2 = DB_CRYPT_KEY2;
    //foreach ($rs as $k => $v) {
    //  foreach ($v as $kc => $vc) {
    //    if(in_array($kc , $aes_fd)) { // 加密欄位
    //      $this->db->set($kc, "AES_ENCRYPT('{$vc}','{$db_crypt_key2}')", FALSE);
    //      unset($rs[$k][$kc]);
    //    }
    //  } 
    //  $this->db->where('s_num', $v['s_num']);
    //  if(!$this->db->update($tbl_fd)) {
    //    $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    //  }
    //}
    return;
  }
  // **************************************************************************
  //  函數名稱: upd_other_change_auth()
  //  函數功能: 更新非餐食異動審核案主名稱未加密
  //  程式設計: Kiwi
  //  設計日期: 2021/08/02
  // **************************************************************************
  public function upd_other_change_auth() {
    // $db_crypt_key2 = DB_CRYPT_KEY2;
    // $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    // $other_change_row = $this->other_change_auth_model->get_all();
    // foreach ($other_change_row as $k => $v) {
    //   $clients_row = $this->clients_model->get_one($v['ct_s_num']);  
    //   $this->db->set("ct_name", "AES_ENCRYPT('{$clients_row->ct01}{$clients_row->ct02}','{$db_crypt_key2}')", FALSE);
    //   $this->db->where('s_num', $v['s_num']);
    //   if(!$this->db->update($tbl_other_change_auth)) {
    //     $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    //   }
    // } 
  }
  // **************************************************************************
  //  函數名稱: upd_dp()
  //  函數功能: 更新送餐員資料(補資料用)
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function upd_dp() {
    $this->route_model->upd_dp();
    return;
  }
  // **************************************************************************
  //  函數名稱: upd_other_change_log_route()
  //  函數功能: 更新路線異動資料
  //  程式設計: Kiwi
  //  設計日期: 2021/08/12
  // *************************************************************************
  public function upd_other_change_log_route() {
    $this->other_change_log_h_model->upd_other_change_log_route();
    return;
  }
  // **************************************************************************
  //  函數名稱: upd_meal_instruction()
  //  函數功能: 更新餐食異動紀錄表
  //  程式設計: Kiwi
  //  設計日期: 2021/09/09
  // *************************************************************************
  public function upd_meal_instruction() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_msg = 'ok';
    $produce_date = $_GET['produce_date'];
    $service_case_row = $this->service_case_model->get_all_by_sec03();
    foreach ($service_case_row as $k => $v) {
      $cnt = 0;
      $meal_log_arr = array(1,2,3,4,5);
      foreach ($meal_log_arr as $vm) {
        switch ($vm) {
          case 1: // 餐點異動
            $meal_instruction_log_m_row = $this->meal_instruction_log_h_model->get_last_m_by_s_num($v['s_num'] , $produce_date); // 取得最後一筆餐點異動資料
            if(NULL != $meal_instruction_log_m_row) {
              $cnt++;
            }
          break;  
          case 2: // 代餐異動
            $meal_instruction_log_mp_row = $this->meal_instruction_log_h_model->get_last_mp_by_s_num($v['s_num'] , $produce_date); // 取得最後一筆代餐異動資料
            if(NULL != $meal_instruction_log_mp_row) {
              $cnt++;
            }
          break;  
          case 3: // 停復餐異動
            $meal_instruction_log_s_row = $this->meal_instruction_log_h_model->get_last_s_by_s_num($v['s_num'] , $produce_date); // 取得最後一筆停餐異動資料
            if(NULL != $meal_instruction_log_s_row) {
              $cnt++;
            }
          break;  
          case 4: // 固定暫停
            $meal_instruction_log_p_row = $this->meal_instruction_log_h_model->get_last_p_by_s_num($v['s_num'] , $produce_date); // 取得最後一筆固定暫停資料
            if(NULL != $meal_instruction_log_p_row) {
              $cnt++;
            }
          break;  
          case 5: // 自費
            $meal_instruction_log_i_row = $this->meal_instruction_log_h_model->get_last_i_by_s_num($v['s_num'] , $produce_date); // 列出自費資料
            if(NULL != $meal_instruction_log_i_row) {
              $cnt++;
            }
          break;
        }
        $data[$k]['b_date'] = date("Y-m-d H:i:s");
        $data[$k]['b_empno'] = $_SESSION['acc_s_num'];
        $data[$k]['ct_s_num'] = $v['ct_s_num'];
        $data[$k]['sec_s_num'] = $v['s_num'];
        $data[$k]['ml_s_num'] = @$meal_instruction_log_m_row->ml_s_num;
        $data[$k]['mil_m01_1'] = @$meal_instruction_log_m_row->mil_m01_1;
        $data[$k]['mil_m01_2'] = @$meal_instruction_log_m_row->mil_m01_2;
        $data[$k]['mil_m01_3'] = @$meal_instruction_log_m_row->mil_m01_3;
        $data[$k]['mil_m01_4'] = @$meal_instruction_log_m_row->mil_m01_4;
        $data[$k]['mil_m01_5'] = @$meal_instruction_log_m_row->mil_m01_5;
        $data[$k]['mil_i01'] = @$meal_instruction_log_i_row->mil_i01;
        $data[$k]['mil_mp01'] = @$meal_instruction_log_mp_row->mil_mp01;
        $data[$k]['mil_mp01_type'] = @$meal_instruction_log_mp_row->mil_mp01_type;
        $data[$k]['mil_p01'] = @$meal_instruction_log_p_row->mil_p01;
        $data[$k]['mil_s01'] = @$meal_instruction_log_s_row->mil_s01;
        $data[$k]['count'] = $cnt;
      }
    } 
    $this->meal_instruction_log_h_model->upd_meal_instruction($data);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: upd_meal_instruction_p()
  //  函數功能: 更新餐表(補上班用)
  //  程式設計: Kiwi
  //  設計日期: 2021/09/10
  // *************************************************************************
  public function upd_meal_instruction_p() {
    $get_data = $this->input->get();
    if(NULL != $get_data['produce_date'] and NULL != $get_data['makeup_date']) {
      $type_arr = array("meal" , "send");
      foreach ($type_arr as $type) {
        $week_day = date("w", strtotime($get_data['makeup_date']));
        $upd_data = $this->daily_work_model->get_upd_data_by_date($type);
        foreach ($upd_data as $k => $v) {
          $meal_instruction_log_p_row = $this->meal_instruction_log_h_model->get_last_p_by_s_num($v['sec_s_num'] , $get_data['produce_date']); // 取得最後一筆固定暫停資料
          if(NULL != $meal_instruction_log_p_row) {
            $meal_instruction_log_p_row->mil_p01_arr = explode(",", $meal_instruction_log_p_row->mil_p01);
            if($meal_instruction_log_p_row->mil_p01_arr != NULL) {
              if(in_array($week_day , $meal_instruction_log_p_row->mil_p01_arr)) {
                if($type == 'meal') {
                  $upd_data[$k]['dyp10'] = "N"; // 是否停餐
                }
                else {
                  $upd_data[$k]['dys10'] = "N"; // 是否停餐
                }
              }
            }  
          }
        }
        $this->daily_work_model->upd_meal_instruction_p($type , $upd_data);
      }
    }
  }  
  // **************************************************************************
  //  函數名稱: upd_sec09()
  //  函數功能: 更新繳費方式
  //  程式設計: Kiwi
  //  設計日期: 2021/10/28
  // **************************************************************************
  function upd_sec09() {
    // $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    // $service_case_row = $this->service_case_model->get_all();
    // foreach ($service_case_row as $k => $v) {
    //   $clients_row = $this->clients_model->get_one($v['ct_s_num']);
    //   if(isset($clients_row->ct18)) {
    //     $upd_data['sec09'] = $clients_row->ct18;
    //     $this->db->where('s_num' , $v['s_num']);
    //     if(!$this->db->update($tbl_service_case , $upd_data)) {
    //       echo "更新失敗";
    //     }
    //   }
    // }
    // return;
  }
  // **************************************************************************
  //  函數名稱: client_identity()
  //  函數功能: 案主身分別寫入歷史檔
  //  程式設計: Kiwi
  //  設計日期: 2021/12/30
  // **************************************************************************
  public function client_identity() {
    // $ct_indentity = NULL;
    // $clients_row = $this->clients_model->get_all();
    // foreach ($clients_row as $k => $v) {
    //   $ct_indentity[$k]['b_empno'] = $_SESSION['acc_s_num']; 
    //   $ct_indentity[$k]['b_date'] = date("Y-m-d H:i:s"); 
    //   $ct_indentity[$k]['ct_s_num'] = $v['s_num']; 
    //   $ct_indentity[$k]['ct_il01'] = "2021-09-01"; 
    //   $ct_indentity[$k]['ct_il02'] = $v['ct34_go']; 
    // } 
    // $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別記錄檔
    // $this->db->insert_batch($tbl_clients_identity_log, $ct_indentity);
    // return;
  }
  // **************************************************************************
  //  函數名稱: download_file()
  //  函數功能: 檔案下載
  //  程式設計: Kiwi
  //  設計日期: 2021/06/05
  // **************************************************************************
  public function download_file($download_file_name, $server_file_name) {    
    $download_file_name = base64url_decode($download_file_name);
    $server_file_name = base64url_decode($server_file_name);
    $path = FCPATH."export_file/";
    $this->zi_my_func->download_file($download_file_name, "{$path}{$server_file_name}");
    return;
  }
    // **************************************************************************
  //  函數名稱: upd_institution_account()
  //  函數功能: 更新機構社工、送餐員帳號
  //  程式設計: Kiwi
  //  設計日期: 2023/12/31 !!!!! 跨年ㄟ
  // **************************************************************************
  public function upd_institution_account() {
    $instituion_code = INSTITUTION_CODE;
    $this->load->model('social_worker_model');   // 社工資料
    $this->load->model('delivery_person_model');   // 送餐員資料

    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $social_worker_arr = $this->social_worker_model->get_all_is_available();
    if(!empty($social_worker_arr)) {
      foreach($social_worker_arr as $k => $v) {
        $this->db->where("s_num", $v['s_num']);
        if(!$this->db->update($tbl_social_worker, array('sw_account' => "{$instituion_code}-{$v['sw05']}"))) {
          echo $v['s_num'];
        }
      }
    }

    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員基本資料檔
    $delivery_person_arr = $this->delivery_person_model->get_all_is_available();
    if(!empty($delivery_person_arr)) {
      foreach($delivery_person_arr as $k => $v) {
        $this->db->where("s_num", $v['s_num']);
        if(!$this->db->update($tbl_delivery_person, array('dp_account' => "{$instituion_code}-{$v['dp05']}"))) {
          echo $v['s_num'];
        }
      }
    }
  }
  
  function __destruct() {
    return;
  }
}
