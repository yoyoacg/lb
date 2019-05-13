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

    /**
     * 充值
     */
    public function index(){
        $param = $this->request->param();
        $payAccounts = $param['payAccounts']??null;
        $price = $param['hdfSalePrice']??null;
        $terminal =$param['terminal']??'';
        if(empty($payAccounts)||empty($price)) $this->error('支付失败！！！',null);
        $user = $this->accounts->table('dbo.AccountsInfo')
            ->where('UserID',$payAccounts)
            ->value('NickName');
        if(empty($user)) $this->error('用户不存在',null);
        $order = rand(100,999).date('YmdHis').rand(100,999);
        $price=number_format($price,2,'.','');
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
                $this->error('充值失败，请稍后再试',null);
            }
        }
    }

    /****
     * 充值回调
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function notify(){
        $param = $this->request->param();
        if(count($param)>1&&$param['status']==='SUCCESS'){
            $data=$this->paySql->table('dbo.OnLineOrder')
                ->where('OrderID',$param['outTradeNo'])
                ->where('OrderAmount',$param['orderAmountRmb'])
//                ->field('OnLineID,UserID,OrderAmount,OrderStatus')
                ->select();
            $user = $this->accounts->table('dbo.AccountsInfo')->where('UserID',$data['UserID'])->select();
            if($data){
                if($data['OrderStatus']===0){
				    $shareDetail = [
				        'OperUserID'=>$data['UserID'],
				        'ShareID'=>'1',
				        'UserID'=>$data['UserID'],
				        'GameID'=>$data['UserID'],
				        'Accounts'=>$user['Accounts'],
				        'SerialID'=>1,
				        'OrderID'=>$data['OrderID'],
				        'CardTypeID'=>1,
				        'CardPrice'=>$data['OrderAmount'],
				        'CardGold'=>0,
				        'BeforeGold'=>$user['Score'],
				        'CardTotal'=>$data['OrderAmount'],
				        'OrderAmount'=>$data['OrderAmount'],
				        'DiscountScale'=>0,
				        'PayAmount'=>$data['PayAmount'],
				        'IPAddress'=>$data['IPAddress'],
				        'ApplyDate'=>date('Y-m-d H:i:s'),
				        'ServerID'=>1,
				        'TrancationNO'=>$data['OrderID'],
				        'AgentID'=>1,
				        'PayPlatform'=>'青苹果',
                    ];
                    $this->accounts->table('dbo.AccountsInfo')
                        ->where('UserID',$data['UserID'])
                        ->setInc('DiamondScore',$data['OrderAmount']);
                    $this->paySql->table('dbo.OnLineOrder')
                        ->where('OnLineID',$data['OnLineID'])
                        ->update(['OrderStatus'=>2]);
                    $this->paySql->table('dbo.ShareDetailInfo')->insert($shareDetail);
                }
               echo  json_encode(['code'=>200,'message'=>'操作成功']);
                exit();
            }
        }
        echo json_encode(['code'=>500,'message'=>'fail']);
        exit();
    }



}