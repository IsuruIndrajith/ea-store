<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'sale_price_cents')) {
                $table->unsignedInteger('sale_price_cents')->default(0);
            }
            if (! Schema::hasColumn('products', 'market')) {
                $table->string('market')->default('');
            }
            if (! Schema::hasColumn('products', 'growth')) {
                $table->decimal('growth', 8, 2)->default(0);
            }
            if (! Schema::hasColumn('products', 'drawdown')) {
                $table->decimal('drawdown', 8, 2)->default(0);
            }
            if (! Schema::hasColumn('products', 'rating')) {
                $table->decimal('rating', 3, 2)->default(0);
            }
            if (! Schema::hasColumn('products', 'reviews')) {
                $table->unsignedInteger('reviews')->default(0);
            }
            if (! Schema::hasColumn('products', 'hue')) {
                $table->unsignedSmallInteger('hue')->default(0);
            }
            if (! Schema::hasColumn('products', 'features')) {
                $table->json('features')->nullable();
            }
        });

        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('category_product')) {
            Schema::create('category_product', function (Blueprint $table) {
                $table->foreignId('category_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->primary(['category_id', 'product_id']);
            });
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('order_items', 'quantity')) {
                $table->unsignedSmallInteger('quantity')->default(1);
            }
            if (! Schema::hasColumn('order_items', 'product_file_path')) {
                $table->string('product_file_path')->nullable();
            }
        });

        // Drop the foreign key (name varies by MySQL version — look it up dynamically),
        // then drop the unique index, then restore the FK without the unique constraint.
        $fkName = collect(Schema::getForeignKeys('licenses'))
            ->first(fn ($fk) => in_array('order_item_id', $fk['columns']))['name'] ?? null;

        if ($fkName) {
            Schema::table('licenses', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        if (collect(Schema::getIndexes('licenses'))->first(fn ($i) => in_array('order_item_id', $i['columns']) && $i['unique'])) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->dropUnique(['order_item_id']);
            });
        }

        Schema::table('licenses', function (Blueprint $table) {
            $table->foreign('order_item_id')
                ->references('id')
                ->on('order_items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $fkName = collect(Schema::getForeignKeys('licenses'))
            ->first(fn ($fk) => in_array('order_item_id', $fk['columns']))['name'] ?? null;

        if ($fkName) {
            Schema::table('licenses', function (Blueprint $table) use ($fkName) {
                $table->dropForeign($fkName);
            });
        }

        Schema::table('licenses', function (Blueprint $table) {
            $table->unique('order_item_id');
        });
        Schema::table('licenses', function (Blueprint $table) {
            $table->foreign('order_item_id')
                ->references('id')
                ->on('order_items')
                ->cascadeOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'product_file_path']);
        });

        Schema::dropIfExists('category_product');
        Schema::dropIfExists('categories');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'sale_price_cents',
                'market',
                'growth',
                'drawdown',
                'rating',
                'reviews',
                'hue',
                'features',
            ]);
        });
    }
};
