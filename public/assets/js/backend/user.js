define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {
    /**
     * 金额大写转换
     * @param money
     * @returns {string}
     */
    function prceNum(money) {
        var fraction = ['角','分'];
        var digit = ['零','壹','贰','叁','肆','伍','陆','柒','捌','玖'];
        var unit = [['元','万','亿'],['','拾','佰','仟']];
        var head = money < 0?'欠':'';
        money = Math.abs(money);
        var s = '';
        for (var i = 0; i < fraction.length; i++) {
            s += (digit[Math.floor(money * 10 * Math.pow(10, i)) % 10] + fraction[i]).replace(/零./, '');
        }
        s = s || '整';
        money = Math.floor(money);
        for (var i = 0; i < unit[0].length && money > 0; i++) {
            var p = '';
            for (var j = 0; j < unit[1].length && money > 0; j++) {
                p = digit[money % 10] + unit[1][j] + p;
                money = Math.floor(money / 10);
            }
            s = p.replace(/(零.)*零$/, '').replace(/^$/, '零') + unit[0][i] + s;
        }
        var sum= head + s.replace(/(零.)*零元/,'元').replace(/(零.)+/g, '零').replace(/^整$/, '零元整');
        return sum;
    }
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
            $(document).on('click','.btn-diamond,.btn-score,.btn-fk',function () {
                var ids = Table.api.selectedids(table);
                var action = $(this).data('action');
                if(eval(ids).length>1){
                    Fast.api.msg('<code>只能操作单一用户</code>');
                }else{
                    Fast.api.open('user/zs?action='+action+'&ids='+ids,'赠送');
                }
            });
            $(document).on('click','.btn-repassword',function () {
                var ids = Table.api.selectedids(table).join(',');
                Fast.api.open('user/repass?ids='+ids,'修改密码');
            });

        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        zs:function(){
            $('#c-num').on('change',function () {
                var money = $(this).val();
                var num = prceNum(money);
                var p='<p class="help-block" style="font-size: 20px"><code>'+num+'</code></p>';
                $(this).nextAll().remove();
                $(this).after(p);
            })
            Controller.api.bindevent();
        },
        repass:function(){
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