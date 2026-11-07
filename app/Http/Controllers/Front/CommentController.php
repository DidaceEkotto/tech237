<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Comment;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return response()->json(['error' => 'Veuillez vous connecter pour commenter.'], 401);
        }

        if ($user->isBanned()) {
            return response()->json(['error' => 'Votre compte est banni. Vous ne pouvez pas publier de commentaire.'], 403);
        }

        $request->validate([
            'content' => 'required|string|min:2|max:2000',
            'article_id' => 'nullable|exists:articles,id',
            'interview_id' => 'nullable|exists:interviews,id',
            'project_id' => 'nullable|exists:projects,id',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id' => $user->id,
            'article_id' => $request->article_id,
            'interview_id' => $request->interview_id,
            'project_id' => $request->project_id,
            'parent_id' => $request->parent_id,
            'content' => trim($request->content),
            'status' => 'pending', // Pending admin moderation
        ]);

        return back()->with('success', 'Votre commentaire a été soumis avec succès ! Il apparaîtra dès sa validation par l\'équipe de modération.');
    }
}
