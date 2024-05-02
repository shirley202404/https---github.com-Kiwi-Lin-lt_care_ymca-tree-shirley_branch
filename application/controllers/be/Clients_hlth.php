<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients_hlth extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 header
    $this->load->model('clients_hlth_model'); // 案主營養評估表
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8'); 
    $this->tpl->assign('tv_rand_str',$mrand_str); // html載入的js與css增加亂數，避免抓到cache的檔案
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','案主營養評估表');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_list_btn',$this->lang->line('list')); // 瀏覽按鈕文字
    $this->tpl->assign('tv_disp_btn',$this->lang->line('disp')); // 明細按鈕文字
    $this->tpl->assign('tv_add_btn',$this->lang->line('add')); // 新增按鈕文字
    $this->tpl->assign('tv_cpy_btn',$this->lang->line('cpy')); // 複製按鈕文字
    $this->tpl->assign('tv_upd_btn',$this->lang->line('upd')); // 修改按鈕文字
    $this->tpl->assign('tv_del_btn',$this->lang->line('del')); // 刪除按鈕文字
    $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
    $this->tpl->assign('tv_prn_btn',$this->lang->line('prn')); // 列印按鈕文字
    $this->tpl->assign('tv_prn_this_btn',$this->lang->line('prn_this')); // 列印本頁按鈕文字
    $this->tpl->assign('tv_import_btn',$this->lang->line('import')); // 匯入按鈕文字
    $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
    $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
    $this->tpl->assign('tv_add_link',be_url().'clients_hlth/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."clients_hlth/"); // return 預設到瀏覽畫面
    $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
    $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
    $this->tpl->assign('tv_import_ok',$this->lang->line('import_ok')); // 匯入資料成功!!
    $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
    $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
    $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
    $this->tpl->assign('tv_import_ng',$this->lang->line('import_ng')); // 匯入資料失敗!!
    $this->tpl->assign('tv_today',date('Y-m-d')); // 系統今天日期
    $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/clients_hlth_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    //if('tony' != $_SESSION['acc_user']) { // 開發階段不給客戶使用的時候用
    //  die('趕工中...');
    //}
    return;
  }
  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function index($pg=1,$q_str=NULL) {
    $msel = 'list';
    $this->load->library('pagination');
    if(NULL==$q_str) { // 沒有帶入參數，就開一個新的session
      $this->load->helper('string');
      $q_str = random_string('alnum', 10); // 取得亂數10碼
    }
    if(!isset($_SESSION[$q_str]['que_str'])) {
      $this->_que_start($q_str);
    }
    //u_var_dump($_SESSION[$q_str]);
    $get_data = $this->input->get(); // GET 用
    if(!isset($get_data['que_str'])) {
      $get_data['que_str'] = NULL;
    }
    list($clients_hlth_row,$row_cnt) = $this->clients_hlth_model->get_que($q_str,$pg); // 列出案主營養評估表
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','案主營養評估表瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'clients_hlth/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'clients_hlth/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'clients_hlth/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'clients_hlth/upd/');
    $this->tpl->assign('tv_del_link',be_url().'clients_hlth/del/');
    $this->tpl->assign('tv_prn_link',be_url().'clients_hlth/prn/');
    $this->tpl->assign('tv_download_link',be_url().'clients_hlth/download/');
    $this->tpl->assign('tv_que_link',be_url()."clients_hlth/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'clients_hlth/save/');
    $this->tpl->assign('tv_clients_hlth_row',$clients_hlth_row);
    $config['base_url'] = be_url()."clients_hlth/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/clients_hlth/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/clients_hlth.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $clients_hlth_row = $this->clients_hlth_model->get_one($s_num); // 列出單筆明細資料
    //u_var_dump($clients_hlth_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_hlth_row',$clients_hlth_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'clients_hlth/upd/');
    $this->tpl->display("be/clients_hlth_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function add()	{ // 新增
    $msel = 'add';
    $clients_hlth_row = NULL;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_clients_hlth_row',$clients_hlth_row);
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth/"); // 上一層連結位置
    $this->tpl->display("be/clients_hlth_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function cpy($s_num)	{
    $msel = 'cpy';
    $clients_hlth_row = $this->clients_hlth_model->get_one($s_num);
    //u_var_dump($clients_hlth_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_hlth_row',$clients_hlth_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth/"); // 上一層連結位置
    $this->tpl->display("be/clients_hlth_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function upd($s_num)	{
    $msel = 'upd';
    $clients_hlth_row = $this->clients_hlth_model->get_one($s_num);
    //u_var_dump($clients_hlth_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_clients_hlth_row',$clients_hlth_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."clients_hlth/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."clients_hlth/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."clients_hlth/"); // 上一層連結位置
    $this->tpl->display("be/clients_hlth_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function del($s_num=NULL)	{
    $rtn_msg = $this->clients_hlth_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'clients_hlth/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除,上下架)
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function save($kind=NULL)	{
    switch($kind) {
      case "add":
      case "cpy":
        $this->clients_hlth_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->clients_hlth_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->clients_hlth_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function que($q_str)	{
    ////$data = $this->input->post(); // POST 用 Mark by Tony 2020/7/27
    ////u_var_dump($data);
    ////exit;
    ////if('que'==$data['que_kind']) {
    ////  $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    ////}
    ////$_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    ////$_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    ////redirect(be_url()."clients_hlth/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."clients_hlth/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  private function _que_start($q_str)	{
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載迷你營養評估表
  //  程式設計: kiwi
  //  設計日期: 2022-08-04(七夕ㄟ!!!!!)
  // **************************************************************************
  public function download() {
    $msel = 'download';
    $file_time = date("YmdHis");
    $fd['radio']["ct04"] = array("M", "Y"); // 性別(M=男;Y=女)
    $fd['radio']["cth45_opt"] = array(1, 2); // 營養篩檢分數評估(1=大於或等於12分：表示正常(無營養不良危險性); 2=小於或等於11分：表示可能營養不良)
    $fd['radio']["cth55_1"] = array(1, 0); // 每天至少攝取一份乳製品(1=是;0=否)
    $fd['radio']["cth55_2"] = array(1, 0); // 每週攝取兩份以上的豆類或蛋類(1=是;0=否)
    $fd['radio']["cth55_3"] = array(1, 0); // 每天均吃些肉、魚、雞鴨類(1=是;0=否)
    $fd['radio']["cth02_opt"] = array(1, 2, 3); // MNA合計分數選項(1=MNA ＞23.5具營養良好;2 = MNA 17~23.5具營養不良危險性; 3 = MNA < 17 營養不良)

    $v = $this->input->post();
    $ct05_yy = '';
    $ct05_mm = '';
    $ct05_dd = '';
    $clients_hlth_row = $this->clients_hlth_model->get_one($v['s_num']); // 列出單筆明細資料
    list($b_yy, $b_mm, $b_dd) = explode("-", date("Y-m-d", strtotime($clients_hlth_row->b_date)));
    $b_yy = $b_yy - 1911;

    if(NULL != $clients_hlth_row->ct05) {
      list($ct05_yy, $ct05_mm, $ct05_dd) = explode("-", $clients_hlth_row->ct05);
      $ct05_yy = $ct05_yy - 1911;
    }

    $sample_file = FCPATH."pub/sample/111_client_hlth_sample.docx";
    $save_path = FCPATH."export_file/client_hlth_{$file_time}.docx";

    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    // 案主資訊及填表資訊
    $templateProcessor->setValue('ct_name', "{$clients_hlth_row->ct01}{$clients_hlth_row->ct02}");
    $templateProcessor->setValue('ct05_yy', "{$ct05_yy}");
    $templateProcessor->setValue('ct05_mm', "{$ct05_mm}");
    $templateProcessor->setValue('ct05_dd', "{$ct05_dd}");
    $templateProcessor->setValue('b_yy', "{$b_yy}");
    $templateProcessor->setValue('b_mm', "{$b_mm}");
    $templateProcessor->setValue('b_dd', "{$b_dd}");
    $templateProcessor->setValue('cth02', "{$clients_hlth_row->cth02}");
    $templateProcessor->setValue('cth22', "{$clients_hlth_row->cth22}");
    $templateProcessor->setValue('cth23', "{$clients_hlth_row->cth23}");
    $templateProcessor->setValue('cth24', "{$clients_hlth_row->cth24}");

    // 營養篩檢
    $templateProcessor->setValue('cth31', "{$clients_hlth_row->cth31}");
    $templateProcessor->setValue('cth32', "{$clients_hlth_row->cth32}");
    $templateProcessor->setValue('cth33', "{$clients_hlth_row->cth33}");
    $templateProcessor->setValue('cth34', "{$clients_hlth_row->cth34}");
    $templateProcessor->setValue('cth35', "{$clients_hlth_row->cth35}");
    $templateProcessor->setValue('cth36', "{$clients_hlth_row->cth36}");
    $templateProcessor->setValue('cth45', "{$clients_hlth_row->cth45}");

    // 一般評估
    $templateProcessor->setValue('cth51', "{$clients_hlth_row->cth51}");
    $templateProcessor->setValue('cth52', "{$clients_hlth_row->cth52}");
    $templateProcessor->setValue('cth53', "{$clients_hlth_row->cth53}");
    $templateProcessor->setValue('cth54', "{$clients_hlth_row->cth54}");
    $templateProcessor->setValue('cth55_total', "{$clients_hlth_row->cth55_total}");
    $templateProcessor->setValue('cth56', "{$clients_hlth_row->cth56}");
    $templateProcessor->setValue('cth57', "{$clients_hlth_row->cth57}");
    $templateProcessor->setValue('cth58', "{$clients_hlth_row->cth58}");
    $templateProcessor->setValue('cth59', "{$clients_hlth_row->cth59}");
    $templateProcessor->setValue('cth60', "{$clients_hlth_row->cth60}");
    $templateProcessor->setValue('cth61', "{$clients_hlth_row->cth61}");
    $templateProcessor->setValue('cth61_mac', "{$clients_hlth_row->cth61_mac}");
    $templateProcessor->setValue('cth62', "{$clients_hlth_row->cth62}");
    $templateProcessor->setValue('cth62_cc', "{$clients_hlth_row->cth62_cc}");
    $templateProcessor->setValue('cth70', "{$clients_hlth_row->cth70}");

    foreach ($fd as $k_type => $v_type) {
      foreach($v_type as $k => $v) {
        foreach($v as $each_optiion) {
          if('radio' == $k_type) {
            if($clients_hlth_row->$k == $each_optiion) {
              $templateProcessor->setValue("{$k}_{$each_optiion}", "■");
            }
            else {
              $templateProcessor->setValue("{$k}_{$each_optiion}", "□");
            }
          }
        }
      }
    }

    $templateProcessor->saveAs($save_path);
    $ch_filename = "{$clients_hlth_row->ct01}{$clients_hlth_row->ct02}_迷你營養評估表.docx"; 
    $en_filename = "client_hlth_{$file_time}.docx";
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename); 
    echo json_encode($rtn_msg);
    return;
  }

  function __destruct() {
    // ajax 用，如果有回傳 echo 於畫面上的，就加上
    $url_str[] = 'be/clients_hlth/save';
    $url_str[] = 'be/clients_hlth/del';
    $url_str[] = 'be/clients_hlth/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 footer
    }
  }
}
?>