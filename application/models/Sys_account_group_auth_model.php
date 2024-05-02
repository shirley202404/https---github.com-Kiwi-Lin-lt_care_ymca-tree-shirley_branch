<?php
class Sys_account_group_auth_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_auth()
  //  函數功能: 取得該群組權限
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_auth($group_s_num) {
    $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 檢查多語系的table
    $data = NULL;
    $sql = "select *
            from {$tbl_account_group_auth}
            where d_date is null
                  and group_s_num = ?
            order by s_num
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($group_s_num));
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
  //  函數名稱: get_agu_open()
  //  函數功能: 取得群組該menu權限
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_agu_open($group_s_num,$menu_s_num) {
    $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 檢查多語系的table
    $row = NULL;
    $sql = "select *
            from {$tbl_account_group_auth}
            where d_date is null
                  and group_s_num = ?
                  and menu_s_num = ?
            order by s_num
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($group_s_num,$menu_s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: get_agu_open_by_acc()
  //  函數功能: 取得目前使用者使用的CT使用權限
  //  程式設計: Tony
  //  設計日期: 2018/8/17
  // **************************************************************************
  public function get_agu_open_by_acc() {
    $crypt_key = DB_CRYPT_KEY; // DB加密key
    $ct_name = $this->router->fetch_class();
    $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 檢查多語系的table
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 檢查多語系的table
    $row = NULL;
    $agu_open = NULL;
    $sql = "select {$tbl_account_group_auth}.*
            from {$tbl_account_group_auth}
            left join {$tbl_sys_menu} on {$tbl_sys_menu}.s_num = {$tbl_account_group_auth}.menu_s_num
            where {$tbl_sys_menu}.d_date is null
                  and AES_DECRYPT({$tbl_sys_menu}.sys_menu_ct,'{$crypt_key}') = '{$ct_name}'
                  and {$tbl_account_group_auth}.d_date is null
                  and {$tbl_account_group_auth}.group_s_num = {$_SESSION['group_s_num']}
            order by {$tbl_account_group_auth}.s_num
            limit 0,1
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($sql));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
      $agu_open['list']=substr($row->agu_open,0,1); // 瀏覽
      $agu_open['add']=substr($row->agu_open,1,1); // 新增
      $agu_open['upd']=substr($row->agu_open,2,1); // 修改
      $agu_open['del']=substr($row->agu_open,3,1); // 刪除
      $agu_open['que']=substr($row->agu_open,4,1); // 查詢
      $agu_open['prn']=substr($row->agu_open,5,1); // 列印
      $agu_open['download']=substr($row->agu_open,6,1); // 下載
      $agu_open['money']=substr($row->agu_open,7,1); // 金額
      $agu_open['cf']=substr($row->agu_open,8,1); // 發單確認
      $agu_open['cf_report']=substr($row->agu_open,9,1); // 列印確認
    }
    return $agu_open;
  }
}
?>