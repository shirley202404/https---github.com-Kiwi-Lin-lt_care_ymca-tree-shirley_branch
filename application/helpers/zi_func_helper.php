<?php
// **************************************************************************
//  函數名稱: pub_url()
//  函數功能: pub的資料夾位置,可帶參數,就是目錄的名稱
//  程式設計: Tony
//  設計日期: 2018/4/24
// **************************************************************************
function pub_url($kind = 'be') {
  return base_url() . "pub/{$kind}";
}
// **************************************************************************
//  函數名稱: be_url()
//  函數功能: 後台的位置
//  程式設計: Tony
//  設計日期: 2018/4/24
// **************************************************************************
function be_url() {
  return base_url() . 'be/';
}
// **************************************************************************
//  函數名稱: enter_url()
//  函數功能: 前台的位置
//  程式設計: Tony
//  設計日期: 2018/4/24
// **************************************************************************
function front_url() {
  return base_url();
}
// **************************************************************************
//  函數名稱: chk_front()
//  函數功能: 針對前台下架資料不顯示
//  程式設計: Tony
//  設計日期: 2018/4/24
// **************************************************************************
function chk_front() {
  $vfront = 'N';
  $vwhere = "";
  if(!strstr(uri_string(), 'be/')) {
    $vfront = 'Y';
    $vwhere = " and is_available=1 ";
  }
  return array($vfront, $vwhere);
}
// **************************************************************************
//  函數名稱: u_var_dump()
//  函數功能: var_dump() 顯示
//  程式設計: Tony
//  設計日期: 2018/4/24
// **************************************************************************
function u_var_dump($vstr) {
  ini_set("xdebug.overload_var_dump", 0);
  ini_set("xdebug.var_display_max_data", -1);
  echo '<pre>';
  var_dump($vstr);
  echo '</pre>';
  echo '<hr>';
}
// **************************************************************************
//  函數名稱: sort_by_sch_num()
//  函數功能: 二維陣列排序，學校代號
//  程式設計: 網路上找的
//  設計日期: 2018/4/24
// **************************************************************************
function sort_by_sch_num($a, $b) {
  if($a['sch_num'] == $b['sch_num']) return 0;
  return ($a['sch_num'] > $b['sch_num']) ? 1 : -1;
}
// **************************************************************************
//  函數名稱: is_footer_not_show()
//  函數功能: 表尾不顯示，基本上用ajax呼叫的會用到
//  程式設計: Tony
//  設計日期: 2018/5/9
// **************************************************************************
function is_footer_not_show($url) {
  foreach($url as $k => $v) {
    if($v == uri_string()) {
      return true;
    }
    if($v == substr(uri_string(), 0, strlen($v))) {
      return true;
    }
  }
  return false;
}
// **************************************************************************
//  函數名稱: replace_symbol_text()
//  函數功能: 將字串部分內容替換成星號或其他符號
//  函數說明:
// * @param string $string 原始字串
// * @param string $symbol 替換的符號
// * @param int $begin_num 顯示開頭幾個字元
// * @param int $end_num 顯示結尾幾個字元
// * return string
//  程式設計: Tony
//  設計日期: 2021/4/22
// **************************************************************************
function replace_symbol_text($string,$symbol,$begin_num = 0,$end_num = 0){
  $string_length = mb_strlen($string);
  $begin_num = (int)$begin_num;
  $end_num = (int)$end_num;
  $string_middle = '';

  $check_reduce_num = $begin_num + $end_num;

  if($check_reduce_num >= $string_length){
    for ($i=0; $i < $string_length; $i++) {
      $string_middle .= $symbol;
    }
    return $string_middle;
  }

  $symbol_num = $string_length - ($begin_num + $end_num);
  $string_begin = mb_substr($string, 0,$begin_num);
  $string_end = mb_substr($string, $string_length-$end_num);

  for ($i=0; $i < $symbol_num; $i++) {
    $string_middle .= $symbol;
  }

  return $string_begin.$string_middle.$string_end;
}
// **************************************************************************
//  函數名稱: base64url_encode()
//  函數功能: base 加密去除特殊字元
//  程式設計: Tony
//  設計日期: 2021/3/27
// **************************************************************************
function base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
// **************************************************************************
//  函數名稱: base64url_decode()
//  函數功能: base 解密去除特殊字元
//  程式設計: Tony
//  設計日期: 2021/3/27
// **************************************************************************
function base64url_decode($data) {
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
// **************************************************************************
//  函數名稱: token_descry()
//  函數功能: base 解密去除特殊字元
//  程式設計: Tony
//  設計日期: 2021/3/27
// **************************************************************************
function token_descry() {
  $headers = apache_request_headers();
  $token = explode("Token " , $headers["Authorization"]);
  $identity = $headers["Identity"];

  $s_num = NULL;
  if(isset($token[1])) {
    $token_str = $token[1];
    $CI = & get_instance();

    switch ($identity) {
      case SW_AUTH_CODE:
        $CI->load->model('social_worker_model'); // 社工基本資料檔
        $sw_row = $CI->social_worker_model->chk_token($token_str);
        if(empty($sw_row)) {
          echo "查無該token!!";
          die();
        }
        else {
          $s_num = $sw_row->s_num;
        }
        break;
      case DP_AUTH_CODE:
        $CI->load->model('delivery_person_model'); // 外送員基本資料檔
        $dp_row = $CI->delivery_person_model->chk_token($token_str);
        if(empty($dp_row)) {
          echo "查無該token!!";
          die();
        }
        else {
          $s_num = $dp_row->s_num;
        }
        break;
    }
  }

  return array($identity, $s_num);
}
// **************************************************************************
//  函數名稱: filter_week()
//  函數功能: 數字轉星期一、二、三....
//  使用方式: filter_week('1');
//  程式設計: Tony
//  設計日期: 2017/11/16
// **************************************************************************
function filter_week($vnum) {
  $vrtn_str = '';
  switch($vnum) {
    case '1':
      $vrtn_str = '<span class="badge badge-secondary">星期一</span>';
      break;
    case '2':
      $vrtn_str = '<span class="badge badge-secondary">星期二</span>';
      break;
    case '3':
      $vrtn_str = '<span class="badge badge-secondary">星期三</span>';
      break;
    case '4':
      $vrtn_str = '<span class="badge badge-secondary">星期四</span>';
      break;
    case '5':
      $vrtn_str = '<span class="badge badge-secondary">星期五</span>';
      break;
    case '6':
      $vrtn_str = '<span class="badge badge-success">星期六</span>';
      break;
    case '0':
      $vrtn_str = '<span class="badge badge-danger">星期日</span>';
      break;
  }
  return ($vrtn_str);
}
// **************************************************************************
//  函數名稱: change_str()
//  函數功能: 文字轉換
//  程式設計: Kiwi
//  設計日期: 2021-02-05
// **************************************************************************
function change_str($change_arr, $change_str) {
  $rtn_str_arr = [];
  if(!empty($change_arr)) {
    foreach($change_arr as $k => $v) {
      if($v == 0) {
        continue;
      }
      if(isset($change_str[$v])) {
        $rtn_str_arr[] = $change_str[$v];
      }
    }
  }
  return join("、", $rtn_str_arr);
}

// **************************************************************************
//  函數名稱: get_month_date()
//  函數功能: 取得整個月的日期
//  程式設計: Kiwi
//  設計日期: 2021/10/13
// **************************************************************************
function get_month_date($month) {  
  $i = 0;
  $monthDays = [];  
  $firstDay = date('Y-m-01', strtotime("{$month}"));
  $lastDay = date('Y-m-d', strtotime("{$firstDay} +1 month -1 day"));  
  while (date('Y-m-d', strtotime("{$firstDay} + {$i} days")) <= $lastDay) {  
    $monthDays[] = date('Y-m-d', strtotime("{$firstDay} + {$i} days"));  
    $i++;  
  }    
  return $monthDays;  
}

// **************************************************************************
//  函數名稱: get_month_date()
//  函數功能: 取得日期區間
//  程式設計: Kiwi
//  設計日期: 2021/10/13
// **************************************************************************
function period_date($start_time,$end_time) {
  $start_time = strtotime($start_time);
  $end_time = strtotime($end_time);
  $i=0;
  while ($start_time<=$end_time){
      $arr[$i]=date('Y-m-d',$start_time);
      $start_time = strtotime('+1 day',$start_time);
      $i++;
  }
  return $arr;
}  

// **************************************************************************
//  函數名稱: time_cnt()
//  函數功能: 計算時間
//  程式設計: Kiwi
//  設計日期: 2022/06/09
// **************************************************************************
function time_cnt($time_start, $time_end) {
  $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
  if($time_diff >= 60) {
    $time_diff = round($time_diff/60,1).' 分'; // 分鐘
  }
  else {
    $time_diff = $time_diff.' 秒'; // 秒
  }   
  return $time_diff;
}

// **************************************************************************
//  函數名稱: hlp_get_subsidy_price
//  函數功能: 取得補助費用(交通費，餐費)
//  程式設計: Kiwi
//  設計日期: 2024/1/11
// **************************************************************************
function hlp_get_subsidy_price($type='meal', $tw_year=112) {
  $unit_price_arr = [];

  // key = 民國年，value = 補助費用
  switch($type) {
    case 'meal':
      $unit_price_arr[112] = 80;
      $unit_price_arr[113] = 100;
      break;
    case 'car':
      $unit_price_arr[112] = 100;
      $unit_price_arr[113] = 125;
      break;
  }

  if($tw_year <= 112) {
    $unit_price = $unit_price_arr[112];
  }
  else {
    $unit_price = $unit_price_arr[$tw_year];
  }

  return $unit_price;
}
?>