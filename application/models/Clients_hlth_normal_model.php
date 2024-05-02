<?php
class Clients_hlth_normal_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料

    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_clients_hlth_normal}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_clients_hlth_normal}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_clients_hlth_normal}.chn11
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn11_str,
                   case {$tbl_clients_hlth_normal}.chn12
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn12_str,
                   case {$tbl_clients_hlth_normal}.chn13
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_1_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_2_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_3_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_99
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_99_str,
                   case {$tbl_clients_hlth_normal}.chn14
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn14_str,
                   case {$tbl_clients_hlth_normal}.chn14_opt_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn14_opt_1_str,
                   case {$tbl_clients_hlth_normal}.chn14_opt_2
                     when '1' then '正常'
                     when '2' then '少尿'
                   end as chn14_opt_2_str,
                   case {$tbl_clients_hlth_normal}.chn15
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn15_str,
                   case {$tbl_clients_hlth_normal}.chn16
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn16_str,
                   case {$tbl_clients_hlth_normal}.chn17
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn17_str,
                   case {$tbl_clients_hlth_normal}.chn18
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn18_str,
                   case {$tbl_clients_hlth_normal}.chn18_opt_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn18_opt_1_str,
                   case {$tbl_clients_hlth_normal}.chn18_opt_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn18_opt_2_str,
                   case {$tbl_clients_hlth_normal}.chn19
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn19_str,
                   case {$tbl_clients_hlth_normal}.chn20
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn20_str,
                   case {$tbl_clients_hlth_normal}.chn21
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn21_str,
                   case {$tbl_clients_hlth_normal}.chn22
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn22_str,
                   case {$tbl_clients_hlth_normal}.chn23
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn23_str,
                   case {$tbl_clients_hlth_normal}.chn23_opt1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn23_opt1_str,
                   case {$tbl_clients_hlth_normal}.chn24
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn24_str,
                   case {$tbl_clients_hlth_normal}.chn25
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn25_str,
                   case {$tbl_clients_hlth_normal}.chn26
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn26_str,
                   case {$tbl_clients_hlth_normal}.chn27
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn27_str,
                   case {$tbl_clients_hlth_normal}.chn28
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn28_str,
                   case {$tbl_clients_hlth_normal}.chn29
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn29_str,
                   case {$tbl_clients_hlth_normal}.chn32
                     when '1' then '正常'
                     when '2' then '可咀嚼軟質食物'
                     when '3' then '咀嚼能力較差'
                     when '4' then '咀嚼不良'
                   end as chn32_str,
                   case {$tbl_clients_hlth_normal}.chn33
                     when '1' then '普通（可吃完）'
                     when '2' then '良好（吃不夠）'
                     when '3' then '不良（<1/2餐盤量）'
                   end as chn33_str,
                   case {$tbl_clients_hlth_normal}.chn73_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_1_str,
                   case {$tbl_clients_hlth_normal}.chn73_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_2_str,
                   case {$tbl_clients_hlth_normal}.chn73_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_3_str,
                   case {$tbl_clients_hlth_normal}.chn73_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_4_str,
                   case {$tbl_clients_hlth_normal}.chn73_99
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_99_str,
                   case {$tbl_clients_hlth_normal}.chn91_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_1_str,
                   case {$tbl_clients_hlth_normal}.chn91_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_2_str,
                   case {$tbl_clients_hlth_normal}.chn91_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_3_str,
                   case {$tbl_clients_hlth_normal}.chn91_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_4_str,
                   case {$tbl_clients_hlth_normal}.chn91_5
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_5_str,
                   case {$tbl_clients_hlth_normal}.chn91_6
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_6_str,
                   case {$tbl_clients_hlth_normal}.chn91_7
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_7_str,
                   case {$tbl_clients_hlth_normal}.chn92_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_1_str,
                   case {$tbl_clients_hlth_normal}.chn92_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_2_str,
                   case {$tbl_clients_hlth_normal}.chn92_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_3_str,
                   case {$tbl_clients_hlth_normal}.chn92_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_4_str,
                   case {$tbl_clients_hlth_normal}.chn92_5
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_5_str,
                   case {$tbl_clients_hlth_normal}.chn92_99
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_99_str,
                   {$tbl_clients}.ct04,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct05,'{$this->db_crypt_key2}') as ct05
            from {$tbl_clients_hlth_normal}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_clients_hlth_normal}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_clients_hlth_normal}.e_empno
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_clients_hlth_normal}.chn02_ct_s_num 
            where {$tbl_clients_hlth_normal}.d_date is null
                  and {$tbl_clients_hlth_normal}.s_num = ?
            order by {$tbl_clients_hlth_normal}.s_num desc
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
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_clients_hlth_normal}.*
                   
            from {$tbl_clients_hlth_normal}
            where {$tbl_clients_hlth_normal}.d_date is null
                  and {$tbl_clients_hlth_normal}.fd_name = ?
            order by {$tbl_clients_hlth_normal}.s_num desc
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
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function get_all() {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $data = NULL;
    $sql = "select {$tbl_clients_hlth_normal}.*
                   
            from {$tbl_clients_hlth_normal}
            where {$tbl_clients_hlth_normal}.d_date is null
            order by {$tbl_clients_hlth_normal}.s_num desc
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
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $data = NULL;
    $sql = "select {$tbl_clients_hlth_normal}.*
                   
            from {$tbl_clients_hlth_normal}
            where {$tbl_clients_hlth_normal}.d_date is null
                  and {$tbl_clients_hlth_normal}.is_available = 1 /* 啟用 */
            order by {$tbl_clients_hlth_normal}.s_num desc
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
  //  函數功能: 取得所有追蹤資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function get_track($s_num) {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $tbl_clients_hlth_normal_track = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal_track'); // 營養師營養評估-追蹤紀錄表
    $data = NULL;
    $sql = "select {$tbl_clients_hlth_normal_track}.*,
                   case {$tbl_clients_hlth_normal_track}.chnt21
                     when '1' then '維持原計畫'
                     when '2' then '改變飲食計畫'
                   end as chnt21_str,
                   case {$tbl_clients_hlth_normal_track}.chnt22_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt22_1_str,
                   case {$tbl_clients_hlth_normal_track}.chnt22_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt22_2_str,
                   case {$tbl_clients_hlth_normal_track}.chnt22_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt22_3_str,
                   case {$tbl_clients_hlth_normal_track}.chnt22_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt22_4_str,
                   case {$tbl_clients_hlth_normal_track}.chnt22_5
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt22_5_str,
                   case {$tbl_clients_hlth_normal_track}.chnt22_6
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt22_6_str,
                   case {$tbl_clients_hlth_normal_track}.chnt22_7
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt22_7_str,
                   case {$tbl_clients_hlth_normal_track}.chnt23_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt23_1_str,
                   case {$tbl_clients_hlth_normal_track}.chnt23_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt23_2_str,
                   case {$tbl_clients_hlth_normal_track}.chnt23_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt23_3_str,
                   case {$tbl_clients_hlth_normal_track}.chnt23_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt23_4_str,
                   case {$tbl_clients_hlth_normal_track}.chnt23_5
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt23_5_str,
                   case {$tbl_clients_hlth_normal_track}.chnt23_99
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chnt23_99_str
            from {$tbl_clients_hlth_normal_track}
            left join {$tbl_clients_hlth_normal} on {$tbl_clients_hlth_normal}.s_num = {$tbl_clients_hlth_normal_track}.chnt01_chn_s_num
            where {$tbl_clients_hlth_normal_track}.d_date is null
                  and {$tbl_clients_hlth_normal_track}.chnt01_chn_s_num = ?
            order by {$tbl_clients_hlth_normal_track}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
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
  //  函數名稱: get_all_by_ct_s_num()
  //  函數功能: 取得案主前三筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-08
  // **************************************************************************
  public function get_all_by_ct_s_num($ct_s_num, $limit=NULL) {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表

    $sql_limit = '';
    if(NULL != $limit) {
      $sql_limit .= "LIMIT 3";
    }

    $data = NULL;
    $sql = "select {$tbl_clients_hlth_normal}.*
            from {$tbl_clients_hlth_normal}
            where {$tbl_clients_hlth_normal}.d_date is null
                  and {$tbl_clients_hlth_normal}.chn02_ct_s_num = '{$ct_s_num}'
            order by {$tbl_clients_hlth_normal}.chn01 desc
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
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料

    $where = " {$tbl_clients_hlth_normal}.d_date is null ";
    $order = " {$tbl_clients_hlth_normal}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_clients_hlth_normal}.chn01 like '%{$que_str}%' /* 填表日期 */                       
                        or {$tbl_clients_hlth_normal}.chn02_ct_s_num like '%{$que_str}%' /* 案主序號-MEMO(tw_clients.s_num) */                       
                        or {$tbl_clients_hlth_normal}.chn11 like '%{$que_str}%' /* S-糖尿病-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn12 like '%{$que_str}%' /* S-心血管疾病-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn13 like '%{$que_str}%' /* S-高血壓-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn13_opt_1 like '%{$que_str}%' /* S-高膽固醇-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn13_opt_2 like '%{$que_str}%' /* S-高三酸甘油酯-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn13_opt_3 like '%{$que_str}%' /* S-中風-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn13_opt_99 like '%{$que_str}%' /* S-其他-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn13_opt_99_memo like '%{$que_str}%' /* S-高血壓其他 */                       
                        or {$tbl_clients_hlth_normal}.chn14 like '%{$que_str}%' /* S-腎臟病-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn14_opt_1 like '%{$que_str}%' /* S-洗腎治療-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn14_opt_2 like '%{$que_str}%' /* S-尿量-OPT(1=正常；2=少尿) */                       
                        or {$tbl_clients_hlth_normal}.chn15 like '%{$que_str}%' /* S-外傷（褥瘡）-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn16 like '%{$que_str}%' /* S-痛風-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn17 like '%{$que_str}%' /* S-肝炎-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn18 like '%{$que_str}%' /* S-肝硬化-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn18_opt_1 like '%{$que_str}%' /* S-食道靜脈曲張-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn18_opt_2 like '%{$que_str}%' /* S-腹水-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn19 like '%{$que_str}%' /* S-長期腹瀉、嘔吐-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn20 like '%{$que_str}%' /* S-消化性潰瘍-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn21 like '%{$que_str}%' /* S-甲狀腺亢進/低下-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn22 like '%{$que_str}%' /* S-慢性阻塞性肺疾病-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn23 like '%{$que_str}%' /* S-胰臟炎-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn23_opt1 like '%{$que_str}%' /* S-脂肪瀉-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn24 like '%{$que_str}%' /* S-長期服用類固醇、利尿劑、瀉藥-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn25 like '%{$que_str}%' /* S-癌症-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn25_memo like '%{$que_str}%' /* S-癌症備註 */                       
                        or {$tbl_clients_hlth_normal}.chn26 like '%{$que_str}%' /* S-失智症-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn27 like '%{$que_str}%' /* S-巴金森-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn28 like '%{$que_str}%' /* S-精神疾病-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn29 like '%{$que_str}%' /* S-其他-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn29_memo like '%{$que_str}%' /* S-其他 */                       
                        or {$tbl_clients_hlth_normal}.chn31 like '%{$que_str}%' /* S-飲食狀況 */                       
                        or {$tbl_clients_hlth_normal}.chn32 like '%{$que_str}%' /* S-咀嚼能力-OPT(1=正常；2=可咀嚼軟質食物；3=咀嚼能力較差；4=咀嚼不良) */                       
                        or {$tbl_clients_hlth_normal}.chn33 like '%{$que_str}%' /* S-食慾狀況-OPT(1=普通（可吃完）；2=良好（吃不夠）；3=不良（<1/2餐盤量）) */                       
                        or {$tbl_clients_hlth_normal}.chn51 like '%{$que_str}%' /* O-案主身高(公分) */                       
                        or {$tbl_clients_hlth_normal}.chn52 like '%{$que_str}%' /* O-案主體重(公斤) */                       
                        or {$tbl_clients_hlth_normal}.chn53 like '%{$que_str}%' /* O-其他 */                       
                        or {$tbl_clients_hlth_normal}.chn71 like '%{$que_str}%' /* A-案主BMI(體重/身高^2) */                       
                        or {$tbl_clients_hlth_normal}.chn72 like '%{$que_str}%' /* A-調整體重 */                       
                        or {$tbl_clients_hlth_normal}.chn73_1 like '%{$que_str}%' /* A-營養診斷-體位-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn73_1_memo like '%{$que_str}%' /* A-營養診斷-體位-備註 */                       
                        or {$tbl_clients_hlth_normal}.chn73_2 like '%{$que_str}%' /* A-營養診斷-熱量攝取-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn73_2_memo like '%{$que_str}%' /* A-營養診斷-熱量攝取-備註 */                       
                        or {$tbl_clients_hlth_normal}.chn73_3 like '%{$que_str}%' /* A-營養診斷-飲食不均衡-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn73_3_memo like '%{$que_str}%' /* A-營養診斷-飲食不均衡-備註 */                       
                        or {$tbl_clients_hlth_normal}.chn73_4 like '%{$que_str}%' /* A-營養診斷-營養知識認知不足-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn73_4_memo like '%{$que_str}%' /* A-營養診斷-營養認識認知不足-備註 */                       
                        or {$tbl_clients_hlth_normal}.chn73_99 like '%{$que_str}%' /* A-營養診斷-其他-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn73_99_memo like '%{$que_str}%' /* A-營養診斷-其他-備註 */                       
                        or {$tbl_clients_hlth_normal}.chn91_1 like '%{$que_str}%' /* P-飲食建議-均衡飲食-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn91_2 like '%{$que_str}%' /* P-飲食建議-糖尿病-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn91_3 like '%{$que_str}%' /* P-飲食建議-低油、低鹽-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn91_4 like '%{$que_str}%' /* P-飲食建議-高蛋白-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn91_5 like '%{$que_str}%' /* P-飲食建議-低蛋白-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn91_6 like '%{$que_str}%' /* P-飲食建議-低普林-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn91_7 like '%{$que_str}%' /* P-飲食建議-低渣-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn92_1 like '%{$que_str}%' /* P-質地建議-普通-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn92_2 like '%{$que_str}%' /* P-質地建議-軟質-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn92_3 like '%{$que_str}%' /* P-質地建議-細軟-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn92_4 like '%{$que_str}%' /* P-質地建議-半流質-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn92_5 like '%{$que_str}%' /* P-質地建議-全流質-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn92_99 like '%{$que_str}%' /* P-質地建議-其他-OPT(Y=有；N=無) */                       
                        or {$tbl_clients_hlth_normal}.chn92_99_memo like '%{$que_str}%' /* P-質地建議-其他內容 */                       
                        or {$tbl_clients_hlth_normal}.chn93 like '%{$que_str}%' /* P-建議熱量(大卡/天) */                       
                        or {$tbl_clients_hlth_normal}.chn94 like '%{$que_str}%' /* P-其他 */                       
                        or {$tbl_clients_hlth_normal}.chn95 like '%{$que_str}%' /* P-建議追蹤項目 */
                        or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */
                      )
                ";
    }

    if(!empty($get_data['que_chn01'])) { // 填表日期
      $que_chn01 = $get_data['que_chn01'];
      $que_chn01 = $this->db->escape_like_str($que_chn01);
      $where .= " and {$tbl_clients_hlth_normal}.chn01 = '{$que_chn01}'  /* 填表日期 */ ";
    }
    if(!empty($get_data['que_ct_name'])) { // 案主名稱
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_ct_name}%' /* 案主姓名 */";
    }
    if(!empty($get_data['que_ct_s_num'])) { // 案主序號
      $que_ct_s_num = $get_data['que_ct_s_num'];
      $que_ct_s_num = $this->db->escape_like_str($que_ct_s_num);
      $where .= " and {$tbl_clients_hlth_normal}.chn02_ct_s_num = {$que_ct_s_num} /* 案主姓名 */";
    }
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_clients_hlth_normal}.s_num
                from {$tbl_clients_hlth_normal}
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_clients_hlth_normal}.chn02_ct_s_num 
                where $where
                group by {$tbl_clients_hlth_normal}.s_num
                order by {$tbl_clients_hlth_normal}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_clients_hlth_normal}.*,
                   case {$tbl_clients_hlth_normal}.chn11
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn11_str,
                   case {$tbl_clients_hlth_normal}.chn12
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn12_str,
                   case {$tbl_clients_hlth_normal}.chn13
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_1_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_2_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_3_str,
                   case {$tbl_clients_hlth_normal}.chn13_opt_99
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn13_opt_99_str,
                   case {$tbl_clients_hlth_normal}.chn14
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn14_str,
                   case {$tbl_clients_hlth_normal}.chn14_opt_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn14_opt_1_str,
                   case {$tbl_clients_hlth_normal}.chn14_opt_2
                     when '1' then '正常'
                     when '2' then '少尿'
                   end as chn14_opt_2_str,
                   case {$tbl_clients_hlth_normal}.chn15
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn15_str,
                   case {$tbl_clients_hlth_normal}.chn16
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn16_str,
                   case {$tbl_clients_hlth_normal}.chn17
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn17_str,
                   case {$tbl_clients_hlth_normal}.chn18
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn18_str,
                   case {$tbl_clients_hlth_normal}.chn18_opt_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn18_opt_1_str,
                   case {$tbl_clients_hlth_normal}.chn18_opt_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn18_opt_2_str,
                   case {$tbl_clients_hlth_normal}.chn19
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn19_str,
                   case {$tbl_clients_hlth_normal}.chn20
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn20_str,
                   case {$tbl_clients_hlth_normal}.chn21
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn21_str,
                   case {$tbl_clients_hlth_normal}.chn22
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn22_str,
                   case {$tbl_clients_hlth_normal}.chn23
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn23_str,
                   case {$tbl_clients_hlth_normal}.chn23_opt1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn23_opt1_str,
                   case {$tbl_clients_hlth_normal}.chn24
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn24_str,
                   case {$tbl_clients_hlth_normal}.chn25
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn25_str,
                   case {$tbl_clients_hlth_normal}.chn26
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn26_str,
                   case {$tbl_clients_hlth_normal}.chn27
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn27_str,
                   case {$tbl_clients_hlth_normal}.chn28
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn28_str,
                   case {$tbl_clients_hlth_normal}.chn29
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn29_str,
                   case {$tbl_clients_hlth_normal}.chn32
                     when '1' then '正常'
                     when '2' then '可咀嚼軟質食物'
                     when '3' then '咀嚼能力較差'
                     when '4' then '咀嚼不良'
                   end as chn32_str,
                   case {$tbl_clients_hlth_normal}.chn33
                     when '1' then '普通（可吃完）'
                     when '2' then '良好（吃不夠）'
                     when '3' then '不良（<1/2餐盤量）'
                   end as chn33_str,
                   case {$tbl_clients_hlth_normal}.chn73_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_1_str,
                   case {$tbl_clients_hlth_normal}.chn73_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_2_str,
                   case {$tbl_clients_hlth_normal}.chn73_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_3_str,
                   case {$tbl_clients_hlth_normal}.chn73_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_4_str,
                   case {$tbl_clients_hlth_normal}.chn73_99
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn73_99_str,
                   case {$tbl_clients_hlth_normal}.chn91_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_1_str,
                   case {$tbl_clients_hlth_normal}.chn91_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_2_str,
                   case {$tbl_clients_hlth_normal}.chn91_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_3_str,
                   case {$tbl_clients_hlth_normal}.chn91_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_4_str,
                   case {$tbl_clients_hlth_normal}.chn91_5
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_5_str,
                   case {$tbl_clients_hlth_normal}.chn91_6
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_6_str,
                   case {$tbl_clients_hlth_normal}.chn91_7
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn91_7_str,
                   case {$tbl_clients_hlth_normal}.chn92_1
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_1_str,
                   case {$tbl_clients_hlth_normal}.chn92_2
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_2_str,
                   case {$tbl_clients_hlth_normal}.chn92_3
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_3_str,
                   case {$tbl_clients_hlth_normal}.chn92_4
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_4_str,
                   case {$tbl_clients_hlth_normal}.chn92_5
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_5_str,
                   case {$tbl_clients_hlth_normal}.chn92_99
                     when 'Y' then '有'
                     when 'N' then '無'
                   end as chn92_99_str,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct05,'{$this->db_crypt_key2}') as ct05
            from {$tbl_clients_hlth_normal}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_clients_hlth_normal}.chn02_ct_s_num 
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
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function save_add() {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $tbl_clients_hlth_normal_track = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal_track'); // 營養師營養評估-追蹤紀錄表

    $rtn_msg = 'ok';
    $track = NULL;
    $data = $this->input->post();
    if(isset($data['track'])) {
      $track = $data['track'];
      unset($data['track']);
    }
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
    
    if(!$this->db->insert($tbl_clients_hlth_normal, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    else {
      $chn_s_num = $this->db->insert_id();
      if(NULL != $track) {
        foreach ($track as $k => $v) {
          unset($track[$k]['s_num']);
          if($v['chnt02'] == '') {
            unset($track[$k]);
            continue;
          }
          $track[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $track[$k]['b_date'] = date('Y-m-d H:i:s');
          $track[$k]['chnt01_chn_s_num'] = $chn_s_num;
        }
        if(NULL != $track) {
          if(!$this->db->insert_batch($tbl_clients_hlth_normal_track, $track)) {
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
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function save_upd() {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $tbl_clients_hlth_normal_track = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal_track'); // 營養師營養評估-追蹤紀錄表

    $rtn_msg = 'ok';
    $track = NULL;
    $data = $this->input->post();
    if(isset($data['track'])) {
      $track = $data['track'];
      unset($data['track']);
    }
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
    if(!$this->db->update($tbl_clients_hlth_normal, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    else {
      $chn_s_num = $data['s_num'];
      $this->db->where('chnt01_chn_s_num', $chn_s_num);
      if(!$this->db->delete($tbl_clients_hlth_normal_track)) {
        $rtn_msg = $this->lang->line('upd_ng'); // 新增失敗!!
      }
      if(NULL != $track) {
        foreach ($track as $k => $v) {
          unset($track[$k]['s_num']);
          if($v['chnt02'] == '') {
            unset($track[$k]);
            continue;
          }
          $track[$k]['b_empno'] = $_SESSION['acc_s_num'];
          $track[$k]['b_date'] = date('Y-m-d H:i:s');
          $track[$k]['chnt01_chn_s_num'] = $chn_s_num;
        }
        if(NULL != $track) {
          if(!$this->db->insert_batch($tbl_clients_hlth_normal_track, $track)) {
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
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function save_is_available() {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_clients_hlth_normal, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  public function del() {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_clients_hlth_normal, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
  // **************************************************************************
  private function _aes_fd() {
    $tbl_clients_hlth_normal = $this->zi_init->chk_tbl_no_lang('clients_hlth_normal'); // 營養師營養評估表
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_clients_hlth_normal}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-10
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