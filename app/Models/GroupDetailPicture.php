<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/3/30
 * Time: 3:01 PM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupDetailPicture extends Model
{
    protected $table = 'group_detail_picture';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}