<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class News extends CI_Controller { // 帳號
  public function __construct() {
    parent::__construct();
    $this->zi_init->header('be'); // 網頁 head
    $this->load->model('news_model'); // 最新消息
    $this->load->model('sys_language_model'); // 語系
	  $mrand_str = $this->config->item('rand_str_8');
	  $this->tpl->assign('tv_rand_str',$mrand_str);
	  $this->tpl->assign('tv_ct_name',$this->router->fetch_class());
	  $this->tpl->assign('tv_menu_title','最新消息');
    $this->tpl->assign('tv_is_super',$_SESSION['is_super']);
    $this->tpl->assign('tv_list_btn',$this->lang->line('list')); // 瀏覽按鈕文字
    $this->tpl->assign('tv_disp_btn',$this->lang->line('disp')); // 明細按鈕文字
	  $this->tpl->assign('tv_add_btn',$this->lang->line('add')); // 新增按鈕文字
	  $this->tpl->assign('tv_upd_btn',$this->lang->line('upd')); // 修改按鈕文字
	  $this->tpl->assign('tv_del_btn',$this->lang->line('del')); // 刪除按鈕文字
	  $this->tpl->assign('tv_add_link',be_url().'news/add/');
	  $this->tpl->assign('tv_pdf_flag','Y'); // 使否顯示pdf按鈕
	  $this->tpl->assign('tv_pdf_btn',$this->lang->line('pdf')); // 輸出pdf按鈕文字
	  $this->tpl->assign('tv_download_execl_flag','Y'); // 使否顯示下載execl按鈕
	  $this->tpl->assign('tv_download_execl_btn',$this->lang->line('download_execl')); // 下載execl按鈕文字
	  $this->tpl->assign('tv_que_btn',$this->lang->line('que')); // 搜尋按鈕文字
	  $this->tpl->assign('tv_exit_btn',$this->lang->line('exit')); // 離開按鈕文字
	  $this->tpl->assign('tv_save_btn',$this->lang->line('save')); // 儲存按鈕文字
	  $this->tpl->assign('tv_return_link',be_url()."news/"); // return 預設到瀏覽畫面
	  $this->tpl->assign('tv_upd_ok',$this->lang->line('upd_ok')); // 修改成功!!
	  $this->tpl->assign('tv_add_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_del_ok',$this->lang->line('del_ok')); // 刪除成功!!
	  $this->tpl->assign('tv_upd_ng',$this->lang->line('upd_ng')); // 修改失敗!!
	  $this->tpl->assign('tv_add_ng',$this->lang->line('add_ng')); // 新增失敗!!
	  $this->tpl->assign('tv_del_ng',$this->lang->line('del_ng')); // 刪除失敗!!
	  $this->tpl->assign('tv_validate_err',$this->lang->line('validate_err')); // 請輸入正確資料!!
	  //u_var_dump($_SERVER);
	  //u_var_dump($this->router->fetch_class());
	  //u_var_dump($this->router->fetch_method());
	  return;
  }

  // **************************************************************************
  //  函數名稱: index()
  //  函數功能: 瀏覽資料
  //  程式設計: Tony
  //  設計日期: 2018/5/9
  // **************************************************************************
	public function index($pg=1,$que=NULL)	{
	  $this->load->library('pagination');
	  $msel = 'list';
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 瀏覽
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_title','最新消息瀏覽');
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
    $this->tpl->assign('tv_add_link',be_url().'news/add/');
    $this->tpl->assign('tv_disp_link',be_url().'news/disp/');
    $this->tpl->assign('tv_upd_link',be_url().'news/upd/');
    $this->tpl->assign('tv_del_link',be_url().'news/del/');
    $this->tpl->assign('tv_pdf_link',be_url().'news/pdf/');
    $this->tpl->assign('tv_download_execl_link',be_url().'news/download_execl/');
    $this->tpl->assign('tv_que_link',be_url().'news/p/1/q/');
    $this->tpl->assign('tv_f_que',rawurldecode($que));
	  $this->tpl->assign('tv_save_link',be_url().'news/save/');
	  list($news_row,$row_cnt) = $this->news_model->get_que($que,$pg); // 列出教師資料
	  $this->tpl->assign('tv_news_row',$news_row);

    $config['base_url'] = be_url()."news/p/";
    $config['suffix'] = "/q/{$que}";
    $config['first_url'] = be_url()."/news/p/1/q/{$que}";
    $config['total_rows'] = $row_cnt; // 總筆數
    $config['uri_segment'] = 4;
    $config['per_page'] = PG_QTY; // 每頁筆數
    $this->pagination->initialize($config);
    $pg_link = $this->pagination->create_links();
    $this->tpl->assign('tv_pg_link',$pg_link);
    $this->tpl->assign('tv_total_rows',$row_cnt);
    $this->tpl->display("be/news.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: disp()
  //  函數功能: 明細畫面
  //  程式設計: Tony
  //  設計日期: 2019/4/23
  // **************************************************************************
	public function disp($s_num)	{
	  $msel = 'disp';
    $news_row = $this->news_model->get_one($s_num); // 列出帳號資料
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_news_row',$news_row);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
	  $this->tpl->assign('tv_save_link',be_url()."news/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."news/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."news/"); // 上一層連結位置
	  $this->tpl->assign('tv_upd_link',be_url().'news/upd/');
    //$this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/origin/'); // origin 保留上傳檔名，要注意中文的問題
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/ci_std_/xls/');
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/');
    $this->tpl->display("be/news_disp.html");
	  return;
	}

  // **************************************************************************
  //  函數名稱: add()
  //  函數功能: 新增輸入畫面
  //  程式設計: Tony
  //  設計日期: 2017/7/16
  // **************************************************************************
	public function add()	{ // 新增
	  $msel = 'add';
	  $news_row = NULL;
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 新增
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('add_ok')); // 新增成功!!
	  $this->tpl->assign('tv_news_row',$news_row);
	  $this->tpl->assign('tv_save_link',be_url()."news/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."news/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."news/"); // 上一層連結位置
    //$this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/origin/'); // origin 保留上傳檔名，要注意中文的問題
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/ci_std_/');
    //$this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/ci_std_/xls/');
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/');
	  $this->tpl->display("be/news_edit.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: upd()
  //  函數功能: 修改輸入畫面
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
	public function upd($s_num)	{
	  $msel = 'upd';
    $news_row = $this->news_model->get_one($s_num); // 列出帳號資料
	  $this->tpl->assign('tv_breadcrumb3',$this->lang->line($msel)); // 修改
	  $this->tpl->assign('tv_msel',$msel);
	  $this->tpl->assign('tv_news_row',$news_row);
	  $this->tpl->assign('tv_save_ok',$this->lang->line('upd_ok')); // 更新成功!!
	  $this->tpl->assign('tv_save_link',be_url()."news/save/{$msel}");
	  $this->tpl->assign('tv_now_link',base_url().uri_string()); // 目前網址
	  $this->tpl->assign('tv_exit_link',be_url()."news/"); // 離開按鈕的連結位置
	  $this->tpl->assign('tv_parent_link',be_url()."news/"); // 上一層連結位置
    //$this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/origin/'); // origin 保留上傳檔名，要注意中文的問題
    $this->tpl->assign('tv_upload_link', be_url() . 'fileupload/upload/ci_std_/');
    $this->tpl->assign('tv_upload_path', pub_url('') . 'uploads/');
    $this->tpl->display("be/news_edit.html");
	  return;
	}
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
	public function del($s_num=NULL)	{
	  $rtn_msg = $this->news_model->del($s_num); // 刪除
	  if($rtn_msg) {
	    redirect(be_url().'news/', 'refresh');
	  }
	  else {
	    die($rtn_msg); // 刪除失敗!!!
	  }
	  return;
	}
  // **************************************************************************
  //  函數名稱: pdf()
  //  函數功能: 輸出pdf
  //  程式設計: Tony
  //  設計日期: 2018/5/9
  // **************************************************************************
	public function pdf($s_num) {
	  $news_row = $this->news_model->get_one($s_num); // 列出帳號資料
    // Barcode code
    $style = array(
    	'position' => '',
    	'align' => 'C',
    	'stretch' => false,
    	'fitwidth' => true,
    	'cellfitalign' => '',
    	'border' => false,
    	'hpadding' => 'auto',
    	'vpadding' => 'auto',
    	'fgcolor' => array(0,0,0),
    	'bgcolor' => false, //array(255,255,255),
    	'text' => true,
    	'font' => 'helvetica',
    	'fontsize' => 8,
    	'stretchtext' => 4
    );
	  $this->load->library('pdf');
    $pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
    //$pdf = new Pi_pdf('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetAutoPageBreak(true);
    $pdf->SetAuthor('Meimen');
    $pdf->SetDisplayMode('real', 'default');
    $pdf->SetTitle("最新消息");
    $pdf->AddPage();
    
    $this->tpl->assign('tv_pdf_b_date',date('Y/m/d')); // 列印日期
    $this->tpl->assign('tv_news_row',$news_row);
    $pdf_content = $this->tpl->fetch("be/news_pdf.html");
	  $pdf->writeHTML($pdf_content, true, false, true, false, '');
	  $pdf->write1DBarcode($news_row->news_publication, 'C128', '', '', 60, 18, 0.5, $style, 'N');
    $filename = "news-".date('Y-m-d').".pdf";
    ob_end_clean();
    //$pdf->Output();
    //ob_end_flush();
    echo $pdf->Output($filename, 'I');
    return;
  }
  // **************************************************************************
  //  函數名稱: download_execl()
  //  函數功能: 下載execl檔案
  //  程式設計: Tony
  //  設計日期: 2018/5/9
  // **************************************************************************
	public function download_execl() {
	  $time_start = date('Y-m-d H:i:s');
    
    $this->load->library('excel');
    //$this->load->library('phpspreadsheet');
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator("Meimen")
                                 ->setLastModifiedBy("")
                                 ->setTitle("最新消息")
                                 ->setSubject("")
                                 ->setDescription("")
                                 ->setKeywords("")
                                 ->setCategory("");
    // 頁籤1
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle("最新消息");
    // 設定欄寬
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
    
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);   // 設定size
    $objPHPExcel->getActiveSheet()->mergeCells('A1:C1'); // 合併儲存格
    
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "最新消息");
    $objPHPExcel->getActiveSheet()->setCellValue('A2', "序");
    $objPHPExcel->getActiveSheet()->setCellValue('B2', "日期");
    $objPHPExcel->getActiveSheet()->setCellValue('C2', "標題");
    $objPHPExcel->getActiveSheet()->setCellValue('D2', "內容");
    
    // 對齊
    $objPHPExcel->getActiveSheet()->getStyle("A2:D2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 水平置中
    $objPHPExcel->getActiveSheet()->getStyle("A2:D2")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER); // 垂直置中
    
    $objPHPExcel->getActiveSheet()->freezePane('A3'); // 凍結視窗
    $num=3;
    $news_row = $this->news_model->get_all(); // 列出帳號資料
    foreach ($news_row as $k => $v) {
      $objPHPExcel->getActiveSheet()->setCellValue("A{$num}", $v['s_num']);
      $objPHPExcel->getActiveSheet()->setCellValue("B{$num}", $v['news_publication']);
      $objPHPExcel->getActiveSheet()->setCellValue("C{$num}", $v['news_title']);
      $objPHPExcel->getActiveSheet()->setCellValue("D{$num}", $v['news_content']);
      $num++;
    }
    // 對齊
    $objPHPExcel->getActiveSheet()->getStyle("A3:B{$num}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); // 水平置中
    $objPHPExcel->getActiveSheet()->getStyle("A3:B{$num}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER); // 垂直置中
    $objPHPExcel->getActiveSheet()->getStyle("C3:D{$num}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); //靠左
    // 設定換行
    $objPHPExcel->getActiveSheet()->getStyle("A3:D{$num}")->getAlignment()->setWrapText(true);
    
    $objPHPExcel->setActiveSheetIndex(0); // 回到第一個頁籤
    $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff>=60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }
    
    $filename = "最新消息-".date('Y-m-d').".xlsx";
    switch(ENVIRONMENT) {
      case 'development':
      case 'testing':
        $filename = iconv('utf-8','big5',$filename);
        break;
      case 'production':
        $filename = iconv('utf-8','big5',$filename); // for ie
        break;
    }
    ob_end_clean();
    header("Content-type: text/html; charset=utf-8");
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment;filename=" . $filename);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    return;
  }
  // **************************************************************************
  //  函數名稱: save()
  //  函數功能: 儲存(新增,修改,刪除)
  //  程式設計: Tony
  //  設計日期: 2017/7/15
  // **************************************************************************
	public function save($kind=NULL)	{
	  switch($kind) {
	    case "add":
	      $this->news_model->save_add(); // 新增儲存
	      break;
	    case "upd":
	      $this->news_model->save_upd(); // 修改儲存
	      break;
	    case "upd_is_available":
	      $this->news_model->save_is_available(); // 上下架儲存
	      break;
	  }
	  return;
	}

  function __destruct() {
    $url_str[] = 'be/news/save';
    $url_str[] = 'be/news/del';
    $url_str[] = 'be/news/download_execl';
    if(!is_footer_not_show($url_str)) {
      $this->zi_init->footer('be'); // 網頁 foot
    }
  }
}