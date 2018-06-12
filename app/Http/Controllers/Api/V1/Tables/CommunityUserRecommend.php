<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/5/7
 * Time: 11:35 AM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

class CommunityUserRecommend extends Model
{
    protected $table = 'community_user_recommend';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}