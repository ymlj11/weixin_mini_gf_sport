var app = getApp();

Page({
    data: {
        tex: [ "1", "兑换商品", "中奖记录" ],
        qie: 0,
        xiu: "修改地址"
    },
    onLoad: function(a) {
        this.Log(), this.Headcolor();
    },
    daio: function(a) {
        this.setData({
            qie: a.currentTarget.dataset.index
        }), this.Log();
    },
    Log: function() {
        var i = this;
        app.util.request({
            url: "entry/wxapp/Log",
            method: "POST",
            data: {
                user_id: app.globalData.user_id,
                qie: i.data.qie
            },
            success: function(a) {
                if (0 == i.data.qie) {
                    var t = a.data.data.coinlog;
                    i.setData({
                        Log: t
                    });
                } else if (1 == i.data.qie) {
                    var e = a.data.data.goodslog;
                    i.setData({
                        Logtwo: e
                    });
                } else if (2 == i.data.qie) {
                    var o = a.data.data.awardslog;
                    i.setData({
                        Logter: o
                    });
                }
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    onReady: function() {},
    onShow: function() {},
    xiu: function(a) {
        var t = a.currentTarget.dataset.id, r = this;
        r.setData({
            id: t
        }), wx.chooseAddress({
            success: function(a) {
                var t = a.userName, e = a.postalCode, o = a.provinceName, i = a.cityName, n = a.countyName, s = a.detailInfo, d = a.telNumber, l = a.nationalCode;
                app.util.request({
                    url: "entry/wxapp/Editwinlog",
                    method: "POST",
                    data: {
                        user_id: app.globalData.user_id,
                        userName: t,
                        postalCode: e,
                        provinceName: o,
                        cityName: i,
                        countyName: n,
                        detailInfo: s,
                        telNumber: d,
                        nationalCode: l,
                        id: r.data.id
                    },
                    success: function(a) {
                        r.setData({
                            qie: 2
                        }), r.Log();
                    },
                    fail: function(a) {
                        console.log(a);
                    }
                });
            }
        });
    },
    Headcolor: function() {
        var n = this, s = n.data.tex;
        app.util.request({
            url: "entry/wxapp/Headcolor",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data.inviteball, e = a.data.data.sonlist, o = a.data.data.set;
                app.globalData.setaa = a.data.data.set;
                for (var i = 0; i < s.length; i++) s[0] = o.coinname, n.setData({
                    tex: s
                });
                n.setData({
                    inviteball: t,
                    sonlist: e,
                    setaa: o
                }), wx.setNavigationBarColor({
                    frontColor: "#ffffff",
                    backgroundColor: o.headcolor
                }), wx.setNavigationBarTitle({
                    title: o.xcx
                });
            },
            fail: function(a) {
                console.log("失败" + a);
            }
        });
    },
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {},
    onReachBottom: function() {}
});