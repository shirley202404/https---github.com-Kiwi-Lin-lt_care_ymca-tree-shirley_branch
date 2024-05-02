<?php
class Gps_log_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public $aes_fd = array('__XX__');  
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
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔

    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_gps_log}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_gps_log}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->delivery_person_model->_aes_fd()}
                  from {$tbl_gps_log}
                  left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_gps_log}.b_empno
                  left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_gps_log}.e_empno
                  left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_gps_log}.em_s_num
                  where {$tbl_gps_log}.d_date is null
                        and {$tbl_gps_log}.s_num = ?
                  order by {$tbl_gps_log}.s_num desc
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd as $k => $v) {
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
  public function chk_duplicate($fd_name) {
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_gps_log}.*
                   
            from {$tbl_gps_log}
            where {$tbl_gps_log}.d_date is null
                  and {$tbl_gps_log}.fd_name = ?
            order by {$tbl_gps_log}.s_num desc
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
  //  設計日期: 2021-02-01
  // **************************************************************************
  public function get_all() {
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $data = NULL;
    $sql = "select {$tbl_gps_log}.*
                   
            from {$tbl_gps_log}
            where {$tbl_gps_log}.d_date is null
            order by {$tbl_gps_log}.s_num desc
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
  //  函數名稱: get_gps_data()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-01
  // **************************************************************************
  public function get_gps_data() {
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $where = '';
    $data = NULL;
    $v = $this->input->get();
    if($v['type'] == 1) {
      $where .= "and {$tbl_gps_log}.b_date >= '{$v['history_date']} 09:00:00'
                     and {$tbl_gps_log}.b_date <= '{$v['history_date']} 13:30:00'
                ";
    }
    if($v['type'] == 2) {
      $where .= "and {$tbl_gps_log}.b_date >= '{$v['history_date']} 13:30:00'
                     and {$tbl_gps_log}.b_date <= '{$v['history_date']} 18:30:00'
                ";
    }
    $sql = "select {$tbl_gps_log}.s_num,
                             {$tbl_gps_log}.gsl01,
                             {$tbl_gps_log}.gsl02,
                             {$tbl_gps_log}.b_date
            from {$tbl_gps_log}
            where {$tbl_gps_log}.d_date is null
                        and {$tbl_gps_log}.em_s_num = {$v['dp_s_num']}
                        {$where}
            order by {$tbl_gps_log}.b_date asc
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
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔

    $where = " {$tbl_gps_log}.d_date is null ";
    $order = " {$tbl_gps_log}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_gps_log}.em_s_num like '%{$que_str}%' /* em_s_num */                       
                             or {$tbl_gps_log}.gsl01 like '%{$que_str}%' /* 打卡經度 */                       
                             or {$tbl_gps_log}.gsl02 like '%{$que_str}%' /* 打卡緯度 */                       
                             or {$tbl_gps_log}.gsl99 like '%{$que_str}%' /* 備註 */
                            )
                          ";
    }

    if(!empty($get_data['que_reh_s_num'])) {
      $dp_s_num_arr = array();
      $reh_s_num = $get_data['que_reh_s_num'];
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
        $where .= "and {$tbl_gps_log}.em_s_num in ({$dp_s_num_set})";
      }
    }
        
    if(!empty($get_data['que_em_s_num'])) { // sys_account.s_num
      $que_em_s_num = $get_data['que_em_s_num'];
      $que_em_s_num = $this->db->escape_like_str($que_em_s_num);
      $where .= " and {$tbl_gps_log}.em_s_num = '{$que_em_s_num}'  /* sys_account.s_num */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_gps_log}.s_num
                from {$tbl_gps_log}
                where $where
                group by {$tbl_gps_log}.s_num
                order by {$tbl_gps_log}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_gps_log}.*
                              {$this->delivery_person_model->_aes_fd()}
                  from {$tbl_gps_log}
                  left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_gps_log}.em_s_num
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
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    if(!$this->db->insert($tbl_gps_log, $data)) {
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
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_gps_log, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_add_api()
  //  函數功能: 新增儲存資料(api)
  //  程式設計: kiwi
  //  設計日期: 2020-12-27
  // **************************************************************************
  public function save_add_api($verify_s_num) {
    $rtn_msg = 'ok';
    $v = $this->input->post();
    $data['b_empno'] = $verify_s_num;
    $data['b_date'] = date('Y-m-d H:i:s');
    $data['em_s_num'] = $verify_s_num;
    $data['gsl01'] = $v['gsl01'];
    $data['gsl02'] = $v['gsl02'];    
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    if(!$this->db->insert($tbl_gps_log, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_add_web_api()
  //  函數功能: 新增儲存資料(web api) JSON 格式，整個不懂在幹嘛
  //  程式設計: kiwi
  //  設計日期: 2020-12-27
  // **************************************************************************
  public function save_add_web_api($verify_s_num) {
    $rtn_msg = 'ok';
    $json_data = json_decode(file_get_contents('php://input'), true);
    $v = explode("&", $json_data);
    $data['b_empno'] = $verify_s_num;
    $data['b_date'] = date('Y-m-d H:i:s');
    $data['em_s_num'] = $verify_s_num;
    $data['gsl01'] = explode("=", $v[0])[1];
    $data['gsl02'] = explode("=", $v[1])[1];
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    if(!$this->db->insert($tbl_gps_log, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
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
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_gps_log, $data)) {
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
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_gps_log, $data)) {
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
  private function _aes_fd() {
    $tbl_gps_log = $this->zi_init->chk_tbl_no_lang('gps_log'); // GPS記錄檔
    $aes_fd = "";
    foreach ($this->aes_fd as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_gps_log}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
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