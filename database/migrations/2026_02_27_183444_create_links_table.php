<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function(Blueprint $table){
            $table->id();
            $table->timestamps();
            $table->timestamp('expires_at')->nullable()->index();
            $table->mediumtext('complete_url');
            $table->string('short_url', 10)->unique()->index();
            $table->bigInteger('click_count')->default(0);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
