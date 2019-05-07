<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/6
 * Time: 15:33
 */

namespace app\common\library;


class GacPay
{

    /**
     * 正式url
     * @var string
     */
//    protected $url = 'https://api.qapple.io/v2/api';

    /**
     * 测试url地址
     * @var string
     */
    protected $url = 'http://paysrv.qapple.io/v2/api';

    /**
     * 私钥
     * @var string
     */
    protected $private_key='MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAIdpDHlyd75BsCMC13syKcDQxCoha98ftlVCa0yWZ+vYtg7IqfOU52JB3BTXVssdETOxP7RjW3CHbHOQ4ZYkuoYbekvDrGoGRShVYh4o0Uc/vsy4wrqkJy8me1VuA/Qf/x3qgAL3ybbCzRUEtEdEvuGBkWEolXmc+pjXkBydTa7FAgMBAAECgYEAgWETtC50zupAexNKA8HoNvzBkWehg+zu8AOoNeM3pBbJzNJZ4AyUEEPRHnCp0yQQvY1LyvVr9tbN/pWdlTG+rORWzFKYBo9IxztrTuVYeUPVvdtTkWhI/LKGhajailgs3W81aj23ll0EBbG7Oohz2Q1c08u9SSn15LrUA5/6cQECQQC9lGLic08EZKIUy5nfzbksZBxUeND4zNwo+aEStN8Ik6Zd8knh+xvqECmlQBS0Div1BeDcSGJFw8MyG46gP2WhAkEAttooQkC+8Sq3aLMwztm2iC9e8M4oUZrynXfLNFz2F1l0o0wd0vCYMej0mu9DCtJwhHu7ZVueAwH0Mv0RlQZupQJAT1de2y8vDsOfIdzkFUpgCTgMsz2tF7OFIJD43H9eKJTCt+bDDRSu5hLFmydqgsC7nNxM82RH3LLFap8l3eMqgQJAHKxnJcSLbLwbGMMIw1cmpYJwK+jYL7vRkdnoNqThPlYb0UOtZZeu9hymxukAJWFMnandgA5239fdmGVQ7YKdtQJAAMCIq/8xPWeIdNMMMt+3DL472v9AnGNxoxXhVfGGEy/mMnz+TqwNniujObfF3XN+eniH6+ZqOjc1pQGauue5ig==';
    /**
     * 公钥
     * @var string
     */
    protected $public_key='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCCOpZuNoLWcizbHJ9C27GgTul83wsLAIqrIv2anUcEir87SRIEAo7lEPLw+J6aY1kmCXmxl7594fxE1LeqiSahz9aDCp703sBbug6MpPWgIYKlBrO0z5ESRriuZ/XmmfMaMxcrEFnqH0DnRw8a9boYu/jVLQuAZVkMj3symtFd+wIDAQAB';
    /**
     * 商户名
     * @var string
     */
    protected $merchantName='yoyoacg';
    /**
     * 会员名
     * @var string
     */
    protected $vipName='yoyoacg';
    /**
     * 回调地址
     * @var string
     */
    protected $notifyUrl='http://game.yoyoacg.com/return';

    public function pay($price='',$order='',$goods='游戏充值',$body=''){
        $price = number_format($price,2);
        $param_arr = array(
            'outTradeNo' => $order,  // 每个outTradeNo只能用一次，否则会因为订单重复而失败
            'orderAmountRmb' =>$price,  //下单金额，保留两位小数
            'merchantName' => $this->merchantName,  //商家账户名
            'vipName' => 'vip22',  //商家平台下会员名
            'subject' => $goods,  //商品的标题/交易标题/订单标题/订单关键字等
            'body' => $body, //对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body
            //具体的回调地址
            'notifyUrl' => $this->notifyUrl,
            'signType' => 'RSA'  //验签方式
        );
        ksort($param_arr);
        $str_signed = $this->MarkSign($param_arr);
        $param_arr['sign'] = $str_signed;
        //预下单地址
        $pre_pay_url = $this->url . "/merchant/merchantcenter/pay/prePay";
        $response_str = $this->http_request($pre_pay_url, $param_arr);
        $response = json_decode($response_str, true);
        var_dump($response);die();
        if ( $response['code'] != 200 ){
            echo 'request failed, server returns: '.$response_str;
        } else {
            $request_data = $response['data'];
            $request_signed = $request_data['sign'];
            unset($request_data['sign']);
            ksort($request_data);
            $request_unsigned = make_json_join_str($request_data);

            $check_sign = do_check($request_unsigned, $request_signed, QAPPLE_PUBLIC_KEY);  // 这里使用response的数据
            if ($check_sign){
                header("Location:".$request_data['returnUrl']);

//            echo '验签成功！';
            } else {
                echo '验签失败！';
            }

            //echo '返回跳转地址:' . $request_data['returnUrl'];

        }


    }

    /**
     * 获取签名
     * @param $params
     * @return string
     */
    public function MarkSign($params){
        ksort($params);
        $params_str = '';
        $float_key_arr = array('orderAmountRmb', 'receiveAmountGac', 'userpayAmountRmb');
        foreach ($params as $k=>$v){
            if (is_string($v) && empty($v)) {
                continue;
            }
            if (is_null($v)) {
                continue;
            }
            if (in_array($k, $float_key_arr)) {
                $params_str .= ($k . '=' . sprintf("%.2f", $v) . '&');
            } else {
                $params_str .= ($k . '=' . $v . '&');
            }
        }
        $str = substr($params_str,0,-1);
        $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->private_key, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        $key=openssl_get_privatekey($private_key);
        openssl_sign($str,$sign,$key);
        openssl_free_key($key);
        return base64_encode($sign);
    }

    /***
     * curl请求
     * @param string $url
     * @param array $post_data
     * @return bool|string
     */
    protected function http_request($url = '', $post_data = array())
    {
        if (empty($url) || empty($post_data)) {
            return false;
        }
        $headers = array(
            "Content-type: application/json;charset=utf-8",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Access-Control-Allow-Origin:*",
        );
        $post_string = json_encode($post_data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        $response = curl_exec($ch);
        return $response;
    }


}