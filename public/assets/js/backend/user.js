define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'user/index',
                    add_url: 'user/add',
                    edit_url: 'user/edit',
                    del_url: 'user/del',
                    multi_url: 'user/multi',
                    table: 'user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'UserID',
                sortName: 'UserID',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'UserID', title: __('游戏ID'), sortable: true},
                        {field: 'Accounts', title: __('用户账号')},
                        {field: 'NickName', title: __('昵称'), operate: 'LIKE'},
                        {field: 'Score', title: __('金币'), operate: false,sortable: true},
                        {field: 'FangKa', title: __('房卡'), operate: false,sortable: true},
                        {field: 'DiamondScore', title: __('砖石'), operate: false,sortable: true},
                        {field: 'InsideAccount', title: __('账号类型'), operate: false,sortable: true},
                        {field: 'IsAgent', title: __('代理'), searchList:{0:'否',1:'是'},sortable: true,formatter: Table.api.formatter.label},
                        {field: 'LogonTimes', title: __('登录次数'), operate: false,sortable: true},
                        {field: 'LastLogonIP', title: __('最后登录IP'), operate: false},
                        {field: 'LastLogonTime', title: __('最后登录时间'), operate: 'BETWEEN',sortable:true},
                        {field: 'StunDown', title: __('冻结'), searchList: {0:'否',1:'是'},custom:{0:'success',1:'danger'},formatter:Table.api.formatter.status},
                        {field: 'ShutReason', title: __('冻结原因'), operate: false},
                        {field: 'ShutTime', title: __('冻结时间'), operate: 'BETWEEN',sortable:true},
                        {field: 'RegisterTime', title: __('注册时间'), operate: 'BETWEEN',sortable:true},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
            $(document).on('click','.btn-dj',function () {
               var ids=Table.api.selectedids(table).join(',');
               layer.open({
                   type:1,
                   title:'冻结',
                   id:'dj',
                   area:['30%'],
                   content:'<div style="padding: 20px;text-align: center" >确定冻结选中的用户？</div>',
                   btn:['确定','取消'],
                   yes:function () {
                       layer.closeAll();
                       Table.api.multi("dj", ids, table, this);
                   },
               });
            });
            $(document).on('click','.btn-jd',function () {
                var ids = Table.api.selectedids(table).join(",");
                Table.api.multi("jd", ids, table, this);
            });
            $(document).on('click','.btn-diamond',function () {
                var ids = Table.api.selectedids(table);
                var action = $(this).data('action');
                if(eval(ids).length>1){
                    Fast.api.msg('<code>只能操作单一用户</code>');
                }else{
                    Fast.api.open('user/zs?action='+action+'&ids='+ids,'赠送');
                }
            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        zs:function(){
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});