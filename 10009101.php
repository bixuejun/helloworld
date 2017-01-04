<?php


include 'functions.php';
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

//3Des加密业务数据
$crypt3Des = new Crypt3Des($des3pwd); 
$busiData_json_3des = $crypt3Des->encrypt($busiData_json);

//对header进行签名
$private_key_path = 'rsa_private_key_xd.pem';
$key = openssl_pkey_get_private(file_get_contents($private_key_path));
openssl_sign($header_json, $sign, $key, OPENSSL_ALGO_SHA1);
$sign = base64_encode($sign);

$signatureValue_array = array(
    'signatureValue' => $sign
);
$signatureValue_json = json_encode($signatureValue_array);

$post_data_array = array(
    'header' => $header_json,
    'busiData' => $busiData_json_3des,
    'securityInfo' => $signatureValue_json
);

$post_data_json = json_encode($post_data_array);

$response_json = postJson($url,$post_data_json);
// dump($response_json);

$result_array = json_decode($response_json,true);
// dump($result_array);

$response_securityInfo = $result_array['securityInfo'];

$response_sign = $result_array['securityInfo']['signatureValue'];
// dump($response_sign);

//对header进行验签,验签可以不做
// $public_key_path = 'rsa_public_key_tc.pem';
// $key = openssl_pkey_get_public(file_get_contents($public_key_path));
// $response_sign = base64_decode($response_sign);
// dump($response_sign);
// $response_header_json = json_decode($response_json);
// $response_header_json = json_encode($response_header_json->header);
// dump($response_header_json);
// if(openssl_verify($response_header_json, $response_sign, $key, OPENSSL_ALGO_SHA1) === 1){
//     echo '验签通过';
// }else {
//     echo '验签不通过';
// }

$response_busiData_3des = $result_array['busiData'];
//对3des加密过的json进行解密
// dump($response_busiData_3des);
$response_busiData_json = $crypt3Des->decrypt($response_busiData_3des);
// dump($response_busiData_json);
$response_busiData_array = json_decode($response_busiData_json,true);
dump($response_busiData_array);

