<?php
/**
 * Created by PhpStorm.
 * User: ellison
 * Date: 2018/6/21
 * Time: 10:46 AM
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}