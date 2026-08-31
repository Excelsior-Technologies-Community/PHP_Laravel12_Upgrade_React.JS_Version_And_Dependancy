<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dependency_upgrade_histories', function (Blueprint $table) {
            $table->id();

            $table->string('package_name');
            $table->string('old_version')->nullable();
            $table->string('new_version')->nullable();

            $table->enum('status', [
                'checked',
                'upgraded',
                'failed',
            ])->default('checked');

            $table->text('message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dependency_upgrade_histories');
    }
};