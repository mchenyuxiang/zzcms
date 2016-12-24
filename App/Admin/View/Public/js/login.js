/**
 * Created by mchenyuxiang on 2016/12/23.
 */
var login = {
    check:function () {
        //获取登录页面中的用户名和密码
        var username = $('input[name = "username"]').val();
        var password = $('input[name = "password"]').val();

        if(!username){
            dialog.error('用户名不能为空');
        }

        if(!password){
            dialog.error('密码不能为空');
            // parent.layer.alert('内容');
        }

        var url = "/admin.php/Login/login";
        var data = {'username':username,'password':password};
        // 执行异步请求
        $.post(url,data,function (result) {
            if(result.status == 0){
                return dialog.error(result.message);
            }
            if(result.status == 1){
                return dialog.success('成功',result.url);
            }

        },'JSON');
    }
}
