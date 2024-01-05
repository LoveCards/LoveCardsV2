<?php
//公共类
namespace geetest;

//TP类
use think\Facade;
use think\facade\Config;

class Gt4 extends Facade
{
    public static function validate($lotNumber, $captchaOutput, $passToken, $genTime)
    {
        if (Config::get('lovecards.class.geetest.DefSetValidatesStatus') == 0) {
            return true;
        }
        error_reporting(0);
        // 注意处理接口异常情况，当请求极验二次验证接口异常时做出相应异常处理
        // pay attention to interface exceptions, and make corresponding exception handling when requesting GeeTest secondary verification interface exceptions or response status is not 200
        // 保证不会因为接口请求超时或服务未响应而阻碍业务流程
        // website's business will not be interrupted due to interface request timeout or server not-responding
        function post_request($url, $postdata)
        {
            $data = http_build_query($postdata);

            $options    = array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded",
                    'content' => $data,
                    'timeout' => 5
                )
            );
            $context = stream_context_create($options);
            $result    = file_get_contents($url, false, $context);
            if ($http_response_header[0] != 'HTTP/1.1 200 OK') {
                $result = array(
                    "result" => "success",
                    "reason" => "request geetest api fail"
                );
                return json_encode($result);
            } else {
                return $result;
            }
        }

        // 1.初始化极验参数信息
        // 1.initialize geetest parameter
        $captcha_id = Config::get('lovecards.class.geetest.DefSetGeetestId');
        $captcha_key = Config::get('lovecards.class.geetest.DefSetGeetestKey');
        $api_server = "http://gcaptcha4.geetest.com";

        // 2.获取用户验证后前端传过来的验证流水号参数
        // 2.get the verification parameters passed from the front end after verification
        $lot_number = $lotNumber;
        $captcha_output = $captchaOutput;
        $pass_token = $passToken;
        $gen_time = $genTime;

        // 3.生成签名
        // 3.generate signature
        // 生成签名使用标准的hmac算法，使用用户当前完成验证的流水号lot_number作为原始消息message，使用客户验证私钥作为key
        // use standard hmac algorithms to generate signatures, and take the user's current verification serial number lot_number as the original message, and the client's verification private key as the key
        // 采用sha256散列算法将message和key进行单向散列生成最终的签名
        // use sha256 hash algorithm to hash message and key in one direction to generate the final signature
        $sign_token = hash_hmac('sha256', $lot_number, $captcha_key);

        // 4.上传校验参数到极验二次验证接口, 校验用户验证状态
        // 4.upload verification parameters to the secondary verification interface of GeeTest to validate the user verification status
        // captcha_id 参数建议放在 url 后面, 方便请求异常时可以在日志中根据id快速定位到异常请求
        // geetest recommends to put captcha_id parameter after url, so that when a request exception occurs, it can be quickly located in the log according to the id
        $query = array(
            "lot_number" => $lot_number,
            "captcha_output" => $captcha_output,
            "pass_token" => $pass_token,
            "gen_time" => $gen_time,
            "sign_token" => $sign_token
        );
        $url = sprintf($api_server . "/validate" . "?captcha_id=%s", $captcha_id);
        $res = post_request($url, $query);
        $obj = json_decode($res, true);

        // 5.根据极验返回的用户验证状态, 网站主进行自己的业务逻辑
        // 5. taking the user authentication status returned from geetest into consideration, the website owner follows his own business logic
        //echo sprintf('{"login":"%s","reason":"%s"}', $obj['result'], $obj['reason']);
        if ($obj['result'] == 'success') {
            return true;
        } else {
            return false;
        }
    }
}
