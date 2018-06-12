<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:59 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购拼团详情
 *
 * Class GroupDetail
 *
 * @package App\Http\Controllers\Community\Tables
 */
class GroupDetail extends Model
{
    protected $table = 'community_group_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}