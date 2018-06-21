<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/3
 * Time: 2:36 PM
 */

namespace App\Http\Controllers\Api\V1;

use App\Models\Commander;
use App\Models\CommanderOrder;
use App\Models\GroupOrder;
use App\Models\GroupOrderDetail;
use App\Models\GroupDetail;
use App\Models\PayConfig;

class UserWxCallBackNotify extends \WxPayNotify
{
    /**
     * 微信支付回调处理
     *
     * @param array  $data
     * @param string $msg
     *
     * @return bool|\true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     * @throws \Exception
     */
    public function NotifyProcess($data, &$msg)
    {
        if ($data['result_code'] == 'SUCCESS') {
            // 商户订单号
            $orderNo = $data['out_trade_no'];
            // 微信支付订单号
            $transaction_id = $data['transaction_id'];

            \DB::beginTransaction();
            try {
                // 1. 更新微信支付订单编号
                GroupOrder::where([
                    'order_number' => $orderNo
                ])->update([
                    'transaction_id' => $transaction_id
                ]);

                // 2. 更新订单状态
                GroupOrder::where([
                    'order_number' => $orderNo
                ])->update([
                    'order_status' => 1
                ]);

                // 3. 削减库存
                $order_info = GroupOrder::where('order_number', $orderNo)->first();
                $order_id = $order_info->id;
                $order_goods = GroupOrderDetail::where('order_id', $order_id)->get();
                foreach ($order_goods as $key => $value) {
                    $group_goods = GroupDetail::where('id', $value->goods_id)->first();
                    GroupDetail::where('id', $value->goods_id)->update([
                        'goods_num' => ($group_goods->goods_num) - ($value->goods_num)
                    ]);
                }

                // 4. 如果是通过团长购买的更新团长提成
                if ($order_info->is_buy_for_commander == 1) {
                    // 团长 ID
                    $commander_id = $order_info->belongs_commander;
                    // 团长详情
                    $commander_info = Commander::find($commander_id);

                    // 计算团长提成
                    $order_goods_id = GroupOrderDetail::where('order_id', $order_id)->get();
                    $commander_money = 0;
                    if ($order_goods_id) {
                        foreach ($order_goods_id as $key => $value) {
                            $goods_id = $value->goods_id;
                            $single_goods_royalty_rate = GroupDetail::where('id', $goods_id)->first();
                            $single_order_goods_withdraw = ($value->goods_sum) * ($single_goods_royalty_rate->royalty_rate) / 100;
                            $commander_money += $single_order_goods_withdraw;
                        }
                    }

                    // 写入团长订单数据
                    CommanderOrder::create([
                        'order_id'           => $order_id,
                        'royalty_money'      => $commander_money,
                    ]);

                    // 更新团长订单总销售额, 订单总提成, 团长提成剩余金额
                    Commander::where('id', $commander_id)->update([
                        // 团长订单总销售额
                        'total_money'    => ($commander_info->total_money) + ($order_info->total_money),
                        // 团长总提成
                        'withdraw_money' => ($commander_info->withdraw_money) + $commander_money,
                        // 团长剩余提成
                        'residue_money'  => ($commander_info->residue_money) + $commander_money
                    ]);
                }

                // 提交数据
                \DB::commit();

                return true;
            } catch (\Exception $ex) {
                //接收异常处理并回滚
                \DB::rollBack();
                // 记录错误日志
                \Log::error($ex);

                // 如果出现异常，向微信返回false，请求重新发送通知
                return false;
            }
        }

        return true;
    }
}
