<?php
class Dietitian_note_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $s_num = (int)$this->db->escape_like_str($s_num);    
    $get_data = $this->input->get();

    $tbl_source = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $left_join = " left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_source}.sec_s_num";
    $source = " and {$tbl_dietitian_note}.d_date is null
                and {$tbl_dietitian_note}.dnn01_source_type = 'meal' 
              ";
    $str_replace = " ,case {$tbl_service_case}.sec01
                       when '1' then '長照案'
                       when '2' then '特殊-老案'
                       when '3' then '自費戶'
                       when '4' then '邊緣戶'
                       when '5' then '身障案'
                       when '6' then '特殊-身案'
                       when '7' then '志工'
                       when '8' then '獨老案'
                     end as sec01_str
                   ";

    if(isset($get_data['que_source'])) { 
      if('item' == $get_data['que_source']) {
        $tbl_source = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 餐點異動資料
        $source = " and {$tbl_dietitian_note}.d_date is null
                    and {$tbl_dietitian_note}.dnn01_source_type = 'item'
                  ";
        $str_replace = '';
        $left_join = '';
      }
    }

    $row = NULL;
    $sql = "select {$tbl_source}.*,
                   {$tbl_dietitian_note}.b_date as dnn_b_date,
                   {$tbl_dietitian_note}.s_num as dnn_s_num,
                   {$tbl_dietitian_note}.dnn01_source_s_num,
                   {$tbl_dietitian_note}.dnn01_source_type,
                   {$tbl_dietitian_note}.dnn02,
                   {$tbl_dietitian_note}.dnn02_02_memo,
                   {$tbl_dietitian_note}.dnn02_03_opt,
                   {$tbl_dietitian_note}.dnn02_04_s_num,
                   {$tbl_dietitian_note}.dnn03,
                   case {$tbl_dietitian_note}.dnn02_03_opt
                     when '1' then '社工組'
                     when '2' then '膳務組'
                     when '3' then '倉管組'
                     when '4' then '行政組'
                     when '5' then '交通組'
                     when '6' then '志工組'
                   end as dnn02_03_opt_str,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02
                   {$str_replace}
            from {$tbl_source}
            {$left_join}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_source}.ct_s_num
            left join {$tbl_dietitian_note} on {$tbl_dietitian_note}.dnn01_source_s_num = {$tbl_source}.s_num
                      {$source} 
            where {$tbl_source}.d_date is null
                  and {$tbl_source}.s_num = ?
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_dietitian_note}.*
                   
            from {$tbl_dietitian_note}
            where {$tbl_dietitian_note}.d_date is null
                  and {$tbl_dietitian_note}.fd_name = ?
            order by {$tbl_dietitian_note}.s_num desc
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function get_all() {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $data = NULL;
    $sql = "select {$tbl_dietitian_note}.*
                   
            from {$tbl_dietitian_note}
            where {$tbl_dietitian_note}.d_date is null
            order by {$tbl_dietitian_note}.s_num desc
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $data = NULL;
    $sql = "select {$tbl_dietitian_note}.*
                   
            from {$tbl_dietitian_note}
            where {$tbl_dietitian_note}.d_date is null
                  and {$tbl_dietitian_note}.is_available = 1 /* 啟用 */
            order by {$tbl_dietitian_note}.s_num desc
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function get_track($dnn_s_num) {
    $tbl_dietitian_track = $this->zi_init->chk_tbl_no_lang('dietitian_track'); // 追蹤紀錄表
    $data = NULL;
    $sql = "select {$tbl_dietitian_track}.*,
                   case {$tbl_dietitian_track}.dnt04_type
                     when '1' then '持續追蹤'
                     when '99' then '結案'
                   end as dnt04_type_str
            from {$tbl_dietitian_track}
            where {$tbl_dietitian_track}.d_date is null
                  and {$tbl_dietitian_track}.dnn_s_num = {$dnn_s_num}
            order by {$tbl_dietitian_track}.dnt01 desc
           ";
    // u_var_dump($sql);
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師    
    $get_data = $this->input->get();

    $tbl_source = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_h'); // 餐點異動資料
    $left_join = " left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_source}.sec_s_num";
    $source = " and {$tbl_dietitian_note}.d_date is null
                and {$tbl_dietitian_note}.dnn01_source_type = 'meal' 
              ";
    $source_where = " and {$tbl_source}.mil02 like '%1%'";
    $str_replace = " ,case {$tbl_service_case}.sec01
                       when '1' then '長照案'
                       when '2' then '特殊-老案'
                       when '3' then '自費戶'
                       when '4' then '邊緣戶'
                       when '5' then '身障案'
                       when '6' then '特殊-身案'
                       when '7' then '志工'
                       when '8' then '獨老案'
                     end as sec01_str
                   ";

    if(isset($get_data['que_source'])) { 
      if('item' == $get_data['que_source']) {
        $tbl_source = $this->zi_init->chk_tbl_no_lang('other_change_log_h'); // 餐點異動資料
        $source = " and {$tbl_dietitian_note}.d_date is null
                    and {$tbl_dietitian_note}.dnn01_source_type = 'item'
                  ";
        $source_where = " and {$tbl_source}.ocl01 like '%6%'";
        $str_replace = '';
        $left_join = '';
      }
    }

    $where = " {$tbl_source}.d_date is null
               {$source_where}
             ";
    $order = " {$tbl_source}.s_num desc ";
    
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_dietitian_note}.dnn01_source_s_num like '%{$que_str}%' /* 來源-MEMO(s_num) */                       
                       or {$tbl_dietitian_note}.dnn01_source_type like '%{$que_str}%' /* 來源-MEMO(meal=餐食異動，item=非餐食異動) */                       
                       or {$tbl_dietitian_note}.dnn02 like '%{$que_str}%' /* 營養師回覆-OPT(1=無需處理;2=原因;3=照會單位;4=連結客訴單) */                       
                       or {$tbl_dietitian_note}.dnn02_02_memo like '%{$que_str}%' /* 原因 */                       
                       or {$tbl_dietitian_note}.dnn02_03_opt like '%{$que_str}%' /* 照會單位-OPT(1=社工組;2=膳務組;3=倉管組;4=行政組;5=交通組;6=志工組) */                       
                       or {$tbl_dietitian_note}.dnn02_04_s_num like '%{$que_str}%' /* 客訴單s_num */
                       or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */ 
                      )
                ";
    }

    if(!empty($get_data['que_sec01'])) { // 服務現況
      $que_sec01 = $get_data['que_sec01'];
      $que_sec01 = $this->db->escape_like_str($que_sec01);
      $where .= " and {$tbl_service_case}.sec01 = '{$que_sec01}'";
    }
    if(!empty($get_data['que_dnn02'])) { // 營養師回覆-OPT(1=無需處理;2=原因;3=照會單位;4=連結客訴單)
      $que_dnn02 = $get_data['que_dnn02'];
      $que_dnn02 = $this->db->escape_like_str($que_dnn02);
      $where .= " and {$tbl_dietitian_note}.dnn02 like '%{$que_dnn02}%'  /* 營養師回覆-OPT(1=無需處理;2=原因;3=照會單位;4=連結客訴單) */ ";
    }
    if(!empty($get_data['que_dnn02_02_memo'])) { // 原因
      $que_dnn02_02_memo = $get_data['que_dnn02_02_memo'];
      $que_dnn02_02_memo = $this->db->escape_like_str($que_dnn02_02_memo);
      $where .= " and {$tbl_dietitian_note}.dnn02_02_memo like '%{$que_dnn02_02_memo}%'  /* 原因 */ ";
    }
    if(!empty($get_data['que_dnn02_03_opt'])) { // 照會單位-OPT(1=社工組;2=膳務組;3=倉管組;4=行政組;5=交通組;6=志工組)
      $que_dnn02_03_opt = $get_data['que_dnn02_03_opt'];
      $que_dnn02_03_opt = $this->db->escape_like_str($que_dnn02_03_opt);
      $where .= " and {$tbl_dietitian_note}.dnn02_03_opt = '{$que_dnn02_03_opt}'  /* 照會單位-OPT(1=社工組;2=膳務組;3=倉管組;4=行政組;5=交通組;6=志工組) */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_source}.s_num
                from {$tbl_source}
                {$left_join}
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_source}.ct_s_num
                left join {$tbl_dietitian_note} on {$tbl_dietitian_note}.dnn01_source_s_num = {$tbl_source}.s_num
                          {$source} 
                where {$where}
                group by {$tbl_source}.s_num
                order by {$tbl_source}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_source}.*,
                   {$tbl_dietitian_note}.b_date as dnn_b_date,
                   {$tbl_dietitian_note}.s_num as dnn_s_num,
                   {$tbl_dietitian_note}.dnn01_source_s_num,
                   {$tbl_dietitian_note}.dnn01_source_type,
                   {$tbl_dietitian_note}.dnn02,
                   {$tbl_dietitian_note}.dnn02_02_memo,
                   {$tbl_dietitian_note}.dnn02_03_opt,
                   {$tbl_dietitian_note}.dnn02_04_s_num,
                   {$tbl_dietitian_note}.dnn03,
                   case {$tbl_dietitian_note}.dnn02_03_opt
                      when '1' then '社工組'
                      when '2' then '膳務組'
                      when '3' then '倉管組'
                      when '4' then '行政組'
                      when '5' then '交通組'
                      when '6' then '志工組'
                   end as dnn02_03_opt_str,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02
                   {$str_replace}
            from {$tbl_source}
            {$left_join}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_source}.ct_s_num
            left join {$tbl_dietitian_note} on {$tbl_dietitian_note}.dnn01_source_s_num = {$tbl_source}.s_num
                      {$source} 
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function save_add() {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $tbl_dietitian_track = $this->zi_init->chk_tbl_no_lang('dietitian_track'); // 追蹤紀錄表
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $dnt = NULL;
    $dnn = $data['dnn'];
    if(isset($data['dnt'])) {
      $dnt = $data['dnt'];
    }
    // 加密欄位處理 Begin //
    foreach ($dnn as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($dnn[$k_fd_name]);
      }
    }
    // 加密欄位處理 End //
    $dnn['b_empno'] = $_SESSION['acc_s_num'];
    $dnn['b_date'] = date('Y-m-d H:i:s');
    
    if(!$this->db->insert($tbl_dietitian_note, $dnn)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    else {
      $dnn_s_num = $this->db->insert_id();
      if(NULL != $dnt) {
        $is_close = FALSE;
        foreach ($dnt as $k => $v) {
          if('' == $v['dnt01'] and '' == $v['dnt02'] and '' == $v['dnt03'] and !isset($v['dnt04_type'])) {
            unset($dnt[$k]);
            continue;
          }
          if(99 == $v['dnt04_type']) {
            $is_close = TRUE;
          }
          $dnt[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $dnt[$k]['b_date'] = date('Y-m-d H:i:s');
          $dnt[$k]['dnn_s_num'] = $dnn_s_num;
          if(!$this->db->insert($tbl_dietitian_track, $dnt[$k])) {
            $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
          }
        }
        if($is_close) {
          $this->db->where('s_num', $dnn_s_num);
          if(!$this->db->update($tbl_dietitian_note, array('dnn03' => date('Y-m-d')))) {
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function save_upd() {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $tbl_dietitian_track = $this->zi_init->chk_tbl_no_lang('dietitian_track'); // 追蹤紀錄表

    $rtn_msg = 'ok';
    $data = $this->input->post();
    $dnt = NULL;
    $dnn = $data['dnn'];
    if(isset($data['dnt'])) {
      $dnt = $data['dnt'];
    }
    // 加密欄位處理 Begin //
    foreach ($dnn as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($dnn[$k_fd_name]);
      }
    } 
    // 加密欄位處理 End //
    $dnn_s_num = $dnn['dnn_s_num'];
    unset($dnn['dnn_s_num']);
    $dnn['e_empno'] = $_SESSION['acc_s_num'];
    $dnn['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $dnn_s_num);
    if(!$this->db->update($tbl_dietitian_note, $dnn)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    else {
      $this->db->where('dnn_s_num', $dnn_s_num);
      if(!$this->db->delete($tbl_dietitian_track)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 新增失敗!!
      }
      if(NULL != $dnt) {
        $is_close = FALSE;
        foreach ($dnt as $k => $v) {
          if('' == $v['dnt01'] and '' == $v['dnt02'] and '' == $v['dnt03'] and !isset($v['dnt04_type'])) {
            unset($dnt[$k]);
            continue;
          }
          if(99 == $v['dnt04_type']) {
            $is_close = TRUE;
          }
          $dnt[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $dnt[$k]['b_date'] = date('Y-m-d H:i:s');
          $dnt[$k]['dnn_s_num'] = $dnn_s_num;
          if(!$this->db->insert($tbl_dietitian_track, $dnt[$k])) {
            $rtn_msg = $this->lang->line('upd_ng'); // 新增失敗!!
          }
        }
        if($is_close) {
          $this->db->where('s_num', $dnn_s_num);
          if(!$this->db->update($tbl_dietitian_note, array('dnn03' => date('Y-m-d')))) {
            $rtn_msg = $this->lang->line('upd_ng'); // 新增失敗!!
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
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function save_is_available() {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_dietitian_note, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  public function del() {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $tbl_dietitian_track = $this->zi_init->chk_tbl_no_lang('dietitian_track'); // 追蹤紀錄表

    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_dietitian_note, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    else {
      $this->db->where('seca_s_num', $v['s_num']);
      if(!$this->db->update($tbl_dietitian_track, $data)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
      }
    }
    echo $rtn_msg;
    return;
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
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date']  = date('Y-m-d H:i:s');
    $data['dnn03']  = date('Y-m-d');
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_dietitian_note, $data)) {
      $rtn_msg = $this->lang->line('over_ng'); // 刪除失敗
    }
    return $rtn_msg;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
  // **************************************************************************
  private function _aes_fd() {
    $tbl_dietitian_note = $this->zi_init->chk_tbl_no_lang('dietitian_note'); // 照會營養師
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_dietitian_note}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2022-07-13
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