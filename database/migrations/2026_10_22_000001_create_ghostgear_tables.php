<?php

use App\Enums\UnitStatus;
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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->restrictOnDelete();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('device_type');
            $table->text('description')->nullable();
            $table->string('emf_range')->nullable();
            $table->string('thermal_temp_range_c')->nullable();
            $table->string('night_vision_resolution')->nullable();
            $table->decimal('audio_record_hours', 5, 1)->nullable();
            $table->decimal('battery_hours', 5, 1)->nullable();
            $table->string('motion_sensor_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->foreignId('equipment_id')->constrained('equipments')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->primary(['equipment_id', 'category_id']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipments')->restrictOnDelete();
            $table->string('unit_code')->unique();
            $table->string('status')->default(UnitStatus::Tersedia->value);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('returned_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->date('borrow_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();
            $table->integer('late_days')->default(0);
            $table->decimal('fine_per_day', 12, 2)->nullable();
            $table->decimal('fine_amount', 14, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->string('status');
            $table->timestamps();

            $table->unsignedBigInteger('active_unit_id')
                ->nullable()
                ->storedAs("CASE WHEN status IN ('menunggu','aktif') THEN unit_id ELSE NULL END")
                ->unique();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('setting_key')->primary();
            $table->text('setting_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
        Schema::dropIfExists('units');
        Schema::dropIfExists('equipment_categories');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('profiles');
        Schema::dropIfExists('settings');
    }
};
