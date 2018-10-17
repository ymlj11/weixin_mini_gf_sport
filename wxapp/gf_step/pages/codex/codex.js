var app = getApp();

Page({
    data: {},
    onLoad: function(a) {
        var o = app.globalData.setaa;
        wx.setNavigationBarColor({
            frontColor: "#ffffff",
            backgroundColor: o.headcolor
        }), this.Headcolor();
    },
    Headcolor: function() {
        var i = this;
        app.util.request({
            url: "entry/wxapp/Headcolor",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var o = a.data.data.inviteball, t = a.data.data.sonlist, n = a.data.data.set;
                app.globalData.setaa = a.data.data.set;
                var e = n.shenhe;
                i.setData({
                    inviteball: o,
                    sonlist: t,
                    setaa: n,
                    shenhe: e
                }), wx.setNavigationBarColor({
                    frontColor: "#ffffff",
                    backgroundColor: n.headcolor
                }), wx.setNavigationBarTitle({
                    title: n.xcx
                });
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    onReady: function() {},
    onShow: function() {},
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {},
    onShareAppMessage: function() {}
});