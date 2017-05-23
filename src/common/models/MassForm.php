<?php

namespace common\models;

use Yii;
use yii\base\Model;

use common\models\Mass;
use common\models\FansTag;


/**
 * 群发记录
 */
class MassForm extends Model
{
    # model fields
    public $material_id;
    public $official_account_id;
    public $pub_at;
    public $user_id;
    public $user_tag_id;
    public $user_sex;
    public $user_area;
    public $status;

    # post fields
    public $type;
    public $media_id;

    const SCENARIO_CREATE_MASS = 'create/mass';
    const SCENARIO_MODIFY_MASS = 'modify/mass';
    const SCENARIO_DELETE_MASS = 'delete/mass';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            ["material_id", "integer"],
            ["material_id", "required", "on"=>[self::SCENARIO_CREATE_MASS]],

            ["official_account_id", "integer"],

            ["pub_at", "integer"],

            ["user_id", "integer"],

            ["type", "integer"],

            ["media_id", "string"],

            ["user_tag_id", "integer"],

            ["user_sex", "integer"],

            ["user_area", "string"],

            ["status", "integer"]
        ];
    }

    // TODO add support for specific user openid list
    public function create() {

        if($this->validate()) {

            $mass = new Mass();
            $now = time();

            $mass->user_id = $this->user_id;
            $mass->material_id = $this->material_id;
            $mass->official_account_id = $this->official_account_id;
            $mass->created_at = $now;
            $mass->updated_at = $now;

            if($this->pub_at) {
                $mass->pub_at = $this->pub_at;
            }

            if($this->user_tag_id) {
                $mass->user_tag_id = $this->user_tag_id;
            }

            if($mass->save(false)) {
                return $mass;
            }
        }

        return;
    }
}