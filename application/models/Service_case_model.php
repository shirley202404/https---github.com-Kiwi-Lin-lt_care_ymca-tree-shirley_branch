<?php
class Service_case_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public $aes_fd1 = array('ct_name');  
  public $aes_fd2 = array('ct01', 'ct02', 'ct06_telephone', 'ct11', 'ct12', 'ct13', 'ct14', 'ct15');
  public $replace_fd = array('sec01', 'sec04', 'sec05', 'sec08', 'sec09',);  
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
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 服務路徑資料-檔頭
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 服務路徑資料-檔身
    // $reh05 = 1;
    // if(3 == $sec04){
      // $reh05 = 2;
    // }
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_service_case}.*,
                   DATE_FORMAT({$tbl_service_case}.sec02 , '%Y-%m-%d') as sec02,
                   DATE_FORMAT({$tbl_service_case}.sec03, '%Y-%m-%d') as sec03,
                   case {$tbl_service_case}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '2'
                     when '3' then '2'
                   end as sec04_time,
                   IF(sys_acc.acc_name is null ,
                     concat(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc.acc_name
                   ) as b_acc_name,
                   IF(sys_acc2.acc_name is null ,
                     concat(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc2.acc_name
                   ) as e_acc_name,
                   {$tbl_route_h}.s_num as reh_s_num
                  {$this->_aes_fd('service_case')}
                  {$this->_aes_fd('clients')}
                  from {$tbl_service_case}
                  left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
                  left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_service_case}.b_empno
                  left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_service_case}.e_empno
                  left join {$tbl_social_worker} sw on sw.s_num = {$tbl_service_case}.b_empno
                  left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_service_case}.e_empno
                  left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_service_case}.ct_s_num
                  left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_route_b}.reh_s_num and if({$tbl_service_case}.sec04=3, '2', '1') = {$tbl_route_h}.reh05
                  where {$tbl_service_case}.d_date is null
                        and {$tbl_service_case}.s_num = ?
                  order by {$tbl_service_case}.s_num desc
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd2 as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
      foreach ($this->replace_fd as $fd_name => $v) {
        list($fd_name, $fd_val) = $this->_replace_text($row, $v); // 字串替換
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
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_service_case}.*
                   
            from {$tbl_service_case}
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.fd_name = ?
            order by {$tbl_service_case}.s_num desc
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
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $data = NULL;
    $sql = "select {$tbl_service_case}.*
                   {$this->_aes_fd('service_case')}
            from {$tbl_service_case}
            where {$tbl_service_case}.d_date is null
            order by {$tbl_service_case}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v) {
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_que_by_ct()
  //  函數功能: 取得案主所有開案服務資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_que_by_ct($s_num, $sec03=NULL) {
    $where = '';
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    if(isset($_POST['produce_date'])) {
      $where .= " and {$tbl_service_case}.sec02 <= {$_POST['produce_date']}";    
    }
    if(0 == $sec03){
      $where .= " and {$tbl_service_case}.sec03 is null";
    }
    $data = NULL;
    $sql = "select {$tbl_service_case}.*
            from {$tbl_service_case}
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.ct_s_num = ?
                  $where
            order by {$tbl_service_case}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_by_sec01()
  //  函數功能: 由服務現況取得尚未結案的資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-06
  // **************************************************************************
  public function get_all_by_sec01($subsidy_month, $rpt_type, $last_date, $sec01) {    
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    
    list($year, $month) = explode("-", $subsidy_month);
    
    $data = NULL;
    $sql = "select {$tbl_service_case}.*,
                   {$tbl_clients}.s_num as ct_s_num,
                   {$tbl_clients}.ct34_go,
                   concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) as ct_name,
                   AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') as ct14
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num 
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec01 = {$sec01}
                  and {$tbl_service_case}.sec02 <= '{$last_date}'
                  and (YEAR({$tbl_service_case}.sec03) >= '{$year}'
                       and MONTH({$tbl_service_case}.sec03) >= '{$month}'
                       or {$tbl_service_case}.sec03 is null)
            order by {$tbl_service_case}.s_num desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }

  // **************************************************************************
  //  函數名稱: get_all_by_sec03()
  //  函數功能: 取得尚未結案的資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function get_all_by_sec03($produce_date = NULL) {
    if(NULL == $produce_date) {
      $produce_date = $this->input->post('produce_date');
    }
    
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $data = NULL;
    $sql = "select {$tbl_service_case}.*
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num 
            where ({$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec02 <= '{$produce_date}')
                  and {$tbl_clients}.is_available = 1
            order by {$tbl_service_case}.s_num desc
           ";
    // u_var_dump($sql);
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
  //  函數名稱: get_all_by_sec04()
  //  函數功能: 取得尚未結案的資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-06
  // **************************************************************************
  public function get_all_by_sec04($produce_date) {    
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $data = NULL;
    $sql = "select {$tbl_service_case}.*,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as reh_type
                   {$this->_aes_fd('service_case')}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num 
            where {$tbl_service_case}.d_date is null
                  and ({$tbl_service_case}.sec03 >= '{$produce_date}'
                  or {$tbl_service_case}.sec03 is null)
            order by {$tbl_service_case}.s_num desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v) {
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }

  // **************************************************************************
  //  函數名稱: get_sec_by_ct34_go()
  //  函數功能: 用身分別來查詢案主資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-15
  // **************************************************************************
  public function get_sec_by_ct34_go($ct34_go, $sec01, $subsidy_month, $last_date) {
    $where = '';
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身份別資料
    if(0 != $ct34_go) {
      $where = " and {$tbl_clients_identity_log}.ct_il02 = {$ct34_go}
                 and {$tbl_clients_identity_log}.ct_il01 <= '{$last_date}'
               ";
    }
    $data = NULL;
    $sql = "select {$tbl_clients}.s_num as ct_s_num,
                   concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) as ct_name,
                   AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') as ct14,
                   case {$tbl_clients_identity_log}.ct_il02
                     when '1' then '一般戶'
                     when '2' then '低收三'
                     when '3' then '中低2.5'
                     when '4' then '中低1.5'
                     when '5' then '低收一'
                     when '6' then '低收二'
                     when '7' then '身 低收'
                     when '8' then '身 中低'
                     when '9' then '身 一般'
                     when '10' then '身補'
                   end as ct34_go_str,
                   {$tbl_clients_identity_log}.ct_il01,
                   {$tbl_clients_identity_log}.ct_il02,
                   {$tbl_service_case}.s_num,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec02,
                   {$tbl_service_case}.sec03,
                   {$tbl_service_case}.sec04,
                   {$tbl_service_case}.sec07,
                   {$tbl_service_case}.sec09,
                   {$tbl_service_case}.sec99
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num
            left join {$tbl_clients_identity_log} on {$tbl_clients_identity_log}.ct_s_num = {$tbl_service_case}.ct_s_num
            where {$tbl_clients}.d_date is null
                  {$where}
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_service_case}.sec01 = {$sec01}
                  and {$tbl_service_case}.sec09 not in (4 , 20)
                  and ({$tbl_service_case}.sec03 >= '{$subsidy_month}'
                       or {$tbl_service_case}.sec03 is null)
            order by {$tbl_clients}.ct14 desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    // u_var_dump($data);
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_sec_by_sec01()
  //  函數功能: 用服務類型來查詢案主資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-21
  // **************************************************************************
  public function get_sec_by_sec01($sec01, $sec05, $subsidy_month) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    
    $data = NULL;
    list($year, $month) = explode("-", $subsidy_month);

    $sql = "select {$tbl_clients}.s_num as ct_s_num,
                   {$tbl_clients}.ct04,
                   {$tbl_clients}.ct35_type,
                   {$tbl_clients}.ct35_level,
                   AES_DECRYPT({$tbl_clients}.ct01, '{$this->db_crypt_key2}') as ct01, 
                   AES_DECRYPT({$tbl_clients}.ct02, '{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct03, '{$this->db_crypt_key2}') as ct03,
                   AES_DECRYPT({$tbl_clients}.ct05, '{$this->db_crypt_key2}') as ct05,
                   AES_DECRYPT({$tbl_clients}.ct06_telephone, '{$this->db_crypt_key2}') as ct06_telephone,
                   AES_DECRYPT({$tbl_clients}.ct06_homephone, '{$this->db_crypt_key2}') as ct06_homephone,
                   AES_DECRYPT({$tbl_clients}.ct14, '{$this->db_crypt_key2}') as ct14,
                   AES_DECRYPT({$tbl_clients}.ct15, '{$this->db_crypt_key2}') as ct15,
                   {$tbl_service_case}.s_num,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec02,
                   {$tbl_service_case}.sec03,
                   {$tbl_service_case}.sec04,
                   {$tbl_service_case}.sec07,
                   {$tbl_service_case}.sec09,
                   {$tbl_service_case}.sec99,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '2'
                     when '3' then '2'
                   end as sec04_type
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num
            where {$tbl_clients}.d_date is null
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec01 = {$sec01}
                  and {$tbl_service_case}.sec05 = {$sec05}
                  and (YEAR({$tbl_service_case}.sec03) >= '{$year}'
                       and MONTH({$tbl_service_case}.sec03) >= '{$month}'
                       or {$tbl_service_case}.sec03 is null)
            order by {$tbl_clients}.ct14 desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    // u_var_dump($data);
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_sec_by_sec04()
  //  函數功能: 用服務類型來查詢案主資料
  //  程式設計: shirley
  //  設計日期: 2024-02-19
  // **************************************************************************
  public function get_sec_by_sec04($ct_s_num, $sec04) {
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $data = NULL;
    $sql = "select {$tbl_service_case}.*
            from {$tbl_service_case}
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec03 is null
                  and {$tbl_service_case}.ct_s_num = {$ct_s_num}
                  and {$tbl_service_case}.sec04 = '{$sec04}'
            order by {$tbl_service_case}.s_num desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    // u_var_dump($data);
    return $data;
  }

  // **************************************************************************
  //  函數名稱: get_unclosed_sec_by_sec01()
  //  函數功能: 用服務類型來取得目前尚未結案的服務案
  //  程式設計: kiwi
  //  設計日期: 2022-03-21
  // **************************************************************************
  public function get_unclosed_sec_by_sec01($sec01, $sec05) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    
    $data = NULL;
    $sql = "select {$tbl_clients}.s_num as ct_s_num,
                   AES_DECRYPT({$tbl_clients}.ct01, '{$this->db_crypt_key2}') as ct01, 
                   AES_DECRYPT({$tbl_clients}.ct02, '{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct03, '{$this->db_crypt_key2}') as ct03,
                   AES_DECRYPT({$tbl_clients}.ct05, '{$this->db_crypt_key2}') as ct05,
                   AES_DECRYPT({$tbl_clients}.ct06_telephone, '{$this->db_crypt_key2}') as ct06_telephone,
                   AES_DECRYPT({$tbl_clients}.ct06_homephone, '{$this->db_crypt_key2}') as ct06_homephone,
                   AES_DECRYPT({$tbl_clients}.ct14, '{$this->db_crypt_key2}') as ct14,
                   AES_DECRYPT({$tbl_clients}.ct15, '{$this->db_crypt_key2}') as ct15,
                   case {$tbl_clients}.ct04
                     when 'M' then '男'
                     when 'Y' then '女'
                   end as ct04_str,
                   {$tbl_clients}.ct35_type,
                   {$tbl_clients}.ct35_level,
                   {$tbl_service_case}.s_num,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec02,
                   {$tbl_service_case}.sec03,
                   {$tbl_service_case}.sec04,
                   {$tbl_service_case}.sec07,
                   {$tbl_service_case}.sec09,
                   {$tbl_service_case}.sec99,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '2'
                     when '3' then '2'
                   end as sec04_type
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num
            where {$tbl_clients}.d_date is null
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec01 = {$sec01}
                  and {$tbl_service_case}.sec05 = {$sec05}
                  and {$tbl_service_case}.sec03 is null
            order by {$tbl_clients}.ct14 desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    // u_var_dump($data);
    return $data;
  }

  // **************************************************************************
  //  函數名稱: get_all_by_ct_s_num()
  //  函數功能: 取得案主全部尚未結案的資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-22
  // **************************************************************************
  public function get_all_by_ct_s_num($ct_s_num, $q_date) {    
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $data = NULL;
    $sql = "select {$tbl_service_case}.*,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as reh_type,
                   case {$tbl_service_case}.sec05
                     when '1' then 'sab'
                     when '2' then 'hb'
                   end as sec05_str
                   {$this->_aes_fd('service_case')}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num 
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.ct_s_num = '{$ct_s_num}'
                  and ({$tbl_service_case}.sec03 >= '{$q_date}'
                  or {$tbl_service_case}.sec03 is null)
            order by {$tbl_service_case}.sec04 asc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
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
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $where = " {$tbl_service_case}.d_date is null ";
    $order = " {$tbl_service_case}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      // or AES_DECRYPT({$tbl_service_case}.ct_name,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主名稱 */           
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_service_case}.ct_s_num like '%{$que_str}%' /* tw_clients.s_num */      
                       or {$tbl_service_case}.sec01 like '%{$que_str}%' /* 服務名稱 */                       
                       or {$tbl_service_case}.sec02 like binary '%{$que_str}%' /* 服務開始日 */                       
                       or {$tbl_service_case}.sec03 like binary '%{$que_str}%' /* 服務結束日 */                       
                       or {$tbl_service_case}.sec04 like '%{$que_str}%' /* 服務類型：1=送餐(午);2=送餐(午晚);3=送餐(晚) */                       
                       or {$tbl_service_case}.sec05 like '%{$que_str}%' /* 經費來源：1=縣政府;2=自費;3=中央政府 */                       
                       or {$tbl_service_case}.sec06 like '%{$que_str}%' /* 服務內容 */                       
                       or {$tbl_service_case}.sec99 like '%{$que_str}%' /* 備註 */
                       or AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 備註 */
                       or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */ 
                      )
                ";
    }
     
    if(!empty($get_data['que_ct_name'])) { // 案主姓名
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and AES_DECRYPT({$tbl_service_case}.ct_name,'{$this->db_crypt_key2}') like '%{$que_str}%'  /* 案主姓名 */ ";
    }
    if(!empty($get_data['que_ct14'])) { // 區域
      $que_ct14 = $get_data['que_ct14'];
      $que_ct14 = $this->db->escape_like_str($que_ct14);
      $where .= " and AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') like '%{$que_ct14}%'  /* 區域 */ ";
    }
    if(!empty($get_data['que_sec01'])) { // 服務現況
      $que_sec01 = $get_data['que_sec01'];
      $que_sec01 = $this->db->escape_like_str($que_sec01);
      $where .= " and {$tbl_service_case}.sec01 = '{$que_sec01}'  /* 服務現況 */ ";
    }
    if(!empty($get_data['que_sec02'])) { // 服務開始日
      $que_sec02 = $get_data['que_sec02'];
      $que_sec02 = $this->db->escape_like_str($que_sec02);
      $where .= " and {$tbl_service_case}.sec02 = '{$que_sec02}'  /* 服務開始日 */ ";
    }
    if(!empty($get_data['que_sec03'])) { // 服務結束日
      $que_sec03 = $get_data['que_sec03'];
      $que_sec03 = $this->db->escape_like_str($que_sec03);
      if('N' == $que_sec03) {
        $where .= " and {$tbl_service_case}.sec03 is null  /* 服務結束日 */ ";
      }
      else {
        $where .= " and {$tbl_service_case}.sec03 is not null  /* 服務結束日 */ ";
      }
    }
    if(!empty($get_data['que_sec04'])) { // 服務類型：1=送餐(午);2=送餐(午晚);3=送餐(晚)
      $que_sec04 = $get_data['que_sec04'];
      $que_sec04 = $this->db->escape_like_str($que_sec04);
      $where .= " and {$tbl_service_case}.sec04 = '{$que_sec04}'  /* 服務類型：1=送餐(午);2=送餐(午晚);3=送餐(晚) */ ";
    }
    if(!empty($get_data['que_sec05'])) { // 經費來源：1=縣政府;2=自費;3=中央政府
      $que_sec05 = $get_data['que_sec05'];
      $que_sec05 = $this->db->escape_like_str($que_sec05);
      $where .= " and {$tbl_service_case}.sec05 = '{$que_sec05}'  /* 經費來源：1=縣政府;2=自費;3=中央政府 */ ";
    }
    if(!empty($get_data['que_sec09'])) { // 繳費方式
      $que_sec09 = $get_data['que_sec09'];
      $que_sec09 = $this->db->escape_like_str($que_sec09);
      $where .= " and {$tbl_service_case}.sec09 = '{$que_sec09}'";
    }
    if(!empty($get_data['que_s_num'])) { // 
      $que_s_num = $get_data['que_s_num'];
      $que_s_num = $this->db->escape_like_str($que_s_num);
      $where .= " and {$tbl_service_case}.s_num = '{$que_s_num}' ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_service_case}.s_num
                from {$tbl_service_case}
                left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
                where $where
                group by {$tbl_service_case}.s_num
                order by {$tbl_service_case}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_service_case}.s_num
                from {$tbl_service_case}
                left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
                where {$where}
                      and {$tbl_service_case}.sec03 is null
                group by {$tbl_service_case}.s_num
                order by {$tbl_service_case}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $sec03_n_row_cnt = $rs_cnt->num_rows();

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_service_case}.s_num
                from {$tbl_service_case}
                left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
                where {$where}
                      and {$tbl_service_case}.sec03 is not null
                group by {$tbl_service_case}.s_num
                order by {$tbl_service_case}.s_num
                ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $sec03_y_row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = " select {$tbl_service_case}.*,
                   case {$tbl_service_case}.sec05
                     when '1' then 'sab'
                     when '2' then 'hb'
                   end as sec05_en_str       
                  {$this->_aes_fd('service_case')}
                  {$this->_aes_fd('clients')}
                  from {$tbl_service_case}
                  left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
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
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return(array($data, $row_cnt, $sec03_n_row_cnt, $sec03_y_row_cnt));
  }
  // **************************************************************************
  //  函數名稱: get_upd_price_data()
  //  函數功能: 取得所有資料
  //  程式設計: shirley
  //  設計日期: 2024-02-23
  // **************************************************************************
  public function get_upd_price_data($v) {
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $data = NULL;
    $where = "";
    if(!empty($v['upd_sec04'])) {
      $where = " and {$tbl_service_case}.sec04 = '{$v['upd_sec04']}'";
    }
    if($v['upd_sec99'] == '★★') {
      $where .= " and {$tbl_service_case}.sec99 NOT LIKE '%★★素食% ESCAPE '!'";
    }else{
      $where .= " and {$tbl_meal_order}.mlo03 LIKE '%{$v['upd_sec99']}%' ESCAPE '!'";
    }
    
    $sql = "select {$tbl_service_case}.*
                   {$this->_aes_fd('service_case')}
            from {$tbl_service_case}
            left join {$tbl_meal_order} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec01 = '{$v['upd_sec01']}'
                  $where
            order by {$tbl_service_case}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v) {
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
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
    $sec_data['ct_s_num'] = $data['ct_s_num'];
    $sec_data['ct_name'] = $data['ct_name'];
    $sec_data['sec01'] = $data['sec01'];
    $sec_data['sec02'] = $data['sec02'];
    $sec_data['sec04'] = $data['sec04'];
    $sec_data['sec05'] = $data['sec05'];
    $sec_data['sec06'] = $data['sec06'];
    $sec_data['sec07'] = $data['sec07'];
    $sec_data['sec09'] = $data['sec09'];
    $sec_data['sec99'] = $data['sec99'];
    foreach ($sec_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($sec_data[$k_fd_name]);
      }
    }
    $sec_data['b_empno'] = $_SESSION['acc_s_num'];
    $sec_data['b_date'] = date('Y-m-d H:i:s');
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    if(!$this->db->insert($tbl_service_case, $sec_data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    else {
      $mil_h = $data['mil_h'];
      foreach ($mil_h as $k_fd_name => $v_data) {
        if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
          $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
          unset($mil_h[$k_fd_name]);
        }
      }
      
      $sec_s_num = $this->db->insert_id();
      $mil_h['b_empno'] = $_SESSION['acc_s_num'];
      $mil_h['b_date'] = date('Y-m-d H:i:s');
      $mil_h['sec_s_num'] = $this->db->insert_id();
      $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料-檔頭
      if(!$this->db->insert($tbl_meal_instruction_log_h, $mil_h)) {
        $rtn_msg = $this->lang->line('add_ng');
      }
      else {
        $mil_h_s_num = $this->db->insert_id();
        $mil02_arr = explode(",", $mil_h["mil02"]);
        if(NULL != $mil02_arr) {
          foreach ($mil02_arr as &$v) {
            switch ($v) {   
              case 1: // 餐點異動      
                $fd_type = $data['mil_m'];
                $fd_type["mil_m02"] = $mil_h['mil01'];
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_m'); // 餐點異動資料-檔身(餐點異動)
              break;
              case 2: // 代餐異動      
                $fd_type = $data['mil_mp'];
                $fd_type["mil_mp02"] = $mil_h['mil01'];
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_mp'); // 餐點異動資料-檔身(代餐異動)
              break;  
              case 3: // 停復餐
                $fd_type = $data['mil_s'];
                $fd_type["mil_s02"] = $mil_h['mil01'];
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 餐點異動資料-檔身(停復餐)
              break;  
              case 4: // 固定暫停      
                $fd_type = $data['mil_p'];
                $fd_type["mil_p02"] = $mil_h['mil01'];
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_p'); // 餐點異動資料-檔身(固定暫停)
              break;    
              case 5: // 自費     
                $fd_type = $data['mil_i'];
                $fd_type["mil_i02"] = $mil_h['mil01'];
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_i'); // 餐點異動資料-檔身(自費)
              break;   
            }
            $fd_type['b_empno'] = $_SESSION['acc_s_num'];
            $fd_type['b_date'] = date('Y-m-d H:i:s');
            $fd_type['mil_h_s_num'] = $mil_h_s_num;
            $fd_type['sec_s_num'] = $sec_s_num;
            if(!$this->db->insert($fd_tbl, $fd_type)) {
              $rtn_msg = $this->lang->line('add_ng');
            }
          } 
        }
        if(!$this->meal_instruction_auth_model->add_auth_data($mil_h_s_num , $sec_s_num)) {
          $rtn_msg = $this->lang->line('add_ng');
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
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    } 
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_service_case, $data)) {
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
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_service_case, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd_price()
  //  函數功能: 餐點價格更新
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function save_upd_price() {
    $rtn_msg = 'ok';
    $post_data = $this->input->post();
    $service_case_row = $this->get_sec_by_identity();
    
    if(!empty($service_case_row)) {
      $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
      $tbl_service_case_charge_amount = $this->zi_init->chk_tbl_no_lang('service_case_charge_amount'); // 收費金額歷程資料
      foreach ($service_case_row as $k => $v) {
        $this->db->where('s_num', $v['s_num']);
        if(!$this->db->update($tbl_service_case, array('sec07' => $post_data['upd_price']))) {
          $rtn_msg = $this->lang->line('upd_ng');
        }
        else{
          // 紀錄收費金額歷程
          $data['b_empno'] = $_SESSION['acc_s_num'];
          $data['b_date']  = date('Y-m-d H:i:s');
          $data['sec_s_num'] = $v['s_num'];
          $data['ct_s_num'] = $v['ct_s_num'];
          $data['scca01'] = $v['upd_price'];
          $data['scca02']  = date('Y-m-d');
          if(!$this->db->insert($tbl_service_case_charge_amount, $data)) {
            $rtn_msg = $this->lang->line('add_ng');
          }
        }
      }
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
    $data = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_service_case, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    return $rtn_msg;
  }
  // **************************************************************************
  //  函數名稱: over()
  //  函數功能: 結案
  //  程式設計: kiwi
  //  設計日期: 2021-04-09
  // **************************************************************************
  public function over() {
    $data = NULL;
    $data = $this->input->post();
    $rtn_msg = 'ok';
    $service_case_row = $this->get_one($data['s_num']);
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date']  = date('Y-m-d H:i:s');
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_service_case, $data)) {
      $rtn_msg = $this->lang->line('over_ng'); // 刪除失敗
    }
    // 確認該案主是否還有其他同餐別的服務案，如果沒有的話，自動將案主資料從路線裡面移除
    $service_case_row2 = $this->get_sec_by_sec04($service_case_row->ct_s_num, $service_case_row->sec04);
    if((NULL == $service_case_row2) && (NULL != $service_case_row->reh_s_num)){
      $route_b_data['d_empno'] = $_SESSION['acc_s_num'];
      $route_b_data['d_date']  = date('Y-m-d H:i:s');
      $tbl_service_case = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔身
      $this->db->where('ct_s_num', $service_case_row->ct_s_num);
      $this->db->where('reh_s_num', $service_case_row->reh_s_num);
      if(!$this->db->update($tbl_service_case, $route_b_data)) {
        $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
      }
    }
    return $rtn_msg;
  }
  // **************************************************************************
  //  函數名稱: over_cancel()
  //  函數功能: 結案取消
  //  程式設計: kiwi
  //  設計日期: 2022-01-24
  // **************************************************************************
  public function over_cancel() {
    $data = NULL;
    $data = $this->input->post();
    $rtn_msg = 'ok';
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date']  = date('Y-m-d H:i:s');
    $data['sec03']  = NULL;
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_service_case, $data)) {
      $rtn_msg = $this->lang->line('over_cancel_ng'); // 刪除失敗
    }
    return $rtn_msg;
  }
  // **************************************************************************
  //  函數名稱: que_sec()
  //  函數功能: 搜尋案主開案資料 
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function que_sec() {
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 會員基本資料檔
    $data = NULL;
    $ct_s_num = $this->db->escape_like_str($_POST['ct_s_num']);
    $where = "{$tbl_service_case}.d_date is null";
    $sql = "select {$tbl_service_case}.s_num , 
                   DATE_FORMAT({$tbl_service_case}.sec02 , '%Y-%m-%d') as sec02,
                   DATE_FORMAT({$tbl_service_case}.sec03, '%Y-%m-%d') as sec03,
                   {$tbl_service_case}.sec99,
                   case {$tbl_service_case}.sec04
                     when '1' then '送餐-(午餐)'
                     when '2' then '送餐-(中晚餐)'
                     when '3' then '送餐-(晚餐)'
                   end as sec04_str
            from {$tbl_service_case}
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec03 is null
                  and {$tbl_service_case}.ct_s_num = ?
           ";
    //  u_var_dump($sql);
    $rs = $this->db->query($sql,array($ct_s_num));
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
  //  函數名稱: que_client_service_data()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function que_client_service_data() {
    $v = $this->input->post();
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $row = NULL;
    $sql = "select {$tbl_service_case}.*
                  from {$tbl_service_case}
                  where {$tbl_service_case}.d_date is null
                        and {$tbl_service_case}.sec03 is null
                        and {$tbl_service_case}.ct_s_num = {$v['ct_s_num']}
                  order by {$tbl_service_case}.s_num desc
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    if($row != NULL) {
      echo json_encode($row->sec01);
    }
    else {
      echo json_encode($row);
    }
    return;
  }
  // **************************************************************************
  //  函數名稱: que_by_sec08()
  //  函數功能: 取得補異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function que_by_sec08() {   
    $v = $this->input->post();
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 會員基本資料檔
    $data = NULL;
    $where = "{$tbl_service_case}.d_date is null";
    $sql = "select {$tbl_service_case}.* , 
                   DATE_FORMAT({$tbl_service_case}.sec02 , '%Y-%m-%d') as sec02,
                   DATE_FORMAT({$tbl_service_case}.sec03, '%Y-%m-%d') as sec03
                   {$this->_aes_fd('service_case')}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num 
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.sec05 = 3
                  and {$tbl_service_case}.sec08 = 2
                  and ({$tbl_service_case}.sec02 like '%{$v['q']}%'
                  and {$tbl_clients}.is_available = 1
                  or {$tbl_service_case}.sec03 like '%{$v['q']}%')
                ";
    //  u_var_dump($sql);
    $rs = $this->db->query($sql,array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: reh_que_data_by_ct_s_num()
  //  函數功能: 取得補異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function reh_que_data_by_ct_s_num($ct_s_num, $reh05) {   
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 會員基本資料檔
    $where = "{$tbl_service_case}.d_date is null";
    if(1 == $reh05) {
      $where = " and ({$tbl_service_case}.sec04 = 1 
                      or {$tbl_service_case}.sec04 = 2
                )";
    }
    else {
      $where = " and {$tbl_service_case}.sec04 = 3";
    }
    $data = NULL;
    $sql = "select {$tbl_service_case}.*
                   {$this->_aes_fd('service_case')}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num 
            where {$tbl_service_case}.d_date is null
                  and {$tbl_service_case}.ct_s_num = {$ct_s_num}
                  and {$tbl_clients}.d_date is null
                  {$where}
                ";
    //  u_var_dump($sql);
    $rs = $this->db->query($sql,array($sql));
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
  //  函數名稱: que_by_pause()
  //  函數功能: 服務案固定暫停查詢
  //  程式設計: kiwi
  //  設計日期: 2022-01-28
  // **************************************************************************
  public function que_by_pause() {    
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $where = " {$tbl_service_case}.d_date is null ";
    $order = " {$tbl_service_case}.s_num desc ";

    $get_data = $this->input->get();
    if(!empty($get_data['que_ct_name'])) { // 案主姓
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and AES_DECRYPT({$tbl_service_case}.ct_name,'{$this->db_crypt_key2}') like '%{$que_ct_name}%'  /* 案主姓名 */ ";
    }
    if(!empty($get_data['que_ct14'])) { // 案主姓
      $que_ct14 = $get_data['que_ct14'];
      $que_ct14 = $this->db->escape_like_str($que_ct14);
      $where .= " and AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') like '%{$que_ct14}%'  /* 案主姓名 */ ";
    }
    if(!empty($get_data['que_sec01'])) { // 服務現況
      $que_sec01 = $get_data['que_sec01'];
      $que_sec01 = $this->db->escape_like_str($que_sec01);
      $where .= " and {$tbl_service_case}.sec01 = '{$que_sec01}'  /* 服務現況 */ ";
    }

    $data = NULL;
    $sql = " select {$tbl_service_case}.*
             {$this->_aes_fd('service_case')}
             {$this->_aes_fd('clients')}
             from {$tbl_service_case}
             left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
             where {$where}
                   and {$tbl_clients}.is_available = 1
                   and {$tbl_clients}.d_date is null
             order by {$order}
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name, $fd_val) = $this->_symbol_text($row, $fd_name);
          list($fd_name, $fd_val) = $this->_replace_text($row, $fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_download_data()
  //  函數功能: 查詢蓋章表下載資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-13
  // **************************************************************************
  public function get_download_data() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔身
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案資料

    // 自籌和自費戶沒有蓋章表
    $today = date("Y-m-d");
    $data = NULL;
    $left_join = '';
    $where = " {$tbl_service_case}.d_date is null
               and {$tbl_service_case}.sec05 not in (3, 4, 5)
             ";

    $post_data = $this->input->post();
    if(!empty($post_data['que1_ct_name'])) { // 案主姓名
      $que_ct_name = $post_data['que1_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_ct_name}%'";
    }

    if(!empty($post_data['que1_sec04'])) { // 服務類型
      $que_sec04 = $post_data['que1_sec04'];
      $que_sec04 = $this->db->escape_like_str($que_sec04);
      $where .= " and {$tbl_service_case}.sec04 = '{$que_sec04}'";
    }

    if(!empty($post_data['que1_sec05'])) { // 經費來源
      $que_sec05 = $post_data['que1_sec05'];
      $que_sec05 = $this->db->escape_like_str($que_sec05);
      $where .= " and {$tbl_service_case}.sec05 = '{$que_sec05}'";
    }

    if(!empty($post_data['que1_reh_s_num'])) { // 路線資料
      $reh_s_num = $post_data['que1_reh_s_num'];
      $reh_s_num = $this->db->escape_like_str($reh_s_num);
      $where .= " and {$tbl_route_h}.s_num = '{$reh_s_num}'";
      $left_join = " left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_service_case}.ct_s_num
                     left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_route_b}.reh_s_num
                   ";
    }

    $sql = "select {$tbl_service_case}.*,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as sec04_time,
                   case {$tbl_service_case}.sec05
                     when '1' then 'sab'
                     when '2' then 'hb'
                   end as sec05_en_str,
                   {$tbl_clients}.ct38_1,
                   {$tbl_clients}.ct38_2,
                   {$tbl_clients}.ct38_memo
                   {$this->_aes_fd('service_case')}
                   {$this->_aes_fd('clients')}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
            {$left_join}
            where {$where}
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_clients}.d_date is null
                  and ({$tbl_service_case}.sec03 >= '{$today}'
                       or {$tbl_service_case}.sec03 is null)
            order by {$tbl_clients}.ct14 asc, {$tbl_clients}.s_num asc, {$tbl_service_case}.sec04 asc 
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name, $fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_download3_data()
  //  函數功能: 查詢開結案下載資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-05
  // **************************************************************************
  public function get_download3_data($source) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案資料
    $where = " {$tbl_service_case}.d_date is null";
    $today = date("Y-m-d");
    $get_data = $this->input->get();
    if(!empty($get_data['que3_ct_name'])) { // 案主姓名
      $que_ct_name = $get_data['que3_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and concat(AES_DECRYPT({$tbl_service_case}.ct_name,'{$this->db_crypt_key2}')) like '%{$que_ct_name}%'";
    }

    if(!empty($get_data['que3_sec03'])) { // 是否結案
      $que_sec03 = $get_data['que3_sec03'];
      $que_sec03 = $this->db->escape_like_str($que_sec03);
      if('N' == $que_sec03) {
        $where .= " and {$tbl_service_case}.sec03 is null  /* 服務結束日 */ ";
      }
      else {
        $where .= " and {$tbl_service_case}.sec03 is not null  /* 服務結束日 */ ";
      }
    }

    if(!empty($get_data['que3_sec04'])) { // 服務類型
      $que_sec04 = $get_data['que3_sec04'];
      $que_sec04 = $this->db->escape_like_str($que_sec04);
      $where .= " and {$tbl_service_case}.sec04 = '{$que_sec04}'";
    }

    if(!empty($get_data['que3_sec05'])) { // 經費來源
      $que_sec05 = $get_data['que3_sec05'];
      $que_sec05 = $this->db->escape_like_str($que_sec05);
      $where .= " and {$tbl_service_case}.sec05 = '{$que_sec05}'";
    }

    if(!empty($get_data['que3_sec09'])) { // 繳費方式
      $que_sec09 = $get_data['que3_sec09'];
      $que_sec09 = $this->db->escape_like_str($que_sec09);
      $where .= " and {$tbl_service_case}.sec09 = '{$que_sec09}'";
    }

    if("mp_rpt" == $source) {
      $where .= " and ({$tbl_service_case}.sec03 >= '{$today}'
                       or {$tbl_service_case}.sec03 is null)
                ";

    }
    $sql = "select {$tbl_service_case}.*,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as sec04_time,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as reh_type,
                   case {$tbl_service_case}.sec05
                     when '1' then 'sab'
                     when '2' then 'hb'
                   end as sec05_en_str,
                   {$tbl_clients}.ct38_1,
                   {$tbl_clients}.ct38_2,
                   {$tbl_clients}.ct38_memo
                   {$this->_aes_fd('service_case')}
                   {$this->_aes_fd('clients')}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
            where {$where}
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_clients}.d_date is null
            order by {$tbl_clients}.ct14 asc, {$tbl_clients}.s_num asc, {$tbl_service_case}.sec04 asc 
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name, $fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_sec03()
  //  函數功能: 取得結案的資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-06
  // **************************************************************************
  public function get_sec03($month, $case, $type) {
    $start_date = date("Y-m-01", strtotime($month));
    $end_date = date("Y-m-t", strtotime($month));

    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料-檔頭
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線資料-檔身
    $data = NULL;
    $sql = "select /*{$tbl_service_case}.sec03_reh_s_num,*/
                   {$tbl_route_h}.s_num,
                   COUNT(distinct {$tbl_service_case}.ct_s_num) AS clients_count
                   {$this->_aes_fd('service_case')}
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num 
            left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_clients}.s_num
            /*left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_service_case}.sec03_reh_s_num*/ 
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_route_b}.reh_s_num
            where {$tbl_service_case}.d_date is null
                  and {$tbl_route_h}.d_date is null
                  and {$tbl_service_case}.sec03 >= '{$start_date}'
                  and {$tbl_service_case}.sec03 <= '{$end_date}'
                  and {$tbl_service_case}.sec01 = $case
                  and {$tbl_service_case}.sec04 = $type
            GROUP BY {$tbl_route_h}.s_num
            ORDER BY {$tbl_route_h}.s_num ASC
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql); // 修改這裡
    if ($rs->num_rows() > 0) {
      foreach ($rs->result() as $row) {
        // 將所有列讀取到 $data
        $data[] = array(
          'reh_s_num' => $row->s_num,
          'clients_count' => $row->clients_count,
        );
      }
    }
    return $data;
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
      case 'sec01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);
        break;
      case 'sec04':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);
        break;
      case 'sec05':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);
        break;
      case 'sec08':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);
        break;
      case 'sec09':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);
        break;
      case 'ct04':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'ct34_go':
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
  // **************************************************************************
  //  函數名稱: get_sec_by_identity()
  //  函數功能: 利用身份別取得案主資料
  //  程式設計: kiwi
  //  設計日期: 2022-03-22
  // **************************************************************************
  public function get_sec_by_identity() {    
    $today = date("Y-m-d");
    $post_data = $this->input->post();

    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別異動資料

    $where = " {$tbl_clients}.d_date is null 
               and {$tbl_clients_identity_log}.ct_il01 = (SELECT MAX({$tbl_clients_identity_log}.ct_il01) 
                                                         FROM {$tbl_clients_identity_log} 
                                                         WHERE {$tbl_clients_identity_log}.d_date is null
                                                               and {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
                                                               and {$tbl_clients_identity_log}.ct_il01 <= '{$today}')
             ";

    if(!empty($post_data['upd_sec01'])) {
      $upd_sec01 = $post_data['upd_sec01'];
      $upd_sec01 = $this->db->escape_like_str($upd_sec01);
      $where .= " and {$tbl_service_case}.sec01 = {$upd_sec01}";
    }
    
    if(!empty($post_data['upd_sec04'])) {
      $upd_sec04 = $post_data['upd_sec04'];
      $upd_sec04 = $this->db->escape_like_str($upd_sec04);
      $where .= " and {$tbl_service_case}.sec04 = {$upd_sec04}";
    }

    if(!empty($post_data['upd_ct34_go'])) {
      $upd_ct34_go = $post_data['upd_ct34_go'];
      $upd_ct34_go = $this->db->escape_like_str($upd_ct34_go);
      $where .= " and {$tbl_clients_identity_log}.ct_il02 in ({$upd_ct34_go})  /* 福利身分別 */ ";
    }

    $data = NULL;
    $sql = "select {$tbl_service_case}.*
            from {$tbl_service_case}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case}.ct_s_num
            left join {$tbl_clients_identity_log} on {$tbl_clients_identity_log}.ct_s_num = {$tbl_clients}.s_num
            where {$where}
           ";

    // u_var_dump($sql);
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
}
?>