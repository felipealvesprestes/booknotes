<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'trial_starts_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('trial_starts_at')->nullable()->after('trial_ends_at');
            });
        }

        if (! Schema::hasColumn('users', 'is_lifetime')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_lifetime')->default(false)->after('trial_starts_at');
            });
        }

        $lifetimeEmails = array_unique(array_filter(array_merge(
            config('services.stripe.lifetime_emails', []),
            [
                'felipealvesprestes@gmail.com',
                'gabrielakrauzerprestes@gmail.com',
            ],
        )));

        if ($lifetimeEmails !== []) {
            DB::table('users')
                ->whereIn('email', $lifetimeEmails)
                ->update([
                    'is_lifetime' => true,
                    'trial_starts_at' => now(),
                    'trial_ends_at' => null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = [];

        if (Schema::hasColumn('users', 'trial_starts_at')) {
            $columnsToDrop[] = 'trial_starts_at';
        }

        if (Schema::hasColumn('users', 'is_lifetime')) {
            $columnsToDrop[] = 'is_lifetime';
        }

        if ($columnsToDrop !== []) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
