<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/4
 * Time: 3:39 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户取货点
 *
 * Class CommunityUserDelivery
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityUserDelivery extends Model
{
    protected $table = 'community_user_delivery';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}