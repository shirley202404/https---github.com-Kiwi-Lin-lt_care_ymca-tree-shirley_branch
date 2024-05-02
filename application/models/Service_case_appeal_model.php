<?php
class Service_case_appeal_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_service_case_appeal}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_service_case_appeal}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_service_case_appeal}.seca01
                     when '1' then '長照'
                     when '2' then '老人'
                     when '3' then '身障'
                   end as seca01_str,
                   case {$tbl_service_case_appeal}.seca36
                     when 'Y' then '需要'
                     when 'N' then '結案'
                   end as seca36_str,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') as ct14
            from {$tbl_service_case_appeal}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case_appeal}.ct_s_num
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_service_case_appeal}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_service_case_appeal}.e_empno
            where {$tbl_service_case_appeal}.d_date is null
                  and {$tbl_service_case_appeal}.s_num = ?
            order by {$tbl_service_case_appeal}.s_num desc
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_service_case_appeal}.*
                   
            from {$tbl_service_case_appeal}
            where {$tbl_service_case_appeal}.d_date is null
                  and {$tbl_service_case_appeal}.fd_name = ?
            order by {$tbl_service_case_appeal}.s_num desc
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function get_all() {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $data = NULL;
    $sql = "select {$tbl_service_case_appeal}.*
                   
            from {$tbl_service_case_appeal}
            where {$tbl_service_case_appeal}.d_date is null
            order by {$tbl_service_case_appeal}.s_num desc
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $data = NULL;
    $sql = "select {$tbl_service_case_appeal}.*
                   
            from {$tbl_service_case_appeal}
            where {$tbl_service_case_appeal}.d_date is null
                  and {$tbl_service_case_appeal}.is_available = 1 /* 啟用 */
            order by {$tbl_service_case_appeal}.s_num desc
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
  //  函數名稱: get_track()
  //  函數功能: 取得所有追蹤紀錄
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function get_track($s_num) {
    $tbl_service_case_appeal_track = $this->zi_init->chk_tbl_no_lang('service_case_appeal_track'); // 追蹤紀錄表
    $data = NULL;
    $sql = "select {$tbl_service_case_appeal_track}.*,
                   case {$tbl_service_case_appeal_track}.secat11
                     when 'Y' then '持續追蹤'
                     when 'N' then '結案'
                   end as secat11_str
            from {$tbl_service_case_appeal_track}
            where {$tbl_service_case_appeal_track}.d_date is null
                  and {$tbl_service_case_appeal_track}.seca_s_num = {$s_num}
            order by {$tbl_service_case_appeal_track}.secat02 asc
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $where = " {$tbl_service_case_appeal}.d_date is null ";
    $order = " {$tbl_service_case_appeal}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_service_case_appeal}.ct_s_num like '%{$que_str}%' /* 客戶序號-MEMO(tw_clients.s_num) */                       
                        or BINARY {$tbl_service_case_appeal}.seca01 like BINARY '%{$que_str}%' /* 客訴類型-OPT（1=長照；2=老人；3=身障） */                       
                        or BINARY {$tbl_service_case_appeal}.seca11 like BINARY '%{$que_str}%' /* 申訴情形-受理日期 */                       
                        or {$tbl_service_case_appeal}.seca12 like '%{$que_str}%' /* 申訴情形-申訴人 */                       
                        or {$tbl_service_case_appeal}.seca13 like '%{$que_str}%' /* 申訴情形-申訴人關係 */                       
                        or {$tbl_service_case_appeal}.seca14 like '%{$que_str}%' /* 申訴情形-申訴事由 */                       
                        or {$tbl_service_case_appeal}.seca22 like '%{$que_str}%' /* 受理情形-受理人員 */                       
                        or BINARY {$tbl_service_case_appeal}.seca23 like BINARY '%{$que_str}%' /* 受理情形-填表日期 */                       
                        or {$tbl_service_case_appeal}.seca34 like '%{$que_str}%' /* 處理情形-處理過程 */                       
                        or {$tbl_service_case_appeal}.seca35 like '%{$que_str}%' /* 處理情形-處置建議 */                       
                        or {$tbl_service_case_appeal}.seca36 like '%{$que_str}%' /* 處理情形-後續追蹤-OPT（Y=需要；N=結案） */                       
                        or BINARY {$tbl_service_case_appeal}.seca36_date like BINARY '%{$que_str}%' /* 處理情形-後續追蹤日期 */
                      )
                ";
    }

    if(!empty($get_data['que_ct_s_num'])) { // 客戶序號-MEMO(tw_clients.s_num)
      $que_ct_s_num = $get_data['que_ct_s_num'];
      $que_ct_s_num = $this->db->escape_like_str($que_ct_s_num);
      $where .= " and {$tbl_service_case_appeal}.ct_s_num = '{$que_ct_s_num}'  /* 客戶序號-MEMO(tw_clients.s_num) */ ";
    }
    if(!empty($get_data['que_seca11'])) { // 申訴情形-受理日期
      $que_seca11 = $get_data['que_seca11'];
      $que_seca11 = $this->db->escape_like_str($que_seca11);
      $where .= " and {$tbl_service_case_appeal}.seca11 = '{$que_seca11}'  /* 申訴情形-受理日期 */ ";
    }
    if(!empty($get_data['que_seca12'])) { // 申訴情形-申訴人
      $que_seca12 = $get_data['que_seca12'];
      $que_seca12 = $this->db->escape_like_str($que_seca12);
      $where .= " and {$tbl_service_case_appeal}.seca12 like '%{$que_seca12}%'  /* 申訴情形-申訴人 */ ";
    }
    if(!empty($get_data['que_seca22'])) { // 受理情形-受理人員
      $que_seca22 = $get_data['que_seca22'];
      $que_seca22 = $this->db->escape_like_str($que_seca22);
      $where .= " and {$tbl_service_case_appeal}.seca22 like '%{$que_seca22}%'  /* 受理情形-受理人員 */ ";
    }
    if(!empty($get_data['que_seca23'])) { // 受理情形-填表日期
      $que_seca23 = $get_data['que_seca23'];
      $que_seca23 = $this->db->escape_like_str($que_seca23);
      $where .= " and {$tbl_service_case_appeal}.seca23 = '{$que_seca23}'  /* 受理情形-填表日期 */ ";
    }
    if(!empty($get_data['que_seca36'])) { // 處理情形-後續追蹤-OPT（Y=需要；N=結案）
      $que_seca36 = $get_data['que_seca36'];
      $que_seca36 = $this->db->escape_like_str($que_seca36);
      $where .= " and {$tbl_service_case_appeal}.seca36 = '{$que_seca36}'  /* 處理情形-後續追蹤-OPT（Y=需要；N=結案） */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_service_case_appeal}.s_num
                from {$tbl_service_case_appeal}
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case_appeal}.ct_s_num
                where $where
                group by {$tbl_service_case_appeal}.s_num
                order by {$tbl_service_case_appeal}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_service_case_appeal}.*,
                   case {$tbl_service_case_appeal}.seca01
                     when '1' then '長照'
                     when '2' then '老人'
                     when '3' then '身障'
                   end as seca01_str,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') as ct14
            from {$tbl_service_case_appeal}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_service_case_appeal}.ct_s_num
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function save_add() {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $tbl_service_case_appeal_track = $this->zi_init->chk_tbl_no_lang('service_case_appeal_track'); // 追蹤紀錄表
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $secat = NULL;
    $seca = $data['seca'];
    if(isset($data['secat'])) {
      $secat = $data['secat'];
    }
    // 加密欄位處理 Begin //
    foreach ($seca as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($dnn[$k_fd_name]);
      }
    }
    // 加密欄位處理 End //
    $seca['b_empno'] = $_SESSION['acc_s_num'];
    $seca['b_date'] = date('Y-m-d H:i:s');
    
    if(!$this->db->insert($tbl_service_case_appeal, $seca)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    else {
      $seca_s_num = $this->db->insert_id();
      if(NULL != $secat) {
        $is_close = FALSE;
        foreach ($secat as $k => $v) {
          if('' == $v['secat01'] and '' == $v['secat02'] and '' == $v['secat03'] and !isset($v['secat11'])) {
            unset($secat[$k]);
            continue;
          }
          if("N" == $v['secat11']) {
            $is_close = TRUE;
          }
          $secat[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $secat[$k]['b_date'] = date('Y-m-d H:i:s');
          $secat[$k]['seca_s_num'] = $seca_s_num;
          if(!$this->db->insert($tbl_service_case_appeal_track, $secat[$k])) {
            $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
          }
        }
        if($is_close) {
          $this->db->where('s_num', $seca_s_num);
          if(!$this->db->update($tbl_service_case_appeal, array('seca41' => date('Y-m-d')))) {
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function save_upd() {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $tbl_service_case_appeal_track = $this->zi_init->chk_tbl_no_lang('service_case_appeal_track'); // 追蹤紀錄表
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $secat = NULL;
    $seca = $data['seca'];
    if(isset($data['secat'])) {
      $secat = $data['secat'];
    }
    // 加密欄位處理 Begin //
    foreach ($seca as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($dnn[$k_fd_name]);
      }
    }
    // 加密欄位處理 End //
    $seca_s_num = $seca['s_num'];
    unset($seca['s_num']);
    $seca['e_empno'] = $_SESSION['acc_s_num'];
    $seca['e_date'] = date('Y-m-d H:i:s');

    $this->db->where('s_num', $seca_s_num);
    if(!$this->db->update($tbl_service_case_appeal, $seca)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    else {
      $this->db->where('seca_s_num', $seca_s_num);
      if(!$this->db->delete($tbl_service_case_appeal_track)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 新增失敗!!
      }
      if(NULL != $secat) {
        $is_close = FALSE;
        foreach ($secat as $k => $v) {
          if('' == $v['secat01'] and '' == $v['secat02'] and '' == $v['secat03'] and !isset($v['secat11'])) {
            unset($secat[$k]);
            continue;
          }
          if("N" == $v['secat11']) {
            $is_close = TRUE;
          }
          $secat[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $secat[$k]['b_date'] = date('Y-m-d H:i:s');
          $secat[$k]['seca_s_num'] = $seca_s_num;
          if(!$this->db->insert($tbl_service_case_appeal_track, $secat[$k])) {
            $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
          }
        }
        if($is_close) {
          $this->db->where('s_num', $seca_s_num);
          if(!$this->db->update($tbl_service_case_appeal, array('seca41' => date('Y-m-d')))) {
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
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function save_is_available() {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_service_case_appeal, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  public function del() {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $tbl_service_case_appeal_track = $this->zi_init->chk_tbl_no_lang('service_case_appeal_track'); // 追蹤紀錄表

    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_service_case_appeal, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    else {
      $this->db->where('seca_s_num', $v['s_num']);
      if(!$this->db->update($tbl_service_case_appeal_track, $data)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
  // **************************************************************************
  private function _aes_fd() {
    $tbl_service_case_appeal = $this->zi_init->chk_tbl_no_lang('service_case_appeal'); // 申訴處理單-社工
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_service_case_appeal}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2022-09-14
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