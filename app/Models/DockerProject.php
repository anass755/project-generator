<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DockerProject extends Model
{


    protected $fillable = [
        'project_name',
        'domain_name',
        'php_version',
        'mysql_version',
        'node_version',
        'app_port',
        'phpmyadmin_port',
        'db_username',
        'db_password',
        'db_root_password',
        'include_redis',
        'include_mailhog',
        'include_node',
        'status',
    ];

    protected $casts = [
        'include_redis' => 'boolean',
        'include_mailhog' => 'boolean',
        'include_node' => 'boolean',
        'app_port' => 'integer',
        'phpmyadmin_port' => 'integer',
        'status' => 'integer',
    ];
}
