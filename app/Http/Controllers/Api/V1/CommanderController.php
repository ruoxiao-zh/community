<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/2
 * Time: 11:59 AM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
// 数据库模型
use App\Models\CommanderWithdrawRecord;
use App\Models\Delivery;
use App\Models\GroupDetail;
use App\Models\GroupOrder;
use App\Models\GroupOrderDetail;
use App\Models\User;
use App\Models\Commander;
use App\Models\CommanderOrder;

// Excel 数据操作

require_once __DIR__ . '/../../../../SDK/Excel/PHPExcel.php';

class CommanderController extends BaseController
{
    /**
     * 检索用户
     *
     * @param Request $request
     *
     * @return string
     */
    public function searchUser(Request $request)
    {
        $nickname = $request->input('nickname');
        if ( !$nickname) {
            return jsonHelper(102, '必要的参数不能为空: nickname');
        }

        $result = User::where('nickname', 'like', $nickname . '%')->select('id', 'openid', 'nickname', 'avatar', 'create_at')->get();

        return jsonHelper(0, '获取成功', $result);
    }

    /**
     * 添加团长
     *
     * @param Request $request
     *
     * @return string
     */
    public function create(Request $request)
    {
        // 配送区域
        $delivery_id = (int)$request->input('delivery_id');
        if ( !$delivery_id) {
            return jsonHelper(102, '必要的参数不能为空: delivery_id');
        }

        // 联系方式
        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(104, '必要的参数不能为空: phone');
        } else {
            if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
                return jsonHelper(105, '手机号格式不正确: phone');
            }
        }

        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(106, '必要的参数不能为空: openid');
        }

        // 团长姓名
        $name = $request->input('name');
        if ( !$name) {
            return jsonHelper(103, '必要的参数不能为空: name');
        }

        $is_delete = Commander::where('openid', $openid)->where('is_delete', 0)->first();
        if ($is_delete) {
            $obj = $is_delete;
        } else {
            $obj = new Commander();
        }

        $obj->delivery_id = $delivery_id;
        $obj->name = $name;
        $obj->phone = $phone;
        $obj->openid = $openid;

        $is_insert = Commander::where('openid', $openid)->where('is_delete', 0)->first();
        if ($is_insert) {
            return jsonHelper(109, '数据已添加, 请勿重复添加');
        }

        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 团长修改
     *
     * @param Request $request
     *
     * @return string
     */
    public function update(Request $request)
    {
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }
        $obj = Commander::find($id);
        if ( !$obj) {
            return jsonHelper(102, '传入的参数异常');
        }

        // 配送区域
        $delivery_id = (int)$request->input('delivery_id');
        if ( !$delivery_id) {
            return jsonHelper(104, '必要的参数不能为空: delivery_id');
        }
        $obj->delivery_id = $delivery_id;

        // 团长姓名
        $name = $request->input('name');
        if ( !$name) {
            return jsonHelper(105, '必要的参数不能为空: name');
        }
        $obj->name = $name;

        // 联系方式
        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(106, '必要的参数不能为空: phone');
        } else {
            if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
                return jsonHelper(107, '手机号格式不正确: phone');
            }
        }
        $obj->phone = $phone;

        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(108, '必要的参数不能为空: openid');
        }
        $obj->openid = $openid;

        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 团长列表
     *
     * @return mixed
     */
    public function index()
    {
        $result = Commander::where('is_delete', 0)->select('id', 'name', 'phone', 'openid', 'delivery_id', 'total_money', 'withdraw_money', 'residue_money', 'create_at')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/commander');

        if ($result) {
            foreach ($result as $key => $value) {
                $delivery_area = Delivery::where('id', $value->delivery_id)->first();
                if ($delivery_area) {
                    $result[$key]['delivery_area'] = $delivery_area->deliver_name;
                }

                $userinfo = User::where('openid', $value->openid)->first();
                if ($userinfo) {
                    $result[$key]['user_nickname'] = $userinfo->nickname;
                }
            }
        }

        return $result->toJson();
    }

    /**
     * 单个团长信息详情
     *
     * @param Request $request
     *
     * @return string
     */
    public function show(Request $request)
    {
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $result = Commander::where('id', $id)->select('id', 'name', 'phone', 'openid', 'delivery_id', 'total_money', 'withdraw_money', 'residue_money', 'create_at')->first();
        if ( !$result) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $delivery_area = Delivery::where('id', $result->delivery_id)->first();
        if ($delivery_area) {
            $result['delivery_area'] = $delivery_area->deliver_name;
        }
        $userinfo = User::where('openid', $result->openid)->first();
        if ($userinfo) {
            $result['user_nickname'] = $userinfo->nickname;
        }

        return jsonHelper(0, '获取成功', $result);
    }

    /**
     * 删除团长
     *
     * @param Request $request
     *
     * @return string
     */
    public function delete(Request $request)
    {
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $result = Commander::find($id);
        if ( !$result) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $result->update([
            'is_delete' => 1
        ]);

        return jsonHelper(0, '删除成功');
    }

    /**
     * 搜索团长
     *
     * @param Request $request
     *
     * @return string
     */
    public function search(Request $request)
    {
        $result = Commander::where('is_delete', 0)->where(function ($query) use ($request) {
            ($request->input('name') !== '') && $query->where('name', 'like', $request->input('name') . '%');
            ($request->input('delivery_id') !== '') && $query->where('delivery_id', $request->input('delivery_id'));
        })->select('id', 'name', 'phone', 'openid', 'delivery_id', 'total_money', 'withdraw_money', 'residue_money', 'create_at')
            ->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/commander/search');

        if ($result) {
            foreach ($result as $key => $value) {
                $delivery_area = Delivery::where('id', $value->delivery_id)->first();
                if ($delivery_area) {
                    $result[$key]['delivery_area'] = $delivery_area->deliver_name;
                }

                $userinfo = User::where('openid', $value->openid)->first();
                if ($userinfo) {
                    $result[$key]['user_nickname'] = $userinfo->nickname;
                }
            }
        }

        return $result->toJson();
    }

    /**
     * 前台申请团长
     *
     * @param Request $request
     *
     * @return string
     */
    public function applyFor(Request $request, Commander $commander)
    {
        // 配送区域
        $delivery = $request->input('delivery');
        if ( !$delivery) {
            return jsonHelper(102, '必要的参数不能为空: delivery');
        } else {
            $delivery_id = Delivery::where('deliver_name', 'like', '%' . $delivery . '%')->first();
            if ($delivery_id) {
                $delivery_id = $delivery_id->id;
            } else {
                $delivery_id = DB::table('community_deliver')->insertGetId([
                    'deliver_name' => $delivery
                ]);
            }
        }
        $commander->delivery_id = $delivery_id;

        // 团长姓名
        $name = $request->input('name');
        if ( !$name) {
            return jsonHelper(103, '必要的参数不能为空: name');
        }
        $commander->name = $name;

        // 联系方式
        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(104, '必要的参数不能为空: phone');
        } else {
            if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
                return jsonHelper(105, '手机号格式不正确: phone');
            }
        }
        $commander->phone = $phone;

        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(106, '必要的参数不能为空: openid');
        }
        $commander->openid = $openid;

        $commander->is_apply = 1;

        $is_insert = Commander::where('openid', $openid)->first();
        if ($is_insert) {
            return jsonHelper(109, '数据已添加, 请勿重复添加');
        }

        try {
            $commander->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 后台申请团长列表
     *
     * @return mixed
     */
    public function applyForList()
    {
        $result = Commander::where('is_apply', 1)->select('id', 'name', 'phone', 'openid', 'delivery_id', 'create_at')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/commander/apply-for');

        if ($result) {
            foreach ($result as $key => $value) {
                $delivery_area = Delivery::where('id', $value->delivery_id)->first();
                if ($delivery_area) {
                    $result[$key]['delivery_area'] = $delivery_area->deliver_name;
                }

                $userinfo = User::where('openid', $value->openid)->first();
                if ($userinfo) {
                    $result[$key]['user_nickname'] = $userinfo->nickname;
                }
            }
        }

        return $result->toJson();
    }

    /**
     * 确认申请团长
     *
     * @param Request $request
     *
     * @return string
     */
    public function applyForConfirm(Request $request)
    {
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = Commander::find($id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $obj->is_apply = 0;

        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 后台数据统计
     *
     * @return string
     */
    public function countMoney()
    {
        $total_money = Commander::sum('total_money');
        $withdraw_money = Commander::sum('withdraw_money');
        $residue_money = Commander::sum('residue_money');

        $data = [
            'total_money'    => $total_money,
            'withdraw_money' => $withdraw_money,
            'residue_money'  => $residue_money
        ];

        return jsonHelper(0, '获取成功', $data);
    }

    /**
     * 提现
     *
     * @param Request $request
     *
     * @return string
     */
    public function withdraw(Request $request)
    {
        $commander_id = (int)$request->input('commander_id');
        if ( !$commander_id) {
            return jsonHelper(102, '必要的参数不能为空: commander_id');
        }

        $money = (float)$request->input('money');
        if ( !$money) {
            return jsonHelper(103, '必要的参数不能为空: money');
        }

        $obj = Commander::find($commander_id);
        if ( !$obj) {
            return jsonHelper(104, '传入的参数异常: commander_id');
        } else {
            if ($obj->residue_money < $money) {
                return jsonHelper(105, '剩余金额不足, 无法体现');
            }
        }

        $obj->update([
            'residue_money' => ($obj->residue_money) - $money
        ]);

        // 将提现记录写入数据库
        CommanderWithdrawRecord::create([
            'commander_id'       => $commander_id,
            // 总提成
            'total_money'        => $obj->withdraw_money,
            // 提现金额
            'withdraw_money'     => $money,
            // 剩余提成
            'residue_money'      => ($obj->residue_money) - $money,
        ]);

        return jsonHelper(0, '操作成功');
    }

    /**
     * 团长订单导出为 Excel
     */
    public function commanderOrderExportToExcel()
    {
        $result = Commander::select('id', 'name', 'phone', 'openid', 'delivery_id', 'total_money', 'withdraw_money', 'residue_money')->get();

        if ($result) {
            foreach ($result as $key => $value) {
                $delivery_area = Delivery::where('id', $value->delivery_id)->first();
                if ($delivery_area) {
                    $result[$key]['delivery_area'] = $delivery_area->deliver_name;
                }

                $userinfo = User::where('openid', $value->openid)->first();
                if ($userinfo) {
                    $result[$key]['user_nickname'] = $userinfo->nickname;
                }

                unset($result[$key]['openid']);
                unset($result[$key]['delivery_id']);
            }
        }
        $result = $result->toArray();

        // 设置 excel 标题
        $title = [
            'ID',
            '姓名',
            '联系方式',
            '提成率',
            '提成总金额',
            '已提成金额',
            '剩余提成金额',
            '团长配送区域',
            '团长微信昵称',
        ];

        // 数据导出为 Excel
        $this->exportToExcel($result, '社区团购团长结算表', $title);
    }

    /**
     * 导出 excel
     *
     * @param        $data
     * @param null   $savefile
     * @param null   $title
     * @param string $sheetname
     *
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function exportToExcel($data, $savefile = null, $title = null, $sheetname = 'sheet1')
    {
        // 若没有指定文件名则为当前时间戳
        if (is_null($savefile)) {
            $savefile = time();
        }
        // 若指字了excel表头，则把表单追加到正文内容前面去
        if (is_array($title)) {
            array_unshift($data, $title);
        }
        $objPHPExcel = new \PHPExcel();
        // Excel内容
        $head_num = count($data);

        foreach ($data as $k => $v) {
            $obj = $objPHPExcel->setActiveSheetIndex(0);
            $row = $k + 1; // 行
            $nn = 0;

            foreach ($v as $vv) {
                $col = chr(65 + $nn); // 列
                $obj->setCellValue($col . $row, $vv); // 列,行,值
                $nn++;
            }
        }

        // 设置列头标题
        for ($i = 0; $i < $head_num - 1; $i++) {
            $alpha = chr(65 + $i);
            $objPHPExcel->getActiveSheet()->getColumnDimension($alpha)->setAutoSize(true); // 单元宽度自适应
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setName("Candara");  // 设置字体
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setSize(12);  // 设置大小
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->getColor()->setARGB('FF000000'); // 设置颜色
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getAlignment()->setHorizontal('center'); // 水平居中
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getAlignment()->setVertical('center'); // 垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setBold(true); // 加粗
        }

        $objPHPExcel->getActiveSheet()->setTitle($sheetname); // 题目
        $objPHPExcel->setActiveSheetIndex(0); // 设置当前的sheet
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $savefile . '.xls"'); // 文件名称
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Excel5
        $objWriter->save('php://output');
    }

    /**
     * 获取团长 id
     *
     * @param Request $request
     *
     * @return string
     */
    public function getCommanderId(Request $request)
    {
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        $commander = Commander::where('openid', $openid)->where('is_delete', 0)->first();
        if ($commander) {
            $data = [
                'commander_id' => $commander->id,
            ];

            return jsonHelper(0, '获取成功', $data);
        } else {
            return jsonHelper(101, '暂无团长信息');
        }
    }

    /**
     * 团长收益
     *
     * @param Request $request
     *
     * @return string
     */
    public function commanderBalance(Request $request)
    {
        // openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        $commander = Commander::where('openid', $openid)->where('is_delete', 0)->first();
        if ($commander) {
            $data = [
                'is_commander'   => 1,
                'total_money'    => $commander->total_money,
                'withdraw_money' => $commander->withdraw_money,
                'residue_money'  => $commander->residue_money
            ];
        } else {
            $data = [
                'is_commander' => 0
            ];
        }

        return jsonHelper(0, '获取成功', $data);
    }

    /**
     * 团长配送小区
     *
     * @param Request $request
     *
     * @return string
     */
    public function area(Request $request)
    {
        // openid
        $commander_id = (int)$request->input('commander_id');
        if ( !$commander_id) {
            return jsonHelper(102, '必要的参数不能为空: commander_id');
        }

        $delivery = Commander::find($commander_id);
        if ($delivery) {
            $delivery_name = Delivery::where('id', $delivery->delivery_id)->select('deliver_name')->first();
            if ($delivery_name) {
                return jsonHelper(0, '获取成功', $delivery_name);
            }
        }

        return jsonHelper(101, '暂无配送小区');
    }
}
