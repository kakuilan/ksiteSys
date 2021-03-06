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
use Lkk\Helpers\DirectoryHelper;


/**
 * Class 后台缓存管理控制器
 * @package Apps\Modules\Manage\Controllers
 */
class CacheController extends Controller {

    public function initialize () {
        yield parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -清除缓存
     * @desc  -清除缓存
     */
    public function clearCacheAction() {
        //清空视图模板缓存
        $viewConf = getConf('view');
        if(is_dir($viewConf->compiledPath)) {
            DirectoryHelper::emptyDir($viewConf->compiledPath);
        }

        unset($viewConf);

        return $this->success();
    }

}