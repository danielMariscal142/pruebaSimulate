<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLatLongPlaceIdToDirectionsTable extends Migration
{
    public function up()
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->string('label', 190)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('place_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('directions', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('place_id');
        });
    }
}

