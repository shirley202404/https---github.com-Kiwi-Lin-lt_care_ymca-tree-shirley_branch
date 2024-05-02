<?php
class News_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }

  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得news單筆資料(s_num)
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_news}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name
            from {$tbl_news}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_news}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_news}.e_empno
            where {$tbl_news}.d_date is null
                  and {$tbl_news}.s_num = ?
                  and {$tbl_news}.news_type = 1
            order by {$tbl_news}.s_num
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
  //  函數功能: 取得News所有資料()
  //  程式設計: Tony
  //  設計日期: 2018/5/9
  // **************************************************************************
  public function get_all() {
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    $data = NULL;
    $sql = "select {$tbl_news}.*
            from {$tbl_news}
            where {$tbl_news}.d_date is null
            order by s_num
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
  //  函數功能: 取得news資料()
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_que($que=NULL,$pg=NULL) {
    $que = rawurldecode($que); // 網址中文要轉換
    $que = $this->db->escape_like_str($que);
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    $where = "{$tbl_news}.news_type = 1 /* 最新消息 */
              and {$tbl_news}.d_date is null
             ";
    if(''<>$que) {
      $where .= " and ({$tbl_news}.news_title like '%{$que}%'
                       or {$tbl_news}.news_content like '%{$que}%'
                      )
                ";
    }
    // 計算總筆數
    $sql_cnt = "select count(*) as cnt
                from {$tbl_news}
                where $where
                order by {$tbl_news}.news_publication desc
               ";
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->row(); 

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_news}.*
            from {$tbl_news}
            where $where
            order by {$tbl_news}.news_publication desc
            $limit
           ";
    //echo $sql;
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
  //  函數名稱: get_by_enter()
  //  函數功能: 取得news前四筆資料，入口頁使用
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_by_enter() {
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    $where = "{$tbl_news}.is_available = 1
              and {$tbl_news}.news_type = 1 /* 最新消息 */
              and {$tbl_news}.d_date is null
             ";
    $data = NULL;
    $sql = "select {$tbl_news}.*
            from {$tbl_news}
            where $where
            order by {$tbl_news}.news_publication desc
            limit 0,4
           ";
    //echo $sql;
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
    return $data;
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
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    if(!$this->db->insert($tbl_news, $data)) {
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
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_news, $data)) {
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
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_news, $data)) {
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
    $tbl_news = $this->zi_init->chk_tbl_no_lang('news'); // 最新消息
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_news, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
}
?>