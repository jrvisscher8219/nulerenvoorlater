/**
 * Comments System - Frontend JavaScript
 * =======================================
 * Handles comment form submission and loading comments
 */

(function() {
    'use strict';
    
    // Configuration
    const API_BASE = '/api';
    const COMMENT_MIN_LENGTH = 10;
    const COMMENT_MAX_LENGTH = 1000;
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        const commentForm = document.getElementById('comment-form');
        const commentsList = document.getElementById('comments-list');
        
        if (!commentForm) {
            console.warn('Comment form not found');
            return;
        }
        
        // Get blog ID from form
        const blogId = commentForm.dataset.blogId;
        if (!blogId) {
            console.error('Blog ID not found');
            return;
        }
        
        // Setup form
        setupCommentForm(commentForm, blogId);
        
        // Load existing comments
        if (commentsList) {
            loadComments(blogId, commentsList);
        }
        
        // Character counter
        setupCharacterCounter(commentForm);
    }
    
    /**
     * Setup Comment Form
     */
    function setupCommentForm(form, blogId) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const messageDiv = document.getElementById('form-message');
            
            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verzenden...';
            
            // Clear previous messages
            if (messageDiv) {
                messageDiv.innerHTML = '';
                messageDiv.className = 'form-message';
            }
            
            // Get form data
            const formData = {
                blog_id: blogId,
                name: form.querySelector('[name="name"]').value.trim(),
                email: form.querySelector('[name="email"]').value.trim(),
                comment: form.querySelector('[name="comment"]').value.trim(),
                csrf_token: form.querySelector('[name="csrf_token"]').value,
                [form.querySelector('[name="honeypot"]').name]: form.querySelector('[name="honeypot"]').value
            };
            
            // Client-side validation
            const errors = validateComment(formData);
            if (errors.length > 0) {
                showErrors(errors, messageDiv);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Plaats reactie';
                return;
            }
            
            try {
                // Submit comment
                const response = await fetch(API_BASE + '/submit-comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    showMessage(result.message, 'success', messageDiv);
                    
                    // Reset form
                    form.reset();
                    updateCharCounter(form);
                    
                    // Reload comments if approved
                    if (result.status === 'approved') {
                        setTimeout(() => {
                            loadComments(blogId, document.getElementById('comments-list'));
                        }, 1000);
                    }
                } else {
                    // Show error
                    if (result.errors && result.errors.length > 0) {
                        showErrors(result.errors, messageDiv);
                    } else {
                        showMessage(result.message || 'Er ging iets mis. Probeer opnieuw.', 'error', messageDiv);
                    }
                }
                
            } catch (error) {
                console.error('Submit error:', error);
                showMessage('Netwerkfout. Controleer je verbinding en probeer opnieuw.', 'error', messageDiv);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Plaats reactie';
            }
        });
    }
    
    /**
     * Load Comments
     */
    async function loadComments(blogId, container) {
        if (!container) return;
        
        container.innerHTML = '<div class="loading">Reacties laden</div>';
        
        try {
            const response = await fetch(`${API_BASE}/get-comments.php?blog_id=${encodeURIComponent(blogId)}`);
            const result = await response.json();
            
            if (result.success) {
                displayComments(result.comments, container);
                updateCommentsCount(result.count);
            } else {
                container.innerHTML = '<p class="error">Fout bij laden van reacties.</p>';
            }
        } catch (error) {
            console.error('Load error:', error);
            container.innerHTML = '<p class="error">Fout bij laden van reacties.</p>';
        }
    }
    
    /**
     * Display Comments
     */
    function displayComments(comments, container) {
        if (!comments || comments.length === 0) {
            container.innerHTML = '<div class="no-comments">Nog geen reacties. Wees de eerste!</div>';
            return;
        }
        
        const html = comments.map(comment => `
            <div class="comment-item">
                <div class="comment-header">
                    <span class="comment-author">${escapeHtml(comment.author_name)}</span>
                    <span class="comment-date" title="${escapeHtml(comment.created_at_formatted)}">${escapeHtml(comment.time_ago)}</span>
                </div>
                <div class="comment-text">${escapeHtml(comment.comment_text)}</div>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }
    
    /**
     * Update Comments Count
     */
    function updateCommentsCount(count) {
        const countEl = document.getElementById('comments-count');
        if (countEl) {
            countEl.textContent = `${count} ${count === 1 ? 'reactie' : 'reacties'}`;
        }
    }
    
    /**
     * Validate Comment
     */
    function validateComment(data) {
        const errors = [];
        
        if (!data.name || data.name.length < 2) {
            errors.push('Naam moet minimaal 2 tekens zijn.');
        }
        
        if (!data.email || !isValidEmail(data.email)) {
            errors.push('Ongeldig e-mailadres.');
        }
        
        if (!data.comment) {
            errors.push('Reactie mag niet leeg zijn.');
        } else if (data.comment.length < COMMENT_MIN_LENGTH) {
            errors.push(`Reactie moet minimaal ${COMMENT_MIN_LENGTH} tekens zijn.`);
        } else if (data.comment.length > COMMENT_MAX_LENGTH) {
            errors.push(`Reactie mag maximaal ${COMMENT_MAX_LENGTH} tekens zijn.`);
        }
        
        return errors;
    }
    
    /**
     * Validate Email
     */
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    /**
     * Show Message
     */
    function showMessage(message, type, container) {
        if (!container) return;
        
        container.className = `form-message ${type}`;
        container.textContent = message;
        container.style.display = 'block';
        
        // Scroll to message
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    /**
     * Show Errors
     */
    function showErrors(errors, container) {
        if (!container) return;
        
        const html = `
            <div class="error-list">
                <strong>Controleer het volgende:</strong>
                <ul>
                    ${errors.map(err => `<li>${escapeHtml(err)}</li>`).join('')}
                </ul>
            </div>
        `;
        
        container.innerHTML = html;
        container.className = 'form-message';
        container.style.display = 'block';
        
        // Scroll to errors
        container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    /**
     * Setup Character Counter
     */
    function setupCharacterCounter(form) {
        const textarea = form.querySelector('[name="comment"]');
        const counter = document.getElementById('char-counter');
        
        if (!textarea || !counter) return;
        
        textarea.addEventListener('input', function() {
            updateCharCounter(form);
        });
        
        // Initial update
        updateCharCounter(form);
    }
    
    /**
     * Update Character Counter
     */
    function updateCharCounter(form) {
        const textarea = form.querySelector('[name="comment"]');
        const counter = document.getElementById('char-counter');
        
        if (!textarea || !counter) return;
        
        const length = textarea.value.length;
        const remaining = COMMENT_MAX_LENGTH - length;
        
        counter.textContent = `${length} / ${COMMENT_MAX_LENGTH} tekens`;
        
        if (remaining < 50) {
            counter.classList.add('warning');
        } else {
            counter.classList.remove('warning');
        }
        
        if (remaining < 0) {
            counter.classList.add('error');
        } else {
            counter.classList.remove('error');
        }
    }
    
    /**
     * Escape HTML (prevent XSS)
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
})();
