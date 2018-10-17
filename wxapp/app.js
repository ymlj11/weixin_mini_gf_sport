App({
    onLaunch: function() {
        var t = this;
        wx.getSystemInfo({
            success: function(s) {
                t.globalData.statusBarHeight = s.statusBarHeight;
            }
        });
    },
    util: require("we7/resource/js/util.js"),
    globalData: {
        userInfo: null,
        user_id: null,
        we_app_info: null
    },
    siteInfo: require("siteinfo.js")
});