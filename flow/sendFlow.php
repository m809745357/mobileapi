<?php

// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.10tech.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: shenyifei <809745357@qq.com>
// +----------------------------------------------------------------------
// | 设置编码utf-8，若设置json则会产生文件
// +----------------------------------------------------------------------
header('Content-type: text/html; charset=utf-8');

// +----------------------------------------------------------------------
// | 设置交易码，查询交易码为：LLSL
// +----------------------------------------------------------------------
define('OPT_CODE', 'LLSL');

// +----------------------------------------------------------------------
// | 接口过滤输入参数，即过滤用户传入数据
// | TODO 添加调用ip限制，或者使用加密方式调用，以下方式推荐在生产环境下搭建。
// +----------------------------------------------------------------------
if (!($_GET['billId'] && $_GET['offerId'])) {
    echo json_encode(array('data' => '', 'info' => array('billId或者offerId不存在')));
}

// +----------------------------------------------------------------------
// | 引入ApiWrapper类，并实例化类。
// +---------------------------------------------------------------------
include "ApiWrapper.php";
$wrapper = new ApiWrapper();

// +----------------------------------------------------------------------
// | optCode 交易码。
// | bizCode 外围渠道。
// | tradeSerialNo 交易流水，交易码+交易时间的年月日时分秒+bizCode+8位序列号。
// | tradeDate 交易日期。（与tradeSerialNo参数的时间保持一致）。
// | accountDate 清算日期，交易时间的次一日。
// | offerId 流量包编号，由移动统一分配。
// | billId 手机号码。
// +----------------------------------------------------------------------
$data = array(
    'optCode' => OPT_CODE,
    'bizCode' => BIZ_CODE,
    'tradeSerialNo' => OPT_CODE . date("YmdHis") . BIZ_CODE . rand(10000000, 99999999),
    'tradeDate' => date("YmdHis"),
    'accountDate' => date("Ymd", strtotime("+1 day")),
    'offerId' => $_GET['offerId'],
    'billId' => $_GET['billId'],
);

// +----------------------------------------------------------------------
// | 调用doCURL方法，并返回数据。
// | TODO 错误的时候貌似不返回数据，但是在log文件中可以查看到数据
// | 重组数组，添加tradeSerialNo，对应“流量包查询”接口中的orderOrgTradeNo
// +----------------------------------------------------------------------
$result = $wrapper->doCURL('POST', '/mbb/presentDataTraffic/v1', $data);
$result = json_decode($result, true);
$result['result']['tradeSerialNo'] = $data['tradeSerialNo'];
echo json_encode($result);
