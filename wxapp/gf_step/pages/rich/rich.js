var WxParse = require("../../../wxParse/wxParse.js"), app = getApp();

Page({
    data: {},
    onLoad: function(o) {
        var n = this;
        app.util.request({
            url: "entry/wxapp/Headcolor",
            method: "POST",
            success: function(o) {
                var a = o.data.data.set.rule, t = o.data.data.set;
                wx.setNavigationBarColor({
                    frontColor: "#ffffff",
                    backgroundColor: t.headcolor
                }), wx.setNavigationBarTitle({
                    title: t.xcx
                }), WxParse.wxParse("article", "html", a, n, 5);
            },
            fail: function(o) {
                console.log("失败");
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