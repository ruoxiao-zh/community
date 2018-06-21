<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 3:01 PM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
use App\Common\CurlHelper;
use App\Http\Controllers\Controller;
// 数据库模型
use App\Models\Commander;
use App\Models\GroupOrder;
use App\Models\GroupOrderDetail;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupDetail;
use App\Models\GroupDetailPicture;
use App\Models\PayConfig;
use App\Models\GroupUserClick;

class GroupController extends Controller
{
    /**
     * 上传拼团介绍图片
     *
     * @return string
     */
    public function groupUploadImg()
    {
        $logo = SaveImage::getSaveImageUrl('images/community/group', 'introduce_picture', '', false);
        if ( !$logo) {
            return jsonHelper(101, '必要的参数不能为空: introduce_picture');
        } else {
            return jsonHelper(0, '上传成功', $logo);
        }
    }

    /**
     * 添加团购
     *
     * @param Request $request
     *
     * @return string
     * @throws \Exception
     */
    public function create(Request $request)
    {
        // 团购主题
        $theme = $request->input('theme');
        if ( !$theme) {
            return jsonHelper(102, '必要的参数不能为空: theme');
        }

        // 简介
        $introduce = $request->input('introduce');
        if ( !$introduce) {
            return jsonHelper(103, '必要的参数不能为空: introduce');
        }

        // 开始时间
        $begin_time = (int)$request->input('begin_time');
        if ( !$begin_time) {
            return jsonHelper(104, '必要的参数不能为空: begin_time');
        }

        // 结束时间
        $end_time = (int)$request->input('end_time');
        if ( !$end_time) {
            return jsonHelper(105, '必要的参数不能为空: end_time');
        } else if ($end_time <= $begin_time) {
            return jsonHelper(106, '开始时间不能小于结束时间');
        }

        // 宣传图片
        $introduce_picture = $request->input('introduce_picture');
        if ( !$introduce_picture) {
            return jsonHelper(107, '必要的参数不能为空: introduce_picture');
        }

        // 包含商品
        $group_goods = $request->input('group_goods');
        if ( !$group_goods) {
            return jsonHelper(108, '必要的参数不能为空: group_goods');
        } else if ( !json_decode($group_goods, true)) {
            return jsonHelper(109, '传入的参数必须为 json: group_goods');
        }
        $group_goods = json_decode($group_goods, true);

        // 开启数据库事务处理
        \DB::beginTransaction();
        try {
            $group_id = \DB::table('community_group')->insertGetId([
                'theme'              => $theme,
                'introduce'          => $introduce,
                'begin_time'         => $begin_time,
                'end_time'           => $end_time,
                'introduce_picture'  => $introduce_picture
            ]);
            if ($group_id) {
                foreach ($group_goods as $key => $value) {
                    GroupDetail::where('id', $value['goods_id'])->update([
                        'group_id' => $group_id
                    ]);
                }
            }

            // 提交数据
            \DB::commit();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {

            //接收异常处理并回滚
            \DB::rollBack();

            return jsonHelper(101, '操作失败');
        }
    }

    /**
     * 修改团购
     *
     * @param Request $request
     *
     * @return \Exception|string
     * @throws \Exception
     */
    public function update(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(101, '必要的参数不能为空: id');
        }

        $obj = Group::find($group_id);
        if ( !$obj) {
            return jsonHelper(102, '传入的参数异常: id');
        }

        // 团购主题
        $theme = $request->input('theme');
        if ( !$theme) {
            return jsonHelper(104, '必要的参数不能为空: theme');
        }

        // 简介
        $introduce = $request->input('introduce');
        if ( !$introduce) {
            return jsonHelper(105, '必要的参数不能为空: introduce');
        }

        // 开始时间
        $begin_time = (int)$request->input('begin_time');
        if ( !$begin_time) {
            return jsonHelper(106, '必要的参数不能为空: begin_time');
        }

        // 结束时间
        $end_time = (int)$request->input('end_time');
        if ( !$end_time) {
            return jsonHelper(107, '必要的参数不能为空: end_time');
        } else if ($end_time <= $begin_time) {
            return jsonHelper(108, '开始时间不能小于结束时间');
        }

        // 宣传图片
        $introduce_picture = $request->input('introduce_picture');
        if ( !$introduce_picture) {
            return jsonHelper(109, '必要的参数不能为空: introduce_picture');
        }

        // 商品详情
        $group_goods = $request->input('group_goods');
        if ( !$group_goods) {
            return jsonHelper(110, '必要的参数不能为空: group_goods');
        } else if ( !json_decode($group_goods, true)) {
            return jsonHelper(111, '传入的参数必须为 json: group_goods');
        }
        $group_goods = json_decode($group_goods, true);

        // 开启数据库事务处理
        \DB::beginTransaction();
        try {
            Group::where('id', $group_id)->update([
                'theme'              => $theme,
                'introduce'          => $introduce,
                'begin_time'         => $begin_time,
                'end_time'           => $end_time,
                'introduce_picture'  => $introduce_picture
            ]);

            if ($group_id) {
                GroupDetail::where('group_id', $group_id)->update(['group_id' => 0]);
                foreach ($group_goods as $key => $value) {
                    GroupDetail::where('id', $value['goods_id'])->update([
                        'group_id' => $group_id
                    ]);
                }
            }
            // 提交数据
            \DB::commit();

            return jsonHelper(0, '操作成功');
        } catch (\Exception $e) {

            //接收异常处理并回滚
            \DB::rollBack();

            return $e;
        }
    }

