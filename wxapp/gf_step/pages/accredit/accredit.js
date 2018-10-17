var app = getApp();

Page({
    data: {},
    onLoad: function(t) {
        var n = app.globalData.setaa;
        this.setData({
            setaa: n
        }), wx.setNavigationBarColor({
            frontColor: "#ffffff",
            backgroundColor: n.headcolor
        }), wx.setNavigationBarTitle({
            title: n.xcx
        });
    },
    bindopensetting: function() {
        wx.openSetting({
            success: function(t) {
                t.authSetting = {
                    "scope.werun": !0
                };
            }
        });
    },
    onReady: function() {},
    onShow: function() {
        var o = this;
        wx.getSetting({
            success: function(t) {
                if (t.authSetting["scope.werun"]) n = 1; else var n = 0;
                o.setData({
                    shou: n
                });
            }
        });
    },
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {},
    onShareAppMessage: function() {}
});