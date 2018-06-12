<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/5/7
 * Time: 11:35 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRecommend extends Model
{
    protected $table = 'user_recommend';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}