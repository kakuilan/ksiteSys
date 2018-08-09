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
use Lkk\Helpers\StringHelper;
use Lkk\Helpers\ValidateHelper;

/**
 * Class 后台配置控制器
 * @package Apps\Modules\Manage\Controllers
 */
class ConfigController extends Controller {

    public function initialize () {
        yield parent::initialize();

        $this->setHeaderSeo('管理后台', '关键词', '描述');

        //视图变量
        $this->view->setVars([
            'headerSeo' => $this->headerSeo,
        ]);

    }


    /**
     * @title -配置管理首页
     * @desc  -配置管理首页
     */
    public function indexAction() {
        //站点
        $sites = [
            $this->siteId => '本站',
            '0' => '系统平台',
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


    /**
     * @title -配置列表JSON
     * @desc  -配置列表JSON
     */
    public function listAction() {
        list($pageNumber, $pageSize) = $this->getPageNumberNSize();
        $sortName = trim($this->getGet('sortName'));
        $sortOrder = trim($this->getGet('sortOrder'));
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
                    if(in_array($field, ['key', 'title'])) {
                        array_push($where, ['like',$field,"%{$value}%"]);
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

            //站点
            $sites = [
                $this->siteId => '本站',
                '0' => '系统平台',
            ];

            foreach ($list as &$item) {
                $usr = ArrayHelper::arraySearchItem($admList, ['uid'=>$item['update_by']]);
                $item['username'] = $usr['username']??'';
                $item['value'] = StringHelper::cutStr($item['value'], 10);
                $item['extra'] = StringHelper::cutStr($item['extra'], 10);
                $item['site_id'] = $sites[$item['site_id']] ?? '未知';
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

    /**
     * @title -配置编辑页
     * @desc  -配置编辑页
     */
    public function editAction() {
        $loginUid = $this->getLoginUid();
        $id = intval($this->getGet('ids'));
        $info = [];

        if($id) {
            $lock = getlockBackendOperate('editConfig', $id, $loginUid);
            if(empty($lock) || $lock<=0) {
                return $this->alert("该信息已被其他后台用户[".abs($lock)."]锁定，您不能操作！");
            }

            $info = Config::findFirst($id);
            if(empty($info) || $info->is_del) {
                return $this->alert('该信息不存在或已删除');
            }
        }

        //站点
        $sites = [
            $this->siteId => '本站',
            '0' => '系统平台',
        ];

        //数据类型
        $dataTypes = Config::getDataTypeArr();
        //控件类型
        $inputTypes = Config::getInputTypeArr();

        $agUuid = $this->di->getShared('userAgent')->getAgentUuidSimp();
        $accToken = UserService::makeAccessToken($loginUid, $agUuid, 1800);
        $tokenName = getConf('login', 'tokenName');

        //视图变量
        $rowJson = $info ? $info->toArray() : '';
        if($rowJson) $rowJson['extra'] = addslashes($rowJson['extra']);

        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'saveUrl' => makeUrl('manage/config/save'),
            'uploadUrl' => makeUrl('api/upload/single', [$tokenName=>$accToken]),
            'globalConfPrefix' => Config::$globalConfPrefix,
            'id' => $id,
            'row' => $info ? Config::rowToObject($info) : $info,
            'rowJson' => json_encode($rowJson),
            'sites' => $sites,
            'dataTypes' => $dataTypes,
            'inputTypes' => $inputTypes,
        ]);

        return null;
    }


    /**
     * @title -配置保存
     * @desc  -配置保存
     */
    public function saveAction() {
        $loginUid = $this->getLoginUid();
        $row = $this->getPost('row');

        $id = intval($row['id'] ?? 0);
        unset($row['id']);

        if(empty($row['title'])) {
            return $this->fail('配置名称不能为空');
        }elseif (empty($row['key'])) {
            return $this->fail('配置键不能为空');
        }elseif (!preg_match("/^[a-z][a-z\d\_]{2,29}$/", $row['key'])) {
            return $this->fail('配置键只能是小写英文、数字和下划线组成,英文开头,3~30个字符');
        }elseif (empty($row['site_id']) && stripos($row['key'], Config::$globalConfPrefix)!==0) {
            return $this->fail('系统全局配置只能以'.Config::$globalConfPrefix.'开头');
        }elseif ($row['site_id']>0 && stripos($row['key'], Config::$globalConfPrefix)===0) {
            return $this->fail('站点配置不能以'.Config::$globalConfPrefix.'开头');
        }

        $row['key'] = strtolower($row['key']);

        //配置值处理
        switch ($row['data_type']) {
            case '' :default :
                return $this->fail('数据类型错误');
                break;
            case 'bool' :
                if($row['input_type'] !=='radio') {
                    return $this->fail('控件类型错误');
                }elseif (!isset($row['value'])) {
                    return $this->fail('请选择配置值');
                }

                $row['value'] = intval($row['value']) ? 1 : 0;
                break;
            case 'integer' :
                if($row['input_type'] !=='number') {
                    return $this->fail('控件类型错误');
                }elseif (!ValidateHelper::isInteger($row['value'])) {
                    return $this->fail('配置值不是整型');
                }

                $row['value'] = intval($row['value']);
                break;
            case 'float' :
                if($row['input_type'] !=='number') {
                    return $this->fail('控件类型错误');
                }elseif (!is_numeric($row['value'])) {
                    return $this->fail('配置值不是有效数值');
                }

                $row['value'] = floatval($row['value']);
                break;
            case 'datetime' :
                if($row['input_type'] !=='datetime') {
                    return $this->fail('控件类型错误');
                }elseif (!ValidateHelper::isDate2time($row['value'])) {
                    return $this->fail('配置值不是日期时间');
                }

                break;
            case 'string' :
                if(!in_array($row['input_type'], ['input','file'])) {
                    return $this->fail('控件类型错误');
                }

                break;
            case 'array' :
                if(!in_array($row['input_type'], ['number','datetime','input','file'])) {
                    return $this->fail('控件类型错误');
                }

                $arr = [];
                if(!empty($row['value'])) {
                    $idxArr = array_unique(array_filter($row['value']['field'], function ($v) {
                        return empty(trim($v)) ? false : true;
                    }));
                    $hasStrKey = empty($idxArr) ? false : true;

                    foreach ($row['value']['value'] as $k=>$item) {
                        $key = $row['value']['field'][$k] ?? '';
                        if($key=='' && $item=='') continue;
                        if($hasStrKey && $key=='') $key = 'idx_' . $k;

                        if(empty($key)) {
                            array_push($arr, $item);
                        }else{
                            $arr[$key] = $item;
                        }
                    }
                }

                $row['extra'] = json_encode($arr);
                break;
            case 'text' :
                if($row['input_type'] !=='textarea') {
                    return $this->fail('控件类型错误');
                }

                $row['extra'] = $row['value'];
                break;
            case 'json' :
                if($row['input_type'] !=='textarea') {
                    return $this->fail('控件类型错误');
                }elseif (!ValidateHelper::isJson($row['value'])) {
                    return $this->fail('配置值不是JSON');
                }

                $row['extra'] = json_encode(json_decode($row['value']));
                break;
        }

        if(in_array($row['data_type'], ['array', 'text', 'json'])) {
            $row['value'] = '';
        }

        //检查配置键是否已存在
        $where = [
            'and',
            ['site_id' => $row['site_id'] ],
            ['key' => $row['key'] ],
            ['neq', 'id', $id]
        ];
        $chkKey = Config::getRow($where);
        if($chkKey) {
            if($chkKey->is_del==0) {
                return $this->fail("该配置键已存在ID:{$chkKey->id}");
            }else{
                $id = $chkKey->id;
            }
        }

        $now = time();
        $row['is_del'] = 0;
        $row['update_time'] = $now;
        $row['update_by'] = $loginUid;

        if($id) {
            $info = Config::findFirst($id);
            if(empty($info)) {
                return $this->fail('该信息不存在或已删除');
            }elseif ($info->key != $row['key']) {
                return $this->fail('禁删除的配置键不能修改');
            }

            $res = Config::upData($row, ['id'=>$id]);
        }else{
            $row['create_time'] = $now;
            $row['create_by'] = $loginUid;
            $res = Config::addData($row);
        }

        return $res ? $this->success() : $this->fail('操作失败');
    }


    /**
     * @title -删除配置项
     * @desc  -删除配置项
     */
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

        //检查是否有禁止删除的配置项
        $chkWhere = [
            'disable_del' => 1,
            'id' => $ids,
        ];
        $chkCount = Config::getCount($chkWhere);
        if($chkCount>0) {
            return $this->fail('所选项包含禁止删除');
        }

        $data = [
            'is_del' => 1,
            'update_time' => $now,
            'update_by' => $this->getLoginUid(),
        ];
        $res = Config::upData($data, ['id' => $ids]);
        if($res) {
            //TODO 缓存操作
        }

        return $res ? $this->success($res) : $this->fail('操作失败,请稍后再试');
    }


    /**
     * @title -批量操作配置项
     * @desc  -批量操作配置项
     */
    public function multiAction() {
        $ids = (array)$this->getPost('ids');
        $ids = array_filter($ids, function ($v) {
            if(!is_numeric($v) || $v<=0) return false;
            return true;
        });

        $params = trim($this->getPost('params'));
        $params = explode('=', $params);

        if(empty($ids) || count($params)!=2) {
            return $this->fail('参数错误');
        }

        $field = strtolower(trim($params[0]));
        $value = intval($params[1]);
        $now = time();

        switch ($field) {
            default : case '' :
                return $this->fail('参数错误');
                break;
            case 'is_del' : //恢复已删
                $data = [
                    'is_del' => 0,
                    'update_time' => $now,
                    'update_by' => $this->getLoginUid(),
                ];
                $res = Config::upData($data, ['id' => $ids]);
                if($res) {
                    //TODO 缓存操作
                }

                break;
        }

        return $res ? $this->success($res) : $this->fail('操作失败,请稍后再试');
    }











}