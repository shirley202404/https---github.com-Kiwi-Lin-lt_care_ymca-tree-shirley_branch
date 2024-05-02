<?php
class Mobile_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_mobile}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_mobile}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_mobile}.me06_sim
                     when '1' then '暨大SIM卡'
                     when '2' then '慈心SIM卡'
                     when '99' then '其他'
                   end as me06_sim_str,
                   case {$tbl_mobile}.me06_program
                     when '1' then '月租'
                     when '2' then '預付卡'
                     when '99' then '其他'
                   end as me06_program_str,
                   AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') as dp01,
                   AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') as dp02
            from {$tbl_mobile}
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile}.me08_dp_s_num
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_mobile}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_mobile}.e_empno
            where {$tbl_mobile}.d_date is null
                  and {$tbl_mobile}.s_num = ?
            order by {$tbl_mobile}.s_num desc
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
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_mobile}.*
                   
            from {$tbl_mobile}
            where {$tbl_mobile}.d_date is null
                  and {$tbl_mobile}.fd_name = ?
            order by {$tbl_mobile}.s_num desc
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
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function get_all() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $data = NULL;
    $sql = "select {$tbl_mobile}.*
                   
            from {$tbl_mobile}
            where {$tbl_mobile}.d_date is null
            order by {$tbl_mobile}.s_num desc
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
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $data = NULL;
    $sql = "select {$tbl_mobile}.*
                   
            from {$tbl_mobile}
            where {$tbl_mobile}.d_date is null
                  and {$tbl_mobile}.is_available = 1 /* 啟用 */
            order by {$tbl_mobile}.s_num desc
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
  //  函數名稱: get_credit()
  //  函數功能: 取得所有充值資料
  //  程式設計: kiwi
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function get_credit($me_s_num) {
    $tbl_mobile_credit = $this->zi_init->chk_tbl_no_lang('mobile_credit'); // 手機充值資料
    $data = NULL;
    $sql = "select {$tbl_mobile_credit}.*
            from {$tbl_mobile_credit}
            where {$tbl_mobile_credit}.d_date is null
                  and {$tbl_mobile_credit}.me_s_num = {$me_s_num}
            order by {$tbl_mobile_credit}.s_num asc
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
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員資料
    $where = " {$tbl_mobile}.d_date is null ";
    $order = " {$tbl_mobile}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_mobile}.me01 like '%{$que_str}%' /* 手機編號 */                       
                       or {$tbl_mobile}.me02 like '%{$que_str}%' /* 手機廠牌 */                       
                       or {$tbl_mobile}.me03 like '%{$que_str}%' /* 手機型號 */                       
                       or {$tbl_mobile}.me04 like '%{$que_str}%' /* 手機序號 */                       
                       or {$tbl_mobile}.me05 like '%{$que_str}%' /* 手機號碼 */                       
                       or {$tbl_mobile}.me06_sim like '%{$que_str}%' /* SIM卡(1=暨大SIM卡, 2=慈心SIM卡, 99=其他) */                       
                       or {$tbl_mobile}.me06_program like '%{$que_str}%' /* 手機方案(1=月租, 2=預付卡, 99=其他) */                       
                       or {$tbl_mobile}.me07 like '%{$que_str}%' /* 手機持有者 */                       
                       or {$tbl_mobile}.me08_dp_s_num like '%{$que_str}%' /* 手機保管者(tw_delivery_person.s_num) */                       
                       or BINARY {$tbl_mobile}.me09 like BINARY '%{$que_str}%' /* 購入日期 */                       
                       or {$tbl_mobile}.me99 like '%{$que_str}%' /* 備註 */
                       or concat(AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 中文姓 */                       
                      )
                ";
    }

    if(!empty($get_data['que_me01'])) { // 手機編號
      $que_me01 = $get_data['que_me01'];
      $que_me01 = $this->db->escape_like_str($que_me01);
      $where .= " and {$tbl_mobile}.me01 like '%{$que_me01}%'  /* 手機編號 */ ";
    }
    if(!empty($get_data['que_me02'])) { // 手機廠牌
      $que_me02 = $get_data['que_me02'];
      $que_me02 = $this->db->escape_like_str($que_me02);
      $where .= " and {$tbl_mobile}.me02 like '%{$que_me02}%'  /* 手機廠牌 */ ";
    }
    if(!empty($get_data['que_me03'])) { // 手機型號
      $que_me03 = $get_data['que_me03'];
      $que_me03 = $this->db->escape_like_str($que_me03);
      $where .= " and {$tbl_mobile}.me03 like '%{$que_me03}%'  /* 手機型號 */ ";
    }
    if(!empty($get_data['que_me04'])) { // 手機序號
      $que_me04 = $get_data['que_me04'];
      $que_me04 = $this->db->escape_like_str($que_me04);
      $where .= " and {$tbl_mobile}.me04 like '%{$que_me04}%'  /* 手機序號 */ ";
    }
    if(!empty($get_data['que_me05'])) { // 手機號碼
      $que_me05 = $get_data['que_me05'];
      $que_me05 = $this->db->escape_like_str($que_me05);
      $where .= " and {$tbl_mobile}.me05 like '%{$que_me05}%'  /* 手機號碼 */ ";
    }
    if(!empty($get_data['que_me06_sim'])) { // SIM卡(1=暨大SIM卡, 2=慈心SIM卡, 99=其他)
      $que_me06_sim = $get_data['que_me06_sim'];
      $que_me06_sim = $this->db->escape_like_str($que_me06_sim);
      $where .= " and {$tbl_mobile}.me06_sim = '{$que_me06_sim}'  /* SIM卡(1=暨大SIM卡, 2=慈心SIM卡, 99=其他) */ ";
    }
    if(!empty($get_data['que_me06_program'])) { // 手機方案(1=月租, 2=預付卡, 99=其他)
      $que_me06_program = $get_data['que_me06_program'];
      $que_me06_program = $this->db->escape_like_str($que_me06_program);
      $where .= " and {$tbl_mobile}.me06_program = '{$que_me06_program}'  /* 手機方案(1=月租, 2=預付卡, 99=其他) */ ";
    }
    if(!empty($get_data['que_me09'])) { // 購入日期
      $que_me09 = $get_data['que_me09'];
      $que_me09 = $this->db->escape_like_str($que_me09);
      $where .= " and {$tbl_mobile}.me09 = '{$que_me09}'  /* 購入日期 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_mobile}.s_num
                from {$tbl_mobile}
                left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile}.me08_dp_s_num
                where $where
                group by {$tbl_mobile}.s_num
                order by {$tbl_mobile}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_mobile}.*,
                   case {$tbl_mobile}.me06_sim
                     when '1' then '暨大SIM卡'
                     when '2' then '慈心SIM卡'
                     when '99' then '其他'
                   end as me06_sim_str,
                   case {$tbl_mobile}.me06_program
                     when '1' then '月租'
                     when '2' then '預付卡'
                     when '99' then '其他'
                   end as me06_program_str,
                   AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') as dp01,
                   AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') as dp02
            from {$tbl_mobile}
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile}.me08_dp_s_num
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
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function save_add() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $tbl_mobile_credit = $this->zi_init->chk_tbl_no_lang('mobile_credit'); // 手機充值資料
    $rtn_msg = 'ok';
    $data = $this->input->post();

    $mobile_data = $data['mobile'];
    $mobile_credit_data = NULL;
    if(isset($data['mobile_credit'])) {
      $mobile_credit_data = $data['mobile_credit'];
    }
    // 加密欄位處理 Begin //
    foreach ($mobile_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($mobile_data[$k_fd_name]);
      }
    }
    // 加密欄位處理 End //
    $mobile_data['b_empno'] = $_SESSION['acc_s_num'];
    $mobile_data['b_date'] = date('Y-m-d H:i:s');
    
    if(!$this->db->insert($tbl_mobile, $mobile_data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    else {
      $me_s_num = $this->db->insert_id();
      if(NULL != $mobile_credit_data) {
        foreach ($mobile_credit_data as $k => $v) {
          if(empty($v['mec01']) and empty($v['mec02'])) {
            unset($mobile_credit_data[$k]);
          }
          $mobile_credit_data[$k]['b_date'] = date("Y-m-d H:i:s");
          $mobile_credit_data[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $mobile_credit_data[$k]['me_s_num'] = $me_s_num;
        }
        if(NULL != $mobile_credit_data) {
          if(!$this->db->insert_batch($tbl_mobile_credit, $mobile_credit_data)) {
            $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
          }
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
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function save_upd() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $tbl_mobile_credit = $this->zi_init->chk_tbl_no_lang('mobile_credit'); // 手機充值資料
    $rtn_msg = 'ok';
    $data = $this->input->post();

    $mobile_data = $data['mobile'];
    $mobile_credit_data = NULL;
    if(isset($data['mobile_credit'])) {
      $mobile_credit_data = $data['mobile_credit'];
    }

    // 加密欄位處理 Begin //
    foreach ($mobile_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($mobile_data[$k_fd_name]);
      }
    } 
    // 加密欄位處理 End //
    $mobile_data['e_empno'] = $_SESSION['acc_s_num'];
    $mobile_data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $mobile_data['s_num']);
    if(!$this->db->update($tbl_mobile, $mobile_data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    else {
      $me_s_num = $mobile_data['s_num'];

      $this->db->where('me_s_num', $me_s_num);
      if(!$this->db->delete($tbl_mobile_credit)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
      }

      if(NULL != $mobile_credit_data) {
        foreach ($mobile_credit_data as $k => $v) {
          if(empty($v['mec01']) and empty($v['mec02'])) {
            unset($mobile_credit_data[$k]);
          }
          $mobile_credit_data[$k]['b_date'] = date("Y-m-d H:i:s");
          $mobile_credit_data[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $mobile_credit_data[$k]['me_s_num'] = $me_s_num;
        }
        if(NULL != $mobile_credit_data) {
          if(!$this->db->insert_batch($tbl_mobile_credit, $mobile_credit_data)) {
            $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
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
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function save_is_available() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_mobile, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function del() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_mobile, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2022-05-26
  // **************************************************************************
  private function _aes_fd() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_mobile}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2022-05-26
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