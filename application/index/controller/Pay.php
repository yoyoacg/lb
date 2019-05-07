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
use think\Db;
use think\Request;

class Pay extends Controller
{

    protected $paySql;
    protected $accounts;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->accounts = Db::connect( config('database.sql_server')['accounts']);
        $this->paySql = Db::connect( config('database.sql_server')['payservice']);
    }

    public function index(){
        $param = $this->request->param();
        $payAccounts = $param['payAccounts']??null;
        $price = $param['hdfSalePrice']??null;
        $terminal =$param['terminal']??'';
        if(empty($payAccounts)||empty($price)) $this->error('支付失败！！！');
        $user = $this->accounts->table('dbo.AccountsInfo')
            ->where('UserID',$payAccounts)
            ->value('NickName');
        if(empty($user)) $this->error('用户不存在');
        $order = rand(100,999).date('YmdHis').rand(100,999);
        $price=number_format($price,2);
//        $price=10;
        $data=[
            'ShareID'=>$terminal,
            'UserID'=>$payAccounts,
            'OperUserID'=>$payAccounts,
            'GameID'=>$payAccounts,
            'Accounts'=>$payAccounts,
            'OrderID'=>$order,
            'CardPrice'=>$price,
            'OrderAmount'=>$price,
            'PayAmount'=>$price,
            'OrderStatus'=>0,
            'CardTypeID'=>0,
            'CardTotal'=>0,
            'IPAddress'=>$this->request->ip(),
            'ApplyDate'=>date('Y-m-d H:i:s'),
            'Platform'=>'wap',
        ];
        $result = $this->paySql->table('dbo.OnLineOrder')->insert($data);
        if($result){
            $GacPay = new GacPay();
            $url = $GacPay->pay($price,$order,'游戏充值',$price.'钻石',$user);
            if($url){
                $this->redirect($url);
            }else{
                $this->error();
            }
        }
    }



}