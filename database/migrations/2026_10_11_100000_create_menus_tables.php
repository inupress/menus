<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('menus_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus_menus')->cascadeOnDelete();
            // Deleting a parent promotes its children to the top level instead of deleting them.
            $table->foreignId('parent_id')->nullable()->constrained('menus_items')->nullOnDelete();
            $table->string('label');
            // null = a custom link, otherwise the key of a registered link source ("pages").
            $table->string('source')->nullable();
            // Id inside the source. No foreign key: those tables belong to other plugins.
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('url')->nullable();
            $table->boolean('target_blank')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['menu_id', 'parent_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus_items');
        Schema::dropIfExists('menus_menus');
    }
};
