<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/3
 * Time: 3:28 PM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Common\CurlHelper;
use App\Http\Controllers\Controller;
// 数据库模型
use App\Models\CheckCodeManager;
use App\Models\Company;
use App\Models\DeliveryArea;
use App\Models\Group;
use App\Models\GroupDetail;
use App\Models\GroupDetailPicture;
use App\Models\User;
use App\Models\GroupOrder;
use App\Models\GroupOrderDetail;

// Excel 数据操作
require_once __DIR__ . '/../../../../SDK/Excel/PHPExcel.php';

class OrderListController extends Controller
{
    /**
     * 后台订单列表
     *
     * @param Request $request
     *
     * @return string
     */
    public function getBackOrderList(Request $request)
    {
        // 所有订单
        $result = GroupOrder::where('is_delete', 0)->select('id',
            'order_number', 'username', 'phone', 'address', 'order_status', 'total_money', 'is_buy_for_commander',
            'is_delivery', 'delivery_area_id', 'express', 'express_number', 'create_at')->orderBy('create_at',
            'desc')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/orderlist/back');

        // 后台订单详情
        $this->backOrderDetail($result);

        return $result->toJson();
    }

    /**
     * 查询后台订单详情
     *
     * @param $result
     */
    private function backOrderDetail($result)
    {
        if ($result) {
            foreach ($result as $key => $value) {
                // 单个订单详情
                $order_detail = GroupOrderDetail::where('order_id', $value->id)->select('id', 'order_id',
                    'goods_id', 'goods_num')->get();
                if ($order_detail) {
                    $order_detail = $order_detail->toArray();
                    $goods = '';
                    foreach ($order_detail as $k => $v) {
                        // 每个订单中包含的商品
                        $order_goods = GroupDetail::where('id', $v['goods_id'])->select('id', 'goods_name', 'goods_specification', 'goods_price')->first();
                        if ($order_goods) {
                            $order_goods = $order_goods->toArray();
                        }
                        // 压入订单中的商品购买数量
                        $order_goods['goods_num'] = $v['goods_num'];
                        $goods .= $order_goods['goods_name'] . '(' . $order_goods['goods_specification'] . ') X ' . $order_goods['goods_price'] . '(单价) X ' . $order_goods['goods_num'] . '(数量); ';
                    }
                }
                $result[$key]['goods_info'] = $goods;

                if ($value->is_delivery == 1) {
                    $deliver_area_info = DeliveryArea::where('id', $value->delivery_area_id)->select('delivery_area', 'phone', 'address')->first()->toArray();
                    $result[$key]['deliver_area_info'] = $deliver_area_info;
                }
            }
        }
    }

    /**
     * 前台订单详情
     *
     * @param Request $request
     *
     * @return string
     */
    public function getFrontOrderList(Request $request)
    {
        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        // 订单状态
        $order_status = (int)$request->input('order_status');

        // 所有订单
        $result = GroupOrder::where('is_delete', 0)->where('openid', $openid)->where('order_status', $order_status)->select('id', 'order_number', 'group_id', 'username', 'phone', 'address', 'order_status', 'total_money', 'token', 'express', 'express_number', 'is_delivery', 'delivery_area_id', 'create_at')->orderBy('create_at', 'desc')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/orderlist/front');

        // 前台订单详情
        $this->frontOrderDetail($result);

        return $result->toJson();
    }

    /**
     * 前台订单查询
     *
     * @param $result
     */
    private function frontOrderDetail($result)
    {
        if ($result) {
            foreach ($result as $key => $value) {
                // 团购图片
                $group_picture = Group::where('id', $value->group_id)->select('theme', 'introduce', 'introduce_picture')->first();
                if ($group_picture) {
                    $result[$key]['theme'] = $group_picture->theme;
                    $result[$key]['introduce'] = $group_picture->introduce;
                    $result[$key]['group_picture'] = $group_picture->introduce_picture;
                }

                // 单个订单详情
                $order_detail = GroupOrderDetail::where('order_id', $value->id)->select('id', 'order_id', 'goods_id', 'goods_num')->get();
                if ($order_detail) {
                    $order_detail = $order_detail->toArray();
                    $goods = [];
                    foreach ($order_detail as $k => $v) {
                        // 每个订单中包含的商品
                        $order_goods = GroupDetail::where('id', $v['goods_id'])->select('id', 'goods_name', 'goods_specification', 'goods_price')->first();
                        if ($order_goods) {
                            $order_goods = $order_goods->toArray();
                        }

                        // 压入订单中的商品购买数量
                        $order_goods['goods_num'] = $v['goods_num'];

                        // 商品图片
                        $goods_img = GroupDetailPicture::where('goods_id', $v['goods_id'])->select('id', 'picture')->get();
                        if ($goods_img) {
                            $goods_img = $goods_img->toArray();
                        }
                        $order_goods['goods_img'] = $goods_img;

                        $goods[] = $order_goods;
                    }
                }
                $result[$key]['goods_info'] = $goods;

                if ($value->is_delivery == 1) {
                    $deliver_area_info = DeliveryArea::where('id', $value->delivery_area_id)->select('delivery_area', 'phone', 'address')->first()->toArray();
                    $result[$key]['deliver_area_info'] = $deliver_area_info;
                }
            }
        }
    }

