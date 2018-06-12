<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/2
 * Time: 5:24 PM
 */

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Community\Tables\CommunityCommander;
use App\Http\Controllers\Community\Tables\CommunityCommanderOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// 数据库模型
use App\Http\Controllers\Community\Tables\CommunityGroupOrder;
use App\Http\Controllers\Community\Tables\CommunityGroupOrderDetail;
use App\Http\Controllers\Community\Tables\CommunityDelivery;
use App\Http\Controllers\Community\Tables\CommunityGroupDetail;

class OrderController extends BaseController
{
    /**
     * 下单
     *
     * @param Request $request
     *
     * @return string
     */
    public function placeOrder(Request $request)
    {
        // 判断用户是否登录失败
        if ( !$this->getSmallid($request)) {
            return jsonHelper(100, '登陆失败,可能原因：小程序已过期;后台未登陆');
        }

        // 随机令牌
        $token = $request->input('token');
        if ( !$token) {
            return jsonHelper(102, '必要的参数不能为空: token');
        } else {
            $is_insert = CommunityGroupOrder::where([
                'community_small_id' => $this->smallid,
                'token'              => $token
            ])->first();

            if ($is_insert == true) {
                return jsonHelper(103, '订单已提交, 请不要重复提交');
            }
        }

        // 团购 id
        $group_id = (int)$request->input('group_id');
        if ( !$group_id) {
            return jsonHelper(114, '必要的参数不能为空: group_id');
        }

        // 微信用户的 openID
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(104, '必要的参数不能为空: openid');
        }

        // 收货人姓名
        $username = $request->input('username');
        if ( !$username) {
            return jsonHelper(105, '必要的参数不能为空: username');
        }

        // 收货人手机号
        $phone = $request->input('phone');
        if ( !$phone) {
            return jsonHelper(106, '必要的参数不能为空: phone');
        } else if ( !preg_match("/^1[345678]\d{9}$/", $phone)) {
            return jsonHelper(107, '手机号格式不正确');
        }

        // 商品信息
        $goods_info = $request->input('goods_info');
        if ( !$goods_info) {
            return jsonHelper(111, '必要的参数不能为空: goods_info');
        } else if ( !json_decode($goods_info, true)) {
            return jsonHelper(112, '传入的参数必须为 json: goods_info');
        }
        $goods_info = json_decode($goods_info, true);

        // 商品总价
        $total_money = (float)$request->input('total_money');
        if ( !$total_money) {
            return jsonHelper(113, '必要的参数不能为空: total_money');
        }

        // 团长 id
        $commander_id = (int)$request->input('commander_id');
        if ($commander_id) {
            $is_buy_for_commander = 1;
        } else {
            $commander_id = null;
            $is_buy_for_commander = 0;
        }

        // 取货点自提 ID
        $delivery_area_id = (int)$request->input('delivery_area_id');
        if ($delivery_area_id) {
            $is_delivery = 1;
        } else {
            $delivery_area_id = null;
            $is_delivery = 0;

            // 收货人城市
            $city = $request->input('city');
            if ( !$city) {
                return jsonHelper(108, '必要的参数不能为空: city');
            }

            // 收货人县区
            $area = $request->input('area');
            if ( !$area) {
                return jsonHelper(109, '必要的参数不能为空: area');
            }

            // 收货人小区
            $delivery_name = $request->input('delivery_name');
            if ( !$delivery_name) {
                return jsonHelper(110, '必要的参数不能为空: delivery_name');
            } else {
                $is_set_delivery = CommunityDelivery::where('community_small_id', $this->smallid)->where('deliver_name', 'like', $delivery_name . '%')->first();
                if ($is_set_delivery) {
                    $delivery_id = $is_set_delivery->id;
                } else {
                    $delivery_id = DB::table('community_deliver')->insertGetId([
                        'deliver_name'       => $delivery_name,
                        'community_small_id' => $this->smallid
                    ]);
                }
            }

            // 小区详细地址
            $detail = $request->input('detail');
            if ( !$detail) {
                return jsonHelper(114, '必要的参数不能为空: detail');
            }
        }

