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
        Schema::table('equipments', function (Blueprint $table) {
            $table->decimal('rental_price_per_day', 12, 2)->default(0)->after('description');
            $table->string('image')->nullable()->after('rental_price_per_day');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('rental_fee', 14, 2)->default(0)->after('fine_paid');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->after('username');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->string('member_code')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('member_code');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('rental_fee');
        });

        Schema::table('equipments', function (Blueprint $table) {
            $table->dropColumn(['rental_price_per_day', 'image']);
        });
    }
};
