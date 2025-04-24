<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grok_trading_log', function (Blueprint $table): void {
            $table->id();

            $table->string('action', 50);
            $table->decimal('price', 16, 8)->nullable();
            $table->bigInteger('order_id')->nullable();
            $table->string('reason', 1000);
            $table->boolean('done')->default(false);

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grok_trading_log');
    }
};
