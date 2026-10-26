<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 100);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->string('num_phone', 12)->nullable();
            $table->enum('role', ['customer', 'admin'])->default('customer');
            $table->enum('status', ['active', 'suspended', 'disabled'])->default('active');
            $table->string('remember_token')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('name', 100);
            $table->string('slug', 150)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('category_id');
            $table->string('name', 150);
            $table->string('slug', 190)->unique();
            $table->longText('description')->nullable();
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
        });

        Schema::create('product_image', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('image_path', 255);
            $table->string('alt_text', 255)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');
            $table->string('sku', 100)->unique();
            $table->string('variant_name', 100);
            $table->string('size', 30)->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedInteger('price');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('weight_gram')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('label', 50)->nullable();
            $table->string('recipient_name', 100);
            $table->string('num_phone', 20);
            $table->string('province', 100);
            $table->string('city', 100);
            $table->string('district', 100);
            $table->string('subdistrict', 100)->nullable();
            $table->string('postal_code', 100)->nullable();
            $table->text('address_line');
            $table->string('destinstion_provider', 100)->nullable();
            $table->string('destination_code', 50)->nullable();
            $table->boolean('is_default')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->unique();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cart_id');
            $table->unsignedInteger('product_variant_id');
            $table->unsignedSmallInteger('quantity');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['cart_id', 'product_variant_id']);
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->restrictOnDelete();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('source_address_id')->nullable();
            $table->string('recipient_name', 100);
            $table->string('recipient_phone', 20);
            $table->string('province', 100);
            $table->string('city', 100);
            $table->string('distric', 100);
            $table->string('subdistric', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->text('address_line');
            $table->string('destination_provider', 50)->nullable();
            $table->string('destination_code', 100)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->foreign('source_address_id')->references('id')->on('addresses')->nullOnDelete();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_number', 30)->unique();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('order_address_id')->unique();
            $table->enum('status', ['pending', 'processing', 'shipped', 'completed', 'cancelled'])->default('pending');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('discount_amount')->default(0);
            $table->unsignedInteger('shipping_cost')->default(0);
            $table->unsignedInteger('grand_total');
            $table->text('customer_note')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('order_address_id')->references('id')->on('order_addresses')->restrictOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('product_variant_id')->nullable();
            $table->string('product_name', 150);
            $table->string('variant_name', 100);
            $table->string('sku', 100);
            $table->string('size', 30)->nullable();
            $table->string('color', 50)->nullable();
            $table->unsignedInteger('unit_price');
            $table->unsignedSmallInteger('quantity');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('weight_gram');
            $table->string('image_path', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->nullOnDelete();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id');
            $table->string('provider', 50);
            $table->string('provider_order_id', 100)->unique();
            $table->string('provider_transaction_id', 100)->nullable()->unique();
            $table->string('payment_method', 50);
            $table->unsignedInteger('amount');
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->text('checkout_url')->nullable();
            $table->string('payment_code', 255)->nullable();
            $table->json('payment_details')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->restrictOnDelete();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('order_id')->nullable()->unique();
            $table->string('shipping_provider', 50)->nullable();
            $table->string('provider_shipment_id', 100)->nullable();
            $table->string('courier_code', 50);
            $table->string('courier_name', 100);
            $table->string('service_code', 50)->nullable();
            $table->string('tracking_number', 100)->nullable();
            $table->string('shipping_label_path', 255)->nullable();
            $table->enum('status', ['pending', 'ready to ship', 'shipped', 'in transit', 'delivered', 'failed', 'returned', 'cancelled'])->default('pending');
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_image');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};
