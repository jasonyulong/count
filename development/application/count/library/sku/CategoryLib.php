<?php
/**
 * @copyright Copyright (c) 2018 http://www.jeoshi.com All rights reserved.
 * @version   Beta 5.0
 * @author    jason
 */

namespace app\count\library\sku;

use app\count\model\SkuPackage;
use app\count\model\SkuPackageCate;

/**
 * 产品包分类管理
 * Class CategoryLib
 * @package app\count\library\sku
 */
class CategoryLib extends SkuPackageCate
{
    /**
     * @name 新增产品包分类
     * @author yang
     * @date 2018/10/04
     * @param array $data
     * @return mixed
     */
    public function addCategory($data)
    {
        $add = [
            'title' => trim($data['title']),
            'pid'   => trim($data['pid']),
            'rank'  => 1,  //默认为1
        ];
        if ($add['pid'] > 0) {
            //检测是否存在产品包
            if($this->checkPackage($add['pid'])) return json(['status' => 2, 'msg' => '父级分类已绑定产品包、您不能再选择此父级分类']);

            $rank = $this->where('id', $add['pid'])->value('rank');
            if ($rank == 1) $add['rank'] = 2;
            if ($rank == 2) $add['rank'] = 3;
        }
        $this->insert($add);
        return json(['status' => 1, 'msg' => '保存成功']);
    }

    /**
     * @name 编辑产品包分类
     * @author yang
     * @date 2018/10/04
     * @param array $data
     * @return mixed
     */
    public function editCategory($data)
    {
        $id = $data['id'];
        if (!$id) return json(['status' => 2, 'msg' => '保存失败(未获取参数)']);
        //查看其是否存在子分类
        $tid  = $this->where('pid', $id)->value('id');
        $pid1 = $data['pid1'];
        if ($pid1 != $data['pid'] && $tid) return json(['status' => 2, 'msg' => '保存失败(当前分类存在子分类)']);

        $save = [
            'title' => trim($data['title']),
            'pid'   => trim($data['pid']),
            'rank'  => 1,  //默认为1
        ];
        if ($save['pid'] > 0) {
            //检测是否存在产品包
            if($this->checkPackage($save['pid'])) return json(['status' => 2, 'msg' => '父级分类已绑定产品包、您不能再选择此父级分类']);

            $rank = $this->where('id', $save['pid'])->value('rank');
            if ($rank == 1) $save['rank'] = 2;
            if ($rank == 2) $save['rank'] = 3;
        }
        $this->where('id', $id)->update($save);
        return json(['status' => 1, 'msg' => '保存成功']);
    }

    /**
     * @name 获取分类数据 1,2两层级
     * @author yang
     * @date 2018/10/04
     * @return mixed
     */
    public function getCategory12()
    {
        $map   = [
            'rank' => ['in', [1, 2]]
        ];
        $obj   = $this->where($map)->field('id,title,rank,pid')->select();
        $arr   = replace_query($obj);
        $array = [];
        foreach ($arr as $val) {
            $arr1 = [];
            //查询其否存在子分类
            $tid            = $this->where('pid', $val['id'])->value('id');
            $arr1['is_pid'] = $tid ? 1 : 0;
            if ($val['rank'] == 1) $array[$val['id'] * 100] = array_merge($arr1, ['title' => $val['title'], 'id' => $val['id'], 'rank' => $val['rank']]);
            if ($val['rank'] == 2) $array[$val['pid'] * 100 + $val['id']] = array_merge($arr1, ['title' => "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;┝ " . $val['title'], 'id' => $val['id'], 'rank' => $val['rank']]);
        }
        return $array;
    }

