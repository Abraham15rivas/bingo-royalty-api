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
            $table->bigInteger('cardboard_number');
            $table->bigInteger('total_collected');
            $table->bigInteger('accumulated');
            $table->bigInteger('commission');
            $table->bigInteger('reearnings_before_39');
            $table->decimal('line_play');
            $table->decimal('full_cardboard');
            $table->enum('status', ['creada', 'en progreso', 'finalizada'])->nullable();
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
