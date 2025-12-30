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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete(); // <--- WAJIB
            $table->string('invoice_number'); // POS-001
            $table->string('customer_name')->nullable();
            $table->string('table_number')->nullable(); // Meja berapa?
            $table->integer('total_price');
            $table->enum('payment_method', ['cash', 'qris', 'debit'])->default('cash');
            $table->enum('status', ['pending', 'cooking', 'ready', 'served', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
