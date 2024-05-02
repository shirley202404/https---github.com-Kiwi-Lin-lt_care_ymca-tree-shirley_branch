<?php
class Sys_account_group_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_all()
  //  函數功能: 取得群組所有資料()
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_all() {
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
    $data = NULL;
    $sql = "select {$tbl_account_group}.*,
                   GROUP_CONCAT({$tbl_sys_account}.acc_name order by {$tbl_sys_account}.acc_name SEPARATOR '、') as acc_name
            from {$tbl_account_group}
            left join {$tbl_sys_account} on {$tbl_sys_account}.group_s_num = {$tbl_account_group}.s_num
            where {$tbl_account_group}.d_date is null
                  and {$tbl_sys_account}.d_date is null
            group by {$tbl_account_group}.s_num
            order by {$tbl_account_group}.s_num
           ";
    //echo $sql;
    //echo '<hr>';
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
  //  函數名稱: get_all_is_open()
  //  函數功能: 取得開放基金會自行選取的所有資料()
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_all_is_open() {
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
    $data = NULL;
    $sql = "select {$tbl_account_group}.*
            from {$tbl_account_group}
            where {$tbl_account_group}.d_date is null
                  and {$tbl_account_group}.acg_is_open = 'Y'
            group by {$tbl_account_group}.s_num
            order by {$tbl_account_group}.s_num
           ";
    //echo $sql;
    //echo '<hr>';
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
  //  函數名稱: get_one()
  //  函數功能: 取得account_group單筆資料(s_num)
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function get_one($s_num) {
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
    $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 帳號群駔權限
    $tbl_sys_menu = $this->zi_init->chk_tbl_no_lang('sys_menu'); // 選單資料
    $tbl_sys_account = $this->zi_init->chk_tbl_no_lang('sys_account'); // 帳戶管理
    $s_num = $this->db->escape_like_str($s_num);
    $row = NULL;
    $sql = "select {$tbl_account_group}.*,
                   {$tbl_account_group_auth}.s_num as aga_s_num,
                   {$tbl_account_group_auth}.group_s_num as aga_group_s_num,
                   {$tbl_account_group_auth}.menu_s_num as aga_menu_s_num,
                   {$tbl_account_group_auth}.agu_open as aga_agu_open,
                   {$tbl_sys_menu}.s_num as sm_s_num,
                   {$tbl_sys_menu}.sys_menu_name as sm_sys_menu_name,
                   {$tbl_sys_menu}.sys_menu_order as sm_sys_menu_order,
                   {$tbl_sys_menu}.sys_menu_level as sm_sys_menu_level,
                   GROUP_CONCAT(DISTINCT({$tbl_sys_account}.acc_name) order by {$tbl_sys_account}.acc_name SEPARATOR '、') as acc_name
            from {$tbl_account_group}
            left join {$tbl_sys_account} on {$tbl_sys_account}.group_s_num = {$tbl_account_group}.s_num
            left join {$tbl_account_group_auth} on {$tbl_account_group_auth}.group_s_num = {$tbl_account_group}.s_num
            left join {$tbl_sys_menu} on {$tbl_sys_menu}.s_num = {$tbl_account_group_auth}.menu_s_num
            where {$tbl_account_group}.s_num = ?
                  and {$tbl_account_group}.d_date is null
                  and {$tbl_account_group_auth}.d_date is null
                  and {$tbl_sys_menu}.d_date is null
                  and {$tbl_sys_account}.d_date is null
            group by {$tbl_account_group}.s_num
            order by {$tbl_account_group}.s_num
            limit 0,1
           ";
    //u_var_dump($sql);
    $rs = $this->db->query($sql, array($s_num));
    if($rs->num_rows() > 0) { // 有資料才執行
      $row = $rs->row(); 
    }
    return $row;
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 新增儲存資料
  //  程式設計: Tony
  //  設計日期: 2017/11/17
  // **************************************************************************
  public function save_add() {
    $rtn_msg = 'ok';
    $data['acg_name'] = $_POST['acg_name'];
    $data['acg_is_open'] = $_POST['acg_is_open'];
    $data['b_empno'] = $_SESSION['acc_s_num'];
    $data['b_date'] = date('Y-m-d H:i:s');
    $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
    if(!$this->db->insert($tbl_account_group, $data)) {
      $rtn_msg = $this->lang->line('add_ng');
    }
    if('ok'==$rtn_msg) { // 沒有錯誤才繼續
      $group_s_num = $this->db->insert_id();
      // 抓打勾的欄位
      foreach ($_POST as $fd => $v){
        if(strpos($fd,'_sel_')) { // 抓到 _sel_ 才處理
          if('ga'==substr($fd,0,2)) {
            $fd = explode('_',$fd);
            if(!empty($fd[4])) {
              $menu_s_num = $fd[2]; // 第三個位置,就是 sys_menu.s_num
              if(!isset($group_auth[$menu_s_num]['menu_s_num'])) {
                $group_auth[$menu_s_num]['menu_s_num']=$menu_s_num;
                $group_auth[$menu_s_num]['agu_open_list']='N';
                $group_auth[$menu_s_num]['agu_open_add']='N';
                $group_auth[$menu_s_num]['agu_open_upd']='N';
                $group_auth[$menu_s_num]['agu_open_del']='N';
                $group_auth[$menu_s_num]['agu_open_que']='N';
                $group_auth[$menu_s_num]['agu_open_prn']='N';
                $group_auth[$menu_s_num]['agu_open_download']='N';
                $group_auth[$menu_s_num]['agu_open_money']='N';
                $group_auth[$menu_s_num]['agu_open_cf']='N';
                $group_auth[$menu_s_num]['agu_open_cfreport']='N';
              }
              $group_auth[$menu_s_num]["agu_open_{$fd[4]}"]='Y'; // 欄位是否有打勾
            }
          }
        }
      }
      $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 帳號群駔權限
      $data_group_auth['group_s_num'] = $group_s_num;
      if(!empty($group_auth)) {
        foreach ($group_auth as $fd => $v){
          $f_menu_s_num = $group_auth[$fd]['menu_s_num'];
          $f_agu_open = "{$group_auth[$fd]['agu_open_list']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_add']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_upd']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_del']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_que']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_prn']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_download']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_money']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_cf']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_cfreport']}";
          $data_group_auth['menu_s_num'] = $f_menu_s_num;
          $data_group_auth['agu_open'] = $f_agu_open;
          $data_group_auth['b_empno'] = $_SESSION['acc_s_num'];
          $data_group_auth['b_date'] = date('Y-m-d H:i:s');
          if(!$this->db->insert($tbl_account_group_auth, $data_group_auth)) {
            $rtn_msg = $this->lang->line('add_ng');
          }
        }
      }
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
    // 抓打勾的欄位
    foreach ($_POST as $fd => $v){
      if(strpos($fd,'_sel_')) { // 抓到 _sel_ 才處理
        if('ga'==substr($fd,0,2)) {
          $fd = explode('_',$fd);
          if(!empty($fd[4])) {
            $menu_s_num = $fd[2]; // 第三個位置,就是 sys_menu.s_num
            if(!isset($group_auth[$menu_s_num]['menu_s_num'])) {
              $group_auth[$menu_s_num]['menu_s_num']=$menu_s_num;
              $group_auth[$menu_s_num]['agu_open_list']='N';
              $group_auth[$menu_s_num]['agu_open_add']='N';
              $group_auth[$menu_s_num]['agu_open_upd']='N';
              $group_auth[$menu_s_num]['agu_open_del']='N';
              $group_auth[$menu_s_num]['agu_open_que']='N';
              $group_auth[$menu_s_num]['agu_open_prn']='N';
              $group_auth[$menu_s_num]['agu_open_download']='N';
              $group_auth[$menu_s_num]['agu_open_money']='N';
              $group_auth[$menu_s_num]['agu_open_cf']='N';
              $group_auth[$menu_s_num]['agu_open_cfreport']='N';
            }
            $group_auth[$menu_s_num]["agu_open_{$fd[4]}"]='Y'; // 欄位是否有打勾
          }
        }
      }
    }
    // 先刪除目前的權限資料
    $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 帳號群駔權限
    $this->db->where('group_s_num', $_POST['group_s_num']);
    if(!$this->db->delete($tbl_account_group_auth)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    if('ok'==$rtn_msg) { // 沒有錯誤才繼續
      $data_group_auth['group_s_num'] = $_POST['group_s_num'];
      if(!empty($group_auth)) {
        foreach ($group_auth as $fd => $v){
          $f_menu_s_num = $group_auth[$fd]['menu_s_num'];
          $f_agu_open = "{$group_auth[$fd]['agu_open_list']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_add']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_upd']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_del']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_que']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_prn']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_download']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_money']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_cf']}";
          $f_agu_open .= "{$group_auth[$fd]['agu_open_cfreport']}";
          $data_group_auth['menu_s_num'] = $f_menu_s_num;
          $data_group_auth['agu_open'] = $f_agu_open;
          $data_group_auth['b_empno'] = $_SESSION['acc_s_num'];
          $data_group_auth['b_date'] = date('Y-m-d H:i:s');
          if(!$this->db->insert($tbl_account_group_auth, $data_group_auth)) {
            $rtn_msg = $this->lang->line('add_ng');
          }
        }
      }
    }
    if('ok'==$rtn_msg) { // 沒有錯誤才繼續
      $data['acg_name'] = $_POST['acg_name'];
      $data['acg_is_open'] = $_POST['acg_is_open'];
      $data['e_empno'] = $_SESSION['acc_s_num'];
      $data['e_date'] = date('Y-m-d H:i:s');
      $tbl_account_group = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
      $this->db->where('s_num', $_POST['group_s_num']);
      if(!$this->db->update($tbl_account_group, $data)) {
        $rtn_msg = $this->lang->line('upd_ng');
      }
    }
    echo $rtn_msg;
    return;
  }
  // **************************************************************************
  //  函數名稱: del()
  //  函數功能: 刪除資料
  //  程式設計: Tony
  //  設計日期: 2017/11/20
  // **************************************************************************
  public function del() {
    $rtn_msg = 'ok';
    $v = $this->input->post();
    $data['d_empno'] = $_SESSION['acc_s_num'];
    $data['d_date']  = date('Y-m-d H:i:s');
    $tbl_account = $this->zi_init->chk_tbl_no_lang('sys_account_group'); // 帳號群駔
    $this->db->where('s_num', $v['s_num']);
    if(!$this->db->update($tbl_account, $data)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    // 刪除權限資料
    $tbl_account_group_auth = $this->zi_init->chk_tbl_no_lang('sys_account_group_auth'); // 帳號群駔權限
    $data_auth['d_empno'] = $_SESSION['acc_s_num'];
    $data_auth['d_date']  = date('Y-m-d H:i:s');
    $this->db->where('group_s_num', $v['s_num']);
    if(!$this->db->update($tbl_account_group_auth,$data_auth)) {
      $rtn_msg = $this->lang->line('del_ng'); // 刪除失敗
    }
    echo $rtn_msg;
    return;
  }
}
?>