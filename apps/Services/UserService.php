<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/7/13
 * Time: 23:32
 * Desc: -用户服务类
 */


namespace Apps\Services;

use Apps\Models\AdmUser;
use Apps\Models\Attach;
use Apps\Models\UserBase;
use Faker\Factory as FakerFactory;
use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use Lkk\Helpers\ArrayHelper;
use Lkk\Helpers\DirectoryHelper;
use Lkk\Helpers\EncryptHelper;
use Lkk\Helpers\ValidateHelper;
use Lkk\Phalwoo\Phalcon\Session\Adapter\Redis as RedisSession;

class UserService extends ServiceBase {

    //用户名最小长度
    const USER_NAME_MINLEN = 5;
    //用户名最大长度
    const USER_NAME_MAXLEN = 30;
    //邮箱最小长度
    const USER_MAIL_MINLEN = 5;
    //邮箱最大长度
    const USER_MAIL_MAXLEN = 30;
    //用户密码最小长度
    const USER_PWD_MINLEN = 5;
    //用户密码最大长度
    const USER_PWD_MAXLEN = 32;
    //access_token有效期,30天
    const ACCESS_TOKEN_TTL = 2592000;

    //超级管理员UID
    const ROOT_UID = 1;
    //超级管理员用户名
    const ROOT_NAME = 'myroot';

    //保留用户名,禁止注册
    public static $holdNames = ['root','admin','test','manage','system','super','vip','guanli','guest','api'];
    //保留昵称,禁止使用
    public static $holdNicks = ['管理','测试','系统','游客','后台','接口'];

    //登录配置-login
    protected $conf;

    //是否记住登录(长时间cookie)
    protected $remember;


    /**
     * 构造函数
     */
    public function __construct($vars=[]) {
        parent::__construct($vars);

        $this->conf = getConf('login');

    }


    /**
     * 验证用户名的字符
     * @param string $str
     * @return int
     */
    public static function validateUsernameChar(string $str) {
        return preg_match('/^[_.0-9a-z]+$/i',$str);
    }


    /**
     * 验证用户密码的字符
     * @param string $str
     * @return int
     */
    public static function validateUserpwdChar(string $str) {
        return preg_match('/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]+$/i',$str);
    }


    /**
     * 是否包含保留名
     * @param string $str
     * @return bool
     */
    public static function hasHoldName(string $str) {
        if(in_array($str, self::$holdNames)) {
            return true;
        }else{
            foreach (self::$holdNames as $holdName) {
                if(mb_stripos($str, $holdName)===0) { //禁止以保留名为前缀
                    return true;
                    break;
                }
            }
        }
        
        return false;
    }


    /**
     * 是否包含保留昵称
     * @param string $str
     * @return bool
     */
    public static function hasHoldNick(string $str) {
        if(in_array($str, self::$holdNicks)) {
            return true;
        }else{
            foreach (self::$holdNicks as $holdNick) {
                if(mb_stripos($str, $holdNick)===0) { //禁止以保留昵称为前缀
                    return true;
                    break;
                }
            }
        }

        return false;
    }


    /**
     * 检查用户名是否已存在
     * @param string $str 用户名
     * @param int $uid 对比UID
     * @return bool
     */
    public static function isUsernameExist(string $str, $uid=0) {
        $user = UserBase::getRow(['username'=>$str]);
        $res = $user ? ($uid ? ($uid==$user->uid ? false : true) : true ) : false;

        return $res;
    }


    /**
     * 检查管理员是否已存在着
     * @param string $str 用户名
     * @param int $uid 对比UID
     * @return bool
     */
    public static function isAdminExist(string $str, $uid=0) {
        $adm = UserBase::getInfoInAdmByUsername($str, true);
        $res = $adm ? ($uid ? ($uid==$adm->adm_uid ? false : true) : true ) : false;

        return $res;
    }


    /**
     * 检查邮箱是否已存在
     * @param string $str
     * @param int $uid
     * @return bool
     */
    public static function isEmailExist(string $str, $uid=0) {
        $user = UserBase::getRow(['email'=>$str]);
        $res = $user ? ($uid ? ($uid==$user->uid ? false : true) : true ) : false;

        return $res;
    }


