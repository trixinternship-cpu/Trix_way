<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GlobalNotificationPopup
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        // Popup hanya ditampilkan untuk HR.
        if (!$user || ($user->role ?? null) !== 'hr') {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (
            $response->getStatusCode() !== 200 ||
            !str_contains($contentType, 'text/html')
        ) {
            return $response;
        }

        $content = $response->getContent();

        if (
            !is_string($content) ||
            stripos($content, '</body>') === false
        ) {
            return $response;
        }

        $script = <<<'HTML'
<style>
/* ==========================================
   GLOBAL NOTIFICATION CONTAINER
========================================== */

#trix-global-notification-stack {
    position: fixed;
    inset: 0;
    z-index: 2147483000;
    pointer-events: none;
}

/* ==========================================
   MODAL OVERLAY
========================================== */

.trix-return-overlay {
    position: fixed;
    inset: 0;
    z-index: 2147483000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: rgba(15, 23, 42, .65);
    backdrop-filter: blur(6px);
    pointer-events: auto;
    animation: trixOverlayIn .2s ease-out;
}

/* ==========================================
   COMPLETION MODAL
========================================== */

.trix-return-modal {
    position: relative;
    width: min(490px, 100%);
    max-height: 90vh;
    overflow-y: auto;
    background: #ffffff;
    border-radius: 20px;
    padding: 30px;
    box-shadow:
        0 1px 2px rgba(20,35,60,.06),
        0 6px 16px rgba(20,35,60,.05),
        0 28px 72px -20px rgba(20,35,60,.30);
    font-family: inherit;
    color: #1C2A40;
    animation: trixModalIn .25s cubic-bezier(.4,0,.2,1);
}

/* ==========================================
   ICON (SUCCESS CHECK)
========================================== */

.trix-return-icon {
    width: 68px;
    height: 68px;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
    color: #ffffff;
    font-size: 34px;
    font-weight: 800;
    box-shadow: 0 10px 26px -8px rgba(99,102,241,.5);
}

/* ==========================================
   BADGE LABEL
========================================== */

.trix-return-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    margin: 0 auto 14px;
    width: fit-content;
    border-radius: 9999px;
    background: rgba(99,102,241,.08);
    color: #4F46E5;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .08em;
    line-height: 1.5;
    text-transform: uppercase;
}

.trix-return-label .dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #10B981;
    animation: trixBlink 1.4s ease-in-out infinite;
}

/* ==========================================
   TITLE & MESSAGE
========================================== */

.trix-return-modal h2 {
    text-align: center;
    color: #1C2A40;
    font-size: 22px;
    font-weight: 800;
    line-height: 1.35;
    margin: 0 0 10px;
    letter-spacing: -0.02em;
}

.trix-return-message {
    text-align: center;
    color: #64748b;
    font-size: 14px;
    line-height: 1.65;
    margin: 0 0 22px;
}

.trix-return-message strong {
    color: #1C2A40;
    font-weight: 700;
}

/* ==========================================
   TRIP DETAILS
========================================== */

.trix-return-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    padding: 18px;
    background: #F8FAFC;
    border: 1px solid #E2E7EF;
    border-radius: 14px;
}

.trix-return-detail span {
    display: block;
    color: #64748b;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .08em;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.trix-return-detail b {
    display: block;
    color: #1C2A40;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.45;
    overflow-wrap: anywhere;
}

.trix-return-footer {
    text-align: center;
    color: #94A3B8;
    font-size: 11px;
    font-weight: 500;
    margin-top: 17px;
    letter-spacing: .02em;
}

/* ==========================================
   CONFIRM BUTTON
========================================== */

.trix-return-confirm {
    display: block;
    width: 100%;
    margin-top: 22px;
    padding: 14px 18px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
    color: #ffffff;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: .02em;
    cursor: pointer;
    box-shadow: 0 10px 24px -8px rgba(99,102,241,.5);
    transition: all .25s cubic-bezier(.4,0,.2,1);
}

.trix-return-confirm:hover {
    transform: translateY(-1px);
    box-shadow: 0 14px 30px -8px rgba(99,102,241,.6);
}

.trix-return-confirm:active {
    transform: translateY(0) scale(.98);
}

/* ==========================================
   NORMAL TOAST NOTIFICATION
========================================== */

.trix-global-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    width: min(390px, calc(100vw - 32px));
    padding: 17px 18px;
    border-radius: 14px;
    background: #ffffff;
    color: #1C2A40;
    border: 1px solid #E2E7EF;
    border-left: 4px solid #6366F1;
    box-shadow:
        0 1px 2px rgba(20,35,60,.06),
        0 12px 28px rgba(20,35,60,.08),
        0 28px 56px -20px rgba(20,35,60,.16);
    pointer-events: auto;
    font-family: inherit;
    animation: trixToastIn .3s cubic-bezier(.4,0,.2,1);
}

