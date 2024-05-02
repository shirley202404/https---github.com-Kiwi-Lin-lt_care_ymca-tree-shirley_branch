<?php
class Mobile_use_model extends CI_Model {
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
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員資料
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_mobile_use}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   IF(sys_acc3.acc_name is null ,
                     concat(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}'))
                     ,sys_acc3.acc_name
                   ) as y_acc_name,
                   case {$tbl_mobile_use}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   {$tbl_mobile}.me01,
                   {$tbl_mobile}.me05,
                   {$tbl_route_h}.reh01,
                   AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') as dp01,
                   AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') as dp02
            from {$tbl_mobile_use}
            left join {$tbl_mobile} on {$tbl_mobile}.s_num = {$tbl_mobile_use}.me_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_mobile_use}.meu02_reh_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile_use}.meu01_dp_s_num
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_mobile_use}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_mobile_use}.e_empno
            left join {$tbl_account} sys_acc3 on sys_acc3.s_num = {$tbl_mobile_use}.meu21_y_empno
            left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_mobile_use}.meu21_y_empno
            where {$tbl_mobile_use}.d_date is null
                  and {$tbl_mobile_use}.s_num = ?
            order by {$tbl_mobile_use}.s_num desc
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_mobile_use}.*
                   
            from {$tbl_mobile_use}
            where {$tbl_mobile_use}.d_date is null
                  and {$tbl_mobile_use}.fd_name = ?
            order by {$tbl_mobile_use}.s_num desc
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $data = NULL;
    $sql = "select {$tbl_mobile_use}.*
                   
            from {$tbl_mobile_use}
            where {$tbl_mobile_use}.d_date is null
            order by {$tbl_mobile_use}.s_num desc
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $data = NULL;
    $sql = "select {$tbl_mobile_use}.*
                   
            from {$tbl_mobile_use}
            where {$tbl_mobile_use}.d_date is null
                  and {$tbl_mobile_use}.is_available = 1 /* 啟用 */
            order by {$tbl_mobile_use}.s_num desc
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
  //  函數名稱: get_mobile_use()
  //  函數功能: 取得手機使用紀錄
  //  程式設計: kiwi
  //  設計日期: 2022-05-31
  // **************************************************************************
  public function get_mobile_use($me_s_num) {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員資料

    $data = NULL;
    $sql = "select {$tbl_mobile_use}.*,
                sys_acc.acc_name as b_acc_name,
                sys_acc2.acc_name as e_acc_name,
                sys_acc3.acc_name as y_acc_name,
                case {$tbl_mobile_use}.is_available
                  when '0' then '停用'
                  when '1' then '啟用'
                end as is_available_str,
                {$tbl_mobile}.me01,
                {$tbl_mobile}.me05,
                {$tbl_route_h}.reh01,
                AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') as dp01,
                AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') as dp02
            from {$tbl_mobile_use}
            left join {$tbl_mobile} on {$tbl_mobile}.s_num = {$tbl_mobile_use}.me_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_mobile_use}.meu02_reh_s_num
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_mobile_use}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_mobile_use}.e_empno
            left join {$tbl_account} sys_acc3 on sys_acc3.s_num = {$tbl_mobile_use}.meu21_y_empno
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile_use}.meu01_dp_s_num
            where {$tbl_mobile_use}.d_date is null
                  and {$tbl_mobile_use}.me_s_num = '{$me_s_num}'
            order by {$tbl_mobile_use}.s_num desc
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
  //  函數名稱: get_download_data()
  //  函數功能: 查詢手機使用紀錄
  //  程式設計: kiwi
  //  設計日期: 2022-06-09
  // **************************************************************************
  public function get_download_data() {
    $tbl_mobile = $this->zi_init->chk_tbl_no_lang('mobile'); // 手機資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員資料
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $where = " {$tbl_mobile_use}.d_date is null ";
    $order = " {$tbl_mobile_use}.s_num desc ";
    $get_data = $this->input->post();

    if(!empty($get_data['download_me_s_num'])) { // tw_mobile.s_num
      $download_me_s_num = $get_data['download_me_s_num'];
      $download_me_s_num = $this->db->escape_like_str($download_me_s_num);
      $where .= " and {$tbl_mobile_use}.me_s_num = '{$download_me_s_num}'  /* tw_mobile.s_num */ ";
    }
    if(!empty($get_data['download_meu01_dp_s_num'])) { // 手機借用者(tw_delivery_person.s_num)
      $download_meu01_dp_s_num = $get_data['download_meu01_dp_s_num'];
      $download_meu01_dp_s_num = $this->db->escape_like_str($download_meu01_dp_s_num);
      $where .= " and {$tbl_mobile_use}.meu01_dp_s_num = '{$download_meu01_dp_s_num}'  /* 手機借用者(tw_delivery_person.s_num) */ ";
    }
    if(!empty($get_data['download_meu02_reh_s_num'])) { // 借用路線(tw_route.s_num)
      $download_meu02_reh_s_num = $get_data['download_meu02_reh_s_num'];
      $download_meu02_reh_s_num = $this->db->escape_like_str($download_meu02_reh_s_num);
      $where .= " and {$tbl_mobile_use}.meu02_reh_s_num = '{$download_meu02_reh_s_num}'  /* 借用路線(tw_route.s_num) */ ";
    }
    if(!empty($get_data['download_meu03_begin_date']) && !empty($get_data['download_meu04_end_date'])) { // 服務開始日
      $download_meu03_begin_date = $get_data['download_meu03_begin_date'];
      $download_meu04_end_date = $get_data['download_meu04_end_date'];
      $where .= " and {$tbl_mobile_use}.meu03_time between '{$download_meu03_begin_date}' and '{$download_meu04_end_date}'";
    }
    else {
      if(!empty($get_data['download_meu03_begin_date'])) { // 借出時間
        $download_meu03_begin_date = $get_data['download_meu03_begin_date'];
        $download_meu03_begin_date = $this->db->escape_like_str($download_meu03_begin_date);
        $where .= " and {$tbl_mobile_use}.meu03_time >= '{$download_meu03_begin_date}'  /* 借出時間 */ ";
      }
      if(!empty($get_data['download_meu04_end_date'])) { // 歸還時間
        $download_meu04_end_date = $get_data['download_meu04_end_date'];
        $download_meu04_end_date = $this->db->escape_like_str($download_meu04_end_date);
        $where .= " and {$tbl_mobile_use}.meu03_time <= '{$download_meu04_end_date}'  /* 歸還時間 */ ";
      }
    }

    $data = NULL;
    $sql = "select {$tbl_mobile_use}.*,
                   IF(sys_acc3.acc_name is null ,
                     concat(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}'))
                     ,sys_acc3.acc_name
                   ) as y_acc_name,
                   {$tbl_mobile}.me01,
                   {$tbl_mobile}.me05,
                   {$tbl_route_h}.reh01,
                   AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') as dp01,
                   AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') as dp02
            from {$tbl_mobile_use}
            left join {$tbl_mobile} on {$tbl_mobile}.s_num = {$tbl_mobile_use}.me_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_mobile_use}.meu02_reh_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile_use}.meu01_dp_s_num
            left join {$tbl_account} sys_acc3 on sys_acc3.s_num = {$tbl_mobile_use}.meu21_y_empno
            left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_mobile_use}.meu21_y_empno
            where {$where}
            order by {$order}
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
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐員資料
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $where = " {$tbl_mobile_use}.d_date is null ";
    $order = " {$tbl_mobile_use}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_mobile_use}.me_s_num like '%{$que_str}%' /* tw_mobile.s_num */                       
                       or {$tbl_mobile_use}.meu01_dp_s_num like '%{$que_str}%' /* 手機借用者(tw_delivery_person.s_num) */                       
                       or {$tbl_mobile_use}.meu02_reh_s_num like '%{$que_str}%' /* 借用路線(tw_route.s_num) */                       
                       or {$tbl_mobile_use}.meu03_flow like '%{$que_str}%' /* 借出流量 */                       
                       or BINARY {$tbl_mobile_use}.meu03_time like BINARY '%{$que_str}%' /* 借出時間 */                       
                       or {$tbl_mobile_use}.meu04_flow like '%{$que_str}%' /* 歸還流量 */                       
                       or BINARY {$tbl_mobile_use}.meu04_time like BINARY '%{$que_str}%' /* 歸還時間 */                       
                       or {$tbl_mobile_use}.meu21_y_empno like '%{$que_str}%' /* 確認者(sys_account.s_num) */                       
                       or BINARY {$tbl_mobile_use}.meu21_y_date like BINARY '%{$que_str}%' /* 確認時間 */                       
                       or {$tbl_mobile_use}.meu99 like '%{$que_str}%' /* 備註 */
                       or {$tbl_mobile}.me01 like '%{$que_str}%' /* 手機編號*/
                       or {$tbl_mobile}.me05 like '%{$que_str}%' /* 手機號碼 */
                       or sys_acc3.acc_name like '%{$que_str}%' /* 確認者 */
                       or concat(AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 外送員 */                       
                       or concat(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 外送員 */                       
                      )
                ";
    }

    if(!empty($get_data['que_me_s_num'])) { // tw_mobile.s_num
      $que_me_s_num = $get_data['que_me_s_num'];
      $que_me_s_num = $this->db->escape_like_str($que_me_s_num);
      $where .= " and {$tbl_mobile_use}.me_s_num = '{$que_me_s_num}'  /* tw_mobile.s_num */ ";
    }
    if(!empty($get_data['que_meu01_dp_s_num'])) { // 手機借用者(tw_delivery_person.s_num)
      $que_meu01_dp_s_num = $get_data['que_meu01_dp_s_num'];
      $que_meu01_dp_s_num = $this->db->escape_like_str($que_meu01_dp_s_num);
      $where .= " and {$tbl_mobile_use}.meu01_dp_s_num = '{$que_meu01_dp_s_num}'  /* 手機借用者(tw_delivery_person.s_num) */ ";
    }
    if(!empty($get_data['que_meu02_reh_s_num'])) { // 借用路線(tw_route.s_num)
      $que_meu02_reh_s_num = $get_data['que_meu02_reh_s_num'];
      $que_meu02_reh_s_num = $this->db->escape_like_str($que_meu02_reh_s_num);
      $where .= " and {$tbl_mobile_use}.meu02_reh_s_num = '{$que_meu02_reh_s_num}'  /* 借用路線(tw_route.s_num) */ ";
    }
    if(!empty($get_data['que_meu03_time'])) { // 借出時間
      $que_meu03_time = $get_data['que_meu03_time'];
      $que_meu03_time = $this->db->escape_like_str($que_meu03_time);
      $where .= " and {$tbl_mobile_use}.meu03_time like '%{$que_meu03_time}%'  /* 借出時間 */ ";
    }
    if(!empty($get_data['que_meu04_time'])) { // 歸還時間
      $que_meu04_time = $get_data['que_meu04_time'];
      $que_meu04_time = $this->db->escape_like_str($que_meu04_time);
      $where .= " and {$tbl_mobile_use}.meu04_time like '%{$que_meu04_time}%'  /* 歸還時間 */ ";
    }
    if(!empty($get_data['que_meu21_y_empno'])) { // 確認者(sys_account.s_num)
      $que_meu21_y_empno = $get_data['que_meu21_y_empno'];
      $que_meu21_y_empno = $this->db->escape_like_str($que_meu21_y_empno);
      $where .= " and {$tbl_mobile_use}.meu21_y_empno = '{$que_meu21_y_empno}'  /* 確認者(sys_account.s_num) */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_mobile_use}.s_num
                from {$tbl_mobile_use}
                left join {$tbl_mobile} on {$tbl_mobile}.s_num = {$tbl_mobile_use}.me_s_num
                left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_mobile_use}.meu02_reh_s_num
                left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile_use}.meu01_dp_s_num
                left join {$tbl_account} sys_acc3 on sys_acc3.s_num = {$tbl_mobile_use}.meu21_y_empno
                left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_mobile_use}.meu21_y_empno
                where $where
                group by {$tbl_mobile_use}.s_num
                order by {$tbl_mobile_use}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_mobile_use}.*,
                   IF(sys_acc3.acc_name is null ,
                     concat(AES_DECRYPT({$tbl_social_worker}.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_social_worker}.sw02,'{$this->db_crypt_key2}'))
                     ,sys_acc3.acc_name
                   ) as y_acc_name,
                   {$tbl_mobile}.me01,
                   {$tbl_mobile}.me05,
                   {$tbl_route_h}.reh01,
                   AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') as dp01,
                   AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') as dp02
            from {$tbl_mobile_use}
            left join {$tbl_mobile} on {$tbl_mobile}.s_num = {$tbl_mobile_use}.me_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_mobile_use}.meu02_reh_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_mobile_use}.meu01_dp_s_num
            left join {$tbl_account} sys_acc3 on sys_acc3.s_num = {$tbl_mobile_use}.meu21_y_empno
            left join {$tbl_social_worker} on {$tbl_social_worker}.s_num = {$tbl_mobile_use}.meu21_y_empno
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
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
    
    if(!$this->db->insert($tbl_mobile_use, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
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
    if(!$this->db->update($tbl_mobile_use, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_mobile_use, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_chk()
  //  函數功能: 儲存確認資訊
  //  程式設計: kiwi
  //  設計日期: 2022-05-26
  // **************************************************************************
  public function save_chk($s_num) {
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $rtn_msg = 'ok';
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $data['meu21_y_empno'] = $_SESSION['acc_s_num'];
    $data['meu21_y_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_mobile_use, $data)) {
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_mobile_use, $data)) {
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
    $tbl_mobile_use = $this->zi_init->chk_tbl_no_lang('mobile_use'); // 手機使用紀錄資料
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_mobile_use}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
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