<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/6/26
 * Time: 11:30
 * Desc:
 */

namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;


/**
 * Class 后台缓存管理控制器
 * @package Apps\Modules\Manage\Controllers
 */
class CacheController extends Controller {

    /**
     * @title -清除缓存
     * @desc  -清除缓存
     */
    public function clearCacheAction() {
        return $this->success();
    }

}