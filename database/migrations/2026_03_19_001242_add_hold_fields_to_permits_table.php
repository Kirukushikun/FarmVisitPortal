<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->change(); // already exists, no change needed
            $table->text('hold_reason')->nullable()->after('remarks');
            $table->boolean('red_alert')->default(false);
            $table->timestamp('held_at')->nullable()->after('hold_reason');
            $table->foreignId('held_by')->nullable()->constrained('users')->nullOnDelete()->after('held_at');
            $table->text('admin_response')->nullable()->after('held_by');
            $table->timestamp('responded_at')->nullable()->after('admin_response');
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete()->after('responded_at');
        });
    }

    public function down(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->dropColumn(['hold_reason', 'held_at', 'held_by', 'admin_response', 'responded_at', 'responded_by']);
        });
    }
};
