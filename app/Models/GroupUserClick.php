<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/12
 * Time: 11:09 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购用户点击团购信息
 *
 * Class GroupUserClick
 *
 * @package App\Http\Controllers\Community\Tables
 */
class GroupUserClick extends Model
{
    protected $table = 'group_user_click';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}