<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/7/5
 * Time: 14:37
 * Desc:
 */

namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;
use Apps\Models\Site;
use Apps\Models\UserBase;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;

/**
 * Class 后台站点管理控制器
 * @package Apps\Modules\Manage\Controllers
 */
class SiteController extends Controller {

    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    public function indexAction() {
        $statusArr = Site::getStatusArr();

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'listUrl' => makeUrl('manage/site/list'),
            'editUrl' => makeUrl('manage/site/edit'),
            'delUrl' => makeUrl('manage/site/del'),
            'multiUrl' => '',
            'uploadUrl' => '',
            'statusArr' => json_encode($statusArr),
        ]);

        return null;
    }


    public function listAction() {
        list($pageNumber, $pageSize) = $this->getPageNumberNSize();
        $sortName = trim($this->getGet('sortName'));
        $sortOrder = trim($this->getGet('sortOrder'));
        if($sortName && $sortOrder) {
            $order = "{$sortName} {$sortOrder}";
        }else{
            $order = 'site_id desc';
        }

        //基本条件
        $isAdmin = true;
        if($isAdmin) {
            $where = [
                'and',
                ['neq', 'site_id', 0],
            ];
        }else{
            $where = [
                'and',
                ['site_id' => $this->siteId],
            ];
        }

        //搜索条件
        $filters = json_decode($this->getGet('filter'), true);
        $ops = json_decode($this->getGet('op'), true);
        if(!empty($ops)) {
            foreach ($ops as $field=>$op) {
                $value = trim($filters[$field]);
                if($value!=='') {
                    if($field=='site_url') {
                        array_push($where, ['like','site_url',"%{$value}%"]);
                    }else{
                        array_push($where, [$field=>$value]);
                    }
                }
            }
        }

        $paginator = Site::getPaginator('*', $where, $order, $pageSize, $pageNumber);
        $pageObj = $paginator->getPaginate();

        $list = $pageObj->items->toArray();
        if(!empty($list)) {
            $uids = array_column($list, 'update_by');
            $admList = UserBase::getList(['uid'=>$uids]);
            if($admList) $admList = $admList->toArray();
            $statusArr = Site::getStatusArr();

            foreach ($list as &$item) {
                $usr = ArrayHelper::arraySearchItem($admList, ['uid'=>$item['update_by']]);
                $item['username'] = $usr['username']??'';
                $item['status'] = $statusArr[$item['status']] ?? '未知';
            }
        }

        $res = [
            'total' => $pageObj->total_items, //总记录数
            'currPage' => $pageNumber, //当前页码
            'pageSize' => $pageSize, //每页数量
            'pageTotal' => $pageObj->total_pages, //总页数
            'rows' => $list, //分页列表数据
        ];

        return $this->success($res);
    }



    public function editAction() {
        $loginUid = $this->getLoginUid();
        $id = intval($this->getGet('ids'));
        $info = [];

        if(empty($id)) {
            return $this->alert('站点ID不能为空');
        }

        $info = Site::findFirst($id);
        if(empty($info)) {
            return $this->alert('该信息不存在或已删除');
        }

        $statusArr = Site::getStatusArr();
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'saveUrl' => makeUrl('manage/site/save'),
            'uploadUrl' => '',
            'id' => $id,
            'statusArr' => json_encode($statusArr),
        ]);

        return null;
    }


    public function saveAction() {

    }



}