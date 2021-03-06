<?php

namespace frontend\modules\order\controllers;

use Yii;
use frontend\config\ParamsClass;
use frontend\controllers\base\BaseController;
use frontend\models\PurchaseModel;
use frontend\models\CustomerModel;
use frontend\models\CatBigModel;
use frontend\models\CatMiddleModel;
use frontend\models\CatSmallModel;
use frontend\models\SeasonModel;
use frontend\models\WaveModel;
use frontend\models\LevelModel;
use frontend\models\SchemeModel;
use frontend\models\TypeModel;
use frontend\models\OrderModel;
use frontend\helpers\IoXls;

/**
 * 订单管理  
 * @author dingran
 * @date(2017.7.28)
 */
class DefaultController extends BaseController
{
	public $layout = '/backend';
    /**
     * 商品订单汇总
     * @return [type] [description]
     */
    public function actionIndex()
    {
    	$select_option = Yii::$app->cache->get('select_option');

    	if(empty($select_option))
    	{
    	    $select_option = $this->tables();
    	}
        // var_dump($select_option);exit;
    	$pageIndex = Yii::$app->request->get('page', 1);

    	$params= Yii::$app->request->get('param', []);

    	$params['page']= $pageIndex;
    	$order = new OrderModel();
    	// 订单的详细信息以及搜索功能
    	$result = $order->orderList($this->pagesize,$params);
        // var_dump($result);exit;
        //下载
        if(!empty($params['download'])){
            $data = [];
    	    if(!empty($result['item'])){
    	        $product_id= [];
                
                //订单总数量
                $nums = 0;
                //订单总价格。
                $amount = 0;
    	        foreach($result['item'] as $k=>$v){
    	            $product_id[] = $v['product_id'];
                    //可以不用查询，直接使用查询结果统计
    	            $order_type = $order->customerOrderByProductIdCount($v['product_id'],$params);
    	            $result['item'][$k]['customer'] = $order_type['customer'];
    	            $result['item'][$k]['self'] = $order_type['self'];
                    //把ID转换成name
    	            foreach($select_option['cat_big'] as $cat_big){
    	                if($cat_big['big_id'] == $v['cat_b']){
    	                    $result['item'][$k]['cat_big_name'] = $cat_big['cat_name'];
                        }
    	            }
    	            foreach($select_option['cat_middle'] as $cat_middle){
    	                if($cat_middle['middle_id'] == $v['cat_m']){
    	                    $result['item'][$k]['cat_middle_name'] = $cat_middle['cat_name'];
                        }
    	            }
    	            foreach($select_option['cat_small'] as $cat_small){
    	                if($cat_small['small_id'] == $v['cat_s']){
    	                    $result['item'][$k]['cat_small_name'] = $cat_small['cat_name'];
                        }
    	            }
                    $nums += $v['nums'];
                    $amount += $v['cost_price']*$v['nums'];
                    
                }
                //订单数量汇总: 订单金额汇总:
                $result['nums'] = $nums;
                $result['amount'] = $amount;

    	    }
    	    $keys = ['大类','中类','小类','款色','流水','商品类型', '吊牌价' ,'加盟订货','直营订货','总订货','尺寸'];
    	    foreach($result['item'] as $k=> $v){
                $data[$k]['A'] = $v['cat_big_name'];
                $data[$k]['B'] = $v['cat_middle_name'];
                $data[$k]['C'] = $v['cat_small_name'];
                $data[$k]['D'] = $v['style_sn'];
                $data[$k]['E'] = $v['serial_num'];
                $data[$k]['F'] = $v['type_name'];
                $data[$k]['G'] = $v['cost_price'];
                $data[$k]['H'] = $v['customer'];
                $data[$k]['I'] = $v['self'];
                $data[$k]['J'] = $v['nums'];
                $data[$k]['K'] = $v['size_name'];
            }

    	    $data2 = [
    	        ['',''],
    	        ['订货数量汇总',empty($result['nums'])?0:$result['nums'] ],
    	        ['订货金额汇总',empty($result['amount'])?0:number_format($result['amount'],2) ],
    	    ];
    	    $filename = '商品导出筛选结果';
    	    $export = new IoXls();
    	    $export->export_begin($keys, $filename, count($data));
    	    $export->export_rows($data);
    	    $export->export_rows($data2);
    	    $export->export_finish();
    	}else{
    	    if(!empty($result['item'])){
    	        $product_id= [];
                //订单总数量
                $nums = 0;
                //订单总价格。
                $amount = 0;
    	        foreach($result['item'] as $k=>$v){
    	            $product_id[] = $v['product_id'];
    	            $order_type = $order->customerOrderByStyleSnCount($v['style_sn'],$params);
    	            $result['item'][$k]['customer'] = $order_type['customer'];
    	            $result['item'][$k]['self'] = $order_type['self'];
    	            foreach($select_option['cat_big'] as $cat_big){
    	                if($cat_big['big_id'] == $v['cat_b'])
    	                    $result['item'][$k]['cat_big_name'] = $cat_big['cat_name'];
    	            }
    	            foreach($select_option['cat_middle'] as $cat_middle){
    	                if($cat_middle['middle_id'] == $v['cat_m'])
    	                    $result['item'][$k]['cat_middle_name'] = $cat_middle['cat_name'];
    	            }
    	            foreach($select_option['cat_small'] as $cat_small){
    	                if($cat_small['small_id'] == $v['cat_s'])
    	                    $result['item'][$k]['cat_small_name'] = $cat_small['cat_name'];
    	            }
    	            $nums += $v['nums'];
                    $amount += $v['cost_price']*$v['nums'];
                }
                //订单数量汇总: 订单金额汇总:
                $result['nums'] = $nums;
                $result['amount'] = $amount;
    	    }

    	    
    	    if(empty($params['view'])){
    	        $view = 'index';
    	    }else{
    	        $view = 'indexwithpic';
    	    }

    	    return $this->render($view,[
    	        'result'=>$result,
    	        'params'=>$params,
    	        'selectOption'=>$select_option
    	    ]);
    	}
    }


    /**
     * 获取表的数据
     * @return [type] [description]
     */
    public function tables(){
        //订货会：
        $purchase = new PurchaseModel();
        $select_option['purchase'] = $purchase->find()->asArray()->all();

        //（渠道）客户类型：
        $customer = new CustomerModel();
        $select_option['customer'] = $customer->getList();

        //大类：
        $cat_big = new CatBigModel();
        $select_option['cat_big'] = $cat_big->getList();
        //中类：
        $cat_middle = new CatMiddleModel();
        $select_option['cat_middle'] = $cat_middle->getList();
        //小类：
        $cat_small = new CatSmallModel();
        $select_option['cat_small'] = $cat_small->getList();
        //季节：
        $season = new SeasonModel();
        $select_option['season'] = $season->getList();

        //波段：
        $wave = new WaveModel();
        $select_option['wave'] = $wave->getList();
        //等级：
        $level = new LevelModel();
        $select_option['level'] = $level->getList();
        //色系：
        $scheme = new SchemeModel();
        $select_option['scheme'] = $scheme->getList();
        //价格带：
        $select_option['price_level'] = ParamsClass::$priceLevel;
        $type = new TypeModel();
        $select_option['ptype'] = $type->getList();

        Yii::$app->cache->set('select_option',$select_option,7200);
        return $select_option;
    }

}
