<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/4
 * Time: 3:38 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 取货点模型
 *
 * Class CommunityDeliveryArea
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityDeliveryArea extends Model
{
    protected $table = 'community_delivery_area';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}