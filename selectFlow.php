<?php
header('Content-type: text/html; charset=utf-8');
define('OPT_CODE', 'LLQY');
if(!($_GET['billId'] && $_GET['orderOrgTradeNo'])){
	echo json_encode(array('data' => '','info' => array('billId或者orderOrgTradeNo不存在')));
}
include "ApiWrapper.php";
$wrapper = new ApiWrapper();
$data = array(
	'optCode' => OPT_CODE,
	'bizCode' => BIZ_CODE,
	'tradeSerialNo' => OPT_CODE. date("YmdHis") . BIZ_CODE . rand(10000000,99999999),
	'orderOrgTradeNo' => $_GET['orderOrgTradeNo'],
	'billId' => $_GET['billId'],
	);
$result = $wrapper->doCURL('POST', '/mbb/queryPresentResult/v1', $data);
echo $result;