    /**
     * 检查用户是否超级管理员
     * @param array|object $userInfo 用户信息
     * @return bool
     */
    public static function isRoot($userInfo=null) {
        $res = false;
        if(empty($userInfo)) return $res;
        if(is_array($userInfo)) $userInfo = ArrayHelper::arrayToObject($userInfo);

        if(isset($userInfo->uid) && $userInfo->uid==self::ROOT_UID) {
            $res = true;
        }elseif(isset($userInfo->username) && $userInfo->username==self::ROOT_NAME) {
            $res = true;
        }

        unset($userInfo);
        return $res;
    }


    /**
     * 检查用户是否后台管理员
     * @param array|object $userInfo 用户信息
     * @return bool
     */
    public static function isAdmin($userInfo=null) {
        $res = false;
        if(empty($userInfo)) return $res;
        if(is_array($userInfo)) $userInfo = ArrayHelper::arrayToObject($userInfo);

        if((isset($userInfo->adm_uid) && $userInfo->adm_uid>0) || (isset($userInfo->type) && $userInfo->type==UserBase::USER_TYPE_ADMNER)) {
            if(isset($userInfo->adm_status) && $userInfo->adm_status==1) {
                $res = true;
            }
        }elseif (isset($userInfo->user_type) && $userInfo->user_type==UserBase::USER_TYPE_ADMNER) {
            if(isset($userInfo->status) && $userInfo->status==1) {
                $res = true;
            }
        }

        unset($userInfo);
        return $res;
    }


    /**
     * 验证用户名是否合法
     * @param string $str
     * @return bool
     */
    public function validateUsername(string $str) {
        $res = false;
        $len = strlen($str);
        if(empty($str)) {
            $this->setError('用户名不能为空');
            return $res;
        }elseif (!self::validateUsernameChar($str)) {
            $this->setError('用户名只能使用英文、数字、点和下划线');
            return $res;
        }elseif ($len < self::USER_NAME_MINLEN) {
            $this->setError('用户名至少'.self::USER_NAME_MINLEN.'个字符');
            return $res;
        }elseif ($len > self::USER_NAME_MAXLEN) {
            $this->setError('用户名至多'.self::USER_NAME_MAXLEN.'个字符');
            return $res;
        }

        return true;
    }


    /**
     * 验证用户密码是否合法
     * @param string $str
     * @return bool
     */
    public function validateUserpwd(string $str) {
        $res = false;
        $len = strlen($str);
        if(empty($str)) {
            $this->setError('密码不能为空');
            return $res;
        }elseif (is_numeric($str)){
            $this->setError('密码不能全是数字');
            return $res;
        }elseif (!self::validateUserpwdChar($str)) {
            $this->setError('密码只能使用英文、数字和特殊字符');
            return $res;
        }elseif ($len < self::USER_PWD_MINLEN) {
            $this->setError('密码至少'.self::USER_PWD_MINLEN.'个字符');
            return $res;
        }elseif ($len > self::USER_PWD_MAXLEN) {
            $this->setError('密码至多'.self::USER_PWD_MAXLEN.'个字符');
            return $res;
        }

        return true;
    }


    /**
     * 验证邮箱是否合法
     * @param string $str
     * @return bool
     */
    public function validateEmail(string $str) {
        $res = false;
        $len = strlen($str);
        if(empty($str)) {
            $this->setError('邮箱不能为空');
            return $res;
        }elseif (!ValidateHelper::isEmail($str)) {
            $this->setError('无效的邮箱');
            return $res;
        }elseif ($len < self::USER_MAIL_MINLEN) {
            $this->setError('邮箱至少'.self::USER_MAIL_MINLEN.'个字符');
            return $res;
        }elseif ($len > self::USER_MAIL_MAXLEN) {
            $this->setError('邮箱至多'.self::USER_MAIL_MAXLEN.'个字符');
            return $res;
        }

        return true;
    }


