document.addEventListener('click', function(e) {
    const submitBtn = e.target.closest('button.brevo-stock-notification-submit');
    if (!submitBtn) {
        return;
    }

    const container = submitBtn.closest('.sendinblue-back-in-stock-container');
    if (!container) {
        return;
    }

    const productId = container.getAttribute('data-product-id');
    const backinstockUrl = container.getAttribute('data-backinstock-url');

    const formWrapper = container.querySelector('.sendinblue-form');
    const emailInput = container.querySelector('input[name="email"]');
    const hiddenProductId = container.querySelector('input[name="product_id"]');
    const hiddenAction = container.querySelector('input[name="action"]');
    const messageDiv = container.querySelector('[id^="sendinblue-message-"]');

    if (!formWrapper || !emailInput) {
        return;
    }

    const email = (emailInput.value || '').trim();
    if (!email) {
        if (messageDiv) {
            messageDiv.style.display = 'block';
            messageDiv.innerHTML = '<div class="alert alert-danger">Please enter a valid email address.</div>';
        }
        return;
    }

    if (submitBtn.disabled) {
        return;
    }

    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Subscribing...';

    if (messageDiv) {
        messageDiv.style.display = 'none';
    }

    const formData = new FormData();
    formData.append('product_id', hiddenProductId ? hiddenProductId.value : productId);
    formData.append('action', hiddenAction ? hiddenAction.value : 'subscribe');
    formData.append('email', email);

    // Send AJAX request
    fetch(backinstockUrl, {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (messageDiv) {
            messageDiv.style.display = 'block';
            if (data && data.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">' + (data.message || 'Subscribed successfully!') + '</div>';
                formWrapper.style.display = 'none';
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger">' + (data && data.message ? data.message : 'Failed to subscribe. Please try again.') + '</div>';
            }
        }
    })
    .catch(() => {
        if (messageDiv) {
            messageDiv.style.display = 'block';
            messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        }
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});
