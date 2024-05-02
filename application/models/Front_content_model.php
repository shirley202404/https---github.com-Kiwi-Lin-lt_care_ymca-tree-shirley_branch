<?php
class Front_content_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_front_content}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_front_content}.fc01_item
                     when '1' then '捐款階段文字'
                     when '2' then '常見問題'
                     when '99' then '衛服部字號'
                   end as fc01_item_str,
                   case {$tbl_front_content}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   
            from {$tbl_front_content}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_front_content}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_front_content}.e_empno
            where {$tbl_front_content}.d_date is null
                  and {$tbl_front_content}.s_num = ?
            order by {$tbl_front_content}.s_num desc
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
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_front_content}.*
                   
            from {$tbl_front_content}
            where {$tbl_front_content}.d_date is null
                  and {$tbl_front_content}.fd_name = ?
            order by {$tbl_front_content}.s_num desc
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
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function get_all() {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $data = NULL;
    $sql = "select {$tbl_front_content}.*
                   
            from {$tbl_front_content}
            where {$tbl_front_content}.d_date is null
            order by {$tbl_front_content}.s_num desc
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
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $data = NULL;
    $sql = "select {$tbl_front_content}.*
                   
            from {$tbl_front_content}
            where {$tbl_front_content}.d_date is null
                  and {$tbl_front_content}.is_available = 1 /* 啟用 */
            order by {$tbl_front_content}.s_num desc
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
  //  函數名稱: get_by_obj_fc01_item()
  //  函數功能: 用類別取得資料(物件)
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function get_by_obj_fc01($fc01, $fc01_item) {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $row = NULL;
    $sql = "select {$tbl_front_content}.*
            from {$tbl_front_content}
            where {$tbl_front_content}.d_date is null
                  and {$tbl_front_content}.is_available = 1 /* 啟用 */
                  and {$tbl_front_content}.fc01 = {$fc01}
                  and {$tbl_front_content}.fc01_item = {$fc01_item}
            order by {$tbl_front_content}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
  //  函數名稱: get_by_arr_fc01_item()
  //  函數功能: 用類別取得資料(陣列)
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function get_by_arr_fc01($fc01, $fc01_item) {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $data = NULL;
    $sql = "select {$tbl_front_content}.*
            from {$tbl_front_content}
            where {$tbl_front_content}.d_date is null
                  and {$tbl_front_content}.is_available = 1 /* 啟用 */
                  and {$tbl_front_content}.fc01 = {$fc01}
                  and {$tbl_front_content}.fc01_item = {$fc01_item}
            order by {$tbl_front_content}.fc03 asc
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
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $where = " {$tbl_front_content}.d_date is null ";
    $order = " {$tbl_front_content}.fc03 asc, {$tbl_front_content}.s_num desc ";
    $get_data = $this->input->get();

    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_front_content}.fc01_item like '%{$que_str}%' /* 1=捐款階段文字，2=常見問題，99=衛服部字號 */                       
                       or {$tbl_front_content}.fc02 like '%{$que_str}%' /* 標題 */                       
                       or {$tbl_front_content}.fc03 like '%{$que_str}%' /* 內容 */
                       or {$tbl_front_content}.fc04 like '%{$que_str}%' /* 內容 */
                      )
                ";
    }

    if(!isset($get_data['que_fc01'])) { // 區塊
      $get_data['que_fc01'] = '30'; // 區塊
    }

    
    if(!empty($get_data['que_fc01'])) { 
      $que_fc01 = $get_data['que_fc01'];
      $que_fc01 = $this->db->escape_like_str($que_fc01);
      $where .= " and {$tbl_front_content}.fc01 = {$que_fc01}";
    }
    
    if(!empty($get_data['que_fc01_item'])) { // 1=捐款階段文字，2=常見問題，99=衛服部字號
      $que_fc01_item = $get_data['que_fc01_item'];
      $que_fc01_item = $this->db->escape_like_str($que_fc01_item);
      $where .= " and {$tbl_front_content}.fc01_item = '{$que_fc01_item}'  /* 1=捐款階段文字，2=常見問題，99=衛服部字號 */ ";
    }
    if(!empty($get_data['que_fc02'])) { // 標題
      $que_fc02 = $get_data['que_fc02'];
      $que_fc02 = $this->db->escape_like_str($que_fc02);
      $where .= " and {$tbl_front_content}.fc02 like '%{$que_fc02}%'  /* 標題 */ ";
    }
    if(!empty($get_data['que_fc03'])) { // 內容
      $que_fc03 = $get_data['que_fc03'];
      $que_fc03 = $this->db->escape_like_str($que_fc03);
      $where .= " and {$tbl_front_content}.fc03 like '%{$que_fc03}%'  /* 內容 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_front_content}.s_num
                from {$tbl_front_content}
                where $where
                group by {$tbl_front_content}.s_num
                order by {$tbl_front_content}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_front_content}.*
            from {$tbl_front_content}
            where {$where}
            order by {$order}
            {$limit}
           ";
           
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
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function save_add() {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
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
    
    if(!$this->db->insert($tbl_front_content, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function save_upd() {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
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
    if(!$this->db->update($tbl_front_content, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function save_is_available() {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_front_content, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  public function del() {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_front_content, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
  // **************************************************************************
  private function _aes_fd() {
    $tbl_front_content = $this->zi_init->chk_tbl_no_lang('front_content'); // 捐款前台內容
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_front_content}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-29
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
}
?>