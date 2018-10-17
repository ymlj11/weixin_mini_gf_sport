var app = getApp();

Page({
    data: {
        qiyf: [ "", "", "" ]
    },
    onLoad: function(a) {
        var t = this;
        t.goodslist(), t.Headcolor();
        var o = app.globalData.setaa, e = app.globalData.user_id;
        t.setData({
            user_id: e
        }), wx.setNavigationBarColor({
            frontColor: "#ffffff",
            backgroundColor: o.headcolor
        }), t.Shenhe();
    },
    onReady: function() {},
    goodslist: function() {
        var o = this;
        app.util.request({
            url: "entry/wxapp/Awardslist",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data;
                o.setData({
                    goods: t
                });
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
                });
            }
        });
    },
    detail: function(a) {
        var t = a.currentTarget.dataset.id;
        wx.navigateTo({
            url: "../detail/detail?id=" + t + "&index=2"
        });
    },
    Lotto: function() {
        var o = this;
        app.util.request({
            url: "entry/wxapp/Lotto",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data;
                wx.showModal({
                    title: "提示",
                    content: "恭喜您抽中了" + t + ",请到个人中心中奖纪录完善收货信息",
                    success: function(a) {}
                }), o.Headcolor();
            },
            fail: function(a) {
                var t = a.data.message;
                wx.showModal({
                    title: "提示",
                    content: t,
                    success: function(a) {}
                });
            }
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
                var t = a.data.data.inviteball, o = a.data.data.sonlist, e = a.data.data.set;
                app.globalData.setaa = a.data.data.set;
                var n = a.data.data.user;
                s.setData({
                    inviteball: t,
                    sonlist: o,
                    setaa: e,
                    user: n
                });
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    onShow: function() {
        var a = this, t = app.globalData.user_id;
        console.log(app.globalData.user_id), a.setData({
            user_id: t
        }), a.Headcolor(), a.Black(t);
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
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {}
});