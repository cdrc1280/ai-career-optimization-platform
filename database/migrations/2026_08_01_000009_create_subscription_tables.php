<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Plans are data, not code — new tiers or limits ship via a seeder/
        // admin panel edit, not a deploy. This is what makes "subscription
        // system future-ready" actually true rather than aspirational.
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // free, premium, enterprise
            $table->string('name');
            $table->unsignedInteger('price_monthly_cents')->default(0);
            $table->json('limits'); // e.g. {"resume_optimizations": 3, "cover_letters": 3, "exports": 5}
            $table->json('features'); // feature flags this plan unlocks
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->enum('status', ['trialing', 'active', 'past_due', 'canceled', 'expired'])
                ->default('active');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            // Provider-agnostic: fill these in once a payment gateway is picked.
            $table->string('payment_provider')->nullable();
            $table->string('payment_provider_subscription_id')->nullable();
            $table->timestamps();
        });

        // Usage counters reset per billing period — this is what enforces
        // "Free: limited optimizations" without hardcoding numbers anywhere.
        Schema::create('usage_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('metric'); // resume_optimizations, cover_letters, exports, ai_analyses
            $table->unsignedInteger('count')->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->timestamps();

            $table->unique(['user_id', 'metric', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_counters');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
