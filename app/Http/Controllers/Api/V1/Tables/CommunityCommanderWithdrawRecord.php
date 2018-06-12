<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/5/28
 * Time: 8:44 AM
 */

namespace App\Http\Controllers\Community\Tables;

use Illuminate\Database\Eloquent\Model;

/**
 * 团长提现记录
 *
 * Class CommunityCommanderWithdrawRecord
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommunityCommanderWithdrawRecord extends Model
{
    protected $table = 'community_commander_withdraw_record';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}