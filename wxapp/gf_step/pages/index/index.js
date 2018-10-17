var util = require("../../../utils/util.js"), app = getApp();

Page({
    data: {
        logs: [ "全部", "时尚", "美妆", "母婴", "书籍", "包包" ],
        cut: 0,
        qiyf: [ "", "", "" ],
        step: "--",
        tujh: [ "true", "true", "true", "true", "true", "true", "true", "true" ],
        upbushu: "",
        shenhe: 1,
        shouquan: 0,
        follow: 0,
        colr: "#ffffff",
        array: [ "01.00", "02.00", "03.00", "04.00", "05.00", "06.00", "07.00", "08.00", "09.00", "10.00", "11.00", "12.00", "13.00", "14.00", "15.00", "16.00", "17.00", "18.00", "19.00", "20.00", "21.00", "22.00", "23.00", "24.00" ],
        index: "",
        yunti: !0,
        sign: !0
    },
    onLoad: function(a) {
        var t = this;
		if(!a.invite){
			a.invite = 0;
		}
		if(!a.follow){
			a.follow = 0;
		}
		if(!a.attention){
			a.attention = 0;
		}

        console.log(a), a.finish && wx.navigateBack({
            delta: 1
        });
        var e = a.invite, s = a.follow, n = a.attention;
        t.data.shouquan;
        app.globalData.invite = e;
        var i = app.globalData.userInfo;
        t.setData({
            userInfo: i,
            follow: s,
            attention: n
        }), t.overdue(), t.goodslist(), t.Headcolor(), t.Shenhe(), t.Adv();
    },
    bindPickerChange: function(a) {
        console.log("picker发送选择改变，携带值为", a.detail.value), this.setData({
            index: a.detail.value
        });
    },
    dabvsb: function() {},
    Shenhe: function() {
        var e = this;
        app.util.request({
            url: "entry/wxapp/Shenhe",
            method: "POST",
            success: function(a) {
                var t = a.data.data.shenhe;
                e.setData({
                    sh_en: t
                });
            }
        });
    },
    Guanzhuaddstep: function() {
        1 == this.data.attention && app.util.request({
            url: "entry/wxapp/Guanzhuaddstep",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {}
        });
    },
    submitInfotwo: function(a) {
        console.log("获取id");
        var t = a.detail.formId;
        console.log(t), console.log("获取formid结束"), this.setData({
            formid: t
        }), app.util.request({
            url: "entry/wxapp/Formid",
            method: "POST",
            data: {
                user_id: app.globalData.user_id,
                formid: this.data.formid
            },
            success: function(a) {}
        });
    },
    tiaohuan: function(a) {
        var t = this, e = a.currentTarget.dataset.jump, s = a.currentTarget.dataset.appid, n = a.currentTarget.dataset.path;
        if (0 == e) {
            if ("" != (o = t.data.index)) ; else var i = new Date(), o = Number(i.getHours()) - 1;
            console.log(o);
            var u = t.data.yunti;
            t.data.array;
            t.setData({
                yunti: !u,
                index: o
            });
        } else 1 == e ? t.hanshui() : 2 == e && wx.navigateToMiniProgram({
            appId: s,
            path: n,
            extraData: {
                user_id: t.data.user_id
            },
            envVersion: "release",
            success: function(a) {
                console.log(a);
            },
            fail: function(a) {
                console.log(a);
            }
        });
    },
    queding: function() {
        var t = this, a = t.data.yunti;
        t.setData({
            yunti: !a
        }), 0 == t.data.user.is_yy && app.util.request({
            url: "entry/wxapp/Yy",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                //wx.showModal({
                //    title: "提示",
                //    content: "预约成功",
				//	showCancel: false,
                //    success: function(a) {
                //        a.confirm ? console.log("用户点击确定") : console.log("用户点击取消");
                //    }
                //}), t.Headcolor(), t.jiemi();
				wx.showToast({
					title: '预约成功',
					icon: 'success',
					duration: 2000
				}), t.Headcolor(), t.jiemi();
            }
        });
    },
    guanbil: function() {
        var a = this.data.yunti;
        this.setData({
            yunti: !a
        });
    },
    sing: function() {
        var a = this, t = a.data.sign;
        a.setData({
            sign: !t
        }), a.Issignshare();
    },
    Issignshare: function() {
        var n = this;
        app.util.request({
            url: "entry/wxapp/Issignshare",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data.set, e = a.data.data.user, s = t.is_signshare;
                n.setData({
                    Issigntaa: t,
                    Issignuser: e,
                    is_signshare: s
                });
            }
        });
    },
    guanbilsig: function() {
        var a = this.data.sign;
        this.setData({
            sign: !a
        });
    },
    submitInhanshui: function(a) {
        var t = this;
        t.submitInfotwo(a), setTimeout(function() {
            t.hanshui();
        }, 350);
    },
    submitInreset: function(a) {
        this.submitInfotwo(a), this.reset();
    },
    submitInqueding: function(a) {
        this.submitInfotwo(a), this.queding();
    },
    zhuan: function(a) {
        var t = a.currentTarget.dataset.appid, e = a.currentTarget.dataset.path;
        1 == a.currentTarget.dataset.jump && wx.navigateToMiniProgram({
            appId: t,
            path: e,
            extraData: {
                user_id: this.data.user_id
            },
            envVersion: "release",
            success: function(a) {
                console.log(a);
            },
            fail: function(a) {
                console.log(a);
            }
        });
    },
    Guanzhuball2bushu: function() {
        var t = this;
        app.util.request({
            url: "entry/wxapp/Guanzhuball2bushu",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                t.Headcolor(), t.jiemi();
            }
        });
    },
    zai: function() {
        this.data.shenhe;
        this.setData({
            shouquan: 0
        });
    },
    overdue: function() {
        var t = this;
        wx.getSetting({
            success: function(a) {
                if (a.authSetting["scope.userInfo"]) wx.checkSession({
                    success: function(a) {
                        t.register(function(a) {});
                    },
                    fail: function(a) {
                        t.data.shouquan;
                        t.setData({
                            shouquan: 1
                        });
                    }
                }); else {
                    t.data.shouquan;
                    t.setData({
                        shouquan: 1
                    });
                }
            }
        });
    },
    getUserInfo: function(t) {
        var e = this;
        wx.getSetting({
            success: function(a) {
                console.log(a), a.authSetting["scope.userInfo"] ? e.login(t) : wx.showModal({
                    title: "提示",
                    content: "获取用户信息失败,需要授权才能继续使用！",
                    showCancel: !1,
                    confirmText: "授权",
                    success: function(a) {
                        a.confirm && wx.openSetting({
                            success: function(a) {
                                a.authSetting["scope.userInfo"] ? wx.showToast({
                                    title: "授权成功"
                                }) : wx.showToast({
                                    title: "未授权..."
                                });
                            }
                        });
                    }
                });
            },
            fail: function(a) {
                console.log(a);
            }
        });
    },
    register: function(c) {
        app.globalData.invite;
        var d = this;
        wx.getStorage({
            key: "user",
            success: function(a) {
                var t = a.data.detail, e = a.data.detail.openid, s = (t = t.userInfo).country, n = t.province, i = t.city, o = t.gender, u = t.nickName, r = t.avatarUrl;
                app.util.request({
                    url: "entry/wxapp/zhuce",
                    method: "post",
                    dataType: "json",
                    data: {
                        open_id: e,
                        nickName: u,
                        gender: o,
                        country: s,
                        province: n,
                        city: i,
                        avatarUrl: r,
                        invite: app.globalData.invite
                    },
                    success: function(a) {
                        d.data.shouquan;
                        app.globalData.user_id = a.data.data;
                        var t = a.data.data;
                        d.setData({
                            user_id: t,
                            shouquan: 0
                        }), "function" == typeof c && c(a.data.data), d.sports(), d.Black(t), d.Signin(t), 
                        d.Guanzhuaddstep(), d.Headcolor();
                    },
                    fail: function(a) {
                        d.data.shouquan;
                        d.setData({
                            shouquan: 1
                        });
                    }
                });
            },
            fail: function(a) {
                d.data.shouquan;
                d.setData({
                    shouquan: 1
                });
            }
        });
    },
    Signin: function(a) {
        app.util.request({
            url: "entry/wxapp/Signin",
            method: "POST",
            data: {
                user_id: a
            },
            success: function(a) {
                a.data;
            },
            fail: function(a) {
                console.log("失败哦", a);
            }
        });
    },
    Black: function(a) {
        var e = this;
        app.util.request({
            url: "entry/wxapp/Black",
            method: "POST",
            data: {
                user_id: a
            },
            success: function(a) {
                var t = a.data.data;
                e.setData({
                    tyuy: t
                }), 1 == t && wx.navigateTo({
                    url: "../back/back?finish=true"
                });
            },
            fail: function(a) {
                console.log("失败哦", a);
            }
        });
    },
    login: function(e) {
        var s = this;
        app.globalData.userInfo ? ("function" == typeof cb && cb(app.globalData.userInfo), 
        s.register(function(a) {})) : wx.login({
            success: function(a) {
                var t = e.detail;
                app.globalData.userInfo = t.userInfo, t.act = "autologin", t.code = a.code, app.util.request({
                    url: "entry/wxapp/getopenid",
                    method: "post",
                    dataType: "json",
                    data: t,
                    success: function(a) {
                        0 == a.data.errno && (t.openid = a.data.data.openid, t.session_key = a.data.data.session_key, 
                        app.globalData.userInfo = t, app.globalData.session_key = a.data.data.session_key, 
                        wx.setStorageSync("user", e), "function" == typeof cb && cb(app.globalData.userInfo), 
                        s.register(function(a) {}), s.setData({
                            session_key: a.data.data.session_key
                        }));
                    }
                });
            },
            fail: function(a) {
                console.log("获取失败");
            }
        });
    },
    sports: function() {
        var t = this;
        wx.authorize({
            scope: "scope.werun",
            success: function(a) {
                t.jiemi(), console.log("首页运动授权成功信息", a);
            },
            fail: function(a) {
                console.log("首页运动授权失败信息", a), wx.showModal({
                    title: "打开微信运动授权，开始获取步数",
                    success: function(a) {
                        a.confirm ? wx.openSetting({
                            success: function(a) {
                                a.authSetting = {
                                    "scope.werun": !0
                                }, t.jiemi();
                            },
                            fail: function(a) {}
                        }) : a.cancel && console.log("用户点击取消");
                    }
                });
            },
            complete: function(a) {
                console.log("首页运动授权状态信息", a);
            }
        });
    },
    jiemi: function() {
        var i = this;
        i.data.session_key;
        wx.login({
            success: function(a) {
                var t = a.code;
                i.setData({
                    code: t
                }), wx.getWeRunData({
                    success: function(a) {
                        var t = a.encryptedData, e = a.iv;
                        app.util.request({
                            url: "entry/wxapp/bushu",
                            method: "post",
                            dataType: "json",
                            data: {
                                wRunEncryptedData: t,
                                iv: e,
                                code: i.data.code,
                                session_key: i.data.session_key,
                                user_id: app.globalData.user_id
                            },
                            success: function(a) {
                                var t = a.data.data.bushu, e = a.data.data.user, s = a.data.data.upbushu, n = a.data.data.weixinbushu;
                                app.globalData.weixinbushu = a.data.data.weixinbushu, i.setData({
                                    step: t,
                                    zpnghe: e,
                                    upbushu: s,
                                    weixinbushu: n
                                });
                            }
                        });
                    }
                });
            }
        });
    },
    Adv: function() {
        var n = this;
        app.util.request({
            url: "entry/wxapp/Adv",
            method: "post",
            dataType: "json",
            success: function(a) {
                var t = a.data.data.adv, e = a.data.data.icon, s = a.data.data.runpic;
                n.setData({
                    Advimg: t,
                    iconimg: e,
                    runpic: s
                });
            }
        });
    },
    jia: function(a) {
        var t = a.currentTarget.dataset.upbushu, e = this;
        app.util.request({
            url: "entry/wxapp/Upball2bushu",
            method: "post",
            dataType: "json",
            data: {
                upbushu: t,
                user_id: app.globalData.user_id
            },
            success: function(a) {
                t = "", e.setData({
                    upbushu: t
                }), e.jiemi();
            }
        });
    },
    onPullDownRefresh: function() {
        this.jiemi(), setTimeout(function() {
            wx.stopPullDownRefresh();
        }, 1e3), this.Headcolor();
    },
    hanshui: function() {
        wx.navigateTo({
            url: "../Sweatdiary/Sweatdiary?bushu=" + this.data.weixinbushu
        });
    },
    detail: function(a) {
        var t = this.data.tyuy, e = a.currentTarget.dataset.id;
        1 != t ? wx.navigateTo({
            url: "../detail/detail?id=" + e + "&index=1&s=" + this.data.setaa.adunit2
        }) : wx.showModal({
            title: "提示",
            content: "您不能兑换此商品",
            success: function(a) {
                a.confirm ? console.log("用户点击确定") : console.log("用户点击取消");
            }
        });
    },
    invite: function() {
        wx.navigateTo({
            url: "../inviter/inviter"
        });
    },
    onShow: function() {
        this.overdue();
        var a = parseInt(100 * Math.random());
        this.setData({
            top: a
        }), this.goodslist(), this.Adv();
    },
    reset: util.throttle(function(a) {
        var e = this;
        app.util.request({
            url: "entry/wxapp/bushu2money",
            method: "post",
            dataType: "json",
            data: {
                step: e.data.step,
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data;
                wx.showModal({
                    content: "兑换" + e.data.step + "步为" + t + e.data.setaa.coinname,
                    success: function(a) {
                        a.confirm ? app.util.request({
                            url: "entry/wxapp/bushulog",
                            method: "post",
                            dataType: "json",
                            data: {
                                step: e.data.step,
                                user_id: app.globalData.user_id
                            },
                            success: function(a) {
                                e.jiemi();
                                var t = wx.createInnerAudioContext();
                                t.autoplay = !0, t.src = e.data.setaa.voice, t.onPlay(function() {
                                    console.log("开始播放");
                                }), setTimeout(function() {
                                    t.onStop(function() {
                                        console.log("开始播放");
                                    });
                                }, 200);
                            }
                        }) : a.cancel;
                    }
                });
            }
        });
    }, 2e3),
    Headcolor: function() {
        var o = this;
        app.util.request({
            url: "entry/wxapp/Headcolor",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data.inviteball, e = a.data.data.sonlist, s = a.data.data.set;
                app.globalData.setaa = a.data.data.set;
                var n = s.shenhe, i = a.data.data.user;
                o.setData({
                    inviteball: t,
                    sonlist: e,
                    setaa: s,
                    shenhe: n,
                    user: i
                }), wx.setNavigationBarColor({
                    frontColor: "#ffffff",
                    backgroundColor: s.headcolor
                }), wx.setNavigationBarTitle({
                    title: s.xcx
                });
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    xiaoshi: function(a) {
        for (var t = a.currentTarget.dataset.id, e = a.currentTarget.dataset.index, s = this, n = s.data.tujh, i = 0; i < n.length; i++) n[e] = !1, 
        s.setData({
            tujh: n
        });
        app.util.request({
            url: "entry/wxapp/Ball2bushu",
            method: "POST",
            data: {
                user_id: app.globalData.user_id,
                id: t
            },
            success: function(a) {
                s.jiemi();
            }
        });
    },
    goodslist: function() {
        var e = this;
        app.util.request({
            url: "entry/wxapp/Goodslist",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data;
                e.setData({
                    goods: t
                });
            }
        });
    },
    guanzhu: function() {
        this.data.follow;
        this.setData({
            follow: 1
        });
    },
    xiao: function() {
        this.data.follow;
        this.setData({
            follow: 0
        });
    },
    fenyia: function() {
        var t = this;
        1 != t.data.Issigntaa.is_signshare && app.util.request({
            url: "entry/wxapp/Signshare",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                t.data.is_signshare;
                t.jiemi();
                t.data.sign;
                t.setData({
                    sign: !0,
                    is_signshare: 0
                });
            }
        });
    },
    onShareAppMessage: function(a) {
        var t = this;
        return "button" === a.from && (console.log(a.target), t.fenyia()), {
            title: t.data.setaa.sharetitle,
            imageUrl: t.data.setaa.sharepic,
            path: "/gf_step/pages/index/index?invite=" + t.data.user_id,
            success: function(a) {
                console.log("本地user_id", t.data.user_id);
            },
            fail: function(a) {}
        };
    }
});