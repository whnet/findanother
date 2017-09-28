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
		var Orientation = null; 
		var expectWidth = null;
		var expectHeight = null;
		//文件上传判断
		var chk = checkImgType(this);
		if (!chk) {
			return false;
		}
		
		var data = new oFormData();
		data.append('uid',uid);
		if (this.files.length>0) {

		EXIF.getData(this.files[0], function() {
			EXIF.getAllTags(this);
			Orientation = EXIF.getTag(this, 'Orientation');
			expectWidth = EXIF.getTag(this, 'PixelXDimension');
			expectHeight = EXIF.getTag(this, 'PixelYDimension');
		});

			var oReader = new FileReader();
            oReader.readAsDataURL(this.files[0]);
     oReader.onload = function(e) {
             var imgData = e.target.result;
         conver(imgData);


		}
//将
// 			data.append('imgfile0',this.files[0])
		}else{
			console.log("没有图片")
		}


		});
});

	function conver(imgData){
        convertImgToBase64(imgData, function(base64Img){
        	//将base64转成formdata
            file = base64Toformdata('imgfile0', base64Img);
            // console.log(base64Img);
            //上传数据
            $.ajax({
                url: '/index.php/weixin/info/savepho',
                type: 'POST',
                data: file,
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
            //上传图片END
        });
	}
// 将base64直接转换成标准的fomeData并通过AJAX提交
// 第一步，将base64转换成二进制图片（Blob）
function dataURItoBlob(base64Data) {
    var byteString = atob(base64Data.split(',')[1]);
    var mimeString = base64Data.split(',')[0].split(':')[1].split(';')[0];
    var ab = new ArrayBuffer(byteString.length);
    var ia = new Uint8Array(ab);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ab], {type: mimeString});
}
// 第二步，借助html5的canvas构建formData
function base64Toformdata(name, imageBase64){
    var blob = dataURItoBlob(imageBase64); // 上一步中的函数
    var fd = new FormData();
    fd.append("imgfile0", blob, 'image.png');
    return fd;
}

// 将base64直接转换成标准的fomeData并通过AJAX提交 END

	function rotateImg(img, direction,canvas) { 
        //alert(img);  
        //最小与最大旋转方向，图片旋转4次后回到原方向    
        var min_step = 0;    
        var max_step = 3;    
        //var img = document.getElementById(pid);    
        if (img == null)return;    
        //img的高度和宽度不能在img元素隐藏后获取，否则会出错    
        var height = img.height;    
        var width = img.width;    
        //var step = img.getAttribute('step');    
        var step = 2;    
        if (step == null) {    
            step = min_step;    
        }    
        if (direction == 'right') {    
            step++;    
            //旋转到原位置，即超过最大值    
            step > max_step && (step = min_step);    
        } else {    
            step--;    
            step < min_step && (step = max_step);    
        }    
        //img.setAttribute('step', step);    
        /*var canvas = document.getElementById('pic_' + pid);   
        if (canvas == null) {   
            img.style.display = 'none';   
            canvas = document.createElement('canvas');   
            canvas.setAttribute('id', 'pic_' + pid);   
            img.parentNode.appendChild(canvas);   
        }  */  
        //旋转角度以弧度值为参数    
        var degree = step * 90 * Math.PI / 180;    
        var ctx = canvas.getContext('2d');    
        switch (step) {    
            case 0:    
                canvas.width = width;    
                canvas.height = height;    
                ctx.drawImage(img, 0, 0);    
                break;    
            case 1:    
                canvas.width = height;    
                canvas.height = width;    
                ctx.rotate(degree);    
                ctx.drawImage(img, 0, -height);    
                break;    
            case 2:    
                canvas.width = width;    
                canvas.height = height;    
                ctx.rotate(degree);    
                ctx.drawImage(img, -width, -height);    
                break;    
            case 3:    
                canvas.width = height;    
                canvas.height = width;    
                ctx.rotate(degree);    
                ctx.drawImage(img, -width, 0);    
                break;    
        }    
    }

//处理交互
function upload_callback(obj, data) {
	var imgUrl = 'http://static.7799520.com/';

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
			//只允许上传10张
			if($('.do-upload').parents('ul').find('li').size() > 10){
				$('.do-upload').parent().hide();
			}
			$('.layer').fadeOut();
			break;
		case "certify":
			$('.identification-form .upload div').show().find('img').attr('src', 'http://static.7799520.com/'+data.path);
			$('form input[name="img_url"]').val('http://static.7799520.com/'+data.path);
			break;
		default:
			break;
	}
}
//压缩图片
function convertImgToBase64(url, callback, outputFormat){
    var canvas = document.createElement('CANVAS');
    var ctx = canvas.getContext('2d');
    var img = new Image;
    img.crossOrigin = 'Anonymous';
    img.onload = function(){
        var width = img.width;
        var height = img.height;
        // 按比例压缩
        var rate = 0.1;
        canvas.width = width*rate;
        canvas.height = height*rate;
        ctx.drawImage(img,0,0,width,height,0,0,width*rate,height*rate);
        var dataURL = canvas.toDataURL(outputFormat || 'image/png');
        callback.call(this, dataURL);
        canvas = null;
    };
    img.src = url;
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
		}
	}
	return true;
}
