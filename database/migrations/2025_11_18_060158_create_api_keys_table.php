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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('account_sid', 34)->index();
            $table->string('sid', 34)->unique()->index(); // SK...
            $table->string('friendly_name')->nullable();
            $table->string('secret')->nullable(); // Chỉ hiển thị khi tạo mới
            $table->string('date_created')->nullable();
            $table->string('date_updated')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
