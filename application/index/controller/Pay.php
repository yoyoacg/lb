<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/6
 * Time: 15:05
 */

namespace app\index\controller;



use app\common\library\GacPay;
use think\Controller;

class Pay extends Controller
{
    public function index(){
        $GacPay = new GacPay();
        $data = $GacPay->pay('100',time().rand(1000,9999),'游戏充值','100砖石');
    }

}