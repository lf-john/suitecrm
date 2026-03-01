/**
 * Change Password — User Dropdown Menu Integration
 * Adds "Change Password" option to the user dropdown (top-right),
 * with a modal dialog for the password change form.
 */
(function() {
    'use strict';

    var MODAL_ID = 'lf-change-password-modal';
    var MENU_ITEM_CLASS = 'lf-change-password-item';
    var ENDPOINT = '/legacy/index.php?module=Users&action=change_password_json';

    function createModal() {
        if (document.getElementById(MODAL_ID)) return;

        var overlay = document.createElement('div');
        overlay.id = MODAL_ID;
        overlay.style.cssText = 'display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:100000;justify-content:center;align-items:center;';

        overlay.innerHTML =
            '<div style="background:#fff;border-radius:8px;padding:0;width:420px;max-width:90vw;box-shadow:0 8px 32px rgba(0,0,0,0.2);font-family:Lato,sans-serif;">' +
                '<div style="padding:20px 24px;border-bottom:1px solid #edebe9;display:flex;justify-content:space-between;align-items:center;">' +
                    '<h3 style="margin:0;font-size:18px;font-weight:600;color:#323130;">Change Password</h3>' +
                    '<button id="lf-cp-close" style="background:none;border:none;font-size:20px;cursor:pointer;color:#605e5c;padding:4px 8px;">&times;</button>' +
                '</div>' +
                '<div style="padding:24px;">' +
                    '<div id="lf-cp-message" style="display:none;padding:10px 14px;border-radius:4px;margin-bottom:16px;font-size:13px;"></div>' +
                    '<div style="margin-bottom:16px;">' +
                        '<label style="display:block;font-size:13px;font-weight:500;color:#605e5c;margin-bottom:6px;">Current Password</label>' +
                        '<input type="password" id="lf-cp-old" autocomplete="current-password" style="width:100%;padding:8px 12px;border:1px solid #edebe9;border-radius:4px;font-size:14px;box-sizing:border-box;" />' +
                    '</div>' +
                    '<div style="margin-bottom:16px;">' +
                        '<label style="display:block;font-size:13px;font-weight:500;color:#605e5c;margin-bottom:6px;">New Password</label>' +
                        '<input type="password" id="lf-cp-new" autocomplete="new-password" style="width:100%;padding:8px 12px;border:1px solid #edebe9;border-radius:4px;font-size:14px;box-sizing:border-box;" />' +
                    '</div>' +
                    '<div style="margin-bottom:20px;">' +
                        '<label style="display:block;font-size:13px;font-weight:500;color:#605e5c;margin-bottom:6px;">Confirm New Password</label>' +
                        '<input type="password" id="lf-cp-confirm" autocomplete="new-password" style="width:100%;padding:8px 12px;border:1px solid #edebe9;border-radius:4px;font-size:14px;box-sizing:border-box;" />' +
                    '</div>' +
                    '<div style="display:flex;justify-content:flex-end;gap:10px;">' +
                        '<button id="lf-cp-cancel" style="padding:8px 20px;border:1px solid #edebe9;border-radius:4px;background:#fff;color:#323130;font-size:14px;cursor:pointer;">Cancel</button>' +
                        '<button id="lf-cp-submit" style="padding:8px 20px;border:none;border-radius:4px;background:#125EAD;color:#fff;font-size:14px;cursor:pointer;font-weight:500;">Change Password</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

        document.body.appendChild(overlay);

        // Event handlers
        document.getElementById('lf-cp-close').addEventListener('click', closeModal);
        document.getElementById('lf-cp-cancel').addEventListener('click', closeModal);
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal();
        });
        document.getElementById('lf-cp-submit').addEventListener('click', submitPasswordChange);

        // Enter key submits
        ['lf-cp-old', 'lf-cp-new', 'lf-cp-confirm'].forEach(function(id) {
            document.getElementById(id).addEventListener('keydown', function(e) {
                if (e.key === 'Enter') submitPasswordChange();
            });
        });
    }

    function openModal() {
        var modal = document.getElementById(MODAL_ID);
        if (!modal) { createModal(); modal = document.getElementById(MODAL_ID); }
        // Reset form
        document.getElementById('lf-cp-old').value = '';
        document.getElementById('lf-cp-new').value = '';
        document.getElementById('lf-cp-confirm').value = '';
        var msg = document.getElementById('lf-cp-message');
        msg.style.display = 'none';
        msg.textContent = '';
        // Show
        modal.style.display = 'flex';
        setTimeout(function() { document.getElementById('lf-cp-old').focus(); }, 100);
    }

    function closeModal() {
        var modal = document.getElementById(MODAL_ID);
        if (modal) modal.style.display = 'none';
    }

    function showMessage(text, isError) {
        var msg = document.getElementById('lf-cp-message');
        msg.textContent = text;
        msg.style.display = 'block';
        msg.style.background = isError ? '#fde7e9' : '#dff6dd';
        msg.style.color = isError ? '#a80000' : '#107c10';
        msg.style.border = '1px solid ' + (isError ? '#f1c0c0' : '#b7e2b0');
    }

    function submitPasswordChange() {
        var oldPw = document.getElementById('lf-cp-old').value;
        var newPw = document.getElementById('lf-cp-new').value;
        var confirmPw = document.getElementById('lf-cp-confirm').value;

        if (!oldPw || !newPw || !confirmPw) {
            showMessage('All fields are required.', true);
            return;
        }
        if (newPw !== confirmPw) {
            showMessage('New passwords do not match.', true);
            return;
        }
        if (newPw.length < 6) {
            showMessage('Password must be at least 6 characters.', true);
            return;
        }

        var btn = document.getElementById('lf-cp-submit');
        btn.disabled = true;
        btn.textContent = 'Changing...';

        fetch(ENDPOINT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                old_password: oldPw,
                new_password: newPw,
                confirm_password: confirmPw
            })
        })
        .then(function(resp) { return resp.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = 'Change Password';
            if (data.success) {
                showMessage('Password changed successfully!', false);
                setTimeout(closeModal, 2000);
            } else {
                showMessage(data.message || 'Failed to change password.', true);
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.textContent = 'Change Password';
            showMessage('An error occurred. Please try again.', true);
        });
    }

    function injectMenuItem() {
        // Find the user dropdown menu (global-links-dropdown)
        var dropdowns = document.querySelectorAll('.global-links-dropdown, .dropdown-menu-right');
        dropdowns.forEach(function(menu) {
            // Only target the user dropdown (has global-user-name or logout)
            var hasLogout = menu.querySelector('scrm-logout-ui, [class*="logout"]');
            var hasUserName = menu.querySelector('.global-user-name');
            if (!hasLogout && !hasUserName) return;

            // Don't add twice
            if (menu.querySelector('.' + MENU_ITEM_CLASS)) return;

            // Create the Change Password menu item
            var item = document.createElement('a');
            item.className = 'dropdown-item global-links-sublink ' + MENU_ITEM_CLASS;
            item.href = 'javascript:void(0)';
            item.textContent = 'Change Password';
            item.setAttribute('ngbdropdownitem', '');
            item.style.cursor = 'pointer';
            item.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openModal();
            });

            // Insert before the logout component or last hr
            var logout = menu.querySelector('scrm-logout-ui');
            var lastHr = null;
            var hrs = menu.querySelectorAll('hr');
            if (hrs.length > 0) lastHr = hrs[hrs.length - 1];

            if (logout) {
                // Add separator + item before logout
                var hr = document.createElement('hr');
                menu.insertBefore(hr, logout);
                menu.insertBefore(item, logout);
            } else if (lastHr) {
                menu.insertBefore(item, lastHr.nextSibling);
            } else {
                menu.appendChild(item);
            }
        });
    }

    // Run periodically to catch dynamically rendered menus
    function init() {
        createModal();
        setInterval(injectMenuItem, 1000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
