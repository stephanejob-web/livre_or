/**
 * Script JavaScript pour le projet Livre d'Or
 */

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {

    // Masquer automatiquement les messages flash après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Confirmation avant déconnexion
    const logoutLinks = document.querySelectorAll('a[href*="deconnexion.php"]');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                e.preventDefault();
            }
        });
    });

    // Validation du formulaire d'inscription
    const inscriptionForm = document.querySelector('form[action*="inscription"]');
    if (inscriptionForm) {
        inscriptionForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères !');
            }
        });
    }

    // Animation des cartes au scroll
    const cards = document.querySelectorAll('.card, .comment-card');

    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s, transform 0.5s';
        observer.observe(card);
    });

    // Compteur de caractères pour le textarea
    const commentTextarea = document.querySelector('textarea[name="commentaire"]');
    if (commentTextarea) {
        const counterDiv = document.createElement('div');
        counterDiv.style.textAlign = 'right';
        counterDiv.style.fontSize = '0.875rem';
        counterDiv.style.color = '#6c757d';
        counterDiv.style.marginTop = '0.25rem';

        commentTextarea.parentNode.appendChild(counterDiv);

        function updateCounter() {
            const length = commentTextarea.value.length;
            counterDiv.textContent = `${length} caractères`;

            if (length < 10) {
                counterDiv.style.color = '#dc3545';
            } else {
                counterDiv.style.color = '#28a745';
            }
        }

        commentTextarea.addEventListener('input', updateCounter);
        updateCounter();
    }
});
