<?php
namespace frontend\controllers;

use Yii;
use frontend\controllers\base\FBaseController;
use frontend\models\ProductModel;
/**
 * 首页controller
 */
class DefaultController extends FBaseController
{
    public function actionIndex()
    {
    	$request = Yii::$app->request;
    	//页码
    	$page = $request->get('page', 1);
    	$c_ids = $request->get('c_id');	//分类ID 大分类,小分类 的格式
    	$sd = $request->get('sd');		//季节
    	$wv = $request->get('wv');		//波段
    	$lv = $request->get('lv');		//等级
    	$plv = $request->get('plv');	//价格带
    	$or = $request->get('or');		//已订/未订
    	$price = $request->get('price');	//价格升降排序
    	$hits = $request->get('hits');		//人气升降排序
    	$plv = $request->get('serial_num');	//输入搜索

    	//搜索条件
    	$conArr = [];
    	//小分类 大分类
    	$c_id = $b_id = 0;
    	if ($c_ids) {
    	    $cat_arr = explode(',', $c_ids);

    	    if (isset($cat_arr[0])) $b_id = $cat_arr[0];
    	    if (isset($cat_arr[1])) $c_id = $cat_arr[1];

    	    if ($c_id) {
    	        $conArr[] = 's_id_' . $b_id;
    	        $conArr[] = 'c_id_' . $c_id;
    	    } elseif ($b_id) {
    	        $conArr[] = 's_id_' . $b_id;
    	    }

    	}
    	if ($sd) {
    	    $sdArr = explode('_', $sd);
    	    $conArr[] = 'sd_' . $sdArr[0];
    	    $model['sd'] = $sdArr[1];
    	}
    	if ($wv) {
    	    $wvArr = explode('_', $wv);
    	    $conArr[] = 'wv_' . $wvArr[0];
    	    $model['wv'] = $wvArr[1];
    	}
    	if ($lv) {
    	    $lvArr = explode('_', $lv);
    	    $conArr[] = 'lv_' . $lvArr[0];
    	    $model['lv'] = $lvArr[1];
    	}
    	if ($plv) {
    	    $plvArr = explode('_', $plv);
    	    $conArr[] = 'plv_' . $plvArr[0];
    	    $model['plv'] = $plvArr[1];
    	}

    	$productModel = new ProductModel;

    	$params = [
    	    'or' => $or,
    	    'purchase_id' => $this->purchase_id,
    	    'customer_id' => $this->customer_id,
    	    'hits' => $hits,
    	];
    	//一个用户的订单状态
    	$res=$productModel->checkStatus($params['customer_id']);

    	//获取搜索的商品
    	$model['list'] = $productModel->newitems($conArr, $serial_num, $params, $price, $page);
    	$model['c_id'] = $c_id;
    	$model['price'] = $price;
    	$model['price_f'] = $price == 1 ? 2 : 1;
    	$model['hits'] = $hits;
    	$model['hits_f'] = $hits == 1 ? 2 : 1;
    	$model['or'] = $or;

    	if ($page > 1) {
    	    echo $this->renderPartial('ajaxindex', array('model' => $model, 'c_id' => $c_id, 'b_id' => $b_id,'res'=>$res));
    	} else {
    	    $this->render('index', 
    	    	[
	    	    	'model' => $model, 
	    	    	'c_id' => $c_id, 
	    	    	'b_id' => $b_id, 
	    	    	'serial_num' => $serial_num,
	    	    	'res'=>$res
    	    	]);
    	}
    }
}