function _defineProperty(a, t, e) {
    return t in a ? Object.defineProperty(a, t, {
        value: e,
        enumerable: !0,
        configurable: !0,
        writable: !0
    }) : a[t] = e, a;
}

var app = getApp();

Page({
    data: {
        dataInfo: []
    },
    onLoad: function(a) {
        this.Question();
        var t = app.globalData.setaa;
        wx.setNavigationBarColor({
            frontColor: "#ffffff",
            backgroundColor: t.headcolor
        }), wx.setNavigationBarTitle({
            title: t.xcx
        });
        var e = this.data.tempInfo;
        for (var o in this.data.tempInfo) this.data.tempInfo[o].flag = !1;
        this.setData({
            tempInfo: this.data.tempInfo
        }), console.log(e), this.Headcolor();
    },
    dainj: function(a) {
        var t = a.currentTarget.dataset.index, e = "tempInfo[" + t + "].flag", o = this.data.tempInfo[t].flag;
        this.setData(_defineProperty({}, e, !o));
    },
    onReady: function() {},
    Question: function() {
        var e = this;
        app.util.request({
            url: "entry/wxapp/Question",
            method: "POST",
            success: function(a) {
                var t = a.data.data;
                e.setData({
                    tempInfo: t
                });
            }
        });
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
                var t = a.data.data.inviteball, e = a.data.data.sonlist, o = a.data.data.set;
                app.globalData.setaa = a.data.data.set;
                var n = a.data.data.user;
                i.setData({
                    inviteball: t,
                    sonlist: e,
                    setaa: o,
                    user: n
                });
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    onShow: function() {},
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {},
    onShareAppMessage: function() {}
});