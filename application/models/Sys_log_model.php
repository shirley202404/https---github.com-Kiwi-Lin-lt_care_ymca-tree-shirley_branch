<?php
class Sys_log_model extends CI_Model {
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_sys_log}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_sys_log}.is_available
                     when '0' then '啟用'
                     when '1' then '停用'
                   end as is_available_str
            from {$tbl_sys_log}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_sys_log}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_sys_log}.e_empno
            where {$tbl_sys_log}.d_date is null
                  and {$tbl_sys_log}.s_num = ?
            order by {$tbl_sys_log}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_sys_log}.*
            from {$tbl_sys_log}
            where {$tbl_sys_log}.d_date is null
                  and {$tbl_sys_log}.fd_name = ?
            order by {$tbl_sys_log}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($fd_name));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
    }
    return $row;
  }

  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function get_all() {
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    $data = NULL;
    $sql = "select {$tbl_sys_log}.*
            from {$tbl_sys_log}
            where {$tbl_sys_log}.d_date is null
            order by {$tbl_sys_log}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    $where = " {$tbl_sys_log}.d_date is null ";
    $order = " {$tbl_sys_log}.s_num desc ";
    if(''<>$_SESSION[$q_str]['que_str']) { // 全文檢索
      $que_str = $_SESSION[$q_str]['que_str'];
      $where .= " and ({$tbl_sys_log}.log_proc_s_num like '%{$que_str}%' /* 程式的s_num */
                       or {$tbl_sys_log}.log_name like '%{$que_str}%' /* sys_account.acc_name */
                       or {$tbl_sys_log}.log_fromip like '%{$que_str}%' /* 來源IP */
                       or {$tbl_sys_log}.log_date like '%{$que_str}%' /* 建立時間 */
                       or {$tbl_sys_log}.log_type like '%{$que_str}%' /* 類別 */
                       or {$tbl_sys_log}.log_platform like '%{$que_str}%' /* 作業系統 */
                       or {$tbl_sys_log}.log_browser like '%{$que_str}%' /* 瀏覽器名稱 */
                       or {$tbl_sys_log}.log_browser_ver like '%{$que_str}%' /* 瀏覽器版本 */
                       or {$tbl_sys_log}.log_agent like '%{$que_str}%' /* agent完整資訊 */
                       or {$tbl_sys_log}.log_current_url like '%{$que_str}%' /* 目前網址 */
                       or {$tbl_sys_log}.log_referrer_url like '%{$que_str}%' /* 從哪的網址來的 */
                       or {$tbl_sys_log}.log_controller like '%{$que_str}%' /* controller */
                       or {$tbl_sys_log}.log_method like '%{$que_str}%' /* method */
                       or {$tbl_sys_log}.log_uri_string like '%{$que_str}%' /* uri_string */
                       or {$tbl_sys_log}.log_segment_1 like '%{$que_str}%' /* segment_1 */
                       or {$tbl_sys_log}.log_segment_2 like '%{$que_str}%' /* segment_2 */
                       or {$tbl_sys_log}.log_segment_3 like '%{$que_str}%' /* segment_3 */
                       or {$tbl_sys_log}.log_segment_4 like '%{$que_str}%' /* segment_4 */
                       or {$tbl_sys_log}.log_segment_5 like '%{$que_str}%' /* segment_5 */
                       or {$tbl_sys_log}.log_segment_6 like '%{$que_str}%' /* segment_6 */
                       or {$tbl_sys_log}.log_last_query like '%{$que_str}%' /* SQL */
                       or {$tbl_sys_log}.log_othe_msg like '%{$que_str}%' /* 額外紀錄 */
                      )
                ";
    }
    $get_data = $this->input->get();
    if(!empty($get_data['que_log_name'])) { // sys_account.acc_name
      $que_log_name = $get_data['que_log_name'];
      $que_log_name = $this->db->escape_like_str($que_log_name);
      $where .= " and {$tbl_sys_log}.log_name  = '{$que_log_name}'  /* sys_account.acc_name */ ";
    }
    if(!empty($get_data['que_log_fromip'])) { // 來源IP
      $que_log_fromip = $get_data['que_log_fromip'];
      $que_log_fromip = $this->db->escape_like_str($que_log_fromip);
      $where .= " and {$tbl_sys_log}.log_fromip  = '{$que_log_fromip}'  /* 來源IP */ ";
    }
    if(!empty($get_data['que_log_date'])) { // 建立時間
      $que_log_date = $get_data['que_log_date'];
      $que_log_date = $this->db->escape_like_str($que_log_date);
      $where .= " and {$tbl_sys_log}.log_date  = '{$que_log_date}'  /* 建立時間 */ ";
    }
    if(!empty($get_data['que_log_type'])) { // 類別
      $que_log_type = $get_data['que_log_type'];
      $que_log_type = $this->db->escape_like_str($que_log_type);
      $where .= " and {$tbl_sys_log}.log_type  = '{$que_log_type}'  /* 類別 */ ";
    }
    if(!empty($get_data['que_log_platform'])) { // 作業系統
      $que_log_platform = $get_data['que_log_platform'];
      $que_log_platform = $this->db->escape_like_str($que_log_platform);
      $where .= " and {$tbl_sys_log}.log_platform  like '%{$que_log_platform}%'  /* 作業系統 */ ";
    }
    if(!empty($get_data['que_log_browser'])) { // 瀏覽器名稱
      $que_log_browser = $get_data['que_log_browser'];
      $que_log_browser = $this->db->escape_like_str($que_log_browser);
      $where .= " and {$tbl_sys_log}.log_browser  like '%{$que_log_browser}%'  /* 瀏覽器名稱 */ ";
    }
    if(!empty($get_data['que_log_current_url'])) { // 目前網址
      $que_log_current_url = $get_data['que_log_current_url'];
      $que_log_current_url = $this->db->escape_like_str($que_log_current_url);
      $where .= " and {$tbl_sys_log}.log_current_url  like '%{$que_log_current_url}%'  /* 目前網址 */ ";
    }
    if(!empty($get_data['que_log_referrer_url'])) { // 從哪的網址來的
      $que_log_referrer_url = $get_data['que_log_referrer_url'];
      $que_log_referrer_url = $this->db->escape_like_str($que_log_referrer_url);
      $where .= " and {$tbl_sys_log}.log_referrer_url  like '%{$que_log_referrer_url}%'  /* 從哪的網址來的 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    //if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
    //  $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
    //  $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
    //  $order = " {$que_order_fd_name} {$que_order_kind} ";
    //}

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_sys_log}.s_num
                from {$tbl_sys_log}
                where $where
                group by {$tbl_sys_log}.s_num
                order by {$tbl_sys_log}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_sys_log}.*
            from {$tbl_sys_log}
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
        }
      }
    }
    return(array($data,$row_cnt));
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    if(!$this->db->insert($tbl_sys_log, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_sys_log, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_sys_log, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: Tony
  //  設計日期: 2020-07-27
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_sys_log = $this->zi_init->chk_tbl_no_lang('sys_log'); // 系統log
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_sys_log, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
}
?>