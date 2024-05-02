<?php
class Clients_hlth_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表

    $s_num = (int)$this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_clients_hlth}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_clients_hlth}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_clients_hlth}.cth02
                     when '1' then 'MNA > 23.5具營養良好'
                     when '2' then 'MAN 17~23.5 具營養不良危險性'
                     when '3' then 'MNA < 17 營養不良'
                   end as cth02_str,
                   case {$tbl_clients_hlth}.cth31
                     when '0' then '嚴重食慾不佳'
                     when '1' then '中度食慾不佳'
                     when '2' then '食慾變化'
                   end as cth31_str,
                   case {$tbl_clients_hlth}.cth32
                     when '0' then '體重減輕 > 3公斤'
                     when '1' then '不知道'
                     when '2' then '體重減輕 1~3 公斤'
                     when '3' then '體重無改變'
                   end as cth32_str,
                   case {$tbl_clients_hlth}.cth33
                     when '0' then '臥床或輪椅'
                     when '1' then '可以下床活動或離開輪椅，但無法自由走動'
                     when '2' then '可以自由走動'
                   end as cth33_str,
                   case {$tbl_clients_hlth}.cth34
                     when '0' then '是'
                     when '2' then '否'
                   end as cth34_str,
                   case {$tbl_clients_hlth}.cth35
                     when '0' then '嚴重痴呆或抑鬱'
                     when '1' then '輕度痴呆'
                     when '2' then '無精神問題'
                   end as cth35_str,
                   case {$tbl_clients_hlth}.cth36
                     when '0' then 'BMI < 19'
                     when '1' then '19 ≦ BMI < 21'
                     when '2' then '21 ≦ BMI < 23'
                     when '3' then 'BMI ≧ 23'
                   end as cth36_str,
                   case {$tbl_clients_hlth}.cth45_opt
                     when '1' then '大於或等於12分：表示正常(無營養不良危險性'
                     when '2' then '小於或等於11分：表示可能營養不良'
                   end as cth45_opt_str,
                   case {$tbl_clients_hlth}.cth51
                     when '0' then '否'
                     when '1' then '是'
                   end as cth51_str,
                   case {$tbl_clients_hlth}.cth52
                     when '0' then '否'
                     when '1' then '是'
                   end as cth52_str,
                   case {$tbl_clients_hlth}.cth53
                     when '0' then '否'
                     when '1' then '是'
                   end as cth53_str,
                   case {$tbl_clients_hlth}.cth54
                     when '0' then '1餐'
                     when '1' then '2餐'
                     when '2' then '3餐'
                   end as cth54_str,
                   case {$tbl_clients_hlth}.cth55_1
                     when '0' then '否'
                     when '1' then '是'
                   end as cth55_1_str,
                   case {$tbl_clients_hlth}.cth55_2
                     when '0' then '否'
                     when '1' then '是'
                   end as cth55_2_str,
                   case {$tbl_clients_hlth}.cth55_3
                     when '0' then '否'
                     when '1' then '是'
                   end as cth55_3_str,
                   case {$tbl_clients_hlth}.cth55_total
                     when '0' then '都是否'
                     when '0.5' then '2個是'
                     when '1' then '3個是'
                   end as cth55_total_str,
                   case {$tbl_clients_hlth}.cth56
                     when '0' then '否'
                     when '1' then '是'
                   end as cth56_str,
                   case {$tbl_clients_hlth}.cth57
                     when '0' then '少於三杯'
                     when '0.5' then '3~5杯'
                     when '1' then '大於5杯'
                   end as cth57_str,
                   case {$tbl_clients_hlth}.cth58
                     when '0' then '無人協助則無法進食'
                     when '1' then '可以自己進食但較吃力'
                     when '2' then '可以自己進食'
                   end as cth58_str,
                   case {$tbl_clients_hlth}.cth59
                     when '0' then '覺得自己營養非常不好'
                     when '1' then '不太清楚或營養不太好'
                     when '2' then '覺得自己沒有營養問題'
                   end as cth59_str,
                   case {$tbl_clients_hlth}.cth60
                     when '0' then '不如同年齡的人'
                     when '0.5' then '不知道'
                     when '1' then '和同年齡的人差不多'
                     when '2' then '比同年齡的人好'
                   end as cth60_str,
                   case {$tbl_clients_hlth}.cth61
                     when '0' then 'MAC<21'
                     when '0.5' then 'MAC21~21.9'
                     when '1' then 'MAC>'
                   end as cth61_str,
                   case {$tbl_clients_hlth}.cth62
                     when '0' then 'C.C.<31'
                     when '1' then 'C.C. >'
                   end as cth62_str,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02,
                   AES_DECRYPT({$tbl_clients}.ct05,'{$this->db_crypt_key2}') as ct05,
                   {$tbl_clients}.ct04
            from {$tbl_clients_hlth}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_clients_hlth}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_clients_hlth}.e_empno
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_clients_hlth}.cth21_ct_s_num 
            where {$tbl_clients_hlth}.d_date is null
                  and {$tbl_clients_hlth}.s_num = ?
            order by {$tbl_clients_hlth}.s_num desc
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
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_clients_hlth}.*
                   
            from {$tbl_clients_hlth}
            where {$tbl_clients_hlth}.d_date is null
                  and {$tbl_clients_hlth}.fd_name = ?
            order by {$tbl_clients_hlth}.s_num desc
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
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function get_all() {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
    $data = NULL;
    $sql = "select {$tbl_clients_hlth}.*
                   
            from {$tbl_clients_hlth}
            where {$tbl_clients_hlth}.d_date is null
            order by {$tbl_clients_hlth}.s_num desc
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
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
    $data = NULL;
    $sql = "select {$tbl_clients_hlth}.*
                   
            from {$tbl_clients_hlth}
            where {$tbl_clients_hlth}.d_date is null
                  and {$tbl_clients_hlth}.is_available = 1 /* 啟用 */
            order by {$tbl_clients_hlth}.s_num desc
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
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
    $where = " {$tbl_clients_hlth}.d_date is null ";
    $order = " {$tbl_clients_hlth}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_clients_hlth}.cth01 like '%{$que_str}%' /* 填表日期 */                       
                        or {$tbl_clients_hlth}.cth02 like '%{$que_str}%' /* MNA合計分數-OPT(1=MNA > 23.5具營養良好;2=MAN 17~23.5 具營養不良危險性;3=MNA < 17 營養不良 */                       
                        or {$tbl_clients_hlth}.cth21_ct_s_num like '%{$que_str}%' /* 案主序號-MEMO(tw_clients.s_num) */                       
                        or {$tbl_clients_hlth}.cth22 like '%{$que_str}%' /* 案主身高(公分) */                       
                        or {$tbl_clients_hlth}.cth23 like '%{$que_str}%' /* 案主體重(公斤) */                       
                        or {$tbl_clients_hlth}.cth24 like '%{$que_str}%' /* 案主膝高(公分) */                       
                        or {$tbl_clients_hlth}.cth25 like '%{$que_str}%' /* 案主BMI(體重/身高^2) */                       
                        or {$tbl_clients_hlth}.cth31 like '%{$que_str}%' /* 營養篩檢.1-OPT(0=嚴重食慾不佳;1=中度食慾不佳;2=食慾變化) */                       
                        or {$tbl_clients_hlth}.cth32 like '%{$que_str}%' /* 營養篩檢.2-OPT(0=體重減輕 > 3公斤;1=不知道;2=體重減輕 1~3 公斤;3=體重無改變) */                       
                        or {$tbl_clients_hlth}.cth33 like '%{$que_str}%' /* 營養篩檢.3-OPT(0=臥床或輪椅;1=可以下床活動或離開輪椅，但無法自由走動;2=可以自由走動) */                       
                        or {$tbl_clients_hlth}.cth34 like '%{$que_str}%' /* 營養篩檢.4-OPT(0=是;2=否) */                       
                        or {$tbl_clients_hlth}.cth35 like '%{$que_str}%' /* 營養篩檢.5-OPT(0=嚴重痴呆或抑鬱;1=輕度痴呆;2=無精神問題) */                       
                        or {$tbl_clients_hlth}.cth36 like '%{$que_str}%' /* 營養篩檢.6-OPT(0=BMI < 19;1=19 ≦ BMI < 21;2=21 ≦ BMI < 23;3=BMI ≧ 23) */                       
                        or {$tbl_clients_hlth}.cth45 like '%{$que_str}%' /* 營養篩檢分數 */                       
                        or {$tbl_clients_hlth}.cth45_opt like '%{$que_str}%' /* 營養篩檢-OPT(1=大於或等於12分：表示正常(無營養不良危險性);2=小於或等於11分：表示可能營養不良) */                       
                        or {$tbl_clients_hlth}.cth51 like '%{$que_str}%' /* 一般評估.7-OPT(0=否;1=是) */                       
                        or {$tbl_clients_hlth}.cth52 like '%{$que_str}%' /* 一般評估.8-OPT(0=否;1=是) */                       
                        or {$tbl_clients_hlth}.cth53 like '%{$que_str}%' /* 一般評估.9-OPT(0=否;1=是) */                       
                        or {$tbl_clients_hlth}.cth54 like '%{$que_str}%' /* 一般評估.10-OPT(0=1餐;1=2餐;2=3餐) */                       
                        or {$tbl_clients_hlth}.cth55_1 like '%{$que_str}%' /* 一般評估.11-OPT(0=否;1=是) */                       
                        or {$tbl_clients_hlth}.cth55_2 like '%{$que_str}%' /* 一般評估.11-OPT(0=否;1=是) */                       
                        or {$tbl_clients_hlth}.cth55_3 like '%{$que_str}%' /* 一般評估.11-OPT(0=否;1=是) */                       
                        or {$tbl_clients_hlth}.cth55_total like '%{$que_str}%' /* 一般評估.11-OPT(0=都是否;0.5=2個是;1=3個是) */                       
                        or {$tbl_clients_hlth}.cth56 like '%{$que_str}%' /* 一般評估.12-OPT(0=否;1=是) */                       
                        or {$tbl_clients_hlth}.cth57 like '%{$que_str}%' /* 一般評估.13-OPT(0=少於三杯;0.5=3~5杯;1=大於5杯) */                       
                        or {$tbl_clients_hlth}.cth58 like '%{$que_str}%' /* 一般評估.14-OPT(0=無人協助則無法進食;1=可以自己進食但較吃力;2=可以自己進食) */                       
                        or {$tbl_clients_hlth}.cth59 like '%{$que_str}%' /* 一般評估.15-OPT(0=覺得自己營養非常不好;1=不太清楚或營養不太好;2=覺得自己沒有營養問題) */                       
                        or {$tbl_clients_hlth}.cth60 like '%{$que_str}%' /* 一般評估.16-OPT(0=不如同年齡的人;0.5=不知道;1=和同年齡的人差不多;2=比同年齡的人好) */                       
                        or {$tbl_clients_hlth}.cth61_mac like '%{$que_str}%' /* 臀中圍MAC(公分) */                       
                        or {$tbl_clients_hlth}.cth61 like '%{$que_str}%' /* 一般評估.17-OPT(0=MAC<21;0.5=MAC21~21.9;1=MAC>=22) */                       
                        or {$tbl_clients_hlth}.cth62_cc like '%{$que_str}%' /* 小腿圍C.C.(公分) */                       
                        or {$tbl_clients_hlth}.cth62 like '%{$que_str}%' /* 一般評估.18-OPT(0=C.C.<31;1=C.C. >= 31 */                       
                        or {$tbl_clients_hlth}.cth70 like '%{$que_str}%' /* 一般評估總分 */                       
                        or {$tbl_clients_hlth}.cth99 like '%{$que_str}%' /* 備註 */
                        or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 案主姓名 */
                      )
                ";
    }

    if(!empty($get_data['que_cth01'])) { // 填表日期
      $que_cth01 = $get_data['que_cth01'];
      $que_cth01 = $this->db->escape_like_str($que_cth01);
      $where .= " and {$tbl_clients_hlth}.cth01 = '{$que_cth01}'  /* 填表日期 */ ";
    }
    if(!empty($get_data['que_cth02'])) { // MNA合計分數-OPT(1=MNA > 23.5具營養良好;2=MAN 17~23.5 具營養不良危險性;3=MNA < 17 營養不良
      $que_cth02 = $get_data['que_cth02'];
      $que_cth02 = $this->db->escape_like_str($que_cth02);
      $where .= " and {$tbl_clients_hlth}.cth02 = '{$que_cth02}'  /* MNA合計分數-OPT(1=MNA > 23.5具營養良好;2=MAN 17~23.5 具營養不良危險性;3=MNA < 17 營養不良 */ ";
    }
    if(!empty($get_data['que_cth21_ct_s_num'])) { // 案主序號-MEMO(tw_clients.s_num)
      $que_cth21_ct_s_num = $get_data['que_cth21_ct_s_num'];
      $que_cth21_ct_s_num = $this->db->escape_like_str($que_cth21_ct_s_num);
      $where .= " and {$tbl_clients_hlth}.cth21_ct_s_num = '{$que_cth21_ct_s_num}'  /* 案主序號-MEMO(tw_clients.s_num) */ ";
    }
    if(!empty($get_data['que_ct_name'])) { // 案主名稱
      $que_ct_name = $get_data['que_ct_name'];
      $que_ct_name = $this->db->escape_like_str($que_ct_name);
      $where .= " and concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_ct_name}%' /* 案主姓名 */";
    }
    if(!empty($get_data['que_cth45'])) { // 營養篩檢分數
      $que_cth45 = $get_data['que_cth45'];
      $que_cth45 = $this->db->escape_like_str($que_cth45);
      $where .= " and {$tbl_clients_hlth}.cth45 = '{$que_cth45}'  /* 營養篩檢分數 */ ";
    }
    if(!empty($get_data['que_cth70'])) { // 一般評估總分
      $que_cth70 = $get_data['que_cth70'];
      $que_cth70 = $this->db->escape_like_str($que_cth70);
      $where .= " and {$tbl_clients_hlth}.cth70 = '{$que_cth70}'  /* 一般評估總分 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }
    
    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_clients_hlth}.s_num
                from {$tbl_clients_hlth}
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_clients_hlth}.cth21_ct_s_num 
                where $where
                group by {$tbl_clients_hlth}.s_num
                order by {$tbl_clients_hlth}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();
    
    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_clients_hlth}.*,
                   case {$tbl_clients_hlth}.cth02
                     when '1' then 'MNA > 23.5具營養良好'
                     when '2' then 'MAN 17~23.5 具營養不良危險性'
                     when '3' then 'MNA < 17 營養不良'
                   end as cth02_str,
                   case {$tbl_clients_hlth}.cth31
                     when '0' then '嚴重食慾不佳'
                     when '1' then '中度食慾不佳'
                     when '2' then '食慾變化'
                   end as cth31_str,
                   case {$tbl_clients_hlth}.cth32
                     when '0' then '體重減輕 > 3公斤'
                     when '1' then '不知道'
                     when '2' then '體重減輕 1~3 公斤'
                     when '3' then '體重無改變'
                   end as cth32_str,
                   case {$tbl_clients_hlth}.cth33
                     when '0' then '臥床或輪椅'
                     when '1' then '可以下床活動或離開輪椅，但無法自由走動'
                     when '2' then '可以自由走動'
                   end as cth33_str,
                   case {$tbl_clients_hlth}.cth34
                     when '0' then '是'
                     when '2' then '否'
                   end as cth34_str,
                   case {$tbl_clients_hlth}.cth35
                     when '0' then '嚴重痴呆或抑鬱'
                     when '1' then '輕度痴呆'
                     when '2' then '無精神問題'
                   end as cth35_str,
                   case {$tbl_clients_hlth}.cth36
                     when '0' then 'BMI < 19'
                     when '1' then '19 ≦ BMI < 21'
                     when '2' then '21 ≦ BMI < 23'
                     when '3' then 'BMI ≧ 23'
                   end as cth36_str,
                   case {$tbl_clients_hlth}.cth45_opt
                     when '1' then '大於或等於12分：表示正常(無營養不良危險性'
                     when '2' then '小於或等於11分：表示可能營養不良'
                   end as cth45_opt_str,
                   case {$tbl_clients_hlth}.cth51
                     when '0' then '否'
                     when '1' then '是'
                   end as cth51_str,
                   case {$tbl_clients_hlth}.cth52
                     when '0' then '否'
                     when '1' then '是'
                   end as cth52_str,
                   case {$tbl_clients_hlth}.cth53
                     when '0' then '否'
                     when '1' then '是'
                   end as cth53_str,
                   case {$tbl_clients_hlth}.cth54
                     when '0' then '1餐'
                     when '1' then '2餐'
                     when '2' then '3餐'
                   end as cth54_str,
                   case {$tbl_clients_hlth}.cth55_1
                     when '0' then '否'
                     when '1' then '是'
                   end as cth55_1_str,
                   case {$tbl_clients_hlth}.cth55_2
                     when '0' then '否'
                     when '1' then '是'
                   end as cth55_2_str,
                   case {$tbl_clients_hlth}.cth55_3
                     when '0' then '否'
                     when '1' then '是'
                   end as cth55_3_str,
                   case {$tbl_clients_hlth}.cth55_total
                     when '0' then '都是否'
                     when '0.5' then '2個是'
                     when '1' then '3個是'
                   end as cth55_total_str,
                   case {$tbl_clients_hlth}.cth56
                     when '0' then '否'
                     when '1' then '是'
                   end as cth56_str,
                   case {$tbl_clients_hlth}.cth57
                     when '0' then '少於三杯'
                     when '0.5' then '3~5杯'
                     when '1' then '大於5杯'
                   end as cth57_str,
                   case {$tbl_clients_hlth}.cth58
                     when '0' then '無人協助則無法進食'
                     when '1' then '可以自己進食但較吃力'
                     when '2' then '可以自己進食'
                   end as cth58_str,
                   case {$tbl_clients_hlth}.cth59
                     when '0' then '覺得自己營養非常不好'
                     when '1' then '不太清楚或營養不太好'
                     when '2' then '覺得自己沒有營養問題'
                   end as cth59_str,
                   case {$tbl_clients_hlth}.cth60
                     when '0' then '不如同年齡的人'
                     when '0.5' then '不知道'
                     when '1' then '和同年齡的人差不多'
                     when '2' then '比同年齡的人好'
                   end as cth60_str,
                   case {$tbl_clients_hlth}.cth61
                     when '0' then 'MAC<21'
                     when '0.5' then 'MAC21~21.9'
                     when '1' then 'MAC>'
                   end as cth61_str,
                   case {$tbl_clients_hlth}.cth62
                     when '0' then 'C.C.<31'
                     when '1' then 'C.C. >'
                   end as cth62_str,
                   AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') as ct01,
                   AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') as ct02
                   
            from {$tbl_clients_hlth}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_clients_hlth}.cth21_ct_s_num 
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
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function save_add() {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
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
    
    if(!$this->db->insert($tbl_clients_hlth, $data)) {
      $rtn_msg = $this->lang->line('add_ng'); // 新增失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function save_upd() {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
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
    if(!$this->db->update($tbl_clients_hlth, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function save_is_available() {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_clients_hlth, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  public function del() {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_clients_hlth, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
  // **************************************************************************
  private function _aes_fd() {
    $tbl_clients_hlth = $this->zi_init->chk_tbl_no_lang('clients_hlth'); // 案主營養評估表
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_clients_hlth}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2022-08-03
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