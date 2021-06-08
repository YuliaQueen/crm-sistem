<?php

namespace backend\modules\rbac;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Class RbacAsset
 *
 * @package yii2mod\rbac
 */
class RbacAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@backend/modules/rbac/assets';

    /**
     * @var array
     */
    public $js = [
        'js/rbac.js',
    ];

    /**
     * @var array
     */
    public $css = [
        'css/rbac.css',
    ];

    /**
     * @var array
     */
    public $depends = [
        YiiAsset::class,
    ];
}
