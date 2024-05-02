<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Que extends CI_Controller {
  public function __construct() {
    parent::__construct();
    set_time_limit(300); // 限制5分鐘
    ini_set('memory_limit', '3072M'); // 先給大一點,避免記憶體不夠用
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('daily_work_model'); // 每日送餐資料
    $this->load->model('delivery_person_model'); // 外送員基本資料檔
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
  //  函數名稱: get_all_route()
  //  函數功能: WEB 傳輸所有路徑資料
  //  程式設計: Kiwi
  //  設計日期: 2022/02/13
  // **************************************************************************
  public function get_all_route() {
    $reh_data = NULL;
    $reh_row = $this->route_model->get_all_without_test();
    foreach ($reh_row as $k => $v) {
      $reh_data[$k]["s_num"] = $v['s_num'];
      $reh_data[$k]["reh_name"] = $v['reh02'];
      $reh_data[$k]["reh_category"] = $v['reh03'];
      $reh_data[$k]["reh_time"] = $v['reh05'];
    }
    echo json_encode($reh_data);
    return;
  }

  // **************************************************************************
  //  函數名稱: get_client_route()
  //  函數功能: WEB 傳輸所有外送員資料
  //  程式設計: Kiwi
  //  設計日期: 2022/02/13
  // **************************************************************************
  public function get_client_route() {
    $reb_data = NULL;
    $reb_row = $this->route_model->api_get_route_b();
    foreach ($reb_row as $k => $v) {
      $reb_data[$k]["ct_s_num"] = $v['ct_s_num'];
      $reb_data[$k]["ct_order"] = $v['reb01'];
      $reb_data[$k]["reh_s_num"] = $v['reh_s_num'];
    }
    echo json_encode($reb_data);
    return;
  }

  // **************************************************************************
  //  函數名稱: get_all_client()
  //  函數功能: WEB 傳輸所有案主資料
  //  程式設計: Kiwi
  //  設計日期: 2022/02/13
  // **************************************************************************
  public function get_all_client() {
    $ct_data = NULL;
    $clients_row = $this->clients_model->get_all();
    foreach ($clients_row as $k => $v) {
      $ct_data[$k]["s_num"] = $v['s_num'];
      $ct_data[$k]["ct_name"] = "{$v['ct01']}{$v['ct02']}";
      $ct_data[$k]["ct_lastname"] = $v['ct01'];
      $ct_data[$k]["ct_gender"] = $v['ct04'];
      $ct_data[$k]["ct_lon"] = $v['ct17'];
      $ct_data[$k]["ct_lat"] = $v['ct16'];
      $ct_data[$k]["ct_address"] = "{$v['ct12']}{$v['ct13']}{$v['ct14']}{$v['ct15']}";
      $ct_data[$k]["status"] = $v['is_available'];
    }
    echo json_encode($ct_data);
    return;
  }

  // **************************************************************************
  //  函數名稱: get_all_dp()
  //  函數功能: WEB 傳輸所有外送員資料
  //  程式設計: Kiwi
  //  設計日期: 2022/02/13
  // **************************************************************************
  public function get_all_dp() {
    $dp_data = NULL;
    $dp_row = $this->delivery_person_model->get_all_is_available();
    foreach ($dp_row as $k => $v) {
      $dp_data[$k]["s_num"] = $v['s_num'];
      $dp_data[$k]["dp01"]  = $v['dp01'];
      $dp_data[$k]["dp02"]  = $v['dp02'];
      $dp_data[$k]["dp_img"] = $v['dp12'];
      $dp_data[$k]["dp_reason"] = $v['dp13'];
      $dp_data[$k]["dp_nickname"] = $v['dp11'];
      $dp_data[$k]["dp_experience"] = $v['dp14'];
    }
    echo json_encode($dp_data);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: test()
  //  函數功能: WEB 傳輸所有外送員資料
  //  程式設計: Kiwi
  //  設計日期: 2022/02/13
  // **************************************************************************
  public function test() {
    $this->zi_my_func->web_api_data("daily_shipment");
    return;
  }

  function __destruct() {
    return;
  }
}
