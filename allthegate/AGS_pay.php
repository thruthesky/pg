<?php
/**
 *
 */
global $payment;


payment_begin_transaction();
payment_log([
    'action' => 'AGS_pay.php-payment_transaction_begin',
    'message' => 'Begin transaction'
]);

if ( payment_check_input() ) {
    payment_log( [
        'action' => 'AGS_pay.php-payment-check-input-error',
        'message' => 'error on payment_check_input()'
    ] );
    return;
}

if ( payment_insert_info() ) {

    payment_log( [
        'action' => 'AGS_pay.php-payment-insert-info-error',
        'message' => 'failed on payment_insert_info()'
    ] );

    return;
}




$AGS_HASHDATA = $payment['AGS_HASHDATA'];
?>

<script language=javascript>
    <!--
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // 올더게이트 플러그인 설치를 확인합니다.
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////



    function Pay(form){
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // MakePayMessage() 가 호출되면 올더게이트 플러그인이 화면에 나타나며 Hidden 필드
        // 에 리턴값들이 채워지게 됩니다.
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////

        if(form.Flag.value == "enable"){
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // 입력된 데이타의 유효성을 검사합니다.
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////

            if(Check_Common(form) == true){
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // 올더게이트 플러그인 설치가 올바르게 되었는지 확인합니다.
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                if(document.AGSPay == null || document.AGSPay.object == null){
                    alert("플러그인 설치 후 다시 시도 하십시오.");
                }else{
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // 올더게이트 플러그인 설정값을 동적으로 적용하기 JavaScript 코드를 사용하고 있습니다.
                    // 상점설정에 맞게 JavaScript 코드를 수정하여 사용하십시오.
                    //
                    // [1] 일반/무이자 결제여부
                    // [2] 일반결제시 할부개월수
                    // [3] 무이자결제시 할부개월수 설정
                    // [4] 인증여부
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // [1] 일반/무이자 결제여부를 설정합니다.
                    //
                    // 할부판매의 경우 구매자가 이자수수료를 부담하는 것이 기본입니다. 그러나,
                    // 상점과 올더게이트간의 별도 계약을 통해서 할부이자를 상점측에서 부담할 수 있습니다.
                    // 이경우 구매자는 무이자 할부거래가 가능합니다.
                    //
                    // 예제)
                    // 	(1) 일반결제로 사용할 경우
                    // 	form.DeviId.value = "9000400001";
                    //
                    // 	(2) 무이자결제로 사용할 경우
                    // 	form.DeviId.value = "9000400002";
                    //
                    // 	(3) 만약 결제 금액이 100,000원 미만일 경우 일반할부로 100,000원 이상일 경우 무이자할부로 사용할 경우
                    // 	if(parseInt(form.Amt.value) < 100000)
                    //		form.DeviId.value = "9000400001";
                    // 	else
                    //		form.DeviId.value = "9000400002";
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    form.DeviId.value = "9000400001";

                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // [2] 일반 할부기간을 설정합니다.
                    //
                    // 일반 할부기간은 2 ~ 12개월까지 가능합니다.
                    // 0:일시불, 2:2개월, 3:3개월, ... , 12:12개월
                    //
                    // 예제)
                    // 	(1) 할부기간을 일시불만 가능하도록 사용할 경우
                    // 	form.QuotaInf.value = "0";
                    //
                    // 	(2) 할부기간을 일시불 ~ 12개월까지 사용할 경우
                    //		form.QuotaInf.value = "0:3:4:5:6:7:8:9:10:11:12";
                    //
                    // 	(3) 결제금액이 일정범위안에 있을 경우에만 할부가 가능하게 할 경우
                    // 	if((parseInt(form.Amt.value) >= 100000) || (parseInt(form.Amt.value) <= 200000))
                    // 		form.QuotaInf.value = "0:2:3:4:5:6:7:8:9:10:11:12";
                    // 	else
                    // 		form.QuotaInf.value = "0";
                    //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    //결제금액이 5만원 미만건을 할부결제로 요청할경우 결제실패
                    if(parseInt(form.Amt.value) < 50000)
                        form.QuotaInf.value = "0";
                    else
                        form.QuotaInf.value = "0:2:3:4:5:6:7:8:9:10:11:12";

                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    // [3] 무이자 할부기간을 설정합니다.
                    // (일반결제인 경우에는 본 설정은 적용되지 않습니다.)
                    //
                    // 무이자 할부기간은 2 ~ 12개월까지 가능하며,
                    // 올더게이트에서 제한한 할부 개월수까지만 설정해야 합니다.
                    //
                    // 100:BC
                    // 200:국민
                    // 201:NH
                    // 300:외환
                    // 310:하나SK
                    // 400:삼성
                    // 500:신한
                    // 800:현대
                    // 900:롯데
                    //
                    // 예제)
                    // 	(1) 모든 할부거래를 무이자로 하고 싶을때에는 ALL로 설정
                    // 	form.NointInf.value = "ALL";
                    //
                    // 	(2) 국민카드 특정개월수만 무이자를 하고 싶을경우 샘플(2:3:4:5:6개월)
                    // 	form.NointInf.value = "200-2:3:4:5:6";
                    //
                    // 	(3) 외환카드 특정개월수만 무이자를 하고 싶을경우 샘플(2:3:4:5:6개월)
                    // 	form.NointInf.value = "300-2:3:4:5:6";
                    //
                    // 	(4) 국민,외환카드 특정개월수만 무이자를 하고 싶을경우 샘플(2:3:4:5:6개월)
                    // 	form.NointInf.value = "200-2:3:4:5:6,300-2:3:4:5:6";
                    //
                    //	(5) 무이자 할부기간 설정을 하지 않을 경우에는 NONE로 설정
                    //	form.NointInf.value = "NONE";
                    //
                    //	(6) 전카드사 특정개월수만 무이자를 하고 싶은경우(2:3:6개월)
                    //	form.NointInf.value = "100-2:3:6,200-2:3:6,201-2:3:6,300-2:3:6,310-2:3:6,400-2:3:6,500-2:3:6,800-2:3:6,900-2:3:6";
                    //
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    if(form.DeviId.value == "9000400002")
                        form.NointInf.value = "ALL";

                    if(MakePayMessage(form) == true){
                        Disable_Flag(form);
                        // var openwin = window.open("AGS_progress.html","popup","width=300,height=160"); //"지불처리중"이라는 팝업창연결 부분
                        form.submit();
                    }
                    else{
                        alert("지불에 실패하였습니다.");// 취소시 이동페이지 설정부분
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
            alert("상점아이디를 입력하십시오.");
            return false;
        }
        else if(form.StoreNm.value == ""){
            alert("상점명을 입력하십시오.");
            return false;
        }
        else if(form.OrdNo.value == ""){
            alert("주문번호를 입력하십시오.");
            return false;
        }
        else if(form.ProdNm.value == ""){
            alert("상품명을 입력하십시오.");
            return false;
        }
        else if(form.Amt.value == ""){
            alert("금액을 입력하십시오.");
            return false;
        }
        else if(form.MallUrl.value == ""){
            alert("상점URL을 입력하십시오.");
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
<section class="AGS_pay">
    <div>
        <form name="frmAGS_pay" method=post action="<?php echo home_url()?>/enrollment?mode=AGS_pay_ing">
            <input type="hidden" name="session_id" value="<?php echo $payment['session_id']?>">
            <input type="hidden" name="StoreId" maxlength=20 value="<?php echo $payment['allthegate_id']?>">
            <input type="hidden" name="OrdNo" maxlength=40 value="<?php echo $payment['ID']?>">
            <input type="hidden" name="Job" maxlength=12 value="<?php echo $payment['method']?>">
            <input type="hidden" name="Amt" maxlength=12 value="<?php echo $payment['amt']?>">
            <input type="hidden" name="StoreNm" value="<?php echo $payment['company_name']?>">
            <input type="hidden" name="ProdNm" maxlength=300 value="<?php echo $payment['allthegate_item_name']?>">
            <input type="hidden" name="MallUrl" value="<?php echo $payment['MallUrl']?>">
            <input type="hidden" name="UserEmail" maxlength=50 value="<?php echo $payment['UserEmail']?>">

            <!-- 결제창 좌측상단에 상점의 로고이미지(85 * 38)를 표시할 수 있습니다.  잘못된 값을 입력하거나 미입력시 이지스올더게이트의 로고가 표시됩니다. -->
            <input type="hidden" name=ags_logoimg_url maxlength=200 value="<?php echo $payment['ags_logoimg_url']?>">

            <!-- 제목은 1컨텐츠당 5자 이내이며, 상점명;상품명;결제금액;제공기간; 순으로 입력해 주셔야 합니다. 입력 예)업체명;판매상품;계산금액;제공기간; -->
            <input type="hidden" name="SubjectData" value="<?php echo $payment['SubjectData']?>">
            <input type="hidden" name="UserId" maxlength=20 value="<?php echo $payment['UserId']?>">

            <!-- 카드 & 가상 계좌 결재 용 변수 -->
            <input type="hidden" name="OrdNm" maxlength=40 value="<?php echo $payment['UserName']?>"><!--주문자 이름-->
            <input type="hidden" name="OrdPhone" maxlength=21 value="<?php echo $payment['UserPhone']?>"><!--주문자 연락처-->
            <input type="hidden" name="OrdAddr" maxlength=100 value="<?php echo $payment['UserAddress']?>"><!--주문자 주소-->
            <input type="hidden" name="RcpNm" maxlength=40 value="<?php echo $payment['RecvName']?>"><!-- 수진자명-->
            <input type="hidden" name="RcpPhone" maxlength=21 value="<?php echo $payment['RecvPhone']?>"><!-- 수신자 연락처 -->
            <input type="hidden" name="DlvAddr" maxlength=100 value="<?php echo $payment['RecvAddress']?>"><!--배송지 주소-->
            <input type="hidden" name="Remark" maxlength=350 value="<?php echo $payment['Remark']?>"><!--기타 요구사항 -->
            <input type=hidden style=width:300px name=CardSelect value=""><!--카드사 선택 : 특정 카드만 사용하고자 하는 경우. 빈 값을 입력하면 모든 카드사 사용. 카드사 코드는 매뉴얼에서 확인-->
            <!-- EO 카드 & 가상 계좌 결재 용 변수 -->
            <!-- 가상계좌 결제 용 변수 -->
            <input type="hidden" name="MallPage" value="<?php echo $payment['MallPage']?>"><!-- 결제 입/출금 통보 URL. 도메인을 제외한 나머지 입력 -->
            <input type="hidden" name="VIRTUAL_DEPODT" value=""><!-- 입금 제한 일. 몇일까지 입금하라는 기간을 정해 줌. 최대 15일. 생략하면 기본 5일. 예) 20001122-->


            <!-- 핸드폰 결제 용 변수. 핸드폰 결제를 하지 않더라도 값을 넣어 준다. 안그러면 PHP 에서 변수 Undefiend 경고 에러가 난다. -->
            <input type=hidden name=HP_ID maxlength=10 value="<?php echo $payment['allthegate_cp_id']?>">
            <input type=hidden name=HP_PWD maxlength=10 value="<?php echo $payment['allthegate_cp_pwd']?>">
            <input type=hidden name=HP_SUBID maxlength=10 value="<?php echo $payment['allthegate_sub_cp_id']?>">
            <input type=hidden  name=ProdCode maxlength=10 value="<?php echo $payment['allthegate_cp_code']?>">
            <input type="hidden" name=HP_UNITType value="1">


            <!-- 스크립트 및 플러그인에서 값을 설정하는 Hidden 필드  !!수정을 하시거나 삭제하지 마십시오-->

            <!-- 각 결제 공통 사용 변수 -->
            <input type=hidden name=Flag value="">				<!-- 스크립트결제사용구분플래그 -->
            <input type=hidden name=AuthTy value="">			<!-- 결제형태 -->
            <input type=hidden name=SubTy value="">				<!-- 서브결제형태 -->
            <input type=hidden name=AGS_HASHDATA value="<?php echo $AGS_HASHDATA?>">	<!-- 암호화 HASHDATA -->

            <!-- 신용카드 결제 사용 변수 -->
            <input type=hidden name=DeviId value="">			<!-- (신용카드공통)		단말기아이디 -->
            <input type=hidden name=QuotaInf value="0">			<!-- (신용카드공통)		일반할부개월설정변수 -->
            <input type=hidden name=NointInf value="NONE">		<!-- (신용카드공통)		무이자할부개월설정변수 -->
            <input type=hidden name=AuthYn value="">			<!-- (신용카드공통)		인증여부 -->
            <input type=hidden name=Instmt value="">			<!-- (신용카드공통)		할부개월수 -->
            <input type=hidden name=partial_mm value="">		<!-- (ISP사용)			일반할부기간 -->
            <input type=hidden name=noIntMonth value="">		<!-- (ISP사용)			무이자할부기간 -->
            <input type=hidden name=KVP_RESERVED1 value="">		<!-- (ISP사용)			RESERVED1 -->
            <input type=hidden name=KVP_RESERVED2 value="">		<!-- (ISP사용)			RESERVED2 -->
            <input type=hidden name=KVP_RESERVED3 value="">		<!-- (ISP사용)			RESERVED3 -->
            <input type=hidden name=KVP_CURRENCY value="">		<!-- (ISP사용)			통화코드 -->
            <input type=hidden name=KVP_CARDCODE value="">		<!-- (ISP사용)			카드사코드 -->
            <input type=hidden name=KVP_SESSIONKEY value="">	<!-- (ISP사용)			암호화코드 -->
            <input type=hidden name=KVP_ENCDATA value="">		<!-- (ISP사용)			암호화코드 -->
            <input type=hidden name=KVP_CONAME value="">		<!-- (ISP사용)			카드명 -->
            <input type=hidden name=KVP_NOINT value="">			<!-- (ISP사용)			무이자/일반여부(무이자=1, 일반=0) -->
            <input type=hidden name=KVP_QUOTA value="">			<!-- (ISP사용)			할부개월 -->
            <input type=hidden name=CardNo value="">			<!-- (안심클릭,일반사용)	카드번호 -->
            <input type=hidden name=MPI_CAVV value="">			<!-- (안심클릭,일반사용)	암호화코드 -->
            <input type=hidden name=MPI_ECI value="">			<!-- (안심클릭,일반사용)	암호화코드 -->
            <input type=hidden name=MPI_MD64 value="">			<!-- (안심클릭,일반사용)	암호화코드 -->
            <input type=hidden name=ExpMon value="">			<!-- (일반사용)			유효기간(월) -->
            <input type=hidden name=ExpYear value="">			<!-- (일반사용)			유효기간(년) -->
            <input type=hidden name=Passwd value="">			<!-- (일반사용)			비밀번호 -->
            <input type=hidden name=SocId value="">				<!-- (일반사용)			주민등록번호/사업자등록번호 -->

            <!-- 계좌이체 결제 사용 변수 -->
            <input type=hidden name=ICHE_OUTBANKNAME value="">	<!-- 이체계좌은행명 -->
            <input type=hidden name=ICHE_OUTACCTNO value="">	<!-- 이체계좌예금주주민번호 -->
            <input type=hidden name=ICHE_OUTBANKMASTER value=""><!-- 이체계좌예금주 -->
            <input type=hidden name=ICHE_AMOUNT value="">		<!-- 이체금액 -->

            <!-- 핸드폰 결제 사용 변수 -->
            <input type=hidden name=HP_SERVERINFO value="">		<!-- 서버정보 -->
            <input type=hidden name=HP_HANDPHONE value="">		<!-- 핸드폰번호 -->
            <input type=hidden name=HP_COMPANY value="">		<!-- 통신사명(SKT,KTF,LGT) -->
            <input type=hidden name=HP_IDEN value="">			<!-- 인증시사용 -->
            <input type=hidden name=HP_IPADDR value="">			<!-- 아이피정보 -->

            <!-- ARS 결제 사용 변수 -->
            <input type=hidden name=ARS_PHONE value="">			<!-- ARS번호 -->
            <input type=hidden name=ARS_NAME value="">			<!-- 전화가입자명 -->

            <!-- 가상계좌 결제 사용 변수 -->
            <input type=hidden name=ZuminCode value="">			<!-- 가상계좌입금자주민번호 -->
            <input type=hidden name=VIRTUAL_CENTERCD value="">	<!-- 가상계좌은행코드 -->
            <input type=hidden name=VIRTUAL_NO value="">		<!-- 가상계좌번호 -->

            <input type=hidden name=mTId value="">

            <!-- 에스크로 결제 사용 변수 -->
            <input type=hidden name=ES_SENDNO value="">			<!-- 에스크로전문번호 -->

            <!-- 계좌이체(소켓) 결제 사용 변수 -->
            <input type=hidden name=ICHE_SOCKETYN value="">		<!-- 계좌이체(소켓) 사용 여부 -->
            <input type=hidden name=ICHE_POSMTID value="">		<!-- 계좌이체(소켓) 이용기관주문번호 -->
            <input type=hidden name=ICHE_FNBCMTID value="">		<!-- 계좌이체(소켓) FNBC거래번호 -->
            <input type=hidden name=ICHE_APTRTS value="">		<!-- 계좌이체(소켓) 이체 시각 -->
            <input type=hidden name=ICHE_REMARK1 value="">		<!-- 계좌이체(소켓) 기타사항1 -->
            <input type=hidden name=ICHE_REMARK2 value="">		<!-- 계좌이체(소켓) 기타사항2 -->
            <input type=hidden name=ICHE_ECWYN value="">		<!-- 계좌이체(소켓) 에스크로여부 -->
            <input type=hidden name=ICHE_ECWID value="">		<!-- 계좌이체(소켓) 에스크로ID -->
            <input type=hidden name=ICHE_ECWAMT1 value="">		<!-- 계좌이체(소켓) 에스크로결제금액1 -->
            <input type=hidden name=ICHE_ECWAMT2 value="">		<!-- 계좌이체(소켓) 에스크로결제금액2 -->
            <input type=hidden name=ICHE_CASHYN value="">		<!-- 계좌이체(소켓) 현금영수증발행여부 -->
            <input type=hidden name=ICHE_CASHGUBUN_CD value="">	<!-- 계좌이체(소켓) 현금영수증구분 -->
            <input type=hidden name=ICHE_CASHID_NO value="">	<!-- 계좌이체(소켓) 현금영수증신분확인번호 -->

            <!-- 텔래뱅킹-계좌이체(소켓) 결제 사용 변수 -->
            <input type=hidden name=ICHEARS_SOCKETYN value="">	<!-- 텔레뱅킹계좌이체(소켓) 사용 여부 -->
            <input type=hidden name=ICHEARS_ADMNO value="">		<!-- 텔레뱅킹계좌이체 승인번호 -->
            <input type=hidden name=ICHEARS_POSMTID value="">	<!-- 텔레뱅킹계좌이체 이용기관주문번호 -->
            <input type=hidden name=ICHEARS_CENTERCD value="">	<!-- 텔레뱅킹계좌이체 은행코드 -->
            <input type=hidden name=ICHEARS_HPNO value="">		<!-- 텔레뱅킹계좌이체 휴대폰번호 -->

            <!-- 스크립트 및 플러그인에서 값을 설정하는 Hidden 필드  !!수정을 하시거나 삭제하지 마십시오-->

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
        요청한 결제 처리중입니다.<br>
        잠시만 기다려 주세요.
        </td>
        </tr>
        </table>
        </div>

        <a href="javascript:history.go(-1)">돌아가기</a>
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
    // PAYMENT_DEBUG_NO_ACTIVEX 이 참이면 ActiveX 결제 확인 생략.
    frmAGS_pay.submit();
    <?php } else { ?>
    /** 페이지 하단이 바로 출력되지 않기 때문에, setTimeout() 을 걸어 준다. */
    setTimeout(function(){
        Enable_Flag(frmAGS_pay);
        Pay(frmAGS_pay);
    }, 100);
    <?php } ?>
</script>