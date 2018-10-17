Page({
    data: {},
    onLoad: function(n) {
        n.finish && wx.navigateBack({
            delta: 2
        });
    },
    dain: function() {},
    onReady: function() {},
    onShow: function() {
        wx.reLaunch({
            url: "../index/index?finish==true"
        });
    },
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {},
    onShareAppMessage: function() {}
});