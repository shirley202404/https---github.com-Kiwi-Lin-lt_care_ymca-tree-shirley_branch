<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['be'] = 'be/main';  // 後台首頁
$route['be/logout'] = 'be/login/logout';  // 後台登出
$route['be/news/p/(:num)/q/(:any)'] = 'be/news/index/$1/$2';  // 最新消息
$route['be/news/p/(:num)/q'] = 'be/news/index/$1';  // 最新消息
$route['be/album/p/(:num)/q/(:any)'] = 'be/album/index/$1/$2';  // 相本管理
$route['be/album/p/(:num)/q'] = 'be/album/index/$1';  // 相本管理
$route['be/sys_menu/p/(:num)/q/(:any)'] = 'be/sys_menu/index/$1/$2';  // 系統選單
$route['be/sys_menu/p/(:num)/q'] = 'be/sys_menu/index/$1';  // 系統選單
$route['be/sys_account/p/(:num)/q/(:any)'] = 'be/sys_account/index/$1/$2';  // 帳戶管理
$route['be/sys_account/p/(:num)/q'] = 'be/sys_account/index/$1';  // 帳戶管理
$route['be/sys_group/p/(:num)/q/(:any)'] = 'be/sys_group/index/$1/$2';  // 群組管理
$route['be/sys_group/p/(:num)/q'] = 'be/sys_group/index/$1';  // 群組管理
$route['be/sys_log/p/(:num)/q/(:any)'] = 'be/sys_log/index/$1/$2';  // 系統log
$route['be/sys_log/p/(:num)/q'] = 'be/sys_log/index/$1';  // 系統log
$route['be/daily_production_order/p/(:num)/q/(:any)'] = 'be/daily_production_order/index/$1/$2';  // 餐條排序設定檔
$route['be/daily_production_order/p/(:num)/q'] = 'be/daily_production_order/index/$1';  // 餐條排序設定檔
$route['be/region_setting/p/(:num)/q/(:any)'] = 'be/region_setting/index/$1/$2';  // 區域設定檔
$route['be/region_setting/p/(:num)/q'] = 'be/region_setting/index/$1';  // 區域設定檔
$route['be/meal_order_date_type/p/(:num)/q/(:any)'] = 'be/meal_order_date_type/index/$1/$2';  // 訂單日期類型紀錄
$route['be/meal_order_date_type/p/(:num)/q'] = 'be/meal_order_date_type/index/$1';  // 訂單日期類型紀錄
$route['be/meal_replacement_day_set/p/(:num)/q/(:any)'] = 'be/meal_replacement_day_set/index/$1/$2';  // 代餐送餐時間設定
$route['be/meal_replacement_day_set/p/(:num)/q'] = 'be/meal_replacement_day_set/index/$1';  // 代餐送餐時間設定
$route['be/meal_service_fee/p/(:num)/q/(:any)'] = 'be/meal_service_fee/index/$1/$2';  // 餐食服務費補助設定資料
$route['be/meal_service_fee/p/(:num)/q'] = 'be/meal_service_fee/index/$1';  // 餐食服務費補助設定
$route['be/work_time/p/(:num)/q/(:any)'] = 'be/work_time/index/$1/$2';  // 班別時間
$route['be/work_time/p/(:num)/q'] = 'be/work_time/index/$1';  // 班別時間

// 案主
$route['be/clients/p/(:num)/q/(:any)'] = 'be/clients/index/$1/$2';  // 案主資料
$route['be/clients/p/(:num)/q'] = 'be/clients/index/$1';  // 案主資料
$route['be/clients_hlth/p/(:num)/q/(:any)'] = 'be/clients_hlth/index/$1/$2';  // 營養評估表
$route['be/clients_hlth/p/(:num)/q'] = 'be/clients_hlth/index/$1';  // 營養評估表
$route['be/clients_disability/p/(:num)/q/(:any)'] = 'be/clients_disability/index/$1/$2';  // 失能評估表
$route['be/clients_disability/p/(:num)/q'] = 'be/clients_disability/index/$1';  // 失能評估表
$route['be/home_interview/p/(:num)/q/(:any)'] = 'be/home_interview/index/$1/$2';  // 家訪資料
$route['be/home_interview/p/(:num)/q'] = 'be/home_interview/index/$1';  // 家訪資料
$route['be/phone_interview/p/(:num)/q/(:any)'] = 'be/phone_interview/index/$1/$2';  // 電訪資料
$route['be/phone_interview/p/(:num)/q'] = 'be/phone_interview/index/$1';  // 電訪資料
$route['be/clients_hlth_normal/p/(:num)/q/(:any)'] = 'be/clients_hlth_normal/index/$1/$2';  // 營養師營養評估表
$route['be/clients_hlth_normal/p/(:num)/q'] = 'be/clients_hlth_normal/index/$1';  // 營養師營養評估表
$route['be/client_import/p/(:num)/q/(:any)'] = 'be/client_import/index/$1/$2';  // 案主暫存資料
$route['be/client_import/p/(:num)/q'] = 'be/client_import/index/$1';  // 案主暫存資料

