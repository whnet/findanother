var imgUrl = 'http://static.7799520.com';
var apiUrl = window.location.host + '/api';
Common = {
    // 设置cookie
    cookieSet: function(name, val, day) {
        var exp = new Date();
        exp.setTime(exp.getTime() + day * 86400000);
        document.cookie = name + "=" + val + ";expires=" + exp.toGMTString() + ";path=/";
    },
    // 获取cookie
    cookieGet: function(name) {
        var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
        if (arr = document.cookie.match(reg)) {
            return unescape(arr[2]);
        } else {
            return null;
        }
    },
    // 清除cookie
    cookieClear: function(name, cval) {
        Common.cookieSet(name, "", -1);
    },
    // 获取参数
    getParam: function(name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) {
            return unescape(r[2]);
        } else {
            return false;
        }
    },
    // 判断是否登录
    checkLogin: function() {
        if (!Common.cookieGet('user_info')) {
            mui.toast('请先登录');
            var href = window.location.href;
            var login = '/login?from=' + href;
            setTimeout("window.location.href='" + login + "';", 1000);
            return false;
        } else {
            return true;
        }
    },

    //传入城市val,返回text
    getCity: function(cityVal) {
        for (var i = 0; i < cityData.length; i++) {
            for (var j = 0; j < cityData[i].children.length; j++) {
                if (cityData[i].children[j].value == cityVal) {
                    return cityData[i].children[j].text;
                }
            }

        }
    },

    //传入省份val,返回text
    getProvince: function(provinceVal) {
        for (var i = 0; i < cityData.length; i++) {
            if (cityData[i].value == provinceVal) {
                return cityData[i].text;
            }
        }
    },

    //给省份,城市value，返回：省份 - 城市
    getCityPair: function(provinceVal, cityVal) {
        var text = '';
        for (var i = 0; i < cityData.length; i++) {
            if (cityData[i].value == provinceVal) {
                text += cityData[i].text;
                for (var j = 0; j < cityData[i].children.length; j++) {
                    if (cityData[i].children[j].value == cityVal) {
                        text += ' - ' + cityData[i].children[j].text;
                        return text
                    }
                }

            }
        }
    },

    //年龄返回
    getBirthdayyear: function(birthyear) {
        return new Date().getFullYear() - parseInt(birthyear);
    },

    //时间戳进入,返回多种格式
    getTimeformat: function(timeStamp, rType) {
        var timeStamp = timeStamp * 1000;
        var date = new Date(timeStamp),
            text = '';
        var year = date.getFullYear(),
            month = date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1,
            day = date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate(),
            hour = date.getHours() < 10 ? '0' + (date.getHours()) : date.getHours(),
            minute = date.getMinutes() < 10 ? '0' + (date.getMinutes()) : date.getMinutes(),
            second = date.getSeconds() < 10 ? '0' + (date.getSeconds()) : date.getSeconds();

        //参数位1     yyyy-mm-dd hh:mm
        //参数为2     yyyy年mm月dd日
        //参数为3     mm-dd hh:mm
        //参数为date  mm-dd
        //参数为time  hh:mm
        if (!rType || rType == 1) {
            text = year + '-' + month + '-' + day + ' ' + hour + ':' + minute;

        } else if (rType == 'time') {
            text = hour + ':' + minute;

        } else if (rType == 'date') {
            text = month + '-' + day;

        } else if (rType == 2) {
            text = year + '年' + month + '月' + day + '日';

        } else if (rType == 3) {
            text = month + '-' + day + ' ' + hour + ':' + minute;

        }

        return text
    },

       //app下载提示弹窗
    downloadApp: function(callback) {

            callback ? callback() : null;

    }

}
