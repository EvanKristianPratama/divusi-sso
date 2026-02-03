<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel modul yang tersedia
        Schema::create('mst_modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // cobit, pmo, hr, finance
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('icon')->nullable();
            $table->string('color')->default('gray');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pivot: User punya akses ke modul mana saja
        Schema::create('trs_user_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('mst_users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('mst_modules')->cascadeOnDelete();
            $table->timestamp('granted_at')->useCurrent();
            $table->foreignId('granted_by')->nullable()->constrained('mst_users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'module_id']);
        });

        // Update users table - tambah kolom approval
        Schema::table('mst_users', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->foreignId('approved_by')->nullable()->after('approval_status')->constrained('mst_users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('avatar_url')->nullable()->after('email');
            $table->string('provider')->nullable()->after('firebase_uid'); // google, github, apple, facebook
        });
    }

    public function down(): void
    {
        Schema::table('mst_users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['approval_status', 'approved_by', 'approved_at', 'avatar_url', 'provider']);
        });

        Schema::dropIfExists('trs_user_modules');
        Schema::dropIfExists('mst_modules');
    }
};
