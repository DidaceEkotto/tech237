<?php

namespace App\Livewire\Admin\Jobs;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\JobOffer;
use App\Models\JobApplication;
use App\Models\JobInterview;
use App\Models\AdminLog;

class Index extends Component
{
    public ?int $selectedJobId = null;
    public ?int $selectedAppId = null;

    // Interview Scheduling Modal Form
    public string $interview_date = '';
    public string $interview_location = 'Douala, Siège Tech237 / Startup';
    public string $interview_instructions = '';
    public string $interview_type = 'in_person';

    public function viewApplications(int $jobId)
    {
        $this->selectedJobId = $jobId;
        $this->selectedAppId = null;
    }

    public function closeApplicationsModal()
    {
        $this->selectedJobId = null;
        $this->selectedAppId = null;
    }

    public function openInterviewModal(int $applicationId)
    {
        $this->selectedAppId = $applicationId;
        $this->interview_date = now()->addDays(2)->format('Y-m-d\T10:00');
        $this->interview_location = 'Douala, Siège Tech237 / Bureau Akwa';
        $this->interview_instructions = 'Veuillez vous présenter avec votre pièce d\'identité et une copie imprimée de votre CV.';
    }

    public function closeInterviewModal()
    {
        $this->selectedAppId = null;
    }

    public function scheduleInterview()
    {
        $this->validate([
            'interview_date' => 'required|date|after:now',
            'interview_location' => 'required|string|max:255',
            'interview_type' => 'required|string',
        ], [
            'interview_date.after' => 'Impossible de planifier un entretien dans le passé ! La date et l\'heure doivent être futures.',
        ]);

        $app = JobApplication::with(['jobOffer', 'user'])->findOrFail($this->selectedAppId);

        $interview = JobInterview::create([
            'job_application_id' => $app->id,
            'scheduled_at' => $this->interview_date,
            'interview_type' => $this->interview_type,
            'location_or_link' => $this->interview_location,
            'instructions' => $this->interview_instructions,
            'status' => 'scheduled',
            'invitation_sent_at' => now(),
        ]);

        $app->update(['status' => 'interview_scheduled']);

        AdminLog::record('job_interview_scheduled', "Entretien planifié pour {$app->applicant_name} le {$this->interview_date}");
        session()->flash('app_success', "Entretien planifié avec succès pour {$app->applicant_name}. Email d'invitation généré !");
        $this->selectedAppId = null;
    }

    public function rejectApplication(int $appId)
    {
        $app = JobApplication::findOrFail($appId);
        $app->update([
            'status' => 'rejected',
            'rejection_sent_at' => now(),
        ]);
        AdminLog::record('job_application_rejected', "Candidature de {$app->applicant_name} rejetée");
        session()->flash('app_success', "Candidature rejetée et email de réponse négative courtoise envoyé.");
    }

    public function approveJob(int $id)
    {
        $j = JobOffer::findOrFail($id);
        $j->update(['status' => 'approved']);
        AdminLog::record('job_approved', "Offre d'emploi #{$id} approuvée : {$j->title}");
        session()->flash('success', "L'offre d'emploi a été approuvée et publiée.");
    }

    public function rejectJob(int $id)
    {
        $j = JobOffer::findOrFail($id);
        $j->update(['status' => 'rejected']);
        AdminLog::record('job_rejected', "Offre d'emploi #{$id} rejetée : {$j->title}");
        session()->flash('success', "L'offre d'emploi a été rejetée.");
    }

    public function togglePublish(int $id)
    {
        $j = JobOffer::findOrFail($id);
        $newStatus = $j->status === 'approved' ? 'pending_validation' : 'approved';
        $j->update(['status' => $newStatus]);
        session()->flash('success', "Statut de l'offre modifié.");
    }

    public function delete(int $id)
    {
        $j = JobOffer::findOrFail($id);
        $j->delete();
        session()->flash('success', "L'offre d'emploi a été supprimée.");
    }

    public function render()
    {
        $jobs = JobOffer::with(['creatorUser', 'applications'])->latest()->paginate(10);
        $selectedJob = $this->selectedJobId ? JobOffer::with(['applications.interviews'])->find($this->selectedJobId) : null;
        $selectedApplication = $this->selectedAppId ? JobApplication::find($this->selectedAppId) : null;

        return view('livewire.admin.jobs.index', compact('jobs', 'selectedJob', 'selectedApplication'))
            ->layout('layouts.admin', ['title' => 'Offres d\'Emploi & Recrutement IT']);
    }
}
