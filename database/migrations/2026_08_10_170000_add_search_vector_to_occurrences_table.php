<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Busca global (Fase 4): coluna gerada (GENERATED ALWAYS AS ... STORED) mantida
 * automaticamente pelo Postgres a cada insert/update — não precisa de trigger nem de
 * código PHP para manter em sincronia. protocol/title têm peso maior (A) que
 * name (B) e description (C).
 */
class AddSearchVectorToOccurrencesTable extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE occurrences ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                setweight(to_tsvector('portuguese', coalesce(protocol, '')), 'A') ||
                setweight(to_tsvector('portuguese', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('portuguese', coalesce(name, '')), 'B') ||
                setweight(to_tsvector('portuguese', coalesce(description, '')), 'C')
            ) STORED
        SQL);

        DB::statement('CREATE INDEX occurrences_search_vector_idx ON occurrences USING GIN (search_vector)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS occurrences_search_vector_idx');
        DB::statement('ALTER TABLE occurrences DROP COLUMN IF EXISTS search_vector');
    }
}
