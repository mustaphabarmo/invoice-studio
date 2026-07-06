<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') && Schema::hasTable('members')) {
            Schema::rename('members', 'users');
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('member')->index()->after('password');
            });
        }

        if (Schema::hasTable('users')) {
            DB::table('users')->whereNull('role')->orWhere('role', '')->update(['role' => 'member']);
        }

        if (Schema::hasTable('admins') && Schema::hasTable('users')) {
            $admins = DB::table('admins')->get();

            foreach ($admins as $admin) {
                if (DB::table('users')->where('email', $admin->email)->exists()) {
                    DB::table('users')
                        ->where('email', $admin->email)
                        ->update([
                            'role' => $admin->role === 'super_admin' ? 'super_admin' : 'admin',
                            'status' => $admin->status ?? 'active',
                            'updated_at' => now(),
                        ]);

                    continue;
                }

                DB::table('users')->insert([
                    'first_name' => $admin->first_name,
                    'last_name' => $admin->last_name,
                    'email' => $admin->email,
                    'phone_number' => $admin->phone_number,
                    'password' => $admin->password,
                    'role' => $admin->role === 'super_admin' ? 'super_admin' : 'admin',
                    'status' => $admin->status ?? 'active',
                    'created_at' => $admin->created_at,
                    'updated_at' => $admin->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['role']);
                $table->dropColumn('role');
            });
        }
    }
};
