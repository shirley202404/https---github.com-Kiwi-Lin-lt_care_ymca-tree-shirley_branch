<?php
class Daily_work_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd1 = array('ct_name'); // 加密欄位
  public $aes_fd2 = array('dp01', 'dp02', 'dp09_teltphone', 'dp09_homephone'); // 加密欄位
  public $aes_fd3 = array('ct06_telephone', 'ct06_homephone', 'ct12', 'ct13', 'ct14', 'ct15'); // 加密欄位

  public function __construct() {
    $this-> load->database();
  }
  // **************************************************************************
  //  函數名稱: get_data_by_date()
  //  函數功能: 取得查詢日期的所有資料
  //  程式設計: kiwi
  //  設計日期: 2021/11/21
  // **************************************************************************
  public function get_data_by_date($download_date = NULL) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務資料
    $tbl_daily_production = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日餐條資料
    $data = NULL;
    $sql = "select {$tbl_daily_production}.*,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04,
                   {$tbl_meal}.s_num as ml_s_num
            from {$tbl_daily_production}
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_daily_production}.sec_s_num
            left join {$tbl_meal_order} on {$tbl_meal_order}.s_num = {$tbl_daily_production}.mlo_s_num
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_order}.ml_s_num
            where {$tbl_daily_production}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_meal_order}.d_date is null
                  and {$tbl_meal}.d_date is null
                  and {$tbl_daily_production}.dyp01 = '{$download_date}'
                  and {$tbl_daily_production}.dyp10 = 'Y'
            order by {$tbl_daily_production}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
  //  函數名稱: get_daily_shipment()
  //  函數功能: 取得訂單(API使用)
  //  程式設計: Kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_daily_shipment($dp_s_num, $dys10) {
    $v = $this->input->post();
    $type = $v["type"];
    $today = date("Y-m-d");
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.s_num,
                   {$tbl_daily_shipment}.sec_s_num,
                   {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.mlo_s_num,
                   {$tbl_daily_shipment}.reh_s_num,
                   {$tbl_daily_shipment}.dys02,
                   {$tbl_daily_shipment}.dys03,
                   {$tbl_daily_shipment}.dys05_type,
                   {$tbl_daily_shipment}.dys06,
                   {$tbl_daily_shipment}.dys08,
                   {$tbl_daily_shipment}.dys09,
                   {$tbl_daily_shipment}.dys13,
                   {$tbl_daily_shipment}.dys21,
                   {$tbl_daily_shipment}.dys23,
                   {$tbl_meal_replacement}.ct_mp01,
                   {$tbl_meal_replacement}.ct_mp04,
                   {$tbl_meal_replacement}.ct_mp06,
                   {$tbl_service_case}.sec06,
                   {$tbl_clients}.ct16,
                   {$tbl_clients}.ct17
                   {$this->_aes_fd('daily_shipment')}
                   {$this->_aes_fd('clients')}
            from {$tbl_daily_shipment}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_daily_shipment}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_daily_shipment}.sec_s_num
            left join {$tbl_meal_replacement} on {$tbl_meal_replacement}.sec_s_num = {$tbl_daily_shipment}.sec_s_num
            where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dp_s_num = ?
                  and {$tbl_daily_shipment}.dys01 = ?
                  and {$tbl_daily_shipment}.dys09 = ?
                  and {$tbl_daily_shipment}.dys10 = ?
            order by {$tbl_daily_shipment}.reh_s_num asc , {$tbl_daily_shipment}.dys08 asc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($dp_s_num, $today, $type, $dys10));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          $data[$row->s_num]['ct_address'] = "{$row->ct13}{$row->ct14}{$row->ct15}";
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_daily_abnormal()
  //  函數功能: 取得停復餐表(API使用)
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_daily_abnormal($dp_s_num , $sec_s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料
    $v = $this->input->post();
    $today = date("Y-m-d");
    $type = $v["type"];
    $row = NULL;
    $sql = "select {$tbl_daily_shipment}.ct_name,
                   {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.s_num,
                   {$tbl_meal_instruction_log_s}.mil_s01,
                   {$tbl_meal_instruction_log_s}.mil_s02,
                   {$tbl_meal_instruction_log_s}.s_num as mil_s_num
            from {$tbl_daily_shipment}
            left join {$tbl_meal_instruction_log_s} on {$tbl_meal_instruction_log_s}.sec_s_num = {$tbl_daily_shipment}.sec_s_num
            where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dp_s_num = {$dp_s_num}
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  and {$tbl_daily_shipment}.sec_s_num = '{$sec_s_num}'
                  and {$tbl_meal_instruction_log_s}.d_date is null
                  and {$tbl_meal_instruction_log_s}.mil_s02 <= '{$today} 23:59:59'
                  and {$tbl_meal_instruction_log_s}.mil_s03 = 'Y'
            order by {$tbl_meal_instruction_log_s}.mil_s02 desc , {$tbl_meal_instruction_log_s}.b_date desc
            limit 1;
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_daily_shipment_by_reh()
  //  函數功能: 用路線取得訂單資料(電子看板使用)
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_daily_shipment_by_reh($dys10) {
    $v = $this->input->post();
    $today = date("Y-m-d");
    $type = $v["type"];
    $reh_s_num = $v["reh_s_num"];
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.s_num,
                   {$tbl_daily_shipment}.sec_s_num,
                   {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.mlo_s_num,
                   {$tbl_daily_shipment}.reh_s_num,
                   {$tbl_daily_shipment}.dys02,
                   {$tbl_daily_shipment}.dys03,
                   {$tbl_daily_shipment}.dys04,
                   {$tbl_daily_shipment}.dys05_type,
                   {$tbl_daily_shipment}.dys06,
                   {$tbl_daily_shipment}.dys07,
                   {$tbl_daily_shipment}.dys08,
                   {$tbl_daily_shipment}.dys13,
                   {$tbl_meal_replacement}.ct_mp01,
                   {$tbl_meal_replacement}.ct_mp04,
                   {$tbl_meal_replacement}.ct_mp06,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec06,
                   {$tbl_service_case}.sec99,
                   {$tbl_clients}.ct16,
                   {$tbl_clients}.ct17
                   {$this->_aes_fd('daily_shipment')}
                   {$this->_aes_fd('clients')}
             from {$tbl_daily_shipment}
             left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_daily_shipment}.ct_s_num
             left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_daily_shipment}.sec_s_num
             left join {$tbl_meal_replacement} on {$tbl_meal_replacement}.sec_s_num = {$tbl_daily_shipment}.sec_s_num
             where {$tbl_daily_shipment}.d_date is null
                   and {$tbl_daily_shipment}.reh_s_num = ?
                   and {$tbl_daily_shipment}.dys01 = ?
                   and {$tbl_daily_shipment}.dys09 = ?
                   and {$tbl_daily_shipment}.dys10 = ?
             order by {$tbl_daily_shipment}.reh_s_num asc , {$tbl_daily_shipment}.dys08 asc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($reh_s_num , $today , $type , $dys10));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          $data[$row->s_num]['ct_address'] = "{$row->ct13}{$row->ct14}{$row->ct15}";
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_daily_abnormal_by_reh()
  //  函數功能: 取得停復餐表(電子看板使用)
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_daily_abnormal_by_reh($sec_s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料
    $v = $this->input->post();
    $today = date("Y-m-d");
    $type = $v["type"];
    $reh_s_num = $v["reh_s_num"];
    $row = NULL;
    $sql = "select {$tbl_daily_shipment}.ct_name,
                   {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.s_num,
                   {$tbl_meal_instruction_log_s}.mil_s01,
                   {$tbl_meal_instruction_log_s}.mil_s02,
                   {$tbl_meal_instruction_log_s}.s_num as mil_s_num
            from {$tbl_daily_shipment}
            left join {$tbl_meal_instruction_log_s} on {$tbl_meal_instruction_log_s}.sec_s_num = {$tbl_daily_shipment}.sec_s_num
            where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.reh_s_num = {$reh_s_num}
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  and {$tbl_daily_shipment}.sec_s_num = '{$sec_s_num}'
                  and {$tbl_meal_instruction_log_s}.d_date is null
                  and {$tbl_meal_instruction_log_s}.mil_s02 <= '{$today} 23:59:59'
                  and {$tbl_meal_instruction_log_s}.mil_s03 = 'Y'
            order by {$tbl_meal_instruction_log_s}.mil_s02 desc , {$tbl_meal_instruction_log_s}.b_date desc
            limit 1;
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_daily_restore()
  //  函數功能: 取得停復餐表
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_daily_restore($dp_s_num , $ct_s_num_arr=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料
    $v = $this->input->post();
    $type = $v["type"];
    $today = date("Y-m-d");
    $before_3day = date('Y-m-d', strtotime(' -3 day'));
    if(NULL != $ct_s_num_arr) { 
      $ct_s_num_str = implode(",", $ct_s_num_arr);
    }
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.ct_name,
                   {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.s_num
            from {$tbl_daily_shipment}
            left join {$tbl_meal_instruction_log_s} on {$tbl_meal_instruction_log_s}.sec_s_num = {$tbl_daily_shipment}.sec_s_num
            where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dp_s_num = {$dp_s_num}
                  and {$tbl_daily_shipment}.ct_s_num not in ($ct_s_num_str)
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  and {$tbl_meal_instruction_log_s}.mil_s02 <= '{$today}'
                  and {$tbl_meal_instruction_log_s}.mil_s02 >= '{$before_3day}'
                  and {$tbl_meal_instruction_log_s}.mil_s01 = 'Y'
            group by {$tbl_meal_instruction_log_s}.sec_s_num
            order by {$tbl_meal_instruction_log_s}.mil_s02 desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
  //  函數名稱: get_restore()
  //  函數功能: 取得訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_restore($dp_s_num) {
    $v = $this->input->post();
    $today = date("Y-m-d");
    $type = $v["type"];
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_meal_instruction_log_s = $this->zi_init->chk_tbl_no_lang('meal_instruction_log_s'); // 停復餐異動資料

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
                  and {$tbl_daily_shipment}.dys06 = 1
                  and {$tbl_daily_shipment}.dys09 = ?
            order by {$tbl_daily_shipment}.reh_s_num asc , {$tbl_daily_shipment}.dys08 asc
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
  public function chk_daily_shipment($produce_date) {
    $v = $this->input->post();
    $produce_time = $v["produce_time"];
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.*
                  from {$tbl_daily_shipment}
                  where {$tbl_daily_shipment}.d_date is null
                        and {$tbl_daily_shipment}.dys01 = '{$produce_date}'
                        and {$tbl_daily_shipment}.dys09 = ?
                  order by {$tbl_daily_shipment}.s_num desc
                ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($produce_time));
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
    $tbl_daily_production = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日餐點資料
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
  public function get_clients($dp_s_num) {
    $today = date("Y-m-d");
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.sec_s_num,
                   {$tbl_daily_shipment}.mlo_s_num,
                   {$tbl_clients}.bn_s_num,
                   {$tbl_clients}.ct01, 
                   {$tbl_clients}.ct02,
                   date_format({$tbl_clients}.ct05 , '%Y-%m-%d') as ct05,
                   {$tbl_clients}.ct09, 
                   {$tbl_clients}.ct10, 
                   {$tbl_clients}.ct11,
                   {$tbl_clients}.ct16,
                   {$tbl_clients}.ct17
                   {$this->clients_model->_aes_fd()}
            from {$tbl_daily_shipment}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_daily_shipment}.ct_s_num 
            where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  and {$tbl_daily_shipment}.dp_s_num = ?
            order by {$tbl_daily_shipment}.reh_s_num asc , {$tbl_daily_shipment}.dys06 desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($dp_s_num));
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
    $v = $this->input->post();
    $tbl_daily_production = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日餐條資料
    // 刪除
    $this->db->where("dyp01" , $v['produce_date']);
    $this->db->where("dyp09" , $v['produce_time']);
    if(!$this->db->delete($tbl_daily_production)) {
      $rtn_msg = $this->lang->line('del_ng');
      echo $rtn_msg;
      return;
    }
    else {
      if(NULL != $data) {
        foreach ($data as $k => $v) {
          foreach ($v as $ks => $vs) {
            if(in_array($ks , $this->aes_fd1)) { // 加密欄位
              $this->db->set($ks, "AES_ENCRYPT('{$vs}','{$this->db_crypt_key2}')", FALSE);
              unset($data[$k][$ks]);
            }
          } 
          if(!$this->db->insert($tbl_daily_production, $data[$k])) {
            return false;
          }
        } 
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: get_one_shipment_row()
  //  函數功能: 查詢當天外送資料(防止重新產生時，如有調整外送員會被蓋掉)
  //  程式設計: kiwi
  //  設計日期: 2021-12-23
  // **************************************************************************
  public function get_one_shipment($produce_date, $sec_s_num) {
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日訂單資料
    $row = NULL;
    $sql = "select {$tbl_daily_shipment}.*
            from {$tbl_daily_shipment}
            where {$tbl_daily_shipment}.sec_s_num = {$sec_s_num}
                  and {$tbl_daily_shipment}.dys01 = '{$produce_date}'
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: save_shipment_data()
  //  函數功能: 新增外送資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-21
  // **************************************************************************
  public function save_shipment_data($data) {
    $rtn_msg = 'ok';
    $v = $this->input->post();
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日訂單資料
    // 刪除
    $this->db->where("dys01" , $v['produce_date']);
    $this->db->where("dys09" , $v['produce_time']);
    if(!$this->db->delete($tbl_daily_shipment)) {
      return false;
    }
    else {
      if(NULL != $data) {
        foreach ($data as $k => $v) {
          foreach ($v as $ks => $vs) {
            if(in_array($ks , $this->aes_fd1)) { // 加密欄位
              $this->db->set($ks, "AES_ENCRYPT('{$vs}','{$this->db_crypt_key2}')", FALSE);
              unset($data[$k][$ks]);
            }
          } 
          if(!$this->db->insert($tbl_daily_shipment, $data[$k])) {
            return false;
          }
        } 
      }
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: update_daily_shipment()
  //  函數功能: 如果異動審核有今天，及時更新代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-12-22
  // **************************************************************************
  public function update_daily_shipment($mil_s_row, $mil_mp_row) {
    $upd_data['e_empno'] = $_SESSION['acc_s_num'];
    $upd_data['e_date']  = date('Y-m-d H:i:s');
    $upd_data['dys05']  = $mil_mp_row->mil_mp01;
    $upd_data['dys10']  = $mil_s_row->mil_s01;
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日訂單資料
    $this->db->where('sec_s_num', $mil_s_row->sec_s_num);
    if(!$this->db->update($tbl_daily_shipment, $upd_data)) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: save_meal_replacement_data()
  //  函數功能: 先獲得這周代餐資料，寫進歷史檔裡面，在清空這周代餐，建立下禮拜的代餐
  //  程式設計: kiwi
  //  設計日期: 2021-06-09
  // **************************************************************************
  public function save_meal_replacement_data($mp_data) {
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    $tbl_meal_replacement_log = $this->zi_init->chk_tbl_no_lang('meal_replacement_log'); // 案主代餐紀錄表
    
    $i = 0;
    $rtn_msg = "ok";
    $mp_type = NULL;
    $mp_hist_data = NULL;

    $meal_replacement_row = $this->get_meal_replacement($_GET['type']); // 熟代，非熟代資料
    if(NULL != $meal_replacement_row) {
      foreach ($meal_replacement_row as $k => $v) {
        $mp_hist_data[$i] = $v;
        $i++;
      } 
      if(NULL != $mp_hist_data) { // 寫進歷史檔裡面
        if(!$this->db->insert_batch("{$tbl_meal_replacement_log}", $mp_hist_data)) {
          $rtn_msg = $this->lang->line('add_ng');
          echo $rtn_msg;
          return;
        }
      }
    }

    // 刪除代餐資料
    switch($_GET['type']) {
      case 1: // 熟代-午餐
        $mp_type = array(3, 4, 9);
        $this->db->where("{$tbl_meal_replacement}.ct_mp07 =", 1);
        break;
      case 4: // 熟代-中晚/晚餐
        $mp_type = array(3, 4, 9);
        $this->db->where("{$tbl_meal_replacement}.ct_mp07 !=", 1);
        break;
      case 2: // 非熟代-中晚/晚餐
        $mp_type = array(1, 2, 5, 6, 7, 8);
        $this->db->where("{$tbl_meal_replacement}.ct_mp07 !=", 1);
        break;
      case 3: // 非熟代-午餐
        $mp_type = array(1, 2, 5, 6, 7, 8);
        $this->db->where("{$tbl_meal_replacement}.ct_mp07", 1);
        break;
    }

    $this->db->where_in("{$tbl_meal_replacement}.ct_mp02", $mp_type);
    $this->db->delete("{$tbl_meal_replacement}");
    
    // 寫入代餐資料
    if(NULL != $mp_data) {
      foreach ($mp_data as $k => $v) {
        foreach ($v as $ks => $vs) {
          if(in_array($ks , $this->aes_fd1)) { // 加密欄位
            $this->db->set($ks, "AES_ENCRYPT('{$vs}','{$this->db_crypt_key2}')", FALSE);
            unset($mp_data[$k][$ks]);
          }
        } 
        if(!$this->db->insert($tbl_meal_replacement, $mp_data[$k])) {
          $rtn_msg = $this->lang->line('add_ng');
          echo $rtn_msg;
          return;
        }
      } 
    }

    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: get_one_meal_replacement()
  //  函數功能: 判斷代餐資料是否再當周，因為牽扯到問卷
  //  程式設計: kiwi
  //  設計日期: 2021-12-26
  // **************************************************************************
  public function get_one_meal_replacement($sec_s_num, $sat_date) {
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    $row = NULL;
    $sql = "select {$tbl_meal_replacement}.*
            from {$tbl_meal_replacement}
            where {$tbl_meal_replacement}.sec_s_num  = {$sec_s_num}
                  and {$tbl_meal_replacement}.ct_mp08 = '{$sat_date}'
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_meal_replacement()
  //  函數功能: 獲得這周代餐資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-09
  // **************************************************************************
  public function get_meal_replacement($type=null) {
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    
    $where = '';
    if(NULL != $type) {
      switch($_GET['type']) {
        case 1: // 熟代-午餐
          $where .= "where {$tbl_meal_replacement}.ct_mp02 in (3, 4, 9)
                           and {$tbl_meal_replacement}.ct_mp07 = 1
                    ";
          break;
        case 4: // 熟代-中晚/晚餐
          $where .= "where {$tbl_meal_replacement}.ct_mp02 in (3, 4, 9)
                            and {$tbl_meal_replacement}.ct_mp07 != 1
                    ";
          break;
        case 2: // 非熟代-中晚/晚餐
          $where .= "where {$tbl_meal_replacement}.ct_mp02 in (1, 2, 5, 6, 7, 8)
                           and {$tbl_meal_replacement}.ct_mp07 != 1
                    ";
          break;
        case 3: // 非熟代-午餐
          $where .= "where {$tbl_meal_replacement}.ct_mp02 in (1, 2, 5, 6, 7, 8)
                            and {$tbl_meal_replacement}.ct_mp07 = 1
                    ";
          break;
      }
    }

    $data = NULL;
    $sql = "select {$tbl_meal_replacement}.*
            from {$tbl_meal_replacement}
            {$where}
           ";
           
    $rs = $this->db->query($sql);
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
  //  函數名稱: update_meal_replacement()
  //  函數功能: 更新代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-09
  // **************************************************************************
  public function update_meal_replacement($type , $data) {
    switch ($type) {   
      case "s":       
        $upd_data['ct_mp01'] = $data->mil_s01;  
      break;
      case "mp":       
        $upd_data['ct_mp02'] = $data->mil_mp01_type;  
        $upd_data['ct_mp03'] = $data->mil_mp01;  
      break;  
    }
    $upd_data['e_empno'] = $_SESSION['acc_s_num'];
    $upd_data['e_date']  = date('Y-m-d H:i:s');
    $upd_data['ct_mp06']  = "Y";
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    $this->db->where('sec_s_num', $data->sec_s_num);
    if(!$this->db->update($tbl_meal_replacement, $upd_data)) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: update_mp_by_ct_mp04()
  //  函數功能: 更新代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-09
  // **************************************************************************
  public function update_mp_by_ct_mp04($dp_s_num , $sec_s_num , $ct_mp04 , $memo=NULL) {
    $upd_data['e_empno'] = $dp_s_num;
    $upd_data['e_date']  = date('Y-m-d H:i:s');
    $upd_data['ct_mp04']  =$ct_mp04;
    $upd_data['ct_mp04_time']  = date('Y-m-d H:i:s');
    $upd_data['ct_mp05']  = $memo;
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    $this->db->where('sec_s_num', $sec_s_num);
    if(!$this->db->update($tbl_meal_replacement, $upd_data)) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: chk_meal_replacemet()
  //  函數功能: 取代這周代餐紀錄表
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function chk_meal_replacemet($ct_s_num , $sec_s_num) {
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    
    $row = NULL;
    $sql = "select {$tbl_meal_replacement}.*
                 from {$tbl_meal_replacement}
                 where {$tbl_meal_replacement}.sec_s_num = {$sec_s_num}
                       and {$tbl_meal_replacement}.ct_s_num = {$ct_s_num}
                 limit 0, 1;
                ";
              
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_meal_replacement_data()
  //  函數功能: 獲得代餐資料統計資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-09
  // **************************************************************************
  public function get_meal_replacement_data() {
    $where = '';
    $data = NULL;
    $v = $this->input->post();
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_meal_replacement = $this->zi_init->chk_tbl_no_lang('meal_replacement'); // 案主代餐紀錄表
    if(isset($v['reh_s_num'])) {
      if('all' != $v['reh_s_num']) {
        $where = "and {$tbl_meal_replacement}.reh_s_num = {$v['reh_s_num']}";
      }
    }
    $sql = "select {$tbl_meal_replacement}.*,
                   {$tbl_route_h}.reh05,
                   case {$tbl_meal_replacement}.ct_mp01
                     when 'Y' then '出餐'
                     when 'N' then '停餐'
                   end as ct_mp01_str,
                   case {$tbl_meal_replacement}.ct_mp03
                     when 'Y' then '有代餐'
                     when 'N' then '無代餐'
                   end as ct_mp03_str,
                   concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) as ct_name
            from {$tbl_meal_replacement}
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_meal_replacement}.reh_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_replacement}.ct_s_num
            where {$tbl_meal_replacement}.ct_mp01 = 'Y'
                  and {$tbl_meal_replacement}.ct_mp03 = 'Y'
                  and {$tbl_meal_replacement}.ct_mp02 is not null
                  and {$tbl_meal_replacement}.reh01 is not null
                  {$where}
            order by {$tbl_meal_replacement}.reh01 asc , {$tbl_meal_replacement}.reb01 asc 
           ";
    $rs = $this->db->query($sql);
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
  //  函數名稱: get_meal_data()
  //  函數功能: 取得訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_meal_data($que_date, $type=NULL, $reh_s_num=NULL) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_daily_production = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日製餐資料
    
    $where = '';
    if(!empty($type)) {
      $where = " and {$tbl_daily_production}.dyp09 = '{$type}'";
    }
    
    if(!empty($reh_s_num)) {
      $where = " and {$tbl_daily_production}.reh_s_num = '{$reh_s_num}'";
    }

    $data = NULL;
    $sql = "select {$tbl_daily_production}.*,
                   date_format({$tbl_daily_production}.dyp01, '%m/%d') as dyp01,
                   {$tbl_route_h}.reh01,
                   case {$tbl_daily_production}.dyp02
                     when '1' then '早上'
                     when '2' then '下午'
                   end as dyp02_str,
                   case {$tbl_daily_production}.dyp06
                     when '0' then '無'
                     when '1' then '有'
                   end as dyp06_str
                  {$this->_aes_fd('daily_production')}
            from {$tbl_daily_production}
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_daily_production}.reh_s_num
            where ({$tbl_daily_production}.d_date is null
                   and {$tbl_daily_production}.dyp01 = '{$que_date}'
                   and {$tbl_daily_production}.dyp10 = 'Y'
                   {$where}
                  )
            order by {$tbl_route_h}.reh01 asc,
                     {$tbl_daily_production}.dyp08 asc
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
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_send_data()
  //  函數功能: 取得訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function get_send_data() {
    $v = $this->input->post();
    $where = '';
    $type = $v["type"];
    $dlvry_date = $v["dlvry_date"];
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 每日配送單資料
    if(!empty($v['reh_s_num'])) {
      $where .= "and {$tbl_daily_shipment}.reh_s_num = {$v['reh_s_num']}";
    }
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.*,
                   {$tbl_route_h}.reh02,
                   {$tbl_delivery_person}.dp01,
                   {$tbl_delivery_person}.dp02,
                   case {$tbl_daily_shipment}.dys09
                     when '1' then '早上'
                     when '2' then '下午' 
                   end as dys09_str,
                   case {$tbl_daily_shipment}.dys05
                     when 'N' then '無'
                     when 'Y' then '有'
                   end as dys05_str,
                   case {$tbl_daily_shipment}.dys06
                     when '0' then '無'
                     when '1' then '有'
                   end as dys06_str,
                   case {$tbl_daily_shipment}.dys10
                     when 'N' then '無'
                     when 'Y' then '有'
                   end as dys10_str,
                   case {$tbl_daily_shipment}.dys22
                     when '1' then '有網路'
                     when '2' then '無網路'
                     when '3' then '補登'
                   end as dys22_str,
                   case {$tbl_daily_shipment}.dys24
                     when '1' then '有網路'
                     when '2' then '無網路'
                     when '3' then '補登'
                   end as dys24_str
                   {$this->_aes_fd('daily_shipment')}
                   {$this->_aes_fd('delivery_person')}
            from {$tbl_daily_shipment}
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_daily_shipment}.reh_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_daily_shipment}.dp_s_num
            where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dys01 = '{$dlvry_date}'
                  and {$tbl_daily_shipment}.dys09 = ?
                  {$where}
            order by {$tbl_daily_shipment}.reh_s_num desc , {$tbl_daily_shipment}.dys08 asc
          ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($type));
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
  //  函數名稱: get_all_send_data()
  //  函數功能: WEB 取得訂單
  //  程式設計: kiwi
  //  設計日期: 2022-02-14
  // **************************************************************************
  public function get_all_send_data($date) {
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $data = NULL;
    $sql = "select {$tbl_daily_shipment}.s_num,
                   {$tbl_daily_shipment}.dp_s_num,
                   {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.sec_s_num,
                   {$tbl_daily_shipment}.reh_s_num,
                   {$tbl_daily_shipment}.ct_name,
                   {$tbl_daily_shipment}.dys01,
                   {$tbl_daily_shipment}.dys02,
                   {$tbl_daily_shipment}.dys03,
                   {$tbl_daily_shipment}.dys04,
                   {$tbl_daily_shipment}.dys05,
                   {$tbl_daily_shipment}.dys05_type,
                   {$tbl_daily_shipment}.dys06,
                   {$tbl_daily_shipment}.dys07 as reh_name,
                   {$tbl_daily_shipment}.dys08 as ct_order,
                   {$tbl_daily_shipment}.dys09,
                   {$tbl_daily_shipment}.dys10,
                   {$tbl_daily_shipment}.dys11,
                   {$tbl_daily_shipment}.dys12,
                   {$tbl_daily_shipment}.dys13
                   {$this->_aes_fd('daily_shipment')}
            from {$tbl_daily_shipment}
            where {$tbl_daily_shipment}.d_date is null
                  and {$tbl_daily_shipment}.dys01 = '{$date}'
            order by {$tbl_daily_shipment}.reh_s_num desc , {$tbl_daily_shipment}.dys08 asc
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
  //  函數名稱: get_upd_data_by_date()
  //  函數功能: 補課獲取資料用
  //  程式設計: kiwi
  //  設計日期: 2021-09-10
  // **************************************************************************
  public function get_upd_data_by_date($fd_tbl) {
    $get_data = $this->input->get();

    switch ($fd_tbl) {   
      case "meal":     
        $fd_tbl = 'dyp';
        $tbl = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日餐點資料
      break;  
      case "send":     
        $fd_tbl = 'dys';
        $tbl = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
      break;  
    }
    
    $data = NULL;    
    $sql = "select {$tbl}.*
            from {$tbl}
            where {$tbl}.d_date is null
                  and {$tbl}.{$fd_tbl}01 = '{$get_data['produce_date']}'
                  and {$tbl}.{$fd_tbl}10 = 'Y'
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
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
  //  函數名稱: upd_meal_instruction_p()
  //  函數功能: 補課固定暫停更新
  //  程式設計: kiwi
  //  設計日期: 2021-09-10
  // **************************************************************************
  public function upd_meal_instruction_p($fd_tbl , $upd_data) {
    $upd_tbl = '';
    $rtn_msg = 'ok';
    switch ($fd_tbl) {   
      case "meal":     
        $upd_tbl = $this->zi_init->chk_tbl_no_lang('daily_production'); // 每日配送單資料
      break;  
      case "send":     
        $upd_tbl = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
      break;  
    }
    if(!$this->db->update_batch($upd_tbl , $upd_data , 's_num')) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_punch()
  //  函數功能: 儲存補登打卡資訊
  //  程式設計: kiwi
  //  設計日期: 2022-05-23
  // **************************************************************************
  public function save_punch() {
    $rtn_msg = 'ok';
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $v = $this->input->post();
    $punch_data = NULL;
    if(isset($v['punch'])) {
      $punch_data = $v['punch'];
    }
    
    $mqtt_data = NULL;
    if(NULL != $punch_data) {
      foreach ($punch_data as $k => $v) {
        
        if(!isset($v['dys22']) or !isset($v['dys24']) or !isset($v['dys25'])) {
          unset($punch_data[$k]);
          continue;
        }

        if('' == $v['dys22'] or '' == $v['dys24'] or '' == $v['dys25']) {
          unset($punch_data[$k]);
          continue;
        }

        /* MQTT 要傳的資料 */
        // dys21(簽到時間), dys22(簽到方式), dys23(簽退時間), dys24(簽退方式), dys25(備註)
        // mqtt string: lat, lon, reh_s_num, en(dp_s_num), inorout(簽到或簽退), wifi(有網路無網路), ct_s_num, sec_s_num, mlo_s_num, 
        //              phl01(打卡時間), phl50(打卡方式(1=有網路，2=無網路, 3=補登)), phl02打卡型態(1=簽到;2=簽退),
        //              phl05(打卡是否有效), phl99(備註), dys09(早上下午), source
        $phl01 = date("Y-m-d H:i:s");
        $en_dp_s_num = $this->zi_my_func->token_encrypt($v['dp_s_num']); 
        $mqtt_data[] = "0,0,{$v['reh_s_num']},{$en_dp_s_num},1,3,{$v['ct_s_num']},{$v['sec_s_num']},{$v['mlo_s_num']},{$phl01},3,1,1,{$v['dys25']},{$v['type']},PHP"; // 簽到記錄
        $mqtt_data[] = "0,0,{$v['reh_s_num']},{$en_dp_s_num},2,3,{$v['ct_s_num']},{$v['sec_s_num']},{$v['mlo_s_num']},{$phl01},3,2,1,{$v['dys25']},{$v['type']},PHP"; // 簽退紀錄
        /* MQTT 要傳的資料 */

        $punch_data[$k]['e_date'] = date("Y-m-d H:i:s");
        $punch_data[$k]['e_empno'] = $_SESSION['acc_s_num'];
        unset($punch_data[$k]['type']);
      }
      if(NULL != $punch_data) {
        if(!$this->db->update_batch($tbl_daily_shipment, $punch_data, 's_num')) {
          $rtn_msg = $this->lang->line('upd_ng');
        }
      }
      $this->zi_my_func->mqtt_data($mqtt_data); 
    }
    echo $rtn_msg;
    return;
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