<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mst_users', function (Blueprint $table) {
            $table->string('sso_token', 64)->nullable()->index();
            $table->timestamp('sso_token_expires_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mst_users', function (Blueprint $table) {
            $table->dropColumn(['sso_token', 'sso_token_expires_at']);
        });
    }
};
