<?php
class Delivery_person_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('dp01','dp02','dp03','dp09_teltphone','dp09_homephone', 'dp10_addr'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_delivery_person}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_delivery_person}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_delivery_person}.dp04
                     when 'M' then '男'
                     when 'F' then '女'
                   end as dp04_str,
                   IF(sys_acc.acc_name is null ,
                     concat(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc.acc_name
                   ) as b_acc_name,
                   IF(sys_acc2.acc_name is null ,
                     concat(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc2.acc_name
                   ) as e_acc_name
                   {$this->_aes_fd()}
            from {$tbl_delivery_person}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_delivery_person}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_delivery_person}.e_empno
            left join {$tbl_social_worker} sw on sw.s_num = {$tbl_delivery_person}.b_empno
            left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_delivery_person}.e_empno
            where {$tbl_delivery_person}.d_date is null
                  and {$tbl_delivery_person}.s_num = ?
            order by {$tbl_delivery_person}.s_num desc
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
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function chk_duplicate($fd_name , $v) {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_delivery_person}.*
                   {$this->_aes_fd()}
            from {$tbl_delivery_person}
            where {$tbl_delivery_person}.d_date is null
                  and {$tbl_delivery_person}.{$fd_name} = ?
            order by {$tbl_delivery_person}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($v));
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
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_all() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $data = NULL;
    $sql = "select {$tbl_delivery_person}.*
                   {$this->_aes_fd()}
            from {$tbl_delivery_person}
            where {$tbl_delivery_person}.d_date is null
            order by {$tbl_delivery_person}.s_num desc
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
  //  函數名稱: get_all_by_dp30()
  //  函數功能: 取得為社工具備外送員身分者
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_all_by_dp30() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $data = NULL;
    $sql = "select {$tbl_delivery_person}.*
                   {$this->_aes_fd()}
            from {$tbl_delivery_person}
            where {$tbl_delivery_person}.d_date is null
                        and {$tbl_delivery_person}.is_available = 1
                        and {$tbl_delivery_person}.dp30 = 'Y'
            order by {$tbl_delivery_person}.s_num desc
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
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $data = NULL;
    $sql = "select {$tbl_delivery_person}.*
                   {$this->_aes_fd()}
            from {$tbl_delivery_person}
            where {$tbl_delivery_person}.d_date is null
                  and {$tbl_delivery_person}.is_available = 1 /* 啟用 */
            order by {$tbl_delivery_person}.s_num desc
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
  //  函數名稱: get_rest()
  //  函數功能: 取得所有請假資料
  //  程式設計: kiwi
  //  設計日期: 2024-01-10
  // **************************************************************************
  public function get_rest($dp_s_num) {
    $tbl_delivery_person_rest = $this->zi_init->chk_tbl_no_lang('delivery_person_rest'); // 外送員請假資料
    $data = NULL;
    $sql = "select {$tbl_delivery_person_rest}.*
            from {$tbl_delivery_person_rest}
            where {$tbl_delivery_person_rest}.d_date is null
                  and {$tbl_delivery_person_rest}.dp_s_num = ?
            order by {$tbl_delivery_person_rest}.dpr01 desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($dp_s_num));
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
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $where = " {$tbl_delivery_person}.d_date is null ";
    $order = " {$tbl_delivery_person}.is_available desc, {$tbl_delivery_person}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_delivery_person}.group_s_num like '%{$que_str}%' /* sys_account_group.s_num */                       
                       or {$tbl_delivery_person}.dt_s_num like '%{$que_str}%' /* tw_department.s_num */                       
                       or concat(AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 中文姓 */                       
                       or AES_DECRYPT({$tbl_delivery_person}.dp03,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 身份證字號 */                       
                       or {$tbl_delivery_person}.dp04 like '%{$que_str}%' /* 性別(M=男；F=女) */                                      
                       or {$tbl_delivery_person}.dp05 like '%{$que_str}%' /* 職工編號 */                       
                       or {$tbl_delivery_person}.dp06 like '%{$que_str}%' /* Email Address */                       
                       or BINARY {$tbl_delivery_person}.dp07_start like BINARY '%{$que_str}%' /* 到職日期 */                       
                       or BINARY {$tbl_delivery_person}.dp07_end like BINARY '%{$que_str}%' /* 到職日期 */                       
                       or {$tbl_delivery_person}.dp08 like '%{$que_str}%' /* 年資 */                       
                       or AES_DECRYPT({$tbl_delivery_person}.dp09_teltphone,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 手機門號 */                       
                       or AES_DECRYPT({$tbl_delivery_person}.dp09_homephone,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 家裡電話 */                       
                       or {$tbl_delivery_person}.dp99 like '%{$que_str}%' /* 備註 */                       
                       or {$tbl_delivery_person}.dp_login_cnt like '%{$que_str}%' /* 登入次數 */                       
                       or BINARY {$tbl_delivery_person}.dp_login_last_date like BINARY '%{$que_str}%' /* 最後登入日期時間 */                       
                       or {$tbl_delivery_person}.dp_login_last_ip like '%{$que_str}%' /* 最後登入IP */
                      )
                ";
    }

    if(!empty($get_data['que_dp01'])) { // 中文姓
      $que_dp01 = $get_data['que_dp01'];
      $que_dp01 = $this->db->escape_like_str($que_dp01);
      $where .= " and AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') = '{$que_dp01}'  /* 中文姓 */ ";
    }
    if(!empty($get_data['que_dp02'])) { // 中文名
      $que_dp02 = $get_data['que_dp02'];
      $que_dp02 = $this->db->escape_like_str($que_dp02);
      $where .= " and AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') = '{$que_dp02}'  /* 中文名 */ ";
    }
    if(!empty($get_data['que_dp03'])) { // 身份證字號
      $que_dp03 = $get_data['que_dp03'];
      $que_dp03 = $this->db->escape_like_str($que_dp03);
      $where .= " and AES_DECRYPT({$tbl_delivery_person}.dp03,'{$this->db_crypt_key2}') = '{$que_dp03}'  /* 身份證字號 */ ";
    }
    if(!empty($get_data['que_dp05'])) { // 職工編號
      $que_dp05 = $get_data['que_dp05'];
      $que_dp05 = $this->db->escape_like_str($que_dp05);
      $where .= " and {$tbl_delivery_person}.dp05 = '{$que_dp05}'  /* 職工編號 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_delivery_person}.s_num
                from {$tbl_delivery_person}
                where $where
                group by {$tbl_delivery_person}.s_num
                order by {$tbl_delivery_person}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_delivery_person}.*,
                   case {$tbl_delivery_person}.dp04
                     when 'M' then '男'
                     when 'F' then '女'
                   end as dp04_str
                   {$this->_aes_fd()}
            from {$tbl_delivery_person}
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
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $delivery_person = $this->input->post('delivery_person');
    $chk_sw_row = $this->chk_duplicate("dp05" , $delivery_person['dp05']); // 檢查登入帳號
    if(!empty($chk_sw_row)) {
      echo $this->lang->line('add_duplicate');
      return;
    }

    foreach ($delivery_person as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($delivery_person[$k_fd_name]);
      }
    }

    $delivery_person['dp_pwd'] = password_hash($delivery_person['dp_pwd'], PASSWORD_DEFAULT); // 密碼要加密
    $delivery_person['b_empno'] = $_SESSION['acc_s_num'];
    $delivery_person['b_date'] = date('Y-m-d H:i:s');
    $delivery_person['group_s_num'] = 14;
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    if(!$this->db->insert($tbl_delivery_person, $delivery_person)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    else {
      $dp_s_num = $this->db->insert_id();
      $delivery_person_rest_data = $this->input->post('delivery_person_rest');
      if(!empty($delivery_person_rest_data)) {
        $res = $this->_save_delivery_person_rest($dp_s_num, $delivery_person_rest_data, $del_flag="N");
        if("ok" != $res) {
          echo $this->lang->line('add_ng');;
          return;
        }
      }
    }
    
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function save_upd() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $rtn_msg = 'ok';
    $delivery_person = $this->input->post('delivery_person');
    foreach ($delivery_person as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($delivery_person[$k_fd_name]);
      }
    }

    if('' <> $delivery_person['dp_pwd']) { // 不是空白才處理
      $delivery_person['dp_pwd'] = password_hash($delivery_person['dp_pwd'], PASSWORD_DEFAULT); // 密碼要加密
    }
    else {
      unset($delivery_person['dp_pwd']);
    } 

    $delivery_person['e_empno'] = $_SESSION['acc_s_num'];
    $delivery_person['e_date'] = date('Y-m-d H:i:s');
    $this->db->where('s_num', $delivery_person['s_num']);
    if(!$this->db->update($tbl_delivery_person, $delivery_person)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    else {
      $dp_s_num = $delivery_person['s_num'];
      $delivery_person_rest_data = $this->input->post('delivery_person_rest');
      if(!empty($delivery_person_rest_data)) {
        $res = $this->_save_delivery_person_rest($dp_s_num, $delivery_person_rest_data, $del_flag="Y");
        if("ok" != $res) {
          echo $this->lang->line('add_ng');;
          return;
        }
      }
    }

    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _save_delivery_person_rest()
  //  函數功能: 儲存送餐員請假資料
  //  程式設計: Kiwi
  //  設計日期: 2023/12/23
  // **************************************************************************
  private function _save_delivery_person_rest($dp_s_num, $delivery_person_rest_data, $del_flag="N") {
    $rtn_msg = 'ok';
    $tbl_delivery_person_rest = $this->zi_init->chk_tbl_no_lang('delivery_person_rest'); // 送餐員請假資料
    $dp_s_num = (int)$this->db->escape_like_str($dp_s_num);

    if('Y' == $del_flag) { // 要刪除資料
      $this->db->where('dp_s_num', $dp_s_num);
      if(!$this->db->delete($tbl_delivery_person_rest)) {
        $rtn_msg = $this->lang->line('del_ng');
        return($rtn_msg);
      }
    }

    if(!empty($delivery_person_rest_data)) { // 有資料才處理
      foreach ($delivery_person_rest_data as $k => $v) {
        unset($delivery_person_rest_data[$k]['s_num']);
        $delivery_person_rest_data[$k]['b_empno'] = $_SESSION['acc_s_num'];
        $delivery_person_rest_data[$k]['b_date'] = date('Y-m-d H:i:s');
        $delivery_person_rest_data[$k]['dp_s_num'] = $dp_s_num;
      }
      if(!empty($delivery_person_rest_data)) {
        if(!$this->db->insert_batch($tbl_delivery_person_rest, $delivery_person_rest_data)) {
          $rtn_msg = $this->lang->line('add_ng');
        }
      }
    }
    return($rtn_msg);
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function save_is_available() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $_POST['s_num'] = $f_s_num;
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_delivery_person, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function del() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_delivery_person, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: rest()
  //  函數功能: 請假資料
  //  程式設計: kiwi
  //  設計日期: 2024-01-10
  // **************************************************************************
  public function rest() {
    $tbl_delivery_person_rest = $this->zi_init->chk_tbl_no_lang('delivery_person_rest'); // 外送員請假資料
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date']  = date('Y-m-d H:i:s');
    $data['dp_s_num']  = $v['rest_dp_s_num'];
    $data['dpr01']  = $v['rest_dpr01'];
    $data['dpr02']  = $v['rest_dpr02'];
    
    if(!$this->db->insert($tbl_delivery_person_rest, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function _aes_fd() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_delivery_person}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  private function _symbol_text($row,$fd_name) {
    $fd_name_mask = '';
    $fd_val = NULL;
    if(isset($row->$fd_name)) {
      $fd_val = $row->$fd_name;
    }
    switch($fd_name) { // 檢查要遮罩的欄位名稱
      case 'dp01': // 中文姓
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'',-1,1);
        break;
      case 'dp02': // 中文名
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'',-1,1);
        break;
      case 'dp03': // 身份證字號
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'*',1,1);
        break;
      case 'dp09_teltphone': // 手機門號
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'',-1,1);
        break;
      case 'dp09_homephone': // 家裡電話
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'',-1,1);
        break;
    }
    if('' != $fd_name_mask) {
      $fd_name = $fd_name_mask;
    }
    return(array($fd_name,$fd_val));
  }
  
  // **************************************************************************
  //  函數名稱: que_dyp()
  //  函數功能: 取得外送員資料
  //  程式設計: kiwi
  //  設計日期: 2020-02-08
  // **************************************************************************
  public function que_dp() {
    $data = NULL;
    $que = $this->db->escape_like_str($_POST['q']);
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 客戶資料
    $where = "{$tbl_delivery_person}.d_date is null
                       and {$tbl_delivery_person}.is_available = 1
                      ";
    if(''<>$que) {
      $where .= " and (concat(AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}'), AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}')) like '%{$que}%' /* 中文姓 */
                            )
                         ";
    }
    $sql = "select {$tbl_delivery_person}.s_num
                             {$this->_aes_fd()}
                 from {$tbl_delivery_person}
                 where {$where}  
                 order by {$tbl_delivery_person}.s_num
                ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      foreach ($rs->result() as $row){
        foreach ($this->aes_fd as $k => $v) {
          list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
          $row->$fd_name = $fd_val;
        }
        $data[] = "{$row->dp01}{$row->dp02};;{$row->s_num};;{$row->dp03}"; 
      }
    }
    echo json_encode($data);
    return;
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
    unset($_SESSION['chg_pwd']);
    unset($_SESSION['tbl_lang']);
    unset($_SESSION['acc_kind']);
    if(!isset($_SESSION['cap']['word'])) { // 沒有驗證碼
      return false;
    }
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組

    $vacc_user     = $_POST['acc_user'];       // 帳號
    $vacc_password = $_POST['acc_password'];  // 密碼
    $vlogincaptcha = $_POST['logincaptcha'];  // 驗證碼
    if(strtolower($vlogincaptcha)==strtolower($_SESSION['cap']['word'])) {
      $vrow = NULL;
      $vsql = "select {$tbl_delivery_person}.*,
                      {$tbl_account_group}.acg_name
                      {$this->_aes_fd()}
               from {$tbl_delivery_person}
               left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_delivery_person}.group_s_num
               where {$tbl_delivery_person}.dp05 = ?
                     and {$tbl_delivery_person}.is_available = 1
                     and {$tbl_delivery_person}.d_date is null
               order by {$tbl_delivery_person}.dp05
               limit 0,1
              ";
      //u_var_dump($vsql);
      //echo '<hr>';
      $vrs = $this->db->query($vsql, array($vacc_user));
      if($vrs->num_rows() > 0) { // 有資料才執行
        $vrow = $vrs->row_array();
        //var_dump($vrow);
      }
      if(password_verify($vacc_password, $vrow['dp_pwd'])) { // 密碼正確
        $_SESSION['acc_s_num'] = $vrow['s_num'];
        $_SESSION['acc_user'] = $vrow['dp05'];
        $_SESSION['acc_name'] = "{$vrow['dp01']}{$vrow['dp02']}";
        $_SESSION['group_s_num'] = $vrow['group_s_num'];
        $_SESSION['acc_depname'] = NULL;
        $_SESSION['acg_name'] = $vrow['acg_name'];
        $_SESSION['is_super'] = 'N';
        if('1234'==$vacc_password) { // 密碼為預設值，就要強迫更新
          $_SESSION['chg_pwd'] = 'Y';
        }
        else {
          $_SESSION['chg_pwd'] = 'N';
        }
        $_SESSION['tbl_lang'] = 'tw'; // 目前預設tw,到時候再修正
        $_SESSION['acc_kind'] = 'DP'; // 外送員
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
    $this->load->library('encryption');
    $post_data = $this->input->post();
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    
    // 登入狀態：狀態碼 <=> 代表狀態
    // - 0: "登入成功" (所有成功全部回傳0)
    // - 101: "查無此機構"
    // - 102: "帳號密碼驗證錯誤"
    // - 103: "禁止重複登入"
    // - 104: "帳號密碼不得為空值"

    $json_data = NULL;            
    if(!isset($post_data['acc_user']) or !isset($post_data['acc_password'])) {
      return array(102, $json_data); 
    }

    if(empty($post_data['acc_user']) or empty($post_data['acc_password'])) {
      return array(102, $json_data); 
    }

    $vrow = NULL;
    $vacc_user     = $post_data['acc_user'];     // 帳號
    $vacc_password = $post_data['acc_password']; // 密碼
    $is_read_only = $post_data['isReadOnly'];    // 是否為實習生
    $is_force_login = $post_data['forceLogin']; // 是否為強制登入

    $vsql = "select {$tbl_delivery_person}.*,
                    {$tbl_account_group}.acg_name
                    {$this->_aes_fd()}
             from {$tbl_delivery_person}
             left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_delivery_person}.group_s_num
             where {$tbl_delivery_person}.dp_account = ?
                   and {$tbl_delivery_person}.is_available = 1
                   and {$tbl_delivery_person}.d_date is null
             order by {$tbl_delivery_person}.dp_account
             limit 0,1
            ";
    // u_var_dump($vsql);
    //echo '<hr>';
    $vrs = $this->db->query($vsql, array($vacc_user));
    if($vrs->num_rows() > 0) { // 有資料才執行
      $vrow = $vrs->row_array();
      //var_dump($vrow);
    }

    if(empty($vrow)) { // 如果查詢結果是空，代表沒有此帳號
      return array(102, $json_data); 
    }

    if(!password_verify($vacc_password, $vrow['dp_pwd'])) { // 如果密碼驗證不正確
      return array(102, $json_data); 
    } 

    if(strtolower($is_force_login) === 'false') {
      if(!empty($vrow['dp_login_last_date']) and strtolower($is_read_only) === 'false') { // 如果有最後登入時間，要檢查是否超過300秒
        $now_strtotime = strtotime(date("Y-m-d H:i:s"));
        $last_login_strtotime = strtotime($vrow['dp_login_last_date']);
        if($now_strtotime - $last_login_strtotime < 300) {
          return array(103, $json_data); 
        }
      }
    }

    // 登入的問題
    // 送餐員
    //   1. 如果已經沒有登入過的話，走一般流程，建立新的token
    //   2. 如果已經有登入過，那就沿用原本的
    // 實習生
    //   1. 如果已經登入過的話，實習生可以直接用原本的
    //   2. 如果已經沒有登入過的話，實習生登入也是走一般流程，建立新的token

    if(empty($vrow['dp_token'])) {
      $rand_num = rand(2,6);
      $time = date('U');
      $time_en = substr($this->encryption->encrypt($time),0,4); // 取4碼當混亂使用
      $verify_s_num = $vrow['s_num'];
      $verify_s_num_en = '';
      for($i = 0; $i < strlen($verify_s_num); $i++) {
        $verify_s_num_en .= substr($verify_s_num,$i,1).random_string('alnum', $rand_num);
      }
      $dp_token = "{$time_en}{$rand_num}".base64url_encode($verify_s_num_en);
    }
    else {
      $dp_token = $vrow['dp_token'];
    }
    
    $json_data["state"] = 0;
    $json_data['login_time'] = date("Y-m-d");
    $json_data['acc_name'] = "{$vrow['dp01']}{$vrow['dp02']}";
    $json_data['acc_auth'] = DP_AUTH_CODE; // APP權限設定，1:系統管理員，2=社工員，3=送餐員
    $json_data['dp_s_num'] = $vrow['s_num'];
    $json_data['acc_token'] = $dp_token;
    $this->save_login_info($vrow['s_num'], $json_data['acc_token']);

    return array(0, $json_data);
  }
  // **************************************************************************
  //  函數名稱: save_login_info()
  //  函數功能: 儲存登入資訊
  //  程式設計: Tony
  //  設計日期: 2021/2/17
  // **************************************************************************
  public function save_login_info($s_num=NULL, $token=NULL) {
    $rtn_msg = 'ok';
    if($s_num == NULL) {
      $s_num = $_SESSION['acc_s_num'];
    }
  
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員基本資料

    if($_POST['isReadOnly'] === 'false') { // 如果是一般送餐員
      $this->db->set('dp_login_last_date', date('Y-m-d H:i:s'));
    }
    $this->db->set('dp_login_cnt', 'dp_login_cnt+1',FALSE);
    $this->db->set('dp_login_last_ip', $_SERVER["REMOTE_ADDR"]);
    $this->db->set('dp_token', $token);
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_delivery_person)) {
      //$rtn_msg = $this->lang->line('account_save_login_info_err');
    }
    //echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_chg_pwd()
  //  函數功能: 密碼變更儲存
  //  程式設計: Tony
  //  設計日期: 2021/2/17
  // **************************************************************************
  public function save_chg_pwd() {
    $rtn_msg = $this->lang->line('account_save_pwd_ng'); // 密碼更新失敗!!!
    if(isset($_SESSION['acc_user']) and isset($_POST['f_acc_pwd'])) {
      $rtn_msg = 'ok';
      $acc_pwd = $_POST['f_acc_pwd'];
      if(''<>$acc_pwd) { // 不是空白才處理
        $data['dp_pwd'] = password_hash($acc_pwd, PASSWORD_DEFAULT); // 密碼要加密
      }
      $data['e_empno'] = $_SESSION['acc_s_num'];
      $data['e_date'] = date('Y-m-d H:i:s');
      $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 專案成員基本資料檔
      $this->db->where('dp05', $_SESSION['acc_user']);
      if(!$this->db->update($tbl_delivery_person, $data)) {
        $rtn_msg = $this->lang->line('account_save_pwd_ng'); // 密碼更新失敗!!!
      }
      else {
        $_SESSION['chg_pwd'] = 'N'; // 已經更新密碼,就解除
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_acc_logout()
  //  函數功能: 儲存APP登出訊息
  //  程式設計: Kiwi
  //  設計日期: 2023/11/15
  // **************************************************************************
  public function save_acc_logout($s_num) {
    $data['dp_login_last_date'] = NULL;
    $data['dp_token'] = NULL;
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員基本資料
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_delivery_person, $data)) {
      $rtn_msg = $this->lang->line('account_save_pwd_ng'); // 密碼更新失敗!!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_token()
  //  函數功能: 儲存token資訊
  //  程式設計: Tony
  //  設計日期: 2019/5/22
  // **************************************************************************
  public function save_token($s_num) {
    $rtn_msg = 'ok';
    $s_num = $s_num;
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 專案成員基本資料檔
    $sw_token = hash('sha256', openssl_random_pseudo_bytes(16));

    $this->db->set("dp_token" , $sw_token); // token
    //  $this->db->set("acc_token_exp" , $acc_token_exp); // token 期限
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_delivery_person)) {
      return false;
    }
    return $sw_token;
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
      $dp_token = $this->db->escape_like_str($token[1]);
      $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 專案成員基本資料檔
      $sql = "select {$tbl_delivery_person}.*
                    from {$tbl_delivery_person}
                    where {$tbl_delivery_person}.d_date is null
                    and {$tbl_delivery_person}.dp_token = ?
                    order by {$tbl_delivery_person}.s_num desc
                  ";
      //u_var_dump($sql);
      $rs = $this->db->query($sql, array($dp_token));
      if($rs->num_rows() > 0) { // 有資料才執行
        $row = $rs->row(); 
      }
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_em()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function chk_sw($s_num) {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_delivery_person}.*,
                   case {$tbl_delivery_person}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_delivery_person}.dp04
                     when 'M' then '男'
                     when 'F' then '女'
                   end as dp04_str
                   {$this->_aes_fd()}
            from {$tbl_delivery_person}
            where {$tbl_delivery_person}.d_date is null
                  and {$tbl_delivery_person}.sw_s_num = ?
            order by {$tbl_delivery_person}.s_num desc
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
  //  函數名稱: chk_dp03()
  //  函數功能: 確認外送員身分證是否重複
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function chk_dp03() {
    $v = $this->input->post();
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $row = NULL;
    $sql = "select {$tbl_delivery_person}.*
            from {$tbl_delivery_person}
            where {$tbl_delivery_person}.d_date is null
                  and AES_DECRYPT({$tbl_delivery_person}.dp03,'{$this->db_crypt_key2}') = ?
            order by {$tbl_delivery_person}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($v['dp03']));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
}
?>