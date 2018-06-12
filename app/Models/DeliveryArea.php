<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/4
 * Time: 3:38 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 取货点模型
 *
 * Class DeliveryArea
 *
 * @package App\Http\Controllers\Community\Tables
 */
class DeliveryArea extends Model
{
    protected $table = 'delivery_area';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}