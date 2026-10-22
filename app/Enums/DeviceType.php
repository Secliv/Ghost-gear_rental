<?php

namespace App\Enums;

enum DeviceType: string
{
    case EmfMeter = 'emf_meter';
    case ThermalCamera = 'thermal_camera';
    case NightVisionCamera = 'night_vision_camera';
    case EvpRecorder = 'evp_recorder';
    case MotionSensor = 'motion_sensor';

    public function label(): string
    {
        return match ($this) {
            self::EmfMeter => 'EMF Meter',
            self::ThermalCamera => 'Kamera Termal',
            self::NightVisionCamera => 'Kamera Night Vision',
            self::EvpRecorder => 'EVP Recorder',
            self::MotionSensor => 'Sensor Gerak',
        };
    }
}
