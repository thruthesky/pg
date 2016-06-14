<script>
    function show_receipt(appr_tm, sRetailer_id, approve, send_no )
    {
        var send_dt = appr_tm;
        var url = "http://www.allthegate.com/customer/receiptLast3.jsp";
        url=url+"?sRetailer_id="+sRetailer_id;
        url=url+"&approve="+approve;
        url=url+"&send_no="+send_no;
        url=url+"&send_dt="+send_dt.substring(0,8);

        window.open(url, "window","toolbar=no,location=no,directories=no,status=,menubar=no,scrollbars=no,resizable=no,width=420,height=700,top=0,left=150");
    }
</script>
<?php

echo "<h1>Payment List</h1>";


global $wpdb;
$table = $wpdb->prefix . 'payment';
$id = payment_get_user_no();
$rows = $wpdb->get_results(
    "SELECT * FROM $table WHERE result='Y' AND user_id = $id ORDER BY id DESC",
    ARRAY_A
);


foreach( $rows as $row ) {
    $data = unserialize($row['values_from_paygate_server']);
    $date = date("Y-m-d h:i a", $row['stamp_finish']);
    $method = $data['AuthTy'];
    $Storeid = get_opt('lms[allthegate_id]');
    if ( $row['method'] == 'onlycardselfnormal' || $method == 'card' ) {
	$method = '신용카드';
$print_receipt = <<<EOS
<a href="javascript:show_receipt('$data[rApprTm]', '$Storeid', '$data[rApprNo]', '$data[rDealNo]');">
Print Receipt
</a>
EOS;
}

    else if ( $row['method'] == 'onlyicheselfnormal' ) {
		$method = '계좌이체';
	}
    else if ( $row['method'] == 'onlyvirtualselfnormal' ) {
		$method = '1회용 무통장';
}

    else $print_receipt = '';
    echo "
    <div class='line'>
	$row[id] :
    $date
    $row[amount]
    $method $print_receipt

    </div>
    ";
}

