<?php

namespace App\Livewire\Admin\Newsletters;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'subscribers'; // 'subscribers', 'compose', 'history'

    // Compose Fields
    public string $subject_fr = '';
    public string $subject_en = '';
    public string $content_fr = '';
    public string $content_en = '';

    protected $paginationTheme = 'bootstrap';

    protected function rules(): array
    {
        return [
            'subject_fr' => 'required|string|max:255',
            'subject_en' => 'nullable|string|max:255',
            'content_fr' => 'required|string',
            'content_en' => 'nullable|string',
        ];
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
    }

    public function sendNewsletter()
    {
        $this->validate();

        $subscribersCount = NewsletterSubscriber::where('is_active', true)->count();
        $admin = Auth::guard('admin')->user();

        $nl = Newsletter::create([
            'sent_by_admin_id' => $admin->id,
            'subject_fr' => $this->subject_fr,
            'subject_en' => $this->subject_en,
            'content_fr' => $this->content_fr,
            'content_en' => $this->content_en,
            'recipients_count' => $subscribersCount,
            'sent_at' => now(),
        ]);

        AdminLog::record('newsletter_sent', "Envoi d'une newsletter '{$nl->subject_fr}' à {$subscribersCount} abonnés", $admin);

        $this->reset(['subject_fr', 'subject_en', 'content_fr', 'content_en']);
        $this->activeTab = 'history';
        session()->flash('success', "Newsletter envoyée avec succès à {$subscribersCount} abonné(s) !");
    }

    public function deleteSubscriber(int $id)
    {
        $sub = NewsletterSubscriber::findOrFail($id);
        $email = $sub->email;
        $sub->delete();
        AdminLog::record('subscriber_deleted', "Suppression de l'abonné newsletter '{$email}'");
        session()->flash('success', "L'abonné '{$email}' a été supprimé.");
    }

    public function render()
    {
        $subscribers = NewsletterSubscriber::latest()->paginate(10);
        $newsletters = Newsletter::with('sender')->latest()->get();

        return view('livewire.admin.newsletters.index', compact('subscribers', 'newsletters'))
            ->layout('layouts.admin', ['title' => 'Newsletters & Abonnés']);
    }
}
