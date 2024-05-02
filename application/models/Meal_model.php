<?php
class Meal_model extends CI_Model {
  public function __construct() {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_one()
  //  函數功能: 取得單筆資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 登入帳號
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_meal}.*,
                   sys_acc.acc_name as b_acc_name,
                   sys_acc2.acc_name as e_acc_name,
                   case {$tbl_meal}.is_available
                     when '0' then '停用'
                     when '1' then '啟用'
                   end as is_available_str,
                   sub_meal.ml01 as sub_ml01
            from {$tbl_meal}
            left join {$tbl_account} sys_acc on sys_acc.s_num = {$tbl_meal}.b_empno
            left join {$tbl_account} sys_acc2 on sys_acc2.s_num = {$tbl_meal}.e_empno
            left join {$tbl_meal} sub_meal on sub_meal.s_num = {$tbl_meal}.ml06_ml_s_num
            where {$tbl_meal}.d_date is null
                  and {$tbl_meal}.s_num = ?
            order by {$tbl_meal}.s_num desc
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
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function chk_duplicate($fd_name) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $fd_name = $this->db->escape_like_str($fd_name);
    $row = NULL;
    $sql = "select {$tbl_meal}.*
            from {$tbl_meal}
            where {$tbl_meal}.d_date is null
                  and {$tbl_meal}.fd_name = ?
            order by {$tbl_meal}.s_num desc
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
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function get_all() {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $data = NULL;
    $sql = "select {$tbl_meal}.*
            from {$tbl_meal}
            where {$tbl_meal}.d_date is null
            order by {$tbl_meal}.ml05 asc
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
  //  設計日期: 2021-11-24
  // **************************************************************************
  public function get_all_is_available() {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $data = NULL;
    $sql = "select {$tbl_meal}.*
            from {$tbl_meal}
            where {$tbl_meal}.d_date is null
                  and {$tbl_meal}.is_available = 1
            order by {$tbl_meal}.ml05 asc
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
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function get_que($q_str=NULL,$pg=NULL) {
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $where = " {$tbl_meal}.d_date is null ";
    $order = " {$tbl_meal}.ml05 asc ";
    $get_data = $this->input->get();
    if(!empty($get_data['que_str'])) { // 全文檢索
      $que_str = $get_data['que_str'];
      $where .= " and ({$tbl_meal}.MealName like '%{$que_str}%' /* 餐點名稱 */
                       or {$tbl_meal}.MealContent like '%{$que_str}%' /* 餐點內容 */
                       or {$tbl_meal}.MealPrices like '%{$que_str}%' /* 餐點價格 */
                      )
                ";
    }
    
    if(!empty($get_data['que_MealName'])) { // 餐點名稱
      $que_MealName = $get_data['que_MealName'];
      $que_MealName = $this->db->escape_like_str($que_MealName);
      $where .= " and {$tbl_meal}.MealName like '%{$que_MealName}%'  /* 餐點名稱 */ ";
    }
    if(!empty($get_data['que_MealContent'])) { // 餐點內容
      $que_MealContent = $get_data['que_MealContent'];
      $que_MealContent = $this->db->escape_like_str($que_MealContent);
      $where .= " and {$tbl_meal}.MealContent like '%{$que_MealContent}%'  /* 餐點內容 */ ";
    }
    if(!empty($get_data['que_MealPrices'])) { // 餐點價格
      $que_MealPrices = $get_data['que_MealPrices'];
      $que_MealPrices = $this->db->escape_like_str($que_MealPrices);
      $where .= " and {$tbl_meal}.MealPrices = '{$que_MealPrices}'  /* 餐點價格 */ ";
    }

    if(''<>$_SESSION[$q_str]['que_order_fd_name']) { // 排序欄位
      $que_order_fd_name = $_SESSION[$q_str]['que_order_fd_name'];
      $que_order_kind = $this->db->escape_like_str($_SESSION[$q_str]['que_order_kind']);
      $order = " {$que_order_fd_name} {$que_order_kind} ";
    }

    // 計算查詢條件後的總筆數
    $sql_cnt = "select {$tbl_meal}.s_num
                from {$tbl_meal}
                where $where
                group by {$tbl_meal}.s_num
                order by {$tbl_meal}.s_num
               ";
    //u_var_dump($sql_cnt);
    $rs_cnt = $this->db->query($sql_cnt, array($sql_cnt));
    $row_cnt = $rs_cnt->num_rows();

    $data = NULL;
    $limit_s = (($pg-1)*PG_QTY);
    $limit_e = PG_QTY;
    $limit = "limit {$limit_s},{$limit_e}";
    $sql = "select {$tbl_meal}.*,
                   sub_meal.ml01 as sub_ml01
            from {$tbl_meal}
            left join {$tbl_meal} sub_meal on sub_meal.s_num = {$tbl_meal}.ml06_ml_s_num
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
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    if(!$this->db->insert($tbl_meal, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    echo $rtn_msg;
    return;
  }

  // **************************************************************************
  //  函數名稱: save_upd()
  //  函數功能: 修改儲存資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function save_upd() {
    $rtn_msg = 'ok';
    $data = $this->input->post();
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $this->db->where('s_num', $data['s_num']);
    if(!$this->db->update($tbl_meal, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: save_is_available()
  //  函數功能: 儲存上下架
  //  程式設計: kiwi
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function save_is_available() {
    $rtn_msg = 'ok';
    $f_s_num = $_POST['f_s_num'];
    $f_is_available = $_POST['f_is_available'];
    $data['is_available'] = $f_is_available;
    $data['e_empno'] = $_SESSION['acc_s_num'];
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $this->db->where('s_num', $f_s_num);
    if(!$this->db->update($tbl_meal, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: kiwi
  //  設計日期: 2020-11-23
  // **************************************************************************
  public function del() {
    $data = NULL;
    $v = $this->input->post();
    $rtn_msg = 'ok';
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_meal = $this->zi_init->chk_tbl_no_lang('meal'); // 餐點資料
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_meal, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
}
?>