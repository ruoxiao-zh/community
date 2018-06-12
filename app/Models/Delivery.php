<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 11:37 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购配送区域模型
 *
 * Class Delivery
 *
 * @package App\Http\Controllers\Community\Tables
 */
class Delivery extends Model
{
    protected $table = 'deliver';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}