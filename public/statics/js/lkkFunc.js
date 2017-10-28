/**
 * Created by kakuilan@163.com/lianq.net on 2017/6/3.
 * Desc: 自定义函数库
 */


/**
 * 变量是否字符串
 * @param v 变量
 */
function isString(v) {
    return typeof v === 'string' || v instanceof String;
}


/**
 * 变量是否数组
 * @param v 变量
 */
function isArray(v) {
    return Object.prototype.toString.call( v ) === '[object Array]';
}


/**
 * 变量是否是一个纯粹的对象
 * 如:
 * isPlainObject([]);//false
 * isPlainObject({});//true
 * @param o 变量
 */
function isObject (o) {
    return o && toString.call(o) === '[object Object]' && 'isPrototypeOf' in o;
}


/**
 * 是否函数
 * @param fn 变量
 */
function isFunction(fn){
    return Object.prototype.toString.call(fn)==='[object Function]';
}



/**
 * 是否数值
 * @param n 变量
 */
function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}


/**
 * 是否整数
 * @param n 变量
 */
function isInteger (n) {
    return Number(n) === n && n % 1 === 0;
}


/**
 * 是否浮点
 * @param n 变量
 */
function isFloat(n){
    return Number(n) === n && n % 1 !== 0;
}


/**
 * 是否邮箱
 * @param email 变量
 */
function isEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}


/**
 * 是否手机号
 * @param s 变量
 */
function isMobile(s) {
    if(typeof(s)=='number') s = String(s);
    return /^1[3,4,5,6,7,8,9]\d{9}$/.test(s);
}


/**
 * 是否中文
 * @param s 变量
 */
function isChinese(s) {
    return /^[\u4e00-\u9fa5]+$/.test(s);
}


/**
 * 是否英文
 * @param s 变量
 */
function isEnglish(s) {
    return /^[A-Za-z]+$/.test(s);
}


/**
 * 是否英文和数字
 * @param s 变量
 */
function isEngNum(s) {
    return /^([a-zA-Z0-9]+)$/.test(s);
}


/**
 * 是否单词(英文、数字和_)
 * @param s 变量
 */
function isWord(s) {
    return /^\w+$/.test(s);
}


