<?php
class Clients_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public $aes_fd = array('ct01','ct02','ct03','ct05','ct06_telephone',
                         'ct06_homephone','ct08','ct09','ct10','ct11',
                         'ct12','ct13','ct14','ct15','ct70','ct71','ct72'); // 加密欄位

  public $chg_fd = array("ct00", "ct04", "ct21", "ct31", "ct34_go",
                         "ct34_go_sab", "ct34_fo", "ct35", "ct35_level", "ct35_type", 
                         "ct36", "ct37", "ct38_1", "ct38_2"); // 替換欄位

  public function __construct() {
    $this->load->database();
    $this->load->model('service_case_model'); // 服務資料
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_clients}.*,
                   if(sys_acc.acc_name is null, 
                      CONCAT(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}')), 
                      sys_acc.acc_name) as b_acc_name,
                   if(sys_acc2.acc_name is null, 
                      CONCAT(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}')), 
                      sys_acc2.acc_name) as e_acc_name,
                   CONCAT(AES_DECRYPT(sw3.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw3.sw02,'{$this->db_crypt_key2}')) as sw_name, 
                   case {$tbl_clients}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->_aes_fd()}
            from {$tbl_clients}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_clients}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_clients}.e_empno
            left join {$tbl_social_worker} sw on sw.s_num = {$tbl_clients}.b_empno
            left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_clients}.e_empno
            left join {$tbl_social_worker} sw3 on sw3.s_num = {$tbl_clients}.sw_s_num
            where {$tbl_clients}.d_date is null
                  and {$tbl_clients}.s_num = ?
            order by {$tbl_clients}.s_num desc
           ";
    // u_var_dump($sql);
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
  //  函數名稱: get_one_by_ct03()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_one_by_ct03($ct03) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工帳號

    $row = NULL;
    $sql = "select {$tbl_clients}.*,
                   if(sys_acc.acc_name is null, 
                      CONCAT(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}')), 
                      sys_acc.acc_name) as b_acc_name,
                   if(sys_acc2.acc_name is null, 
                      CONCAT(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}')), 
                      sys_acc2.acc_name) as e_acc_name,
                   case {$tbl_clients}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->_aes_fd()}
            from {$tbl_clients}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_clients}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_clients}.e_empno
            left join {$tbl_social_worker} sw on sw.s_num = {$tbl_clients}.b_empno
            left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_clients}.e_empno
            where {$tbl_clients}.d_date is null
                  and AES_DECRYPT({$tbl_clients}.ct03,'{$this->db_crypt_key2}') = ?
            order by {$tbl_clients}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($ct03));
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
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_all() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $data = NULL;
    $sql = "select {$tbl_clients}.*
                   {$this->_aes_fd()}
            from {$tbl_clients}
            where {$tbl_clients}.d_date is null
            order by {$tbl_clients}.s_num desc
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
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $data = NULL;
    $sql = "select {$tbl_clients}.*
                   {$this->_aes_fd()}
                  from {$tbl_clients}
                  where {$tbl_clients}.d_date is null
                        and {$tbl_clients}.is_available = 1
                  order by {$tbl_clients}.s_num desc
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
  //  函數名稱: get_one_identity_log()
  //  函數功能: 取得案主身分別異動資料
  //  程式設計: kiwi
  //  設計日期: 2023-02-02
  // **************************************************************************
  public function get_one_identity_log($cti_s_num) {
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料
    $row = NULL;
    $sql = "select {$tbl_clients_identity_log}.*
            from {$tbl_clients_identity_log}
            where {$tbl_clients_identity_log}.d_date is null
                  and {$tbl_clients_identity_log}.s_num = {$cti_s_num}
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_identity_log()
  //  函數功能: 取得案主身分別異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_identity_log($ct_s_num) {
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料
    $data = NULL;
    $sql = "select {$tbl_clients_identity_log}.*
            from {$tbl_clients_identity_log}
            where {$tbl_clients_identity_log}.d_date is null
                  and {$tbl_clients_identity_log}.ct_s_num = {$ct_s_num}
            order by {$tbl_clients_identity_log}.ct_il01 asc
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
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_identity_log_latest()
  //  函數功能: 取得案主最新身分別異動資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-06
  // **************************************************************************
  public function get_identity_log_latest($ct_s_num, $month_last_date) {
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料
    $tbl_other_change_log_identity = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 非餐食類異動-身分別異動
    $tbl_service_case_charge_amount = $this->zi_init->chk_tbl_no_lang('service_case_charge_amount'); // 收費金額歷程資料
    $row = NULL;
    $sql = "select {$tbl_clients_identity_log}.*
            from {$tbl_clients_identity_log}
            left join {$tbl_other_change_log_identity} on {$tbl_other_change_log_identity}.s_num = {$tbl_clients_identity_log}.ct_il03
            left join {$tbl_service_case_charge_amount} on {$tbl_service_case_charge_amount}.ct_s_num = {$tbl_other_change_log_identity}.ct_s_num
                                                           and {$tbl_service_case_charge_amount}.scca02 = {$tbl_other_change_log_identity}.ocl_i02_date
            where {$tbl_clients_identity_log}.d_date is null
                  and {$tbl_clients_identity_log}.ct_s_num = {$ct_s_num}
                  and {$tbl_clients_identity_log}.ct_il01 <= '{$month_last_date}'
            order by {$tbl_clients_identity_log}.ct_il01 desc, {$tbl_clients_identity_log}.b_date desc
            limit 0, 1
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
  //  函數名稱: get_all_with_route()
  //  函數功能: 取得案主資料，包括路線資訊
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_all_with_route() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線資料-檔身
    $data = NULL;
    $sql = "select {$tbl_clients}.s_num,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_clients}.ct03,
                   {$tbl_clients}.ct04,
                   {$tbl_clients}.ct05,
                   {$tbl_clients}.ct06_homephone,
                   {$tbl_clients}.ct06_telephone,
                   {$tbl_clients}.ct16,
                   {$tbl_clients}.ct17,
                   {$tbl_route_b}.reh_s_num
           {$this->_aes_fd()}
           from {$tbl_clients}
           left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_clients}.s_num 
           where {$tbl_clients}.d_date is null
                 and {$tbl_clients}.is_available = 1
                 and {$tbl_route_b}.d_date is null
           order by {$tbl_clients}.s_num desc
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
  //  函數名稱: get_all_api()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_all_api($sw_s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $data = NULL;
    $i = 1;
    $where = '';
    $sw_ra03_str = '';
    $sw_ra_row = $this->social_worker_model->get_ra($sw_s_num);
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
    
    $sql = "select {$tbl_clients}.s_num,
                   {$tbl_clients}.bn_s_num,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_clients}.ct03,
                   {$tbl_clients}.ct04,
                   {$tbl_clients}.ct05,
                   {$tbl_clients}.ct06_homephone,
                   {$tbl_clients}.ct06_telephone,
                   {$tbl_clients}.ct16,
                   {$tbl_clients}.ct17
                   {$this->_aes_fd()}
            from {$tbl_clients}
            where {$tbl_clients}.d_date is null
                  and {$tbl_clients}.is_available = 1
                  {$where}
            order by {$tbl_clients}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $data = $rs->result_array();
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_all_by_sw_s_num()
  //  函數功能: 取得社工負責的案主
  //  程式設計: kiwi
  //  設計日期: 2024-01-24
  // **************************************************************************
  public function get_all_by_sw_s_num($sw_s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料

    $data = NULL;
    $sql = "select {$tbl_clients}.*
                   {$this->_aes_fd()}
           from {$tbl_clients}
           where {$tbl_clients}.d_date is null
                 and {$tbl_clients}.is_available = 1
                 and {$tbl_clients}.sw_s_num = ?
           order by {$tbl_clients}.s_num desc
          ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sw_s_num));
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
  //  函數名稱: get_all_sw_by_ct_s_num()
  //  函數功能: 取得歷任社工資料
  //  程式設計: kiwi
  //  設計日期: 2024-01-24
  // **************************************************************************
  public function get_all_sw_by_ct_s_num($ct_s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_clients_principal_social_worker = $this->zi_init->chk_tbl_no_lang('clients_principal_social_worker'); // 主責社工服務紀錄
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工帳號

    $data = NULL;
    $sql = "select {$tbl_clients_principal_social_worker}.s_num,
                   {$tbl_clients_principal_social_worker}.cpsw01_begin,
                   {$tbl_clients_principal_social_worker}.cpsw01_end,
                   CONCAT(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}')) as sw_name
                   {$this->_aes_fd()}
            from {$tbl_clients_principal_social_worker}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_clients_principal_social_worker}.ct_s_num
            left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_clients_principal_social_worker}.sw_s_num
            where {$tbl_clients_principal_social_worker}.d_date is null
                  and {$tbl_clients_principal_social_worker}.ct_s_num = ?
            order by {$tbl_clients_principal_social_worker}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($ct_s_num));
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
  //  函數名稱: get_download2_data()
  //  函數功能: 查詢案主下載資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-05
  // **************************************************************************
  public function get_download2_data() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案資料
    $where = " {$tbl_clients}.d_date is null 
               and {$tbl_clients}.is_available = 1
               and {$tbl_service_case}.d_date is null
             ";
    $get_data = $this->input->get();
    
    if(!empty($get_data['que_ct_name'])) { // 案主姓名
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_ct_name}%'";
    }

    if(!empty($get_data['que_sec03'])) { // 是否結案
      $que_sec03 = $get_data['que_sec03'];
      $que_sec03 = $this->db->escape_like_str($que_sec03);
      if('N' == $que_sec03) {
        $where .= " and {$tbl_service_case}.sec03 is null  /* 服務結束日 */ ";
      }
      else {
        $where .= " and {$tbl_service_case}.sec03 is not null  /* 服務結束日 */ ";
      }
    }

    if(!empty($get_data['que_sec05'])) { // 經費來源
      $que_sec05 = $get_data['que_sec05'];
      $que_sec05 = $this->db->escape_like_str($que_sec05);
      $where .= " and {$tbl_service_case}.sec05 = '{$que_sec05}'";
    }

    $sql = "select {$tbl_clients}.*,
                   {$tbl_service_case}.s_num as sec_s_num,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec02,
                   {$tbl_service_case}.sec03,
                   {$tbl_service_case}.sec04,
                   {$tbl_service_case}.sec05,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as reh_type
                   {$this->_aes_fd()}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num
            where {$where}
            order by {$tbl_clients}.ct14
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      array_push($this->chg_fd, 'sec01', 'sec04', 'sec05');
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v) {
          $data[$row->sec_s_num][$fd_name] = $row->$fd_name;

          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->sec_s_num][$fd_name] = $fd_val;

          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->sec_s_num][$fd_name] = $fd_val;
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
    $today = date("Y-m-d");
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工帳號
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料
    $where = " {$tbl_clients}.d_date is null 
               and {$tbl_clients_identity_log}.ct_il01 = (SELECT MAX({$tbl_clients_identity_log}.ct_il01) 
                                                          FROM {$tbl_clients_identity_log} 
                                                          WHERE {$tbl_clients_identity_log}.d_date is null
                                                                and {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
                                                                and {$tbl_clients_identity_log}.ct_il01 <= '{$today}')
             ";
    $order = " {$tbl_clients}.ct_case_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and (concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */                       
                       or AES_DECRYPT({$tbl_clients}.ct03,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主身分證 */                       
                       or AES_DECRYPT({$tbl_clients}.ct05,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主生日 */                                           
                       or AES_DECRYPT({$tbl_clients}.ct12,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主聯絡區號 */                                                
                       or AES_DECRYPT({$tbl_clients}.ct13,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主連絡-城市 */                                                
                       or AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主連絡-區 */                                                
                       or AES_DECRYPT({$tbl_clients}.ct15,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主連絡-地址 */                                                           
                       or {$tbl_clients}.ct_case_num like '%{$que_str}%' /* 案號 */                                              
                       or {$tbl_clients}.ct16 like binary '%{$que_str}%' /* 案主家緯度(由督導新增及更改) */                                              
                       or {$tbl_clients}.ct17 like binary '%{$que_str}%' /* 案主家經度(由督導新增及更改) */                                              
                       or {$tbl_clients}.ct99 like binary '%{$que_str}%' /* 備註 */
                      )
                ";
    }
    
    if(!empty($get_data['que_ct_case_num'])) { // 案號
      $que_ct_case_num = $get_data['que_ct_case_num'];
      $que_ct_case_num = $this->db->escape_like_str($que_ct_case_num);
      $where .= " and {$tbl_clients}.ct_case_num like '%{$que_ct_case_num}%'  /* 純OT */ ";
    }
    
    if(!empty($get_data['que_sw_s_num'])) { // 主責社工
      $que_sw_s_num = implode(",", $get_data['que_sw_s_num']);
      $que_sw_s_num = $this->db->escape_like_str($que_sw_s_num);
      $where .= " and {$tbl_clients}.sw_s_num in ({$que_sw_s_num})  /* 主責社工 */ ";
    }

    if(!empty($get_data['que_ct00'])) { // 純OT
      $que_ct00 = $get_data['que_ct00'];
      $que_ct00 = $this->db->escape_like_str($que_ct00);
      $where .= " and {$tbl_clients}.ct00 = '{$que_ct00}'  /* 純OT */ ";
    }

    if(!empty($get_data['que_ct31'])) { // 居住狀況
      $que_ct31 = implode(",", $get_data['que_ct31']);
      $que_ct31 = $this->db->escape_like_str($que_ct31);
      $where .= " and {$tbl_clients}.ct31 in ({$que_ct31})  /* CMS等級 */ ";
    }

    if(!empty($get_data['que_ct34_go'])) { // 福利身分別
      $que_ct34_go = implode(",", $get_data['que_ct34_go']);
      $que_ct34_go = $this->db->escape_like_str($que_ct34_go);
      $where .= " and {$tbl_clients_identity_log}.ct_il02 in ({$que_ct34_go})  /* 福利身分別 */ ";
    }

    if(!empty($get_data['que_ct37'])) { // CMS等級
      $que_ct37 = implode(",", $get_data['que_ct37']);
      $que_ct37 = $this->db->escape_like_str($que_ct37);
      $where .= " and {$tbl_clients}.ct37 in ({$que_ct37})  /* CMS等級 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_clients}.s_num
                from {$tbl_clients}
                left join {$tbl_clients_identity_log} on {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
                where $where
                group by {$tbl_clients}.s_num
                order by {$tbl_clients}.s_num
               ";
    // u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $total_row_cnt = $rs_cnt->num_rows();

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_clients}.s_num
                from {$tbl_clients}
                left join {$tbl_clients_identity_log} on {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
                where {$where}
                      and {$tbl_clients}.is_available = 1
                group by {$tbl_clients}.s_num
                order by {$tbl_clients}.s_num
              ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $is_available_1_row_cnt = $rs_cnt->num_rows();

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_clients}.s_num
                from {$tbl_clients}
                left join {$tbl_clients_identity_log} on {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
                where {$where}
                      and {$tbl_clients}.is_available = 0
                group by {$tbl_clients}.s_num
                order by {$tbl_clients}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $is_available_0_row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";

    $sql = "select {$tbl_clients}.*,
                   CONCAT(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}')) as sw_name, 
                   {$tbl_clients_identity_log}.ct_il02 as ct34_go
                   {$this->_aes_fd()}
            from {$tbl_clients}
            left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_clients}.sw_s_num
            left join {$tbl_clients_identity_log} on {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
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

    return(array($data, $total_row_cnt, $is_available_1_row_cnt, $is_available_0_row_cnt));
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

    if(!empty($data['sw_s_num'])) {
      $data['sw_b_date'] = date('Y-m-d');
    }

    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    if(!$this->db->insert($tbl_clients, $data)) {
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
      $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料
      if(!$this->db->insert($tbl_clients_identity_log, $ct_identity)) {
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
    $clients_row = $this->get_one($data['s_num']);

    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    } 

    $chk_sw_s_num = false;
    if($clients_row->sw_s_num != $data['sw_s_num']) {
      $chk_sw_s_num = true;
      $data['sw_b_date'] = date("Y-m-d");
    }

    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_clients, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    else {
      if($chk_sw_s_num) {
        $upd_sw_data['ct_s_num'] = $data['s_num'];
        $upd_sw_data['sw_s_num'] = $data['sw_s_num'];
        $upd_sw_data['cpsw01_begin'] = $clients_row->sw_b_date;
        $upd_sw_data['cpsw01_end'] = date("Y-m-d");
        $tbl_clients_principal_social_worker = $this->zi_init->chk_tbl_no_lang('clients_principal_social_worker'); // 主責社工服務紀錄
        if(!$this->db->insert($tbl_clients_principal_social_worker, $upd_sw_data)) {
          $rtn_msg = $this->lang->line('upd_ng');
        }
      }
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
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_clients, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd_sw()
  //  函數功能: 儲存主責社工更新資料
  //  程式設計: kiwi
  //  設計日期: 2024-01-24
  // **************************************************************************
  public function save_upd_sw() {
    $rtn_msg = 'ok';
    $post_data = $this->input->post();

    $clients_arr = $this->get_all_by_sw_s_num($post_data['old_sw_s_num']);
    if(empty($clients_arr)) {
      echo "查無該社工負責的案主!!";
      return;
    }

    $ct_data = NULL;
    foreach ($clients_arr as $k => $v) {
      $ct_data[$k]['b_empno'] = $_SESSION['acc_s_num'];
      $ct_data[$k]['b_date'] = date('Y-m-d H:i:s');
      $ct_data[$k]['ct_s_num'] = $v['s_num'];
      $ct_data[$k]['sw_s_num'] = $post_data['old_sw_s_num'];
      $ct_data[$k]['cpsw01_begin'] = $v['sw_b_date'];
      $ct_data[$k]['cpsw01_end'] = date("Y-m-d H:i:s");
    }

    if(empty($ct_data)) {
      echo "查無該社工負責的案主!!";
      return;
    }

    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_clients_principal_social_worker = $this->zi_init->chk_tbl_no_lang('clients_principal_social_worker'); // 主責社工服務紀錄

    if(!$this->db->insert_batch($tbl_clients_principal_social_worker, $ct_data)) {
      echo $this->lang->line('upd_ng');
      return;
    }
    else {
      $data['e_empno'] = $_SESSION['acc_s_num'];
      $data['e_date'] = date('Y-m-d H:i:s');
      $data['sw_b_date'] = date('Y-m-d');
      $data['sw_s_num'] = $post_data['new_sw_s_num'];
      $this->db->where('sw_s_num', $post_data['old_sw_s_num']);
      if(!$this->db->update($tbl_clients, $data)) {
        echo $this->lang->line('upd_ng');
        return;      
      }
    }

    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_convert()
  //  函數功能: 將資料轉為正式資料
  //  程式設計: kiwi
  //  設計日期: 2023-08-23
  // **************************************************************************
  public function save_convert($client_data) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料暫存檔
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料

    $chk_row = $this->clients_model->get_one_by_ct03($client_data['ct03']);
    foreach ($client_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($client_data[$k_fd_name]);
      }
    }
     
    if(empty($chk_row)) {
      // 如果是新增資料的話，要新增一筆政府身分別到identity_log
      $client_data['b_empno'] = $_SESSION['acc_s_num'];
      $client_data['b_date'] = date('Y-m-d H:i:s');
      $client_data['sw_b_date'] = date('Y-m-d');
      $client_data['sw_s_num'] = $_SESSION['acc_s_num'];
      if(!$this->db->insert($tbl_clients, $client_data)) {
        return false;
      }
      $ct_identity['b_empno'] = $_SESSION['acc_s_num'];
      $ct_identity['b_date'] = date('Y-m-d H:i:s');
      $ct_identity['ct_s_num'] = $this->db->insert_id();
      $ct_identity['ct_il01'] = date('Y-m-d H:i:s');
      $ct_identity['ct_il02'] = $client_data['ct34_go'];
      if(!$this->db->insert($tbl_clients_identity_log, $ct_identity)) {
        return false;
      }
    }
    else {
      $ct_s_num = $chk_row->s_num;
      $ct34_go = $client_data['ct34_go'];
      unset($client_data['ct34_go']);
      unset($client_data['ct_s_num']);
      $client_data['e_empno'] = $_SESSION['acc_s_num'];
      $client_data['e_date'] = date('Y-m-d H:i:s');
      $this->db->where('s_num', $ct_s_num);
      if(!$this->db->update($tbl_clients, $client_data)) {
        return false;
      }

      $month_last_date = date("Y-m-t");
      $latest_identity_row = $this->get_identity_log_latest($ct_s_num, $month_last_date);
      if(!empty($latest_identity_row)) {
        if($latest_identity_row->ct_il02 != $ct34_go) {
          $ct_identity['b_empno'] = $_SESSION['acc_s_num'];
          $ct_identity['b_date'] = date('Y-m-d H:i:s');
          $ct_identity['ct_s_num'] = $ct_s_num;
          $ct_identity['ct_il01'] = date('Y-m-d H:i:s');
          $ct_identity['ct_il02'] = $ct34_go;
          if(!$this->db->insert($tbl_clients_identity_log, $ct_identity)) {
            return false;
          }
        }
      }
    }

    // 處理完成後，立即刪除
    // $tbl_client_import = $this->zi_init->chk_tbl_no_lang('client_import'); // 案主資料暫存檔
    // $this->db->where('s_num', $s_num);
    // if(!$this->db->delete($tbl_client_import)) {
    //   $rtn_msg = $this->lang->line('convert_ng'); // 刪除失敗
    // }

    return true;
  }
  // **************************************************************************
  //  函數名稱: save_api_add()
  //  函數功能: 新增儲存資料(api用)
  //  程式設計: kiwi
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function save_api_add($ct94 , $sw_s_num) {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
    $data['b_empno'] = $sw_s_num;
    $data['b_date'] = date('Y-m-d H:i:s');     
    $data['ct94'] = $ct94;
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    if(!$this->db->insert($tbl_clients, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_api_upd()
  //  函數功能: 修改儲存資料(api用)
  //  程式設計: kiwi
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function save_api_upd($sw_s_num) {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
    $data['e_empno'] = $sw_s_num;
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_clients, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd_gps_by_api()
  //  函數功能: 修改儲存資料(api用)
  //  程式設計: kiwi
  //  設計日期: 2021-09-17
  // **************************************************************************
  public function save_upd_gps_by_api($dp_s_num) {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_clients, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
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
    $service_case_row = $this->service_case_model->get_que_by_ct($v['s_num'], 0);
    // u_var_dump($service_case_row);
    if(NULL != $service_case_row){
      $rtn_msg = '刪除失敗!!目前有服務案正在進行中!!';
    }else{
      $data['d_empno'] = $_SESSION['acc_s_num'];
      $data['d_date']  = date('Y-m-d H:i:s');
      $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
      $this->db->where('s_num', $v['s_num']);
      if(!$this->db->update($tbl_clients, $data)) {
        // $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
        $rtn_msg = "刪除失敗!!";
      }
    }
    
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del_identity()
  //  函數功能: 刪除案主身分別資料資料
  //  程式設計: kiwi
  //  設計日期: 2023-01-25(大年初四接神囉!!!)
  // **************************************************************************
  public function del_identity() {
    $data = NULL;
    $v = $this->input->post();
    $cti_row = $this->get_one_identity_log($v['s_num']);

    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別資料
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_clients_identity_log, $data)) {
      die($this->lang->line('del_ng')); // 刪除失敗
    }
    else {
      // 刪除身分別異動資料 Begin //
      $ocl_i_row = $this->other_change_log_h_model->get_identity_by_s_num($cti_row->ct_il03);
      if(!empty($ocl_i_row)) {
        // 1.將案主的付費方式還原成異動前的
        $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案服務
        $this->db->where('ct_s_num', $cti_row->ct_s_num);
        $this->db->where('d_date is null', null, false);
        if(!$this->db->update($tbl_service_case, array('sec09' => $ocl_i_row->ocl_sec09_before))) {
          die($this->lang->line('del_ng')); // 刪除失敗
        }
        // 2.刪除身分別異動資料
        $tbl_other_change_log_identity = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 非餐食類異動-身分別異動
        $this->db->where('ocl_s_num', $ocl_i_row->ocl_s_num);
        if(!$this->db->update($tbl_other_change_log_identity, $data)) {
          die($this->lang->line('del_ng')); // 刪除失敗
        }
        // 3.刪除身分別異動資料
        $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
        $this->db->where('s_num', $ocl_i_row->ocl_s_num);
        if(!$this->db->update($tbl_other_change_log_h, $data)) {
          $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
        }
      }
      // 刪除身分別異動資料 End //
    }
    return $rtn_msg;
  }
  // **************************************************************************
  //  函數名稱: que_ct()
  //  函數功能: 取得案主資料
  //  程式設計: kiwi
  //  設計日期: 2020-01-31
  // **************************************************************************
  public function que_ct() {
    $data = NULL;
    $source = '';
    $v = $this->input->post();
    $que = $this->db->escape_like_str($v['q']);
    if(isset($v['source'])) {
      $source = $v['source'];
    }
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 客戶資料
    $where = "{$tbl_clients}.d_date is null
                       and {$tbl_clients}.is_available = 1
                      ";
    if('' <> $que) {
      $where .= " and (concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que}%'
                       or AES_DECRYPT({$tbl_clients}.ct03,'{$this->db_crypt_key2}') like '%{$que}%'
                       )
                    ";
    }

    $sql = "select {$tbl_clients}.s_num,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_clients}.ct03,
                   {$tbl_clients}.ct34_go
                   {$this->_aes_fd()}
            from {$tbl_clients}
            where {$where}  
            order by {$tbl_clients}.ct01
           ";

    //u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row) {
        $ct34_go_str = '';
        if(NULL != $source) {
          $month_last_date = date("Y-m-t");
          $latest_identity_row = $this->get_identity_log_latest($row->s_num, $month_last_date);
          if(NULL != $latest_identity_row) {
            $row->ct34_go = $latest_identity_row->ct_il02;
          }
          if(NULL != $row->ct34_go) {
            $ct34_go_str = ";;".hlp_opt_setup("ct34_go", $row->ct34_go);
          }
        }
        $data[] = "{$row->ct01}{$row->ct02};;{$row->ct03};;{$row->s_num}{$ct34_go_str}";
      }
    }
    echo json_encode($data);
    return;
  }
  // **************************************************************************
  //  函數名稱: reh_que_ct()
  //  函數功能: 取得案主資料(路徑資料用)
  //  程式設計: kiwi
  //  設計日期: 2020-12-21
  // **************************************************************************
  public function reh_que_ct() {
    $data = NULL;
    $get_data = $this->input->get();
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 客戶資料
    $where = "{$tbl_clients}.d_date is null
              and {$tbl_clients}.is_available = 1
             ";
    if(isset($get_data['que_ct_name'])) {
      $que_ct_name = $this->db->escape_like_str($get_data['que_ct_name']);
      $where .= " and concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_ct_name}%'";
    }

    if(isset($get_data['que_ct03'])) {
      $que_ct03 = $this->db->escape_like_str($get_data['que_ct03']);
      $where .= " and AES_DECRYPT({$tbl_clients}.ct03, '{$this->db_crypt_key2}') like '%{$que_ct03}%'";
    }

    if(isset($get_data['ct_s_num_str']) and NULL != $get_data['ct_s_num_str']) {
      $ct_s_num_str = $this->db->escape_like_str($get_data['ct_s_num_str']);
      $where .= " and {$tbl_clients}.s_num not in({$ct_s_num_str})";
    }

    $sql = "select {$tbl_clients}.s_num,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_clients}.ct03,
                   {$tbl_clients}.ct05
                   {$this->_aes_fd()}
            from {$tbl_clients}
            where {$where}  
            order by {$tbl_clients}.ct01
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
  //  函數名稱: que_client_data()
  //  函數功能: 查詢案主資料(JSON)
  //  程式設計: kiwi
  //  設計日期: 2021-06-04
  // **************************************************************************
  public function que_client_data() {
    $v = $this->input->post();
    switch ($v['que_type']) {   
      case 1:       
        $fd_name = "ct34_go";   
      break;  
      case 2:       
        $fd_name = "ct37";   
      break; 
    }
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $row = NULL;
    $sql = "select {$tbl_clients}.{$fd_name}
            from {$tbl_clients}
            where {$tbl_clients}.d_date is null
                  and {$tbl_clients}.s_num = {$v['ct_s_num']}
            order by {$tbl_clients}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
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
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_clients}.*
                   
            from {$tbl_clients}
            where {$tbl_clients}.d_date is null
                  and {$tbl_clients}.fd_name = ?
            order by {$tbl_clients}.s_num desc
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
  //  函數名稱: chk_ct03()
  //  函數功能: 確認案主身分證是否重複
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function chk_ct03() {
    $v = $this->input->post();
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $row = NULL;
    $sql = "select {$tbl_clients}.*
            from {$tbl_clients}
            where {$tbl_clients}.d_date is null
                  and AES_DECRYPT({$tbl_clients}.ct03,'{$this->db_crypt_key2}') = ?
            order by {$tbl_clients}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($v['ct03']));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_all_is_available_by_ct34_go()
  //  函數功能: 取得所有ct34_go不是一般戶資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_all_is_available_by_ct34_go($date) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別資料
    $data = NULL;
    $sql = "select {$tbl_clients}.*,
                   {$tbl_clients_identity_log}.ct_il01,
                   {$tbl_clients_identity_log}.ct_il02
                   {$this->_aes_fd()}
                  from {$tbl_clients}
                  left join {$tbl_clients_identity_log} on {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
                  where {$tbl_clients}.d_date is null
                        and {$tbl_clients_identity_log}.d_date is null
                        and {$tbl_clients}.is_available = 1
                        and {$tbl_clients_identity_log}.is_available = 1
                        and {$tbl_clients_identity_log}.ct_il01 = (
                          select max({$tbl_clients_identity_log}.ct_il01)
                          from {$tbl_clients_identity_log}
                          where {$tbl_clients_identity_log}.is_available = 1
                                and {$tbl_clients_identity_log}.d_date is null 
                                and {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
                                and {$tbl_clients_identity_log}.ct_il01 <= '{$date}')
                  group by {$tbl_clients}.s_num
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
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function _aes_fd() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_clients}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
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
      case 'ct_il02':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("ct34_go", $fd_val);      
        break;
      case 'sec01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'sec04':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
    }

    if('' != $fd_name_mask) {
      $fd_name = $fd_name_mask;
    }

    return(array($fd_name, $fd_val));
  }
}
?>