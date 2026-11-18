<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            
            $table->string('shipment_number')->unique();
            
            // Kolom Relasi ID
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('carrier_id')->constrained('carriers');
            $table->foreignId('origin_id')->constrained('locations');
            $table->foreignId('destination_id')->constrained('locations');
            $table->foreignId('created_by')->constrained('users');
            
            // Kolom Data Kargo
            $table->string('cargo_type')->nullable();
            $table->decimal('weight_kg', 10, 2)->nullable();
            $table->dateTime('expected_departure_at')->nullable();
            $table->dateTime('expected_arrival_at')->nullable();
            $table->string('status');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};