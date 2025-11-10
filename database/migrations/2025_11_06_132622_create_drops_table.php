<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
{
    Schema::create('drops', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // the creator
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('location')->nullable();
        $table->unsignedBigInteger('category_id')->nullable();
        $table->json('media')->nullable(); // store multiple image URLs
        $table->string('campaign_type')->nullable(); // e.g., limited, promo
        $table->timestamps();
    });
}
    public function down()
{
    Schema::dropIfExists('drops');
}
};
