define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'online/index',
                }
            });

            var table = $("#table");
            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'ID',
                sortName: 'ID',
                search:false,
                columns: [
                    [
                        {field:'ID',title:'ID'},
                        {field: 'UserID', title: __('用户ID'), sortable: true},
                        // {field: 'Accounts', title: __('用户账号')},
                        {field: 'NickName', title: __('昵称'), operate: false},
                        {field: 'KindID', title: __('游戏类型ID'),sortable:true},
                        {field: 'ServerID', title: __('游戏ID') },
                        {field: 'TableID', title: __('桌号') },
                        {field: 'RoomName', title: __('游戏名称'), operate: 'LIKE',sortable:true},
                        {field: 'LogTime', title: __('进入时间'), operate: false,sortable: true},
                        // {field: 'Score', title: __('金币'), operate: false,sortable: true},
                        // {field: 'FangKa', title: __('房卡'), operate: false,sortable: true},
                        // {field: 'DiamondScore', title: __('砖石'), operate: false,sortable: true},
                        // {field: 'InsideAccount', title: __('账号类型'), operate: false,sortable: true},
                        // {field: 'IsAgent', title: __('代理'), searchList:{0:'否',1:'是'},sortable: true,formatter: Table.api.formatter.label},
                        // {field: 'LogonTimes', title: __('登录次数'), operate: false,sortable: true},
                        // {field: 'LastLogonIP', title: __('最后登录IP'), operate: false},
                        // {field: 'LastLogonTime', title: __('最后登录时间'), operate: 'BETWEEN',sortable:true},
                        // {field: 'StunDown', title: __('冻结'), searchList: {0:'否',1:'是'},custom:{0:'success',1:'danger'},formatter:Table.api.formatter.status},
                        // {field: 'ShutReason', title: __('冻结原因'), operate: false},
                        // {field: 'ShutTime', title: __('冻结时间'), operate: 'BETWEEN',sortable:true},
                        // {field: 'RegisterTime', title: __('注册时间'), operate: 'BETWEEN',sortable:true},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
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