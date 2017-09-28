var getCookie = function getCookie(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg))
            return unescape(arr[2]);
        else
            return null;
    };
var setCookie = function(name, val, day) {
        var exp = new Date();
        exp.setTime(exp.getTime() + day * 86400000);    
        document.cookie = name + "="+ val + ";expires=" + exp.toGMTString()+";path=/"; 
    };
var clearCookie = function(name, cval) {
         setCookie(name, "", -1);
    };


$(function() {
    //定义滚动控件高度
    $("#pullrefresh").css("margin-top", $(".mui-bar").eq(0).outerHeight());
    //app下载推广-删除
    mui("body").on("tap",".body-bottom .hide",function(){
        $('.body-bottom').animate({
            left: '100%'
        }, function() {
            $('.body-bottom').remove();
        });
        return false;
    })
    //app下载推广-跳转
    mui("body").on("tap",".body-bottom",function() {
         window.location.href = '#'
    })

        
})