    /**
     * 验证手机号是否合法
     * @param string $str
     * @return bool
     */
    public function validateMobile(string $str) {
        $res = false;
        if(empty($str)) {
            $this->setError('手机号不能为空');
            return $res;
        }elseif(!ValidateHelper::isMobile($str)) {
            $this->setError('无效的手机号');
            return $res;
        }

        return true;
    }


    /**
     * 验证身份证号是否合法
     * @param string $str
     * @return bool
     */
    public function validateIdentityNo(string $str) {
        $res = false;
        if(empty($str)) {
            $this->setError('身份证号不能为空');
            return $res;
        }elseif(!ValidateHelper::isCreditNo($str)) {
            $this->setError('无效的身份证号');
            return $res;
        }

        return true;
    }


    /**
     * 检查基本用户是否存在
     * @param string $str
     * @param int $uid
     * @return bool
     */
    public function checkUsernameExist(string $str, $uid=0) {
        $chk = self::isUsernameExist($str, $uid);
        if($chk) $this->setError('该用户名已存在');

        return !$chk;
    }


    /**
     * 检查邮箱是否存在
     * @param string $str
     * @param int $uid
     * @return bool
     */
    public function checkEmailExist(string $str, $uid=0) {
        $chk = self::isEmailExist($str, $uid);
        if($chk) $this->setError('该邮箱已存在');

        return !$chk;
    }


    /**
     * 检查是否保留的名字
     * @param string $str
     * @return bool
     */
    public function checkIsHoldName(string $str) {
        $chk = self::hasHoldName($str);
        if($chk) $this->setError('该用户名已存在');

        return !$chk;
    }


    /**
     * 检查是否保留的昵称
     * @param string $str
     * @return bool
     */
    public function checkIsHoldNick(string $str) {
        $chk = self::hasHoldNick($str);
        if($chk) $this->setError('该用户名已存在');

        return !$chk;
    }


    /**
     * 生成密码值
     * @param string $str
     *
     * @return bool|string
     */
    public static function makePasswdHash($str='') {
        $str = trim($str);
        if(empty($str)) return $str;
        $res = password_hash($str, PASSWORD_BCRYPT);
        return $res;
    }



    /**
     * 检查管理员是否存在
     * @param string $str 管理员用户名
     * @param int $uid
     * @return bool
     */
    public function checkAdminExist(string $str, $uid=0) {
        $chk = self::isAdminExist($str, $uid);
        if($chk) $this->setError('该管理员已存在');

        return !$chk;
    }


    /**
     * 设置是否记住登录
     * @param bool $remember
     */
    public function setRemember($remember=false) {
        $this->remember = boolval($remember);
    }


    /**
     * 是否记住登录
     * @return mixed
     */
    public function getRemember() {
        return $this->remember;
    }



    public function memberLogin($username='', $password='') {
        if(empty($username)) {
            $this->setError('登录名不能为空');
            return false;
        }elseif(empty($password)) {
            $this->setError('密码不能为空');
            return false;
        }





    }


    public function memberLogout() {

    }


    public function isMemberLogin() {

    }


    /**
     * 管理员登录
     * @param string $username 用户名或邮箱
     * @param string $password 密码
     *
     * @return array|bool|object|\Phalcon\Mvc\ModelInterface
     */
    public function managerLogin($username='', $password='') {
        if(empty($username)) {
            $this->setError('登录名不能为空');
            return false;
        }elseif(empty($password)) {
            $this->setError('密码不能为空');
            return false;
        }

        $admn = AdmUser::getInfoByKeyword($username);
        if(empty($admn)) {
            $this->setError('账号或密码错误');
            return false;
        }

        $admn = AdmUser::rowToObject($admn);
        if($admn->user_type != UserBase::USER_TYPE_ADMNER) {
            $this->setError('用户类型错误');
            return false;
        }

        //是否锁定
        if($admn->status ==0) {
            $this->setError('该用户已被锁定');
            return false;
        }elseif ($admn->status ==-1) {
            $this->setError('该用户已被禁止登录');
            return false;
        }

        //检查密码
        if(!password_verify($password, $admn->password)) {
            //登录失败事件
            $this->fireEvent('afterManagerLoginFail', $admn);
            $this->setError('账号或密码错误');
            return false;
        }

        //登录成功事件
        $this->fireEvent('afterManagerLoginSuccess', $admn);

        return $admn;
    }


