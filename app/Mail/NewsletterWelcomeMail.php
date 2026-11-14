<?php

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public NewsletterSubscriber $subscriber;

    public function __construct(NewsletterSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function build()
    {
        return $this->subject('Merci pour votre inscription à la newsletter Tech237 / Thank you for subscribing to Tech237 Newsletter')
            ->view('emails.newsletter_welcome', [
                'subscriber' => $this->subscriber,
            ]);
    }
}