        // 开启数据库事务处理
        \DB::beginTransaction();
        // 写入订单数据
        try {
            if ($is_delivery == 0) {
                // 订单数据
                $order_id = \DB::table('community_group_order')->insertGetId([
                    'order_number'         => $this->generateOrderNo(),
                    'group_id'             => $group_id,
                    'openid'               => $openid,
                    'username'             => $username,
                    'phone'                => $phone,
                    'delivery_id'          => $delivery_id,
                    'city'                 => $city,
                    'area'                 => $area,
                    'address'              => $city . $area . $delivery_name . $detail,
                    'total_money'          => $total_money,
                    'is_buy_for_commander' => $is_buy_for_commander,
                    'belongs_commander'    => $commander_id,
                    //                    'is_delivery'          => $is_delivery,
                    //                    'delivery_area_id'     => $delivery_area_id,
                    'token'                => $token,
                    'community_small_id'   => $this->smallid
                ]);
            } else if ($is_delivery == 1) {
                // 订单数据
                $order_id = \DB::table('community_group_order')->insertGetId([
                    'order_number'         => $this->generateOrderNo(),
                    'group_id'             => $group_id,
                    'openid'               => $openid,
                    'username'             => $username,
                    'phone'                => $phone,
                    // 'delivery_id'          => $delivery_id,
                    //                    'city'                 => $city,
                    //                    'area'                 => $area,
                    //                    'address'              => $city . $area . $delivery_name . $detail,
                    'total_money'          => $total_money,
                    'is_buy_for_commander' => $is_buy_for_commander,
                    'belongs_commander'    => $commander_id,
                    'is_delivery'          => $is_delivery,
                    'delivery_area_id'     => $delivery_area_id,
                    'token'                => $token,
                    'community_small_id'   => $this->smallid
                ]);
            }

            // 订单商品详情
            if ($order_id) {
                foreach ($goods_info as $key => $value) {
                    $goods_price = CommunityGroupDetail::find($value['goods_id']);
                    if ($goods_price) {
                        CommunityGroupOrderDetail::create([
                            'order_id'           => $order_id,
                            'goods_id'           => $value['goods_id'],
                            'goods_num'          => $value['goods_num'],
                            'goods_sum'          => ($goods_price->goods_price) * ($value['goods_num']),
                            'community_small_id' => $this->smallid,
                        ]);
                    }
                }
            }

            // 如果是通过分享下单的, 将订单数据写入团长中
            //            if ($is_buy_for_commander == 1 && $order_id) {
            //                $commander_info = Commander::find($commander_id);
            //                if ($commander_info) {
            //                    // 写入团长订单数据
            //                    CommanderOrder::create([
            //                        'order_id'           => $order_id,
            //                        'royalty_rate'       => $commander_info->royalty_rate,
            //                        'royalty_money'      => $total_money * ($commander_info->royalty_rate) / 100,
            //                        'community_small_id' => $this->smallid
            //                    ]);

            // 更新团长订单总销售额, 订单总提成, 团长提成剩余金额
            //                    Commander::where('id', $commander_id)->update([
            //                        // 团长订单总销售额
            //                        'total_money'    => ($commander_info->total_money) + $total_money,
            //                        // 团长总提成
            //                        'withdraw_money' => ($commander_info->withdraw_money) + ($total_money * ($commander_info->royalty_rate) / 100),
            //                        // 团长剩余提成
            //                        'residue_money'  => ($commander_info->residue_money) + ($total_money * ($commander_info->royalty_rate) / 100)
            //                    ]);

            //                    // 更新团长订单总提成
            //                    Commander::where('id', $commander_id)->update([
            //                        'withdraw_money' => ($commander_info->withdraw_money) + ($total_money * ($commander_info->royalty_rate) / 100)
            //                    ]);
            //
            //                    // 更新团长提成剩余金额
            //                    Commander::where('id', $commander_id)->update([
            //                        'residue_money' => ($commander_info->residue_money) + ($total_money * ($commander_info->royalty_rate) / 100)
            //                    ]);
            //                }
            //            }

            // 提交数据
            \DB::commit();

            $data = ['order_id' => $order_id];

            return jsonHelper(0, '操作成功', $data);
        } catch (\Exception $e) {
            // 接收异常处理并回滚
            \DB::rollBack();

            return $e;
        }
    }

    /**
     * 生成唯一订单编号
     *
     * @return string
     */
    private function generateOrderNo()
    {
        return date('Ymd') . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
     * 定时任务, 自动确认订单
     */
    public function confirmOrder()
    {
        //        $order = GroupOrder::where('order_status', 3)->where('express', '!=', '')->where('express_number', '!=', '')->get();
        $order = CommunityGroupOrder::where('order_status', 3)->get();
        if ($order) {
            $order = $order->toArray();
            // 发货时间要是 > 两天, 确认收货
            foreach ($order as $key => $value) {
                $is_greater_than = time() - strtotime($value['deliver_time']);
                // 两天时间
                $tow_days = 3600 * 24 * 3;
                if ($is_greater_than > $tow_days) {
                    // 将订单状态自动改为完成
                    CommunityGroupOrder::where('id', $value['id'])->update([
                        'order_status' => 4
                    ]);
                    // 信息写入日志
                    \Log::INFO('auto_confirm_order_message: order_id = ' . $value['id']);
                }
            }
        }

        return;
    }
}
