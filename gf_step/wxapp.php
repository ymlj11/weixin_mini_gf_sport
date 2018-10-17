<?php
/**
 * 小程序接口定义
 *
 * @author gf
 * @url http://bbs.we7.cc
 */

defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT."/addons/gf_step/inc/model.class.php";
require_once IA_ROOT."/addons/gf_step/wxBizDataCrypt.php";
class Gf_stepModuleWxapp extends WeModuleWxapp {
	
	public $table_bushuday = "gfstep_bushuday";
	
    public function doPageTest(){
        ob_end_clean();
        global $_GPC, $_W;
        $post_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->wx_get_token();
        $post_data = '';

        $json = $this->api_notice_increment($post_url,$post_data);
        $json=json_decode($json,true);
        return $this->result(0, '获取成功',$json);
    }
	
    public function get_url_content($url){
        $user_agent = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0)";
		$header = array(
			'Content-Type' => 'application/json'
		);
        //$data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function doPageGetopenid(){
        ob_end_clean();
        global $_GPC, $_W;
        $code = $_GPC['code'];
        $account = pdo_get('account_wxapp', array('uniacid' => $_GPC['i']));
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $account['key'] . '&secret=' . $account['secret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $result = $this->get_url_content($url);
        $result = json_decode($result, true);
        return $this->result(0, '获取成功', $result);
    }
    

    public function doPageZhuce(){
        ob_end_clean();
        global $_GPC, $_W;
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        $data['uniacid'] = $_GPC['i'];
        $data['city'] = $_POST['city'];
        $data['country'] = $_POST['country'];
        $data['gender'] = $_POST['gender'];
        $data['open_id'] = $_POST['open_id'];
        $data['nick_name'] = $_POST['nickName'];
        $data['head_pic'] = $_POST['avatarUrl'];
        $data['province'] = $_POST['province'];
		$data['createtime'] = TIMESTAMP;

        $stact = 1;
        if(empty($data['open_id'])){
            $this->result(1, $stact, '');
        }
        $uid = pdo_getcolumn('hcstep_users', array('open_id' => $data['open_id'], 'uniacid' => $data['uniacid']), 'user_id', 1);
        if (empty($uid)) {
            $stact = 0;
            //记录父id
            $data['fatherid'] = $_POST['invite'];                  
            $result = pdo_insert('hcstep_users', $data, $replace = true);
			file_put_contents(MODULE_ROOT . '/temp/pdo_insert-id.txt', $result);	// 测试
            $uid = pdo_insertid();
            //添加一条邀请记录
            $log['uniacid'] = $_GPC['i'];
            $log['user_id'] = $data['fatherid'];
            $log['sonid'] = $uid;
            $log['step'] = $set['sharestep'];
            $log['invite_time'] = time();
            $log['time'] = date('Y-m-d',time());
            $log['status'] = 0;
            if($log['user_id'] > 0){
                $invitelog = pdo_insert('hcstep_invitelog', $log, $replace = true);
            }
            
        } else {
            $result = pdo_update('hcstep_users', $data, array('user_id' => $uid));
        }
        $this->result(0, $stact, $uid);
    }

