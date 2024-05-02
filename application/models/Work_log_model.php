<?php
class Work_log_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2;
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_social_worker = $this->zi_init->chk_tbl_no_lang('social_worker'); // 社工帳號
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_work_log}.*,
                   {$tbl_route_h}.reh01,
                   if(sys_acc.acc_name is null, 
                     CONCAT(AES_DECRYPT({$tbl_delivery_person}.dp01,'{$this->db_crypt_key2}'),'',AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}')), 
                     sys_acc.acc_name) as b_acc_name,
                   if(sys_acc2.acc_name is null, 
                     CONCAT(AES_DECRYPT(sw.sw01,'{$this->db_crypt_key2}'),'',AES_DECRYPT(sw.sw02,'{$this->db_crypt_key2}')), 
                     sys_acc2.acc_name) as e_acc_name,
                   case {$tbl_work_log}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->_aes_fd("clients")}
                   {$this->_aes_fd("delivery_person")}
            from {$tbl_work_log}
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_work_log}.reh_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_work_log}.ct_s_num
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_clients}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_work_log}.e_empno
            left join {$tbl_social_worker} sw on sw.s_num = {$tbl_clients}.e_empno
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_work_log}.b_empno
            where {$tbl_work_log}.d_date is null
                  and {$tbl_work_log}.s_num = ?
            order by {$tbl_work_log}.s_num desc
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
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_work_log}.*
            from {$tbl_work_log}
            where {$tbl_work_log}.d_date is null
                  and {$tbl_work_log}.fd_name = ?
            order by {$tbl_work_log}.s_num desc
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
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_all() {
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    $data = NULL;
    $sql = "select {$tbl_work_log}.*
            from {$tbl_work_log}
            where {$tbl_work_log}.d_date is null
            order by {$tbl_work_log}.s_num desc
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
  //  函數名稱: get_que()
  //  函數功能: 取得查詢資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_route_h = $this->zi_init->chk_tbl_no_lang('route_h'); // 路線資料
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $where = " {$tbl_work_log}.d_date is null ";
    $order = " {$tbl_work_log}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_work_log}.wkl01 like '%{$que_str}%' /* 工作備註 */
                       or {$tbl_work_log}.wkl02 like '%{$que_str}%' /* 工作紀錄圖片 */
                       or AES_DECRYPT({$tbl_clients}.ct01,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 中文姓 */                       
                       or AES_DECRYPT({$tbl_clients}.ct02,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 中文名 */        
                       or AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 中文名 */        
                       or AES_DECRYPT({$tbl_delivery_person}.dp02,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 中文名 */        
                       or {$tbl_route_h}.reh01 like '%{$que_str}%' /* 路線名稱 */                      
                      )
                ";
    }
    
    if(!empty($get_data['que_mealo_s_num'])) { // tw_meal_order.s_num
      $que_mealo_s_num = $get_data['que_mealo_s_num'];
      $que_mealo_s_num = $this->db->escape_like_str($que_mealo_s_num);
      $where .= " and {$tbl_work_log}.mealo_s_num = '{$que_mealo_s_num}'  /* tw_meal_order.s_num */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_work_log}.s_num
                from {$tbl_work_log}
                left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_work_log}.reh_s_num
                left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_work_log}.ct_s_num
                left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_work_log}.b_empno
                where {$where}
                group by {$tbl_work_log}.s_num
                order by {$tbl_work_log}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_work_log}.*,
                   {$tbl_route_h}.reh01
                   {$this->_aes_fd("clients")}
                   {$this->_aes_fd("delivery_person")}
            from {$tbl_work_log}
            left join {$tbl_route_h} on {$tbl_route_h}.s_num = {$tbl_work_log}.reh_s_num
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_work_log}.ct_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_work_log}.b_empno
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
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function save_add($work_log_pic=NULL , $acc_s_num=NUll) {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = null;
    if(NULL <> $acc_s_num) { // 如果從api來
      $data['b_empno'] = $acc_s_num;
      $data['WorkLogPicture'] = $work_log_pic;
    }
    else {
      $data['b_empno'] = $_SESSION['acc_s_num'];
    }
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    if(!$this->db->insert($tbl_work_log, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_add_api()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function save_add_api($work_log_pic , $verify_s_num) {
    $rtn_msg = 'ok';
    $v = $this->input->post();
    $data = NULL;
    $data['b_empno'] = $verify_s_num;
    $data['b_date'] = date('Y-m-d H:i:s');
    $data['ct_s_num'] = $v['ct_s_num'];
    $data['reh_s_num'] = $v['reh_s_num'];
    $data['wkl01'] = $v["WorkLogNote"];
    $data['wkl02'] = $work_log_pic;
    $data['wkl03_lat'] = $v['wkl03_lat'];
    $data['wkl03_lng'] = $v['wkl03_lng'];
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    if(!$this->db->insert($tbl_work_log, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }
  
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_work_log, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_work_log, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-05
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_work_log = $this->zi_init->chk_tbl_no_lang('work_log'); // 工作紀錄資料
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_work_log, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
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
  public function _aes_fd($fd_tbl) {
    $aes_fd = "";
    $aes_col = NULL;
    switch($fd_tbl) {
      case "clients":
        $aes_col = array('ct01', 'ct02');
        $fd_tbl = $this->zi_init->chk_tbl_no_lang("clients"); // 案主本資料
        break;
      case "delivery_person":
        $aes_col = array('dp01', 'dp02');
        $fd_tbl = $this->zi_init->chk_tbl_no_lang("delivery_person"); // 外送員基本資料檔
        break;
    }
    foreach ($aes_col as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$fd_tbl}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
}
?>