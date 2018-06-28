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
use Lkk\Helpers\ValidateHelper;

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
        $loginUid = $this->getLoginUid();
        $id = intval($this->getGet('ids'));
        $info = [];

        if($id) {
            $lock = getlockBackendOperate('editConfig', $id, $loginUid);
            if(empty($lock) || $lock<=0) {
                return $this->fail("该信息已被其他后台用户[".abs($lock)."]锁定操作，您不能操作！");
            }

            $info = Config::findFirst($id);
            if(empty($info) || $info->is_del) {
                return $this->fail('信息不存在或已删除');
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
        $this->view->setVars([
            'siteUrl' => getSiteUrl(),
            'saveUrl' => makeUrl('manage/config/save'),
            'uploadUrl' => makeUrl('api/upload/single', [$tokenName=>$accToken]),
            'id' => $id,
            'row' => $info ? Config::rowToObject($info) : $info,
            'rowJson' => json_encode($info ? $info->toArray() : ''),
            'sites' => $sites,
            'dataTypes' => $dataTypes,
            'inputTypes' => $inputTypes,
        ]);

        return null;
    }


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
        }

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
                }elseif (!ValidateHelper::isFloat($row['value'])) {
                    return $this->fail('配置值不是浮点型');
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
                    foreach ($row['value']['value'] as $k=>$item) {
                        $key = $row['value']['value'][$k] ?? '';
                        if($key=='' && $item=='') continue;

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

                $row['extra'] = $row['value'];
                break;
        }

        if(in_array($row['data_type'], ['array', 'text', 'json'])) {
            $row['value'] = '';
        }

        $now = time();
        $row['update_time'] = $now;
        $row['update_by'] = $loginUid;

        if($id) {
            $row['create_time'] = $now;
            $row['create_by'] = $loginUid;
            $res = Config::upData($row, ['id'=>$id]);
        }else{
            $res = Config::addData($row);
        }


        return $res ? $this->success() : $this->fail('操作失败');
    }


    /**
     * 删除配置项
     * @return array|string
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










}