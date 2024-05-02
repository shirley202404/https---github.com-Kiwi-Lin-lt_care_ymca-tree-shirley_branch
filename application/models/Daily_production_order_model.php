<?php
class Daily_production_order_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_daily_production_order}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   {$tbl_meal}.ml01,
                   case {$tbl_daily_production_order}.dpo09
                     when 'N' then ''
                     when 'Y' then '合'
                   end as dpo09_str,
                   case {$tbl_daily_production_order}.dpo10
                     when '1' then '早上'
                     when '2' then '下午'
                   end as dpo10_str,
                   case {$tbl_daily_production_order}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
            from {$tbl_daily_production_order}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_daily_production_order}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_daily_production_order}.e_empno
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_daily_production_order}.ml_s_num
            where {$tbl_daily_production_order}.d_date is null
                  and {$tbl_daily_production_order}.s_num = ?
            order by {$tbl_daily_production_order}.s_num desc
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
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_daily_production_order}.*
                   
            from {$tbl_daily_production_order}
            where {$tbl_daily_production_order}.d_date is null
                  and {$tbl_daily_production_order}.fd_name = ?
            order by {$tbl_daily_production_order}.s_num desc
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
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function get_all() {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $data = NULL;
    $sql = "select {$tbl_daily_production_order}.*
                   
            from {$tbl_daily_production_order}
            where {$tbl_daily_production_order}.d_date is null
            order by {$tbl_daily_production_order}.s_num desc
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
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $data = NULL;
    $sql = "select {$tbl_daily_production_order}.*
                   
            from {$tbl_daily_production_order}
            where {$tbl_daily_production_order}.d_date is null
                  and {$tbl_daily_production_order}.is_available = 1 /* 啟用 */
            order by {$tbl_daily_production_order}.dpo01 desc
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
  //  函數名稱: get_all_by_dpo10()
  //  函數功能: 取得所有已經啟用的資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function get_all_by_dpo10() {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $type = NULL;
    $data = NULL;
    $v = $this->input->post();
    $get_data = $this->input->get();
    if(isset($v['type'])) {
      $type = $v['type'];
    }
    if(isset($get_data['type'])) {
      $type = $get_data['type'];
    }
    $sql = "select {$tbl_daily_production_order}.*,
                   {$tbl_meal}.ml01,
                   case {$tbl_daily_production_order}.dpo09
                     when 'N' then ''
                     when 'Y' then '合'
                   end as dpo09_str,
                   case {$tbl_daily_production_order}.dpo10
                     when '1' then '早上'
                     when '2' then '下午'
                   end as dpo10_str
            from {$tbl_daily_production_order}
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_daily_production_order}.ml_s_num
            where {$tbl_daily_production_order}.d_date is null
                  and {$tbl_daily_production_order}.is_available = 1 /* 啟用 */
                  and {$tbl_daily_production_order}.dpo10 = {$type} /* 送餐時間 */
            order by {$tbl_daily_production_order}.dpo01 asc
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
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {            
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $where = " {$tbl_daily_production_order}.d_date is null ";
    $order = " {$tbl_daily_production_order}.dpo01 asc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_daily_production_order}.ml_s_num like '%{$que_str}%' /* (tw_meal.s_num) */                       
                       or {$tbl_daily_production_order}.dpo01 like '%{$que_str}%' /* 順序 */                       
                       or {$tbl_daily_production_order}.dpo02 like '%{$que_str}%' /* 餐點名稱 */                       
                       or {$tbl_daily_production_order}.dpo03 like '%{$que_str}%' /* 餐別 */                       
                       or {$tbl_daily_production_order}.dpo04 like '%{$que_str}%' /* 特殊內容 */                       
                       or {$tbl_daily_production_order}.dpo05 like '%{$que_str}%' /* 硬度份量 */                       
                       or {$tbl_daily_production_order}.dpo06 like '%{$que_str}%' /* 餐食禁忌 */                       
                       or {$tbl_daily_production_order}.dpo07 like '%{$que_str}%' /* 主食禁忌 */                       
                       or {$tbl_daily_production_order}.dpo08 like '%{$que_str}%' /* 治療餐 */                       
                       or {$tbl_daily_production_order}.dpo09 like '%{$que_str}%' /* 是否為自費戶 */
                      )
                ";
    }


    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_daily_production_order}.s_num
                from {$tbl_daily_production_order} 
                left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_daily_production_order}.ml_s_num
                left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_daily_production_order}.reh_s_num
                where $where
                group by {$tbl_daily_production_order}.s_num
                order by {$tbl_daily_production_order}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_daily_production_order}.*,
                   {$tbl_meal}.ml01,
                   case {$tbl_daily_production_order}.dpo09
                     when 'N' then ''
                     when 'Y' then '合'
                   end as dpo09_str,
                   case {$tbl_daily_production_order}.dpo10
                     when '1' then '早上'
                     when '2' then '下午'
                   end as dpo10_str,
                   {$tbl_route_h}.reh01
            from {$tbl_daily_production_order}
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_daily_production_order}.ml_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_daily_production_order}.reh_s_num
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
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function save_add() {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
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
    
    if(!$this->db->insert($tbl_daily_production_order, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function save_upd() {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
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
    if(!$this->db->update($tbl_daily_production_order, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function save_is_available() {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_daily_production_order, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
  // **************************************************************************
  public function del() {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_daily_production_order, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
  // **************************************************************************
  private function _aes_fd() {
    $tbl_daily_production_order = $this->zi_init->chk_tbl_no_lang('daily_production_order'); // 餐條排序設定檔
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_daily_production_order}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-25
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