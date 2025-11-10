<?php
/**
 * MessageDialog Component
 * Usage: 
 *   - Include this file
 *   - Call showMessageDialog($type, $message, $autoClose = true, $redirectUrl = null)
 *   - Or use showSuccessMessage(), showErrorMessage(), showWarningMessage(), showInfoMessage()
 * 
 * Types: 'success', 'error', 'warning', 'info'
 */

function showMessageDialog($type, $message, $autoClose = true, $redirectUrl = null, $closeDelay = 3000) {
    $icons = [
        'success' => '✓',
        'error' => '✗',
        'warning' => '⚠',
        'info' => 'ℹ'
    ];
    
    $titles = [
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        'info' => 'Information'
    ];
    
    $icon = $icons[$type] ?? 'ℹ';
    $title = $titles[$type] ?? 'Message';
    ?>
    <div class="message-dialog-overlay" id="messageDialogOverlay">
        <div class="message-dialog <?php echo 'message-dialog-' . $type; ?>">
            <div class="message-dialog-icon">
                <?php echo $icon; ?>
            </div>
            <div class="message-dialog-content">
                <h3 class="message-dialog-title"><?php echo htmlspecialchars($title); ?></h3>
                <p class="message-dialog-message"><?php echo nl2br(htmlspecialchars($message)); ?></p>
            </div>
            <button class="message-dialog-close" onclick="closeMessageDialog(<?php echo $redirectUrl ? "'" . htmlspecialchars($redirectUrl) . "'" : 'null'; ?>)">×</button>
            <?php if ($autoClose): ?>
                <div class="message-dialog-progress">
                    <div class="message-dialog-progress-bar" style="animation-duration: <?php echo $closeDelay / 1000; ?>s;"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Auto-close dialog after delay
        <?php if ($autoClose): ?>
        setTimeout(function() {
            closeMessageDialog(<?php echo $redirectUrl ? "'" . htmlspecialchars($redirectUrl) . "'" : 'null'; ?>);
        }, <?php echo $closeDelay; ?>);
        <?php endif; ?>
        
        // Close dialog function
        function closeMessageDialog(redirectUrl) {
            const overlay = document.getElementById('messageDialogOverlay');
            if (overlay) {
                overlay.classList.add('message-dialog-fade-out');
                setTimeout(function() {
                    overlay.remove();
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                }, 300);
            }
        }
        
        // Close on overlay click
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('messageDialogOverlay');
            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    if (e.target === overlay) {
                        closeMessageDialog(<?php echo $redirectUrl ? "'" . htmlspecialchars($redirectUrl) . "'" : 'null'; ?>);
                    }
                });
            }
        });
    </script>
    <?php
}

// Helper functions
function showSuccessMessage($message, $autoClose = true, $redirectUrl = null, $closeDelay = 3000) {
    showMessageDialog('success', $message, $autoClose, $redirectUrl, $closeDelay);
}

function showErrorMessage($message, $autoClose = true, $redirectUrl = null, $closeDelay = 5000) {
    showMessageDialog('error', $message, $autoClose, $redirectUrl, $closeDelay);
}

function showWarningMessage($message, $autoClose = true, $redirectUrl = null, $closeDelay = 4000) {
    showMessageDialog('warning', $message, $autoClose, $redirectUrl, $closeDelay);
}

function showInfoMessage($message, $autoClose = true, $redirectUrl = null, $closeDelay = 4000) {
    showMessageDialog('info', $message, $autoClose, $redirectUrl, $closeDelay);
}

