
document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.navbar');
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', function () {
            this.classList.toggle('active');
            navLinks.classList.toggle('active');

            const spans = this.querySelectorAll('span');
            if (this.classList.contains('active')) {
                spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                spans[1].style.opacity = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            }
        });

        const links = navLinks.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function () {
                mobileToggle.classList.remove('active');
                navLinks.classList.remove('active');
                const spans = mobileToggle.querySelectorAll('span');
                spans[0].style.transform = 'none';
                spans[1].style.opacity = '1';
                spans[2].style.transform = 'none';
            });
        });
    }
});

window.addEventListener('scroll', function () {
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#') return;

            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {

    const scrollBtn = document.createElement('div');
    scrollBtn.className = 'scroll-to-top';
    scrollBtn.innerHTML = '↑';
    scrollBtn.title = 'Back to Top';
    document.body.appendChild(scrollBtn);

    window.addEventListener('scroll', function () {
        if (window.scrollY > 300) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });

    scrollBtn.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const animatedElements = document.querySelectorAll('.facility-card, .room-card');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
});

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = '#f44336';
            input.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        } else {
            input.style.borderColor = '#4CAF50';
            input.style.boxShadow = '0 0 0 3px rgba(76, 175, 80, 0.1)';
        }
    });

    return isValid;
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePhone(phone) {
    const re = /^[0-9]{10}$/;
    return re.test(phone);
}

function validateContactForm() {
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const message = document.getElementById('message');
    let isValid = true;
    let errors = [];

    [name, email, phone, message].forEach(field => {
        if (field) {
            field.style.borderColor = '#e0e0e0';
            field.style.boxShadow = 'none';
        }
    });

    if (!name.value.trim()) {
        name.style.borderColor = '#f44336';
        name.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        errors.push('Name is required');
        isValid = false;
    }

    if (!email.value.trim() || !validateEmail(email.value)) {
        email.style.borderColor = '#f44336';
        email.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        errors.push('Valid email is required');
        isValid = false;
    }

    if (phone.value.trim() && !validatePhone(phone.value)) {
        phone.style.borderColor = '#f44336';
        phone.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        errors.push('Phone must be 10 digits');
        isValid = false;
    }

    if (!message.value.trim()) {
        message.style.borderColor = '#f44336';
        message.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        errors.push('Message is required');
        isValid = false;
    }

    if (!isValid) {
        showNotification('Please correct the errors: ' + errors.join(', '), 'error');
    }

    return isValid;
}

function validateRegistration() {
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const guardianPhone = document.getElementById('guardian_phone');
    let isValid = true;

    if (!validateEmail(email.value)) {
        email.style.borderColor = '#f44336';
        email.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        showNotification('Please enter a valid email address.', 'error');
        return false;
    }

    if (!validatePhone(phone.value)) {
        phone.style.borderColor = '#f44336';
        phone.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        showNotification('Please enter a valid 10-digit phone number.', 'error');
        return false;
    }

    if (!validatePhone(guardianPhone.value)) {
        guardianPhone.style.borderColor = '#f44336';
        guardianPhone.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        showNotification('Please enter a valid 10-digit guardian phone number.', 'error');
        return false;
    }

    if (password.value.length < 6) {
        password.style.borderColor = '#f44336';
        password.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        showNotification('Password must be at least 6 characters long.', 'error');
        return false;
    }

    if (password.value !== confirmPassword.value) {
        confirmPassword.style.borderColor = '#f44336';
        confirmPassword.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        showNotification('Passwords do not match.', 'error');
        return false;
    }

    return validateForm('registerForm');
}

function validateLogin() {
    const email = document.getElementById('email');
    const password = document.getElementById('password');

    if (!email.value.trim() || !password.value.trim()) {
        showNotification('Please fill all fields.', 'error');
        return false;
    }

    if (!validateEmail(email.value)) {
        showNotification('Please enter a valid email address.', 'error');
        return false;
    }

    return true;
}

function validateRoomApplication() {
    return validateForm('roomApplicationForm');
}

function validateComplaint() {
    return validateForm('complaintForm');
}

function confirmAction(message) {
    return confirm(message);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '10000';
    notification.style.minWidth = '300px';
    notification.style.maxWidth = '500px';
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.transition = 'opacity 0.5s, transform 0.5s';
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (!alert.style.position || alert.style.position !== 'fixed') {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const inputs = document.querySelectorAll('input[type="email"], input[type="tel"]');

    inputs.forEach(input => {
        input.addEventListener('blur', function () {
            if (this.value.trim()) {
                if (this.type === 'email') {
                    if (validateEmail(this.value)) {
                        this.style.borderColor = '#4CAF50';
                        this.style.boxShadow = '0 0 0 3px rgba(76, 175, 80, 0.1)';
                    } else {
                        this.style.borderColor = '#f44336';
                        this.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
                    }
                } else if (this.type === 'tel') {
                    if (validatePhone(this.value)) {
                        this.style.borderColor = '#4CAF50';
                        this.style.boxShadow = '0 0 0 3px rgba(76, 175, 80, 0.1)';
                    } else {
                        this.style.borderColor = '#f44336';
                        this.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
                    }
                }
            }
        });

        input.addEventListener('focus', function () {
            this.style.borderColor = '#2196F3';
            this.style.boxShadow = '0 0 0 3px rgba(33, 150, 243, 0.1)';
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function (e) {
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.6)';
            ripple.style.width = ripple.style.height = '100px';
            ripple.style.left = e.clientX - this.offsetLeft - 50 + 'px';
            ripple.style.top = e.clientY - this.offsetTop - 50 + 'px';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.pointerEvents = 'none';

            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);

            setTimeout(() => ripple.remove(), 600);
        });
    });
});
