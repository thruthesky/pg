<?php
/**
 *
 */
header('Content-type: text/html; charset=euc-kr');
global $payment;

payment_begin_transaction();
di( $payment );
payment_log([
    'action' => 'AGS_pay.php-payment_transaction_begin',
    'message' => 'Begin transaction'
]);

if ( $error = payment_check_input() ) {
    payment_log( [
        'action' => 'AGS_pay.php-payment-check-input-error',
        'message' => 'error on payment_check_input()'
    ] );
}
else {
    if ( $error = payment_insert_info() ) {
        payment_log( [
            'action' => 'AGS_pay.php-payment-insert-info-error',
            'message' => 'failed on payment_insert_info()'
        ] );
    }
    $AGS_HASHDATA = $payment['AGS_HASHDATA'];
}

?>
<!doctype html>
<html>
<head>
    <meta charset="euc-kr">

<script language=javascript>
    <!--
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // �ô�����Ʈ �÷����� ��ġ�� Ȯ���մϴ�.
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////



    function Pay(form){


        var error = "<?php echo $error ?>";
        if ( error && error != 0 ) {
            alert( error  );
            return;
        }

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // MakePayMessage() �� ȣ��Ǹ� �ô�����Ʈ �÷������� ȭ�鿡 ��Ÿ���� Hidden �ʵ�
        // �� ���ϰ����� ä������ �˴ϴ�.
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////

        if(form.Flag.value == "enable"){
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // �Էµ� ����Ÿ�� ��ȿ���� �˻��մϴ�.
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////

            if(Check_Common(form) == true){
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // �ô�����Ʈ �÷����� ��ġ�� �ùٸ��� �Ǿ����� Ȯ���մϴ�.
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                if(document.AGSPay == null || document.AGSPay.object == null){
                    alert("�÷����� ��ġ �� �ٽ� �õ� �Ͻʽÿ�.");
                }else{
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // �ô�����Ʈ �÷����� �������� �������� �����ϱ� JavaScript �ڵ带 ����ϰ� �ֽ��ϴ�.
                    // ���������� �°� JavaScript �ڵ带 �����Ͽ� ����Ͻʽÿ�.
                    //
                    // [1] �Ϲ�/������ ��������
                    // [2] �Ϲݰ����� �Һΰ�����
                    // [3] �����ڰ����� �Һΰ����� ����
                    // [4] ��������
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // [1] �Ϲ�/������ �������θ� �����մϴ�.
                    //
                    // �Һ��Ǹ��� ��� �����ڰ� ���ڼ����Ḧ �δ��ϴ� ���� �⺻�Դϴ�. �׷���,
                    // ������ �ô�����Ʈ���� ���� ����� ���ؼ� �Һ����ڸ� ���������� �δ��� �� �ֽ��ϴ�.
                    // �̰�� �����ڴ� ������ �Һΰŷ��� �����մϴ�.
                    //
                    // ����)
                    // 	(1) �Ϲݰ����� ����� ���
                    // 	form.DeviId.value = "9000400001";
                    //
                    // 	(2) �����ڰ����� ����� ���
                    // 	form.DeviId.value = "9000400002";
                    //
                    // 	(3) ���� ���� �ݾ��� 100,000�� �̸��� ��� �Ϲ��Һη� 100,000�� �̻��� ��� �������Һη� ����� ���
                    // 	if(parseInt(form.Amt.value) < 100000)
                    //		form.DeviId.value = "9000400001";
                    // 	else
                    //		form.DeviId.value = "9000400002";
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    form.DeviId.value = "9000400001";

                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // [2] �Ϲ� �ҺαⰣ�� �����մϴ�.
                    //
                    // �Ϲ� �ҺαⰣ�� 2 ~ 12�������� �����մϴ�.
                    // 0:�Ͻú�, 2:2����, 3:3����, ... , 12:12����
                    //
                    // ����)
                    // 	(1) �ҺαⰣ�� �ϽúҸ� �����ϵ��� ����� ���
                    // 	form.QuotaInf.value = "0";
                    //
                    // 	(2) �ҺαⰣ�� �Ͻú� ~ 12�������� ����� ���
                    //		form.QuotaInf.value = "0:3:4:5:6:7:8:9:10:11:12";
                    //
                    // 	(3) �����ݾ��� ���������ȿ� ���� ��쿡�� �Һΰ� �����ϰ� �� ���
                    // 	if((parseInt(form.Amt.value) >= 100000) || (parseInt(form.Amt.value) <= 200000))
                    // 		form.QuotaInf.value = "0:2:3:4:5:6:7:8:9:10:11:12";
                    // 	else
                    // 		form.QuotaInf.value = "0";
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    //�����ݾ��� 5���� �̸����� �Һΰ����� ��û�Ұ�� ��������
                    if(parseInt(form.Amt.value) < 50000)
                        form.QuotaInf.value = "0";
                    else
                        form.QuotaInf.value = "0:2:3:4:5:6:7:8:9:10:11:12";

                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // [3] ������ �ҺαⰣ�� �����մϴ�.
                    // (�Ϲݰ����� ��쿡�� �� ������ ������� �ʽ��ϴ�.)
                    //
                    // ������ �ҺαⰣ�� 2 ~ 12�������� �����ϸ�,
                    // �ô�����Ʈ���� ������ �Һ� ������������ �����ؾ� �մϴ�.
                    //
                    // 100:BC
                    // 200:����
                    // 201:NH
                    // 300:��ȯ
                    // 310:�ϳ�SK
                    // 400:�Ｚ
                    // 500:����
                    // 800:����
                    // 900:�Ե�
                    //
                    // ����)
                    // 	(1) ��� �Һΰŷ��� �����ڷ� �ϰ� ���������� ALL�� ����
                    // 	form.NointInf.value = "ALL";
                    //
                    // 	(2) ����ī�� Ư���������� �����ڸ� �ϰ� ������� ����(2:3:4:5:6����)
                    // 	form.NointInf.value = "200-2:3:4:5:6";
                    //
                    // 	(3) ��ȯī�� Ư���������� �����ڸ� �ϰ� ������� ����(2:3:4:5:6����)
                    // 	form.NointInf.value = "300-2:3:4:5:6";
                    //
                    // 	(4) ����,��ȯī�� Ư���������� �����ڸ� �ϰ� ������� ����(2:3:4:5:6����)
                    // 	form.NointInf.value = "200-2:3:4:5:6,300-2:3:4:5:6";
                    //
                    //	(5) ������ �ҺαⰣ ������ ���� ���� ��쿡�� NONE�� ����
                    //	form.NointInf.value = "NONE";
                    //
                    //	(6) ��ī��� Ư���������� �����ڸ� �ϰ� �������(2:3:6����)
                    //	form.NointInf.value = "100-2:3:6,200-2:3:6,201-2:3:6,300-2:3:6,310-2:3:6,400-2:3:6,500-2:3:6,800-2:3:6,900-2:3:6";
                    //
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    if(form.DeviId.value == "9000400002")
                        form.NointInf.value = "ALL";


                    if(MakePayMessage(form) == true){
                        Disable_Flag(form);
                        // var openwin = window.open("AGS_progress.html","popup","width=300,height=160"); //"����ó����"�̶�� �˾�â���� �κ�
                        form.submit();
                    }
                    else{
                        alert("���ҿ� �����Ͽ����ϴ�.");// ��ҽ� �̵������� �����κ�
                    }
                }
            }
        }
    }

    function Enable_Flag(form){
        form.Flag.value = "enable"
    }

    function Disable_Flag(form){
        form.Flag.value = "disable"
    }

    function Check_Common(form){
        if(form.StoreId.value == ""){
            alert("�������̵� �Է��Ͻʽÿ�.");
            return false;
        }
        else if(form.StoreNm.value == ""){
            alert("�������� �Է��Ͻʽÿ�.");
            return false;
        }
        else if(form.OrdNo.value == ""){
            alert("�ֹ���ȣ�� �Է��Ͻʽÿ�.");
            return false;
        }
        else if(form.ProdNm.value == ""){
            alert("��ǰ���� �Է��Ͻʽÿ�.");
            return false;
        }
        else if(form.Amt.value == ""){
            alert("�ݾ��� �Է��Ͻʽÿ�.");
            return false;
        }
        else if(form.MallUrl.value == ""){
            alert("����URL�� �Է��Ͻʽÿ�.");
            return false;
        }
        return true;
    }

    function Display(form){
        if(form.Job.value == "onlycard" || form.TempJob.value == "onlycard"){
            document.all.card_hp.style.display= "";
            document.all.card.style.display= "";
            document.all.hp.style.display= "none";
            document.all.virtual.style.display= "none";
        }else if(form.Job.value == "onlyhp" || form.TempJob.value == "onlyhp"){
            document.all.card_hp.style.display= "";
            document.all.card.style.display= "none";
            document.all.hp.style.display= "";
            document.all.virtual.style.display= "none";
        }else if(form.Job.value == "onlyvirtual" || form.TempJob.value == "onlyvirtual" ){
            document.all.card_hp.style.display= "none";
            document.all.card.style.display= "";
            document.all.hp.style.display= "none";
            document.all.virtual.style.display= "";
        }else if(form.Job.value == "onlyiche" || form.TempJob.value == "onlyiche"  ){
            document.all.card_hp.style.display= "none";
            document.all.card.style.display= "none";
            document.all.hp.style.display= "none";
            document.all.virtual.style.display= "none";
        }else{
            document.all.card_hp.style.display= "";
            document.all.card.style.display= "";
            document.all.hp.style.display= "";
            document.all.virtual.style.display= "";
        }
    }
    -->
