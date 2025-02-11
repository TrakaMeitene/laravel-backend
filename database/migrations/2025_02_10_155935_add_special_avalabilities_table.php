<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('special_availabilities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('service'); // Pakalpojums, relācija
            $table->time('from'); // Sākuma laiks
            $table->time('to'); // Beigu laiks
            $table->json('days'); // Saraksts ar nedēļas dienām JSON formātā
            $table->bigInteger('specialist');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_availabilities');
    }
};
