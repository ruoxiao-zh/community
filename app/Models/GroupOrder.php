<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/31
 * Time: 8:41 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购订单模型
 *
 * Class GroupOrder
 *
 * @package App\Http\Controllers\Community\Tables
 */
class GroupOrder extends Model
{
    protected $table = 'group_order';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}