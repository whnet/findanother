$(function(){
	if (Common.cookieGet('user_info')) {
		var url = '//' + apiUrl + '/user/wap/interest/get';
		$.ajax(url, {
			data: {
				'token':Common.cookieGet('user_info')
			},
			dataType: 'json',
			type: 'get',
			success: function(result) {
				if (result.error_code == 0 && result.data != "") {
					var type = ['art','comic','film','food','game','literature',
						'music','outdoor','sport'];
					for (var i = 0; i < type.length; i++) {
						if (type[i] != "") {
							str = result.data[type[i]].split(",");
							for (var j = 0; j < str.length; j++) {
								var obj = "#"+type[i];
								$(obj).children("span").each(function(){
									var _this = $(this);
									if (str[j] == _this.text()) {
										_this.addClass('checked');
									}
			  					});
							};
						}
					};
				}
			},
			error: function(xhr, type, errorThrown) {
				console.log(type);
			}
		});
	}
	

	$('#message').click(function() {
		var uid = $('#infox').attr('uid');
		var num = $('#infox').attr('num');
		window.location.href='/index.php/weixin/pipei/hulue/uid/'+uid+'/num/'+num;
	})
	
	
	$('#detail').click(function(){
		var uid = $('#infox').attr('uid');
		var suid = $('#infox').attr('suid');
		var tjly = $('#infox').attr('tjly');
		
		window.location.href='/index.php/weixin/detail/index/uid/'+uid+'/suid/'+suid+'/tjly/'+tjly;
	})
	
	$('#tongjixuec').click(function() {
		var href = $('#tongjixuec').attr('href');
		window.location.href='/index.php/weixin/pipei/'+href;
	})
	$('#xuexingc').click(function() {
		var href = $('#xuexingc').attr('href');
		window.location.href='/index.php/weixin/pipei/'+href;
	})
	$('#xingquc').click(function() {
		var href = $('#xingquc').attr('href');
		window.location.href='/index.php/weixin/pipei/'+href;
	})

	
	
	$('#dehulue').click(function(){
		var url = '/index.php/weixin/pipei/dehulue';
		
		var uid = $('#deinfox').attr('uid');
		var suid = $('#deinfox').attr('suid');

		$.ajax(url, {
			data: {
				'uid':uid,
				'suid':suid,
			},
			dataType: 'json',
			type: 'post',
			success: function(result) {
				if(result.error_code ==0){
					mui.toast(result.msg);
				}else{
					mui.toast(result.msg);
				}
					
			}
		});
		
	})
	
	
	$('#delike').click(function(){
		var url = '/index.php/weixin/pipei/derenshi';
		
		var uid = $('#deinfox').attr('uid');
		var suid = $('#deinfox').attr('suid');
		var tjly = $('#deinfox').attr('tjly');

		$.ajax(url, {
			data: {
				'uid':uid,
				'suid':suid,
				'tjly':tjly,
			},
			dataType: 'json',
			type: 'post',
			success: function(result) {
				if(result.error_code ==0){
					mui.toast(result.msg);
				}else{
					mui.toast(result.msg);
				}

			}
		});
		
	})
	
	
	$('#demsgrecord').click(function(){
		var url = '/index.php/weixin/pipei/debeixuan';
		
		var uid = $('#deinfox').attr('uid');
		var suid = $('#deinfox').attr('suid');
		var tjly = $('#deinfox').attr('tjly');
		$.ajax(url, {
			data: {
				'uid':uid,
				'suid':suid,
				'tjly':tjly,
			},
			dataType: 'json',
			type: 'post',
			success: function(result) {
				if(result.error_code ==0){
					mui.toast(result.msg);
				}else{
					mui.toast(result.msg);
				}

			}
		});
		
	})
	
	$('#msgrecord').click(function(){
		var url = '/index.php/weixin/pipei/beixuan';
		
		var uid = $('#infox').attr('uid');
		var suid = $('#infox').attr('suid');
		var tjly = $('#infox').attr('tjly');
		var num = $('#infox').attr('num');
		var flag = $('#infox').attr('flag');
		var control = $('#infox').attr('control');
		$.ajax(url, {
			data: {
				'uid':uid,
				'suid':suid,
				'tjly':tjly,
				'num':num,
				'flag':flag,
			},
			dataType: 'json',
			type: 'post',
			success: function(result) {
				if(flag == 2){
					if(result.error_code ==0){
						mui.toast(result.msg);
					}else{
						mui.toast(result.msg);
						var url = result.url;
						(function(){
							var wait = 3;
							var interval = setInterval(function(){
								var time = --wait;
								if(time <= 0) {
									location.href = '/index.php/weixin/'+control+'/'+url+'/num/'+num;
									clearInterval(interval);
								};
							}, 1000);
						})();
					}
				}else{
					if(result.error_code ==0){
						mui.toast(result.msg);
					}else{
						mui.toast(result.msg);
						var url = result.url;
						
						(function(){
							var wait = 3;
							var interval = setInterval(function(){
								var time = --wait;
								if(time <= 0) {
									location.href = '/index.php/weixin/'+url;
									clearInterval(interval);
								};
							}, 1000);
						})();
						

					}
				}

			}
		});
		
	})
	
	$('#like').click(function() {
		//加为好友
		var url = '/index.php/weixin/pipei/renshi';
		// var url = '/index.php/weixin/bus/friend';

		var uid = $('#infox').attr('uid');
		var suid = $('#infox').attr('suid');
		var tjly = $('#infox').attr('tjly');
		var num = $('#infox').attr('num');
		var flag = $('#infox').attr('flag');
		var control = $('#infox').attr('control');
		$.ajax(url, {
			data: {
				'uid':uid,
				'suid':suid,
				'tjly':tjly,
				'num':num,
				'flag':flag,
			},
			dataType: 'json',
			type: 'post',
			success: function(result) {
				if(flag == 2){
					if(result.error_code ==0){
						mui.toast(result.msg);
					}else{
						mui.toast(result.msg);
						var url = result.url;

						(function(){
							var wait = 2;
							var interval = setInterval(function(){
								var time = --wait;
								if(time <= 0) {
									location.href = '/index.php/weixin/'+control+'/'+url+'/num/'+num;
									clearInterval(interval);
								};
							}, 1000);
						})();
					}
				}else{

					if(result.error_code ==0){
						mui.toast(result.msg);
					}else{
						mui.toast(result.msg);
						var url = result.url;
						
						(function(){
							var wait = 3;
							var interval = setInterval(function(){
								var time = --wait;
								if(time <= 0) {
									location.href = '/index.php/weixin/center/'+url;
									clearInterval(interval);
								};
							}, 1000);
						})();
					}
				}

			}
		});
	})
	
	$('#hulue').click(function() {
		var url = '/index.php/weixin/pipei/hulue';
		
		var uid = $('#infox').attr('uid');
		var suid = $('#infox').attr('suid');
		var tjly = $('#infox').attr('tjly');
		var num = $('#infox').attr('num');
		var flag = $('#infox').attr('flag');
		var control = $('#infox').attr('control');
		$.ajax(url, {
			data: {
				'uid':uid,
				'suid':suid,
				'tjly':tjly,
				'num':num,
				'flag':flag,
			},
			dataType: 'json',
			type: 'post',
			success: function(result) {
				if(flag == 2){
					if(result.error_code ==0){
						mui.toast(result.msg);
						
					}else{
						mui.toast(result.msg);
						var url = result.url;
						(function(){
							var wait = 3;
							var interval = setInterval(function(){
								var time = --wait;
								if(time <= 0) {
									location.href = '/index.php/weixin/'+control+'/'+url+'/num/'+num;
									clearInterval(interval);
								};
							}, 1000);
						})();
					}
				}else{
					if(result.error_code ==0){
						mui.toast(result.msg);
					}else{
						mui.toast(result.msg);
						var url = result.url;
						(function(){
							var wait = 3;
							var interval = setInterval(function(){
								var time = --wait;
								if(time <= 0) {
									location.href = '/index.php/weixin/'+url;
									clearInterval(interval);
								};
							}, 1000);
						})();
					}
				}

			}
		});
	})

	
})