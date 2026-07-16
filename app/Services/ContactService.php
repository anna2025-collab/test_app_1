<?php

namespace App\Services;

use App\Mail\OwnerContactMail;
use App\Mail\UserContactCopyMail;
use App\Models\ContactRequest;
use App\Repositories\ContactRepository;
use App\Services\Ai\ContactAnalyzer;
use Illuminate\Support\Facades\Mail;

class ContactService
{
    public function __construct(
        private readonly ContactRepository $contacts,
        private readonly ContactAnalyzer $ai,
        private readonly MetricsService $metrics,
    ) {}

    public function handle(array $payload): ContactRequest
    {
        $data = $this->sanitize($payload);
        $analysis = $this->ai->analyze($data);

        $contact = $this->contacts->create([
            ...$data,
            'ai_sentiment' => $analysis['sentiment'],
            'ai_category' => $analysis['category'],
            'ai_auto_reply' => $analysis['auto_reply'],
            'ai_available' => $analysis['available'],
            'ai_error' => $analysis['error'],
        ]);

        Mail::to(config('mail.owner_address'))->send(new OwnerContactMail($contact));
        Mail::to($contact->email)->send(new UserContactCopyMail($contact));

        $this->metrics->increment('total');
        $this->metrics->increment('successful');
        $this->metrics->increment($analysis['available'] ? 'ai_available' : 'ai_fallback');

        return $contact;
    }

    private function sanitize(array $payload): array
    {
        return [
            'name' => trim(strip_tags($payload['name'])),
            'phone' => trim(strip_tags($payload['phone'])),
            'email' => mb_strtolower(trim($payload['email'])),
            'comment' => trim(strip_tags($payload['comment'])),
        ];
    }
}