</script>
</head>
<body topmargin=0 leftmargin=0 rightmargin=0 bottommargin=0 onload="javascript:Enable_Flag(frmAGS_pay);">
<section class="AGS_pay">
    <div>
        <form name="frmAGS_pay" method=post action="<?php echo home_url()?>/enrollment?mode=AGS_pay_ing" target="_parent">
		<input type="hidden" name="layout" value="no">
            <input type="hidden" name="session_id" value="<?php echo $payment['session_id']?>">
            <input type="hidden" name="StoreId" maxlength=20 value="<?php echo $payment['allthegate_id']?>">
            <input type="hidden" name="OrdNo" maxlength=40 value="<?php echo $payment['ID']?>">
            <input type="hidden" name="Job" maxlength=12 value="<?php echo $payment['method']?>">
            <input type="hidden" name="Amt" maxlength=12 value="<?php echo $payment['amt']?>">
            <input type="hidden" name="StoreNm" value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['company_name'])?>">
            <input type="hidden" name="ProdNm" maxlength=300 value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['allthegate_item_name'])?>">
            <input type="hidden" name="MallUrl" value="<?php echo $payment['MallUrl']?>">
            <input type="hidden" name="UserEmail" maxlength=50 value="<?php echo $payment['UserEmail']?>">

            <!-- ����â ������ܿ� ������ �ΰ��̹���(85 * 38)�� ǥ���� �� �ֽ��ϴ�.  �߸��� ���� �Է��ϰų� ���Է½� �������ô�����Ʈ�� �ΰ� ǥ�õ˴ϴ�. -->
            <input type="hidden" name=ags_logoimg_url maxlength=200 value="<?php echo $payment['ags_logoimg_url']?>">

            <!-- ������ 1�������� 5�� �̳��̸�, ������;��ǰ��;�����ݾ�;�����Ⱓ; ������ �Է��� �ּž� �մϴ�. �Է� ��)��ü��;�ǸŻ�ǰ;���ݾ�;�����Ⱓ; -->
            <input type="hidden" name="SubjectData" value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['SubjectData'])?>">
            <input type="hidden" name="UserId" maxlength=20 value="<?php echo $payment['UserId']?>">

            <!-- ī�� & ���� ���� ���� �� ���� -->
            <input type="hidden" name="OrdNm" maxlength=40 value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['UserName'])?>"><!--�ֹ��� �̸�-->
            <input type="hidden" name="OrdPhone" maxlength=21 value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['UserPhone'])?>"><!--�ֹ��� ����ó-->
            <input type="hidden" name="OrdAddr" maxlength=100 value="�ֹ��� �ּ� <?php echo $payment['UserAddress']?>"><!--�ֹ��� �ּ�-->
            <input type="hidden" name="RcpNm" maxlength=40 value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['RecvName'])?>"><!-- �����ڸ�-->
            <input type="hidden" name="RcpPhone" maxlength=21 value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['RecvPhone'])?>"><!-- ������ ����ó -->
            <input type="hidden" name="DlvAddr" maxlength=100 value="����� �ּ� <?php echo $payment['RecvAddress']?>"><!--����� �ּ�-->
            <input type="hidden" name="Remark" maxlength=350 value="<?php echo iconv('UTF-8', 'EUC-KR', $payment['Remark'])?>..."><!--��Ÿ �䱸���� -->
            <input type=hidden style=width:300px name=CardSelect value=""><!--ī��� ���� : Ư�� ī�常 ����ϰ��� �ϴ� ���. �� ���� �Է��ϸ� ��� ī��� ���. ī��� �ڵ�� �Ŵ��󿡼� Ȯ��-->
            <!-- EO ī�� & ���� ���� ���� �� ���� -->
            <!-- ������� ���� �� ���� -->
            <input type="hidden" name="MallPage" value="<?php echo $payment['MallPage']?>"><!-- ���� ��/��� �뺸 URL. �������� ������ ������ �Է� -->
            <input type="hidden" name="VIRTUAL_DEPODT" value=""><!-- �Ա� ���� ��. ���ϱ��� �Ա��϶�� �Ⱓ�� ���� ��. �ִ� 15��. �����ϸ� �⺻ 5��. ��) 20001122-->


            <!-- �ڵ��� ���� �� ����. �ڵ��� ������ ���� �ʴ��� ���� �־� �ش�. �ȱ׷��� PHP ���� ���� Undefiend ��� ������ ����. -->
            <input type=hidden name=HP_ID maxlength=10 value="<?php echo $payment['allthegate_cp_id']?>">
            <input type=hidden name=HP_PWD maxlength=10 value="<?php echo $payment['allthegate_cp_pwd']?>">
            <input type=hidden name=HP_SUBID maxlength=10 value="<?php echo $payment['allthegate_sub_cp_id']?>">
            <input type=hidden  name=ProdCode maxlength=10 value="<?php echo $payment['allthegate_cp_code']?>">
            <input type="hidden" name=HP_UNITType value="1">


            <!-- ��ũ��Ʈ �� �÷����ο��� ���� �����ϴ� Hidden �ʵ�  !!������ �Ͻðų� �������� ���ʽÿ�-->

            <!-- �� ���� ���� ��� ���� -->
            <input type=hidden name=Flag value="">				<!-- ��ũ��Ʈ������뱸���÷��� -->
            <input type=hidden name=AuthTy value="">			<!-- �������� -->
            <input type=hidden name=SubTy value="">				<!-- ����������� -->
            <input type=hidden name=AGS_HASHDATA value="<?php echo $AGS_HASHDATA?>">	<!-- ��ȣȭ HASHDATA -->

            <!-- �ſ�ī�� ���� ��� ���� -->
            <input type=hidden name=DeviId value="">			<!-- (�ſ�ī�����)		�ܸ�����̵� -->
            <input type=hidden name=QuotaInf value="0">			<!-- (�ſ�ī�����)		�Ϲ��Һΰ����������� -->
            <input type=hidden name=NointInf value="NONE">		<!-- (�ſ�ī�����)		�������Һΰ����������� -->
            <input type=hidden name=AuthYn value="">			<!-- (�ſ�ī�����)		�������� -->
            <input type=hidden name=Instmt value="">			<!-- (�ſ�ī�����)		�Һΰ����� -->
            <input type=hidden name=partial_mm value="">		<!-- (ISP���)			�Ϲ��ҺαⰣ -->
            <input type=hidden name=noIntMonth value="">		<!-- (ISP���)			�������ҺαⰣ -->
            <input type=hidden name=KVP_RESERVED1 value="">		<!-- (ISP���)			RESERVED1 -->
            <input type=hidden name=KVP_RESERVED2 value="">		<!-- (ISP���)			RESERVED2 -->
            <input type=hidden name=KVP_RESERVED3 value="">		<!-- (ISP���)			RESERVED3 -->
            <input type=hidden name=KVP_CURRENCY value="">		<!-- (ISP���)			��ȭ�ڵ� -->
            <input type=hidden name=KVP_CARDCODE value="">		<!-- (ISP���)			ī����ڵ� -->
            <input type=hidden name=KVP_SESSIONKEY value="">	<!-- (ISP���)			��ȣȭ�ڵ� -->
            <input type=hidden name=KVP_ENCDATA value="">		<!-- (ISP���)			��ȣȭ�ڵ� -->
            <input type=hidden name=KVP_CONAME value="">		<!-- (ISP���)			ī��� -->
            <input type=hidden name=KVP_NOINT value="">			<!-- (ISP���)			������/�Ϲݿ���(������=1, �Ϲ�=0) -->
            <input type=hidden name=KVP_QUOTA value="">			<!-- (ISP���)			�Һΰ��� -->
            <input type=hidden name=CardNo value="">			<!-- (�Ƚ�Ŭ��,�Ϲݻ��)	ī���ȣ -->
            <input type=hidden name=MPI_CAVV value="">			<!-- (�Ƚ�Ŭ��,�Ϲݻ��)	��ȣȭ�ڵ� -->
            <input type=hidden name=MPI_ECI value="">			<!-- (�Ƚ�Ŭ��,�Ϲݻ��)	��ȣȭ�ڵ� -->
            <input type=hidden name=MPI_MD64 value="">			<!-- (�Ƚ�Ŭ��,�Ϲݻ��)	��ȣȭ�ڵ� -->
            <input type=hidden name=ExpMon value="">			<!-- (�Ϲݻ��)			��ȿ�Ⱓ(��) -->
            <input type=hidden name=ExpYear value="">			<!-- (�Ϲݻ��)			��ȿ�Ⱓ(��) -->
            <input type=hidden name=Passwd value="">			<!-- (�Ϲݻ��)			��й�ȣ -->
            <input type=hidden name=SocId value="">				<!-- (�Ϲݻ��)			�ֹε�Ϲ�ȣ/����ڵ�Ϲ�ȣ -->

            <!-- ������ü ���� ��� ���� -->
            <input type=hidden name=ICHE_OUTBANKNAME value="">	<!-- ��ü��������� -->
            <input type=hidden name=ICHE_OUTACCTNO value="">	<!-- ��ü���¿������ֹι�ȣ -->
            <input type=hidden name=ICHE_OUTBANKMASTER value=""><!-- ��ü���¿����� -->
            <input type=hidden name=ICHE_AMOUNT value="">		<!-- ��ü�ݾ� -->

            <!-- �ڵ��� ���� ��� ���� -->
            <input type=hidden name=HP_SERVERINFO value="">		<!-- �������� -->
            <input type=hidden name=HP_HANDPHONE value="">		<!-- �ڵ�����ȣ -->
            <input type=hidden name=HP_COMPANY value="">		<!-- ��Ż��(SKT,KTF,LGT) -->
            <input type=hidden name=HP_IDEN value="">			<!-- �����û�� -->
            <input type=hidden name=HP_IPADDR value="">			<!-- ���������� -->

            <!-- ARS ���� ��� ���� -->
            <input type=hidden name=ARS_PHONE value="">			<!-- ARS��ȣ -->
            <input type=hidden name=ARS_NAME value="">			<!-- ��ȭ�����ڸ� -->

            <!-- ������� ���� ��� ���� -->
            <input type=hidden name=ZuminCode value="">			<!-- ��������Ա����ֹι�ȣ -->
            <input type=hidden name=VIRTUAL_CENTERCD value="">	<!-- ������������ڵ� -->
            <input type=hidden name=VIRTUAL_NO value="">		<!-- ������¹�ȣ -->

            <input type=hidden name=mTId value="">

            <!-- ����ũ�� ���� ��� ���� -->
            <input type=hidden name=ES_SENDNO value="">			<!-- ����ũ��������ȣ -->

            <!-- ������ü(����) ���� ��� ���� -->
            <input type=hidden name=ICHE_SOCKETYN value="">		<!-- ������ü(����) ��� ���� -->
            <input type=hidden name=ICHE_POSMTID value="">		<!-- ������ü(����) �̿����ֹ���ȣ -->
            <input type=hidden name=ICHE_FNBCMTID value="">		<!-- ������ü(����) FNBC�ŷ���ȣ -->
            <input type=hidden name=ICHE_APTRTS value="">		<!-- ������ü(����) ��ü �ð� -->
            <input type=hidden name=ICHE_REMARK1 value="">		<!-- ������ü(����) ��Ÿ����1 -->
            <input type=hidden name=ICHE_REMARK2 value="">		<!-- ������ü(����) ��Ÿ����2 -->
            <input type=hidden name=ICHE_ECWYN value="">		<!-- ������ü(����) ����ũ�ο��� -->
            <input type=hidden name=ICHE_ECWID value="">		<!-- ������ü(����) ����ũ��ID -->
            <input type=hidden name=ICHE_ECWAMT1 value="">		<!-- ������ü(����) ����ũ�ΰ����ݾ�1 -->
            <input type=hidden name=ICHE_ECWAMT2 value="">		<!-- ������ü(����) ����ũ�ΰ����ݾ�2 -->
            <input type=hidden name=ICHE_CASHYN value="">		<!-- ������ü(����) ���ݿ��������࿩�� -->
            <input type=hidden name=ICHE_CASHGUBUN_CD value="">	<!-- ������ü(����) ���ݿ��������� -->
            <input type=hidden name=ICHE_CASHID_NO value="">	<!-- ������ü(����) ���ݿ������ź�Ȯ�ι�ȣ -->

            <!-- �ڷ���ŷ-������ü(����) ���� ��� ���� -->
            <input type=hidden name=ICHEARS_SOCKETYN value="">	<!-- �ڷ���ŷ������ü(����) ��� ���� -->
            <input type=hidden name=ICHEARS_ADMNO value="">		<!-- �ڷ���ŷ������ü ���ι�ȣ -->
            <input type=hidden name=ICHEARS_POSMTID value="">	<!-- �ڷ���ŷ������ü �̿����ֹ���ȣ -->
            <input type=hidden name=ICHEARS_CENTERCD value="">	<!-- �ڷ���ŷ������ü �����ڵ� -->
            <input type=hidden name=ICHEARS_HPNO value="">		<!-- �ڷ���ŷ������ü �޴�����ȣ -->

            <!-- ��ũ��Ʈ �� �÷����ο��� ���� �����ϴ� Hidden �ʵ�  !!������ �Ͻðų� �������� ���ʽÿ�-->



            <style scoped>
                .pay-buttons {
                    display: block;
                    float: right;
                    margin: 1em 0;
                    width: 320px;
                }
                .total {
                    padding: .4em;
                    background-color: #00a0d2;
                    color: white;
                    font-size: 1.2em;
                    font-family: "Malgun Gothic", serif;
                }
                .pay-button input {
                    margin: .4em 0 0 0;
                    background-color: #4f160d;
                    padding: .4em;
                    width: 100%;
                    box-sizing: border-box;
                    border: 0;
                    color: white;
                    font-size: 1.2em;
                    font-family: "Malgun Gothic", serif;
                }
            </style>
            <nav class="pay-buttons">
                <?php
                ob_start();
                _text('Payment Total : ');
                $total = ob_get_clean();
                ob_start();
                _text('Pay Now');
                $pay_now = ob_get_clean();
                ?>
                <div class="total"><?php echo iconv('UTF-8', 'EUC-KR', $total)?> <?php echo number_format($payment['amt'])?></div>
                <div class="pay-button"><input type="button" value="<?php echo iconv('UTF-8', 'EUC-KR', $pay_now)?>" onclick="javascript:Pay(frmAGS_pay);"></div>
            </nav>

        </form>

        <?php
        /**


        <div>
        <table>
        <tr valign="top">
        <td>
        <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
        </td>
        <td>
        ��û�� ���� ó�����Դϴ�.<br>
        ��ø� ��ٷ� �ּ���.
        </td>
        </tr>
        </table>
        </div>

        <a href="javascript:history.go(-1)">���ư���</a>
         */

        ?>

    </div>
</section>
<?php

payment_log( [
    'action' => 'Opening ActiveX',
    'message' => "AGS_pay.php >> Opening ActiveX for verification. Or if it just pass to AGS_pay_ing.php without verification if is's debug mode."
] );
?>
<script>
    <?php if ( PAYMENT_DEBUG && PAYMENT_DEBUG_NO_ACTIVEX ) { ?>
    // PAYMENT_DEBUG_NO_ACTIVEX �� ���̸� ActiveX ���� Ȯ�� ����.
    frmAGS_pay.submit();
    <?php } else { ?>
	
    <?php } ?>
</script>

</body>
</html>
