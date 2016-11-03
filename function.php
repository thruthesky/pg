<?php
/**
 * @file function.php
 * @desc �⺻ �Լ� ���̺귯��
 */


/**
 * ���� ���ڿ��� �����Ѵ�.
 *
 * @return string
 */
function payment_random_string() {
    return md5(uniqid(mt_rand(), true));
}


/**
 * �� �Լ��� ���� ������ ó�� �κп��� ȣ���ϸ� �ȴ�.
 *
 * ������ ���� ���ڿ��� ���� $payment['session_id'] �� �����Ѵ�.
 *
 * �̷��� �ϹǷμ� ���ۺ��� ������ ������ session_id �� �α� �����̳� �ܰ� ������ �����ϴ�.
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
 * AGS_pay.php ���� payment_begin_transaction() ȣ��� session_id �� �����.
 *
 * ���� �������� �ű� ��, FORM ���� session_id �� �ѱ��, �� ���������� payemnt_resume_transaction( session_id ) �� �ؼ�,
 *
 * ���� ������ ��� �̾� �� �� �ֵ��� �Ѵ�.
 *
 * ��, �� �Լ��� ���ο� �������� ���� �� �� ����, �� ���� ���� �ϸ� �ȴ�.
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
 * �ô�����Ʈ ���� ������ �ε��Ѵ�.
 *
 * @note ���� ���̵�, �ڵ��� ���� ���� ���� �ε��Ѵ�.
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
 * �� CMS �� ȸ�� ��ȣ�� �����Ѵ�.
 *
 * @return int - ȸ����ȣ
 *
 */
function payment_get_user_no() {
    switch ( PAYMENT_CMS ) {
        case 'wordpress'            : return wp_get_current_user()->ID;
        default                     : return 0;
    }
}
function payment_get_user_id() {
    switch ( PAYMENT_CMS ) {
        case 'wordpress'            : return wp_get_current_user()->user_login;
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
 * ���� �� ���� �Է� �� üũ
 *
 * @return int - �����̸� 0 �� ����.
 */
function payment_check_input() {

    global $payment;

    // min & amount
    if ( isset($_REQUEST['amount']) && $_REQUEST['amount'] ) {
        $payment['amt'] = $_REQUEST['amount'];
    }
    else if ( isset($_REQUEST['amount_input']) && $_REQUEST['amount_input'] ) {
        $payment['amt'] = $_REQUEST['amount_input'];
    }
    else {
        $payment['amt'] = 0;
        return "���� �Ǵ� �����Ḧ ����(�Է�)�Ͻʽÿ�.";
    }

    $org_amount = $payment['amt'];

    // discount on days. (disabled to remove discount)
    //if ( isset($_REQUEST['days']) && $payment['amt'] ) {
    //    if ( $_REQUEST['days'] == 4 ) $payment['amt'] = $payment['amt'] - ( $org_amount * 5 / 100 );
    //    else if ( $_REQUEST['days'] == 3 ) $payment['amt'] = $payment['amt'] - ( $org_amount * 10 / 100 );
    //}

    // discount on curriculum.
    if ( isset($_REQUEST['curriculum']) && $payment['amt'] ) {
        $arr = explode(':', $_REQUEST['curriculum']);
        if ( $arr[0] ) {
            $payment['amt'] = $payment['amt'] - ( $org_amount * $arr[0] / 100 );
        }
    }




// payment method
    if ( ! isset( $_REQUEST['method'] ) || empty($_REQUEST['method']) ) {
        return "���� ����� �����Ͻʽÿ�.";
    }
    $payment['method'] = $_REQUEST['method'];

    $payment['values_from_user_form'] = serialize( $_REQUEST );



    $date = date("Y-m");
    $item = str_replace(' ', '', $payment['allthegate_item_name']);
    $payment['SubjectData'] = "$payment[company_name];$item;$payment[amt];$date"; //��ü��;�ǸŻ�ǰ;���ݾ�;2012.09.01 ~ 2012.09.30;
    $payment['UserId'] = payment_get_user_id();

    return 0;
}

/**
 * ����� ���� ������ �����ͺ��̽� �����Ѵ�.
 *
 * @note �����ϰ� ���̺��� ID ���� $GLOBALS[payment][ID] �� �����Ѵ�.
 *
 * @return int - �����̸� 0, �����̸� ��.
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
            $q, payment_get_user_no(),
            $payment['session_id'],
            'allthegate',
            $payment['allthegate_id'],
            $payment['method'],
            'KWR',
            $payment['amt'], time(), $payment['values_from_user_form'] );
        //di($prepare);
        $re = $wpdb->query( $prepare );

        if ( $re === false ) {
            return "Error on inserting payment information";
        }
        $payment['ID'] = $wpdb->insert_id;
        $payment['AGS_HASHDATA'] = md5($payment['allthegate_id'] . $payment['ID'] . $payment['amt']);
        return 0;
    }
    return -1;
}

function payment_list() {

}


/**
 * ���� �α׸� ����Ѵ�.
 * @return int - �����̸� 0. ���и� ��.
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


/**
 *
 * ������ ���������� �� ����� DB �� �����Ѵ�.
 *
 * �⺻������ $payment ������ �޾Ƽ� ����������, ���� ������ ��쿡�� session_id ���� ���� ���� ���ϹǷ� $id ���� �޴´�.
 *
 * @return int
 *
 *
 */
function payment_success( $id = 0 ) {
    global $wpdb, $payment;
    $table = $wpdb->prefix . 'payment';
    if ( $id ) {
        $q = "UPDATE $table SET stamp_finish=%d, result=%s WHERE id=%d";
        $cond_value = $id;
    }
    else {
        $q = "UPDATE $table SET stamp_finish=%d, result=%s WHERE session_id=%s";
        $cond_value = $payment['session_id'];
    }
    $prepare = $wpdb->prepare( $q,
        time(),
        'Y',
        $cond_value
    );
    dog( $prepare );
    $re = $wpdb->query( $prepare );
    if ( $re === false ) {
        dog("Database error on payment_success()");
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
