<?php
class Service_case_complaint_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_service_case_complaint}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_service_case_complaint}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   {$tbl_route_h}.reh01,
                   case {$tbl_service_case}.sec01
                     when '1' then '長照案'
                     when '2' then '特殊-老案'
                     when '3' then '自費戶'
                     when '4' then '邊緣戶'
                     when '5' then '身障案'
                     when '6' then '特殊-身案'
                   end as sec01_str,
                   case {$tbl_service_case}.sec04
                     when '1' then '午餐'
                     when '2' then '中晚餐'
                     when '3' then '晚餐'
                   end as sec04_str,                   
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') as ct14
            from {$tbl_service_case_complaint}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_service_case_complaint}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_service_case_complaint}.e_empno
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case_complaint}.ct_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_service_case_complaint}.reh_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_service_case_complaint}.sec_s_num
            where {$tbl_service_case_complaint}.d_date is null
                  and {$tbl_service_case_complaint}.s_num = ?
            order by {$tbl_service_case_complaint}.s_num desc
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
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_service_case_complaint}.*
                   
            from {$tbl_service_case_complaint}
            where {$tbl_service_case_complaint}.d_date is null
                  and {$tbl_service_case_complaint}.fd_name = ?
            order by {$tbl_service_case_complaint}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($fd_name));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
      // 遮罩欄位處理 Begin //
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      }
      // 遮罩欄位處理 End //
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function get_all() {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $data = NULL;
    $sql = "select {$tbl_service_case_complaint}.*
                   
            from {$tbl_service_case_complaint}
            where {$tbl_service_case_complaint}.d_date is null
            order by {$tbl_service_case_complaint}.s_num desc
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
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $data = NULL;
    $sql = "select {$tbl_service_case_complaint}.*
                   
            from {$tbl_service_case_complaint}
            where {$tbl_service_case_complaint}.d_date is null
                  and {$tbl_service_case_complaint}.is_available = 1 /* 啟用 */
            order by {$tbl_service_case_complaint}.s_num desc
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
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單

    $where = " {$tbl_service_case_complaint}.d_date is null ";
    $order = " {$tbl_service_case_complaint}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_service_case_complaint}.ct_s_num like '%{$que_str}%' /* 客戶序號-MEMO(tw_clients.s_num) */                       
                       or {$tbl_service_case_complaint}.sec_s_num like '%{$que_str}%' /* 開案服務序號-MEMO(tw_service_case.s_num) */                       
                       or {$tbl_service_case_complaint}.mil_h_s_num like '%{$que_str}%' /* 餐食異動序號-MEMO(tw_meal_instruction_log_h.s_num) */                       
                       or BINARY {$tbl_service_case_complaint}.sect01 like BINARY '%{$que_str}%' /* 受理日期 */                       
                       or {$tbl_service_case_complaint}.sect02 like '%{$que_str}%' /* 照會人員 */                       
                       or {$tbl_service_case_complaint}.sect03 like '%{$que_str}%' /* 照會事項 */                       
                       or {$tbl_service_case_complaint}.sect04 like '%{$que_str}%' /* 原因分析 */                       
                       or {$tbl_service_case_complaint}.sect05 like '%{$que_str}%' /* 改善對策 */                       
                       or {$tbl_service_case_complaint}.sect06 like '%{$que_str}%' /* 處理措施 */                       
                       or {$tbl_service_case_complaint}.sect07 like '%{$que_str}%' /* 評值 */                       
                       or {$tbl_service_case_complaint}.sect99 like '%{$que_str}%' /* 主管簽核 */
                       or {$tbl_route_h}.reh01 like '%{$que_str}%' /* 主管簽核 */
                       or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}'), '', AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */ 
                       or AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 區域 */                        
                      )
                ";
    }

    if(!empty($get_data['que_ct14'])) { // 客戶序號-MEMO(tw_clients.s_num)
      $que_ct14 = $get_data['que_ct14'];
      $que_ct14 = $this->db->escape_like_str($que_ct14);
      $where .= " and AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') like '%{$que_ct14}%' /* 區域 */";
    }
    if(!empty($get_data['que_ct_name'])) { // 客戶序號-MEMO(tw_clients.s_num)
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}'), '', AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_ct_name}%' /* 案主姓名 */ ";
    }
    if(!empty($get_data['que_sec_s_num'])) { // 開案服務序號-MEMO(tw_service_case.s_num)
      $que_sec_s_num = $get_data['que_sec_s_num'];
      $que_sec_s_num = $this->db->escape_like_str($que_sec_s_num);
      $where .= " and {$tbl_service_case_complaint}.sec_s_num = '{$que_sec_s_num}'  /* 開案服務序號-MEMO(tw_service_case.s_num) */ ";
    }
    if(!empty($get_data['que_mil_h_s_num'])) { // 餐食異動序號-MEMO(tw_meal_instruction_log_h.s_num)
      $que_mil_h_s_num = $get_data['que_mil_h_s_num'];
      $que_mil_h_s_num = $this->db->escape_like_str($que_mil_h_s_num);
      $where .= " and {$tbl_service_case_complaint}.mil_h_s_num = '{$que_mil_h_s_num}'  /* 餐食異動序號-MEMO(tw_meal_instruction_log_h.s_num) */ ";
    }
    if(!empty($get_data['que_reh_s_num'])) { // 開案服務序號-MEMO(tw_service_case.s_num)
      $que_reh_s_num = $get_data['que_reh_s_num'];
      $que_reh_s_num = $this->db->escape_like_str($que_reh_s_num);
      $where .= " and {$tbl_service_case_complaint}.reh_s_num = '{$que_reh_s_num}'  /* 路線序號-MEMO(tw_service_case.s_num) */ ";
    }
    if(!empty($get_data['que_sec01'])) { // 開案服務序號-MEMO(tw_service_case.s_num)
      $que_sec01 = $get_data['que_sec01'];
      $que_sec01 = $this->db->escape_like_str($que_sec01);
      $where .= " and {$tbl_service_case}.sec01 = '{$que_sec01}'  /* 餐別 */ ";
    }
    if(!empty($get_data['que_sec04'])) { // 開案服務序號-MEMO(tw_service_case.s_num)
      $que_sec04 = $get_data['que_sec04'];
      $que_sec04 = $this->db->escape_like_str($que_sec04);
      $where .= " and {$tbl_service_case}.sec04 = '{$que_sec04}'  /* 服務現況 */ ";
    }
    if(!empty($get_data['que_sect01'])) { // 受理日期
      $que_sect01 = $get_data['que_sect01'];
      $que_sect01 = $this->db->escape_like_str($que_sect01);
      $where .= " and {$tbl_service_case_complaint}.sect01 = '{$que_sect01}'  /* 受理日期 */ ";
    }
    if(!empty($get_data['que_sect02'])) { // 照會人員
      $que_sect02 = $get_data['que_sect02'];
      $que_sect02 = $this->db->escape_like_str($que_sect02);
      $where .= " and {$tbl_service_case_complaint}.sect02 = '{$que_sect02}'  /* 照會人員 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_service_case_complaint}.s_num
                from {$tbl_service_case_complaint}
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case_complaint}.ct_s_num
                left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_service_case_complaint}.reh_s_num
                left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_service_case_complaint}.sec_s_num
                where $where
                group by {$tbl_service_case_complaint}.s_num
                order by {$tbl_service_case_complaint}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_service_case_complaint}.*,
                   {$tbl_route_h}.reh01,
                   case {$tbl_service_case}.sec01
                     when '1' then '長照案'
                     when '2' then '特殊-老案'
                     when '3' then '自費戶'
                     when '4' then '邊緣戶'
                     when '5' then '身障案'
                     when '6' then '特殊-身案'
                   end as sec01_str,
                   case {$tbl_service_case}.sec04
                     when '1' then '午餐'
                     when '2' then '中晚餐'
                     when '3' then '晚餐'
                   end as sec04_str,                   
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') as ct14
            from {$tbl_service_case_complaint}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case_complaint}.ct_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_service_case_complaint}.reh_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_service_case_complaint}.sec_s_num
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
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function save_add() {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $rtn_msg = 'ok';
    $data = $this->input->post();
    // 加密欄位處理 Begin //
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
    // 加密欄位處理 End //
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    
    if(!$this->db->insert($tbl_service_case_complaint, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function save_upd() {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $rtn_msg = 'ok';
    $data = $this->input->post();
    // 加密欄位處理 Begin //
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    } 
    // 加密欄位處理 End //
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_service_case_complaint, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function save_is_available() {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_service_case_complaint, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function del() {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_service_case_complaint, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: que_by_dnn()
  //  函數功能: 查詢該筆照會營養師是否有建立客訴單
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  public function que_by_dnn($source, $dnn01_source_s_num) {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $where = '';
    if('meal' == $source) {
      $where .= " and {$tbl_service_case_complaint}.mil_h_s_num  = {$dnn01_source_s_num}";
    }
    if('item' == $source) {
      $where .= " and {$tbl_service_case_complaint}.ocl_h_s_num  = {$dnn01_source_s_num}";
    }
    $row = NULL;
    $sql = "select {$tbl_service_case_complaint}.*
            from {$tbl_service_case_complaint}
            where {$tbl_service_case_complaint}.d_date is null
                  {$where}
            order by {$tbl_service_case_complaint}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
  // **************************************************************************
  private function _aes_fd() {
    $tbl_service_case_complaint = $this->zi_init->chk_tbl_no_lang('service_case_complaint'); // 客訴處理單
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_service_case_complaint}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-26
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
}
?>