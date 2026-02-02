<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trs_sso_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('mst_users')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('app', 50)->index();
            $table->string('callback_url');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trs_sso_tokens');
    }
};
