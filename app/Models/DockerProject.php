<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docker_projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('domain_name');
            $table->string('php_version');
            $table->string('mysql_version');
            $table->string('node_version')->nullable();
            $table->integer('app_port');
            $table->integer('phpmyadmin_port');
            $table->string('db_username');
            $table->string('db_password');
            $table->string('db_root_password');
            $table->boolean('include_redis')->default(false);
            $table->boolean('include_mailhog')->default(false);
            $table->boolean('include_node')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docker_projects');
    }
};
