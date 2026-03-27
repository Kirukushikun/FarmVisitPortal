<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permit_logs', function (Blueprint $table) {
            $table->id();
 
            $table->foreignId('permit_id')->constrained('permits')->cascadeOnDelete();
 
            // Status of the permit at the time of this log entry
            // 0:Scheduled, 1:In Progress, 2:Completed, 3:Cancelled, 4:On Hold, 5:Returned
            $table->unsignedTinyInteger('status');
 
            // What action triggered this log entry
            // 0:Created, 1:Accepted, 2:Held, 3:Approved, 4:Rejected, 5:Returned,
            // 6:Resubmitted, 7:Completed, 8:Cancelled, 9:Override
            $table->unsignedTinyInteger('action');
 
            $table->foreignId('changed_by')->constrained('users');
 
            // Holds the reason, admin response, remarks, etc. depending on action
            $table->text('message')->nullable();
 
            // Red alert state at time of this action
            $table->boolean('red_alert')->default(false);
 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_logs');
    }
};
