<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/4
 * Time: 3:39 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 用户取货点
 *
 * Class UserDelivery
 *
 * @package App\Http\Controllers\Community\Tables
 */
class UserDelivery extends Model
{
    protected $table = 'user_delivery';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}