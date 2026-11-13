<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('short_description')->nullable()->after('meta_description');
            $table->string('vendor_url')->nullable()->after('short_description');
            $table->string('myfxbook_link')->nullable()->after('vendor_url');
            $table->json('extended')->nullable()->after('myfxbook_link');
            $table->json('set_review_data')->nullable()->after('extended');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title', 'meta_description', 'short_description',
                'vendor_url', 'myfxbook_link', 'extended', 'set_review_data',
            ]);
        });
    }
};
