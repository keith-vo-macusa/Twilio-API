<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('account_sid', 34)->index();
            $table->string('api_version', 20)->default('2010-04-01');
            $table->text('body')->nullable();
            $table->string('date_created')->nullable();
            $table->string('date_sent')->nullable();
            $table->string('date_updated')->nullable();
            $table->string('direction', 20)->default('outbound-api');
            $table->integer('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->string('from', 20)->index();
            $table->string('messaging_service_sid', 34)->nullable();
            $table->integer('num_media')->default(0);
            $table->integer('num_segments')->default(1);
            $table->string('price', 20)->nullable();
            $table->string('price_unit', 3)->nullable();
            $table->string('sid', 34)->unique()->index();
            $table->string('status', 20)->default('queued');
            $table->string('to', 20)->index();
            $table->string('uri')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
