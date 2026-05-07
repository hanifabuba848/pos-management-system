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
        Schema::table('products', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->default('Cash')->after('total'); // Cash, Card, Transfer
            $table->decimal('amount_tendered', 10, 2)->default(0)->after('payment_method');
            $table->decimal('change_given', 10, 2)->default(0)->after('amount_tendered');
            $table->string('reference_code')->nullable()->unique()->after('id'); // Will be generated
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('tax_amount', 10, 2)->default(0)->after('price');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'discount_amount']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'amount_tendered', 'change_given', 'reference_code']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
