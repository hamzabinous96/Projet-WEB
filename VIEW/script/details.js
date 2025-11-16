// JavaScript pour la gestion des interactions de la page de détails du projet
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la participation
    const participateBtn = document.getElementById('participateBtn');
    const taskModal = document.getElementById('taskModal');
    const closeTaskModal = document.getElementById('closeTaskModal');
    const cancelTaskSelection = document.getElementById('cancelTaskSelection');
    const confirmParticipation = document.getElementById('confirmParticipation');
    const successModal = document.getElementById('successModal');
    const closeSuccessModal = document.getElementById('closeSuccessModal');
    const saveProjectBtn = document.getElementById('saveProjectBtn');

    // Récupération des données du projet depuis les éléments HTML
    const projectId = document.querySelector('.project-details-container').dataset.projectId;
    const projectTitle = document.querySelector('.project-details-container').dataset.projectTitle;
    const projectAssociation = document.querySelector('.project-details-container').dataset.projectAssociation;
    const projectLocation = document.querySelector('.project-details-container').dataset.projectLocation;
    const projectAvailability = document.querySelector('.project-details-container').dataset.projectAvailability;

    // Ouvrir le modal de sélection des tâches
    if (participateBtn) {
        participateBtn.addEventListener('click', function() {
            taskModal.style.display = 'block';
        });
    }

    // Fermer le modal des tâches
    if (closeTaskModal) {
        closeTaskModal.addEventListener('click', function() {
            taskModal.style.display = 'none';
        });
    }

    if (cancelTaskSelection) {
        cancelTaskSelection.addEventListener('click', function() {
            taskModal.style.display = 'none';
        });
    }

    // Confirmer la participation
    if (confirmParticipation) {
        confirmParticipation.addEventListener('click', function() {
            taskModal.style.display = 'none';
            successModal.style.display = 'block';
            
            // Mise à jour des détails dans le modal de succès
            const successTitle = successModal.querySelector('h3');
            const successProject = successModal.querySelector('.success-item:nth-child(1) span');
            const successAssociation = successModal.querySelector('.success-item:nth-child(2) span');
            const successLocation = successModal.querySelector('.success-item:nth-child(3) span');
            
            if (successTitle) successTitle.textContent = 'Participation confirmée !';
            if (successProject) successProject.textContent = projectTitle;
            if (successAssociation) successAssociation.textContent = projectAssociation;
            if (successLocation) successLocation.textContent = projectLocation;
            
            // Ici vous pouvez ajouter une requête AJAX pour enregistrer la participation
            console.log('Participation confirmée pour le projet:', projectId);
        });
    }

    // Fermer le modal de succès
    if (closeSuccessModal) {
        closeSuccessModal.addEventListener('click', function() {
            successModal.style.display = 'none';
        });
    }

    // Sauvegarder le projet
    if (saveProjectBtn) {
        saveProjectBtn.addEventListener('click', function() {
            this.classList.toggle('saved');
            if (this.classList.contains('saved')) {
                this.innerHTML = '<i class="fas fa-heart"></i> Projet sauvegardé';
                // AJAX pour sauvegarder
                console.log('Projet sauvegardé:', projectId);
            } else {
                this.innerHTML = '<i class="fas fa-heart"></i> Sauvegarder le projet';
                // AJAX pour retirer
                console.log('Projet retiré des favoris:', projectId);
            }
        });
    }

    // Gestion de la sélection des tâches
    const taskCheckboxes = document.querySelectorAll('.task-checkbox');
    const selectedTasks = document.getElementById('selectedTasks');

    if (taskCheckboxes.length > 0 && selectedTasks) {
        taskCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedTasks();
                updateConfirmButton();
            });
        });
    }

    function updateSelectedTasks() {
        const selected = Array.from(taskCheckboxes).filter(cb => cb.checked);
        
        if (selected.length === 0) {
            selectedTasks.innerHTML = '<p class="no-tasks">Aucune tâche sélectionnée</p>';
        } else {
            selectedTasks.innerHTML = selected.map(cb => {
                const label = cb.nextElementSibling;
                const title = label.querySelector('.task-title').textContent;
                return `<div class="selected-task">${title}</div>`;
            }).join('');
        }
    }

    function updateConfirmButton() {
        const selected = Array.from(taskCheckboxes).filter(cb => cb.checked);
        if (confirmParticipation) {
            confirmParticipation.disabled = selected.length === 0;
        }
    }

    // Fermer les modals en cliquant à l'extérieur
    window.addEventListener('click', function(event) {
        if (event.target === taskModal) {
            taskModal.style.display = 'none';
        }
        if (event.target === successModal) {
            successModal.style.display = 'none';
        }
    });

    // Animation d'apparition progressive
    const fadeElements = document.querySelectorAll('.fade-in');
    fadeElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    });

    setTimeout(() => {
        fadeElements.forEach(element => {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        });
    }, 100);

    // Animation du bouton pulse
    const pulseButton = document.querySelector('.pulse');
    if (pulseButton) {
        setInterval(() => {
            pulseButton.classList.toggle('pulse-active');
        }, 2000);
    }
});