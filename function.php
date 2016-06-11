<?php
/**
 * @file function.php
 * @desc 기본 함수 라이브러리
 */


/**
 * 랜덤 문자열을 리턴한다.
 *
 * @return string
 */
function payment_random_string() {
    return md5(uniqid(mt_rand(), true));
}
/**
 *
 * 각 CMS 의 회원 번호를 리턴한다.
 *
 * @return int - 회원번호
 *
 */
function payment_get_user_id() {
    switch ( PAYMENT_CMS ) {
        case 'wordpress'            : return wp_get_current_user()->ID;
        default                     : return 0;
    }
}

/**
 * 결제 폼 변수 입력 값 체크
 *
 * @return int - 성공이면 0 을 리턴.
 */
function payment_check_input() {

    global $payment;
// min
    if ( ! isset($_REQUEST['min']) || empty($_REQUEST['min']) ) {
        jsAlert("수업 분을 선택하십시오.");
        return -1;
    }
    $min = $_REQUEST['min'];
    $payment['min'] = $min;

    $amt = 0;
    if ( $min == '25' ) $amt = 120000;
    else if ( $min == '50' ) $amt = 240000;
    if ( $amt == 0 ) {
        jsAlert("수업료를 선택하십시오.");
        return -1;
    }

    $payment['amt'] = $amt;

// payment method
    if ( ! isset( $_REQUEST['method'] ) || empty($_REQUEST['method']) ) {
        jsAlert("결재 방식을 선택하십시오.");
        return -2;
    }
    $payment['method'] = $_REQUEST['method'];


    $payment['values_from_user_form'] = serialize( $_REQUEST );

    $date = date("Y-m");
    $payment['SubjectData'] = "$payment[company_name];$payment[item_name];$payment[amt];$date"; //업체명;판매상품;계산금액;2012.09.01 ~ 2012.09.30;
    $payment['UserId'] = payment_get_user_id();

    return 0;
}

/**
 * 사용자 결제 정보를 데이터베이스 저장한다.
 *
 * @note 저장하고 테이블의 ID 값을 $GLOBALS[payment][ID] 에 저장한다.
 *
 * @return int - 성공이면 0, 에러이면 참.
 */
function payment_insert_info() {
    global $payment;
    $payment['session_id'] = payment_random_string();
    if ( PAYMENT_CMS == 'wordpress' ) {

        global $wpdb;
        $table = $wpdb->prefix . 'payment';

// insert db
        $q = "INSERT INTO $table
              (user_id, session_id, paygate, paygate_account, method, currency, amount, stamp_create, values_from_user_form)
              VALUES ( %d, %s, %s, %s, %s, %s, %d, %d, %s )";
        $prepare = $wpdb->prepare(
            $q, payment_get_user_id(),
            $payment['session_id'],
            'allthegate',
            $payment['allthegate_account'],
            $payment['method'],
            'KWR',
            $payment['amt'], time(), $payment['values_from_user_form'] );
        di($prepare);
        $re = $wpdb->query( $prepare );

        if ( $re === false ) {
            jsAlert("Error on inserting payment information");
            return -4002;
        }
        $payment['ID'] = $wpdb->insert_id;
        $payment['AGS_HASHDATA'] = md5($payment['allthegate_account'] . $payment['ID'] . $payment['amt']);
        return 0;
    }
    return -1;
}

/**
 * 결제 로그를 기록한다.
 * @return int - 성공이면 0. 실패면 참.
 */
function payment_insert_log() {
    return 0;
}

function create_table_payment( $table_name ) {

    $sql = <<<EOS
CREATE TABLE IF NOT EXISTS `$table_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL DEFAULT 0,
  `paygate` varchar(32) NOT NULL DEFAULT '',
  `paygate_account` varchar(64) NOT NULL DEFAULT '',
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `method` varchar(64) NOT NULL DEFAULT '',
  `currency` varchar(16) DEFAULT '',
  `amount` int(10) unsigned NOT NULL DEFAULT '0',
  `stamp_create` int(10) unsigned NOT NULL DEFAULT '0',
  `stamp_finish` int(10) unsigned NOT NULL DEFAULT '0',
  `result` char(1) NOT NULL DEFAULT 'N',
  `values_from_user_form` LONGTEXT,
  `values_from_paygate_server` LONGTEXT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `paygate_account` (`paygate_account`),
  KEY `result` (`result`),
  KEY `paygate` (`paygate`),
  KEY `method` (`method`),
  KEY `stamp_create` (`stamp_create`),
  KEY `stamp_finish` (`stamp_finish`)
)

EOS;
    return $sql;
}

function create_table_payment_log( $table_name ) {
    $sql = <<<EOS
CREATE TABLE $table_name (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    payment_id INT UNSIGNED NOT NULL DEFAULT 0,
    message LONGTEXT,
    stamp_create INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY  (id)
);
EOS;
    return $sql;
}
