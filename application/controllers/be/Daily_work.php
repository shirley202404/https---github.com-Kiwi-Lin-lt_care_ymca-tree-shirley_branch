<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daily_work extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('route_model'); // 路線資料
    $this->load->model('work_q_model'); // 問卷資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('daily_work_model'); // 每日工作
    $this->load->model('meal_order_model'); // 訂單資料
    $this->load->model('meal_order_date_type_model'); // 訂單日期類型資料
    $this->load->model('service_case_model');   // 開結案服務資料
    $this->load->model('meal_instruction_log_h_model'); // 餐食異動
    $this->load->model('other_change_log_h_model'); // 非餐食異動
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->load->library('lunar');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."daily_work/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_log_confirm_link',be_url()."daily_work/log_confirm"); // 每日訂單
    $this->tpl->assign('tv_produce_meal_order_link',base_url()."lt_care_tool/produce_meal_order"); // 每日訂單
    $this->tpl->assign('tv_upd_other_change_log_route_link',base_url()."lt_care_tool/upd_other_change_log_route"); // 路線更新
    $this->tpl->assign('tv_month',date('Y-m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"YMCA南投縣基督教青年會");
    $this->tpl->assign('tv_import_ok',$this->lang->line('import_ok')); // 匯入成功!!
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/harvest_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    $this->meal_type_arr = array(1, 3, 4, 5, 6);
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
    $js_judgment = "訂單已產生";
    $date = $this->_get_meal_order_status(date("Y-m"));
    
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','送餐系統');
    $this->tpl->assign('tv_today',date("Y-m-d"));
    $this->tpl->assign('tv_js_judgment', $js_judgment);
    $this->tpl->assign('tv_date' , json_encode($date)); // 訂單資料
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_produce_link',be_url().'daily_work/produce');
    $this->tpl->assign('tv_produce_send_link',be_url().'daily_work/produce_send/');
    $this->tpl->assign('tv_produce_meal_link',be_url().'daily_work/produce_meal/');
    $this->tpl->assign('tv_add_meal_order_link',be_url().'daily_work/meal_order/add');
    $this->tpl->assign('tv_chk_meal_order_link',be_url().'daily_work/meal_order/chk');
    $this->tpl->assign('tv_upd_meal_order_link',be_url().'daily_work/meal_order/upd');
    $this->tpl->assign('tv_que_meal_order_status_link',be_url().'daily_work/que_meal_order_status/');
    $this->tpl->assign('tv_return_link',be_url().'daily_work/');
    $this->tpl->display("be/daily_work.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: log_confirm
  //  函數功能: 異動確認
  //  程式設計: Kiwi
  //  設計日期: 2021/05/06
  // **************************************************************************
  public function log_confirm() {
    set_time_limit(3600); // 限制處理時間60分鐘
    ini_set('memory_limit', '3072M');

    $rtn_msg = 'ok';
    $produce_date = $this->input->post("produce_date");
    $daily_order_row = $this->meal_order_model->get_daily_order();

    if(!empty($daily_order_row)) {
      $meal_order_data = $this->_run_log_confirm($daily_order_row , $produce_date);
      if(!$this->meal_order_model->upd_meal_order($meal_order_data)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 更新失敗
        echo $rtn_msg;
        return;
      } 
    }
    echo $rtn_msg; 
    return;
  }  
  // **************************************************************************
  //  函數名稱: produce
  //  函數功能: 產生餐條、配送單
  //  程式設計: Kiwi
  //  設計日期: 2020/12/08
  // **************************************************************************
  public function produce() {
    set_time_limit(3600); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');

    $produce_date = $this->input->post('produce_date'); // 產生日期
    $produce_time = $this->input->post('produce_time'); // 產生班別
    $produce_type = $this->input->post('produce_type'); // 產生類型

    switch ($produce_type) {
      case 'all': // 一次產生全部
        $this->_produce_send($produce_date, $produce_time);
        $this->_produce_meal($produce_date, $produce_time);
        break;
      case 'send': // 產生配送單
        $this->_produce_send($produce_date, $produce_time);
        break;
      case 'meal': // 產生餐條
        $this->_produce_meal($produce_date, $produce_time);
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: produce_send
  //  函數功能: 產生外送單
  //  程式設計: Kiwi
  //  設計日期: 2020/12/08
  // **************************************************************************
  public function _produce_send($produce_date, $produce_time) {
    $this->_init_date($produce_date); // 產生五種日期

    $i = 0;
    $shipment_data = NULL;
    $route_row = $this->route_model->get_all();
    $daily_order_row = $this->meal_order_model->get_daily_order();

    if($daily_order_row != NULL) {
      foreach ($daily_order_row as $kdo => $vdo) {
        if(!isset($route_row[$vdo['reh_s_num']])) {
          continue;
        }
        
        list($res, $err_step, $generate_data) = $this->_generate_data($vdo['s_num'], $vdo['ct_s_num'], $vdo['sec_s_num']);
        if(!$res) {
          continue;
        }

        $shipment_data[$i]['b_empno'] = $_SESSION['acc_s_num'];
        $shipment_data[$i]['b_date'] = date("Y-m-d H:i:s");
        $shipment_data[$i]['ct_s_num'] = $vdo['ct_s_num'];
        $shipment_data[$i]['dp_s_num'] = $route_row[$vdo['reh_s_num']]["reh06_{$this->week_day_str}_dp_s_num"];
        $shipment_data[$i]['reh_s_num'] = $vdo['reh_s_num'];
        $shipment_data[$i]['mlo_s_num'] = $vdo['s_num'];
        $shipment_data[$i]['sec_s_num'] = $vdo['sec_s_num'];
        $shipment_data[$i]['ct_name'] = "{$vdo['ct01']}{$vdo['ct02']}";
        $shipment_data[$i]['dys01'] = $produce_date; // 送餐日期
        $shipment_data[$i]['dys02'] = $vdo["sec04_str"]; // 餐別
        $shipment_data[$i]['dys03'] = $generate_data['ml01']; // 餐點名稱
        $shipment_data[$i]['dys04'] = ''; // 特殊內容
        $shipment_data[$i]['dys05'] = ''; // YMCA沒有代餐
        $shipment_data[$i]['dys05_type'] = ''; // YMCA沒有代餐
        $shipment_data[$i]['dys06'] = $generate_data['is_meal_instruction']; // 是否異動
        $shipment_data[$i]['dys07'] = !empty($generate_data['reh01']) ? $generate_data['reh01'] : $vdo["reh01"]; // 路線代碼
        $shipment_data[$i]['dys08'] = !empty($generate_data['reb01']) ? $generate_data['reb01'] : $vdo["reb01"]; // 路線順序
        $shipment_data[$i]['dys09'] = $vdo["mlo02"]; // 送餐時間
        $shipment_data[$i]['dys10'] = $generate_data['is_send']; // 是否停餐
        $shipment_data[$i]['dys13'] = ''; // 是否自費

        // 如果事先產生好的路線過了異動當天會因為沒有偵測到案主有更新路線，所以會咬住
        // 但是他們有可能當天會重新安排路線
        $shipment_row = $this->daily_work_model->get_one_shipment($produce_date, $vdo['sec_s_num']); // 獲取配送資料
        // if(NULL != $shipment_row) { // 如果重新產生異動單，資料會被覆蓋掉
        //   if(NULL == $this->ocl_row) { // 如果沒有路線異動資料
        //     if(!empty($shipment_row->dys21) and !empty($shipment_row->dys23)) { // 如果已經打卡的話，路線要換回原本的
        //       $shipment_data[$i]['dp_s_num'] = $shipment_row->dp_s_num;
        //       $shipment_data[$i]['reh_s_num'] = $shipment_row->reh_s_num;
        //     }
        //   }
        //   else {
        //     $shipment_data[$i]['dp_s_num'] = $this->ocl_row->res_dp_s_num;
        //     $shipment_data[$i]['reh_s_num'] = $this->ocl_row->res_reh_s_num;
        //   }
        //   $shipment_data[$i]['dys21'] = $shipment_row->dys21;
        //   $shipment_data[$i]['dys22'] = $shipment_row->dys22;
        //   $shipment_data[$i]['dys23'] = $shipment_row->dys23;
        //   $shipment_data[$i]['dys24'] = $shipment_row->dys24;
        //   $shipment_data[$i]['dys25'] = $shipment_row->dys25;
        // }

        $i++;
      }

      if(!$this->daily_work_model->save_shipment_data($shipment_data)) {
        echo "error";
        return;
      }
      else {
        // 產生工作問卷
        $daily_order_row = $this->daily_work_model->chk_daily_shipment($produce_date);
        if(!empty($daily_order_row)) {
          $work_h_add_data_batch = NULL;
          $work_h_upd_data_batch = NULL;
          $work_q_row = $this->work_q_model->get_all_by_date($produce_date); // 拿取今日所有問卷資料;
          if(empty($work_q_row)) {
            $i = 0;
            foreach ($daily_order_row as $k => $v) {
              if($v['dys10'] == "Y") {
                $work_h_add_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
                $work_h_add_data_batch[$i]['b_date'] = date("Y-m-d H:i:s");
                $work_h_add_data_batch[$i]['sec_s_num'] = $v['sec_s_num'];
                $work_h_add_data_batch[$i]['reh_s_num'] = $v['reh_s_num'];
                $work_h_add_data_batch[$i]['reb01'] = $v['dys08'];
                $work_h_add_data_batch[$i]['dys_s_num'] = $v['s_num'];
                $work_h_add_data_batch[$i]['qh_s_num'] = 1; // 工作問卷
                $work_h_add_data_batch[$i]['dys01'] = $produce_date;
                $i++;
              }
            }
            if(!$this->work_q_model->save_daily_work_h($work_h_add_data_batch)) {
              echo "error";
              return;
            }
          }
          else {
            $i = 0;
            $sec_s_num_arr = array_column($work_q_row, "sec_s_num");
            $work_q_s_num_arr = array_column($work_q_row, "s_num");
            
            foreach ($daily_order_row as $k => $v) {
              $chk_arr_index = array_search($v['sec_s_num'], $sec_s_num_arr);
              if($chk_arr_index === false) {
                if($v['dys10'] == "Y") {
                  $work_h_add_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
                  $work_h_add_data_batch[$i]['b_date'] = date("Y-m-d H:i:s");
                  $work_h_add_data_batch[$i]['dys_s_num'] = $v['s_num'];
                  $work_h_add_data_batch[$i]['sec_s_num'] = $v['sec_s_num'];
                  $work_h_add_data_batch[$i]['reh_s_num'] = $v['reh_s_num'];
                  $work_h_add_data_batch[$i]['reb01'] = $v['dys08'];
                  $work_h_add_data_batch[$i]['qh_s_num'] = 1; // 工作問卷
                  $work_h_add_data_batch[$i]['dys01'] = $produce_date;
                  $i++;
                }
              }
              else {
                $work_h_upd_data_batch[$v['s_num']]['s_num'] = $work_q_s_num_arr[$chk_arr_index];
                $work_h_upd_data_batch[$v['s_num']]['b_empno'] = $_SESSION['acc_s_num'];
                $work_h_upd_data_batch[$v['s_num']]['b_date'] = date("Y-m-d H:i:s");
                $work_h_upd_data_batch[$v['s_num']]['dys_s_num'] = $v['s_num'];
                $work_h_upd_data_batch[$v['s_num']]['reh_s_num'] = $v['reh_s_num'];
                $work_h_upd_data_batch[$v['s_num']]['reb01'] = $v['dys08'];
              }
            }

            if(NULL != $work_h_add_data_batch) {
              if(!$this->work_q_model->save_daily_work_h($work_h_add_data_batch)) {
                echo "error123";
                return;
              }
            }

            if(NULL != $work_h_upd_data_batch) {
              $this->work_q_model->upd_daily_work_h($work_h_upd_data_batch);
            }

          }
        }
      }  
    }

    if($produce_date == date("Y-m-d")) {
      $this->zi_my_func->web_api_data("daily_shipment");
    }
    echo "ok";
    return;
  }
  // **************************************************************************
  //  函數名稱: _produce_meal
  //  函數功能: 產生今日餐條
  //  程式設計: Kiwi
  //  設計日期: 2020/12/08
  // **************************************************************************
  public function _produce_meal($produce_date, $produce_time) {
    $this->_init_date($produce_date); // 產生五種日期

    $i = 0;
    $production_data = NULL;
    $daily_order_row = $this->meal_order_model->get_daily_order();

    if($daily_order_row != NULL) {
      foreach ($daily_order_row as $kdo => $vdo) {
        // 利用_generate_data先將資料判斷好，如果回傳的res是FALSE直接continue;
        list($res, $err_step, $generate_data) = $this->_generate_data($vdo['s_num'], $vdo['ct_s_num'], $vdo['sec_s_num']);
        if(!$res) {
          continue;
        }
        
        // 產生資料內容 Begin //
        $production_data[$i]['b_empno'] = $_SESSION['acc_s_num'];
        $production_data[$i]['b_date'] = date("Y-m-d H:i:s");
        $production_data[$i]['ct_s_num'] = $vdo['ct_s_num'];
        $production_data[$i]['reh_s_num'] = !empty($generate_data['reh_s_num']) ? $generate_data['reh_s_num'] : $vdo['reh_s_num'];
        $production_data[$i]['mlo_s_num'] = $vdo['s_num'];
        $production_data[$i]['sec_s_num'] = $vdo['sec_s_num'];
        $production_data[$i]['ct_name'] = "{$vdo['ct01']}{$vdo['ct02']}";
        $production_data[$i]['reh_reh03'] = $vdo['reh03'];
        $production_data[$i]['dyp01'] = $produce_date; // 送餐日期
        $production_data[$i]['dyp02'] = $vdo["sec04_str"]; // 餐別
        $production_data[$i]['dyp03'] = $generate_data['ml01']; // 餐點名稱
        $production_data[$i]["dyp04_1"] = $generate_data['meal_instruction_1'];
        $production_data[$i]["dyp04_2"] = $generate_data['meal_instruction_2'];
        $production_data[$i]["dyp04_3"] = $generate_data['meal_instruction_3'];
        $production_data[$i]["dyp04_4"] = $generate_data['meal_instruction_4'];
        $production_data[$i]["dyp04_5"] = $generate_data['meal_instruction_5'];
        $production_data[$i]['dyp06'] = $generate_data['is_meal_instruction'];; // 是否異動
        $production_data[$i]['dyp07'] = !empty($generate_data['reh01']) ? $generate_data['reh01'] : $vdo["reh01"]; // 路線代碼
        $production_data[$i]['dyp08'] = !empty($generate_data['reb01']) ? $generate_data['reb01'] : $vdo["reb01"]; // 路線順序
        $production_data[$i]['dyp09'] = $vdo["mlo02"]; // 送餐時間
        $production_data[$i]['dyp10'] = $generate_data['is_send'];; // 餐點名稱
        // 產生資料內容 End //
        
        $i++;
      }
      $this->daily_work_model->save_production_data($production_data);   
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: _run_log_confirm()
  //  函數功能: 處理異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-13
  // **************************************************************************
  public function _run_log_confirm($daily_order_row, $produce_date) {
    $this->_init_date($produce_date); // 產生五種日期

    $i = 0;
    $meal_order_data = NULL;
    foreach ($daily_order_row as $kdo => $vdo) {
      // 利用_generate_data先將資料判斷好，如果回傳的res是FALSE直接continue;
      list($res, $err_step, $generate_data) = $this->_generate_data($vdo['s_num'], $vdo['ct_s_num'], $vdo['sec_s_num']);
      if(!$res) {
        continue;
      }
      // 產生資料內容 Begin //
      $meal_order_data[$i]["s_num"] = $generate_data['s_num'];
      $meal_order_data[$i]["e_date"] = date('Y-m-d H:i:s');
      $meal_order_data[$i]['e_empno'] = $_SESSION['acc_s_num'];
      $meal_order_data[$i]['ct_s_num'] = $vdo['ct_s_num'];
      $meal_order_data[$i]['sec_s_num'] = $vdo['sec_s_num'];
      $meal_order_data[$i]['reh_s_num'] = !empty($generate_data['reh_s_num']) ? $generate_data['reh_s_num'] : $vdo['reh_s_num'];;
      $meal_order_data[$i]['ml_s_num'] = $generate_data['ml_s_num'];
      $meal_order_data[$i]["mlo01"] = $produce_date; // 送餐日期
      $meal_order_data[$i]["mlo03"] = $generate_data['ml01'];
      $meal_order_data[$i]["mlo04_1"] = $generate_data['meal_instruction_1'];
      $meal_order_data[$i]["mlo04_2"] = $generate_data['meal_instruction_2'];
      $meal_order_data[$i]["mlo04_3"] = $generate_data['meal_instruction_3'];
      $meal_order_data[$i]["mlo04_4"] = $generate_data['meal_instruction_4'];
      $meal_order_data[$i]["mlo04_5"] = $generate_data['meal_instruction_5'];
      $meal_order_data[$i]["mlo05"] = "";      // ymca沒有代餐
      $meal_order_data[$i]["mlo05_type"] = ""; // ymca沒有代餐
      $meal_order_data[$i]["mlo99"] = $generate_data['is_send']; // 是否出餐
      // 產生資料內容 End //
      $i++;
    }
    return $meal_order_data;
  }
  // **************************************************************************
  //  函數名稱: _generate_data()
  //  函數功能: 處理異動資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-24
  // **************************************************************************
  public function _generate_data($s_num=NULL, $ct_s_num, $sec_s_num) {
    // 判斷步驟
    // 1. 先判斷是否有建立餐食異動資料
    // 2. 過來判斷是否有建立四筆不同的異動類別資料
    // 3. 檢查路線異動資料
    // 4. 前置判斷完成，產生資料

    $week_day = $this->week_day;
    $lunar_day = $this->lunar_day;
    $order_type = $this->order_type;
    $makeup_date = $this->makeup_date;
    $produce_date = $this->produce_date;

    // 步驟1 Begin //
    $meal_instruction_log_h_row = $this->meal_instruction_log_h_model->get_que_by_sec_s_num($ct_s_num, $sec_s_num); // 取得該服務異動資料
    if($meal_instruction_log_h_row->s_num == NULL) {
      return array(false, "step 1 false", NULL);
    }   
    // 步驟1 End //

    // 步驟2 Begin //
    $cnt = 0;
    $mil_m01_arr = NULL;
    foreach ($this->meal_type_arr as $meal_type) {
      switch ($meal_type) {
        case 1: // 餐點異動
          $meal_instruction_log_m_row = $this->meal_instruction_log_h_model->get_last_m_by_s_num($sec_s_num, $produce_date); // 取得最後一筆餐點異動資料
          if(NULL != $meal_instruction_log_m_row ) {
            $mil_m01_arr["1"] = explode(",", $meal_instruction_log_m_row->mil_m01_1); // 飯糰
            $mil_m01_arr["2"] = explode(",", $meal_instruction_log_m_row->mil_m01_2); // 菜盒
            $mil_m01_arr["3"] = explode(",", $meal_instruction_log_m_row->mil_m01_3); // 菜盒內容
            $mil_m01_arr["4"] = explode(",", $meal_instruction_log_m_row->mil_m01_4); // 素食
            $mil_m01_arr["5"] = explode(",", $meal_instruction_log_m_row->mil_m01_5); // 初一、十五素食
            $cnt++;
          }
          break;  
        case 2: // 代餐異動
          // ymca沒有代餐
          break;  
        case 3: // 停復餐異動
          $meal_instruction_log_s_row = $this->meal_instruction_log_h_model->get_last_s_by_s_num($sec_s_num, $produce_date); // 取得最後一筆停餐異動資料
          if(NULL != $meal_instruction_log_s_row) {
            $meal_instruction_log_s_row->mil_s01_reason_arr = explode(",", $meal_instruction_log_s_row->mil_s01_reason);
            $cnt++;
          }
          break;  
        case 4: // 固定暫停
          $meal_instruction_log_p_row = $this->meal_instruction_log_h_model->get_last_p_by_s_num($sec_s_num, $produce_date); // 取得最後一筆固定暫停資料
          if(NULL != $meal_instruction_log_p_row) {
            $meal_instruction_log_p_row->mil_p01_arr = explode(",", $meal_instruction_log_p_row->mil_p01);
            $cnt++;
          }
          break;  
        case 5: // 自費
          $meal_instruction_log_i_row = $this->meal_instruction_log_h_model->get_last_i_by_s_num($sec_s_num, $produce_date); // 列出自費資料
          if(NULL != $meal_instruction_log_i_row) {
            $cnt++;
          }
          break;
        case 6: // 補班日出餐
          $meal_instruction_log_d_row = $this->meal_instruction_log_h_model->get_last_d_by_s_num($sec_s_num, $produce_date); // 列出自費資料
          break;
      } 
    }

    if($cnt != 4) {
      return array(false, "step 2 false", null);
    }
    // 步驟2 End //

    $service_case_row = $this->service_case_model->get_one($sec_s_num);
    $mlo02 = $service_case_row->sec04_time; // 出餐時段，1 = 午餐，2 = 晚餐

    // 步驟3 Begin //
    $reh_s_num = 0;
    $dp_s_num = 0;
    $reh01 = '';
    $reb01 = '';
    $ocl_row = $this->other_change_log_h_model->get_route_by_ct($ct_s_num, $mlo02, $produce_date);
    if(NULL != $ocl_row) {
      // 如果異動日期小於產生日期，代表還是先用目前的路線資訊
      // 如果異動日期大於產生日期，就是用新的路線資訊
      if(strtotime($produce_date) < strtotime($ocl_row->ocl_r06)) {
        $ocl_route_row = $this->route_model->get_one($ocl_row->ocl_r02_reh_s_num);
        $reh_s_num = $ocl_row->ocl_r02_reh_s_num;
        $reh01 = $ocl_route_row->reh01;
        $reb01 = $ocl_row->ocl_r02_reb01;
        $dp_s_num = $ocl_route_row->dp_s_num;
      }
      else {
        $ocl_route_row = $this->route_model->get_one($ocl_row->ocl_r03_reh_s_num);
        $reh_s_num = $ocl_row->ocl_r03_reh_s_num;
        $reh01 = $ocl_route_row->reh01;
        $reb01 = $ocl_row->ocl_r03_reb01;
        $dp_s_num = $ocl_route_row->dp_s_num;
      }
      $ocl_row->res_dp_s_num = $dp_s_num;
      $ocl_row->res_reh_s_num = $reh_s_num;
      $this->ocl_row = $ocl_row;
    }
    // 步驟3 End //

    // 步驟4 Begin //
    $generate_data = array();
    $generate_data["s_num"] = $s_num;
    $generate_data["e_date"] = date('Y-m-d H:i:s');
    $generate_data['e_empno'] = $_SESSION['acc_s_num'];
    $generate_data['ct_s_num'] = $ct_s_num;
    $generate_data['sec_s_num'] = $sec_s_num;
    $generate_data['reh_s_num'] = $reh_s_num;
    $generate_data['dp_s_num'] = $dp_s_num;
    $generate_data['reh01'] = $reh01;
    $generate_data['reb01'] = $reb01;
    $generate_data['ml_s_num'] = 0;
    $generate_data["ml01"] = ''; // 餐點名稱
    $generate_data["send_date"] = $produce_date; // 送餐日期
    $generate_data["is_meal_instruction"] = 0;
    $generate_data["meal_instruction_1"] = "";
    $generate_data["meal_instruction_2"] = "";
    $generate_data["meal_instruction_3"] = "";
    $generate_data["meal_instruction_4"] = "";
    $generate_data["meal_instruction_5"] = "";
    $generate_data["meal_replacement"] = "";      // ymca沒有代餐
    $generate_data["meal_replacement_type"] = ""; // ymca沒有代餐
    $generate_data["is_send"] = "N";     // 是否出餐

    // 是否有異動
    if($meal_instruction_log_h_row->cnt > 1) {
      $generate_data["is_meal_instruction"] = 1;
    }

    // 訂單內容調整 Begin //
    if(NULL != $mil_m01_arr) {
      for($j = 1 ; $j <= 5 ; $j++) {
        if(NULL != $mil_m01_arr[$j]) {
          $mil_m01_to_str = '';
          foreach ($mil_m01_arr[$j] as $v) {
            if($v != NULL) {
              $mil_m01_to_str .= hlp_opt_setup("mil_m01_{$j}", $v, "multiple"); 
            }       
          }
          $generate_data["meal_instruction_{$j}"] = $mil_m01_to_str;
        }
      }
    }
    // 訂單內容調整 End //

    // 餐點內容 Begin //
    if(!empty($meal_instruction_log_m_row->ml01)) {
      // 一般狀況下
      $generate_data["ml_s_num"] = $meal_instruction_log_m_row->ml_s_num; // 初一十五搭配的餐點
      $generate_data["ml01"] = $meal_instruction_log_m_row->ml01; // 一般正常的餐點
      // 初一十五，要多檢查案主是否初一、十五用素
      // 一般餐 => 要改成素飯和、素食菜盒和中飯糰
      // 分飯、分粥、碎食飯、碎食粥的菜盒要變成素食菜盒
      if ($lunar_day == "初一" or $lunar_day == "十五") {
        if(in_array("Y", $mil_m01_arr[5])) {
          $generate_data["ml_s_num"] = $meal_instruction_log_m_row->sub_ml_s_num; // 初一十五搭配的餐點
          $generate_data["ml01"] = $meal_instruction_log_m_row->sub_ml01; // 初一十五搭配的餐點
          if('一般餐' == $meal_instruction_log_m_row->ml01) {
            $generate_data["meal_instruction_1"] = "中";
          }
        }
      }
    }
    // 餐點內容 End //

    /* 20230922
    // ymca沒有代餐
    if(NULL != $meal_instruction_log_mp_row->mil_mp01) {
      $generate_data['meal_replacement'] = $meal_instruction_log_mp_row->mil_mp01; // 是否代餐
    }
      
    if(NULL != $meal_instruction_log_mp_row->mil_mp01_type) {
      $generate_data['meal_replacement_type'] = meal_instruction_opt_setup("radio", "mil_mp01_type", $meal_instruction_log_mp_row->mil_mp01_type); // 代餐種類
    }
    */
          
    // 判斷停餐 Begin，如果今天沒有停餐，就要判斷固定暫停 //
    if(NULL != $meal_instruction_log_s_row->mil_s01) {
      $generate_data['is_send'] = $meal_instruction_log_s_row->mil_s01; // 是否停餐
      if($meal_instruction_log_s_row->mil_s01 == "Y") {
        if($meal_instruction_log_p_row->mil_p01_arr != NULL) {
          switch ($order_type) {
            case 1: // 送餐-便當
              if(in_array($week_day, $meal_instruction_log_p_row->mil_p01_arr)) {
                $generate_data['is_send'] = "N"; // 是否停餐
              }
              break;
            case 2: // 送餐-代餐
              // if(NULL != $meal_instruction_log_mp_row) {
              //   if("N" == $meal_instruction_log_mp_row->mil_mp01) {
              //     $generate_data['is_send'] = "N"; // 是否停餐
              //   }
              //   else {
              //     if(in_array($week_day , $meal_instruction_log_p_row->mil_p01_arr)) {
              //       $generate_data['is_send'] = "N"; // 是否停餐
              //     }
              //   }
              // }
              break;
            case 3: // 補班
              // 如果有一次性異動的話，就看要不要送餐，如果沒有的話，就要判斷補班那天是否要送餐
              if(!empty($meal_instruction_log_d_row)) {
                $generate_data['is_send'] = $meal_instruction_log_d_row->mil_d01; // 是否停餐
              }
              else {
                if(in_array($makeup_date, $meal_instruction_log_p_row->mil_p01_arr)) {
                  $generate_data['is_send'] = "N"; // 是否停餐
                }
              }
              break;
            case 4: // 不送餐
              // 如果有一次性異動的話，就看要不要送餐，不然全都不送餐
              if(!empty($meal_instruction_log_d_row)) {
                $generate_data['is_send'] = $meal_instruction_log_d_row->mil_d01; // 是否停餐
              }
              else{
                $generate_data['is_send'] = "N"; // 是否停餐
              }
              break;
          }
        }  
      }
    }
    // 判斷停餐 End //

    // 查 BUG 用 
    if($sec_s_num == '2018') {
      // u_var_dump($meal_order_data[$i]);
      // u_var_dump($meal_instruction_log_p_row->mil_p01_arr[0]);
      // u_var_dump($meal_instruction_log_m_row);
      // u_var_dump($meal_instruction_log_mp_row);
      // u_var_dump($meal_instruction_log_s_row);
      // u_var_dump($meal_instruction_log_p_row);
      // u_var_dump($meal_instruction_log_d_row);
      // die();
    }

    // 步驟4 End //
    
    return array(true, '', $generate_data);
  }  
  // **************************************************************************
  //  函數名稱: _init_date()
  //  函數功能: 產生五種日期
  //  程式設計: kiwi
  //  設計日期: 2023-09-24
  // **************************************************************************
  public function _init_date($produce_date) {
    list($week_day, $week_day_str) = $this->_judge_week_day($produce_date);
    list($order_type, $makeup_date) = $this->_judge_meal_order($produce_date);
    $lunar_day = $this->_judge_lunar_date($produce_date);

    $this->week_day = $week_day;
    $this->week_day_str = $week_day_str;
    $this->lunar_day = $lunar_day;
    $this->order_type = $order_type;
    $this->makeup_date = $makeup_date;
    $this->produce_date = $produce_date;
    return;
  }
  // **************************************************************************
  //  函數名稱: _judge_week_day()
  //  函數功能: 判斷今天是星期幾
  //  程式設計: kiwi
  //  設計日期: 2023-09-25
  // **************************************************************************
  public function _judge_week_day($produce_date) {
    $week_day = date("w", strtotime($produce_date));
    $week_day_str = strtolower(date("D", strtotime($produce_date)));    
    if($week_day_str == 'sun' or $week_day_str == 'sat') { // 當產生時間為星期六 或 星期日的時候，比照禮拜五的內容產生
      $week_day_str = "fri";
    }
    return array($week_day, $week_day_str);
  }
  // **************************************************************************
  //  函數名稱: _judge_meal_order()
  //  函數功能: 判斷今天的農曆日期
  //  程式設計: kiwi
  //  設計日期: 2023-09-25
  // **************************************************************************
  public function _judge_meal_order($produce_date) {
    $order_type = 1;
    $makeup_date = NULL;

    $meal_order_type_row = $this->meal_order_date_type_model->get_meal_order_date_type($produce_date);    
    if(NULL != $meal_order_type_row) { // 判斷產生日期的類型
      $order_type = $meal_order_type_row->mlo_dt02;
      if(3 == $meal_order_type_row->mlo_dt02) { // 如果是補班的話，要知道是補哪一天的班
        $makeup_date = date("w", strtotime($meal_order_type_row->mlo_dt03));
      }
    }
    return array($order_type, $makeup_date);
  }
  // **************************************************************************
  //  函數名稱: _judge_lunar_date()
  //  函數功能: 判斷今天的農曆日期
  //  程式設計: kiwi
  //  設計日期: 2023-09-22
  // **************************************************************************
  public function _judge_lunar_date($produce_date) {
    $lunar = new Lunar();
    $today_split = explode("-" , $produce_date);
    $lunar_day = $lunar->convertSolarToLunar($today_split[0], $today_split[1], $today_split[2])[2];
    return $lunar_day;
  }
  // **************************************************************************
  //  函數名稱: meal_order()
  //  函數功能: 處理訂單
  //  程式設計: Kiwi
  //  設計日期: 2023-09-25
  // **************************************************************************
  public function meal_order($msel) {
    switch($msel) {
      case "add":
        $this->_add_meal_order();
        break;
      case "chk":
        $this->_chk_meal_order();
        break;
      case "upd":
        $this->_upd_meal_order();
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: add_meal_order()
  //  函數功能: 補訂單
  //  程式設計: Kiwi
  //  設計日期: 2021/06/29
  // **************************************************************************
  public function _add_meal_order() {
    $data = NULL;
    $rtn_msg = 'ok';
    $produce_date = $this->input->post("produce_date");
    $service_case_row = $this->service_case_model->get_all_by_sec03();
    $meal_order_row = $this->meal_order_model->get_by_date();
    $all_service_row = array_column($service_case_row , "s_num");
    $exisits_service_row = array_column($meal_order_row , "sec_s_num");
    $service_diff = array_unique(array_diff($all_service_row , $exisits_service_row));
    
    if(NULL != $service_diff) {
      list($order_data , $service_diff, $sec_data) = $this->_run_makeup_order($service_diff , $produce_date);  
      if(NULL != $order_data) {
        if(!$this->meal_order_model->add_meal_order($order_data)) {
          $rtn_msg = $this->lang->line('add_ng'); // 新增失敗
        }
      }
    }
    
    if(NULL == $service_diff or NULL == $data) {
      $rtn_msg = 'none';  
    }
    
    echo $rtn_msg;
    return; 
  }
  // **************************************************************************
  //  函數名稱: _upd_meal_order
  //  函數功能: 一鍵更新異動資料
  //  程式設計: Kiwi
  //  設計日期: 2021/09/23
  // **************************************************************************
  public function _upd_meal_order() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $date_arr = NULL;
    $upd_info_str = '';
    
    $v = $this->input->post(); 
    if('month' == $v['q_type']) {
      $date_arr = get_month_date($v['q_month']);
    }
    if('date' == $v['q_type']) {
      $date_arr = period_date($v['q_start_date'] , $v['q_end_date']);
    }
    
    $time_start = date('Y-m-d H:i:s');
    if(NULL != $date_arr) {
      foreach ($date_arr as $k => $date) {
                
        // 1. 先處理補訂單 Begin //
        $all_service_row = array();
        $exisits_service_row = array();
        $service_case_row = $this->service_case_model->get_all_by_sec03($date);
        $meal_order_row = $this->meal_order_model->get_by_date($date);
        if(NULL == $meal_order_row or NULL == $service_case_row) {
          continue;
        }
        
        // 備份原始訂單 Begin //
        $daily_order_row = $this->meal_order_model->get_order_by_date_no_type($date); // 拉訂單資料，不管中午送還是晚上送      
        $this->meal_order_model->save_meal_order_hist($daily_order_row); // 保存訂單歷史紀錄
        // 備份原始訂單 End //
        
        $all_service_row = array_column($service_case_row , "s_num");
        $exisits_service_row = array_column($meal_order_row , "sec_s_num");
        $service_diff = array_unique(array_diff($all_service_row , $exisits_service_row));          
        if(NULL == $service_diff) {
          continue;
        }
        list($order_data , $service_diff , $sec_data) = $this->_run_makeup_order($service_diff , $date);  
        if(NULL != $order_data) {
          if(!$this->meal_order_model->add_meal_order($order_data)) {
            $rtn_msg = $this->lang->line('add_ng'); // 新增失敗
            echo $rtn_msg;
            return;
          }
        }
        // 1. 先處理補訂單 End //
        
        // 2. 異動更新 Begin //
        $daily_order_row = $this->meal_order_model->get_order_by_date_no_type($date);
        $meal_order_data = $this->_run_log_confirm($daily_order_row , $date);
        if(!$this->meal_order_model->upd_meal_order($meal_order_data)) {
          $rtn_msg = $this->lang->line('add_ng'); // 新增失敗
          echo $rtn_msg;
          return;
        } 
        // 2. 異動更新 End //
        
        // 3. 回傳補訂單結果 Begin //
        $upd_info_str .= "<h2 class='maTB10'>{$date} 補訂單服務</h2>";
        $upd_info_str .= "<table class='table-bordered table-striped table-hover table-sm' style='width:100%'>";
        $upd_info_str .= "  <thead>";
        $upd_info_str .= "     <tr class='thead-light'>";
        $upd_info_str .= "       <th class='text-nowrap'>案主名稱</th>";
        $upd_info_str .= "       <th class='text-nowrap'>服務現況</th>";
        $upd_info_str .= "       <th class='text-nowrap'>服務開始日</th>";
        $upd_info_str .= "     </tr>";
        $upd_info_str .= "  </thead>";
        $upd_info_str .= "  <tbody>";
        if(NULL != $sec_data) {
          foreach ($sec_data as $k_sec => $v_sec) {
            $upd_info_str .= " <tr>";
            $upd_info_str .= "   <td>{$v_sec['ct_name']}</td>";
            $upd_info_str .= "   <td>{$v_sec['sec01_str']}-{$v_sec['sec04_str']}</td>";
            $upd_info_str .= "   <td>{$v_sec['sec02']}</td>";
            $upd_info_str .= " </tr>";
          } 
        }
        else {
          $upd_info_str .= '  <tr><td class="alert alert-warning mt-2" colspan="99">無服務案需更新異動資料!!</td></tr>';
        }
        $upd_info_str .= "  </tbody>";
        $upd_info_str .= "</table>";
        // 3. 回傳補訂單結果 EnD //
      } 
    }
    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff>=60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }   
    
    $upd_info_str .= "<h3 class='maTB10 text-right'>執行時間：{$time_diff}</h3>";
    echo json_encode($upd_info_str);
    return;
  }
  // **************************************************************************
  //  函數名稱: chk_meal_order
  //  函數功能: 確認是否已經生產過
  //  程式設計: Kiwi
  //  設計日期: 2021/06/29
  // **************************************************************************
  public function _chk_meal_order() {
    $meal_order_row = $this->meal_order_model->get_all_by_date();
    echo $meal_order_row->cnt;
    return;
  }
  // **************************************************************************
  //  函數名稱: _run_makeup_order()
  //  函數功能: 跑補訂單資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-13
  // **************************************************************************
  public function _run_makeup_order($service_diff , $produce_date) {
    $i = 0;
    $sec_data = NULL;
    $order_data = NULL;
    if(NULL != $service_diff) {
      foreach ($service_diff as $k => $v) {
        $service_case_row = $this->service_case_model->get_one($v);
        if(NULL != $service_case_row) {
          
          // 判斷服務案是已開始或結束 Begin //
          if(NULL != $service_case_row->sec03) {
            if(strtotime($produce_date) > strtotime($service_case_row->sec03)) { // 當生產日期大於結案日期，代表該案已結案了
              unset($service_diff[$k]);
              continue;
            }
          }

          if(NULL != $service_case_row->sec02) {
            if(strtotime($produce_date) < strtotime($service_case_row->sec02)) { // 當生產日期小於開始服務日，代表該案還未開始
              unset($service_diff[$k]);
              continue;
            }
          }
          // 判斷服務案是已開始或結束 Begin //

          // 用服務類型判斷出餐時段
          $sec_data[$i]['ct_name'] = $service_case_row->ct_name;
          $sec_data[$i]['sec01_str'] = $service_case_row->sec01_str;
          $sec_data[$i]['sec02'] = $service_case_row->sec02;
          $sec_data[$i]['sec04_str'] = $service_case_row->sec04_str;
          $order_data[$i]['b_empno'] = $_SESSION['acc_s_num'];
          $order_data[$i]['b_date'] = date("Y-m-d H:i:s");
          $order_data[$i]['b_empno'] = $_SESSION['acc_s_num'];
          $order_data[$i]['ct_s_num'] = $service_case_row->ct_s_num;
          $order_data[$i]['sec_s_num'] = $service_case_row->s_num;
          $order_data[$i]['mlo01'] = $produce_date;
          $order_data[$i]['mlo02'] = $service_case_row->sec04_time;
          $i++;
        }
        else {
          unset($service_diff[$k]);
        }
      }
    }
    return(array($order_data , $service_diff , $sec_data)); 
  }
  // **************************************************************************
  //  函數名稱: que_meal_order_status()
  //  函數功能: 查詢每個月的訂單生產狀況
  //  程式設計: kiwi
  //  設計日期: 2023-10-18
  // **************************************************************************
  public function que_meal_order_status() {
    $que_month = $this->input->post('que_month');
    $res_data = $this->_get_meal_order_status($que_month);
    echo json_encode($res_data);
    return;
  }
  // **************************************************************************
  //  函數名稱: que_meal_order_status()
  //  函數功能: 查詢每個月的訂單生產狀況
  //  程式設計: kiwi
  //  設計日期: 2023-10-18
  // **************************************************************************
  public function _get_meal_order_status($que_month) {
    $res_data = NULL;
    list($year, $month) = explode("-", $que_month);
    $que_data_arr = $this->meal_order_date_type_model->get_all_by_month($year, $month, $date_type=NULL, $mlo_dt04="Y");
    if(!empty($que_data_arr)) {
      foreach ($que_data_arr as $k => $v) {
        $i = 0;
        foreach ($que_data_arr as $k => $v) {
          $res_data[$i]['title'] = "訂單已產生";
          $res_data[$i]['start'] = $v['mlo_dt01'];
          $res_data[$i]['allDay'] = true;
          $res_data[$i]['display'] = "background";
          $res_data[$i]['is_produce'] = "0";
          $i++;
        } 
      }
    }
    return $res_data;
  }

  function __destruct() {
    $url_str[] = 'be/daily_work/produce';
    $url_str[] = 'be/daily_work/meal_order';
    $url_str[] = 'be/daily_work/log_confirm';
    $url_str[] = 'be/daily_work/que_meal_order_status';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 不顯示 foot
    }
  }
}