    /**
     * @name 获取分类数据 3层级
     * @author yang
     * @date 2018/10/04
     * @return mixed
     */
    public function getCategory3()
    {
        $map   = ['rank' => 2];
        $idArr = $this->where($map)->column('id');
        $array = [];
        foreach ($idArr as $val) {
            $map = ['rank' => 3, 'pid' => $val];
            $obj = $this->where($map)->select();
            $arr = replace_query($obj);
            if (count($arr) > 0) {
                $arr1 = [];
                foreach ($arr as $value) {
                    $arr1[$value['id']] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;┕" . $value['title'];
                }
                $array[$val] = $arr1;
            }
        }
        return $array;
    }

    /**
     * @name 获取分类数据
     * @author yang
     * @date 2018/10/04
     * @return mixed
     */
    public function getCategory()
    {
        $obj   = $this->field('id,title,rank,pid')->select();
        $arr   = replace_query($obj);
        $array = [];
        foreach ($arr as $val) {
            if ($val['rank'] == 1) $array[$val['id']][] = $val;
            if ($val['rank'] == 2) $array[$val['pid']][] = $val;
            if ($val['rank'] == 3) $array['rank3'][] = $val;
        }
        return $array;
    }

    /**
     * @name 删除分类数据
     * @author yang
     * @param int $id
     * @date 2018/10/04
     * @return mixed
     */
    public function deleteCategory($id)
    {
        if (!$id) return json(['status' => 2, 'msg' => '删除失败(未获取参数)']);
        //查看其是否存在子分类(存在不允许删除)
        $tid = $this->where('pid', $id)->value('id');
        if ($tid) return json(['status' => 2, 'msg' => '删除失败(存在子分类)']);
        $result = $this->where('id', $id)->delete();
        if (!$result) return json(['status' => 2, 'msg' => '删除失败']);
        return json(['status' => 1, 'msg' => '删除成功']);
    }

    /**
     * @name 删除分类数据(3层)
     * @author yang
     * @date 2018/10/04
     * @return mixed
     */
    public function getCategory123()
    {
        $obj     = $this->field('id,title,rank,pid')->order('rank desc')->select();
        $cateArr = replace_query($obj);

        $map             = ['cate_id' => ['gt', 0]];
        $skuPackageModel = new SkuPackage();
        $obj             = $skuPackageModel->where($map)->field('cate_id,group_sn,title')->select();
        $backArr         = replace_query($obj);

        $arr    = [];
        $arr1   = [];
        $arr2   = [];
        $delArr = [];
        foreach ($cateArr as $val) {
            foreach ($backArr as $v) {
                if ($v['cate_id'] == $val['id']) {
                    $arr1[$val['id']]['group'][] = $v;
                }
                $arr1[$val['id']]['id'] = $val['id'];
                $arr1[$val['id']]['title'] = $val['title'];
                $arr1[$val['id']]['rank'] = $val['rank'];
                $arr1[$val['id']]['pid'] = $val['pid'];
            }
        }
        unset($cateArr);

        foreach ($arr1 as $key => $val) {
            //删除已存在的分类
            if (isset($delArr[$key])) {
                unset($arr1[$key]);
                continue;
            }
            if (isset($val['rank']) && $val['rank'] == 1) {
                $arr[$val['id']] = $val;
            }
            if (isset($val['rank']) && $val['rank'] == 2) $arr2[$val['id']]['rank2'] = $val;
            if (isset($val['rank']) && $val['rank'] == 3) $arr2[$val['pid']]['rank3'][] = $val;
        }
        unset($arr1);
        
        foreach ($arr as $key => $val) {
            foreach ($arr2 as $v) {
                if ($v['rank2']['pid'] == $val['id']) $arr[$key]['rank1'][] = $v;
            }
        }
        unset($arr2);
        return $arr;
    }

    /**
     * @name 接触分类是否绑定产品包
     * @author yang
     * @date 2018/10/10
     * @return mixed
     */
    public function checkPackage($cate_id){
        if(!$cate_id) return false;
        $SkuPackageModel = new SkuPackage();
        $result = $SkuPackageModel->where('cate_id',$cate_id)->value('group_sn');
        return $result;
    }
}