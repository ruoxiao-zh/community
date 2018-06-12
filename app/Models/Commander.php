<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/2
 * Time: 11:56 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购团长
 *
 * Class Commander
 *
 * @package App\Http\Controllers\Community\Tables
 */
class Commander extends Model
{
    protected $table = 'commander';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}