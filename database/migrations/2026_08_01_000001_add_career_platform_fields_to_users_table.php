<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role-based access: guest is implicit (unauthenticated), so we only
            // need to persist the roles that exist once a user is authenticated.
            $table->enum('role', ['user', 'premium_user', 'admin', 'super_admin'])
                ->default('user')
                ->after('password');

            // OAuth (Google / GitHub / Microsoft). Nullable because email/password
            // users won't have these.
            $table->string('oauth_provider')->nullable()->after('role');
            $table->string('oauth_id')->nullable()->after('oauth_provider');

            $table->timestamp('last_login_at')->nullable()->after('oauth_id');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'oauth_provider', 'oauth_id', 'last_login_at']);
            $table->dropSoftDeletes();
        });
    }
};
