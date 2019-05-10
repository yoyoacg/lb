<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/9
 * Time: 16:15
 */

namespace app\admin\controller;


use app\common\controller\Backend;
use think\Db;
use think\Request;

class Base extends Backend
{

    protected $accounts;

    protected $qggame;

    protected $nativeweb;

    protected $payservice;

    protected $gamelog;


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $sqlConfig=config(config('database.sql_server'));
        $this->accounts = Db::connect($sqlConfig['accounts']);
        $this->qggame = Db::connect($sqlConfig['game']);
        $this->payservice = Db::connect($sqlConfig['payservice']);
        $this->gamelog = Db::connect($sqlConfig['gamelog']);
        $this->nativeweb = Db::connect($sqlConfig['nativeweb']);
    }

}