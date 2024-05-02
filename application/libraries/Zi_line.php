<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Zi_line {
                             
  public function __construct() {
    // Assign the CodeIgniter super-object
    $this->CI =& get_instance();
    $this->channel_id = "1656360599";
    $this->channel_secret = "e42e44696635f35f8c1be1f3ce109ba6";
    $this->channel_access_token = "oZXAYYEbM025PxIZt83yopWuWWIr9uuA8GdhxI8hAWdDoKzyRyXrt2+8CuvKZYoKxummgC84/kkVwKlhCVQzNwCtrVpLmibbSx6CL+Rsk01/3L8sSREPdgP0c0BRknO4UcLZ8/I2/Mtd0kRaAjrICAdB04t89/1O/w1cDnyilFU=";
  }

  // **************************************************************************
  //  函數名稱: delegated_get_token()
  //  函數功能: 獲取Token
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function send_line_message() {
    // make payload
    $payload = [
       'to' => "steven97xup60817",
       'messages' => [
           [
               'type' => 'text',
               'text' => "HI"
           ]
       ]
    ];
     
    // Send Request by CURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.line.me/v2/bot/message/multicast');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
       'Content-Type: application/json',
       'Authorization: Bearer ' . $this->channel_access_token
    ]);
    $result = curl_exec($ch);
    u_var_dump($result);
    curl_close($ch);
  }
  
  // **************************************************************************
  //  函數名稱: delegated_get_token()
  //  函數功能: 獲取Token
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function delegated_get_token() {
    $client_id = TEAMS_CLIENT_ID;
    $client_secret = TEAMS_CLIENT_SECRET;
    $tenant_id = TEAMS_TENANT_ID;
    // $response_uri = "http://localhost/dms/teams/ms_login";
    
    $post_url = "{$tenant_id}/oauth2/v2.0/token";
    $hostname = "login.microsoftonline.com";
    $full_url = "https://login.microsoftonline.com/{$tenant_id}/oauth2/v2.0/token";
    
    $headers = array(
      "POST {$post_url} HTTP/1.1",
      "Host: {$hostname}",
      "Content-type: application/x-www-form-urlencoded",
    );
    
    $post_params = array(
      "grant_type" => "password",
      "client_id" => $client_id,
      "username" => TEAMS_USERNAME,
      "password" => TEAMS_PASSWORD,
      "client_secret" => $client_secret,
      "scope" => "https://graph.microsoft.com/.default",
    );
    
    $curl = curl_init($full_url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("application/x-www-form-urlencoded"));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($curl);
    $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    // echo $_SESSION["access_token"];
    $decode = json_decode(curl_exec($curl));
    // u_var_dump($decode);
    if(isset($decode->access_token)) {
      $_SESSION["access_token"] = $decode->access_token;
    }
    else {
      echo "token索取失敗!!";
    }
    return;
  }
  
  // **************************************************************************
  //  函數名稱: user_profile()
  //  函數功能: 獲取帳號資料
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function user_profile() {
    $list_items_permission = "https://graph.microsoft.com/v1.0/users/delta";

    $headers = array(
      "Content-Type: application/json",
      "Authorization: Bearer {$_SESSION['access_token']}",
    );

    // print_r($headers);
    $curl = curl_init($list_items_permission);
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = json_decode(curl_exec($curl));
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    //檢查錯誤
    if($httpcode == "200") {
      var_dump($response->value);
    }
    var_dump($response);
    curl_close($curl);
  }
  
  // **************************************************************************
  //  函數名稱: get_team_id()
  //  函數功能: 獲取帳號資料
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function get_team_id() {
    // get events api
    $get_calender_url = "https://graph.microsoft.com/v1.0/users/{$this->user_id}/joinedTeams";
        
    $headers = array(
      "Content-Type: application/json",
      "Authorization: Bearer {$_SESSION['access_token']}",
    );

    // print_r($headers);
    $curl = curl_init($get_calender_url);
    
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = json_decode(curl_exec($curl));
    //檢查錯誤
    if(curl_errno($curl)) {
      echo "Error" . curl_error($curl);
    } 
    else {
      var_dump($response);
    }
    curl_close($curl);
  }
  
  // **************************************************************************
  //  函數名稱: get_all_channel()
  //  函數功能: 獲得這組帳號底下所有的Channel
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function get_all_channel() {
    // get events api
    $get_calender_url = "https://graph.microsoft.com/v1.0/teams/{$this->team_id}/channels";
        
    $headers = array(
      "Content-Type: application/json",
      "Authorization: Bearer {$_SESSION['access_token']}",
    );

    // print_r($headers);
    $curl = curl_init($get_calender_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = json_decode(curl_exec($curl));
    if(curl_errno($curl)) { //檢查錯誤
      echo "Error" . curl_error($curl);
    } 
    else {
      var_dump($response);
    }
    curl_close($curl);
  }

  // **************************************************************************
  //  函數名稱: list_members()
  //  函數功能: 獲得Channel底下的所有成員
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function list_members() {
    $channel_member_data = NULL;
    $get_member_url = "https://graph.microsoft.com/v1.0/teams/{$this->team_id}/channels/{$this->channel_id}/members";     // get events api
    
    $headers = array(
      "Content-Type: application/json",
      "Authorization: Bearer {$_SESSION['access_token']}",
    );

    $curl = curl_init($get_member_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = json_decode(curl_exec($curl));
    if(curl_errno($curl)) {     //檢查錯誤
      echo "Error" . curl_error($curl);
    } 
    else {
      foreach($response->value as $k => $v) {
        $channel_member_data['email'][$k] = $v->email;
        $channel_member_data['teams_user_id'][$k] = $v->userId;
      }
    }
    curl_close($curl);
    return $channel_member_data;
  }
    
  // **************************************************************************
  //  函數名稱: add_chat()
  //  函數功能: 針對Channel底下的所有成員發送訊息
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function add_chat($em16=NULL) {
    $flag = false;
    $chat_id = '';
    $add_msgs_url = "https://graph.microsoft.com/v1.0/chats";    // get msgs api 

    $headers = array(
      "Content-Type: application/json",
      "Authorization: Bearer {$_SESSION['access_token']}",
    );

    $post_params = json_encode(array(
      "chatType" => "oneOnOne",
      "members" => array(
                   array(
                      "@odata.type" => "#microsoft.graph.aadUserConversationMember",
                      "roles" => ["owner"],
                      "user@odata.bind" => "https://graph.microsoft.com/v1.0/users('{$em16}')"
                    ),
                    array(
                      "@odata.type" => "#microsoft.graph.aadUserConversationMember",
                      "roles" =>["owner"],
                      "user@odata.bind"=>"https://graph.microsoft.com/v1.0/users('{$this->user_id}')"
                    ),
      )
    ));
    
    $curl = curl_init($add_msgs_url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = json_decode(curl_exec($curl));
    if(curl_errno($curl)) { // 檢查錯誤
      echo "Error" . curl_error($curl);
    } 
    else {
      if(isset($response->error)) {
        $flag = false;
      }
      else {
        $flag = true;
        $chat_id = $response->id;
      }
    }
    curl_close($curl);
    return array($flag , $chat_id);
  }
  
  // **************************************************************************
  //  函數名稱: send_message_to_chat()
  //  函數功能: 針對Channel底下的所有成員發送訊息
  //  程式設計: Kiwi
  //  設計日期: 2021/07/20
  // **************************************************************************
  function send_message_to_chat($chat_id , $send_content) {
    $add_msgs_url = "https://graph.microsoft.com/v1.0/chats/{$chat_id}/messages";
    $headers = array(
      "Content-Type: application/json",
      "Authorization: Bearer {$_SESSION['access_token']}",
    );

    $post_params = json_encode(array(
      "body"=>array("contentType" => "html",
                    "content" => "{$send_content}"
                   )
    ));

    $curl = curl_init($add_msgs_url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = json_decode(curl_exec($curl));
    if(curl_errno($curl)) { //檢查錯誤
      echo "Error" . curl_error($curl);
    } 
    curl_close($curl);
  }
  
  function __destruct() {
  }
}

?>
