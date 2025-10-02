<!-- Version Notification Popup Modal -->
<div class="modal fade" id="versionNotificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-rocket-takeoff me-2"></i>
                    <span id="notificationTitle">New Version Available!</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="notificationContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <button type="button" class="btn btn-outline-secondary" id="dismissAllBtn">
                            <i class="bi bi-x-circle me-1"></i>Dismiss All
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" id="viewLaterBtn">
                            <i class="bi bi-clock me-1"></i>View Later
                        </button>
                        <button type="button" class="btn btn-primary" id="markAsViewedBtn">
                            <i class="bi bi-check-circle me-1"></i>Got it!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Version Notification Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentNotifications = [];
    let currentNotificationIndex = 0;

    // Check for pending notifications on page load
    checkPendingNotifications();

    function checkPendingNotifications() {
        fetch('{{ route("version-notifications.check") }}')
            .then(response => response.json())
            .then(data => {
                if (data.has_pending && data.notifications.length > 0) {
                    currentNotifications = data.notifications;
                    currentNotificationIndex = 0;
                    showNextNotification();
                }
            })
            .catch(error => {
                console.error('Error checking notifications:', error);
            });
    }

    function showNextNotification() {
        if (currentNotificationIndex < currentNotifications.length) {
            const notification = currentNotifications[currentNotificationIndex];
            displayNotification(notification);
            
            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('versionNotificationModal'));
            modal.show();
        }
    }

    function displayNotification(notification) {
        // Update title
        document.getElementById('notificationTitle').textContent = notification.title;
        
        // Create content
        let content = `
            <div class="p-4">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-success fs-6 me-2">v${notification.version}</span>
                    <span class="badge ${getPriorityBadge(notification.priority)} fs-6">${notification.priority.toUpperCase()}</span>
                </div>
                
                <div class="mb-4">
                    <p class="text-muted mb-3">${notification.description}</p>
                </div>
        `;
        
        if (notification.features && notification.features.length > 0) {
            content += `
                <div class="mb-4">
                    <h6 class="text-primary mb-3"><i class="bi bi-stars me-2"></i>What's New:</h6>
                    <ul class="list-unstyled">
            `;
            
            notification.features.forEach(feature => {
                content += `
                    <li class="mb-2">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        ${feature}
                    </li>
                `;
            });
            
            content += `
                    </ul>
                </div>
            `;
        }
        
        content += `
                <div class="text-center">
                    <small class="text-muted">
                        <i class="bi bi-calendar me-1"></i>
                        Released: ${formatDate(notification.release_date)}
                    </small>
                </div>
            </div>
        `;
        
        document.getElementById('notificationContent').innerHTML = content;
    }

    function getPriorityBadge(priority) {
        const badges = {
            'low': 'bg-secondary',
            'medium': 'bg-warning',
            'high': 'bg-danger'
        };
        return badges[priority] || 'bg-secondary';
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Mark as viewed button
    document.getElementById('markAsViewedBtn').addEventListener('click', function() {
        const notification = currentNotifications[currentNotificationIndex];
        markAsViewed(notification.id);
    });

    // View later button (just close modal)
    document.getElementById('viewLaterBtn').addEventListener('click', function() {
        bootstrap.Modal.getInstance(document.getElementById('versionNotificationModal')).hide();
    });

    // Dismiss all button
    document.getElementById('dismissAllBtn').addEventListener('click', function() {
        dismissAllNotifications();
    });

    function markAsViewed(notificationId) {
        fetch('{{ route("version-notifications.mark-viewed") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Move to next notification or close modal
                currentNotificationIndex++;
                bootstrap.Modal.getInstance(document.getElementById('versionNotificationModal')).hide();
                
                // Show next notification after a brief delay
                setTimeout(() => {
                    showNextNotification();
                }, 500);
            }
        })
        .catch(error => {
            console.error('Error marking notification as viewed:', error);
        });
    }

    function dismissAllNotifications() {
        fetch('{{ route("version-notifications.dismiss-all") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('versionNotificationModal')).hide();
                
                // Show a brief success message
                showToast('All notifications dismissed successfully!', 'success');
            }
        })
        .catch(error => {
            console.error('Error dismissing notifications:', error);
        });
    }

    function showToast(message, type = 'info') {
        // Create a simple toast notification
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'info'} border-0 position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999;" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', toastHtml);
        const toast = new bootstrap.Toast(document.querySelector('.toast:last-child'));
        toast.show();
        
        // Remove toast after it's hidden
        toast._element.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
});
</script>

<style>
.modal-content {
    border-radius: 15px;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

#versionNotificationModal .list-unstyled li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

#versionNotificationModal .list-unstyled li:last-child {
    border-bottom: none;
}

#versionNotificationModal .badge {
    font-size: 0.75rem;
}

@media (max-width: 768px) {
    .modal-dialog {
        margin: 10px;
    }
    
    .modal-footer .d-flex {
        flex-direction: column;
        gap: 10px;
    }
    
    .modal-footer .d-flex > div {
        width: 100%;
        text-align: center;
    }
}
</style>