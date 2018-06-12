<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/31
 * Time: 8:43 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购订单详情
 *
 * Class GroupOrderDetail
 *
 * @package App\Http\Controllers\Community\Tables
 */
class GroupOrderDetail extends Model
{
    protected $table = 'group_order_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}