    /**
     * 根据条件查询后台订单
     *
     * @param Request $request
     *
     * @return string
     */
    public function searchBackOrderList(Request $request)
    {
        // 所有订单
        $result = GroupOrder::where('is_delete', 0)->where(function ($query) use ($request) {
            ($request->input('order_status') !== '7') && $query->where('order_status',
                (int)$request->input('order_status'));
            ($request->input('belongs_commander') !== '0') && $query->where('belongs_commander',
                (int)$request->input('belongs_commander'));
            ($request->input('delivery_id') !== '0') && $query->where('delivery_id',
                (int)$request->input('delivery_id'));
            ($request->input('begin_time') !== '0') && $query->where('create_at', '>',
                strtotime($request->input('begin_time')));
            ($request->input('end_time') !== '0') && $query->where('create_at', '<',
                strtotime($request->input('end_time')));
        })->select('id', 'order_number', 'username', 'phone', 'address', 'order_status', 'total_money', 'is_buy_for_commander', 'is_delivery', 'delivery_area_id', 'create_at')->orderBy('create_at', 'desc')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/orderlist/back');

        // 后台订单详情
        $this->backOrderDetail($result);

        return $result->toJson();
    }

    /**
     * 发货
     *
     * @param Request $request
     *
     * @return string
     */
    public function deliverGoods(Request $request)
    {
        $order_id = (int)$request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        $order_info = GroupOrder::find($order_id);
        if ( !$order_info) {
            return jsonHelper(103, '传入的参数异常: order_id');
        }

        if ($order_info->order_status == 3) {
            return jsonHelper(106, '订单已发货, 请勿重复提交');
        }

        if ($order_info->order_status != 1) {
            return jsonHelper(105, '订单尚未支付, 无法发货');
        }

        // 2018-5-7 新增
        $express = $request->input('express');
        $express_number = $request->input('express_number');

        if ($express && $express_number) {
            $order_info->update([
                'order_status'   => 3,
                'express'        => $express,
                'express_number' => $express_number,
                // 发货时间
                'deliver_time'   => date('Y-m-d H:i:s', time())
            ]);
        } else {
            $order_info->update([
                'order_status' => 3,
                // 发货时间
                'deliver_time' => date('Y-m-d H:i:s', time())
            ]);
        }

        return jsonHelper(0, '操作成功');
    }

    /**
     * 批量发货
     *
     * @param Request $request
     *
     * @return string
     */
    public function deliverGoodsToghter(Request $request)
    {
        // 订单 ID 数组
        $order_id_array = $request->input('order_id');
        if ( !$order_id_array) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }
        $order_id_array = explode(',', $order_id_array);

        // 查询订单
        foreach ($order_id_array as $key => $value) {
            $order_info = GroupOrder::find($value);
            if ($order_info) {
                $order_info->update([
                    'order_status' => 3
                ]);
            }
        }

