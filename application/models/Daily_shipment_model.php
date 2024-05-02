<?php
class Daily_shipment_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd1 = array('ct_name'); // 加密欄位
  public $aes_fd2 = array('dp01','dp02','dp09_teltphone','dp09_homephone'); // 加密欄位
  public $aes_fd3 = array('ct06_telephone','ct06_homephone','ct12','ct13','ct14','ct15'); // 加密欄位
  
  public function __construct() {
    $this->load->database();
  }
  
  // **************************************************************************
  //  函數名稱: save_upd_this_page()
  //  函數功能: 本頁儲存
  //  程式設計: Kiwi
  //  設計日期: 2020/12/27
  // **************************************************************************
  public function save_upd_this_page() {
    $rtn_msg = 'ok';
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $data = $this->input->post();
    u_var_dump($data['s_num'][0]);
    foreach ($data['s_num'] as $k => $v_s_num) {
      $upd_data[$data['reh_s_num'][$k]][$data['dys08'][$k]]['e_empno'] = $_SESSION['acc_s_num'];
      $upd_data[$data['reh_s_num'][$k]][$data['dys08'][$k]]['e_date'] = date('Y-m-d H:i:s');
      $upd_data[$data['reh_s_num'][$k]][$data['dys08'][$k]]['s_num'] = $v_s_num;
      $upd_data[$data['reh_s_num'][$k]][$data['dys08'][$k]]['dp_s_num'] = $data['dp_s_num'][$k];
      $upd_data[$data['reh_s_num'][$k]][$data['dys08'][$k]]['reh_s_num'] = $data['reh_s_num'][$k];
      $upd_data[$data['reh_s_num'][$k]][$data['dys08'][$k]]['dys06'] = $data['dys08'][$k];
    }

    // 排序資料
    foreach($upd_data as $k => $v) {
      $i = 0;
      ksort($upd_data[$k]); // 先將原先的資料依照index由小到大排序一次
      foreach($upd_data[$k] as $k2 => $v2) {
        $upd_data[$k][$k2]["dys08"] = $i + 1;
        $this->db->where('s_num' , $upd_data[$k][$k2]["s_num"]);
        if(!$this->db->update($tbl_daily_shipment, $upd_data[$k][$k2])) {
         $rtn_msg = $this->lang->line('upd_ng');
         return;
        }
        $i++;
      }
    }
    echo "ok";
    return;
  }
  
  // **************************************************************************
  //  函數名稱: save_change()
  //  函數功能: 路線調整
  //  程式設計: Kiwi
  //  設計日期: 2021/06/03
  // **************************************************************************
  public function save_change() {
    $rtn_msg = 'ok';
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $data = $this->input->post();
    $dp_s_num = '';
    if(isset($data["dp_s_num"])) {
      $dp_s_num = $data["dp_s_num"];
    }
    $reh_s_num = '';
    if(isset($data["reh_s_num"]) && $data["reh_s_num"] != NULL) {
      $reh_s_num = $data["reh_s_num"];
      $route_last_num = $this->get_route_last_num($reh_s_num);
    }
    
    foreach($data['s_num'] as $k => $v) {
      if($data['select_change'][$k] == 1) {
        if(NULL != $dp_s_num) {
          $upd_data['dp_s_num'] = $dp_s_num;
        }
        if(NULL != $reh_s_num) {
          $upd_data['reh_s_num'] = $reh_s_num;
        }
        $upd_data["e_date"] = date("Y-m-d H:i:s");
        $upd_data["e_empno"] = $_SESSION['acc_s_num'];
        $upd_data["dys08"] = $data['dys08'][$k];
        $this->db->where('s_num' , $data['s_num'][$k]);
        if(!$this->db->update($tbl_daily_shipment, $upd_data)) {
         $rtn_msg = $this->lang->line('upd_ng');
         return;
        }
      }
    }
    return $rtn_msg;
  }
  // **************************************************************************
  //  函數名稱: get_route_last_num()
  //  函數功能: 取得路線最後一個人
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_route_last_num($reh_s_num) {
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    
    $row = NULL;
    $sql = "select {$tbl_daily_shipment}.*
                 from {$tbl_daily_shipment}
                 where {$tbl_daily_shipment}.reh_s_num = {$reh_s_num}
                 order by {$tbl_daily_shipment}.dys08 desc
                 limit 0 , 1;
                ";
              
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_daily_shipment()
  //  函數功能: 取得訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_daily_shipment($dp_s_num) {
    $v = $this->input->post();
    $today = date("Y-m-d");
    $type = $v["type"];
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.s_num,
                              {$tbl_daily_shipment}.ct_s_num,
                              {$tbl_daily_shipment}.dys03,
                              {$tbl_daily_shipment}.dys06,
                              CONCAT({$tbl_clients}.ct01 , {$tbl_clients}.ct02) as ClientName,
                              {$tbl_clients}.ct16 as ClientLatitude,
                              {$tbl_clients}.ct17 as ClientLongitude
                  from {$tbl_daily_shipment}
                  left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_daily_shipment}.ct_s_num
                  where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dp_s_num = ?
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  and {$tbl_daily_shipment}.dys09 = ?
                  order by {$tbl_daily_shipment}.s_num desc
                ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($dp_s_num , $type));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $today = date("Y-m-d");
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 送餐人員
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $where = " {$tbl_daily_shipment}.d_date is null ";
    $order = " {$tbl_daily_shipment}.reh_s_num , {$tbl_daily_shipment}.dys06";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_daily_shipment}.clnt_s_num like '%{$que_str}%' /* tw_clients.s_num */
                       or {$tbl_daily_shipment}.mealo_s_num like '%{$que_str}%' /* tw_meal_order.s_num */
                       or {$tbl_daily_shipment}.srvc_s_num like '%{$que_str}%' /* tw_service_case.s_num */
                       or {$tbl_daily_shipment}.acc_s_num like '%{$que_str}%' /* sus_account.acc_num */
                       or {$tbl_daily_shipment}.dys01 like '%{$que_str}%' /* 類型 */
                       or {$tbl_daily_shipment}.dys02 like '%{$que_str}%' /* 送餐日期 */
                       or {$tbl_daily_shipment}.dys06 like '%{$que_str}%' /* 送餐路線 */
                      )
                ";
    }
    
    if(!empty($get_data['que_order_kind'])) { // 查詢選擇的排序
      $_SESSION[$q_str]['que_order_kind'] = $get_data['que_order_kind'];
    }

    if(!empty($get_data['que_order'])) { // 排序
      $que_order = $get_data['que_order'];
      $que_order = $this->db->escape_like_str($que_order);
      $order = " {$tbl_daily_shipment}.reh_s_num asc , {$tbl_daily_shipment}.dys08 {$que_order}";
    }
 
    if(!empty($get_data['que_dys09'])) { // 類型
      $que_dys09 = $get_data['que_dys09'];
      $que_dys09 = $this->db->escape_like_str($que_dys09);
      $where .= " and {$tbl_daily_shipment}.dys09 = '{$que_dys09}'  /* 類型 */ ";
    }
    
    if(!empty($get_data['que_reh_s_num'])) { // reh_s_num
      $que_reh_s_num = $get_data['que_reh_s_num'];
      $que_reh_s_num = $this->db->escape_like_str($que_reh_s_num);
      $where .= " and {$tbl_daily_shipment}.reh_s_num = '{$que_reh_s_num}'  /* 路線 */ ";
    }


    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_daily_shipment}.s_num
                from {$tbl_daily_shipment}
                where $where
                and {$tbl_daily_shipment}.dys02 = '{$today}' 
                group by {$tbl_daily_shipment}.s_num
                order by {$tbl_daily_shipment}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*20000);
    $limit_e = 20000;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_daily_shipment}.*,
                   case {$tbl_daily_shipment}.dys11
                     when '2' then '未送達'
                     when '1' then '已送達'
                   end as dys11_str
                   {$this->_aes_fd('daily_shipment')}
                   {$this->_aes_fd('delivery_person')}
            from {$tbl_daily_shipment}
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_daily_shipment}.dp_s_num
            where {$where}
            and {$tbl_daily_shipment}.dys01 = '{$today}' 
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
        }
      }
    }
    return(array($data,$row_cnt));
  }
  // **************************************************************************
  //  函數名稱: sw_chk_daily_shipment()
  //  函數功能: 社工確認今日外送單
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function sw_chk_daily_shipment($acc_s_num) {
    $today = date("Y-m-d");
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $row = NULL;
    $sql = "select {$tbl_daily_shipment}.s_num , count(*) as daily_shipment_nums
                  from {$tbl_daily_shipment}
                  left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_daily_shipment}.ct_s_num
                  where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.acc_s_num = ?
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  order by {$tbl_daily_shipment}.s_num desc
                ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($acc_s_num));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
    }
    return $row;
  }
    
  // **************************************************************************
  //  函數名稱: get_last_s_num()
  //  函數功能: 取得訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_last_s_num() {
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    return $this->db->count_all($tbl_daily_shipment);
  }
  
  // **************************************************************************
  //  函數名稱: chk_daily_shipment()
  //  函數功能: 確定是否有今日訂單
  //  程式設計: kiwi
  //  設計日期: 2021-01-08
  // **************************************************************************
  public function chk_daily_shipment($type) {
    $today = date("Y-m-d");
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.*
                  from {$tbl_daily_shipment}
                  where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  and {$tbl_daily_shipment}.dys02 = ?
                  order by {$tbl_daily_shipment}.s_num desc
                ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($type));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: chk_daily_production()
  //  函數功能: 確定是否有今日送餐餐條
  //  程式設計: kiwi
  //  設計日期: 2021-01-08
  // **************************************************************************
  public function chk_daily_production($type) {
    $today = date("Y-m-d");
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_daily_production = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日配送單資料
    $data = NULL;
    $sql = "select {$tbl_daily_production}.*
                  from {$tbl_daily_production}
                  where {$tbl_daily_production}.d_date is null
                  and {$tbl_daily_production}.dyp01 = '{$today}'
                  and {$tbl_daily_production}.dyp09 = ?
                ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($type));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
        }
      }
    }
    return $data;
  }
    
  // **************************************************************************
  //  函數名稱: get_clients()
  //  函數功能: 取得今天外送案資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_clients($acc_s_num) {
    $today = date("Y-m-d");
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.ct_s_num,
                             {$tbl_daily_shipment}.sec_s_num,
                             {$tbl_daily_shipment}.mlo_s_num,
                             {$tbl_clients}.bn_s_num,
                             CONCAT({$tbl_clients}.ct01 , {$tbl_clients}.ct02) as ct_name,
                             date_format({$tbl_clients}.ct05 , '%Y-%m-%d') as ct05,
                             CONCAT({$tbl_clients}.ct09 , {$tbl_clients}.ct10 , {$tbl_clients}.ct11) as ct_address,
                             {$tbl_clients}.ct16,
                             {$tbl_clients}.ct17
                   from {$tbl_daily_shipment}
                   left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_daily_shipment}.ct_s_num 
                   where {$tbl_daily_shipment}.d_date is null
                               and {$tbl_daily_shipment}.dys01 = '{$today}'
                               and {$tbl_daily_shipment}.acc_s_num = ?
                   order by {$tbl_daily_shipment}.reh_s_num asc , {$tbl_daily_shipment}.dys06 desc
                 ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($acc_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $data = $rs->result_array();
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: save_daily_production_data()
  //  函數功能: 新增餐條資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function save_production_data($data) {
    $rtn_msg = 'ok';
    $tbl_daily_production = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日餐條資料
    // 刪除
    $this->db->where("dyp01" , date("Y-m-d"));
    if(!$this->db->delete($tbl_daily_production)) {
      $rtn_msg = $this->lang->line('del_ng');
      echo $rtn_msg;
      return;
    }
    else {
      if(!$this->db->insert_batch($tbl_daily_production, $data)) {
        $rtn_msg = $this->lang->line('add_ng');
      }
    }
    echo $rtn_msg;
  }
  
  // **************************************************************************
  //  函數名稱: save_shipment_data()
  //  函數功能: 新增外送資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function save_shipment_data($data) {
    $rtn_msg = 'ok';
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日訂單資料
    // 刪除
    $this->db->where("dys01" , date("Y-m-d"));
    if(!$this->db->delete($tbl_daily_shipment)) {
      return false;
    }
    else {
      if(!$this->db->insert_batch($tbl_daily_shipment, $data)) {
        return false;
      }
    }
    return true;
  }
    // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  private function _aes_fd($fd_name) {
    $tbl_fd= $this->zi_init->chk_tbl_no_lang("{$fd_name}"); // 每日配送單資料|每日餐條
    $aes_fd = "";
    switch($fd_name) {   
      case "daily_shipment":       
      case "daily_production":       
        $aes_arr = "aes_fd1"; 
      break;  
      case "delivery_person":       
        $aes_arr = "aes_fd2"; 
      break;  
      case "clients":       
        $aes_arr = "aes_fd3"; 
      break;  
    }
    foreach ($this->$aes_arr as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_fd}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
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