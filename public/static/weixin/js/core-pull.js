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

