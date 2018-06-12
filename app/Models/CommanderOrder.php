<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/2
 * Time: 11:58 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购团长提成订单
 *
 * Class CommanderOrder
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommanderOrder extends Model
{
    protected $table = 'commander_order';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}