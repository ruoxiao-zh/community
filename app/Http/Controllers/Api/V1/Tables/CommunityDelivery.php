<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 11:37 AM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购配送区域模型
 *
 * Class CommunityDelivery
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityDelivery extends Model
{
    protected $table = 'community_deliver';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}