<?php
/**
 * @file AGS_pay_result.php
 * @desc
 *
 * 		2016-06-11 By Mr. Song
 *
 * 		이 파일은 결제 처리 결과를 보여 준다.
 *
 * 		결제 성공, 실패, 입금대기(가상계좌) 중 하나를 보여 준다.
 *
 * 		이 페이지에서는 본문 내용만 간략하게 보여주므로,
 *
 * 		커스터마이징을 하는 경우, 상단/하단(전/후)의 디자인 추가가 필요하다.
 *
 * @참고 : 테스트를 하는 경우, config.php 를 참고한다.
 *
 *
 */
/**********************************************************************************************
*
* 파일명 : AGS_pay_result.php
* 작성일자 : 2012/04/30
*
* 소켓결제결과를 처리합니다.
*
* Copyright AEGIS ENTERPRISE.Co.,Ltd. All rights reserved.
*
**********************************************************************************************/



if ( ! isset( $_REQUEST['session_id'] ) ) exit;

global $payment;
payment_resume_transaction( $_REQUEST['session_id'] );
function payment_store_pay_result() {
	global $wpdb, $payment;
	$table = $wpdb->prefix . 'payment';
	$q = $wpdb->prepare(
			"UPDATE $table SET values_from_paygate_server=%s WHERE session_id=%s",
			serialize($_REQUEST),
			$payment['session_id']
	);
	$re = $wpdb->query( $q );
	if ( $re === false ) {
		dog("Database error on saving values from paygate server");
		return -4005;
	}
	return 0;
}
payment_store_pay_result();


// 테스트 용 : 변수 에러 처리.
// 상점아이디와 주문번호가 없다면, 테스트하는 것으로 간주하고, 임시 데이터를 출력한다.
// 주의 : 이전 AGS_pay_ing.php 에서 테스트 또는 디버깅 용 등으로 결제 서버와 연결이 안되거나 결제 정보가 올바르지 않으면,
//          $_POST['rStoreId'] 의 값은 "Undefined index rStoreId ... " 와 같이 넘어 온다.
// 참고 : https://docs.google.com/document/d/1u1FyzfWWuVf0D_U-QYPdwN95rWFv4xFdhB7Unvs4PA8/edit#heading=h.xoj6kfhfmvst
//
if (  empty( $_POST['rStoreId']) || strlen($_POST['rStoreId']) > 64 || strlen($_POST['rOrdNo']) > 32 ) {

    $_POST['AuthTy'] = "card";			// card, iche, virtual
    $_POST['SubTy'] = 'isp';		// isp, visa3d, normal


    $_POST['rStoreId'] = "TestStoreID";
    $_POST['rOrdNo'] = -1234;
    $_POST['rOrdNm'] = 'Order Name';
    $_POST['rAmt'] = 54321;
    $_POST['rProdNm'] = 'Product Name';
    $_POST['rSuccYn'] = 'Y';
    $_POST['rResMsg'] = "실패 사유 : 테스트";
    $_POST['rApprTm'] = "승인 시각 : 테스트";
    $_POST['rBusiCd'] = "전문 코드 : 테스트";
    $_POST['rApprNo'] = '승인 번호 : 테스트';
    $_POST['rCardCd'] = '카드사 코드 : 테스트';
    $_POST['rDealNo'] = '거래 고유 번호 : 테스트';
    $_POST['rCardNm'] = '카드사 명 : 테스트';
    $_POST['rMembNo'] = '가맹점 번호 : 테스트';
    $_POST['rAquiCd'] = '매입사 코드 : 테스트';
    $_POST['rAquiNm'] = '매입사 명 : 테스트';

    $_POST['ICHE_OUTBANKNAME'] = '이체 계좌 은행명 : 테스트';
    $_POST['ICHE_OUTACCTNO'] = '이체 계좌 번호 : 테스트';
    $_POST['ICHE_OUTBANKMASTER'] = '이체 계좌 소유주 : 테스트';
    $_POST['ICHE_AMOUNT'] = '이체 금액 : 테스트';
    $_POST['rHP_TID'] = '핸드폰 결제 TID : 테스트';
    $_POST['rHP_DATE'] = '핸드폰 결제 날짜 : 테스트';
    $_POST['rHP_HANDPHONE'] = '핸드폰 결제 번호 : 테스트';
    $_POST['rHP_COMPANY'] = '핸드폰 결제 통신사명(SKT, KTF, LGT) : 테스트';


    $_POST['rARS_PHONE'] = 'ARS 결제 전화번호 : 테스트';
    $_POST['rVirNo'] = '가상계좌번호 가상계좌추가 : 테스트';
    $_POST['VIRTUAL_CENTERCD'] = '가상계좌 입금은행코드 : 테스트';
    $_POST['ES_SENDNO'] = '이지스에스크로(전문번호) : 테스트';


}


