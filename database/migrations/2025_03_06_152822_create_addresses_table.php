<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('postcode-nl.table_name'), function (Blueprint $table) {
            $table->id();
            $table->string('street');
            $table->string('street_nen')->nullable();
            $table->string('house_number');
            $table->string('house_number_addition')->nullable();
            $table->string('postcode');
            $table->string('city');
            $table->string('city_short')->nullable();
            $table->string('municipality')->nullable();
            $table->string('municipality_short')->nullable();
            $table->string('province');
            $table->integer('rd_x')->nullable();
            $table->integer('rd_y')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('bag_number_designation_id')->nullable();
            $table->string('bag_addressable_object_id')->nullable();
            $table->string('address_type')->nullable();
            $table->json('purposes')->nullable();
            $table->integer('surface_area')->nullable();
            $table->json('house_number_additions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
