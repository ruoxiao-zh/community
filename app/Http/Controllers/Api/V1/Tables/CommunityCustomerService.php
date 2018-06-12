<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 2:04 PM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 社区团购客服模型
 *
 * Class CommunityCustomerService
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityCustomerService extends Model
{
    protected $table = 'community_customer_service';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}