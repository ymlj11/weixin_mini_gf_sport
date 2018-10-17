var app = getApp();

function countdown(t) {
    var a = t.data.Activitylist.tomorrow.endtime || [], e = new Date().getTime() / 1e3, o = a - (e = parseInt(e)) || [];
    t.setData({
        clock: dateformat(o)
    }), o <= 0 && t.setData({
        clock: "0:0:0"
    }), setTimeout(function() {
        o -= 1, countdown(t);
    }, 1e3);
}

function dateformat(t) {
    var a = Math.floor(t);
    Math.floor(a / 3600 / 24);
    return Math.floor(a / 3600 % 24) + "时" + Math.floor(a / 60 % 60) + "分" + Math.floor(a % 60) + "秒";
}

Page({
    data: {
        txd: [ "3000步", "5000步", "10000步" ],
        moretab: 0,
        countDownHour: 0,
        countDownMinute: 0,
        countDownSecond: 0
    },
    onLoad: function(t) {
        var e = this;
        e.Activity(), e.Shenhe(), e.Headcolor(), app.util.request({
            url: "entry/wxapp/Activitylist",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(t) {
                var a = t.data.data.data;
                e.setData({
                    Activitylist: a
                }), countdown(e);
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
            success: function(t) {
                var a = t.data.data.inviteball, e = t.data.data.sonlist, o = t.data.data.set;
                app.globalData.setaa = t.data.data.set;
                var n = t.data.data.fake, i = o.shenhe;
                s.setData({
                    inviteball: a,
                    sonlist: e,
                    setaa: o,
                    fake: n,
                    shenhe: i
                }), wx.setNavigationBarColor({
                    frontColor: "#ffffff",
                    backgroundColor: o.headcolor
                }), wx.setNavigationBarTitle({
                    title: o.xcx
                });
            },
            fail: function(t) {
                console.log("失败" + t);
            }
        });
    },
    rich: function() {
        wx.navigateTo({
            url: "../rich/rich"
        });
    },
    daoji: function(t) {
        var r = t - Date.parse(new Date()) / 1e3, u = setInterval(function() {
            var t = r, a = Math.floor(t / 3600 / 24), e = a.toString();
            1 == e.length && (e = "0" + e);
            var o = Math.floor((t - 3600 * a * 24) / 3600), n = o.toString();
            1 == n.length && (n = "0" + n);
            var i = Math.floor((t - 3600 * a * 24 - 3600 * o) / 60), s = i.toString();
            1 == s.length && (s = "0" + s);
            var c = (t - 3600 * a * 24 - 3600 * o - 60 * i).toString();
            1 == c.length && (c = "0" + c), this.setData({
                countDownDay: e,
                countDownHour: n,
                countDownMinute: s,
                countDownSecond: c
            }), --r < 0 && (clearInterval(u), wx.showToast({
                title: "活动已结束"
            }), this.setData({
                countDownDay: "00",
                countDownHour: "00",
                countDownMinute: "00",
                countDownSecond: "00"
            }));
        }.bind(this), 1e3);
    },
    Shenhe: function() {
        var e = this;
        app.util.request({
            url: "entry/wxapp/Shenhe",
            method: "POST",
            success: function(t) {
                var a = t.data.data.shenhe;
                e.setData({
                    sh_en: a
                });
            }
        });
    },
    Activity: function() {
        var n = this;
        app.util.request({
            url: "entry/wxapp/Activity",
            method: "POST",
            data: {
                user_id: app.globalData.user_id
            },
            success: function(t) {
                for (var a = t.data.data.activity, e = 0; e < a.length; e++) {
                    var o = a[0].id;
                    n.setData({
                        aid: o
                    }), n.Activitylist(o), n.Updatestep(o);
                }
                n.setData({
                    activity: a
                });
            }
        });
    },
    Activitylist: function(t) {
        var e = this;
        app.util.request({
            url: "entry/wxapp/Activitylist",
            method: "POST",
            data: {
                user_id: app.globalData.user_id,
                aid: t
            },
            success: function(t) {
                var a = t.data.data.data;
                e.setData({
                    Activitylist: a
                });
            }
        });
    },
    qiehuf: function(t) {
        console.log("走接口");
        var a = t.currentTarget.dataset.index, e = t.currentTarget.dataset.aid;
        this.setData({
            moretab: a,
            aid: e
        }), this.Activitylist(this.data.aid);
    },
    challenge: function() {
        wx.navigateTo({
            url: "../challenge/challenge?aid=" + this.data.Activitylist.tomorrow.aid
        });
    },
    Updatestep: function(o) {
        var n = this;
        wx.login({
            success: function(t) {
                var a = t.code;
                n.setData({
                    code: a
                }), wx.getWeRunData({
                    success: function(t) {
                        var a = t.encryptedData, e = t.iv;
                        app.util.request({
                            url: "entry/wxapp/Updatestep",
                            method: "post",
                            dataType: "json",
                            data: {
                                wRunEncryptedData: a,
                                iv: e,
                                code: n.data.code,
                                user_id: app.globalData.user_id,
                                aid: o
                            },
                            success: function(t) {
                                var a = t.data.data;
                                n.setData({
                                    step: a
                                });
                            }
                        });
                    }
                });
            }
        });
    },
    onShow: function() {
        this.Activity(), this.Shenhe(), this.Headcolor();
    },
    onHide: function() {},
    onUnload: function() {},
    onPullDownRefresh: function() {
        var t = this;
        t.Updatestep(t.data.aid), t.Activitylist(t.data.aid), setTimeout(function() {
            wx.stopPullDownRefresh(), wx.showModal({
                title: "提示",
                content: "步数同步成功",
                success: function(t) {
                    t.confirm ? console.log("用户点击确定") : console.log("用户点击取消");
                }
            });
        }, 1e3), t.Headcolor();
    },
    onReachBottom: function() {},
    onShareAppMessage: function() {}
});