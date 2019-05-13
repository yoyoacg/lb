<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/13
 * Time: 17:12
 */

namespace app\admin\controller;


class Online extends Base
{

    public function index(){
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
//            if ($this->request->request('keyField')) {
//                return $this->selectpage();
//            }
            $filter = $this->request->param();
            $sort = $filter['sort'];
            $order = $filter['order'];
            $offset = $filter['offset'];
            $limit = $filter['limit'];
            $cache_where = json_decode($filter['filter']);
            $where=[];
            foreach ($cache_where as $k=>$v){
                if($k=='RoomName'){
                    $where['OnLineInfo.RoomName']=['LIKE','%'.$v.'%'];
                }else{
                    $where['LockGame.'.$k]=$v;
                }
            }
            $total = $this->accounts
                ->table('LockGame')
                ->join('AccountsInfo','LockGame.UserID=AccountsInfo.UserID','LEFT')
                ->join('OnLineInfo','LockGame.KindID=OnLineInfo.KindID AND LockGame.ServerID=OnLineInfo.ServerID','LEFT')
                ->field('LockGame.*,AccountsInfo.NickName,OnLineInfo.RoomName')
                ->where($where)
                ->count();
            $list = $this->accounts
                ->table('LockGame')
                ->join('AccountsInfo','LockGame.UserID=AccountsInfo.UserID','LEFT')
                ->join('OnLineInfo','LockGame.KindID=OnLineInfo.KindID AND LockGame.ServerID=OnLineInfo.ServerID','LEFT')
                ->field('LockGame.*,AccountsInfo.NickName,OnLineInfo.RoomName')
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

}