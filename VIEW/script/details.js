// Données des projets (6 projets)
const projects = {
  1: { 
    title: "Volunteer in Tunisia", 
    association: "Go Volunteer Africa", 
    location: "Tunis, Tunisie", 
    duration: "Semaine (journée et soirée)", 
    description: "Participez à des initiatives éducatives et communautaires dans les quartiers de Tunis. Vous aiderez à l’enseignement, à la formation des jeunes et à l’animation d’ateliers culturels.", 
    category: "solidarite" 
  },
  2: { 
    title: "Human Connection Volunteer", 
    association: "Acquaint", 
    location: "En ligne", 
    duration: "Flexible (participation à distance possible)", 
    description: "Rejoignez un programme d’échanges culturels pour aider à créer des liens humains à travers des conversations internationales et des activités linguistiques en ligne.", 
    category: "discussion" 
  },
  3: { 
    title: "Nettoyage de Plage", 
    association: "Bleue Tunisie", 
    location: "La Marsa, Tunis", 
    duration: "1 journée", 
    description: "Aidez-nous à préserver la beauté des plages tunisiennes en participant à une journée de nettoyage et de sensibilisation au respect de l’environnement.", 
    category: "environnement" 
  },
  4: { 
    title: "Éducation pour Tous", 
    association: "Teach4Tunisia", 
    location: "Kairouan", 
    duration: "Février - Juin 2026", 
    description: "Contribuez à l’éducation des enfants défavorisés en soutenant des programmes d’alphabétisation et de tutorat scolaire.", 
    category: "education" 
  },
  5: { 
    title: "Reforestation Locale", 
    association: "Green Future", 
    location: "Nabeul", 
    duration: "10 décembre 2025", 
    description: "Participez à une campagne de plantation d’arbres pour restaurer les zones dégradées et sensibiliser la communauté à l’écologie.", 
    category: "environnement" 
  },
  6: { 
    title: "Aide aux Animaux", 
    association: "SOS Animaux Tunisie", 
    location: "Ariana", 
    duration: "Week-end", 
    description: "Impliquez-vous dans la protection animale en aidant les refuges locaux à s’occuper des animaux abandonnés et maltraités.", 
    category: "sante" 
  }
};

const projectTasks = {
    1: [
        {
            id: 1,
            title: "Enseignement de l'anglais",
            description: "Donner des cours d'anglais basique aux enfants de 6 à 12 ans",
            duration: "2 heures par session",
            volunteersNeeded: 3,
            skills: ["Anglais", "Pédagogie"]
        },
        {
            id: 2,
            title: "Ateliers créatifs",
            description: "Animer des ateliers de dessin et d'artisanat",
            duration: "3 heures par session",
            volunteersNeeded: 2,
            skills: ["Art", "Créativité"]
        },
        {
            id: 3,
            title: "Soutien scolaire",
            description: "Aider aux devoirs et renforcement scolaire",
            duration: "2 heures par jour",
            volunteersNeeded: 4,
            skills: ["Mathématiques", "Français"]
        }
    ],
    2: [
        {
            id: 4,
            title: "Modération de discussions",
            description: "Animer des sessions de conversation en ligne",
            duration: "1 heure par session",
            volunteersNeeded: 5,
            skills: ["Communication", "Multilinguisme"]
        },
        {
            id: 5,
            title: "Traduction de contenu",
            description: "Traduire des documents éducatifs",
            duration: "Flexible",
            volunteersNeeded: 2,
            skills: ["Traduction", "Rédaction"]
        }
    ],
    3: [
        {
            id: 6,
            title: "Ramassage des déchets",
            description: "Collecte et tri des déchets sur la plage",
            duration: "4 heures",
            volunteersNeeded: 10,
            skills: ["Environnement", "Travail d'équipe"]
        },
        {
            id: 7,
            title: "Sensibilisation écologique",
            description: "Informez le public sur la protection marine",
            duration: "3 heures",
            volunteersNeeded: 3,
            skills: ["Communication", "Environnement"]
        }
    ]
};

let selectedTaskIds = [];

