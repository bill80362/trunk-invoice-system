<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('invoice_number')->nullable();
            $table->string('issuer_name')->nullable();
            $table->string('issuer_address')->nullable();
            $table->string('issuer_phone')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['client_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
