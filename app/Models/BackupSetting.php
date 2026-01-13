<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $fillable = ['enabled','frequency','weekly_day','time_hm','backup_path'];
}
