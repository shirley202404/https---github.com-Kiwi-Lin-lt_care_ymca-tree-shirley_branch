<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Zi_client_html {

  public function __construct() {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
  }

  function get_client_data($client_html) {

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTMLFile($client_html);
    libxml_clear_errors();
    $html_xpath = new DOMXPath($dom);

    // 陣列的index是對應資料表欄位名稱，後面的值是對應HTML xpath
    // $text_col_xpath_arr['ct_gps'] = 'GPS'; // 系統緯度ct16、經度ct17，要用GOOGLE API轉
    $text_col_xpath_arr['ct00'] = '//*[@id="ca110FormInfo-body"]/table[1]/tbody/tr[21]/td'; // 判斷是否為僅OT個案
    $text_col_xpath_arr['ct01'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[2]/td[1]'; // 個案姓
    $text_col_xpath_arr['ct02'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[2]/td[1]'; // 個案名
    $text_col_xpath_arr['ct03'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[1]/td[1]'; // 個案身分證
    $text_col_xpath_arr['ct04'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[4]/td[1]'; // 個案性別
    $text_col_xpath_arr['ct05'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[2]/td[2]'; // 個案生日，要切割，年份要加上1911
    $text_col_xpath_arr['ct06_telephone'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[1]/td[2]'; // 個案電話，可以判斷前面是否為09開頭，如果不是就是"無"
    $text_col_xpath_arr['ct06_homephone'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[1]/td[2]'; // 個案家電，可以判斷前方是否為家電區域碼，04-台中，07-高雄，如果不是就是"無"
    $text_col_xpath_arr['ct07_1_name'] = '//*[@id="ca100Form5-body"]/table/tbody/tr[1]/td'; // 主要聯絡人-姓名
    $text_col_xpath_arr['ct07_1_rlat'] = '//*[@id="ca100Form5-body"]/table/tbody/tr[4]/td[1]'; // 主要聯絡人-關係
    $text_col_xpath_arr['ct07_1_tel'] = '//*[@id="ca100Form5-body"]/table/tbody/tr[3]/td'; // 主要聯絡人-電話
    $text_col_xpath_arr['ct_address1'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[5]/td'; // 戶籍地址，系統欄位從ct08~ct11
    $text_col_xpath_arr['ct_address2'] = '//*[@id="ca100Form3-body"]/table[1]/tbody/tr[6]/td'; // 聯絡地址，系統欄位從ct12~ct15
    $text_col_xpath_arr['ct31'] = '//*[@id="ca110FormH-body"]/table/tbody/tr[2]/td'; // 居住狀況
    $text_col_xpath_arr['ct35'] = '//*[@id="ca100Form3-body"]/table[2]/tbody/tr[1]/td[1]'; // 殘障手冊，Y=有，N=沒有
    $text_col_xpath_arr['ct35_end_date'] = '//*[@id="ca100Form3-body"]/table[2]/tbody/tr[4]/td[2]'; // 換照日期 
    $text_col_xpath_arr['ct35_type'] = '//*[@id="ca100Form3-body"]/table[2]/tbody/tr[2]/td'; // 障礙類別
    $text_col_xpath_arr['ct35_level'] = '//*[@id="ca100Form3-body"]/table[2]/tbody/tr[3]/td[1]'; // 障礙等級
    $text_col_xpath_arr['ct37'] = '//*[@id="ca110FormInfo-body"]/table[1]/tbody/tr[8]/td'; // CMS等級
    $text_col_xpath_arr['ct97'] = '//*[@id="ca110FormInfoCmt-body"]/table/tbody/tr[2]/td'; // 個案評估摘要
    $text_col_xpath_arr['ct98'] = '//*[@id="ca110FormInfo-body"]/div[10]/table/tbody/tr[1]/td/div/pre'; // 處遇計畫摘要
    
    $client_data = NULL;
    foreach ($text_col_xpath_arr as $tbl_col_name => $col_xpath) {
      $tbl_val = '';
      $html_val = trim($html_xpath->query($col_xpath)->item(0)->nodeValue);
      switch ($tbl_col_name) {
        case 'ct00': // 判斷是否為僅OT個案，值裡面還有"縣市營養餐飲承辦"或"未啟用"等字眼。
          $tbl_val = $this->process_ct00($html_val);      
          break;
        case 'ct01': // 個案姓，要判斷字串長度，如果是三個字抓第一個字，四個字抓前兩個字
          $tbl_val = $this->process_ct01($html_val);      
          break;
        case 'ct02': // 個案名，都抓字串最後兩個字
          $tbl_val = $this->process_ct02($html_val);      
          break;
        case 'ct03': // 個案身分證，會有其他資訊，只保留英文和數字的部分
          $tbl_val = $this->process_ct03($html_val);      
          break;
        case 'ct04': // 個案性別，要判斷，男=M；女=Y
          $tbl_val = $this->process_ct04($html_val);      
          break;
        case 'ct05': // 個案生日，要切割，年份要加上1911
          $tbl_val = $this->process_ct05($html_val);      
          break;
        case 'ct06_telephone': // 個案電話，可以判斷前面是否為09開頭，如果是就是手機號碼
          $tbl_val = $this->process_ct06_telephone($html_val);      
          break;
        case 'ct06_homephone': // 個案家電，可以判斷前面是否為09開頭，如果不是就是家電號碼
          $tbl_val = $this->process_ct06_homephone($html_val);
          break;
        case 'ct07_1_name': // 個案聯絡人-1 名稱
          $tbl_val = $html_val;
          break;
        case 'ct07_1_rlat': // 個案聯絡人-1 關係
          $tbl_val = $html_val;
          if($tbl_val == '') {
            $alternative_xpath = '//*[@id="ca100Form5-body"]/table/tbody/tr[4]/td[2]';
            $tbl_val = trim($html_xpath->query($alternative_xpath)->item(0)->nodeValue);
          }
          break;
        case 'ct07_1_tel': // 個案聯絡人-1 電話
          $tbl_val = $html_val;
          break;
        case 'ct_address1': // 戶籍地址，系統欄位從ct08~ct11
          $tbl_val = $this->process_ct_address1($client_data, $html_val);
          break;
        case 'ct_address2': // 聯絡地址，系統欄位從ct12~ct15
          $tbl_val = $this->process_ct_address2($client_data, $html_val);
          break;
        case 'ct31': // 居住狀況
          $tbl_val = $this->process_ct31($html_val);
          break;
        case 'ct35': // 殘障手冊，Y=有，N=沒有
          $tbl_val = $this->process_ct35($html_val);
          break;
        case 'ct35_end_date': // 換照日期
          if($client_data['ct35'] == "Y") {
            $tbl_val = $this->process_ct35_end_date($html_val);
          }
          break;
        case 'ct35_type': // 障礙類別，如果有殘障手冊才需要判斷
          $tbl_val = $this->process_ct35_type($client_data, $html_val);
          break;
        case 'ct35_level': // 障礙等級
          $tbl_val = $this->process_ct35_level($html_val);
          break;
        case 'ct37': // CMS等級
          $tbl_val = $this->process_ct37($html_val);
          break;
        case 'ct97': // 個案評估摘要
          $tbl_val = $html_val;
          break;
        case 'ct98': // 處遇計畫摘要
          $tbl_val = $this->process_ct98($html_val);
          break;
      }
      $client_data[$tbl_col_name] = $tbl_val;
    }

    $img_judge_xpath = array('ct34_go', 'ct34_fo', 'ct38');
    foreach ($img_judge_xpath as $tbl_col_name) {
      switch ($tbl_col_name) {
        case 'ct34_go':
          $this->process_ct34_go($html_xpath, $client_data);
          break;
        case 'ct34_fo':
          $this->process_ct34_fo($html_xpath, $client_data);
          break;
        case 'ct38':
          $this->process_ct38($html_xpath, $client_data);
          break;
      }
    }

    $this->process_chk_val($client_data);
    return $client_data;
  }

  function process_ct00($html_val) {
    $pattern = array("縣市營養餐飲承辦", "未啟用");
    $matched_categories = array();
    foreach ($pattern as $index => $category) {
      if (strpos($html_val, $category) !== false) {
        $matched_categories[] = $index;
      }
    }
    return empty($matched_categories) ? "N" : "Y";
  }

  function process_ct01($html_val) {
    return mb_substr($html_val, 0, mb_strlen($html_val) - 2);
  }

  function process_ct02($html_val) {
    return mb_substr($html_val, mb_strlen($html_val) - 2, 2);
  }

  function process_ct03($html_val) {
    preg_match_all('/[A-Za-z0-9]+/', $html_val, $matches);
    return implode('', $matches[0]);
  }

  function process_ct04($html_val) {
    return str_replace(array('男', '女'), array("M", "Y"), $html_val);
  }

  function process_ct05($html_val) {
    $html_val_arr = explode("/", $html_val);
    if(!empty($html_val_arr)) {
      return (int)$html_val_arr[0] + 1911 . "-{$html_val_arr[1]}-{$html_val_arr[2]}";
    }
    else {
      return '';
    }
  }

  function process_ct06_telephone($html_val) {
    if("09" == substr($html_val, 0, 2)) {
      return $html_val;
    }
    else {
      return "無";
    }
  }

  function process_ct06_homephone($html_val) {
    if("09" != substr($html_val, 0, 2)) {
      return $html_val;
    }
    else {
      return "無";
    }
  }

  // 處理ct_address1
  function process_ct_address1(&$client_data, $html_val) {
    $pattern = '/([\x{4e00}-\x{9fa5}]+[市縣])([\x{4e00}-\x{9fa5}]+[區鎮鄉])(.*)/u';
    preg_match($pattern, mb_convert_kana($html_val, 'n'), $matches); // mb_convert_kana 將全形數字轉半形
    if (count($matches) >= 3) {
      $client_data['ct09'] = $matches[1];
      $client_data['ct10'] = $matches[2];
      $client_data['ct11'] = $matches[3];
    }
  }

  // 處理ct_address2
  function process_ct_address2(&$client_data, $html_val) {
    $pattern = '/([\x{4e00}-\x{9fa5}]+[市縣])([\x{4e00}-\x{9fa5}]+[區鎮鄉])(.*)/u';
    preg_match($pattern, mb_convert_kana($html_val, 'n'), $matches); // mb_convert_kana 將全形數字轉半形
    if (count($matches) >= 3) {
      $client_data['ct13'] = $matches[1];
      $client_data['ct14'] = $matches[2];
      $client_data['ct15'] = $matches[3];
      $this->process_address_convert($client_data, $html_val);
    }
  }

  function process_ct31($html_val) {
    $pattern = array(1 => "獨居", 2 => "與家人或其他人同住");
    $matched_categories = array();
    foreach ($pattern as $index => $category) {
      if (strpos($html_val, $category) !== false) {
        $matched_categories[] = $index;
      }
    }  
    if(!empty($matched_categories)) {
      return join(",", $matched_categories);
    }   
    return '';
  }

  function process_ct34_go($html_xpath, &$client_data) {
    for($i = 1; $i <= 3; $i++) {
      $ct34_go_xpath = "//*[@id='ca110FormA-body']/table/tbody/tr[2]/td/span[1]/label[{$i}]/img";
      $img_src = $html_xpath->query($ct34_go_xpath)->item(0)->getAttribute('src');
      if (strpos($img_src, 'checkbox_checked.gif') !== false) {
        switch ($i) {
          case 1: // 一般戶
            $client_data['ct34_go'] = 1;
            break;
          case 2: // 低收
            // 社會救助法低收入戶（未達1倍）=> 低收一
            $chk_xpath_1 = '//*[@id="ca110FormA-body"]/table/tbody/tr[2]/td/span[1]/span/label[1]/img'; 
            $chk_res_1 = $html_xpath->query($chk_xpath_1)->item(0)->getAttribute('src');
            if (strpos($chk_res_1, 'checkbox_checked.gif') !== false) {
              $client_data['ct34_go'] = 5;
            }
            // 長照低收（未達1.5倍）=> 中低1.5 
            $chk_xpath_2 = '//*[@id="ca110FormA-body"]/table/tbody/tr[2]/td/span[1]/span/label[2]/img'; 
            $chk_res_2 = $html_xpath->query($chk_xpath_2)->item(0)->getAttribute('src');
            if (strpos($chk_res_2, 'checkbox_checked.gif') !== false) {
              $client_data['ct34_go'] = 4;
            }
            break;
          case 3: // 長照中低收（1.5~2.5倍）
            $client_data['ct34_go'] = 3;
            break;
        }
      }
    }
  }

  function process_ct34_fo($html_xpath, &$client_data) {
    $ct34_fo_arr = array();
    for($i = 1; $i <= 2; $i++) {
      $ct34_fo_xpath = "//*[@id='ca110FormA-body']/table/tbody/tr[2]/td/span[2]/label[{$i}]/img";
      $img_src_val = $html_xpath->query($ct34_fo_xpath)->item(0)->getAttribute('src');
      if(strpos($img_src_val, 'checkbox_checked.gif') !== false) {
        switch ($i) {
          case 1: // 原住民
            $ct34_fo_arr[] = 2;
            break;
          case 2: // 榮民
            $ct34_fo_arr[] = 3;
            break;
        }
      }
    }
    $client_data['ct34_fo'] = join(",", $ct34_fo_arr);
  }

  function process_ct35($html_val) {
    return str_replace(array('有', '無'), array("Y", "N"), $html_val);
  }

  function process_ct35_end_date($html_val) {
    $html_val_arr = explode("/", $html_val);
    if(!empty($html_val_arr)) {
      return (int)$html_val_arr[0] + 1911 . "-{$html_val_arr[1]}-{$html_val_arr[2]}";
    }
    return '';
  }

  function process_ct35_type(&$client_data, $html_val) {
    if("N" == $client_data['ct35']) {
      return;
    }
    
    $matched_categories = array();
    $pattern = array(11 => "一類", 12 => "二類", 13 => "三類", 
                     14 => "四類", 15 => "五類", 16 => "六類", 
                     17 => "七類", 18 => "八類");
    foreach ($pattern as $index => $category) {
      if (strpos($html_val, $category) !== false) {
        $matched_categories[] = $index;
      }
    }
    if(empty($matched_categories)) {
      return 99;
    }
    else {
      return join(",", $matched_categories);
    }
  }

  function process_ct35_level($html_val) {
    $choices = array("輕度", "中度", "重度", "極重度", "無");
    $choices_replace = array(1, 2, 3, 4, 99);
    $html_replace = str_replace($choices, $choices_replace, $html_val);
    if($html_replace == "極3") {
      return 4;
    }
    return str_replace($choices, $choices_replace, $html_val);
  }

  function process_ct37($html_val) {
    $choices = array("第2級", "第3級", "第4級", "第5級", "第6級", "第7級", "第8級");
    $choices_replace = array(7, 8, 9, 10, 11, 12, 13);
    return str_replace($choices, $choices_replace, $html_val);
  }

  function process_ct38($html_xpath, &$client_data) {
    $ct38_1_pattetn = array("1" => "高血壓",
                            "2" => "糖尿病",
                            "4" => "冠狀動脈疾病（如心絞痛、心肌梗塞、動脈硬化性心臟病）",
                            "5" => "癌症（過去五年內）",
                            "6" => "呼吸系統疾病（氣喘、慢性阻塞性肺病、肺炎、呼吸衰竭等）",
                            "7" => "消化系統疾病（肝、膽、腸、胃）",
                            "8" => "泌尿生殖系統疾病（良性攝護腺肥大、腎衰竭等）",
                            "9" => "痛風",
                            "10" => "乳糖不耐症",
                            "11" => "腦血管意外（中風）、暫時性腦部缺血（小中風）",
                            "12" => "心房顫動或其他節律障礙",
                           );
      
    $ct38_2_pattetn = array("3" => "小兒麻痺",
                            "4" => "頑性(難治型)癲癇症",
                            "5" => "帕金森氏症",
                            "6" => "失智症",
                            "7" => "精神疾病（思覺失調症、雙極性精神障礙、憂鬱症等）",
                            "9" => "運動神經元疾病（最常見為肌萎縮性脊髓側索硬化症, ALS）",
                            "10" => "骨骼系統（關節炎、骨折、骨質疏鬆症）",
                            "11" => "視覺疾病（白內障、視網膜病變、青光眼或黃斑性退化等）",
                            "12" => "自閉症",
                            "13" => "智能不足(輕度、中度、重度、極重度、其他及非特定智能不足)",
                            "14" => "腦性麻痺",
                            "15" => "脊髓損傷",
                            "16" => "傳染性疾病（疥瘡、肺結核、梅毒、愛滋病等）",
                            "17" => "感染性疾病（過去一個月內）",
                            "18" => "罕見疾病",
                           );

    $ct38_1_chk_arr = array();
    $ct38_2_chk_arr = array();
    for($i = 3; $i <= 25; $i++) {
      $img_src_val = $html_xpath->query("//*[@id='ca110FormG-body']/table[2]/tbody/tr[{$i}]/td[1]/label/img")->item(0)->getAttribute('src');
      $temp_text_val = $html_xpath->query("//*[@id='ca110FormG-body']/table[2]/tbody/tr[{$i}]/td[1]/label/text()")->item(0)->nodeValue;
      if(strpos($img_src_val, 'checkbox_checked.gif') !== false) {
        $text_val = explode(".", $temp_text_val)[1];
        foreach ($ct38_1_pattetn as $k => $v) {
          if(strpos($text_val, $v) !== false) {
            $ct38_1_chk_arr[] = $k;
          }
        }
        foreach ($ct38_2_pattetn as $k => $v) {
          if(strpos($text_val, $v) !== false) {
            $ct38_2_chk_arr[] = $k;
          }
        }
      } 
    }

    $client_data['ct38_1'] = join(",", $ct38_1_chk_arr);
    $client_data['ct38_2'] = join(",", $ct38_2_chk_arr);
    
    $img_src_val = $html_xpath->query("//*[@id='ca110FormG-body']/table[2]/tbody/tr[26]/td[1]/label/img")->item(0)->getAttribute('src');
    if(strpos($img_src_val, 'checkbox_checked.gif') !== false) {
      $temp_text_val = $html_xpath->query("//*[@id='ca110FormG-body']/table[2]/tbody/tr[26]/td[1]/text()")->item(0)->nodeValue;
      $text_val = explode(".", $temp_text_val)[1];
      $pattern = '/[\x{4e00}-\x{9fa5}]+/u';
      preg_match_all($pattern, $text_val, $matches);
      $client_data['ct38_memo'] = implode("、", $matches[0]);
    }
  }

  function process_ct98($html_val) {
    $html_val_arr = explode("四、照顧計畫", $html_val);
    if(count($html_val_arr) < 2) {
      return $html_val;
    }
    else {
      return $html_val_arr[1];
    }
  }

  function process_address_convert(&$client_data, $html_val) {
    $map_api_key = 'AIzaSyA7M7Hqze-Zf-0D4UmC4iCt8YpeIEiJ7h8';
    $url = "https://maps.googleapis.com/maps/api/geocode/json?key={$map_api_key}&sensor=true&language=zh-TW&region=tw&address={$html_val}";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    $response = json_decode(curl_exec($curl), true);

    if ($response['status'] == 'OK') {
      //取得需要的重要資訊
      $client_data['ct16'] = $response['results'][0]['geometry']['location']['lat']; // 緯度
      $client_data['ct17'] = $response['results'][0]['geometry']['location']['lng']; // 經度
    }
    else {
      $client_data['ct16'] = '';
      $client_data['ct17'] = '';
    }
  }

  function process_chk_val(&$client_data) {
    $chk_row = $this->CI->clients_model->get_one_by_ct03($client_data['ct03']);
    if(empty($chk_row)) {
      return;
    }

    $tbl_clients = $this->CI->zi_init->chk_tbl_no_lang('clients'); // 案主資料暫存檔
    $tbl_fields_arr = $this->CI->db->list_fields($tbl_clients);
    foreach ($tbl_fields_arr as $k => $field) {
      if(empty($client_data[$field]) and !empty($chk_row->$field)) {
        if($field == 's_num') {
          $client_data['ct_s_num'] = $chk_row->$field;
        }
        else {
          $client_data[$field] = $chk_row->$field;
        }
      }
    }
  }
}