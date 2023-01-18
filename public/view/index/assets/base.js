//基础
var apiUrlCardsAdd = '/api/Cards/add'//添加卡
var apiUrlCardsGood = '/api/Cards/good'//添加卡
var apiUrlCardsCommentsAdd = '/api/CardsComments/add'//添加评论

//初始化标签
function ViewCardsTag(arr) {
    var CardsTagData = arr;
    for (let i = 0; i < $(".css-cards-primary-subtitle").length; i++) {
        //jq的坑$()取不到class的对象，取回的是数组，要变对象要套个$();
        var tagList = JSON.parse($($(".css-cards-primary-subtitle")[i])[0].attributes[1].value);
        $($(".css-cards-primary-subtitle")[i]).append('Tag：');
        for (let j = 0; j < tagList.length; j++) {
            for (const key in CardsTagData) {
                if (tagList[j] == CardsTagData[key]['id']) {
                    $($(".css-cards-primary-subtitle")[i]).append('<a>' + CardsTagData[key]['name'] + '</a> ')
                }
            }
        }
    }
}

//点赞
$('.js-Btn-Update-CardsGood').click(function () {
    if ($(this).val() == 'false') {
        return;
    }
    data = {
        'id': $(this).val(),
    };
    //提交数据
    var result = apiAjax0(data, apiUrlCardsGood, 'POST');
    if (result) {
        //成功
        mdui.snackbar({
            message: '点赞成功',
            position: 'left-top'
        });
        $(this).attr('class', 'css-card-actions-good-1 mdui-btn mdui-float-right');
        $(this).val(false);
        $(this).html('<i class="mdui-icon material-icons">favorite</i>点赞' + result.data.Num);
        return;
    }
});