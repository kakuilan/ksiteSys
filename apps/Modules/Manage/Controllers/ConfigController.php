<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2018/3/29
 * Time: 9:21
 * Desc:
 */

namespace Apps\Modules\Manage\Controllers;

use Apps\Modules\Manage\Controller;
use Apps\Models\Config;
use Apps\Models\UserBase;
use Apps\Services\UserService;
use Lkk\Helpers\ArrayHelper;

/**
 * Class 后台配置控制器
 * @package Apps\Modules\Manage\Controllers
 */
class ConfigController extends Controller {

    public function initialize () {
        parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    public function indexAction() {
        //站点
        $sites = [
            '0' => '系统平台',
            $this->siteId => '本站',
        ];

        //数据类型
        $dataTypes = Config::getDataTypeArr();
        //控件类型
        $inputTypes = Config::getInputTypeArr();

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'listUrl' => makeUrl('manage/config/list'),
            'editUrl' => makeUrl('manage/config/edit'),
            'delUrl' => makeUrl('manage/config/del'),
            'multiUrl' => makeUrl('manage/config/multi'),
            'uploadUrl' => '',
            'sites' => json_encode($sites),
            'dataTypes' => json_encode($dataTypes),
            'inputTypes' => json_encode($inputTypes),
        ]);

        return null;
    }


    public function listAction() {
        list($pageNumber, $pageSize) = $this->getPageNumberNSize();
        $sortName = trim($this->getGet('sort'));
        $sortOrder = trim($this->getGet('order'));
        if($sortName && $sortOrder) {
            $order = "{$sortName} {$sortOrder}";
        }else{
            $order = 'id desc';
        }

        //基本条件
        $isAdmin = true;
        $siteIds = [$this->siteId];
        if($isAdmin) array_push($siteIds, 0);
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
                    if($field=='key') {
                        array_push($where, ['like','key',"%{$value}%"]);
                    }else{
                        array_push($where, [$field=>$value]);
                    }
                }
            }
        }

        $paginator = Config::getPaginator('*', $where, $order, $pageSize, $pageNumber);
        $pageObj = $paginator->getPaginate();

        $list = $pageObj->items->toArray();
        if(!empty($list)) {
            $uids = array_column($list, 'update_by');
            $admList = UserBase::getList(['uid'=>$uids]);
            if($admList) $admList = $admList->toArray();

            foreach ($list as &$item) {
                $usr = ArrayHelper::arraySearchItem($admList, ['uid'=>$item['update_by']]);
                $item['username'] = $usr['username']??'';

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
        $id = intval($this->getGet('ids'));
        $info = [];

        if($id) {
            $info = Config::findFirst($id);
            if(empty($info) || $info->is_del) {
                return $this->fail('信息不存在或已删除');
            }
        }

        //站点
        $sites = [
            '0' => '系统平台',
            $this->siteId => '本站',
        ];

        //数据类型
        $dataTypes = Config::getDataTypeArr();
        //控件类型
        $inputTypes = Config::getInputTypeArr();

        $loginUid = $this->getLoginUid();
        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $accToken = UserService::makeAccessToken($loginUid, $agUuid, 1800);
        $tokenName = getConf('login', 'tokenName');

        //视图变量
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'saveUrl' => makeUrl('manage/config/save'),
            'uploadUrl' => makeUrl('api/upload/single', [$tokenName=>$accToken]),
            'row' => $info ? Config::rowToObject($info) : $info,
            'dataTypes' => json_encode($dataTypes),
            'inputTypes' => json_encode($inputTypes),
        ]);

        return null;
    }

    public function saveAction() {

    }


    public function delAction() {
        $ids = (array)$this->getPost('ids');
        $ids = array_filter($ids, function ($v) {
            if(!is_numeric($v) || $v<=0) return false;
            return true;
        });

        $idsNum = count($ids);
        $now = time();
        $sucNum = 0;

        if($idsNum==0) {
            return $this->fail('id不能为空');
        }elseif ($idsNum>10) {
            return $this->fail('每批最多只能删10个');
        }



    }










}