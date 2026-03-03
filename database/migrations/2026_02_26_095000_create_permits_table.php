<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->id();

            $table->string('permit_id')->unique();

            $table->string('area');
            $table->foreignId('farm_location_id')->constrained('locations');
            $table->text('names');
            $table->timestamp('date_of_visit');
            $table->decimal('expected_duration_hours', 5, 2)->unsigned()->nullable();

            $table->foreignId('previous_farm_location_id')->nullable()->constrained('locations');
            $table->timestamp('date_of_visit_previous_farm')->nullable();
            $table->text('purpose')->nullable();

            $table->unsignedTinyInteger('status')->default(0); //0:Schedulled, 1:In Progress, 2:Completed, 3:Cancelled
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permits');
    }
};
