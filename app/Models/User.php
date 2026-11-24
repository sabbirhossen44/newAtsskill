<?php

namespace App\Models;

use App\Enums\UserBannedEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'is_banned', 'verification_token', 'forget_password_token', 'phone', 'address', 'media_id', 'image', 'cover', 'bio', 'short_bio', 'job_title', 'gender', 'age', 'country_id', 'state', 'city', 'facebook', 'twitter', 'linkedin', 'website', 'github'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => App\Enums\UserRoleEnum::class,
            'status' => App\Enums\UserStatusEnum::class,
            'is_banned' => App\Enums\UserBannedEnum::class,
            'wallet_balance' => 'decimal:2',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatusEnum::ACTIVE->value);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', UserStatusEnum::DEACTIVE->value);
    }

    public function scopeBanned(Builder $query): Builder
    {
        return $query->where('is_banned', UserBannedEnum::YES->value);
    }

    public function scopeUnbanned(Builder $query): Builder
    {
        return $query->where('is_banned', UserBannedEnum::NO->value);
    }

    public function scopeInstructors(Builder $query): Builder
    {
        return $query->where('role', UserRoleEnum::INSTRUCTOR->value);
    }

    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', UserRoleEnum::STUDENT->value);
    }

    public function isInstructor(): bool
    {
        return $this->role === UserRoleEnum::INSTRUCTOR;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRoleEnum::STUDENT;
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function instructorInfo(): HasOne
    {
        return $this->hasOne(InstructorRequest::class, 'user_id', 'id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class, 'user_id');
    }

    public function education(): HasMany
    {
        return $this->hasMany(UserEducation::class, 'user_id');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(UserExperience::class, 'user_id');
    }

    public function skillTopics(): HasMany
    {
        return $this->hasMany(UserSkillTopic::class, 'user_id');
    }

    public function bannedHistories(): HasMany
    {
        return $this->hasMany(BannedHistory::class, 'user_id');
    }

    public function socialite(): HasMany
    {
        return $this->hasMany(SocialiteCredential::class, 'user_id');
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'user_id');
    }

    public function courseProgress(): HasMany
    {
        return $this->hasMany(CourseProgress::class, 'user_id');
    }

    public function zoomCredential(): HasOne
    {
        return $this->hasOne(ZoomCredential::class, 'instructor_id', 'id');
    }

    public function jitsiCredential(): HasOne
    {
        return $this->hasOne(JitsiSetting::class, 'instructor_id', 'id');
    }

    public function withdrawRequests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class, 'user_id');
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class, 'user_id');
    }

    public function thumbnail(): Attribute
    {
        return Attribute::make(
            get: fn () => Media::resolveUrl($this->media?->src)
                ?? Media::resolveUrl($this->image)
                ?? asset('images/avatar.jpg'),
        );
    }

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->instructorInfo()->delete();
        });
    }

}
