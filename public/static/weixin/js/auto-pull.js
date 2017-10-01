//兼容UC浏览器
(function($) {
	//链接点击跳转（需要跳转的a标签附加link的class）
	$('body').on('tap', '.link', function(event) {
		var url = this.getAttribute('href');
		var blank = this.getAttribute('target');
		if (blank == '_blank') {
			window.open(url);
		} else {
			window.location.href = url;
		}
	});
})(mui);

// todo 形象照处理,省份城市id处理
var base_url = '/';
var api_url = apiUrl;
var load = 0;
var page = 0; //默认页
var url = ''; //默认Ajax地址
var ajax_data = {}; //默认Ajax传递数据
ajax_data['page'] = page;
var obj = $('.mui-content').attr('pull');
var default_obj = $('.mui-content').attr('pull-obj');
var arr = new Array();
arr['apply'] = new Array();
arr['apply']['url'] = '//' + api_url + '/party/wap/enroll/user';
arr['mylike'] = new Array();
arr['mylike']['url'] = '/index.php/weixin/center/ajaxmylike';
arr['likeme'] = new Array();
arr['likeme']['url'] = '/index.php/weixin/center/ajaxlikeme';
arr['mysaw'] = new Array();
arr['mysaw']['url'] = '/index.php/weixin/center/ajaxmysaw';
arr['sawme'] = new Array();
arr['sawme']['url'] = '/index.php/weixin/center/ajaxsawme';
arr['myfriend'] = new Array();
arr['myfriend']['url'] = '/index.php/weixin/center/ajaxmyfriend';
arr['hulue'] = new Array();
arr['hulue']['url'] = '/index.php/weixin/center/ajaxhulue';
// bus
arr['like_s'] = new Array();
arr['like_s']['url'] = '/index.php/weixin/bus/ajaxlikes';
// 活动列表
arr['bei_s'] = new Array();
arr['bei_s']['url'] = '/index.php/weixin/bus/ajaxbeis';
// 微博列表
arr['weibo-list'] = new Array();
arr['weibo-list']['url'] = '//' + api_url +  '/dynamic/wap/dynamic/list';
// 我的微博列表
arr['my-weibo'] = new Array();
arr['my-weibo']['url'] = '//' + api_url + '/dynamic/wap/dynamic/user';
// 交友列表
arr['recommend'] = new Array();
arr['recommend']['url'] = '//' + api_url + '/recommend/wap/search/list';
// 红娘列表
arr['team'] = new Array();
arr['team']['url'] = '//' + api_url + '/hongniang/wap/hongniang/list';
// 爱情急症室列表
arr['team-detail'] = new Array();
arr['team-detail']['url'] = '//' + api_url + '/emergency/wap/emergency/list';
// 门店详情
arr['store'] = new Array();
arr['store']['url'] = '//' + api_url + '/address/wap/address/list';
// 微博详情
arr['weibo-detail'] = new Array();
arr['weibo-detail']['url'] = '//' + api_url + '/dynamic/wap/comment/get';
// 收到招呼
arr['hibox-recv'] = new Array();
arr['hibox-recv']['url'] = '//' + api_url + '/hibox/wap/hibox/recv';
// 发起招呼
arr['hibox-send'] = new Array();
arr['hibox-send']['url'] = '//' + api_url + '/hibox/wap/hibox/send';
// 消息列表
arr['message-detail'] = new Array();
arr['message-detail']['url'] = '//' + api_url + '/message/wap/message/user';

