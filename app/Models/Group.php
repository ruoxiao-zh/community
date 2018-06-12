<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:58 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购拼团模型
 *
 * Class Group
 *
 * @package App\Http\Controllers\Community\Tables
 */
class Group extends Model
{
    protected $table = 'group';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}