// 外送員
$route['be/delivery_person/p/(:num)/q/(:any)'] = 'be/delivery_person/index/$1/$2';  // 外送員基本資料檔
$route['be/delivery_person/p/(:num)/q'] = 'be/delivery_person/index/$1';  // 外送員基本資料檔
$route['be/work_log/p/(:num)/q/(:any)'] = 'be/work_log/index/$1/$2';  // 工作紀錄資料
$route['be/work_log/p/(:num)/q'] = 'be/work_log/index/$1';  // 工作紀錄資料
$route['be/punch_log/p/(:num)/q/(:any)'] = 'be/punch_log/index/$1/$2';  // 打卡紀錄資料
$route['be/punch_log/p/(:num)/q'] = 'be/punch_log/index/$1';  // 打卡紀錄資料
$route['be/beacon_log/p/(:num)/q/(:any)'] = 'be/beacon_log/index/$1/$2';  // Beacon Log 資料
$route['be/beacon_log/p/(:num)/q'] = 'be/beacon_log/index/$1';  // Beacon Log 資料
$route['be/gps_log/p/(:num)/q/(:any)'] = 'be/gps_log/index/$1/$2';  // gps_log
$route['be/gps_log/p/(:num)/q'] = 'be/gps_log/index/$1';  // gps_log

// 開案服務
$route['be/service_case/p/(:num)/q/(:any)'] = 'be/service_case/index/$1/$2';  // 服務資料
$route['be/service_case/p/(:num)/q'] = 'be/service_case/index/$1';  // 服務資料
$route['be/meal_instruction_log/p/(:num)/q/(:any)'] = 'be/meal_instruction_log/index/$1/$2';  // 餐點異動資料
$route['be/meal_instruction_log/p/(:num)/q'] = 'be/meal_instruction_log/index/$1';  // 餐點異動資料
$route['be/meal_instruction_auth/p/(:num)/q/(:any)'] = 'be/meal_instruction_auth/index/$1/$2';  // 餐食異動單審核紀錄檔
$route['be/meal_instruction_auth/p/(:num)/q'] = 'be/meal_instruction_auth/index/$1';  // 餐食異動單審核紀錄檔
$route['be/other_change_log/p/(:num)/q/(:any)'] = 'be/other_change_log/index/$1/$2';  // 非餐點異動資料
$route['be/other_change_log/p/(:num)/q'] = 'be/other_change_log/index/$1';  // 非餐點異動資料
$route['be/other_change_auth/p/(:num)/q/(:any)'] = 'be/other_change_auth/index/$1/$2';  // 非餐食異動單審核紀錄檔
$route['be/other_change_auth/p/(:num)/q'] = 'be/other_change_auth/index/$1';  // 非餐食異動單審核紀錄檔
$route['be/dietitian_note/p/(:num)/q/(:any)'] = 'be/dietitian_note/index/$1/$2';  // 照會營養師
$route['be/dietitian_note/p/(:num)/q'] = 'be/dietitian_note/index/$1';  // 照會營養師
$route['be/service_case_complaint/p/(:num)/q/(:any)'] = 'be/service_case_complaint/index/$1/$2';  // 客訴處理單
$route['be/service_case_complaint/p/(:num)/q'] = 'be/service_case_complaint/index/$1';  // 客訴處理單
$route['be/service_case_appeal/p/(:num)/q/(:any)'] = 'be/service_case_appeal/index/$1/$2';  // 申訴處理單-社工
$route['be/service_case_appeal/p/(:num)/q'] = 'be/service_case_appeal/index/$1';  // 申訴處理單-社工

