<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extensions', function (Blueprint $table) {

            $table->increments('id');
            $table->string('context');
            $table->string('exten')->index();
            $table->string('priority');
            $table->string('app');
            $table->string('appdata');
            $table->string('company_id')->nullable()->index();
            $table->string('pbx_scheme_id')->nullable()->index();
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
        Schema::drop('extensions');
    }
}
