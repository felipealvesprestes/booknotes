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
        if (! Schema::hasColumn('users', 'cpf')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('cpf', 14)->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('users', 'address_street')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address_street')->nullable()->after('cpf');
            });
        }

        if (! Schema::hasColumn('users', 'address_number')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address_number', 20)->nullable()->after('address_street');
            });
        }

        if (! Schema::hasColumn('users', 'address_neighborhood')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address_neighborhood')->nullable()->after('address_number');
            });
        }

        if (! Schema::hasColumn('users', 'address_city')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address_city')->nullable()->after('address_neighborhood');
            });
        }

        if (! Schema::hasColumn('users', 'address_state')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address_state')->nullable()->after('address_city');
            });
        }

        if (! Schema::hasColumn('users', 'address_country')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('address_country')->default('Brasil')->after('address_state');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columnsToDrop = [];

        foreach ([
            'cpf',
            'address_street',
            'address_number',
            'address_neighborhood',
            'address_city',
            'address_state',
            'address_country',
        ] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if ($columnsToDrop !== []) {
            Schema::table('users', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }
};
