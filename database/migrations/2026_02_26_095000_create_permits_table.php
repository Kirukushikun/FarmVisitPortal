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
 
            $table->foreignId('area_id')->constrained('areas');
            $table->foreignId('farm_location_id')->constrained('locations');
            $table->json('names'); // { mode: 'simple'|'detailed', value|groups: ... }
            $table->timestamp('date_of_visit');
            $table->decimal('expected_duration_hours', 5, 2)->unsigned()->nullable();
 
            $table->string('previous_farm_location')->nullable();
            $table->timestamp('date_of_visit_previous_farm')->nullable();
            $table->text('purpose')->nullable();
            $table->text('remarks')->nullable();
 
            $table->boolean('red_alert')->default(false);
            // Quick-access mirror of latest log state — kept for filtering/querying
            // 0:Scheduled, 1:In Progress, 2:Completed, 3:Cancelled, 4:On Hold, 5:Returned
            $table->unsignedTinyInteger('status')->default(0);
 
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permits');
    }
};
