<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Work_q extends CI_Controller {
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('route_model'); // 路徑資料
    $this->load->model('work_q_model'); // 工作問卷
    $this->load->model('daily_work_model'); // 每日工作
    $this->load->model('sys_language_model'); // 語系
    $mrand_str = $this->config->item('rand_str_8');
    $this->tpl->assign('tv_rand_str',$mrand_str);
    $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
    $this->tpl->assign('tv_method',$this->router->fetch_method());
    $this->tpl->assign('tv_menu_title','工作問卷');
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
    $this->tpl->assign('tv_add_link',be_url().'work_q/add/');
    $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
    $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
    $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
    $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
    $this->tpl->assign('tv_return_list_link',be_url()."work_q_route_list/"); // return 預設到路線列表畫面
    $this->tpl->assign('tv_return_link',be_url()."work_q/"); // return 預設到瀏覽畫面
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
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/work_q_/'); // upload/"origin" 保留上傳檔名，測試機要注意中文的問題
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
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function index($pg=1,$q_str=NULL) {
    $msel = 'list';
    $reh_s_num = 0;
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
    if(isset($get_data['que_reh_s_num'])) {
      $reh_s_num = $get_data['que_reh_s_num'];
    }
    $route_row = $this->route_model->get_one($reh_s_num);
    list($work_q_h_row,$row_cnt) = $this->work_q_model->get_que($q_str,$pg); // 列出工作問卷
    if(NULL != $work_q_h_row && NULL != $route_row) {
      foreach ($work_q_h_row as $kh => $vh) {
        $work_q_h_row[$kh]['reh01'] = $route_row->reh01;
        $work_q_b_row = $this->work_q_model->get_work_q_b($vh['s_num']); // 列出單筆明細資料
        if(NULL != $work_q_b_row) {
          foreach($work_q_b_row as $kb => $vb) {
            if($vb['qb02'] != 3) {
              $work_q_h_row[$kh]["qb02_{$vb['qb_order']}"] = $vb["qb02"];
              $work_q_h_row[$kh]["wqb01_arr_{$vb['qb_order']}"] = explode(",", $vb["wqb01"]);
              $work_q_h_row[$kh]["qb03_arr_{$vb['qb_order']}"] = explode(",", $vb["qb03"]);
            }
            else {
              $work_q_h_row[$kh]["wqb01_1"] = $vb["wqb01"];
            }
          }
        }
      }
    }
    // u_var_dump($work_q_h_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_title','工作問卷瀏覽');
    $this->tpl->assign('tv_reh_s_num',$reh_s_num);
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'work_q/add/');
    $this->tpl->assign('tv_cpy_link',be_url().'work_q/cpy/');
    $this->tpl->assign('tv_disp_link',be_url().'work_q/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'work_q/upd/');
    $this->tpl->assign('tv_del_link',be_url().'work_q/del/');
    $this->tpl->assign('tv_prn_link',be_url().'work_q/prn/');
    $this->tpl->assign('tv_que_link',be_url()."work_q/que/{$q_str}");
    $this->tpl->assign('tv_que_str',$get_data['que_str']); // 全文檢索-另開視窗(有需要其他欄位查詢的時候開啟查詢2的註記)
    $this->tpl->assign('tv_f_que',$get_data['que_str']); // 全文檢索-瀏覽上方
    $this->tpl->assign('tv_que_order_fd_name',$_SESSION[$q_str]['que_order_fd_name']); // 排序欄位
    $this->tpl->assign('tv_que_order_kind',$_SESSION[$q_str]['que_order_kind']); // 排序類別
    $this->tpl->assign('tv_save_link',be_url().'work_q/save/');
    $this->tpl->assign('tv_work_q_h_row',$work_q_h_row);
    $config['base_url'] = be_url()."work_q/p/";
    $config['suffix'] = "/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['first_url'] = be_url()."/work_q/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);

    $this->tpl->display("be/work_q.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function disp($s_num) {
    $msel = 'disp';
    $work_q_h_row = $this->work_q_model->get_one($s_num); // 列出單筆明細資料
    $work_q_b_row = $this->work_q_model->get_work_q_b($s_num); // 列出單筆明細資料
    if(NULL != $work_q_b_row) {
      foreach($work_q_b_row as $k => $v) {
        // qb02=>問卷類型
        // 1.單選題
        // 2.複選題
        // 3.問答題	
        if($v['qb02'] != 3) {
          $work_q_b_row[$k]["wqb01_arr"] = explode(",", $v["wqb01"]);
          $work_q_b_row[$k]["qb03_arr"] = explode(",", $v["qb03"]);
        }
      }
    }
    // u_var_dump($work_q_b_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 明細
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_work_q_h_row',$work_q_h_row);
    $this->tpl->assign('tv_work_q_b_row',$work_q_b_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."work_q/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q/"); // 上一層連結位置
    $this->tpl->assign('tv_upd_link',be_url().'work_q/upd/');
    $this->tpl->display("be/work_q_disp.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function add() { // 新增
    $msel = 'add';
    $work_q_h_row = NULL;
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
    $this->tpl->assign('tv_work_q_h_row',$work_q_h_row);
    $this->tpl->assign('tv_save_link',be_url()."work_q/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q/"); // 上一層連結位置
    $this->tpl->display("be/work_q_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: cpy()
  //  函數功能: 複製輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function cpy($s_num) {
    $msel = 'cpy';
    $work_q_h_row = $this->work_q_model->get_one($s_num);
    //u_var_dump($work_q_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 複製
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_work_q_h_row',$work_q_h_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('cpy_ok')); // 複製成功!!
    $this->tpl->assign('tv_save_link',be_url()."work_q/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q/"); // 上一層連結位置
    $this->tpl->display("be/work_q_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function upd($s_num) {
    $msel = 'upd';
    $work_q_h_row = $this->work_q_model->get_one($s_num);
    //u_var_dump($work_q_row);
    $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
    $this->tpl->assign('tv_msel',$msel);
    $this->tpl->assign('tv_work_q_h_row',$work_q_h_row);
    $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
    $this->tpl->assign('tv_save_link',be_url()."work_q/save/{$msel}");
    $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_exit_link',be_url()."work_q/"); // 離開按鈕的連結位置
    $this->tpl->assign('tv_parent_link',be_url()."work_q/"); // 上一層連結位置
    $this->tpl->display("be/work_q_edit.html");
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function del($s_num=NULL)  {
    $rtn_msg = $this->work_q_model->del($s_num); // 刪除
    if($rtn_msg) {
      redirect(be_url().'work_q/', 'refresh');
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
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save($kind=NULL)  {
    switch($kind) {
      case "add":
      case "cpy":
        $this->work_q_model->save_add(); // 新增儲存
        break;
      case "upd":
        $this->work_q_model->save_upd(); // 修改儲存
        break;
      case "upd_is_available":
        $this->work_q_model->save_is_available(); // 上下架儲存
        break;
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que()
  //  函數功能: 開窗查詢,或是瀏覽頁面查詢
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
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
    ////redirect(be_url()."work_q/p/1/q/{$q_str}", 'refresh');
    
    // GET 使用
    $data = $this->input->get(); // GET 用
    $_SESSION[$q_str]['que_order_fd_name'] = $data['que_order_fd_name']; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = $data['que_order_kind']; // 排序類別
    redirect(be_url()."work_q/p/1/q/{$q_str}/?{$_SERVER['QUERY_STRING']}", 'refresh');
    return;
  }

  // **************************************************************************
  //  函數名稱: _que_start()
  //  函數功能: 查詢設定，主要清除查詢session資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  private function _que_start($q_str) {
    $_SESSION[$q_str]['que_str'] = ''; // 全文檢索
    $_SESSION[$q_str]['que_order_fd_name'] = ''; // 排序欄位
    $_SESSION[$q_str]['que_order_kind'] = ''; // 排序類別
    return;
  }

  // **************************************************************************
  //  函數名稱: download()
  //  函數功能: 下載關懷紀錄
  //  程式設計: kiwi
  //  設計日期: 2022-02-03(大年初三準備回高雄!!!!!!)
  // **************************************************************************
  public function download() {
    set_time_limit(1800); // 限制處理時間30分鐘
    ini_set('memory_limit', '3072M');
    $time_start = date('Y-m-d H:i:s');
    $work_q_h_row = $this->work_q_model->get_work_q_h(); 
    if(NULL != $work_q_h_row) {
      foreach ($work_q_h_row as $kh => $vh) {
        $route_h_row = $this->route_model->get_one($vh['reh_s_num']);
        if(NULL != $route_h_row) {
          $work_q_h_row[$kh]['reh01'] = $route_h_row->reh01;
          $work_q_b_row = $this->work_q_model->get_work_q_b($vh['s_num']); // 列出單筆明細資料
          if(NULL != $work_q_b_row) {
            foreach($work_q_b_row as $kb => $vb) {
              if($vb['qb02'] != 3) {
                $work_q_h_row[$kh]["qb02_{$vb['qb_order']}"] = $vb["qb02"];
                $work_q_h_row[$kh]["wqb01_arr_{$vb['qb_order']}"] = explode(",", $vb["wqb01"]);
                $work_q_h_row[$kh]["qb03_arr_{$vb['qb_order']}"] = explode(",", $vb["qb03"]);
              }
              else {
                $work_q_h_row[$kh]["wqb01_1"] = $vb["wqb01"];
              }
            }
          }
        }
      }
    }

    $sample_file = FCPATH."pub/sample/work_q_sample.xlsx";
    $objSpreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $objSpreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($sample_file);
    $objSpreadsheet->setActiveSheetIndex(0);
    if(NULL != $work_q_h_row) {
      $row = 2; 
      $ans_1 = '';
      $ans_2 = '';
      $ans_3 = '';
      $ans_4 = '';
      foreach ($work_q_h_row as $k => $v) {
        for($i = 1; $i <= 4; $i++) {
          $val_str = "ans_{$i}";
          ${$val_str} = '';
          if(isset($v["qb02_{$i}"])) {
            if($v["qb02_{$i}"] != 3) {
              foreach($v["wqb01_arr_{$i}"] as $k_wqb => $v_wqb) {
                ${$val_str} = $v["qb03_arr_{$i}"][$v_wqb];
              }
            }
            else {
              ${$val_str} = $v["wqb01_{$i}"];
            }
          }
        }
        $objSpreadsheet->getActiveSheet()->setCellValue("A{$row}", str_replace("-", "/", $v['dys01'])); // 服務日期
        $objSpreadsheet->getActiveSheet()->setCellValue("B{$row}", $v['reh01']); // 路線
        $objSpreadsheet->getActiveSheet()->setCellValue("C{$row}", $v['reb01']); // 順序
        $objSpreadsheet->getActiveSheet()->setCellValue("D{$row}", $v['ct_name']); // 案主名稱
        $objSpreadsheet->getActiveSheet()->setCellValue("E{$row}", "{$v['sec01_str']}-{$v['sec04_str']}"); // 服務名稱
        $objSpreadsheet->getActiveSheet()->setCellValue("F{$row}", str_replace("-", "/", $v['e_date'])); // 填寫日期
        $objSpreadsheet->getActiveSheet()->setCellValue("G{$row}", "{$v['dp01']}{$v['dp02']}"); // 填寫人
        $objSpreadsheet->getActiveSheet()->setCellValue("H{$row}", $ans_1); // 看到案主
        $objSpreadsheet->getActiveSheet()->setCellValue("I{$row}", $ans_2); // 有狀況
        $objSpreadsheet->getActiveSheet()->setCellValue("J{$row}", $ans_3); // 是否發放代餐
        $objSpreadsheet->getActiveSheet()->setCellValue("K{$row}", $ans_4); // 未發原因
        $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);// 垂直置中
        $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); // 置右
        $objSpreadsheet->getActiveSheet()->getStyle("A{$row}:K{$row}")->getBorders()->getAllborders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $row++;
      }
    }
    
    $ch_filename = "";
    if(!empty($_GET['que_reh_s_num'])) {
      if('all' != $_GET['que_reh_s_num']) {
        $route_row = $this->route_model->get_one($_GET['que_reh_s_num']);
        $ch_filename .= "{$route_row->reh01}線_";
      }
    }
    
    if(!empty($_GET['que_dys01_start'])) {
      $dys01_start = str_replace("-" , "_" , $_GET['que_dys01_start']);
      $ch_filename .= "{$dys01_start}";
      if(!empty($_GET['que_dys01_end'])) {
        $ch_filename .= "~";
      }
    }
    
    if(!empty($_GET['que_dys01_end'])) {
      $dys01_end = str_replace("-" , "_" , $_GET['que_dys01_end']);
      $ch_filename .= "{$dys01_end}_";
    }

    $ch_filename .= "關懷紀錄.xlsx";
    $en_filename = "{$_GET['que_reh_s_num']}_work_q.xlsx";
    
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

  // **************************************************************************
  //  函數名稱: prn()
  //  函數功能: 列印
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function prn() {
    $msel = 'prn';
    return;
  }

  function __destruct() {
    $url_str[] = 'be/work_q/save';
    $url_str[] = 'be/work_q/del';
    $url_str[] = 'be/work_q/download';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}
