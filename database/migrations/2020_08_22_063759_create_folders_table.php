<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoldersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('folders');
        Schema::create('folders', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->foreignId("user_id");
            $table->foreignUuid("parent_id")->nullable();
            $table->string("name");
            $table->softDeletes('deleted_at', 0);
            $table->foreignId("type_id")->references("id")->on("folder_types");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folders');
    }
}
