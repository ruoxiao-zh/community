<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 11:07 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购支付配置
 *
 * Class PayConfig
 *
 * @package App\Http\Controllers\Community\Tables
 */
class PayConfig extends Model
{
    protected $table = 'pay';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}