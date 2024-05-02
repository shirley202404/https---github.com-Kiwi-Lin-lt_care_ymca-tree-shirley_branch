<?php
class Other_change_log_h_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public $aes_fd1 = array('ct_name');  
  public $aes_fd2 = array('ct01','ct02');  
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $tbl_service_case_charge_amount = $this->zi_init->chk_tbl_no_lang('service_case_charge_amount'); // 收費金額歷程資料
    
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_other_change_log_h}.*,
                   case {$tbl_other_change_log_h}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_other_change_log_h}.ocl02
                     when 'Y' then '審核通過'    
                     when 'N' then '尚未通過'    
                   end as ocl02_str,
                   IF(sys_acc.acc_name is null ,
                     concat(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc.acc_name
                   ) as b_acc_name,
                   IF(sys_acc2.acc_name is null ,
                     concat(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc2.acc_name
                   ) as e_acc_name,
                   {$tbl_service_case_charge_amount}.scca01
                   {$this->_aes_fd('clients')}
                   from {$tbl_other_change_log_h}
                   left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_other_change_log_h}.b_empno
                   left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_other_change_log_h}.e_empno
                   left join {$tbl_social_worker} sw on sw.s_num = {$tbl_other_change_log_h}.b_empno
                   left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_other_change_log_h}.e_empno
                   left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_other_change_log_h}.ct_s_num
                   left join {$tbl_service_case_charge_amount} on {$tbl_service_case_charge_amount}.ocl_s_num = {$tbl_other_change_log_h}.s_num
                   where {$tbl_other_change_log_h}.d_date is null
                        and {$tbl_other_change_log_h}.s_num = ?
                   order by {$tbl_other_change_log_h}.s_num desc
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd2 as $k => $v) {
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_other_change_log_h}.*
                   
            from {$tbl_other_change_log_h}
            where {$tbl_other_change_log_h}.d_date is null
                  and {$tbl_other_change_log_h}.fd_name = ?
            order by {$tbl_other_change_log_h}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($fd_name));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_all() {
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $data = NULL;
    $sql = "select {$tbl_other_change_log_h}.*
                   
            from {$tbl_other_change_log_h}
            where {$tbl_other_change_log_h}.d_date is null
            order by {$tbl_other_change_log_h}.s_num desc
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
  //  函數名稱: get_all_by_b_date()
  //  函數功能: 取得所有審核通過的資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-06
  // **************************************************************************
  public function get_all_by_b_date() {
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $post_data = $this->input->post();
    $data = NULL;
    $sql = "select {$tbl_other_change_log_h}.*
            from {$tbl_other_change_log_h}
            where {$tbl_other_change_log_h}.d_date is null
                  and {$tbl_other_change_log_h}.ocl02 = 'Y'
                  and {$tbl_other_change_log_h}.b_date between '{$post_data['que_b_date_start']}' and '{$post_data['que_b_date_end']}'
            order by {$tbl_other_change_log_h}.s_num desc
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
  //  函數名稱: get_identity_by_s_num()
  //  函數功能: 取得身分異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_identity_by_s_num($ocl_s_num) {
    $tbl_other_change_log_identity = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 身分別異動資料
    $row = NULL;
    $sql = "select {$tbl_other_change_log_identity}.*
            from {$tbl_other_change_log_identity}
            where {$tbl_other_change_log_identity}.d_date is null
                  and {$tbl_other_change_log_identity}.ocl_s_num = {$ocl_s_num}
            order by {$tbl_other_change_log_identity}.b_date desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
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
  //  函數名稱: get_disabled_by_s_num()
  //  函數功能: 取得代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_disabled_by_s_num($ocl_s_num) {
    $tbl_other_change_log_disabled = $this->zi_init->chk_tbl_no_lang('other_change_log_disabled'); // 身分別異動資料
    $row = NULL;
    $sql = "select {$tbl_other_change_log_disabled}.*
            from {$tbl_other_change_log_disabled}
            where {$tbl_other_change_log_disabled}.d_date is null
                  and {$tbl_other_change_log_disabled}.ocl_s_num = {$ocl_s_num}
            order by {$tbl_other_change_log_disabled}.b_date desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
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
  //  函數名稱: get_route_by_s_num()
  //  函數功能: 取得停復餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_route_by_s_num($ocl_s_num) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線異動資料
    $tbl_other_change_log_route = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 路線異動資料
    $tbl_verification_person = $this->zi_init->chk_tbl_no_lang('verification_person'); // 核銷人員資料檔
    
    $row = NULL;
    $sql = "select {$tbl_other_change_log_route}.*,
                   reh1.reh01 as reh01_name,
                   reh2.reh01 as reh02_name,
                   AES_DECRYPT(vp.vp01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(vp.vp02,'{$this->db_crypt_key2}') as ocl_r02_vp_name,
                   AES_DECRYPT(vp2.vp01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(vp2.vp02,'{$this->db_crypt_key2}') as ocl_r03_vp_name,
                   case {$tbl_other_change_log_route}.ocl_r01
                     when '1' then '午餐/中晚餐'
                     when '2' then '晚餐'
                   end as ocl_r01_str
            from {$tbl_other_change_log_route}
            left join {$tbl_route_h} reh1 on reh1.s_num = {$tbl_other_change_log_route}.ocl_r02_reh_s_num
            left join {$tbl_route_h} reh2 on reh2.s_num = {$tbl_other_change_log_route}.ocl_r03_reh_s_num
            left join {$tbl_verification_person} vp on vp.s_num = {$tbl_other_change_log_route}.ocl_r02_vp_s_num
            left join {$tbl_verification_person} vp2 on vp2.s_num = {$tbl_other_change_log_route}.ocl_r03_vp_s_num
            where {$tbl_other_change_log_route}.d_date is null
                  and {$tbl_other_change_log_route}.ocl_s_num = {$ocl_s_num}
            order by {$tbl_other_change_log_route}.b_date desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_service_by_s_num()
  //  函數功能: 取得固定暫停資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_service_by_s_num($ocl_s_num) {
    $tbl_other_change_log_service = $this->zi_init->chk_tbl_no_lang('other_change_log_service'); // 服務現況異動資料
    $row = NULL;
    $sql = "select {$tbl_other_change_log_service}.*
            from {$tbl_other_change_log_service}
            where {$tbl_other_change_log_service}.d_date is null
                  and {$tbl_other_change_log_service}.ocl_s_num = {$ocl_s_num}
            order by {$tbl_other_change_log_service}.b_date desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
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
  //  函數名稱: get_identity_by_ct()
  //  函數功能: 取得固定暫停資料
  //  程式設計: kiwi
  //  設計日期: 2022-09-07
  // **************************************************************************
  public function get_identity_by_sec($ct_s_num, $ocl_i01, $ocl_type) {
    $tbl_other_change_log_identity = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 身分別異動資料
    $row = NULL;
    $sql = "select {$tbl_other_change_log_identity}.*
            from {$tbl_other_change_log_identity}
            where {$tbl_other_change_log_identity}.d_date is null
                  and {$tbl_other_change_log_identity}.ct_s_num = {$ct_s_num}
                  and {$tbl_other_change_log_identity}.ocl_i01_{$ocl_type} = {$ocl_i01}
            order by {$tbl_other_change_log_identity}.b_date desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
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
  //  函數名稱: get_route_by_ct()
  //  函數功能: 取得路線變更資料
  //  程式設計: kiwi
  //  設計日期: 2022-09-07
  // **************************************************************************
  public function get_route_by_ct($ct_s_num, $type, $produce_date) {
    $today = date("Y-m-d");
    $tbl_other_change_log_route = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 路線異動資料
    $row = NULL;
    $sql = "select {$tbl_other_change_log_route}.*
            from {$tbl_other_change_log_route}
            where {$tbl_other_change_log_route}.d_date is null
                  and {$tbl_other_change_log_route}.ct_s_num = {$ct_s_num}
                  and {$tbl_other_change_log_route}.ocl_r01 = {$type}
                  and {$tbl_other_change_log_route}.ocl_r06 between '{$today}' and '{$produce_date}'
                  and {$tbl_other_change_log_route}.ocl_r10 = 'N'
            order by {$tbl_other_change_log_route}.b_date desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $where = " {$tbl_other_change_log_h}.d_date is null ";
    $order = " {$tbl_other_change_log_h}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_other_change_log_h}.ct_s_num like '%{$que_str}%' /* tw_clients.s_num */                       
                       or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */ 
                       or {$tbl_other_change_log_h}.ocl01 like binary '%{$que_str}%' /* 指令生效開始日期 */                       
                       or {$tbl_other_change_log_h}.ocl02 like binary '%{$que_str}%' /* 異動類型 */                                            
 
                      )
                ";
    }
    
    if(!empty($get_data['que_sec_s_num'])) { // 服務開始日
      $que_sec_s_num = $get_data['que_sec_s_num'];
      $que_sec_s_num = $this->db->escape_like_str($que_sec_s_num);
      $where .= " and {$tbl_other_change_log_h}.sec_s_num = {$que_sec_s_num}  /* 服務編號 */ ";
    }
    
    if(!empty($get_data['que_ct_name'])) { // 案主姓
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and {$tbl_other_change_log_h}.ct_name like '%{$que_ct_name}%'  /* 案主姓名 */ ";
    }

    if(!empty($get_data['que_sec01'])) { // 服務現況
      $que_sec01 = $get_data['que_sec01'];
      $que_sec01 = $this->db->escape_like_str($que_sec01);
      $where .= " and {$tbl_service_case}.sec01 = '{$que_sec01}'  /* 服務現況 */ ";
    } 
    
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_other_change_log_h}.s_num
                from {$tbl_other_change_log_h}
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_other_change_log_h}.ct_s_num
                left join {$tbl_service_case} on {$tbl_service_case}.ct_s_num = {$tbl_clients}.s_num
                where {$where}
                      and {$tbl_clients}.is_available = 1
                      and {$tbl_clients}.d_date is null
                group by {$tbl_other_change_log_h}.s_num, {$tbl_other_change_log_h}.ct_s_num
                order by {$tbl_other_change_log_h}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_other_change_log_h}.*,
                   case {$tbl_other_change_log_h}.ocl02
                     when 'Y' then '審核通過'    
                     when 'N' then '尚未通過'   
                   end as ocl02_str
                  {$this->_aes_fd('clients')}
                  from {$tbl_other_change_log_h}
                  left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_other_change_log_h}.ct_s_num
                  left join {$tbl_service_case} on {$tbl_service_case}.ct_s_num = {$tbl_clients}.s_num
                  where {$where}
                        and {$tbl_clients}.is_available = 1
                        and {$tbl_clients}.d_date is null
                  group by {$tbl_other_change_log_h}.s_num, {$tbl_other_change_log_h}.ct_s_num
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $ocl_h = $data['ocl_h'];
    foreach ($ocl_h as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($ocl_h[$k_fd_name]);
      }
    }
    $ocl_h['b_empno'] = $_SESSION['acc_s_num'];
    $ocl_h['b_date'] = date('Y-m-d H:i:s');
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料-檔頭
    if(!$this->db->insert($tbl_other_change_log_h, $ocl_h)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    else {
      $ocl_s_num = $this->db->insert_id();
      $ocl01_arr = explode(",", $ocl_h["ocl01"]);
      if(NULL != $ocl01_arr) {
        foreach ($ocl01_arr as &$v) {
          switch ($v) {   
            case 1: // 身分別
              $fd_type = $data['ocl_i'];
              $fd_type2 = $data['scca'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 非非餐食異動資料-檔身(身分別)
            break;
            case 2: // 失能等級      
              $fd_type = $data['ocl_d'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_disabled'); // 非非餐食異動資料-檔身(失能等級)
            break;  
            case 3: // 改路線
              $fd_type = $data['ocl_r'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 非非餐食異動資料-檔身(改路線)
            break;  
            case 4: // 服務現況      
              $fd_type = $data['ocl_s'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_service'); // 非非餐食異動資料-檔身(服務現況)
            break;
            case 5:    
            case 6:    
            case 7:
              $fd_tbl = '';
            break;    
          }
             
          $fd_type['b_empno'] = $_SESSION['acc_s_num'];
          $fd_type['b_date'] = date('Y-m-d H:i:s');
          $fd_type['ct_s_num'] = $ocl_h['ct_s_num'];
          $fd_type['ocl_s_num'] = $ocl_s_num;
          
          if('' != $fd_tbl) {
            if(!$this->db->insert($fd_tbl, $fd_type)) {
              $rtn_msg = $this->lang->line('add_ng');
            }
          }
          if(!empty($fd_type2['scca01'])) {
            $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案服務
            $this->db->where('ct_s_num', $ocl_h['ct_s_num']);
            $this->db->where('d_date is null', null, false);
            $this->db->where('sec03 is null', null, false);
            if(!$this->db->update($tbl_service_case, array('sec07' => $fd_type2['scca01']))) {
              $rtn_msg = $this->lang->line('upd_ng');
            }
            // 紀錄收費金額歷程
            $tbl_service_case_charge_amount = $this->zi_init->chk_tbl_no_lang('service_case_charge_amount'); // 收費金額歷程資料
            $fd_type2['b_empno'] = $_SESSION['acc_s_num'];
            $fd_type2['b_date'] = date('Y-m-d H:i:s');
            $fd_type2['ct_s_num'] = $ocl_h['ct_s_num'];
            $fd_type2['ocl_s_num'] = $ocl_s_num;
            $fd_type2['scca02'] = $fd_type['ocl_i02_date'];
            if(!$this->db->insert($tbl_service_case_charge_amount, $fd_type2)) {
              $rtn_msg = $this->lang->line('add_ng');
            }
          }
        } 
      }
      if(!$this->other_change_auth_model->add_auth_data($ocl_s_num)) {
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $ocl_h = $data['ocl_h'];
    foreach ($ocl_h as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($ocl_h[$k_fd_name]);
      }
    }

    $other_change_log_h_row = $this->other_change_log_h_model->get_one($ocl_h['s_num']); // 列出單筆明細資料
    $ocl01_arr = explode(",", $other_change_log_h_row->ocl01);
    if(NULL != $ocl01_arr) {
      foreach ($ocl01_arr as $v) {
        switch ($v) {   
          case 1: // 身分別
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 非非餐食異動資料-檔身(身分別)
          break;
          case 2: // 失能等級      
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_disabled'); // 非非餐食異動資料-檔身(失能等級)
          break;  
          case 3: // 改路線
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 非非餐食異動資料-檔身(改路線)
          break;  
          case 4: // 服務現況      
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_service'); // 非非餐食異動資料-檔身(服務現況)
          break;    
          case 5:    
          case 6:    
          case 7:
            $fd_tbl = '';
          break; 
        }
        
        if('' != $fd_tbl) {
          $this->db->where('ocl_s_num', $ocl_h['s_num']);
          if(!$this->db->delete($fd_tbl)) {
            $rtn_msg = $this->lang->line('del_ng');
            echo $rtn_msg;
            return;
          }
        }
      } 
    }

    $ocl_h['e_empno'] = $_SESSION['acc_s_num'];
    $ocl_h['e_date'] = date('Y-m-d H:i:s');
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料-檔頭
    $this->db->where('s_num', $ocl_h['s_num']);
    if(!$this->db->update($tbl_other_change_log_h, $ocl_h)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    else {
      $ocl_s_num = $ocl_h['s_num'];
      $ocl01_arr = explode(",", $ocl_h["ocl01"]);
      if(NULL != $ocl01_arr) {
        foreach ($ocl01_arr as $v) {
          switch ($v) {   
            case 1: // 身分別
              $fd_type = $data['ocl_i'];
              $fd_type2 = $data['scca'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 非非餐食異動資料-檔身(身分別)
            break;
            case 2: // 失能等級      
              $fd_type = $data['ocl_d'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_disabled'); // 非非餐食異動資料-檔身(失能等級)
            break;  
            case 3: // 改路線
              $fd_type = $data['ocl_r'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 非非餐食異動資料-檔身(改路線)
            break;  
            case 4: // 服務現況      
              $fd_type = $data['ocl_s'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('other_change_log_service'); // 非非餐食異動資料-檔身(服務現況)
            break;    
            case 5:    
            case 6:    
            case 7:
              $fd_tbl = '';
            break; 
          }
          
          if('' != $fd_tbl) {
            $this->db->where('ocl_s_num', $ocl_s_num);
            if(!$this->db->delete($fd_tbl)) {
              $rtn_msg = $this->lang->line('del_ng');
              echo $rtn_msg;
              return;
            }
            
            $fd_type['b_empno'] = $_SESSION['acc_s_num'];
            $fd_type['b_date'] = date('Y-m-d H:i:s');
            $fd_type['ocl_s_num'] = $ocl_s_num;
            $fd_type['ct_s_num'] = $ocl_h['ct_s_num'];
            
            if(!$this->db->insert($fd_tbl, $fd_type)) {
              $rtn_msg = $this->lang->line('add_ng');
            }
          }
          if(!empty($fd_type2['scca01'])) {
            $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案服務
            $this->db->where('ct_s_num', $ocl_h['ct_s_num']);
            $this->db->where('d_date is null', null, false);
            $this->db->where('sec03 is null', null, false);
            if(!$this->db->update($tbl_service_case, array('sec07' => $fd_type2['scca01']))) {
              $rtn_msg = $this->lang->line('upd_ng');
            }
            // 紀錄收費金額歷程
            $tbl_service_case_charge_amount = $this->zi_init->chk_tbl_no_lang('service_case_charge_amount'); // 收費金額歷程資料
            $this->db->where('ocl_s_num', $ocl_s_num);
            if(!$this->db->delete($tbl_service_case_charge_amount)) {
              $rtn_msg = $this->lang->line('del_ng');
              echo $rtn_msg;
              return;
            }
            $fd_type2['b_empno'] = $_SESSION['acc_s_num'];
            $fd_type2['b_date'] = date('Y-m-d H:i:s');
            $fd_type2['ct_s_num'] = $ocl_h['ct_s_num'];
            $fd_type2['ocl_s_num'] = $ocl_s_num;
            $fd_type2['scca02'] = $fd_type['ocl_i02_date'];
            if(!$this->db->insert($tbl_service_case_charge_amount, $fd_type2)) {
              $rtn_msg = $this->lang->line('add_ng');
            }
          }
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_other_change_log_h, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $ocl_h_row = $this->get_one($v['s_num']);
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_other_change_log_h, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    else {

      // 非餐食異動審核資料刪除 Begin //
      $tbl_other_change_auth = $this->zi_init->chk_tbl_no_lang('other_change_auth'); // 非餐食異動審核
      $this->db->where('ocl_s_num', $v['s_num']);
      if(!$this->db->update($tbl_other_change_auth, $data)) {
        die($this->lang->line('del_ng')); // 刪除失敗
      }
      // 非餐食異動審核資料刪除 End //

      $ocl01 = $ocl_h_row->ocl01;
      $ct_s_num = $ocl_h_row->ct_s_num;
      $ocl_s_num = $ocl_h_row->s_num;
      $ocl01_arr = explode(",", $ocl01);
      if(NULL != $ocl01_arr) {
        foreach ($ocl01_arr as $ocl_type) {
          switch ($ocl_type) {
            case 1: // 身分別異動
              // 1. 刪除這筆身分別異動資料關聯的身分別資料
              $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別-紀錄檔
              $this->db->where('ct_il03', $v['s_num']);
              if(!$this->db->update($tbl_clients_identity_log, $data)) {
                die($this->lang->line('del_ng')); // 刪除失敗
              }
              // 2. 將案主的付費方式還原成異動前的
              $ocl_i_row = $this->get_identity_by_s_num($ocl_s_num);
              $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案服務
              $this->db->where('ct_s_num', $ct_s_num);
              $this->db->where('d_date is null', null, false);
              if(!$this->db->update($tbl_service_case, array('sec09' => $ocl_i_row->ocl_sec09_before))) {
                die($this->lang->line('del_ng')); // 刪除失敗
              }
              // 3. 刪除身分別異動資料
              $tbl_other_change_log_identity = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 非餐食類異動-身分別異動
              $this->db->where('ocl_s_num', $v['s_num']);
              if(!$this->db->update($tbl_other_change_log_identity, $data)) {
                die($this->lang->line('del_ng')); // 刪除失敗
              }
              break;
            case 2: // 失能等級
              $tbl_other_change_log_disabled = $this->zi_init->chk_tbl_no_lang('other_change_log_disabled'); // 非餐食類異動-失能等級異動
              $this->db->where('ocl_s_num', $v['s_num']);
              if(!$this->db->update($tbl_other_change_log_disabled, $data)) {
                die($this->lang->line('del_ng')); // 刪除失敗
              }
              break;
            case 3: // 更改路線
              $tbl_other_change_log_route = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 非餐食類異動-更改路線
              $this->db->where('ocl_s_num', $v['s_num']);
              if(!$this->db->update($tbl_other_change_log_route, $data)) {
                die($this->lang->line('del_ng')); // 刪除失敗
              }
              break;
            case 4: // 服務現況
              $tbl_other_change_log_service = $this->zi_init->chk_tbl_no_lang('other_change_log_service'); // 非餐食類異動-服務現況
              $this->db->where('ocl_s_num', $v['s_num']);
              if(!$this->db->update($tbl_other_change_log_service, $data)) {
                die($this->lang->line('del_ng')); // 刪除失敗
              }
              break;
          }
        }
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: upd_change_by_s_num()
  //  函數功能: 更新資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function upd_change_by_s_num($ocl_s_num) {
    $other_change_log_row = $this->get_one($ocl_s_num);
    $ct_s_num = $other_change_log_row->ct_s_num;
    $ocl01_arr = explode(",", $other_change_log_row->ocl01);
    foreach ($ocl01_arr as $v) {
      $data = NULL;
      switch ($v) {  
        case 1: // 身分別異動
          $other_change_log_identity_row = $this->other_change_log_h_model->get_identity_by_s_num($ocl_s_num); // 列出非餐食異動資料
          if(NULL != $other_change_log_identity_row ) {
            $tbl_clients_identity_log = $this->zi_init->chk_tbl_no_lang('clients_identity_log'); // 案主身分別記錄檔
            $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開結案服務
            $data['b_empno'] = $_SESSION['acc_s_num']; 
            $data['b_date'] = date("Y-m-d H:i:s"); 
            $data['ct_s_num'] = $ct_s_num;
            $data['ct_il01'] = $other_change_log_identity_row->ocl_i02_date; // 變更日期
            $data['ct_il02'] = $other_change_log_identity_row->ocl_i01_after; // 變更後的身分別
            $data['ct_il03'] = $other_change_log_identity_row->ocl_s_num; // 身分別異動紀錄s_num
            if(!$this->db->insert($tbl_clients_identity_log , $data)) {
              $rtn_msg = $this->lang->line('upd_ng');
              echo $rtn_msg;
              die();
            }
            else {
              if('N' != $other_change_log_identity_row->ocl_sec09_after) {
                $data = NULL;
                $data['sec09'] = $other_change_log_identity_row->ocl_sec09_after;
                $this->db->where('ct_s_num', $ct_s_num);
                if(!$this->db->update($tbl_service_case , $data)) {
                  $rtn_msg = $this->lang->line('upd_ng');
                  echo $rtn_msg;
                  die();
                }
              }
            }
          }
          break;
        case 2: // 失能等級異動
          $other_change_log_disabled_row = $this->other_change_log_h_model->get_disabled_by_s_num($ocl_s_num); // 列出失能異動資料
          if(NULL != $other_change_log_disabled_row) {
            $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
            $data['ct37'] = $other_change_log_disabled_row->ocl_d01_after;
            $this->db->where('s_num' , $ct_s_num);
            $this->db->where('is_available' , "1");
            if(!$this->db->update($tbl_clients , $data)) {
              $rtn_msg = $this->lang->line('upd_ng');
              echo $rtn_msg;
              die();
            }
          }
          break;  
        case 3: // 送餐路線異動
        /*
          $other_change_log_route_row = $this->other_change_log_h_model->get_route_by_s_num($ocl_s_num); // 列出路線異動資料
          if(NULL != $other_change_log_route_row) {
            $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路徑-檔身
            $data['reh_s_num'] = $other_change_log_route_row->ocl_r03_reh_s_num;
            $this->db->where('ct_s_num' , $ct_s_num);
            $this->db->where('reh_s_num' , $other_change_log_route_row->ocl_r02_reh_s_num);
            if(!$this->db->update($tbl_route_b , $data)) {
              $rtn_msg = $this->lang->line('upd_ng');
              echo $rtn_msg;
              die();
            }
          }
          */
          break;  
        case 4: // 服務現況異動
          $other_change_log_service_row = $this->other_change_log_h_model->get_service_by_s_num($ocl_s_num); // 列出固定暫停資料
          if(NULL != $other_change_log_service_row) {
            $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
            $data['sec01'] = $other_change_log_service_row->ocl_s01_after;
            $this->db->where('ct_s_num' , $ct_s_num);
            $this->db->where('sec03' , NULL);
            $this->db->where('d_date' , NULL);
            if(!$this->db->update($tbl_service_case , $data)) {
              $rtn_msg = $this->lang->line('upd_ng');
              echo $rtn_msg;
              die();
            }
          }
          break;  
      } 
    }
    
    $data = NULL;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $data['ocl02'] = "Y";
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 非餐食異動資料
    $this->db->where('s_num', $ocl_s_num);
    if(!$this->db->update($tbl_other_change_log_h, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    else {
      $this->db->where('ocl_s_num', $ocl_s_num);
      $tbl_other_change_log_identity = $this->zi_init->chk_tbl_no_lang('other_change_log_identity'); // 非非餐食異動-身分
      if(!$this->db->update($tbl_other_change_log_identity, array("ocl_i03" => "Y"))) { // 非非餐食異動-身分
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_other_change_log_disabled = $this->zi_init->chk_tbl_no_lang('other_change_log_disabled'); // 非非餐食異動-失能
      if(!$this->db->update($tbl_other_change_log_disabled, array("ocl_d03" => "Y"))) { // 非非餐食異動-失能
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_other_change_log_service = $this->zi_init->chk_tbl_no_lang('other_change_log_service'); // 非非餐食異動-開案現況
      if(!$this->db->update($tbl_other_change_log_service, array("ocl_s02" => "Y"))) { // 非非餐食異動-開案現況
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_other_change_log_route = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 非非餐食異動-路徑服務
      if(!$this->db->update($tbl_other_change_log_route, array("ocl_r05" => "Y"))) { // 固定暫停
        $rtn_msg = $this->lang->line('upd_ng');
      }
    }
  }  
  // **************************************************************************
  //  函數名稱: upd_other_change_log_route()
  //  函數功能: 更新資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function upd_other_change_log_route() {
    $rtn_msg = "ok";
    $today = date("Y-m-d");
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路徑-檔身
    $tbl_other_change_log_h = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 路線異動資料
    $tbl_other_change_log_route = $this->zi_init->chk_tbl_no_lang('other_change_log_route'); // 路線異動資料
    $data = NULL;
    $sql = "select {$tbl_other_change_log_route}.*
            from {$tbl_other_change_log_route}
            left join {$tbl_other_change_log_h} on {$tbl_other_change_log_h}.s_num = {$tbl_other_change_log_route}.ocl_s_num
            where {$tbl_other_change_log_h}.d_date is null
                  and {$tbl_other_change_log_route}.d_date is null
                  and {$tbl_other_change_log_route}.ocl_r05 = 'Y'
                  and {$tbl_other_change_log_route}.ocl_r10 = 'N'
                  and {$tbl_other_change_log_route}.ocl_r06 = '{$today}'           
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
    
    $reh_s_num_arr = NULL;
    if(NULL != $data) {
      foreach ($data as $k => $v) {
        $upd_data = NULL;
        $upd_data['e_date'] = date("Y-m-d H:i:s"); 
        $upd_data['e_empno'] = $_SESSION['acc_s_num'];
        $upd_data['reb01'] = $v['ocl_r03_reb01'];
        $upd_data['reh_s_num'] = $v['ocl_r03_reh_s_num'];
        $upd_data['reb03_vp_s_num'] = $v['ocl_r03_vp_s_num'];
        if(0 == $v['ocl_r03_reb01']) { // 如果不知道送餐順序或者順餐順序是0就預設為新案主
          $upd_data['reb02'] = "Y";
        }
        $this->db->where('ct_s_num', $v['ct_s_num']);
        $this->db->where('reh_s_num', $v['ocl_r02_reh_s_num']);
        if(!$this->db->update($tbl_route_b, $upd_data)) {
          $rtn_msg = $this->lang->line('upd_ng');
          echo $rtn_msg;
          die();
        }
        else {
          $record = NULL;
          $record['e_date'] = date("Y-m-d H:i:s"); 
          $record['e_empno'] = $_SESSION['acc_s_num']; 
          $record['ocl_r10'] = "Y"; 
          $this->db->where('s_num', $v['s_num']);
          if(!$this->db->update($tbl_other_change_log_route, $record)) {
            $rtn_msg = $this->lang->line('upd_ng');
            echo $rtn_msg;
            die();
          }
          else {
            $reh_s_num_arr[] = $v['ocl_r02_reh_s_num'];
            $reh_s_num_arr[] = $v['ocl_r03_reh_s_num'];
          }
        }
      }
      
      if(NULL != $reh_s_num_arr) {
        // 先取得非0順位的案主，用e_date排序，取得有
        // 異動過的案主排在前面，後面所有的案主都加1位
        // 全部案主順序重排
        foreach ($reh_s_num_arr as $reh_s_num) {
          $reorder = 1;
          $reb_row = $this->route_model->get_route_b($reh_s_num);
          if(NULL == $reb_row) {
            continue;
          }
          foreach ($reb_row as $k => $v) {
            if(0 == $v['reb01'] or "Y" == $v['reb02']) {
              continue;
            }
            $this->db->where('s_num', $v['s_num']);
            if(!$this->db->update($tbl_route_b, array('reb01' => $reorder))) {
              echo "重新排序失敗";
              die();
            }
            $reorder++;
          }
        }
      }
    }
    else {
      $rtn_msg = "目前無案主須更新路線!!";
    }
    echo $rtn_msg;
    return;
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
      case 'ocl01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");
        $fd_val = str_replace(",", "、", $fd_val);   
        break;
      case 'ocl_i01_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("ct34_go", $fd_val);      
        break;
      case 'ocl_i01_after':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("ct34_go", $fd_val);      
        break;
      case 'ocl_d01_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("ct37", $fd_val);      
        break;
      case 'ocl_d01_after':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("ct37", $fd_val);      
        break;
      case 'ocl_s01_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup('sec01', $fd_val);      
        break;
      case 'ocl_s01_after':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup('sec01', $fd_val);      
        break;
      case 'ocl_sec09_before':
        $fd_name_mask = "{$fd_name}_str";
        if($fd_val == "N") {
          $fd_val = "不變";
        }
        else {
          $fd_val = hlp_opt_setup('sec09', $fd_val);      
        }
        break;
      case 'ocl_sec09_after':
        $fd_name_mask = "{$fd_name}_str";
        if($fd_val == "N") {
          $fd_val = "不變";
        }
        else {
          $fd_val = hlp_opt_setup('sec09', $fd_val);      
        }        
        break;
    }

    if('' != $fd_name_mask) {
      $fd_name = $fd_name_mask;
    }

    return(array($fd_name, $fd_val));
  }
}
?>