<?php

/**
 * ══════════════════════════════════════════════════════════════════
 * MIGRATION CONSOLIDÉE — Toutes les tables de base de DiscovTrip
 * ══════════════════════════════════════════════════════════════════
 *
 * Remplace les fichiers 2024_01_01_000000 à 2024_01_01_000006 et
 * 2024_create_settings_and_testimonials.php
 *
 * Ordre de création sans dépendance circulaire :
 *   1. countries
 *   2. cities          → countries
 *   3. users
 *   4. offers          → cities, users
 *   5. bookings        → users, offers  (SANS payment_id)
 *   6. payments        → bookings
 *   7. reviews         → offers, users, bookings
 *   8. media           → users
 *   9. site_settings   (autonome)
 *  10. testimonials    (autonome)
 * ══════════════════════════════════════════════════════════════════
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────────────────
        // 1. COUNTRIES
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique()->nullable();
                $table->string('code', 2)->nullable();
                $table->string('continent')->nullable();
                $table->timestamps();
            });
        }

        // ──────────────────────────────────────────────────────────
        // 2. CITIES
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('set null');
                $table->string('name');
                $table->string('slug')->unique()->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('cover_image')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('display_order')->default(0);
                $table->timestamps();

                $table->index('country_id');
                $table->index('is_active');
            });
        }

        // ──────────────────────────────────────────────────────────
        // 3. USERS
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->string('avatar')->nullable();
                $table->enum('role', ['user', 'admin', 'super_admin'])->default('user');
                $table->boolean('is_active')->default(true);
                $table->boolean('two_factor_enabled')->default(false);
                $table->string('two_factor_secret')->nullable();
                $table->json('two_factor_recovery_codes')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();

                $table->index('email');
                $table->index('role');
            });
        }

        // ──────────────────────────────────────────────────────────
        // 4. OFFERS
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('offers')) {
            Schema::create('offers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('title');
                $table->string('slug')->unique()->nullable();
                $table->text('description')->nullable();
                $table->text('long_description')->nullable();
                $table->decimal('price', 10, 2);
                $table->string('currency', 3)->default('XOF');
                $table->integer('duration_hours')->nullable();
                $table->integer('max_participants')->nullable();
                $table->integer('min_participants')->default(1);
                $table->string('cover_image')->nullable();
                $table->json('gallery')->nullable();
                $table->json('included_items')->nullable();
                $table->json('excluded_items')->nullable();
                $table->json('itinerary')->nullable();
                $table->json('faqs')->nullable();
                $table->enum('difficulty_level', ['easy', 'moderate', 'challenging', 'expert'])->default('easy');
                $table->integer('min_age')->default(0);
                $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_instant_booking')->default(false);
                $table->integer('available_spots')->nullable();
                $table->enum('payment_mode', ['online', 'on_site', 'both'])->default('both');
                $table->string('guide_video_url')->nullable();
                $table->text('guide_video_description')->nullable();
                $table->text('promo_description')->nullable();
                $table->decimal('average_rating', 3, 2)->default(0);
                $table->integer('reviews_count')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['city_id', 'status']);
                $table->index('status');
                $table->index('is_featured');
            });
        }

        // ──────────────────────────────────────────────────────────
        // 5. BOOKINGS  (SANS payment_id — relation inverse via payments.booking_id)
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();

                $table->string('reference', 20)->unique();

                // Relations
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
                // Réservation
                $table->timestamp('booking_date')->nullable();
                $table->string('booking_time', 10)->nullable();
                $table->text('special_requests')->nullable();
                $table->text('customer_notes')->nullable();
                $table->text('notes')->nullable();

                // Statut
                $table->enum('status', [
                    'pending', 'confirmed', 'processing', 'completed',
                    'cancelled_by_user', 'cancelled_by_partner',
                    'cancelled_by_system', 'refunded',
                ])->default('pending');

                // Participants
                $table->integer('adults')->default(1);
                $table->integer('children')->default(0);
                $table->integer('infants')->default(0);
                $table->integer('participants')->default(1);
                $table->json('participant_details')->nullable();

                // Tarification
                $table->decimal('total_price', 10, 2);
                $table->string('currency', 3)->default('XOF');
                $table->enum('cancellation_policy', [
                    'flexible', 'moderate', 'strict', 'very_strict', 'non_refundable',
                ])->default('moderate');

                // Paiement (colonnes autonomes, pas de FK vers payments)
                $table->boolean('is_paid')->default(false);
                $table->timestamp('paid_at')->nullable();
                $table->string('payment_method')->nullable();
                $table->string('payment_status')->nullable()->default('pending');
                $table->string('payment_reference')->nullable();
                $table->string('payment_transaction_id')->nullable();

                // Annulation / remboursement
                $table->decimal('refunded_amount', 10, 2)->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->string('refund_reason')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->string('cancellation_reason')->nullable();
                $table->unsignedBigInteger('cancelled_by')->nullable();

                // Invité (sans compte)
                $table->string('guest_first_name')->nullable();
                $table->string('guest_last_name')->nullable();
                $table->string('guest_email')->nullable();
                $table->string('guest_phone')->nullable();

                // Notifications
                $table->boolean('confirmation_sent')->default(false);
                $table->boolean('reminder_sent')->default(false);
                $table->timestamp('confirmation_sent_at')->nullable();
                $table->timestamp('reminder_sent_at')->nullable();

                // Expiration
                $table->timestamp('expires_at')->nullable();

                $table->timestamps();

                $table->index('reference');
                $table->index(['user_id', 'status']);
                $table->index('booking_date');
                $table->index('status');
            });
        }

        // ──────────────────────────────────────────────────────────
        // 6. PAYMENTS  (après bookings — pas de cercle)
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id')->unique();
                $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3);
                $table->enum('gateway', ['stripe', 'kkiapay', 'paypal', 'wave']);
                $table->enum('method', ['card', 'mobile_money', 'bank_transfer', 'paypal']);
                $table->enum('status', [
                    'pending', 'succeeded', 'failed', 'refunded', 'partially_refunded',
                ])->default('pending');
                $table->string('gateway_payment_id')->nullable();
                $table->string('gateway_customer_id')->nullable();
                $table->json('gateway_metadata')->nullable();
                $table->decimal('refunded_amount', 10, 2)->nullable();
                $table->timestamp('refunded_at')->nullable();
                $table->string('refund_reason')->nullable();
                $table->timestamp('failed_at')->nullable();
                $table->string('failure_reason')->nullable();
                $table->timestamps();

                $table->index('transaction_id');
                $table->index('booking_id');
                $table->index('status');
            });
        }

        // ──────────────────────────────────────────────────────────
        // 7. REVIEWS
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->foreignId('offer_id')->constrained('offers')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
                $table->integer('rating');
                $table->text('comment');
                $table->enum('status', ['pending', 'published', 'rejected', 'spam'])->default('pending');
                $table->text('moderator_notes')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->index(['offer_id', 'status']);
                $table->unique('booking_id');
            });
        }

        // ──────────────────────────────────────────────────────────
        // 8. MEDIA
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('media')) {
            Schema::create('media', function (Blueprint $table) {
                $table->id();
                $table->string('filename');
                $table->string('path');
                $table->enum('type', ['image', 'video', 'document']);
                $table->string('mime_type')->nullable();
                $table->bigInteger('size_bytes');
                $table->integer('width')->nullable();
                $table->integer('height')->nullable();
                $table->string('alt')->nullable();
                $table->text('caption')->nullable();
                $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->index('type');
                $table->index('uploaded_by');
            });
        }

        // ──────────────────────────────────────────────────────────
        // 9. SITE SETTINGS
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('text');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // ──────────────────────────────────────────────────────────
        // 10. TESTIMONIALS
        // ──────────────────────────────────────────────────────────
        if (! Schema::hasTable('testimonials')) {
            Schema::create('testimonials', function (Blueprint $table) {
                $table->id();
                $table->string('client_name');
                $table->string('client_title')->nullable();
                $table->string('client_photo')->nullable();
                $table->text('testimonial');
                $table->integer('rating')->default(5);
                $table->string('offer_title')->nullable();
                $table->date('travel_date')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->boolean('is_published')->default(true);
                $table->integer('order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Suppression dans l'ordre inverse des dépendances
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('media');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('users');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
    }
};
