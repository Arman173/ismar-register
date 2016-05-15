<?php
namespace app\commands;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
	public function actionInit()
    {
		$auth = Yii::$app->authManager;
		$administrator = $auth->createRole('administrator');
		$auth->add($administrator);
		$auth->assign($administrator, 1);
	}
	
	public function actionSecond()
	{
		$auth = Yii::$app->authManager;
		$administrator = $auth->getRole('administrator');
		$auth->assign($administrator,2);
	}
	
	public function actionThird()
	{
		$auth = Yii::$app->authManager;
		$superuser = $auth->createRole('superuser');
		$auth->add($superuser);
		$administrator = $auth->getRole('administrator');
		$auth->addChild($superuser,$administrator);
		$auth->assign($superuser,3);
	}
}











?>