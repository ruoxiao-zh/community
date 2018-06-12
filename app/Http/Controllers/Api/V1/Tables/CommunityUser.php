<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:42 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购用户
 *
 * Class CommunityUser
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityUser extends Model
{
    protected $table = 'community_user';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}