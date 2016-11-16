<?php
header('Content-type: text/html; charset=utf-8');
define('OPT_CODE', 'LLSL');
if(!($_GET['billId'] && $_GET['offerId'])){
	echo json_encode(array('data' => '','info' => array('billId或者offerId不存在')));
}
include "ApiWrapper.php";
$wrapper = new ApiWrapper();
$data = array(
	'optCode' => OPT_CODE,
	'bizCode' => BIZ_CODE,
	'tradeSerialNo' => OPT_CODE. date("YmdHis") . BIZ_CODE . rand(10000000,99999999),
	'tradeDate' => date("YmdHis"),
	'accountDate' => date("Ymd",strtotime("+1 day")),
	'offerId' => $_GET['offerId'],
	'billId' => $_GET['billId'],
	);
$result = $wrapper->doCURL('POST', '/mbb/presentDataTraffic/v1', $data);
$result = json_decode($result,true);
$result['result']['tradeSerialNo'] = $data['tradeSerialNo'];
echo json_encode($result);