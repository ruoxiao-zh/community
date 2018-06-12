<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:04 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购客服模型
 *
 * Class CustomerService
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CustomerService extends Model
{
    protected $table = 'customer_service';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}