$(function() {
	//输出样式
	$('.mui-content').append('<div class="auto-pull"><span class="mui-spinner"></span>\u6b63\u5728\u52aa\u529b\u52a0\u8f7d\u4e2d\u2026</div>');

	//判断对象
	url = arr[obj]['url'];
	switch (obj) {
		case "active" :
			var cityid = Common.cookieGet('city_id');
			if (cityid == 395 || cityid == 321 || cityid == 180 || cityid == 322) {
				cityid = 0;
			}
			ajax_data['cityid'] = cityid;
			ajax_data['tag_id'] = $('.article-nav .active').attr('tag');
			break;
		case "recommend" : 
			if (data != 0) {
				ajax_data = data;
			}
			break;
		case "weibo-list" :
			if ($('#userid').attr('userid') > 0) { // 用户个人微博
				ajax_data['userid'] = $('#userid').attr('userid');
				url = '//' + api_url +  '/weibo/wap/weibo/ortheruser';
			}
			if (Common.cookieGet('user_info')) {
				ajax_data['token'] = Common.cookieGet('user_info');
			}
			break;
		case "team" :
			ajax_data['cityid'] = Common.cookieGet('city_id') ? Common.cookieGet('city_id') : 0;
			break;
		case "team-detail" :
			ajax_data['hongniangid'] = $('#hongniang').attr('hongniang');
			break;
		case "weibo-detail" :
			ajax_data['dyid'] = $('.do-submit').attr('dyid');
			break;
		case "bei_s" :
			ajax_data['token'] = '1';
			break;
		case "like_s" :
			ajax_data['token'] = '1';
			break;
		case "mylike" :
			ajax_data['token'] = '1';
			break;
		case "likeme" :
			ajax_data['token'] = '1';
			break;
		case "mysaw" :
			ajax_data['token'] = '1';
			break;
		case "sawme" :
			ajax_data['token'] = '1';
			break;
		case "hulue" :
			ajax_data['token'] = '1';
			break;
		case "myfriend" :
			ajax_data['token'] = '1';
			break;
		case "message-detail" :
			ajax_data['touserid'] = $('.mui-title').attr('userid');
			ajax_data['token'] = Common.cookieGet('user_info');
			break;
		case "my-weibo" : 
			ajax_data['token'] = Common.cookieGet('user_info');
			break;
		case "sys-msg" :
			ajax_data['token'] = Common.cookieGet('user_info');
			break;
	}

	//滚动监听
	$(window).bind('scroll', function() {
		
		//提前一屏触发更新
		var bot = $(window).height();
		if ((bot + $(window).scrollTop()) >= ($(document).height() - $(window).height())) {
			AjaxLoad(obj);
		}
	});

	//读取内容
	AjaxLoad(obj);
});

/**
 * Ajax加载
 */
function LoadData(){
		$.ajax(url, {
			data: ajax_data,
			dataType: 'json',
			async: false,
			type: 'get',
			success: function(data) {
				if (data.error_code == 0) { 
					if (data.data) { //加载页面						
						$('.' + default_obj).append(JsonHtml(obj, data.data));
						page++;
						ajax_data['page'] = page;
					} else { // 没有数据
						if (page == 0) {
							//没有内容
							$('.mui-content').append('<div class="empty"><span></span>\u6682\u65e0\u8bb0\u5f55</div>');
							$(window).unbind('scroll');
						} else {
							//加载结束
							$('.auto-pull').text('\u6ca1\u6709\u66f4\u591a\u5185\u5bb9\u4e86\u2026').show();
							$(window).unbind('scroll');
						}
					}
				} else {

					mui.toast(data.msg);
					href=data.href;
					(function(){
							var wait = 3;
							var interval = setInterval(function(){
								var time = --wait;
								if(time <= 0) {
									location.href = href;
									clearInterval(interval);
								};
							}, 1000);
						})();
					
				}

				$('.auto-pull').hide();
				load = 0;
			},
			error: function(xhr, type, errorThrown) {
				alert(type);
				$('.auto-pull').hide();
				load = 0;
			}
		});
	}
function AjaxLoad() {
	if (load == 0) {
		load = 1;
		$('.auto-pull').show();
		LoadData();
	}
}


/**
 * Json处理
 */
