<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/4/2
 * Time: 11:56 AM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购团长
 *
 * Class CommunityCommander
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityCommander extends Model
{
    protected $table = 'community_commander';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}