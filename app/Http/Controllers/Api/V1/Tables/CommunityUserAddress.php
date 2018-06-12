<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/21
 * Time: 1:58 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

class CommunityUserAddress extends Model
{
    protected $table = 'community_user_address';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}