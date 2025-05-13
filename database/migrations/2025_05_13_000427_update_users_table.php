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
        Schema::table('users', function (Blueprint $table) {
            $table->string("id_type")->nullable();
            $table->string("id_no")->nullable();
            $table->enum("gender", ["male", "female"])->nullable();
            $table->date("dob")->nullable();
            $table->string("address")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("id_type");
            $table->dropColumn("id_no");
            $table->dropColumn("gender");
            $table->dropColumn("dob");
            $table->dropColumn("address");
        });
    }
};
