<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/31
 * Time: 10:48 AM
 */

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Common\SaveImage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
// 数据库模型
use App\Models\GroupDetail;
use App\Models\GroupDetailPicture;

class GoodsController extends Controller
{
    /**
     * 上传商品图片
     *
     * @param Request $request
     *
     * @return string
     */
    public function uploadGoodsImg(Request $request)
    {
        $logo = SaveImage::getSaveImageUrl('images/community/group', 'picture', '', false);
        if ( !$logo) {
            return jsonHelper(101, '必要的参数不能为空: picture');
        } else {
            return jsonHelper(0, '上传成功', $logo);
        }
    }

    /**
     * 添加商品
     *
     * @param Request $request
     *
     * @return string
     */
    public function create(Request $request)
    {
        $goods_name = $request->input('goods_name');
        if ( !$goods_name) {
            return jsonHelper(102, '必要的参数不能为空: goods_name');
        }

        $goods_specification = $request->input('goods_specification');
        if ( !$goods_specification) {
            return jsonHelper(103, '必要的参数不能为空: goods_specification');
        }

        $goods_price = $request->input('goods_price');
        if ( !$goods_price) {
            return jsonHelper(104, '必要的参数不能为空: goods_price');
        }

        $goods_num = (int)$request->input('goods_num');
        if ( !$goods_num) {
            return jsonHelper(105, '必要的参数不能为空: goods_num');
        }

        $goods_img = $request->input('goods_img');
        if ( !$goods_img) {
            return jsonHelper(106, '必要的参数不能为空: goods_img');
        } else {
            if ( !json_decode($goods_img, true)) {
                return jsonHelper(107, '传入的参数必须为 json: goods_img');
            }
        }
        $goods_img = json_decode($goods_img, true);

        // 6.2 新增单个商品提成率
        $royalty_rate = (int)$request->input('royalty_rate');
        if ( !$royalty_rate) {
            return jsonHelper(108, '必要的参数不能为空: royalty_rate');
        } else if ($royalty_rate <= 0 || $royalty_rate > 100) {
            return jsonHelper(109, '提成率必须为 0 ~ 100 的整数');
        }

        // 开启数据库事务处理
        \DB::beginTransaction();
        try {
            $insert_goods_id = DB::table('community_group_detail')->insertGetId([
                'goods_name'          => $goods_name,
                'goods_specification' => $goods_specification,
                'goods_price'         => $goods_price,
                'goods_num'           => $goods_num,
                'royalty_rate'        => $royalty_rate,
            ]);

            if ($insert_goods_id) {
                foreach ($goods_img as $key => $value) {
                    GroupDetailPicture::create([
                        'picture'            => $value['picture'],
                        'goods_id'           => $insert_goods_id,
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
     * 商品修改
     *
     * @param Request $request
     *
     * @return string
     */
    public function update(Request $request)
    {
        $id = (int)$request->input('id');
        if ( !$id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = GroupDetail::find($id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $goods_name = $request->input('goods_name');
        if ( !$goods_name) {
            return jsonHelper(105, '必要的参数不能为空: goods_name');
        }

        $goods_specification = $request->input('goods_specification');
        if ( !$goods_specification) {
            return jsonHelper(106, '必要的参数不能为空: goods_specification');
        }

        $goods_price = $request->input('goods_price');
        if ( !$goods_price) {
            return jsonHelper(107, '必要的参数不能为空: goods_price');
        }

        $goods_num = (int)$request->input('goods_num');
        if ( !$goods_num) {
            return jsonHelper(108, '必要的参数不能为空: goods_num');
        }

        // 6.2 新增单个商品提成率
        $royalty_rate = (int)$request->input('royalty_rate');
        if ( !$royalty_rate) {
            return jsonHelper(109, '必要的参数不能为空: royalty_rate');
        } else if ($royalty_rate <= 0 || $royalty_rate > 100) {
            return jsonHelper(110, '提成率必须为 0 ~ 100 的整数');
        }

        $goods_img = $request->input('goods_img');
        if ( !$goods_img) {
            return jsonHelper(111, '必要的参数不能为空: goods_img');
        } else {
            if ( !json_decode($goods_img, true)) {
                return jsonHelper(112, '传入的参数必须为 json: goods_img');
            }
        }
        $goods_img = json_decode($goods_img, true);

        // 开启数据库事务处理
        \DB::beginTransaction();
        try {
            GroupDetail::where('id', $id)->update([
                'goods_name'          => $goods_name,
                'goods_specification' => $goods_specification,
                'goods_price'         => $goods_price,
                'goods_num'           => $goods_num,
                'royalty_rate'        => $royalty_rate,
            ]);

            GroupDetailPicture::where('goods_id', $id)->delete();
            foreach ($goods_img as $key => $value) {
                GroupDetailPicture::create([
                    'picture'            => $value['picture'],
                    'goods_id'           => $id,
                ]);
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
     * 商品列表
     *
     * @param Request $request
     *
     * @return string
     */
    public function index(Request $request)
    {
        $result = GroupDetail::where('is_delete', 0)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num', 'royalty_rate', 'create_at')->paginate(15)->setPath('https://www.ailetugo.com/ailetutourism/public/community/goods');
        if ($result) {
            foreach ($result as $key => $value) {
                $goods_img = GroupDetailPicture::where('goods_id', $value->id)->select('id', 'picture')->get();
                if ($goods_img) {
                    $result[$key]['goods_img'] = $goods_img;
                }
            }
        }

        return $result->toJson();
    }

    /**
     * 单个商品详情
     *
     * @param Request $request
     *
     * @return string
     */
    public function show(Request $request)
    {
        $goods_id = (int)$request->input('id');
        if ( !$goods_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = GroupDetail::where('id', $goods_id)->select('id', 'goods_name', 'goods_specification', 'goods_price', 'goods_num', 'royalty_rate', 'create_at')->first();
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $goods_img = GroupDetailPicture::where('goods_id', $goods_id)->select('id', 'picture')->get();
        if ($goods_img) {
            $obj['goods_img'] = $goods_img;
        }

        return jsonHelper(0, '获取成功', $obj);
    }

    /**
     * 删除商品
     *
     * @param Request $request
     *
     * @return string
     */
    public function delete(Request $request)
    {
        $goods_id = (int)$request->input('id');
        if ( !$goods_id) {
            return jsonHelper(102, '必要的参数不能为空: id');
        }

        $obj = GroupDetail::find($goods_id);
        if ( !$obj) {
            return jsonHelper(103, '传入的参数异常: id');
        }

        $obj->update([
            'is_delete' => 1
        ]);

        return jsonHelper(0, '删除成功');
    }
}
