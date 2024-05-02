<?php
class Album_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }

  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料(s_num)
  //  程式設計: Tony
  //  設計日期: 2018/8/14
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_album = $this->zi_init->chk_tbl_no_lang('album'); // 相本管理
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_album}.*
            from {$tbl_album}
            where {$tbl_album}.d_date is null
                  and {$tbl_album}.s_num = ?
            order by {$tbl_album}.s_num
           ";
    //echo $sql;
    //echo '<hr>';
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得所有資料()
  //  程式設計: Tony
  //  設計日期: 2018/8/14
  // **************************************************************************
  public function get_all() {
    $tbl_album = $this->zi_init->chk_tbl_no_lang('album'); // 相本管理
    $data = NULL;
    $sql = "select {$tbl_album}.*
              from {$tbl_album}
             where {$tbl_album}.d_date is null
             order by s_num
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
  //  函數名稱: get_que()
  //  函數功能: 取得news資料()
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_que($que=NULL,$pg=NULL) {
    $que = rawurldecode($que); // 網址中文要轉換
    $que = $this->db->escape_like_str($que);
    $tbl_album = $this->zi_init->chk_tbl_no_lang('album'); // 相本管理
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account');
    
    $where = "{$tbl_album}.d_date is null ";
    if(''<>$que) {
      $where .= " and ({$tbl_album}.album_title like '%{$que}%'
                      )
                ";
    }
    // 計算總筆數
    $sql_cnt = "select count(*) as cnt
                  from {$tbl_album}, {$tbl_sys_account}
                 where $where
                 order by {$tbl_album}.b_date desc
               ";
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->row(); 

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_album}.*,
                   {$tbl_sys_account}.acc_name
            from {$tbl_album}
            left join {$tbl_sys_account} on {$tbl_album}.b_empno = {$tbl_sys_account}.s_num
            where $where
            order by {$tbl_album}.b_date desc
            $limit
           ";
    //u_var_dump($sql);
    //echo '<hr>';
    $rs = $this->db->query($sql);
    if($rs->num_rows() > 0) { // 有資料才執行
      foreach ($rs->result() as $row){
        // 將所有欄位讀取到 $data
        foreach ($row as $fd_name => $v){
          $data[$row->s_num][$fd_name] = $row->$fd_name;
        }
      }
    }
    return(array($data,$row_cnt->cnt));
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_album = $this->zi_init->chk_tbl_no_lang('album'); // 相本管理
    if(!$this->db->insert($tbl_album, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_album = $this->zi_init->chk_tbl_no_lang('album'); // 相本管理
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_album, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: Tony
  //  設計日期: 2018/5/11
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];

    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_album = $this->zi_init->chk_tbl_no_lang('album'); // 相本管理
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_album, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: Tony
  //  設計日期: 2017/7/21
  // **************************************************************************
  public function del() {
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_album = $this->zi_init->chk_tbl_no_lang('album'); 
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_album, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
}
?>