function JsonHtml(obj, data) {
	var HTML = '';
	switch (obj) {
		case "team":
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				HTML += '<li class="mui-table-view-cell mui-media"><a href="/team/'+obj["hongniangid"]+'.html" class="mui-navigate-right link"><div class="mui-pull-left mui-media-object clip-bg" style="background: url('+obj['uploadfiles']+');"></div><div class="mui-media-body">'+obj["name"]+'<span>'+obj['workage']+'年经验</span><label>'+obj['successnum']+'对成功案例</label><p class="mui-ellipsis">擅长：'+obj['domain']+'</p></div><button type="button" class="active"><span class="ico ico-heart"></span>求撮合</button></a></li>';
			};
			break;
		case "store":
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				HTML += '<li class="mui-table-view-cell mui-media"><a href="/address/'+obj.store_id+'.html" class="link"><div class="mui-pull-left mui-media-object clip-bg" style="background: url('+obj.address_img_url+');"></div><div class="mui-media-body"><b>'+obj.name+'门店</b><span>服务热线：'+obj.contact+'</span><p class="mui-ellipsis">门店地址：'+obj.address+'</p></div></a></li>';
			};
			break;
		case "article":
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				HTML += '<li class="mui-table-view-cell mui-media"><a href="/news/' + obj.id + '.html" class="link"><img class="mui-media-object mui-pull-left" src="' + obj.title_img_url + '"><div class="mui-media-body"><p class="mui-ellipsis">' + obj.title + '</p><label>' + Common.getTimeformat(obj.created_time,3) + '</label><label class="view"><span class="mui-icon mui-icon-search"></span>' + obj.click_numbers + '</label></div></a></li>';
			}
			break;
		case "hulue":
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				
				if(obj.Province){
					var pro = obj.Province;
				}else{
					var pro = '';
				}
				
				if(obj.City){
					var city = obj.City;
				}else{
					var city = '';
				}
				
				if(obj.height){
					var height = obj.height;
				}else{
					var height = '';
				}
				
				if(obj.Sign){
					var sign = obj.Sign;
				}else{
					var sign = '';
				}
				
				if(obj.sex){
					if(obj.sex==1){
						var sex = "男";
					}else{
						var sex = "女";
					}
				}else{
					var sex = '';
				}
				
				if(obj.blood && obj.start){
					var bstart = obj.start+obj.blood;
				}else{
					var bstart = '';
				}				
				
				
				HTML += '<li class="mui-table-view-cell mui-media" objid="' + data[i].id + '"><div class="mui-slider-right mui-disabled"><a class="mui-btn mui-btn-red">删除记录</a></div><div class="mui-slider-handle"><a href="/index.php/weixin/detail/index/uid/' + data[i].nuid + '/jh/1.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.headimgurl + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.nickname + '</b><span>' + (y.getFullYear() - obj.birthdayyear) + '岁 ' + sex +' '+ pro + ' ' + city + ' '+ bstart +' '+ height + '</span></p><p class="mui-ellipsis">' + sign + '</p><label>' + Common.getTimeformat(data[i].addtime,3) + '</label></div></a></div></li>';
			}
			break;
		case "sawme":
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				
				if(obj.Province){
					var pro = obj.Province;
				}else{
					var pro = '';
				}
				
				if(obj.City){
					var city = obj.City;
				}else{
					var city = '';
				}
				
				if(obj.height){
					var height = obj.height;
				}else{
					var height = '';
				}
				
				if(obj.Sign){
					var sign = obj.Sign;
				}else{
					var sign = '';
				}
				
				if(obj.sex){
					if(obj.sex==1){
						var sex = "男";
					}else{
						var sex = "女";
					}
				}else{
					var sex = '';
				}
				
				if(obj.blood && obj.start){
					var bstart = obj.start+obj.blood;
				}else{
					var bstart = '';
				}	
				
				HTML += '<li class="mui-table-view-cell mui-media" objid="' + data[i].id + '"><div class="mui-slider-handle"><a href="/index.php/weixin/detail/index/uid/' + data[i].nuid + '/jh/1.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.headimgurl + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.nickname + '</b><span>' + (y.getFullYear() - obj.birthdayyear) + '岁 ' + sex +' '+ pro + ' ' + city + ' '+ bstart +' '+ height +'</span></p><p class="mui-ellipsis">' + sign + '</p><label>' + Common.getTimeformat(data[i].addtime,3) + '</label></div></a></div></li>';
			}
			break;
		case "likeme":
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				if(obj.Province){
					var pro = obj.Province;
				}else{
					var pro = '';
				}
				
				if(obj.City){
					var city = obj.City;
				}else{
					var city = '';
				}
				
				if(obj.height){
					var height = obj.height;
				}else{
					var height = '';
				}
				
				if(obj.Sign){
					var sign = obj.Sign;
				}else{
					var sign = '';
				}
				
				
				if(obj.sex){
					if(obj.sex==1){
						var sex = "男";
					}else{
						var sex = "女";
					}
				}else{
					var sex = '';
				}
				
				if(obj.blood && obj.start){
					var bstart = obj.start+obj.blood;
				}else{
					var bstart = '';
				}	
				
				HTML += '<li class="mui-table-view-cell mui-media" objid="' + data[i].id + '"><div class="mui-slider-handle"><a href="/index.php/weixin/detail/index/uid/' + data[i].nuid + '/jh/1.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.headimgurl + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.nickname + '</b><span>' + (y.getFullYear() - obj.birthdayyear) + '岁 ' + sex +' '+ pro + ' ' + city + ' '+ bstart +' '+ height +'</span></p><p class="mui-ellipsis">' + sign + '</p><label>' + Common.getTimeformat(data[i].addtime,3) + '</label></div></a></div></li>';
			}
			break;
		case "mysaw":
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				
				if(obj.Province){
					var pro = obj.Province;
				}else{
					var pro = '';
				}
				
				if(obj.City){
					var city = obj.City;
				}else{
					var city = '';
				}
				
				if(obj.height){
					var height = obj.height;
				}else{
					var height = '';
				}
				
				if(obj.Sign){
					var sign = obj.Sign;
				}else{
					var sign = '';
				}
				if(obj.sex){
					if(obj.sex==1){
						var sex = "男";
					}else{
						var sex = "女";
					}
				}else{
					var sex = '';
				}
				
				if(obj.blood && obj.start){
					var bstart = obj.start+obj.blood;
				}else{
					var bstart = '';
				}	
				HTML += '<li class="mui-table-view-cell mui-media" objid="' + data[i].id + '"><div class="mui-slider-right mui-disabled"><a class="mui-btn mui-btn-red">删除记录</a></div><div class="mui-slider-handle"><a href="/index.php/weixin/detail/index/uid/' + data[i].nuid + '/jh/1.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.headimgurl + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.nickname + '</b><span>' + (y.getFullYear() - obj.birthdayyear) + '岁 ' + sex +' '+ pro + ' ' + city + ' '+ bstart +' '+ height +'</span></p><p class="mui-ellipsis">' + sign + '</p><label>' + Common.getTimeformat(data[i].addtime,3) + '</label></div></a></div></li>';
			}
			break;
		case "mylike":
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				
				if(obj.Province){
					var pro = obj.Province;
				}else{
					var pro = '';
				}
				
				if(obj.City){
					var city = obj.City;
				}else{
					var city = '';
				}
				
				if(obj.height){
					var height = obj.height;
				}else{
					var height = '';
				}
				
				if(obj.Sign){
					var sign = obj.Sign;
				}else{
					var sign = '';
				}
				if(obj.sex){
					if(obj.sex==1){
						var sex = "男";
					}else{
						var sex = "女";
					}
				}else{
					var sex = '';
				}
				
				if(obj.blood && obj.start){
					var bstart = obj.start+obj.blood;
				}else{
					var bstart = '';
				}	
				
				HTML += '<li class="mui-table-view-cell mui-media" objid="' + data[i].id + '"><div class="mui-slider-right mui-disabled"><a class="mui-btn mui-btn-red">删除记录</a></div><div class="mui-slider-handle"><a href="/index.php/weixin/detail/index/uid/' + data[i].nuid + '/jh/1.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.headimgurl + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.nickname + '</b><span>' + (y.getFullYear() - obj.birthdayyear) + '岁 ' + sex +' '+ pro + ' ' + city + ' '+ bstart +' '+ height +'</span></p><p class="mui-ellipsis">' + sign + '</p><label>' + Common.getTimeformat(data[i].addtime,3) + '</label></div></a></div></li>';
			}
			break;
		case "myfriend":
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				
				if(obj.Province){
					var pro = obj.Province;
				}else{
					var pro = '';
				}
				
				if(obj.City){
					var city = obj.City;
				}else{
					var city = '';
				}
				
				if(obj.height){
					var height = obj.height;
				}else{
					var height = '';
				}
				
				if(obj.Sign){
					var sign = obj.Sign;
				}else{
					var sign = '';
				}
				if(obj.sex){
					if(obj.sex==1){
						var sex = "男";
					}else{
						var sex = "女";
					}
				}else{
					var sex = '';
				}
				
				if(obj.blood && obj.start){
					var bstart = obj.start+obj.blood;
				}else{
					var bstart = '';
				}	
				
				HTML += '<li class="mui-table-view-cell mui-media" objid="' + data[i].id + '"><div class="mui-slider-right mui-disabled"><a class="mui-btn mui-btn-red">删除记录</a></div><div class="mui-slider-handle"><a href="/index.php/weixin/detail/index/uid/' + data[i].nuid + '/jh/1.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.headimgurl + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.nickname + '</b><span>' + (y.getFullYear() - obj.birthdayyear) + '岁 ' + sex +' '+ pro + ' ' + city + ' '+ bstart +' '+ height +'</span></p><p class="mui-ellipsis">' + sign + '</p><label>' + Common.getTimeformat(data[i].addtime,3) + '</label></div></a></div></li>';
			}
			break; 
		case "recommend":
			if (data.num > 0) {
				var address;
				for (var i = 0; i < data.list.length; i++) {
					var obj = data.list[i];
					if (obj["province"] != '北京' && obj["province"] != '天津' && obj["province"] != '重庆' && obj["province"] != '上海') {
						address = obj["province"] + obj['city'];
					} else {
						address = obj["province"]
					}
					HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-pull-left mui-media-object clip-bg" style="background: url(' + obj["avatar"] + ');"><a href="/user/' + obj["userid"] + '.html" class="link"></a></div><div class="mui-media-body"><a href="/user/' + obj["userid"] + '.html" class="link"><b>' + obj["username"] + '</b><span>' + Common.getBirthdayyear(obj["birthdayyear"]) + '岁，' + address + '，' + obj["height"] + 'cm</span><p class="mui-ellipsis">内心独白：' + obj["monolog"] + '</p></a></div><div><a href="javascript:;" class="do-message-hi" objid="' + obj["userid"] + '"><span class="ico ico-msg-hi"></span>打招呼</a><a href="javascript:;" objid="' + obj["userid"] + '" class="do-message-add"><span class="ico ico-msg-call"></span>发信息</a></div></li>';
				}
			} else {
				// 数据为空
				$('.mui-content').append('<div class="empty"><span></span>\u6682\u65e0\u8bb0\u5f55</div>');
			}
			break;
		case "diary-detail":
			HTML = '<li class="mui-table-view-cell mui-media"><div class="mui-slider-handle"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(tmp/member-face1.png);"><a href="个人资料.html" class="link"></a></div><div class="mui-media-body"><p class="title"><a href="个人资料.html" class="link"><b>阳光夏天</b></a><span>05-20 18:00</span></p><p>很好很好  支持！</p></div></div></li>';
			break;
		case "weibo-list":
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				if (obj.userInfo != null) {
					HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-pull-left mui-media-object clip-bg" style="background: url(' + obj.userInfo.avatar + ');"><a href="/user/' + obj["userid"] + '.html" class="link"></a></div><div class="mui-media-body"><a href="/user/' + obj["userid"] + '.html" class="link"><b><font>' + obj.userInfo.username + '</font><span class="mui-pull-right">' + Common.getTimeformat(obj["addtime"],3) + '</span></b><span>' + Common.getTimeformat(obj["addtime"],'date') + ' . ' + obj.userInfo.province + ' ' + obj.userInfo.city + '</span></a></div><div><a href="/weibo/' + obj["dyid"] + '.html" class="link"><p>' + obj["content"] + '&nbsp;</p></a></div>';
					if (obj.img.length > 0) {
						HTML += '<a href="/weibo/' + obj["dyid"] + '.html" class="link"><ul class="mui-table-view mui-grid-view">';
						for (var j = 0; j < obj["img"].length; j++) {
							HTML += '<li class="mui-table-view-cell mui-media mui-col-xs-4 mui-col-sm-4"><div class="clip-bg" style="background: url('+obj["img"][j] +');"></div><p></p></li>';
						}
						HTML += '</ul></a>';
					}
					HTML += '<div><a href="javascript:;" class="do-collect';
					if (data[i]["isDing"]) {
						HTML += ' active';
					}
					HTML += '" objid="' + obj["dyid"] + '"><span class="ico ico-msg-heart"></span><label>' + data[i]["dingcount"] + '</label></a><a href="javascript:;" class="do-message-add" objid="' + obj["userid"] + '"><span class="ico ico-msg-call"></span>发信息</a></div></li>';
				}
			}
			break;
		case "like_s":
			var val = data.renshi;
			
			if(val['Sign']){
				var sign = val['Sign'];
			}else{
				var sign = '';
			}
			
			HTML +='<header class="mui-bar mui-bar-nav nav-transparent" style="margin-top:10px;"><a href="/index.php/weixin/center/index" class="mui-bar-nav-home link" style="left: 1em;"></a><h1 class="mui-title">'+val['name']+'</h1><a class="link" style="right:0.8em;font-size:14px!important;position:absolute;line-height:40px;padding-right:0.5em!important;color:#fff;" style="color:#ccc;" href="/tousu/btuid/'+val['nuid']+'/tuid/'+data.uid+'"></a></header><div class="self-intro" onclick=detail('+val['nuid']+','+data.uid+',"'+data['renshival']['tjly']+'");><div class="self-intro-main"><div class="self-avatar" style="background-image:url('+val['header']+');"><div class="audit-hint"></div></div><div class="data"><span class="sex" status="'+val['Sex']+'"></span><span>'+data.renshiage+'</span><span>'+data.renshistart+'</span></div><div class="mood">'+sign+'</div></div><img class="self-intro-bg" src="/static/weixin/images/self_detail_top.jpg" /></div>';
			
			HTML +='<section class="cm-list-style"><div class="group"><div class="label">基础信息</div><div class="values" style="padding-left: 100px;"><div class="values-main" style="padding-left:0px;">';
			
			if(val['height']){
				HTML+='<span class="value-item">'+val['height']+'</span>';
			}
			if(val['weight']){
				HTML+='<span class="value-item">'+val['weight']+'</span>';
			}
			if(val['Gqzt']){
				HTML+='<span class="value-item">'+val['Gqzt']+'</span>';
			}
			
			HTML+='<span class="value-item">现居:'+val['xianju']+'</span>';
			
			if(val['salary']){
				HTML+='<span class="value-item">'+val['salary']+'万</span>';
			}
			
			if(val['education']){
				HTML+='<span class="value-item">'+val['education']+'</span>';
			}
			
			if(val['Blood']){
				HTML+='<span class="value-item">血型:'+val['Blood']+'型</span>';
			}
			
			if(val['character']){
				HTML+='<span class="value-item">'+val['character']+'</span>';
			}
			
			HTML+='</div></div></div><div class="group"><div class="label">推荐理由</div><div class="values" style="padding-left: 97px;padding-bottom:30px;"><div class="values-main" style="padding-right:0px;font-size:15px;">'+data['renshival']['tjly']+'</div><div></div></section><div id="infox" likeid="'+data.likeid+'" num="'+data.snum+'" renshiuid="'+val['nuid']+'"></div>';
			
			HTML +='<nav class="footbar mui-clearfix">';
			
			HTML += '<a href="javascript:;" class="footbar-item" onclick=send('+data.uid+','+data.likeid+','+val['nuid']+')><div class="footbar-icon beixuan-icon" status="false"></div><span class="mui-tab-label">加为好友</span></a>';

			// HTML +='<a href="/index.php/weixin/bus/xhhelp" class="footbar-item link"><div class="footbar-icon renshi-icon" status="false"></div><span class="mui-tab-label">小红帮忙</span></a></nav>';
			break;
		
		case "bei_s":
			var val = data.beixuan;
			
			if(val['Sign']){
				var sign = val['Sign'];
			}else{
				var sign = '';
			}
			
			HTML +='<header class="mui-bar mui-bar-nav nav-transparent" style="margin-top:10px;"><a href="/index.php/weixin/center/index" class="mui-bar-nav-home link" style="left: 1em;"></a><h1 class="mui-title">'+val['name']+'</h1><a class="link" style="right:0.8em;font-size:14px!important;position:absolute;line-height:40px;padding-right:0.5em!important;color:#fff;" style="color:#ccc;" href="/tousu/btuid/'+val['nuid']+'/tuid/'+data.uid+'"></a></header><div class="self-intro" onclick=detail('+val['nuid']+','+data.uid+',"'+data['beixuanval']['tjly']+'");><div class="self-intro-main"><div class="self-avatar" style="background-image:url('+val['header']+');"><div class="audit-hint"></div></div><div class="data"><span class="sex" status="'+val['Sex']+'"></span><span>'+data.beixuanage+'</span><span>'+data.beixuanstart+'</span></div><div class="mood">'+sign+'</div></div><img class="self-intro-bg" src="/static/weixin/images/self_detail_top.jpg" /></div>';
			
			HTML +='<section class="cm-list-style"><div class="group"><div class="label">基础信息</div><div class="values" style="padding-left: 100px;"><div class="values-main" style="padding-left:0px;">';
			
			if(val['height']){
				HTML+='<span class="value-item">'+val['height']+'</span>';
			}
			if(val['weight']){
				HTML+='<span class="value-item">'+val['weight']+'</span>';
			}
			if(val['Gqzt']){
				HTML+='<span class="value-item">'+val['Gqzt']+'</span>';
			}
			
			HTML+='<span class="value-item">现居:'+val['xianju']+'</span>';
			
			if(val['salary']){
				HTML+='<span class="value-item">'+val['salary']+'万</span>';
			}
			
			if(val['education']){
				HTML+='<span class="value-item">'+val['education']+'</span>';
			}
			
			if(val['Blood']){
				HTML+='<span class="value-item">血型:'+val['Blood']+'型</span>';
			}
			
			if(val['character']){
				HTML+='<span class="value-item">'+val['character']+'</span>';
			}
			
			HTML+='</div></div></div><div class="group"><div class="label">推荐理由</div><div class="values" style="padding-left: 97px;padding-bottom:30px;"><div class="values-main" style="padding-right:0px;font-size:15px;">'+data['beixuanval']['tjly']+'</div><div></div></section><div id="infox"  num="'+data.num+'" renshiuid="'+val['nuid']+'"></div>';HTML +='<nav class="footbar mui-clearfix">';
			
			HTML +='<a onclick=hulie('+data.num+','+data.uid+','+val['nuid']+','+data['beixuanval']['id']+'); class="footbar-item"><div class="footbar-icon hulue-icon" status="false"></div><span class="mui-tab-label">忽略</span></a>';
			HTML +='<a class="footbar-item" onclick=like('+val['nuid']+','+data.uid+',"'+data['beixuanval']['tjly']+'",'+data['beixuanval']['id']+');><div class="footbar-icon renshi-icon" status="false"></div><span class="mui-tab-label">认识一下</span></a></nav>';
			break;
		case "my-diary":
			HTML = '<li class="mui-table-view-cell mui-media"><div class="mui-pull-left mui-media-object clip-bg" style="background: url(tmp/member-face1.png);"><a href="个人资料.html" class="link"></a></div><div class="mui-media-body"><a href="个人资料.html" class="link"><b>骑着乌龟看海<span class="mui-pull-right">55741人浏览</span></b><span>05-01 . 广东 广州</span></a></div><div><a href="日记详情.html" class="link"><p class="title"><span class="ico ico-edit"></span>大家好，我是潘盼盼</p><p>五月了。 好好工作，积极锻炼，姻缘事不要抱过多期望， 但也不要象祥林嫂似地抱怨看脸社会、女人价值由年龄 来确定等，虽然很多确实是事实。</p></a></div><div><a href="#"><span class="ico ico-msg-heart"></span><label>567</label></a><a href="#" class="do-del"><span class="ico ico-msg-recycle"></span>删除</a></div></li>';
			break;
		case "score":
			HTML = '<li class="mui-table-view-cell mui-media"><div class="mui-media-body"><b>每日签到奖励</b>2016-06-02 17:45:38</div><button class="mui-btn">+100<span class="ico ico-uc-pig"></span></button></li>';
			break;
		case "message":
			HTML = '<li class="mui-table-view-cell mui-media"><div class="mui-slider-right mui-disabled"><a class="mui-btn mui-btn-red">删除</a></div><div class="mui-slider-handle"><a href="回信息.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(tmp/member-face3.png);"></div><div class="mui-media-body"><p class="title"><b>Boomdaisy</b></p><p class="mui-ellipsis">喜欢了你</p><label>05-20 11:22</label></div></a></div></li>';
			break;
		case "message-detail":
			var toavatar = $('.mui-title').attr('toavatar');
			var fromavatar = $('body').attr('avatar');
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				if (obj['fromuserid'] == $('.mui-title').attr('userid')) {
					HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-slider-handle"><p class="title">'+Common.getTimeformat(obj['sendtime'],3)+'</p><span></span><div class="clip-bg mui-media-object" style="background: url('+toavatar+');"><a href="/user/'+obj['fromuserid']+'.html" class="link"></a></div><div class="mui-media-body"><p>'+emotion_exchange(obj['content'])+'</p></div></div></li>';
				} else {
					HTML += '<li class="mui-table-view-cell mui-media active"><div class="mui-slider-handle"><p class="title">'+Common.getTimeformat(obj['sendtime'],3)+'</p><span></span><div class="clip-bg mui-media-object" style="background: url('+fromavatar+');"><a href="/user/'+obj['fromuserid']+'.html" class="link"></a></div><div class="mui-media-body"><p>'+emotion_exchange(obj['content'])+'</p></div></div></li>';
				}
			}		
			break;
		case "msg":
				// HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-slider-handle"><a href="/self/notice" class="link"><div class="clip-bg mui-pull-left mui-media-object system"></div><div class="mui-media-body"><font>' + data.noticeCount + '</font><p class="title"><b>系统消息</b></p><p class="mui-ellipsis">您有' + data.noticeCount + '条未读信息</p><label></label></div></a></div></li>';
			var userid = $('body').attr('userid');
			if (data.length > 0) {
				for (var i = 0; i < data.length; i++) {
					var obj = data[i];
					if (obj.fromuserid == userid) {
						var id = obj.touserid;
					} else {
						var id = obj.fromuserid;
					}
					if (obj.readflag == 0) {
						var status = 1;
					} else {
						var status = 0;
					}
					//href="/self/reply/' + id + '.html?status='+status+'"
					HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-slider-handle"><a class="v-link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.userInfo.avatar + ');"></div><div class="mui-media-body">';
					if (obj.readflag == 0 && obj.touserid == userid) { // 接收用户为自己
						HTML += '<font>' + 1 + '</font>';
					}
					HTML += '<p class="title"><b>' + obj.userInfo.username + '</b>';
					if (obj.userInfo.gender == 1) {
						HTML += '<em class="active"><span class="ico ico-male">';
					} else {
						HTML += '<em><span class="ico ico-female">';
					}
					HTML += '</span>' + (Common.getBirthdayyear(obj.userInfo.birthdayyear)) + '</em></p><p class="mui-ellipsis">'+obj['content']+'</p><label>' + Common.getTimeformat(obj["sendtime"],3) + '</label></div></a></div></li>';
				}
			}
			break;
		case "sys-msg":
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-slider-right mui-disabled"><a class="mui-btn mui-btn-red" objid="' + obj["noticeid"] + '">删除</a></div><div class="mui-slider-handle"><a href="/self/reply/' + obj["senduserid"] + '.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.userInfo.avatar + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.userInfo.username + '</b>';
				if (obj.userInfo.gender == 1) {
					HTML += '<em class="active"><span class="ico ico-male">';
				} else {
					HTML += '<em><span class="ico ico-female">';
				}
				HTML += '</span>'+Common.getBirthdayyear(obj.userInfo.birthdayyear)+'</em></p><p class="mui-ellipsis">' + obj["content"] + '</p><label>' + Common.getTimeformat(obj["addtime"],3) + '</label></div></a></div></li>';
			}
			break;
		case "apply":
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-slider-handle"><div class="clip-bg mui-pull-right mui-media-object"><a href="/party/' + obj["partyid"] + '.html" class="link">申请成功&gt;</a></div><a href="/party/' + obj["partyid"] + '.html" class="link"><div class="mui-media-body"><p class="title mui-ellipsis">' + obj["title"] + '</p><p>' + Common.getTimeformat(obj["created_time"],3) + '</p></div></a></div></li>';
			}
			break;
		case "team-detail":
			var name = $('#hongniang').attr('name');
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				if (obj.reply_contents != "") {
					var str = '<p class="reply"><b>'+name+'：</b>'+obj.reply_contents+'</p>';
				} else {
					var str = "";
				}
				HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-slider-handle"><div class="clip-bg mui-pull-left mui-media-object" style="background: url('+obj.userInfo.avatar+');"><a href="/user/'+obj.userid+'.html" class="link"></a></div><div class="mui-media-body"><p class="title"><a href="/user/'+obj.userid+'.html" class="link"><b>'+obj.userInfo.username+'</b></a><span>'+Common.getTimeformat(obj.addtime,3)+'</span></p><p>'+obj.content+'</p>'+str+'</div></div></li>';
			}
			break;
		case "weibo-detail" :
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				HTML += '<li class="mui-table-view-cell mui-media"><div class="mui-slider-handle"><div class="clip-bg mui-pull-left mui-media-object" style="background: url('+obj.avatar+');"><a href="/user/'+obj.userid+'.html" class="link"></a></div><div class="mui-media-body"><p class="title"><a href="/user/'+obj['cmuserid']+'.html" class="link"><b>'+obj.username+'</b></a><span>'+obj.cmtime+'</span></p><p>'+obj.content+'</p></div></div></li>';
			};
			break;
		case "hibox-send" :
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				if (obj.greeting == "" ) {
					obj.greeting = "向TA打了招呼";
				}
				HTML += '<li class="mui-table-view-cell mui-media" hiid="'+obj['hiid']+'" objid="' + obj['touserid'] + '"><div class="mui-slider-right mui-disabled"><a class="mui-btn mui-btn-red">取消招呼</a></div><div class="mui-slider-handle"><a href="/user/' + obj['touserid'] + '.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.userInfo.avatar + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.userInfo.username + '</b><span>' + (y.getFullYear() - obj.userInfo.birthdayyear) + '岁 . ' + obj.userInfo.province + ' ' +  obj.userInfo.city + ' . ' + obj.userInfo.height + 'CM</span></p><p class="mui-ellipsis">' + obj.greeting + '</p><label>' + Common.getTimeformat(data[i].sendtime,3) + '</label></div></a></div></li>';
			}
			break;
		case "hibox-recv" :
			var y = new Date();
			for (var i = 0; i < data.length; i++) {
				var obj = data[i];
				if (data[i].greeting == "") {
					data[i].greeting = "向你打了招呼";
				}
				HTML += '<li class="mui-table-view-cell mui-media" hiid="'+obj['hiid']+'" objid="' + obj['userid'] + '"><div class="mui-slider-right mui-disabled"></div><div class="mui-slider-handle"><a href="/user/' + obj.userInfo.userid + '.html" class="link"><div class="clip-bg mui-pull-left mui-media-object" style="background: url(' + obj.userInfo.avatar + ');"></div><div class="mui-media-body"><p class="title"><b>' + obj.userInfo.username + '</b><span>' + (y.getFullYear() - obj.userInfo.birthdayyear) + '岁 . ' + obj.userInfo.province + ' ' +  obj.userInfo.city + ' . ' + obj.userInfo.height + 'CM</span></p><p class="mui-ellipsis">' + data[i].greeting + '</p><label>' + Common.getTimeformat(data[i].sendtime,3) + '</label></div></a></div></li>';
			}
			break;
		default:
			break;
	}

	return HTML;
}