<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:43 PM
 */

namespace App\Http\Controllers\Community;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
// 数据库模型
use App\Models\GroupOrder;
use App\Models\GroupOrderDetail;
use App\Models\GroupUserClick;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupDetail;
use App\Models\GroupDetailPicture;


class IndexController extends Controller
{
    /**
     * 更新或存储微信用户信息
     * 更新用户信息, 业务需要微信用户的头像和昵称信息, 所以需要把这些信息存储下来
     *
     * @param Request $request
     *
     * @return string
     */
    public function user(Request $request)
    {
        // 微信用户的openid
        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(102, '必要的参数不能为空: openid');
        }

        // 微信用户的昵称
        $nickname = $request->input('nickname');
        if ( !$nickname) {
            return jsonHelper(105, '必要的参数不能为空: nickname');
        }

        // 微信用户的头像
        $avatar = $request->input('avatar');
        if ( !$avatar) {
            return jsonHelper(106, '必要的参数不能为空: avatar');
        }

        $obj = User::where([
            'openid' => $openid
        ])->first();

        if ($obj) {
            if ( !(($obj->nickname === $nickname) && ($obj->avatar === $avatar))) {
                $obj->nickname = $nickname;
                $obj->avatar = $avatar;
            }
        } else {
            $obj = new User();
            $obj->nickname = $nickname;
            $obj->avatar = $avatar;
            $obj->openid = $openid;
        }

        try {
            $obj->save();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {
            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 即将开始(最多取了6条数据)
     *
     * @param Request $request
     *
     * @return string
     */
    public function startSoon(Request $request)
    {
        $group_result = Group::where('is_delete', 0)->where('is_putaway', 0)->where('begin_time', '>', time())->select('id', 'theme', 'introduce', 'begin_time', 'end_time', 'introduce_picture', 'create_at')->orderBy('is_top', 1)->orderBy('create_at', 'desc')->limit(6)->get();
        if ( !$group_result) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        // 商品详情
        $this->searchGoodsInfo($group_result);

        return jsonHelper(0, '获取成功', $group_result);
    }

    /**
     * 所有未开始的商品
     *
     * @param Request $request
     *
     * @return string
     */
    public function startSoonAll(Request $request)
    {
        $group_result = Group::where('is_delete', 0)->where('is_putaway', 0)->where('begin_time', '>', time())->select('id', 'theme', 'introduce', 'begin_time', 'end_time', 'introduce_picture', 'create_at')->orderBy('is_top', 1)->orderBy('create_at', 'desc')->paginate(15)->setPath('https://www.ailetugo.com/ailetutourism/public/community/shopping/srart-soon/all');
        if ( !$group_result) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        // 商品详情
        $this->searchGoodsInfo($group_result);

        return $group_result->toJson();
    }

    /**
     * 正在进行的团购
     *
     * @param Request $request
     *
     * @return string
     */
    public function onSell(Request $request)
    {
        $group_result = Group::where('is_delete', 0)->where('is_putaway', 0)->where('begin_time', '<', time())->select('id', 'theme', 'introduce', 'begin_time', 'end_time', 'introduce_picture', 'click_num', 'create_at')->orderBy('is_top', 1)->orderBy('create_at', 'desc')->paginate(15)->setPath('https://www.ailetugo.com/ailetutourism/public/community/onsell');
        if ( !$group_result) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        // 商品详情
        $this->onsellSearchGoodsInfo($group_result);

        return $group_result->toJson();
    }

    /**
     * 商品详情
     *
     * @param $group_result
     */
    private function searchGoodsInfo($group_result)
    {
        foreach ($group_result as $key => $value) {
            $goods_info = GroupDetail::where('group_id', $value['id'])->where('is_delete', 0)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num')->get();
            if ($goods_info) {
                foreach ($goods_info as $k => $v) {
                    // 商品图片
                    $goods_img = GroupDetailPicture::where('goods_id', $v['id'])->select('id', 'picture')->get();
                    if ($goods_img) {
                        $goods_img = $goods_img->toArray();
                    }
                    $goods_info[$k]['picture'] = $goods_img;
                }
                // 商品详情
                $group_result[$key]['goods_info'] = $goods_info;
            }
            // 时间剩余
            $group_result[$key]['time_reminding'] = $this->timediff(time(), $value['begin_time']);
        }
    }

    /**
     * 商品详情
     *
     * @param $group_result
     */
    private function onsellSearchGoodsInfo($group_result)
    {
        foreach ($group_result as $key => $value) {
            $goods_info = GroupDetail::where('group_id', $value['id'])->where('is_delete', 0)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num')->get();
            if ($goods_info) {
                // 团购的下单数量
                $buy_group_num = GroupOrder::where('group_id', $value['id'])->count();
                $group_result[$key]['buy_group_num'] = $buy_group_num;

                foreach ($goods_info as $k => $v) {
                    // 商品图片
                    $goods_img = GroupDetailPicture::where('goods_id', $v['id'])->select('id', 'picture')->get();
                    if ($goods_img) {
                        $goods_img = $goods_img->toArray();
                    }
                    $goods_info[$k]['picture'] = $goods_img;
                }
                // 商品详情
                $group_result[$key]['goods_info'] = $goods_info;
            }

            $click_user_info = GroupUserClick::where('group_id', $value['id'])->select('id', 'openid')->orderBy('update_at', 'desc')->take(36)->get();
            if ($click_user_info) {
                foreach ($click_user_info as $k => $v) {
                    // 用户头像
                    $user_avatar = User::where('openid', $v['openid'])->select('id', 'avatar')->get();
                    if ($user_avatar->count()) {
                        // 不为空
                        $user_avatar = $user_avatar->toArray();
                        $click_user_info[$k]['user_info'] = $user_avatar;
                    } else {
                        // 为空
                        unset($click_user_info[$k]);
                    }
                }
                $click_user_info = array_values(array_slice($click_user_info->toArray(), 0, 15));
                // 用户点击详情
                $group_result[$key]['click_user_avatar'] = $click_user_info;
            }

            // 时间剩余
            $group_result[$key]['time_reminding'] = $this->timediff(time(), $value['end_time']);
        }
    }

    /**
     * 时间相差格式化
     *
     * @param $begin_time
     * @param $end_time
     *
     * @return array
     */
    private function timediff($begin_time, $end_time)
    {
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            return [
                'day'  => 0,
                'hour' => 0,
                'min'  => 0,
                'sec'  => 0
            ];
        }
        // 计算天数
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);

        // 计算小时数
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);

        // 计算分钟数
        $remain = $remain % 3600;
        $mins = intval($remain / 60);

        // 计算秒数
        $secs = $remain % 60;

        return [
            'day'  => $days,
            'hour' => $hours,
            'min'  => $mins,
            'sec'  => $secs
        ];
    }
}
