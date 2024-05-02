<?php
class other_change_auth_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd1 = array('ct_name');  
  public $aes_fd2 = array('ct01' , 'ct02' , 'ct14');  
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_other_change_auth}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   IF({$tbl_account}.acc_name is null ,
                      concat(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}'))
                     ,{$tbl_account}.acc_name
                   ) as sw_acc_name,
                   case {$tbl_other_change_auth}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->_aes_fd('clients')}
            from {$tbl_other_change_auth}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_other_change_auth}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_other_change_auth}.e_empno
            left join {$tbl_account} on {$tbl_account}.s_num = {$tbl_other_change_auth}.sw_empno
            left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_other_change_auth}.sw_empno
            left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_other_change_auth}.ct_s_num
            where {$tbl_other_change_auth}.d_date is null
                  and {$tbl_other_change_auth}.s_num = ?
            order by {$tbl_other_change_auth}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd2 as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_other_change_auth}.*
            from {$tbl_other_change_auth}
            where {$tbl_other_change_auth}.d_date is null
                  and {$tbl_other_change_auth}.fd_name = ?
            order by {$tbl_other_change_auth}.s_num desc
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
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function get_all() {
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $data = NULL;
    $sql = "select {$tbl_other_change_auth}.*
            from {$tbl_other_change_auth}
            where {$tbl_other_change_auth}.d_date is null
            order by {$tbl_other_change_auth}.s_num desc
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
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $where = " {$tbl_other_change_auth}.d_date is null ";
    $order = " {$tbl_other_change_auth}.s_num desc ";
    
    if($_SESSION['acc_kind'] == "SW") {   
      $sw_s_num_arr = array(13, 16, 26);  
      if(in_array($_SESSION['group_s_num'], $sw_s_num_arr)) { 
        $i = 1;
        $sw_ra03_str = '';
        $sw_ra_row = $this->social_worker_model->get_ra($_SESSION['acc_s_num']);
        if(NULL != $sw_ra_row) {
          foreach ($sw_ra_row as $k_ra => $v_ra) {
            $sw_ra03_str .= "'{$v_ra['sw_ra03']}'"; // 負責區域
            if(count($sw_ra_row) != $i) {
              $sw_ra03_str .= ",";
            }
            $i++;
          }
          $where .= "and AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') in ({$sw_ra03_str})";
        }
      }
    }
    
    $get_data = $this->input->get();
    if(!isset($get_data['que_check_type'])) { // 沒有傳資料，預設為未簽簽呈
      $get_data['que_check_type'] = 'unchecked'; // 未簽簽呈
    }

    if($get_data['que_check_type'] == "unchecked") {
      $where .= " and {$tbl_other_change_auth}.sw_date is null ";
    }
    else {
      $where .= " and {$tbl_other_change_auth}.sw_date is not null ";
    }
    
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */               
                           or {$tbl_clients}.ct14 like '%{$que_str}%' /* 區域 */
                        ";
    }
    
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_other_change_auth}.s_num
                from {$tbl_other_change_auth}
                left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_other_change_auth}.ct_s_num
                where {$where}
                      and {$tbl_clients}.is_available = 1
                      and {$tbl_clients}.d_date is null
                group by {$tbl_other_change_auth}.s_num
                order by {$tbl_other_change_auth}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_other_change_auth}.*,
                   concat(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}')) as sw_acc_name
                   {$this->_aes_fd('clients')}
            from {$tbl_other_change_auth}
            left join {$tbl_account} on {$tbl_account}.s_num = {$tbl_other_change_auth}.sw_empno
            left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_other_change_auth}.sw_empno
            left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_other_change_auth}.ct_s_num
            where {$where}
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_clients}.d_date is null
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
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
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
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    if(!$this->db->insert($tbl_other_change_auth, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_other_change_auth, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_other_change_auth, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-04
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_other_change_auth, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: add_auth_data()
  //  函數功能: 新增審核資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function add_auth_data($ocl_s_num) {
    $v = $this->input->post();
    $ocl_h = $v["ocl_h"];
    foreach ($ocl_h as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($ocl_h[$k_fd_name]);
      }
    }
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $data['ct_s_num'] = $ocl_h['ct_s_num'];
    $data['ocl_s_num'] = $ocl_s_num;
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    if(!$this->db->insert($tbl_other_change_auth, $data)) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: save_sw_auth()
  //  函數功能: 社工確認
  //  程式設計: Kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save_sw_auth($s_num) {
    $rtn_msg = 'ok';
    $s_num = (int)$this->db->escape_like_str($s_num);
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $data['sw_empno'] = $_SESSION['acc_s_num'];
    $data['sw_date']  = date('Y-m-d H:i:s');
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_other_change_auth, $data)) {
      $rtn_msg = $this->lang->line('cf_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_wo_auth()
  //  函數功能: 核銷人員確認
  //  程式設計: Kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save_wo_auth($s_num) {
    $rtn_msg = 'ok';
    $s_num = (int)$this->db->escape_like_str($s_num);
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $data['wo_empno'] = $_SESSION['acc_s_num'];
    $data['wo_date']  = date('Y-m-d H:i:s');
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_other_change_auth, $data)) {
      $rtn_msg = $this->lang->line('cf_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_me_auth()
  //  函數功能: 核銷人員確認
  //  程式設計: Kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save_me_auth($s_num) {
    $rtn_msg = 'ok';
    $s_num = (int)$this->db->escape_like_str($s_num);
    $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 異動單審核紀錄檔
    $data['me_empno'] = $_SESSION['acc_s_num'];
    $data['me_date']  = date('Y-m-d H:i:s');
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_other_change_auth, $data)) {
      $rtn_msg = $this->lang->line('cf_ng');
    }
    echo $rtn_msg;
    return;
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
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  private function _aes_fd($fd_tbl) {
    switch ($fd_tbl) {   
      case "service_case":       
        $encry_arr = $this->aes_fd1;
        $tbl = $this->zi_init->chk_tbl_no_lang('service_case');
      break;  
      case "clients":       
        $encry_arr = $this->aes_fd2;
        $tbl = $this->zi_init->chk_tbl_no_lang('clients');
      break;  
    }
    $aes_fd = "";
    foreach ($encry_arr as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
}
?>