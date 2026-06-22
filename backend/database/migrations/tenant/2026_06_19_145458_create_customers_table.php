<?php

use App\Enums\PersonType;
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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->index();
            $table->string('document', 14)->index();
            $table->string('email', 60)->index();
            $table->enum('type', [PersonType::INDIVIDUAL, PersonType::COMPANY])->default(PersonType::INDIVIDUAL);
            $table->date('birth_date')->nullable();
            $table->string('phone', 11)->index();
            $table->string('secondary_phone', 11)->index()->nullable();
            $table->string('zip_code', 8);
            $table->string('street', 100);
            $table->string('number', 6)->nullable();
            $table->string('complement', 200)->nullable();
            $table->string('district', 60);
            $table->string('city', 75);
            $table->string('state', 75);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
