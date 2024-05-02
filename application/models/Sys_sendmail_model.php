<?php
class Sys_sendmail_model extends CI_Model {
  public function __construct()  {
    $this->load->database();
  }
  // **************************************************************************
  //  函數名稱: get_not_send_mail()
  //  函數功能: 取得尚未發信的信件排程資料，每次只抓500筆
  //  程式設計: Tony
  //  設計日期: 2018/4/24
  // **************************************************************************
  public function get_not_send_mail() {
    $tbl_sendmail_crontab = $this->zi_init->chk_tbl_no_lang('sys_sendmail_crontab'); // 發信排程
    $data = NULL;
    $where = "{$tbl_sendmail_crontab}.d_date is null
              and $tbl_sendmail_crontab.send_time is null
             ";
    $sql = "select *
            from {$tbl_sendmail_crontab}
            where $where
            order by s_num asc
            limit 0,500
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
    return($data);    
  }
  // **************************************************************************
  //  函數名稱: save_add()
  //  函數功能: 發信排程儲存
  //  程式設計: Tony
  //  設計日期: 2021/4/27
  // **************************************************************************
  public function save_add($send_source=NULL,$send_title=NULL,$send_to=NULL,$send_mail=NULL,$send_content=NULL,$em_s_num=NULL) {
    $data=NULL;
    $data['send_source'] = $send_source; // 發信源
    $data['send_title'] = $send_title; // 信件主旨
    $data['send_to'] = $send_to; // 收件者姓名
    $data['send_mail'] = $send_mail; // 收件者郵件
    $data['send_content'] = $send_content; // 內容
    $data['b_empno'] = 0;
    $data['b_date'] = date('Y-m-d H:i:s');
    $this->db->insert('sys_sendmail_crontab', $data);
    return;
  }
  // **************************************************************************
  //  函數名稱: save_upd_send_result()
  //  函數功能: 發信排程儲存
  //  程式設計: Tony
  //  設計日期: 2018/4/24
  // **************************************************************************
  public function save_upd_send_result($s_num=NULL,$send_result=NULL) {
    $rtn_msg = 'ok';
    $s_num = $this->db->escape_like_str($s_num);
    $send_result = $this->db->escape_like_str($send_result);
    $data['send_result'] = $send_result;
    $data['send_time'] = date('Y-m-d H:i:s');
    $data['e_date'] = date('Y-m-d H:i:s');
    $tbl_sendmail_crontab = $this->zi_init->chk_tbl_no_lang('sys_sendmail_crontab'); // 發信排程
    $this->db->where('s_num', $s_num);
    if(!$this->db->update($tbl_sendmail_crontab, $data)) {
      $rtn_msg = $this->lang->line('upd_ng');
    }
    echo $rtn_msg;
    return;
  }
}
?>