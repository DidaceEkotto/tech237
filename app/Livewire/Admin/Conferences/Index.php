<?php

namespace App\Livewire\Admin\Conferences;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Conference;
use App\Models\ConferenceRegistration;
use App\Models\AdminLog;
use App\Services\ImageService;

class Index extends Component
{
    use WithFileUploads;

    public ?int $conferenceId = null;
    public string $title = '';
    public string $slug = '';
    public $banner_image = null;
    public ?string $existing_banner = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $location_type = 'in_person'; // in_person, online, hybrid
    public string $venue_address = '';
    public string $city = 'Douala';
    public int $max_attendees = 200;
    public bool $is_official = true;
    public bool $accepts_registrations = true;
    public string $description = '';
    public string $status = 'approved';

    public bool $isEditing = false;
    public ?int $viewingRegistrationsConfId = null;
    public int $emailsSentCount = 0;
    public int $totalEmailsToSend = 0;
    public bool $isSendingBulk = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:conferences,slug,' . $this->conferenceId,
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'city' => 'required|string|max:100',
            'venue_address' => 'nullable|string|max:255',
            'max_attendees' => 'required|integer|min:1',
            'description' => 'required|string',
            'banner_image' => 'nullable|image|max:10240',
        ];
    }

    public function updatedTitle($val)
    {
        if (!$this->conferenceId) {
            $this->slug = Str::slug($val);
        }
    }

    public function resetForm()
    {
        $this->reset(['conferenceId', 'title', 'slug', 'banner_image', 'existing_banner', 'start_date', 'end_date', 'venue_address', 'description', 'isEditing']);
        $this->location_type = 'in_person';
        $this->city = 'Douala';
        $this->max_attendees = 200;
        $this->is_official = true;
        $this->accepts_registrations = true;
        $this->status = 'approved';
    }

    public function save()
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
        $this->validate();

        $imgPath = $this->existing_banner;
        if ($this->banner_image) {
            $imgPath = ImageService::saveImage($this->banner_image, 'conference');
        }

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'banner_image' => $imgPath,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date ?: $this->start_date,
            'location_type' => $this->location_type,
            'venue_address' => $this->venue_address ?: null,
            'city' => $this->city,
            'max_attendees' => $this->max_attendees,
            'is_official' => $this->is_official,
            'accepts_registrations' => $this->accepts_registrations,
            'description' => $this->description,
            'status' => $this->status,
        ];

        if ($this->conferenceId) {
            $conf = Conference::findOrFail($this->conferenceId);
            $conf->update($data);
            AdminLog::record('conference_updated', "Mise à jour de la conférence '{$conf->title}'");
            session()->flash('success', "Conférence mise à jour avec succès.");
        } else {
            $data['admin_id'] = Auth::guard('admin')->id();
            $conf = Conference::create($data);
            AdminLog::record('conference_created', "Création de la conférence '{$conf->title}'");
            session()->flash('success', "Nouvelle conférence créée.");
        }

        $this->resetForm();
    }

    public function edit(int $id)
    {
        $c = Conference::findOrFail($id);
        $this->conferenceId = $c->id;
        $this->title = $c->title;
        $this->slug = $c->slug;
        $this->existing_banner = $c->banner_image;
        $this->start_date = $c->start_date ? $c->start_date->format('Y-m-d\TH:i') : '';
        $this->end_date = $c->end_date ? $c->end_date->format('Y-m-d\TH:i') : '';
        $this->location_type = $c->location_type;
        $this->venue_address = $c->venue_address ?? '';
        $this->city = $c->city;
        $this->max_attendees = $c->max_attendees;
        $this->is_official = $c->is_official;
        $this->accepts_registrations = $c->accepts_registrations;
        $this->description = $c->description;
        $this->status = $c->status;
        $this->isEditing = true;
    }

    public function approve(int $id)
    {
        $c = Conference::findOrFail($id);
        $c->update(['status' => 'approved']);
        AdminLog::record('conference_approved', "Conférence #{$id} approuvée : {$c->title_fr}");
        session()->flash('success', "La conférence '{$c->title_fr}' a été validée et est désormais visible sur le site.");
    }

    public function reject(int $id)
    {
        $c = Conference::findOrFail($id);
        $c->update(['status' => 'rejected']);
        AdminLog::record('conference_rejected', "Conférence #{$id} rejetée : {$c->title_fr}");
        session()->flash('success', "La conférence '{$c->title_fr}' a été refusée.");
    }

    public function toggleApprove(int $id)
    {
        $c = Conference::findOrFail($id);
        $c->status = $c->status === 'approved' ? 'pending_validation' : 'approved';
        $c->save();
        session()->flash('success', "Statut de la conférence modifié.");
    }

    public function viewRegistrations(int $id)
    {
        $this->viewingRegistrationsConfId = $id;
        $this->emailsSentCount = 0;
        $this->totalEmailsToSend = 0;
        $this->isSendingBulk = false;
    }

    public function closeRegistrationsModal()
    {
        $this->viewingRegistrationsConfId = null;
    }

    public function sendSingleConfirmation(int $regId)
    {
        $reg = ConferenceRegistration::with('conference')->findOrFail($regId);
        $reg->update([
            'status' => 'confirmed',
            'email_sent_at' => now(),
        ]);
        AdminLog::record('conference_ticket_sent', "Envoi billet de conférence à {$reg->email}");
        session()->flash('reg_success', "Email de confirmation envoyé à {$reg->email} !");
    }

    public function sendBulkConfirmations()
    {
        if (!$this->viewingRegistrationsConfId) return;

        $regs = ConferenceRegistration::where('conference_id', $this->viewingRegistrationsConfId)
            ->whereNull('email_sent_at')
            ->get();

        $this->totalEmailsToSend = $regs->count();
        $this->emailsSentCount = 0;
        $this->isSendingBulk = true;

        foreach ($regs as $reg) {
            $reg->update([
                'status' => 'confirmed',
                'email_sent_at' => now(),
            ]);
            $this->emailsSentCount++;
        }

        AdminLog::record('conference_bulk_tickets', "Envoi groupé de {$this->emailsSentCount} billets de conférence");
        session()->flash('reg_success', "Envoi terminé : {$this->emailsSentCount} emails envoyés avec succès !");
    }

    public function delete(int $id)
    {
        $c = Conference::findOrFail($id);
        $title = $c->title;
        $c->delete();
        AdminLog::record('conference_deleted', "Suppression de la conférence '{$title}'");
        session()->flash('success', "La conférence a été supprimée.");
    }

    public function render()
    {
        $conferences = Conference::with(['creatorUser', 'registrations'])->latest()->get();
        $selectedConf = $this->viewingRegistrationsConfId ? Conference::with('registrations.user')->find($this->viewingRegistrationsConfId) : null;

        return view('livewire.admin.conferences.index', compact('conferences', 'selectedConf'))
            ->layout('layouts.admin', ['title' => 'Gestion des Conférences & Événements']);
    }
}
