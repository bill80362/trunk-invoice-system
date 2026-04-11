<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_trip_id')->constrained('invoice_trips')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations');
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_trip_stops');
    }
};
