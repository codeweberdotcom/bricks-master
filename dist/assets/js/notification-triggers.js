/**
 * Notification Triggers Handler
 * Handles all trigger types for notification modals
 */

document.addEventListener("DOMContentLoaded", function() {
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:7',message:'Script loaded, DOMContentLoaded fired',data:{timestamp:Date.now()},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    
    const modalElement = document.getElementById("notification-modal");
    
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:11',message:'Modal element check',data:{modalElementExists:!!modalElement,modalElementId:modalElement?modalElement.id:'null'},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
    // #endregion
    
    if (!modalElement) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:15',message:'EXIT: Modal element not found',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'A'})}).catch(()=>{});
        // #endregion
        return;
    }
    
    // Get trigger data from modal element
    const triggerType = modalElement.getAttribute('data-trigger-type');
    const triggerInactivity = modalElement.getAttribute('data-trigger-inactivity');
    const triggerViewport = modalElement.getAttribute('data-trigger-viewport');
    const waitDelay = modalElement.getAttribute('data-wait') || 200;
    
    // Check if modal has notification content (modal-popup class)
    const isNotification = modalElement.classList.contains('modal-popup');
    
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:28',message:'Trigger data extracted',data:{triggerType:triggerType,triggerInactivity:triggerInactivity,triggerViewport:triggerViewport,waitDelay:waitDelay,isNotification:isNotification,allAttributes:Array.from(modalElement.attributes).map(a=>a.name+':'+a.value).join(',')},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'B'})}).catch(()=>{});
    // #endregion
    
    if (!isNotification || !triggerType) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:31',message:'EXIT: Not notification or no trigger type',data:{isNotification:isNotification,triggerType:triggerType},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'C'})}).catch(()=>{});
        // #endregion
        return;
    }
    
    // Initialize Bootstrap modal
    let modal;
    try {
        modal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true
        });
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:40',message:'Bootstrap Modal initialized',data:{bootstrapExists:typeof bootstrap!=='undefined',modalCreated:!!modal},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
        // #endregion
    } catch (e) {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:44',message:'ERROR: Bootstrap Modal init failed',data:{error:e.message,stack:e.stack},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'D'})}).catch(()=>{});
        // #endregion
        return;
    }
    
    let triggerHandled = false;
    let triggerInitFunctions = []; // Store trigger initialization functions for re-initialization
    
    /**
     * Check if a higher priority modal is currently open
     * Priority order:
     * 1. Cookie modal (id="cookieModal") - highest priority
     * 2. Other modals (id="modal", id="newsletter-success-modal", id="codeweber-form-success-modal") - medium priority
     * 3. Notification modal (id="notification-modal") - lowest priority
     * 
     * @returns {boolean} true if higher priority modal is open, false otherwise
     */
    function isHigherPriorityModalOpen() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return false;
        }
        
        // Check cookie modal (priority 1)
        const cookieModal = document.getElementById('cookieModal');
        if (cookieModal && cookieModal.classList.contains('show')) {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:isHigherPriorityModalOpen',message:'Cookie modal is open (priority 1)',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            return true;
        }
        
        // Check other modals (priority 2)
        const otherModals = [
            document.getElementById('modal'),
            document.getElementById('newsletter-success-modal'),
            document.getElementById('codeweber-form-success-modal')
        ];
        
        for (let otherModal of otherModals) {
            if (otherModal && otherModal.classList.contains('show')) {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:isHigherPriorityModalOpen',message:'Other modal is open (priority 2)',data:{modalId:otherModal.id},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
                return true;
            }
        }
        
        return false;
    }
    
    // Function to show modal
    function showNotificationModal() {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:showNotificationModal',message:'showNotificationModal called',data:{triggerHandled:triggerHandled,waitDelay:waitDelay},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        if (triggerHandled) {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:showNotificationModal',message:'EXIT: Trigger already handled',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            return;
        }
        
        // Check if higher priority modal is open
        if (isHigherPriorityModalOpen()) {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:showNotificationModal',message:'EXIT: Higher priority modal is open',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            return;
        }
        
        triggerHandled = true;
        
        setTimeout(function() {
            // Check again before showing (in case another modal opened during delay)
            if (isHigherPriorityModalOpen()) {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:showNotificationModal',message:'EXIT: Higher priority modal opened during delay',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
                triggerHandled = false; // Reset to allow retry later
                return;
            }
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:showNotificationModal',message:'Calling modal.show()',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            modal.show();
        }, parseInt(waitDelay));
    }
    
    /**
     * Initialize notification triggers based on trigger type
     * This function can be called multiple times (e.g., after cookie modal closes)
     */
    function initNotificationTriggers() {
        // Check if trigger was already handled and modal was shown
        if (triggerHandled && modalElement.classList.contains('show')) {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:initNotificationTriggers',message:'Trigger already handled and modal shown, skipping re-init',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            return;
        }
        
        // Check if higher priority modal is open
        if (isHigherPriorityModalOpen()) {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:initNotificationTriggers',message:'Higher priority modal is open, deferring trigger init',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            return;
        }
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:initNotificationTriggers',message:'Initializing triggers',data:{triggerType:triggerType,triggerHandled:triggerHandled},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        // Trigger: delay (default)
        if (triggerType === 'delay') {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:initNotificationTriggers:delay',message:'Trigger type: delay - executing',data:{triggerHandled:triggerHandled},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            showNotificationModal();
        }
    
    // Trigger: inactivity
    else if (triggerType === 'inactivity') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:78',message:'Trigger type: inactivity - setting up',data:{triggerInactivity:triggerInactivity},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        let inactivityTimer;
        const inactivityDelay = triggerInactivity ? parseInt(triggerInactivity) : 30000;
        let lastActivity = Date.now();
        
        function resetTimer() {
            lastActivity = Date.now();
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(function() {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:88',message:'Inactivity timer fired',data:{timeSinceLastActivity:Date.now()-lastActivity,inactivityDelay:inactivityDelay},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
                if (Date.now() - lastActivity >= inactivityDelay) {
                    showNotificationModal();
                }
            }, inactivityDelay);
        }
        
        // Track user activity
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(function(event) {
            document.addEventListener(event, resetTimer, true);
        });
        
        // Start timer
        resetTimer();
    }
    
    // Trigger: viewport
    else if (triggerType === 'viewport') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:108',message:'Trigger type: viewport - setting up',data:{triggerViewport:triggerViewport},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        if (triggerViewport) {
            const elementId = triggerViewport.replace('#', '');
            const targetElement = document.getElementById(elementId) || document.querySelector(triggerViewport);
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:112',message:'Viewport element search',data:{elementId:elementId,targetElementFound:!!targetElement},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            
            if (targetElement) {
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        // #region agent log
                        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:118',message:'IntersectionObserver callback',data:{isIntersecting:entry.isIntersecting,intersectionRatio:entry.intersectionRatio},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                        // #endregion
                        if (entry.isIntersecting) {
                            showNotificationModal();
                            observer.disconnect();
                        }
                    });
                }, {
                    threshold: 0.1
                });
                
                observer.observe(targetElement);
            }
        }
    }
    
    // Trigger: scroll to middle
    else if (triggerType === 'scroll_middle') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:133',message:'Trigger type: scroll_middle - setting up',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        let scrollMiddleTriggered = false;
        let scrollEventCount = 0;
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:138',message:'Scroll listener attached',data:{documentHeight:document.documentElement.scrollHeight,windowHeight:window.innerHeight,bodyHeight:document.body.scrollHeight},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        // Check initial scroll position
        function checkScrollPosition() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollPercent = (scrollTop + windowHeight) / documentHeight;
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:148',message:'Initial scroll check (middle)',data:{scrollTop:scrollTop,scrollPercent:scrollPercent.toFixed(3),documentHeight:documentHeight,windowHeight:windowHeight,threshold:0.5},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            
            if (scrollPercent >= 0.5 && !scrollMiddleTriggered) {
                scrollMiddleTriggered = true;
                showNotificationModal();
            }
        }
        
        // Check immediately and after a short delay (in case page loads scrolled)
        checkScrollPosition();
        setTimeout(checkScrollPosition, 100);
        setTimeout(checkScrollPosition, 500);
        
        window.addEventListener('scroll', function() {
            scrollEventCount++;
            
            // #region agent log
            if (scrollEventCount <= 5 || scrollEventCount % 10 === 0) {
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:145',message:'Scroll event fired',data:{scrollEventCount:scrollEventCount,scrollMiddleTriggered:scrollMiddleTriggered},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            }
            // #endregion
            
            if (scrollMiddleTriggered) {
                return;
            }
            
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollPercent = (scrollTop + windowHeight) / documentHeight;
            
            // #region agent log
            if (scrollEventCount <= 5 || (scrollPercent > 0.3 && scrollPercent < 0.7)) {
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:158',message:'Scroll event - checking middle',data:{scrollPercent:scrollPercent.toFixed(3),scrollTop:scrollTop,documentHeight:documentHeight,windowHeight:windowHeight,threshold:0.5},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            }
            // #endregion
            
            if (scrollPercent >= 0.5) {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:166',message:'Scroll middle threshold reached',data:{scrollPercent:scrollPercent.toFixed(3)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
                scrollMiddleTriggered = true;
                showNotificationModal();
            }
        }, { passive: true });
    }
    
    // Trigger: scroll to end
    else if (triggerType === 'scroll_end') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:160',message:'Trigger type: scroll_end - setting up',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        let scrollEndTriggered = false;
        let scrollEventCount = 0;
        
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:165',message:'Scroll listener attached',data:{documentHeight:document.documentElement.scrollHeight,windowHeight:window.innerHeight,bodyHeight:document.body.scrollHeight},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        // Check initial scroll position
        function checkScrollPosition() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollPercent = (scrollTop + windowHeight) / documentHeight;
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:173',message:'Initial scroll check',data:{scrollTop:scrollTop,scrollPercent:scrollPercent.toFixed(3),documentHeight:documentHeight,windowHeight:windowHeight,threshold:0.95},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            
            if (scrollPercent >= 0.95 && !scrollEndTriggered) {
                scrollEndTriggered = true;
                showNotificationModal();
            }
        }
        
        // Check immediately and after a short delay (in case page loads scrolled)
        checkScrollPosition();
        setTimeout(checkScrollPosition, 100);
        setTimeout(checkScrollPosition, 500);
        
        window.addEventListener('scroll', function() {
            scrollEventCount++;
            
            // #region agent log
            if (scrollEventCount <= 5 || scrollEventCount % 10 === 0) {
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:172',message:'Scroll event fired',data:{scrollEventCount:scrollEventCount,scrollEndTriggered:scrollEndTriggered},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            }
            // #endregion
            
            if (scrollEndTriggered) {
                return;
            }
            
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const windowHeight = window.innerHeight;
            const documentHeight = document.documentElement.scrollHeight;
            const scrollPercent = (scrollTop + windowHeight) / documentHeight;
            
            // #region agent log
            if (scrollEventCount <= 5 || scrollPercent > 0.5) {
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:185',message:'Scroll event - checking end',data:{scrollPercent:scrollPercent.toFixed(3),scrollTop:scrollTop,documentHeight:documentHeight,windowHeight:windowHeight,threshold:0.95},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            }
            // #endregion
            
            if (scrollPercent >= 0.95) {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:191',message:'Scroll end threshold reached',data:{scrollPercent:scrollPercent.toFixed(3)},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
                scrollEndTriggered = true;
                showNotificationModal();
            }
        }, { passive: true });
    }
    
    // Trigger: Codeweber form success
    else if (triggerType === 'codeweber_form') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:188',message:'Trigger type: codeweber_form - setting up listener',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        // Listen for custom event from Codeweber forms
        document.addEventListener('codeweber_form_success', function() {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:192',message:'codeweber_form_success event fired',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            showNotificationModal();
        });
    }
    
    // Trigger: CF7 form success
    else if (triggerType === 'cf7_form') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:200',message:'Trigger type: cf7_form - setting up listeners',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        // Listen for CF7 form submission success
        document.addEventListener('wpcf7mailsent', function() {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:205',message:'wpcf7mailsent event fired',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            showNotificationModal();
        });
        
        // Also listen for custom event if CF7 uses it
        document.addEventListener('cf7_form_success', function() {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:211',message:'cf7_form_success event fired',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            showNotificationModal();
        });
    }
    
    // Trigger: WooCommerce order success
    else if (triggerType === 'woocommerce_order') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:220',message:'Trigger type: woocommerce_order - checking',data:{hasOrderReceivedClass:document.body.classList.contains('woocommerce-order-received')},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        
        // Check if we're on order received page
        if (document.body.classList.contains('woocommerce-order-received')) {
            showNotificationModal();
        }
        
        // Also listen for custom event
        document.addEventListener('woocommerce_order_success', function() {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:228',message:'woocommerce_order_success event fired',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            showNotificationModal();
        });
    }
    
    // Trigger: page/post/archive (handled by PHP, just show modal)
    else if (triggerType === 'page') {
        // #region agent log
        fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:235',message:'Trigger type: page - executing',data:{},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
        // #endregion
        showNotificationModal();
    }
        else {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:initNotificationTriggers',message:'Unknown trigger type',data:{triggerType:triggerType},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
        }
    }
    
    // Initial trigger initialization
    // #region agent log
    fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:main',message:'Checking trigger type',data:{triggerType:triggerType},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
    // #endregion
    
    initNotificationTriggers();
    
    /**
     * Function to handle modal close and re-initialize notification triggers
     */
    function handleModalCloseAndReinit(modalId, modalName) {
        return function(e) {
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:handleModalCloseAndReinit',message:modalName + ' closed, re-initializing notification triggers',data:{modalId:modalId,triggerHandled:triggerHandled,notificationModalShown:modalElement.classList.contains('show')},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            
            // Always reset triggerHandled when success modal closes (to allow notification to show)
            // This is important because notification might have been blocked by the success modal
            triggerHandled = false;
            
            // #region agent log
            fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:handleModalCloseAndReinit',message:'Reset triggerHandled, will re-initialize',data:{modalId:modalId,triggerType:triggerType},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
            // #endregion
            
            // Re-initialize triggers after a short delay to ensure modal is fully closed
            setTimeout(function() {
                // #region agent log
                fetch('http://127.0.0.1:7242/ingest/49b89e88-4674-4191-9133-bf7fd16c00a5',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'notification-triggers.js:handleModalCloseAndReinit',message:'Calling initNotificationTriggers after delay',data:{modalId:modalId,triggerHandled:triggerHandled},timestamp:Date.now(),sessionId:'debug-session',runId:'run1',hypothesisId:'E'})}).catch(()=>{});
                // #endregion
                initNotificationTriggers();
            }, 200); // Increased delay to 200ms to ensure modal is fully closed
        };
    }
    
    // Listen for cookie modal close event to re-initialize triggers
    const cookieModal = document.getElementById('cookieModal');
    if (cookieModal) {
        cookieModal.addEventListener('hidden.bs.modal', handleModalCloseAndReinit('cookieModal', 'Cookie modal'));
    }
    
    // Listen for CF7 success modal close event (uses id="modal")
    const cf7SuccessModal = document.getElementById('modal');
    if (cf7SuccessModal) {
        // Use a flag to track if this is CF7 success modal
        // We'll check if modal was opened by CF7 by listening to custom event or checking content
        cf7SuccessModal.addEventListener('hidden.bs.modal', function(e) {
            // Check if this is CF7 success modal by checking if it has CF7-specific content or class
            // We'll re-init if modal was shown (it could be CF7 or REST API modal)
            // But we want to re-init notification triggers after any modal closes
            handleModalCloseAndReinit('modal', 'CF7/REST API modal')(e);
        });
    }
    
    // Listen for newsletter success modal close event
    const newsletterSuccessModal = document.getElementById('newsletter-success-modal');
    if (newsletterSuccessModal) {
        newsletterSuccessModal.addEventListener('hidden.bs.modal', handleModalCloseAndReinit('newsletter-success-modal', 'Newsletter success modal'));
    }
    
    // Listen for codeweber form success modal close event
    const codeweberFormSuccessModal = document.getElementById('codeweber-form-success-modal');
    if (codeweberFormSuccessModal) {
        codeweberFormSuccessModal.addEventListener('hidden.bs.modal', handleModalCloseAndReinit('codeweber-form-success-modal', 'Codeweber form success modal'));
    }
    
});


