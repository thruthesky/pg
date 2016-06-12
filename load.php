<?php
dog("load.php");
include 'function.php';
include 'config.php';


if ( PAYMENT_CMS == 'wordpress' ) include 'install-wordpress.php';
