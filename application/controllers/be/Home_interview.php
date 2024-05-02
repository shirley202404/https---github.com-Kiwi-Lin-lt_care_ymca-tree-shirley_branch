<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_interview extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('social_worker_model'); // 社工資料
    $this->load->model('home_interview_model'); // 個案家訪資料
    $this->load->model('service_case_model'); // 開結案服務
    $this->load->model('meal_instruction_log_h_model'); // 餐食異動資料
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','個案家訪資料');
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
    $this->tpl->assign('tv_add_link',be_url().'home_interview/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."home_interview/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/home_interview_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
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
  //  設計日期: 2021-10-08
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
    list($home_interview_row,$row_cnt) = $this->home_interview_model->get_que($q_str,$pg); // 列出個案家訪資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','個案家訪資料瀏覽');
    $this->tpl->assign('tv_ct_name_str','家訪紀錄表');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'home_interview/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'home_interview/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'home_interview/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'home_interview/upd/');
    $this->tpl->assign('tv_del_link',be_url().'home_interview/del/');
    $this->tpl->assign('tv_prn_link',be_url().'home_interview/prn/');
    $this->tpl->assign('tv_download_link',be_url().'home_interview/download/');
    $this->tpl->assign('tv_download_blank_link',be_url().'home_interview/download_blank/');    
    $this->tpl->assign('tv_que_link',be_url()."home_interview/que/{$q_str}");
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'home_interview/save/');
    $this->tpl->assign('tv_home_interview_row',$home_interview_row);
    $config['base_url'] = be_url()."home_interview/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/home_interview/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/home_interview.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $home_interview_row = $this->home_interview_model->get_one($s_num); // 列出單筆明細資料
    // u_var_dump($home_interview_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_home_interview_row',$home_interview_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."home_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."home_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."home_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'home_interview/upd/');
    $this->tpl->display("be/home_interview_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function add()	{ // 新增
    $msel = 'add';
    $home_interview_row = NULL;
    $social_worker_row = $this->social_worker_model->get_all_is_available();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_name',$_SESSION['acc_name']);
    $this->tpl->assign('tv_acc_s_num',$_SESSION['acc_s_num']);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_social_worker_row',$social_worker_row);
    $this->tpl->assign('tv_home_interview_row',$home_interview_row);
    $this->tpl->assign('tv_save_link',be_url()."home_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."home_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."home_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_ct_disp_link',be_url()."clients/que_ct_disp"); // 搜尋案主詳細資料
    $this->tpl->display("be/home_interview_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function cpy($s_num)	{
    $msel = 'cpy';
    $home_interview_row = $this->home_interview_model->get_one($s_num);
    $social_worker_row = $this->social_worker_model->get_all_is_available();
    //u_var_dump($home_interview_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_name',$_SESSION['acc_name']);
    $this->tpl->assign('tv_acc_s_num',$_SESSION['acc_s_num']);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_social_worker_row',$social_worker_row);
    $this->tpl->assign('tv_home_interview_row',$home_interview_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."home_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."home_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."home_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_ct_disp_link',be_url()."clients/que_ct_disp"); // 搜尋案主詳細資料
    $this->tpl->display("be/home_interview_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function upd($s_num)	{
    $msel = 'upd';
    $home_interview_row = $this->home_interview_model->get_one($s_num);
    $social_worker_row = $this->social_worker_model->get_all_is_available();
    //u_var_dump($home_interview_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_name',$_SESSION['acc_name']);
    $this->tpl->assign('tv_acc_s_num',$_SESSION['acc_s_num']);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_social_worker_row',$social_worker_row);
    $this->tpl->assign('tv_home_interview_row',$home_interview_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."home_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."home_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."home_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_ct_disp_link',be_url()."clients/que_ct_disp"); // 搜尋案主詳細資料
    $this->tpl->display("be/home_interview_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 家訪記錄下載
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function download($s_num) {
    $today = date("Y-m-d");
    $fd_arr["hew10"] = array(1, 2, 3, 99); // 訪視目的(1=定期關懷,2=特殊問題,3=年度評估,99=其他)
    $fd_arr["hew10_1"] = array(1, 99);    // 家庭
    $fd_arr["hew10_2"] = array(1, 99);    // 生理
    $fd_arr["hew10_3"] = array(1, 99);    // 心理
    $fd_arr["hew10_4"] = array(1, 99);    // 社會
    $fd_arr["hew10_5"] = array(1, 99);    // 經濟
    $fd_arr["hew10_6"] = array(1, 99);    // 環境
    // $fd_arr["hew20"] = array(1, 2, 3, 4, 5); // 服務態度
    // $fd_arr["hew21"] = array(1, 2, 3, 4, 5); // 是否準時
    // $fd_arr["hew22"] = array(1, 2, 3, 4, 5); // 衛生
    // $fd_arr["hew23"] = array(1, 2, 3, 4, 5); // 整體滿意度
    // $fd_arr["hew24"] = array(1, 2, 3, 4, 5); // 機構滿意度
    
    $home_interview_row = $this->home_interview_model->get_one($s_num); // 列出單筆明細資料
    $ct_s_num = $home_interview_row->hew04_ct_s_num;
    $clients_row = $this->clients_model->get_one($ct_s_num);
    $service_case_row = $this->service_case_model->get_all_by_ct_s_num($ct_s_num, $today);
    if(NULL != $service_case_row) {
      foreach ($service_case_row as $k => $v) {
        $mil_m_row = $this->meal_instruction_log_h_model->get_last_m_by_s_num($v['s_num'], $today);
        $mil_mp_row = $this->meal_instruction_log_h_model->get_last_mp_by_s_num($v['s_num'], $today);
        $route_row = $this->route_model->que_client_route($v['reh_type'], $v['ct_s_num']);
        $sec01_arr[] = $v['sec01_str'];
        $sec02_arr[] = $v['sec02'];
        $sec04_arr[] = $v['sec04_str'];
        $sec05_str = $v['sec05_str'];
        if(NULL != $mil_m_row) {
          $ml01_arr[] = $mil_m_row->ml01;
        }
        if(NULL != $route_row) {
          $reh01_arr[] = $route_row->reh01;
        }
        if(NULL != $mil_mp_row) {
          if(NULL != $mil_mp_row->mil_mp01_type) {
            $mil_mp01_type_arr[] = radio_value('mil_mp01_type', $mil_mp_row->mil_mp01_type);
          }
          else {
            $mil_mp01_type_arr[] = '無';
          }
        }
      }
    }

    $ct07_1 = array($clients_row->ct07_1_name, $clients_row->ct07_1_rlat, $clients_row->ct07_1_tel);
    $ct07_2 = array($clients_row->ct07_2_name, $clients_row->ct07_2_rlat, $clients_row->ct07_2_tel);
    $contact_info = array("{$clients_row->ct06_telephone}", "{$clients_row->ct06_homephone}");

    $en_file_name = "{$s_num}_home_interview.docx";
    $ch_file_name = "{$home_interview_row->hew01}_{$home_interview_row->ct01}{$home_interview_row->ct02}_家訪紀錄表.docx"; 
    $save_path = FCPATH."export_file/{$en_file_name}";
    $sample_file = FCPATH."pub/sample/home_interview_{$sec05_str}_sample.docx";
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    $templateProcessor->setValue('region', $home_interview_row->ct14);
    $templateProcessor->setValue('ct_name', "{$clients_row->ct01}{$clients_row->ct02}");
    $templateProcessor->setValue('contact_info', join("／", array_filter($contact_info)));
    $templateProcessor->setValue('ct_address', "{$clients_row->ct12}{$clients_row->ct13}{$clients_row->ct14}{$clients_row->ct15}");
    $templateProcessor->setValue('ct03', $clients_row->ct03);
    $templateProcessor->setValue('ct05', $clients_row->ct05);
    $templateProcessor->setValue('ct07_1', join("-", array_filter($ct07_1)));
    $templateProcessor->setValue('ct07_2', join("-", array_filter($ct07_2)));
    $templateProcessor->setValue('ct21_str', $clients_row->ct21_str);
    $templateProcessor->setValue('ct31_str', $clients_row->ct31_str);
    $templateProcessor->setValue('ct31_memo', $clients_row->ct31_memo);
    $templateProcessor->setValue('ct34_go', $clients_row->ct34_go_str);
    $templateProcessor->setValue('ct35_str', $clients_row->ct35_str);
    $templateProcessor->setValue('ct35_type_str', $clients_row->ct35_type_str);
    $templateProcessor->setValue('ct35_level_str', $clients_row->ct35_level_str);
    $templateProcessor->setValue('ct35_memo_str', $clients_row->ct35_memo);
    $templateProcessor->setValue('ct36_str', $clients_row->ct36_str);
    $templateProcessor->setValue('ct38_1_str', $clients_row->ct38_1_str);
    $templateProcessor->setValue('sec01', join("／", array_unique($sec01_arr)));
    $templateProcessor->setValue('sec02', join("／", array_unique($sec02_arr)));
    $templateProcessor->setValue('sec04', join("／", $sec04_arr));
    $templateProcessor->setValue('ml01', join("／", $ml01_arr));
    $templateProcessor->setValue('mil_mp01_type', join("／", $mil_mp01_type_arr));
    $templateProcessor->setValue('reh01', join("／", $reh01_arr));
    $templateProcessor->setValue('b_acc_name', $home_interview_row->b_acc_name);
    $templateProcessor->setValue('hew01', date('Y年m月d日', strtotime($home_interview_row->hew01)));
    $templateProcessor->setValue('sw2', $home_interview_row->sw_chk_name);
    $templateProcessor->setValue('sw2_date', date('Y年m月d日', strtotime($home_interview_row->hew01 . "+7 days"))); // 顯示電訪日期後七天
    $templateProcessor->setValue('hew30', $home_interview_row->hew30);
    $templateProcessor->setValue('hew31', $home_interview_row->hew31);
    $templateProcessor->setValue('hew32', $home_interview_row->hew32);
    $templateProcessor->setValue('region', $clients_row->ct14);
    $templateProcessor->setValue('region', $clients_row->ct14);
    $ct_family_tree_img_path = FCPATH . "upload_files/clients/{$clients_row->ct95}";
    if(file_exists($ct_family_tree_img_path)) {
      $templateProcessor->setImageValue('ct_family_tree_img', array('path' => $ct_family_tree_img_path, 
                                                                    'width' => '', 
                                                                    'height' => 94, 
                                                                    'ratio' => true)
                                                                   );
    }
    else {
      $templateProcessor->setValue('ct_family_tree_img', '');
    }

    $circle_fd_arr = array('hew20', 'hew21', 'hew22', 'hew23', 'hew24');
    foreach ($fd_arr as $k => $v) {
      $memo_col = "{$k}_memo";
      foreach($v as $each_optiion) {
        if(!in_array($k, $circle_fd_arr)) {
          if($home_interview_row->$k == $each_optiion) {
            $templateProcessor->setValue("{$k}_{$each_optiion}", "☒");
          }
          else {
            $templateProcessor->setValue("{$k}_{$each_optiion}", "☐");
          }
        }
        else {
          if($home_interview_row->$k == $each_optiion) {
            $templateProcessor->setValue("{$k}_{$each_optiion}", "Ｏ");
          }
          else {
            $templateProcessor->setValue("{$k}_{$each_optiion}", "");
          }
        }
      }
      if(isset($home_interview_row->$memo_col)) {
        $memo_str = str_replace(' ', '', $home_interview_row->$memo_col);
        $memo_str = str_replace('。', '。</w:t><w:br/><w:t>', $memo_str);
        $templateProcessor->setValue("{$k}_memo", $memo_str);
      }
    }
    
    $templateProcessor->saveAs($save_path);
    $rtn_msg = $this->zi_my_func->download_str($ch_file_name, $en_file_name);
    echo json_encode($rtn_msg);
    return;
  }
  // **************************************************************************
  //  函數名稱: download_blank()
  //  函數功能: 家訪記錄下載(空白)
  //  程式設計: kiwi
  //  設計日期: 2023-03-12
  // **************************************************************************
  public function download_blank($ct_s_num) {
    $today = date("Y-m-d");

    $sec01_arr = array();
    $sec02_arr = array();
    $sec04_arr = array();
    $ml01_arr = array();
    $mil_mp01_type_arr = array();
    $reh01_arr = array();
    $sec05_str = NULL;

    $clients_row = $this->clients_model->get_one($ct_s_num);
    $service_case_row = $this->service_case_model->get_all_by_ct_s_num($ct_s_num, $today);
    if(NULL != $service_case_row) {
      foreach ($service_case_row as $k => $v) {
        $mil_m_row = $this->meal_instruction_log_h_model->get_last_m_by_s_num($v['s_num'], $today);
        $mil_mp_row = $this->meal_instruction_log_h_model->get_last_mp_by_s_num($v['s_num'], $today);
        $route_row = $this->route_model->que_client_route($v['reh_type'], $v['ct_s_num']);
        $sec01_arr[] = $v['sec01_str'];
        $sec02_arr[] = $v['sec02'];
        $sec04_arr[] = $v['sec04_str'];
        $sec05_str = $v['sec05_str'];
        if(NULL != $mil_m_row) {
          $ml01_arr[] = $mil_m_row->ml01;
        }
        if(NULL != $route_row) {
          $reh01_arr[] = $route_row->reh01;
        }
        if(NULL != $mil_mp_row) {
          if(NULL != $mil_mp_row->mil_mp01_type) {
            $mil_mp01_type_arr[] = radio_value('mil_mp01_type', $mil_mp_row->mil_mp01_type);
          }
          else {
            $mil_mp01_type_arr[] = '無';
          }
        }
      }
    }

    $ct07_1 = array($clients_row->ct07_1_name, $clients_row->ct07_1_rlat, $clients_row->ct07_1_tel);
    $ct07_2 = array($clients_row->ct07_2_name, $clients_row->ct07_2_rlat, $clients_row->ct07_2_tel);
    $contact_info = array("{$clients_row->ct06_telephone}", "{$clients_row->ct06_homephone}");
    
    $en_file_name = "{$ct_s_num}_home_interview.docx";
    $ch_file_name = "{$today}_{$clients_row->ct01}{$clients_row->ct02}_家訪紀錄表.docx"; 
    $save_path = FCPATH."export_file/{$en_file_name}";
    $sample_file = FCPATH."pub/sample/blank_home_interview_{$sec05_str}_sample.docx";
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    $templateProcessor->setValue('region', $clients_row->ct14);
    $templateProcessor->setValue('b_acc_name', "");
    $templateProcessor->setValue('ct_name', "{$clients_row->ct01}{$clients_row->ct02}");
    $templateProcessor->setValue('contact_info', join("／", array_filter($contact_info)));
    $templateProcessor->setValue('ct_address', "{$clients_row->ct12}{$clients_row->ct13}{$clients_row->ct14}{$clients_row->ct15}");
    $templateProcessor->setValue('ct03', $clients_row->ct03);
    $templateProcessor->setValue('ct05', $clients_row->ct05);
    $templateProcessor->setValue('ct07_1', join("-", array_filter($ct07_1)));
    $templateProcessor->setValue('ct07_2', join("-", array_filter($ct07_2)));
    $templateProcessor->setValue('ct21_str', $clients_row->ct21_str);
    $templateProcessor->setValue('ct31_str', $clients_row->ct31_str);
    $templateProcessor->setValue('ct31_memo', $clients_row->ct31_memo);
    $templateProcessor->setValue('ct34_go', $clients_row->ct34_go_str);
    $templateProcessor->setValue('ct35_str', $clients_row->ct35_str);
    $templateProcessor->setValue('ct35_type_str', $clients_row->ct35_type_str);
    $templateProcessor->setValue('ct35_level_str', $clients_row->ct35_level_str);
    $templateProcessor->setValue('ct35_memo_str', $clients_row->ct35_memo);
    $templateProcessor->setValue('ct36_str', $clients_row->ct36_str);
    $templateProcessor->setValue('ct38_1_str', $clients_row->ct38_1_str);
    $templateProcessor->setValue('sec01', join("／", array_unique($sec01_arr)));
    $templateProcessor->setValue('sec02', join("／", array_unique($sec02_arr)));
    $templateProcessor->setValue('sec04', join("／", $sec04_arr));
    $templateProcessor->setValue('ml01', join("／", $ml01_arr));
    $templateProcessor->setValue('mil_mp01_type', join("／", $mil_mp01_type_arr));
    $templateProcessor->setValue('reh01', join("／", $reh01_arr));
    
    $templateProcessor->saveAs($save_path);
    $rtn_msg = $this->zi_my_func->download_str($ch_file_name, $en_file_name);
    echo json_encode($rtn_msg);
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function del($s_num=NULL)	{
    $rtn_msg = $this->home_interview_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'home_interview/', 'refresh');
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
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function save($kind=NULL)	{
    switch($kind) {
      case "add":
      case "cpy":
        $this->home_interview_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->home_interview_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->home_interview_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
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
    ////redirect(be_url()."home_interview/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."home_interview/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }
  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  private function _que_start($q_str)	{
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }
  // **************************************************************************
  //  函數名稱: prn()
  //  函數功能: 列印
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function prn()	{
    $msel = 'prn';
    return;
  }

  function __destruct() {
    // ajax 用，如果有回傳 echo 於畫面上的，就加上
    $url_str[] = 'be/home_interview/save';
    $url_str[] = 'be/home_interview/del';
    $url_str[] = 'be/home_interview/download';
    $url_str[] = 'be/home_interview/download_blank';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
?>