<?php


namespace SamIT\abac\connectors\yii2;


use yii\web\Controller;

class PermissionController extends Controller
{

    public function actionRevoke($source_name, $source_id, $target_name, $target_id, $permission)
    {
        /** @var Manager $manager */
        $manager = \Yii::$app->authManager;
        $manager->revokeById($source_name, $source_id, $target_name, $target_id, $permission);
        return $this->renderContent('test');
    }

}