var app = getApp();

Page({
    data: {
        logs: [],
        fx_level: 0,
        is_daili: 0,
        follow: 0
    },
    onLoad: function() {
        var a = this;
        console.log(a.data);
        var t = app.globalData.userInfo;
        a.setData({
            userInfo: t
        });
        var o = app.globalData.setaa;
        wx.setNavigationBarColor({
            frontColor: "#ffffff",
            backgroundColor: o.headcolor
        }), a.Headcolor(), a.Shenhe();
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
    submitIncodex: function(a) {
        this.submitInfotwo(a), this.codex();
    },
    dizhi: function() {
        wx.chooseAddress({
            success: function(a) {
                a.userName, a.postalCode, a.provinceName, a.cityName, a.countyName, a.detailInfo, 
                a.telNumber, a.nationalCode;
            }
        });
    },
    Shenhe: function() {
        var o = this;
        app.util.request({
            url: "entry/wxapp/Shenhe",
            method: "POST",
            success: function(a) {
                var t = a.data.data.shenhe;
                o.setData({
                    sh_en: t
                }), 1 == t && o.Sanshibushu();
            }
        });
    },
    Sanshibushu: function() {
        var e = this;
        wx.login({
            success: function(a) {
                var t = a.code;
                e.setData({
                    code: t
                }), wx.getWeRunData({
                    success: function(a) {
                        var t = a.encryptedData, o = a.iv;
                        app.util.request({
                            url: "entry/wxapp/Sanshibushu",
                            method: "post",
                            dataType: "json",
                            data: {
                                wRunEncryptedData: t,
                                iv: o,
                                code: e.data.code,
                                user_id: app.globalData.user_id
                            },
                            success: function(a) {
                                var t = a.data.data.bushu, o = a.data.data.data;
                                e.setData({
                                    stepInfoList: t,
                                    bu: o
                                });
                            }
                        });
                    }
                });
            }
        });
    },
    news: function() {
        wx.navigateTo({
            url: "../news/news"
        });
    },
    trouble: function() {
        wx.navigateTo({
            url: "../trouble/trouble"
        });
    },
    codex: function() {
        wx.navigateTo({
            url: "../codex/codex"
        });
    },
    order: function(a) {
        var t = a.currentTarget.dataset.chshi;
        wx.navigateTo({
            url: "../goods/goods?chshi=" + t
        });
    },
    Headcolor: function() {
        var s = this;
        app.util.request({
            url: "entry/wxapp/Headcolor",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data.user;
                console.log(t.head_pic);
                var o = a.data.data.set, e = o.shenhe;
                s.setData({
                    user: t,
                    setaa: o,
                    shenhe: e
                });
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    duiahua: function() {
        wx.navigateTo({
            url: "../conversion/conversion"
        });
    },
    accredit: function() {
        wx.navigateTo({
            url: "../accredit/accredit"
        });
    },
    onShow: function() {
        this.Headcolor();
        var a = app.globalData.user_id;
        console.log(app.globalData.user_id), this.setData({
            user_id: a
        }), this.Black(a);
    },
    Black: function(a) {
        app.util.request({
            url: "entry/wxapp/Black",
            method: "POST",
            data: {
                user_id: a
            },
            success: function(a) {
                1 == a.data.data && wx.navigateTo({
                    url: "../back/back?finish=true"
                });
            },
            fail: function(a) {
                console.log("失败看咯哦", a);
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
        }), app.util.request({
            url: "entry/wxapp/Kefu",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            }
        });
    },
    dabvsb: function() {}
});