    //是否拉黑
    public function doPageBlack(){
        global $_GPC, $_W;       
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));              
        return $this->result(0, '是否拉黑',$user['black']);
    }

    //签到
    public function doPageSignin(){
        
        global $_GPC, $_W;        
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        /**如果有就进行判断时间差，然后处理签到次数*/
        /**昨天的时间戳时间范围*/
        $t = time();
        $last_start_time = mktime(0,0,0,date("m",$t),date("d",$t)-1,date("Y",$t));
        $last_end_time = mktime(23,59,59,date("m",$t),date("d",$t)-1,date("Y",$t));
        /**今天的时间戳时间范围*/
        $now_start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $now_end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        /**判断最后一次签到时间是否在昨天的时间范围内*/
        if($last_start_time<$user['lasttime']&&$user['lasttime']<$last_end_time){
            $user['lasttime'] = time();
            $user['signtime'] = $user['signtime'] + 1;
            //$user['money'] = $user['money'] + 2;
            /**这里还可以加一些判断连续签到几天然后加积分等等的操作*/
            $result = pdo_update('hcstep_users',$user,array('user_id' => $_GPC['user_id']));
            return $this->result(0, '获取成功',$result);
        }elseif($now_start_time<$user['lasttime']&&$user['lasttime']<$now_end_time){
            /**返回已经签到的操作*/
            $user['lasttime'] = time();
            //$user['signtime'] = 1;
            $result = pdo_update('hcstep_users',$user,array('user_id' => $_GPC['user_id']));
            return $this->result(0, '获取成功',$result);
        }else{
            $user['lasttime'] = time();
            $user['signtime'] = 1;
            $result = pdo_update('hcstep_users',$user,array('user_id' => $_GPC['user_id']));
            return $this->result(0, '获取成功',$result);
        }
        
    }

    //签到分享
    public function doPageSignshare(){
        global $_GPC, $_W;        
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        /**如果有就进行判断时间差，然后处理签到次数*/
        /**昨天的时间戳时间范围*/
        $t = time();
        //$last_start_time = mktime(0,0,0,date("m",$t),date("d",$t)-1,date("Y",$t));
        //$last_end_time = mktime(23,59,59,date("m",$t),date("d",$t)-1,date("Y",$t));
        /**今天的时间戳时间范围*/
         $now_start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
         $now_end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        /**判断最后一次签到时间是否在昨天的时间范围内*/
        if($now_start_time<$user['sharetime']&&$user['sharetime']<$now_end_time){
            /**这里还可以加一些判断连续签到几天然后加积分等等的操作*/
            return $this->result(0, '今天已经分享过',$user);
        }else{
            /**返回已经签到的操作*/
            $user['sharetime'] = time();
            $user['money'] = $user['money'] + $set['signsharemoney'];
            $result = pdo_update('hcstep_users',$user,array('user_id' => $_GPC['user_id']));
            return $this->result(0, '分享成功',$result);
        }
    }

    //审核状态
    public function doPageStatus(){
        global $_GPC, $_W;
        $config = pdo_get('hczhongzhuan_set',array('uniacid'=>$_GPC['i']),array('status'));       
         
        return $this->result(0, '审核状态',$config);
    }
    //页面
    /*public function doPageShenhe(){
        global $_GPC, $_W;
        $shenhe = pdo_getall('hczhongzhuan_shenhe', array('stact'=>1,'uniacid' => $_GPC['i']), array(),'','sort asc');
        foreach ($shenhe as $key => $val) {
            $shenhe[$key]['icon'] = $_W['attachurl'].$val['img'];
        }      
         
        ob_end_clean();
        global $_GPC, $_W;
        $shenhe=pdo_get('hczhongzhuan_shenhe', array('id' => $_GPC['id']));
        $this->result(0, '获取成功', $shenhe);
        //return $this->result(0, '页面',$shenhe);
    }*/
    
    function api_notice_increment($url, $data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function doPagebushu(){
        global $_GPC, $_W;
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));

        $code = $_GPC['code'];
        $account = pdo_get('account_wxapp', array('uniacid' => $_GPC['i']));
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $account['key'] . '&secret=' . $account['secret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $result = $this->get_url_content($url);
        $result = json_decode($result, true);

        $uid           	= $_GPC['user_id'];
		$uniacid		= $_GPC['i'];
        $encryptedData 	= $_GPC['wRunEncryptedData'];
        $iv            	= $_GPC['iv'];
        $sessionKey    	= $result['session_key'];
        $appid = $_W['account']['key'];
        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode == 0) {
            $bushu = json_decode($data,'true');
            $bushu = $bushu['stepInfoList'][30]['step'];

            $weixinbushu = $bushu;	// 微信运动步数
			
			$time=date('Y-m-d',	TIMESTAMP);
			
			// TODO: save to db temp by gf 20180909
			// ALTER TABLE `ims_hcstep_users` ADD `bushu_weixin` INT NOT NULL COMMENT '今日步数' AFTER `sharetime`;			
			pdo_update('hcstep_users',array('bushu_weixin' => $weixinbushu), array('user_id' => $uid));
            
			// create table `ims_gfstep_bushuday`
            $bushu_today = pdo_fetch("select id, user_id, bushu, day from " . tablename($this->table_bushuday) . " where user_id = :user_id and day = :day", array(':user_id' => $uid, ':day' => $time));
			if(empty($bushu_today)){
				pdo_insert($this->table_bushuday, array('uniacid' => $uniacid, 'user_id' => $uid, 'bushu' => $bushu, 'day' => $time));
			}else{
				pdo_update($this->table_bushuday, array('bushu' => $bushu), array('id' => $bushu_today['id']));
			}
			
			// end by
			
            //兑换记录
            $log = pdo_getall('hcstep_bushulog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i'],'time'=>$time));
            $zong = array_sum(array_column($log,'bushu'));
            //邀请记录
            $invitelog = pdo_getall('hcstep_invitelog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i'],'status'=>1,'time'=>$time));
            $invitezong = array_sum(array_column($invitelog,'step'));
            //步数加成比例
            $invitelogall = pdo_getall('hcstep_invitelog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i']));
            $invitelogtoday = pdo_getall('hcstep_invitelog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i'],'time'=>$time));
            $count = count($invitelogall) - count($invitelogtoday);
            //步数加成
            $uplog = pdo_getall('hcstep_uplog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i'],'day'=>$time));
            $upzong = array_sum(array_column($uplog,'step'));
            $upbushu = $bushu * $count*$set['up']/100 - $upzong;
            $upbushu = floor($upbushu);
            //关注送步数
            $guanzhu = pdo_get('hcstep_guanzhulog',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id'],'status'=>1));

            $bushu = $bushu + $invitezong + $upzong + $guanzhu['step'] - $zong ;
             
            return $this->result(0, '操作成功',array('bushu'=>$bushu,'user'=>$user,'upbushu'=>$upbushu,'weixinbushu'=>$weixinbushu));
        } else {
            return $this->result(1, array('errcode'=>$errCode,'appid'=>$appid));
        }

    }

    public function doPagebushu2money(){
        
        global $_GPC, $_W;
        
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        $time=date('Y-m-d',time());
        $log = pdo_getall('hcstep_bushulog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i'],'time'=>$time));
        $zong = array_sum(array_column($log,'bushu'));
       
        $data['bushu'] = $_GPC['step'];
        $data['user_id'] = $_GPC['user_id'];
        $money = $data['bushu']*$set['rate'];

        if($data['bushu'] == 0){
           return $this->result(1,$set['zerotip']);
        }elseif($zong >= $set['maxstep'] and !empty($zong)){
           return $this->result(1,'今日已达到步数兑换上限，请明日再来吧');
        }else{
           if(($data['bushu'] +$zong) > $set['maxstep']){
             $money = ($set['maxstep'] - $zong)*$set['rate'];
             return $this->result(0,'',$money);
           }else{
             return $this->result(0,'',$money);
           }         
        }      
    }

    public function doPagebushulog(){
        global $_GPC, $_W;
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        
        $time=date('Y-m-d',time());
        $log = pdo_getall('hcstep_bushulog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i'],'time'=>$time));
        $zong = array_sum(array_column($log,'bushu'));
        
        $data['bushu'] = $_GPC['step'];
        $data['user_id'] = $_GPC['user_id'];
        $data['uniacid'] = $_GPC['i'];
        $data['timestamp'] = time();
        $data['time'] = date('Y-m-d',time());
        $data['money'] = $data['bushu']*$set['rate'];

        if($data['bushu'] == 0){
           return $this->result(1,$set['zerotip']);
        }elseif(($data['bushu'] + $zong) >= $set['maxstep']){
            $data['bushu'] = $set['maxstep'] - $zong;
            $data['user_id'] = $_GPC['user_id'];
            $data['uniacid'] = $_GPC['i'];
            $data['timestamp'] = time();
            $data['time'] = date('Y-m-d',time());
            $data['money'] = $data['bushu']*$set['rate'];

            $result = pdo_insert('hcstep_bushulog',$data);

            $nowmoney = $user['money']+$data['money'];

            pdo_update('hcstep_users',array('money'=>$nowmoney), array('user_id' => $_GPC['user_id']));  

            return $this->result(0, '操作成功',$result);         
        }else{
           $result = pdo_insert('hcstep_bushulog',$data);

           $nowmoney = $user['money']+$data['money'];

           pdo_update('hcstep_users',array('money'=>$nowmoney), array('user_id' => $_GPC['user_id']));  

           return $this->result(0, '操作成功',$result);
        }

    }

    public function doPageHeadcolor(){
        global $_GPC, $_W;
        
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        $user['stepid'] = $user['user_id'] + 100000;

        $set['sharepic'] = $_W['attachurl'].$set['sharepic'];
        $set['rulepic'] = $_W['attachurl'].$set['rulepic'];
        $set['voice'] = $_W['siteroot']."addons/gf_step/coin.mp3";
        $set['no'] = $_W['siteroot']."addons/gf_step/no.png";
        $set['no2'] = $_W['siteroot']."addons/gf_step/no2.png";
        
        if(!empty($set['loginpic'])){
            $set['loginpic'] = $_W['attachurl'].$set['loginpic']; 
        }else{
            $set['loginpic'] = '';
        }
        if(!empty($set['indexbg'])){
            $set['indexbg'] = $_W['attachurl'].$set['indexbg']; 
        }else{
            $set['indexbg'] = '';
        }
        if(!empty($set['indexbutton'])){
            $set['indexbutton'] = $_W['attachurl'].$set['indexbutton']; 
        }else{
            $set['indexbutton'] = '';
        }
        if(!empty($set['inviteball'])){
            $set['inviteball'] = $_W['attachurl'].$set['inviteball']; 
        }else{
            $set['inviteball'] = '';
        }
        if(!empty($set['upball'])){
            $set['upball'] = $_W['attachurl'].$set['upball']; 
        }else{
            $set['upball'] = '';
        }
        if(!empty($set['questionpic'])){
            $set['questionpic'] = $_W['attachurl'].$set['questionpic']; 
        }else{
            $set['questionpic'] = '';
        }
        if(!empty($set['followpic'])){
            $set['followpic'] = $_W['attachurl'].$set['followpic']; 
        }else{
            $set['followpic'] = '';
        }
        if(!empty($set['kefupic'])){
            $set['kefupic'] = $_W['attachurl'].$set['kefupic']; 
        }else{
            $set['kefupic'] = '';
        }
        if(!empty($set['followlogo'])){
            $set['followlogo'] = $_W['attachurl'].$set['followlogo']; 
        }else{
            $set['followlogo'] = '';
        }
        if(!empty($set['boxpic'])){
            $set['boxpic'] = $_W['attachurl'].$set['boxpic']; 
        }else{
            $set['boxpic'] = '';
        }
        if(!empty($set['activitypic'])){
            $set['activitypic'] = $_W['attachurl'].$set['activitypic']; 
        }else{
            $set['activitypic'] = '';
        }
        if(!empty($set['applypic'])){
            $set['applypic'] = $_W['attachurl'].$set['applypic']; 
        }else{
            $set['applypic'] = '';
        }
        if(!empty($set['icon'])){
            $set['icon'] = $_W['attachurl'].$set['icon']; 
        }else{
            $set['icon'] = '';
        }
        if(!empty($set['signpic'])){
            $set['signpic'] = $_W['attachurl'].$set['signpic']; 
        }else{
            $set['signpic'] = '';
        }
        if(!empty($set['frame'])){
            $set['frame'] = $_W['attachurl'].$set['frame']; 
        }else{
            $set['frame'] = '';
        }
        if(!empty($set['signicon'])){
            $set['signicon'] = $_W['attachurl'].$set['signicon']; 
        }else{
            $set['signicon'] = '';
        }
        if(!empty($set['buttonbg'])){
            $set['buttonbg'] = $_W['attachurl'].$set['buttonbg']; 
        }else{
            $set['buttonbg'] = '';
        }
        if(!empty($set['coinpic'])){
            $set['coinpic'] = $_W['attachurl'].$set['coinpic']; 
        }else{
            $set['coinpic'] = '';
        }


        if($_GPC['user_id']){

            $inviteball = pdo_getall('hcstep_invitelog',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id'],'status'=>0));
            //$sonlist = pdo_getall('hcstep_users',array('uniacid'=>$_GPC['i'],'fatherid'=>$_GPC['user_id'],'fatherid !='=>0));
			$sql = "select user_id, nick_name, head_pic, `money`, bushu_weixin from " . tablename("hcstep_users") . " where uniacid = :uniacid and fatherid =:fatherid order by `money` desc";
			$sonlist = pdo_fetchall($sql, array('uniacid'=>$_GPC['i'],'fatherid'=>$_GPC['user_id']));	// by gf 20180909
            //步数加成
            $time=date('Y-m-d',time());
            $invitelogall = pdo_getall('hcstep_invitelog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i']));
            $invitelogtoday = pdo_getall('hcstep_invitelog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i'],'time'=>$time));
            $count = count($invitelogall) - count($invitelogtoday);
            $tcount = count($invitelogall);
            //是否显示关注气泡
            $guanzhu = pdo_get('hcstep_guanzhulog',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
            if($guanzhu['status'] == 0 && !empty($guanzhu)){
                $set['is_guanzhujiang'] = 1; //显示气泡
            }else{
                $set['is_guanzhujiang'] = 0; //不显示气泡
            }

        }
        
        $set['todayup'] = $set['up']*$count;
        $set['tomorrow'] = $set['up']*$tcount;
        $set['adunit'] = $set['adunit'];
        $set['adunit2'] = $set['adunit2'];
        $set['adunit3'] = $set['adunit3'];

        //骗审数据
        //$fake = pdo_getll('hcstep_users',array('uniacid'=>$_GPC['i']));
        $fake = pdo_getslice('hcstep_users', array('uniacid'=>$_GPC['i']), array(0, 10), $total);
        $fake[0]['step'] = 23845;
        $fake[1]['step'] = 20667;
        $fake[2]['step'] = 18652;
        $fake[3]['step'] = 16632;
        $fake[4]['step'] = 12420;
        $fake[5]['step'] = 10248;
        $fake[6]['step'] = 8649;
        $fake[7]['step'] = 7456;
        $fake[8]['step'] = 6324;
        $fake[9]['step'] = 4396;

        

        return $this->result(0,'操作成功',array('inviteball'=>$inviteball,'sonlist'=>$sonlist,'user'=>$user,'set'=>$set,'fake'=>$fake));

    }

    //签到分享状态
    public function doPageIssignshare(){
        
        global $_GPC, $_W;
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        //判断今日签到是否分享过
        $t = time();
        /**今天的时间戳时间范围*/
        $now_start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $now_end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t));
        /**判断最后一次签到时间是否在昨天的时间范围内*/
        if($now_start_time<$user['sharetime']&&$user['sharetime']<$now_end_time){
            /**这里还可以加一些判断连续签到几天然后加积分等等的操作*/
            $set['is_signshare'] = 1;//今日已分享
        }else{
            $set['is_signshare'] = 0;//今日未分享
        }

        return $this->result(0,'操作成功',array('set'=>$set,'user'=>$user));
    }

    //商品列表
    public function doPageGoodslist(){

        global $_GPC, $_W;      
        $list = pdo_getall('hcstep_goods', array('status' => 1,'uniacid'=>$_GPC['i']), array(),'','displayorder asc');
        foreach ($list as $k => $v) {
            $list[$k]['main_img'] = $_W['attachurl'].$v['main_img'];
        }
        return $this->result(0, '商品列表',$list);

    }

    //商品详情
    public function doPageGoodsdetail(){

        global $_GPC, $_W;
		$id = intval($_GPC['goods_id']);
		
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        if($_GPC['index'] == 1){
			
			pdo_query("update " . tablename('hcstep_goods') . " set views = views +1 where id=" . $id);
			
            $goods = pdo_get('hcstep_goods', array('id'=>$id, 'uniacid'=>$_GPC['i']));
            $goods['goods_img'] = json_decode($goods['goods_img']);

            if($goods['inventory'] <= 0){
                return $this->result(1, '已经抢光了',$goods);
            }

            if(!empty($goods['goods_img'])){
                foreach ($goods['goods_img'] as $k => $v) {
                   $goods['goods_img'][$k] = $_W['attachurl'].$v;
                }
            }
            
            $goods['usermoney'] = $user['money'];

            return $this->result(0, '商品详情',$goods);
        }else{
            $goods = pdo_get('hcstep_awards',array('id'=>$id,'uniacid'=>$_GPC['i']));
            $goods['goods_img'] = json_decode($goods['goods_img']);

            if($goods['inventory'] <= 0){
                return $this->result(1, '对不起已经兑换完了',$goods);
            }

            if(!empty($goods['goods_img'])){
                foreach ($goods['goods_img'] as $k => $v) {
                   $goods['goods_img'][$k] = $_W['attachurl'].$v;
                }
            }
            
            $goods['usermoney'] = $user['money'];

            return $this->result(0, '商品详情',$goods);
        }
    }

    //奖品列表
    public function doPageAwardslist(){

        global $_GPC, $_W;      
        $list = pdo_getall('hcstep_awards', array('uniacid'=>$_GPC['i']));
        foreach ($list as $k => $v) {
            $list[$k]['main_img'] = $_W['attachurl'].$v['main_img'];
        }
        return $this->result(0, '商品列表',$list);

    }

    //商品列表
    public function doPageCreateorder(){

        global $_GPC, $_W; 

        $goods = pdo_get('hcstep_goods',array('id'=>$_GPC['goods_id'],'uniacid'=>$_GPC['i']));
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));   
        
        $data['uniacid'] = $_GPC['i'];
        $data['userName'] = $_GPC['userName'];
        $data['postalCode'] = $_GPC['postalCode'];
        $data['provinceName'] = $_GPC['provinceName'];
        $data['cityName'] = $_GPC['cityName'];
        $data['countyName'] = $_GPC['countyName'];
        $data['detailInfo'] = $_GPC['detailInfo'];
        $data['nationalCode'] = $_GPC['nationalCode'];
        $data['telNumber'] = $_GPC['telNumber'];
        $data['user_id'] = $_GPC['user_id'];
        $data['goods_id'] = $_GPC['goods_id'];
        $data['time'] = time();
       
        if($user['money'] < $goods['price']){
            return $this->result(1, '余额不足',$user);
        }else{
            $order = pdo_insert('hcstep_orders', $data, $replace = true);
            $nowmoney = $user['money'] - $goods['price'];
            pdo_update('hcstep_users',array('money'=>$nowmoney), array('user_id' => $_GPC['user_id']));
            $nowinventory = $goods['inventory'] - 1;
            pdo_update('hcstep_goods',array('inventory'=>$nowinventory), array('id' => $_GPC['goods_id']));
            if($order){
               return $this->result(0, '兑换成功',$order); 
            }else{
               return $this->result(1, '兑换失败',$order);
            }
        }
    }
	
    //点击邀请气泡换步数
    public function doPageBall2bushu(){
        global $_GPC, $_W;

        $ballid = $_GPC['id'];
        $result = pdo_update('hcstep_invitelog',array('status'=>1), array('id' => $ballid));
        if($result){
           return $this->result(0, '获取成功',$result); 
        }else{
           return $this->result(1, '获取失败',$result);
        }
    }

    //点击加成气泡换步数
    public function doPageUpball2bushu(){
        global $_GPC, $_W;

        $data['uniacid'] = $_GPC['i'];
        $data['user_id'] = $_GPC['user_id'];
        $data['step'] = $_GPC['upbushu'];
        $data['time'] = time();
        $data['day'] = date('Y-m-d',time());
   
        $result = pdo_insert('hcstep_uplog', $data, $replace = true);
        if($result){
           return $this->result(0, '获取成功',$result); 
        }else{
           return $this->result(1, '获取失败',$result);
        }
    }

    //点击关注气泡换步数
    public function doPageGuanzhuball2bushu(){
        global $_GPC, $_W;

        $data['uniacid'] = $_GPC['i'];
        $data['user_id'] = $_GPC['user_id'];
        $result = pdo_update('hcstep_guanzhulog',array('status'=>1), array('user_id' => $_GPC['user_id'],'uniacid'=>$_GPC['i']));

        if($result){
           return $this->result(0, '获取成功',$result); 
        }else{
           return $this->result(1, '获取失败',$result);
        }
    }

    //点击邀请气泡换步数
    public function doPageYy(){
        global $_GPC, $_W;
        $result = pdo_update('hcstep_users',array('is_yy'=>1), array('user_id' =>$_GPC['user_id']));
        if($result){
           return $this->result(0, '获取成功',$result); 
        }else{
           return $this->result(1, '获取失败',$result);
        }
    }

    //抽奖
    public function doPageLotto(){
        
        global $_GPC, $_W;
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        if($user['money'] < $set['boxprice']){
            return $this->result(1, $set['coinname'].'不足',$user);
        }else{
            $list = pdo_getall('hcstep_awards', array('uniacid'=>$_GPC['i'],'status'=>1));
            if($list){
               $idlist = array_column($list,'id');
               $awardsid = array_rand($idlist,1);

               $data['uniacid'] = $_GPC['i'];
               $data['user_id'] = $_GPC['user_id'];
               $data['goods_id'] = $idlist[$awardsid];
               $data['time'] =time();
               $result = pdo_insert('hcstep_winlog', $data, $replace = true);
               
               $nowmoney = $user['money'] - $set['boxprice'];
               pdo_update('hcstep_users',array('money'=>$nowmoney), array('user_id' => $_GPC['user_id']));

               $goods = pdo_get('hcstep_awards',array('id'=>$data['goods_id'],'uniacid'=>$_GPC['i']));
               $tip = "恭喜您抽中了".$goods['goods_name'].",请到个人中心中奖纪录完善收货信息。";
               return $this->result(0,$tip,$goods['goods_name']); 
            }else{
               $nowmoney = $user['money'] - $set['boxprice'];
               pdo_update('hcstep_users',array('money'=>$nowmoney), array('user_id' => $_GPC['user_id']));
               return $this->result(1, '本次未抽中，请继续努力',$list);
            }
        }       

    }

    //完善中奖订单信息
    public function doPageEditwinlog(){

        global $_GPC, $_W;    
        
        $data['userName'] = $_GPC['userName'];
        $data['postalCode'] = $_GPC['postalCode'];
        $data['provinceName'] = $_GPC['provinceName'];
        $data['cityName'] = $_GPC['cityName'];
        $data['countyName'] = $_GPC['countyName'];
        $data['detailInfo'] = $_GPC['detailInfo'];
        $data['nationalCode'] = $_GPC['nationalCode'];
        $data['telNumber'] = $_GPC['telNumber'];

        $order = pdo_update('hcstep_winlog', $data, array('id' =>$_GPC['id']));


        if($order){
           return $this->result(0,'修改成功',$order); 
        }else{
           return $this->result(1,'修改失败',$order);
        }

    }

    //兑换记录
    public function doPageLog(){
       
        global $_GPC, $_W;

        $qie = $_GPC['qie'];
        //燃力币兑换记录
        if($qie == 0){
           $coinlog = pdo_getall('hcstep_bushulog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i']));
            foreach ($coinlog as $k => $v) {
                $coinlog[$k]['timestamp'] = date('Y-m-d H:i:s',$v['timestamp']);
            } 
        }
        //商品兑换记录
        if($qie == 1){
            $goodslog = pdo_getall('hcstep_orders',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i']));
            foreach ($goodslog as $k => $v) {
                $goods = pdo_get('hcstep_goods',array('id'=>$v['goods_id'],'uniacid'=>$_GPC['i']));
                $goodslog[$k]['goods_name'] = $goods['goods_name'];
                $goodslog[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
            } 
        }

        //奖品兑换记录
        if($qie == 2){
            $awardslog = pdo_getall('hcstep_winlog',array('user_id'=>$_GPC['user_id'],'uniacid'=>$_GPC['i']));
            foreach ($awardslog as $k => $v) {
                $goods = pdo_get('hcstep_awards',array('id'=>$v['goods_id'],'uniacid'=>$_GPC['i']));
                $awardslog[$k]['main_img'] = $_W['attachurl'].$goods['main_img'];
                $awardslog[$k]['goods_name'] = $goods['goods_name'];
                $awardslog[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
                
                if($awardslog[$k]['status'] == 0){
                    $awardslog[$k]['zhuangtai'] = "待发货";
                }else{
                    $awardslog[$k]['zhuangtai'] = "已发货";
                }
                if($awardslog[$k]['userName']){
                    $awardslog[$k]['is_dizhi'] = 1;
                }else{
                    $awardslog[$k]['is_dizhi'] = 0;
                }
            } 
        }

        return $this->result(0, '获取成功',array('coinlog'=>$coinlog,'goodslog'=>$goodslog,'awardslog'=>$awardslog));
    }

    //问题列表
    public function doPageQuestion(){

        global $_GPC, $_W;
        $question = pdo_getall('hcstep_question', array('enabled' => 1,'uniacid'=>$_GPC['i']));
        
        foreach ($question as $k => $v) {
            $question[$k]['createtime'] = date('Y-m-d',$v['createtime']);
        }
        
        return $this->result(0,'问题',$question);

    }
    public function doPagePosterlist(){
        global $_GPC, $_W;
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i'])); 

        if(!empty($set['posterpic'])){
            $data['url'] = $_W['attachurl'].$set['posterpic']; 
        }else{
            $data['url'] = $_W['siteroot']."addons/gf_step/tu1.jpg";
        }   
        
        $data['text'] = $set['comeon']; 
        $data['nick_name'] = $user['nick_name'];
        $data['head_pic'] = $user['head_pic'];
        $data['time'] = date('Y年m月d日',time());

        /*$accessTokenObject = json_decode(file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$_W['account']['key'].'&secret='.$_W['account']['secret']));
        //二维码接口
        //$url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$accessTokenObject->access_token;
        //菊花码
        $url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$accessTokenObject->access_token;
        $width = '430';
        $path = '/gf_step/pages/index/index?invite='.$user_id;
        $json='{"path":"'.$path.'","width":'.$width.'}';
        //$json = '{"scene": "/user_id/'.$user_id.'", "width": 50, "page": ""}';
        //$data=$this->api_notice_increment($url,$json);
        $aaa=$this->request_post($url,$json);*/

        $data['qrcode'] = $this->GetQrcode($_GPC['user_id']);
        $data['xcx'] = $set['xcx'];
        /*$data['1'] =  $accessTokenObject;
        $data['2'] =  $url;
        $data['3'] =  $aaa;*/   
        
        return $this->result(0,'海报',$data);
    }

    public function doPagePosterurl(){
        global $_GPC, $_W;    
        $url = $_W['siteroot']."addons/gf_step/poster.jpg";       
        
        return $this->result(0,'海报',$url);
    }

    public function doPageCreate(){
        ob_end_clean();
        ob_clean();
        global $_GPC, $_W;   
        $res = $this->createPoster($_GPC['bushu'],$_GPC['user_id']);
    }

    // 生成海报
    private function createPoster($bushu,$user_id){
        ob_end_clean();
        ob_clean();
        global $_W,$_GPC;
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i'])); 
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$user_id));
        if(!empty($set['posterpic'])){
            $bg_path = $_W['attachurl'].$set['posterpic']; 
        }else{
            $bg_path = $_W['siteroot']."addons/gf_step/tu1.jpg";//自定义背景
        }               
        $dst_path = $_W['siteroot']."addons/gf_step/white.png";//背景图
        $hp_path = $this->download_remote_pic($user_id, $user['head_pic']);
        $qr_path = $this->GetQrcode($user_id);//小程序码
        //创建图片的实例
        $dst = imagecreatefromstring(file_get_contents($dst_path));
        $qr = imagecreatefromstring(file_get_contents($qr_path));
        $hp = imagecreatefromstring(file_get_contents($hp_path));
        $bg = imagecreatefromstring(file_get_contents($bg_path)); 
        //打上文字
        $font = '../addons/gf_step/PingFang.ttf';//字体路径
        $boldfont = '../addons/gf_step/Bold.ttf';//粗字体路径
        //echo $font;
        $black = imagecolorallocate($dst, 0x00, 0x00, 0x00);//字体颜色
        $red = imagecolorallocate($dst,224,47,37);//券后价字体颜色
        $white = imagecolorallocate($dst,255,255,255);//优惠券颜色
        $hui = imagecolorallocate($dst,126,126,126);//原价颜色      
        
        //印上背景图
        list($bg_w, $bg_h) = getimagesize($bg_path);
        $bgnew_x = 689;
        $bgnew_y = 772;
        $image_bg = imagecreatetruecolor($bgnew_x,$bgnew_y); //设置缩略图
        imagecopyresampled($image_bg, $bg, 0, 0, 0, 0, $bgnew_x, $bgnew_y, $bg_w, $bg_h);
        imagecopymerge($dst,$image_bg,0,0,0,0,$bgnew_x,$bgnew_y,100);
        //印上二维码
        list($qr_w, $qr_h) = getimagesize($qr_path);
        $new_x = 115;
        $new_y = 115;
        $image_p = imagecreatetruecolor($new_x, $new_y); //设置缩略图
        imagecopyresampled($image_p, $qr, 0, 0, 0, 0, $new_x, $new_y, $qr_w, $qr_h);
        imagecopymerge($dst,$image_p,560,773,0,0,$new_x,$new_y,100);
        //印上头像
        list($hp_w, $hp_h) = getimagesize($hp_path);
        $hnew_x = 110;
        $hnew_y = 110;
        $image_hp = imagecreatetruecolor($hnew_x,$hnew_y); //设置缩略图
        imagecopyresampled($image_hp, $hp, 0, 0, 0, 0, $hnew_x, $hnew_y, $hp_w, $hp_h);
        imagecopymerge($dst,$image_hp,17,637,0,0,$hnew_x,$hnew_y,100);
        //打上文字
        $nickname =$user['nick_name'];
        $text =$set['comeon'];
        $date = date('Y年m月d日',time());
        $wname = $set['xcx'];
        $long = strlen($bushu);
        $right = 25 + 43*$long;
        imagefttext($dst,18,0,137,670,$white,$boldfont,$nickname);//昵称
        imagefttext($dst,18,0,137,700,$white,$font,'——————————————————————————————————————————');//昵称
        imagefttext($dst,30,0,137,730,$white,$boldfont,$text);//格言
        imagefttext($dst,15,0,480,25,$white,$boldfont,$date);//日期
        imagefttext($dst,50,0,25,850,$black,$boldfont,$bushu);//步数
        imagefttext($dst,20,0,$right,850,$black,$boldfont,"步");//步数
        imagefttext($dst,23,0,380,830,$black,$font,$wname);//步数
        imagefttext($dst,15,0,380,860,$hui,$boldfont,'长按识别小程序');//步数
        //输出图片
       /* list($dst_w, $dst_h, $dst_type) = getimagesize($dst_path);
        switch ($dst_type) {
        case 1://GIF
           //header('Content-Type: image/gif');
           imagegif($dst);
           break;
        case 2://JPG
           //header('Content-Type: image/jpeg');
           imagejpeg($dst);
           break;
        case 3://PNG
           //header('Content-Type: image/png');          
           imagepng($dst);
           break;
        default:
           break;
        }*/

        $filename = dirname(__FILE__)."/poster.jpg";
        imagepng($dst,$filename);
        imagedestroy($dst);

    }
    //生成小程序码
    private function GetQrcode($user_id){
        global $_GPC, $_W;
		
		// by gf 20180909
		if(file_exists($_W['siteroot']."addons/gf_step/erweima.png")){
			return $_W['siteroot']."addons/gf_step/erweima.png";
		}
		// end by
		
        // 获取access_token
        //$accessTokenObject = json_decode(file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$_W['account']['key'].'&secret='.$_W['account']['secret']));
        $account_api = WeAccount::create();
        $token = $account_api->getAccessToken();
        //菊花码
        //$url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$accessTokenObject->access_token;
        $url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$token;
        $width = '430';
        $path = '/gf_step/pages/index/index?invite='.$user_id;
        $json='{"path":"'.$path.'","width":'.$width.'}';
        //$json = '{"scene": "/user_id/'.$user_id.'", "width": 50, "page": ""}';
        //$data=$this->api_notice_increment($url,$json);
        $data=$this->request_post($url,$json);
        $filename = dirname(__FILE__)."/erweima.png";
        $local_file = fopen($filename, 'w');
        if (false !== $local_file) {
            if (false !== fwrite($local_file, $data)) {
                fclose($local_file);
            }
        }
        //file_put_contents($filename,$data);
        return $_W['siteroot']."addons/gf_step/erweima.png";
    }
    //消息客服
    public function doPageKefu()
    {
        ob_end_clean();
        global $_GPC, $_W;

        $user_id = $_GPC['user_id'];
        $users=pdo_get('hcstep_users', array('user_id' => $user_id));
        $setup=pdo_get('hcstep_set', array('uniacid' => $_GPC['i']));
        $post_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->wx_get_token();
        $post_data = json_encode(array(
            'touser' =>$users['open_id'],
            'msgtype' => 'link',
            'link' => array(
                'title'=>$setup['kefu_title'],
                'description'=>$setup['kefu_gaishu'],
                'url'=>$setup['kefu_url'],
                'thumb_url'=>$_W['attachurl'].$setup['kefu_img'],
            ),
        ), JSON_UNESCAPED_UNICODE);
        $json = $this->api_notice_increment($post_url,$post_data);
        $arr = json_decode($json,true);
        if($arr['errcode']!=0){
            $this->kefu($user_id);
        }
        $myfile = fopen(IA_ROOT."/addons/gf_step/kefu.txt", 'w');
        fwrite($myfile, $json);
        fclose($myfile);
        $json=json_decode($json,true);
        return $this->result(0, '获取成功',$json);
    }


    public function kefu($user_id)
    {
        ob_end_clean();
        global $_GPC, $_W;
        $users=pdo_get('hcstep_users', array('user_id' => $user_id));
        $setup=pdo_get('hcstep_set', array('uniacid' => $users['uniacid']));
        $post_url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$this->wx_get_token();
        $post_data = json_encode(array(
            'touser' =>$users['open_id'],
            'msgtype' => 'link',
            'link' => array(
                'title'=>$setup['kefu_title'],
                'description'=>$setup['kefu_gaishu'],
                'url'=>$setup['kefu_url'],
                'thumb_url'=>$_W['attachurl'].$setup['kefu_img'],
            ),
        ), JSON_UNESCAPED_UNICODE);

        $json = $this->api_notice_increment($post_url,$post_data);
        $json=json_decode($json,true);
        if($json['errcode']!=0){
            $this->kefu($user_id);
        }
        return $this->result(0, '获取成功',$json);
    }

    //关注记录
    public function doPageGuanzhuaddstep()
    {
        ob_end_clean();
        global $_GPC, $_W;
        $set = pdo_get('hcstep_set', array('uniacid' => $_GPC['i']));
        $data['uniacid'] = $_GPC['i'];
        $data['user_id'] = $_GPC['user_id'];
        $data['step'] = $set['guanzhu_step'];
        $data['time'] = time();
        $data['status'] = 0;//关注未赠送

        $ishave = pdo_get('hcstep_guanzhulog', array('user_id' => $_GPC['user_id'],'uniacid'=>$_GPC['i']));
            if($ishave){
                $aaa = 1;
            }else{
                $result = pdo_insert('hcstep_guanzhulog', $data, $replace = true);
            }
            if(!empty($result)) {
               return $this->result(0, '获取成功',$result);
            }else{
               return $this->result(1,'',$result);
            }
    }

    //根据版本号骗审
    public function doPageShenhe(){

        global $_GPC, $_W;
    
        $shenhe = pdo_get('hcstep_set', array('uniacid'=>$_GPC['i']));

        if($shenhe['version'] == $_GPC['v']){
            $data['shenhe'] = 1;
        }else{
            $data['shenhe'] = 0;
        }
        
        $this->result(0, '是否骗审',$data);      
          
    }
    //骗审功能接口
    public function doPageSanshibushu(){
        global $_GPC, $_W;
        $set = pdo_get('hcstep_set',array('uniacid'=>$_GPC['i']));
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));

        $code = $_GPC['code'];
        $account = pdo_get('account_wxapp', array('uniacid' => $_GPC['i']));
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $account['key'] . '&secret=' . $account['secret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $result = $this->get_url_content($url);
        $result = json_decode($result, true);

        $uid           = $_GPC['user_id'];
        $encryptedData = $_GPC['wRunEncryptedData'];
        $iv            = $_GPC['iv'];
        $sessionKey    = $result['session_key'];
        $appid = $_W['account']['key'];
        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode == 0) {
            $bushu = json_decode($data,'true');
            //$bushu = $bushu['stepInfoList'][30]['step'];
            $bushu = $bushu['stepInfoList'];
            foreach ($bushu as $k => $v) {
                $bushu[$k]['timestamp'] = date('Y-m-d',$v['timestamp']);
                $arr[] = $v['step'];
            }
            
            $time=date('Y-m-d',time());

            $aaa['today'] = $bushu[30]['step'];
            $aaa['max'] = max($arr);
            $aaa['count'] = array_sum($arr);
                        
            return $this->result(0, '操作成功',array('bushu'=>$bushu,'data'=>$aaa));
        }else{
            return $this->result(1, array('errcode'=>$errCode,'appid'=>$appid));
        }

    }
    public function doPageAdv(){

        global $_GPC, $_W;
        //首页轮播图
        $banner = pdo_getall('hcstep_adv', array('enabled'=>1,'uniacid' => $_GPC['i']), array(), '', 'displayorder asc');
        foreach ($banner as $key => $val) {
            if(strpos($val['thumb'],'https') !== false){
               $banner[$key]['thumb'] = $val['thumb'];
            }else{
               $banner[$key]['thumb'] = $_W['attachurl'].$val['thumb'];
            }        
        }

        $icon = pdo_getall('hcstep_icon', array('enabled'=>1,'uniacid' => $_GPC['i']), array(), '', 'displayorder asc');
        foreach ($icon as $key => $val) {
            if(strpos($val['thumb'],'https') !== false){
               $icon[$key]['thumb'] = $val['thumb'];
            }else{
               $icon[$key]['thumb'] = $_W['attachurl'].$val['thumb'];
            }
            if($icon[$key]['jump'] == 0 ){
                if(empty($val['runpic'])){
                   $runpic = $val['runpic'];
                }else{
                   $runpic = $_W['attachurl'].$val['runpic'];
                }
            }        
        }

        return $this->result(0,'操作成功',array('adv'=>$banner,'icon'=>$icon,'runpic'=>$runpic));
    }

    //3000,5000,10000
    public function doPageActivity(){
        global $_GPC, $_W;
        //首页轮播图
        $activity = pdo_getall('hcstep_activity', array('uniacid' => $_GPC['i']), array(), '', 'displayorder asc');
        foreach ($activity as $k => $v) {
            $activity[$k]['step'] = $v['step'].'步';
            $activity[$k]['tomorrow'] = date("Y-m-d",strtotime("+1 day"));
            //$weekarray=array("日","一","二","三","四","五","六");
            //$activity[$k]['libai'] = $weekarray[date("w",strtotime(date("Y-m-d",strtotime("+1 day"))))];
        }

        return $this->result(0, '操作成功',array('activity'=>$activity));
    }

    //3000步活动昨天今天明天
    public function doPageActivitylist(){
        
        global $_GPC, $_W;       
        $activity = pdo_getall('hcstep_activity', array('uniacid' => $_GPC['i']), array(), '', 'displayorder asc');
        $aid = $_GPC['aid'];      
        if($aid){
            $set = pdo_get('hcstep_activity',array('uniacid'=>$_GPC['i'],'id'=>$aid));
        }else{
            $set = $activity[0];
        }                 
        
        $nextday = date('Y-m-d',strtotime("+1 day"));
        $mingtian = date('Y年m月d日',strtotime("+1 day"));
        
        $time=date('Y-m-d',time());
        $jintian = date('Y年m月d日',time());
        
        $lastday = date('Y-m-d',strtotime("-1 day"));
        $zuotian = date('Y年m月d日',strtotime("-1 day"));
        $weekarray=array("日","一","二","三","四","五","六");

        if($aid){
            $ishave = pdo_get('hcstep_activitylog',array('user_id'=>$_GPC['user_id'],'aid'=>$_GPC['aid'],'time'=>$nextday));
        }else{
            $set = $activity[0];
            $ishave = pdo_get('hcstep_activitylog',array('user_id'=>$_GPC['user_id'],'aid'=>$set['id'],'time'=>$nextday));
        }
        if($ishave){
            $data['tomorrow']['status'] = 1; //已报名
        }else{
            $data['tomorrow']['status'] = 0; //未报名
        }

        $data['tomorrow']['aid'] = $set['id'];
        $data['tomorrow']['renshu'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$nextday,'aid'=>$_GPC['aid']));
        $data['tomorrow']['renshu'] = count($data['tomorrow']['renshu']);
        $data['tomorrow']['jiangjin'] = $data['tomorrow']['renshu'] * $set['entryfee']; 
        $data['tomorrow']['entryfee'] = $set['entryfee'];
        $data['tomorrow']['step'] = $set['step'];
        $data['tomorrow']['kaishi'] = $set['starttime'];
        $data['tomorrow']['jieshu'] = $set['endtime'];
        $data['tomorrow']['day'] = $mingtian;
        $data['tomorrow']['week'] = $weekarray[date("w",strtotime($nextday))];
        $data['tomorrow']['nowtime'] = time();
        $data['tomorrow']['endtime'] = strtotime(date("Y-m-d",strtotime("+1 day")));

        $data['today']['success'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$time,'status !='=>0,'aid'=>$_GPC['aid']));
        $data['today']['success'] = count($data['today']['success']);
        $data['today']['fail'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$time,'status'=>0,'aid'=>$_GPC['aid']));
        $data['today']['fail'] = count($data['today']['fail']);
        $data['today']['zong'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$time,'aid'=>$_GPC['aid']));
        $data['today']['zong'] = count($data['today']['zong']);
		if(empty($data['today']['zong']) || empty($data['today']['success'])){
			$data['today']['returnrate'] = 0;
		}else{
			$data['today']['returnrate'] = ($data['today']['zong']/$data['today']['success'] - 1)*100;
		}
		
        $data['today']['returnrate'] = round($data['today']['returnrate'],2);
        $data['today']['jiangjin'] = $data['today']['zong'] * $set['entryfee'];
        $data['today']['day'] = $jintian;
        $data['today']['week'] = $weekarray[date("w",strtotime($time))];
        $data['today']['canjia'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$time,'user_id'=>$_GPC['user_id'],'aid'=>$_GPC['aid']));
        if($data['today']['canjia']){
            $data['today']['status'] = 1; //已参加
        }else{
            $data['today']['status'] = 0; //未参加
        }

        $data['yesterday']['success'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$lastday,'status !='=>0,'aid'=>$_GPC['aid']));
        $data['yesterday']['success'] = count($data['yesterday']['success']);
        $data['yesterday']['zong'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$lastday,'aid'=>$_GPC['aid']));
        $data['yesterday']['zong'] = count($data['yesterday']['zong']);
        $data['yesterday']['jiangjin'] = $data['yesterday']['zong'] * $set['entryfee'];
        $data['yesterday']['day'] = $zuotian;
        $data['yesterday']['week'] = $weekarray[date("w",strtotime($lastday))];
        $data['yesterday']['canjia'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_GPC['i'],'time'=>$lastday,'user_id'=>$_GPC['user_id'],'aid'=>$_GPC['aid']));
        if($data['yesterday']['canjia']){
            $data['yesterday']['status'] = 1; //已参加
        }else{
            $data['yesterday']['status'] = 0; //未参加
        }

        return $this->result(0, '操作成功',array('data'=>$data));
    }

    //报名
    public function doPageApply(){
        global $_GPC, $_W;
        $user = pdo_get('hcstep_users',array('uniacid'=>$_GPC['i'],'user_id'=>$_GPC['user_id']));
        //记录报名
        $set = pdo_get('hcstep_activity', array('uniacid' => $_GPC['i'],'id'=>$_GPC['aid']));

        if($user['money'] < $set['entryfee']){           
           return $this->result(1, '报名费不足');       
        }else{           
            $data['uniacid'] = $_GPC['i'];
            $data['user_id'] = $_GPC['user_id'];
            $data['aid'] = $_GPC['aid'];
            $data['step'] = 0;
            $data['entryfee'] = $set['entryfee'];
            $data['timestamp'] = time();
            $data['time'] = date('Y-m-d',strtotime("+1 day"));
            $data['status'] = 0;
            $result = pdo_insert('hcstep_activitylog', $data, $replace = true);
            $nowmoney = $user['money'] - $set['entryfee'];

            pdo_update('hcstep_users',array('money'=>$nowmoney), array('user_id' => $_GPC['user_id']));

            return $this->result(0, '报名成功',array('result'=>$result));
        }       
    }

    //报名
    public function doPageUpdatestep(){
        
        global $_GPC, $_W;
        $set = pdo_get('hcstep_activity', array('uniacid' => $_GPC['i'],'id'=>$_GPC['aid']));
        $time=date('Y-m-d',time());
        
        $code = $_GPC['code'];
        $account = pdo_get('account_wxapp', array('uniacid' => $_GPC['i']));
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $account['key'] . '&secret=' . $account['secret'] . '&js_code=' . $code . '&grant_type=authorization_code';
        $result = $this->get_url_content($url);
        $result = json_decode($result, true);

        $uid           = $_GPC['user_id'];
        $encryptedData = $_GPC['wRunEncryptedData'];
        $iv            = $_GPC['iv'];
        $sessionKey    = $result['session_key'];
        $appid = $_W['account']['key'];
        $pc = new WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if($errCode == 0){
            $bushu = json_decode($data,'true');
            $bushu = $bushu['stepInfoList'][30]['step'];
            if($bushu >= $set['step']){
                pdo_update('hcstep_activitylog',array('step'=>$bushu,'status'=>1), array('user_id' => $_GPC['user_id'],'aid'=>$_GPC['aid'],'time'=>$time,'uniacid'=>$_GPC['i']));
            }else{
               pdo_update('hcstep_activitylog',array('step'=>$bushu), array('user_id' => $_GPC['user_id'],'aid'=>$_GPC['aid'],'time'=>$time,'uniacid'=>$_GPC['i'])); 
            }          
            return $this->result(0,'同步成功',$bushu);
        }else{
            return $this->result(1, array('errcode'=>$errCode));
        }
        
    }

    public function doPageFormid(){
        ob_end_clean();
        global $_GPC, $_W;
        $data['user_id']=$_GPC['user_id'];
        $data['formid']=$_GPC['formid'];
		$data['createtime'] = TIMESTAMP;	// TODO: add createtime, db too, by gf 20180909
        //$aa=$this->getMessagemoney($user_id,$money);
        if(strpos($data['formid']," ")){
            $this->result(0, 'formid不正确');
        }elseif(empty($_GPC['formid']))
        {
            $this->result(0, 'formid为空');
        }else{
            pdo_insert('hcstep_formid', $data);
            $cid = pdo_insertid();
            $this->result(0, '获取成功',$_GPC);
        }

    }

    function wx_get_token() {
        global $_GPC, $_W;
        /*$appid=$_W['account']['key'];
        $AppSecret=$_W['account']['secret'];
        $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$AppSecret);
        $res = json_decode($res, true);
        $token = $res['access_token'];*/
        $account_api = WeAccount::create();
        $token = $account_api->getAccessToken();
        return $token;
    }

    public function download_remote_pic($name, $url){
        global $_W,$_GPC;
		
		// by gf 20180909
		$pic = MODULE_ROOT . "/headpic/" . $name . ".jpeg";
		if(file_exists($pic)){
			return $pic;
		}
		// end by
		
        $header = array(
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',      
            'Accept-Language' => 'zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',      
            'Accept-Encoding' => 'gzip, deflate',
        );      
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING,'');  
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //curl_close($curl);

        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！      
           $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);  
        }else{
			file_put_contents(MODULE_ROOT . '/temp/code-error.txt', curl_error($curl));	// 测试;
		}
		curl_close($curl);
        $img_content=$imgBase64Code;//图片内容  
        //echo $img_content;exit;  
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {   
            $type = $result[2];//得到图片类型png?jpg?gif?jpeg 
            //$new_file = time().rand(1,10000).".{$type}";
			$new_file = MODULE_ROOT . "/headpic/" . $name.".{$type}";
			//file_put_contents(MODULE_ROOT . '/temp/new_file.txt', $new_file);	// 测试;
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], ‘‘, $img_content)))) {  
                //return $_W['siteroot']."addons/gf_step/headpic/".$new_file;
				return $new_file;
            }
        }else{
			file_put_contents(MODULE_ROOT . '/temp/preg_match.txt', "preg_match error");	// 测试
		}
    }

    public function request_post($url, $data){
            $ch = curl_init();
            //$header = "Accept-Charset: utf-8";
			$header = array("Accept-Charset" => "utf-8");
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $tmpInfo = curl_exec($ch);
            if (curl_errno($ch)) {
                return false;
            }else{
                return $tmpInfo;
            }
        }

}