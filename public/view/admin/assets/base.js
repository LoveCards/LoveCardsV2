//基础
var apiUrlAuthlogin = '/api/auth/login';//登入
var apiUrlAuthlogout = '/api/auth/logout';//注销

var apiUserDelete = '/api/user/delete';//删除用户
var apiUserAdd = '/api/user/add';//添加用户
var apiUserEdit = '/api/user/edit';//添加用户

var apiSystemSite = '/api/system/site';//系统设置
var apiSystemEmail = '/api/system/email';//系统邮箱设置

var apiCardsTagAdd = '/api/cardstag/add';//添加标签
var apiCardsTagEdit = '/api/cardstag/edit';//编辑标签
var apiCardsTagDelete = '/api/cardstag/delete';//删除标签

/*
*API请求函数
*/
function apiAjax0(data, url, type = 'GET', dataType = 'json') {
    //设置公共变量
    var data;
    $.ajax({
        url: url,
        type: type,
        async: false,
        dataType: dataType,
        data: data,

        beforeSend: function () {//提交数据前执行判断，根据返回t/f决定是否发送
            return true;
        },

        success: function (result, status) {
            if (result.ec == '200') {
                //成功
                data = result;
                return;
            } else {
                //失败
                data = false;
                var arrData = result.data;
                var reuData = '';
                //整理二维数组
                for (let index in arrData) {
                    reuData = reuData + arrData[index] + '&nbsp;';
                }
                //详细输出
                reuData = '<br>' + result.ec + '&nbsp;:&nbsp;' + reuData;
                mdui.snackbar({
                    message: 'msg&nbsp;:&nbsp;' + result.msg + reuData,
                    position: 'left-top'
                });
                return;
            }
        },

        error: function () {
            data = false;
            mdui.snackbar({
                message: '4XX&nbsp;:&nbsp;未知错误，数据获取失败',
                position: 'left-top'
            });
            return;
        }
    })
    //返回公共变量
    return data;
}

/*
*分页按钮
*/
function pager() {
    //获取数据
    pager = $(".pager").find("li");
    pageFirst = pager.eq(0);
    pageLast = pager.eq(1);
    //判断分页按钮状态
    if (pageFirst.attr("class") == "disabled") {
        $("#pageFirst").attr("disabled", "");
    } else {
        $("#pageFirst").attr("jumpUrl", pageFirst.children().attr("href"));
    }

    if (pageLast.attr("class") == "disabled") {
        $("#pageLast").attr("disabled", "");
    } else {
        $("#pageLast").attr("jumpUrl", pageLast.children().attr("href"));
    }
    //翻页按钮初始化
    if (pager.length == 0) {
        $("#pageFirst").remove();
        $("#pageLast").remove();
    } else {
        $("#pageFirst").click(function () {
            jumpUrl($(this).attr("jumpUrl"));
        })
        $("#pageLast").click(function () {
            jumpUrl($(this).attr("jumpUrl"));
        })
    }
}

/*
*跳转
*/
function jumpUrl(url, time = 600) {
    setTimeout(function () {
        window.location.replace(url);
    }, time);
}

$(function () {
    /*
    *提示msg
    */
    //读取
    msg = $.cookie('msg');
    //判断
    if (msg != 'undefined' && msg != undefined) {
        data = false;
        mdui.snackbar({
            message: msg,
            position: 'left-top'
        });
        //重置
        $.cookie('msg', 'undefined', { path: '/' });
    }
});
