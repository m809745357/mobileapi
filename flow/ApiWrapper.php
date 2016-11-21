<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2014-2016 http://www.10tech.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: shenyifei <809745357@qq.com>
// +----------------------------------------------------------------------

// +----------------------------------------------------------------------
// | 定义接口常量
// +----------------------------------------------------------------------
// | false 正式环境 true 测试环境
// | URL_API 正式或者测试的地址，如http://127.0.0.1:1100
// | URL_HOST 正式或者测试的域名，如localhost，用来内网转发域名使用。必须要写
// +----------------------------------------------------------------------
define('DEBUG', false); 
if (DEBUG) {
    define('URL_API', '');
    define('URL_HOST', '');
} else {
    define('URL_API', '');
    define('URL_HOST', '');
}

// +----------------------------------------------------------------------
// | APPKEY 接口调用key
// | APPSECRET 接口调用密钥
// | BIZ_CODE 接口外围渠道，如：杭州移动 HZYD
// +----------------------------------------------------------------------
define('APPKEY', '');
define('APPSECRET', '');
define('BIZ_CODE', '');

// +----------------------------------------------------------------------
// | 以下参数哦不做要求
// +----------------------------------------------------------------------
define('HTTP_BASIC_ENV', false);
define('HTTP_BASIC_USER', '');
define('HTTP_BASIC_PASSWORD', '');

// +----------------------------------------------------------------------
// | 调用方式如下：
// | $wrapper = new ApiWrapper();
// | $wrapper->doCURL('POST', '/mbb/queryPresentResult/v1', $data);
// | $wrapper->doCURL('PUT', '/mbb/queryPresentResult/v1', $data);
// | $wrapper->doCURL('DELETE', '/mbb/queryPresentResult/v1');
// | $wrapper->doCURL('GET', '/mbb/queryPresentResult/v1');
// +----------------------------------------------------------------------
/**
 * @author Tim Vermaercke <tim.vermaercke@wijs.be>
 * @author Jonas Goderis <jonas.goderis@wijs.be>
 * @author shenyifei <809745357@qq.com>
 * @version 0.0.1
 */
Class ApiWrapper {

    /**
     * This method will make a cURL call
     */
    public function doCURL($method, $url, $data = array()) {
        $result = null;
        // init the php curl
        $curl = curl_init();
        // init the php curl
        $url = URL_API . $url;
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        // 设置 Host 必须要大写，如： Host: localhost
        $headers[] = 'Host: ' . URL_HOST;
        // 设置 WSSE 移动公司提供常量，如： Authorization: WSSE realm="SDP",profile="UsernameToken",type="Appkey"
        $headers[] = 'Authorization: WSSE realm="SDP",profile="UsernameToken",type="Appkey"';
        // 设置 X-WSSE 
        $headers[] = $this->getWsseHeader(APPKEY, APPSECRET);
        error_log(json_encode($headers) . "\n", 3, "my-headers.log");
        // set headers when there is data
        if ($data) {
            $data = json_encode($data);
            $dataLength = strlen($data);
            $headers[] = 'Content-Length: ' . $dataLength;
        }
        error_log($data . "\n", 3, "my-data.log");
        // depending on the given method, fire the right action
        switch ($method) {
            case 'POST':
                // let curl know we want to post
                curl_setopt($curl, CURLOPT_POST, true);
                // set post fields
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        // set the curl url to call
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // set http basic auth if needed
        if (HTTP_BASIC_ENV == true) {
            curl_setopt($curl, CURLOPT_USERPWD, HTTP_BASIC_USER . ':' . HTTP_BASIC_PASSWORD);
        }
        // set headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        // make the call
        $result = curl_exec($curl);
        // close the curl session
        $aStatus = curl_getinfo($curl);
        error_log($result . "\n", 3, "my-result.log");
        curl_close($curl);
        // return the result of the call
        if ($aStatus['http_code'] == 200) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Will create a x-wsse header, based on your username and password
     */
    protected function getWsseHeader($username, $secret) {
        // 调用java方法实现加密后返回X-WSSE
        require_once("http://localhost:8080/JavaBridge/Java/Java.inc");
        $tongfu = new Java("tongfu");
        $xwsse = $tongfu->phptobase64($username, $secret);
        return "X-WSSE: $xwsse";

        // 调用php方法实现加密，该方法在移动公司加密中无效
        // create random nonce 
        $nonce = md5(rand(), false);
        $b64nonce = base64_encode($nonce);
        // create UNIX Timestamp
        $created = str_replace("+0000", "Z", gmdate(DATE_ISO8601));
        // create digest
        $digest = base64_encode(hash("sha256", utf8_encode(($nonce . $created . $secret))));
        // echo header data
        return sprintf('X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"', $username, $digest, $b64nonce, $created
        );
    }

}

?>