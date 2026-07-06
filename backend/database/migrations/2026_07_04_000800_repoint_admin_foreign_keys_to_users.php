<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->repointNullableForeignKey('resource_documents', 'uploaded_by_admin_id', 'admins', 'users');
        $this->repointNullableForeignKey('announcements', 'created_by_admin_id', 'admins', 'users');
        $this->repointNullableForeignKey('publications', 'uploaded_by_admin_id', 'admins', 'users');

        Schema::dropIfExists('admins');
    }

    public function down(): void
    {
        // The application now uses role-based users. Recreating the old admins table is intentionally unsupported.
    }

    private function repointNullableForeignKey(string $table, string $column, string $fromTable, string $toTable): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        foreach ($this->foreignKeys($table, $column, $fromTable) as $constraint) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        }

        if ($this->foreignKeys($table, $column, $toTable) !== []) {
            return;
        }

        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_{$column}_users_foreign` " .
            "FOREIGN KEY (`{$column}`) REFERENCES `{$toTable}` (`id`) ON DELETE SET NULL"
        );
    }

    /**
     * @return list<string>
     */
    private function foreignKeys(string $table, string $column, string $referencedTable): array
    {
        return array_map(
            fn ($row) => $row->CONSTRAINT_NAME,
            DB::select(
                'SELECT CONSTRAINT_NAME
                 FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?
                   AND REFERENCED_TABLE_NAME = ?',
                [$table, $column, $referencedTable],
            ),
        );
    }
};
