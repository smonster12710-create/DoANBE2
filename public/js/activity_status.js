document.addEventListener('DOMContentLoaded', function () {
    const currentUserId = window.currentUserId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const heartbeatMs = 45000;
    const onlineGraceMs = 90000;
    const maxAwayMs = 24 * 60 * 60 * 1000;
    const tabId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    const openTabsKey = `activity_open_tabs_${currentUserId}`;
    let isInternalNavigation = false;

    if (!currentUserId || !csrfToken) {
        return;
    }

    registerOpenTab();
    markOnline();
    setInterval(markOnline, heartbeatMs);
    setInterval(refreshActivityDots, 60000);
    setInterval(registerOpenTab, 30000);

    window.addEventListener('focus', markOnline);

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            markOnline();
        }
    });

    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');

        if (link && isSameOriginUrl(link.href)) {
            isInternalNavigation = true;
        }
    });

    document.addEventListener('submit', function (event) {
        const action = event.target.getAttribute('action') || window.location.href;

        if (isSameOriginUrl(action)) {
            isInternalNavigation = true;
        }
    });

    window.addEventListener('pagehide', function () {
        unregisterOpenTab();

        if (isInternalNavigation || hasOtherOpenTabs()) {
            return;
        }

        markOfflineWithBeacon();
    });

    waitForEcho(function () {
        window.Echo.private(`user.${currentUserId}`)
            .listen('.UserActivityUpdated', function (event) {
                if (event.activity) {
                    updateActivityDots(event.activity);
                }
            });
    });

    function markOnline() {
        postActivity('/activity/online');
    }

    function postActivity(url) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            keepalive: true
        })
            .then(function (response) {
                if (!response.ok) {
                    return null;
                }

                return response.json();
            })
            .then(function (data) {
                if (data && data.activity) {
                    updateActivityDots(data.activity);
                }

                if (data && Array.isArray(data.activities)) {
                    data.activities.forEach(updateActivityDots);
                }
            })
            .catch(function () {});
    }

    function markOfflineWithBeacon() {
        if (!navigator.sendBeacon) {
            postActivity('/activity/offline');
            return;
        }

        const formData = new FormData();
        formData.append('_token', csrfToken);
        navigator.sendBeacon('/activity/offline', formData);
    }

    function isSameOriginUrl(url) {
        try {
            return new URL(url, window.location.href).origin === window.location.origin;
        } catch (error) {
            return false;
        }
    }

    function registerOpenTab() {
        const tabs = openTabs();
        tabs[tabId] = Date.now();
        localStorage.setItem(openTabsKey, JSON.stringify(cleanOpenTabs(tabs)));
    }

    function unregisterOpenTab() {
        const tabs = openTabs();
        delete tabs[tabId];
        localStorage.setItem(openTabsKey, JSON.stringify(cleanOpenTabs(tabs)));
    }

    function hasOtherOpenTabs() {
        return Object.keys(cleanOpenTabs(openTabs())).length > 0;
    }

    function openTabs() {
        try {
            return JSON.parse(localStorage.getItem(openTabsKey) || '{}');
        } catch (error) {
            return {};
        }
    }

    function cleanOpenTabs(tabs) {
        const aliveAfter = Date.now() - heartbeatMs * 3;

        return Object.fromEntries(
            Object.entries(tabs).filter(function ([, lastSeen]) {
                return Number(lastSeen) >= aliveAfter;
            })
        );
    }

    function waitForEcho(callback, tries = 0) {
        if (window.Echo) {
            callback();
            return;
        }

        if (tries > 40) {
            return;
        }

        setTimeout(function () {
            waitForEcho(callback, tries + 1);
        }, 250);
    }

    function updateActivityDots(activity) {
        if (String(activity.user_id) === String(currentUserId)) {
            activity = {
                user_id: activity.user_id,
                visible: false,
                status: 'hidden',
                last_activity_at: activity.last_activity_at || '',
                label: '',
                short_label: ''
            };
        }

        document
            .querySelectorAll(`[data-activity-user-id="${activity.user_id}"]`)
            .forEach(function (dot) {
                applyActivityToDot(dot, activity);
            });
    }

    function applyActivityToDot(dot, activity) {
        dot.classList.remove('online', 'away', 'hidden', 'd-none');
        dot.classList.add(activity.status || 'hidden');

        if (!activity.visible || activity.status === 'hidden') {
            dot.classList.add('d-none');
        }

        dot.dataset.activityStatus = activity.status || 'hidden';
        dot.dataset.lastActivityAt = activity.last_activity_at || '';
        dot.dataset.shortLabel = activity.short_label || '';
        dot.setAttribute('title', activity.label || '');
    }

    function refreshActivityDots() {
        document.querySelectorAll('[data-activity-user-id]').forEach(function (dot) {
            const lastActivityAt = dot.dataset.lastActivityAt;

            if (!lastActivityAt) {
                return;
            }

            const elapsedMs = Date.now() - new Date(lastActivityAt).getTime();

            if (elapsedMs > maxAwayMs) {
                applyActivityToDot(dot, {
                    user_id: dot.dataset.activityUserId,
                    visible: false,
                    status: 'hidden',
                    last_activity_at: lastActivityAt,
                    label: '',
                    short_label: ''
                });
                return;
            }

            if (elapsedMs > onlineGraceMs) {
                const shortLabel = shortActivityTime(elapsedMs);

                applyActivityToDot(dot, {
                    user_id: dot.dataset.activityUserId,
                    visible: true,
                    status: 'away',
                    last_activity_at: lastActivityAt,
                    label: `Hoạt động ${shortLabel} trước`,
                    short_label: shortLabel
                });
            }
        });
    }

    function shortActivityTime(elapsedMs) {
        const seconds = Math.floor(elapsedMs / 1000);

        if (seconds < 60) {
            return 'vừa xong';
        }

        if (seconds < 3600) {
            return `${Math.floor(seconds / 60)}p`;
        }

        return `${Math.floor(seconds / 3600)}h`;
    }
});
