@extends('layouts.app')

@section('title', 'Mes Notifications - BHDM')
@section('header-title', 'Notifications')

@section('content')
<div class="notifications-container">
    <!-- En-tête avec badge non lues -->
    <div class="notifications-header">
        <div class="header-info">
            <h1 class="page-title">Centre de notifications</h1>
            @if($unreadCount > 0)
                <span class="unread-badge">
                    {{ $unreadCount }} non lue{{ $unreadCount > 1 ? 's' : '' }}
                </span>
            @endif
        </div>
        
        @if($unreadCount > 0)
            <form action="{{ route('client.notifications.read-all') }}" method="POST" class="mark-all-form">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn-mark-all">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Tout marquer comme lu
                </button>
            </form>
        @endif
    </div>

    <!-- Liste des notifications -->
    <div class="notifications-list">
        @forelse($notifications as $notification)
            <div class="notification-card {{ $notification->is_read ? 'read' : 'unread' }}" 
                 data-notification-id="{{ $notification->id }}">
                
                <!-- Indicateur de statut -->
                <div class="notification-indicator">
                    @if(!$notification->is_read)
                        <span class="unread-dot"></span>
                    @endif
                </div>

                <!-- Icône selon le type -->
                <div class="notification-icon {{ $notification->type ?? 'info' }}">
                    @switch($notification->type ?? 'info')
                        @case('success')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @break
                        @case('warning')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            @break
                        @case('error')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            @break
                        @case('payment')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            @break
                        @case('document')
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            @break
                        @default
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                    @endswitch
                </div>

                <!-- Contenu -->
                <div class="notification-content">
                    <h3 class="notification-title">{{ $notification->title }}</h3>
                    <p class="notification-message">{{ $notification->message }}</p>
                    
                    <div class="notification-meta">
                        <span class="notification-time">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="12" height="12">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                        
                        @if($notification->read_at)
                            <span class="read-at">
                                Lu {{ $notification->read_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="notification-actions">
                    @if(!$notification->is_read)
                        <form action="{{ route('client.notifications.read', $notification) }}" method="POST" class="mark-read-form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-mark-read" title="Marquer comme lu">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </form>
                    @endif
                    
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="btn-action" title="Voir les détails">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3>Aucune notification</h3>
                <p>Vous n'avez pas encore reçu de notifications.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
        <div class="notifications-pagination">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@section('styles')
<style>
/* Container principal */
.notifications-container {
    padding: 1rem;
    max-width: 800px;
    margin: 0 auto;
}

/* En-tête */
.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.header-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.unread-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-mark-all {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f1f5f9;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-mark-all:hover {
    background: #e2e8f0;
    color: #1e293b;
}

/* Liste des notifications */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

/* Carte de notification */
.notification-card {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: white;
    border-radius: 1rem;
    padding: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}

.notification-card.unread {
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
    border-left: 3px solid #3b82f6;
}

.notification-card.read {
    opacity: 0.85;
}

.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Indicateur non lu */
.notification-indicator {
    width: 12px;
    display: flex;
    justify-content: center;
    padding-top: 0.5rem;
}

.unread-dot {
    width: 8px;
    height: 8px;
    background: #3b82f6;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Icône */
.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-icon svg {
    width: 20px;
    height: 20px;
}

.notification-icon.info {
    background: #dbeafe;
    color: #1d4ed8;
}

.notification-icon.success {
    background: #d1fae5;
    color: #059669;
}

.notification-icon.warning {
    background: #fef3c7;
    color: #d97706;
}

.notification-icon.error {
    background: #fee2e2;
    color: #dc2626;
}

.notification-icon.payment {
    background: #ddd6fe;
    color: #7c3aed;
}

.notification-icon.document {
    background: #cffafe;
    color: #0891b2;
}

/* Contenu */
.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 0.25rem 0;
}

.notification-card.read .notification-title {
    font-weight: 500;
}

.notification-message {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
}

.notification-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.75rem;
    color: #94a3b8;
}

.notification-time {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.read-at {
    font-style: italic;
}

/* Actions */
.notification-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-mark-read,
.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-mark-read:hover {
    background: #d1fae5;
    color: #059669;
}

.btn-action:hover {
    background: #dbeafe;
    color: #1d4ed8;
}

/* État vide */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}

.empty-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1rem;
    background: #f1f5f9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon svg {
    width: 40px;
    height: 40px;
    color: #cbd5e1;
}

.empty-state h3 {
    font-size: 1.125rem;
    color: #475569;
    margin: 0 0 0.5rem 0;
}

.empty-state p {
    font-size: 0.875rem;
    margin: 0;
}

/* Pagination */
.notifications-pagination {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

.notifications-pagination nav {
    display: flex;
    gap: 0.25rem;
}

.notifications-pagination a,
.notifications-pagination span {
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
}

.notifications-pagination a {
    background: white;
    color: #475569;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.notifications-pagination a:hover {
    background: #f1f5f9;
}

.notifications-pagination span[aria-current="page"] {
    background: #3b82f6;
    color: white;
}

/* Responsive */
@media (max-width: 640px) {
    .notifications-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .notification-card {
        padding: 0.875rem;
    }
    
    .notification-icon {
        width: 36px;
        height: 36px;
    }
    
    .notification-icon svg {
        width: 18px;
        height: 18px;
    }
    
    .notification-title {
        font-size: 0.875rem;
    }
    
    .notification-message {
        font-size: 0.8125rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-mark as read on click (optionnel)
    const notificationCards = document.querySelectorAll('.notification-card.unread');
    
    notificationCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Ne pas déclencher si on clique sur un bouton ou un lien
            if (e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            // Soumettre le formulaire de marquage comme lu
            const form = card.querySelector('.mark-read-form');
            if (form) {
                form.submit();
            }
        });
    });
    
    // Animation d'entrée
    const cards = document.querySelectorAll('.notification-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 50);
    });
});
</script>
@endsection