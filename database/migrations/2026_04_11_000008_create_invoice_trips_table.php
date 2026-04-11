<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('origin_id')->constrained('locations');
            $table->foreignId('driver_id')->constrained('drivers');
            $table->foreignId('carrier_type_id')->constrained('carrier_types');
            $table->decimal('freight_fee', 10, 2)->default(0);
            $table->string('weight')->nullable();
            $table->unsignedInteger('sequence')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_trips');
    }
};
