<?php
/**
 * 模块微站定义
 *
 * @author gf
 * @url 
 */
defined('IN_IA') or exit('Access Denied');
require_once IA_ROOT."/addons/gf_step/inc/model.class.php"; 
class Gf_stepModuleSite extends WeModuleSite {

	public $table_bushuday = "gfstep_bushuday";	// add by gf

    public function doWebTest(){
        global $_W,$_GPC;        
        $account_api = WeAccount::create();
        $token = $account_api->getAccessToken();
        //$url = "https://api.weixin.qq.com/cgi-bin/menu/delete?access_token={$token}";
        //$response = ihttp_get($url);
        print_r($token);
    }

    public function download_remote_pic($url){
        global $_W,$_GPC;
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
        curl_close($curl);
        if ($code == 200) {//把URL格式的图片转成base64_encode格式的
           $imgBase64Code = "data:image/jpeg;base64," . base64_encode($data);
        }  
        $img_content=$imgBase64Code;//图片内容  
        //echo $img_content;exit;
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {
            $type = $result[2];//得到图片类型png?jpg?gif?
            $new_file = time().rand(1,10000).".{$type}";
            if (file_put_contents(dirname(__FILE__)."/headpic/".$new_file, base64_decode(str_replace($result[1], ‘‘, $img_content)))) {
                return $_W['siteroot']."addons/gf_step/headpic/".$new_file; 
            }
        }
    }
	   
    //商品管理
    public function doWebGoods() {
        global $_W,$_GPC;

        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_goods',$where,array($pageindex, $pagesize),$total,array(),'','displayorder asc');
        $page = pagination($total, $pageindex, $pagesize);

        include $this->template('goods');
    }

    //模板消息
    public function doWebMessage(){
        global $_W,$_GPC;
        if($_GPC['act']=='add'){
			$id = $_GPC['id'];
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/           
            //$data['hongbao_msgid'] = $_GPC['hongbao_msgid'];
            $data['msgid'] = $_GPC['msgid'];
            $data['keyword1'] = $_GPC['keyword1'];
            $data['keyword2'] = $_GPC['keyword2'];
            $data['keyword3'] = $_GPC['keyword3'];
            
            //$ishave = pdo_get('hcstep_message', array('uniacid' => $_W['uniacid']));
            if(!empty($id)){
                $result = pdo_update('hcstep_message', $data ,array('id'=>$id));
            }else{
                $result = pdo_insert('hcstep_message', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('message'));
            }
        }
        $setup = pdo_get('hcstep_message', array('uniacid' => $_W['uniacid']));
        include $this->template('message');
    }


    //添加商品
    public function doWebAddgoods() {
        global $_W,$_GPC;
        $id = $_GPC['id'];

            $data['uniacid'] = $_W['uniacid'];
            $data['goods_name'] = $_GPC['goods_name'];
            $data['main_img'] = $_GPC['main_img'];
            $data['goods_img'] = json_encode($_GPC['goods_img']);
            $data['price'] = $_GPC['price'];
            $data['inventory'] = $_GPC['inventory'];
            $data['express'] = $_GPC['express'];
			$data['goodsdesc'] = $_GPC['goodsdesc'];
			$data['shopname'] = $_GPC['shopname'];
			$data['shopaddr'] = $_GPC['shopaddr'];
            $data['status'] = $_GPC['status'];
            $data['displayorder'] = $_GPC['displayorder'];
            $data['goodsinfo'] = $_GPC['goodsinfo'];
            $data['views'] = $_GPC['views'];
		
        if($_GPC['act']=='add'){
            $result = pdo_insert('hcstep_goods', $data);
        }elseif($_GPC['act']=='edit'){
			$result = pdo_update('hcstep_goods', $data, array('id'=>$_GPC['id']));
		}elseif($_GPC['act']=='del'){
            $result = pdo_delete('hcstep_goods', array('id'=>$_GPC['id']));
        }elseif($_GPC['act']=='display'){
            $result = pdo_update('hcstep_goods',array('displayorder'=>$_GPC['displayorder']),array('id'=>$_GPC['id']));
		}
		
		if (!empty($result)) {
			message('操作成功',$this->createWebUrl('goods'));
		}
        
        $info = pdo_get('hcstep_goods',array('id'=>$id));
        $info['goods_img'] = json_decode($info['goods_img']);

        include $this->template('addgoods');
    }

    //奖品管理
    public function doWebAwards() {
        global $_W,$_GPC;

        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_awards',$where,array($pageindex, $pagesize),$total,array(),'','id desc');
        $page = pagination($total, $pageindex, $pagesize);


        include $this->template('awards');
    }