        return jsonHelper(0, '操作成功!');
    }

    /**
     * 获取订单二维码
     *
     * @param Request $request
     *
     * @return string
     */
    public function getqrcode(Request $request)
    {
        // 订单 id
        $order_id = (int)$request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        } else {
            $order = GroupOrder::find($order_id);
            if ( !$order) {
                return jsonHelper(103, '订单不存在, 请检查订单 ID');
            }
        }

        $pay_config = PayConfig::first();
        // 获取 access_token
        $access_token = $this->getAccessToken($request, $pay_config);

        // 获取小程序码 api
        $api_url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=' . $access_token;

        $qrcode = '/storage/images/community/qrcode/' . $pay_config->appid . '_' . $order_id . '.jpg';
        $filename = '../storage/images/community/qrcode/' . $pay_config->appid . '_' . $order_id . '.jpg';
        $data = '{"path": "pages/hexiao/hexiao?order_id=' . $order_id . '"}';

        $curl = new CurlHelper();
        $curl->postCurlFile($api_url, $data, $filename);

        $order->qrcode = $qrcode;
        $order->save();

        return jsonHelper(0, '获取成功', $qrcode);
    }

    /**
     * 获取 access_token
     *
     * @param $request
     *
     * @return string
     */
    private function getAccessToken($request, $pay_config)
    {
        if ( !$pay_config) {
            return jsonHelper(103, '请填写支付信息');
        } else {
            $api = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $pay_config->appid . '&secret=' . $pay_config->appsecret;
            $curl = new CurlHelper();
            $result = $curl->getCurl($api);

            return $result['access_token'];
        }
    }

    /**
     * 执行订单核销
     *
     * @param Request $request
     *
     * @return string
     */
    public function checkOrder(Request $request)
    {
        $order_id = $request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(103, '必要的参数不能为空: openid');
        }
        // 如果该核销员可以核销所有区域
        $is_all_area_check_code_manager = CheckCodeManager::where('is_delete', 0)->where('level', 1)->where('openid', $openid)->get();
        if ($is_all_area_check_code_manager) {
            foreach ($is_all_area_check_code_manager as $key => $value) {
                if ($openid == $value->openid) {
                    // 开启数据库事务处理
                    \DB::beginTransaction();
                    // 写入订单数据
                    try {
                        GroupOrder::where('id', $order_id)->update([
                            'order_status' => 4,
                        ]);
                        // 提交数据
                        \DB::commit();

                        return jsonHelper(0, '操作成功');
                    } catch (\Exception $e) {
                        // 接收异常处理并回滚
                        \DB::rollBack();

                        return jsonHelper(101, '操作失败');
                    }
                }
            }
        }

        // 如果该核销员可以核销指定区域
        $order_info = GroupOrder::find($order_id);
        if ($order_info) {
            // 所有的配送区域
            $receiver_delivery = CheckCodeManager::where('is_delete', 0)->where('delivery_id', $order_info->delivery_id)->get();
            if ($receiver_delivery) {
                foreach ($receiver_delivery as $key => $value) {
                    if ($openid == $value->openid) {
                        // 开启数据库事务处理
                        \DB::beginTransaction();
                        // 写入订单数据
                        try {
                            GroupOrder::where('id', $order_id)->update([
                                'order_status' => 4,
                            ]);
                            // 提交数据
                            \DB::commit();

                            return jsonHelper(0, '操作成功');
                        } catch (\Exception $e) {
                            // 接收异常处理并回滚
                            \DB::rollBack();

                            return jsonHelper(101, '操作失败');
                        }
                    }
                }

                return jsonHelper(104, '您不是指定的核销员无法进行核销操作');
            } else {
                return jsonHelper(105, '该区域暂无核销员信息');
            }
        }
    }

    /**
     * 确认收货
     *
     * @param Request $request
     *
     * @return string
     */
    public function orderConfirm(Request $request)
    {
        $order_id = $request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        // 微信用户的 openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(103, '必要的参数不能为空: openid');
        }

        $order = GroupOrder::where('id', $order_id)->where('openid', $openid)->first();
        if ($order) {
            $order->update([
                'order_status' => 4,
            ]);

            return jsonHelper(0, '操作成功');
        }
    }

    /**
     * 删除订单
     *
     * @param Request $request
     *
     * @return string
     */
    public function deleteOrder(Request $request)
    {
        $order_id = $request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        $order = GroupOrder::find($order_id);
        if ( !$order) {
            return jsonHelper(103, '传入的参数异常: order_id');
        }

        GroupOrder::where('id', $order_id)->update([
            'is_delete' => 1
        ]);

        return jsonHelper(0, '操作成功');
    }

    /**
     * 导出订单
     *
     * @param Request $request
     *
     * @return string
     */
    public function singleOrderExport(Request $request)
    {
        $order_id = (int)$request->input('order_id');
        if ( !$order_id) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }

        $result = GroupOrder::where('id', $order_id)->select('id', 'order_number', 'username', 'phone', 'address', 'order_status', 'total_money', 'is_delivery', 'delivery_area_id', 'create_at')->first();
        if ($result) {
            $result = $result->toArray();
            $order_detail = GroupOrderDetail::where('order_id', $order_id)->select('id', 'order_id', 'goods_id', 'goods_num')->get();
            if ($order_detail) {
                $goods = '';
                foreach ($order_detail as $k => $v) {
                    // 每个订单中包含的商品
                    $order_goods = GroupDetail::where('id', $v['goods_id'])->select('id', 'goods_name', 'goods_specification', 'goods_price')->first();
                    if ($order_goods) {
                        $order_goods = $order_goods->toArray();
                    }
                    // 压入订单中的商品购买数量
                    $order_goods['goods_num'] = $v['goods_num'];
                    $goods .= $order_goods['goods_name'] . '(' . $order_goods['goods_specification'] . ') X ' . $order_goods['goods_num'] . '(数量) X ' . $order_goods['goods_price'] . '(单价);';
                }
                $result['goods_info'] = $goods;

                if ($result['is_delivery'] == 1) {
                    $deliver_area_info = DeliveryArea::where('id', $result['delivery_area_id'])->select('delivery_area', 'phone', 'address')->first()->toArray();
                    $result['address'] = '配送点地址: ' . $deliver_area_info['address'] . '(电话: ' . $deliver_area_info['phone'] . ')';
                }

                $data = [
                    [
                        '订单编号: ',
                        '`' . $result['order_number'],
                    ],
                    [
                        '收货人: ',
                        $result['username'],
                    ],
                    [
                        '联系方式: ',
                        '`' . $result['phone'],
                    ],
                    [
                        '收货地址: ',
                        $result['address'],
                    ],
                    [
                        '订单详情: ',
                        $result['goods_info'],
                    ],
                    [
                        '创建时间: ',
                        $result['create_at'],
                    ],
                    [
                        '订单总金额: ',
                        $result['total_money'] . ' 元',
                    ]
                ];

                // 设置 excel 标题
                $title = [
                    '条目',
                    '详情'
                ];
                // 数据导出为 Excel
                $this->singleExportToExcel($data, '团购惠 ' . $result['order_number'] . ' 订单', $title);
            }
        }
    }

    /**
     * 导出 excel
     *
     * @param        $data
     * @param null   $savefile
     * @param null   $title
     * @param string $sheetname
     */
    private function singleExportToExcel($data, $savefile = null, $title = null, $sheetname = 'sheet1')
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
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setName("Candara");  // 设置字体
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setSize(14);  // 设置大小
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->getColor()->setARGB('FF000000'); // 设置颜色
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getAlignment()->setHorizontal('left'); // 水平居中
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getAlignment()->setVertical('center'); // 垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setBold(true); // 加粗
        }

        $objPHPExcel->getActiveSheet()->setTitle($sheetname); // 题目
        $objPHPExcel->setActiveSheetIndex(0); // 设置当前的sheet
        $objPHPExcel->getProperties()->setCreator("团购惠"); // 设置文件的创建者
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12); // 设置除标题外的的文字大小

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12); // 列宽 15
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50); // 列宽 40

        $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getStyle('B6')->getAlignment()->setWrapText(true); // 长度不够显示的时候 是否自动换行

        $objPHPExcel->getActiveSheet()->getStyle('A5')->getAlignment()->setVertical('center'); // 垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A6')->getAlignment()->setVertical('center');
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->setVertical('center');
        $objPHPExcel->getActiveSheet()->getStyle('B6')->getAlignment()->setVertical('center');

        $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE); // 为横向打印
        $pageMargins = $objPHPExcel->getActiveSheet()->getPageMargins();
        // 设置边距为0.5厘米 (1英寸 = 2.54厘米)
        $margin = 0.5 / 2.54;   //phpexcel 中是按英寸来计算的,所以这里换算了一下
        $pageMargins->setTop($margin);  // 上边距
        $pageMargins->setBottom($margin);   // 下
        $pageMargins->setLeft($margin * 2); // 左
        $pageMargins->setRight($margin * 2);    // 右

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $savefile . '.xls"'); // 文件名称
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5'); // Excel5
        $objWriter->save('php://output');
    }

    /**
     * 导出 excel
     *
     * @param Request $request
     *
     * @return string
     */
    public function export(Request $request)
    {
        // 订单 ID 数组
        $order_id_array = $request->input('order_id');
        if ( !$order_id_array) {
            return jsonHelper(102, '必要的参数不能为空: order_id');
        }
        $order_id_array = explode(',', $order_id_array);

        // 查询订单
        $result = \DB::table('community_group_order')->whereIn('id', $order_id_array)->select('id', 'order_number', 'username', 'phone', 'address', 'order_status', 'total_money', 'express', 'express_number', 'is_delivery', 'delivery_area_id', 'create_at')->orderBy('create_at', 'desc')->get();
        if ($result) {
            foreach ($result as $key => $value) {
                // 单个订单详情
                $result[$key] = (array)$result[$key];
                $order_detail = GroupOrderDetail::where('order_id', $value->id)->select('id', 'order_id', 'goods_id', 'goods_num')->get();
                if ($order_detail) {
                    $order_detail = $order_detail->toArray();
                    $goods = '';
                    foreach ($order_detail as $k => $v) {
                        // 每个订单中包含的商品
                        $order_goods = GroupDetail::where('id', $v['goods_id'])->select('id', 'goods_name', 'goods_specification', 'goods_price')->first();
                        if ($order_goods) {
                            $order_goods = $order_goods->toArray();
                        }
                        // 压入订单中的商品购买数量
                        $order_goods['goods_num'] = $v['goods_num'];
                        $goods .= $order_goods['goods_name'] . '(' . $order_goods['goods_specification'] . ') X ' . $order_goods['goods_price'] . '(单价) X ' . $order_goods['goods_num'] . '(数量); ';
                    }
                }
                $result[$key]['goods_info'] = $goods;

                if ($value->is_delivery == 1) {
                    $deliver_area_info = DeliveryArea::where('id', $value->delivery_area_id)->select('delivery_area', 'phone', 'address')->first()->toArray();
                    $result[$key]['address'] = $deliver_area_info['delivery_area'];
                }

                if ($result[$key]['order_status'] == 0) {
                    $result[$key]['order_status'] = '未支付';
                } else {
                    if ($result[$key]['order_status'] == 1) {
                        $result[$key]['order_status'] = '已支付';
                    } else {
                        if ($result[$key]['order_status'] == 3) {
                            $result[$key]['order_status'] = '已配送';
                        } else {
                            if ($result[$key]['order_status'] == 4) {
                                $result[$key]['order_status'] = '已完成';
                            } else {
                                $result[$key]['order_status'] = '已退款';
                            }
                        }
                    }
                }
                // 订单编号前边拼接 `, 防止导出 Excel, 编号过大已科学计数法显示
                $result[$key]['order_number'] = '`' . $result[$key]['order_number'];
                // 快递编号前边拼接 `, 防止导出 Excel, 编号过大已科学计数法显示
                if ($result[$key]['express_number'] != '') {
                    $result[$key]['express_number'] = '`' . $result[$key]['express_number'];
                }
                // 公司信息
                $company_deliver_info = Company::first();
                if ($company_deliver_info) {
                    $result[$key]['address'] = $company_deliver_info->delivery_city . $result[$key]['address'];
                }
                unset($result[$key]['id']);
            }
        }

        // 设置 excel 标题
        $title = [
            '订单编号',
            '姓名',
            '联系方式',
            '收货地址',
            '订单状态',
            '总金额',
            '快递方式',
            '快递编号',
            '创建时间',
            '订单详情',
        ];

        // 数据导出为 Excel
        $this->exportToExcel($result, '订单统计表', $title);
    }

    /**
     * 导出 excel
     *
     * @param        $data
     * @param null   $savefile
     * @param null   $title
     * @param string $sheetname
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
     * 最近完成订单
     *
     * @param Request $request
     *
     * @return string
     */
    public function recentOrder(Request $request)
    {
        $result = GroupOrder::select('id', 'username', 'openid', 'create_at')->orderBY('id', 'desc')->take(10)->get();
        if ($result) {
            $this->backOrderDetail($result);
            foreach ($result as $key => $value) {
                $userinfo = User::where('openid', $value->openid)->first();
                $result[$key]['avatar'] = $userinfo->avatar;
            }
        }

        return $result->toJson();
    }
}
