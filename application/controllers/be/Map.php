<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Map extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('clients_model'); // 案主資料
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class()); // controllers name
    $this->tpl->assign('tv_menu_title','Map');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2018/4/25
  // **************************************************************************
  public function index() {
    $route_row = $this->route_model->get_all();
    $clients_row = $this->clients_model->get_all_with_route();
    $this->tpl->assign('tv_now_date',date('Y-m-d H:i:s'));
    $this->tpl->assign('tv_route_row',$route_row);
    $this->tpl->assign('tv_clients_row',$clients_row);
    $this->tpl->display("be/map.html");
    return;
  }

  function __destruct() {
    $this->zi_init->footer('be'); // 網頁 foot
  }
}
