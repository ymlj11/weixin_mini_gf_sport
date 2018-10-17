var app = getApp();

Page({
    data: {
        imgUrls: [ "../../resource/images/0180803114113.png", "../../resource/images/0180803114113.png" ],
        circular: !0,
        bushu: ""
    },
    onLoad: function(a) {
        var t = String(a.bushu);
        console.log(t);
        var o = this;
        if ("undefined" == t) {
            t = 0;
            o.setData({
                bushu: t
            });
        } else if (null != t) {
            t = a.bushu;
            o.setData({
                bushu: t
            });
        }
        app.util.request({
            url: "entry/wxapp/Posterlist",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(a) {
                var t = a.data.data;
                o.setData({
                    zong: t
                });
            }
        }), o.Headcolor();
    },
    fan: function() {
        wx.navigateBack({
            delta: 1
        });
    },
    bindchange: function(a) {
        for (var t = this, o = a.detail.current, e = t.data.imgUrls, s = 0; s < e.length; s++) {
            var n = e[o].pic;
            t.setData({
                imgcxs: n
            });
        }
        t.setData({
            tuhight: o
        });
    },
    Create: function() {
        var t = this;
        wx.showLoading({
            title: "图片保存中..."
        }), app.util.request({
            url: "entry/wxapp/Create",
            method: "POST",
            data: {
                user_id: app.globalData.user_id,
                bushu: t.data.bushu
            },
            success: function(a) {
                console.log("第一步成功接口", a), app.util.request({
                    url: "entry/wxapp/Posterurl",
                    method: "POST",
                    data: {
                        user_id: app.globalData.user_id,
                        bushu: t.data.bushu
                    },
                    success: function(a) {
                        var t = a.data.data;
                        wx.downloadFile({
                            url: t,
                            success: function(a) {
                                console.log(a);
                                var t = a.tempFilePath;
                                wx.showToast({
                                    title: "保存成功",
                                    icon: "success",
                                    duration: 2000
                                }), wx.saveImageToPhotosAlbum({
                                    filePath: t,
                                    success: function(a) {
                                        console.log(a);
                                    },
                                    fail: function(a) {}
                                });
                            },
                            fail: function(a) {
                                console.log(a);
                            }
                        });
                    }
                });
            },
            fail: function(a) {
                console.log("第一步失败接口", a);
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
				var i = e.shenhe;
                app.globalData.setaa = a.data.data.set, s.setData({
                    inviteball: t,
                    sonlist: o,
                    setaa: e,
					shenhe: i
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
    onReachBottom: function() {}
});