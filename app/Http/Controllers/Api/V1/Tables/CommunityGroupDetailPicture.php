<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 3:01 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

class CommunityGroupDetailPicture extends Model
{
    protected $table = 'community_group_detail_picture';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}