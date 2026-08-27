<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'subject_id',
        'school_class_id',
        'title',
        'summary',
        'body',
        'video_url',
        'video_path',
        'resource_link',
        'note_images',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'note_images' => 'array',
        ];
    }

    protected function resourceLink(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                $url = trim((string) $value);

                if ($url === '') {
                    return null;
                }

                $host = strtolower((string) parse_url($url, PHP_URL_HOST));
                $placeholderHosts = [
                    'example.com',
                    'www.example.com',
                    'example.org',
                    'www.example.org',
                    'example.net',
                    'www.example.net',
                ];

                return in_array($host, $placeholderHosts, true) ? null : $url;
            },
        );
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
