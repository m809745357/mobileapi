<?php
// Set header to json 会产生json文件
//header('content-type: application/json');
define('DEBUG', false);// false 正式环境 true 测试环境
if(DEBUG){
	define('URL_API', '');
	define('URL_HOST', '');
}else{
	define('URL_API', '');
	define('URL_HOST', '');
}
define('APPKEY', '');
define('APPSECRET', '');
define('BIZ_CODE', '');

define('HTTP_BASIC_ENV', false);
define('HTTP_BASIC_USER', '');
define('HTTP_BASIC_PASSWORD', '');
/**
 * @author Tim Vermaercke <tim.vermaercke@wijs.be>
 * @author Jonas Goderis <jonas.goderis@wijs.be>
 * @author shenyifei <809745357@qq.com>
 */
Class ApiWrapper{
    /**
     * This method will make a cURL call
     */
    public function doCURL($method, $url, $data = array()){
    	$result = null;
        // init the php curl
    	$curl = curl_init();
        // init the php curl
    	$url = URL_API . $url;
    	$headers = array();
    	$headers[] = 'Content-Type: application/json';
		// set Host
    	$headers[] = 'Host: ' . URL_HOST;
		// set WSSE
    	$headers[] = 'Authorization: WSSE realm="SDP",profile="UsernameToken",type="Appkey"';
		// get X-WSSE
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
    	if (HTTP_BASIC_ENV == true){
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
		if($aStatus['http_code'] == 200){
			return $result;
		}else{
			return false;
		}
    }
	
    /**
     * Will create a x-wsse header, based on your username and password
     */
    protected function getWsseHeader($username, $secret){
		// java
		require_once("http://localhost:8080/JavaBridge/Java/Java.inc");
		$tongfu = new Java("tongfu");
		$xwsse = $tongfu->phptobase64($username, $secret);
		return "X-WSSE: $xwsse";
		
		// php
        // create random nonce 
		$nonce = md5(rand(), false);
		$b64nonce = base64_encode($nonce);
        // create UNIX Timestamp
		$created = str_replace("+0000","Z",gmdate(DATE_ISO8601));
        // create digest
		$digest = base64_encode(hash("sha256", utf8_encode(($nonce . $created . $secret))));
        // echo header data
		return sprintf('X-WSSE: UsernameToken Username="%s", PasswordDigest="%s", Nonce="%s", Created="%s"',
			$username,
			$digest,
			$b64nonce,
			$created
			);
	}
}
?>