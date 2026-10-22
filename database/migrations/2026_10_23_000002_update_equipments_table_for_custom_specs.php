<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add `deskripsi` column if it does not exist
        if (! Schema::hasColumn('equipments', 'deskripsi')) {
            Schema::table('equipments', function (Blueprint $table) {
                $table->longText('deskripsi')->nullable()->after('device_type');
            });
        }

        // 2. Migrate existing data from old columns into JSON in `deskripsi`
        $equipments = DB::table('equipments')->get();
        foreach ($equipments as $eq) {
            if (empty($eq->deskripsi)) {
                $specs = [];
                if (! empty($eq->thermal_temp_range_c ?? null)) {
                    $specs[] = ['title' => 'Rentang suhu', 'value' => $eq->thermal_temp_range_c];
                }
                if (! empty($eq->night_vision_resolution ?? null)) {
                    $specs[] = ['title' => 'Resolusi kamera', 'value' => $eq->night_vision_resolution];
                }
                if (! empty($eq->emf_range ?? null)) {
                    $specs[] = ['title' => 'Rentang pengukuran EMF', 'value' => $eq->emf_range];
                }
                if (! empty($eq->audio_record_hours ?? null)) {
                    $specs[] = ['title' => 'Durasi rekam audio', 'value' => $eq->audio_record_hours.' jam'];
                }
                if (! empty($eq->motion_sensor_type ?? null)) {
                    $specs[] = ['title' => 'Jenis sensor gerak', 'value' => $eq->motion_sensor_type];
                }
                if (! empty($eq->battery_hours ?? null)) {
                    $specs[] = ['title' => 'Daya tahan baterai', 'value' => $eq->battery_hours.' jam'];
                }
                if (! empty($eq->description ?? null) && empty($specs)) {
                    $specs[] = ['title' => 'Deskripsi', 'value' => $eq->description];
                }

                if (! empty($specs)) {
                    DB::table('equipments')->where('id', $eq->id)->update([
                        'deskripsi' => json_encode($specs, JSON_UNESCAPED_UNICODE),
                    ]);
                }
            }
        }

        // 3. Drop legacy rigid spec columns
        $columnsToDrop = array_values(array_intersect(
            ['emf_range', 'thermal_temp_range_c', 'night_vision_resolution', 'audio_record_hours', 'battery_hours', 'motion_sensor_type', 'description'],
            Schema::getColumnListing('equipments')
        ));

        if (! empty($columnsToDrop)) {
            Schema::table('equipments', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->text('description')->nullable()->after('device_type');
            $table->string('emf_range')->nullable();
            $table->string('thermal_temp_range_c')->nullable();
            $table->string('night_vision_resolution')->nullable();
            $table->decimal('audio_record_hours', 5, 1)->nullable();
            $table->decimal('battery_hours', 5, 1)->nullable();
            $table->string('motion_sensor_type')->nullable();
            $table->dropColumn('deskripsi');
        });
    }
};
