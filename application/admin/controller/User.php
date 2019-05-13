<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/9
 * Time: 16:40
 */

namespace app\admin\controller;


use think\Db;

class User extends Base
{

    protected $searchFields='Accounts,NickName,UserID';

    /**
     * 查看
     */
    public function index()
    {
//        $data = $this->accounts->table('AccountsInfo')->select();
//        var_dump($data);
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
//            if ($this->request->request('keyField')) {
//                return $this->selectpage();
//            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->accounts
                ->table('AccountsInfo')
                ->where($where)
                ->count();
            $list = $this->accounts
                ->table('AccountsInfo')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function multi($ids = "")
    {
        $params = $this->request->param();
        if(!empty($params['action'])&&method_exists($this,$params['action'])){
            call_user_func([$this,$params['action']],$params['ids']);
        }
        $this->error('操作失误');
    }

    /**
     * 冻结
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function dj($ids=null){
            if(!empty($ids)){
                $update=[
                    'StunDown'=>1,
                    'ShutTime'=>date('Y-m-d H:i:s'),
                ];
                $this->accounts->table('AccountsInfo')->whereIn('UserID',$ids)->update($update);
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
    }

    /**
     * 解冻
     * @string $ids
     */
    public function jd($ids=null){
        if(!empty($ids)){
            $update=[
                'StunDown'=>0,
                'ShutTime'=>date('Y-m-d H:i:s'),
            ];
            $this->accounts->table('AccountsInfo')->whereIn('UserID',$ids)->update($update);
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 赠送
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function zs(){
        $param = $this->request->param();
        if(strrpos($param['ids'],',')) $this->error('只能选择一个账号，请忽选择多账号');
        $action = $param['action'];
        $info = ['buy'=>'购买','bc'=>'补偿', 'jl'=>'奖励'];
        $user =  $this->accounts->table('AccountsInfo')
            ->where('UserID',$param['ids'])
            ->field('UserID,Accounts,NickName,Score,FangKa,DiamondScore')
            ->find();
        if($this->request->isPost()){
            $post = $this->request->post('row/a');
            if($post['num']<10){
                $this->error('最低充值10元');
            }
            $game_log_where = [
                'UserID'=>$user['UserID'],
                'Date'=>date('Y-m-d')
            ];
            $is_zs = $this->gamelog->table('dbo.HouTaiZengSongDayLog')
                ->where($game_log_where)
                ->value('ID');
            if($post['action']=='diamond'){
                $diamond = $post['num'];
                $score = 0;
                $fk = 0;
            }elseif ($post['action']=='score'){
                $diamond = 0;
                $score = $post['num']*100;
                $fk = 0;
            }else{
                $diamond = 0;
                $score = 0;
                $fk = $post['num'];
            }
            $scorelog = [
                'UserID'=>$user['UserID'],
                'BeforeScore'=>$user['Score'],
                'Score'=>$score,
                'AfterScore'=>bcadd($user['Score'],$score),
                'BeforeDiamond'=>$user['DiamondScore'],
                'Diamond'=>$diamond,
                'AfterDiamond'=>bcadd($user['DiamondScore'],$diamond),
                'BeforeFangKa'=>$user['FangKa'],
                'FangKa'=>$fk,
                'AfterFangKa'=>bcadd($user['FangKa'],$fk),
                'Revenue'=>0,
                'clientType'=>0,
                'taxRate'=>0,
                'GameLogID'=>0,
                'GameUserLogID'=>0,
                'RevenueGx'=>0,
                'ServerID'=>-1,
                'KindID'=>-1,
                'ScoreKind'=>987654321,
                'RecordTime'=>date('Y-m-d H:i:s'),
                'Reason'=>1,
                'Note'=>$info[$post['info']],
            ];
            Db::startTrans();
            try{
                $this->gamelog->table('dbo.UserScoreLog')->insert($scorelog);
                if($is_zs){
                    if($score){
                        $this->gamelog->table('dbo.HouTaiZengSongDayLog')
                            ->where($game_log_where)->setInc('Score',$score);
                    }
                    if($diamond){
                        $this->gamelog->table('dbo.HouTaiZengSongDayLog')
                            ->where($game_log_where)->setInc('Diamond',$diamond);
                    }
                    if($fk){
                        $this->gamelog->table('dbo.HouTaiZengSongDayLog')
                            ->where($game_log_where)->setInc('FangKa',$fk);
                    }
                }else{
                    $game_data_log=[
                        'UserID'=>$user['UserID'],
                        'Score'=>$score,
                        'Diamond'=>$diamond,
                        'FangKa'=>$fk,
                        'Date'=>date('Y-m-d'),
                    ];
                    $this->gamelog->table('dbo.HouTaiZengSongDayLog')->insert($game_data_log);
                }
            }catch (\Exception $exception){
                Db::rollback();
                $this->error('赠送失败');
            }
            Db::commit();
            $this->success('赠送成功');
        }
       $action_list = [
           'diamond'=>'钻石数量',
           'score'=>'金币数量',
           'fk'=>'房卡数量',
       ];
        $this->assign('action',$action);
        $this->assign('action_info',$action_list[$action]);
        $this->assign('user',$user);
        $this->assign('info',$info);
      return  $this->view->fetch();
    }

    /**
     * 重置密码
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function repass(){
        $ids = $this->request->param('ids');
        if($this->request->isAjax()){
            $post = $this->request->post('row/a');
            if(empty($post['password'])||empty($post['repassword'])||strlen($post['password'])<6){
                $this->error('请按要求输入新密码');
            }
            if($post['password']!==$post['repassword']){
                $this->error('两次密码不一致');
            }
            $password = md5($post['password']);
            $this->accounts->table('AccountsInfo')->whereIn('UserID',$post['ids'])->update(['LogonPass'=>$password]);
            $this->success('修改密码成功');
        }
        $this->assign('ids',$ids);
        return $this->view->fetch();
    }





}