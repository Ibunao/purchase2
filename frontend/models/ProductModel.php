<?php

namespace frontend\models;

use Yii;
use yii\db\Query;
use frontend\config\ParamsClass;
use frontend\models\ColorModel;
use frontend\models\CatBigModel;
use frontend\models\CatMiddleModel;
use frontend\models\PurchaseModel;
use yii\data\Pagination;
/**
 * This is the model class for table "{{%product}}".
 *
 * @property string $product_id
 * @property string $purchase_id
 * @property string $product_sn
 * @property string $style_sn
 * @property string $model_sn
 * @property string $serial_num
 * @property string $name
 * @property string $img_url
 * @property string $color_id
 * @property string $size_id
 * @property string $brand_id
 * @property string $cat_b
 * @property string $cat_m
 * @property string $cat_s
 * @property string $season_id
 * @property string $level_id
 * @property string $wave_id
 * @property string $scheme_id
 * @property string $cost_price
 * @property string $price_level_id
 * @property string $memo
 * @property integer $type_id
 * @property string $disabled
 * @property string $is_error
 * @property integer $is_down
 */
class ProductModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['purchase_id', 'product_sn', 'style_sn', 'model_sn', 'serial_num', 'name', 'color_id', 'size_id', 'brand_id', 'cat_b', 'cat_m', 'cat_s', 'season_id', 'level_id', 'wave_id', 'scheme_id', 'cost_price', 'price_level_id'], 'required'],
            [['purchase_id', 'serial_num', 'color_id', 'size_id', 'brand_id', 'cat_b', 'cat_m', 'cat_s', 'season_id', 'level_id', 'wave_id', 'scheme_id', 'price_level_id', 'type_id', 'is_down'], 'integer'],
            [['cost_price'], 'number'],
            [['disabled', 'is_error'], 'string'],
            [['product_sn', 'style_sn', 'model_sn'], 'string', 'max' => 30],
            [['name', 'img_url'], 'string', 'max' => 128],
            [['memo'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'product_id' => 'Product ID',
            'purchase_id' => 'Purchase ID',
            'product_sn' => 'Product Sn',
            'style_sn' => 'Style Sn',
            'model_sn' => 'Model Sn',
            'serial_num' => 'Serial Num',
            'name' => 'Name',
            'img_url' => 'Img Url',
            'color_id' => 'Color ID',
            'size_id' => 'Size ID',
            'brand_id' => 'Brand ID',
            'cat_b' => 'Cat B',
            'cat_m' => 'Cat M',
            'cat_s' => 'Cat S',
            'season_id' => 'Season ID',
            'level_id' => 'Level ID',
            'wave_id' => 'Wave ID',
            'scheme_id' => 'Scheme ID',
            'cost_price' => 'Cost Price',
            'price_level_id' => 'Price Level ID',
            'memo' => 'Memo',
            'type_id' => 'Type ID',
            'disabled' => 'Disabled',
            'is_error' => 'Is Error',
            'is_down' => 'Is Down',
        ];
    }

    /**
     * 获取商品筛选条件数据，下拉框数据
     * @param array $data
     * @return mixed
     */
    public function getIndexFilter($data = [])
    {

        $result = $this->getFilter();

        $result['priceList'] = ParamsClass::$priceLevel;
        $result['catMiddle'] = [];
        if (!empty($data['catBig'])) {
            $result['catMiddle'] = CatMiddleModel::getCatMiddle($data['catBig']);
        }

        $result['catSmall'] = [];
        if (!empty($data['catMiddle'])) {
            $result['catSmall'] = CatSmallModel::getCatSmall($data['catMiddle']);
        }

        return $result;
    }
    /**
     * 查询数据
     */
    public function selectQueryRows($fields = '')
    {
        return self::find()->select([$fields])->where(['disabled' => 'false'])->groupBy($fields)->asArray()->all();
    }
    public function getFilter()
    {
        $result = Yii::$app->cache->get('product_filter');
        if (empty($result)) {
            $result['serialNum'] = $this->selectQueryRows('serial_num');
            $result['modelSn'] = $this->selectQueryRows('model_sn');
            $result['name'] = $this->selectQueryRows('name');
            
            $color = new ColorModel();
            $result['color'] = $color->getColor();

            $catBig = new CatBigModel();
            $result['catBig'] = $catBig->getCatBig();
        }
        Yii::$app->cache->set('product_filter', $result);
        return $result;
    }

    /**
     * 商品查询 ，根据关键字搜索出相应的结果
     * @param array $arr 搜索关键字
     * @param string $page 页码
     * @return array|mixed
     */
    public function productSearch($arr = [])
    {

        $query = self::find()
            ->where(['disabled' => 'false'])
            ->groupBy('serial_num')
            ->orderBy(['serial_num' => SORT_DESC]);
        if (!empty($arr['serialNum'])) {
            $query->andWhere(['p.serial_num' => $arr['serialNum']]);
        }
        if (!empty($arr['modelSn'])) {
            $query->andWhere(['p.model_sn' => $arr['modelSn']]);
        }
        if (!empty($arr['name'])) {
            $query->andWhere(['p.name' => $arr['name']]);
        }
        if (!empty($arr['catBig'])) {
            $query->andWhere(['p.cat_b' => $arr['catBig']]);
        }
        if (!empty($arr['catMiddle'])) {
            $query->andWhere(['p.cat_m' => $arr['catMiddle']]);
        }
        if (!empty($arr['catSmall'])) {
            $query->andWhere(['p.cat_s' => $arr['catSmall']]);
        }
        if (!empty($arr['color'])) {
            $query->andWhere(['p.color_id' => $arr['color']]);
        }
        if (!empty($arr['priceList'])) {
            $query->andWhere(['p.price_level_id' => $arr['priceList']]);
        }
        $newQuery = clone $query;
        $count = $newQuery->count();
        //分页
        $pagination = new Pagination(['totalCount' => $count, 'pageSize' => ParamsClass::$pageSize]);

        $query->alias('p')
            ->select(['p.serial_num', 'p.model_sn', 'p.name', 'b.cat_name', 'm.cat_name AS cat_middle', 'p.is_down', 's.small_cat_name', 'c.color_name', 'p.cost_price'])
            ->leftJoin('meet_color as c', 'p.color_id = c.color_id')
            ->leftJoin('meet_cat_big as b', 'p.cat_b = b.big_id')
            ->leftJoin('meet_cat_middle as m', 'm.middle_id= p.cat_m')
            ->leftJoin('meet_cat_big_small as s', 'p.cat_s=s.small_id');
        $query->offset($pagination->offset)
            ->limit($pagination->limit);
        $result = $query->asArray()->all();
        return ['result' => $result, 'pagination' => $pagination];
    }

    /**
     * 检查是否有错误信息
     * @return bool
     */
    public function isHaveError(){

        $result = self::find()->where(['is_error' => 'true'])->andWhere(['disabled' => 'false'])->count();
        return $result;
    }
    /**
     * 获取添加单个商品时的选择项
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function getAddProductFilter($data = [])
    {
        //获取订购会数据
        $purchaseModel = new PurchaseModel;
        $result['purchase'] = $purchaseModel->getPurchase();
        //获取品牌数据
        $brandModel = new BrandModel;
        $result['brand'] = $brandModel->getBrand();
        //色系信息
        $schemeModel = new SchemeModel;
        $result['scheme'] = $schemeModel->getScheme();
        //获取尺码组
        $result['sizeGroup'] = (new Query)->select(['size_group_code', 'group_id', 'size_group_name'])
            ->from('meet_size_group')
            ->all();
        //等级表
        $levelModel = new LevelModel;
        $result['level'] = $levelModel->getLevel();

        //波段表
        $waveModel = new WaveModel;
        $result['wave'] = $waveModel->getWave();

        //大分类
        $catBigModel = new CatBigModel;
        $result['catBig'] = $catBigModel->getList();
        //颜色。
        $colorModel = new ColorModel;
        $result['color'] = $colorModel->getColor();
        //类型
        $result['type'] = TypeModel::getType();
        $result['season'] = $result['catMiddle'] = $result['catSmall'] = [];
        /**
         * 这些都可以通过ajax来请求
         */
        // if (!empty($data['cat_b'])) {
        //     //大分类含有的季节
        //     $result['season'] = (new Query)->select(['season_id', 'season_name'])
        //         ->from('meet_season_big')
        //         ->where(['big_id' => $data['cat_b']])
        //         ->all();
        //     $result['catMiddle'] = CatMiddleModel::getCatMiddle($data['cat_b']);
        //     $result['catSmall'] = (new Query)->select(['small_id', 'small_cat_name AS cat_name'])
        //         ->from('meet_cat_big_small')
        //         ->where(['big_id' => $data['cat_b']])
        //         ->all();
        // }

        // if(!empty($data['sizeGroup'])){
        //     $result['size'] = (new Query)->select(['size_id', 'size_name'])
        //         ->from('meet_size')
        //         ->where(['group_id' => $data['sizeGroup']])
        //         ->all();
        // }

        return $result;
    }

    /**
     * 缓存指定订购会所有产品 包括下架的
     * @return [type] [description]
     */
    public function productListCache()
    {
        $purchaseId = Yii::$app->session->get('purchase_id');
        $list = Yii::$app->cache->get('all-product-list-without-down-' . $purchaseId);
        if (empty($list)) {
            $list = self::find()
                ->where(['purchase_id' => $purchaseId])
                ->andWhere(['disabled' => 'false'])
                ->orderBy(['serial_num' => SORT_ASC])
                ->asArray()
                ->all();
            Yii::$app->cache->set('all-product-list-without-down-' . $purchaseId, $list, 86400);
        }
        return $list;
    }

    /**
     * 商品搜索
     * @param $conArr  搜索条件
     * @param $serial   搜索型号
     * @param $params   小条件
     * @param int $price  价格排序
     * @param int $page  页码
     * @param int $pagesize
     * @return array
     */
    public function newitems($conArr, $serial, $params, $price = 1, $page = 1, $pagesize = 8){
        
        //根据输入框的长度来判断是否是 model_sn型号 还是 serial_num 流水号查询 出去重的 style_sn 款号
        if(strlen($serial) >4){
            //获取查询的去重的款号 的型号  
            $row = self::find()->select(['style_sn'])
                ->where(['like', 'model_sn', $serial.'%', false])//右模糊
                ->andWhere(['disabled' => 'false'])
                ->andWhere(['is_down' => 0])
                ->andWhere(['purchase_id' => $params['purchase_id']])
                ->distinct()
                ->all();

            if (empty($row)) return [];
            //根据查询出的款号 和 搜索条件 获取商品的详细信息
            $items = $this->listStyleSn($row, $params, $conArr);
        }else{
            if (!empty($serial)) {
                //流水号
                $row = self::find()->select(['style_sn'])
                ->where(['serial_num', $serial])
                ->andWhere(['disabled' => 'false'])
                ->andWhere(['is_down' => 0])
                ->andWhere(['purchase_id' => $params['purchase_id']])
                ->distinct()
                ->all();

                if (empty($row)) return [];
                $items = $this->listStyleSn($row, $params, $conArr);
            }else{

                $style_sn = '';
                $items = $this->listSerial($style_sn, $params, $conArr);
            }
        }
        //人气排序 1:降序  2:升序
        $hits_sort = [];
        if ($params['hits'] && !empty($items)) {
            //根据下单数量来定义人气
            $order_item_list = (new Query)->select(['style_sn', 'SUM(nums) AS num'])
            ->from('meet_order_items')
            ->where(['disabled' => 'false'])
            ->groupBy('style_sn')
            ->all();
            foreach ($order_item_list as $v) {
                $order_item_list[$v['style_sn']] = $v['num'];
            }

            foreach ($items as $k => $v) {
                $num = isset($order_item_list[$v['style_sn']]) ? $order_item_list[$v['style_sn']] : 0;
                $items[$k]['hit_num'] = $num;
                $hits_sort[$k] = $num;
            }

            $sort2 = $params['hits'] == 2 ? SORT_ASC : SORT_DESC;
            array_multisort($hits_sort, $sort2, $items);
        }

        //价格升降排序 1:升序  2:降序
        $price_sort = [];
        if ($price && !empty($items)) {
            foreach ($items as $k => $v) {
                $price_sort[$k] = $v['cost_price'];
            }
            $sort1 = $price == 2 ? SORT_ASC : SORT_DESC;
            array_multisort($price_sort, $sort1, $items);
        }
        //这里可以根据查询条件进行缓存的，这样分页太差劲了
        //分页超出
        if (($page - 1) * $pagesize > count($items)) return [];
        //从数组中取出指定分页需要的数据
        return array_slice($items, ($page - 1) * $pagesize, $pagesize);
    }

    /**
     * 指定型号下的商品搜索
     * @param array $style_sn   去重的 搜索指定型号model_sn的 款号style_sn
     * @param $params
     * @param $conArr
     * @return array]
     */
    public function listStyleSn($style_sn, $params, $conArr)
    {
        //尺码表获取所有的尺码  
        $size_list = $this->gitSizes();
        //获取该订购会下所有上线的商品
        $list = $this->getProductUp();

        //获取客户订单详细信息
        $order_row = $this->getOrderInfo($params['purchase_id'], $params['customer_id']);

        $items_model_sn = [];
        //记录客户下单的款号style_sn
        foreach ($order_row as $v) {
            $items_model_sn[] = $v['style_sn'];
        }
        $items = [];
        // style_sn款号的处理,查找产品
        foreach ($style_sn as $s) {
            foreach ($list as $v) {
                //款号筛选
                if ($s['style_sn'] && ($v['style_sn'] != $s['style_sn'])) continue;

                //搜索已订条件的产品
                if ($params['or'] == 1 && !in_array($v['style_sn'], $items_model_sn)) continue;
                //搜索未订购条件的产品
                if ($params['or'] == 2 && in_array($v['style_sn'], $items_model_sn)) continue;

                $item = $v;
                //筛选条件
                $item['search_id'] = [
                    's_id_' . $v['cat_b'],
                    'c_id_' . $v['cat_s'],
                    'sd_' . $v['season_id'],
                    'wv_' . $v['wave_id'],
                    'lv_' . $v['level_id'],
                    'plv_' . $v['price_level_id'],
                ];

                //根据筛选条件进行筛选  
                //根据该条记录拼接数来的筛选条件和用户传过来的筛选条件进行交集，看是否等于用户的筛选条件，如果等于则符合用户筛选
                if (array_intersect($conArr, $item['search_id']) != $conArr) continue;

                //该商品是否已订 
                $item['is_order'] = isset($items_model_sn) && in_array($v['style_sn'], $items_model_sn) ? 1 : 2;

                //尺码
                //款号style_sn 相同则尺寸信息相同  
                //获取一个style_sn 下产品的所有尺寸,以及商品信息
                /*
                 [size] => Array
                (
                    [4] => L
                    [3] => M
                    [5] => XL
                )

            [size_item] => Array
                (
                    [0] => Array
                        (
                            [product_id] => 915
                            [product_sn] => 143209020163001
                            [size_name] => L
                        )

                    [1] => Array
                        (
                            [product_id] => 916
                            [product_sn] => 143209020163002
                            [size_name] => M
                        )

                    [2] => Array
                        (
                            [product_id] => 917
                            [product_sn] => 143209020163003
                            [size_name] => XL
                        )

                )
                 */
                if (isset($items[$v['style_sn']])) {
                    $item['size'] = $items[$v['style_sn']]['size'];
                    $item['size_item'] = $items[$v['style_sn']]['size_item'];
                }

                if (!isset($item['size']) || !in_array($size_list[$v['size_id']], $item['size'])) {
                    $item['size'][$v['size_id']] = $size_list[$v['size_id']];
                }
                $row['product_id'] = $v['product_id'];
                $row['product_sn'] = $v['product_sn'];
                $row['size_name'] = $size_list[$v['size_id']];//尺码
                $item['size_item'][] = $row;
                $items[$v['style_sn']] = $item;//款号的信息
            }
        }
        return $items;
    }
    /**
     * 搜索框为空是搜索的产品
     * @return [type] [description]
     */
    public function listSerial($style_sn, $params, $conArr)
    {
        //尺码表获取所有的尺码  
        $size_list = $this->gitSizes();
        //获取该订购会下所有上线的商品
        $list = $this->getProductUp();

        //获取客户订单详细信息
        $order_row = $this->getOrderInfo($params['purchase_id'], $params['customer_id']);

        $items_model_sn = [];
        //记录客户下单的款号style_sn
        foreach ($order_row as $v) {
            $items_model_sn[] = $v['style_sn'];
        }
        $items = [];
        foreach ($list as $v) {
            //款号筛选
            if ($style_sn && ($v['style_sn'] != $style_sn)) continue;

            //搜索已订条件的产品
            if ($params['or'] == 1 && !in_array($v['style_sn'], $items_model_sn)) continue;
            //搜索未订购条件的产品
            if ($params['or'] == 2 && in_array($v['style_sn'], $items_model_sn)) continue;

            $item = $v;
            //筛选条件
            $item['search_id'] = [
                's_id_' . $v['cat_b'],
                'c_id_' . $v['cat_s'],
                'sd_' . $v['season_id'],
                'wv_' . $v['wave_id'],
                'lv_' . $v['level_id'],
                'plv_' . $v['price_level_id'],
            ];

            //根据筛选条件进行筛选  
            //根据该条记录拼接数来的筛选条件和用户传过来的筛选条件进行交集，看是否等于用户的筛选条件，如果等于则符合用户筛选
            if (array_intersect($conArr, $item['search_id']) != $conArr) continue;

            //该商品是否已订 
            $item['is_order'] = isset($items_model_sn) && in_array($v['style_sn'], $items_model_sn) ? 1 : 2;

            //尺码
            //款号style_sn 相同则尺寸信息相同  
            //获取一个style_sn 下产品的所有尺寸,以及商品信息
            if (isset($items[$v['style_sn']])) {
                $item['size'] = $items[$v['style_sn']]['size'];
                $item['size_item'] = $items[$v['style_sn']]['size_item'];
            }

            if (!isset($item['size']) || !in_array($size_list[$v['size_id']], $item['size'])) {
                $item['size'][$v['size_id']] = $size_list[$v['size_id']];
            }
            $row['product_id'] = $v['product_id'];
            $row['product_sn'] = $v['product_sn'];
            $row['size_name'] = $size_list[$v['size_id']];//尺码
            $item['size_item'][] = $row;
            $items[$v['style_sn']] = $item;//款号的信息
        }
        return $items;
    }
    /**
     * 获取尺码数据
     * @return [type] [description]
     */
    public function getSizes()
    {
        $items = Yii::$app->cache->get('size-id-list');
        if (empty($items)) {
            $result = (new Query)->select(['size_name', 'size_id'])
                ->from('size')
                ->all();
            $items = [];
            foreach ($result as $row) {
                $items[$row['size_id']] = $row['size_name'];
            }
            Yii::$app->cache->set('size-id-list', $items);
        }
        return $items;
    }
    /**
     * 获取指定订购会下所有上架的商品
     * @return [type] [description]
     */
    public function getProductUp()
    {
        $purchaseId = Yii::$app->session['purchase_id'];
        $items = Yii::$app->cache->get('product-list-'.$purchaseId);
        if (empty($items)) {
            $items = self::find()
                ->where(['purchase_id' => $purchaseId])
                ->andWhere(['disabled' => 'false'])
                ->andWhere(['is_down' => 0])
                ->orderBy(['serial_num' => SORT_ASC])
                ->asArray()
                ->all();
            Yii::$app->cache->set('product-list-'.$purchaseId, $items, 3600*24);
        }
        return $list;
    }
    /**
     * 获取客户订单详情
     * @param  [type] $purchaseId [description]
     * @param  [type] $customerId [description]
     * @return [type]             [description]
     */
    public function getOrderInfo($purchaseId, $customerId)
    {
        //获取订单详情
        $items = (new Query)->select(['oi.nums', 'oi.product_id', 'oi.style_sn', 'oi.model_sn'])
            ->from('meet_order as order')
            ->leftJoin('meet_order_items as oi', 'order.order_id = oi.order_id')
            ->where(['order.disabled' => 'false'])
            ->andWhere(['oi.disabled' => 'false'])
            ->andWhere(['order.purchase_id' => $purchaseId])
            ->andWhere(['order.customer_id' => $customerId])
            ->all();

        if (empty($items)) {
            return [];
        }
        //上架商品id
        $upProductIds = self::find()->select(['product_id'])
            ->where(['is_down' => 0])
            ->andWhere(['disabled' => 'false'])
            ->andWhere(['purchase_id' => $purchaseId])
            ->indexBy('product_id')
            ->asArray()->all();
        $model = [];
        foreach ($items as $item) {
            $model[$item['product_id']] = $item;
            $model[$item['product_id']]['is_down'] = isset($upProductIds['purchase_id'])?0:1;
        }
        return $model;
    }

}
