<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Route extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('route_model'); // 送餐路徑規劃-檔頭
    $this->load->model('service_case_model'); // 開結案服務
    $this->load->model('meal_instruction_log_h_model'); // 餐點異動資料
    $this->load->model('delivery_person_model'); // 外送員基本資料檔
    $this->load->model('verification_person_model'); // 核銷人員資料檔
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','送餐路徑規劃-檔頭');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_group_s_num',$_SESSION['group_s_num']);
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
    $this->tpl->assign('tv_add_link',be_url().'route/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."route/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/route_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/files/');
    //if('tony' != $_SESSION['acc_user']) {
    //  die('趕工中...');
    //}
    return;
  }

  // **************************************************************************
  //  函數名稱: index
  //  函數功能: 瀏覽資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
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
    $stat_route_row = $this->route_model->get_all_without_test();
    if(NULL != $stat_route_row) {
      foreach($stat_route_row as $kh => $vh) {
        $stat_route_row[$kh]['total_ct'] = 0;
        $stat_route_row[$kh]['send_ct'] = 0;
        $stat_route_row[$kh]['no_send_ct'] = 0;
        $route_b_row = $this->route_model->get_route_b($vh['s_num']); // 路線規劃-單身
        if(NULL != $route_b_row) {
          $stat_route_row[$kh]['total_ct'] = count($route_b_row);
          foreach($route_b_row as $kb => $vb) {
            $is_send_flag = FALSE;
            $ct_service_case_row = $this->service_case_model->reh_que_data_by_ct_s_num($vb['ct_s_num'] , $vh['reh05']);
            if(NULL != $ct_service_case_row) {
              foreach($ct_service_case_row as $k_sec => $v_sec) {
                if(NULL != $v_sec['sec03']) {
                  if(strtotime(date("Y-m-d")) > strtotime($v_sec['sec03'])) { // 當生產日期大於結案日期，代表該案已結案了
                    continue;
                  }
                }
                $meal_instruction_log_h_row = $this->meal_instruction_log_h_model->get_last_s_by_s_num($v_sec['s_num'], date("Y-m-d"));
                if(NULL != $meal_instruction_log_h_row) {
                  if($meal_instruction_log_h_row->mil_s01 == 'Y') {
                    $is_send_flag = TRUE;
                  }
                }
              }
            }
            if($is_send_flag) {
              $stat_route_row[$kh]['send_ct'] += 1;
            }
            else {
              $stat_route_row[$kh]['no_send_ct'] += 1;
            }
          }
        }
      }
    }
    list($route_h_row,$row_cnt) = $this->route_model->get_que($q_str,$pg); // 列出送餐路徑規劃-檔頭
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','送餐路徑規劃-檔頭瀏覽');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'route/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'route/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'route/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'route/upd/');
    $this->tpl->assign('tv_del_link',be_url().'route/del/');
    $this->tpl->assign('tv_prn_link',be_url().'route/prn/');
    $this->tpl->assign('tv_download_link',be_url().'route/download/');
    $this->tpl->assign('tv_que_link',be_url()."route/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'route/save/');
    $this->tpl->assign('tv_route_h_row',$route_h_row);
    $this->tpl->assign('tv_stat_route_row',$stat_route_row);
    $config['base_url'] = be_url()."route/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/route/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

    $this->tpl->display("be/route.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $route_h_row = $this->route_model->get_one($s_num); // 列出單筆明細資料
    $route_b_row = $this->route_model->get_route_b($s_num); // 路線規劃-單身
    $route_sw_row = $this->route_model->get_route_sw($s_num); // 路線規劃-輔助社工
    //u_var_dump($route_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_route_h_row',$route_h_row);
    $this->tpl->assign('tv_route_b_row',$route_b_row);
    $this->tpl->assign('tv_route_sw_row',$route_sw_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."route/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."route/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."route/"); // 上一層連結位置
    $this->tpl->assign('tv_sort_up_link',be_url()."route/save/sort_up");
    $this->tpl->assign('tv_sort_down_link',be_url()."route/save/sort_down");
    $this->tpl->assign('tv_upd_link',be_url().'route/upd/');
    $this->tpl->display("be/route_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function add() { // 新增
    $msel = 'add';
    $route_h_row = NULL;
    $route_b_row = NULL;
    $route_sw_row = NULL;
    $delivery_person_row = $this->delivery_person_model->get_all_is_available();
    $verification_person_row = $this->verification_person_model->get_all_is_available();
    // u_var_dump($delivery_person_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_route_h_row',$route_h_row);
    $this->tpl->assign('tv_route_b_row',$route_b_row);
    $this->tpl->assign('tv_route_sw_row',$route_sw_row);
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $this->tpl->assign('tv_verification_person_row',$verification_person_row);
    $this->tpl->assign('tv_save_link',be_url()."route/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."route/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."route/"); // 上一層連結位置
    $this->tpl->assign('tv_reh_que_ct_link',be_url()."clients/reh_que_ct"); // 搜尋案主資料
    $this->tpl->assign('tv_que_dp_link',be_url()."delivery_person/que_dp"); // 搜尋外送員資料(autocomplete 使用)
    $this->tpl->display("be/route_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function cpy($s_num) {
    $msel = 'cpy';
    $route_h_row = $this->route_model->get_one($s_num); // 列出單筆明細資料
    $route_b_row = $this->route_model->get_route_b($s_num); // 路線規劃-單身
    //u_var_dump($route_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_route_h_row',$route_h_row);
    $this->tpl->assign('tv_route_b_row',$route_b_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."route/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."route/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."route/"); // 上一層連結位置
    $this->tpl->assign('tv_reh_que_ct_link',be_url()."clients/reh_que_ct"); // 搜尋案主資料
    $this->tpl->assign('tv_que_dp_link',be_url()."delivery_person/que_dp"); // 搜尋外送員資料(autocomplete 使用)
    $this->tpl->display("be/route_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function upd($s_num) {
    $msel = 'upd';
    $route_h_row = $this->route_model->get_one($s_num); // 列出單筆明細資料
    $route_b_row = $this->route_model->get_route_b($s_num); // 路線規劃-單身
    $route_sw_row = $this->route_model->get_route_sw($s_num); // 路線規劃-輔助社工
    $delivery_person_row = $this->delivery_person_model->get_all_is_available();
    $verification_person_row = $this->verification_person_model->get_all_is_available();
    // u_var_dump($route_sw_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_route_h_row',$route_h_row);
    $this->tpl->assign('tv_route_b_row',$route_b_row);
    $this->tpl->assign('tv_route_sw_row',$route_sw_row);
    $this->tpl->assign('tv_delivery_person_row',$delivery_person_row);
    $this->tpl->assign('tv_verification_person_row',$verification_person_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."route/save/{$msel}");
    $this->tpl->assign('tv_save_upd_vp_link',be_url()."route/save/upd_vp");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."route/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."route/"); // 上一層連結位置
    $this->tpl->assign('tv_reh_que_ct_link',be_url()."clients/reh_que_ct"); // 搜尋案主資料
    $this->tpl->assign('tv_que_dp_link',be_url()."delivery_person/que_dp"); // 搜尋外送員資料(autocomplete 使用)
    $this->tpl->display("be/route_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->route_model->del($s_num); // 刪除
    $this->zi_my_func->web_api_data("client_route", "del");
    if($rtn_msg) {
      redirect(be_url().'route/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除)
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "add":
      case "cpy":
        $fd_msel = 'add';
        $this->route_model->save_add(); // 新增儲存
        break;
      case "upd":
        $fd_msel = 'upd';
        $this->route_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $fd_msel = 'stop';
        $this->route_model->save_is_available(); // 上下架儲存
        break;
      case "sort_up":
	      $this->route_model->save_sort('up'); // 修改儲存
	      redirect(be_url()."route/disp/{$_GET['reh_s_num']}", 'refresh');
        break;
	    case "sort_down":
	      $this->route_model->save_sort('down'); // 修改儲存
	      redirect(be_url()."route/disp/{$_GET['reh_s_num']}", 'refresh');
	      break;
      case "upd_vp":
        $this->route_model->save_upd_vp(); // 儲存核備人員更新 
        break;
    }
    $this->zi_my_func->web_api_data("client_route", $fd_msel);
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function que($q_str) {
    ////$data = $this->input->post(); // POST 用
    //////u_var_dump($data);
    //////exit;
    ////if('que'==$data['que_kind']) {
    ////  $_SESSION[$q_str]['que_str'] = $data['que_str']; // 全文檢索
    ////}
    ////$_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    ////$_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    ////redirect(be_url()."route/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."route/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  private function _que_start($q_str) {
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }
  // **************************************************************************
  //  函數名稱: prn()
  //  函數功能: 列印
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $time_start = date('Y-m-d H:i:s');
    $route_h_arr = array();

    $post_data = $this->input->get();
    if('all' != $post_data['download_route_s_num']) {
      $reh_s_num = $post_data['download_route_s_num'];
      $route_h_arr[$reh_s_num] = (array) $this->route_model->get_one($reh_s_num); // 列出單筆明細資料
      $route_h_arr[$reh_s_num]['route_b_arr'] = $this->route_model->get_route_b($reh_s_num); // 路線規劃-單身
    }
    else {
      $route_h_arr = $this->route_model->get_all();
      foreach ($route_h_arr as $k => $v) {
        $route_h_arr[$k]['route_b_arr'] = array();
        $route_b_row = $this->route_model->get_route_b($v['s_num']); // 路線規劃-單身
        if(!empty($route_b_row)) {
          $route_h_arr[$k]['route_b_arr'] = $route_b_row;
        }
      }
    }

    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

    if(NULL != $route_h_arr) {
      $worksheet_index = 0;
      foreach ($route_h_arr as $k => $v) {
        if($worksheet_index != 0) {
          $objSpreadsheet->createSheet();
        }
        $objSpreadsheet->setActiveSheetIndex($worksheet_index);
        $objSpreadsheet->getActiveSheet()->setTitle("{$v['reh01']}");
        $objSpreadsheet->getActiveSheet()->setCellValue("A1", "順位");
        $objSpreadsheet->getActiveSheet()->setCellValue("B1", "姓名");
        $objSpreadsheet->getActiveSheet()->setCellValue("C1", "地址");
        $objSpreadsheet->getActiveSheet()->getColumnDimension("A")->setWidth('8');
        $objSpreadsheet->getActiveSheet()->getColumnDimension("B")->setWidth('12');
        $objSpreadsheet->getActiveSheet()->getColumnDimension("C")->setWidth('50');

        $row = 2;
        if(!empty($v['route_b_arr'])) {
          foreach ($v['route_b_arr'] as $k_reb => $v_reb) {
            $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", $v_reb['reb01']);                               // 路線
            $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v_reb['ct_name']);                                // 區域
            $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $v_reb['ct_address']);
            $row++;
          }
        }
        $worksheet_index++;
      }
    }
    
    $ch_filename = date("Ymd")."路線案主資料.xlsx";
    $en_filename = "route_client_data_".date('Y-m-d H-i-s').".xlsx";
    
    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=" . $en_filename);
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objSpreadsheet , 'Xlsx');
    $writer->save(FCPATH."export_file/{$en_filename}"); // 儲存到server

    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff >= 60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }   
        
    $rtn_msg = $this->zi_my_func->download_str($ch_filename, $en_filename, $time_diff); 
    echo json_encode($rtn_msg);
    return;
  }
  
  function __destruct() {
    $url_str[] = 'be/route/save';
    $url_str[] = 'be/route/del';
    $url_str[] = 'be/route/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
