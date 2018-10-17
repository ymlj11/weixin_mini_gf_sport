<?php
/**
 * 模块定义
 *
 * @author gf
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Gf_stepModule extends WeModule {

public function settingsDisplay($settings)
	{
		global $_GPC, $_W;
		if (checksubmit()) {
			$setting = $this->module['config'];
			$setting['expireTime'] = $_GPC['expireTime'];
			$setting['strategyKeywords'] = $_GPC['strategyKeywords'];
			$setting['productKeywords'] = $_GPC['productKeywords'];
			$setting['servicePhone'] = $_GPC['servicePhone'];
			$setting['map_key'] = $_GPC['map_key'];
			$setting['distribution_is_enable'] = $_GPC['distribution_is_enable'];
			$setting['distribution_level'] = $_GPC['distribution_level'];
			$setting['distribution_levelName'] = $_GPC['distribution_levelName'];
			$setting['withdraw_minLimit'] = $_GPC['withdraw_minLimit'];
			$setting['distribution_bgImg'] = $_GPC['distribution_bgImg'];
			$setting['qrfontcolor'] = $_GPC['qrfontcolor'];
			$setting['qrfontsize'] = $_GPC['qrfontsize'];
			$setting['qrlocation'] = $_GPC['qrlocation'];
			$setting['index_video_url'] = $_GPC['index_video_url'];
			$setting['index_video_autoplay'] = $_GPC['index_video_autoplay'];
			$setting['index_video_cover'] = $_GPC['index_video_cover'];
			$setting['template_payed'] = $_GPC['template_payed'];
			$setting['template_checked'] = $_GPC['template_checked'];
			$setting['template_cancel'] = $_GPC['template_cancel'];
			$setting['template_group'] = $_GPC['template_group'];
			$setting['copyright'] = $_GPC['copyright'];
			$setting['diy_detail_poster_name'] = $_GPC['diy_detail_poster_name'];
			$setting['enable_coin'] = $_GPC['enable_coin'];
			$setting['enable_poster'] = $_GPC['enable_poster'];
			$setting['enable_mail'] = $_GPC['enable_mail'];
			$setting['group_expireTime'] = $_GPC['group_expireTime'];
			//短信
			$setting['enable_sms'] = $_GPC['enable_sms'];
			$setting['sms_appKey'] = $_GPC['sms_appKey'];
			$setting['sms_appSecret'] = $_GPC['sms_appSecret'];
			$setting['sms_order_templateId'] = $_GPC['sms_order_templateId'];
			$setting['sms_order_product'] = $_GPC['sms_order_product'];
			$setting['sms_withdraw_templateId'] = $_GPC['sms_withdraw_templateId'];
			$setting['sms_refund_templateId'] = $_GPC['sms_refund_templateId'];
			$setting['sms_signName'] = $_GPC['sms_signName'];
			$setting['sms_intervalTime'] = $_GPC['sms_intervalTime'];
			$setting['sms_mobile'] = $_GPC['sms_mobile'];
			//邮件配置
			$setting['enable_email'] = $_GPC['enable_email'];
			$setting['smtp_server'] = $_GPC['smtp_server'];
			$setting['smtp_port'] = $_GPC['smtp_port'];
			$setting['smtp_username'] = $_GPC['smtp_username'];
			$setting['smtp_password'] = $_GPC['smtp_password'];
			$setting['smtp_sender'] = $_GPC['smtp_sender'];
			$setting['smtp_signature'] = $_GPC['smtp_signature'];
			$setting['smtp_enable_ssl'] = $_GPC['smtp_enable_ssl'];
			$setting['smtp_admin_email'] = $_GPC['smtp_admin_email'];
			//闸机配置
			$setting['gate_online'] = $_GPC['gate_online'];
			$setting['gate_key'] = $_GPC['gate_key'];
			$setting['gate_transferMode'] = $_GPC['gate_transferMode'];
			$setting['gate_verifyType'] = $_GPC['gate_verifyType'];
			$setting['gate_verifyUpper'] = $_GPC['gate_verifyUpper'];
			$setting['gate_return'] = $_GPC['gate_return'];
			if ($this->saveSettings($setting)) {
				message('保存参数成功', 'refresh');
			}
		}
		$setting = $this->module['config'];
		include $this->template('setting');
	}

}