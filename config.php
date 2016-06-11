<?php
/**
 *
 * @file payment-gateway/config.php
 * @desc
 *      AllTheGate 와 PayPal 등 모든 결제 모듈의 저보를 여기서 설정한다.
 */

/** ===== 기본 변수 ===== */
$GLOBALS['payment'] = array();                                     // 모든 처리 값을 이 변수 하나에 담는다. 각 함수에서 이 변수를 공유한다.
global $payment;
/**
 * --------------------------------------------------------------------------
 *
 * 올더게이트 설정
 *
 * --------------------------------------------------------------------------
 */



/** ====== 필수 ===== */
// 참고 : 이 값은 관리자 페이지에서 수정이 가능 하도록 한다.
define( 'PAYMENT_CMS', 'wordpress' );                               // CMS 를 기록한다. 2016년 현재, 'wordpress' 만 지원.
define( 'PAYMENT_GATEWAY_PATH', __LMS_PATH__ . 'payment-gateway/allthegate/' ); // 필수 : 각 CMS 에 맞게 수정.
$payment['MallUrl']             = "http://www.withcenter.kr";              // 쇼핑몰 홈페이지.
$payment['MallPage']            = "/enrollment?mode=AGS_VirAccResult"; // 예제) /mall/AGS_VirAcctResult.php
$payment['allthegate_account']  = "aegis";                           // 올더게이트 상점 아이디. 테스트 아이디는 "aegis"
$payment['company_name']        = "Withcenter, Inc.";                // 회사 이름 ( 상점 이름 )
$payment['item_name']           = "화상영어 수업료";                       // 상품 이름. ( 아이템 이름 )
$payment['UserEmail']           = "thruthesky@gmail.com";                           // 상점 관리자 메일 주소
$payment['ags_logoimg_url'] = "http://www.allthegate.com/hyosung/images/aegis_logo.gif"; // 결제창에 나타날 로고. 매뉴얼 참고. 예제) http://www.allthegate.com/hyosung/images/aegis_logo.gif

/** ====== 변수 ===== */
// 상황에 따라서 변하는 값으로 대충 지정 해 놓고, 수정 할 필요가 없는 값
$payment['SubjectData'] = "$payment[company_name];$payment[item_name];100000;2012.09.01 ~ 2012.09.31"; // 제목: 제목은 1컨텐츠당 5자 이내이며, 상점명;상품명;결제금액;제공기간; 순으로 입력해 주셔야 합니다. 입력 예)업체명;판매상품;계산금액;제공기간; 예제 1) 업체명;판매상품;계산금액;2012.09.01 ~ 2012.09.30;
$payment['UserId'] = "UserId";                                      // 사용자 (주문자) 아이디.
$payment['UserName'] = "UserName";                                      // 사용자 (주문자) 이름.
$payment['UserPhone'] = "02-222-2222";                                      // 사용자 (주문자) 전화번호.
$payment['UserAddress'] = "User Address";                                      // 사용자 (주문자) 주소.
$payment['RecvName'] = "RecvName";                                      // 배달 받을 사람 이름.
$payment['RecvPhone'] = "055-555-5555";                                      // 배달 받을 사람 전화.
$payment['RecvAddress'] = "Recv Address";                                      // 배달 받을 사람 주소.
$payment['Remark'] = "";                                      // 기타 요구 사항 예) 오후에 배송 요망.


/** ====== 옵션 ===== */
define( 'PAYMENT_DEBUG', true );                                // true 이면 디버깅을 한다.
define( 'PAYMENT_DEBUG_NO_ACTIVEX', true );                     // true 이면 ActiveX 결제 확인 생략
if ( PAYMENT_DEBUG ) define( 'PAYMENT_LOG_LEVEL', 'DEBUG');     // 디버깅 시에는 자세한 기록을 남긴다.                         // DEBUG, INFO 등을 기록.
else define( 'PAYMENT_LOG_LEVEL', 'INFO');                      // DEBUG, INFO 등을 기록.

define( 'PAYMENT_LOG', true );                                      // 결제 연동 간편 메뉴얼 참고. AGS_pay_ing.php 참고.
define( 'PAYMENT_LOG_PATH', PAYMENT_GATEWAY_PATH );          // 이 폴더에 log 서브 폴더를 만들고 반드시 쓰기 퍼미션을 줘야 한다. AGSLib.php::PayLog::InitLog() 참고