//공통사용
$AuthTy 		= trim( $_POST["AuthTy"] );				//결제형태
$SubTy 			= trim( $_POST["SubTy"] );				//서브결제형태
$rStoreId 		= trim( $_POST["rStoreId"] );			//업체ID
$rAmt 			= trim( $_POST["rAmt"] );				//거래금액
$rOrdNo 		= trim( $_POST["rOrdNo"] );				//주문번호
$rProdNm 		= trim( $_POST["rProdNm"] );			//상품명
$rOrdNm			= trim( $_POST["rOrdNm"] );				//주문자명

//소켓통신결제(신용카드,핸드폰,일반가상계좌)시 사용
$rSuccYn 		= trim( $_POST["rSuccYn"] );			//성공여부
$rResMsg 		= trim( $_POST["rResMsg"] );			//실패사유
$rApprTm 		= trim( $_POST["rApprTm"] );			//승인시각

//신용카드공통
$rBusiCd 		= trim( $_POST["rBusiCd"] );			//전문코드
$rApprNo 		= trim( $_POST["rApprNo"] );			//승인번호
$rCardCd 		= trim( $_POST["rCardCd"] );			//카드사코드
$rDealNo 		= trim( $_POST["rDealNo"] );			//거래고유번호

//신용카드(안심,일반)
$rCardNm 		= trim( $_POST["rCardNm"] );			//카드사명
$rMembNo 		= trim( $_POST["rMembNo"] );			//가맹점번호
$rAquiCd 		= trim( $_POST["rAquiCd"] );			//매입사코드
$rAquiNm 		= trim( $_POST["rAquiNm"] );			//매입사명


//계좌이체
$ICHE_OUTBANKNAME	= trim( $_POST["ICHE_OUTBANKNAME"] );		//이체계좌은행명
$ICHE_OUTACCTNO 	= trim( $_POST["ICHE_OUTACCTNO"] );			//이체계좌번호
$ICHE_OUTBANKMASTER = trim( $_POST["ICHE_OUTBANKMASTER"] );		//이체계좌소유주
$ICHE_AMOUNT 		= trim( $_POST["ICHE_AMOUNT"] );			//이체금액

//핸드폰
$rHP_TID 		= trim( $_POST["rHP_TID"] );			//핸드폰결제TID
$rHP_DATE 		= trim( $_POST["rHP_DATE"] );			//핸드폰결제날짜
$rHP_HANDPHONE 	= trim( $_POST["rHP_HANDPHONE"] );		//핸드폰결제핸드폰번호
$rHP_COMPANY 	= trim( $_POST["rHP_COMPANY"] );		//핸드폰결제통신사명(SKT,KTF,LGT)


//ARS
$rARS_PHONE = trim( $_POST["rARS_PHONE"] );				//ARS결제전화번호

//가상계좌
$rVirNo 		= trim( $_POST["rVirNo"] );				//가상계좌번호 가상계좌추가
$VIRTUAL_CENTERCD = trim( $_POST["VIRTUAL_CENTERCD"] );	//가상계좌 입금은행코드

//이지스에스크로
$ES_SENDNO	= trim( $_POST["ES_SENDNO"] );				//이지스에스크로(전문번호)

//*******************************************************************************
//* MD5 결제 데이터 정상여부 확인
//* 결제전 AGS_HASHDATA 값과 결제 후 rAGS_HASHDATA의 일치 여부 확인
//* 형태 : 상점아이디(StoreId) + 주문번호(OrdNo) + 결제금액(Amt)
//*******************************************************************************

$AGS_HASHDATA	= trim( $_POST["AGS_HASHDATA"] );				
$rAGS_HASHDATA	= md5($rStoreId . $rOrdNo . (int)$rAmt);				

if($AGS_HASHDATA == $rAGS_HASHDATA){
	$errResMsg   = "";
}else{
	$errResMsg   = "결재금액 변조 발생. 확인 바람";
}



payment_log( [
    'action' => 'AGS_pay_result.php-displaying-result',
    'message' => "AGS_pay_ing.php >> Displaying payment result."
] );


/*
$rSuccYn = 'y'; // TEST
$AuthTy = 'iche'; // TEST
$VIRTUAL_CENTERCD = '04'; // TEST
*/

