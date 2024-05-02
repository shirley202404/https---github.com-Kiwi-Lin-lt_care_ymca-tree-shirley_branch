<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sw extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->load->model('social_worker_model');
    $this->load->model('clients_model');
  }
  // **************************************************************************
  //  函數名稱: index()
  //  函數功能:
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function index() {
    return;
  }
  // **************************************************************************
  //  函數名稱: get_all_clients()
  //  函數功能: 拿取全部案主資料
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function get_all_clients() {
    list($identity, $sw_s_num) = token_descry();
    if(!empty($sw_s_num)) {
      $clients_row = $this->clients_model->get_all_api($sw_s_num);
	    echo json_encode($clients_row);
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: save_clients()
  //  函數功能: 儲存案主資料
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function save_clients() {
    list($identity, $sw_s_num) = token_descry();
    if(!empty($sw_s_num)) {
      $ct94 = NULL;
      if(!empty($_FILES['ct94'])) {
        $config['upload_path'] = FCPATH.'pub/uploads/client_picture/';
        $config['allowed_types'] = '*'; // jpg|png|jpeg|bmp;
        $config['max_size'] = 6100;
        $config['max_width'] = 0;
        $config['max_height'] = 0;
        $config['encrypt_name'] = TRUE;
        $this->load->library('upload', $config);
        if(!$this->upload->do_upload('ct94')) {
          $error = array('error' => $this->upload->display_errors());
          echo $error['error'];
          return;
        }
        $fileMetaData = $this->upload->data();
        $ct94 = $fileMetaData['file_name'];
      }
	    $this->clients_model->save_api_add($ct94 , $sw_s_num);
	  }
	  return;
  }
  // **************************************************************************
  //  函數名稱: upd_clients()
  //  函數功能: 更新案主資料
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function upd_clients() {
    list($identity, $sw_s_num) = token_descry();
    if(!empty($sw_s_num)) {
	    $this->clients_model->save_api_upd($sw_s_num);
    }
    return;
  }

  function __destruct() {
    return;
  }
}