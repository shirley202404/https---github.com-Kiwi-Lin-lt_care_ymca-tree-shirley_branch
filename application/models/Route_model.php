<?php
class Route_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('__XX__'); // 加密欄位
  public $aes_fd1 = array('dp_name','reh06_mon_dp_name','reh06_tue_dp_name','reh06_wed_dp_name','reh06_thu_dp_name','reh06_fri_dp_name'); // 加密欄位
  public $aes_fd2 = array('ct_name'); // 加密欄位
  public $aes_fd3 = array('ct01', 'ct02', 'ct12', 'ct13', 'ct14', 'ct15'); // 加密欄位
  public $chg_fd = array("reh03", "reh05", "reh07");
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工資料
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_route_h}.*,
                   case {$tbl_route_h}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   IF(sys_acc.acc_name is null ,
                     concat(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc.acc_name
                   ) as b_acc_name,
                   IF(sys_acc2.acc_name is null ,
                     concat(AES_DECRYPT(sw2.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw2.sw02,'{$this->db_crypt_key2}'))
                    ,sys_acc2.acc_name
                   ) as e_acc_name
                   {$this->_aes_fd('route_h')}
            from {$tbl_route_h}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_route_h}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_route_h}.e_empno
            left join {$tbl_social_worker} sw on sw.s_num = {$tbl_route_h}.b_empno
            left join {$tbl_social_worker} sw2 on sw2.s_num = {$tbl_route_h}.e_empno
            where {$tbl_route_h}.d_date is null
                  and {$tbl_route_h}.s_num = ?
            order by {$tbl_route_h}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      // 遮罩欄位處理 Begin //
      foreach ($this->aes_fd as $k => $v) {
        list($fd_name, $fd_val) = $this->_symbol_text($row,$v); // 遮罩欄位處理
        $row->$fd_name = $fd_val;
      } 
      // 遮罩欄位處理 End //
      foreach ($this->chg_fd as $k => $v) {
        list($fd_name, $fd_val) = $this->_replace_text($row,$v);
        $row->$fd_name = $fd_val;
      }
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: chk_duplicate()
  //  函數功能: 檢查重複
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_route_h}.*
            from {$tbl_route_h}
            where {$tbl_route_h}.d_date is null
                  and {$tbl_route_h}.fd_name = ?
            order by {$tbl_route_h}.s_num desc
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_all($order_fd=NULL) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔

    $order = " order by {$tbl_route_h}.reh01 asc";
    if(!empty($order_fd)) {
      switch ($order_fd) {
        case 'dp07_start':
          $order = " order by {$tbl_delivery_person}.{$order_fd} asc";
          break;
        case 's_num':
          $order = " order by {$tbl_route_h}.s_num asc";
          break;
      }
      
    }

    $data = NULL;
    $sql = "select {$tbl_route_h}.*
                   {$this->_aes_fd('route_h')}
            from {$tbl_route_h}
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_route_h}.dp_s_num
            where {$tbl_route_h}.d_date is null
            {$order}
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
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: get_all_without_test()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_all_without_test() {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $data = NULL;
    $sql = "select {$tbl_route_h}.*
                   {$this->_aes_fd('route_h')}
            from {$tbl_route_h}
            where {$tbl_route_h}.d_date is null
                  and {$tbl_route_h}.s_num not in('3','48','49','50')
            order by {$tbl_route_h}.reh01 asc
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
  //  函數名稱: get_all_is_available()
  //  函數功能: 取得所有資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $data = NULL;
    $sql = "select {$tbl_route_h}.*
                   {$this->_aes_fd('route_h')}
            from {$tbl_route_h}
            where {$tbl_route_h}.d_date is null
                  and {$tbl_route_h}.is_available = 1
            order by {$tbl_route_h}.s_num desc
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
  //  函數名稱: get_route_b()
  //  函數功能: 取得單身資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_route_b($reh_s_num) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔身

    $data = NULL;
    $sql = "select {$tbl_route_b}.*,
                   concat(AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}'), '', 
                          AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}')) as ct_name,
                   concat(AES_DECRYPT({$tbl_clients}.ct13,'{$this->db_crypt_key2}'), '', 
                          AES_DECRYPT({$tbl_clients}.ct14,'{$this->db_crypt_key2}'), '', 
                          AES_DECRYPT({$tbl_clients}.ct15,'{$this->db_crypt_key2}')) as ct_address
            from {$tbl_route_b}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_route_b}.ct_s_num
            where {$tbl_route_b}.d_date is null
                  and {$tbl_route_b}.reh_s_num = ?
            order by {$tbl_route_b}.reb01 asc, {$tbl_route_b}.e_date desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($reh_s_num));
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
  //  函數名稱: get_route_sw()
  //  函數功能: 取得輔助社工資料
  //  程式設計: kiwi
  //  設計日期: 2021-05-20
  // **************************************************************************
  public function get_route_sw($reh_s_num) {
    $tbl_route_sw = $this->zi_init->chk_tbl_no_lang('route_sw'); // 送餐路徑規劃-協助社工紀錄表
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員資料表
    $data = NULL;
    $sql = "select {$tbl_route_sw}.*,
                   {$tbl_delivery_person}.dp01,
                   {$tbl_delivery_person}.dp02,
                   {$tbl_delivery_person}.dp09_teltphone,
                   {$tbl_delivery_person}.dp09_homephone
                   {$this->delivery_person_model->_aes_fd()}
            from {$tbl_route_sw}
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_route_sw}.dp_s_num
            where {$tbl_route_sw}.d_date is null
            and {$tbl_route_sw}.reh_s_num = ?
            order by {$tbl_route_sw}.s_num asc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($reh_s_num));
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
  //  函數名稱: get_all_by_ct()
  //  函數功能: 取得案主所有路徑資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_all_by_ct($ct_s_num) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔身
    $data = NULL;
    $sql = "select {$tbl_route_b}.*,
                   {$tbl_route_h}.reh01,
                   {$tbl_route_h}.reh03,
                   {$tbl_route_h}.reh05
                   {$this->_aes_fd('route_b')}
                  from {$tbl_route_b}
                  left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_route_b}.reh_s_num
                  where {$tbl_route_b}.d_date is null
                  and {$tbl_route_b}.ct_s_num = ?
                  order by {$tbl_route_b}.reb01 asc
                 ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($ct_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_symbol_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  
  // **************************************************************************
  //  函數名稱: api_get_route_b()
  //  函數功能: WEB API 取得所有單身資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function api_get_route_b() {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔身
    $data = NULL;
    $sql = "select {$tbl_route_b}.*,
                   {$tbl_clients}.ct01,
                   {$tbl_clients}.ct02,
                   {$tbl_clients}.ct12,
                   {$tbl_clients}.ct13,
                   {$tbl_clients}.ct14,
                   {$tbl_clients}.ct15,
                   {$tbl_clients}.ct16,
                   {$tbl_clients}.ct17
                   {$this->_aes_fd('route_b')}
                   {$this->_aes_fd('clients')}
            from {$tbl_route_b}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_route_b}.ct_s_num
            where {$tbl_route_b}.d_date is null
            order by {$tbl_route_b}.reb01 asc
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $where = " {$tbl_route_h}.d_date is null ";
    $order = " {$tbl_route_h}.reh01 asc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_route_h}.reh01 like '%{$que_str}%' /* 路線序號 */
                       or {$tbl_route_h}.reh02 like '%{$que_str}%' /* 路線名稱 */
                       or {$tbl_route_h}.reh03 like '%{$que_str}%' /* 上限人數 */
                       or {$tbl_route_h}.reh99 like '%{$que_str}%' /* 備註 */
                       or {$tbl_route_h}.reh99 like '%{$que_str}%' /* 備註 */
                       or AES_DECRYPT({$tbl_route_h}.dp_name,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 外送員姓名 */                       
                      )
                ";
    }

    if(!empty($get_data['que_reh01'])) { // 路線序號
      $que_reh01 = $get_data['que_reh01'];
      $que_reh01 = $this->db->escape_like_str($que_reh01);
      $where .= " and {$tbl_route_h}.reh01 = '{$que_reh01}'  /* 路線序號 */ ";
    }
    if(!empty($get_data['que_reh02'])) { // 路線名稱
      $que_reh02 = $get_data['que_reh02'];
      $que_reh02 = $this->db->escape_like_str($que_reh02);
      $where .= " and {$tbl_route_h}.reh02 = '{$que_reh02}'  /* 路線名稱 */ ";
    }
    if(!empty($get_data['que_reh03'])) { // 路線類別
      $que_reh03 = $get_data['que_reh03'];
      $que_reh03 = $this->db->escape_like_str($que_reh03);
      $where .= " and {$tbl_route_h}.reh03 = '{$que_reh03}'  /* 路線類別 */ ";
    }
    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_route_h}.s_num
                          from {$tbl_route_h}
                          where $where
                          group by {$tbl_route_h}.s_num
                          order by {$tbl_route_h}.s_num
                         ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_route_h}.*
                  {$this->_aes_fd('route_h')}
             from {$tbl_route_h}
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
          list($fd_name, $fd_val) = $this->_replace_text($row,$fd_name); // 字串替換
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $route_h_data = NULL;
    if(isset($data["route_h"])) {
      $route_h_data = $data["route_h"];
    }
    $route_sw_data = NULL;
    if(isset($data["route_sw"])) {
      $route_sw_data = $data["route_sw"];
    }
    $route_b_data = NULL;
    if(isset($data["route_b"])) {
      $route_b_data = $data["route_b"];
    }
    foreach ($route_h_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name , $this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($route_h_data[$k_fd_name]);
      }
    }
    $route_h_data['b_empno'] = $_SESSION['acc_s_num'];
    $route_h_data['b_date'] = date('Y-m-d H:i:s');
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 耗材入庫資料-檔頭
    if(!$this->db->insert($tbl_route_h, $route_h_data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    else {
      $reh_s_num = $this->db->insert_id();
      $_POST['s_num'] = $reh_s_num;
      if($route_sw_data != NULL) {
        $i = 0;
        foreach ($route_sw_data['dp_s_num'] as $k => $v) {
          $route_sw_data_batch[$i]['reh_s_num'] = $reh_s_num;
          $route_sw_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
          $route_sw_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
          $route_sw_data_batch[$i]['dp_s_num'] = $route_sw_data['dp_s_num'][$k];
          $i++;
        }
        if($route_sw_data_batch != NULL) {
          $tbl_route_sw = $this->zi_init->chk_tbl_no_lang('route_sw'); // 外送輔助社工紀錄資料庫
          if(!$this->db->insert_batch($tbl_route_sw , $route_sw_data_batch)) {
            $rtn_msg = $this->lang->line('add_ng');
            die($rtn_msg);
          }
        }  
      }
      
      // 路線案主資料-檔身 Begin //
      if(isset($route_b_data)) { // 有資料才處理
        $i=0;
        foreach ($route_b_data['s_num'] as $k => $v) {
          foreach ($route_b_data as $k2 => $v2) {
            $route_b_data_batch[$i][$k2]=$route_b_data[$k2][$k];
          }

          $route_b_data_batch[$i]['reh_s_num'] = $reh_s_num;
          $route_b_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
          $route_b_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
          // 如果欄位都沒有輸入資料,就不儲存
          if('' == $route_b_data_batch[$i]['ct_s_num'] and '' == $route_b_data_batch[$i]['reb01'] ) {
            unset($route_b_data_batch[$i]);
          }
          unset($route_b_data_batch[$i]['s_num']); // s_num 要取消,不然會重複,導致儲存失敗
          $i++;
        }

        if($route_b_data_batch != NULL) {
          $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線案主資料
          foreach ($route_b_data_batch as $k => $v) {
            foreach ($v as $k_fd_name => $v_data) {
              if(in_array($k_fd_name , $this->aes_fd2)) { // 加密欄位
                $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
                unset($route_b_data_batch[$k][$k_fd_name]);
              }
            }
            if(!$this->db->insert($tbl_route_b , $route_b_data_batch[$k])) {
              $rtn_msg = $this->lang->line('add_ng');
            } 
          }
        }
      }
      // 路線案主資料-檔身 End //
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 耗材入庫資料-檔頭
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 耗材入庫資料-檔身
    $tbl_route_sw = $this->zi_init->chk_tbl_no_lang('route_sw'); // 外送輔助社工紀錄資料庫
    $data = $this->input->post();
    $route_h_data = $data["route_h"];
    $route_sw_data = NULL;
    if(isset($data["route_sw"])) {
      $route_sw_data = $data["route_sw"];
    }
    $route_b_data = NULL;
    if(isset($data["route_b"])) {
      $route_b_data = $data["route_b"];
    }
    $reh_s_num = $route_h_data['s_num']; // tw_route_h.s_num
    foreach ($route_h_data as $k_fd_name => $v_data) {
      if(in_array($k_fd_name , $this->aes_fd1)) { // 加密欄位
        $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
        unset($route_h_data[$k_fd_name]);
      }
    }
    $route_h_data['e_empno'] = $_SESSION['acc_s_num'];
    $route_h_data['e_date'] = date('Y-m-d H:i:s');
    $this->db->where('s_num', $reh_s_num);
    if(!$this->db->update($tbl_route_h, $route_h_data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }

    // 刪除
    $this->db->where('reh_s_num', $reh_s_num);
    if(!$this->db->delete($tbl_route_b)) {
      $rtn_msg = $this->lang->line('del_ng');
      echo $rtn_msg;
      return;
    }
    
    // 刪除
    $this->db->where('reh_s_num', $reh_s_num);
    if(!$this->db->delete($tbl_route_sw)) {
      $rtn_msg = $this->lang->line('del_ng');
      echo $rtn_msg;
      return;
    }

    if($route_sw_data != NULL) {
      $i = 0;
      foreach ($route_sw_data['dp_s_num'] as $k => $v) {
        $route_sw_data_batch[$i]['reh_s_num'] = $reh_s_num;
        $route_sw_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
        $route_sw_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
        $route_sw_data_batch[$i]['dp_s_num'] = $route_sw_data['dp_s_num'][$k];
        $i++;
      }
      if($route_sw_data_batch != NULL) {
        if(!$this->db->insert_batch($tbl_route_sw , $route_sw_data_batch)) {
          $rtn_msg = $this->lang->line('add_ng');
          die($rtn_msg);
          return;
        }
      }  
    }
      
    if(NULL != $route_b_data) { // 有資料才處理
      $i = 0;
      foreach ($route_b_data['s_num'] as $k => $v) {
        foreach ($route_b_data as $k2 => $v2) {
          $route_b_data_batch[$i][$k2] = $route_b_data[$k2][$k];
        }
        $route_b_data_batch[$i]['reh_s_num'] = $reh_s_num;
        $route_b_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
        $route_b_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
        // 如果欄位都沒有輸入資料,就不儲存
        if('' == $route_b_data_batch[$i]['ct_s_num'] and '' == $route_b_data_batch[$i]['reb01'] ) {
          unset($route_b_data_batch[$i]);
        }
        unset($route_b_data_batch[$i]['s_num']); // s_num 要取消,不然會重複,導致儲存失敗
        $i++;
      }
      if($route_b_data_batch != NULL) {
        $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 路線案主資料
        foreach ($route_b_data_batch as $k => $v) {
          foreach ($v as $k_fd_name => $v_data) {
            if(in_array($k_fd_name , $this->aes_fd2)) { // 加密欄位
              $this->db->set($k_fd_name, "AES_ENCRYPT('{$v_data}','{$this->db_crypt_key2}')", FALSE);
              unset($route_b_data_batch[$k][$k_fd_name]);
            }
          }
          if(!$this->db->insert($tbl_route_b , $route_b_data_batch[$k])) {
            $rtn_msg = $this->lang->line('add_ng');
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
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $_POST['s_num'] = $f_s_num;
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_route = $this->zi_init->chk_tbl_no_lang('route'); // 送餐路徑規劃-檔頭
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_route, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_route_h, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    $this->db->where('reh_s_num' , $v["s_num"]);
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔身
    if(!$this->db->update($tbl_route_b, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_sort()
  //  函數功能: 儲存瀏覽上下移動排序
  //  程式設計: Tony
  //  設計日期: 2020-08-22
  // **************************************************************************
  public function save_sort($kind='up') {
    $rtn_msg = 'ok';
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); 
    $get_data = $this->input->get();         
    $reh_s_num = $get_data["reh_s_num"];
    $before_sort = $get_data['before_sort'];
    if('up'==$kind) { // 上移
      $after_sort = $before_sort-1; // 修改後的排序
    }
    else {
      $after_sort = $before_sort+1; // 修改後的排序
    }
    
    //u_var_dump($before_sort);
    // 更新排序位置
    if($before_sort < $after_sort) {
      for($i=($before_sort+1);$i<=$after_sort;$i++) {
        //$classify_sort_row = $this->get_one_by_sort($i);
        $data_sort['reb01'] = $i-1;
        $this->db->where('reh_s_num', $reh_s_num);
        $this->db->where('reb01', $i);
        if(!$this->db->update($tbl_route_b, $data_sort)) {
          $rtn_msg = $this->lang->line('upd_ng');
        }
      }
    }
    else {
      for($i=($before_sort-1);$i>=$after_sort;$i--) {
        $data_sort['reb01'] = $i+1;
        $this->db->where('reh_s_num', $reh_s_num);
        $this->db->where('reb01', $i);
        if(!$this->db->update($tbl_route_b, $data_sort)) {
          $rtn_msg = $this->lang->line('upd_ng');
        }
      }
    }

    $data_sort['reb01'] = $after_sort;
    // 更新目前修改資料
    $this->db->where('ct_s_num', $get_data['ct_s_num']);
    $this->db->where('reh_s_num', $get_data['reh_s_num']);
    if(!$this->db->update($tbl_route_b, $data_sort)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    //echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: que_client_route()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-04
  // **************************************************************************
  public function que_client_route($sec04, $ct_s_num) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔頭
    $tbl_verification_person = $this->zi_init->chk_tbl_no_lang('verification_person'); // 核銷人員資料檔
    
    $row = NULL;
    $sql = "select {$tbl_route_h}.*,
                   {$tbl_route_b}.reb01,
                   {$tbl_route_b}.reb03_vp_s_num,
                   concat(AES_DECRYPT({$tbl_verification_person}.vp01,'{$this->db_crypt_key2}') , '' , AES_DECRYPT({$tbl_verification_person}.vp02,'{$this->db_crypt_key2}')) as vp_name
                   {$this->_aes_fd('route_h')}
            from {$tbl_route_h}
            left join {$tbl_route_b} on {$tbl_route_b}.reh_s_num = {$tbl_route_h}.s_num
            left join {$tbl_verification_person} on {$tbl_verification_person}.s_num = {$tbl_route_b}.reb03_vp_s_num
            where {$tbl_route_h}.d_date is null
                  and {$tbl_route_h}.reh05 = {$sec04}
                  and {$tbl_route_b}.ct_s_num = {$ct_s_num}
            order by {$tbl_route_h}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: que_by_reh01()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-04
  // **************************************************************************
  public function que_by_reh01($reh01) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $row = NULL;
    $sql = "select {$tbl_route_h}.*
            from {$tbl_route_h}
            where {$tbl_route_h}.d_date is null
                  and {$tbl_route_h}.reh01 = ?
            order by {$tbl_route_h}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($reh01));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: que_client_route_data()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-06-04
  // **************************************************************************
  public function que_client_route_data() {
    $v = $this->input->post();
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    $tbl_route_b = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔頭
    $row = NULL;
    $sql = "select {$tbl_route_h}.*,
                   {$tbl_route_b}.reb01,
                   {$tbl_route_b}.reb03_vp_s_num
                   {$this->_aes_fd('route_h')}
            from {$tbl_route_h}
            left join {$tbl_route_b} on {$tbl_route_b}.reh_s_num = {$tbl_route_h}.s_num
            where {$tbl_route_h}.d_date is null
                  and {$tbl_route_h}.reh05 = {$v['ocl_r01']}
                  and {$tbl_route_b}.ct_s_num = {$v['ct_s_num']}
            order by {$tbl_route_h}.s_num desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    echo json_encode($row);
    return;
  }
  
  // **************************************************************************
  //  函數名稱: upd_dp()
  //  函數功能: 更新每日送餐員資料(補資料用)
  //  程式設計: kiwi
  //  設計日期: 2021-07-19
  // **************************************************************************
  public function upd_dp() {
    $route_row = $this->get_all();
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
    foreach ($route_row as $k => $v) {
      $dp_s_num = $v['dp_s_num'];
      $dp_name = $v['dp_name'];
      foreach ($v as $k_fd_name => $v_data) {
        if($k_fd_name != 'dp_name') {
          if(in_array($k_fd_name , $this->aes_fd1)) { // 加密欄位
            $this->db->set($k_fd_name, "AES_ENCRYPT('{$dp_name}','{$this->db_crypt_key2}')", FALSE);
          }
        }
      }
      $route_h_data['reh06_mon_dp_s_num'] = $dp_s_num;
      $route_h_data['reh06_tue_dp_s_num'] = $dp_s_num;
      $route_h_data['reh06_wed_dp_s_num'] = $dp_s_num;
      $route_h_data['reh06_thu_dp_s_num'] = $dp_s_num;
      $route_h_data['reh06_fri_dp_s_num'] = $dp_s_num;
      $this->db->where("s_num" , $v['s_num']);
      if(!$this->db->update($tbl_route_h, $route_h_data)) {
        $rtn_msg = $this->lang->line('upd_ng');
      }
    } 
  }
  // **************************************************************************
  //  函數名稱: _aes_fd1()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
  // **************************************************************************
  private function _aes_fd($tbl) {
    switch ($tbl) {
      case 'route_h':
        $fd_col = $this->aes_fd1;
        $fd_tbl = $this->zi_init->chk_tbl_no_lang('route_h'); // 送餐路徑規劃-檔頭
        break;
      case 'route_b':
        $fd_col = $this->aes_fd2;
        $fd_tbl = $this->zi_init->chk_tbl_no_lang('route_b'); // 送餐路徑規劃-檔身
        break; 
      case 'clients':
        $fd_col = $this->aes_fd3;
        $fd_tbl = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
        break;
    }
    $aes_fd = "";
    foreach ($fd_col as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$fd_tbl}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
  // **************************************************************************
  //  函數名稱: _symbol_text()
  //  函數功能: 顯示遮罩資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-31
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
  // **************************************************************************
  //  函數名稱: _replace_text()
  //  函數功能: 替換字串資料
  //  程式設計: kiwi
  //  設計日期: 2023-10-02
  // **************************************************************************
  private function _replace_text($row, $fd_name) {
    $fd_name_mask = '';
    $fd_val = NULL;
    if(isset($row->$fd_name)) {
      $fd_val = $row->$fd_name;
    }
  
    switch($fd_name) { // 檢查要替換的欄位名稱
      case 'reh03':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'reh05':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
      case 'reh07':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup($fd_name, $fd_val);      
        break;
    }

    if('' != $fd_name_mask) {
      $fd_name = $fd_name_mask;
    }

    return(array($fd_name, $fd_val));
  }
}
?>