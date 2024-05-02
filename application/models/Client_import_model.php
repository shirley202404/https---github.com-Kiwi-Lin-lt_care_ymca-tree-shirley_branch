<?php
class Client_import_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public $aes_fd = array('ct01','ct02','ct03','ct05','ct06_telephone',
                         'ct06_homephone','ct08','ct09','ct10','ct11',
                         'ct12','ct13','ct14','ct15','ct70','ct71','ct72'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_client_import}.*,
                   if(sys_acc.acc_name is null, 
                      CONCAT(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}')), 
                      sys_acc.acc_name) as b_acc_name,
                   if(sys_acc2.acc_name is null, 
                      CONCAT(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}')), 
                      sys_acc2.acc_name) as e_acc_name,
                   case {$tbl_client_import}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->_aes_fd()}
            from {$tbl_client_import}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_client_import}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_client_import}.e_empno
            left join {$tbl_social_worker} sw on sw.s_num = {$tbl_client_import}.b_empno
            left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_client_import}.e_empno
            where {$tbl_client_import}.d_date is null
                  and {$tbl_client_import}.s_num = ?
            order by {$tbl_client_import}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      }
      foreach ($row as $fd_name => $v) {
        list($fd_name, $fd_val) = $this->_replace_text($row, $fd_name);
        $row->$fd_name = $fd_val;
      }
    }
    
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_client_import}.*
                   
            from {$tbl_client_import}
            where {$tbl_client_import}.d_date is null
                  and {$tbl_client_import}.fd_name = ?
            order by {$tbl_client_import}.s_num desc
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
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_all() {
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $data = NULL;
    $sql = "select {$tbl_client_import}.*
                   {$this->_aes_fd()}
            from {$tbl_client_import}
            where {$tbl_client_import}.d_date is null
            order by {$tbl_client_import}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_all_is_available()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $data = NULL;
    $sql = "select {$tbl_client_import}.*
                   {$this->_aes_fd()}
                  from {$tbl_client_import}
                  where {$tbl_client_import}.d_date is null
                               and {$tbl_client_import}.is_available = 1
                  order by {$tbl_client_import}.s_num desc
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $where = " {$tbl_client_import}.d_date is null ";
    $order = " {$tbl_client_import}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_client_import}.bn_s_num like binary '%{$que_str}%' /* tw_beacon.s_num */                       
                       or concat(AES_DECRYPT({$tbl_client_import}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_client_import}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */                       
                       or AES_DECRYPT({$tbl_client_import}.ct03,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主身分證 */                       
                       or {$tbl_client_import}.ct05 like binary '%{$que_str}%' /* 案主生日 */                                           
                       or AES_DECRYPT({$tbl_client_import}.ct12,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主聯絡區號 */                                                
                       or AES_DECRYPT({$tbl_client_import}.ct13,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主連絡-城市 */                                                
                       or AES_DECRYPT({$tbl_client_import}.ct14,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主連絡-區 */                                                
                       or AES_DECRYPT({$tbl_client_import}.ct15,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主連絡-地址 */                                                           
                       or {$tbl_client_import}.ct16 like binary '%{$que_str}%' /* 案主家緯度(由督導新增及更改) */                                              
                       or {$tbl_client_import}.ct17 like binary '%{$que_str}%' /* 案主家經度(由督導新增及更改) */                                              
                       or {$tbl_client_import}.ct99 like binary '%{$que_str}%' /* 備註 */
                      )
                ";
    }
    
    if(!empty($get_data['que_ct01'])) { // 案主姓
      $que_ct01 = $get_data['que_ct01'];
      $que_ct01 = $this->db->escape_like_str($que_ct01);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct01,'{$this->db_crypt_key2}') like '%{$que_ct01}%'  /* 中文姓 */ ";
    }

    if(!empty($get_data['que_ct02'])) { // 案主名
      $que_ct02 = $get_data['que_ct02'];
      $que_ct02 = $this->db->escape_like_str($que_ct02);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct02,'{$this->db_crypt_key2}') like '%{$que_ct02}%'  /* 中文名 */ ";
    }
    
    if(!empty($get_data['que_ct03'])) { // 案主身分證
      $que_ct03 = $get_data['que_ct03'];
      $que_ct03 = $this->db->escape_like_str($que_ct03);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct03,'{$this->db_crypt_key2}') = '{$que_ct03}'  /* 身分證 */ ";
    }
    
    if(!empty($get_data['que_ct06'])) { // 案主手機
      $que_ct06 = $get_data['que_ct06'];
      $que_ct06 = $this->db->escape_like_str($que_ct06);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct06,'{$this->db_crypt_key2}') = '{$que_ct06}'  /* 案主手機 */ ";
    }
    
    if(!empty($get_data['que_ct12'])) { // 案主聯絡區號
      $que_ct12 = $get_data['que_ct12'];
      $que_ct12 = $this->db->escape_like_str($que_ct12);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct12,'{$this->db_crypt_key2}') = '{$que_ct12}'  /* 案主聯絡區號 */ ";
    }

    if(!empty($get_data['que_ct13'])) { // 案主連絡-城市
      $que_ct13 = $get_data['que_ct13'];
      $que_ct13 = $this->db->escape_like_str($que_ct13);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct13,'{$this->db_crypt_key2}') like '%{$que_ct13}%'  /* 案主連絡-城市 */ ";
    }

    if(!empty($get_data['que_ct14'])) { // 案主連絡-區
      $que_ct14 = $get_data['que_ct14'];
      $que_ct14 = $this->db->escape_like_str($que_ct14);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct14,'{$this->db_crypt_key2}') like '%{$que_ct14}%'  /* 案主連絡-區 */ ";
    }    
    
    if(!empty($get_data['que_ct15'])) { // 案主連絡-地址
      $que_ct15 = $get_data['que_ct15'];
      $que_ct15 = $this->db->escape_like_str($que_ct15);
      $where .= " and AES_DECRYPT({$tbl_client_import}.ct15,'{$this->db_crypt_key2}') like '%{$que_ct15}%'  /* 案主連絡-地址 */ ";
    }    

    if(isset($get_data['que_ct16'])) { // 案主家緯度
      $que_ct16 = $get_data['que_ct16'];
      if(is_numeric($que_ct16)) {
        $que_ct16 = $this->db->escape_like_str($que_ct16);
        $where .= " and {$tbl_client_import}.ct16 = '{$que_ct16}'  /* 案主家緯度 */ ";
      }
    }    

    if(isset($get_data['que_ct17'])) { // 案主家經度
      $que_ct17 = $get_data['que_ct17'];
      if(is_numeric($que_ct17)) {
        $que_ct17 = $this->db->escape_like_str($que_ct17);
        $where .= " and {$tbl_client_import}.ct17 = '{$que_ct17}'  /* 案主家經度 */ ";
      }
    }    

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_client_import}.s_num
                from {$tbl_client_import}
                where $where
                group by {$tbl_client_import}.s_num
                order by {$tbl_client_import}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_client_import}.*
                   {$this->_aes_fd()}
            from {$tbl_client_import}
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
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return(array($data,$row_cnt));
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    if(!$this->db->insert($tbl_client_import, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    else {
      $ct_s_num = $this->db->insert_id();
      $_POST['s_num'] = $ct_s_num;
      $ct_identity['b_empno'] = $_SESSION['acc_s_num'];
      $ct_identity['b_date'] = date('Y-m-d H:i:s');
      $ct_identity['ct_s_num'] = $ct_s_num;
      $ct_identity['ct_il01'] = date('Y-m-d H:i:s');
      $ct_identity['ct_il02'] = $data['ct34_go'];
      $tbl_client_import_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料
      if(!$this->db->insert($tbl_client_import_identity_log, $ct_identity)) {
        $rtn_msg = $this->lang->line('add_ng');
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    } 
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_client_import, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $_POST['s_num'] = $_POST['f_s_num'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_client_import, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_import()
  //  函數功能: 儲存匯入的資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function save_import($client_data) {
    $rtn_msg = 'ok';
    foreach ($client_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($client_data[$k_fd_name]);
      }
    } 
    $client_data['b_empno'] = $_SESSION['acc_s_num'];
    $client_data['b_date'] = date('Y-m-d H:i:s');
    $client_data['is_available'] = 1;
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    if(!$this->db->insert($tbl_client_import, $client_data)) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: save_convert()
  //  函數功能: 將資料轉為正式資料
  //  程式設計: kiwi
  //  設計日期: 2023-08-23
  // **************************************************************************
  public function save_convert() {
    $rtn_msg = 'ok';
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料暫存檔

    $data = $this->input->post();
    $s_num = $data['s_num'];
    $ct_s_num = $data['ct_s_num'];
    unset($data['s_num'], $data['ct_s_num']);
    $chk_row = $this->clients_model->get_one_by_ct03($data['ct03']);
    
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
     
    if(empty($chk_row)) {
      // 如果是新增資料的話，要新增一筆政府身分別到identity_log
      $data['b_empno'] = $_SESSION['acc_s_num'];
      $data['b_date'] = date('Y-m-d H:i:s');
      if(!$this->db->insert($tbl_clients, $data)) {
        die($this->lang->line('convert_ng'));
      }

      $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料
      $ct_identity['b_empno'] = $_SESSION['acc_s_num'];
      $ct_identity['b_date'] = date('Y-m-d H:i:s');
      $ct_identity['ct_s_num'] = $this->db->insert_id();
      $ct_identity['ct_il01'] = date('Y-m-d H:i:s');
      $ct_identity['ct_il02'] = $data['ct34_go'];
      if(!$this->db->insert($tbl_clients_identity_log, $ct_identity)) {
        $rtn_msg = $this->lang->line('convert_ng');
      }
    }
    else {
      unset($data['ct34_go']);
      $data['e_empno'] = $_SESSION['acc_s_num'];
      $data['e_date'] = date('Y-m-d H:i:s');
      $this->db->where('s_num', $chk_row->s_num);
      if(!$this->db->update($tbl_clients, $data)) {
        die($this->lang->line('convert_ng'));
      }
    }

    // 處理完成後，立即刪除
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $this->db->where('s_num', $s_num);
    if(!$this->db->delete($tbl_client_import)) {
      $rtn_msg = $this->lang->line('convert_ng'); // 刪除失敗
    }

    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_client_import, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function _aes_fd() {
    $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_client_import}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
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
  //  函數名稱: _replace_text()
  //  函數功能: 替換字串資料
  //  程式設計: kiwi
  //  設計日期: 2023-10-02
  // **************************************************************************
  private function _replace_text($row, $fd_name) {
    $fd_name_mask = '';
    $fd_val = NULL;
    if(isset($row->$fd_name)) {
      $fd_val = $row->$fd_name;
    }
  
    switch($fd_name) { // 檢查要替換的欄位名稱
      case 'ct00':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct04':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct21':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'ct31':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'ct34_go_sab':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct34_go':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct34_fo':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'ct35':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct35_level':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct35_type':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct36':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct37':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct38_1':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'ct38_2':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
    }

    if('' != $fd_name_mask) {
      $fd_name = $fd_name_mask;
    }

    return(array($fd_name, $fd_val));
  }
}
?>