?>

<script language=javascript>
<!--
/***********************************************************************************
* ◈ 영수증 출력을 위한 자바스크립트
*		
*	영수증 출력은 [카드결제]시에만 사용하실 수 있습니다.
*  
*   ※당일 결제건에 한해서 영수증 출력이 가능합니다.
*     당일 이후에는 아래의 주소를 팝업(630X510)으로 띄워 내역 조회 후 출력하시기 바랍니다.
*	  ▷ 팝업용 결제내역조회 패이지 주소 : 
*	     	 http://www.allthegate.com/support/card_search.html
*		→ (반드시 스크롤바를 'yes' 상태로 하여 팝업을 띄우시기 바랍니다.) ←
*
***********************************************************************************/
function show_receipt() 
{
	if("<?=$rSuccYn?>"== "y" && "<?=$AuthTy?>"=="card")
	{
		var send_dt = appr_tm.value;
		
		url="http://www.allthegate.com/customer/receiptLast3.jsp"
		url=url+"?sRetailer_id="+sRetailer_id.value;
		url=url+"&approve="+approve.value;
		url=url+"&send_no="+send_no.value;
		url=url+"&send_dt="+send_dt.substring(0,8);
		
		window.open(url, "window","toolbar=no,location=no,directories=no,status=,menubar=no,scrollbars=no,resizable=no,width=420,height=700,top=0,left=150");
	}
	else
	{
		alert("해당하는 결제내역이 없습니다");
	}
}
-->
</script>

<style>

	.payment {
		padding: 1em 0;
	}
	.payment .header {
		margin-bottom: .6em;
		padding: 2em;
		background-color: #563d7c;
		color:white;
	}

	.payment .header .title {
		margin-bottom: .4em;
		font-size: 1.5em;
	}

	.payment .line {
		overflow: auto;
		overflow-y: hidden;
	}

	.payment .line > div {
		margin: .2em 0;
		padding: .6em .8em;
		float: left;
	}

	.payment .line .caption {
		width: 20%;
		background-color: #6e818a;
		color: white;
	}

	.payment .line .text {
		background-color: #dfdfdf;
		min-width: 240px;
	}


	.payment .line .text .print-receipt {
		margin: 0;
		padding: 1em;
		background-color: #9a9ea9;
		box-sizing: border-box;
		color: white;
		cursor: pointer;
	}

</style>
<section class="AGS_pay payment">

	<div>
		<div class="header">
			<div class="title">
				<?php
				if ( $AuthTy == 'card' ) {
					echo "수업료 카드결재";
					if ($rSuccYn == 'y') echo ' 성공';
					else echo ' 실패';
				}
				else if ( $AuthTy == 'iche' ) {
					echo "수업료 계좌이체";
					if ($rSuccYn == 'y') echo ' 성공';
					else echo ' 실패';
				}
				else if ( $AuthTy == 'virtual' ) {
					echo "수업료 입금 대기";
				}
				?>
			</div>
			<div class="desc">
				<?php if ( $AuthTy == 'card' || $AuthTy == 'iche' ) { ?>
					회원님께서 요청하신 수업료 지불에 대한 결과입니다.<br />
					아래의 통장으로 입금하시면 수업료 결제가 됩니다.<br />
				<?php } else if ( $AuthTy == 'virtual' ) { ?>
						수업료 결제가 아직 되지 않았습니다.<br>
					아래의 통장으로 입금하시면 수업료 결제가 됩니다.<br />
				<?php } ?>

			</div>
		</div>

		<?php /*
		<div class="line">
			<div class="caption">결제형태</div>
			<div class="text">
 <?php

							if($AuthTy == "card")
							{
								if($SubTy == "isp")
								{
									echo "신용카드결제-안전결제(ISP)";
								}	
								else if($SubTy == "visa3d")
								{
									echo "신용카드결제-안심클릭";
								}
								else if($SubTy == "normal")
								{
									echo "신용카드결제-일반결제";
								}
								
							}
							else if($AuthTy == "iche")
							{
								echo "계좌이체";
							}
							else if($AuthTy == "hp")
							{
								echo "핸드폰결제";
							}
							else if($AuthTy == "ars")
							{
								echo "ARS결제";
							}
							else if($AuthTy == "virtual")
							{
								echo "가상계좌결제";
							}


							?>

				</div>
			</div>
*/?>

		<?php /*

		<div class="line">
			<div class="caption">상점아이디</div>
			<div class="text"><?php echo $rStoreId?></div>
		</div>
 */?>

		<?php /*
		<div class="line">
			<div class="caption">주문번호</div>
			<div class="text"><?php echo $rOrdNo?></div>
		</div>
 */?>



		<?php /*
		<div class="line">
			<div class="caption">주문자명</div>
			<div class="text"> <?php echo $rOrdNm?></div>
		</div>
 */ ?>


		<div class="line">
			<div class="caption">이름</div>
			<div class="text"> <?php echo payment_get_user_name()?></div>
		</div>




		<div class="line">
			<div class="caption">결제 상품</div>
			<div class="text"><?php echo iconv('EUC-KR', 'UTF-8', $rProdNm)?></div>
		</div>



		<div class="line">
			<div class="caption">수업료</div>
			<div class="text"><?php echo number_format($rAmt)?></div>
		</div>




		<?php if( $rSuccYn != "y") { ?>
		<div class="line">
			<div class="caption">성공여부</div>
			<div class="text"><?php echo $rSuccYn?></div>
		</div>
		<div class="line">
			<div class="caption">처리메세지</div>
			<div class="text"><?php echo iconv('EUC-KR', 'UTF-8', $rResMsg)?></div>
		</div>
		<?php } ?>



