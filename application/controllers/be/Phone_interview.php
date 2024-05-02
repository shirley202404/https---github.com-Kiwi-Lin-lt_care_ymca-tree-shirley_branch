<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phone_interview extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('clients_model'); // 案主資料
    $this->load->model('social_worker_model'); // 社工資料
    $this->load->model('phone_interview_model'); // 個案電訪資料
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','個案電訪資料');
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
    $this->tpl->assign('tv_add_link',be_url().'phone_interview/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_link',be_url()."phone_interview/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/phone_interview_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
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
    list($phone_interview_row,$row_cnt) = $this->phone_interview_model->get_que($q_str,$pg); // 列出個案電訪資料
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','個案電訪資料瀏覽');
    $this->tpl->assign('tv_ct_name_str','電訪紀錄表');
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'phone_interview/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'phone_interview/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'phone_interview/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'phone_interview/upd/');
    $this->tpl->assign('tv_del_link',be_url().'phone_interview/del/');
    $this->tpl->assign('tv_prn_link',be_url().'phone_interview/prn/');
    $this->tpl->assign('tv_download_link',be_url().'phone_interview/download/');
    $this->tpl->assign('tv_download_blank_link',be_url().'phone_interview/download_blank/');
    $this->tpl->assign('tv_que_link',be_url()."phone_interview/que/{$q_str}");
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'phone_interview/save/');
    $this->tpl->assign('tv_phone_interview_row',$phone_interview_row);
    $config['base_url'] = be_url()."phone_interview/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/phone_interview/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/phone_interview.html");
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
    $phone_interview_row = $this->phone_interview_model->get_one($s_num); // 列出單筆明細資料
    //u_var_dump($phone_interview_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_phone_interview_row',$phone_interview_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."phone_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."phone_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."phone_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'phone_interview/upd/');
    $this->tpl->display("be/phone_interview_disp.html");
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
    $phone_interview_row = NULL;
    $social_worker_row = $this->social_worker_model->get_all_is_available();
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_name',$_SESSION['acc_name']);
    $this->tpl->assign('tv_acc_s_num',$_SESSION['acc_s_num']);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_social_worker_row',$social_worker_row);
    $this->tpl->assign('tv_phone_interview_row',$phone_interview_row);
    $this->tpl->assign('tv_save_link',be_url()."phone_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."phone_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."phone_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->display("be/phone_interview_edit.html");
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
    $phone_interview_row = $this->phone_interview_model->get_one($s_num);
    $social_worker_row = $this->social_worker_model->get_all_is_available();
    //u_var_dump($phone_interview_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_social_worker_row',$social_worker_row);
    $this->tpl->assign('tv_phone_interview_row',$phone_interview_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."phone_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."phone_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."phone_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->display("be/phone_interview_edit.html");
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
    $phone_interview_row = $this->phone_interview_model->get_one($s_num);
    $social_worker_row = $this->social_worker_model->get_all_is_available();
    //u_var_dump($phone_interview_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_acc_name',$_SESSION['acc_name']);
    $this->tpl->assign('tv_acc_s_num',$_SESSION['acc_s_num']);
    $this->tpl->assign('tv_acc_kind',$_SESSION['acc_kind']);
    $this->tpl->assign('tv_social_worker_row',$social_worker_row);
    $this->tpl->assign('tv_phone_interview_row',$phone_interview_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."phone_interview/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."phone_interview/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."phone_interview/"); // 上一層連結位置
    $this->tpl->assign('tv_que_ct_link',be_url()."clients/que_ct"); // 搜尋案主資料(autocomplete 使用)
    $this->tpl->display("be/phone_interview_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function del($s_num=NULL)	{
    $rtn_msg = $this->phone_interview_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'phone_interview/', 'refresh');
    }
    else {
      die($rtn_msg); // 刪除失敗!!!
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 電訪記錄下載
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function download($s_num) {
    $fd_arr["phw10_1"] = array(1, 2, 3, 99);       // 接聽情形
    $fd_arr["phw10_2"] = array(1, 99);              // 餐食建議
    $fd_arr["phw10_3"] = array(1, 99);              // 服務建議
    $fd_arr["phw10_4"] = array(1, 2, 3, 4, 99);    // 精神狀況
    $fd_arr["phw10_5"] = array(1, 2, 3, 4, 99);    // 身體狀況
    $fd_arr["phw10_6"] = array(1, 2, 3, 99);       // 社會
    $fd_arr["phw10_7"] = array("Y", "N");          // 是否轉介或追蹤
    
    $phone_interview_row = $this->phone_interview_model->get_one($s_num);
    $en_file_name = "{$s_num}_phone_interview.docx"; 
    $ch_file_name = "{$phone_interview_row->phw01}_{$phone_interview_row->ct01}{$phone_interview_row->ct02}_電訪紀錄表.docx"; 
    $save_path = FCPATH."export_file/{$en_file_name}";
    $sample_file = FCPATH."pub/sample/phone_interview_sample.docx";
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    $templateProcessor->setValue('region', $phone_interview_row->ct14);
    $templateProcessor->setValue('b_acc_name', $phone_interview_row->b_acc_name);
    $templateProcessor->setValue('ct_name', "{$phone_interview_row->ct01}{$phone_interview_row->ct02}");
    $templateProcessor->setValue('sw1', $phone_interview_row->b_acc_name);
    $templateProcessor->setValue('sw1_date', date('Y年m月d日', strtotime($phone_interview_row->b_date)));
    $templateProcessor->setValue('sw2', $phone_interview_row->sw_chk_name);
    $templateProcessor->setValue('sw2_date', date('Y年m月d日', strtotime($phone_interview_row->phw01 . "+7 days"))); // 顯示電訪日期後七天
    $templateProcessor->setValue('phw01', $phone_interview_row->phw01);
    $templateProcessor->setValue('phw20', $phone_interview_row->phw20);
    foreach ($fd_arr as $k => $v) {
      $memo_col = "{$k}_memo";
      foreach($v as $each_option) {
        if($phone_interview_row->$k == $each_option) {
          $templateProcessor->setValue("{$k}_{$each_option}", "■"); // ■
        }
        else {
          $templateProcessor->setValue("{$k}_{$each_option}", "□");
        }
      }
      if(isset($phone_interview_row->$memo_col)) {
        $memo_str = str_replace(' ', '', $phone_interview_row->$memo_col);
        $memo_str = str_replace('。', '。</w:t><w:br/><w:t>', $memo_str);
        $templateProcessor->setValue("{$k}_memo", str_replace('"', '', $memo_str));
      }
    }

    $templateProcessor->saveAs($save_path);
    $rtn_msg = $this->zi_my_func->download_str($ch_file_name, $en_file_name);
    echo json_encode($rtn_msg);
    return;
  }
  // **************************************************************************
  //  函數名稱: download_blank()
  //  函數功能: 電訪記錄下載(空白)
  //  程式設計: kiwi
  //  設計日期: 2023-03-12
  // **************************************************************************
  public function download_blank($ct_s_num) {    
    $fd_arr["phw10_1"] = array(1, 2, 3, 99);       // 接聽情形
    $fd_arr["phw10_2"] = array(1, 99);              // 餐食建議
    $fd_arr["phw10_3"] = array(1, 99);              // 服務建議
    $fd_arr["phw10_4"] = array(1, 2, 3, 4, 99);    // 精神狀況
    $fd_arr["phw10_5"] = array(1, 2, 3, 4, 99);    // 身體狀況
    $fd_arr["phw10_6"] = array(1, 2, 3, 99);       // 社會
    $fd_arr["phw10_7"] = array("Y", "N");          // 是否轉介或追蹤
    $phw10_1_memo = '<w:br/>'; // 接聽情形 備註
    $phw10_2_memo = '<w:br/>'; // 餐食部份 備註
    $phw10_3_memo = '<w:br/>'; // 服務部份 備註
    $phw10_4_memo = '<w:t>與案主的溝通狀況為(主動/被動/選擇性溝通/拒絕溝通)，交談態度(和善/冷漠/激動/無異狀)。</w:t><w:br/><w:t>案主對於(家庭/夫妻/與子女/與父母)的關係感到(焦慮/不諒解/傷心/無助)，且有(自傷/表示不想要活/憂鬱/自我封鎖)的行為。</w:t>'; // 精神狀況 備註
    $phw10_5_memo = '<w:t>案主能清楚了解自身疾病與飲食方面的注意事項，且有(定時吃藥與就醫/主動治療與復健)。</w:t>'; // 身體狀況 備註
    $phw10_6_memo = '<w:t>案主有(聘請看傭/親戚/鄰居/朋友/房東/警衛/宮廟)__________(偶爾/常常/每天)會來關心案主(生活/備餐/就醫)。</w:t><w:br/><w:t>案主有參加社區聚會，(偶爾/常常/每天)到社區(用餐/參加活動/上課/聊天)。</w:t><w:br/><w:t>案主有社交障礙，不喜歡與人互動，導致人際關係不佳，有社會脫離之情形。</w:t>'; // 社會互動 備註
    $phw10_7_memo = '<w:br/><w:br/><w:br/>'; // 後續追蹤或轉介 備註

    $today = date("Y-m-d");
    $clients_row = $this->clients_model->get_one($ct_s_num);
    $en_file_name = "{$ct_s_num}_phone_interview.docx"; 
    $ch_file_name = "{$today}_{$clients_row->ct01}{$clients_row->ct02}_電訪紀錄表.docx"; 
    $save_path = FCPATH."export_file/{$en_file_name}";
    $sample_file = FCPATH."pub/sample/phone_interview_sample.docx";
    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($sample_file);
    $templateProcessor->setValue('region', $clients_row->ct14);
    $templateProcessor->setValue('ct_name', "{$clients_row->ct01}{$clients_row->ct02}");
    $templateProcessor->setValue('sw1', '');
    $templateProcessor->setValue('sw1_date', '');
    $templateProcessor->setValue('sw2', '');
    $templateProcessor->setValue('sw2_date', ''); // 顯示電訪日期後七天
    $templateProcessor->setValue('phw10_1_memo', $phw10_1_memo);
    $templateProcessor->setValue('phw10_2_memo', $phw10_2_memo);
    $templateProcessor->setValue('phw10_3_memo', $phw10_3_memo);
    $templateProcessor->setValue('phw10_4_memo', $phw10_4_memo);
    $templateProcessor->setValue('phw10_5_memo', $phw10_5_memo);
    $templateProcessor->setValue('phw10_6_memo', $phw10_6_memo);
    $templateProcessor->setValue('phw10_7_memo', $phw10_7_memo);

    foreach ($fd_arr as $k => $v) {
      foreach($v as $each_option) {
        $templateProcessor->setValue("{$k}_{$each_option}", "□");
      }
    }

    $templateProcessor->saveAs($save_path);
    $rtn_msg = $this->zi_my_func->download_str($ch_file_name, $en_file_name);
    echo json_encode($rtn_msg);
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
        $this->phone_interview_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->phone_interview_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->phone_interview_model->save_is_available(); // 上下架儲存
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
    ////redirect(be_url()."phone_interview/p/1/q/{$q_str}", 'refresh');

    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."phone_interview/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
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
    $url_str[] = 'be/phone_interview/save';
    $url_str[] = 'be/phone_interview/del';
    $url_str[] = 'be/phone_interview/download';
    $url_str[] = 'be/phone_interview/download_blank';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
?>