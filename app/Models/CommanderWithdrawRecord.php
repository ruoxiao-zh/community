<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/5/28
 * Time: 8:44 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 团长提现记录
 *
 * Class CommanderWithdrawRecord
 *
 * @package App\Http\Controllers\Community\Tables
 */
class CommanderWithdrawRecord extends Model
{
    protected $table = 'commander_withdraw_record';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}