<?php if($AuthTy == "card" || $AuthTy == "virtual") {
	/*
	?>
		<div class="line">
			<div class="caption">승인시각</div>
			<div class="text"><?php echo $rApprTm?></div>
			</div>
<?php */
	 } ?>
<?php if($AuthTy == "card" && $rSuccYn == "y") {
	/*
	?>

	<div class="line"><div class="caption">전문코드</div><div class="text"><?php echo $rBusiCd?></div></div>
	<div class="line"><div class="caption">승인번호</div><div class="text"><?php echo $rApprNo?></div></div>
	<div class="line"><div class="caption">카드사코드</div><div class="text"><?php echo $rCardCd?></div></div>
	<div class="line"><div class="caption">거래번호</div><div class="text"><?php echo $rDealNo?></div></div>
	<?php
	*/
}
?>
<?php if($AuthTy == "card" && ($SubTy == "visa3d" || $SubTy == "normal") && $rSuccYn == "y") { ?>
	<div class="line"><div class="caption">카드사명</div><div class="text"><?php echo $rCardNm?></div></div>
	<div class="line"><div class="caption">매입사코드</div><div class="text"><?php echo $rAquiCd?></div></div>
	<div class="line"><div class="caption">매입사명</div><div class="text"><?php echo $rAquiNm?></div></div>
	<div class="line"><div class="caption">가맹점번호</div><div class="text"><?php echo $rMembNo?></div></div>
<?php } ?>
<?php

if($AuthTy == "iche" ) { ?>
	<div class="line"><div class="caption">이체계좌은행명</div><div class="text"><?php echo $ICHE_OUTBANKNAME?><?php echo getCenter_cd($ICHE_OUTBANKNAME)?></div></div>
	<div class="line"><div class="caption">이체금액</div><div class="text"><?php echo $ICHE_AMOUNT?></div></div>
	<div class="line"><div class="caption">이체계좌소유주</div><div class="text"><?php echo iconv('EUC-KR', 'UTF-8', $ICHE_OUTBANKMASTER)?></div></div>
	<?php /*
	<div class="line"><div class="caption">이지스에스크로(SEND_NO)</div><div class="text"><?php echo $ES_SENDNO?></div></div>
 */?>
<?php } ?>
<?php if($AuthTy == "hp" ) { ?>
	<div class="line"><div class="caption">핸드폰결제TID</div><div class="text"><?php echo $rHP_TID?></div></div>
	<div class="line"><div class="caption">핸드폰결제날짜</div><div class="text"><?php echo $rHP_DATE?></div></div>
	<div class="line"><div class="caption">핸드폰결제핸드폰번호</div><div class="text"><?php echo $rHP_HANDPHONE?></div></div>
	<div class="line"><div class="caption">핸드폰결제통신사명</div><div class="text"><?php echo $rHP_COMPANY?></div></div>
<?php } ?>
<?php if($AuthTy == "ars" ) { ?>
		<div class="line"><div class="caption">ARS결제TID</div><div class="text"><?php echo $rHP_TID?></div></div>
		<div class="line"><div class="caption">ARS결제날짜</div><div class="text"><?php echo $rHP_DATE?></div></div>
		<div class="line"><div class="caption">ARS결제전화번호</div><div class="text"><?php echo $rARS_PHONE?></div></div>
	<div class="line"><div class="caption">ARS결제통신사명</div><div class="text"><?php echo $rHP_COMPANY?></div></div>
<?php } ?>

