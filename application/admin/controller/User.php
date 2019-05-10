<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/9
 * Time: 16:40
 */

namespace app\admin\controller;


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

    public function zs(){
        $param = $this->request->param();
        if(strrpos($param['ids'],',')) $this->error('只能选择一个账号，请忽选择多账号');
        if($this->request->isPost()){
            $this->success('赠送成功');
        }
        $user =  $this->accounts->table('AccountsInfo')
            ->where('UserID',$param['ids'])
            ->field('UserID,Accounts,NickName,Score,FangKa,DiamondScore')
            ->select();
      return  $this->view->fetch();
    }



}