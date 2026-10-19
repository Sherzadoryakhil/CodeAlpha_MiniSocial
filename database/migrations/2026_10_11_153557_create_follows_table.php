<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('following_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            // The same follow cannot exist twice.
            $table->unique(['follower_id', 'following_id']);
        });

        // Self-follow protection. A CHECK constraint cannot be used here because
        // MySQL does not allow CHECK on columns that have ON DELETE CASCADE foreign keys,
        // so MySQL uses triggers. (Automated tests run on SQLite, which skips this part.)
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared("
                CREATE TRIGGER follows_no_self_follow_insert
                BEFORE INSERT ON follows
                FOR EACH ROW
                BEGIN
                    IF NEW.follower_id = NEW.following_id THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A user cannot follow themselves.';
                    END IF;
                END
            ");

            DB::unprepared("
                CREATE TRIGGER follows_no_self_follow_update
                BEFORE UPDATE ON follows
                FOR EACH ROW
                BEGIN
                    IF NEW.follower_id = NEW.following_id THEN
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A user cannot follow themselves.';
                    END IF;
                END
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS follows_no_self_follow_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS follows_no_self_follow_update');
        }
        Schema::dropIfExists('follows');
    }
};
