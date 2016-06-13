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
 * 이 함수는 결제 과정의 처음 부분에서 호출하면 된다.
 *
 * 고유한 랜덤 문자열을 만들어서 $payment['session_id'] 로 보관한다.
 *
 * 이렇게 하므로서 시작부터 끝가지 고유한 session_id 로 로그 추적이나 단계 추적이 가능하다.
 *
 *
 */
function payment_begin_transaction() {
    global $payment;
    $payment['session_id'] = payment_random_string();
    payment_load_allthegate_info();
}

/**
 *
 * AGS_pay.php 에서 payment_begin_transaction() 호출로 session_id 를 만든다.
 *
 * 다음 페이지로 옮길 때, FORM 으로 session_id 를 넘기고, 그 페이지에서 payemnt_resume_transaction( session_id ) 로 해서,
 *
 * 결제 정보를 계속 이어 갈 수 있도록 한다.
 *
 * 즉, 이 함수는 새로운 페이지가 실행 될 때 마다, 이 값을 지정 하면 된다.
 *
 * @param $session_id
 */
function payment_resume_transaction( $session_id ) {
    global $payment;
    $payment['session_id'] = $session_id;
    payment_load_allthegate_info();
    payment_log([
        'action' => 'payment_resume_transaction',
        'message' => 'transaction resumes'
    ]);
}


/**
 * 올더게이트 결제 정보를 로드한다.
 *
 * @note 상점 아이디, 핸드폰 결제 정보 등을 로드한다.
 */
function payment_load_allthegate_info() {
    global $payment;


    $id = get_opt('lms[allthegate_id]');
    if ( ! empty($id) ) $payment['allthegate_id'] = $id;


    $payment['allthegate_cp_id']                        = get_opt('lms[allthegate_cp_id]');
    $payment['allthegate_cp_pwd']                       = get_opt('lms[allthegate_cp_pwd]');
    $payment['allthegate_sub_cp_id']                    = get_opt('lms[allthegate_sub_cp_id]');
    $payment['allthegate_cp_code']                      = get_opt('lms[allthegate_cp_code]');
    $payment['allthegate_item_name']                    = get_opt('lms[allthegate_item_name]');

    $payment['UserName'] = payment_get_user_name();
    $payment['UserPhone'] = payment_get_user_phone();
    $payment['UserAddress'] = payment_get_user_address();
    $payment['RecvName'] = payment_get_user_name();
    $payment['RecvPhone'] = payment_get_user_phone();
    $payment['RecvAddress'] = payment_get_user_address();
    $payment['Remark'] = '';

    $payment['company_name'] = get_opt('lms[company_name]');
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
function payment_get_user_name() {
    switch ( PAYMENT_CMS ) {
        case 'wordpress'            : return user()->name;
        default                     : return 0;
    }
}
function payment_get_user_email() {
    switch ( PAYMENT_CMS ) {
        case 'wordpress'            : return user()->user_email;
        default                     : return 0;
    }
}
function payment_get_user_phone() {
    switch ( PAYMENT_CMS ) {
        case 'wordpress'            : return user()->mobile;
        default                     : return 0;
    }
}
function payment_get_user_address() {
    switch ( PAYMENT_CMS ) {
        case 'wordpress'            : return '';
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


    // min & amount
    if ( isset($_REQUEST['amount']) && $_REQUEST['amount'] ) {
        $payment['amt'] = $_REQUEST['amount'];
    }
    else if ( isset($_REQUEST['amount_input']) || $_REQUEST['amount_input'] ) {
        $payment['amt'] = $_REQUEST['amount_input'];
    }
    else {
        jsAlert("수업 또는 수업료를 선택(입력)하십시오.");
        return -1;
    }


// payment method
    if ( ! isset( $_REQUEST['method'] ) || empty($_REQUEST['method']) ) {
        jsAlert("결재 방식을 선택하십시오.");
        return -2;
    }
    $payment['method'] = $_REQUEST['method'];

    $payment['values_from_user_form'] = serialize( $_REQUEST );



    $date = date("Y-m");
    $payment['SubjectData'] = "$payment[company_name];$payment[allthegate_item_name];$payment[amt];$date"; //업체명;판매상품;계산금액;2012.09.01 ~ 2012.09.30;
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
            $payment['allthegate_id'],
            $payment['method'],
            'KWR',
            $payment['amt'], time(), $payment['values_from_user_form'] );
        //di($prepare);
        $re = $wpdb->query( $prepare );

        if ( $re === false ) {
            jsAlert("Error on inserting payment information");
            return -4002;
        }
        $payment['ID'] = $wpdb->insert_id;
        $payment['AGS_HASHDATA'] = md5($payment['allthegate_id'] . $payment['ID'] . $payment['amt']);
        return 0;
    }
    return -1;
}

/**
 * 결제 로그를 기록한다.
 * @return int - 성공이면 0. 실패면 참.
 */
function payment_insert_log( $data ) {
    global $wpdb, $payment;
    $table = $wpdb->prefix . 'payment_log';
    if ( ! isset( $data['session_id'] ) ) {
        if ( isset($payment['session_id']) ) {
            $data['session_id'] = $payment['session_id'];
        }
        else {
            jsAlert("No session_id");
            exit;
        }
    }
    $q = "INSERT INTO $table ( stamp_create, session_id, action, message ) VALUES ( %d, %s, %s, %s)";
    $prepare = $wpdb->prepare( $q,
        time(),
        $data['session_id'],
        $data['action'],
        $data['message']
    );
    dog( $prepare );
    $re = $wpdb->query( $prepare );
    if ( $re === false ) {
        dog("Error on payment_insert_log()");
        return -4005;
    }
    return 0;
}


function payment_log( $data ) {
    payment_insert_log( $data );
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
    action varchar(255) NOT NULL default '',
    message LONGTEXT,
    session_id varchar(255) NOT NULL default '',
    stamp_create INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY  (id),
    KEY `session_id` (`session_id`)
);
EOS;
    return $sql;
}
