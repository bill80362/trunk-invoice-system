<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freight_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_id')->constrained('locations');
            $table->foreignId('destination_id')->constrained('locations');
            $table->foreignId('carrier_type_id')->constrained('carrier_types');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['origin_id', 'destination_id', 'carrier_type_id'], 'freight_rates_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freight_rates');
    }
};
