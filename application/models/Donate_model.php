<?php
class Donate_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('de01','de02','de03_phone','de03_email','de04_addr','de10','de12'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function get_one($s_num,$de23=NULL) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $tbl_donate_item = $this->zi_init->chk_tbl_no_lang('donate_item'); // 捐款項目
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $where = "";
    if($de23 != NULL){
      $where = "and {$tbl_donate}.de23 = '{$de23}'";
    }
    $row = NULL;
    $sql = "select {$tbl_donate}.*,
                   {$tbl_donate_item}.di01,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_donate}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_donate}.de05
                     when 'N' then '否'
                     when 'Y' then '是'
                     when '1' then '不索取'
                     when '2' then '紙本年度收據，響應環保節省資源'
                     when '3' then '紙本單筆收據'
                   end as de05_str,
                   case {$tbl_donate}.de09
                     when '1' then '信用卡(一次付清)'
                     when '2' then 'Line Pay'
                     when '3' then 'ATM轉帳'
                     when '4' then '超商繳費'
                     when '5' then '超商條碼繳費'
                     when '6' then 'GOOGLE PAY'
                     when '7' then '信用卡(定期定額)'
                     when '8' then 'WebATM'
                   end as de09_str,
                   case {$tbl_donate}.de21
                     when 'N' then '否'
                     when 'Y' then '是'
                   end as de21_str,
                   case {$tbl_donate}.de22
                     when 'N' then '否'
                     when 'Y' then '是'
                   end as de22_str
                   {$this->_aes_fd()}
                   
            from {$tbl_donate}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_donate}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_donate}.e_empno
            left join {$tbl_donate_item} on {$tbl_donate_item}.s_num = {$tbl_donate}.di_s_num
            where {$tbl_donate}.d_date is null
                  and {$tbl_donate}.s_num = ?
                  {$where}
            order by {$tbl_donate}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
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
  //  函數名稱: get_one_by_de18()
  //  函數功能: 取得單筆資料
  //  程式設計: shirley
  //  設計日期: 2022-02-11
  // **************************************************************************
  public function get_one_by_de18($de18) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $de18 = (int)$this->db->escape_like_str($de18);
    $row = NULL;
    $sql = "select {$tbl_donate}.*
                   {$this->_aes_fd()}
                   
            from {$tbl_donate}
            where {$tbl_donate}.d_date is null
                  and {$tbl_donate}.de18 = ?
            order by {$tbl_donate}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($de18));
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
  //  函數名稱: get_one_by_de23()
  //  函數功能: 取得單筆資料
  //  程式設計: shirley
  //  設計日期: 2022-02-22
  // **************************************************************************
  public function get_one_by_de23($de23) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_donate}.*,
                   case {$tbl_donate}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_donate}.de05
                     when 'N' then '否'
                     when 'Y' then '是'
                   end as de05_str,
                   case {$tbl_donate}.de09
                     when '1' then '信用卡(一次付清)'
                     when '2' then 'Line Pay'
                     when '3' then 'ATM轉帳'
                     when '4' then '超商繳費'
                     when '5' then '超商條碼繳費'
                     when '6' then 'GOOGLE PAY'
                     when '7' then '信用卡(定期定額)'
                     when '8' then 'WebATM'
                   end as de09_str,
                   case {$tbl_donate}.de22
                     when 'N' then '否'
                     when 'Y' then '是'
                   end as de22_str
                   {$this->_aes_fd()}
                   
            from {$tbl_donate}
            where {$tbl_donate}.d_date is null
                  and {$tbl_donate}.de23 = {$de23}
            order by {$tbl_donate}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql);
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
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_donate}.*
                   
            from {$tbl_donate}
            where {$tbl_donate}.d_date is null
                  and {$tbl_donate}.fd_name = ?
            order by {$tbl_donate}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($fd_name));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function get_all() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $tbl_donate_item = $this->zi_init->chk_tbl_no_lang('donate_item'); // 捐款項目
    $data = NULL;
    $sql = "select {$tbl_donate}.*
                   {$this->_aes_fd()},
                   {$tbl_donate_item}.di01
                   
            from {$tbl_donate}
            left join {$tbl_donate_item} on {$tbl_donate_item}.s_num = {$tbl_donate}.di_s_num
            where {$tbl_donate}.d_date is null
            order by {$tbl_donate}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          // 遮罩欄位處理 Begin //
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name); // 遮罩欄位處理
          $data[$row->s_num][$fd_name] = $fd_val;
          // 遮罩欄位處理 End //
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_all_is_available()
  //  函數功能: 取得所有已經啟用的資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $data = NULL;
    $sql = "select {$tbl_donate}.*
                   
            from {$tbl_donate}
            where {$tbl_donate}.d_date is null
                  and {$tbl_donate}.is_available = 1 /* 啟用 */
            order by {$tbl_donate}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $tbl_donate_item = $this->zi_init->chk_tbl_no_lang('donate_item'); // 捐款項目
    $where = " {$tbl_donate}.d_date is null ";
    $order = " {$tbl_donate}.s_num desc ";

    $get_data = $this->input->get();
    if(!isset($get_data['que_de19'])) { // 付款狀態
      $get_data['que_de19'] = '1'; // 付款狀態
    }

    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and (AES_DECRYPT({$tbl_donate}.de01,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 姓 */                       
                       or AES_DECRYPT({$tbl_donate}.de02,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 名字 */                       
                       or AES_DECRYPT({$tbl_donate}.de03_email,'{$this->db_crypt_key2}') like '%{$que_str}%' /* Email */                       
                       or AES_DECRYPT({$tbl_donate}.de03_phone,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 電話 */                       
                       or {$tbl_donate}.de04_zipcode like '%{$que_str}%' /* 郵遞區號 */                       
                       or {$tbl_donate}.de04_county like '%{$que_str}%' /* 縣市 */                       
                       or {$tbl_donate}.de04_district like '%{$que_str}%' /* 鄉鎮市區 */                       
                       or AES_DECRYPT({$tbl_donate}.de04_addr,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 地址 */                       
                       or {$tbl_donate}.de05 like '%{$que_str}%' /* 是否需要收據 */                       
                       or {$tbl_donate}.de06 like '%{$que_str}%' /* 捐款金額 */                       
                       or {$tbl_donate}.de07 like '%{$que_str}%' /* 每月定期定額期數 */                       
                       or {$tbl_donate}.de08 like '%{$que_str}%' /* 頻率(每年、每月) */                       
                       or {$tbl_donate}.de09 like '%{$que_str}%' /* 捐款方式 */
                      )
                ";
    }

    if(!empty($get_data['que_de01'])) { // 姓
      $que_de01 = $get_data['que_de01'];
      $que_de01 = $this->db->escape_like_str($que_de01);
      $where .= " and AES_DECRYPT({$tbl_donate}.de01,'{$this->db_crypt_key2}') like '%{$que_de01}%'  /* 姓 */ ";
    }
    if(!empty($get_data['que_de02'])) { // 名字
      $que_de02 = $get_data['que_de02'];
      $que_de02 = $this->db->escape_like_str($que_de02);
      $where .= " and AES_DECRYPT({$tbl_donate}.de02,'{$this->db_crypt_key2}') like '%{$que_de02}%'  /* 名字 */ ";
    }
    if(!empty($get_data['que_de03'])) { // Email
      $que_de03 = $get_data['que_de03'];
      $que_de03 = $this->db->escape_like_str($que_de03);
      $where .= " and AES_DECRYPT({$tbl_donate}.de03,'{$this->db_crypt_key2}') like '%{$que_de03}%'  /* Email */ ";
    }
    if(!empty($get_data['que_de19'])) { // 付款狀態 1=捐款成功，2=捐款失敗，3=等待入帳
      $que_de19 = $get_data['que_de19'];
      $que_de19 = $this->db->escape_like_str($que_de19);
      $where .= " and {$tbl_donate}.de19 = {$que_de19}  /* 付款狀態 1=捐款成功，2=捐款失敗，3=等待入帳 */ ";
    }
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_donate}.s_num
                from {$tbl_donate}
                left join {$tbl_donate_item} on {$tbl_donate_item}.s_num = {$tbl_donate}.di_s_num
                where $where
                group by {$tbl_donate}.s_num
                order by {$tbl_donate}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_donate}.*,
                   case {$tbl_donate}.de05
                     when 'N' then '否'
                     when 'Y' then '是'
                     when '1' then '不索取'
                     when '2' then '紙本年度收據，響應環保節省資源'
                     when '3' then '紙本單筆收據'
                   end as de05_str,
                   case {$tbl_donate}.de09
                     when '1' then '信用卡(一次付清)'
                     when '2' then 'Line Pay'
                     when '3' then 'ATM轉帳'
                     when '4' then '超商繳費'
                     when '5' then '超商條碼繳費'
                     when '6' then 'GOOGLE PAY'
                     when '7' then '信用卡(定期定額)'
                     when '8' then 'WebATM'
                   end as de09_str,
                   case {$tbl_donate}.de21
                     when 'N' then '否'
                     when 'Y' then '是'
                   end as de21_str,
                   {$tbl_donate_item}.di01
                   {$this->_aes_fd()}
            from {$tbl_donate}
            left join {$tbl_donate_item} on {$tbl_donate_item}.s_num = {$tbl_donate}.di_s_num
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
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $tbl_donate_item = $this->zi_init->chk_tbl_no_lang('donate_item'); // 捐款項目
    $where = " {$tbl_donate}.d_date is null 
               and {$tbl_donate}.de19 = 1
             ";
    $order = " {$tbl_donate}.de17 desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_de01'])) { // 姓
      $que_de01 = $get_data['que_de01'];
      $que_de01 = $this->db->escape_like_str($que_de01);
      $where .= " and AES_DECRYPT({$tbl_donate}.de01,'{$this->db_crypt_key2}') like '%{$que_de01}%'  /* 姓 */ ";
    }
    if(!empty($get_data['que_de02'])) { // 名字
      $que_de02 = $get_data['que_de02'];
      $que_de02 = $this->db->escape_like_str($que_de02);
      $where .= " and AES_DECRYPT({$tbl_donate}.de02,'{$this->db_crypt_key2}') like '%{$que_de02}%'  /* 名字 */ ";
    }
    if(!empty($get_data['que_de03'])) { // Email
      $que_de03 = $get_data['que_de03'];
      $que_de03 = $this->db->escape_like_str($que_de03);
      $where .= " and AES_DECRYPT({$tbl_donate}.de03_email,'{$this->db_crypt_key2}') like '%{$que_de03}%'  /* Email */ ";
    }
    if(!empty($get_data['que_de10'])) { // 收據抬頭
      $que_de10 = $get_data['que_de10'];
      $que_de10 = $this->db->escape_like_str($que_de10);
      $where .= " and AES_DECRYPT({$tbl_donate}.de10,'{$this->db_crypt_key2}') like '%{$que_de10}%'  /* 收據抬頭 */ ";
    }
    if(!empty($get_data['que_de03_phone'])) { // 電話
      $que_de03_phone = $get_data['que_de03_phone'];
      $que_de03_phone = $this->db->escape_like_str($que_de03_phone);
      $where .= " and AES_DECRYPT({$tbl_donate}.de03_phone,'{$this->db_crypt_key2}') like '%{$que_de03_phone}%'  /* 電話 */ ";
    }
    if((!empty($get_data['start'])) or (!empty($get_data['end']))) { // 捐款日期
      $que_start = $get_data['start'];
      $que_end = $get_data['end'];
      $que_start = $this->db->escape_like_str($que_start);
      $que_end = $this->db->escape_like_str($que_end);
      $where .= " and date({$tbl_donate}.de17) between '{$que_start}' and '{$que_end}' /* 捐款日期 */ ";
    }
    if(!empty($get_data['que_addr'])) { // 地址
      $que_addr = $get_data['que_addr'];
      $que_addr = $this->db->escape_like_str($que_addr);
      $where .= " and CONCAT({$tbl_donate}.de04_zipcode,{$tbl_donate}.de04_county, {$tbl_donate}.de04_district,AES_DECRYPT({$tbl_donate}.de04_addr,'{$this->db_crypt_key2}')) like '%{$que_addr}%' /* 地址 */ ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_donate}.s_num
                from {$tbl_donate}
                left join {$tbl_donate_item} on {$tbl_donate_item}.s_num = {$tbl_donate}.di_s_num
                where $where
                group by {$tbl_donate}.s_num
                order by {$tbl_donate}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*20);
    $limit_e = 20;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_donate}.*,
                   case {$tbl_donate}.de09
                     when '1' then '信用卡(一次付清)'
                     when '2' then 'Line Pay'
                     when '3' then 'ATM轉帳'
                     when '4' then '超商繳費'
                     when '5' then '超商條碼繳費'
                     when '6' then 'GOOGLE PAY'
                     when '7' then '信用卡(定期定額)'
                     when '8' then 'WebATM'
                   end as de09_str,
                   case {$tbl_donate}.de21
                     when 'N' then '否'
                     when 'Y' then '是'
                   end as de21_str,
                   {$tbl_donate_item}.di01
                   {$this->_aes_fd()}
            from {$tbl_donate}
            left join {$tbl_donate_item} on {$tbl_donate_item}.s_num = {$tbl_donate}.di_s_num
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
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function save_add() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $rtn_msg = 'ok';
    $data = $this->input->post();
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
    
    if(!$this->db->insert($tbl_donate, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_front_add()
  //  函數功能: 新增儲存資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function save_front_add() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $rtn_msg = 'ok';
    $get_data = $this->input->post();
    // u_var_dump($get_data);
    // exit;
    $data['b_empno'] = 10000052;
    $data['b_date'] = date('Y-m-d H:i:s');
    $data['di_s_num'] = $get_data['di_s_num'];
    $data['de01'] = $get_data['de01'];
    $data['de02'] = $get_data['de02'];
    $data['de03_phone'] = $get_data['de03_phone'];
    $data['de03_email'] = $get_data['de03_email'];
    $data['de04_zipcode'] = $get_data['de04_zipcode'];
    $data['de04_county'] = $get_data['de04_county'];
    $data['de04_district'] = $get_data['de04_district'];
    $data['de04_addr'] = $get_data['de04_addr'];
    $data['de05'] = $get_data['de05'];
    $data['de06'] = $get_data['de06'];
    if($get_data['donate_type'] == 'period') { // 定期定額
      $data['de07'] = $get_data['de07'];
      $data['de08'] = $get_data['de08'];
    }
    $data['de09'] = $get_data['de09'];
    $data['de10'] = $get_data['de10'];
    $data['de12'] = $get_data['de12'];
    if(!empty($get_data['de21'])){
      $data['de21'] = $get_data['de21'];
    }
    $data['de22'] = $get_data['de22'];
    if(!empty($get_data['de30'])){
      $data['de30'] = $get_data['de30'];
    }
    
    // 加密欄位處理 Begin //
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    } 
    // 加密欄位處理 End //
    
    if(!$this->db->insert($tbl_donate, $data)) {
      echo $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    $s_num = $this->db->insert_id();
    // u_var_dump($data);
    // exit;
    //echo $rtn_msg;
    return $s_num;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function save_upd() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $rtn_msg = 'ok';
    $data = $this->input->post();
    // 加密欄位處理 Begin //
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    } 
    // 加密欄位處理 End //
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_donate, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_front_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: shirley
  //  設計日期: 2021-12-2
  // **************************************************************************
  public function save_front_upd($return_trade_info,$s_num) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $data['e_empno'] = 10000052;
    $data['e_date'] = date('Y-m-d H:i:s');
    $data['de15'] = $return_trade_info['Status'];
    $data['de16'] = $return_trade_info['Message'];
    $data['de18'] = NULL;
    if(!empty($return_trade_info["Result"]['TradeNo'])){
      $data['de18'] = $return_trade_info["Result"]['TradeNo'];
    }
    if(isset($return_trade_info["Result"]['PayTime'])) { // 一般交易
      $data['de17'] = $return_trade_info["Result"]['PayTime'];
    }
    else { // 
      $data['de17'] = NULL;
      if(!empty($return_trade_info["Result"]['AuthTime'])){
        $timestamp = strtotime($return_trade_info["Result"]['AuthTime']); 
        $auth_time = date("Y-m-d H:i:s", $timestamp);
        $data['de17'] = $auth_time;
      }
    }
    if('SUCCESS' == $return_trade_info['Status']) {
      $data['de19'] = 1; // 成功
    }
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_donate, $data)) {
      echo $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    //echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function save_is_available() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_donate, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_res_data()
  //  函數功能: 儲存查詢完藍新交易紀錄的結果
  //  程式設計: Kiwi
  //  設計日期: 2022-01-25
  // **************************************************************************
  public function save_res_data($res_data) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $rtn_msg = "ok";
    if(!$this->db->update_batch($tbl_donate, $res_data, "s_num")) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function del() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_donate, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: shirley
  //  設計日期: 2021-11-24
  // **************************************************************************
  private function _aes_fd() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_donate}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
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
  //  函數名稱: get_donate_total()
  //  函數功能: 取得已捐款總金額
  //  程式設計: shirley
  //  設計日期: 2021-11-30
  // **************************************************************************
  public function get_donate_total() {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $row = NULL;
    $sql = "select SUM({$tbl_donate}.de06) as total
            from {$tbl_donate}
            where {$tbl_donate}.d_date is null
                  and {$tbl_donate}.de19 = 1
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: que_by_de19()
  //  函數功能: 取得要處理的捐款資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-25
  // **************************************************************************
  public function que_by_de19($de19) {
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $data = NULL;
    $sql = "select {$tbl_donate}.*
            from {$tbl_donate}
            where {$tbl_donate}.d_date is null
                  and {$tbl_donate}.de19 = {$de19}
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
    return $data;
  }
  // **************************************************************************
  //  函數名稱: save_import()
  //  函數功能: 儲存匯入的csv檔捐款資料
  //  程式設計: shirley
  //  設計日期: 2022-02-09
  // **************************************************************************
  public function save_import() {
    $time_start = date('Y-m-d H:i:s');
    $tbl_donate = $this->zi_init->chk_tbl_no_lang('donate'); // 捐款資料
    $row_cnt=0; // 資料筆數
    $no_credit_card_upd_cnt=0; // 非信用卡更新筆數
    $credit_card_cnt=0; // 信用卡筆數
    $no_credit_card_cnt=0; // 非信用卡筆數
    $credit_card_unpaid_cnt=0; // 信用卡未付款更新筆數
    $regular_add_cnt=0; // 定期定額新增筆數
    $regular_unpaid_cnt=0; // 定期定額更新筆數
    $upd_no_cnt=0; // 未更新筆數
    $err_cnt=0; // 失敗筆數
    //$err_data= NULL; // 失敗內容
    $rtn_msg = '';
    $get_data = $this->input->post();
    $csv = FCPATH."upload_files/donate/{$get_data['csv_file']}"; // csv file path
    $file = fopen($csv, 'r'); //open file
    $i = 1;
    $today = date('Y-m-d H:i:s');
    while (($content = fgetcsv($file)) !== false) {
      //$content_utf8 = NULL;
      $csv_data = NULL;
      foreach ($content as $k => $v) {
        $fd_name = 'csv'.str_pad(($k+1),2,"0",STR_PAD_LEFT);
        //$content_utf8[] = mb_convert_encoding($v,"utf-8","big5");
        //$csv_data[$fd_name] = mb_convert_encoding($v,"utf-8","big5");
        $csv_data[$fd_name] = $v;
      }
      $csv_data = str_replace(array('"', "="), "", $csv_data);
      if($i != 1){
        $s_num = $csv_data['csv04'];
        $de23 = NULL;
        if(strpos($csv_data['csv04'], "_") !== false){ // 定期定額
          $de23 = substr($csv_data['csv04'],strripos($csv_data['csv04'],"_")+1); // 期數
          $s_num = substr($csv_data['csv04'],0,strripos($csv_data['csv04'],"_"));
          $data['e_date'] = date('Y-m-d H:i:s');
          $data['de17'] = $csv_data['csv02']; // 訂單交易日期
          $data['de18'] = $csv_data['csv03']; // 藍新金流交易序號
          $data['de20_status'] = $csv_data['csv20'];
          $data['de23'] = $csv_data['csv04']; // 已扣款期數
          $donate_row = $this->get_one_by_de23($csv_data['csv04']);
          if($donate_row !=NULL){
            $s_num = $donate_row->s_num;
          }
          if($csv_data['csv20'] == "已付款"){ // 檢查定期定額付款是否付款，如果是新增/更新為付款成功
            $data['de19'] = 1;
            if(($de23 == '01') or ($donate_row !=NULL)){
              $this->db->where('s_num',$s_num);
              if(!$this->db->update($tbl_donate, $data)) {
                $err_cnt++; // 修改失敗!!
              }else{
                $regular_unpaid_cnt++; // 定期定額更新筆數
              }
            }else{
              if(!$this->db->insert($tbl_donate, $data)) {
                $err_cnt++; // 新增失敗!!
              }else{
                $regular_add_cnt++; // 定期定額新增筆數
              }
            }
          }else if($csv_data['csv20'] == "未付款"){ // 檢查定期定額付款是否未付款，如果是新增/更新為等待入帳
            $data['de19'] = 3;
            if(($de23 == '01') or ($donate_row !=NULL)){
              $this->db->where('s_num',$s_num);
              if(!$this->db->update($tbl_donate, $data)) {
                $err_cnt++; // 修改失敗!!
              }else{
                $regular_unpaid_cnt++; // 定期定額更新筆數
              }
            }else{
              if(!$this->db->insert($tbl_donate, $data)) {
                $err_cnt++; // 新增失敗!!
              }else{
                $regular_add_cnt++; // 定期定額新增筆數
              }
            }
          }else if(($csv_data['csv20'] == "付款失敗") or ($csv_data['csv20'] == "付款取消")){ // 檢查定期定額付款是否付款失敗
            $data['de19'] = 2;
            if(($de23 == '01') or ($donate_row !=NULL)){
              $this->db->where('s_num',$s_num);
              if(!$this->db->update($tbl_donate, $data)) {
                $err_cnt++; // 修改失敗!!
              }else{
                $regular_unpaid_cnt++; // 定期定額更新筆數
              }
            }else{
              if(!$this->db->insert($tbl_donate, $data)) {
                $err_cnt++; // 新增失敗!!
              }else{
                $regular_add_cnt++; // 定期定額新增筆數
              }
            }
          }else{
            $upd_no_cnt++; // 未更新筆數
          }
          $credit_card_cnt++;
        }
        if("信用卡" != $csv_data['csv05']) { // 非信用卡
          $data['e_date'] = date('Y-m-d H:i:s');
          $data['de15'] = $csv_data['csv20']; // 訂單支付狀態
          $data['de16'] = $csv_data['csv20']; // 訂單支付狀態
          $data['de17'] = $csv_data['csv02']; // 訂單交易日期
          $data['de18'] = $csv_data['csv03']; // 藍新金流交易序號
          $data['de20_status'] = $csv_data['csv20'];
          if($csv_data['csv20'] == "已付款"){ // 檢查非信用卡是否已付款
            $data['de19'] = 1;
            //u_var_dump($data);
            $donate_row = $this->get_one($csv_data['csv04']);
            if($donate_row != NULL){
              $this->db->where('s_num',$csv_data['csv04']);
              if(!$this->db->update($tbl_donate, $data)) { // 修改失敗!!
                $err_cnt++;
              }else{
                $no_credit_card_upd_cnt++;
              }
            }else{
              $upd_no_cnt++; // 未更新筆數
            }
          }else{
            if(strtotime($today) > strtotime($csv_data['csv19'])){ // 如果未付款，且系統日期已大於交易截止日，則更新為付款失敗
              $data['de19'] = 2;
              $this->db->where('s_num',$csv_data['csv04']);
              if(!$this->db->update($tbl_donate, $data)) { // 修改失敗!!
                $err_cnt++;
              }else{
                $no_credit_card_upd_cnt++;
              }
              //u_var_dump($csv_data['csv03']);
            }else{
              $this->db->where('s_num',$csv_data['csv04']);
              if(!$this->db->update($tbl_donate, $data)) { // 修改失敗!!
                $err_cnt++;
              }else{
                $no_credit_card_upd_cnt++;
              }
            }
          }
          $no_credit_card_cnt++;
        }else if("信用卡" == $csv_data['csv05'] and ($de23 == NULL)){
          $data['e_date'] = date('Y-m-d H:i:s');
          $data['de20_status'] = $csv_data['csv20'];
          if($csv_data['csv20'] == "未付款"){ // 檢查信用卡付款是否未付款，如果是更新為等待入帳
            $data['de15'] = $csv_data['csv20']; // 訂單支付狀態
            $data['de16'] = $csv_data['csv20']; // 訂單支付狀態
            $data['de17'] = $csv_data['csv02']; // 訂單交易日期
            $data['de19'] = 3;
            $this->db->where('s_num',$s_num);
            if(!$this->db->update($tbl_donate, $data)) {
              $err_cnt++; // 修改失敗!!
            }else{
              $credit_card_unpaid_cnt++; // 信用卡更新筆數
            }
          }else if(($csv_data['csv20'] == "付款失敗") or ($csv_data['csv20'] == "付款取消")){ // 檢查信用卡付款是否付款失敗
            $data['de19'] = 2;
            $this->db->where('s_num',$s_num);
            if(!$this->db->update($tbl_donate, $data)) {
              $err_cnt++; // 修改失敗!!
            }else{
              $credit_card_unpaid_cnt++; // 信用卡更新筆數
            }
         
          }else{
            $upd_no_cnt++; // 未更新筆數
          }
          $credit_card_cnt++;
        }
        $row_cnt++;
      }
      $i++;
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
    $rtn_msg .= "    <td width='30%' align='right'>資料總筆數</td>";
    $rtn_msg .= "    <td width='70%' class='text-left'>{$row_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>未處理筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$upd_no_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>信用卡筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$credit_card_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>非信用卡筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$no_credit_card_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>定期定額新增筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$regular_add_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>定期定額更新筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$regular_unpaid_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>信用卡更新筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$credit_card_unpaid_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>非信用卡更新筆數</td>";
    $rtn_msg .= "    <td class='text-left'>{$no_credit_card_upd_cnt}</td>";
    $rtn_msg .= "  </tr>";
    $rtn_msg .= "  <tr>";
    $rtn_msg .= "    <td align='right'>更新失敗總筆數</td>";
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