    /**
     * 单个团购详情
     *
     * @param Request $request
     * @return string
     */
    public function detail(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $group_result = Group::where('id', $group_id)->select('id', 'theme', 'introduce', 'begin_time', 'end_time', 'introduce_picture', 'create_at')->first();
        if ( !$group_result) {
            return jsonHelper(103, '传入的参数异常: id');
        } else {
            $group_result = $group_result->toArray();
        }

        $goods_info = GroupDetail::where('group_id', $group_id)->where('is_delete', 0)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num')->get();
        if ($goods_info) {
            foreach ($goods_info as $key => $value) {
                $goods_img = GroupDetailPicture::where('goods_id', $value['id'])->select('id', 'picture')->get();
                if ($goods_img) {
                    $goods_img = $goods_img->toArray();
                }
                $goods_info[$key]['picture'] = $goods_img;
            }
        }
        $group_result['goods_info'] = $goods_info;

        // 2017.5.21 修改未添加团购的商品也显示
        $no_group_goods_info = GroupDetail::where('group_id', 0)->where('is_delete', 0)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num')->get();
        if ($no_group_goods_info) {
            foreach ($no_group_goods_info as $key => $value) {
                $goods_img = GroupDetailPicture::where('goods_id', $value['id'])->select('id', 'picture')->get();
                if ($goods_img) {
                    $goods_img = $goods_img->toArray();
                }
                $no_group_goods_info[$key]['picture'] = $goods_img;
            }
        }
        $group_result['no_group_goods_info'] = $no_group_goods_info;

        return jsonHelper(0, '获取成功', $group_result);
    }

    /**
     * 拼团列表
     *
     * @return string
     */
    public function show()
    {
        $group_result = Group::where('is_delete', 0)->select('id', 'theme', 'introduce', 'begin_time', 'end_time', 'introduce_picture', 'is_top','is_putaway', 'create_at')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/community/group');
        if ( !$group_result) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        foreach ($group_result as $key => $value) {
            $goods_info = GroupDetail::where('group_id', $value['id'])->where('is_delete', 0)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num')->get();
            if ($goods_info) {
                foreach ($goods_info as $k => $v) {
                    $goods_img = GroupDetailPicture::where('goods_id', $v['id'])->select('id', 'picture')->get();
                    if ($goods_img) {
                        $goods_img = $goods_img->toArray();
                    }
                    $goods_info[$k]['picture'] = $goods_img;
                }
                $group_result[$key]['goods_info'] = $goods_info;
            }
        }

        return $group_result->toJson();
    }

