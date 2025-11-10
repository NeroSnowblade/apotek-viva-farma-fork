<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new column nama_obat
        Schema::table('detail_transaksis', function (Blueprint $table) {
            $table->string('nama_obat')->nullable()->after('idObat');
        });

        // Copy existing obat name into nama_obat where possible
        try {
            $rows = DB::table('detail_transaksis')->get();
            foreach ($rows as $row) {
                if (isset($row->idObat)) {
                    $name = DB::table('obats')->where('idObat', $row->idObat)->value('namaObat');
                    if ($name) {
                        DB::table('detail_transaksis')->where('id', $row->id ?? $row->idDetailTransaksi)->update(['nama_obat' => $name]);
                    }
                }
            }
        } catch (\Exception $e) {
            // best-effort; ignore if copy fails
        }

        // Drop foreign key constraint and idObat column (if possible)
        Schema::table('detail_transaksis', function (Blueprint $table) {
            // drop foreign key if exists
            try {
                $table->dropForeign(['idObat']);
            } catch (\Exception $e) {
                // ignore
            }

            try {
                $table->dropColumn('idObat');
            } catch (\Exception $e) {
                // ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_transaksis', function (Blueprint $table) {
            // add idObat back as nullable integer
            $table->unsignedBigInteger('idObat')->nullable()->after('idTransaksi');
        });

        // We can't reliably restore FK relationships; skip recreating FK.

        // Optionally remove nama_obat
        Schema::table('detail_transaksis', function (Blueprint $table) {
            $table->dropColumn('nama_obat');
        });
    }
};
