<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->enum('product_type', ['service', 'account']);
            $table->unsignedBigInteger('product_id');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('game_account_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->json('customer_details')->nullable();
            $table->json('processing_details')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
