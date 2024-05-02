<?php
class Work_q_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd1 = array('dp01','dp02'); // 加密欄位
  public $aes_fd2 = array('ct_name'); // 加密欄位
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_work_q_h}.*,
                   {$tbl_questionnaire_h}.qh01,
                   sys_acc.acc_name as b_acc_name,
                   {$tbl_service_case}.ct_name,
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
                   case {$tbl_work_q_h}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
                   {$this->_aes_fd1()}
                   {$this->_aes_fd2('service_case')}
            from {$tbl_work_q_h}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_work_q_h}.b_empno
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_work_q_h}.sec_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_work_q_h}.e_empno
            left join {$tbl_questionnaire_h} on {$tbl_questionnaire_h}.s_num = {$tbl_work_q_h}.qh_s_num
            where {$tbl_work_q_h}.d_date is null
                  and {$tbl_work_q_h}.s_num = ?
            order by {$tbl_work_q_h}.s_num desc
          ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
      foreach ($this->aes_fd2 as $k => $v) {
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
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_work_q_h}.*
            from {$tbl_work_q_h}
            where {$tbl_work_q_h}.d_date is null
                  and {$tbl_work_q_h}.fd_name = ?
            order by {$tbl_work_q_h}.s_num desc
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
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function get_all() {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $data = NULL;
    $sql = "select {$tbl_work_q_h}.*
            from {$tbl_work_q_h}
            where {$tbl_work_q_h}.d_date is null
            order by {$tbl_work_q_h}.s_num desc
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
  //  函數名稱: get_all_by_date()
  //  函數功能: 取得今天的問卷資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function get_all_by_date($produce_date) {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $data = NULL;
    $sql = "select {$tbl_work_q_h}.*
            from {$tbl_work_q_h}
            where {$tbl_work_q_h}.d_date is null
                  and {$tbl_work_q_h}.dys01 = '{$produce_date}'
            order by {$tbl_work_q_h}.s_num desc
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
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭

    $today = date("Y-m-d");
    $where = " {$tbl_work_q_h}.d_date is null
               and {$tbl_work_q_h}.dys01 <= '{$today}'
            ";

    $order = " {$tbl_work_q_h}.dys01 desc, {$tbl_work_q_h}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_work_q_h}.dys_s_num like '%{$que_str}%' /* tw_daily_shipment.s_num */
                       or {$tbl_work_q_h}.qh_s_num like '%{$que_str}%' /* tw_questionnaire_h.s_num */
                       or {$tbl_work_q_h}.b_date like binary '%{$que_str}%' /* tw_questionnaire_h.s_num */
                       or {$tbl_work_q_h}.dys01 like binary '%{$que_str}%' /* 問卷日期 */
                       or AES_DECRYPT({$tbl_service_case}.ct_name,'{$this->db_crypt_key2}') like '%{$que_str}%' /* 案主名稱 */           
                      )
                ";
    }

    if(!empty($get_data['que_reh_s_num'])) { // tw_daily_shipment.s_num
      $que_reh_s_num = $get_data['que_reh_s_num'];
      $que_reh_s_num = $this->db->escape_like_str($que_reh_s_num);
      $where .= " and {$tbl_work_q_h}.reh_s_num = '{$que_reh_s_num}'  /* tw_daily_shipment.s_num */ ";
    }
    if(!empty($get_data['que_dys_s_num'])) { // tw_daily_shipment.s_num
      $que_dys_s_num = $get_data['que_dys_s_num'];
      $que_dys_s_num = $this->db->escape_like_str($que_dys_s_num);
      $where .= " and {$tbl_work_q_h}.dys_s_num = '{$que_dys_s_num}'  /* tw_daily_shipment.s_num */ ";
    }
    if(!empty($get_data['que_qh_s_num'])) { // tw_questionnaire_h.s_num
      $que_qh_s_num = $get_data['que_qh_s_num'];
      $que_qh_s_num = $this->db->escape_like_str($que_qh_s_num);
      $where .= " and {$tbl_work_q_h}.qh_s_num = '{$que_qh_s_num}'  /* tw_questionnaire_h.s_num */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_work_q_h}.s_num
                from {$tbl_work_q_h}
                left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_work_q_h}.sec_s_num
                left join {$tbl_questionnaire_h} on {$tbl_questionnaire_h}.s_num = {$tbl_work_q_h}.qh_s_num
                left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_work_q_h}.e_empno
                where $where
                group by {$tbl_work_q_h}.s_num
                order by {$tbl_work_q_h}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_work_q_h}.*,
                   {$tbl_questionnaire_h}.qh01,
                   {$tbl_service_case}.ct_name,
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
                   end as sec04_str
                   {$this->_aes_fd1()}
                   {$this->_aes_fd2('service_case')}
            from {$tbl_work_q_h}
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_work_q_h}.sec_s_num
            left join {$tbl_questionnaire_h} on {$tbl_questionnaire_h}.s_num = {$tbl_work_q_h}.qh_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_work_q_h}.e_empno
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
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    if(!$this->db->insert($tbl_work_q_h, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_work_q_h, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_work_q_h, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_work_q_h, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
    // **************************************************************************
  //  函數名稱: get_work_q_h()
  //  函數功能: 取得檔頭資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function get_work_q_h() {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h');    // 工作問卷-檔頭
    $tbl_service_case = $this->zi_init->chk_tbl_no_lang('service_case'); // 開案服務
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷-檔頭

    $where = " {$tbl_work_q_h}.d_date is null ";
    $order = " {$tbl_work_q_h}.reb01 asc, {$tbl_work_q_h}.sec_s_num asc, {$tbl_work_q_h}.dys01 asc";
    $get_data = $this->input->get();
    if(!empty($get_data['que_reh_s_num'])) { // tw_daily_shipment.s_num
      if('all' != $get_data['que_reh_s_num']) {
        $que_reh_s_num = $get_data['que_reh_s_num'];
        $que_reh_s_num = $this->db->escape_like_str($que_reh_s_num);
        $where .= " and {$tbl_work_q_h}.reh_s_num = '{$que_reh_s_num}'  /* tw_daily_shipment.s_num */ ";
      }
    }
    if(!empty($get_data['que_ct_name'])) { // 案主名稱
      if('all' != $get_data['que_ct_name']) {
        $que_ct_name = $get_data['que_ct_name'];
        $que_ct_name = $this->db->escape_like_str($que_ct_name);
        $where .= " and AES_DECRYPT({$tbl_service_case}.ct_name,'{$this->db_crypt_key2}') like '%{$que_ct_name}%'";
      }
    }
    if(!empty($get_data['que_dys_s_num'])) { // tw_daily_shipment.s_num
      $que_dys_s_num = $get_data['que_dys_s_num'];
      $que_dys_s_num = $this->db->escape_like_str($que_dys_s_num);
      $where .= " and {$tbl_work_q_h}.dys_s_num = '{$que_dys_s_num}'  /* tw_daily_shipment.s_num */ ";
    }
    if(!empty($get_data['que_dys01_start'])) { // 問卷查詢開始日期
      $que_dys01_start = $get_data['que_dys01_start'];
      $que_dys01_start = $this->db->escape_like_str($que_dys01_start);
      $where .= " and {$tbl_work_q_h}.dys01 >= '{$que_dys01_start}'  /* 問卷日期 */ ";
    }
    if(!empty($get_data['que_dys01_end'])) { // 問卷查詢結束日期
      $que_dys01_end = $get_data['que_dys01_end'];
      $que_dys01_end = $this->db->escape_like_str($que_dys01_end);
      $where .= " and {$tbl_work_q_h}.dys01 <= '{$que_dys01_end}'  /* 問卷日期 */ ";
    }

    $data = NULL;
    $sql = "select {$tbl_work_q_h}.*,
                   {$tbl_questionnaire_h}.qh01,
                   {$tbl_service_case}.ct_name,
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
                   end as sec04_str
                   {$this->_aes_fd1()}
                   {$this->_aes_fd2('service_case')}
            from {$tbl_work_q_h}
            left join {$tbl_service_case} on {$tbl_service_case}.s_num = {$tbl_work_q_h}.sec_s_num
            left join {$tbl_questionnaire_h} on {$tbl_questionnaire_h}.s_num = {$tbl_work_q_h}.qh_s_num
            left join {$tbl_delivery_person} on {$tbl_delivery_person}.s_num = {$tbl_work_q_h}.e_empno
            where {$where}
            order by {$order}
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
  //  函數名稱: get_work_q_b()
  //  函數功能: 取得檔身資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function get_work_q_b($wqh_s_num) {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h');    // 工作問卷-檔頭
    $tbl_work_q_b = $this->zi_init->chk_tbl_no_lang('work_q_b'); // 工作問卷-檔身
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $tbl_questionnaire_b = $this->zi_init->chk_tbl_no_lang('questionnaire_b'); // 問卷基本資料-檔身
    $data = NULL;
    $sql = "select {$tbl_work_q_b}.*,
                   qb_2.qb_order,
                   qb_2.qb01,
                   qb_2.qb02,
                   qb_2.qb03,
                   case qb_2.qb02
                     when '1' then '單選題'
                     when '2' then '複選題'
                     when '3' then '問答題'
                   end as qb02_str
            from {$tbl_work_q_b}
            left join {$tbl_work_q_h} on {$tbl_work_q_h}.s_num = {$tbl_work_q_b}.wqh_s_num
            left join {$tbl_questionnaire_h} on {$tbl_questionnaire_h}.s_num = {$tbl_work_q_b}.qh_s_num
            left join {$tbl_questionnaire_b} qb_1 on qb_1.qh_s_num = {$tbl_questionnaire_h}.s_num
            left join {$tbl_questionnaire_b} qb_2 on qb_2.s_num = {$tbl_work_q_b}.qb_s_num
            where {$tbl_work_q_h}.d_date is null
            and {$tbl_work_q_b}.wqh_s_num = ?
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($wqh_s_num));
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
  //  函數名稱: get_daily_work_q
  //  函數功能: 獲取每日工作問卷
  //  程式設計: Kiwi
  //  設計日期: 2021/01/02
  // **************************************************************************
  public function get_daily_work_q($dp_s_num) {
    $today = date("Y-m-d");
    $tbl_clients = $this->zi_init->chk_tbl_no_lang('clients'); // 案主資料
    $tbl_daily_shipment = $this->zi_init->chk_tbl_no_lang('daily_shipment'); // 每日配送單資料
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷-檔頭
    $tbl_questionnaire_b = $this->zi_init->chk_tbl_no_lang('questionnaire_b'); // 問卷-檔身
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 問卷基本資料-檔頭
    $data = NULL;

    $where = '';
    if(isset($_POST['type'])) {
      $where = " and {$tbl_daily_shipment}.dys09 = '{$_POST['type']}' ";
    }

    $sql = "select {$tbl_work_q_h}.s_num,
                   {$tbl_questionnaire_h}.s_num as qh_s_num,
                   {$tbl_questionnaire_h}.qh01,
                   {$tbl_questionnaire_b}.s_num as qb_s_num,
                   {$tbl_questionnaire_b}.qb01,
                   {$tbl_questionnaire_b}.qb02,
                   {$tbl_questionnaire_b}.qb03,
                   {$tbl_questionnaire_b}.qb_order,
                   {$tbl_daily_shipment}.ct_name,
                   {$tbl_daily_shipment}.ct_s_num,
                   {$tbl_daily_shipment}.sec_s_num,
                   {$tbl_daily_shipment}.dys02
                   {$this->_aes_fd2('daily_shipment')}
            from {$tbl_daily_shipment}
            left join {$tbl_work_q_h} on {$tbl_work_q_h}.dys_s_num = {$tbl_daily_shipment}.s_num
            left join {$tbl_questionnaire_h} on {$tbl_questionnaire_h}.s_num = {$tbl_work_q_h}.qh_s_num
            left join {$tbl_questionnaire_b} on {$tbl_questionnaire_b}.qh_s_num = {$tbl_questionnaire_h}.s_num
            where {$tbl_daily_shipment}.dp_s_num = {$dp_s_num}
                  and {$tbl_daily_shipment}.dys01 = '{$today}'
                  {$where}
            order by {$tbl_work_q_h}.reh_s_num asc, 
                     {$tbl_work_q_h}.reb01 asc,
                     {$tbl_daily_shipment}.dys02 desc,
                     {$tbl_questionnaire_b}.qb_order asc
          ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $data = $rs->result_array();
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: save_daily_work_h()
  //  函數功能: 新增儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function save_daily_work_h($work_h_data_batch) {
    if($work_h_data_batch != NULL) {
      $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
      if(!$this->db->insert_batch($tbl_work_q_h, $work_h_data_batch)) {
        return false;
      }
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: upd_daily_work_h()
  //  函數功能: 更新儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-03
  // **************************************************************************
  public function upd_daily_work_h($upd_data) {
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷
    if(!$this->db->update_batch($tbl_work_q_h, $upd_data, 's_num')) {
      return false;
    }
    return true;
  }
  // **************************************************************************
  //  函數名稱: save_daily_work_q()
  //  函數功能: 儲存問卷資料
  //  程式設計: kiwi
  //  設計日期: 2020-12-28
  // **************************************************************************
  public function save_daily_work_q($dp_s_num) {
    $rtn_msg = 'ok';
    $data = json_decode(file_get_contents('php://input'), true);
    $get_data = $data['work_data'];
     
    $tbl_work_q_h = $this->zi_init->chk_tbl_no_lang('work_q_h'); // 工作問卷-單頭
    $tbl_work_q_b = $this->zi_init->chk_tbl_no_lang('work_q_b'); // 工作問卷-單身
    $work_h_data = NULL;
    $work_b_data = NULL;
    
    // 先跑完單頭，並且清除單身資料
    foreach($get_data as $k => $v) {
      $work_h_data["e_empno"] = $dp_s_num;
      $work_h_data["e_date"] = date('Y-m-d H:i:s');
      $this->db->where("s_num" , $v["wqh_s_num"]);
      if(!$this->db->update($tbl_work_q_h, $work_h_data)) {
        $rtn_msg = $this->lang->line('upd_ng');
      }
     
      // 刪除舊的資料
      $this->db->where('wqh_s_num', $v["wqh_s_num"]);
      if(!$this->db->delete($tbl_work_q_b)) {
        $rtn_msg = $this->lang->line('del_ng');
        echo $rtn_msg;
        return;
      }
    }
    
    // 再把單身資料更新上去
    foreach($get_data as $k => $v) {  
      $work_b_data["b_empno"] = $dp_s_num;
      $work_b_data["b_date"] = date('Y-m-d H:i:s');
      $work_b_data["wqh_s_num"] = $v["wqh_s_num"];
      $work_b_data["qh_s_num"] = $v["qh_s_num"];
      $work_b_data["qb_s_num"] = $v["qb_s_num"];
      $work_b_data["wqb01"] = $v["wqb01"];
      $work_b_data["wqb99"] = $v["wqb99"];
      if(!$this->db->insert($tbl_work_q_b, $work_b_data)) {
        $rtn_msg = $this->lang->line('add_ng');
      }
      else {
        if($v['qb_order'] == 3) { // 是否已發放代餐
          if(NULL != $v["wqb01"]) {
            $work_q_h_row = $this->get_one($v["wqh_s_num"]);
            if(NULL != $work_q_h_row) {
              if($v["wqb01"] == 0) { // 已發
                if(!$this->daily_work_model->update_mp_by_ct_mp04($dp_s_num , $work_q_h_row->sec_s_num , "Y" , NULL)) {
                  $rtn_msg = $this->lang->line('add_ng');
                }
              }
              else { // 未發
                if(!$this->daily_work_model->update_mp_by_ct_mp04($dp_s_num , $work_q_h_row->sec_s_num , "N" , $v["wqb01"])) {
                  $rtn_msg = $this->lang->line('add_ng');
                }
              }
            }
          }
        }
      }
    }
    echo $rtn_msg;
    return;
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
  // **************************************************************************
  //  函數名稱: _aes_fd1()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  public function _aes_fd1() {
    $tbl_delivery_person = $this->zi_init->chk_tbl_no_lang('delivery_person'); // 外送員基本資料檔
    $aes_fd1 = "";
    foreach ($this->aes_fd1 as $k_fd_name => $v_fd_name) {
      $aes_fd1 .= ",AES_DECRYPT({$tbl_delivery_person}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd1);
  }
  // **************************************************************************
  //  函數名稱: _aes_fd2()
  //  函數功能: 將加密欄位解密
  //  程式設計: kiwi
  //  設計日期: 2021-04-22
  // **************************************************************************
  private function _aes_fd2($fd_name) {
    $tbl_fd= $this->zi_init->chk_tbl_no_lang("{$fd_name}"); // 每日配送單資料|每日餐條
    $aes_fd = "";
    foreach ($this->aes_fd2 as $k_fd_name => $v_fd_name) {
      $aes_fd .= ",AES_DECRYPT({$tbl_fd}.{$v_fd_name},'{$this->db_crypt_key2}') as {$v_fd_name}\n";
    }
    return($aes_fd);
  }
}
?>