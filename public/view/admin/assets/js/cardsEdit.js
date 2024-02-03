//Tag数据全局数组
var checkChooseTagId = [];

//函数-初始化标签组件
function InitialChooseTagAdd(arrData) {
    if (arrData == '') {
        return;
    }
    arrData = JSON.parse(arrData);
    //重置组件
    $('#btnChooseTagAdd').siblings("div[class='mdui-chip']").remove();
    //设置组件
    $("input[name='inputChooseTag']").each(function (i) {
        for (var i in arrData) {
            if (arrData[i] == $(this).val()) {
                $(this).prop('checked', true);
                checkChooseTagId[i] = $(this).val();
                $('#btnChooseTagAdd').before(`<div class="mdui-chip">
                    <span class="mdui-chip-title">${$(this).parent().text()}</span>
                </div>` );
            }
        }
    });
}

//按钮-初始化-删除Img
$('.js-btn-delete-UrlUpdataImage').click(function () {
    $(this).parent().parent().parent().parent().remove();
});

//按钮-图片上传
$('#btnUploadImage').change(function () {
    var formData = new FormData();
    formData.append('file', dataUpdataImage.files[0]);
    $('#btnUploadImage').find('span').text('正在上传');
    //提交数据
    var result = apiAjax1(formData, apiUrlUploadImage, 'POST');
    if (result) {
        //成功
        mdui.snackbar({
            message: '上传成功',
            position: 'left-top'
        });
        $('#urlUpdataImage').val('/storage/' + result.data);
        $('#btnUploadImage').find('span').text('继续上传');
        return;
    } else {
        $('#urlUpdataImage').attr('placeholder', '上传失败，请重试');
        $('#btnUploadImage').find('span').text('上 传');
        return;
    }
});

//按钮-加载图片组件
$('#btnUpdataImgUrl').click(function () {
    var urlUpdataImage = $('#urlUpdataImage').val();
    console.log(checkChooseTagId)
    if (checkUrl(urlUpdataImage)) {
        //限制图片个数
        if ($('#listImageUrl').children().length >= DefSetCardsImgNum) {
            mdui.snackbar({
                message: '超过上传限制',
                position: 'left-top'
            });
            return;
        }
        //添加图片到位置
        $('#listImageUrl').append(`
            <div class="mdui-col mdui-p-b-1">
                <div class="mdui-grid-tile">
                    <div class="css-cardsAdd-img">
                        <img class="js-url-UpdataImage" src="${urlUpdataImage}">
                        <div class="mdui-cardAdd-menu">
                            <button class="mdui-btn mdui-btn-icon js-btn-delete-UrlUpdataImage">
                                <i class="mdui-icon material-icons" style="padding:unset;">clear</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    } else {
        mdui.snackbar({
            message: 'URL不合法，请重试',
            position: 'left-top'
        });
    }
    //重置Url输入框
    $('#urlUpdataImage').val('');
    //删除按钮再次初始化
    $('.js-btn-delete-UrlUpdataImage').click(function () {
        $(this).parent().parent().parent().parent().remove();
    });
})

//按钮-加载标签组件
$('#btnChooseTag').click(function () {
    //重置标签组件
    $('#btnChooseTagAdd').siblings("div[class='mdui-chip']").remove();
    checkChooseTagId = [];
    //加载标签组件
    $("input[name='inputChooseTag']:checked").each(function (i) {
        checkChooseTagId[i] = $(this).val();
        $('#btnChooseTagAdd').before(`<div class="mdui-chip">
            <span class="mdui-chip-title">${$(this).parent().text()}</span>
        </div>` );
    });

})