<?php
dog("load.php");

define( 'PAYMENT_GATEWAY_DIR', dirname( __FILE__ ) . '/' ); // 필수 : 각 CMS 에 맞게 수정.

include PAYMENT_GATEWAY_DIR . 'function.php';
include PAYMENT_GATEWAY_DIR . 'config.php';


if ( PAYMENT_CMS == 'wordpress' ) include 'install-wordpress.php';
