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
        $accToken = UserService::makeAccessToken($loginUid, $agUuid, 7200);
        $tokenName = getConf('login', 'tokenName');

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'listUrl' => makeUrl('manage/attach/list'),
            'editUrl' => makeUrl('manage/attach/edit'),
            'delUrl' => makeUrl('manage/attach/del'),
            'multiUrl' => makeUrl('manage/attach/multi'),
            'uploadUrl' => makeUrl('api/upload/single', [$tokenName=>$accToken, 'use_title'=>1]),
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
        $info = Attach::findFirst($id);

        if(empty($id) || empty($info)) {
            return $this->alert('该信息不存在');
        }

        $lock = getlockBackendOperate('editAttach', $id, $loginUid);
        if(empty($lock) || $lock<=0) {
            return $this->alert("该信息已被其他后台用户[".abs($lock)."]锁定，您不能操作！");
        }

        $sites = yield $this->getAllowSites();
        $authStatusArr = Attach::getAuthStatusArr();
        $persistentStatusArr = Attach::getPersistentStatusArr();
        $hasThirdArr = Attach::getHasThirdArr();
        $belongTypeArr = Attach::getBelongTypeArr();
        $fileTypeArr = Attach::getFileTypeArr();
        $tagArr = Attach::getTagArr();

        $info = Attach::rowToObject($info);
        $info->is_del = $info->is_del ? '已删' : '正常';
        $info->compress_enable = $info->compress_enable ? '是' : '否';
        $info->site_id = $sites[$info->site_id] ?? '';
        $info->is_auth = $authStatusArr[$info->is_auth] ?? '';
        $info->is_persistent = $persistentStatusArr[$info->is_persistent] ?? '';
        $info->has_third = $hasThirdArr[$info->has_third] ?? '';
        $info->belong_type = $belongTypeArr[$info->belong_type] ?? '';
        $info->file_type = $fileTypeArr[$info->file_type] ?? '';
        $info->tag = $tagArr[$info->tag] ?? '';

        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'saveUrl' => makeUrl('manage/attach/save'),
            'uploadUrl' => '',
            'id' => $id,
            'row' => $info,
        ]);

        return null;
    }


    /**
     * @title -保存附件信息
     * @desc  -保存附件信息
     */
    public function saveAction() {
        $loginUid = $this->getLoginUid();
        $id = intval($this->getRequest('id'));
        $title = $this->getPost('title');

        if(empty($id)) {
            return $this->fail(20104, '参数错误');
        }elseif (empty($title)) {
            return $this->fail('标题不能为空');
        }

        $info = Attach::findFirst($id);
        if(empty($info)) {
            return $this->fail('该信息不存在');
        }

        if($info->title != $title) {
            $now = time();
            if(ValidateHelper::isEnglish($title)) {
                $title = substr($title, 0, 30);
            }else{
                $title = StringHelper::cutStr($title, 20, 0, '');
            }

            $data = [
                'title' => $title,
                'update_time' => $now,
                'update_by' => $loginUid,
            ];
            $res = Attach::upData($data, ['id'=>$id]);
            if(!$res) return $this->fail('操作失败,请稍后再试');
        }

        return $this->success();
    }


    /**
     * @title -批量操作附件
     * @desc  -批量操作附件
     */
    public function multiAction() {
        //TODO
        return $this->success();
    }



}