<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:58 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购拼团模型
 *
 * Class CommunityGroup
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityGroup extends Model
{
    protected $table = 'community_group';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}