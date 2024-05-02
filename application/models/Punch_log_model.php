<?php
class Punch_log_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public $aes_fd1 = array('ct_name');  
  public $aes_fd2 = array('dp01','dp02');  
  public $aes_fd3 = array('ct01','ct02');  
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_punch_log}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_punch_log}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   case {$tbl_punch_log}.phl02
                    when '1' then '簽到'   
                    when '2' then '簽退'
                  end as phl02_str,
                  case {$tbl_punch_log}.phl50
                    when '1' then '有網路'   
                    when '2' then '無網路'
                    when '3' then '補登'
                  end as phl50_str,
                  {$tbl_service_case}.sec01,
                  {$tbl_delivery_person}.dp01,
                  {$tbl_delivery_person}.dp02,
                  concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) as ct_name
                  {$this->_aes_fd('delivery_person')}
                  from {$tbl_punch_log}
                  left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_punch_log}.b_empno
                  left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_punch_log}.e_empno
                  left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_punch_log}.sec_s_num
                  left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
                  left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_punch_log}.dp_s_num
                  where {$tbl_punch_log}.d_date is null
                        and {$tbl_punch_log}.s_num = ?
                  order by {$tbl_punch_log}.s_num desc
                 ";

    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd1 as $k => $v) {
        list($fd_name,$fd_val) = $this->_symbol_text($row,$v);
        $row->$fd_name = $fd_val;
      } 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function chk_duplicate() {
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $v = $this->input->post();
    $row = NULL;
    $sql = "select {$tbl_punch_log}.*
            from {$tbl_punch_log}
            where {$tbl_punch_log}.d_date is null
                  and {$tbl_punch_log}.ct_s_num = ?
                  and {$tbl_punch_log}.mlo_s_num = ?
                  and {$tbl_punch_log}.sec_s_num = ?
                  and {$tbl_punch_log}.phl01 = ?
                  and {$tbl_punch_log}.phl02 = ?
            order by {$tbl_punch_log}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($v['ct_s_num'] , $v['mlo_s_num'] , $v['sec_s_num'] , $v['phl01'], $v['phl02']));
    if($rs->num_rows() > 0) { // 資料重複
      $row = $rs->row(); 
    }
    return $row;
  }

  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function get_all() {
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $data = NULL;
    $sql = "select {$tbl_punch_log}.* 
                  from {$tbl_punch_log}
                  where {$tbl_punch_log}.d_date is null
                  order by {$tbl_punch_log}.s_num desc
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
  //  函數名稱: que_download_data()
  //  函數功能: 取得下載資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function que_download_data() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線單頭資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線單身資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料

    $where = " {$tbl_punch_log}.d_date is null ";
    $order = " {$tbl_punch_log}.reh_s_num desc, {$tbl_punch_log}.phl01 desc ";
    $get_data = $this->input->get();
    
    if(!empty($get_data['que_reh_s_num'])) {
      if("all" != $get_data['que_reh_s_num']) {
        $reh_s_num = $get_data['que_reh_s_num'];
        $where .=  "and {$tbl_route_b}.reh_s_num = {$reh_s_num}";
      }
    }
    
    if(!empty($get_data['que_phl01_start'])) {
      $que_phl01_start = $get_data['que_phl01_start'];
      $que_phl01_start = $this->db->escape_like_str($que_phl01_start);
      $where .= " and {$tbl_punch_log}.phl01 >= '{$que_phl01_start} 00:00:00'  ";
    }
    
    if(!empty($get_data['que_phl01_end'])) {
      $que_phl01_end = $get_data['que_phl01_end'];
      $que_phl01_end = $this->db->escape_like_str($que_phl01_end);
      $que_phl01_end = date('Y-m-d',strtotime("{$que_phl01_end} +1 day")); 
      $where .= " and {$tbl_punch_log}.phl01 <= '{$que_phl01_end} 00:00:00' ";
    }

    $data = NULL;
    $sql = "select {$tbl_punch_log}.*,
                   case {$tbl_punch_log}.phl02
                     when '1' then '簽到'   
                     when '2' then '簽退'
                   end as phl02_str,
                   {$tbl_service_case}.sec01,
                   {$tbl_service_case}.sec04,
                   case {$tbl_service_case}.sec01
                      when '1' then '長照案'
                      when '2' then '特殊-老案'
                      when '3' then '自費戶'
                      when '4' then '邊緣戶'
                      when '5' then '身障案'
                      when '6' then '特殊-身案'
                      when '7' then '志工'
                   end as sec01_str,
                   case {$tbl_service_case}.sec04
                     when '1' then '午餐'
                     when '2' then '中晚餐'
                     when '3' then '晚餐'
                   end as sec04_str,
                   case {$tbl_punch_log}.phl50
                     when '1' then '有網路'   
                     when '2' then '無網路'
                     when '3' then '補登'
                   end as phl50_str,
                   concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) as ct_name,
                  {$tbl_delivery_person}.dp01,
                  {$tbl_delivery_person}.dp02,
                  {$tbl_route_h}.reh01
                  {$this->_aes_fd('delivery_person')}
            from {$tbl_punch_log}
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_punch_log}.sec_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_punch_log}.dp_s_num
            left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_punch_log}.ct_s_num
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_route_b}.reh_s_num
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
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_punch_data()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-03
  // **************************************************************************
  public function get_punch_data() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路徑資料
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $where = '';
    $data = NULL;
    $v = $this->input->get();
    if($v['type'] == 1) {
      $where .= "and {$tbl_punch_log}.phl01 >= '{$v['history_date']} 09:00:00'
                     and {$tbl_punch_log}.phl01 <= '{$v['history_date']} 12:30:00'
                ";
    }
    if($v['type'] == 2) {
      $where .= "and {$tbl_punch_log}.phl01 >= '{$v['history_date']} 13:30:00'
                     and {$tbl_punch_log}.phl01 <= '{$v['history_date']} 18:30:00'
                ";
    }
    $sql = "select {$tbl_punch_log}.*,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02
                   {$this->_aes_fd('clients')}
            from {$tbl_punch_log}
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_punch_log}.reh_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_punch_log}.ct_s_num
            where {$tbl_punch_log}.d_date is null
                  and {$tbl_punch_log}.dp_s_num = {$v['dp_s_num']}
                  and {$tbl_punch_log}.phl02 = 1
                  {$where}
            group by {$tbl_punch_log}.ct_s_num, {$tbl_punch_log}.phl01
            order by {$tbl_punch_log}.phl01 asc
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
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路徑資料
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料

    $where = " {$tbl_punch_log}.d_date is null ";
    $order = " {$tbl_punch_log}.phl01 desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_punch_log}.ct_s_num like binary '%{$que_str}%' /* tw_clients.s_num */                       
                       or {$tbl_punch_log}.mlo_s_num like binary '%{$que_str}%' /* tw_meal_order.s_num */                       
                       or {$tbl_punch_log}.phl01 like binary '%{$que_str}%' /* 打卡時間 */                       
                       or {$tbl_punch_log}.phl02 like binary '%{$que_str}%' /* 打卡型態(簽到、簽退等) */                       
                       or {$tbl_punch_log}.phl03 like binary '%{$que_str}%' /* 打卡經度 */                       
                       or {$tbl_punch_log}.phl04 like binary '%{$que_str}%' /* 打卡緯度 */                       
                       or {$tbl_punch_log}.phl05 like binary '%{$que_str}%' /* 打卡是否有效 */                       
                       or {$tbl_punch_log}.phl99 like binary '%{$que_str}%' /* 備註 */
                       or AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 中文姓 */                       
                       or AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 中文名 */   
                       or concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) like '%{$que_str}%' /* 中文名 */   
                      )
                ";
    }
    
    if(!empty($get_data['que_reh_s_num'])) {
      $dp_s_num_arr = array();
      $reh_s_num = $get_data['que_reh_s_num'];
      $where .=  "and {$tbl_punch_log}.reh_s_num = {$reh_s_num}";
    }
    
    if(!empty($get_data['que_phl01_start'])) {
      $que_phl01_start = $get_data['que_phl01_start'];
      $que_phl01_start = $this->db->escape_like_str($que_phl01_start);
      $where .= " and {$tbl_punch_log}.phl01 >= '{$que_phl01_start} 00:00:00'  ";
    }
    
    if(!empty($get_data['que_phl01_end'])) {
      $que_phl01_end = $get_data['que_phl01_end'];
      $que_phl01_end = $this->db->escape_like_str($que_phl01_end);
      $que_phl01_end = date('Y-m-d',strtotime("{$que_phl01_end} +1 day")); 
      $where .= " and {$tbl_punch_log}.phl01 <= '{$que_phl01_end} 00:00:00' ";
    }
      
      /*
      $route_row = $this->route_model->get_one($reh_s_num); // 送餐員
      if(NULL != $route_row) {
        $route_sw_row = $this->route_model->get_route_sw($reh_s_num); // 輔助送餐社工
        if(NULL != $route_sw_row) {
          $dp_s_num_arr = array_column($route_sw_row , 'dp_s_num');
        }
        array_push($dp_s_num_arr , 
                            $route_row->dp_s_num , 
                            $route_row->reh06_mon_dp_s_num,
                            $route_row->reh06_tue_dp_s_num,
                            $route_row->reh06_wed_dp_s_num,
                            $route_row->reh06_thu_dp_s_num,
                            $route_row->reh06_fri_dp_s_num
                          );
        $dp_s_num_set = implode("," , $dp_s_num_arr);
        $where .=  "and {$tbl_punch_log}.dp_s_num in ({$dp_s_num_set})
                               and {$tbl_route_b}.reh_s_num = {$reh_s_num}                           
                            ";
        */
    
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_punch_log}.s_num
                from {$tbl_punch_log}
                left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_punch_log}.sec_s_num
                left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
                left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_punch_log}.dp_s_num
                left join {$tbl_route_b} on {$tbl_route_b}.ct_s_num = {$tbl_punch_log}.ct_s_num
                where {$where}
                group by {$tbl_punch_log}.s_num
                order by {$tbl_punch_log}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_punch_log}.*,
                  case {$tbl_punch_log}.phl02
                    when '1' then '簽到'   
                    when '2' then '簽退'
                  end as phl02_str,
                  case {$tbl_punch_log}.phl50
                    when '1' then '有網路'   
                    when '2' then '無網路'
                    when '3' then '補登'
                  end as phl50_str,
                  {$tbl_service_case}.sec01,
                  {$tbl_service_case}.sec04,
                  case {$tbl_service_case}.sec01
                     when '1' then '長照案'
                     when '2' then '特殊-老案'
                     when '3' then '自費戶'
                     when '4' then '邊緣戶'
                     when '5' then '身障案'
                     when '6' then '特殊-身案'
                     when '7' then '志工'
                   end as sec01_str,
                   case {$tbl_service_case}.sec04
                     when '1' then '午餐'
                     when '2' then '中晚餐'
                     when '3' then '晚餐'
                   end as sec04_str,
                  {$tbl_delivery_person}.dp01,
                  {$tbl_delivery_person}.dp02,
                  concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) as ct_name
                  {$this->_aes_fd('delivery_person')}
                  from {$tbl_punch_log}
                  left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_punch_log}.sec_s_num
                  left join {$tbl_clients} on {$tbl_clients}.s_num =  {$tbl_service_case}.ct_s_num
                  left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_punch_log}.dp_s_num
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
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return(array($data,$row_cnt));
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    }
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    if(!$this->db->insert($tbl_punch_log, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    foreach ($data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name,$this->aes_fd)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($data[$k_fd_name]);
      }
    } 
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_punch_log, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_add_api()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-06
  // **************************************************************************
  public function save_add_api($dp_s_num) {
    $data = $this->input->post();
    
    if(NULL != $this->chk_duplicate()) { // 檢查是否重複打卡
      echo "duplicate";
      return;
    }
    
    $data['b_empno'] = $dp_s_num;
    $data['b_date'] = date('Y-m-d H:i:s');
    $data['dp_s_num'] = $dp_s_num;
    // $data['phl01'] = date('Y-m-d H:i:s');
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    if(!$this->db->insert($tbl_punch_log, $data)) {
      echo "error";
      return;
    }
    else {
      echo "ok";
      return;
    }

    /*
    else {
      if(1 == $data['phl02']) {
        
        $punch_data['dys11'] = 1;
        $punch_data['dys12'] = $data['phl01'];
        $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 打卡紀錄資料
        if(!$this->db->insert($tbl_daily_shipment, $punch_data)) {
          $rtn_msg = $this->lang->line('add_ng');
        }
        
        $clients_row = $this->clients_model->get_one($data['ct_s_num']);
        if(NULL != $clients_row) {
          if(NULL != $clients_row->ct71) {
            $send_source = "PUNCH";
            $send_title = "餐點已送達!!";
            $send_to = $clients_row->ct70;
            $send_mail = $clients_row->ct71;
            $send_content = "您好，案主: {$clients_row->ct01}{$clients_row->ct02} 的餐點已送達!!";
            $this->sys_sendmail_model->save_add($send_source, $send_title, $send_to, $send_mail, $send_content);
            // $this->sys_sendline_model->save_add();
          }
        }
      }
    }
    */
  }
  // **************************************************************************
  //  函數名稱: save_web_add_api()
  //  函數功能: 新增儲存資料-web json格式
  //  程式設計: kiwi
  //  設計日期: 2020-12-06
  // **************************************************************************
  public function save_web_add_api($dp_s_num) {
    $data = $this->input->post();
    $json_data = json_decode(file_get_contents('php://input'), true);
    $v = explode("&", $json_data);
    foreach ($v as $k_fd => $v_fd) { // 切json資料，變成陣列格式
      $fd = explode("=", $v_fd);
      $data[$fd[0]] = $fd[1];
    }

    $phl01 = urldecode(urldecode(($data['phl01'])));
    $_POST['ct_s_num'] = $data['ct_s_num'];
    $_POST['mlo_s_num'] = $data['mlo_s_num'];
    $_POST['sec_s_num'] = $data['sec_s_num'];
    $_POST['phl01'] = $phl01;
    $_POST['phl02'] = $data['phl02'];
    $dys09 = $data['dys09'];
    $source = $data['source'];
    unset($data['dys09']);
    unset($data['source']);

    if(NULL != $this->chk_duplicate()) { // 檢查是否重複打卡
      echo "duplicate";
      unset($_POST);
      return;
    }
    
    $phl01 = urldecode(urldecode(($data['phl01'])));
    $data['b_empno'] = $dp_s_num;
    $data['b_date'] = date('Y-m-d H:i:s');
    $data['dp_s_num'] = $dp_s_num;
    $data['phl01'] = $phl01;
    // $data['phl01'] = date('Y-m-d H:i:s');
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    if(!$this->db->insert($tbl_punch_log, $data)) {
      echo "error";
      return;
    }
    else {
      if("PHP" != $source) {
        $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
        $this->db->where('ct_s_num', $data['ct_s_num']);
        $this->db->where('dys01', date("Y-m-d", strtotime($data['phl01'])));
        $this->db->where('dys09', $dys09);
        if($data['phl02'] == 1) {
          $upd_data['dys21'] = $phl01; // 簽到時間
          $upd_data['dys22'] = $data['phl50']; // 簽到方式
        }
        else {
          $upd_data['dys23'] = $phl01; // 簽到時間
          $upd_data['dys24'] = $data['phl50']; // 簽到方式
        }
        if(!$this->db->update($tbl_daily_shipment, $upd_data)) {
          echo "error";
          return;
        }
        else {
          echo "ok";
          return;
        }
      }
    }
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_punch_log, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_punch_log = $this->zi_init->chk_tbl_no_lang('punch_log'); // 打卡紀錄資料
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_punch_log, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
  // **************************************************************************
  private function _aes_fd($fd_tbl) {
    switch ($fd_tbl) {   
      case "service_case":       
        $encry_arr = $this->aes_fd1;
        $tbl = $this->zi_init->chk_tbl_no_lang('service_case');
      break;  
      case "delivery_person":    
        $encry_arr = $this->aes_fd2;   
        $tbl = $this->zi_init->chk_tbl_no_lang('delivery_person');
      break;  
      case "clients":       
        $encry_arr = $this->aes_fd3;
        $tbl = $this->zi_init->chk_tbl_no_lang('clients');
      break;  
    }
    $aes_fd = "";
    foreach ($encry_arr as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2021-02-01
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