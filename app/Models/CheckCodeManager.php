<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/31
 * Time: 8:39 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购核销员模型
 *
 * Class CheckCodeManager
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CheckCodeManager extends Model
{
    protected $table = 'check_code_manager';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}