// Sélection des éléments
const container = document.getElementById('projectContainer');
const shareIcons = document.getElementById('shareIcons');
const chatModal = document.getElementById('chatModal');
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const closeChatBtn = document.getElementById('closeChat');
const sendMsgBtn = document.getElementById('sendMsg');
// Task Selection Functionality
const taskModal = document.getElementById('taskModal');
const tasksList = document.getElementById('tasksList');
const selectedTasks = document.getElementById('selectedTasks');
const confirmParticipationBtn = document.getElementById('confirmParticipation');
const cancelTaskSelectionBtn = document.getElementById('cancelTaskSelection');
const successModal = document.getElementById('successModal');
const successDetails = document.getElementById('successDetails');
const closeSuccessModalBtn = document.getElementById('closeSuccessModal');

let participantCount = 0;

// Récupération de l'ID du projet depuis l'URL et conversion en number
const projectId = Number(new URLSearchParams(window.location.search).get('id'));

// Vérification que le projet existe
if(projectId && projects[projectId]){
  const p = projects[projectId];

  container.innerHTML = `
    <h2>${p.title}</h2>
    <p><strong>Association :</strong> ${p.association}</p>
    <p><strong>Lieu :</strong> ${p.location}</p>
    <p><strong>Durée :</strong> ${p.duration}</p>
    <p><strong>Description :</strong></p>
    <p>${p.description}</p>
    <div id="participants">Participants : ${participantCount}</div>
    <div class="buttons">
      <button class="btn participate-btn" id="participateBtn">Je Participe</button>
      <button class="btn share-btn" id="shareBtn">Partager</button>
      <button class="btn chat-btn" id="chatBtn">Chat Participants</button>
      <a href="projects.html?category=${p.category}" class="btn back-btn">← Retourner à la liste</a>
    </div>
  `;

  // Sélection des boutons dynamiques
  const participateBtn = document.getElementById('participateBtn');
  const shareBtn = document.getElementById('shareBtn');
  const chatBtn = document.getElementById('chatBtn');
  const participantsDiv = document.getElementById('participants');

  // Bouton Je Participe
  participateBtn.addEventListener('click', () => {
    participantCount++;
    participantsDiv.textContent = `Participants : ${participantCount}`;
    alert("✅ Vous participez immédiatement à ce projet !");
  });

  // Bouton Partager
  shareBtn.addEventListener('click', () => {
    shareIcons.style.display = shareIcons.style.display === 'flex' ? 'none' : 'flex';
  });

  // Bouton Chat
  chatBtn.addEventListener('click', () => {
    chatModal.classList.add('active');
  });

} else {
  container.innerHTML = "<p>Projet introuvable.</p>";
}

// Fermeture du Chat Modal
closeChatBtn.addEventListener('click', () => {
  chatModal.classList.remove('active');
});

// Envoyer message dans le chat
sendMsgBtn.addEventListener('click', () => {
  const msg = chatInput.value.trim();
  if(!msg) return;
  const div = document.createElement('div');
  div.className = 'chat-message';
  div.innerHTML = `<strong>Vous:</strong> ${msg}`;
  chatMessages.appendChild(div);
  chatInput.value = '';
  chatMessages.scrollTop = chatMessages.scrollHeight;
});


function openTaskSelection(projectId) {
    selectedTaskIds = [];
    updateSelectedTasksDisplay();
    renderTasks(projectId);
    taskModal.classList.add('active');
}

// Render tasks for a project
function renderTasks(projectId) {
    const tasks = projectTasks[projectId] || [];
    tasksList.innerHTML = '';
    
    if (tasks.length === 0) {
        tasksList.innerHTML = '<p class="no-tasks">Aucune tâche disponible pour ce projet</p>';
        return;
    }
    
    tasks.forEach(task => {
        const taskElement = document.createElement('div');
        taskElement.className = 'task-item';
        taskElement.innerHTML = `
            <div class="task-checkbox"></div>
            <div class="task-title">${task.title}</div>
            <div class="task-description">${task.description}</div>
            <div class="task-meta">
                <span><i class="fas fa-clock"></i> ${task.duration}</span>
                <span><i class="fas fa-users"></i> ${task.volunteersNeeded} volontaires</span>
                <span><i class="fas fa-tools"></i> ${task.skills.join(', ')}</span>
            </div>
        `;
        
        taskElement.addEventListener('click', () => toggleTaskSelection(task.id, taskElement));
        tasksList.appendChild(taskElement);
    });
}

