<?php

namespace common\helpers;

use Yii;


class Cron {

    private static $pid;

    function __construct() {}

    function __clone() {}

    private static function isrunning() {
        $pids = explode(PHP_EOL, `ps -e | awk '{print $1}'`);
        if(in_array(self::$pid, $pids))
            return TRUE;
        return FALSE;
    }

    public static function lock($uni_key) {

        $LOCK_DIR = Yii::$app->params['LOCK_DIR'];
        $LOCK_SUFFIX = Yii::$app->params['LOCK_SUFFIX'];

        $lock_file = $LOCK_DIR.$uni_key.$LOCK_SUFFIX;

        if(file_exists($lock_file)) {
            //return FALSE;

            // Is running?
            self::$pid = file_get_contents($lock_file);
            if(self::isrunning()) {
                Yii::error("==".self::$pid."== Already in progress...", __METHOD__);
                return FALSE;
            }
            else {
                Yii::error("==".self::$pid."== Previous job died abruptly...", __METHOD__);
            }
        }

        self::$pid = getmypid();
        file_put_contents($lock_file, self::$pid);
        Yii::info("==".self::$pid."== Lock acquired, processing the job...", __METHOD__);
        return self::$pid;
    }

    public static function unlock($uni_key) {

        $LOCK_DIR = Yii::$app->params['LOCK_DIR'];
        $LOCK_SUFFIX = Yii::$app->params['LOCK_SUFFIX'];

        $lock_file = $LOCK_DIR.$uni_key.$LOCK_SUFFIX;

        if(file_exists($lock_file))
            unlink($lock_file);

        Yii::info("==".self::$pid."== Releasing lock...", __METHOD__);

        return TRUE;
    }

}

?>