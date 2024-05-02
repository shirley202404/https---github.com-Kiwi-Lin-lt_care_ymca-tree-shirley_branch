<?php
class Meal_order_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public $aes_fd = array('ct01','ct02','ct03','ct05','ct06_telephone',
                         'ct06_homephone','ct08','ct09','ct10','ct11',
                         'ct12','ct13','ct14','ct15'); // 加密欄位
  public $ct03_aes_fd = 'ct03';
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_meal_order}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_meal_order}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
            from {$tbl_meal_order}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_meal_order}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_meal_order}.e_empno
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_meal_order}.s_num = ?
            order by {$tbl_meal_order}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_meal_order}.*
            from {$tbl_meal_order}
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_meal_order}.fd_name = ?
            order by {$tbl_meal_order}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($fd_name));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
    }
    return $row;
  }

  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function get_all() {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*
            from {$tbl_meal_order}
            where {$tbl_meal_order}.d_date is null
            order by {$tbl_meal_order}.s_num desc
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
  //  函數名稱: get_all_is_available()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-28
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*
            from {$tbl_meal_order}
            where {$tbl_meal_order}.d_date is null
            order by {$tbl_meal_order}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->mlo01][$fd_name] = $row->$fd_name;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_only_date()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-28
  // **************************************************************************
  public function get_all_only_date() {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $data = NULL;
    $sql = "select {$tbl_meal_order}.mlo01
            from {$tbl_meal_order}
            where {$tbl_meal_order}.d_date is null
            group by {$tbl_meal_order}.mlo01
            order by {$tbl_meal_order}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->mlo01][$fd_name] = $row->$fd_name;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_today()
  //  函數功能: 取得今日所有資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function get_all_today() {
    $today = date("Y-m-d");
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $row = NULL;
    $sql = "select count(*) as cnt
            from {$tbl_meal_order}
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_meal_order}.mlo01 = '{$today}'
            order by {$tbl_meal_order}.s_num desc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
    }
    return $row;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_by_date()
  //  函數功能: 查詢生產日是否有資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-28
  // **************************************************************************
  public function get_all_by_date() {
    $v = $this->input->post();
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $row = NULL;
    $sql = "select {$tbl_meal_order}.* , count(*) as cnt
            from {$tbl_meal_order}
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_meal_order}.mlo01 = '{$v['produce_date']}'
            order by {$tbl_meal_order}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
    }
    return $row;
  }
  
  // **************************************************************************
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $where = " {$tbl_meal_order}.d_date is null ";
    $order = " {$tbl_meal_order}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_meal_order}.clnt_s_num like '%{$que_str}%' /* tw_clients.s_num */
                       or {$tbl_meal_order}.meal_s_num like '%{$que_str}%' /* tw_meal.s_num */
                       or {$tbl_meal_order}.srvc_s_num like '%{$que_str}%' /* tw_service_case.s_num */
                       or {$tbl_meal_order}.MealDeliveryDate like '%{$que_str}%' /* 送餐日期 */
                       or {$tbl_meal_order}.MealSpecialInstructions like '%{$que_str}%' /* 出餐調整(少油、易消化) */
                       or {$tbl_meal_order}.MealSpecialInstructionType like '%{$que_str}%' /* 出餐調整(少油、易消化) */
                       or {$tbl_meal_order}.MealOrderStatus like '%{$que_str}%' /* 當日是否出餐(1=是/0=否) */
                      )
                ";
    }
    

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_meal_order}.s_num
                from {$tbl_meal_order}
                where $where
                group by {$tbl_meal_order}.s_num
                order by {$tbl_meal_order}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_meal_order}.*,
            case {$tbl_meal_order}.MealOrderStatus
              when null then '無'                  
              when '0' then '不出餐'                 
              when '1' then '出餐'                  
            end as MealOrderStatus_str            
            from {$tbl_meal_order}
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
        }
      }
    }
    return(array($data,$row_cnt));
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    if(!$this->db->insert($tbl_meal_order, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_meal_order, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_meal_order, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-30
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_meal_order, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  
  // **************************************************************************
  //  函數名稱: get_meal_order()
  //  函數功能: 取得該服務案的訂單資料
  //  程式設計: kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function get_meal_order($sec_s_num) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $sec_s_num = $this->db->escape_like_str($sec_s_num);
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*,
                   {$tbl_meal}.ml01,
                   case {$tbl_meal_order}.mlo02
                     when '1' then '早上'
                     when '2' then '下午'
                   end as mlo02_str,
                   case {$tbl_meal_order}.mlo05
                     when 'Y' then '有代餐'
                     when 'N' then '無代餐'
                   end as mlo05_str,
                   case {$tbl_meal_order}.mlo99
                     when 'Y' then '出餐'
                     when 'N' then '停餐'
                   end as mlo99_str
            from {$tbl_meal_order}   
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_order}.ml_s_num
            where {$tbl_meal}.d_date is null
            and {$tbl_meal_order}.d_date is null
            and {$tbl_meal_order}.sec_s_num = ?
            order by {$tbl_meal_order}.mlo01 desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sec_s_num));
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
  //  函數名稱: get_by_date()
  //  函數功能: 取得某時間的所有訂單
  //  程式設計: kiwi
  //  設計日期: 2021/07/25
  // **************************************************************************
  public function get_by_date($date = NULL) {
    $produce_date = NULL;
    if(NULL == $date) {
      $produce_date = $this->input->post('produce_date');
    }
    else {
      $produce_date = $date;
    }
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*
                  from {$tbl_meal_order}   
                  left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num 
                  where {$tbl_meal_order}.d_date is null
                        and {$tbl_meal_order}.mlo01 = '{$produce_date}'
                        and {$tbl_clients}.is_available = 1
                  group by {$tbl_meal_order}.sec_s_num
                  order by {$tbl_meal_order}.mlo01 desc
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
  //  函數名稱: get_order_by_date()
  //  函數功能: 根據日期獲取訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_order_by_date($date = NULL) {
    $v = $this->input->post();  
    $mlo02 = $v['type'];
    $produce_date = $v['produce_date'];
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_meal}.ml01,
                   case {$tbl_service_case}.sec04
                     when '1' then '午餐'
                     when '2' then '中晚餐'
                     when '3' then '晚餐'
                   end as sec04_str,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '1'
                     when '3' then '2'
                   end as reh05_type
                   {$this->_aes_fd()}
            from {$tbl_meal_order} 
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_order}.ml_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num 
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_meal_order}.mlo01 = '{$produce_date}' 
                  and {$tbl_meal_order}.mlo02 = {$mlo02}
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
  //  函數名稱: get_order_by_date_no_type()
  //  函數功能: 根據日期獲取訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_order_by_date_no_type($date = NULL) {
    $produce_date = $date;
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*
            from {$tbl_meal_order} 
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_order}.ml_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num 
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_meal_order}.mlo01 = '{$produce_date}' 
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
  //  函數名稱: get_daily_order()
  //  函數功能: 獲取每日訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_daily_order() {
    $v = $this->input->post();    
    $mlo02 = $v['produce_time'];
    $produce_date = $v['produce_date'];
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料-檔身
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路徑資料-檔頭
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*,
                   {$tbl_delivery_person}.s_num as dp_s_num,
                   {$tbl_route_h}.s_num as reh_s_num,
                   {$tbl_route_h}.reh01,
                   {$tbl_route_h}.reh03,
                   {$tbl_route_b}.reb01,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_meal}.ml01,
                   case {$tbl_service_case}.sec04
                     when '1' then '午餐'
                     when '2' then '中晚餐'
                     when '3' then '晚餐'
                   end as sec04_str
                  {$this->_aes_fd()}
            from {$tbl_meal_order} 
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_order}.ml_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num 
            left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_meal_order}.ct_s_num 
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_route_b}.reh_s_num 
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_route_h}.dp_s_num 
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_route_h}.d_date is null
                  and {$tbl_meal_order}.mlo01 = '{$produce_date}' 
                  and {$tbl_meal_order}.mlo02 = {$mlo02}
                  and {$tbl_route_h}.reh05 = {$mlo02}
            order by {$tbl_route_h}.s_num desc , {$tbl_route_b}.reb01 asc
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
  //  函數名稱: add_meal_order()
  //  函數功能: 建立訂單
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function add_meal_order($data) {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 服務資料
    if(!$this->db->insert_batch($tbl_meal_order, $data)) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: upd_meal_order()
  //  函數功能: 更新訂單
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function upd_meal_order($meal_order_data) {
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    if(!$this->db->update_batch($tbl_meal_order , $meal_order_data , 's_num')) {
      return false;
    }
    return true;
  }

  // **************************************************************************
  //  函數名稱: upd_meal_order_by_sec_s_num()
  //  函數功能: 如果異動審核有今天，及時更新代餐異動資料
  //  程式設計: kiwi
  //  設計日期: 2021-12-22
  // **************************************************************************
  public function upd_meal_order_by_sec_s_num($mil_s_row, $mil_mp_row) {
    $upd_data['e_empno'] = $_SESSION['acc_s_num'];
    $upd_data['e_date']  = date('Y-m-d H:i:s');
    $upd_data['mlo05']  = $mil_mp_row->mil_mp01;
    $upd_data['mlo99']  = $mil_s_row->mil_s01;
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $this->db->where('mlo01', $mil_s_row->mil_s02);
    $this->db->where('sec_s_num', $mil_s_row->sec_s_num);
    if(!$this->db->update($tbl_meal_order, $upd_data)) {
      return false;
    }
    return true;
  }

  // **************************************************************************
  //  函數名稱: save_meal_order_hist()
  //  函數功能: 更新訂單
  //  程式設計: Kiwi
  //  設計日期: 2021/01/31
  // **************************************************************************
  public function save_meal_order_hist($meal_order_data) {
    $tbl_meal_order_hist = $this->zi_init->chk_tbl_no_lang('meal_order_hist'); // 訂單歷史資料
    foreach ($meal_order_data as $k => $v) {
      $meal_order_data[$k]['backup_date'] = date('Y-m-d H:i:s');
      $meal_order_data[$k]['backup_empno'] = $_SESSION['acc_s_num'];
    } 
    if(!$this->db->insert_batch($tbl_meal_order_hist , $meal_order_data)) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: get_data_by_dys01()
  //  函數功能: 獲取送餐資料
  //  程式設計: kiwi
  //  設計日期: 2021-04-07
  // **************************************************************************
  public function get_data_by_dys01($dys01) {
    $data = NULL;
    $where = '';
    $v = $this->input->post();
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料

    $meal_order_type_row = $this->meal_order_date_type_model->get_meal_order_date_type($dys01);
    if(NULL != $meal_order_type_row) { // 判斷產生日期的類型
      switch ($meal_order_type_row->mlo_dt02) {   
        case 1: // 送餐-便當
        case 3: // 補上班
        case 4: // 颱風假
          $where .= "";
          break;       
        case 2: // 送餐-代餐
          $where .= "and {$tbl_meal_order}.mlo05 = 'Y'";
          break;  
      }
    }

    // 判斷產生報表的時間
    switch ($v['rpt_dys09']) {   
      case 1:
        $mlo02 = 1;   
        break;  
      case 3:
        $mlo02 = 2;  
        break;  
    }
    
    // and {$tbl_route_b}.reh_s_num not in('3','48','49','50') /* 測試路線 */
    $sql = "select {$tbl_meal_order}.s_num,
                   {$tbl_meal_order}.sec_s_num,
                   {$tbl_meal_order}.ct_s_num,
                   {$tbl_meal_order}.mlo01,
                   AES_DECRYPT({$tbl_clients}.ct03,'{$this->db_crypt_key2}') as ct03,
                   AES_DECRYPT({$tbl_delivery_person}.dp03,'{$this->db_crypt_key2}') as dp03
            from {$tbl_meal_order}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_route_b}.reh_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num
            left join {$tbl_daily_shipment} on {$tbl_daily_shipment}.mlo_s_num = {$tbl_meal_order}.s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_daily_shipment}.dp_s_num
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_service_case}.sec01 = 1
                  and {$tbl_meal_order}.mlo02 = '{$mlo02}'
                  and {$tbl_meal_order}.mlo99 = 'Y'
                  and {$tbl_meal_order}.mlo01 >= '{$dys01} 00:00:00'
                  and {$tbl_meal_order}.mlo01 <= '{$dys01} 23:59:59'
                  and {$tbl_service_case}.sec04 = {$v['rpt_dys09']}
                  and {$tbl_route_h}.reh05 = {$mlo02}
                  {$where}
            order by {$tbl_clients}.ct03 asc , {$tbl_meal_order}.mlo01 asc
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
  //  函數名稱: get_service_data_by_dys01()
  //  函數功能: 獲取清冊資料
  //  程式設計: kiwi
  //  設計日期: 2021-11-28
  // **************************************************************************
  public function get_service_data_by_dys01($dys01, $district=NULL) {
    $data = NULL;
    $where = '';
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    
    $meal_order_type_row = $this->meal_order_date_type_model->get_meal_order_date_type($dys01);
    if(NULL != $meal_order_type_row) { // 判斷產生日期的類型
      switch ($meal_order_type_row->mlo_dt02) {   
        case 1: // 送餐-便當
        case 3: // 補上班
        case 4: // 颱風假
          $where .= "";
          break;       
        case 2: // 送餐-代餐
          $where .= "and {$tbl_meal_order}.mlo05 = 'Y'";
          break;  
      }
    }

    if(NULL != $district) {
      $where .= " and AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}') = '{$district}'";
    }

    // left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_daily_shipment}.dp_s_num => 每天送餐員，改為先抓路線
    $sql = "select {$tbl_meal_order}.s_num,
                   {$tbl_meal_order}.sec_s_num,
                   {$tbl_meal_order}.ct_s_num,
                   {$tbl_meal_order}.vp_s_num,
                   {$tbl_meal_order}.reh_s_num,
                   {$tbl_meal_order}.mlo01,
                   {$tbl_meal_order}.mlo02,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04,
                   {$tbl_service_case}.sec09,
                   case {$tbl_service_case}.sec04
                     when '1' then '1'
                     when '2' then '2'
                     when '3' then '2'
                   end as sec04_time,
                   AES_DECRYPT({$tbl_route_h}.dp_name, '{$this->db_crypt_key2}') as dp_name,
                   {$tbl_route_h}.reh01,
                   case {$tbl_clients}.ct04
                     when 'M' then '男'
                     when 'Y' then '女'
                   end as ct04_str,
                   {$tbl_clients}.ct36
                   {$this->_aes_fd()}
            from {$tbl_meal_order}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num
            left join {$tbl_daily_shipment} on {$tbl_daily_shipment}.s_num = {$tbl_meal_order}.s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_meal_order}.reh_s_num
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_service_case}.sec01 = 1
                  and {$tbl_meal_order}.mlo99 = 'Y'
                  and {$tbl_meal_order}.mlo01 >= '{$dys01} 00:00:00'
                  and {$tbl_meal_order}.mlo01 <= '{$dys01} 23:59:59'
                  {$where}
            order by {$tbl_meal_order}.mlo01 asc, {$tbl_route_h}.reh01 asc
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
  //  函數名稱: get_data_by_subsidy()
  //  函數功能: 獲取自費戶繳費名冊資料
  //  程式設計: kiwi
  //  設計日期: 2021-10-19
  // **************************************************************************
  public function get_data_by_subsidy($sec_s_num , $first_date , $last_date) {
    $where = '';
    $data = NULL;
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
   
    // and {$tbl_route_b}.reh_s_num not in('3','48','49','50') /* 測試路線 */
    $sql = "select {$tbl_meal_order}.*
            from {$tbl_meal_order}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_clients}.is_available = 1
                  and {$tbl_meal_order}.mlo99 = 'Y'
                  and {$tbl_meal_order}.sec_s_num = {$sec_s_num}
                  and {$tbl_meal_order}.mlo01 between '{$first_date}' and '{$last_date}'
            order by {$tbl_meal_order}.s_num asc
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
  //  函數名稱: get_meal_num_by_date()
  //  函數功能: 根據日期獲取訂單
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_meal_num_by_date($date = NULL) {
    $produce_date = $date;
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04
            from {$tbl_meal_order} 
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_order}.ml_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num 
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_meal_order}.mlo01 = '{$produce_date}' 
                  and {$tbl_meal_order}.mlo99 = 'Y' 
                  and {$tbl_meal_order}.reh_s_num not in('0','3','48','49','50') /* 測試路線資料 */

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
  //  函數名稱: get_data_by_service_count()
  //  函數功能: 獲取每區人數人次統計資料
  //  程式設計: kiwi
  //  設計日期: 2021-12-16
  // **************************************************************************
  public function get_data_by_service_count($is_newyear) {
    $v = $this->input->post();
    $where = '';
    $data = NULL;
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
    $date_type_row = $this->meal_order_date_type_model->get_date_type_by_mlo_dt02($v['download_year'] , 6);
    if('' == $is_newyear and NULL == $date_type_row) {
      return $data;
    }
    if(NULL != $date_type_row) { // 去除掉過年的日期
      $newyear_date_arr = array_column($date_type_row, 'mlo_dt01');
      $newyear_date_str = "'".implode("','", $newyear_date_arr)."'"; // 轉成資料庫 IN 可用的形式
      $where .= " and {$tbl_meal_order}.mlo01 {$is_newyear} in({$newyear_date_str})";
    }
    if(1 == $v['download_type']) {
      $where .= " and {$tbl_service_case}.sec01 = 1";
    }
    else {
      $where = " and {$tbl_service_case}.sec01 not in (1, 3, 4, 7)"; // 社會局不包括長照案、自費戶、邊緣戶、志工
    }
    
    $sql = "select count(*) as each_month_cnt,
                   {$tbl_meal_order}.sec_s_num, 
                   year({$tbl_meal_order}.mlo01),
                   month({$tbl_meal_order}.mlo01) as month,
                   AES_DECRYPT({$tbl_clients}.ct12,'{$this->db_crypt_key2}') as ct12,
                   {$tbl_clients}.ct04,
                   {$tbl_clients}.s_num as ct_s_num,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04
            from tw_meal_order
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = tw_meal_order.sec_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = tw_meal_order.ct_s_num
            where {$tbl_meal_order}.mlo99 = 'Y'
                  and {$tbl_clients}.d_date is null
                  and year({$tbl_meal_order}.mlo01) = '{$v['download_year']}'
                  {$where}
            group by sec_s_num, year({$tbl_meal_order}.mlo01), month({$tbl_meal_order}.mlo01)
            order by sec_s_num, year({$tbl_meal_order}.mlo01), month({$tbl_meal_order}.mlo01)
           ";
    
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          if(!isset($data[$row->sec_s_num])) {
            $data[$row->sec_s_num]['sec_s_num'] = $row->sec_s_num;
            $data[$row->sec_s_num]['sec01'] = $row->sec01;
            $data[$row->sec_s_num]['sec04'] = $row->sec04;
            $data[$row->sec_s_num]['ct_s_num'] = $row->ct_s_num;
            $data[$row->sec_s_num]['ct04'] = $row->ct04;
            $data[$row->sec_s_num]['ct12'] = $row->ct12;
            $data[$row->sec_s_num]['each_month'] = NULL;
          }
          if(!isset($data[$row->sec_s_num]['each_month'][$row->month])) {
            $data[$row->sec_s_num]['each_month'][$row->month] = $row->each_month_cnt;
          }
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_stats_data_by_sec_s_num()
  //  函數功能: 獲取服務案出餐及停餐統計數據
  //  程式設計: kiwi
  //  設計日期: 2021-12-20
  // **************************************************************************
  public function get_stats_data_by_sec_s_num($sec_s_num, $que_start_month=NULL, $que_end_month=NULL) {
    $where = '';
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
    if(NULL != $que_start_month && NULL != $que_end_month) {
      $where .= "and DATE_FORMAT({$tbl_meal_order}.mlo01,'%Y-%m') between '{$que_start_month}' and '{$que_end_month}'";
    }
    $data = NULL;
    $sql = "select {$tbl_meal_order}.sec_s_num, 
                   year({$tbl_meal_order}.mlo01) as year,
                   month({$tbl_meal_order}.mlo01) as month,
                   AES_DECRYPT({$tbl_clients}.ct12,'{$this->db_crypt_key2}') as ct12,
                   {$tbl_service_case}.s_num, 
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04,
                   SUM(CASE WHEN {$tbl_meal_order}.mlo99 = 'Y' THEN 1 ELSE 0 END) AS mlo99_y,
                   SUM(CASE WHEN {$tbl_meal_order}.mlo99 = 'N' THEN 1 ELSE 0 END) AS mlo99_n
            from tw_meal_order
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = tw_meal_order.sec_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = tw_meal_order.ct_s_num
            where {$tbl_meal_order}.sec_s_num = {$sec_s_num}
                  and {$tbl_clients}.d_date is null
                  {$where}
            group by year({$tbl_meal_order}.mlo01), month({$tbl_meal_order}.mlo01)
            order by year({$tbl_meal_order}.mlo01) asc, month({$tbl_meal_order}.mlo01) asc
           ";
    
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->month][$fd_name] = $row->$fd_name;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: how_many_times_by_month()
  //  函數功能: 根據月份獲取每個案主該月有幾次訂單
  //  程式設計: loyenhsiang
  //  設計日期: 2023-11-20
  // **************************************************************************
  public function how_many_times_by_month($month = NULL, $case, $ct_s_num) {
    // 確定月份的起始日期和结束日期
    $start_date = date("Y-m-01", strtotime($month));
    $end_date = date("Y-m-t", strtotime($month));

    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    
    $data = NULL;
    $sql = "select {$tbl_clients}.s_num,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_meal}.ml01,
                   sum(case {$tbl_service_case}.sec04
                      when '1' then 1
                      when '2' then 1
                      when '3' then 1
                      else 0
                   end) as delivery_count
                   {$this->_aes_fd()}
            from {$tbl_meal_order} 
            left join {$tbl_meal} on {$tbl_meal}.s_num = {$tbl_meal_order}.ml_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num 
            where {$tbl_meal_order}.d_date is null
                  and {$tbl_service_case}.d_date is null
                  and {$tbl_meal_order}.mlo01 between '{$start_date}' and '{$end_date}'
                  and {$tbl_service_case}.sec01 = '{$case}'
                  and {$tbl_meal_order}.mlo99 = 'Y'
                  and {$tbl_clients}.s_num = {$ct_s_num}
            group by {$tbl_clients}.s_num
                  ";

    $rs = $this->db->query($sql, array($sql));
    if ($rs->num_rows() > 0) {
      foreach ($rs->result() as $row) {
        // 直接將資料添加到 $data 陣列中
        $data[] = array(
          's_num' => $row->s_num,
          'ct01' => $row->ct01,
          'ct02' => $row->ct02,
          'ml01' => $row->ml01,
          'delivery_count' => $row->delivery_count,
        );
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_order_by_month()
  //  函數功能: 根據日期獲取訂單
  //  程式設計: loyenhsiang
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_order_by_month($month = NULL, $case = NULL, $type = NULL, $gender = NULL) {
    $start_date = date("Y-m-01", strtotime($month));
    $end_date = date("Y-m-t", strtotime($month));

    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線資料-檔身

    $where = "";
    if ($type !== NULL){
      $where .= "AND {$tbl_service_case}.sec04 = {$type}";
    }

    if ($gender !== NULL){
      $where .= "AND tw_clients.ct04 = '{$gender}'";
    }

    $data = array();
    $sql = "select {$tbl_route_b}.reh_s_num,
                  COUNT(DISTINCT {$tbl_clients}.s_num) AS clients_count,
                  COUNT({$tbl_meal_order}.ct_s_num) AS total_orders
              FROM
                  {$tbl_route_b}
              LEFT JOIN
                  {$tbl_clients} ON {$tbl_clients}.s_num = {$tbl_route_b}.ct_s_num
              LEFT JOIN
                  {$tbl_meal_order} ON {$tbl_meal_order}.ct_s_num = {$tbl_clients}.s_num
                  AND {$tbl_meal_order}.d_date IS NULL
                  AND {$tbl_meal_order}.mlo01 BETWEEN '{$start_date}' AND '{$end_date}'
                  AND {$tbl_meal_order}.mlo99 = 'Y'
              left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num 
              WHERE
                  {$tbl_clients}.d_date IS NULL
                  AND {$tbl_route_b}.d_date IS NULL
                  AND {$tbl_service_case}.sec01 = {$case}
                  {$where}
              GROUP BY
                  {$tbl_route_b}.reh_s_num;
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql); // 修改這裡
    if ($rs->num_rows() > 0) {
        foreach ($rs->result() as $row) {
            // 將所有列讀取到 $data
            $data[] = array(
                'reh_s_num' => $row->reh_s_num,
                'clients_count' => $row->clients_count,
                'total_orders' => $row->total_orders
            );
        }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: how_many_times_for_route_by_dys01()
  //  函數功能: 獲取送餐資料
  //  程式設計: loyenhsiang
  //  設計日期: 2024-01-30
  // **************************************************************************
  public function how_many_times_for_route_by_dys01($dys01) {
    $data = array();
    $s_num = '';
    $where = '';
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂單資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 服務資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料

    $first_day = new DateTime($dys01 . '-01');
    $last_day = clone $first_day;
    $last_day->modify('last day of this month');

    $current_day = $first_day;

    while ($current_day <= $last_day) {
      $day_str = $current_day->format('Y-m-d');

      $sql = "SELECT {$tbl_route_h}.dp_s_num,
                  COUNT(CASE WHEN {$tbl_meal_order}.mlo02 = 1 AND {$tbl_service_case}.sec04 = 1 AND {$tbl_route_h}.reh05 = 1 THEN 1 END) AS lunch_count,
                  COUNT(CASE WHEN {$tbl_meal_order}.mlo02 = 2 AND {$tbl_service_case}.sec04 = 3 AND {$tbl_route_h}.reh05 = 2 THEN 1 END) AS dinner_count
              FROM {$tbl_meal_order}
              LEFT JOIN {$tbl_clients} ON {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num
              LEFT JOIN {$tbl_route_h} ON {$tbl_route_h}.s_num = {$tbl_meal_order}.reh_s_num
              LEFT JOIN {$tbl_service_case} ON {$tbl_service_case}.s_num = {$tbl_meal_order}.sec_s_num
              LEFT JOIN {$tbl_daily_shipment} ON {$tbl_daily_shipment}.mlo_s_num = {$tbl_meal_order}.s_num
              LEFT JOIN {$tbl_delivery_person} ON {$tbl_delivery_person}.s_num = {$tbl_daily_shipment}.dp_s_num
              WHERE {$tbl_meal_order}.d_date IS NULL
                  AND {$tbl_service_case}.d_date IS NULL
                  AND {$tbl_route_h}.d_date IS NULL
                  AND {$tbl_delivery_person}.is_available = 1
                  AND {$tbl_clients}.is_available = 1
                  AND {$tbl_meal_order}.mlo99 = 'Y'
                  AND {$tbl_meal_order}.mlo01 >= '{$day_str} 00:00:00'
                  AND {$tbl_meal_order}.mlo01 <= '{$day_str} 23:59:59'
              GROUP BY {$tbl_route_h}.dp_s_num
              ORDER BY {$tbl_route_h}.dp_s_num";

      $rs = $this->db->query($sql);
      if ($rs->num_rows() > 0) { // 有資料才執行
        foreach ($rs->result() as $row) {
          // 將所有欄位讀取到 $data
          $data[$day_str][$row->dp_s_num] = array(
            'lunch' => $row->lunch_count,
            'dinner' => $row->dinner_count
          );
        }
      }
      // 增加一天
      $current_day->modify('+1 day');
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: get_by_month()
  //  函數功能: 取得某時間的所有訂單
  //  程式設計: loyenhsiang
  //  設計日期: 2024/02/20
  // **************************************************************************
  public function get_by_month($month = NULL, $mlo02 = NULL) {
    $start_date = date("Y-m-01", strtotime($month));
    $end_date = date("Y-m-t", strtotime($month));

    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_meal_order = $this->zi_init->chk_tbl_no_lang('meal_order'); // 訂餐資料
    $data = NULL;
    $sql = "select {$tbl_meal_order}.*
                  from {$tbl_meal_order}   
                  left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_meal_order}.ct_s_num 
                  where {$tbl_meal_order}.d_date is null
                        and {$tbl_meal_order}.mlo01 >= '{$start_date}'
                        and {$tbl_meal_order}.mlo01 <= '{$end_date}'
                        and {$tbl_meal_order}.mlo02 = '{$mlo02}'
                        and {$tbl_clients}.is_available = 1
                  group by {$tbl_meal_order}.sec_s_num
                  order by {$tbl_meal_order}.mlo01 desc
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
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
  // **************************************************************************
  private function _aes_fd() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_clients}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-30
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