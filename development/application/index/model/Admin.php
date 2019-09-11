<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\index\model;

use app\common\model\AccountAdmin;
use app\common\model\AccountFundDetail;
use app\index\library\Auth;
use think\Session;

/**
 * 管理员模型
 * Class Admin
 * @package app\index\model
 */
class Admin extends \app\common\model\Admin
{

    /**
     * 重置用户密码
     * @param $uid
     * @param $NewPassword
     * @return $this
     */
    public function resetPassword($uid, $newPassword)
    {
        $passwd = $this->encryptPassword($newPassword);
        $ret    = $this->where(['id' => $uid])->update(['password' => $passwd]);
        return $ret;
    }

    /**
     * 密码加密
     * @param $password
     * @param string $salt
     * @param string $encrypt
     * @return mixed
     */
    public function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        // return $encrypt(APP_SECRETKEY . $password . $salt);
        return md5(md5($password));
    }
}
