<?php
namespace frontend\controllers;

use yii\web\Controller;
/**
 * 前台登录页
 *
 * @author        丁冉
 */

class UserController extends Controller
{
    /**
     * 用户登录
     */
    public function actionIndex()
    {
        $this->layout = '/column_user';
        $error = false;
        if ($_POST) {
            $account = $_POST['account'];
            $password = $_POST['password'];

            $userModel = new User();
            // var_dump($account,md5(md5($password)));exit;
            $item = $userModel->login($account,md5(md5($password)));
            Yii::app()->session['code'] = $item['code'];
            if ($item) {
                $nowTime = time();
                Yii::app()->session['customer_id'] = $item['customer_id'];
                Yii::app()->session['purchase_id'] = $item['purchase_id'];
                Yii::app()->session['name'] = $item['name'];
                Yii::app()->session['mobile'] = $item['mobile'];
                Yii::app()->session['type'] = $item['type'];
                Yii::app()->session['province'] = $item['province'];
                Yii::app()->session['area'] = $item['area'];
                Yii::app()->session['target'] = $item['target'];
                Yii::app()->session['login_time'] = $nowTime;
                $userModel->userLoginLog($item['customer_id'], $nowTime);
                $this->redirect('/default/index');
            } else {
                $error = true;
            }
        }

        return $this->render('index',['error'=>$error]);
    }

    /**
     * 用户退出
     */
    public function actionLogout()
    {
        unset(Yii::app()->session['customer_id']);
        unset(Yii::app()->session['purchase_id']);
        unset(Yii::app()->session['name']);
        unset(Yii::app()->session['mobile']);
        unset(Yii::app()->session['type']);
        unset(Yii::app()->session['province']);
        unset(Yii::app()->session['area']);
        unset(Yii::app()->session['target']);
        unset(Yii::app()->session['login_time']);

        $this->redirect('index');
    }

    /**
     * 删除缓存
     */
    public function actionCache()
    {
        Yii::app()->cache->flush();
        echo "清除缓存";
    }
}