<?php
class Meal_instruction_log_h_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd1 = array('ct_name');  
  public $aes_fd2 = array('ct01' , 'ct02');  
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
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_h}.*,
                   case {$tbl_meal_instruction_log_h}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04,                   
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as reh_type,
                   {$tbl_meal_instruction_log_h}.mil03,
                   IF(sys_acc.acc_name is null ,
                     concat(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc.acc_name
                   ) as b_acc_name,
                   IF(sys_acc2.acc_name is null ,
                     concat(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc2.acc_name
                   ) as e_acc_name
                   {$this->_aes_fd('service_case')}
                   {$this->_aes_fd('clients')}
                   from {$tbl_meal_instruction_log_h}
                   left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_meal_instruction_log_h}.b_empno
                   left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_meal_instruction_log_h}.e_empno
                   left join {$tbl_social_worker} sw on sw.s_num = {$tbl_meal_instruction_log_h}.b_empno
                   left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_meal_instruction_log_h}.e_empno
                   left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_instruction_log_h}.sec_s_num
                   left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_meal_instruction_log_h}.ct_s_num
                   where {$tbl_meal_instruction_log_h}.d_date is null
                        and {$tbl_meal_instruction_log_h}.s_num = ?
                   order by {$tbl_meal_instruction_log_h}.s_num desc
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
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_h}.*
                   
            from {$tbl_meal_instruction_log_h}
            where {$tbl_meal_instruction_log_h}.d_date is null
                  and {$tbl_meal_instruction_log_h}.fd_name = ?
            order by {$tbl_meal_instruction_log_h}.s_num desc
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
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_h}.*
                   
            from {$tbl_meal_instruction_log_h}
            where {$tbl_meal_instruction_log_h}.d_date is null
            order by {$tbl_meal_instruction_log_h}.s_num desc
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
  //  函數名稱: get_all_by_mil03()
  //  函數功能: 取得所有審核通過的資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-06
  // **************************************************************************
  public function get_all_by_mil03() {
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $post_data = $this->input->post();
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_h}.*
            from {$tbl_meal_instruction_log_h}
            where {$tbl_meal_instruction_log_h}.d_date is null
                  and {$tbl_meal_instruction_log_h}.mil03 = 'Y'
                  and {$tbl_meal_instruction_log_h}.mil01 between '{$post_data['que_mil01_stop_start']}' and '{$post_data['que_mil01_stop_end']}'
            order by {$tbl_meal_instruction_log_h}.s_num desc
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
  //  設計日期: 2021-0
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $where = " {$tbl_meal_instruction_log_h}.d_date is null ";
    $order = " {$tbl_meal_instruction_log_h}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      // or AES_DECRYPT({$tbl_meal_instruction_log_h}.ct_name,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 異動類型 */  
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_meal_instruction_log_h}.ct_s_num like '%{$que_str}%' /* tw_clients.s_num */                       
                       or {$tbl_meal_instruction_log_h}.sec_s_num like '%{$que_str}%' /* tw_service_case.s_num */                       
                       or {$tbl_meal_instruction_log_h}.mil01 like binary '%{$que_str}%' /* 指令生效開始日期 */                       
                       or {$tbl_meal_instruction_log_h}.mil02 like binary '%{$que_str}%' /* 異動類型 */  
                       or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */ 
                      )
                ";
    }
    
    if(!empty($get_data['que_sec_s_num'])) { // 服務開始日
      $que_sec_s_num = $get_data['que_sec_s_num'];
      $que_sec_s_num = $this->db->escape_like_str($que_sec_s_num);
      $where .= " and {$tbl_meal_instruction_log_h}.sec_s_num = {$que_sec_s_num}  /* 服務編號 */ ";
    }
    
    if(!empty($get_data['que_type'])) {
      $que_type = $get_data['que_type'];
       $order = " {$tbl_meal_instruction_log_h}.{$que_type} desc ";
      if(!empty($get_data['que_mil01_start']) && !empty($get_data['que_mil01_end'])) { // 服務開始日
        $que_mil01_start = $get_data['que_mil01_start'];
        $que_mil01_end = $get_data['que_mil01_end'];
        $where .= " and {$tbl_meal_instruction_log_h}.{$que_type} >= '{$que_mil01_start} 00:00:00' 
                               and {$tbl_meal_instruction_log_h}.{$que_type} <= '{$que_mil01_end} 23:59:59' /* 服務編號 */ ";
      }
      else {
        if(!empty($get_data['que_mil01_start'])) { // 指令生效開始日期
          $que_mil01_start = $get_data['que_mil01_start'];
          $que_mil01_start = $this->db->escape_like_str($que_mil01_start);
          $where .= " and {$tbl_meal_instruction_log_h}.{$que_type} >= '{$que_mil01_start}'  /* 指令生效開始日期 */ ";
        }
        if(!empty($get_data['que_mil01_end'])) { // 指令生效開始日期
          $que_mil01_end = $get_data['que_mil01_end'];
          $que_mil01_end = $this->db->escape_like_str($que_mil01_end);
          $where .= " and {$tbl_meal_instruction_log_h}.{$que_type} <= '{$que_mil01_end}'  /* 指令生效開始日期 */ ";
        }
      }
    }
    
    if(!empty($get_data['que_ct_name'])) { // 案主姓
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and {$tbl_meal_instruction_log_h}.ct_name like '%{$que_ct_name}%'  /* 案主姓名 */ ";
    }
    
    if(!empty($get_data['que_mil01'])) { // 指令生效開始日期
      $que_mil01 = $get_data['que_mil01'];
      $que_mil01 = $this->db->escape_like_str($que_mil01);
      $where .= " and {$tbl_meal_instruction_log_h}.mil01 like binary '{$que_mil01}'  /* 指令生效開始日期 */ ";
    }

    if(!empty($get_data['que_mil02'])) { // 異動類型
      $que_mil02 = $get_data['que_mil02'];
      $que_mil02 = $this->db->escape_like_str($que_mil02);
      $where .= " and {$tbl_meal_instruction_log_h}.mil02 like binary '%{$que_mil02}%'  /* 指令截止日期 */ ";
    }

    if(!empty($get_data['que_mil03'])) { // 是否審核通過
      $que_mil03 = $get_data['que_mil03'];
      $que_mil03 = $this->db->escape_like_str($que_mil03);
      $where .= " and {$tbl_meal_instruction_log_h}.mil03 = '{$que_mil03}'  /* Y=復餐;N=停餐 */ ";
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
    $sql_cnt = "select {$tbl_meal_instruction_log_h}.s_num
                from {$tbl_meal_instruction_log_h}
                left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_instruction_log_h}.sec_s_num
                left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_meal_instruction_log_h}.ct_s_num
                where {$where}
                      and {$tbl_clients}.is_available = 1
                      and {$tbl_clients}.d_date is null
                group by {$tbl_meal_instruction_log_h}.s_num
                order by {$tbl_meal_instruction_log_h}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_meal_instruction_log_h}.*,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04,
                   {$tbl_service_case}.sec99,
                   {$tbl_meal_instruction_log_h}.mil03
                  {$this->_aes_fd('service_case')}
                  {$this->_aes_fd('clients')}
                  from {$tbl_meal_instruction_log_h}
                  left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_instruction_log_h}.sec_s_num
                  left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_meal_instruction_log_h}.ct_s_num
                  where {$where}
                        and {$tbl_clients}.is_available = 1
                        and {$tbl_clients}.d_date is null
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return(array($data,$row_cnt));
  }
  
  //**************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $mil_h = $data['mil_h'];
    foreach ($mil_h as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($mil_h[$k_fd_name]);
      }
    }
    $mil_h['b_empno'] = $_SESSION['acc_s_num'];
    $mil_h['b_date'] = date('Y-m-d H:i:s');
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
            case 6: // 一次性出餐異動
              $fd_type = $data['mil_d'];
              $fd_type["mil_d02"] = $mil_h['mil01'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 餐點異動資料-檔身(補班日出餐)
              break;
          }
             
          $fd_type['b_empno'] = $_SESSION['acc_s_num'];
          $fd_type['b_date'] = date('Y-m-d H:i:s');
          $fd_type['mil_h_s_num'] = $mil_h_s_num;
          $fd_type['sec_s_num'] = $mil_h['sec_s_num'];
          if(!$this->db->insert($fd_tbl, $fd_type)) {
            $rtn_msg = $this->lang->line('add_ng');
          }
          
        } 
      }
      if(!$this->meal_instruction_auth_model->add_auth_data($mil_h_s_num)) {
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
    $mil_h = $data['mil_h'];
    foreach ($mil_h as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($mil_h[$k_fd_name]);
      }
    }

    $meal_instruction_log_h_row = $this->meal_instruction_log_h_model->get_one($mil_h['s_num']); // 列出單筆明細資料
    $mil02_arr = explode(",", $meal_instruction_log_h_row->mil02);
    if(NULL != $mil02_arr) {
      foreach ($mil02_arr as $v) {
        switch ($v) {   
          case 1: // 餐點異動      
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_m'); // 餐點異動資料-檔身(餐點異動)
            break;
          case 2: // 代餐異動      
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_mp'); // 餐點異動資料-檔身(代餐異動)
            break;  
          case 3: // 停復餐
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 餐點異動資料-檔身(停復餐)
            break;  
          case 4: // 固定暫停      
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_p'); // 餐點異動資料-檔身(固定暫停)
            break;   
          case 5: // 自費     
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_i'); // 餐點異動資料-檔身(自費)
            break;   
          case 6: // 一次性出餐異動
            $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 餐點異動資料-檔身(補班日出餐)
            break; 
        }
        
        $this->db->where('mil_h_s_num', $mil_h['s_num']);
        if(!$this->db->delete($fd_tbl)) {
          $rtn_msg = $this->lang->line('del_ng');
          echo $rtn_msg;
          return;
        }
      }
    }

    $mil_h['e_empno'] = $_SESSION['acc_s_num'];
    $mil_h['e_date'] = date('Y-m-d H:i:s');
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $this->db->where('s_num', $mil_h['s_num']);
    if(!$this->db->update($tbl_meal_instruction_log_h, $mil_h)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    else {
      $mil_h_s_num = $mil_h['s_num'];
      $mil02_arr = explode(",", $mil_h["mil02"]);
      if(NULL != $mil02_arr) {
        foreach ($mil02_arr as $v) {
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
            case 6: // 一次性出餐異動
              $fd_type = $data['mil_d'];
              $fd_type["mil_d02"] = $mil_h['mil01'];
              $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 餐點異動資料-檔身(補班日出餐)
              break; 
          }
          
          $fd_type['b_empno'] = $_SESSION['acc_s_num'];
          $fd_type['b_date'] = date('Y-m-d H:i:s');
          $fd_type['mil_h_s_num'] = $mil_h_s_num;
          $fd_type['sec_s_num'] = $mil_h['sec_s_num'];
          
          if(!$this->db->insert($fd_tbl, $fd_type)) {
            $rtn_msg = $this->lang->line('add_ng');
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
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_meal_instruction_log_h, $data)) {
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
    $mil_h_s_num = $v['s_num'];
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $meal_instruction_log_h_row = $this->get_one($v['s_num']);
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $this->db->where('s_num', $mil_h_s_num);
    if(!$this->db->update($tbl_meal_instruction_log_h, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    else {
      if(NULL != $meal_instruction_log_h_row) {
        $mil02_arr = explode(",", $meal_instruction_log_h_row->mil02);
        if(NULL != $mil02_arr) {
          foreach ($mil02_arr as &$v) {
            switch ($v) {   
              case 1: // 餐點異動      
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_m'); // 餐點異動資料-檔身(餐點異動)
              break;
              case 2: // 代餐異動      
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_mp'); // 餐點異動資料-檔身(代餐異動)
              break;  
              case 3: // 停復餐
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 餐點異動資料-檔身(停復餐)
              break;  
              case 4: // 固定暫停      
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_p'); // 餐點異動資料-檔身(固定暫停)
              break;   
              case 5: // 自費     
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_i'); // 餐點異動資料-檔身(自費)
              break;    
              case 6: // 自費     
                $fd_tbl = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 餐點異動資料-檔身(補班日出餐)
              break;   
            }
            
            $this->db->where('mil_h_s_num', $mil_h_s_num);
            if(!$this->db->update($fd_tbl, $data)) {
              $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
            }            
          } 
        }
      }
      $tbl_meal_instruction_auth = $this->zi_init->chk_tbl_no_lang('meal_instruction_auth'); // 異動單審核紀錄檔
      $this->db->where('mil_s_num', $mil_h_s_num);
      if(!$this->db->update($tbl_meal_instruction_auth, $data)) {
        $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
      }            
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
  //  設計日期: 2021-01-31
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
  //  函數名稱: upd_mil03_by_s_num()
  //  函數功能: 更新異動單審核狀況
  //  程式設計: kiwi
  //  設計日期: 2021-04-15
  // **************************************************************************
  public function upd_mil03_by_s_num($mil_s_num) {
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
    $data['mil03'] = "Y";
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $this->db->where('s_num', $mil_s_num);
    if(!$this->db->update($tbl_meal_instruction_log_h, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    else {
      $this->db->where('mil_h_s_num', $mil_s_num);
      $tbl_meal_instruction_log_m = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_m'); // 餐點異動資料
      if(!$this->db->update($tbl_meal_instruction_log_m, array("mil_m03" => "Y"))) { // 餐點異動
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_meal_instruction_log_mp = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_mp'); // 代餐異動資料
      if(!$this->db->update($tbl_meal_instruction_log_mp, array("mil_mp03" => "Y"))) { // 代餐異動
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料
      if(!$this->db->update($tbl_meal_instruction_log_s, array("mil_s03" => "Y"))) { // 停復餐
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_meal_instruction_log_p = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_p'); // 固定暫停異動資料
      if(!$this->db->update($tbl_meal_instruction_log_p, array("mil_p03" => "Y"))) { // 固定暫停
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_meal_instruction_log_i = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_i'); // 自費戶異動資料
      if(!$this->db->update($tbl_meal_instruction_log_i, array("mil_i03" => "Y"))) { // 自費戶
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $tbl_meal_instruction_log_d = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 一次性出餐異動
      if(!$this->db->update($tbl_meal_instruction_log_d, array("mil_d03" => "Y"))) { // 補班日出餐
        $rtn_msg = $this->lang->line('upd_ng');
      }
      $meal_instruction_log_row = $this->get_one($mil_s_num);
      if(NULL != $meal_instruction_log_row) {
        $mil02_arr = explode(",", $meal_instruction_log_row->mil02);
        if(in_array(3 , $mil02_arr)) { // 如果停餐在array裡面
          $mil_s_row = $this->get_s_by_s_num($mil_s_num);
          // 代餐資料不用自動更新
          // if(!$this->daily_work_model->update_meal_replacement("s" , $mil_s_row)) {
          //   die($this->lang->line('upd_ng'));
          //   return;
          // }
          // 
          if(strtotime(date("Y-m-d")) >= strtotime($meal_instruction_log_row->mil01)) { // 如果停復餐異動資料有當天的話，直接更新上去配送單
            $mil_mp_row = $this->get_mp_by_s_num($mil_s_num);
            if(!$this->daily_work_model->update_daily_shipment($mil_s_row, $mil_mp_row)) {
              die($this->lang->line('upd_ng'));
              return;
            }
            if(!$this->meal_order_model->upd_meal_order_by_sec_s_num($mil_s_row, $mil_mp_row)) {
              die($this->lang->line('upd_ng'));
              return;
            }
          }
        }
        // 代餐資料不用自動更新
        // if(in_array(2 , $mil02_arr)) { // 如果代餐在array裡面
        //   $mil_mp_row = $this->get_mp_by_s_num($mil_s_num);
        //   if(!$this->daily_work_model->update_meal_replacement("mp" , $mil_mp_row)) {
        //     $rtn_msg = $this->lang->line('upd_ng');
        //   }
        // }
      }
    }
    echo $rtn_msg;
    return;
  }
  
  // **************************************************************************
  //  函數名稱: get_by_sec_s_num_last()
  //  函數功能: 取得最後一筆異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_que_by_sec_s_num($ct_s_num,$sec_s_num) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_h}.*, 
                   count(*) as cnt
            from {$tbl_meal_instruction_log_h}
            where {$tbl_meal_instruction_log_h}.d_date is null
                  and {$tbl_meal_instruction_log_h}.ct_s_num = {$ct_s_num}
                  and {$tbl_meal_instruction_log_h}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_instruction_log_h}.mil03 = 'Y'
            order by {$tbl_meal_instruction_log_h}.b_date desc
            limit 1
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
  //  函數名稱: get_all_m_by_sec()
  //  函數功能: 取得餐點異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_all_m_by_sec($sec_s_num) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_meal_instruction_log_m = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_m'); // 餐點異動資料
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_m}.*, 
                   {$tbl_meal}.ml01
            from {$tbl_meal_instruction_log_m}
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_instruction_log_m}.ml_s_num
            where {$tbl_meal_instruction_log_m}.d_date is null
                  and {$tbl_meal_instruction_log_m}.sec_s_num = {$sec_s_num}
            order by {$tbl_meal_instruction_log_m}.b_date desc
            limit 3
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_mp_by_sec()
  //  函數功能: 取得代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_all_mp_by_sec($sec_s_num) {
    $tbl_meal_instruction_log_mp = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_mp'); // 餐點異動資料
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_mp}.*
            from {$tbl_meal_instruction_log_mp}
            where {$tbl_meal_instruction_log_mp}.d_date is null
                  and {$tbl_meal_instruction_log_mp}.sec_s_num = {$sec_s_num}
            order by {$tbl_meal_instruction_log_mp}.b_date desc
            limit 3
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_s_by_sec()
  //  函數功能: 取得停復餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_all_s_by_sec($sec_s_num) {
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_s}.*
            from {$tbl_meal_instruction_log_s}
            where {$tbl_meal_instruction_log_s}.d_date is null
                  and {$tbl_meal_instruction_log_s}.sec_s_num = {$sec_s_num}
            order by {$tbl_meal_instruction_log_s}.b_date desc
            limit 3
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_p_by_sec()
  //  函數功能: 取得固定暫停資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_all_p_by_sec($sec_s_num) {
    $tbl_meal_instruction_log_p = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_p'); // 停復餐異動資料
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_p}.*
            from {$tbl_meal_instruction_log_p}
            where {$tbl_meal_instruction_log_p}.d_date is null
                  and {$tbl_meal_instruction_log_p}.sec_s_num = {$sec_s_num}
            order by {$tbl_meal_instruction_log_p}.b_date desc
            limit 3
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_i_by_sec()
  //  函數功能: 取得自費資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_all_i_by_sec($sec_s_num) {
    $tbl_meal_instruction_log_i = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_i'); // 停復餐異動資料
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_i}.*
            from {$tbl_meal_instruction_log_i}
            where {$tbl_meal_instruction_log_i}.d_date is null
                  and {$tbl_meal_instruction_log_i}.sec_s_num = {$sec_s_num}
            order by {$tbl_meal_instruction_log_i}.b_date desc
            limit 3
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_d_by_sec()
  //  函數功能: 取得補班日異動資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-27
  // **************************************************************************
  public function get_all_d_by_sec($sec_s_num) {
    $tbl_meal_instruction_log_d = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 補班日異動資料
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_d}.*
            from {$tbl_meal_instruction_log_d}
            where {$tbl_meal_instruction_log_d}.d_date is null
                  and {$tbl_meal_instruction_log_d}.sec_s_num = {$sec_s_num}
            order by {$tbl_meal_instruction_log_d}.b_date desc
            limit 3
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }

  // **************************************************************************
  //  函數名稱: get_m_by_s_num()
  //  函數功能: 取得餐點異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_m_by_s_num($mil_h_s_num) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_meal_instruction_log_m = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_m'); // 餐點異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_m}.*, 
                   meal.ml01,
                   meal_before.ml01 as ml01_before
            from {$tbl_meal_instruction_log_m}
            left join {$tbl_meal} meal on meal.s_num = {$tbl_meal_instruction_log_m}.ml_s_num
            left join {$tbl_meal} meal_before on meal_before.s_num = {$tbl_meal_instruction_log_m}.ml_s_num_before
            where {$tbl_meal_instruction_log_m}.d_date is null
                  and {$tbl_meal_instruction_log_m}.mil_h_s_num = {$mil_h_s_num}
            order by {$tbl_meal_instruction_log_m}.b_date desc
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
  //  函數名稱: get_mp_by_s_num()
  //  函數功能: 取得代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_mp_by_s_num($mil_h_s_num) {
    $tbl_meal_instruction_log_mp = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_mp'); // 餐點異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_mp}.*
            from {$tbl_meal_instruction_log_mp}
            where {$tbl_meal_instruction_log_mp}.d_date is null
                  and {$tbl_meal_instruction_log_mp}.mil_h_s_num = {$mil_h_s_num}
            order by {$tbl_meal_instruction_log_mp}.b_date desc
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
  //  函數名稱: get_s_by_s_num()
  //  函數功能: 取得停復餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_s_by_s_num($mil_h_s_num) {
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_s}.*
            from {$tbl_meal_instruction_log_s}
            where {$tbl_meal_instruction_log_s}.d_date is null
                  and {$tbl_meal_instruction_log_s}.mil_h_s_num = {$mil_h_s_num}
            order by {$tbl_meal_instruction_log_s}.b_date desc
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
  //  函數名稱: get_p_by_s_num()
  //  函數功能: 取得固定暫停資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_p_by_s_num($mil_h_s_num) {
    $tbl_meal_instruction_log_p = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_p'); // 停復餐異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_p}.*
            from {$tbl_meal_instruction_log_p}
            where {$tbl_meal_instruction_log_p}.d_date is null
                  and {$tbl_meal_instruction_log_p}.mil_h_s_num = {$mil_h_s_num}
            order by {$tbl_meal_instruction_log_p}.b_date desc
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
  //  函數名稱: get_i_by_s_num()
  //  函數功能: 取得自費資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_i_by_s_num($mil_h_s_num) {
    $tbl_meal_instruction_log_i = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_i'); // 停復餐異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_i}.*
            from {$tbl_meal_instruction_log_i}
            where {$tbl_meal_instruction_log_i}.d_date is null
                  and {$tbl_meal_instruction_log_i}.mil_h_s_num = {$mil_h_s_num}
            order by {$tbl_meal_instruction_log_i}.b_date desc
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
  //  函數名稱: get_d_by_s_num()
  //  函數功能: 取得補班日異動資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-27
  // **************************************************************************
  public function get_d_by_s_num($mil_h_s_num) {
    $tbl_meal_instruction_log_d = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 補班日異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_d}.*
            from {$tbl_meal_instruction_log_d}
            where {$tbl_meal_instruction_log_d}.d_date is null
                  and {$tbl_meal_instruction_log_d}.mil_h_s_num = {$mil_h_s_num}
            order by {$tbl_meal_instruction_log_d}.b_date desc
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
  //  函數名稱: get_last_m_by_s_num()
  //  函數功能: 取得最後一筆餐點異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_last_m_by_s_num($sec_s_num , $produce_date=NULL) {
    if(isset($_POST["produce_date"])) {
      $produce_date = $this->input->post("produce_date");
    }
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_meal_instruction_log_m = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_m'); // 餐點異動資料

    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_m}.*, 
                   meal.s_num as ml_s_num,
                   meal.ml01,
                   sub_meal.s_num as sub_ml_s_num,
                   sub_meal.ml01 as sub_ml01
            from {$tbl_meal_instruction_log_m}
            left join {$tbl_meal} meal on meal.s_num = {$tbl_meal_instruction_log_m}.ml_s_num
            left join {$tbl_meal} sub_meal on sub_meal.s_num = meal.ml06_ml_s_num
            where {$tbl_meal_instruction_log_m}.d_date is null
                  and {$tbl_meal_instruction_log_m}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_instruction_log_m}.mil_m02 <= '{$produce_date}'
                  and {$tbl_meal_instruction_log_m}.mil_m03 = 'Y'
            order by {$tbl_meal_instruction_log_m}.mil_m02 desc ,  {$tbl_meal_instruction_log_m}.b_date desc
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
  //  函數名稱: get_last_mp_by_s_num()
  //  函數功能: 取得最後一筆代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_last_mp_by_s_num($sec_s_num , $produce_date=NULL) {
    if(isset($_POST["produce_date"])) {
      $produce_date = $this->input->post("produce_date");
    }
    $tbl_meal_instruction_log_mp = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_mp'); // 餐點異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_mp}.*
            from {$tbl_meal_instruction_log_mp}
            where {$tbl_meal_instruction_log_mp}.d_date is null
                  and {$tbl_meal_instruction_log_mp}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_instruction_log_mp}.mil_mp02 <= '{$produce_date}'
                  and {$tbl_meal_instruction_log_mp}.mil_mp03 = 'Y'
            order by {$tbl_meal_instruction_log_mp}.mil_mp02 desc ,  {$tbl_meal_instruction_log_mp}.b_date desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      foreach ($this->aes_fd1 as $k => $v) {
        list($fd_name, $fd_val) = $this->_symbol_text($row, $v);
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
  //  函數名稱: get_last_s_by_s_num()
  //  函數功能: 取得最後一筆停復餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-04
  // **************************************************************************
  public function get_last_s_by_s_num($sec_s_num , $produce_date=NULL) {
    if(isset($_POST["produce_date"])) {
      $produce_date = $this->input->post("produce_date");
    }
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_s}.*
            from {$tbl_meal_instruction_log_s}
            where {$tbl_meal_instruction_log_s}.d_date is null
                  and {$tbl_meal_instruction_log_s}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_instruction_log_s}.mil_s02 <= '{$produce_date}'
                  and {$tbl_meal_instruction_log_s}.mil_s03 = 'Y'
            order by {$tbl_meal_instruction_log_s}.mil_s02 desc ,  {$tbl_meal_instruction_log_s}.b_date desc
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
  //  函數名稱: get_last_p_by_s_num()
  //  函數功能: 取得最後一筆固定暫停資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_last_p_by_s_num($sec_s_num , $produce_date=NULL) {
    if(isset($_POST["produce_date"])) {
      $produce_date = $this->input->post("produce_date");
    }
    $tbl_meal_instruction_log_p = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_p'); // 停復餐異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_p}.*
            from {$tbl_meal_instruction_log_p}
            where {$tbl_meal_instruction_log_p}.d_date is null
                  and {$tbl_meal_instruction_log_p}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_instruction_log_p}.mil_p02 <= '{$produce_date}'
                  and {$tbl_meal_instruction_log_p}.mil_p03 = 'Y'
            order by {$tbl_meal_instruction_log_p}.mil_p02 desc , {$tbl_meal_instruction_log_p}.b_date desc
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
  //  函數名稱: get_last_i_by_s_num()
  //  函數功能: 取得最後一筆固定暫停資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function get_last_i_by_s_num($sec_s_num , $produce_date=NULL) {
    if(isset($_POST["produce_date"])) {
      $produce_date = $this->input->post("produce_date");
    }
    $tbl_meal_instruction_log_i = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_i'); // 自費戶異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_i}.*
            from {$tbl_meal_instruction_log_i}
            where {$tbl_meal_instruction_log_i}.d_date is null
                  and {$tbl_meal_instruction_log_i}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_instruction_log_i}.mil_i02 <= '{$produce_date}'
                  and {$tbl_meal_instruction_log_i}.mil_i03 = 'Y'
            order by {$tbl_meal_instruction_log_i}.mil_i02 desc , {$tbl_meal_instruction_log_i}.b_date desc
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
  //  函數名稱: get_last_d_by_s_num()
  //  函數功能: 取得生產日當天補餐日異動資料
  //  程式設計: kiwi
  //  設計日期: 2022-01-27
  // **************************************************************************
  public function get_last_d_by_s_num($sec_s_num , $produce_date=NULL) {
    if(isset($_POST["produce_date"])) {
      $produce_date = $this->input->post("produce_date");
    }
    $tbl_meal_instruction_log_d = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_d'); // 補餐日異動資料
    $row = NULL;
    $sql = "select {$tbl_meal_instruction_log_d}.*
            from {$tbl_meal_instruction_log_d}
            where {$tbl_meal_instruction_log_d}.d_date is null
                  and {$tbl_meal_instruction_log_d}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_instruction_log_d}.mil_d02 = '{$produce_date}'
                  and {$tbl_meal_instruction_log_d}.mil_d03 = 'Y'
            order by {$tbl_meal_instruction_log_d}.mil_d02 desc , {$tbl_meal_instruction_log_d}.b_date desc
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
  //  函數名稱: get_by_sec_s_num()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_by_sec_s_num($sec_s_num) {
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $sec_s_num = $this->db->escape_like_str($sec_s_num);
    $row = NULL;
    $sql = "select count(*) as cnt
                   from {$tbl_meal_instruction_log_h}
                   where {$tbl_meal_instruction_log_h}.d_date is null
                        and {$tbl_meal_instruction_log_h}.sec_s_num = ?
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sec_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd1 as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
    }
    return $row;
  }
  
  // **************************************************************************
  //  函數名稱: get_stop_by_date()
  //  函數功能: 查詢停餐名單
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_stop_by_date() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 餐點異動資料
    $v = $this->input->post();
    $where = "{$tbl_meal_instruction_log_h}.d_date is null 
               and {$tbl_meal_instruction_log_h}.mil02 like '%3%'
             ";
    if(!empty($v['que_mil01_stop_start']) && !empty($v['que_mil01_stop_end'])) { // 服務開始日
        $que_mil01_start = $v['que_mil01_stop_start'];
        $que_mil01_end = $v['que_mil01_stop_end'];
        $where .= " and {$tbl_meal_instruction_log_h}.mil01 >= '{$que_mil01_start} 00:00:00' 
                    and {$tbl_meal_instruction_log_h}.mil01 <= '{$que_mil01_end} 23:59:59' /* 服務編號 */ ";
    }
    else {
      if(!empty($v['que_mil01_stop_start'])) { // 指令生效開始日期
        $que_mil01_start = $v['que_mil01_stop_start'];
        $que_mil01_start = $this->db->escape_like_str($que_mil01_start);
        $where .= " and {$tbl_meal_instruction_log_h}.mil01 >= '{$que_mil01_start}'  /* 指令生效開始日期 */ ";
      }
      if(!empty($v['que_mil01_stop_end'])) { // 指令生效開始日期
        $que_mil01_end = $v['que_mil01_stop_end'];
        $que_mil01_end = $this->db->escape_like_str($que_mil01_end);
        $where .= " and {$tbl_meal_instruction_log_h}.mil01 <= '{$que_mil01_end}'  /* 指令生效開始日期 */ ";
      }
    }
    
    $data = NULL;
    $sql = "select {$tbl_meal_instruction_log_h}.*,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as reh_type
                   {$this->_aes_fd('service_case')}
                   {$this->_aes_fd('clients')}
                   from {$tbl_meal_instruction_log_h}
                   left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_instruction_log_h}.ct_s_num
                   left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_instruction_log_h}.sec_s_num
                   left join {$tbl_meal_instruction_log_s} on {$tbl_meal_instruction_log_s}.mil_h_s_num = {$tbl_meal_instruction_log_h}.s_num
                   where {$where}
                          and {$tbl_meal_instruction_log_s}.mil_s01 = 'N'
                   order by {$tbl_meal_instruction_log_h}.mil01 asc
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_stop_by_date_sec04()
  //  函數功能: 查詢停餐名單
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_stop_by_date_sec04($month, $case, $type) {
    $start_date = date("Y-m-01", strtotime($month));
    $end_date = date("Y-m-t", strtotime($month));

    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_meal_instruction_log_h = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 餐點異動資料

    $data = NULL;
    $sql = "select {$tbl_route_h}.s_num, 
              COUNT(distinct {$tbl_meal_order}.ct_s_num) AS clients_count
            FROM {$tbl_meal_instruction_log_h}
            LEFT JOIN {$tbl_clients} ON {$tbl_clients}.s_num = {$tbl_meal_instruction_log_h}.ct_s_num
            LEFT JOIN {$tbl_service_case} ON {$tbl_service_case}.s_num = {$tbl_meal_instruction_log_h}.sec_s_num
            LEFT JOIN {$tbl_meal_order} ON {$tbl_meal_order}.sec_s_num = $tbl_service_case.s_num
            LEFT JOIN {$tbl_route_h} ON {$tbl_meal_order}.reh_s_num = {$tbl_route_h}.s_num
            LEFT JOIN {$tbl_meal_instruction_log_s} ON {$tbl_meal_instruction_log_s}.mil_h_s_num = {$tbl_meal_instruction_log_h}.s_num
            WHERE {$tbl_meal_instruction_log_h}.mil01 >= '{$start_date}' 
              AND {$tbl_meal_instruction_log_h}.mil01 <= '{$end_date}'
              AND {$tbl_meal_instruction_log_s}.mil_s01 = 'N'
              AND {$tbl_service_case}.sec04 = $case
              AND {$tbl_service_case}.sec04 = $type
            GROUP BY {$tbl_route_h}.s_num
            ORDER BY {$tbl_route_h}.s_num ASC
           ";
                 
    //u_var_dump($sql);
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
      case 'mil02':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");
          $fd_val = str_replace(",", "、", $fd_val);
        break;
      case 'mil_m01_1':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'mil_m01_1_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_m01_1", $fd_val, "multiple");      
        break;
      case 'mil_m01_2':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'mil_m01_2_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_m01_2", $fd_val, "multiple");      
        break;
      case 'mil_m01_3':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'mil_m01_3_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_m01_3", $fd_val, "multiple");      
        break;
      case 'mil_m01_4':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'mil_m01_4_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_m01_4", $fd_val, "multiple");      
        break;
      case 'mil_m01_5':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'mil_m01_5_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_m01_5", $fd_val, "multiple");      
        break;
      case 'mil_mp01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'mil_mp01_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_mp01", $fd_val);      
        break;
      case 'mil_mp01_type':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'mil_mp01_type_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_mp01_type", $fd_val);      
        break;
      case 'mil_s01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'mil_s01_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_s01", $fd_val);      
        break;        
      case 'mil_s01_reason':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'mil_s01_reason_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_s01_reason", $fd_val);      
        break;   
      case 'mil_i01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'mil_i01_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_i01", $fd_val);      
        break;        
      case 'mil_p01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'mil_p01_before':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("mil_p01", $fd_val, "multiple");      
        break;
      case 'mil_d01':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val, "multiple");      
        break;
      case 'mil03':
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