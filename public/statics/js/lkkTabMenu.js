/**
 * Created by kakuilan@163.com/lianq.net on 2017/4/29.
 * Desc: 后台MENU菜单生成插件
 */

!function ($) {
    var calculateObjectValue = function (self, name, args, defaultValue) {
        var func = name;
        if (typeof name === 'string') {
            var names = name.split('.');
            if (names.length > 1) {
                func = window;
                $.each(names, function (i, f) {
                    func = func[f];
                });
            } else {
                func = window[name];
            }
        }
        if (typeof func === 'object') {
            return func;
        }
        if (typeof func === 'function') {
            return func.apply(self, args);
        }
        if (!func && typeof name === 'string' && sprintf.apply(this, [name].concat(args))) {
            return sprintf.apply(this, [name].concat(args));
        }
        return defaultValue;
    };
    var isFunction = function (fn) {
        return Object.prototype.toString.call(fn)=== '[object Function]';
    };
    var isArray = (function () {
        if (Array.isArray) {
            return Array.isArray;
        }

        var objectToStringFn = Object.prototype.toString,
            arrayToStringResult = objectToStringFn.call([]);

        return function (subject) {
            return objectToStringFn.call(subject) === arrayToStringResult;
        };
    }());


    var lkkTabMenu = function (el, options) {
        this.options = options;
        this.$el = $(el);

        this.init();
    };


    //默认参数
    lkkTabMenu.DEFAULTS = {
        //数据相关
        data : [], //本地数组数据
        fromServer : false, //数据是否从服务端获取
        dataUrl : '', //服务端URL

        //数据字段
        idField : 'id', //ID字段
        parentField : 'parent_id', //父级字段
        titleField : 'title', //title字段
        urlField : 'url', //链接url字段
        sortField : 'id', //排序字段
        rootID : '0', //根节点ID

        //请求相关
        method: 'get',
        ajax: undefined,
        cache: true,
        contentType: 'application/json',
        dataType: 'json',
        ajaxOptions: {},
        dataField : '', //服务端返回的列表数组字段,为空则使用全部

        test : null
    };

    //插件允许的方法
    var allowedMethods = [
        'getOriginalData', //获取原始数据
        'getTreeData', //获取树数据
        'turnToTree', //(数据)转换为树型
        'test'
    ];

    lkkTabMenu.prototype.init = function () {
        this.initData();
        this.createMenu();
    };

    lkkTabMenu.prototype.getMenuNum = function () {
        return this.$el.find('li').length;
    };

    lkkTabMenu.prototype.initData = function () {
        this.data = this.options.data;
        if(this.options.fromServer) {

        }
    };

    //初始化服务端数据
    lkkTabMenu.prototype.initServer = function (query) {
        var that = this,
            data = {},
            params = {},
            request;

        $.extend(data, query || {});
        if (data === false) {
            return;
        }

        request = $.extend({}, calculateObjectValue(null, this.options.ajaxOptions), {
            type: this.options.method,
            url: this.options.dataUrl,
            data: this.options.contentType === 'application/json' && this.options.method === 'post' ? JSON.stringify(data) : data,
            cache: this.options.cache,
            contentType: this.options.contentType,
            dataType: this.options.dataType,
            success: function (res) {
                res = calculateObjectValue(that.options, that.options.responseHandler, [res], res);
                //TODO
            },
            error: function (res) {}
        });

        if (this.options.ajax) {
            calculateObjectValue(this, this.options.ajax, [request], null);
        } else {
            $.ajax(request);
        }

    };


    //二维数组排序
    lkkTabMenu.prototype.sortArray = function (array, key) {
        if(!isArray(array) || array.length===0 || typeof array[key] =='undefined') return array;

        array.sort((function(index){
            return function(a, b){
                return (a[index] === b[index] ? 0 : (a[index] < b[index] ? -1 : 1));
            };
        })(key));

        return array;
    };


    //按父级分组
    lkkTabMenu.prototype.groupByParents = function (array, options) {
        if(!isArray(array) || array.length===0) return array;

        return array.reduce(function(prev, item) {
            var parentID = item[options.parentField] || options.rootID;

            if (parentID && prev.hasOwnProperty(parentID)) {
                prev[parentID].push(item);
                return prev;
            }

            prev[parentID] = [item];
            return prev;
        }, {});
    };

    //生成树型数组
    lkkTabMenu.prototype.createTree = function (array, rootNodes, idField) {
        var tree = [];
        for (var rootNode in rootNodes) {
            var node = rootNodes[rootNode];
            var childNode = array[node[idField]];

            if (!node && !rootNodes.hasOwnProperty(rootNode)) {
                continue ;
            }

            if (childNode) {
                node.children = this.createTree(array, childNode, idField);
            }

            tree.push(node);
        }

        return tree;
    };

    //生成树型菜单
    lkkTabMenu.prototype.createMenu = function () {
        if(this.data.length==0) {
            this.$el.html('<li><a href="javascript:;"><i class="menu-icon ace-icon fa fa-spinner fa-spin"></i><span class="menu-text"> Loading... </span></a></li>');
            return false;
        }

        var sortField,sortArr,groupArr,treeArr;
        sortField = this.options.sortField ? this.options.sortField : this.options.idField;
        sortArr = sortField ? this.sortArray(this.data, sortField) : this.data;
        groupArr = this.groupByParents(sortArr, this.options);
        treeArr = this.createTree(groupArr, groupArr[this.options.rootID], this.options.idField);
        if(treeArr.length==0) return false;

        var html = this._createMenu(treeArr, this.options);
        this.$el.html(html);
        this.bindMenu();
    };

    //递归生成菜单树
    lkkTabMenu.prototype._createMenu = function (data, option, level) {
        if(typeof level=='undefined') level=0;
        var html = [], item=null;

        for(i in data) {
            item = data[i];

            html.push('<li class="">');
            html.push('<a href="'+item[option.urlField]+'" data-id="'+item[option.idField]+'" data-parent="'+item[option.parentField]+'" class="lkk_menuItem dropdown-toggle">');
            html.push('<i class="menu-icon ');
            if(level==0) {
                if(typeof item.class !='undefined'){
                    html.push('fa '+item.class);
                }else{
                    html.push('fa fa-list');
                }
            }else{
                html.push('fa fa-caret-right');
            }
            html.push(' "></i>');
            html.push('<span class="menu-text">'+item[option.titleField]+'</span>');
            if(typeof item.children !='undefined' && item.children.length>0) {
                html.push('<b class="arrow fa fa-angle-down"></b>');
            }
            html.push('</a><b class="arrow"></b>');

            if(typeof item.children !='undefined' && item.children.length>0) {
                html.push('<ul class="submenu nav-hide">');
                var subhtml = this._createMenu(item.children, option, level+1);
                html.push(subhtml);
                html.push('</ul>');
            }
            html.push('</li>');
        }

        return html.join('');
    };


    //绑定菜单事件
    lkkTabMenu.prototype.bindMenu = function () {
        $('.lkk_menuItem').each(function (k) {
            $(this).attr('data-tabid', k+1);
        });

        this.$el.on('click','.lkk_menuItem', {that:this}, this.clickMenu);
        $(".lkk_menuTabs").on("click", ".lkk_menuTab", {that:this}, this.selectTab);
        $(".lkk_menuTabs").on("dblclick", ".lkk_menuTab", this.reloadIframe);
        $(".lkk_menuTabs").on("click", ".lkk_menuTab i", {that:this}, this.closeTab);
        $(".lkk_tabLeft").on("click", {that:this}, this.tabLeft);
        $(".lkk_tabRight").on("click", {that:this}, this.tabRight);
        $(".lkk_tabCloseOther").on("click", this.closeOtherTab);
        $(".lkk_tabCloseAll").on("click", this.closeAllTab);
        $(".lkk_tabShowActive").on("click", {that:this}, this.showCurrentTab);
    };

    //点击菜单项
    lkkTabMenu.prototype.clickMenu = function (event) {
        var that=event.data.that,url=$(this).attr("href"),tabid=$(this).data("tabid"),title=$.trim($(this).text()),newIfm=true;

        $('#menu li').removeClass('active');
        $(this).parent('li').addClass('active');

        var submenu = $(this).siblings('.submenu');
        if(typeof that.submenuClick =='undefined') that.submenuClick = [];
        if(submenu.is(':visible') && $.inArray(tabid, that.submenuClick)==-1) {
            that.submenuClick.push(tabid);
            submenu.css({display:"none"});
        }

        if (url == undefined || $.trim(url).length == 0) {
            return false;
        }
        if(url=='#' || url=='') {
            newIfm = false;
        }

        $(".lkk_menuTab").each(function () {
            if ($(this).data("id") == tabid) {
                if (!$(this).hasClass("active")) {
                    $(this).addClass("active").siblings(".lkk_menuTab").removeClass("active");
                    that.activTab(this);
                    $(".lkk_mainContent .lkk_iframe").each(function () {
                        if ($(this).data("id") == tabid) {
                            $(this).show().siblings(".lkk_iframe").hide();
                            return false;
                        }
                    });
                }
                newIfm = false;
                return false;
            }
        });

        if (newIfm) {
            var p = '<a href="javascript:;" class="active lkk_menuTab" data-id="' + tabid + '">' + title + ' <i class="fa fa-times-circle"></i></a>';
            $(".lkk_menuTab").removeClass("active");
            var n = '<iframe class="lkk_iframe" name="iframe' + tabid + '" width="100%" height="100%" src="' + url + '" frameborder="0" data-id="' + tabid + '" seamless></iframe>';
            $(".lkk_mainContent").find("iframe.lkk_iframe").hide().parents(".lkk_mainContent").append(n);
            $(".lkk_menuTabs .page-tabs-content").append(p);
            that.activTab($(".lkk_menuTab.active"));
        }

        return true;
    };

    //激活tab
    lkkTabMenu.prototype.activTab = function (n) {
        var o = this.countEleWidth($(n).prevAll()), q = this.countEleWidth($(n).nextAll());
        var l = this.countEleWidth($(".content-tabs").children().not(".lkk_menuTabs"));
        var k = $(".content-tabs").outerWidth(true) - l;
        var p = 0;
        if ($(".page-tabs-content").outerWidth() < k) {
            p = 0
        } else {
            if (q <= (k - $(n).outerWidth(true) - $(n).next().outerWidth(true))) {
                if ((k - $(n).next().outerWidth(true)) > q) {
                    p = o;
                    var m = n;
                    while ((p - $(m).outerWidth()) > ($(".page-tabs-content").outerWidth() - k)) {
                        p -= $(m).prev().outerWidth();
                        m = $(m).prev();
                    }
                }
            } else {
                if (o > (k - $(n).outerWidth(true) - $(n).prev().outerWidth(true))) {
                    p = o - $(n).prev().outerWidth(true);
                }
            }
        }
        $(".page-tabs-content").animate({marginLeft: 0 - p + "px"}, "fast");
    };

    //计算元素的宽度
    lkkTabMenu.prototype.countEleWidth = function (n) {
        var k = 0;
        $(n).each(function () {
            k += $(this).outerWidth(true);
        });
        return k;
    };

    //TAB左定位
    lkkTabMenu.prototype.tabLeft = function (event) {
        var that = event.data.that;
        var o = Math.abs(parseInt($(".page-tabs-content").css("margin-left")));
        var l = that.countEleWidth($(".content-tabs").children().not(".lkk_menuTabs"));
        var k = $(".content-tabs").outerWidth(true) - l;
        var p = 0;
        if ($(".page-tabs-content").width() < k) {
            return false
        } else {
            var m = $(".lkk_menuTab:first");
            var n = 0;
            while ((n + $(m).outerWidth(true)) <= o) {
                n += $(m).outerWidth(true);
                m = $(m).next();
            }
            n = 0;
            if (that.countEleWidth($(m).prevAll()) > k) {
                while ((n + $(m).outerWidth(true)) < (k) && m.length > 0) {
                    n += $(m).outerWidth(true);
                    m = $(m).prev();
                }
                p = that.countEleWidth($(m).prevAll());
            }
        }
        $(".page-tabs-content").animate({marginLeft: 0 - p + "px"}, "fast");
    };

    //TAB右定位
    lkkTabMenu.prototype.tabRight = function (event) {
        var that = event.data.that;
        var o = Math.abs(parseInt($(".page-tabs-content").css("margin-left")));
        var l = that.countEleWidth($(".content-tabs").children().not(".lkk_menuTabs"));
        var k = $(".content-tabs").outerWidth(true) - l;
        var p = 0;
        if ($(".page-tabs-content").width() < k) {
            return false
        } else {
            var m = $(".lkk_menuTab:first");
            var n = 0;
            while ((n + $(m).outerWidth(true)) <= o) {
                n += $(m).outerWidth(true);
                m = $(m).next();
            }
            n = 0;
            while ((n + $(m).outerWidth(true)) < (k) && m.length > 0) {
                n += $(m).outerWidth(true);
                m = $(m).next();
            }
            p = that.countEleWidth($(m).prevAll());
            if (p > 0) {
                $(".page-tabs-content").animate({marginLeft: 0 - p + "px"}, "fast");
            }
        }
    };

    //关闭TAB
    lkkTabMenu.prototype.closeTab = function (event) {
        var that = event.data.that;
        var m = $(this).parents(".lkk_menuTab").data("id");
        var l = $(this).parents(".lkk_menuTab").width();
        if ($(this).parents(".lkk_menuTab").hasClass("active")) {
            if ($(this).parents(".lkk_menuTab").next(".lkk_menuTab").size()) {
                var k = $(this).parents(".lkk_menuTab").next(".lkk_menuTab:eq(0)").data("id");
                $(this).parents(".lkk_menuTab").next(".lkk_menuTab:eq(0)").addClass("active");
                $(".lkk_mainContent .lkk_iframe").each(function () {
                    if ($(this).data("id") == k) {
                        $(this).show().siblings(".lkk_iframe").hide();
                        return false;
                    }
                });
                var n = parseInt($(".page-tabs-content").css("margin-left"));
                if (n < 0) {
                    $(".page-tabs-content").animate({marginLeft: (n + l) + "px"}, "fast")
                }
                $(this).parents(".lkk_menuTab").remove();
                $(".lkk_mainContent .lkk_iframe").each(function () {
                    if ($(this).data("id") == m) {
                        $(this).remove();
                        return false;
                    }
                })
            }
            if ($(this).parents(".lkk_menuTab").prev(".lkk_menuTab").size()) {
                var k = $(this).parents(".lkk_menuTab").prev(".lkk_menuTab:last").data("id");
                $(this).parents(".lkk_menuTab").prev(".lkk_menuTab:last").addClass("active");
                $(".lkk_mainContent .lkk_iframe").each(function () {
                    if ($(this).data("id") == k) {
                        $(this).show().siblings(".lkk_iframe").hide();
                        return false;
                    }
                });
                $(this).parents(".lkk_menuTab").remove();
                $(".lkk_mainContent .lkk_iframe").each(function () {
                    if ($(this).data("id") == m) {
                        $(this).remove();
                        return false;
                    }
                })
            }
        } else {
            $(this).parents(".lkk_menuTab").remove();
            $(".lkk_mainContent .lkk_iframe").each(function () {
                if ($(this).data("id") == m) {
                    $(this).remove();
                    return false;
                }
            });
            that.activTab($(".lkk_menuTab.active"))
        }
        return false;
    };

    //关闭其他TAB
    lkkTabMenu.prototype.closeOtherTab = function () {
        $(".page-tabs-content").children("[data-id]").not(":first").not(".active").each(function () {
            $('.lkk_iframe[data-id="' + $(this).data("id") + '"]').remove();
            $(this).remove();
        });
        $(".page-tabs-content").css("margin-left", "0");
    };

    //定位到当前TAB
    lkkTabMenu.prototype.showCurrentTab = function (event) {
        var that = event.data.that;
        that.activTab($(".lkk_menuTab.active"));
    };

    //选中TAB
    lkkTabMenu.prototype.selectTab = function (event) {
        var that = event.data.that;
        if (!$(this).hasClass("active")) {
            var k = $(this).data("id");
            $(".lkk_mainContent .lkk_iframe").each(function () {
                if ($(this).data("id") == k) {
                    $(this).show().siblings(".lkk_iframe").hide();
                    return false;
                }
            });
            $(this).addClass("active").siblings(".lkk_menuTab").removeClass("active");
            that.activTab(this);
        }
    };

    //重新加载ifram
    lkkTabMenu.prototype.reloadIframe = function () {
        var l = $('.lkk_iframe[data-id="' + $(this).data("id") + '"]');
        var k = l.attr("src");
    };

    //关闭所有TAB
    lkkTabMenu.prototype.closeAllTab = function () {
        $(".page-tabs-content").children("[data-id]").not(":first").each(function () {
            $('.lkk_iframe[data-id="' + $(this).data("id") + '"]').remove();
            $(this).remove();
        });
        $(".page-tabs-content").children("[data-id]:first").each(function () {
            $('.lkk_iframe[data-id="' + $(this).data("id") + '"]').show();
            $(this).addClass("active");
        });
        $(".page-tabs-content").css("margin-left", "0");
    };

    //lkkTabMenu.prototype.test = function () {};


    //插件
    $.fn.lkkTabMenu = function (option) {
        var value,args = Array.prototype.slice.call(arguments, 1);

        this.each(function () {
            var $this = $(this),
                data  = $this.data('menuObj'),
                options = $.extend({}, lkkTabMenu.DEFAULTS, $this.data(), typeof option === 'object' && option);

            if (typeof option === 'string') {
                if ($.inArray(option, allowedMethods) < 0) {
                    throw new Error("Unknown method: " + option);
                }

                if (!data) {
                    return;
                }
                value = data[option].apply(data, args);
            }

            if (!data) {
                $this.data('menuObj', (data = new lkkTabMenu(this, options)));
            }
        });

        return typeof value === 'undefined' ? this : value;
    };

}(jQuery);