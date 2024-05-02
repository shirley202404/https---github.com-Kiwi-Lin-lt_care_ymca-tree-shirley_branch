<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Fileupload extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    //$this->load->library("UploadHandler");
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
  public function index() {
    $this->tpl->assign('tv_save_link',be_url().'fileupload/upload/');
    $this->tpl->display("be/fileupload/fileupload.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upload()
  //  函數功能: 檔案上傳
  //  程式設計: Tony
  //  設計日期: 2018/3/16
  // **************************************************************************
  public function upload($std_name=NULL,$file_type=NULL)  {
    switch ($file_type) {
      case "img":
        $maccept_file_types = '/\.(gif|jpe?g|png)$/i';
        break;
      case "xls":
        $maccept_file_types = '/\.(xls|xlsx)$/i';
        break;
      case "zip":
        $maccept_file_types = '/\.(zip)$/i';
        break;
      case "csv":
        $maccept_file_types = '/\.(csv)$/i';
        break;
      default:
        $maccept_file_types = '/\.(csv|pdf|xls|xlsx|doc|docx|ppt|pps|pptx|gif|jpe?g|png)$/i';
        break;
    }
    
    switch ($std_name) {
      case "harvest_":      // 善耕
        $upload_dir = FCPATH.'pub/uploads/harvest/';
        $std_name = 'origin';
        break;
      case "seal_":      // 印章
        $upload_dir = FCPATH.'upload_files/seal/';
        $std_name = 'origin';
        break;
      case "donate_":      // 捐款資料
        $upload_dir = FCPATH.'upload_files/donate/';
        $std_name = 'origin';
        break;
      case "donate_import_":      // 捐款資料(線下資料匯入)
        $upload_dir = FCPATH.'upload_files/donate_import/';
        break;
      case "clients_": // 案主資料
        $upload_dir = FCPATH.'upload_files/clients/';
        $std_name = 'origin';
        break;
      case "delivery_person_": // 送餐員資料
        $upload_dir = FCPATH.'pub/uploads/delivery_person/';
        $std_name = 'origin';
        break;
      case "verification_person_": // 核備人員資料
        $upload_dir = FCPATH.'upload_files/verification_person/';
        $std_name = 'origin';
        break;
    }
    
    $options = array ('accept_file_types' => $maccept_file_types,
                                      'upload_dir' => $upload_dir, // 要用絕對路徑，不能用網址
                                      'upload_url' => be_url().'fileupload/upload/',
                                      'max_file_size' => 11000000, // 11MB
                                      'std_name' => $std_name,
                                      //'max_number_of_files' => 1 // 可上傳的檔案數量
                                     );
    $this->load->library("UploadHandler",$options);
    return;
  }
  // **************************************************************************
  //  函數名稱: album_upload()
  //  函數功能: 相片檔案上傳
  //  程式設計: Tony
  //  設計日期: 2018/8/13
  // **************************************************************************
  public function album_upload($std_name=NULL,$file_type='img',$s_num=NULL) {
    switch ($file_type) {
      case "img":
        $maccept_file_types = '/\.(gif|jpe?g|JPG|png|jpg)$/i';
        break;
      case "xls":
        $maccept_file_types = '/\.(xls|xlsx)$/i';
        break;
      case "zip":
        $maccept_file_types = '/\.(zip)$/i';
        break;
      default:
        $maccept_file_types = '/\.(pdf|xls|xlsx|doc|docx|ppt|pps|pptx|gif|jpe?g|png)$/i';
        break;
    }
    $options = array ('accept_file_types' => $maccept_file_types,
                      'script_url' => be_url() . "fileupload/album_upload/origin/img/{$s_num}",
                      'upload_dir' => FCPATH . "pub/uploads/albums/{$s_num}/", // 要用絕對路徑，不能用網址
                      'upload_url' => pub_url('') . "/uploads/albums/{$s_num}/",
                      'max_file_size' => 11000000, // 11MB
                      'std_name' => $std_name,
                      'max_number_of_files' => 50 // 可上傳的檔案數量
                     );
    $this->load->library("UploadHandler",$options);
    return;
  }
  // **************************************************************************
  //  函數名稱: hrml_upload()
  //  函數功能: HTML檔案上傳
  //  程式設計: Tony
  //  設計日期: 2022/1/5
  // **************************************************************************
	public function html_upload($std_name=NULL,$file_type='html',$s_num=NULL)	{
	  switch ($file_type) {
	    case "html":
	      $maccept_file_types = '/\.(htm?l)$/i';
	      break;
	  }
    $options = array ('accept_file_types' => $maccept_file_types,
                      'script_url' => be_url() . "fileupload/html_upload/origin/html/{$s_num}/",
                      'upload_dir' => FCPATH . "upload_files/clients_html/", // 要用絕對路徑，不能用網址
                      'upload_url' => be_url() . "fileupload/html_upload/origin/html/{$s_num}/",
                      'max_file_size' => 21000000, // 21MB
                      'std_name' => $std_name,
                      'max_number_of_files' => 100 // 可上傳的檔案數量
                     );
	  $this->load->library("UploadHandler",$options);
	  return;
	}
  
  function __destruct() {
    //$this->zi_init->footer('be'); // 網頁 foot
    return;
  }
}
