<?php
class Rpt_pay_model extends CI_Model {
  public $db_crypt_key2 = DB_CRYPT_KEY2; // 敏感個人資料加密key
  public $aes_fd = array('ct01','ct02','ct03'); // 加密欄位
  public $chg_fd = array("ct34_go"); // 替換欄位
  public function __construct() {
    $this->load->database();
  }
    // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得案主路徑資料
  //  程式設計: kiwi
  //  設計日期: 2021-11-11
  // **************************************************************************
  public function get_ct_reh($sec_s_num , $ct_s_num) {
    $fd_tbl = '';
    $v = $this->input->post();
    switch ($v['rpt_type']) {
      case 'subsidy_1':
      case 'subsidy_2':
        $fd_tbl = 'rpt_pay_subsidy';
        break;
      case 'ownexpense':
        $fd_tbl = 'rpt_pay_ownexpense';
        break;
    }
    $tbl_fd = $this->zi_init->chk_tbl_no_lang("{$fd_tbl}"); // 繳費資料表
    $ct_s_num = $this->db->escape_like_str($ct_s_num);
    $sec_s_num = $this->db->escape_like_str($sec_s_num);
    $row = NULL;
    $sql = "select {$tbl_fd}.*
            from {$tbl_fd}
            where {$tbl_fd}.d_date is null
                  and {$tbl_fd}.sec_s_num = ?
                  and {$tbl_fd}.ct_s_num = ?
            order by {$tbl_fd}.b_date desc
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sec_s_num , $ct_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row();
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_rpt_data_by_month()
  //  函數功能: 取得查詢月份的繳費資料
  //  程式設計: kiwi
  //  設計日期: 2021-11-11
  // **************************************************************************
  public function get_rpt_data_by_month($type=NULL) {
    $tbl_clients = $this->zi_init->chk_tbl_no_lang("clients"); // 案主資料表
    $where = '';
    $group = '';
    $select = '';
    $fd_tbl = '';
    $fd_tbl_col = '';
    $rpt_type = NULL;
    $rpt_pay_month = NULL;
    $v = $this->input->post();
    $get_data = $this->input->get();
    if(NULL != $v) {
      $rpt_type = $v['rpt_type'];
      $rpt_pay_month = $v['rpt_pay_month'];
    }
    else {
      $rpt_type = $get_data['rpt_type'];
      $rpt_pay_month = $get_data['rpt_pay_month'];
    }
    
    if($rpt_type == 'subsidy_1' or $rpt_type == 'subsidy_2') {
      $fd_tbl_col = 's';
      $fd_tbl = 'rpt_pay_subsidy';
    }
    else {
      $fd_tbl_col = 'o';
      $fd_tbl = 'rpt_pay_ownexpense';
    }
    $tbl_fd = $this->zi_init->chk_tbl_no_lang("{$fd_tbl}"); // 繳費資料表
    
    if(!empty($get_data['f_que'])){ // 查詢案主姓名
      $where .= "and {$tbl_fd}.rp{$fd_tbl_col}02_ct_name like '%{$get_data['f_que']}%' ";
    }
    
    if('receipe' == $type){ // 同身分別、同案主午餐、晚餐資料放在同一張收據
      $group = "group by {$tbl_fd}.ct_s_num, {$tbl_clients}.ct34_go";
      $select = "SUM({$tbl_fd}.rp{$fd_tbl_col}05) as rp{$fd_tbl_col}05_total,
                 SUM({$tbl_fd}.rp{$fd_tbl_col}06) as rp{$fd_tbl_col}06_total,";
    }

    $data = NULL;
    if($rpt_type == 'subsidy_1') { // 長照案
      $where .= "and {$tbl_fd}.rps03_sec01 = 1 ";
      if('receipe' != $type){
        // 長照案繳費名冊不需顯示繳費金額為0元的案主
        $where .= "and {$tbl_clients}.ct34_go NOT IN (4,5)";
      }
    }
    if($rpt_type == 'subsidy_2') { // 公所案
      $where .= "and {$tbl_fd}.rps03_sec01 = 2";
    }
    
    $sql = "select {$tbl_fd}.*,
                   {$select}
                   {$tbl_clients}.ct03,
                   {$tbl_clients}.ct20,
                   {$tbl_clients}.ct34_go,
                   {$tbl_clients}.ct37
                   {$this->_aes_fd('clients')}
            from {$tbl_fd}
            left join {$tbl_clients} on {$tbl_clients}.s_num = {$tbl_fd}.ct_s_num
            where {$tbl_fd}.d_date is null
                  and {$tbl_fd}.rp{$fd_tbl_col}01 like '%{$rpt_pay_month}%'
                  {$where}
            {$group}
            order by {$tbl_fd}.ct_s_num asc, {$tbl_fd}.rp{$fd_tbl_col}04_reh01 asc
           ";
    // u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
          list($fd_name,$fd_val) = $this->_replace_text($row,$fd_name);
          $data[$row->s_num][$fd_name] = $fd_val;
        }
      }
    }
    return $data;
  }
  // **************************************************************************
  //  函數名稱: save_rpt_data()
  //  函數功能: 儲存繳費資料
  //  程式設計: kiwi
  //  設計日期: 2021-09-22
  // **************************************************************************
  public function save_rpt_data($rpt_data) {
    $fd_tbl = ''; 
    $fd_tbl_col = ''; 
    $v = $this->input->post();
    if($v['rpt_type'] == 'subsidy_1' or $v['rpt_type'] == 'subsidy_2') {
      $fd_tbl = "rpt_pay_subsidy";
      $fd_tbl_col = 's';
    }
    else {
      $fd_tbl = "rpt_pay_ownexpense";
      $fd_tbl_col = 'o';
    }

    $tbl_fd = $this->zi_init->chk_tbl_no_lang($fd_tbl); // 繳費資料表
    if($v['rpt_type'] == 'subsidy_1') { // 補助戶(長照案)
      $this->db->where("rps03_sec01", 1);
    }
    if($v['rpt_type'] == 'subsidy_2') { // 補助戶(公所案)
      $this->db->where("rps03_sec01", 2);
    }
    $this->db->where("rp{$fd_tbl_col}01", $v['rpt_pay_month']);
    if(!$this->db->delete("{$tbl_fd}")) {
      return FALSE;
    }
    if(!$this->db->insert_batch($tbl_fd, $rpt_data)) {
      return FALSE;
    }
    return TRUE;
  }
  // **************************************************************************
  //  函數名稱: upd_reh()
  //  函數功能: 修改路徑資料
  //  程式設計: kiwi
  //  設計日期: 2021-11-11
  // **************************************************************************
  public function upd_reh() {
    $fd_tbl = ''; 
    $fd_tbl_col = ''; 
    $v = $this->input->post();
    if($v['rpt_type'] == 'subsidy_1' or $v['rpt_type'] == 'subsidy_2') {
      $fd_tbl_col = 's';
      $fd_tbl = "rpt_pay_subsidy";
    }
    else {
      $fd_tbl_col = 'o';
      $fd_tbl = "rpt_pay_ownexpense";
    }
    $tbl_fd = $this->zi_init->chk_tbl_no_lang("{$fd_tbl}");
    $rtn_msg = 'ok';
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    if(!empty($v['reh_s_num'])){
      $data['reh_s_num'] = $v['reh_s_num'];
    }
    $data[$v['field']] = $v['reh_text'];
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_fd, $data)) {
      $rtn_msg = $this->lang->line('upd_ng'); // 修改失敗!!
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: _aes_fd()
  //  函數功能: 將加密欄位解密
  //  程式設計: shirley
  //  設計日期: 2024-02-24
  // **************************************************************************
  private function _aes_fd($fd_tbl) {
    switch ($fd_tbl) {     
      case "clients":
        $encry_arr = $this->aes_fd;
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
      case 'ct34_go':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("ct34_go", $fd_val);
        break;
      case 'ct37':
        $fd_name_mask = "{$fd_name}_str";
        $fd_val = hlp_opt_setup("ct37", $fd_val);
        break;
    }

    if('' != $fd_name_mask) {
      $fd_name = $fd_name_mask;
    }

    return(array($fd_name, $fd_val));
  }
}
?>