    /**
     * 管理员退出
     * @param int $uid
     *
     * @return bool
     */
    public function managerLogout($uid=0) {
        if(empty($uid)) return false;
        $session = $this->getDI()->getShared('session');
        //删除session变量
        $session->remove($this->conf->managerLoginSession);
        //销毁全部session会话
        $session->destroy();

        //删除cookie
        $cookies  = $this->getDI()->getShared('cookies');
        $cookies->del($this->conf->managerAuthCookie);

        //退出成功事件
        $this->fireEvent('afterManagerLogout', $uid);

        return true;
    }


    public function isManagerLogin() {

    }


    public function apiLogin($token='') {

    }


    public function thirdLogin() {

    }


    public function sinaLogin() {

    }


    public function qqLogin() {

    }


    public function weixinLogin() {

    }


    /**
     * 生成管理员session
     * @param null $admn
     *
     * @return bool
     */
    public function makeManagerSession($admn=null) {
        if(empty($admn)) return false;

        $data = [
            'uid' => $admn->uid,
            'username' => $admn->username,
            'last_login_ip' => $admn->last_login_ip,
            'last_login_time' => $admn->last_login_time,
            'avatar' => $admn->avatar ?? '/assets/img/avatar.png',
        ];

        $session = $this->getDI()->getShared('session');
        $session->set($this->conf->managerLoginSession, $data);
        $this->makeManagerCookie($admn->uid);

        return true;
    }


    /**
     * 生成管理员cookie
     * @param int $uid
     *
     * @return bool
     */
    public function makeManagerCookie($uid=0) {
        if(empty($uid)) return false;

        $life = $this->remember ? $this->conf->managerRememberLife : $this->conf->managerCookieLifetime;
        $clientUuid = $this->getDI()->getShared('userAgent')->getAgentUuidNofp();
        $value = $uid .'|' . substr($clientUuid, -5);
        $cryptKey = getConf('crypt')->key;
        $cryptVal = EncryptHelper::ucAuthcode($value, 'ENCODE', $cryptKey, $life);
        $cryptVal = EncryptHelper::base64urlEncode($cryptVal);

        $cookies  = $this->getDI()->getShared('cookies');
        $cookies->set($this->conf->managerAuthCookie, $cryptVal, $life);

        return true;
    }


    /**
     * 获取管理员session
     * @return mixed
     */
    public function getManagerSession() {
        $session = $this->getDI()->getShared('session');
        $res = $session->get($this->conf->managerLoginSession);
        return $res;
    }


    /**
     * 销毁管理员session
     * @return mixed
     */
    public function destroyManagerSession() {
        $session = $this->getDI()->getShared('session');
        $session->set($this->conf->managerLoginSession, null);
        $res = $session->destroy(true);
        return $res;
    }


    /**
     * 检查管理员是否登录
     * @return int
     */
    public function checkManagerLogin() {
        $uid = 0;

        //先检查cookie
        if($this->getDI()->getShared('cookies')->has($this->conf->managerAuthCookie)) {
            $cookieVal = $this->getDI()->getShared('cookies')->getValue($this->conf->managerAuthCookie);
            $cookieVal = EncryptHelper::base64urlDecode($cookieVal);
            $decodeVal = EncryptHelper::ucAuthcode($cookieVal, 'DECODE', getConf('crypt')->key);
            if(!empty($decodeVal)) {
                $arr = explode('|', $decodeVal);
                $clientUuid = $this->getDI()->getShared('userAgent')->getAgentUuidNofp();
                if(isset($arr[1]) && $arr[1]== substr($clientUuid, -5)) {
                    $uid = intval($arr[0]);

                    //检查session
                    $sessionData = $this->getDI()->getShared('session')->get($this->conf->managerLoginSession);
                    if(empty($sessionData)) {
                        $admn = AdmUser::getInfoByUid($uid);
                        $this->makeManagerSession($admn);
                    }
                }
            }
        }

        return $uid;
    }


