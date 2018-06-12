<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:59 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购拼团详情
 *
 * Class CommunityGroupDetail
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityGroupDetail extends Model
{
    protected $table = 'community_group_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}