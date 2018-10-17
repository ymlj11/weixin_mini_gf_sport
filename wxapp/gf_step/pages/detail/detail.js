var WxParse = require("../../../wxParse/wxParse.js"), app = getApp();

Page({
    data: {
        circular: !0,
        shenhe: 0
    },
    onLoad: function(a) {
        var o = a.s, t = a.id, e = a.index, n = app.globalData.setaa;
        wx.setNavigationBarColor({
            frontColor: "#ffffff",
            backgroundColor: n.headcolor
        }), wx.setNavigationBarTitle({
            title: n.xcx
        });
        var s = this;
        s.setData({
            goods_id: t,
            index: e,
            s: o
        }), 1 == e ? s.goodsone() : 2 == e && s.goodstwo(), s.Headcolor();
    },
    fan: function() {
        wx.navigateBack({
            delta: 1
        });
    },
    binderror: function(a) {
        console.log(a.detail);
    },
    dui: function() {
        var d = this;
        wx.showModal({
            title: "提示",
            content: "您确定要兑换此商品么",
            success: function(a) {
                a.confirm ? (console.log("用户点击确定"), wx.chooseAddress({
                    success: function(a) {
                        var o = a.userName, t = a.postalCode, e = a.provinceName, n = a.cityName, s = a.countyName, i = a.detailInfo, l = a.telNumber, c = a.nationalCode;
                        app.util.request({
                            url: "entry/wxapp/Createorder",
                            method: "POST",
                            data: {
                                user_id: app.globalData.user_id,
                                userName: o,
                                postalCode: t,
                                provinceName: e,
                                cityName: n,
                                countyName: s,
                                detailInfo: i,
                                telNumber: l,
                                nationalCode: c,
                                goods_id: d.data.goods_id
                            },
                            success: function(a) {
                                if (1 == a.data.data) {
                                    wx.showModal({
                                        title: "提示",
                                        content: "领取成功",
                                        success: function(a) {
                                            a.confirm ? (console.log("用户点击确定"), d.goodsone()) : a.cancel && console.log("用户点击取消");
                                        }
                                    });
                                    d.data.id;
                                }
                            },
                            fail: function(a) {
                                wx.showModal({
                                    title: "提示",
                                    content: a.data.message,
                                    success: function(a) {
                                        a.confirm ? (console.log("用户点击确定"), wx.navigateBack({
                                            delta: 1
                                        })) : a.cancel && console.log("用户点击取消");
                                    }
                                });
                            }
                        });
                    }
                })) : a.cancel && console.log("用户点击取消");
            }
        });
    },
    goodsone: function() {
        var s = this;
        app.util.request({
            url: "entry/wxapp/Goodsdetail",
            method: "POST",
            data: {
                goods_id: s.data.goods_id,
                user_id: app.globalData.user_id,
                index: 1
            },
            success: function(a) {
                var o = a.data.data, t = o.goodsinfo;
                if (WxParse.wxParse("article", "html", t, s, 5), s.setData({
                    zhmng: o,
                    index: 1
                }), parseInt(o.usermoney) < parseInt(o.price)) var e = "运动币不足，邀请好友可以增加步数", n = 0; else e = "我要兑换", 
                n = 1;
                s.setData({
                    hua: e,
                    bnian: n
                });
            },
            fail: function(a) {
                s.setData({
                    qity: 1
                }), wx.showModal({
                    title: "提示",
                    content: "已经抢光了！！！",
                    success: function(a) {
                        a.confirm ? (console.log("用户点击确定"), wx.navigateBack({
                            delta: 1
                        })) : a.cancel && (console.log("用户点击取消"), wx.navigateBack({
                            delta: 1
                        }));
                    }
                });
            }
        });
    },
    goodstwo: function() {
        var n = this;
        app.util.request({
            url: "entry/wxapp/Goodsdetail",
            method: "POST",
            data: {
                goods_id: n.data.goods_id,
                user_id: app.globalData.user_id,
                index: 2
            },
            success: function(a) {
                var o = a.data.data;
                if (n.setData({
                    zhmng: o,
                    index: 2
                }), parseInt(o.usermoney) < parseInt(o.price)) var t = "运动币不足，邀请好友可以增加步数", e = 0; else t = "我要兑换", 
                e = 1;
                n.setData({
                    hua: t,
                    bnian: e
                });
            },
            fail: function(a) {
                wx.showModal({
                    title: "提示",
                    content: "已经抢光了！！！",
                    success: function(a) {
                        a.confirm ? (console.log("用户点击确定"), wx.navigateBack({
                            delta: 1
                        })) : a.cancel && console.log("用户点击取消");
                    }
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
                var o = a.data.data.inviteball, t = a.data.data.sonlist, e = a.data.data.set;
                app.globalData.setaa = a.data.data.set;
                var n = e.shenhe, s = e.adunit2;
                i.setData({
                    inviteball: o,
                    sonlist: t,
                    setaa: e,
                    shenhe: n,
                    sliu: s
                }), wx.setNavigationBarColor({
                    frontColor: "#ffffff",
                    backgroundColor: e.headcolor
                }), wx.setNavigationBarTitle({
                    title: e.xcx
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
    onShareAppMessage: function(a) {
        var o = this;
        return "button" === a.from && console.log(a.target), {
            title: o.data.setaa.sharetitle,
            imageUrl: o.data.setaa.sharepic,
            path: "/gf_step/pages/index/index?invite=" + app.globalData.user_id,
            success: function(a) {
                console.log("本地user_id", o.data.user_id);
            },
            fail: function(a) {}
        };
    }
});