    /**
     * 根据UID生成用户access_token
     * @param int $uid UID
     * @param string $veriCode 插入校验码,如客户端指纹
     * @param int $ttl 有效期,秒
     * @return string
     */
    public static function makeAccessToken($uid=0, $veriCode='', $ttl=0) {
        $key = getConf('crypt', 'key');
        $str = "{$uid}|{$veriCode}";
        if(empty($ttl)) $ttl = self::ACCESS_TOKEN_TTL;
        $code = EncryptHelper::ucAuthcode($str, 'ENCODE', $key, $ttl);
        $code = EncryptHelper::base64urlEncode($code);

        return $code;
    }


    /**
     * 检查解析access_token
     * @param string $token
     * @param string $veriCode 插入校验码,如客户端指纹
     * @return bool|string
     */
    public static function parseAccessToken($token='', $veriCode='') {
        $res = false;
        if(empty($token)) return $res;

        $key = getConf('crypt', 'key');
        $code = strval(EncryptHelper::base64urlDecode($token));
        $code = EncryptHelper::ucAuthcode($code, 'DECODE', $key);
        getLogger('token')->info('parseAccessToken', ['$token'=>$token, '$veriCode'=>$veriCode, '$key'=>$key, '$code'=>$code]);
        if(!empty($code)) {
            $arr = explode('|', $code);
            if(!empty($veriCode) && (!isset($arr[1]) || $arr[1]!=$veriCode)) return false;
            if(is_numeric($arr[0])) $res = intval($arr[0]);
        }

        return $res;
    }


    /**
     * 生成用户默认头像路径
     * @param int $uid
     *
     * @return string
     */
    public static function makeAvatarPath($uid=0) {
        if(empty($uid) || !is_numeric($uid)) return '';

        $uid = abs(intval($uid));
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, -9, 3);
        $dir2 = substr($uid, -6, 2);

        $res = $dir1.'/'.$dir2.'/';
        return $res;
    }


    /**
     * 根据UID获取用户默认头像
     * @param int    $uid
     * @param string $ext
     *
     * @return string
     */
    public static function getAvatarByUid($uid=0, $ext='jpg') {
        if(empty($uid) || !is_numeric($uid)) return '';

        $path = self::makeAvatarPath($uid);
        $res = $path . "{$uid}.{$ext}";
        return strtolower($res);
    }


    /**
     * 生成用户头像
     * @param int $uid 用户ID
     * @param array $param 参数
     * @param bool $rand
     * @param bool $new
     * @return bool|\Intervention\Image\Image|string
     */
    public static function createUserAvatar($uid=0, $param=[], $rand=true, $new=false) {
        if(empty($uid)) return false;

        $user = UserBase::findFirst($uid);
        $username = empty($user) ? $uid : $user->username;

        $ext = 'jpg';
        $avatarPath = self::getAvatarByUid($uid, $ext);
        $filePath = UPLODIR . 'avatar/' . ltrim($avatarPath, '/');
        if(file_exists($filePath) && !$new) return $filePath;

        $direct = dirname($filePath);
        if(!is_dir($direct)) DirectoryHelper::mkdirDeep($direct);

        $faker = FakerFactory::create();
        if(!isset($param['length']) || empty($param['length'])) $param['length'] = 2; //字符长度
        if(!isset($param['size']) || empty($param['size'])) $param['size'] = 200; //100x100
        if(!isset($param['font']) || empty($param['font'])) $param['font'] = '/fonts/OpenSans-Regular.ttf';
        if(!isset($param['fontsize']) || empty($param['fontsize'])) $param['fontsize'] = 0.5;
        if(!isset($param['background']) || empty($param['background'])) $param['background'] = $faker->hexColor;
        if(!isset($param['color']) || empty($param['color'])) $param['color'] = $faker->hexColor;

        $avatar = new InitialAvatar();
        $image = $avatar->name($username)
            ->length($param['length'])
            ->size($param['size'])
            ->font($param['font'])
            ->fontSize($param['fontsize'])
            ->background($param['background'])
            ->color($param['color'])
            ->generate();

        $ret = $image->save($filePath);
        return $ret ? $filePath : $ret;
    }






}