// Toggle task selection
function toggleTaskSelection(taskId, taskElement) {
    const index = selectedTaskIds.indexOf(taskId);
    
    if (index === -1) {
        // Add task
        selectedTaskIds.push(taskId);
        taskElement.classList.add('selected');
    } else {
        // Remove task
        selectedTaskIds.splice(index, 1);
        taskElement.classList.remove('selected');
    }
    
    updateSelectedTasksDisplay();
    updateConfirmButton();
}

// Update selected tasks display
function updateSelectedTasksDisplay() {
    const currentProjectId = getCurrentProjectId(); // You'll need to implement this
    const tasks = projectTasks[currentProjectId] || [];
    const selectedTasksData = tasks.filter(task => selectedTaskIds.includes(task.id));
    
    if (selectedTasksData.length === 0) {
        selectedTasks.innerHTML = '<p class="no-tasks">Aucune tâche sélectionnée</p>';
        return;
    }
    
    selectedTasks.innerHTML = selectedTasksData.map(task => `
        <div class="selected-task-item">
            <span class="selected-task-title">${task.title}</span>
            <button class="remove-task" onclick="removeTask(${task.id})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

// Remove task from selection
function removeTask(taskId) {
    const index = selectedTaskIds.indexOf(taskId);
    if (index !== -1) {
        selectedTaskIds.splice(index, 1);
        updateSelectedTasksDisplay();
        updateConfirmButton();
        
        // Update the task item in the list
        const taskItems = document.querySelectorAll('.task-item');
        taskItems.forEach(item => {
            if (item.querySelector('.task-title').textContent === 
                projectTasks[getCurrentProjectId()].find(t => t.id === taskId).title) {
                item.classList.remove('selected');
            }
        });
    }
}

// Update confirm button state
function updateConfirmButton() {
    confirmParticipationBtn.disabled = selectedTaskIds.length === 0;
}

// Show success modal
function showSuccessModal() {
    const currentProjectId = getCurrentProjectId();
    const tasks = projectTasks[currentProjectId] || [];
    const selectedTasksData = tasks.filter(task => selectedTaskIds.includes(task.id));
    
    successDetails.innerHTML = `
        <div class="success-detail-item">
            <span class="detail-label">Projet :</span>
            <span class="detail-value">${document.querySelector('.project-title').textContent}</span>
        </div>
        <div class="success-detail-item">
            <span class="detail-label">Tâches sélectionnées :</span>
            <span class="detail-value">${selectedTasksData.length}</span>
        </div>
        <div class="success-detail-item">
            <span class="detail-label">Date d'inscription :</span>
            <span class="detail-value">${new Date().toLocaleDateString('fr-FR')}</span>
        </div>
    `;
    
    successModal.classList.add('active');
}

// Event Listeners
confirmParticipationBtn.addEventListener('click', () => {
    // Here you would typically send the data to your backend
    console.log('Tasks selected:', selectedTaskIds);
    
    // Close task modal and show success
    taskModal.classList.remove('active');
    showSuccessModal();
});

cancelTaskSelectionBtn.addEventListener('click', () => {
    taskModal.classList.remove('active');
    selectedTaskIds = [];
});

closeSuccessModalBtn.addEventListener('click', () => {
    successModal.classList.remove('active');
});

// Close modals when clicking outside
document.addEventListener('click', (e) => {
    if (e.target === taskModal) {
        taskModal.classList.remove('active');
    }
    if (e.target === successModal) {
        successModal.classList.remove('active');
    }
});

// Helper function to get current project ID (you'll need to implement this based on your data)
function getCurrentProjectId() {
    // This should return the current project ID from your data
    // For now, returning 1 as example
    return 1;
}

// Modify your existing participate button to open task selection
// Add this to your existing details.js where you handle the participate button
document.addEventListener('DOMContentLoaded', function() {
    // This should be integrated with your existing project loading logic
    const participateBtn = document.querySelector('.participate-btn');
    if (participateBtn) {
        participateBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openTaskSelection(getCurrentProjectId());
        });
    }
});