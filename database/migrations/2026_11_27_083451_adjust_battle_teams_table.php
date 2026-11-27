<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $ownerId = DB::table('users')->min('id');

        // Data lama harus punya pemilik
        if (! $ownerId && DB::table('battle_teams')->exists()) {
            throw new RuntimeException('Register minimal 1 akun dulu, lalu jalankan migrate lagi.');
        }

        if (! Schema::hasColumn('battle_teams', 'user_id')) {
            Schema::table('battle_teams', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

        // nama_tim -> name
        if (Schema::hasColumn('battle_teams', 'nama_tim')) {
            if (Schema::hasIndex('battle_teams', 'battle_teams_nama_tim_unique')) {
                Schema::table('battle_teams', function (Blueprint $table) {
                    $table->dropUnique('battle_teams_nama_tim_unique');
                });
            }

            Schema::table('battle_teams', function (Blueprint $table) {
                $table->renameColumn('nama_tim', 'name');
            });
        }

        // Tim lama diberikan ke akun pertama
        DB::table('battle_teams')->whereNull('user_id')->update(['user_id' => $ownerId]);

        Schema::table('battle_teams', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        if (! collect(Schema::getForeignKeys('battle_teams'))->contains(fn ($fk) => $fk['columns'] === ['user_id'])) {
            Schema::table('battle_teams', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // Nama tim cukup unik per user
        if (! Schema::hasIndex('battle_teams', ['user_id', 'name'], 'unique')) {
            Schema::table('battle_teams', function (Blueprint $table) {
                $table->unique(['user_id', 'name']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('battle_teams', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'name']);
            $table->dropColumn('user_id');
        });

        Schema::table('battle_teams', function (Blueprint $table) {
            $table->renameColumn('name', 'nama_tim');
        });
    }
};