//时间戳转为格式化日期
time2Date = function(){
    var dayNames = ["周日","周一","周二","周三","周四","周五","周六"];
    var monthNames = ["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"];

    function zp(a,b){return(1e9+a+'').slice(-b)} // pads the number a until it is b digits long
    function or(a){return["th","st","nd","rd"][(a=~~(a<0?-a:a)%100)>10&&a<14||(a%=10)>3?0:a]} // returns ordinal suffix for number a
    function fm(y){var d=new Date(y,0,1);while(d.getDay()-1)d.setDate(d.getDate()+1);return+d} // returns timestamp of first monday in year y
    function mn(d){return 864e5*~~(d/864e5)} // Timestamp of midnight

    return function(format, timestamp){
        if(format==undefined || format=='') format = 'Y-n-j';
        format = format.replace(/r/g,'D, d M Y H:i:s O').replace(/c/g,'Y-m-d\\TH:i:sP');

        if(timestamp!=undefined && isInteger(timestamp) && timestamp>0) {
            dt = new Date(timestamp*1000);
            if(format=='smart') { //聪明地显示
                var curDate = new Date,
                    difTime = curDate - dt,
                    difHour = difTime / 3600000,
                    difMinu = curDate.getMinutes() - dt.getMinutes();
                if(difHour<1) {
                    return (difMinu>5) ? difMinu+'分钟前' : '刚刚';
                }else{
                    format = 'Y-n-j H:i';
                }
            }
        }else{
            dt = new Date;
        }

        var year = dt.getFullYear(),
            month = dt.getMonth(),
            date = dt.getDate(),
            day = dt.getDay(),
            hour = dt.getHours(),
            mins = dt.getMinutes(),
            secs = dt.getSeconds(),
            ms = dt.getMilliseconds(),
            tz = dt.getTimezoneOffset();
        function component(code){
            var ret;
            // in the order they appear on http://php.net/manual/en/function.date.php
            // Not as nice-looking as a switch, I know. But it compiles smaller
            if(code=='d')ret = zp(date, 2);
            if(code=='D')ret = dayNames[day].substr(0,3);
            if(code=='j')ret = date;
            if(code=='l')ret = dayNames[day];
            if(code=='N')ret = day || 7;
            if(code=='S')ret = or(date);
            if(code=='w')ret = day;
            if(code=='z')ret = 0|(dt-new Date(year,0,1))/864e5;
            if(code=='W')ret = Math.ceil(~~((mn(dt)-fm(year))/864e5+0.5)/7);
            if(code=='F')ret = monthNames[month];
            if(code=='m')ret = zp(month+1,2);
            if(code=='M')ret = monthNames[month].substr(0,3);
            if(code=='n')ret = month+1;
            if(code=='t')ret = (new Date(year,month+1,0)).getDate();
            if(code=='L')ret = +((new Date(year,2,0)).getDate()==29);
            if(code=='o')ret = year-+(new Date(fm(year))>dt);
            if(code=='Y')ret = year;
            if(code=='y')ret = (year+'').slice(-2);
            if(code=='a')ret = hour>11?'pm':'am';
            if(code=='A')ret = hour>11?'PM':'AM';
            if(code=='B')ret = 0|((+dt+36e5)%864e5)/86400;
            if(code=='g')ret = (hour%12)||12;
            if(code=='G')ret = hour;
            if(code=='h')ret = zp((hour%12)||12,2);
            if(code=='H')ret = zp(hour,2);
            if(code=='i')ret = zp(mins,2);
            if(code=='s')ret = zp(secs,2);
            if(code=='u')ret = ms*1000;
            //if(code=='e')ret = undefined; // Can this be done in js?
            if(code=='I')ret = +!!((new Date(year,month,day)-new Date(year,1,1))%864e5);
            if(code=='O')ret = /(\S*\s){5}\S+([\+\-]\d{4})/.exec(dt.toString())[2];
            if(code=='P')ret = /(\S*\s){5}\S+([\+\-]\d{2})(\d{2})/.exec(dt.toString()).slice(2).join(':');
            //if(code=='T')ret = undefined; // Can this be done in js?
            if(code=='Z')ret = -tz*60;
            if(code=='U')ret = 0|dt/1000;

            return ret;
        }

        var out = '', cache = {};

        while(format){
            var c = format.charAt(0);
            if(c=='\\'){
                out += format.charAt(1);
                format = format.slice(2);
                continue;
            }
            var bit = (c in cache) ? cache[c] : (cache[c] = component(c));
            out += (bit!==undefined)?bit:c;
            format = format.slice(1);
        }
        return out;
    }
}();



/**
 * 页面跳转[使用location.reload()有可能导致重新提交]
 * @param url url,为空则刷新
 * @param win window对象
 */
function redirect(url, win) {
    if(win==undefined) win = window;
    var location = win.location;
    if(url==undefined || url=='') url = location.pathname + location.search;
    location.href = url;
}



/**
 * JqGrid刷新函数
 * @param selector JqGrid选择器
 * @param newParams 参数对象
 */
function refreshJqGrid(selector, newParams) {
    var data = $(selector).jqGrid('getGridParam', null);
    if(typeof newParams === 'object') {
        data = $.extend({}, data, newParams);
    }
    $(selector).jqGrid('setGridParam',data).trigger("reloadGrid");//重新载入
}


/**
 * 后台代开弹窗
 * @param url URL
 * @param title 标题
 * @param params url参数对象[键值对]
 * @param width 宽度
 * @param height 高度
 */
function admOpenLayer(url, title, params, width, height) {
    if(width == undefined) width = 605;
    if(height == undefined) height = 405;

    //将参数对象转为url
    if (!isString(params)) {
        params = $.param(params);
    }

    if(url.indexOf('?')==-1) {
        url += '?';
    }
    url += params;

    var indexL = layer.open({
        type: 2,
        title: title,
        shadeClose: true,
        shade: false,
        maxmin: true, //开启最大化最小化按钮
        area: [width+'px', height+'px'],
        offset: '10px',
        content: url
    });
    layer.style(indexL,{
        position: 'absolute'
    });

    return indexL;
}






