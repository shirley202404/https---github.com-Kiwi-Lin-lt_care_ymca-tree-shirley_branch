<?php
class Meal_service_fee_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_meal_service_fee}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_meal_service_fee}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_meal_service_fee}.msf01
                     when '1' then '低收(免自付)'
                     when '2' then '中低收/專案(自付8元)'
                   end as msf01_str
                   
            from {$tbl_meal_service_fee}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_meal_service_fee}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_meal_service_fee}.e_empno
            where {$tbl_meal_service_fee}.d_date is null
                  and {$tbl_meal_service_fee}.s_num = ?
            order by {$tbl_meal_service_fee}.s_num desc
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
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_meal_service_fee}.*
                   
            from {$tbl_meal_service_fee}
            where {$tbl_meal_service_fee}.d_date is null
                  and {$tbl_meal_service_fee}.fd_name = ?
            order by {$tbl_meal_service_fee}.s_num desc
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
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function get_all() {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $data = NULL;
    $sql = "select {$tbl_meal_service_fee}.*
                   
            from {$tbl_meal_service_fee}
            where {$tbl_meal_service_fee}.d_date is null
            order by {$tbl_meal_service_fee}.s_num desc
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
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $data = NULL;
    $sql = "select {$tbl_meal_service_fee}.*
                   
            from {$tbl_meal_service_fee}
            where {$tbl_meal_service_fee}.d_date is null
                  and {$tbl_meal_service_fee}.is_available = 1 /* 啟用 */
            order by {$tbl_meal_service_fee}.s_num desc
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
  //  函數名稱: get_by_year()
  //  函數功能: 取得年度資料
  //  程式設計: kiwi
  //  設計日期: 2023-05-24
  // **************************************************************************
  public function get_by_year($year, $msf01) {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定資料
    $row = NULL;
    $sql = "select {$tbl_meal_service_fee}.*,
                   case {$tbl_meal_service_fee}.msf01
                     when '1' then '專案/中低收'
                     when '2' then '低收'
                   end as msf01_str
            from {$tbl_meal_service_fee}
            where {$tbl_meal_service_fee}.d_date is null
                  and {$tbl_meal_service_fee}.msf01 = ?
                  and {$tbl_meal_service_fee}.msf02 = ?
            order by {$tbl_meal_service_fee}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($msf01, $year));
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
  //  程式設計: kiwi
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $where = " {$tbl_meal_service_fee}.d_date is null ";
    $order = " {$tbl_meal_service_fee}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_meal_service_fee}.msf01 like '%{$que_str}%' /* 類型-OPT(1=低收(免自付))；2=中低收/專案(自付8元))) */                       
                        or {$tbl_meal_service_fee}.msf02 like '%{$que_str}%' /* 年度-MEMO(西元-YYYY) */                       
                        or {$tbl_meal_service_fee}.msf11_meal like '%{$que_str}%' /* 一餐費用 */                       
                        or {$tbl_meal_service_fee}.msf11_mp like '%{$que_str}%' /* 一餐代餐費用 */                       
                        or {$tbl_meal_service_fee}.msf12_meal like '%{$que_str}%' /* 午晚分費用 */                       
                        or {$tbl_meal_service_fee}.msf12_mp like '%{$que_str}%' /* 午晚分代餐費用 */                       
                        or {$tbl_meal_service_fee}.msf13_meal like '%{$que_str}%' /* 午晚併費用 */                       
                        or {$tbl_meal_service_fee}.msf13_mp like '%{$que_str}%' /* 午晚併代餐費用 */
                      )
                ";
    }

    if(!empty($get_data['que_msf01'])) { // 類型-OPT(1=低收(免自付))；2=中低收/專案(自付8元)))
      $que_msf01 = $get_data['que_msf01'];
      $que_msf01 = $this->db->escape_like_str($que_msf01);
      $where .= " and {$tbl_meal_service_fee}.msf01 = '{$que_msf01}'  /* 類型-OPT(1=低收(免自付))；2=中低收/專案(自付8元))) */ ";
    }
    if(!empty($get_data['que_msf02'])) { // 年度-MEMO(西元-YYYY)
      $que_msf02 = $get_data['que_msf02'];
      $que_msf02 = $this->db->escape_like_str($que_msf02);
      $where .= " and {$tbl_meal_service_fee}.msf02 = '{$que_msf02}'  /* 年度-MEMO(西元-YYYY) */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_meal_service_fee}.s_num
                from {$tbl_meal_service_fee}
                where $where
                group by {$tbl_meal_service_fee}.s_num
                order by {$tbl_meal_service_fee}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_meal_service_fee}.*,
                   case {$tbl_meal_service_fee}.msf01
                     when '1' then '低收(免自付)'
                     when '2' then '中低收/專案(自付8元)'
                   end as msf01_str
                   
            from {$tbl_meal_service_fee}
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
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function save_add() {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
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
    
    if(!$this->db->insert($tbl_meal_service_fee, $post_data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function save_upd() {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
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
    if(!$this->db->update($tbl_meal_service_fee, $post_data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function save_is_available() {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_meal_service_fee, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2023-06-24
  // **************************************************************************
  public function del() {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_meal_service_fee, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2023-06-24
  // **************************************************************************
  private function _aes_fd() {
    $tbl_meal_service_fee = $this->zi_init->chk_tbl_no_lang('meal_service_fee'); // 餐食服務費補助設定
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_meal_service_fee}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2023-06-24
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