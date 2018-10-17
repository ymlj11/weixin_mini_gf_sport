var app = getApp();

Page({
    data: {},
    onLoad: function(a) {
        var t = this, o = a.aid;
        t.setData({
            aid: o
        }), t.Activitylist(o), t.Headcolor();
    },
    onReady: function() {},
    Headcolor: function() {
        var s = this;
        app.util.request({
            url: "entry/wxapp/Headcolor",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data.inviteball, o = a.data.data.sonlist, i = a.data.data.set;
                app.globalData.setaa = a.data.data.set;
                var n = i.shenhe;
                s.setData({
                    inviteball: t,
                    sonlist: o,
                    setaa: i,
                    shenhe: n
                }), wx.setNavigationBarColor({
                    frontColor: "#ffffff",
                    backgroundColor: i.headcolor
                }), wx.setNavigationBarTitle({
                    title: i.xcx
                });
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    Activitylist: function() {
        var i = this;
        app.util.request({
            url: "entry/wxapp/Activitylist",
            method: "POST",
            data: {
                user_id: app.globalData.user_id,
                aid: i.data.aid
            },
            success: function(a) {
                var t = a.data.data.data, o = t.tomorrow.status;
                i.setData({
                    Activitylist: t,
                    status: o
                });
            }
        });
    },
    onShow: function() {},
    onHide: function() {},
    bao: function() {
        var t = this;
        0 == t.data.status && app.util.request({
            url: "entry/wxapp/Apply",
            method: "POST",
            data: {
                user_id: app.globalData.user_id,
                aid: t.data.aid
            },
            success: function(a) {
                t.Activitylist(), wx.showModal({
                    title: "提示",
                    content: "报名成功",
					showCancel: false,
                    success: function(a) {
                        a.confirm ? console.log("用户点击确定") : console.log("用户点击取消");
						wx.redirectTo({url:"/pages/Defiance/Defiance"});
                    }
                });
            }
        });
    },
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {},
    onShareAppMessage: function() {}
});