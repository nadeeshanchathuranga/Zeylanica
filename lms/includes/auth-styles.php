<style>
.auth-left-panel {
    flex: 1;
    background: linear-gradient(135deg, #000022 0%, #0202C5 100%);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    padding: 3rem 2rem;
    color: white;
    position: relative;
}
.book-image {
    position: absolute;
    top: 41px;
    left: 0px;
    width: 125px;
    height: 50px;
    opacity: 1;
    z-index: 10;
}
.book-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.design2-image {
    position: absolute;
    top: 287px;
    left: 0px;
    height: 211.41166256289367px;
    opacity: 1;
    z-index: 10;
}
.design2-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.brand-content {
    text-align: center;
    flex: 0.2;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.logo {
    width: 100px;
    height: 100px;
    margin: 0 auto 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
}
.logo img {
    width: 100px;
    height: 100px;
    object-fit: contain;
}
.brand-content h1 {
    font-size: 1.5rem;
    font-weight: 300;
    margin-bottom: 1rem;
    line-height: 1.2;
}
.brand-content h2 {
    font-size: 1.5rem;
    font-weight: 500;
    margin-bottom: 2rem;
    opacity: 0.9;
}
.welcome-section h3 {
    font-size: 0.8rem;
    font-weight: 600;
    margin-bottom: 1rem;
}
.welcome-section p {
    font-size: 1rem;
    line-height: 1.6;
    opacity: 0.8;
    max-width: 400px;
    margin: 0 auto;
}
.lecture-image {
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    display: flex;
    justify-content: center;
}
.lecture-image img {
    width: 300px;
    object-fit: contain;
    opacity: 0.9;
}
.custom-icon {
    font-size: 6rem;
    margin-bottom: 2rem;
}

.right-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
}

.right-panel.scrollable {
    align-items: flex-start;
    overflow-y: auto;
}

.right-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('assets/images/login-bg.png') center/cover;
    opacity: 0.05;
    z-index: 0;
    pointer-events: none;
}

.auth-card {
    background: white;
    padding: 3rem;
    border-radius: 12px;
    width: 100%;
    max-width: 450px;
    position: relative;
    z-index: 10;
}

.auth-header {
    text-align: center;
    margin-bottom: 2rem;
}

.auth-header h2 {
    color: #111827;
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.auth-header p {
    color: #6B7280;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    color: #374151;
    font-weight: 500;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.input-wrapper {
    position: relative;
}

.input-wrapper input {
    width: 100%;
    padding: 12px 16px 12px 45px;
    border: 1px solid #D1D5DB;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.input-wrapper input:focus {
    outline: none;
    border-color: #4F46E5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
}

.input-wrapper .icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6B7280;
    font-size: 1.1rem;
    width: 16px;
    height: 16px;
}

.auth-btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.auth-btn.primary {
    background: #4F46E5;
    color: white;
}

.auth-btn.primary:hover {
    background: #4338CA;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.auth-btn.secondary {
    background: #6B7280;
    color: white;
}

.auth-btn.secondary:hover {
    background: #4B5563;
}

.auth-btn:disabled {
    background: #E5E7EB;
    color: #9CA3AF;
    cursor: not-allowed;
}

.error {
    background: #FEE2E2;
    color: #991B1B;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid #DC2626;
}

.success {
    background: #D1FAE5;
    color: #065F46;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid #10B981;
}

.info-section {
    background: #F9FAFB;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border-left: 4px solid #4F46E5;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid #E5E7EB;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #374151;
}

.info-value {
    color: #6B7280;
}

@media (max-width: 768px) {
    .auth-left-panel {
        min-height: 40vh;
        padding: 2rem 1rem;
    }
    .brand-content h1 {
        font-size: 1.3rem;
    }
    .lecture-image {
        display: none;
    }
    .book-image, .design2-image {
        display: none;
    }
    .right-panel {
        padding: 1rem;
    }
    .right-panel.scrollable {
        padding-top: 1rem;
    }
    .auth-card {
        padding: 2rem;
    }
}
</style>