    //添加奖品
    public function doWebAddawards() {
        global $_W,$_GPC;
        $id = $_GPC['id'];

        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            $data['goods_name'] = $_GPC['goods_name'];
            $data['main_img'] = $_GPC['main_img'];
            $data['goods_img'] = json_encode($_GPC['goods_img']);
            $data['price'] = $_GPC['price'];
            $data['inventory'] = $_GPC['inventory'];
            $data['express'] = $_GPC['express'];
			$data['goodsdesc'] = $_GPC['goodsdesc'];
			$data['shopname'] = $_GPC['shopname'];
			$data['shopaddr'] = $_GPC['shopaddr'];
            $data['status'] = $_GPC['status'];

            $result = pdo_insert('hcstep_awards', $data);
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('awards'));
            }
        }

        if($_GPC['act']=='edit'){
            $data['uniacid'] = $_W['uniacid'];
            $data['goods_name'] = $_GPC['goods_name'];
            $data['main_img'] = $_GPC['main_img'];
            $data['goods_img'] = json_encode($_GPC['goods_img']);
            $data['price'] = $_GPC['price'];
            $data['inventory'] = $_GPC['inventory'];
            $data['express'] = $_GPC['express'];
			$data['goodsdesc'] = $_GPC['goodsdesc'];
			$data['shopname'] = $_GPC['shopname'];
			$data['shopaddr'] = $_GPC['shopaddr'];
            $data['status'] = $_GPC['status'];

            $result = pdo_update('hcstep_awards', $data, array('id'=>$_GPC['id']));
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('awards'));
            }
        }

        if($_GPC['act']=='del'){
            $result = pdo_delete('hcstep_awards', array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('awards'));
            }
        }
        $info = pdo_get('hcstep_awards',array('id'=>$id));
        $info['goods_img'] = json_decode($info['goods_img']);

        include $this->template('addawards');
    }

    //用户管理
    public function doWebUsers(){
        global $_W,$_GPC;
        $op = $_GPC['op'];
        if ($op == 'del') {
            $id = $_GPC['id'];
            $item = pdo_get('hcstep_users',array('user_id'=>$id,'uniacid'=>$_W['uniacid']));
            if(empty($item)){
                message('操作失败',$this->createWebUrl('users'),'error');
            }
            if(pdo_delete('hcstep_users',array('user_id'=>$id,'uniacid'=>$_W['uniacid'])) === false) message('操作失败',referer(),'error');
            else message('操作成功',$this->createWebUrl('users'),'success');
        }
        if ($op == 'black') {
            $id = $_GPC['id'];
            $item = pdo_get('hcstep_users',array('user_id'=>$id,'uniacid'=>$_W['uniacid']));
            if(empty($item)){
                message('操作失败',$this->createWebUrl('users'),'error');
            }
            if(pdo_update('hcstep_users',array('black'=>1), array('user_id'=>$id,'uniacid'=>$_W['uniacid'])) === false) message('操作失败',referer(),'error');
            else message('操作成功',$this->createWebUrl('users'),'success');
        }
        
        $keyword = $_GPC['keyword'];        
        if(!empty($_GPC['keyword']) and $_GPC['order_status'] ==1){
            $where['nick_name LIKE'] = '%'.$keyword.'%';
        }
        if(!empty($_GPC['keyword']) and $_GPC['order_status'] ==2){
            $where['user_id'] = $keyword;
        }
        $where['uniacid'] = $_W['uniacid'];
        $where['black'] = 0;
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_users',$where,array($pageindex, $pagesize),$total,array(),'','user_id desc');
        $page = pagination($total, $pageindex, $pagesize);
       
        include $this->template('users');      
    }

    //黑名单
    public function doWebBlacklist(){
        global $_W,$_GPC;
        $op = $_GPC['op'];
        if ($op == 'white') {
            $id = $_GPC['id'];
            $item = pdo_get('hcstep_users',array('user_id'=>$id,'uniacid'=>$_W['uniacid']));
            if(empty($item)){
                message('操作失败',$this->createWebUrl('blacklist'),'error');
            }
            if(pdo_update('hcstep_users',array('black'=>0), array('user_id'=>$id,'uniacid'=>$_W['uniacid'])) === false) message('操作失败',referer(),'error');
            else message('操作成功',$this->createWebUrl('blacklist'),'success');
        }
        
        $keyword = $_GPC['keyword'];        
        if(!empty($_GPC['keyword'])){
            $where['nick_name LIKE'] = '%'.$keyword.'%';
        }
        $where['uniacid'] = $_W['uniacid'];
        $where['black'] = 1;
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_users',$where,array($pageindex, $pagesize),$total,array(),'','user_id desc');
        $page = pagination($total, $pageindex, $pagesize);
       
        include $this->template('blacklist');      
    }

    //基础设置
    public function doWebSetting(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['sharetitle'] = $_GPC['sharetitle'];
            $data['sharepic'] = $_GPC['sharepic'];
            $data['coinname'] = $_GPC['coinname'];
            $data['rate'] = $_GPC['rate'];
            $data['sharestep'] = $_GPC['sharestep'];
            $data['boxprice'] = $_GPC['boxprice'];
            $data['rulepic'] = $_GPC['rulepic'];
            $data['headcolor'] = $_GPC['headcolor'];
            $data['xcx'] = $_GPC['xcx'];
            $data['up'] = $_GPC['up'];
            $data['notice'] = $_GPC['notice'];
            $data['shenhe'] = $_GPC['shenhe'];
            $data['loginpic'] = $_GPC['loginpic'];
            $data['indexbg'] = $_GPC['indexbg'];
            $data['indexbutton'] = $_GPC['indexbutton'];
            $data['inviteball'] = $_GPC['inviteball'];
            $data['upball'] = $_GPC['upball'];
            $data['zerotip'] = $_GPC['zerotip'];
            $data['poortip'] = $_GPC['poortip'];
            $data['is_follow'] = $_GPC['is_follow'];
            $data['followpic'] = $_GPC['followpic'];
            $data['kefupic'] = $_GPC['kefupic'];
            $data['maxstep'] = $_GPC['maxstep'];
            $data['followlogo'] = $_GPC['followlogo'];
            $data['sharetext'] = $_GPC['sharetext'];
            $data['shareinfo'] = $_GPC['shareinfo'];
            $data['upinfo'] = $_GPC['upinfo'];
            $data['boxpic'] = $_GPC['boxpic'];
            $data['smalltip'] = $_GPC['smalltip'];
            $data['frame'] = $_GPC['frame'];
            $data['smalltipcolor'] = $_GPC['smalltipcolor'];
            $data['sharetextcolor'] = $_GPC['sharetextcolor'];
            $data['shareinfocolor'] = $_GPC['shareinfocolor'];
            $data['buttonbg'] = $_GPC['buttonbg'];
            $data['balltextcolor'] = $_GPC['balltextcolor'];
            $data['centercolor'] = $_GPC['centercolor'];
            $data['cointextcolor'] = $_GPC['cointextcolor'];
            $data['coinpic'] = $_GPC['coinpic'];
            
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('setting'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('setting');
    }

    //问题设置
    public function doWebQuestion_set(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['questionpic'] = $_GPC['questionpic'];
            
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('question_set'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('question_set');
    }

    //关注设置
    public function doWebGuanzhu(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['kefu_title'] = $_GPC['kefu_title'];
            $data['kefu_img'] = $_GPC['kefu_img'];
            $data['kefu_gaishu'] = $_GPC['kefu_gaishu'];
            $data['kefu_url'] = $_GPC['kefu_url'];
            $data['guanzhu_step'] = $_GPC['guanzhu_step'];
                      
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('guanzhu'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('guanzhu');
    }

    //关注设置
    public function doWebShenhe(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['version'] = $_GPC['version'];
                      
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('shenhe'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('shenhe');
    }

    //流量主设置
    public function doWebAd(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['adunit'] = $_GPC['adunit'];
            $data['adunit2'] = $_GPC['adunit2'];
            $data['adunit3'] = $_GPC['adunit3'];
                      
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('ad'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('ad');
    }

    //流量主设置
    public function doWebActivityset(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['activitypic'] = $_GPC['activitypic'];
            $data['applypic'] = $_GPC['applypic'];
            $data['rule'] = $_GPC['rule'];
                      
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('activityset'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('activityset');
    }

    //海报设置
    public function doWebPoster(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['sweattext'] = $_GPC['sweattext'];
            $data['icon'] = $_GPC['icon'];
            $data['posterpic'] = $_GPC['posterpic'];
            $data['comeon'] = $_GPC['comeon'];
                      
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('poster'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('poster');
    }

    //海报设置
    public function doWebSignin(){

        global $_W,$_GPC;
        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            /*empty($_GPC['client_id'])?'':$data['client_id'] = $_GPC['client_id'];*/
            $data['signsharemoney'] = $_GPC['signsharemoney'];
            $data['signpic'] = $_GPC['signpic'];
            $data['signsharetext'] = $_GPC['signsharetext'];
            $data['signicon'] = $_GPC['signicon'];
            $data['signtext'] = $_GPC['signtext'];
            $data['signtextcolor'] = $_GPC['signtextcolor'];
                      
            $ishave = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
            if(!empty($ishave)){
                $result = pdo_update('hcstep_set', $data ,array('uniacid'=>$_W['uniacid']));
            }else{
                $result = pdo_insert('hcstep_set', $data);
            }
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('signin'));
            }
        }
        $info = pdo_get('hcstep_set', array('uniacid' => $_W['uniacid']));
        include $this->template('signin');
    }

    //问题管理
    public function doWebQuestion() {
        global $_W,$_GPC;

        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_question',$where,array($pageindex, $pagesize),$total,array(),'','id desc');
        foreach($list as $key=>$val){
            $list[$key]['createtime'] = date('Y-m-d H:i',$val['createtime']);
        }
        $page = pagination($total, $pageindex, $pagesize);

        include $this->template('question');
    }


   //添加问题
    public function doWebQuestion_post() {
        global $_W,$_GPC;
        $id = $_GPC['id'];

        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            $data['createtime'] = time();
            empty($_GPC['title'])?'':$data['title'] = $_GPC['title'];
            empty($_GPC['content'])?'':$data['content'] = $_GPC['content'];
            empty($_GPC['enabled'])?'':$data['enabled'] = $_GPC['enabled'];

            $result = pdo_insert('hcstep_question', $data);
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('question'));
            }
        }

        if($_GPC['act']=='edit'){
            $data['uniacid'] = $_W['uniacid'];
            $data['createtime'] = time();
            empty($_GPC['title'])?'':$data['title'] = $_GPC['title'];
            empty($_GPC['content'])?'':$data['content'] = $_GPC['content'];
            $data['enabled'] = $_GPC['enabled'];

            $result = pdo_update('hcstep_question', $data, array('id'=>$_GPC['id']));
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('question'));
            }
        }

        if($_GPC['act']=='del'){
            $result = pdo_delete('hcstep_question', array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('question'));
            }
        }
        $info = pdo_get('hcstep_question',array('id'=>$id));

        include $this->template('question_post');
    }

    //商品兑换记录
    public function doWebExchange() {
        global $_W,$_GPC;

        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_orders',$where,array($pageindex, $pagesize),$total,array(),'','id desc');
        $page = pagination($total, $pageindex, $pagesize);

        //$list = pdo_getall('hcstep_orders',array('uniacid'=>$_W['uniacid']), array(),'','id desc');
        
        foreach ($list as $k => $v) {
           $user = pdo_get('hcstep_users',array('uniacid'=>$_W['uniacid'],'user_id'=>$v['user_id']));
           $goods = pdo_get('hcstep_goods',array('id'=>$v['goods_id'],'uniacid'=>$_W['uniacid']));
           $list[$k]['head_pic'] = $user['head_pic'];
           $list[$k]['nick_name'] = $user['nick_name'];
           $list[$k]['goods_name'] = $goods['goods_name'];
           $list[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
        }        

        include $this->template('exchange');
    }

    //奖品兑换记录
    public function doWebWin_exchange() {
        global $_W,$_GPC;

        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_winlog',$where,array($pageindex, $pagesize),$total,array(),'','id desc');
        $page = pagination($total, $pageindex, $pagesize);

        //$list = pdo_getall('hcstep_winlog',array('uniacid'=>$_W['uniacid']), array(),'','id desc');
        
        foreach ($list as $k => $v) {
           $user = pdo_get('hcstep_users',array('uniacid'=>$_W['uniacid'],'user_id'=>$v['user_id']));
           $goods = pdo_get('hcstep_awards',array('id'=>$v['goods_id'],'uniacid'=>$_W['uniacid']));
           $list[$k]['head_pic'] = $user['head_pic'];
           $list[$k]['nick_name'] = $user['nick_name'];
           $list[$k]['goods_name'] = $goods['goods_name'];
           $list[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
        }        

        include $this->template('win_exchange');
    }

    //步数兑换记录
    public function doWebCoin_exchange() {
        global $_W,$_GPC;

        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_bushulog',$where,array($pageindex, $pagesize),$total,array(),'','id desc');
        $page = pagination($total, $pageindex, $pagesize);

        //$list = pdo_getall('hcstep_bushulog',array('uniacid'=>$_W['uniacid']));
        
        foreach ($list as $k => $v) {
           $user = pdo_get('hcstep_users',array('uniacid'=>$_W['uniacid'],'user_id'=>$v['user_id']));
           $list[$k]['head_pic'] = $user['head_pic'];
           $list[$k]['nick_name'] = $user['nick_name'];
           $list[$k]['timestamp'] = date('Y-m-d H:i:s',$v['timestamp']);
        }        

        include $this->template('coin_exchange');
    }

    //步数兑换记录
    public function doWebBushulog() {
        global $_W,$_GPC;

        $where['uniacid'] = $_W['uniacid'];
        $where['user_id'] = $_GPC['user_id'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_bushulog',$where,array($pageindex, $pagesize),$total,array(),'','id desc');
        $page = pagination($total, $pageindex, $pagesize);

        //$list = pdo_getall('hcstep_bushulog',array('uniacid'=>$_W['uniacid']));
        
        foreach ($list as $k => $v) {
           $user = pdo_get('hcstep_users',array('uniacid'=>$_W['uniacid'],'user_id'=>$v['user_id']));
           $list[$k]['head_pic'] = $user['head_pic'];
           $list[$k]['nick_name'] = $user['nick_name'];
           $list[$k]['timestamp'] = date('Y-m-d H:i:s',$v['timestamp']);
        }        

        include $this->template('bushulog');
    }

    //发货
    public function doWebFahuo() {
        global $_W,$_GPC;
        if($_GPC['op'] == 'shangpin'){
            $res = pdo_update('hcstep_orders',array('status'=>1), array('id' => $_GPC['id']));
            if($res){
                message('发货成功',$this->createWebUrl('exchange'),'success');
            }else{
                message('已发货',$this->createWebUrl('exchange'),'error');
            }
        }
        if($_GPC['op'] == 'jiangpin'){
            $res = pdo_update('hcstep_winlog',array('status'=>1), array('id' => $_GPC['id']));
            if($res){
                message('发货成功',$this->createWebUrl('win_exchange'),'success');
            }else{
                message('已发货',$this->createWebUrl('win_exchange'),'error');
            }
        }	
    }

    public function doWebAdv() {
        global $_W,$_GPC;
        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_adv',$where,array($pageindex, $pagesize),$total,array(),'','displayorder asc');
        $page = pagination($total, $pageindex, $pagesize);

        //var_dump($list);

        include $this->template('adv');
    }
    //幻灯片
    public function doWebAdv_post() {
        global $_W,$_GPC;
        $id = $_GPC['id'];

        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            empty($_GPC['displayorder'])?'':$data['displayorder'] = $_GPC['displayorder'];
            empty($_GPC['advname'])?'':$data['advname'] = $_GPC['advname'];
            //empty($_GPC['link'])?'':$data['link'] = $_GPC['link'];
            empty($_GPC['thumb'])?'':$data['thumb'] = $_GPC['thumb'];
            empty($_GPC['enabled'])?'':$data['enabled'] = $_GPC['enabled'];
            empty($_GPC['jump'])?'':$data['jump'] = $_GPC['jump'];
            empty($_GPC['xcxpath'])?'':$data['xcxpath'] = $_GPC['xcxpath'];
            empty($_GPC['xcxappid'])?'':$data['xcxappid'] = $_GPC['xcxappid'];
            //empty($_GPC['diypic'])?'':$data['diypic'] = $_GPC['diypic'];

            $result = pdo_insert('hcstep_adv', $data);
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('adv'));
            }
        }

        if($_GPC['act']=='edit'){
            $data['uniacid'] = $_W['uniacid'];
            $data['displayorder'] = $_GPC['displayorder'];
            $data['advname'] = $_GPC['advname'];
            //$data['link'] = $_GPC['link'];
            $data['thumb'] = $_GPC['thumb'];
            $data['enabled'] = $_GPC['enabled'];
            $data['jump'] = $_GPC['jump'];
            $data['xcxpath'] = $_GPC['xcxpath'];
            $data['xcxappid'] = $_GPC['xcxappid'];
            //$data['diypic'] = $_GPC['diypic'];

            $result = pdo_update('hcstep_adv', $data, array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('adv'));
            }
        }

        if($_GPC['act']=='del'){
            $result = pdo_delete('hcstep_adv', array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('adv'));
            }
        }
        if($_GPC['act']=='display'){
            $result = pdo_update('hcstep_adv',array('displayorder'=>$_GPC['displayorder']),array('id'=>$_GPC['id']));
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('adv'));
            }
        }
        $info = pdo_get('hcstep_adv',array('id'=>$id));

        include $this->template('adv_post');
    }

    public function doWebIcon() {
        global $_W,$_GPC;
        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_icon',$where,array($pageindex, $pagesize),$total,array(),'','displayorder asc');
        $page = pagination($total, $pageindex, $pagesize);

        //var_dump($list);

        include $this->template('icon');
    }
    //幻灯片
    public function doWebIcon_post() {
        global $_W,$_GPC;
        $id = $_GPC['id'];

        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            empty($_GPC['displayorder'])?'':$data['displayorder'] = $_GPC['displayorder'];
            empty($_GPC['advname'])?'':$data['advname'] = $_GPC['advname'];
            //empty($_GPC['link'])?'':$data['link'] = $_GPC['link'];
            empty($_GPC['thumb'])?'':$data['thumb'] = $_GPC['thumb'];
            empty($_GPC['enabled'])?'':$data['enabled'] = $_GPC['enabled'];
            empty($_GPC['jump'])?'':$data['jump'] = $_GPC['jump'];
            empty($_GPC['xcxpath'])?'':$data['xcxpath'] = $_GPC['xcxpath'];
            empty($_GPC['xcxappid'])?'':$data['xcxappid'] = $_GPC['xcxappid'];
            empty($_GPC['runpic'])?'':$data['runpic'] = $_GPC['runpic'];
            empty($_GPC['advnamecolor'])?'':$data['advnamecolor'] = $_GPC['advnamecolor'];

            $result = pdo_insert('hcstep_icon', $data);
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('icon'));
            }
        }

        if($_GPC['act']=='edit'){
            $data['uniacid'] = $_W['uniacid'];
            $data['displayorder'] = $_GPC['displayorder'];
            $data['advname'] = $_GPC['advname'];
            //$data['link'] = $_GPC['link'];
            $data['thumb'] = $_GPC['thumb'];
            $data['enabled'] = $_GPC['enabled'];
            $data['jump'] = $_GPC['jump'];
            $data['xcxpath'] = $_GPC['xcxpath'];
            $data['xcxappid'] = $_GPC['xcxappid'];
            $data['runpic'] = $_GPC['runpic'];
            $data['advnamecolor'] = $_GPC['advnamecolor'];

            $result = pdo_update('hcstep_icon', $data, array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('icon'));
            }
        }

        if($_GPC['act']=='del'){
            $result = pdo_delete('hcstep_icon', array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('icon'));
            }
        }
        if($_GPC['act']=='display'){
            $result = pdo_update('hcstep_icon',array('displayorder'=>$_GPC['displayorder']),array('id'=>$_GPC['id']));
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('icon'));
            }
        }
        $info = pdo_get('hcstep_icon',array('id'=>$id));

        include $this->template('icon_post');
    }

    public function doWebActivity() {
        global $_W,$_GPC;
        $where['uniacid'] = $_W['uniacid'];
        
        $pageindex = max(1, intval($_GPC['page']));
        $pagesize = 10;

        $list = pdo_getslice('hcstep_activity',$where,array($pageindex, $pagesize),$total,array(),'','displayorder asc');
        $page = pagination($total, $pageindex, $pagesize);

        //var_dump($list);

        include $this->template('activity');
    }
    //幻灯片
    public function doWebActivity_post() {
        global $_W,$_GPC;
        $id = $_GPC['id'];

        if($_GPC['act']=='add'){
            $data['uniacid'] = $_W['uniacid'];
            empty($_GPC['displayorder'])?'':$data['displayorder'] = $_GPC['displayorder'];
            empty($_GPC['step'])?'':$data['step'] = $_GPC['step'];
            empty($_GPC['entryfee'])?'':$data['entryfee'] = $_GPC['entryfee'];
            /*empty($_GPC['starttime'])?'':$data['starttime'] = $_GPC['starttime'];
            empty($_GPC['endtime'])?'':$data['endtime'] = $_GPC['endtime'];
            empty($_GPC['rule'])?'':$data['rule'] = $_GPC['rule'];*/

            $result = pdo_insert('hcstep_activity', $data);
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('activity'));
            }
        }

        if($_GPC['act']=='edit'){
            $data['uniacid'] = $_W['uniacid'];
            $data['displayorder'] = $_GPC['displayorder'];
            $data['step'] = $_GPC['step'];
            $data['entryfee'] = $_GPC['entryfee'];
            /*$data['starttime'] = $_GPC['starttime'];
            $data['endtime'] = $_GPC['endtime'];
            $data['rule'] = $_GPC['rule'];*/

            $result = pdo_update('hcstep_activity', $data, array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('activity'));
            }
        }

        if($_GPC['act']=='del'){
            $result = pdo_delete('hcstep_activity', array('id'=>$_GPC['id']));
            
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('activity'));
            }
        }
        if($_GPC['act']=='display'){
            $result = pdo_update('hcstep_activity',array('displayorder'=>$_GPC['displayorder']),array('id'=>$_GPC['id']));
            if (!empty($result)) {
                message('操作成功',$this->createWebUrl('activity'));
            }
        }
        $info = pdo_get('hcstep_activity',array('id'=>$id));

        include $this->template('activity_post');
    }

    public function doWebActivitylog(){
        global $_W,$_GPC;
        $aid = $_GPC['id'];
        $set = pdo_get('hcstep_activity', array('uniacid'=>$_W['uniacid'],'id'=>$aid));
        $step = $set['step'];

        $lastday = date('Y-m-d',strtotime("-1 day"));
        $today = date('Y-m-d',time());
        $zuotian = date('Y年m月d日',strtotime("-1 day"));

        $data['yesterday']['success'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'status !='=>0,'aid'=>$aid));
        $success = count($data['yesterday']['success']);
        $data['yesterday']['fail'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'status'=>0,'aid'=>$aid));
        $fail = count($data['yesterday']['fail']);
        $data['yesterday']['zong'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'aid'=>$aid));
        $zong = count($data['yesterday']['zong']);
		if(empty($success)){
			$jiangjin = 0;
		}else{
			$jiangjin = $zong * $set['entryfee'] / $success;
		}
        
        $list = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'aid'=>$aid));
        foreach ($list as $k => $v) {
            $user = pdo_get('hcstep_users',array('uniacid'=>$_W['uniacid'],'user_id'=>$v['user_id']));
            $list[$k]['nick_name'] = $user['nick_name'];
            $list[$k]['time'] = date("Y-m-d H:i:s",$v['timestamp']);
            if($v['status'] == 1){
               $list[$k]['status'] = '已达标，未发奖';
               $list[$k]['jiangjin'] = $jiangjin;
            }elseif($v['status'] == 2){
               $list[$k]['status'] = '已达标，已发奖';
               $list[$k]['jiangjin'] = $jiangjin;
            }else{
               $list[$k]['status'] = '未达标';
               $list[$k]['jiangjin'] = 0;
            } 
        }
        
        include $this->template('activitylog');
    }

    public function doWebsendmoney(){
        
        global $_W, $_GPC;
        $aid = $_GPC['id'];
        $set = pdo_get('hcstep_activity', array('uniacid'=>$_W['uniacid'],'id'=>$aid));

        $lastday = date('Y-m-d',strtotime("-1 day"));
        $data['yesterday']['success'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'status'=>1,'aid'=>$aid));
        $success = count($data['yesterday']['success']);
        $data['yesterday']['fail'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'status'=>0,'aid'=>$aid));
        $fail = count($data['yesterday']['fail']);
        $data['yesterday']['zong'] = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'aid'=>$aid));
        $zong = count($data['yesterday']['zong']);

		if(empty($success)){
			$jiangjin = 0;
		}else{
			$jiangjin = $zong * $set['entryfee'] / $success;
		}
        
        $list = pdo_getall('hcstep_activitylog',array('uniacid'=>$_W['uniacid'],'time'=>$lastday,'aid'=>$aid));

        foreach ($list as $k => $v) {
            if($v['status'] == 1){
                $user = pdo_get('hcstep_users',array('uniacid'=>$_W['uniacid'],'user_id'=>$v['user_id']));
                if(!empty($user)){
                    $nowmoney = $user['money'] + $jiangjin;
                    $faqian[] = pdo_update('hcstep_users',array('money' => $nowmoney), array('user_id'=>$v['user_id'],'uniacid' =>$_W['uniacid']));
                    $zhuangtai = pdo_update('hcstep_activitylog',array('status' => 2), array('id'=>$v['id'],'uniacid' =>$_W['uniacid']));
                }else{
                    $zhuangtai = pdo_update('hcstep_activitylog',array('status' =>-1), array('id'=>$v['id'],'uniacid' =>$_W['uniacid']));
                }
            }
        }

        if ($faqian){
            message('发放成功',$this->createWebUrl('activity'),'success');
        }else{
            message('没有可发放的记录',$this->createWebUrl('activity'),'success');
        }

    }
    //模板消息
    public function doWebMsg(){
        ob_end_clean();
        global $_GPC, $_W;
		
		$msgid = 1; // from $_GPC;
		$msg = pdo_get('hcstep_message', array("id" => $msgid, 'uniacid' => $_W['uniacid']));
		
		$token = $this->wx_get_token();
		
		$sql = "select user_id, open_id, nick_name, bushu_weixin from " . tablename("hcstep_users") . " where uniacid =:uniacid order by user_id";
		$users = pdo_fetchall($sql, array('uniacid' => $_W['uniacid']));
        //$users=pdo_getall('hcstep_users', array('uniacid' => $_W['uniacid']));
        //for($i=0;$i<count($users);$i++){
		
		foreach($users as $user){
			$sql = "select id, user_id, formid from " . tablename("hcstep_formid") . " where user_id =:user_id and status = 0 order by id desc limit 0,1";
			$form = pdo_fetch($sql, array(':user_id' => $user['user_id']));
			
            //$form=pdo_get('hcstep_formid', array('user_id' => $users[$i]['user_id'],'status'=>0), array() , '',array('id DESC') , array());
            if(!empty($form)){
                //$aa=$this->getMessage($form[0]);
				
				$user['fid'] = $form['id'];
				$user['formid'] = $form['formid'];
				//file_put_contents(MODULE_ROOT . '/temp/user.txt', var_export($user, true));	// 测试
				//file_put_contents(MODULE_ROOT . '/temp/msg.txt', var_export($msg, true));	// 测试
				
				$msg['keyword2'] = "今日已运动" . $user['bushu_weixin'] . "步";
				$aa = $this->sendMessage($user, $msg, $token);
                echo "<pre>";
                var_dump($aa);
				//file_put_contents(MODULE_ROOT . '/temp/aa.txt', var_export($aa, true));	// 测试
                echo"</br>";
                //var_dump($form[0]);
                echo "</pre>";
            }
        }
        echo "发送成功，请关闭";
    }

    //发奖模板消息
    public function doWebFajiang(){
        ob_end_clean();
        global $_GPC, $_W;
        $users=pdo_getall('hcstep_users', array('uniacid' => $_W['uniacid']));
        for($i=0;$i<count($users);$i++){
            $formid=pdo_getall('hcstep_formid', array('user_id' => $users[$i]['user_id'],'status'=>0), array() , '',array('id DESC') , array());
            if(!empty($formid[0])){
                $aa=$this->getMessage($formid[0]);
                echo "<pre>";
                var_dump($aa);
                echo "</pre>";
            }
        }
        echo "发送成功，请关闭";
    }

    private function getMessage($formid) {
        global $_GPC, $_W;
        $user=pdo_get('hcstep_users', array('user_id' => $formid['user_id']));
        $setup = pdo_get('hcstep_message', array('uniacid' => $_W['uniacid']));      
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->wx_get_token();
        $data['touser']=$user['open_id'];
        $data['template_id']=$setup['msgid'];
        //$setup=json_decode($setup,true);
        $data['form_id']=$formid['formid'];
        $data['page']='gf_step/pages/index/index';
        $data['data']['keyword1']['value']=$setup['keyword1'];
        $data['data']['keyword1']['color']='#173177';
        $data['data']['keyword2']['value']=$setup['keyword2'];
        $data['data']['keyword2']['color']='#173177';
        $data['data']['keyword3']['value']=$setup['keyword3'];
        $data['data']['keyword3']['color']='#000000';
        $json = json_encode($data);
        $dete=$this->api_notice_increment($url,$json);
        pdo_update('hcstep_formid', array('status' => 1), array('id' => $formid['id']));
        return $dete;
    }
	
    private function sendMessage($user, $msg, $token = ''){
        global $_GPC, $_W;
        //$user=pdo_get('hcstep_users', array('user_id' => $formid['user_id']));
        //$setup = pdo_get('hcstep_message', array('uniacid' => $_W['uniacid']));
        //$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token='.$this->wx_get_token();
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $token;
        $data['touser']=$user['open_id'];
        $data['template_id']= $msg['msgid'];
        //$setup=json_decode($setup,true);
        $data['form_id']=$user['formid'];
        $data['page']='gf_step/pages/index/index';
        $data['data']['keyword1']['value']=$msg['keyword1'];
        $data['data']['keyword1']['color']='#173177';
        $data['data']['keyword2']['value']=$msg['keyword2'];
        $data['data']['keyword2']['color']='#173177';
        $data['data']['keyword3']['value']=$msg['keyword3'];
        $data['data']['keyword3']['color']='#000000';
		//file_put_contents(MODULE_ROOT . '/temp/send-data.txt', var_export($data, true));	// 测试
        $json = json_encode($data);
        $dete=$this->api_notice_increment($url,$json);
        //pdo_update('hcstep_formid', array('status' => 1), array('id' => $formid['id']));
		pdo_update('hcstep_formid', array('status' => 1), array('id' => $user['fid']));
        return $dete;
    }

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

    function wx_get_token() {
        global $_GPC, $_W;
        $appid=$_W['account']['key'];
        $AppSecret=$_W['account']['secret'];
        $res = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$AppSecret);
        $res = json_decode($res, true);
        $token = $res['access_token'];
        return $token;
    }



}