    /**
     * 删除团购
     *
     * @param Request $request
     * @return string
     */
    public function delete(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = Group::find($group_id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $obj->update([
            'is_delete' => 1
        ]);
        $goods = GroupDetail::where('group_id', $group_id)->get();
        foreach ($goods as $key => $value) {
            $value->group_id = 0;
            $value->save();
        }

        return jsonHelper(0, '删除成功');
    }

    /**
     * 团购上架下架
     *
     * @param Request $request
     * @return string
     */
    public function putaway(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = Group::find($group_id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        if ($obj->is_putaway == 0) {
            $obj->update([
                'is_putaway' => 1
            ]);
            $data = 'putaway';
        } else {
            $obj->update([
                'is_putaway' => 0
            ]);
            $data = 'unputaway';
        }

        return jsonHelper(0, '修改成功', $data);
    }

    /**
     * 团购置顶与取消置顶
     *
     * @param Request $request
     *
     * @return string
     */
    public function putGroupTop(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = Group::find($group_id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        if ($obj->is_top == 0) {
            $obj->update([
                'is_top' => 1
            ]);
            $data = 'is_top';
        } else {
            $obj->update([
                'is_top' => 0
            ]);
            $data = 'un_is_top';
        }

        return jsonHelper(0, '修改成功', $data);
    }

    /**
     * 商品列表(未参与团购与未删除)
     *
     * @return string
     */
    public function goodsList()
    {
        $goods_info = GroupDetail::where('group_id', 0)->where('is_delete', 0)->select('id', 'goods_name', 'create_at')->get();

        return jsonHelper(0, '获取成功', $goods_info);
    }

    /**
     * 团购搜索
     *
     * @param Request $request
     * @return string
     */
    public function search(Request $request)
    {
        $group_result = Group::where('is_delete', 0)->where(function ($query) use ($request) {
            ($request->input('theme') !== '') && $query->where('theme', 'like', $request->input('theme') . '%');
            ($request->input('begin_time') !== '') && $query->where('begin_time', '>=', strtotime($request->input('begin_time')));
            ($request->input('end_time') !== '') && $query->where('end_time', '<=', strtotime($request->input('end_time')));
        })->select('id', 'theme', 'introduce', 'begin_time', 'end_time', 'introduce_picture', 'is_putaway', 'create_at')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/group/search');

        if ( !$group_result) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        foreach ($group_result as $key => $value) {
            $goods_info = GroupDetail::where('group_id', $value['id'])->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num')->get();
            if ($goods_info) {
                foreach ($goods_info as $k => $v) {
                    $goods_img = GroupDetailPicture::where('goods_id', $v['id'])->select('id', 'picture')->get();
                    if ($goods_img) {
                        $goods_img = $goods_img->toArray();
                    }
                    $goods_info[$k]['picture'] = $goods_img;
                }
                $group_result[$key]['goods_info'] = $goods_info;
            }
        }

        return $group_result->toJson();
    }

    /**
     *  前台单个团购详情
     *
     * @param Request $request
     * @return string
     */
    public function frontDetail(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $openid = $request->input('openid');
        if ( !$openid) {
            return jsonHelper(104, '必要的参数不能为空: openid');
        }

        $group_result = Group::where('id', $group_id)->select('id', 'theme', 'introduce', 'begin_time', 'end_time', 'introduce_picture', 'create_at')->first();
        if ( !$group_result) {
            return jsonHelper(103, '传入的参数异常: id');
        } else {
            $group_result = $group_result->toArray();
        }
        $group_result['time_reminding'] = $this->timediff(time(), $group_result['end_time']);

        $goods_info = GroupDetail::where('group_id', $group_id)->where('is_delete', 0)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num')->orderBy('create_at', 'desc')->get();
        if ($goods_info) {
            foreach ($goods_info as $key => $value) {
                $order_ids_arr = \DB::table('community_group_order')->where('group_id', $group_id)->lists('id');
                // 已购买的订单人数
                $order_goods_num = \DB::table('community_group_order_detail')->where('goods_id', $value['id'])->whereIn('order_id', $order_ids_arr)->count();
                $goods_info[$key]['order_goods_num'] = $order_goods_num;

                // 商品图片
                $goods_img = GroupDetailPicture::where('goods_id', $value['id'])->select('id', 'picture')->get();
                if ($goods_img) {
                    $goods_img = $goods_img->toArray();
                }
                $goods_info[$key]['picture'] = $goods_img;
            }
        }
        $group_result['goods_info'] = $goods_info;

        // 用户点击进来点击数加 1
        \DB::table('community_group')->where('id', $group_id)->increment('click_num');
        // 用户点击信息写入数据库
        $is_insert = GroupUserClick::where('openid', $openid)->where('group_id', $group_id)->first();
        if ( !$is_insert) {
            GroupUserClick::create([
                'openid'             => $openid,
                'group_id'           => $group_id
            ]);
        } else {
            GroupUserClick::where('id', $is_insert->id)->update([
                'update_at' => date('Y-m-d H:i:s', time())
            ]);
        }

        return jsonHelper(0, '获取成功', $group_result);
    }

    /**
     * 团购用户购买商品详情
     *
     * @param Request $request
     * @return string
     */
    public function orderPerson(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $result = GroupOrder::where('group_id', $group_id)->select('id', 'openid', 'create_at')->orderBy('create_at', 'desc')->paginate(15)->setPath(env('APP_URL') . '/public/api/v1/front/group/person');
        if ($result) {
            foreach ($result as $key => $value) {
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
                        $goods[] = $order_goods['goods_name'] . '(' . $order_goods['goods_specification'] . ') X ' . $order_goods['goods_num'] . '; ';
                    }
                }
                $result[$key]['goods_info'] = $goods;

                // 用户头像与昵称
                $user = User::where('openid', $value->openid)->select('nickname', 'avatar')->first();
                if ($user) {
                    $result[$key]['nickname'] = $user->nickname;
                    $result[$key]['avatar'] = $user->avatar;
                }
            }
        }

        return $result->toJson();
    }

    /**
     * 团购统计那两个数
     *
     * @param Request $request
     * @return string
     */
    public function orderPersonCount(Request $request)
    {
        $group_id = (int)$request->input('id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $buy_group_num = GroupOrder::where('group_id', $group_id)->count();
        $click_num = Group::where('id', $group_id)->select('click_num')->first();

        $data = [
            'buy_group_num' => $buy_group_num,
            'click_num'     => $click_num->click_num,
        ];

        return jsonHelper(0, '获取成功', $data);
    }

    /**
     * 获取团购的二维码, 分享使用
     *
     * @param Request $request
     * @return string
     */
    public function getGroupQRCode(Request $request)
    {
        // 团购 id
        $group_id = (int)$request->input('group_id');
        if ( !$group_id) {
            return jsonHelper(102, '必要的参数不能为空: group_id');
        } else {
            $group = Group::find($group_id);
            if ( !$group) {
                return jsonHelper(103, '传入的参数异常: group_id');
            }
        }

        // 团长的 openid
        $commander_openid = $request->input('openid');
        if ($commander_openid) {
            $commander = Commander::where('openid', $commander_openid)->first();
            if ($commander) {
                $commander_id = $commander->id;
            } else {
                $commander_id = null;
            }
        } else {
            return jsonHelper(104, '必要的参数不能为空: openid');
        }

        $fid = (int)$request->input('fid');
        if ( !$fid) {
            return jsonHelper(105, '必要的参数不能为空: fid');
        }

        $pay_config = PayConfig::first();
        // 获取 access_token
        $access_token = $this->getAccessToken($request, $pay_config);

        // 获取小程序码 api
        $api_url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token=' . $access_token;

        $qrcode = '/storage/images/community/group/qrcode/' . $pay_config->appid . '_' . $group_id . '.jpg';
        $filename = '../storage/images/community/group/qrcode/' . $pay_config->appid . '_' . $group_id . '.jpg';
        // 需要带着团长的 id
        // TODO...
        $data = '{"path": "pages/goods/goods?id=' . $group_id . '&commander_id=' . $commander_id . '&fid=' . $fid . '"}';

        $curl = new CurlHelper();
        $curl->postCurlFile($api_url, $data, $filename);

        $group->qrcode = $qrcode;
        $group->save();

        return jsonHelper(0, '获取成功', $qrcode);
    }

    /**
     * 获取 access_token
     *
     * @param $request
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
     * 时间相差格式化
     *
     * @param $begin_time
     * @param $end_time
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
