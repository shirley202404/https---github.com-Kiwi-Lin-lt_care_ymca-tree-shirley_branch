<?php
class Work_time_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_work_time}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_work_time}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   
                   
            from {$tbl_work_time}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_work_time}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_work_time}.e_empno
            where {$tbl_work_time}.d_date is null
                  and {$tbl_work_time}.s_num = ?
            order by {$tbl_work_time}.s_num desc
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
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_work_time}.*
                   
            from {$tbl_work_time}
            where {$tbl_work_time}.d_date is null
                  and {$tbl_work_time}.fd_name = ?
            order by {$tbl_work_time}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($fd_name));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      // 遮罩欄位處理 Begin //
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      }
      // 遮罩欄位處理 End //
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function get_all() {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $data = NULL;
    $sql = "select {$tbl_work_time}.*
                   
            from {$tbl_work_time}
            where {$tbl_work_time}.d_date is null
            order by {$tbl_work_time}.s_num desc
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
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $data = NULL;
    $sql = "select {$tbl_work_time}.*
                   
            from {$tbl_work_time}
            where {$tbl_work_time}.d_date is null
                  and {$tbl_work_time}.is_available = 1 /* 啟用 */
            order by {$tbl_work_time}.s_num desc
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
  //  函數名稱: get_by_time()
  //  函數功能: 取得所有已經啟用的資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function get_by_time() {
    $time = date("H:i:s");

    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $row = NULL;
    $sql = "select {$tbl_work_time}.*
                   
            from {$tbl_work_time}
            where {$tbl_work_time}.d_date is null
                  and {$tbl_work_time}.is_available = 1 /* 啟用 */
                  and '{$time}' between {$tbl_work_time}.wt02_begin and {$tbl_work_time}.wt02_end
            order by {$tbl_work_time}.s_num desc
            limit 1
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      // 遮罩欄位處理 Begin //
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      }
      // 遮罩欄位處理 End //
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $where = " {$tbl_work_time}.d_date is null ";
    $order = " {$tbl_work_time}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_work_time}.wt01 like '%{$que_str}%' /* 班別名稱 */                       
                        or BINARY {$tbl_work_time}.wt02_begin like BINARY '%{$que_str}%' /* 班別開始時間 */                       
                        or BINARY {$tbl_work_time}.wt02_end like BINARY '%{$que_str}%' /* 班別結束時間 */                       
                        or {$tbl_work_time}.wt03 like '%{$que_str}%' /* 班別對應餐別 */
                      )
                ";
    }

    if(!empty($get_data['que_wt01'])) { // 班別名稱
      $que_wt01 = $get_data['que_wt01'];
      $que_wt01 = $this->db->escape_like_str($que_wt01);
      $where .= " and {$tbl_work_time}.wt01 = '{$que_wt01}'  /* 班別名稱 */ ";
    }
    if(!empty($get_data['que_wt02_begin'])) { // 班別開始時間
      $que_wt02_begin = $get_data['que_wt02_begin'];
      $que_wt02_begin = $this->db->escape_like_str($que_wt02_begin);
      $where .= " and {$tbl_work_time}.wt02_begin like '%{$que_wt02_begin}%'  /* 班別開始時間 */ ";
    }
    if(!empty($get_data['que_wt02_end'])) { // 班別結束時間
      $que_wt02_end = $get_data['que_wt02_end'];
      $que_wt02_end = $this->db->escape_like_str($que_wt02_end);
      $where .= " and {$tbl_work_time}.wt02_end like '%{$que_wt02_end}%'  /* 班別結束時間 */ ";
    }
    if(!empty($get_data['que_wt03'])) { // 班別對應餐別
      $que_wt03 = $get_data['que_wt03'];
      $que_wt03 = $this->db->escape_like_str($que_wt03);
      $where .= " and {$tbl_work_time}.wt03 = '{$que_wt03}'  /* 班別對應餐別 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_work_time}.s_num
                from {$tbl_work_time}
                where $where
                group by {$tbl_work_time}.s_num
                order by {$tbl_work_time}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_work_time}.*
                   
                   
            from {$tbl_work_time}
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
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function save_add() {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $rtn_msg = 'ok';
    $post_data = $this->input->post();
    // 加密欄位處理 Begin //
    foreach ($post_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($post_data[$k_fd_name]);
      }
    }
    // 加密欄位處理 End //
    $post_data['b_empno'] = $_SESSION['acc_s_num'];
    $post_data['b_date'] = date('Y-m-d H:i:s');
    
    if(!$this->db->insert($tbl_work_time, $post_data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function save_upd() {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $rtn_msg = 'ok';
    $post_data = $this->input->post();
    // 加密欄位處理 Begin //
    foreach ($post_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($post_data[$k_fd_name]);
      }
    } 
    // 加密欄位處理 End //
    $post_data['e_empno'] = $_SESSION['acc_s_num'];
    $post_data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $post_data['s_num']);
    if(!$this->db->update($tbl_work_time, $post_data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function save_is_available() {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_work_time, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  public function del() {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_work_time, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
  // **************************************************************************
  private function _aes_fd() {
    $tbl_work_time = $this->zi_init->chk_tbl_no_lang('work_time'); // 班別時間
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_work_time}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2023-09-02
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