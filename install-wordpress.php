<?php
include_once "database-query.php";
global $wpdb;
$table_payment = $wpdb->prefix . 'payment';
dog( "table_name: $table_payment");
if( $wpdb->get_var( "show tables like '{$table_payment}'" ) == $table_payment ) {
    dog("table_name exists.");
}
else {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $sql = create_table_payment( $table_payment );
    dbDelta( $sql );


    $table_log = $wpdb->prefix . 'payment_log';
    $sql = create_table_payment_log( $table_log );
    dbDelta( $sql );

    dog("table created");
}