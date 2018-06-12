<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 11:07 AM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购支付配置
 *
 * Class CommunityPayConfig
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityPayConfig extends Model
{
    protected $table = 'community_pay';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}