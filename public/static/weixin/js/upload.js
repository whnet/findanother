var upload_url = '';
var process = '';
$(function() {
    $('.make-upload').append('<input name="imgfile0" multiple="multiple" type="file" accept="image/*" />');
    var uploadUrl = {
        "avatar" : '//' + apiUrl + "/user/wap/avatar/save",
        "regavatar" : '//' + apiUrl + "/user/wap/avatar/save",
        "album"  : '//' + apiUrl + "/user/wap/album/save",
        "dynamic"  : '//' + apiUrl + "/dynamic/wap/dynamic/saveImg",
        "certify" :'//' + apiUrl + "/authenticate/wap/idnumber/saveImg"
    }
    //创建formData
    function oFormData(){
        var isNeedShim = ~navigator.userAgent.indexOf('Android') && ~navigator.vendor.indexOf('Google') && !~navigator.userAgent.indexOf('Chrome') && navigator.userAgent.match(/AppleWebKit\/(\d+)/).pop() <= 534;
        return isNeedShim = isNeedShim ? new FormDataShim() : new FormData();
    }

    //执行上传
    $('.make-upload input').change(function() {
        var object = $('.make-upload').attr('object');
        var uid = $('.make-upload').attr('uid');
        //文件上传判断
        var chk = checkImgType(this);
        if (!chk) {
            return false;
        }
        var data = new oFormData();
        data.append('uid',uid);
        if (this.files.length>0) {
            data.append('imgfile0',this.files[0])
        } else{
            console.log("没有图片")
        }
        $.ajax({
            url: '/index.php/weixin/info/savepho',
            type: 'POST',
            data: data,
            dataType: "json",
            processData: false,
            contentType: false,
            beforeSend:function(){
                if($('#progressbar').length>0){
                    $("#progressbar").show();
                }else{
                    $('body').append('<div id="progressbar" class="mui-progressbar mui-progressbar-infinite"><span></span></div>');
                    $("#progressbar").show();
                }
            },
            success: function(responce) {
                if(responce.error_code ==0){
                    upload_callback(object,responce);
                }else{
                    mui.toast(responce.msg);
                }

                $("#progressbar").hide();
            },

        })
    });
});

//处理交互
function upload_callback(obj, data) {
    var imgUrl = '';

    switch (obj) {
        case "regavatar":
            $('.do-upload label').css({
                'background-image': 'url(' + data.data.path + ')'
            });
            $('form input[name="avatar_url"]').val(+data.data.path);
            break;
        case "dynamic":
            var data = data.data;
            $('.layer-weibo-add .upload').append('<div class="clip-bg" style="background: url(' + data.path + ');" pic="' + data.path + '"><b>&times;</b></div>');
            CountHeight();
            break;
        case "avatar":
            $('#avatar').css({
                'background': 'url(' +data.data.path + ')'
            });
            break;
        case "album":
            var data = data.data;
            $('.do-upload').parents('.mui-grid-view').append('<li class="mui-table-view-cell mui-media mui-col-xs-3 mui-col-sm-3"><a href="#"><div class="clip" photoid="'+ data[0].photoid +'"><img width="80" height="98" src="' + data[0].thumbfiles + '" data-preview-src="'+ data[0].uploadfiles +'" data-preview-group="1" /></div></a></li>');
            //只允许上传5张
            if($('.do-upload').parents('ul').find('li').size() > 5){
                $('.do-upload').parent().hide();
            }
            $('.layer').fadeOut();
            break;
        case "certify":
            $('.identification-form .upload div').show().find('img').attr('src', ''+data.path);
            $('form input[name="img_url"]').val(''+data.path);
            break;
        default:
            break;
    }
}


//图片上传验证
function checkImgType(obj) {
    if (obj.value == "") {
        alert("请上传图片");
        return false;
    } else {
        if (!/\.(gif|jpg|jpeg|png|GIF|JPG|PNG)$/.test(obj.value)) {
            alert("图片类型必须是.gif,jpeg,jpg,png中的一种");
            obj.value = "";
            return false;
        } else {
            //以下无效
            //var img = new Image();
            //img.src = obj.value;
            //while (true) {
            //	if (img.fileSize > 0) {
            //		if (img.fileSize > 0.1 * 1024) {
            //			alert("图片不大于10M。");
            //			return false;
            //		}
            //		break;
            //	}
            //}
        }
    }
    return true;
}
