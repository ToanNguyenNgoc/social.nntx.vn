<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('telephone')->nullable()->after('email_verified_at');
            $table->boolean('active')->nullable()->default(true)->after('telephone');
            $table->date('birthday')->nullable()->after('active');
            $table->boolean('gender')->nullable()->default(true)->after('birthday');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('telephone');
            $table->dropColumn('active');
            $table->dropColumn('birthday');
            $table->dropColumn('gender');
        });
    }
};