// 繳費管理          				    
$route['be/seal/p/(:num)/q/(:any)'] = 'be/seal/index/$1/$2';  // 印章設定
$route['be/seal/p/(:num)/q'] = 'be/seal/index/$1';  // 印章設定
$route['be/subsidy/p/(:num)/q/(:any)'] = 'be/subsidy/index/$1/$2';  // 補助戶
$route['be/subsidy/p/(:num)/q'] = 'be/subsidy/index/$1';  // 補助戶
$route['be/ownexpense/p/(:num)/q/(:any)'] = 'be/ownexpense/index/$1/$2';  // 自費戶
$route['be/ownexpense/p/(:num)/q'] = 'be/ownexpense/index/$1';  // 自費戶

// 服務路徑
$route['be/route/p/(:num)/q/(:any)'] = 'be/route/index/$1/$2';  // 送餐路徑規劃
$route['be/route/p/(:num)/q'] = 'be/route/index/$1';  // 送餐路徑規劃

// 餐點					    		
$route['be/meal/p/(:num)/q/(:any)'] = 'be/meal/index/$1/$2';  // 餐點資料
$route['be/meal/p/(:num)/q'] = 'be/meal/index/$1';  // 餐點資料                                      

// 員工管理
$route['be/social_worker/p/(:num)/q/(:any)'] = 'be/social_worker/index/$1/$2';  // 社工基本資料檔
$route['be/social_worker/p/(:num)/q'] = 'be/social_worker/index/$1';  // 社工基本資料檔
$route['be/delivery_person/p/(:num)/q/(:any)'] = 'be/delivery_person/index/$1/$2';  // 送餐員基本資料檔
$route['be/delivery_person/p/(:num)/q'] = 'be/delivery_person/index/$1';  // 送餐員基本資料檔
$route['be/verification_person/p/(:num)/q/(:any)'] = 'be/verification_person/index/$1/$2';  // 核銷人員資料檔
$route['be/verification_person/p/(:num)/q'] = 'be/verification_person/index/$1';  // 核銷人員資料檔

// beacon
$route['be/rpt_funding/p/(:num)/q/(:any)'] = 'be/rpt_funding/index/$1/$2';  // Beacon資料
$route['be/rpt_funding/p/(:num)/q'] = 'be/rpt_funding/index/$1';  // Beacon資料

// 手機管理
$route['be/mobile/p/(:num)/q/(:any)'] = 'be/mobile/index/$1/$2';  // 手機資料
$route['be/mobile/p/(:num)/q'] = 'be/mobile/index/$1';  // 手機資料
$route['be/mobile_use/p/(:num)/q/(:any)'] = 'be/mobile_use/index/$1/$2';  // 手機使用紀錄資料
$route['be/mobile_use/p/(:num)/q'] = 'be/mobile_use/index/$1';  // 手機使用紀錄資料

// 作業專區                          
$route['be/daily_rpt/p/(:num)/q/(:any)'] = 'be/daily_rpt/index/$1/$2';  // 訂單報表
$route['be/daily_rpt/p/(:num)/q'] = 'be/daily_rpt/index/$1';  // 訂單報表                         				    		
$route['be/daily_work/p/(:num)/q/(:any)'] = 'be/daily_work/index/$1/$2';  // 每日工作單
$route['be/daily_work/p/(:num)/q'] = 'be/daily_work/index/$1';  // 每日工作單      
$route['be/daily_shipment/p/(:num)/q/(:any)'] = 'be/daily_shipment/index/$1/$2';  // 配送調整
$route['be/daily_shipment/p/(:num)/q'] = 'be/daily_shipment/index/$1';  // 配送調整
$route['be/work_q_route_list/p/(:num)/q/(:any)'] = 'be/work_q_route_list/index/$1/$2';  // 問卷基本資料-檔頭
$route['be/work_q_route_list/p/(:num)/q'] = 'be/work_q_route_list/index/$1';  // 問卷基本資料-檔頭
$route['be/work_q/p/(:num)/q/(:any)'] = 'be/work_q/index/$1/$2';  // 工作問卷-檔頭
$route['be/work_q/p/(:num)/q'] = 'be/work_/index/$1';  // 工作問卷-檔頭

