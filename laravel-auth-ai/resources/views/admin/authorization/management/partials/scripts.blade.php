<script>
(function() {
    const CSRF = '{{ csrf_token() }}';
    const ENDPOINTS = {
        roles: {
            store: '{{ route("dashboard.roles.store") }}',
            update: (id) => `{{ url("dashboard/roles") }}/${id}`,
            destroy: (id) => `{{ url("dashboard/roles") }}/${id}`,
        },
        assign: '{{ route("dashboard.access-management.assign") }}',
    };

    let selectedUserIds = [];

    // ─── Section Switching ───────────────────────────────────────────────────
    window.switchSection = function(section) {
        document.querySelectorAll('.access-section').forEach(el => el.classList.add('hidden'));
        document.getElementById(`section-${section}`).classList.remove('hidden');

        // Update Nav UI
        document.querySelectorAll('.nav-tab').forEach(el => {
            el.classList.remove('text-indigo-600', 'dark:text-indigo-400');
            el.classList.add('text-slate-400');
        });
        document.querySelectorAll('.nav-tab div[id^="line-"]').forEach(el => {
            el.classList.remove('w-full');
            el.classList.add('w-0');
        });

        const activeNav = document.getElementById(`nav-${section}`);
        activeNav.classList.remove('text-slate-400');
        activeNav.classList.add('text-indigo-600', 'dark:text-indigo-400');
        
        const activeLine = document.getElementById(`line-${section}`);
        activeLine.classList.remove('w-0');
        activeLine.classList.add('w-full');

        // Update URL hash/tab param if needed (optional)
        const url = new URL(window.location);
        url.searchParams.set('tab', section);
        window.history.pushState({}, '', url);
    };

    // ─── Checkbox Selection ──────────────────────────────────────────────────
    window.toggleAllUsers = function(masterCb) {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCb.checked;
            updateSelectionState(cb.value, cb.checked);
        });
        refreshSelectionUI();
    };

    window.toggleUserSelection = function(cb) {
        updateSelectionState(cb.value, cb.checked);
        refreshSelectionUI();
        
        // Sync master checkbox
        const allCbs = document.querySelectorAll('.user-checkbox');
        const masterCb = document.getElementById('check-all-users');
        if (masterCb) {
            const allChecked = Array.from(allCbs).every(c => c.checked);
            const someChecked = Array.from(allCbs).some(c => c.checked);
            masterCb.checked = allChecked;
            masterCb.indeterminate = !allChecked && someChecked;
        }
    };

    function updateSelectionState(userId, isSelected) {
        userId = parseInt(userId);
        if (isSelected) {
            if (!selectedUserIds.includes(userId)) selectedUserIds.push(userId);
        } else {
            selectedUserIds = selectedUserIds.filter(id => id !== userId);
        }
    }

    function refreshSelectionUI() {
        const count = selectedUserIds.length;
        const counterEl = document.getElementById('selection-counter');
        const assignBtn = document.getElementById('btn-open-assign-modal');

        if (counterEl) {
            counterEl.textContent = `${count} Terpilih`;
            counterEl.classList.toggle('hidden', count === 0);
        }

        if (assignBtn) {
            assignBtn.disabled = count === 0;
            assignBtn.classList.toggle('opacity-50', count === 0);
            assignBtn.classList.toggle('cursor-not-allowed', count === 0);
        }
    }

    // ─── Assign Modal ────────────────────────────────────────────────────────
    window.openAssignModal = function() {
        document.getElementById('assign-user-count').textContent = selectedUserIds.length;
        document.getElementById('assignModal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    window.closeAssignModal = function() {
        document.getElementById('assignModal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    window.submitAssignment = function() {
        const roleIds = Array.from(document.querySelectorAll('.assign-role-checkbox-modal:checked')).map(cb => cb.value);
        if (roleIds.length === 0) {
            showToast('error', 'Pilih setidaknya satu role.');
            return;
        }

        const action = document.querySelector('input[name="assign_action_modal"]:checked').value;

        setLoading('btn-submit-assign', 'assign-spinner', true);

        fetch(ENDPOINTS.assign, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                user_ids: selectedUserIds,
                role_ids: roleIds,
                action: action
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('success', res.message);
                location.reload(); // Reload to refresh table states
            } else {
                showToast('error', res.message);
            }
        })
        .catch(() => showToast('error', 'Terjadi kesalahan server.'))
        .finally(() => setLoading('btn-submit-assign', 'assign-spinner', false));
    };

    window.deleteRole = function(id, name) {
        AppPopup.confirm({
            title: 'Hapus Role?',
            description: `Role "${name}" akan dihapus permanen.`,
            confirmText: 'Ya, Hapus',
            onConfirm: () => {
                fetch(ENDPOINTS.roles.destroy(id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) { showToast('success', res.message); location.reload(); }
                    else { showToast('error', res.message); }
                });
            }
        });
    };

    // ─── Misc Helpers ────────────────────────────────────────────────────────
    window.toggleGroup = function(toggle) {
        const group = toggle.dataset.group;
        document.querySelectorAll(`.perm-checkbox[data-group="${group}"]`).forEach(cb => cb.checked = toggle.checked);
    };

    window.toggleAllPermissions = function(checked) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = checked);
        document.querySelectorAll('.group-toggle').forEach(cb => { cb.checked = checked; cb.indeterminate = false; });
    };

    function syncAllGroupToggles() {
        document.querySelectorAll('.group-toggle').forEach(toggle => {
            const group = toggle.dataset.group;
            const allInGroup = document.querySelectorAll(`.perm-checkbox[data-group="${group}"]`);
            const allChecked = Array.from(allInGroup).every(cb => cb.checked);
            const someChecked = Array.from(allInGroup).some(cb => cb.checked);
            toggle.checked = allChecked;
            toggle.indeterminate = !allChecked && someChecked;
        });
    }

    function setLoading(btnId, spinnerId, loading) {
        const btn = document.getElementById(btnId);
        btn.disabled = loading;
        btn.classList.toggle('opacity-70', loading);
        if (spinnerId) document.getElementById(spinnerId).classList.toggle('hidden', !loading);
    }

    // ─── INITIALIZATION ──────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'roles';
        switchSection(activeTab);
    });

})();
</script>
