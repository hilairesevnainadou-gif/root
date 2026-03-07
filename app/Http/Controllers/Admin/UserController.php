<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Liste
     */
    public function index(Request $request): View
    {
        $query = User::withCount(['fundingRequests', 'documentUsers']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('member_id', 'like', "%{$request->search}%");
            });
        }

        if ($request->member_type) {
            $query->where('member_type', $request->member_type);
        }

        $users = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Détails
     */
    public function show(User $user): View
    {
        $user->load(['wallet', 'fundingRequests' => fn($q) => $q->latest()->limit(10)]);

        $stats = [
            'total_requests' => $user->fundingRequests()->count(),
            'total_funded' => $user->fundingRequests()->where('status', 'funded')->sum('amount_approved'),
            'documents_count' => $user->documentUsers()->count(),
            'wallet_balance' => $user->wallet?->balance ?? 0,
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Mettre à jour
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->update($request->validated());

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    /**
     * Vérifier (attribution member_id)
     */
    public function verify(User $user): RedirectResponse
    {
        $user->update([
            'is_verified' => true,
            'member_status' => 'active',
        ]);

        if (!$user->member_id) {
            $user->update([
                'member_id' => 'BHDM-' . now()->year . '-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                'member_since' => now(),
            ]);
        }

        return back()->with('success', 'Utilisateur vérifié. Member ID: ' . $user->member_id);
    }

    /**
     * Suspendre/Réactiver
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        $newStatus = $user->member_status === 'active' ? 'suspended' : 'active';
        $user->update(['member_status' => $newStatus]);

        return back()->with('success', $newStatus === 'active' ? 'Réactivé' : 'Suspendu');
    }

    /**
     * Changer rôle
     */
    public function promote(Request $request, User $user): RedirectResponse
    {
        $request->validate(['role' => ['required', 'in:admin,moderator,user']]);

        $updates = match($request->role) {
            'admin' => ['is_admin' => true, 'is_moderator' => true],
            'moderator' => ['is_admin' => false, 'is_moderator' => true],
            default => ['is_admin' => false, 'is_moderator' => false],
        };

        $user->update($updates);

        return back()->with('success', 'Rôle mis à jour: ' . $request->role);
    }
}
