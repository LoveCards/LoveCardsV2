var apiUrlUploadImage = '/api/upload/image'//图片上传

//默认添加卡片上传图片个数
const DefSetCardsImgNum = 9;
//默认添加卡片标签个数
const DefSetCardsTagNum = 3;

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
function apiAjax0a(data, url, type = 'GET', snackbar = true, dataType = 'json') {
    //设置公共变量
    var data;
    var state;
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
                state = true;
                return;
            } else {
                //失败
                data = result;
                state = false;
                if (snackbar) {
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
                }
                return;
            }
        },

        error: function () {
            data = result;
            state = false;
            if (snackbar) {
                mdui.snackbar({
                    message: '4XX&nbsp;:&nbsp;未知错误，数据获取失败',
                    position: 'left-top'
                });
            }
            return;
        }
    })
    //返回公共变量
    return {
        'state': state,
        'r': data
    };
}

/*
*API请求函数-文件上传
*/
function apiAjax1(data, url, type = 'GET', dataType = 'json') {
    //设置公共变量
    var data;
    $.ajax({
        url: url,
        type: type,
        async: false,
        dataType: dataType,
        data: data,
        contentType: false,
        processData: false,

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
*URL验证
*/
function checkUrl(url) {
    //判断是否为站内URL
    if (url == '') {
        return false;
    }
    if (url.search('storage/image')) {
        return true;
    } else {
        var strRegex = '^((https|http|ftp|rtsp|mms)?://)?'
            + '(([0-9a-z_!~*().&=+$%-]+: )?[0-9a-z_!~*().&=+$%-]+@)?' //ftp的user@
            + '(([0-9]{1,3}.){3}[0-9]{1,3}|'// IP形式的URL- 199.194.52.184
            + '([0-9a-z_!~*()-]+.)*'// 域名- www.
            + '[a-z]{2,6})'//域名的扩展名
            + '(:[0-9]{1,4})?'// 端口- :80
            + '((/?)|(/[0-9a-z_!~*().;?:@&=+$,%#-]+)+/?)$';
        return new RegExp(strRegex).test(url);
    }
}

/*
*URL解析
*/
function urlConversion(path) {
    let matcht = /^(https?:\/\/)([0-9a-z.]+)(:[0-9]+)?([/0-9a-z.]+)?(\?[0-9a-z&=]+)?(#[0-9-a-z]+)?/i
    result = matcht.exec(path);
    if (result == null) {
        return false
    } else {
        return result;
    }
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

/*
*当前URLGet参数获取decodeURI
*/
function getUrlParam(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = decodeURI(window.location.search).substr(1).match(reg);
    if (r != null) return unescape(r[2]); return null;
}


/**
 * 将文本内容复制到剪切板
 * @param str 复制内容
 */
function copyText(str) {
    try {
        navigator.clipboard.writeText(str);
    } catch (err) {
        var state = false;
    }
    if (state == false) {
        mdui.snackbar({
            message: '好像出问题了，再试一次',
            position: 'left-top',
        });
        return false;
    } else {
        mdui.snackbar({
            message: '复制成功',
            position: 'left-top',
        });
        return true;
    }
}

$(function () {
    /*
    *提示msg
    */
    //读取
    // msg = $.cookie('msg');
    // //判断
    // if (msg != 'undefined' && msg != undefined) {
    //     data = false;
    //     mdui.snackbar({
    //         message: msg,
    //         position: 'left-top'
    //     });
    //     //重置
    //     $.cookie('msg', 'undefined', { path: '/' });
    // }
    //初始化-提示
    $('.js-mdui-Tooltip').each(function (index, domEle) {
        $(domEle).attr('mdui-tooltip', "{content:'" + $(domEle).val() + "'}")
    });
});
