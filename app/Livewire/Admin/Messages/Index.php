<?php

namespace App\Livewire\Admin\Messages;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\User;
use App\Models\UserMessage;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $viewMode = 'threads'; // 'threads', 'compose', 'stats'

    // Compose Message Form State
    public string $recipientType = 'all'; // 'all', 'specific'
    public array $selectedUserIds = [];
    public string $userSearch = '';
    public string $subject = '';
    public string $messageContent = '';
    public $attachments = []; // array of UploadedFiles
    public bool $sendEmailCopy = true;

    // Active thread inspection
    public ?int $activeUserId = null;
    public string $threadReplyText = '';
    public $threadReplyAttachment = null;

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCompose(string $type = 'all', ?int $userId = null)
    {
        $this->viewMode = 'compose';
        $this->recipientType = $type;
        $this->selectedUserIds = $userId ? [$userId] : [];
        $this->subject = '';
        $this->messageContent = '';
        $this->attachments = [];
        $this->sendEmailCopy = true;
    }

    public function selectThread(int $userId)
    {
        $this->activeUserId = $userId;
        $this->threadReplyText = '';
        $this->threadReplyAttachment = null;

        // Mark messages in this thread as read by admin if applicable
        UserMessage::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('admin_id')->orWhere('is_support', true);
            })
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function toggleUserSelection(int $userId)
    {
        if (in_array($userId, $this->selectedUserIds)) {
            $this->selectedUserIds = array_diff($this->selectedUserIds, [$userId]);
        } else {
            $this->selectedUserIds[] = $userId;
        }
    }

    public function sendMessage()
    {
        $this->validate([
            'subject' => 'required|string|max:200',
            'messageContent' => 'required|string|min:5|max:10000',
            'attachments.*' => 'nullable|file|max:15360|mimes:jpeg,png,jpg,webp,gif,pdf,docx',
        ], [
            'subject.required' => 'Le sujet du message est obligatoire.',
            'messageContent.required' => 'Le contenu du message ne peut pas être vide.',
            'attachments.*.mimes' => 'Formats autorisés : images (JPG, PNG, WEBP, GIF), PDF et Word (.docx uniquement).',
            'attachments.*.max' => 'Chaque pièce jointe ne doit pas dépasser 15 Mo.',
        ]);

        $admin = Auth::guard('admin')->user();
        $adminId = $admin?->id;

        // Save attachments to public/storage/messages
        $savedAttachments = [];
        if (!empty($this->attachments)) {
            foreach ($this->attachments as $file) {
                if ($file) {
                    $origName = $file->getClientOriginalName();
                    $ext = strtolower($file->getClientOriginalExtension());
                    $type = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'image' : 'document';
                    $filename = 'admin_att_' . time() . '_' . Str::random(8) . '.' . $ext;
                    $path = $file->storeAs('messages', $filename, 'public');
                    $savedAttachments[] = [
                        'path' => 'storage/' . $path,
                        'name' => $origName,
                        'type' => $type,
                    ];
                }
            }
        }

        // Determine recipients
        if ($this->recipientType === 'all') {
            $recipients = User::all();
            $targetDesc = "tous les inscrits du site (" . $recipients->count() . " utilisateurs)";
        } else {
            if (empty($this->selectedUserIds)) {
                $this->addError('selectedUserIds', 'Veuillez sélectionner au moins un destinataire.');
                return;
            }
            $recipients = User::whereIn('id', $this->selectedUserIds)->get();
            $targetDesc = $recipients->count() . " membre(s) spécifique(s)";
        }

        if ($recipients->isEmpty()) {
            session()->flash('error', "Aucun destinataire trouvé.");
            return;
        }

        $primaryAttachment = !empty($savedAttachments) ? $savedAttachments[0] : null;

        $sentCount = 0;
        foreach ($recipients as $recipient) {
            UserMessage::create([
                'user_id' => $recipient->id,
                'admin_id' => $adminId,
                'sender_id' => null,
                'subject' => $this->subject,
                'message' => $this->messageContent,
                'attachment_path' => $primaryAttachment['path'] ?? null,
                'attachment_name' => $primaryAttachment['name'] ?? null,
                'attachment_type' => $primaryAttachment['type'] ?? null,
                'is_read' => false,
            ]);

            // Email copy
            if ($this->sendEmailCopy && $recipient->email && filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::send('emails.new_message_notification', [
                        'recipient' => $recipient,
                        'sender' => (object)['name' => 'Équipe d\'administration Tech237', 'email' => config('mail.from.address')],
                        'msgSubject' => $this->subject,
                        'msgContent' => $this->messageContent,
                        'replyUrl' => route('user.dashboard', ['section' => 'messages']),
                    ], function ($m) use ($recipient) {
                        $m->to($recipient->email)
                          ->subject("💬 Message officiel de l'administration Tech237 : {$this->subject}");
                    });
                } catch (\Throwable $e) {
                    Log::error("Erreur envoi email broadcast admin: " . $e->getMessage());
                }
            }
            $sentCount++;
        }

        AdminLog::record('admin_message_sent', "Message admin envoyé à {$targetDesc} (Sujet : {$this->subject})", $admin);
        session()->flash('success', "Le message a été distribué avec succès à {$sentCount} utilisateur(s) ! Une notification email leur a été transmise.");

        $this->viewMode = 'threads';
        $this->attachments = [];
        $this->subject = '';
        $this->messageContent = '';
        $this->selectedUserIds = [];
    }

    public function sendReplyInThread()
    {
        if (!$this->activeUserId || !trim($this->threadReplyText)) return;

        $recipient = User::findOrFail($this->activeUserId);
        $admin = Auth::guard('admin')->user();

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;

        if ($this->threadReplyAttachment) {
            $this->validate([
                'threadReplyAttachment' => 'file|max:15360|mimes:jpeg,png,jpg,webp,gif,pdf,docx',
            ]);
            $origName = $this->threadReplyAttachment->getClientOriginalName();
            $ext = strtolower($this->threadReplyAttachment->getClientOriginalExtension());
            $attachmentType = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'image' : 'document';
            $filename = 'admin_reply_' . time() . '_' . Str::random(8) . '.' . $ext;
            $path = $this->threadReplyAttachment->storeAs('messages', $filename, 'public');
            $attachmentPath = 'storage/' . $path;
            $attachmentName = $origName;
        }

        $lastMsg = UserMessage::where('user_id', $recipient->id)->latest()->first();
        $sub = $lastMsg ? (str_starts_with($lastMsg->subject, 'Re:') ? $lastMsg->subject : 'Re: ' . $lastMsg->subject) : 'Message de l\'administration';

        UserMessage::create([
            'user_id' => $recipient->id,
            'admin_id' => $admin?->id,
            'sender_id' => null,
            'subject' => $sub,
            'message' => $this->threadReplyText,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'is_read' => false,
        ]);

        if ($recipient->email && filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::send('emails.new_message_notification', [
                    'recipient' => $recipient,
                    'sender' => (object)['name' => $admin ? $admin->name : 'Support Tech237'],
                    'msgSubject' => $sub,
                    'msgContent' => $this->threadReplyText,
                    'replyUrl' => route('user.dashboard', ['section' => 'messages']),
                ], function ($m) use ($recipient, $sub) {
                    $m->to($recipient->email)
                      ->subject("💬 Réponse de l'administration Tech237 : {$sub}");
                });
            } catch (\Throwable $e) {}
        }

        $this->threadReplyText = '';
        $this->threadReplyAttachment = null;
        session()->flash('success', "Réponse envoyée avec succès à {$recipient->name}.");
    }

    public function deleteMessage(int $id)
    {
        $msg = UserMessage::findOrFail($id);
        $msg->delete();
        session()->flash('success', "Message supprimé.");
    }

    public function render()
    {
        // 1. Thread Partners (Users who have exchanged messages, or all users if searching)
        if ($this->search) {
            $threadsQuery = User::where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('admin_id')->orWhere('is_support', true);
                })->where('is_read', false);
            }])
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }]);
        } else {
            $threadsQuery = User::where(function ($q) {
                $q->whereHas('messages')
                  ->orWhereHas('sentMessages', function ($sub) {
                      $sub->where('is_support', true);
                  });
            })
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('admin_id')->orWhere('is_support', true);
                })->where('is_read', false);
            }])
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }]);
        }

        $threads = $threadsQuery->paginate(15);

        // 2. Active discussion messages
        $activeMessages = collect();
        $activeUser = null;
        if ($this->activeUserId) {
            $activeUser = User::find($this->activeUserId);
            if ($activeUser) {
                $activeMessages = UserMessage::where(function ($q) use ($activeUser) {
                    $q->where('user_id', $activeUser->id)
                      ->where(function ($sub) {
                          $sub->whereNotNull('admin_id')
                              ->orWhere('is_support', true)
                              ->orWhereNull('sender_id');
                      });
                })
                ->orWhere(function ($q) use ($activeUser) {
                    $q->where('sender_id', $activeUser->id)
                      ->where('is_support', true);
                })
                ->with(['user', 'sender', 'admin'])
                ->orderBy('created_at', 'asc')
                ->get();
            }
        }

        // 3. User search for compose modal (searches all registered users on the site)
        $usersForSelection = collect();
        if ($this->viewMode === 'compose' && $this->recipientType === 'specific') {
            $usersForSelection = User::when($this->userSearch, function ($q) {
                $q->where('name', 'like', "%{$this->userSearch}%")
                  ->orWhere('email', 'like', "%{$this->userSearch}%");
            })->take(200)->get();
        }

        // 4. Quick stats
        $stats = [
            'total_messages' => UserMessage::count(),
            'unread_messages' => UserMessage::where('is_read', false)->where(function ($q) {
                $q->whereNull('admin_id')->orWhere('is_support', true);
            })->count(),
            'total_users' => User::count(),
            'admin_broadcasts' => UserMessage::whereNotNull('admin_id')->count(),
        ];

        return view('livewire.admin.messages.index', compact('threads', 'activeMessages', 'activeUser', 'usersForSelection', 'stats'))
            ->layout('layouts.admin', ['title' => 'Messagerie & Communications Administrateur']);
    }
}