<?php if( $rSuccYn == 'y' && $AuthTy == "virtual" ) { ?>
	<div class="line"><div class="caption">입금계좌번호</div><div class="text"><?php echo $rVirNo?></div></div>
	<!-- 은행코드(20) : 우리은행 -->
	<div class="line"><div class="caption">입금은행</div><div class="text"><?php echo getCenter_cd($VIRTUAL_CENTERCD)?></div></div>
	<!--올더게이트에 등록된 상점명으로 표기-------->
	<div class="line"><div class="caption">예금주명</div><div class="text">온라인영어</div></div>
	<?php /*
	<div class="line"><div class="caption">이지스에스크로(SEND_NO)</div><div class="text"><?=$ES_SENDNO?></div></div>
 */?>
		<div class="virtual-desc">
			위 은행으로 입금을 하시면 자동으로 수업료가 결제됩니다.
		</div>
<?php } ?>

<?php if( $rSuccYn == 'y' && $AuthTy == "card" ) { ?>
		<div class="line">
			<div class="caption">영수증</div>
			<div class="text">
				<!--영수증출력을위해서보내주는값-------------------->
						<input type=hidden name=sRetailer_id value="<?=$rStoreId?>"><!--상점아이디-->
						<input type=hidden name=approve value="<?=$rApprNo?>"><!---승인번호-->
						<input type=hidden name=send_no value="<?=$rDealNo?>"><!--거래고유번호-->
						<input type=hidden name=appr_tm value="<?=$rApprTm?>"><!--승인시각-->
						<!--영수증출력을위해서보내주는값-------------------->


				<span class="print-receipt" onclick="javascript:show_receipt();">영수증 출력하기</span>
				이용명세서에 구입처가 <font color=red>이지스효성(주)</font>로 표기됩니다.
			</div>
		</div>

<?php } ?>
<?php if( $rSuccYn != "y" && $errResMsg ) { ?>
		<div class="line"><div class="caption">에러메세지</div><div class="text"><?php echo $errResMsg?></div></div>
	<!--
		<div class="line"><div class="caption">원본 해쉬</div><div class="text"><?php echo $AGS_HASHDATA?></div></div>
		<div class="line"><div class="caption">결과 해쉬</div><div class="text"><?php echo $rAGS_HASHDATA?></div></div>
		-->
<?php } ?>

</div>
	</section>
<?

	function getCenter_cd($VIRTUAL_CENTERCD){
		if($VIRTUAL_CENTERCD == "39"){
			echo "경남은행";
		}else if($VIRTUAL_CENTERCD == "34"){
			echo "광주은행";
		}else if($VIRTUAL_CENTERCD == "04"){
			echo "국민은행";
		}else if($VIRTUAL_CENTERCD == "11"){
			echo "농협중앙회";
		}else if($VIRTUAL_CENTERCD == "31"){
			echo "대구은행";
		}else if($VIRTUAL_CENTERCD == "32"){
			echo "부산은행";
		}else if($VIRTUAL_CENTERCD == "02"){
			echo "산업은행";
		}else if($VIRTUAL_CENTERCD == "45"){
			echo "새마을금고";
		}else if($VIRTUAL_CENTERCD == "07"){
			echo "수협중앙회";
		}else if($VIRTUAL_CENTERCD == "48"){
			echo "신용협동조합";
		}else if($VIRTUAL_CENTERCD == "26"){
			echo "(구)신한은행";
		}else if($VIRTUAL_CENTERCD == "05"){
			echo "외환은행";
		}else if($VIRTUAL_CENTERCD == "20"){
			echo "우리은행";
		}else if($VIRTUAL_CENTERCD == "71"){
			echo "우체국";
		}else if($VIRTUAL_CENTERCD == "37"){
			echo "전북은행";
		}else if($VIRTUAL_CENTERCD == "23"){
			echo "제일은행";
		}else if($VIRTUAL_CENTERCD == "35"){
			echo "제주은행";
		}else if($VIRTUAL_CENTERCD == "21"){
			echo "(구)조흥은행";
		}else if($VIRTUAL_CENTERCD == "03"){
			echo "중소기업은행";
		}else if($VIRTUAL_CENTERCD == "81"){
			echo "하나은행";
		}else if($VIRTUAL_CENTERCD == "88"){
			echo "신한은행";
		}else if($VIRTUAL_CENTERCD == "27"){
			echo "한미은행";
		}
				}
?>
