<?php
class Donate_import_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('dei03','dei08'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: chk_dei02()
  //  函數功能: 檢查收據編號是否重複
  //  程式設計: shirley
  //  設計日期: 2023-02-10
  // **************************************************************************
  public function chk_dei02($dei02) {
    $tbl_donate_import = $this->zi_init->chk_tbl_no_lang('donate_import'); // 捐款資料(線下資料匯入)
    $row = NULL;
    $sql = "select {$tbl_donate_import}.*
                   
            from {$tbl_donate_import}
            where {$tbl_donate_import}.d_date is null
                  and {$tbl_donate_import}.dei02 = ?
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($dei02));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      // 遮罩欄位處理 Begin //
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v); // 遮罩欄位處理
        $row->$fd_name = $fd_val;
      } 
      // 遮罩欄位處理 End //
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_donate_import = $this->zi_init->chk_tbl_no_lang('donate_import'); // 捐款資料(線下資料匯入)
    $where = " {$tbl_donate_import}.d_date is null";
    $order = " {$tbl_donate_import}.dei01 desc ";

    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_donate_import}.dei01 like '%{$que_str}%' /* 捐款日期 */                       
                       or {$tbl_donate_import}.dei02 like '%{$que_str}%' /* 收據編號 */                       
                       or {$tbl_donate_import}.dei04 like '%{$que_str}%' /* 捐款物品 */                       
                       or {$tbl_donate_import}.dei06 like '%{$que_str}%' /* 捐款方式 */                       
                       or {$tbl_donate_import}.dei05 like '%{$que_str}%' /* 捐款金額 */                       
                       or {$tbl_donate_import}.dei07 like '%{$que_str}%' /* 捐款用途/方案 */                       
                       or AES_DECRYPT({$tbl_donate_import}.dei03,'{$this->db_crypt_key2}') like '%{$que_str}%'  /* 收據抬頭 */
                      )
                ";
    }

    if(!empty($get_data['que_type'])) { // 捐贈類型
      $que_type = $get_data['que_type'];
      $que_type = $this->db->escape_like_str($que_type);
      if(2 == $que_type){
        $where .= " and {$tbl_donate_import}.dei07 != '捐物'  /* 捐贈類型 */ ";
      }else if(3 == $que_type){
        $where .= " and {$tbl_donate_import}.dei07 = '捐物'  /* 捐贈類型 */ ";
      }
    }
    if(!empty($get_data['que_dei03'])) { // 收據抬頭
      $que_dei03 = $get_data['que_dei03'];
      $que_dei03 = $this->db->escape_like_str($que_dei03);
      $where .= " and AES_DECRYPT({$tbl_donate_import}.dei03,'{$this->db_crypt_key2}') like '%{$que_dei03}%'  /* 收據抬頭 */ ";
    }
    if((!empty($get_data['start'])) or (!empty($get_data['end']))) { // 捐款日期
      $que_start = $get_data['start'];
      $que_end = $get_data['end'];
      $que_start = $this->db->escape_like_str($que_start);
      $que_end = $this->db->escape_like_str($que_end);
      $where .= " and {$tbl_donate_import}.dei01 between '{$que_start}' and '{$que_end}' /* 捐款日期 */ ";
    }
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_donate_import}.s_num
                from {$tbl_donate_import}
                where $where
                group by {$tbl_donate_import}.s_num
                order by {$tbl_donate_import}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_donate_import}.*
                   {$this->_aes_fd()}
            from {$tbl_donate_import}
            where {$where}
            order by {$order}
            {$limit}
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          // 遮罩欄位處理 Begin //
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          // 遮罩欄位處理 End //
        }
      }
    }
    return(array($data,$row_cnt));
  }
  // **************************************************************************
  //  函數名稱: get_que_front()
  //  函數功能: 取得查詢資料
  //  程式設計: shirley
  //  設計日期: 2022-11-30
  // **************************************************************************
  public function get_que_front($q_str=NULL,$pg=NULL) {
    $tbl_donate_import = $this->zi_init->chk_tbl_no_lang('donate_import'); // 捐款資料(線下資料匯入)
    $where = " {$tbl_donate_import}.d_date is null";
    $order = " {$tbl_donate_import}.dei01 desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_type'])) { // 捐贈類型
      $que_type = $get_data['que_type'];
      $que_type = $this->db->escape_like_str($que_type);
      if(2 == $que_type){
        $where .= " and {$tbl_donate_import}.dei07 != '捐物'  /* 捐贈類型 */ ";
      }else if(3 == $que_type){
        $where .= " and {$tbl_donate_import}.dei07 = '捐物'  /* 捐贈類型 */ ";
      }
    }
    if(!empty($get_data['que_dei03'])) { // 收據抬頭
      $que_dei03 = $get_data['que_dei03'];
      $que_dei03 = $this->db->escape_like_str($que_dei03);
      $where .= " and AES_DECRYPT({$tbl_donate_import}.dei03,'{$this->db_crypt_key2}') like '%{$que_dei03}%'  /* 收據抬頭 */ ";
    }
    if((!empty($get_data['start'])) or (!empty($get_data['end']))) { // 捐款日期
      $que_start = $get_data['start'];
      $que_end = $get_data['end'];
      $que_start = $this->db->escape_like_str($que_start);
      $que_end = $this->db->escape_like_str($que_end);
      $where .= " and {$tbl_donate_import}.dei01 between '{$que_start}' and '{$que_end}' /* 捐款日期 */ ";
    }
    if(!empty($get_data['que_addr'])) { // 地址
      $que_addr = $get_data['que_addr'];
      $que_addr = $this->db->escape_like_str($que_addr);
      $where .= " and  AES_DECRYPT({$tbl_donate_import}.dei08,'{$this->db_crypt_key2}') like '%{$que_addr}%' /* 地址 */ ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_donate_import}.s_num
                from {$tbl_donate_import}
                where $where
                group by {$tbl_donate_import}.s_num
                order by {$tbl_donate_import}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*20);
    $limit_e = 20;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_donate_import}.*
                   {$this->_aes_fd()}
            from {$tbl_donate_import}
            where {$where}
            order by {$order}
            {$limit}
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          // 遮罩欄位處理 Begin //
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          // 遮罩欄位處理 End //
        }
      }
    }
    return(array($data,$row_cnt));
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  private function _aes_fd() {
    $tbl_donate_import = $this->zi_init->chk_tbl_no_lang('donate_import'); // 捐款資料
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_donate_import}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  private function _symbol_text($row,$fd_name) {
    $fd_name_mask = '';
    $fd_val = NULL;
    if(isset($row->$fd_name)) {
      $fd_val = $row->$fd_name;
    }
    if('' != $fd_name_mask) {
      $fd_name = $fd_name_mask;
    }
    return(array($fd_name,$fd_val));
  }
  // **************************************************************************
  //  函數名稱: save_import_excel()
  //  函數功能: 儲存匯入的excel檔捐款資料(110年以後)
  //  程式設計: shirley
  //  設計日期: 2022-11-22
  // **************************************************************************
  public function save_import_excel() {
    set_time_limit(240); // 4 minutes，, maximum execution time
    ini_set('memory_limit', '3072M'); // Give it a bigger size first to avoid running out of memory
    $time_start = date('Y-m-d H:i:s');
    $tbl_donate_import = $this->zi_init->chk_tbl_no_lang('donate_import'); // 捐款資料
    $this->load->library('excel');
    $v = $this->input->post();
    $excel = FCPATH."upload_files/donate_import/{$v['excel_file']}"; // excel file
    $rtn_msg = '';
    $objPHPExcel = PHPExcel_IOFactory::load($excel);
    $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
    $highestRow = $sheet->getHighestRow(); // 取得總列數
    $data_cnt   = $highestRow-1; // 資料筆數
    // $column_str = $sheet->getHighestColumn(); // 目前execl的x軸的最後一個位置
    // $column_cnt = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()); // x軸最大數量
    $row_cnt=0; // 資料筆數
    $add_cnt=0; // 新增筆數
    $upd_cnt=0; // 更新筆數
    //$upd_no_cnt=0; // 未更新筆數
    $err_cnt=0; // 失敗筆數
    $err_data= NULL; // 失敗內容
    $rtn_msg = '';
    for($row=2;$row<=$highestRow;$row++) {
      $data = NULL;
      $dei02 = trim($sheet->getCell("B{$row}")->getValue()); // 收據編號
      if(!empty($dei02)){
        $data['dei03'] = trim($sheet->getCell("F{$row}")->getValue()); // 收據抬頭
        $data['dei01'] = NULL;
        if(!empty(trim($sheet->getCell("G{$row}")->getValue()))){
          $data['dei01'] = date('Y-m-d',PHPExcel_Shared_Date::ExcelToPHP(trim($sheet->getCell("G{$row}")->getValue()))); // 捐款日期
        }
        $data['dei05'] = trim($sheet->getCell("H{$row}")->getValue()); // 捐款金額
        $data['dei07'] = trim($sheet->getCell("J{$row}")->getValue()); // 項目別
        if(!empty(trim($sheet->getCell("K{$row}")->getValue()))){
          $data['dei07'] .= "-";
          $data['dei07'] .= trim($sheet->getCell("K{$row}")->getValue()); // 專案別
        }
        $data['dei06'] = trim($sheet->getCell("L{$row}")->getValue()); // 捐贈方式
        $data['dei04'] = trim($sheet->getCell("N{$row}")->getValue()); // 捐物
        $data['dei08'] = trim($sheet->getCell("Y{$row}")->getValue()); // 收據地址
        $data['dei10'] = trim($sheet->getCell("N{$row}")->getValue()); // 捐款備註
        // 加密欄位處理 Begin //
        foreach ($data as $k_fd_name => $v_data) {
          if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
            $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
            unset($data[$k_fd_name]);
          }
        } 
        // 加密欄位處理 End //
        $donate_import_row = $this->chk_dei02($dei02);
        if(NULL != $donate_import_row){
          $data['e_empno'] = $_SESSION['acc_s_num'];
          $data['e_date'] = date('Y-m-d H:i:s');
          $this->db->where('dei02',$dei02);
          if($this->db->update($tbl_donate_import, $data)) {
            $upd_cnt++; // 更新筆數
          }else {
            $err_cnt++; // 失敗筆數
          }
        }else{
          $data['dei02'] = $dei02;
          $data['b_empno'] = $_SESSION['acc_s_num'];
          $data['b_date'] = date('Y-m-d H:i:s');
          if($this->db->insert($tbl_donate_import, $data)) {
            $add_cnt++; // 新增筆數
          }else {
            $err_cnt++; // 失敗筆數
            // $err_data[] = "新增=>{$data['qb01']}"; // 失敗內容的s_num,name
          }
        }
        $row_cnt++; // 資料筆數
      }
    }
    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff>=60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }
    
    $rtn_msg .= "<table class='table table-bordered table-striped table-hover table-sm'>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td width='30%' align='right'>上傳資料總筆數</td>";
    $rtn_msg .= "    <td width='70%' class='text-left'>{$row_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>新增筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$add_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>更新筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$upd_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>失敗筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$err_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>處理時間</td>";
    $rtn_msg .= "    <td class='text-left'>{$time_diff}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "</table>";
    
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_import_old_excel()
  //  函數功能: 儲存匯入的excel檔捐款資料(109年以前)
  //  程式設計: shirley
  //  設計日期: 2022-12-02
  // **************************************************************************
  public function save_import_old_excel() {
    set_time_limit(240); // 4 minutes，, maximum execution time
    ini_set('memory_limit', '3072M'); // Give it a bigger size first to avoid running out of memory
    $time_start = date('Y-m-d H:i:s');
    $tbl_donate_import = $this->zi_init->chk_tbl_no_lang('donate_import'); // 捐款資料
    $this->load->library('excel');
    $v = $this->input->post();
    $excel = FCPATH."upload_files/donate_import/{$v['excel_file']}"; // excel file
    $rtn_msg = '';
    $objPHPExcel = PHPExcel_IOFactory::load($excel);
    $sheet = $objPHPExcel->getSheet(0); // 讀取第一個工作表(編號從 0 開始)
    $highestRow = $sheet->getHighestRow(); // 取得總列數
    $data_cnt   = $highestRow-1; // 資料筆數
    $column_str = $sheet->getHighestColumn(); // 目前execl的x軸的最後一個位置
    $column_cnt = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn()); // x軸最大數量
    $row_cnt=0; // 資料筆數
    $add_cnt=0; // 新增筆數
    //$upd_cnt=0; // 更新筆數
    //$upd_no_cnt=0; // 未更新筆數
    $err_cnt=0; // 失敗筆數
    $err_data= NULL; // 失敗內容
    $rtn_msg = '';
    for($row=2;$row<=$highestRow;$row++) {
      $row_cnt++; // 資料筆數
      $data = NULL;
      $data['dei02'] = trim($sheet->getCell("A{$row}")->getValue()); // 收據編號
      $data['dei03'] = trim($sheet->getCell("C{$row}")->getValue()); // 收據抬頭
      $dei01 = trim($sheet->getCell("B{$row}")->getValue()); // 捐款日期
      if('10' == substr("{$dei01}",0,2)){
        $dei01 = str_replace("/","-","{$dei01}");
        $arr = explode("-",$dei01);
        $arr[0] += 1911; //民國年加 1911 為西元年
        $dei01 = implode("-",$arr);
        $data['dei01'] = $dei01; // 捐款日期
      }else{
        $dateFormat = \PHPExcel_Shared_Date::ExcelToPHP($dei01);
        $data['dei01'] = date("Y-m-d",$dateFormat); // 捐款日期
      }
      $data['dei07'] = trim($sheet->getCell("E{$row}")->getValue()); // 項目別
      if('捐物' == $data['dei07']){
        $data['dei04'] = trim($sheet->getCell("D{$row}")->getValue()); // 捐物
      }else{
        $data['dei05'] = trim($sheet->getCell("D{$row}")->getValue()); // 捐款金額
      }
      // 加密欄位處理 Begin //
      foreach ($data as $k_fd_name => $v_data) {
        if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
          $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
          unset($data[$k_fd_name]);
        }
      } 
      // 加密欄位處理 End //
      $data['b_empno'] = $_SESSION['acc_s_num'];
      $data['b_date'] = date('Y-m-d H:i:s');
      if($this->db->insert($tbl_donate_import, $data)) {
        $add_cnt++; // 新增筆數
      }else {
        $err_cnt++; // 失敗筆數
        // $err_data[] = "新增=>{$data['qb01']}"; // 失敗內容的s_num,name
      }
    }
    
    $time_end = date('Y-m-d H:i:s');
    $time_diff = strtotime($time_end)-strtotime($time_start); // 分鐘
    if($time_diff>=60) {
      $time_diff = round($time_diff/60,1).' 分'; // 分鐘
    }
    else {
      $time_diff = $time_diff.' 秒'; // 秒
    }
    
    $rtn_msg .= "<table class='table table-bordered table-striped table-hover table-sm'>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td width='30%' align='right'>上傳資料總筆數</td>";
    $rtn_msg .= "    <td width='70%' class='text-left'>{$row_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>新增筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$add_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>失敗筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$err_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>處理時間</td>";
    $rtn_msg .= "    <td class='text-left'>{$time_diff}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "</table>";
    
    echo $rtn_msg;
    return;
  }
}
?>