<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trading', function (Blueprint $table): void {
            $table->id();

            $table->boolean('active')->default(false);
            $table->tinyInteger('stock');
            $table->tinyInteger('strategy');
            $table->decimal('different', 4, 2);
            $table->decimal('skip_sum', 16, 8);
            $table->decimal('max_trade', 16, 8);
            $table->string('pair');
            $table->text('description')->nullable();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading');
    }
};
