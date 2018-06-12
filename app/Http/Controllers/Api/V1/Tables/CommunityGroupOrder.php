<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/31
 * Time: 8:41 AM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购订单模型
 *
 * Class CommunityGroupOrder
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityGroupOrder extends Model
{
    protected $table = 'community_group_order';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}