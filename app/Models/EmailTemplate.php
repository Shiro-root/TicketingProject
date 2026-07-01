<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'subject', 'body_html', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Replace {{placeholder}} tokens with given values. */
    public function render(array $values): array
    {
        $subject = $this->subject;
        $body = $this->body_html;

        foreach ($values as $key => $value) {
            $subject = str_replace('{{'.$key.'}}', (string) $value, $subject);
            $body = str_replace('{{'.$key.'}}', (string) $value, $body);
        }

        return compact('subject', 'body');
    }
}
