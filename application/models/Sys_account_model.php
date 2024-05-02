<?php
class Sys_account_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }

  // **************************************************************************
  //  函數名稱: chk_login()
  //  函數功能: 檢查登入帳號密碼是否正確
  //  使用方式: chk_login()
  //  程式設計: Tony
  //  設計日期: 2017/11/14
  // **************************************************************************
  public function chk_login() {
    unset($_SESSION['acc_s_num']);
    unset($_SESSION['acc_user']);
    unset($_SESSION['acc_name']);
    unset($_SESSION['group_s_num']);
    unset($_SESSION['acc_depname']);
    unset($_SESSION['acg_name']);
    unset($_SESSION['acc_mvc_db']);
    unset($_SESSION['is_super']);
    unset($_SESSION['tbl_lang']);
    unset($_SESSION['acc_kind']);
    if(!isset($_SESSION['cap']['word'])) { // 沒有驗證碼
      return false;
    }
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組

    $vacc_user     = $_POST['acc_user'];       // 帳號
    $vacc_password = $_POST['acc_password'];  // 密碼
    $vlogincaptcha = $_POST['logincaptcha'];  // 驗證碼
    if(strtolower($vlogincaptcha)==strtolower($_SESSION['cap']['word'])) {
      $vrow = NULL;
      $vsql = "select {$tbl_account}.*,
                      {$tbl_account_group}.acg_name
               from {$tbl_account}
               left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_account}.group_s_num
               where {$tbl_account}.acc_user = ?
                     and {$tbl_account}.is_available = 1
                     and {$tbl_account}.d_date is null
               order by {$tbl_account}.acc_user
               limit 0,1
              ";
      //u_var_dump($vsql);
      //echo '<hr>';
      $vrs = $this->db->query($vsql, array($vacc_user));
      if($vrs->num_rows() > 0) { // 有資料才執行
        $vrow = $vrs->row_array();
        //var_dump($vrow);
      }
      if(password_verify($vacc_password, $vrow['acc_pwd'])) { // 密碼正確
        $_SESSION['acc_s_num']  = $vrow['s_num'];
        $_SESSION['acc_user']   = $vrow['acc_user'];
        $_SESSION['acc_name']   = $vrow['acc_name'];
        $_SESSION['group_s_num'] = $vrow['group_s_num'];
        $_SESSION['acc_depname'] = $vrow['acc_depname'];
        $_SESSION['acg_name'] = $vrow['acg_name'];
        $_SESSION['acc_mvc_db'] = $vrow['acc_mvc_db'];
        $_SESSION['is_super']   = $vrow['is_super'];
        $_SESSION['tbl_lang'] = 'tw'; // 目前預設tw,到時候再修正
        $_SESSION['acc_kind'] = 'M'; //
        $this->save_login_info();
        return true;
      }     
      else {
        return false;
      }
    }
    else {
      return false;
    }
  }
  // **************************************************************************
  //  函數名稱: chk_api_login()
  //  函數功能: 檢查登入帳號密碼是否正確
  //  使用方式: chk_api_login()
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function chk_api_login() {
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    
    $json_data = NULL;            
    $json_data['state'] = "false";

    if(!isset($_POST['acc_user']) or !isset($_POST['acc_password'])) {
      return $json_data; 
    }
    
    $vrow = NULL;
    $vacc_user     = $_POST['acc_user'];       // 帳號
    $vacc_password = $_POST['acc_password'];  // 密碼

    $vsql = "select {$tbl_account}.*,
                    {$tbl_account_group}.acg_name
             from {$tbl_account}
             left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_account}.group_s_num
             where {$tbl_account}.acc_user = ?
                   and {$tbl_account}.is_available = 1
                   and {$tbl_account}.d_date is null
             order by {$tbl_account}.acc_user
             limit 0,1
            ";
    //u_var_dump($vsql);
    //echo '<hr>';
    $vrs = $this->db->query($vsql, array($vacc_user));
    if($vrs->num_rows() > 0) { // 有資料才執行
      $vrow = $vrs->row_array();
      //var_dump($vrow);
    }
    
    if(password_verify($vacc_password, $vrow['acc_pwd'])) { // 密碼正確
      $_SESSION['acc_s_num']  = $vrow['s_num'];
      $_SESSION['acc_name']  = $vrow['acc_name'];
      
      $json_data["state"] = "true";
      // $json_data['acc_user']   = $vrow['acc_user'];
      $json_data['acc_s_num']  = $vrow['s_num'];
      $json_data['acc_name']   = $vrow['acc_name'];
      $json_data['acc_auth'] = $vrow['group_s_num']; // 4:外送員權限 , 6:社福機構
      $json_data['acc_token'] = $this->save_token(); // token 產生及儲存
      $this->save_login_info();
    }
    return $json_data;      
  }
  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得account所有資料()
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_all() {
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    $data = NULL;
    $sql = "select {$tbl_account}.*,
                   {$tbl_account_group}.acg_name
            from {$tbl_account}
            left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_account}.group_s_num
            where {$tbl_account}.is_available = 1
                  and {$tbl_account}.d_date is null
                  and {$tbl_account_group}.d_date is null
            order by s_num
           ";
    //echo $sql;
    //echo '<hr>';
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
  //  函數名稱: get_acc_by_group()
  //  函數功能: 取得社工、外送員名單()
  //  程式設計: Kiwi
  //  設計日期: 2020/12/27
  // **************************************************************************
  public function get_acc_by_group() {
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    $data = NULL;
    $sql = "select {$tbl_account}.*,
                  {$tbl_account_group}.acg_name
                  from {$tbl_account}
                  left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_account}.group_s_num
                  where {$tbl_account}.is_available = 1
                  and {$tbl_account}.group_s_num in (4 , 6)
                  and {$tbl_account}.d_date is null
                  and {$tbl_account_group}.d_date is null
                  order by s_num
                 ";
    //echo $sql;
    //echo '<hr>';
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
  //  函數名稱: get_login()
  //  函數功能: 取得account登入人員單筆資料()
  //  程式設計: Tony
  //  設計日期: 2017/11/15
  // **************************************************************************
  public function get_login() {
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    $acc_s_num = $_SESSION['acc_s_num'];
    $row = NULL;
    $sql = "select {$tbl_account}.*,
                   {$tbl_account_group}.acg_name
            from {$tbl_account}
            left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_account}.group_s_num
            where {$tbl_account}.s_num = ?
                  and {$tbl_account}.is_available = 1
                  and {$tbl_account}.d_date is null
                  and {$tbl_account_group}.d_date is null
            order by {$tbl_account}.s_num
            limit 0,1
           ";
    //echo $sql;
    //echo '<hr>';
    $rs = $this->db->query($sql, array($acc_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: Tony
  //  設計日期: 2019-05-21
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_sys_account}.*,
                   {$tbl_account_group}.acg_name,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name
            from {$tbl_sys_account}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_sys_account}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_sys_account}.e_empno
            left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_sys_account}.group_s_num
            where {$tbl_sys_account}.d_date is null
                  and {$tbl_sys_account}.s_num = ?
            order by {$tbl_sys_account}.s_num desc
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
  //  設計日期: 2019/5/8
  // **************************************************************************
  public function chk_duplicate($acc_user) {
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
    $acc_user = $this->db->escape_like_str($acc_user);
    $row = NULL;
    $sql = "select {$tbl_sys_account}.*
            from {$tbl_sys_account}
            where {$tbl_sys_account}.d_date is null
                  and {$tbl_sys_account}.acc_user = ?
            order by {$tbl_sys_account}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($acc_user));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_token()
  //  函數功能: 檢查token
  //  程式設計: Kiwi
  //  設計日期: 2020/12/05
  // **************************************************************************
  public function chk_token() {
    $headers = $this->input->request_headers(); 
    $token = explode("Token " , $headers["Authorization"]);  
    $row = NULL;
    if(isset($token[1])) {
      $acc_token = $this->db->escape_like_str($token[1]);
      $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
      $sql = "select {$tbl_sys_account}.*
                    from {$tbl_sys_account}
                    where {$tbl_sys_account}.d_date is null
                    and {$tbl_sys_account}.acc_token = ?
                    order by {$tbl_sys_account}.s_num desc
                  ";
      //u_var_dump($sql);
      $rs = $this->db->query($sql, array($acc_token));
      if($rs->num_rows() > 0) { // 有資料才執行
        $row = $rs->row(); 
      }
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得account資料
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    $where = " {$tbl_sys_account}.d_date is null ";
    $order = " {$tbl_sys_account}.s_num desc ";    
    if(''<>$_SESSION[$q_str]['que_str']) { // 全文檢索
      $que_str = $_SESSION[$q_str]['que_str'];
      $where .= " and ({$tbl_sys_account}.group_s_num like '%{$que_str}%'
                       or {$tbl_sys_account}.acc_user like '%{$que_str}%'
                       or {$tbl_sys_account}.acc_name like '%{$que_str}%'
                       or {$tbl_sys_account}.acc_depname like '%{$que_str}%'
                       or {$tbl_sys_account}.acc_email like '%{$que_str}%'
                       or {$tbl_sys_account}.acc_tel like '%{$que_str}%'
                       or {$tbl_sys_account}.acc_phone like '%{$que_str}%'
                      )
                ";
    }
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$tbl_sys_account}.{$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算總筆數
    $sql_cnt = "select count(*) as cnt
                from {$tbl_sys_account}
                left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_sys_account}.group_s_num
                where $where
                order by {$tbl_sys_account}.s_num
               ";
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->row(); 

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_sys_account}.*,
                   {$tbl_account_group}.acg_name
            from {$tbl_sys_account}
            left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_sys_account}.group_s_num
            where $where
            order by {$order}
            $limit
           ";
    //echo $sql;
    //echo '<hr>';
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
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $chk_acc_row = $this->chk_duplicate($data['acc_user']); // 檢查登入帳號
    if(NULL==$chk_acc_row) {
      $data['acc_pwd'] = password_hash($data['acc_pwd'], PASSWORD_DEFAULT); // 密碼要加密
      $data['b_empno'] = $_SESSION['acc_s_num'];
      $data['b_date'] = date('Y-m-d H:i:s');
      $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
      if(!$this->db->insert($tbl_sys_account, $data)) {
        $rtn_msg = $this->lang->line('add_ng');
      }
      if($data['group_s_num'] == 4) { // 外送員
        $dp_data['b_empno'] = $_SESSION['acc_s_num'];
        $dp_data['b_date'] = date('Y-m-d H:i:s');
        $dp_data["is_available"] = 1;
        $dp_data["acc_s_num"] = $this->db->insert_id(); 
        $dp_data["acc_name"] = $data["acc_name"];
        $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person');  // 外送員管理
        if(!$this->db->insert($tbl_delivery_person, $dp_data)) {
          $rtn_msg = $this->lang->line('add_ng');
        }
      }
    }
    else {
      $rtn_msg = $this->lang->line('add_duplicate');
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
    $rtn_msg = 'ok';

    $rtn_msg = 'ok';
    $data = $this->input->post();
    if(''<>$data['acc_pwd']) { // 不是空白才處理
      $data['acc_pwd'] = password_hash($data['acc_pwd'], PASSWORD_DEFAULT); // 密碼要加密
    }
    else {
      unset($data['acc_pwd']);
    }
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_sys_account, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: Tony
  //  設計日期: 2019-05-21
  // **************************************************************************
  public function del() {
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_sys_account, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: Tony
  //  設計日期: 2017/7/21
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_kind         = $_POST['f_kind'];
    $f_s_num        = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $vtbl_name = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號
    $vdata['is_available'] = $f_is_available;
    $vdata['e_empno']      = $_SESSION['acc_s_num'];
    $vdata['e_date']       = date('Y-m-d H:i:s');
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($vtbl_name, $vdata)) {
      $rtn_msg = $this->lang->line('account_save_'.$f_kind.'_err');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_info_edit()
  //  函數功能: 儲存修改個人資料資料
  //  程式設計: Tony
  //  設計日期: 2017/11/16
  // **************************************************************************
  public function save_info_edit() {
    $rtn_msg = 'ok';
    $f_s_num = $_SESSION['acc_s_num'];
    $f_acc_name = $_POST['f_acc_name'];
    $f_acc_phone = $_POST['f_acc_phone'];
    $f_acc_tel = $_POST['f_acc_tel'];
    $f_acc_depname = $_POST['f_acc_depname'];
    $f_acc_email = $_POST['f_acc_email'];
    $vtbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號

    $vdata['acc_name'] = $f_acc_name;
    $vdata['acc_phone'] = $f_acc_phone;
    $vdata['acc_tel'] = $f_acc_tel;
    $vdata['acc_depname'] = $f_acc_depname;
    $vdata['acc_email'] = $f_acc_email;
    $vdata['e_empno'] = $_SESSION['acc_s_num'];
    $vdata['e_date'] = date('Y-m-d H:i:s');
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($vtbl_account, $vdata)) {
      $rtn_msg = $this->lang->line('account_save_info_err');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_pwd_edit()
  //  函數功能: 儲存個人密碼
  //  程式設計: Tony
  //  設計日期: 2017/11/16
  // **************************************************************************
  public function save_pwd_edit() {
    $rtn_msg = 'ok';
    $vtbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號
    $f_s_num = $_SESSION['acc_s_num'];
    $f_acc_pwd_old = $_POST['f_acc_pwd_old']; // 原登入密碼
    $f_acc_pwd = password_hash($_POST['f_acc_pwd'], PASSWORD_DEFAULT); // 新密碼要加密

    $sql = "select *
            from {$vtbl_account}
            where {$vtbl_account}.s_num = ?
                  and {$vtbl_account}.is_available = 1
                  and {$vtbl_account}.d_date is null
            order by {$vtbl_account}.s_num
            limit 0,1
           ";
    //echo $sql;
    //echo '<hr>';
    $rs = $this->db->query($sql, array($f_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row_array();
      //echo $row['acc_pwd'];
      //echo "\n";
      //exit;
      if(password_verify($f_acc_pwd_old, $row['acc_pwd'])) { // 密碼正確
        $vdata['acc_pwd'] = $f_acc_pwd;
        $vdata['e_empno'] = $_SESSION['acc_s_num'];
        $vdata['e_date'] = date('Y-m-d H:i:s');
        $this->db->where('s_num', $f_s_num);
        if(!$this->db->update($vtbl_account, $vdata)) {
          $rtn_msg = $this->lang->line('account_upd_pwd_err'); // 個人密碼更新失敗
        }
      }
      else {
        $rtn_msg = $this->lang->line('account_save_pwd_old_err'); // 原密碼錯誤
      }
    }
    else {
      $rtn_msg = $this->lang->line('account_no_data_err'); // 查無資料，請重新登入
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_login_info()
  //  函數功能: 儲存登入資訊
  //  程式設計: Tony
  //  設計日期: 2019/5/22
  // **************************************************************************
  public function save_login_info() {
    $rtn_msg = 'ok';
    $s_num = $_SESSION['acc_s_num'];
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳號

    $this->db->set('acc_login_cnt', 'acc_login_cnt+1',FALSE);
    $this->db->set('acc_login_last_date', date('Y-m-d H:i:s'));
    $this->db->set('acc_login_last_ip', $_SERVER["REMOTE_ADDR"]);
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_account)) {
      //$rtn_msg = $this->lang->line('account_save_login_info_err');
    }
    //echo $rtn_msg;
    return;
  }
}
?>