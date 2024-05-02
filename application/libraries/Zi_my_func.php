<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zi_my_func {

  public function __construct() {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  // **************************************************************************
  //  函數名稱: meal_num_item()
  //  函數功能: 參數統計表陣列
  //  程式設計: Kiwi
  //  設計日期: 2021/11/24
  // **************************************************************************
  function meal_num_item() {
    $set_data_row[4]['arr_id'] = 'mlo03'; 
    $set_data_row[4]['arr_num'] = 10; // 半流
    $set_data_row[5]['arr_id'] = 'mlo03';
    $set_data_row[5]['arr_num'] = 11; // 小半流
    $set_data_row[6]['arr_id'] = 'mlo03';
    $set_data_row[6]['arr_num'] = 3; // 粥
    $set_data_row[7]['arr_id'] = 'mlo03';
    $set_data_row[7]['arr_num'] = 15; // 素粥
    $set_data_row[8]['arr_id'] = 'mlo03';
    $set_data_row[8]['arr_num'] = 16; // ★★粥
    $set_data_row[9]['arr_id'] = 'mlo03';
    $set_data_row[9]['arr_num'] = 2; // 大粥
    $set_data_row[10]['arr_id'] = 'mlo04_1';
    $set_data_row[10]['arr_num'] = 2; // 鐵盒子
    $set_data_row[11]['arr_id'] = 'mlo03';
    $set_data_row[11]['arr_num'] = 14; // 大飯碗
    $set_data_row[12]['arr_id'] = 'mlo03';
    $set_data_row[12]['arr_num'] = 13; // ★★素食
    $set_data_row[13]['arr_id'] = 'mlo03';
    $set_data_row[13]['arr_num'] = 4; // 素食
    $set_data_row[14]['arr_id'] = 'mlo03';
    $set_data_row[14]['arr_num'] = 12; // ★★
    $set_data_row[15]['arr_id'] = 'mlo03';
    $set_data_row[15]['arr_num'] = 8; // 一般餐
    $set_data_row[16]['arr_id'] = 'mlo04_2';
    $set_data_row[16]['arr_num'] = 0; // 軟質
    $set_data_row[17]['arr_id'] = 'mlo04_2';
    $set_data_row[17]['arr_num'] = 1; // 細軟
    $set_data_row[18]['arr_id'] = 'mlo04_2';
    $set_data_row[18]['arr_num'] = 3; // O
    $set_data_row[19]['arr_id'] = 'mlo04_2';
    $set_data_row[19]['arr_num'] = 4; // X
    $set_data_row[20]['arr_id'] = 'mlo04_2';
    $set_data_row[20]['arr_num'] = 5; // XX
    $set_data_row[21]['arr_id'] = 'mlo04_2';
    $set_data_row[21]['arr_num'] = 6; // XXX
    $set_data_row[22]['arr_id'] = 'mlo04_2';
    $set_data_row[22]['arr_num'] = 2; // 飯分
    $set_data_row[23]['arr_id'] = 'mlo04_1';
    $set_data_row[23]['arr_num'] = 0; // 飯糰
    $set_data_row[24]['arr_id'] = 'mlo04_3';
    $set_data_row[24]['arr_num'] = 2; // 忌豬
    $set_data_row[25]['arr_id'] = 'mlo04_3';
    $set_data_row[25]['arr_num'] = 1; // 忌雞
    $set_data_row[26]['arr_id'] = 'mlo04_3';
    $set_data_row[26]['arr_num'] = 0; // 忌魚
    $set_data_row[27]['arr_id'] = 'other';
    $set_data_row[27]['arr_num'] = 2; // ★★忌豬
    $set_data_row[28]['arr_id'] = 'other';
    $set_data_row[28]['arr_num'] = 1; // ★★忌雞
    $set_data_row[29]['arr_id'] = 'other';
    $set_data_row[29]['arr_num'] = 0; // ★★忌魚
    $set_data_row[30]['arr_id'] = 'mlo04_5';
    $set_data_row[30]['arr_num'] = 0; // 低普林
    $set_data_row[31]['arr_id'] = 'mlo04_5';
    $set_data_row[31]['arr_num'] = 1; // 糖餐
    $set_data_row[32]['arr_id'] = 'mlo04_5';
    $set_data_row[32]['arr_num'] = 2; // 低油
    $set_data_row[33]['arr_id'] = 'mlo04_5';
    $set_data_row[33]['arr_num'] = 3; // 低蛋白
    $set_data_row[34]['arr_id'] = 'mlo04_5';
    $set_data_row[34]['arr_num'] = 4; // 低碘
    $set_data_row[35]['arr_id'] = 'mlo04_4';
    $set_data_row[35]['arr_num'] = 0; // 僅白飯
    $set_data_row[36]['arr_id'] = 'mlo04_4';
    $set_data_row[36]['arr_num'] = 1; // 忌麵
    $set_data_row[37]['arr_id'] = 'mlo04_1';
    $set_data_row[37]['arr_num'] = 1; // 初115素
    return $set_data_row;
  }

  // **************************************************************************
  //  函數名稱: download_str()
  //  函數功能: 產生回傳的下載字串
  //  程式設計: Kiwi
  //  設計日期: 2022/1/8
  // **************************************************************************
  public function download_str($ch_file_name, $en_file_name, $time_diff=NULL) {
    $rtn_str = '';
    $ch_file_name_en = base64url_encode("{$ch_file_name}");
    $en_file_name_en = base64url_encode("{$en_file_name}");
    $be_url = base_url()."lt_care_tool/download_file/{$ch_file_name_en}/";
    $rtn_str .= "<table class='table table-bordered table-hover'>";
    $rtn_str .= "  <thead>";
    $rtn_str .= "    <tr>";
    $rtn_str .= "      <th width='20%'>項目</th>";
    $rtn_str .= "      <th width='80%'>說明</th>";
    $rtn_str .= "    </tr>";
    $rtn_str .= "  </thead>";
    $rtn_str .= "  <tbody>";
    $rtn_str .= "    <tr>";
    $rtn_str .= "      <td>資料下載</td>"; // {$subsidy_filename}&nbsp; 
    $rtn_str .= "      <td>{$ch_file_name}&nbsp <button class='btn btn-C3 btn-sm' type='button' onclick='location.href=\"{$be_url}{$en_file_name_en}\"'>檔案下載</button></td>";
    $rtn_str .= "    </tr>";
    if(NULL != $time_diff) {
      $rtn_str .= "    <tr>";
      $rtn_str .= "      <td>處理時間</th>";
      $rtn_str .= "      <td>{$time_diff}</td>";
      $rtn_str .= "    </tr>";
    }
    $rtn_str .= "  </tbody>";
    $rtn_str .= "</table>";
    return $rtn_str;
  }

  // **************************************************************************
  //  函數名稱: download_file()
  //  函數功能: 下載檔案
  //  程式設計: Tony
  //  設計日期: 2021/1/29
  // **************************************************************************
  public function download_file($file_name = NULL, $file=NULL) {
    $file = urldecode($file);
    //$file = base64_decode($file);   
    //header('Content-Disposition: attachment; filename*="' . basename($file) . '"');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Type: application/octet-stream; charset= utf-8');
    header('Content-Length: ' . filesize($file));
    header("Pragma: no-cache");
    header("Expires: 0");
    ob_get_clean();
    echo file_get_contents($file);
    //echo file_get_contents($file,FILE_USE_INCLUDE_PATH);
    // ob_end_flush();
    return;
  }

  // **************************************************************************
  //  函數名稱: send_sms()
  //  函數功能: 發送簡訊(使用三竹資訊API)
  //  程式設計: Tony
  //  設計日期: 2020/2/13
  // **************************************************************************
  public function send_sms($kind=NULL,$dstaddr=NULL,$smbody=NULL,$msgid=NULL) {
    $rtn_msg = 'ok';
    $rtn_status = array('*' => '系統發生錯誤，請聯絡三竹資訊窗口人員',
                        'a' => '簡訊發送功能暫時停止服務，請稍候再試',
                        'b' => '簡訊發送功能暫時停止服務，請稍候再試',
                        'c' => '請輸入帳號',
                        'd' => '請輸入密碼',
                        'e' => '帳號、密碼錯誤',
                        'f' => '帳號已過期',
                        'h' => '帳號已被停用',
                        'k' => '無效的連線位址',
                        'm' => '必須變更密碼，在變更密碼前，無法使用簡訊發送服務',
                        'n' => '密碼已逾期，在變更密碼前，將無法使用簡訊發送服務',
                        'p' => '沒有權限使用外部Http程式',
                        'r' => '系統暫停服務，請稍後再試',
                        's' => '帳務處理失敗，無法發送簡訊',
                        't' => '簡訊已過期',
                        'u' => '簡訊內容不得為空白',
                        'v' => '無效的手機號碼',
                        '0' => '預約傳送中',
                        '1' => '已送達業者',
                        '2' => '已送達業者',
                        '3' => '已送達業者',
                        '4' => '已送達手機',
                        '5' => '內容有錯誤',
                        '6' => '門號有錯誤',
                        '7' => '簡訊已停用',
                        '8' => '逾時無送達',
                        '9' => '預約已取消'
                       );
    $data = '';

    switch ($kind) {
      case "send": // 發送
        $url = SMS_API_URL.'SmSend?';
        $url  .=  'CharsetURL=UTF-8';
        $data .= '&username='.SMS_API_USERNAME;
        $data .= '&password='.SMS_API_PASSWORD;
        $data .= "&dstaddr={$dstaddr}";
        $data .= "&smbody={$smbody}";
        break;
      case "que": // 查詢
        $url = SMS_API_URL.'SmQuery?';
        $url .= "&msgid={$msgid}";
        $url .= '&username='.SMS_API_USERNAME;
        $url .= '&password='.SMS_API_PASSWORD;
        $url .= "&Encoding_PostIn=UTF-8";
        break;
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded")); 
    curl_setopt($curl, CURLOPT_POST, 1); 
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $output = curl_exec($curl);
    curl_close($curl);
    
    switch ($kind) {
      case "send": // 發送
        $sms_data = preg_split('/\n/', $output);
        $msgid_rs = $sms_data[1];
        $msgid_rs_len = strlen($msgid_rs);
        $msgid = substr($msgid_rs,6,$msgid_rs_len-6);
        // var_dump($msgid);
        // $this->send_sms('que',NULL,NULL,$msgid);
        break;
      case "que": // 查詢
        $sms_data = preg_split('/\t/', $output);
        $sms_que_status = (int)$sms_data[1];
        if($sms_que_status >= 5) {
          $rtn_msg = $rtn_status[$sms_que_status];
        }
        break;
    }
    // u_var_dump(SMS_API_URL);
    //echo $rtn_msg;
    return($rtn_msg);
  }

  // **************************************************************************
  //  函數名稱: web_upd_data()
  //  函數功能: 及時地圖更新資料
  //  程式設計: Kiwi
  //  設計日期: 2022-02-14
  // **************************************************************************
  public function web_api_data($fd_type, $fd_msel=NULL) {  
    $api_data = NULL;
    $api_seg = $fd_type; 

    switch ($fd_type) {
      // 每日配送單
      case 'daily_shipment':
        $api_data = array();
        $this->CI->load->model('daily_work_model');
        $send_data = $this->CI->daily_work_model->get_all_send_data(date("Y-m-d"));
        if(NULL != $send_data) {
          foreach ($send_data as $k => $v) {
            if(strpos($v['ct_name'], '志工') === false) {
              array_push($api_data, $v);
            }
          }
        }
        break;
      // 送餐員資料
      case 'delivery_person':
        if(!isset($_POST['s_num'])) {
          return;
        }
        $dp_s_num = $_POST['s_num'];
        $this->CI->load->model('delivery_person_model');
        if('del' != $fd_msel) {
          $dp_row = $this->CI->delivery_person_model->get_one($dp_s_num);
          if(NULL != $dp_row) {
            $api_data['type'] = $fd_msel;
            $api_data["s_num"] = $dp_row->s_num;
            $api_data["dp01"]  = $dp_row->dp01;
            $api_data["dp02"]  = $dp_row->dp02;
            $api_data["dp_img"] = $dp_row->dp12;
            $api_data["dp_reason"] = $dp_row->dp13;
            $api_data["dp_nickname"] = $dp_row->dp11;
            $api_data["dp_experience"] = $dp_row->dp14;
          }
        }
        else {
          $api_data['s_num'] = $dp_s_num;
        }
        $api_data['type'] = $fd_msel;
        break;
      // 案主資料
      case 'client':
        if(!isset($_POST['s_num'])) {
          return;
        }
        $ct_s_num = $_POST['s_num'];
        $this->CI->load->model('clients_model');
        if('del' != $fd_msel) {
          $clients_row = $this->CI->clients_model->get_one($ct_s_num);
          $api_data['type'] = $fd_msel;
          $api_data['s_num'] = $ct_s_num;
          $api_data["ct_name"] = "{$clients_row->ct01}{$clients_row->ct02}";
          $api_data["ct_lastname"] = "{$clients_row->ct01}";
          $api_data['ct_address'] = "{$clients_row->ct12}{$clients_row->ct13}{$clients_row->ct14}{$clients_row->ct15}";
          $api_data['ct_lon'] = $clients_row->ct17;
          $api_data['ct_lat'] = $clients_row->ct16;
          $api_data['ct_gender'] = $clients_row->ct04;
          $api_data['status'] = $clients_row->is_available;
        }
        else {
          $api_data['s_num'] = $ct_s_num;
        }
        $api_data['type'] = $fd_msel;
        break;
      // 路徑資料
      case 'client_route':
        if(!isset($_POST['s_num'])) {
          return;
        }
        $reh_s_num = $_POST['s_num'];
        $api_data['client_route'] = array();
        $this->CI->load->model('route_model'); // 路徑資料
        if('del' != $fd_msel) {
          $reh_row = $this->CI->route_model->get_one($reh_s_num);
          $reb_row = $this->CI->route_model->get_route_b($reh_s_num);
          $api_data['type'] = $fd_msel;
          $api_data['route']["s_num"] = $reh_row->s_num;
          $api_data['route']["reh_name"] = $reh_row->reh02;
          $api_data['route']["reh_category"] = $reh_row->reh03;
          $api_data['route']["reh_time"] = $reh_row->reh05;
          if(NULL != $reb_row) {
            foreach ($reb_row as $k => $v) {
              $client_route["ct_s_num"] = $v['ct_s_num'];
              $client_route["ct_order"] = $v['reb01'];
              $client_route["reh_s_num"] = $v['reh_s_num'];
              array_push($api_data['client_route'], $client_route);
            }
          }
        }
        else {
          $api_data['s_num'] = $reh_s_num;
        }
        $api_data['type'] = $fd_msel;
        break;
    }
    
    $ch = curl_init();
    $que_url = MAP_API_URL."{$api_seg}";
    curl_setopt($ch, CURLOPT_URL, $que_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout in seconds
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 超重要!!兩個晚上!!規避ssl檢查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 超重要!!兩個晚上!!跳過host驗證
    $response = curl_exec($ch);

    // 查BUG用
    // $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    // $headers = substr($response, 0, $header_size);
    // $body = substr($response, $header_size);

    // if(curl_exec($ch) === false) {
    //   echo 'Errno:'.curl_error($ch);//捕抓異常
    // }
    // else {
    //   echo "OK";
    // }
  
    curl_close($ch);
    return;
  }
  // **************************************************************************
  //  函數名稱: token_encrypt()
  //  函數功能: 加密字串
  //  程式設計: Kiwi
  //  設計日期: 2022/5/26
  // **************************************************************************
  function token_encrypt($s_num) {
    $this->CI->load->library('encryption');
    $rand_num = rand(2,6);
    $time = date('U');
    $time_en = substr($this->CI->encryption->encrypt($time),0,4); // 取4碼當混亂使用
    $verify_s_num = $s_num;
    $verify_s_num_en = '';
    for($i = 0; $i < strlen($verify_s_num); $i++) {
      $verify_s_num_en .= substr($verify_s_num,$i,1).random_string('alnum', $rand_num);
    }
    return "{$time_en}{$rand_num}".base64url_encode($verify_s_num_en);
  }
  // **************************************************************************
  //  函數名稱: mqtt_data()
  //  函數功能: 傳送mqtt資料
  //  程式設計: Kiwi
  //  設計日期: 2022-05-24
  // **************************************************************************
  public function mqtt_data($mqtt_data) {    
    if(NULL == $mqtt_data) {
      return;
    }
  
    $client_id = 'Ltcare-Backend-Publisher'; // make sure this is unique for connecting to sever - you could use uniqid()
    switch(ENVIRONMENT) {
      case 'development':
      case 'testing':
        $server = '192.168.108.191';     // change if necessary
        $port = 1883;                     // change if necessary
        $channel = "sensor/Test/room4";
        break;
      case 'production':
        $server = '61.218.250.30';     // change if necessary
        $port = 4220;                     // change if necessary
        $channel = "sensor/Test/room2";
        break;
    }

    $mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $client_id);
    $mqtt->connect();
    foreach ($mqtt_data as $json_str) {
      $mqtt->publish($channel, $json_str, 0);
    }
    $mqtt->disconnect();

  }

  // **************************************************************************
  //  函數名稱: img_to_base64()
  //  函數功能: image to base64
  //  程式設計: Kiwi
  //  設計日期: 2021/02/20
  // **************************************************************************
  function img_to_base64($type , $file_name) {
    $base64 = NULL;
    if(NULL != $file_name) {
      $path = FCPATH . "upload_files/{$type}/{$file_name}";
      if(!file_exists($path)) {
        $path = FCPATH . "pub/be/images/no-image-icon.png";
      }
      $file_type = pathinfo($path, PATHINFO_EXTENSION); // 獲取檔案類型
      $data = base64_encode(file_get_contents($path));
      $base64 = "data:image/{$file_type};base64,{$data}";
    }
    return $base64;
  }

  function __destruct() {
  }
}
?>