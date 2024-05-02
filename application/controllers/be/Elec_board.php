<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Elec_board extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁head
    $this->load->model('route_model'); // 送餐資料
    $this->load->model('daily_work_model'); // 配送單資料
    $this->load->model('daily_production_order_model'); // 餐條順序設定資料
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
    $this->tpl->assign('tv_return_link',be_url()."elec_board/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_month',date('m')); // 系統本月
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_company_name',"弗傳慈心基金會");
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->test_route = array('3','48','49','50');
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
    $this->tpl->assign('tv_title','電子看板');
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_prn_link',be_url().'elec_board/prn/');
    $this->tpl->display("be/elec_board_input.html");
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
    $menu_title = "送餐總表";
    
    // 送餐總表 BEGIN //
    $stop_str = '';
    $stop_three_str = '';
    $restore_str = '';
    $daily_shipment_data = [];
    $today = date("Y-m-d");
    $prev_3_day = date('Y-m-d', strtotime(' -3 day'));
    $daily_shipment_row_Y = $this->daily_work_model->get_daily_shipment_by_reh("Y"); //  餐點配送單
    $daily_shipment_row_N = $this->daily_work_model->get_daily_shipment_by_reh("N"); //  餐點配送單
    if(NULL != $daily_shipment_row_Y) {        
      foreach($daily_shipment_row_Y as $k => $v) {
        $daily_abnormal_row = $this->daily_work_model->get_daily_abnormal_by_reh($v['sec_s_num']); // 停復餐名單
        if(NULL != $daily_abnormal_row) {
          if($daily_abnormal_row->mil_s01 == "Y") {
            if($daily_abnormal_row->mil_s02 >= $prev_3_day && $daily_abnormal_row->mil_s02 <= $today) {
              if(strpos($restore_str , "{$v['ct_name']}({$v['dys02']})") === false) {
                if($restore_str != '') {
                  $restore_str .= "、";
                }
                if($v['sec01'] == 3) {
                  $sec99 = str_replace("&nbsp;", '', trim(strip_tags($v['sec99'])));
                  $restore_str .= "<span class='badge badge-warning' style='font-size: 16px'>{$v['ct_name']}({$v['dys02']}-{$sec99})</span>";
                }
                else {
                  $restore_str .= "<span>{$v['ct_name']}({$v['dys02']})</span>";
                }
              }
            }
            array_push($daily_shipment_data , $v);            
          } 
        }
      }
    }
      
    if(NULL != $daily_shipment_row_N) {
      foreach ($daily_shipment_row_N as $k => $v) {
        $daily_abnormal_row = $this->daily_work_model->get_daily_abnormal_by_reh($v['sec_s_num']); // 停復餐名單
        if(NULL != $daily_abnormal_row and $daily_abnormal_row->mil_s01 == "N") {
          if($daily_abnormal_row->mil_s02 >= $prev_3_day && $daily_abnormal_row->mil_s02 <= $today) {
            if(strpos($stop_three_str , "{$v['ct_name']}({$v['dys02']})") === false) {
              if($stop_three_str != '') {
                $stop_three_str .= "、";
              }
              if($v['sec01'] == 3) {
                $sec99 = str_replace("&nbsp;", '', trim(strip_tags($v['sec99'])));
                $stop_three_str .= "<span class='badge badge-warning' style='font-size: 16px'>{$v['ct_name']}({$v['dys02']}-{$sec99})</span>";
              }
              else {
                $stop_three_str .= "<span>{$v['ct_name']}({$v['dys02']})</span>";
              }
            }
          }
          if(strpos($stop_str , "{$v['ct_name']}({$v['dys02']})") === false) {
            if($stop_str != '') {
            $stop_str .= "、";
            }
            if($v['sec01'] == 3) {
              $sec99 = str_replace("&nbsp;", '', trim(strip_tags($v['sec99'])));
              $stop_str .= "<span class='badge badge-warning' style='font-size: 16px'>{$v['ct_name']}({$v['dys02']}-{$sec99})</span>";
            }
            else {
              $stop_str .= "<span>{$v['ct_name']}({$v['dys02']})</span>";
            }
          }
        }
      }
    }
    // 送餐總表 END //

    // 代餐數量 BEGIN //
    $route_str = '';
    $week_day = date("w");
    $sec04 = array(1,2,3); // 服務類型: 午餐，午晚，晚
    $meal_replacement_data = NULL;
    $item_replacement_data = NULL;
    $meal_replace_arr = array(3,4,9); // 熟代
    $reh_s_num = $this->input->post('reh_s_num');
    $meal_replace_type = radio_value("mil_mp01_type"); 
    
    $route_row = $this->route_model->get_all_without_test();
    $meal_replacement_row = $this->daily_work_model->get_meal_replacement_data();
    foreach ($route_row as $kr => $vr) {
      if($vr['s_num'] == $reh_s_num) { 
        $route_str = $vr['reh01'];
        foreach ($meal_replace_type as $k => $v) {
          if(in_array($k , $meal_replace_arr)) { // 如果是熟代
            $meal_replacement_data[$vr['reh05']][$k]["total"] = 0;
            $meal_replacement_data[$vr['reh05']][$k]["each"][$vr['s_num']]["reh01"] = $vr['reh01'];
            $meal_replacement_data[$vr['reh05']][$k]["each"][$vr['s_num']]["num"] = 0;
          }
          else {
            foreach ($sec04 as $v_sec) {
              $item_replacement_data[$v_sec][$vr['reh05']][$k]["total"] = 0;
              $item_replacement_data[$v_sec][$vr['reh05']][$k]["each"][$vr['s_num']]["reh01"] = $vr['reh01'];
              $item_replacement_data[$v_sec][$vr['reh05']][$k]["each"][$vr['s_num']]["num"] = 0;
            } 
          }
        } 
      }
    } 
    
    if(NULL != $meal_replacement_row) {
      foreach ($meal_replacement_row as $k => $v) {
        if(NULL != $v['reh_s_num'] and !in_array($v['reh_s_num'] , $this->test_route)) {
          if(in_array($v['ct_mp02'] , $meal_replace_arr)) { // 如果是熟代
            $meal_replacement_data[$v['reh05']][$v['ct_mp02']]["each"][$v['reh_s_num']]["num"] += 1;
            $meal_replacement_data[$v['reh05']][$v['ct_mp02']]["total"] += 1;
          }
          else {
            $item_replacement_data[$v['ct_mp07']][$v['reh05']][$v['ct_mp02']]["each"][$v['reh_s_num']]["num"] += 1;
            $item_replacement_data[$v['ct_mp07']][$v['reh05']][$v['ct_mp02']]["total"] += 1;
          }
        }
      } 
    }
    // 代餐數量 END //
    // u_var_dump($stop_three_str);
    // u_var_dump($meal_replacement_data);
    // u_var_dump($item_replacement_data);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 列印
    $this->tpl->assign('tv_menu_title',$menu_title);
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_route_str',$route_str);
    $this->tpl->assign('tv_stop_str',$stop_str);
    $this->tpl->assign('tv_stop_three_str',$stop_three_str);
    $this->tpl->assign('tv_restore_str',$restore_str);
    $this->tpl->assign('tv_prn_date',date('Y-m-d H:i:s'));
    $this->tpl->assign('tv_prn_emp',$_SESSION['acc_name']);
    $this->tpl->assign('tv_daily_shipment_data',$daily_shipment_data);
    $this->tpl->assign('tv_meal_replacement_data',$meal_replacement_data);
    $this->tpl->assign('tv_item_replacement_data',$item_replacement_data);
    $this->tpl->assign('tv_exit_link',be_url().'elec_board/');
    $this->tpl->display("be/elec_board_prn.html");
    return;
  }
  
  function __destruct() {
    $url_str[] = '';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // ???? foot
    }
  }
}
