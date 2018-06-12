<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 9:47 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购公司模型
 *
 * Class Company
 *
 * @package App\Http\Controllers\Community\Tables
 */
class Company extends Model
{
    protected $table = 'company';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}