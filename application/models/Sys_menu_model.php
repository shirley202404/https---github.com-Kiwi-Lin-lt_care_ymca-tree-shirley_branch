<?php
class Sys_menu_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: Tony
  //  設計日期: 2019-05-22
  // **************************************************************************
  public function get_one($s_num) {
    $crypt_key = DB_CRYPT_KEY; // DB加密key
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 選單管理
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_sys_menu}.*,
                   AES_DECRYPT({$tbl_sys_menu}.sys_menu_ct,'{$crypt_key}') as sys_menu_ct,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name
            from {$tbl_sys_menu}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_sys_menu}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_sys_menu}.e_empno
            where {$tbl_sys_menu}.d_date is null
                  and {$tbl_sys_menu}.s_num = ?
            order by {$tbl_sys_menu}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得sys_menu所有資料()
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_all($ct_menu=NULL) {
    $crypt_key = DB_CRYPT_KEY; // DB加密key
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 檢查多語系的table
    $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 帳號群駔權限
    $data = NULL;
    if(NULL==$ct_menu) {
      $sql = "select *,
                     AES_DECRYPT({$tbl_sys_menu}.sys_menu_ct,'{$crypt_key}') as sys_menu_ct
              from {$tbl_sys_menu}
              where d_date is null
              order by sys_menu_level,sys_menu_order
             ";

    }
    else { // 依據權限顯示menu
      $group_s_num = $this->session->userdata('group_s_num');
      $sql = "select {$tbl_sys_menu}.*,
                     AES_DECRYPT({$tbl_sys_menu}.sys_menu_ct,'{$crypt_key}') as sys_menu_ct
              from {$tbl_sys_menu}
              left join {$tbl_account_group_auth} on {$tbl_account_group_auth}.menu_s_num = {$tbl_sys_menu}.s_num
              where {$tbl_account_group_auth}.group_s_num = {$group_s_num}
                    and {$tbl_account_group_auth}.d_date is null
                    and {$tbl_sys_menu}.is_available = 1
                    and {$tbl_sys_menu}.d_date is null
              order by {$tbl_sys_menu}.sys_menu_level,{$tbl_sys_menu}.sys_menu_order
             ";
    }
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
  //  函數名稱: get_one_by_ct_name()
  //  函數功能: 取得多語系的選單名稱
  //  程式設計: kiwi
  //  設計日期: 2022-08-17
  // **************************************************************************
  public function get_one_by_ct_name($ct_name) {
    $crypt_key = DB_CRYPT_KEY; // DB加密key
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 選單管理
    $row = NULL;
    $sql = "select {$tbl_sys_menu}.sys_menu_level,
                   {$tbl_sys_menu}.sys_menu_name
            from {$tbl_sys_menu}
            where {$tbl_sys_menu}.d_date is null
                  and {$tbl_sys_menu}.is_available = 1 /* 啟用 */
                  and AES_DECRYPT({$tbl_sys_menu}.sys_menu_ct,'{$crypt_key}') = '{$ct_name}'
            order by {$tbl_sys_menu}.s_num asc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_one_menu_name()
  //  函數功能: 取得多語系的選單名稱
  //  程式設計: kiwi
  //  設計日期: 2022-08-17
  // **************************************************************************
  public function get_one_menu_name($sys_menu_s_num) {
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 選單管理
    $data = NULL;
    $sql = "select {$tbl_sys_menu}.*
            from {$tbl_sys_menu}
            where {$tbl_sys_menu}.d_date is null
                  and {$tbl_sys_menu}.is_available = 1 /* 啟用 */
                  and {$tbl_sys_menu}.s_num = ?
            order by {$tbl_sys_menu}.s_num asc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sys_menu_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: Tony
  //  設計日期: 2019-05-22
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $crypt_key = DB_CRYPT_KEY; // DB加密key
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 系統選單
    $where = "{$tbl_sys_menu}.d_date is null";
    $where = " {$tbl_sys_menu}.d_date is null ";
    $order = " case 
                 when {$tbl_sys_menu}.sys_menu_level = 0 then if({$tbl_sys_menu}.is_available = 0, {$tbl_sys_menu}.sys_menu_order * 10000, {$tbl_sys_menu}.sys_menu_order * 100)
                 when {$tbl_sys_menu}.sys_menu_level != 0 then if(parent.is_available = 0, parent.sys_menu_order * 10000, parent.sys_menu_order * 100)
               end 
               + if({$tbl_sys_menu}.sys_menu_level != 0, if({$tbl_sys_menu}.is_available = 0, {$tbl_sys_menu}.sys_menu_order * 10, {$tbl_sys_menu}.sys_menu_order), 0)
             ";
    if(''<>$_SESSION[$q_str]['que_str']) { // 全文檢索
      $que_str = $_SESSION[$q_str]['que_str'];
      $where .= " and ({$tbl_sys_menu}.sys_menu_name like '%{$que_str}%'
                       or AES_DECRYPT({$tbl_sys_menu}.sys_menu_ct,'{$crypt_key}') like '%{$que_str}%'
                      )
                ";
    }
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$tbl_sys_menu}.{$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算總筆數
    $sql_cnt = "select count(*) as cnt
                from {$tbl_sys_menu}
                left join {$tbl_sys_menu} parent on parent.s_num = {$tbl_sys_menu}.sys_menu_level
                where $where
               ";
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->row(); 

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_sys_menu}.*,
                   AES_DECRYPT({$tbl_sys_menu}.sys_menu_ct,'{$crypt_key}') as sys_menu_ct
            from {$tbl_sys_menu}
            left join {$tbl_sys_menu} parent on parent.s_num = {$tbl_sys_menu}.sys_menu_level
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
    return(array($data,$row_cnt->cnt));
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function save_add() {
    $crypt_key = DB_CRYPT_KEY; // DB加密key
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 系統選單
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $sql_add = "insert into {$tbl_sys_menu}
                set sys_menu_icon = '{$data['sys_menu_icon']}',
                    sys_menu_name = '{$data['sys_menu_name']}',
                    sys_menu_ct = AES_ENCRYPT('{$data['sys_menu_ct']}','{$crypt_key}'),
                    sys_menu_level = {$data['sys_menu_level']},
                    sys_menu_order = {$data['sys_menu_order']},
                    is_available = {$data['is_available']},
                    b_empno = {$data['b_empno']},
                    b_date = '{$data['b_date']}'
               ";
    //u_var_dump($sql_add);
    //exit;
    if(!$this->db->query($sql_add)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function save_upd() {
    $crypt_key = DB_CRYPT_KEY; // DB加密key
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 系統選單
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $sql_upd = "update {$tbl_sys_menu}
                set sys_menu_icon = '{$data['sys_menu_icon']}',
                    sys_menu_name = '{$data['sys_menu_name']}',
                    sys_menu_ct = AES_ENCRYPT('{$data['sys_menu_ct']}','{$crypt_key}'),
                    sys_menu_level = {$data['sys_menu_level']},
                    sys_menu_order = {$data['sys_menu_order']},
                    is_available = {$data['is_available']},
                    e_empno = {$data['e_empno']},
                    e_date = '{$data['e_date']}'
                where {$tbl_sys_menu}.s_num = {$data['s_num']}
               ";
    //u_var_dump($sql_upd);
    //exit;
    if(!$this->db->query($sql_upd)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd_crypt_key()
  //  函數功能: 更新sys_menu_ct欄位加密key
  //  程式設計: Tony
  //  設計日期: 2018/10/2
  // **************************************************************************
  public function save_upd_crypt_key($db_crypt_key) {
    $ocrypt_key = DB_CRYPT_KEY; // DB加密key-old
    $crypt_key = $db_crypt_key; // DB加密key-new
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 系統選單
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = 0;
    $data['e_date'] = date('Y-m-d H:i:s');
    $sql_upd = "update {$tbl_sys_menu}
                set sys_menu_ct = AES_ENCRYPT(AES_DECRYPT(sys_menu_ct,'{$ocrypt_key}'),'{$crypt_key}'),
                    e_empno = {$data['e_empno']},
                    e_date = '{$data['e_date']}'
                where 1=1
               ";
    //u_var_dump($sql_upd);
    //exit;
    if(!$this->db->query($sql_upd)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: Tony
  //  設計日期: 2018/5/11
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];

    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 系統選單
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_sys_menu, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: Tony
  //  設計日期: 2019-05-22
  // **************************************************************************
  public function del() {
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 選單管理
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_sys_menu, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
}
?>