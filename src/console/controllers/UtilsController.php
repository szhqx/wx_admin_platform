<?php

namespace console\controllers;

use Yii;

use common\helpers\Cron;
use common\helpers\WechatHelper;

/**
 * Utils controller
 */
class UtilsController extends BaseController {

    // clean tmp files at regular time
    public function actionCleanTmp() {

        $unique_key = md5(__METHOD__);
        $params = Yii::$app->params;

        if(Cron::lock($unique_key) !== FALSE) {

            try {
                $script_path = dirname(__FILE__);
                shell_exec(sprintf('bash %s/clean.sh', $script_path));
            } catch(\Exception $e) {
                Yii::error(sprintf("Fail to clean temp dir cos reason:(%s).\n", $e->getMessage()), __METHOD__);
            }

            Cron::unlock($unique_key);
        }
    }

}
