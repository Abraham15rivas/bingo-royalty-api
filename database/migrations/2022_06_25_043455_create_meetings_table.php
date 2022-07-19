<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('start')->nullable();
            $table->decimal('cardboard_number');
            $table->decimal('total_collected')->nullable();
            $table->decimal('accumulated')->nullable();
            $table->decimal('commission')->nullable();
            $table->decimal('reearnings_before_39');
            $table->decimal('reearnings_after_39');
            $table->decimal('line_play');
            $table->decimal('full_cardboard');
            $table->decimal('referred');
            $table->enum('status', ['creada', 'en progreso', 'finalizada']);
            $table->json('numbers')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meetings');
    }
}
