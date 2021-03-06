<?php

namespace frontend\modules\desktop\controllers;

use frontend\controllers\base\BaseController;
use frontend\modules\desktop\models\TableModel;
/**
 * 后台
 */
class DefaultController extends BaseController
{
	//用最外层的layout
	public $layout = '/backend';
    /**
     * 后台首页
     * @return string
     */
    public function actionIndex()
    {
    	$table = new TableModel;
    	//今日用户登陆
    	$result['login_nums'] = $table->getAllNewLogin();
    	//总订单金额
    	$result['all_order'] = $table->getAllOrder();
    	//已审核订单金额
    	$result['confirm_orders'] = $table->getAllOrder('finish');
    	//总订货指标
    	$result['all_target'] = $table->getAllUserTarget();
    	//加盟订货指标
    	$result['jm_target'] = $table->getAllUserTarget('客户');

    	//加盟已订货总金额
    	$result['jm_all_nums'] = $table->getOrderNum('客户');
    	//加盟已订货active状态的金额
    	$result['jm_active'] = $table->getOrderNum('客户', 'active');
    	//加盟已订货confirm状态的金额
    	$result['jm_confirm'] = $table->getOrderNum('客户', 'confirm');
		//加盟已订货finish状态的金额
    	$result['jm_finish'] = $table->getOrderNum('客户', 'finish');

    	//总达成率  
    	$result['all_target'] != 0 ? $result['all_target_rate'] = round($result['confirm_orders'] / $result['all_target']*100, 2) : $result['all_target_rate'] = 0;
    	//加盟审核后达成率  
    	$result['jm_target'] != 0 ? $result['jm_target_rate'] = round($result['jm_finish'] / $result['jm_target'] *100, 2) : $result['jm_target_rate'] = 0;

        return $this->render('index', ['res'=>$result]);
    }
}
