<?php

namespace UserFrosting\Tests\Integration\Migrations;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\System\Bakery\Migration;

class DeprecatedClassTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->schema->create('deprecated_table', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $this->schema->dropIfExists('deprecated_table');
    }

    /**
     * Seed the database.
     */
    public function seed()
    {
        $this->schema->table('deprecated_table', function (Blueprint $table) {
            $table->string('foo')->nullable();
        });
    }
}
