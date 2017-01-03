<?php

/**
 * 浏览器友好的变量输出
 * @param mixed $var 变量
 * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
 * @param string $label 标签 默认为空
 * @param boolean $strict 是否严谨 默认为true
 * @return void|string
 */
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}


/**
 * 提交json
 * @param  $url
 * @param  $post
 * @return mixed
 */
function postJson($url, $post)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type:application/json;charset=utf-8',
        'Content-Length:'.strlen($post))
        );
    $handles = curl_exec($ch);
    curl_close($ch);
    return $handles;
}

/**
 * rsa-sha1 签名算法
 * @param string $data 待签名数据
 * @param string $privateKeyPath 私钥证书文件路径
 * @return string
 */
function RSA_SHA1_Sign($data,$privateKeyPath){
    $key = openssl_pkey_get_private(file_get_contents($privateKeyPath));
    openssl_sign($data, $sign, $key, OPENSSL_ALGO_SHA1);
    $sign = base64_encode($sign);
    return $sign;
}

/**
 * rsa-sha1 验签算法
 * @param string $data 待签名数据
 * @param string $sign 数据签名
 * @param string $publicKeyPath 公钥证书文件路径
 * @return boolean
 */
function RSA_SHA1_Verify($data, $sign,$publicKeyPath){
    $sign = base64_decode($sign);
    $key = openssl_pkey_get_public(file_get_contents($publicKeyPath));
    $result = openssl_verify($data, $sign, $key, OPENSSL_ALGO_SHA1) === 1;
    return $result;
}

/**
 * 得到订单号的方法
 * 应用场合：非集群环境
 * 规则：当前时间戳，年月日8位+6位流水号 ，例：20160309000001
 * @return 14位订单号
 */
function getOrderId(){
    $date = date('Ymd',time());
    $serial_number = getSerialNumberFromFile();
    $sn_string = str_pad($serial_number,6,"0",STR_PAD_LEFT);
    return $date . $sn_string;
}

/**
 * 从文件中获取流水号
 * @return string 不固定位数流水号
 */
function getSerialNumberFromFile(){
    $filename = 'order_serial_num.txt';
    $line_string = file_get_contents($filename);
    if(empty($line_string)){
        file_put_contents($filename, 1,LOCK_EX);
        return 1;
    }else{
        if($line_string > 999999){
            file_put_contents($filename, 1,LOCK_EX);
            return 1;
        }else{
            $data = (int)$line_string + 1;
            file_put_contents($filename, $data,LOCK_EX);
            return $data;
        }
    }
}

include 'Crypt3Des.php';

$url = 'http://test.niiwoo.com:5102/boss/uap';

$des3pwd = 'AD905@!QLF-D25WEDA5!@#$%';

$header_array = array(
        'orgCode' => '1003',
        'transNo' => getOrderId(),
        'transDate' => date('YmdHis'),
        'userName' => 'tuandai',
        'userPassword' => strtolower(md5('tuandai_test123')),
        'functionCode' => 'QDP_10009101',
    );
$header_json = json_encode($header_array);

$busiData_array = array(
    'functionCode' => '10009101'
);
$busiData_json = json_encode($busiData_array);
// dump($busiData_json);
//3Des加密业务数据
$crypt3Des = new Crypt3Des($des3pwd); 
$busiData_json_3des = $crypt3Des->encrypt($busiData_json);
// dump($busiData_json_3des);

//对header进行签名
$private_key_path = 'rsa_private_key_xd.pem';
$key = openssl_pkey_get_private(file_get_contents($private_key_path));
openssl_sign($header_json, $sign, $key, OPENSSL_ALGO_SHA1);
$sign = base64_encode($sign);
// dump($sign);

$signatureValue_array = array(
    'signatureValue' => $sign
);
$signatureValue_json = json_encode($signatureValue_array);

$post_data_array = array(
    'header' => $header_json,
    'busiData' => $busiData_json_3des,
    'securityInfo' => $signatureValue_json
);

// dump($post_data_array);
$post_data_json = json_encode($post_data_array);
// dump($post_data_json);

// $response_json = postJson($url,$post_data_json);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_json);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type:application/json;charset=utf-8',
    'Content-Length:'.strlen($post_data_json))
    );
$response_json = curl_exec($ch);
curl_close($ch);
// echo $result;
dump($response_json);

$result_array = json_decode($response_json);
dump($result_array);

// $response_securityInfo = $result_array['securityInfo'];
// // dump($response_securityInfo);

// $response_sign_array = json_decode($response_securityInfo,true);
// // dump($response_securityInfo);
// $response_sign = $response_sign_array['signatureValue'];
// // dump($response_sign);
//对header进行验签
// $public_key_path = 'rsa_public_key_xd.pem';
// $key = openssl_pkey_get_public(file_get_contents($public_key_path));
// if(openssl_verify($header_json, $response_sign, $key, OPENSSL_ALGO_SHA1) === 1){
//     echo '验签通过';
// }else {
//     echo '验签不通过';
// }

// $response_busiData_3des = $result_array['busiData'];
// //对3des加密过的json进行解密
// // dump($response_busiData_3des);
// $response_busiData_json = $crypt3Des->decrypt($response_busiData_3des);
// // dump($response_busiData_json);
// $response_busiData_array = json_decode($response_busiData_json,true);
// // dump($response_busiData_array);

