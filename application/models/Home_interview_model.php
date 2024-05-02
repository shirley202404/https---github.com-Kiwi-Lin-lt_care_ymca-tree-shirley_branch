<?php
class Home_interview_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('XX'); // 加密欄位
  public $aes_fd_ct = array('ct01' , 'ct02' , 'ct03' , 'ct14'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_home_interview}.*,
                   concat(AES_DECRYPT(sw_acc.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc.sw02,'{$this->db_crypt_key2}')) as b_acc_name, 
                   concat(AES_DECRYPT(sw_acc2.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc2.sw02,'{$this->db_crypt_key2}')) as e_acc_name, 
                   concat(AES_DECRYPT(sw_acc3.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc3.sw02,'{$this->db_crypt_key2}')) as sw_chk_name,
                   case {$tbl_home_interview}.hew10
                     when '1' then '定期關懷'
                     when '2' then '特殊問題'
                     when '3' then '年度評估'
                     when '99' then '其他'
                   end as hew10_str,
                   case {$tbl_home_interview}.hew10_1
                     when '1' then '維持'
                     when '99' then '改變'
                   end as hew10_1_str,
                   case {$tbl_home_interview}.hew10_2
                     when '1' then '維持'
                     when '99' then '改變'
                   end as hew10_2_str,
                   case {$tbl_home_interview}.hew10_3
                     when '1' then '維持'
                     when '99' then '改變'
                   end as hew10_3_str,
                   case {$tbl_home_interview}.hew10_4
                     when '1' then '維持'
                     when '99' then '改變'
                   end as hew10_4_str,
                   case {$tbl_home_interview}.hew10_5
                     when '1' then '維持'
                     when '99' then '改變'
                   end as hew10_5_str,
                   case {$tbl_home_interview}.hew10_6
                     when '1' then '維持'
                     when '99' then '改變'
                   end as hew10_6_str,
                   case {$tbl_home_interview}.hew20
                     when '1' then '非常滿意'
                     when '2' then '滿意'
                     when '3' then '普通'
                     when '4' then '不滿意'
                     when '5' then '非常不滿意'
                   end as hew20_str,
                   case {$tbl_home_interview}.hew21
                     when '1' then '非常滿意'
                     when '2' then '滿意'
                     when '3' then '普通'
                     when '4' then '不滿意'
                     when '5' then '非常不滿意'
                   end as hew21_str,
                   case {$tbl_home_interview}.hew22
                     when '1' then '非常滿意'
                     when '2' then '滿意'
                     when '3' then '普通'
                     when '4' then '不滿意'
                     when '5' then '非常不滿意'
                   end as hew22_str,
                   case {$tbl_home_interview}.hew23
                     when '1' then '非常滿意'
                     when '2' then '滿意'
                     when '3' then '普通'
                     when '4' then '不滿意'
                     when '5' then '非常不滿意'
                   end as hew23_str,
                   case {$tbl_home_interview}.hew24
                     when '1' then '非常滿意'
                     when '2' then '滿意'
                     when '3' then '普通'
                     when '4' then '不滿意'
                     when '5' then '非常不滿意'
                   end as hew24_str,
                   case {$tbl_home_interview}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->_aes_fd('clients')}
            from {$tbl_home_interview}
            left join {$tbl_social_worker} sw_acc on sw_acc.s_num = {$tbl_home_interview}.b_empno
            left join {$tbl_social_worker} sw_acc2 on sw_acc2.s_num = {$tbl_home_interview}.e_empno
            left join {$tbl_social_worker} sw_acc3 on sw_acc3.s_num = {$tbl_home_interview}.hew03_sw_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_home_interview}.hew04_ct_s_num
            where {$tbl_home_interview}.d_date is null
                  and {$tbl_home_interview}.s_num = ?
            order by {$tbl_home_interview}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      // 遮罩欄位處理 Begin //
      foreach ($this->aes_fd_ct as $k => $v) {
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
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_home_interview}.*
                   
            from {$tbl_home_interview}
            where {$tbl_home_interview}.d_date is null
                  and {$tbl_home_interview}.fd_name = ?
            order by {$tbl_home_interview}.s_num desc
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
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function get_all() {
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
    $data = NULL;
    $sql = "select {$tbl_home_interview}.*
                   
            from {$tbl_home_interview}
            where {$tbl_home_interview}.d_date is null
            order by {$tbl_home_interview}.s_num desc
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
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
    $data = NULL;
    $sql = "select {$tbl_home_interview}.*
                   
            from {$tbl_home_interview}
            where {$tbl_home_interview}.d_date is null
                  and {$tbl_home_interview}.is_available = 1 /* 啟用 */
            order by {$tbl_home_interview}.s_num desc
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
  //  函數名稱: get_all_by_ct_s_num()
  //  函數功能: 取得案主前三筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function get_all_by_ct_s_num($ct_s_num, $limit=NULL) {
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料

    $sql_limit = '';
    if(NULL != $limit) {
      $sql_limit .= "LIMIT 3";
    }

    $data = NULL;
    $sql = "select {$tbl_home_interview}.*,
                   concat(AES_DECRYPT(sw_acc.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc.sw02,'{$this->db_crypt_key2}')) as sw_name,
                   concat(AES_DECRYPT(sw_acc2.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc2.sw02,'{$this->db_crypt_key2}')) as sw_chk_name,
                   case {$tbl_home_interview}.hew10
                     when '1' then '定期關懷'
                     when '2' then '特殊問題'
                     when '3' then '年度評估'
                     when '99' then '其他'
                   end as hew10_str,
                   case {$tbl_home_interview}.hew10_1
                   when '1' then '維持'
                   when '99' then '改變'
                 end as hew10_1_str,
                 case {$tbl_home_interview}.hew10_2
                   when '1' then '維持'
                   when '99' then '改變'
                 end as hew10_2_str,
                 case {$tbl_home_interview}.hew10_3
                   when '1' then '維持'
                   when '99' then '改變'
                 end as hew10_3_str,
                 case {$tbl_home_interview}.hew10_4
                   when '1' then '維持'
                   when '99' then '改變'
                 end as hew10_4_str,
                 case {$tbl_home_interview}.hew10_5
                   when '1' then '維持'
                   when '99' then '改變'
                 end as hew10_5_str,
                 case {$tbl_home_interview}.hew10_6
                   when '1' then '維持'
                   when '99' then '改變'
                 end as hew10_6_str,
                 case {$tbl_home_interview}.hew20
                   when '1' then '非常滿意'
                   when '2' then '滿意'
                   when '3' then '普通'
                   when '4' then '不滿意'
                   when '5' then '非常不滿意'
                 end as hew20_str
            from {$tbl_home_interview}
            left join {$tbl_social_worker} sw_acc on sw_acc.s_num = {$tbl_home_interview}.b_empno         
            left join {$tbl_social_worker} sw_acc2 on sw_acc2.s_num = {$tbl_home_interview}.hew03_sw_s_num
            where {$tbl_home_interview}.d_date is null
                  and {$tbl_home_interview}.hew04_ct_s_num = '{$ct_s_num}'
            order by {$tbl_home_interview}.hew01 desc
            {$sql_limit}
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
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
    $where = " {$tbl_home_interview}.d_date is null ";
    $order = " {$tbl_home_interview}.hew01 desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_home_interview}.hew01 like binary '%{$que_str}%' /* 訪視時間 */                       
                       or {$tbl_home_interview}.hew02_sw_s_num like binary '%{$que_str}%' /* 到訪者(tw_social_worker.s_num) */                       
                       or {$tbl_home_interview}.hew03_sw_s_num like binary '%{$que_str}%' /* 檢閱者(tw_social_worker.s_num) */                                       
                       or {$tbl_home_interview}.hew04_ct_s_num like binary '%{$que_str}%' /* tw_clients.s_num */                       
                       or {$tbl_home_interview}.hew10 like binary '%{$que_str}%' /* 訪視目的(1=定期關懷,2=特殊問題,3=年度評估,99=其他) */
                       or concat(AES_DECRYPT(sw_acc.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc.sw02,'{$this->db_crypt_key2}')) like '%{$que_str}%'
                       or concat(AES_DECRYPT(sw_acc2.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc2.sw02,'{$this->db_crypt_key2}')) like '%{$que_str}%'
                       or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%'
                       or AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') like '%{$que_str}%'
                      )
                ";
    }
    
    if(!empty($get_data['que_ct_s_num'])) { // 案主序號
      $que_ct_s_num = $get_data['que_ct_s_num'];
      $que_ct_s_num = $this->db->escape_like_str($que_ct_s_num);
      $where .= " and {$tbl_home_interview}.hew04_ct_s_num = '{$que_ct_s_num}'  /* 案主序號 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_home_interview}.s_num
                from {$tbl_home_interview}
                left join {$tbl_social_worker} sw_acc on sw_acc.s_num = {$tbl_home_interview}.b_empno         
                left join {$tbl_social_worker} sw_acc2 on sw_acc2.s_num = {$tbl_home_interview}.hew03_sw_s_num
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_home_interview}.hew04_ct_s_num
                where $where
                group by {$tbl_home_interview}.s_num
                order by {$tbl_home_interview}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_home_interview}.*,
                   concat(AES_DECRYPT(sw_acc.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc.sw02,'{$this->db_crypt_key2}')) as sw_name,
                   concat(AES_DECRYPT(sw_acc2.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT(sw_acc2.sw02,'{$this->db_crypt_key2}')) as sw_chk_name,
                   case {$tbl_home_interview}.hew10
                     when '1' then '定期關懷'
                     when '2' then '特殊問題'
                     when '3' then '年度評估'
                     when '99' then '其他'
                   end as hew10_str
                   {$this->_aes_fd('clients')}
            from {$tbl_home_interview}
            left join {$tbl_social_worker} sw_acc on sw_acc.s_num = {$tbl_home_interview}.b_empno         
            left join {$tbl_social_worker} sw_acc2 on sw_acc2.s_num = {$tbl_home_interview}.hew03_sw_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_home_interview}.hew04_ct_s_num
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
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function save_add() {
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
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
    
    if(!$this->db->insert($tbl_home_interview, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function save_upd() {
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
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
    if(!$this->db->update($tbl_home_interview, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function save_is_available() {
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_home_interview, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function del() {
    $tbl_home_interview = $this->zi_init->chk_tbl_no_lang('home_interview'); // 個案家訪資料
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_home_interview, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  private function _aes_fd($tbl_name) {
    $aes_fd = NULL;
    $aes_fd_str = "";
    
    switch ($tbl_name) {   
      case 'clients':
        $tbl_fd = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
        $aes_fd = $this->aes_fd_ct;
        break;  
      case 'social_worker':
        $tbl_fd = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
        $aes_fd = $this->aes_fd_sw;
        break;  
    }

    foreach ($aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd_str .= ",AES_DECRYPT({$tbl_fd}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd_str);
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
}
?>