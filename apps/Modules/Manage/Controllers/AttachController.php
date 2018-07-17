<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/7/16
 * Time: 9:47
 * Desc:
 */

namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;
use Apps\Models\Attach;
use Apps\Models\UserBase;
use Apps\Services\AttachService;
use Apps\Services\UserService;
use Apps\Services\UploadService;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;


/**
 * Class 后台附件管理控制器
 * @package Apps\Modules\Manage\Controllers
 */
class AttachController extends Controller {

    public function initialize () {
        yield parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -附件管理首页
     * @desc  -附件管理首页
     */
    public function indexAction() {
        //站点
        $sites = yield $this->getAllowSites();

        $authStatusArr = Attach::getAuthStatusArr();
        $persistentStatusArr = Attach::getPersistentStatusArr();
        $hasThirdArr = Attach::getHasThirdArr();
        $belongTypeArr = Attach::getBelongTypeArr();
        $fileTypeArr = Attach::getFileTypeArr();
        $tagArr = Attach::getTagArr();

        $loginUid = $this->getLoginUid();
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $accToken = UserService::makeAccessToken($loginUid, $agUuid, 1800);
        $tokenName = getConf('login', 'tokenName');

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'listUrl' => makeUrl('manage/attach/list'),
            'editUrl' => makeUrl('manage/attach/edit'),
            'delUrl' => makeUrl('manage/attach/del'),
            'multiUrl' => makeUrl('manage/attach/multi'),
            'uploadUrl' => makeUrl('api/upload/single', [$tokenName=>$accToken]),
            'sites' => json_encode($sites),
            'authStatusArr' => json_encode($authStatusArr),
            'persistentStatusArr' => json_encode($persistentStatusArr),
            'hasThirdArr' => json_encode($hasThirdArr),
            'belongTypeArr' => json_encode($belongTypeArr),
            'fileTypeArr' => json_encode($fileTypeArr),
            'tagArr' => json_encode($tagArr),
        ]);

        return null;
    }


    /**
     * @title -附件列表JSON
     * @desc  -附件列表JSON
     */
    public function listAction() {
        list($pageNumber, $pageSize) = $this->getPageNumberNSize();
        $sortName = trim($this->getGet('sortName'));
        $sortOrder = trim($this->getGet('sortOrder'));
        if($sortName && $sortOrder) {
            $order = "{$sortName} {$sortOrder}";
        }else{
            $order = 'update_timedesc,id desc';
        }

        //基本条件
        $sites = yield $this->getAllowSites();
        $siteIds = array_keys($sites);
        $where = [
            'and',
            ['site_id' => $siteIds],
        ];

        //搜索条件
        $filters = json_decode($this->getGet('filter'), true);
        $ops = json_decode($this->getGet('op'), true);
        if(!empty($ops)) {
            foreach ($ops as $field=>$op) {
                $value = trim($filters[$field]);
                if($value!=='') {
                    if(in_array($field, ['title'])) {
                        array_push($where, ['like',$field,"%{$value}%"]);
                    }else{
                        array_push($where, [$field=>$value]);
                    }
                }
            }
        }

        $paginator = Attach::getPaginator('*', $where, $order, $pageSize, $pageNumber);
        $pageObj = $paginator->getPaginate();

        $list = $pageObj->items->toArray();
        if(!empty($list)) {
            $authStatusArr = Attach::getAuthStatusArr();
            $persistentStatusArr = Attach::getPersistentStatusArr();
            $hasThirdArr = Attach::getHasThirdArr();
            $belongTypeArr = Attach::getBelongTypeArr();
            $fileTypeArr = Attach::getFileTypeArr();
            $tagArr = Attach::getTagArr();

            $uids = array_column($list, 'update_by');
            $admList = UserBase::getList(['uid'=>$uids]);
            if($admList) $admList = $admList->toArray();

            foreach ($list as &$item) {
                $usr = ArrayHelper::arraySearchItem($admList, ['uid'=>$item['update_by']]);
                $item['username'] = $usr['username']??'';
                $item['site_id'] = $sites[$item['site_id']] ?? '未知';
                $item['is_auth'] = $authStatusArr[$item['is_auth']] ?? '-';
                $item['is_persistent'] = $persistentStatusArr[$item['is_persistent']] ?? '-';
                $item['has_third'] = $hasThirdArr[$item['has_third']] ?? '-';
                $item['belong_type'] = $belongTypeArr[$item['belong_type']] ?? '-';

                $item['file_type_vue'] = $item['file_type'];
                $item['file_type'] = $fileTypeArr[$item['file_type']] ?? '-';

                $item['tag'] = $tagArr[$item['tag']] ?? '-';
                $item['file_size'] = intval($item['file_size']/1024);

                $item['url'] = yield AttachService::formatAttachUrl($item['file_path']);

            }
            unset($item);
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


    /**
     * @title -附件编辑页
     * @desc  -附件编辑页
     */
    public function editAction() {
        $loginUid = $this->getLoginUid();
        $id = intval($this->getGet('ids'));
        $info = [];





    }



}