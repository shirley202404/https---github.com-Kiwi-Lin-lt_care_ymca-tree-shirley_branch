<?php
class Questionnaire_model extends CI_Model {
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_questionnaire_h}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_questionnaire_h}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str
            from {$tbl_questionnaire_h}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_questionnaire_h}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_questionnaire_h}.e_empno
            where {$tbl_questionnaire_h}.d_date is null
                  and {$tbl_questionnaire_h}.s_num = ?
            order by {$tbl_questionnaire_h}.s_num desc
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
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_questionnaire_h}.*
            from {$tbl_questionnaire_h}
            where {$tbl_questionnaire_h}.d_date is null
                  and {$tbl_questionnaire_h}.fd_name = ?
            order by {$tbl_questionnaire_h}.s_num desc
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
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function get_all() {
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $data = NULL;
    $sql = "select {$tbl_questionnaire_h}.*
            from {$tbl_questionnaire_h}
            where {$tbl_questionnaire_h}.d_date is null
            order by {$tbl_questionnaire_h}.s_num desc
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
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $where = " {$tbl_questionnaire_h}.d_date is null ";
    $order = " {$tbl_questionnaire_h}.s_num desc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_questionnaire_h}.qh01 like '%{$que_str}%' /* 問卷名稱 */
                       or {$tbl_questionnaire_h}.qh10 like '%{$que_str}%' /* 備註事項 */
                      )
                ";
    }
    
    if(!empty($get_data['que_qh01'])) { // 問卷名稱
      $que_qh01 = $get_data['que_qh01'];
      $que_qh01 = $this->db->escape_like_str($que_qh01);
      $where .= " and {$tbl_questionnaire_h}.qh01 = '{$que_qh01}'  /* 問卷名稱 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_questionnaire_h}.s_num
                from {$tbl_questionnaire_h}
                where $where
                group by {$tbl_questionnaire_h}.s_num
                order by {$tbl_questionnaire_h}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_questionnaire_h}.*
            from {$tbl_questionnaire_h}
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
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function save_add() {
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $tbl_questionnaire_b = $this->zi_init->chk_tbl_no_lang('questionnaire_b'); // 問卷基本資料-檔身
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $questionnaire_h_data = $data['questionnaire_h'];
    $questionnaire_b_data = $data['questionnaire_b'];
    $questionnaire_h_data['b_empno'] = $_SESSION['acc_s_num'];
    $questionnaire_h_data['b_date'] = date('Y-m-d H:i:s');
    $tbl_part_out_h = $this->zi_init->chk_tbl_no_lang('part_out_h'); // 問卷基本資料-檔頭
    if(!$this->db->insert($tbl_questionnaire_h, $questionnaire_h_data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    else {
      $qh_s_num = $this->db->insert_id(); // tw_questionnaire_h.s_num
      // 處理問卷-檔身 Begin //
      if(isset($questionnaire_b_data)) { // 有資料才處理
        $i=0;
        foreach ($questionnaire_b_data['s_num'] as $k => $v) {
          foreach ($questionnaire_b_data as $k2 => $v2) {
            $questionnaire_b_data_batch[$i][$k2]=$questionnaire_b_data[$k2][$k];
          }
          
          $questionnaire_b_data_batch[$i]['qh_s_num'] = $qh_s_num;
          $questionnaire_b_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
          $questionnaire_b_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
          // 如果欄位都沒有輸入資料,就不儲存
          if('' == $questionnaire_b_data_batch[$i]['qb01'] and '' == $questionnaire_b_data_batch[$i]['qb03'] ) {
            unset($questionnaire_b_data_batch[$i]);
          }
          unset($questionnaire_b_data_batch[$i]['s_num']); // s_num 要取消,不然會重複,導致儲存失敗
          $i++;
        } 
        
        if(!$this->db->insert_batch($tbl_questionnaire_b , $questionnaire_b_data_batch)) {
          $rtn_msg = $this->lang->line('add_ng');
        }
      }
      // 處理問卷-檔身 End //
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $tbl_questionnaire_b = $this->zi_init->chk_tbl_no_lang('questionnaire_b'); // 問卷基本資料-檔身
    $data = $this->input->post();
    $questionnaire_h_data = $data['questionnaire_h'];
    $questionnaire_b_data = $data['questionnaire_b'];
    $qh_s_num = $questionnaire_h_data['s_num']; // tw_material_h.s_num
    $questionnaire_h_data['e_empno'] = $_SESSION['acc_s_num'];
    $questionnaire_h_data['e_date'] = date('Y-m-d H:i:s');
    
    $this->db->where('s_num', $qh_s_num);
    if(!$this->db->update($tbl_questionnaire_h, $questionnaire_h_data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }

    // 刪除
    $this->db->where('qh_s_num', $qh_s_num);
    if(!$this->db->delete($tbl_questionnaire_b)) {
      $rtn_msg = $this->lang->line('del_ng');
      echo $rtn_msg;
      return;
    }
    
    if(NULL != $questionnaire_b_data) { // 有資料才處理
      $i=0;
      foreach ($questionnaire_b_data['s_num'] as $k => $v) {
        foreach ($questionnaire_b_data as $k2 => $v2) {
          $questionnaire_b_data_batch[$i][$k2]=$questionnaire_b_data[$k2][$k];
        }
        $questionnaire_b_data_batch[$i]['qh_s_num'] = $qh_s_num;
        $questionnaire_b_data_batch[$i]['b_empno'] = $_SESSION['acc_s_num'];
        $questionnaire_b_data_batch[$i]['b_date'] = date('Y-m-d H:i:s');
        // 如果欄位都沒有輸入資料,就不儲存
        if('' == $questionnaire_b_data_batch[$i]['qb01'] and '' == $questionnaire_b_data_batch[$i]['qb03'] ) {
          unset($questionnaire_b_data_batch[$i]);
        }
        unset($questionnaire_b_data_batch[$i]['s_num']);
        $i++;
      }
      //u_var_dump($material_b_data_batch);
      //exit;
      if(!$this->db->insert_batch($tbl_questionnaire_b, $questionnaire_b_data_batch)) {
        $rtn_msg = $this->lang->line('add_ng');
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_questionnaire_h, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2021-01-02
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_questionnaire_h = $this->zi_init->chk_tbl_no_lang('questionnaire_h'); // 問卷基本資料-檔頭
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_questionnaire_h, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: get_questionnaire_b()
  //  函數功能: 列出單身明細資料
  //  程式設計: Kiwi
  //  設計日期: 2021/01/02
  // **************************************************************************
  public function get_questionnaire_b($s_num) {
    $tbl_questionnaire_b = $this->zi_init->chk_tbl_no_lang('questionnaire_b'); // 問卷基本資料-檔身
    $data = NULL;
    $sql = "select {$tbl_questionnaire_b}.*,
                 case {$tbl_questionnaire_b}.qb02
                   when '1' then '單選題'
                   when '2' then '複選題'
                   when '3' then '問答題'
                 end as qb02_str
                 from {$tbl_questionnaire_b}
                 where {$tbl_questionnaire_b}.qh_s_num = {$s_num}
                 order by {$tbl_questionnaire_b}.qb_order asc
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
}
?>