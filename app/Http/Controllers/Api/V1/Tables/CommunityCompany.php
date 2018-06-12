<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 30/03/2018
 * Time: 9:47 AM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购公司模型
 *
 * Class CommunityCompany
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityCompany extends Model
{
    protected $table = 'community_company';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}