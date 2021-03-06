/**
 * Created by kakuilan@163.com/lianq.net on 2018/3/10.
 * Desc:
 */

define(['jquery', 'bootstrap', 'backend', 'addtabs', 'adminlte', 'form','md5','lkkTabMenu'], function ($, undefined, Backend, undefined, AdminLTE, Form, md5, undefined) {
    var Controller = {
        index: function () {
            //窗口大小改变,修正主窗体最小高度
            $(window).resize(function () {
                $(".tab-addtabs").css("height", $(".content-wrapper").height() + "px");
            });

            //双击重新加载页面
            $(document).on("dblclick", ".sidebar-menu li > a", function (e) {
                $("#tab_" + $(this).attr("addtabs") + " iframe").attr('src', function (i, val) {
                    return val;
                });
                e.stopPropagation();
            });

            //快捷搜索
            $(".menuresult").width($("form.sidebar-form > .input-group").width());
            var isAndroid = /(android)/i.test(navigator.userAgent);
            var searchResult = $(".menuresult");
            $("form.sidebar-form").on("blur", "input[name=q]", function () {
                searchResult.addClass("hide");
                if (isAndroid) {
                    $.AdminLTE.options.sidebarSlimScroll = true;
                }
            }).on("focus", "input[name=q]", function () {
                if (isAndroid) {
                    $.AdminLTE.options.sidebarSlimScroll = false;
                }
                if ($("a", searchResult).size() > 0) {
                    searchResult.removeClass("hide");
                }
            }).on("keyup", "input[name=q]", function () {
                searchResult.html('');
                var val = $(this).val();
                var html = new Array();
                if (val != '') {
                    $("ul.sidebar-menu li a[addtabs]:not([href^='javascript:;'])").each(function () {
                        if ($("span:first", this).text().indexOf(val) > -1 || $(this).attr("py").indexOf(val) > -1 || $(this).attr("pinyin").indexOf(val) > -1) {
                            html.push('<a data-url="' + $(this).attr("href") + '" href="javascript:;">' + $("span:first", this).text() + '</a>');
                            if (html.length >= 100) {
                                return false;
                            }
                        }
                    });
                }
                $(searchResult).append(html.join(""));
                if (html.length > 0) {
                    searchResult.removeClass("hide");
                } else {
                    searchResult.addClass("hide");
                }
            });
            //快捷搜索点击事件
            $("form.sidebar-form").on('mousedown click', '.menuresult a[data-url]', function () {
                Backend.api.addtabs($(this).data("url"));
            });

            //切换左侧sidebar显示隐藏
            $(document).on("click fa.event.toggleitem", ".sidebar-menu li > a", function (e) {
                $(".sidebar-menu li").removeClass("active");
                //当外部触发隐藏的a时,触发父辈a的事件
                if (!$(this).closest("ul").is(":visible")) {
                    //如果不需要左侧的菜单栏联动可以注释下面一行即可
                    $(this).closest("ul").prev().trigger("click");
                }

                var visible = $(this).next("ul").is(":visible");
                if (!visible) {
                    $(this).parents("li").addClass("active");
                } else {
                }
                e.stopPropagation();
            });

            //清除缓存
            $(document).on('click', "[data-toggle='wipecache']", function () {
                var url = $(this).data('url');
                $.ajax({
                    url: url,
                    dataType: 'json',
                    cache: false,
                    success: function (ret) {
                        var msg = ret.hasOwnProperty("msg") && ret.msg != "" ? ret.msg : "";
                        if(ret.status) {
                            Toastr.success(msg ? msg : __('Wipe cache completed'));
                        }else{
                            Toastr.error(msg ? msg : __('Wipe cache failed'));
                        }
                    }, error: function () {
                        Toastr.error(__('Network error'));
                    }
                });
            });

            //全屏事件
            $(document).on('click', "[data-toggle='fullscreen']", function () {
                var doc = document.documentElement;
                if ($(document.body).hasClass("full-screen")) {
                    $(document.body).removeClass("full-screen");
                    document.exitFullscreen ? document.exitFullscreen() : document.mozCancelFullScreen ? document.mozCancelFullScreen() : document.webkitExitFullscreen && document.webkitExitFullscreen();
                } else {
                    $(document.body).addClass("full-screen");
                    doc.requestFullscreen ? doc.requestFullscreen() : doc.mozRequestFullScreen ? doc.mozRequestFullScreen() : doc.webkitRequestFullscreen ? doc.webkitRequestFullscreen() : doc.msRequestFullscreen && doc.msRequestFullscreen();
                }
            });

            //生成菜单
            $.getJSON(Config.extparam.menuUrl, function (res) {
                var menuData = res.data;
                $('#myMenu').lkkTabMenu({data: menuData, idField:'id', parentField:'parent', sortField: 'sort'});
            });


            //绑定tabs事件
            $('#nav').addtabs({iframeHeight: "100%"});

            //修复iOS下iframe无法滚动的BUG
            if (/iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream) {
                $(".tab-addtabs").addClass("ios-iframe-fix");
            }

            if ($("ul.sidebar-menu li.active a").size() > 0) {
                $("ul.sidebar-menu li.active a").trigger("click");
            } else {
                $("ul.sidebar-menu li a[url!='javascript:;']:first").trigger("click");
            }

            //保留最后一次点击的窗口
            $(document).on("click", "a[addtabs]", function (e) {
                localStorage.setItem("lastpage", $(this).attr("url"));
            });

            //回到最后一次打开的页面
            var lastpage = localStorage.getItem("lastpage");
            if (lastpage) {
                //刷新页面后跳到到刷新前的页面
                Backend.api.addtabs(lastpage);
            }

            /**
             * List of all the available skins
             *
             * @type Array
             */
            var my_skins = [
                "skin-blue",
                "skin-black",
                "skin-red",
                "skin-yellow",
                "skin-purple",
                "skin-green",
                "skin-blue-light",
                "skin-black-light",
                "skin-red-light",
                "skin-yellow-light",
                "skin-purple-light",
                "skin-green-light"
            ];

            setup();

            /**
             * Toggles layout classes
             *
             * @param String cls the layout class to toggle
             * @returns void
             */
            function change_layout(cls) {
                $("body").toggleClass(cls);
                AdminLTE.layout.fixSidebar();
                //Fix the problem with right sidebar and layout boxed
                if (cls == "layout-boxed")
                    AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
                if ($('body').hasClass('fixed') && cls == 'fixed' && false) {
                    AdminLTE.pushMenu.expandOnHover();
                    AdminLTE.layout.activate();
                }
                AdminLTE.controlSidebar._fix($(".control-sidebar-bg"));
                AdminLTE.controlSidebar._fix($(".control-sidebar"));
            }

            /**
             * Replaces the old skin with the new skin
             * @param String cls the new skin class
             * @returns Boolean false to prevent link's default action
             */
            function change_skin(cls) {
                if (!$("body").hasClass(cls)) {
                    $.each(my_skins, function (i) {
                        $("body").removeClass(my_skins[i]);
                    });

                    $("body").addClass(cls);
                    store('skin', cls);
                    var cssfile = Backend.api.fixurl("/assets/css/skins/" + cls + ".css");
                    $('head').append('<link rel="stylesheet" href="' + cssfile + '" type="text/css" />');
                }
                return false;
            }

            /**
             * Store a new settings in the browser
             *
             * @param String name Name of the setting
             * @param String val Value of the setting
             * @returns void
             */
            function store(name, val) {
                if (typeof (Storage) !== "undefined") {
                    localStorage.setItem(name, val);
                } else {
                    window.alert('Please use a modern browser to properly view this template!');
                }
            }

            /**
             * Get a prestored setting
             *
             * @param String name Name of of the setting
             * @returns String The value of the setting | null
             */
            function get(name) {
                if (typeof (Storage) !== "undefined") {
                    return localStorage.getItem(name);
                } else {
                    window.alert('Please use a modern browser to properly view this template!');
                }
            }

            /**
             * Retrieve default settings and apply them to the template
             *
             * @returns void
             */
            function setup() {
                var tmp = get('skin');
                if (tmp && $.inArray(tmp, my_skins))
                    change_skin(tmp);

                // 皮肤切换
                $("[data-skin]").on('click', function (e) {
                    if ($(this).hasClass('knob'))
                        return;
                    e.preventDefault();
                    change_skin($(this).data('skin'));
                });

                // 布局切换
                $("[data-layout]").on('click', function () {
                    change_layout($(this).data('layout'));
                });

                // 切换子菜单显示和菜单小图标的显示
                $("[data-menu]").on('click', function () {
                    if ($(this).data("menu") == 'show-submenu') {
                        $("ul.sidebar-menu").toggleClass("show-submenu");
                    } else {
                        $(".nav-addtabs").toggleClass("disable-top-badge");
                    }
                });

                // 右侧控制栏切换
                $("[data-controlsidebar]").on('click', function () {
                    change_layout($(this).data('controlsidebar'));
                    var slide = !AdminLTE.options.controlSidebarOptions.slide;
                    AdminLTE.options.controlSidebarOptions.slide = slide;
                    if (!slide)
                        $('.control-sidebar').removeClass('control-sidebar-open');
                });

                // 右侧控制栏背景切换
                $("[data-sidebarskin='toggle']").on('click', function () {
                    var sidebar = $(".control-sidebar");
                    if (sidebar.hasClass("control-sidebar-dark")) {
                        sidebar.removeClass("control-sidebar-dark")
                        sidebar.addClass("control-sidebar-light")
                    } else {
                        sidebar.removeClass("control-sidebar-light")
                        sidebar.addClass("control-sidebar-dark")
                    }
                });

                // 菜单栏展开或收起
                $("[data-enable='expandOnHover']").on('click', function () {
                    $(this).attr('disabled', true);
                    AdminLTE.pushMenu.expandOnHover();
                    if (!$('body').hasClass('sidebar-collapse'))
                        $("[data-layout='sidebar-collapse']").click();
                });

                // 重设选项
                if ($('body').hasClass('fixed')) {
                    $("[data-layout='fixed']").attr('checked', 'checked');
                }
                if ($('body').hasClass('layout-boxed')) {
                    $("[data-layout='layout-boxed']").attr('checked', 'checked');
                }
                if ($('body').hasClass('sidebar-collapse')) {
                    $("[data-layout='sidebar-collapse']").attr('checked', 'checked');
                }
                if ($('ul.sidebar-menu').hasClass('show-submenu')) {
                    $("[data-menu='show-submenu']").attr('checked', 'checked');
                }
                if ($('ul.nav-addtabs').hasClass('disable-top-badge')) {
                    $("[data-menu='disable-top-badge']").attr('checked', 'checked');
                }

            }

            $(window).resize();
        },

        //登录
        login: function () {
            var lastlogin = localStorage.getItem("lastlogin");
            if (lastlogin) {
                lastlogin = JSON.parse(lastlogin);
                if (lastlogin && typeof lastlogin === 'object') {
                    if(typeof lastlogin.avatar !== 'undefined' && lastlogin.avatar!='') $("#profile-img").attr("src", Backend.api.cdnurl(lastlogin.avatar));
                    if(typeof lastlogin.username !== 'undefined' && lastlogin.username!='') $("#pd-form-username").val(lastlogin.username);
                }
            }

            //让错误提示框居中
            Fast.config.toastr.positionClass = "toast-top-center";

            //本地验证未通过时提示
            $("#login-form").data("validator-options", {
                invalid: function (form, errors) {
                    $.each(errors, function (i, j) {
                        Toastr.error(j);
                    });
                },
                target: '#errtips'
            });

            //为表单绑定事件
            Form.api.bindevent($("#login-form"), function () {
                //提交前
                var pwd = $.trim($('#password').val());
                if(pwd=='') {
                    Toastr.error('请输入密码');
                    return false;
                }
                $('#password').val(md5(pwd));
                return true;
            }, function (data, ret) {
                //提交后
                if(ret.status) {
                    localStorage.setItem("lastlogin", JSON.stringify({id: data.id, username: data.username, avatar: data.avatar}));
                    location.href = Backend.api.fixurl(data.url);
                }else{
                    $("#password").val('');
                    $("#verifyCode").val('');
                }
            });

            var refreshCaptcha = function () {
                $.getJSON(Config.captchaUrl +'?width=140&height=30', function (res) {
                    if(res.status) {
                        $('#verifyImg').attr('src', res.data.img);
                        $('#veriEncode').val(res.data.encode);
                    }
                });
            };
            $('#verifyImg').click(function () {
                refreshCaptcha();
            });

            refreshCaptcha();
        },
        passwd: function () {
            //让错误提示框居中
            Fast.config.toastr.positionClass = "toast-top-center";

            //为表单绑定事件
            Form.api.bindevent($("#pwd-form"), null, function (data) {
                $("#pwd-form")[0].reset();
                return true;
            });
        }
    };

    return Controller;
});