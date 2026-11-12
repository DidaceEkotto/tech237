<?php

namespace App\Mail;

use App\Models\Form;
use App\Models\FormResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormNewResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public Form $form;
    public FormResponse $response;

    /**
     * Create a new message instance.
     */
    public function __construct(Form $form, FormResponse $response)
    {
        $this->form = $form;
        $this->response = $response;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject("📝 Nouvelle réponse : {$this->form->title} (Tech237 Forms)")
            ->view('emails.form_new_response', [
                'form' => $this->form,
                'response' => $this->response,
            ]);
    }
}
