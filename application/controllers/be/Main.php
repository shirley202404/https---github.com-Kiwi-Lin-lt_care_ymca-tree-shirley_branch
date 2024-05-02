<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('route_model'); // 路線資料
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('service_case_model'); // 開案服務資料
    $this->load->model('meal_instruction_log_h_model'); // 餐點異動資料
	  $mrand_str = $this->config->item('rand_str_8');
	  $this->tpl->assign('tv_rand_str',$mrand_str);
	  $this->tpl->assign('tv_ct_name',$this->router->fetch_class()); // controllers name
	  $this->tpl->assign('tv_menu_title','Main');
	  $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
  }
  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2018/4/25
  // **************************************************************************
	public function index()	{
	  $this->tpl->assign('tv_now_date',date('Y-m-d H:i:s'));
    $this->tpl->assign('tv_service_case_link',be_url()."service_case?que_s_num=");
    $this->tpl->assign('tv_get_notify_clients_link',be_url()."main/get_notify_clients");
	  $this->tpl->display("be/main.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: get_notify_clients()
  //  函數功能: 取得已經停餐超過三個月須要停案的案主
  //  程式設計: Kiwi
  //  設計日期: 2023/9/18
  // **************************************************************************
	public function get_notify_clients() {
    session_write_close(); 
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $rtn_tbody_str = '';
    
    // 檢查是否已經停餐超過三個月
    $_GET['que_sec03'] = "N";
    $clients_arr = $this->clients_model->get_download2_data(); 
    if(NULL != $clients_arr) {
      foreach ($clients_arr as $k => $v) {
        $clients_arr[$k]['mil_s02'] = '';
        $meal_instruction_log_s_row = $this->meal_instruction_log_h_model->get_last_s_by_s_num($v['sec_s_num'], date("Y-m-d")); // 取得最後一筆停餐異動資料

        if(empty($meal_instruction_log_s_row)) {
          unset($clients_arr[$k]);
          continue;
        }

        $today_obj = new DateTime(date("Y-m-d"));
        $chk_mil_s02_obj = new DateTime($meal_instruction_log_s_row->mil_s02);
        $intvl_date = floor(abs($today_obj->getTimestamp() - $chk_mil_s02_obj->getTimestamp()) / 86400);
        if($meal_instruction_log_s_row->mil_s01 == "N" and $intvl_date >= 90) {
          $clients_arr[$k]['mil_s02'] = $meal_instruction_log_s_row->mil_s02;
        }
        else {
          unset($clients_arr[$k]);
          continue;
        }
      }
      usort($clients_arr, function($a, $b) {
        return $b['mil_s02'] <=> $a['mil_s02'];
      });
    }

    $total_cnt = count($clients_arr);
    $each_sec01_cnt[1] = 0; // 長照案
    $each_sec01_cnt[2] = 0; // 公所案
    $each_sec01_cnt[3] = 0; // 朝清案
    $each_sec01_cnt[4] = 0; // 自費案

    if(!empty($clients_arr)) {
      foreach ($clients_arr as $k => $v) {
        $rtn_tbody_str .= "<tr>";
        $rtn_tbody_str .= "  <td>{$v['ct14']}</td>";
        $rtn_tbody_str .= "  <td>{$v['ct01']}{$v['ct02']}</td>";
        $rtn_tbody_str .= "  <td>{$v['sec01_str']}</td>";
        $rtn_tbody_str .= "  <td>{$v['sec02']}</td>";
        $rtn_tbody_str .= "  <td>{$v['sec04_str']}</td>";
        $rtn_tbody_str .= "  <td>{$v['mil_s02']}</td>";
        $rtn_tbody_str .= "  <td align='right'>";
        $rtn_tbody_str .= "    <button type='button' class='btn btn-info btn-sm btn_sec' data-que_s_num='{$v['sec_s_num']}'>查看</button></td>";
        $rtn_tbody_str .= "  </td>";
        $rtn_tbody_str .= "</tr>";
        $each_sec01_cnt[$v['sec01']] += 1;
      }
    }

    if("" == $rtn_tbody_str) {
      $rtn_tbody_str .= "<tr>";
      $rtn_tbody_str .= "  <td colspan='7' class='table-warning'>目前沒有任何資料</td>";
      $rtn_tbody_str .= "</>";
    }

    $rtn_static_str = "<span class='text-secondary'>總數：{$total_cnt}</span>｜
                       <span class='text-primary'>長照案：{$each_sec01_cnt[1]}</span>｜
                       <span class='text-success'>公所案：{$each_sec01_cnt[2]}</span>｜
                       <span class='text-warning'>朝清案：{$each_sec01_cnt[3]}</span>｜
                       <span class='text-info'>自費：{$each_sec01_cnt[4]}</span>";

    echo json_encode(array("rtn_tbody_str" => $rtn_tbody_str, "rtn_static_str" => $rtn_static_str));
    return;
  }

  function __destruct() {
    $url_str[] = 'be/main/get_notify_clients';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 不顯示 foot
    }  
  }
}
