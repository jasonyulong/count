ERP v5.0
===============

# PHP版本
* version 7.0x, 7.1x
* php7 新特性 (http://www.php.net/manual/zh/migration71.new-features.php)

## 命名规范

命名规范遵循`PSR-2`规范以及`PSR-4`自动加载规范。
* [**PSR-2**](http://www.kancloud.cn/thinkphp/php-fig-psr/3141)
* [**PSR-4**](http://www.kancloud.cn/thinkphp/php-fig-psr/3144)

## 代码规范
### 1. PHP配置文件规范
    <?php
    // +----------------------------------------------------------------------
    // | 应用设置中心
    // +----------------------------------------------------------------------
    // | Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
    // +----------------------------------------------------------------------
    // | Author: kevin
    // +----------------------------------------------------------------------

    return [
        // +----------------------------------------------------------------------
        // | 应用设置
        // +----------------------------------------------------------------------
        // 应用命名空间
        'app_namespace'          => 'app',
        // 应用调试模式
        'app_debug'              => Env::get('app.debug', true),
    ]`

### 2. PHP公共方法规范
```php
<?php
// +----------------------------------------------------------------------
// | 公共助手函数
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: kevin
// +----------------------------------------------------------------------

use fast\Form;

if (!function_exists('__')) {
    /**
     * 获取语言变量值
     * @param string $name 语言变量名
     * @param array $vars 动态变量值
     * @param string $lang 语言
     * @return mixed
     */
    function __($name, $vars = [], $lang = '') : string
    {
        if (is_numeric($name) || !$name)
            return $name;
        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }
        return \think\Lang::get($name, $vars, $lang);
    }
}
```
### 3. PHP类文件规范
```php
<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller;

use app\common\controller\Common;
use think\Config;
use think\Lang;

/**
 * Ajax异步请求接口
 * @internal
 */
class Ajax extends Common
{
    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包
     * @return \think\response\Jsonp
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        $controllername = input("controllername");
        //默认只加载了控制器对应的语言名，你还根据控制器名来加载额外的语言包
        $this->loadlang($controllername);
        return jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }
}
```
### 4. PHP控制器规范
* 控制器类注释必须加上 @name ,后台权限控制可自动读取
* 控制器方法注释必须加上, @name,@access auth ,后台权限控制可自动读取

```php
<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    kevin
 */

namespace app\count\controller\order;

use app\common\controller\Common;
use app\count\model\Order;
use think\cache\driver\Redis;
use think\Config;

/**
 * @name 订单状态报表
 * @package app\count\controller\order
 */
class Index extends Common
{
    /**
     * @var \app\count\model\Order
     */
    protected $model = null;

    /**
     * @name 查看
     * @access auth
     * @return string
     * @throws \think\Exception
     */
    public function index()
    {

    }
}
```

### 4. PHP代码编写规范
* 所有代码缩进都使用`tab*4`
* 项目遵循模块化开发， 不同模块之间禁止随意引用
* 每个类和每个方法都必须要有备注
* 控制器里方法全部统一小写, 并且控制器的注释特别重要.**(跟权限有关)**


## 目录结构
    ├─application           应用目录
    │  ├─common             公共模块目录（可以更改）
    │  ├─module_name        模块目录
    │  │  ├─config.php      模块配置文件
    │  │  ├─common.php      模块函数文件
    │  │  ├─controller      控制器目录
    │  │  ├─model           模型目录
    │  │  ├─view            视图目录
    │  │  └─ ...            更多类库目录
    │  │
    │  ├─config             数据库配置文件
    │  ├─command.php        命令行工具配置文件
    │  ├─common.php         公共函数文件
    │  ├─config.php         公共配置文件
    │  ├─route.php          路由配置文件
    │  ├─tags.php           应用行为扩展定义文件
    │  └─database.php       数据库配置文件
    │
    ├─public                WEB目录（对外访问目录）
    │  ├─index.php          入口文件
    │  ├─router.php         快速测试文件
    │  └─.htaccess          用于apache的重写
    │
    ├─thinkphp              框架系统目录
    │  ├─lang               语言文件目录
    │  ├─library            框架类库目录
    │  │  ├─think           Think类库包目录
    │  │  └─traits          系统Trait目录
    │  │
    │  ├─tpl                系统模板目录
    │  ├─base.php           基础定义文件
    │  ├─console.php        控制台入口文件
    │  ├─convention.php     框架惯例配置文件
    │  ├─helper.php         助手函数文件
    │  ├─phpunit.xml        phpunit配置文件
    │  └─start.php          框架入口文件
    │
    ├─extend                扩展类库目录
    ├─runtime               应用的运行时目录（可写，可定制）
    ├─vendor                第三方类库目录（Composer依赖库）
    ├─build.php             自动生成定义文件（参考）
    ├─composer.json         composer 定义文件
    ├─LICENSE.txt           授权说明文件
    ├─README.md             README 文件
    ├─think                 命令行入口文件