// JavaScript function to show message dialog from client-side
function renderMessageDialogScript() {
    ?>
    <script>
        // Client-side message dialog function
        function showMessageDialog(type, message, autoClose = true, redirectUrl = null, closeDelay = 3000) {
            const icons = {
                'success': '✓',
                'error': '✗',
                'warning': '⚠',
                'info': 'ℹ'
            };
            
            const titles = {
                'success': 'Success',
                'error': 'Error',
                'warning': 'Warning',
                'info': 'Information'
            };
            
            const icon = icons[type] || 'ℹ';
            const title = titles[type] || 'Message';
            
            // Remove existing dialog if any
            const existing = document.getElementById('messageDialogOverlay');
            if (existing) {
                existing.remove();
            }
            
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'message-dialog-overlay';
            overlay.id = 'messageDialogOverlay';
            
            // Create dialog
            const dialog = document.createElement('div');
            dialog.className = 'message-dialog message-dialog-' + type;
            
            dialog.innerHTML = `
                <div class="message-dialog-icon">${icon}</div>
                <div class="message-dialog-content">
                    <h3 class="message-dialog-title">${title}</h3>
                    <p class="message-dialog-message">${message.replace(/\n/g, '<br>')}</p>
                </div>
                <button class="message-dialog-close" onclick="closeMessageDialog(null)">×</button>
                ${autoClose ? `<div class="message-dialog-progress"><div class="message-dialog-progress-bar" style="animation-duration: ${closeDelay / 1000}s;"></div></div>` : ''}
            `;
            
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);
            
            // Animate in
            setTimeout(() => {
                overlay.classList.add('message-dialog-show');
            }, 10);
            
            // Auto-close
            if (autoClose) {
                setTimeout(() => {
                    closeMessageDialog(redirectUrl);
                }, closeDelay);
            }
            
            // Close on overlay click
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    closeMessageDialog(redirectUrl);
                }
            });
        }
        
        // Close dialog function
        function closeMessageDialog(redirectUrl) {
            const overlay = document.getElementById('messageDialogOverlay');
            if (overlay) {
                overlay.classList.add('message-dialog-fade-out');
                setTimeout(function() {
                    overlay.remove();
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                }, 300);
            }
        }
        
        // Helper functions
        function showSuccess(message, autoClose = true, redirectUrl = null, closeDelay = 3000) {
            showMessageDialog('success', message, autoClose, redirectUrl, closeDelay);
        }
        
        function showError(message, autoClose = true, redirectUrl = null, closeDelay = 5000) {
            showMessageDialog('error', message, autoClose, redirectUrl, closeDelay);
        }
        
        function showWarning(message, autoClose = true, redirectUrl = null, closeDelay = 4000) {
            showMessageDialog('warning', message, autoClose, redirectUrl, closeDelay);
        }
        
        function showInfo(message, autoClose = true, redirectUrl = null, closeDelay = 4000) {
            showMessageDialog('info', message, autoClose, redirectUrl, closeDelay);
        }
    </script>
    <style>
        .message-dialog-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.3s ease;
            backdrop-filter: blur(4px);
        }
        
        .message-dialog-overlay.message-dialog-show {
            opacity: 1;
        }
        
        .message-dialog-overlay.message-dialog-fade-out {
            opacity: 0;
        }
        
        .message-dialog {
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            transform: scale(0.9) translateY(-20px);
            transition: transform 0.3s ease;
            animation: messageDialogSlideIn 0.3s ease forwards;
        }
        
        .message-dialog-overlay.message-dialog-show .message-dialog {
            transform: scale(1) translateY(0);
        }
        
        .message-dialog-overlay.message-dialog-fade-out .message-dialog {
            transform: scale(0.9) translateY(-20px);
        }
        
        @keyframes messageDialogSlideIn {
            from {
                transform: scale(0.9) translateY(-20px);
                opacity: 0;
            }
            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }
        
        .message-dialog-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            margin: 0 auto 20px;
            color: #fff;
        }
        
        .message-dialog-success .message-dialog-icon {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .message-dialog-error .message-dialog-icon {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .message-dialog-warning .message-dialog-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .message-dialog-info .message-dialog-icon {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        
        .message-dialog-content {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .message-dialog-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 12px;
            color: #1f2937;
        }
        
        .message-dialog-message {
            font-size: 1rem;
            color: #6b7280;
            margin: 0;
            line-height: 1.6;
        }
        
        .message-dialog-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            border: none;
            background: #f3f4f6;
            border-radius: 50%;
            font-size: 24px;
            color: #6b7280;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            line-height: 1;
        }
        
        .message-dialog-close:hover {
            background: #e5e7eb;
            color: #1f2937;
        }
        
        .message-dialog-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #e5e7eb;
            border-radius: 0 0 16px 16px;
            overflow: hidden;
        }
        
        .message-dialog-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            animation: messageDialogProgress linear forwards;
            transform-origin: left;
        }
        
        @keyframes messageDialogProgress {
            from {
                transform: scaleX(0);
            }
            to {
                transform: scaleX(1);
            }
        }
        
        @media (max-width: 640px) {
            .message-dialog {
                padding: 24px;
                margin: 20px;
            }
            
            .message-dialog-icon {
                width: 56px;
                height: 56px;
                font-size: 28px;
            }
            
            .message-dialog-title {
                font-size: 1.25rem;
            }
            
            .message-dialog-message {
                font-size: 0.9rem;
            }
        }
    </style>
    <?php
}
?>
