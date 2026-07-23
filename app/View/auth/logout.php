<div class="logout-container">
    <div class="logout-card card">
        <div class="logout-icon">
            <span>👋</span>
        </div>
        
        <div class="logout-content text-center">
            <h2 class="mb-3">See you soon!</h2>
            <p class="text-muted mb-4">You have been successfully logged out.</p>
            
            <div class="logout-animation">
                <div class="logout-circle"></div>
                <div class="logout-circle"></div>
                <div class="logout-circle"></div>
            </div>
            
            <p class="text-muted small mt-4">Redirecting to login page in <span id="countdown">3</span> seconds...</p>
        </div>
        
        <div class="logout-actions">
            <a href="<?= url('/home/login') ?>" class="btn btn-primary w-100">
                ➜ Back to Login
            </a>
        </div>
    </div>
</div>

<style>
    .logout-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        padding: var(--spacing-lg);
    }

    .logout-card {
        max-width: 400px;
        width: 100%;
        text-align: center;
        animation: slideUp 0.5s ease-out;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .logout-icon {
        font-size: 4rem;
        margin-bottom: var(--spacing-xl);
        animation: wave 1s ease-in-out infinite;
    }

    @keyframes wave {
        0%, 100% {
            transform: rotate(0deg);
        }
        25% {
            transform: rotate(-10deg);
        }
        75% {
            transform: rotate(10deg);
        }
    }

    .logout-content h2 {
        font-size: 2rem;
        font-weight: 900;
        color: var(--primary);
        margin-bottom: var(--spacing-md);
    }

    .logout-animation {
        display: flex;
        justify-content: center;
        gap: var(--spacing-md);
        margin: var(--spacing-xl) 0;
    }

    .logout-circle {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--primary);
        animation: bounce 1.4s infinite;
    }

    .logout-circle:nth-child(1) {
        animation-delay: -0.32s;
    }

    .logout-circle:nth-child(2) {
        animation-delay: -0.16s;
    }

    @keyframes bounce {
        0%, 80%, 100% {
            opacity: 0.3;
            transform: scale(0.8);
        }
        40% {
            opacity: 1;
            transform: scale(1);
        }
    }

    .logout-actions {
        margin-top: var(--spacing-2xl);
    }

    .logout-actions .btn {
        width: 100%;
        justify-content: center;
    }

    .small {
        font-size: 0.875rem;
    }

    #countdown {
        font-weight: 700;
        color: var(--primary);
    }

    @media (max-width: 576px) {
        .logout-container {
            padding: var(--spacing-md);
        }

        .logout-content h2 {
            font-size: 1.5rem;
        }

        .logout-icon {
            font-size: 3rem;
        }
    }
</style>

<script>
    // Auto-redirect countdown
    let seconds = 3;
    const countdownElement = document.getElementById('countdown');

    const countdownInterval = setInterval(() => {
        seconds--;
        if (countdownElement) {
            countdownElement.textContent = seconds;
        }

        if (seconds <= 0) {
            clearInterval(countdownInterval);
            window.location.href = '<?= url('/home/login') ?>';
        }
    }, 1000);

    // Allow manual redirect if user clicks button
    const buttons = document.querySelectorAll('.logout-actions .btn');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            clearInterval(countdownInterval);
        });
    });
</script>
