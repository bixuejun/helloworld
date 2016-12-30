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
function postData($url, $post)
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

$url = 'http://test.niiwoo.com:5102/boss/uap';

$des3pwd = 'AD905@!QLF-D25WEDA5!@#$%';

$header = array(
        'orgCode' => '1003',
        'transNo' => getOrderId(),
        'transDate' => date('Y-m-d H:i:s'),
        'userName' => 'tuandai',
        'userPassword' => bin2hex(md5('tuandai_test123')),
        'functionCode' => '10009101',
    );
$busiData = '';
$signatureValue = '';

$post_data = array(
    'header' => $header,
    'busiData' => $busiData,
    'securityInfo' => $signatureValue
);


dump($post_data);