// 報表下載
$route['be/rpt_write_off/p/(:num)/q/(:any)'] = 'be/rpt_Write_off/index/$1/$2';  // 核銷報表
$route['be/rpt_write_off/p/(:num)/q'] = 'be/rpt_Write_off/index/$1';  // 核銷報表
$route['be/rpt_service/p/(:num)/q/(:any)'] = 'be/rpt_service/index/$1/$2';  // 清冊
$route['be/rpt_service/p/(:num)/q'] = 'be/rpt_service/index/$1';  // 清冊  
$route['be/rpt_pay/p/(:num)/q/(:any)'] = 'be/rpt_service/index/$1/$2';  // 繳費資料
$route['be/rpt_pay/p/(:num)/q'] = 'be/rpt_service/index/$1';  // 繳費資料           
$route['be/rpt_sab/p/(:num)/q/(:any)'] = 'be/rpt_service/index/$1/$2';  // 社會局月報
$route['be/rpt_sab/p/(:num)/q'] = 'be/rpt_service/index/$1';  // 社會局月報        

// 問卷管理
$route['be/questionnaire/p/(:num)/q/(:any)'] = 'be/questionnaire/index/$1/$2';  // 問卷基本資料-檔頭
$route['be/questionnaire/p/(:num)/q'] = 'be/questionnaire/index/$1';  // 問卷基本資料-檔頭

// 捐款資料
$route['be/donate_item/p/(:num)/q/(:any)'] = 'be/donate_item/index/$1/$2';  // 捐款項目
$route['be/donate_item/p/(:num)/q'] = 'be/donate_item/index/$1';  // 捐款項目
$route['be/donate_progress/p/(:num)/q/(:any)'] = 'be/donate_progress/index/$1/$2';  // 捐款進度資料
$route['be/donate_progress/p/(:num)/q'] = 'be/donate_progress/index/$1';  // 捐款進度資料
$route['be/donate/p/(:num)/q/(:any)'] = 'be/donate/index/$1/$2';  // 捐款資料
$route['be/donate/p/(:num)/q'] = 'be/donate/index/$1';  // 捐款資料
$route['be/donate_import/p/(:num)/q/(:any)'] = 'be/donate_import/index/$1/$2';  // 捐款徵信資料匯入
$route['be/donate_import/p/(:num)/q'] = 'be/donate_import/index/$1';  // 捐款徵信資料匯入
$route['be/front_content/p/(:num)/q/(:any)'] = 'be/front_content/index/$1/$2';  // 捐款前台內容
$route['be/front_content/p/(:num)/q'] = 'be/front_content/index/$1';  // 捐款前台內容

// 捐款前台畫面
$route['main'] = 'front/main';                                 // 前台首頁
$route['donate'] = 'front/donate/index';                       // 捐款畫面
$route['donate1'] = 'front/donate/donate_embed';               // 捐款畫面(嵌入用)
$route['donate/pay'] = 'front/donate/pay/';                    // 付款連結 
$route['donate/test'] = 'front/donate/test/';                  // 付款連結 
$route['donate/thank/(:any)'] = 'front/donate/thank/$1';       // 捐款完成畫面
$route['donate/cancel'] = 'front/donate/cancel/';              // 捐款取消畫面
$route['donate/tab/(:any)/(:num)'] = 'front/donate/tab/$1/$2'; // 捐款分頁
$route['donate_que'] = 'front/donate_que/index'; // 線上捐款查詢畫面(嵌入用)
$route['donate_que/que/(:any)'] = 'front/donate_que/que/$1'; // 線上捐款查詢畫面(嵌入用)
$route['donate_que/p/(:num)/q/(:any)'] = 'front/donate_que/index/$1/$2'; // 線上捐款查詢畫面(嵌入用)
$route['donate_que/p/(:num)/q'] = 'front/donate_que/index/$1'; // 線上捐款查詢畫面(嵌入用)
$route['donate1_que'] = 'front/donate1_que/index'; // 捐贈徵信查詢畫面(嵌入用)
$route['donate1_que/que/(:any)'] = 'front/donate1_que/que/$1'; // 捐贈徵信查詢畫面(嵌入用)
$route['donate1_que/p/(:num)/q/(:any)'] = 'front/donate1_que/index/$1/$2'; // 捐贈徵信查詢畫面(嵌入用)
$route['donate1_que/p/(:num)/q'] = 'front/donate1_que/index/$1'; // 捐贈徵信查詢畫面(嵌入用)

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
