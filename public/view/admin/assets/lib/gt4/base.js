const geetest4 = (id, Fun, CaptchaId) => {
    //验证
    var button = $(id)[0];
    initGeetest4({
        captchaId: CaptchaId,
        product: 'bind'
    }, function (captcha) {
        // captcha为验证码实例
        captcha.onReady(function () {
            //验证码ready之后才能调用verify方法显示验证码
        }).onSuccess(function () {
            var result = captcha.getValidate();
            if (!result) {
                return alert('请完成验证');
            }
            result.captcha_id = CaptchaId;
            //实例化函数并传入验证参数
            Fun(result);
        }).onError(function () {
            //your code
        })
        // 按钮提交事件
        button.onclick = function () {
            // some code
            // 检测验证码是否ready, 验证码的onReady是否执行
            captcha.showBox(); //显示验证码
            // some code
        }
    });
}