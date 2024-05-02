<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_job extends CI_Controller { // 帳號
  public function __construct() {
    parent::__construct();
    switch(ENVIRONMENT) {
      case 'development':
      case 'testing':
        break;
      case 'production':
        if(!$this->input->is_cli_request()) {
          die('無法在瀏覽器使用!!!');
        }
        break;
    }
    $this->load->model('sys_sendmail_model'); // 發信排程
    $this->load->model('donate_model'); // 捐款資料
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
  //  函數名稱: count_db()
  //  函數功能: 計算DB資料筆數
  //  程式設計: Kiwi
  //  設計日期: 2023/12/13
  // **************************************************************************
  public function count_db() {
    return;
    
    $tables = $this->db->list_tables();

    $rtn_msg = "<table>";
    $rtn_msg .= "  <tbody>";

    foreach ($tables as $table) {
      $sql = "SELECT count(*) as cnt FROM {$table}";
      $row = $this->db->query($sql)->result();
      $rtn_msg .= "<tr>";
      $rtn_msg .= "  <td>{$table}</td>";
      $rtn_msg .= "  <td>{$row[0]->cnt}</td>";
      $rtn_msg .= "</tr>";
    }

    $rtn_msg .= "  </tbody>";
    $rtn_msg .= "</tbody>";
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: send_mail()
  //  函數功能: 發送 sendmail_crontab 中的mail，
  //  程式設計: Tony
  //  設計日期: 2018/4/24
  // **************************************************************************
  public function send_mail() {
    set_time_limit(1800); // 30 分鐘，最大執行時間
    $mail_row = $this->sys_sendmail_model->get_not_send_mail();
    if(NULL==$mail_row) { // 沒資料就不處理
      return;
    }
    $send_result = '';
    $this->load->library('email');
    $this->load->config('email');
    $email_set = $this->config->item('email');
    $this->email->initialize($this->config->item('email'));
    $sleep = 1;
    foreach($mail_row as $k => $v) {
      echo $v['send_to'].'___';
      // 這裡要看 mailserver的容忍度，再做調整
      //if($sleep%50==0) { // 發50封休息3秒
      //  echo 'sleep...';
      //  sleep(3);
      //}
      $this->email->from(MAIL_FROM_NAME, MAIL_FROM_MAIL);
      //$this->email->from('hstony@ms7.hinet.net', 'Tony-test'); // Tony Test
      $this->email->to($v['send_mail']);
      $this->email->subject($v['send_title']);
      $this->email->message($v['send_content']);
      if(!$this->email->send()) { // 發送失敗
        $send_result = $this->email->print_debugger();
      }
      else {
        $send_result = 'ok';
      }
      $this->sys_sendmail_model->save_upd_send_result($v['s_num'],$send_result);
      $sleep++;
    }
    return;
  }     
  // **************************************************************************
  //  函數名稱: upd_donate_de19()
  //  函數功能: 更新donate資料(連接api)
  //  程式設計: Kiwi
  //  設計日期: 2022/01/25
  // **************************************************************************
  //public function upd_donate_de19() {
  //  $this->load->library('npmpay'); 
  //  $donate_row = $this->donate_model->que_by_de19($de19=3);
  //  if(NULL != $donate_row) {
  //    $res_data = NULL;
  //    foreach ($donate_row as $k => $v) {
  //      $npmpay_data = $this->curl_numpy($v['s_num'], $v['de06']);    
  //      if(NULL != $npmpay_data) {
  //        // u_var_dump($npmpay_data);
  //        $res_data[$k]['s_num'] = $v['s_num'];
  //        $res_data[$k]['e_date'] = date("Y-m-d H:i:s");
  //        $res_data[$k]['de20_status'] = $npmpay_data['Status']; // 藍新回傳狀態
  //        $res_data[$k]['de20_memo'] = $npmpay_data['Message'];  // 藍新回傳訊息
  //        if("SUCCESS" == $npmpay_data['Status']) { // 如果查詢不等於SUCCESS
  //          switch ($npmpay_data['Result']['TradeStatus']) {
  //            case 0: // 未付款
  //              $de19 = 3;
  //              break;
  //            case 1: // 付款成功
  //              $de19 = 1;
  //              break;
  //            case 2: // 付款失敗
  //            case 3: // 取消付款
  //              $de19 = 2;
  //              break;    
  //          }
  //          if("0000-00-00 00:00:00" != $npmpay_data['Result']['PayTime']) { // 付款時間
  //            $upd_data[$k]['de17'] = $npmpay_data['Result']['PayTime'];
  //          }
  //          $res_data[$k]['de18'] = $npmpay_data['Result']['TradeNo']; // 藍星交易號碼
  //          $res_data[$k]['de19'] = $de19;
  //        }
  //        else { 
  //          $res_data[$k]['de19'] = 2; // 捐款失敗
  //        }
  //      }                      
  //    }
  //    if(NULL != $res_data) {
  //      $this->donate_model->save_res_data($res_data);
  //      return;
  //    }
  //  }
  //}
  // **************************************************************************
  //  函數名稱: upd_donate_de19()
  //  函數功能: 更新donate資料
  //  程式設計: shirley
  //  設計日期: 2022/02/15
  // **************************************************************************
  public function upd_donate_de19() {
    $this->load->library('npmpay'); 
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $donate_row = $this->donate_model->que_by_de19($de19=3); // 取得等待入帳捐款資料
    $today = date('Y-m-d H:i:s'); // 系統日期
    $now = strtotime(date('Y-m-d')); // 系統日期
    $date = strtotime(date('Y-m-d H:i:s'). ' -10 days'); // 系統時間前10天
    // $first_day = strtotime(date('Y-m-01')); // 每個月第一天
    $rtn_msg = "{$today}</br>";
    if(NULL != $donate_row) {
      $res_data = NULL;
      foreach ($donate_row as $k => $v) {
        if(($v['de18'] == NULL) and ($v['de19'] == 3) and ($date > strtotime($v['b_date']))){ // 如果10天前等待入帳資料未付款，則更新為付款失敗
          $data['e_date'] = date('Y-m-d H:i:s');
          $data['de19'] = 2;
          $this->db->where('s_num',$v['s_num']);
          if(!$this->db->update($tbl_donate, $data)) { // 修改失敗!!
            $rtn_msg .= $this->lang->line('upd_ng'); // 修改失敗!!
            $rtn_msg .= ",s_num={$v['s_num']},de19={$v['de19']}</br>";
          }else{
            $rtn_msg .= "s_num={$v['s_num']},de19={$v['de19']}</br>";
          }
        }
      }
    }
    echo $rtn_msg;
  }
  // **************************************************************************
  //  函數名稱: curl_numpy()
  //  函數功能: 查詢藍新交易紀錄
  //  程式設計: Kiwi
  //  設計日期: 2022/01/25
  // **************************************************************************
  public function curl_numpy($order_no, $amt) {
    $this->load->library('npmpay'); 
    $check_code = ['IV'              => HASH_IV,
                   'Amt'             => $amt,
                   'MerchantID'      => MERCHANT_ID,
                   'MerchantOrderNo' => $order_no,
                   'Key'             => HASH_KEY];
    $check_code_str = strtoupper(hash('sha256', http_build_query($check_code)));

    $que_data = ["MerchantID" => MERCHANT_ID,
                 "Version" => "1.3",
                 "RespondType" => "JSON",
                 "CheckValue" => $check_code_str,
                 "TimeStamp" => time(),
                 "MerchantOrderNo" => $order_no,
                 "Amt" => $amt];
   
    $que_url = "https://ccore.newebpay.com/API/QueryTradeInfo"; // 查詢網址
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $que_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $que_data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 超重要!!兩個晚上!!規避ssl檢查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 超重要!!兩個晚上!!跳過host驗證
    $result = curl_exec($ch);  
    curl_close($ch);
    return json_decode($result, true);
  }
  // **************************************************************************
  //  函數名稱: test()
  //  函數功能: 測試
  //  程式設計: Kiwi
  //  設計日期: 2022-02-14
  // **************************************************************************
  public function test() {   
    // pcntl_async_signals(true);
    // $server = '192.168.108.191';
    // $port = 1883;                   
    // $channel = "sensor/Test/room2";
    // $client_id = 'Ltcare-Backend-Publisher'; // make sure this is unique for connecting to sever - you could use uniqid()

    // $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $client_id);
    // $mqtt->connect();
    // $mqtt->subscribe($channel, function ($topic, $message) {
    //     echo sprintf("Received message on topic [%s]: %s\n", $topic, $message);
    // }, 0);
    // $mqtt->loop(true);
    // $mqtt->disconnect();
    // return;
  }
  // **************************************************************************
  //  函數名稱: t1()
  //  函數功能: test
  //  程式設計: Tony
  //  設計日期: 2018/4/24
  // **************************************************************************
  public function t1() {
  }

  // **************************************************************************
  //  函數名稱: import_verification_person_data()
  //  函數功能: 匯入核備人員資料
  //  程式設計: Kiwi
  //  設計日期: 2023/1/4
  // **************************************************************************
  public function import_verification_person_data() {
    $nation_file = FCPATH."upload_files/verification_person.xlsx";
    $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($nation_file);
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
    $objSpreadsheet = $reader->load($nation_file);
    $last_row = $objSpreadsheet->getActiveSheet()->getHighestRow(); // e.g. 10

    $add_nation_data = NULL;
    for($row = 2; $row <= $last_row; $row++) {
      $vp_name = $objSpreadsheet->getActiveSheet()->getCell("B{$row}")->getValue();
      $vp04_str = $objSpreadsheet->getActiveSheet()->getCell("C{$row}")->getValue(); // 性別
      $vp03 = $objSpreadsheet->getActiveSheet()->getCell("D{$row}")->getValue(); // 身分證

      $vp01 = mb_substr($vp_name, 0, 1, 'UTF-8');
      $vp02 = mb_substr($vp_name, 1, mb_strlen($vp_name), 'UTF-8');

      $vp04 = '';      
      if("男" == $vp04_str) {
        $vp04 = "M";
      }
      else {
        $vp04 = "F";
      }

      $add_nation_data[$row]['b_empno'] = 1;
      $add_nation_data[$row]['b_date'] = date("Y-m-d H:i:s");
      $add_nation_data[$row]['is_available'] = 1;
      $add_nation_data[$row]['vp01'] = $vp01;
      $add_nation_data[$row]['vp02'] = $vp02;
      $add_nation_data[$row]['vp03'] = $vp03;
      $add_nation_data[$row]['vp04'] = $vp04;
    }

    if(NULL != $add_nation_data) {
      $db_crypt_key2 = DB_CRYPT_KEY2;
      $aes_fd = array('vp01','vp02','vp03');
      $tbl_verification_person = $this->zi_init->chk_tbl_no_lang('verification_person'); // verification person
      foreach ($add_nation_data as $k => $v) {
        foreach ($v as $k_fd_name => $v_data) {
          if(in_array($k_fd_name, $aes_fd)) { // 加密欄位
            $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$db_crypt_key2}')", FALSE);
            unset($add_nation_data[$k][$k_fd_name]);
          }
        }
        if(!$this->db->insert($tbl_verification_person, $add_nation_data[$k])) {
          die("匯入核備人員資料失敗");
        }
      }
      echo "匯入核備人員資料成功";
    }
  }

  // **************************************************************************
  //  函數名稱: del_folder_file()
  //  函數功能: 固定清理資料夾的檔案
  //  程式設計: Kiwi
  //  設計日期: 2022/9/23
  // **************************************************************************
  public function del_folder_file($folder_name='export_file') {
    $files = glob(FCPATH."{$folder_name}/*"); // get all file names
    foreach($files as $file) {
      if(is_file($file)) {
        unlink($file); // delete file
      }
    }
  }
  // **************************************************************************
  //  函數名稱: upd_donate_de10()
  //  函數功能: 更新donate 收據抬頭資料
  //  程式設計: shirley
  //  設計日期: 2022/12/23
  // **************************************************************************
  public function upd_donate_de10() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $donate_row = $this->donate_model->get_all(); // 取得捐款資料
    $rtn_msg = NULL;
    $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
    $aes_fd = array('de01','de02','de03_phone','de03_email','de04_addr','de10','de12'); // 加密欄位
    if(NULL != $donate_row) {
      foreach ($donate_row as $k => $v) {
        $data['e_date'] = date('Y-m-d H:i:s');
        $data['de10'] = $v['de01'].$v['de02'];
        // 加密欄位處理 Begin //
        foreach ($data as $k_fd_name => $v_data) {
          if(in_array($k_fd_name,$aes_fd)) { // 加密欄位
            $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$db_crypt_key2}')", FALSE);
            unset($data[$k_fd_name]);
          }
        } 
        // 加密欄位處理 End //
        $this->db->where('s_num',$v['s_num']);
        if(!$this->db->update($tbl_donate, $data)) { // 修改失敗!!
          $rtn_msg .= $this->lang->line('upd_ng'); // 修改失敗!!
          $rtn_msg .= "s_num={$v['s_num']},de10={$data['de10']}</br>";
        }else{
          $rtn_msg .= $this->lang->line('upd_ok'); // 修改成功!!
          $rtn_msg .= "s_num={$v['s_num']}</br>";
        }
      }
    }
    echo $rtn_msg;
  }
  
  function __destruct() {
    return;
  }
}
