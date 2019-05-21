<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Token;
use think\Db;
use think\Request;
use think\Validate;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    private $sqlsvr;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->sqlsvr =Db::connect( config('database.sql_server')['accounts']);
    }

    public function index()
    {
        $usercount = $this->request->param('UserContent','');
        $content = $this->request->param('Content','');
        $this->assign('usercontent',$usercount);
        $this->assign('content',$content);
        return $this->view->fetch();
    }

    public function news()
    {
        $newslist = [];
        return jsonp(['newslist' => $newslist, 'new' => count($newslist), 'url' => 'https://www.fastadmin.net?ref=news']);
    }

    /**
     * 登录
     * @return array
     */
    public function login(){
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        $result=[];
        if(!empty($account)&&!empty($password)){
            $data = $this->sqlsvr->table('dbo.AccountsInfo')
                ->where('Accounts',$account)
                ->where('LogonPass',md5($password))
                ->value('UserID');
            if($data){
                $result=[
                    'code'=>1,
                    'msg'=>'登录成功'
                ];
                session('login',$data);
            }else{
                $result=[
                    'code'=>0,
                    'msg'=>'账号或密码错误'
                ];
            }
        }
        if(empty($password)){
            $result=[
                'code'=>0,
                'msg'=>'请输入密码'
            ];
        }
        if(empty($account)){
            $result=[
                'code'=>0,
                'msg'=>'请输入账号'
            ];
        }
        return $result;
    }

    public function is_login(){
        if(session('login')){
            return ['code'=>1,'msg'=>'success'];
        }else{
            return ['code'=>0,'msg'=>'fail'];
        }
    }

    public function register(){
        $params = $this->request->post();
        $usercontent = $params['usercontent'];
        $content = $params['content'];
        $rule = [
            'mobile'    => 'regex:/^1\d{10}$/',
            'password'  => 'require|length:6,16',
            'nickname'  => 'require|length:3,30',
            'captcha'   => 'require|captcha',
        ];

        $msg = [
            'nickname.require' => '昵称必须填写',
            'nickname.length'  => '昵称请输入3-30个字符',
            'password.require' => '密码不能空',
            'password.length'  => '密码请输入6-16位',
            'captcha.require'  => '请输入验证码',
            'captcha.captcha'  => '请输入正确验证码',
        ];
        $data = [
            'mobile'    => $params['mobile'],
            'nickname'  => $params['nickname'],
            'password'  => $params['password'],
            'captcha'   => $params['captcha'],
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->error(__($validate->getError()), null);
        }
        $check=$this->sqlsvr->table('AccountsInfo')
            ->where('Accounts',$params['mobile'])
            ->value('UserID');
        if($check) $this->error('该手机号已被注册',null);
        $password=md5($params['password']);
        $InsurePass = md5(123456);
        $Ip = $this->request->ip();
        $time=date('Y-m-d H:i:s');
        $accounts = [
            'Accounts'=>$params['mobile'],
            'NickName'=>$params['nickname'],
            'RegAccounts'=>$password,
            'LogonPass'=>$password,
            'InsurePass'=>$InsurePass,
            'RegisterTime'=>$time,
            'RegisterIP'=>$Ip,
            'RegisterType'=>1,
            'ShutTime'=>$time,
            'LastLogonIP'=>$Ip,
            'LastLogonTime'=>$time,
            'NoteDate'=>$time,
            'SpreaderID'=>empty($content)?0:intval($content),
            'WX_openid'=>md5($params['mobile']),
            'WX_nickname'=>$params['nickname'],
            'WX_headimgurl'=>'~~'.rand(1,9).'.png',
            'WX_privilege'=>$params['mobile'],
            'WX_unionid'=>md5($params['mobile']),
        ];
        $UserId = $this->sqlsvr->table('AccountsInfo')->insertGetId($accounts);
        if($UserId){
            $task = [
                'UserID'=>$UserId
            ];
            $errorpass=[
                'UserID'=>$UserId,
                'RecordDate'=>$time
            ];
            $this->sqlsvr->table('AccountsInfo')->where('UserID',$UserId)->update('AccountID',$UserId);
            $this->sqlsvr->table('Task')->insert($task);
            $this->sqlsvr->table('ErrorPassRecord')->insert($errorpass);
            $renrendai = [
                'UserID'=>$UserId
            ];
            if($usercontent){
                $renrendai['ParentUserID']=$usercontent;
                $leve1= $this->sqlsvr->table('RenRenDaiInfo')->where('UserID',$usercontent)->value('ParentUserID');
                if($leve1){
                    $leve1_2=$this->sqlsvr->table('RenRenDaiInfo')->where('UserID',$leve1)->value('ParentUserID');
                    if($leve1_2){
                        $this->sqlsvr->table('RenRenDaiInfo')->where('UserID',$leve1_2)->setInc('UserCount_Lv3',1);
                    }
                    $this->sqlsvr->table('RenRenDaiInfo')->where('UserID',$leve1)->setInc('UserCount_Lv2',1);
                }
                $this->sqlsvr->table('RenRenDaiInfo')->where('UserID',$usercontent)->setInc('UserCount_Lv1',1);
            }
            $this->sqlsvr->table('RenRenDaiInfo')->insert($renrendai);
            $this->success('注册成功',null);
        }else{
            $this->error('注册失败',null);
        }
    }

}
