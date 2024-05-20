<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name', 255)->nullable(false);
                $table->text('description');
                $table->integer('price');
                $table->string('image', 255);
                $table->unsignedBigInteger('category_id')->nullable(false);
                $table->foreign('category_id')->references('id')->on('categories')
                    ->onUpdate('no action')
                    ->onDelete('cascade');
                $table->date('expired_at')->nullable(false);
                $table->unsignedBigInteger('modified_by')->default(1); // default to user with ID 1
                $table->foreign('modified_by')->references('id')->on('users')
                    ->onUpdate('no action')
                    ->onDelete('no action');
                $table->timestamps();
            });

            // Create Trigger
            DB::unprepared('
                CREATE TRIGGER products_insert_trigger BEFORE INSERT ON products
                FOR EACH ROW
                BEGIN
                    DECLARE user_email VARCHAR(255);
                    SELECT email INTO user_email FROM users WHERE id = NEW.modified_by;
                    SET NEW.modified_by = user_email;
                END
            ');

            DB::unprepared('
                CREATE TRIGGER products_update_trigger BEFORE UPDATE ON products
                FOR EACH ROW
                BEGIN
                    DECLARE user_email VARCHAR(255);
                    SELECT email INTO user_email FROM users WHERE id = NEW.modified_by;
                    SET NEW.modified_by = user_email;
                END
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};