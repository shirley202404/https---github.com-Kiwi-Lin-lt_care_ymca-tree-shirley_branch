<?php
class Social_worker_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('sw01','sw02','sw03','sw10'); // 加密欄位
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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_social_worker}.*,
                   case {$tbl_social_worker}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_social_worker}.sw04
                     when 'M' then '男'
                     when 'F' then '女'
                   end as sw04_str,
                   case {$tbl_social_worker}.sw30
                     when 'Y' then '有'
                     when 'N' then '沒有'
                   end as sw30_str,
                   IF(sys_acc.acc_name is null ,
                     concat(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc.acc_name
                   ) as b_acc_name,
                   IF(sys_acc2.acc_name is null ,
                     concat(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc2.acc_name
                   ) as e_acc_name,
                   {$tbl_account_group}.acg_name
                   {$this->_aes_fd()}
            from {$tbl_social_worker}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_social_worker}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_social_worker}.e_empno
            left join {$tbl_social_worker} sw on sw.s_num = {$tbl_social_worker}.b_empno
            left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_social_worker}.e_empno
            left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_social_worker}.group_s_num
            where {$tbl_social_worker}.d_date is null
                  and {$tbl_social_worker}.s_num = ?
            order by {$tbl_social_worker}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 資料重複
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
  public function chk_duplicate($fd_name) {
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_social_worker}.*
                   {$this->_aes_fd()}
            from {$tbl_social_worker}
            where {$tbl_social_worker}.d_date is null
                  and {$tbl_social_worker}.sw05 = ?
            order by {$tbl_social_worker}.s_num desc
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
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_all() {
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $data = NULL;
    $sql = "select {$tbl_social_worker}.*
                   {$this->_aes_fd()}
            from {$tbl_social_worker}
            where {$tbl_social_worker}.d_date is null
            order by {$tbl_social_worker}.s_num desc
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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $data = NULL;
    $sql = "select {$tbl_social_worker}.*
                   {$this->_aes_fd()}
            from {$tbl_social_worker}
            where {$tbl_social_worker}.d_date is null
                  and {$tbl_social_worker}.is_available = 1 /* 啟用 */
            order by {$tbl_social_worker}.s_num desc
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
  //  函數名稱: get_ra()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_ra($s_num) {
    $tbl_social_worker_responsible_area = $this->zi_init->chk_tbl_no_lang('social_worker_responsible_area'); // 社工負責區域
    $data = NULL;
    $sql = "select {$tbl_social_worker_responsible_area}.*
            from {$tbl_social_worker_responsible_area}
            where {$tbl_social_worker_responsible_area}.d_date is null
                        and {$tbl_social_worker_responsible_area}.sw_s_num = {$s_num}
            order by {$tbl_social_worker_responsible_area}.s_num desc
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
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
    $where = " {$tbl_social_worker}.d_date is null ";
    $order = " {$tbl_social_worker}.is_available desc, {$tbl_social_worker}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_social_worker}.group_s_num like '%{$que_str}%' /* sys_account_group.s_num */                       
                       or {$tbl_social_worker}.dt_s_num like '%{$que_str}%' /* tw_department.s_num */                       
                       or concat(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 中文姓 */                       
                       or AES_DECRYPT({$tbl_social_worker}.sw03,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 身份證字號 */                       
                       or {$tbl_social_worker}.sw04 like '%{$que_str}%' /* 性別(M=男；F=女) */                       
                       or {$tbl_social_worker}.sw05 like '%{$que_str}%' /* 職工編號 */                       
                       or {$tbl_social_worker}.sw_pwd like '%{$que_str}%' /* 登入密碼 */                       
                       or {$tbl_social_worker}.sw06 like '%{$que_str}%' /* Email Address */                       
                       or {$tbl_social_worker}.sw07 like '%{$que_str}%' /* 學歷(1=高中；2=專科；3=大學；4=研究所；5=學士；6=碩士；7=博士) */                       
                       or BINARY {$tbl_social_worker}.sw08_start like BINARY '%{$que_str}%' /* 到職日期 */                       
                       or {$tbl_social_worker}.sw09 like '%{$que_str}%' /* 年資 */                       
                       or AES_DECRYPT({$tbl_social_worker}.sw10,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 手機門號 */                       
                       or {$tbl_social_worker}.sw30 like '%{$que_str}%' /* 是否具外送員身分(Y=有,N=沒有) */                       
                       or {$tbl_social_worker}.sw99 like '%{$que_str}%' /* 備註 */                       
                       or {$tbl_social_worker}.sw_login_cnt like '%{$que_str}%' /* 登入次數 */                       
                       or BINARY {$tbl_social_worker}.sw_login_last_date like BINARY '%{$que_str}%' /* 最後登入日期時間 */                       
                       or {$tbl_social_worker}.sw_login_last_ip like '%{$que_str}%' /* 最後登入IP */
                      )
                ";
    }

    if(!empty($get_data['que_sw01'])) { // 中文姓
      $que_sw01 = $get_data['que_sw01'];
      $que_sw01 = $this->db->escape_like_str($que_sw01);
      $where .= " and AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}') = '{$que_sw01}'  /* 中文姓 */ ";
    }
    if(!empty($get_data['que_sw02'])) { // 中文名
      $que_sw02 = $get_data['que_sw02'];
      $que_sw02 = $this->db->escape_like_str($que_sw02);
      $where .= " and AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}') = '{$que_sw02}'  /* 中文名 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_social_worker}.s_num
                from {$tbl_social_worker}
                where $where
                group by {$tbl_social_worker}.s_num
                order by {$tbl_social_worker}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_social_worker}.*,
                   case {$tbl_social_worker}.sw04
                     when 'M' then '男'
                     when 'F' then '女'
                   end as sw04_str,
                   case {$tbl_social_worker}.sw30
                     when 'Y' then '有'
                     when 'N' then '沒有'
                   end as sw30_str,
                   {$tbl_account_group}.acg_name
                   {$this->_aes_fd()}
            from {$tbl_social_worker}
            left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_social_worker}.group_s_num
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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料檔
    $tbl_social_worker_responsible_area = $this->zi_init->chk_tbl_no_lang('social_worker_responsible_area'); // 社工負責區域

    $rtn_msg = 'ok';
    $data = $this->input->post();
    $sw_ra = NULL;
    if(isset($data['sw_ra'])) {
      $sw_ra = $data['sw_ra'];
    }
    unset($data['sw_ra']);
    $sw_data = $data;
    $chk_sw_row = $this->chk_duplicate($data['sw05']); // 檢查登入帳號
    if(NULL==$chk_sw_row) {
      foreach ($data as $k_fd_name => $v_data) {
        if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
          $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
          unset($data[$k_fd_name]);
        }
      }
    
      $data['sw_pwd'] = password_hash($data['sw_pwd'], PASSWORD_DEFAULT); // 密碼要加密
      $data['b_empno'] = $_SESSION['acc_s_num'];
      $data['b_date'] = date('Y-m-d H:i:s');
      if(!$this->db->insert($tbl_social_worker, $data)) {
        $rtn_msg = $this->lang->line('add_ng');
      }
      else {
        if($data['sw30'] == "Y") {
          $sw_s_num = $this->db->insert_id();
          $dp_data['dp01'] = $sw_data['sw01'];
          $dp_data['dp02'] = $sw_data['sw02'];
          $dp_data['dp03'] = $sw_data['sw03'];
          $dp_aes_fd = array('dp01','dp02','dp03','dp10'); // 加密欄位
          foreach ($dp_data as $k_fd_name => $v_data) {
            if(in_array($k_fd_name,$dp_aes_fd)) { // 加密欄位
              $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
              unset($dp_data[$k_fd_name]);
            }
          }
          $dp_data['group_s_num'] = 14;
          $dp_data['b_empno'] = $_SESSION['acc_s_num'];
          $dp_data['b_date'] = date('Y-m-d H:i:s');
          $dp_data['sw_s_num'] = $sw_s_num;
          $dp_data['dp04'] = $data['sw04'];
          $dp_data['dp05'] = $data['sw05'];
          $dp_data['dp_account'] = INSTITUTION_CODE . "-{$data['sw05']}";
          $dp_data['dp06'] = $data['sw06'];
          $dp_data['dp07_start'] = $data['sw08_start'];
          $dp_data['dp07_end'] = $data['sw08_end'];
          $dp_data['dp30'] = "Y";
          $dp_data['dp_pwd'] = $data['sw_pwd'];
          $dp_data['is_available'] = $data['is_available'];
          if(!$this->db->insert($tbl_delivery_person, $dp_data)) {
            $rtn_msg = $this->lang->line('add_ng');
          }
          $_POST['s_num'] = $sw_s_num;
          $this->zi_my_func->web_api_data("delivery_person", "add");
        }
        
        if(NULL != $sw_ra) {
          $i = 0;
          $s_num = $this->db->insert_id();
          foreach ($sw_ra['s_num'] as $k => $v) {
            $sw_ra_data_batch[$i]['sw_s_num'] = $s_num;
            $sw_ra_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
            $sw_ra_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
            $sw_ra_data_batch[$i]['sw_ra01'] = $sw_ra['sw_ra01'][$k];
            $sw_ra_data_batch[$i]['sw_ra02'] = $sw_ra['sw_ra02'][$k];
            $sw_ra_data_batch[$i]['sw_ra03'] = $sw_ra['sw_ra03'][$k];
            if($sw_ra['sw_ra01'][$k] == '' or $sw_ra['sw_ra02'][$k] == '' or $sw_ra['sw_ra03'][$k] == '') {
              unset($sw_ra_data_batch[$i]);
            }
            $i++;
          }
          
          if($sw_ra_data_batch != NULL) {
            if(!$this->db->insert_batch($tbl_social_worker_responsible_area , $sw_ra_data_batch)) {
              $rtn_msg = $this->lang->line('upd_ng');
            }
          }  
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
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function save_upd() {
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料檔
    $tbl_social_worker_responsible_area = $this->zi_init->chk_tbl_no_lang('social_worker_responsible_area'); // 社工負責區域
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $sw_ra = NULL;
    if(isset($data['sw_ra'])) {
      $sw_ra = $data['sw_ra'];
    }
    unset($data['sw_ra']);
    $sw_data = $data;
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
    if(''<>$data['sw_pwd']) { // 不是空白才處理
      $data['sw_pwd'] = password_hash($data['sw_pwd'], PASSWORD_DEFAULT); // 密碼要加密
    }
    else {
      unset($data['sw_pwd']);
    } 
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_social_worker, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
      echo $rtn_msg;
      return;
    }
    
    $delivery_person_row = $this->delivery_person_model->chk_sw($data['s_num']);
    if(NULL != $delivery_person_row) {
      $this->db->where('sw_s_num', $data['s_num']);
      if($data['sw30'] == "Y") {
        $dp_data['e_empno'] = $_SESSION['acc_s_num'];
        $dp_data['e_date'] = date('Y-m-d H:i:s');
        $dp_data['dp04'] = $data['sw04'];
        $dp_data['dp05'] = $data['sw05'];
        $dp_data['dp_account'] = INSTITUTION_CODE . "-{$data['sw05']}";
        $dp_data['dp06'] = $data['sw06'];
        $dp_data['dp07_start'] = $data['sw08_start'];
        $dp_data['dp07_end'] = $data['sw08_end'];
        $dp_data['dp30'] = "Y";
        $dp_data['dp_pwd'] = password_hash($sw_data['sw_pwd'], PASSWORD_DEFAULT); // 密碼要加密  
        $dp_data['is_available'] = $data['is_available'];
        if(!$this->db->update($tbl_delivery_person, $dp_data)) {
          $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
          echo $rtn_msg;
          return;
        }
      }
      else {
        if(!$this->db->update($tbl_delivery_person, array("is_available" => 0))) {
          $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
          echo $rtn_msg;
          return;
        }
      }
      $_POST['s_num'] = $data['s_num'];
      $this->zi_my_func->web_api_data("delivery_person", "upd");
    }
    else {
      if($data['sw30'] == "Y") {
        $dp_data['dp01'] = $sw_data['sw01'];
        $dp_data['dp02'] = $sw_data['sw02'];
        $dp_data['dp03'] = $sw_data['sw03'];
        $dp_aes_fd = array('dp01','dp02','dp03','dp10'); // 加密欄位
        foreach ($dp_data as $k_fd_name => $v_data) {
          if(in_array($k_fd_name,$dp_aes_fd)) { // 加密欄位
            $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
            unset($dp_data[$k_fd_name]);
          }
        }
        $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料檔
        $dp_data['group_s_num'] = 14;
        $dp_data['b_empno'] = $_SESSION['acc_s_num'];
        $dp_data['b_date'] = date('Y-m-d H:i:s');
        $dp_data['sw_s_num'] = $data['s_num'];
        $dp_data['dp04'] = $data['sw04'];
        $dp_data['dp05'] = $data['sw05'];
        $dp_data['dp06'] = $data['sw06'];
        $dp_data['dp07_start'] = $data['sw08_start'];
        $dp_data['dp07_end'] = $data['sw08_end'];
        $dp_data['dp30'] = "Y";
        $dp_data['dp_pwd'] = password_hash($sw_data['sw_pwd'], PASSWORD_DEFAULT); // 密碼要加密  
        $dp_data['is_available'] = $data['is_available'];
        if(!$this->db->insert($tbl_delivery_person, $dp_data)) {
          $rtn_msg = $this->lang->line('add_ng');
          echo $rtn_msg;
          return;
        }
      }
    }
   
    // 刪除
    $this->db->where('sw_s_num', $data['s_num']);
    if(!$this->db->delete($tbl_social_worker_responsible_area)) {
      $rtn_msg = $this->lang->line('del_ng');
      echo $rtn_msg;
      return;
    }
    
    if(NULL != $sw_ra) {   
      $i = 0;
      foreach ($sw_ra['s_num'] as $k => $v) {
        $sw_ra_data_batch[$i]['sw_s_num'] = $data['s_num'];
        $sw_ra_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
        $sw_ra_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
        $sw_ra_data_batch[$i]['sw_ra01'] = $sw_ra['sw_ra01'][$k];
        $sw_ra_data_batch[$i]['sw_ra02'] = $sw_ra['sw_ra02'][$k];
        $sw_ra_data_batch[$i]['sw_ra03'] = $sw_ra['sw_ra03'][$k];
        if($sw_ra['sw_ra01'][$k] == '' or $sw_ra['sw_ra02'][$k] == '' or $sw_ra['sw_ra03'][$k] == '') {
          unset($sw_ra_data_batch[$i]);
        }
        $i++;
      }
      
      if($sw_ra_data_batch != NULL) {
        if(!$this->db->insert_batch($tbl_social_worker_responsible_area , $sw_ra_data_batch)) {
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
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function save_is_available() {
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_social_worker, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    else {
      $_POST['s_num'] = $_POST['f_s_num'];
      $this->zi_my_func->web_api_data("delivery_person", "stop");
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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_social_worker, $data)) {
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
  private function _aes_fd() {
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_social_worker}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
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
      case 'sw01': // 中文姓
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'',-1,1);
        break;
      case 'sw02': // 中文名
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'',-1,1);
        break;
      case 'sw03': // 身份證字號
        $fd_name_mask = "{$fd_name}_mask";
        $fd_val = replace_symbol_text($row->$fd_name,'*',1,6);
        break;
      case 'sw10': // 手機門號
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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組

    $vacc_user     = $_POST['acc_user'];       // 帳號
    $vacc_password = $_POST['acc_password'];  // 密碼
    $vlogincaptcha = $_POST['logincaptcha'];  // 驗證碼
    if(strtolower($vlogincaptcha)==strtolower($_SESSION['cap']['word'])) {
      $vrow = NULL;
      $vsql = "select {$tbl_social_worker}.*,
                      {$tbl_account_group}.acg_name
                      {$this->_aes_fd()}
               from {$tbl_social_worker}
               left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_social_worker}.group_s_num
               where {$tbl_social_worker}.sw05 = ?
                     and {$tbl_social_worker}.is_available = 1
                     and {$tbl_social_worker}.d_date is null
               order by {$tbl_social_worker}.sw05
               limit 0,1
              ";
      //u_var_dump($vsql);
      //echo '<hr>';
      $vrs = $this->db->query($vsql, array($vacc_user));
      if($vrs->num_rows() > 0) { // 有資料才執行
        $vrow = $vrs->row_array();
        //var_dump($vrow);
      }
      if(password_verify($vacc_password, $vrow['sw_pwd'])) { // 密碼正確
        $_SESSION['acc_s_num'] = $vrow['s_num'];
        $_SESSION['acc_user'] = $vrow['sw05'];
        $_SESSION['acc_name'] = "{$vrow['sw01']}{$vrow['sw02']}";
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
        $_SESSION['acc_kind'] = 'SW'; // 同仁
        $_SESSION['acc_sw22'] = $vrow['sw22']; // 社工負責區域
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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工基本資料檔
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群組
    
    // 登入狀態：狀態碼 <=> 代表狀態
    // - 0: "登入成功" (所有成功全部回傳0)
    // - 101: "查無此機構"
    // - 102: "帳號密碼驗證錯誤"
    // - 103: "禁止重複登入"
    // - 104: "帳號密碼不得為空值"

    $json_data = NULL;            
    if(!isset($_POST['acc_user']) or !isset($_POST['acc_password'])) {
      return array(102, $json_data);
    }
    
    if(empty($post_data['acc_user']) or empty($post_data['acc_password'])) {
      return array(102, $json_data); 
    }
    
    $vrow = NULL;
    $vacc_user     = $_POST['acc_user'];       // 帳號
    $vacc_password = $_POST['acc_password'];  // 密碼

    $vsql = "select {$tbl_social_worker}.*,
                    {$tbl_account_group}.acg_name
                    {$this->_aes_fd()}
             from {$tbl_social_worker}
             left join {$tbl_account_group} on {$tbl_account_group}.s_num = {$tbl_social_worker}.group_s_num
             where {$tbl_social_worker}.sw_account = ?
                   and {$tbl_social_worker}.is_available = 1
                   and {$tbl_social_worker}.d_date is null
             order by {$tbl_social_worker}.sw_account
             limit 0,1
            ";
    //u_var_dump($vsql);
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

    if(!empty($vrow->sw_login_last_date)) { // 如果有最後登入時間，要檢查是否超過300秒
      $now_strtotime = strtotime(date("Y-m-d H:i:s"));
      $last_login_strtotime = strtotime($vrow->sw_login_last_date);
      if($now_strtotime - $last_login_strtotime < 300) {
        return array(103, $json_data);
      }
    }

    $json_data["state"] = 0;
    $json_data['login_time'] = date("Y-m-d");
    $json_data['acc_name'] = "{$vrow['sw01']}{$vrow['sw02']}";
    $json_data['acc_auth'] = SW_AUTH_CODE;
    $rand_num = rand(2,6);
    $time = date('U');
    $time_en = substr($this->encryption->encrypt($time),0,4); // 取4碼當混亂使用
    $verify_s_num = $vrow['s_num'];
    $verify_s_num_en = '';
    for($i = 0; $i < strlen($verify_s_num); $i++) {
      $verify_s_num_en .= substr($verify_s_num,$i,1).random_string('alnum', $rand_num);
    }
    $json_data['acc_token'] = "{$time_en}{$rand_num}".base64url_encode($verify_s_num_en);
    $this->save_login_info($vrow['s_num']);

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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 專案成員基本資料檔

    $this->db->set('sw_login_cnt', 'sw_login_cnt+1',FALSE);
    $this->db->set('sw_login_last_date', date('Y-m-d H:i:s'));
    $this->db->set('sw_login_last_ip', $_SERVER["REMOTE_ADDR"]);
    $this->db->set('sw_token', $token);
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_social_worker)) {
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
        $data['sw_pwd'] = password_hash($acc_pwd, PASSWORD_DEFAULT); // 密碼要加密
      }
      $data['e_empno'] = $_SESSION['acc_s_num'];
      $data['e_date'] = date('Y-m-d H:i:s');
      $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 專案成員基本資料檔
      $this->db->where('sw05', $_SESSION['acc_user']);
      if(!$this->db->update($tbl_social_worker, $data)) {
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
    $data['sw_login_last_date'] = NULL;
    $data['sw_token'] = NULL;
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 專案成員基本資料檔
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_social_worker, $data)) {
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
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 專案成員基本資料檔
    $sw_token = hash('sha256', openssl_random_pseudo_bytes(16));

    $this->db->set("sw_token" , $sw_token); // token
    //  $this->db->set("acc_token_exp" , $acc_token_exp); // token 期限
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_social_worker)) {
      return false;
    }
    return $sw_token;
  }
  // **************************************************************************
  //  函數名稱: chk_sw03()
  //  函數功能: 確認社工身分證是否重複
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  public function chk_sw03() {
    $v = $this->input->post();
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 專案成員基本資料檔
    $row = NULL;
    $sql = "select {$tbl_social_worker}.*
            from {$tbl_social_worker}
            where {$tbl_social_worker}.d_date is null
                  and AES_DECRYPT({$tbl_social_worker}.sw03,'{$this->db_crypt_key2}') = ?
            order by {$tbl_social_worker}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($v['sw03']));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
}
?>