.trix-global-toast strong {
    display: block;
    margin: 0 28px 6px 0;
    font-size: 14px;
    font-weight: 700;
    color: #1C2A40;
}

.trix-global-toast p {
    color: #64748b;
    font-size: 12.5px;
    line-height: 1.55;
    margin: 0;
    overflow-wrap: anywhere;
}

.trix-global-toast button {
    position: absolute;
    top: 10px;
    right: 12px;
    border: none;
    background: transparent;
    color: #94A3B8;
    font-size: 20px;
    line-height: 1;
    cursor: pointer;
    transition: color .15s ease;
}

.trix-global-toast button:hover {
    color: #E11D48;
}

/* ==========================================
   ANIMATION
========================================== */

@keyframes trixOverlayIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes trixModalIn {
    from { opacity: 0; transform: translateY(12px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes trixToastIn {
    from { opacity: 0; transform: translateX(30px); }
    to   { opacity: 1; transform: translateX(0); }
}

@keyframes trixBlink {
    0%, 100% { opacity: 1; }
    50%      { opacity: .3; }
}

/* ==========================================
   RESPONSIVE
========================================== */

@media (max-width: 480px) {
    .trix-return-modal {
        padding: 24px 18px;
        border-radius: 16px;
    }

    .trix-return-details {
        grid-template-columns: 1fr 1fr;
        gap: 11px;
        padding: 14px;
    }

    .trix-return-modal h2 {
        font-size: 19px;
    }

    .trix-global-toast {
        top: 10px;
        right: 10px;
    }
}

/* ==========================================
   REDUCED MOTION
========================================== */

@media (prefers-reduced-motion: reduce) {
    .trix-return-overlay,
    .trix-return-modal,
    .trix-global-toast,
    .trix-return-label .dot {
        animation: none !important;
    }
}
</style>

<div id="trix-global-notification-stack"></div>

<script>
(function () {

    if (window.__trixGlobalNotificationStarted) {
        return;
    }

    window.__trixGlobalNotificationStarted = true;

    const endpoint = '/hr/notifications/data';
    const storageKey = 'trixway_seen_notification_ids_v4';

    const stack = document.getElementById(
        'trix-global-notification-stack'
    );

    if (!stack) return;

    let seen = new Set();
    let initialized = false;
    let queue = [];
    let showing = false;

    try {
        seen = new Set(
            JSON.parse(
                sessionStorage.getItem(storageKey) || '[]'
            ).map(String)
        );
    } catch (e) {}


    // ==========================================
    // REMEMBER NOTIFICATION
    // ==========================================

    function remember(id) {

        seen.add(String(id));

        const values = Array.from(seen).slice(-150);

        seen = new Set(values);

        try {
            sessionStorage.setItem(
                storageKey,
                JSON.stringify(values)
            );
        } catch (e) {}

    }


    // ==========================================
    // CREATE TEXT ELEMENT
    // ==========================================

    function addText(parent, tag, className, value) {

        const node = document.createElement(tag);

        if (className) {
            node.className = className;
        }

        node.textContent = value || '-';

        parent.appendChild(node);

        return node;
    }


    // ==========================================
    // TRIP DETAIL
    // ==========================================

    function addDetail(parent, label, value) {

        const wrap = document.createElement('div');

        wrap.className = 'trix-return-detail';

        addText(wrap, 'span', '', label);
        addText(wrap, 'b', '', value || '-');

        parent.appendChild(wrap);

    }


    // ==========================================
    // COMPLETION MODAL (MANUAL + AUTOMATIC)
    // ==========================================

    function showReturnModal(item) {

        const isAutomatic =
            item.category === 'trip_auto_completed';

        const overlay = document.createElement('div');
        overlay.className = 'trix-return-overlay';

        overlay.setAttribute('role', 'alertdialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute(
            'aria-labelledby',
            'trix-return-title'
        );

        const modal = document.createElement('div');
        modal.className = 'trix-return-modal';

        // Success icon
        addText(modal, 'div', 'trix-return-icon', '✓');

        // Label (badge) with live dot
        const label = document.createElement('div');
        label.className = 'trix-return-label';

        const labelDot = document.createElement('span');
        labelDot.className = 'dot';
        label.appendChild(labelDot);

        const labelText = document.createElement('span');
        labelText.textContent = isAutomatic
            ? 'AUTO COMPLETED · GPS'
            : 'TRIP COMPLETED';
        label.appendChild(labelText);

        modal.appendChild(label);

        // Title
        const title = addText(
            modal,
            'h2',
            '',
            isAutomatic
                ? 'Driver Has Returned!'
                : 'Trip Successfully Completed!'
        );

        title.id = 'trix-return-title';

        // Message (English + mention PT TRIX INDONESIA)
        const message = document.createElement('p');
        message.className = 'trix-return-message';

        if (isAutomatic) {
            message.innerHTML =
                'The driver has returned to <strong>PT TRIX INDONESIA</strong>. ' +
                'The trip was automatically completed based on the driver’s GPS location.';
        } else {
            message.innerHTML =
                'The driver has returned to <strong>PT TRIX INDONESIA</strong> ' +
                'and successfully completed the trip.';
        }

        modal.appendChild(message);

        // Trip details
        const details = document.createElement('div');
        details.className = 'trix-return-details';

        addDetail(details, 'Driver Name', item.driver_name);
        addDetail(details, 'Trip Code', item.trip_code);
        addDetail(details, 'Vehicle', item.vehicle_plate);
        addDetail(
            details,
            isAutomatic ? 'Return Time' : 'Completion Time',
            item.created_at_label || item.created_at
        );

        modal.appendChild(details);

        // Footer
        addText(
            modal,
            'div',
            'trix-return-footer',
            isAutomatic
                ? 'TRIX WAY · Automatic completion based on GPS location'
                : 'TRIX WAY · Trip completed by driver'
        );

        // Confirm button
        const button = addText(
            modal,
            'button',
            'trix-return-confirm',
            'Got It'
        );

        button.type = 'button';

        button.addEventListener('click', function () {
            overlay.remove();
            showing = false;
            showNext();
        });

        overlay.appendChild(modal);
        stack.appendChild(overlay);

        button.focus();
    }


    // ==========================================
    // NORMAL TOAST
    // ==========================================

    function showToast(item) {

        const toast = document.createElement('div');

        toast.className = 'trix-global-toast';

        const close = addText(
            toast,
            'button',
            '',
            '×'
        );

        close.type = 'button';

        close.setAttribute(
            'aria-label',
            'Close notification'
        );

        close.addEventListener('click', function () {

            toast.remove();

            showing = false;

            showNext();

        });

        addText(
            toast,
            'strong',
            '',
            item.title || 'New notification'
        );

        addText(
            toast,
            'p',
            '',
            item.message || 'You have a new notification.'
        );

        stack.appendChild(toast);

        window.setTimeout(function () {

            if (toast.isConnected) {
                toast.remove();
            }

            showing = false;

            showNext();

        }, 6500);

    }


    // ==========================================
    // SHOW NEXT NOTIFICATION
    // ==========================================

    function showNext() {

        if (showing || queue.length === 0) {
            return;
        }

        showing = true;

        const item = queue.shift();

        // Both manual and automatic completion
        // must use a large modal.
        if (
            item.category === 'trip_completed' ||
            item.category === 'trip_auto_completed'
        ) {

            showReturnModal(item);

        } else {

            showToast(item);

        }

    }


    // ==========================================
    // POLLING NOTIFICATIONS
    // ==========================================

    async function poll() {

        try {

            const response = await fetch(endpoint, {

                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },

                credentials: 'same-origin',

                cache: 'no-store'

            });


            if (!response.ok) {
                return;
            }


            const data = await response.json();

            const items = Array.isArray(data.notifications)
                ? data.notifications
                : [];


            // First page load
            if (!initialized) {

                initialized = true;

                items.slice().reverse().forEach(function (item) {

                    const isCompletion =
                        item.category === 'trip_completed' ||
                        item.category === 'trip_auto_completed';

                    // Show unread completion notifications
                    // that have not already been shown.
                    if (
                        isCompletion &&
                        !item.is_read &&
                        !seen.has(String(item.id))
                    ) {

                        queue.push(item);

                    }

                    remember(item.id);

                });

                showNext();

                return;

            }


            // New notifications
            const fresh = items
                .filter(function (item) {

                    return !seen.has(String(item.id));

                })
                .reverse();


            fresh.forEach(function (item) {

                queue.push(item);

                remember(item.id);

            });


            showNext();

        } catch (e) {

            // Polling failure must not interrupt HR pages.

        }

    }


    // ==========================================
    // START
    // ==========================================

    poll();

    window.setInterval(poll, 5000);

})();
</script>
HTML;

        $content = preg_replace(
            '/<\/body>/i',
            $script . "\n</body>",
            $content,
            1
        );

        if (is_string($content)) {
            $response->setContent($content);
        }

        return $response;
    }
}