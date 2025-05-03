<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../logo.png" type="image/png">
    <title>MediAssist - Appointment Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
    --primary: #26a69a;
    --primary-light: #64d8cb;
    --primary-dark: #00766c;
    --secondary: #80cbc4;
    --accent: #004d40;
    --light: #e0f2f1;
    --dark: #00352c;
    --gray: #f5f7fa;
    --text: #333;
    --white: #ffffff;
    --danger: #f44336;
    --warning: #ff9800;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: var(--gray);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 0 2rem 0; /* Removed top padding */
}

.container {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1rem; /* Added padding to container instead */
}

header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    padding: 1rem 0;
    color: white;
    width: 100vw;
    margin-bottom: 2rem;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1rem; /* Reduced padding to bring elements closer to edges */
}

.back-button {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: -15rem; /* Pull closer to the left edge */
}

.back-button:hover {
    color: var(--light);
}

.back-button i {
    font-size: 1.2rem;
}

.logo {
    display: flex;
    align-items: center;
    font-size: 1.5rem;
    font-weight: bold;
    margin-right: -15rem; /* Pull closer to the right edge */
}

.logo i {
    margin-right: 0.5rem;
    color: var(--light);
    font-size: 2rem;
    animation: pulse 2s infinite; /* Added pulse animation */
}

.back-button {
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.back-button:hover {
    color: var(--light);
}

.back-button i {
    font-size: 1.2rem;
}

/* Tabs */
.tabs {
    display: flex;
    margin-bottom: 2rem;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.tab {
    flex: 1;
    padding: 1rem;
    text-align: center;
    background-color: var(--light);
    cursor: pointer;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.tab.active {
    background-color: var(--white);
    border-bottom: 3px solid var(--primary);
    color: var(--primary);
}

.tab:hover:not(.active) {
    background-color: var(--white);
    border-bottom: 3px solid var(--secondary);
}

/* Tab Content */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* Appointment Card */
.appointment-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 600px;
    overflow: hidden;
    position: relative;
    margin: 0 auto;
}

.card-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    padding: 1.5rem;
    position: relative;
}

.card-header h2 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.pulse-circle {
    position: absolute;
    top: -5px;
    right: -5px;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    z-index: 0;
}

.pulse-circle::before {
    content: "";
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: pulse 2s infinite;
}

/* Pulse Animation */
@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.2); }
}

.form-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.2rem;
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--dark);
    font-size: 0.9rem;
}

.input-icon {
    position: relative;
}

.input-icon i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--primary);
}

.form-control {
    width: 100%;
    padding: 0.8rem 1rem 0.8rem 2.5rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(38, 166, 154, 0.1);
}

.datetime-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn {
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary {
    background-color: #f1f1f1;
    color: var(--text);
}

.btn-primary {
    background-color: var(--primary);
    color: white;
}

.btn-danger {
    background-color: var(--danger);
    color: white;
}

.btn-secondary:hover {
    background-color: #e0e0e0;
}

.btn-primary:hover {
    background-color: var(--primary-dark);
}

.btn-danger:hover {
    background-color: #d32f2f;
}

.btn-floating {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    z-index: 100;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-floating:hover {
    background-color: var(--primary-dark);
    transform: scale(1.05);
}

/* Appointment List */
.appointment-list {
    margin-top: 2rem;
}

.appointment-item {
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    padding: 1.2rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    border-left: 4px solid var(--primary);
}

.appointment-item:hover {
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.appointment-info {
    flex: 1;
}

.appointment-title {
    font-weight: 600;
    font-size: 1.1rem;
    margin-bottom: 0.3rem;
    color: var(--dark);
}

.appointment-meta {
    display: flex;
    gap: 1rem;
    color: #666;
    font-size: 0.9rem;
    flex-wrap: wrap;
}

.appointment-meta span {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.appointment-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background-color: var(--light);
    color: var(--primary-dark);
}

.action-btn:hover {
    background-color: var(--primary-light);
}

.action-btn.delete:hover {
    background-color: var(--danger);
    color: white;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #888;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

/* Modal */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal-backdrop.active {
    opacity: 1;
    visibility: visible;
}

.modal {
    background-color: white;
    border-radius: 15px;
    width: 90%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transform: translateY(-20px);
    transition: all 0.3s ease;
}

.modal-backdrop.active .modal {
    transform: translateY(0);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-weight: 600;
    color: var(--dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    color: #888;
    transition: color 0.2s ease;
}

.close-modal:hover {
    color: var(--dark);
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

/* Search */
.search-form {
    margin-bottom: 1.5rem;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 1px solid #ddd;
    border-radius: 30px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(38, 166, 154, 0.1);
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
}

/* Filter tabs */
.filter-tabs {
    display: flex;
    margin-bottom: 1.5rem;
    border-radius: 30px;
    background-color: var(--light);
    padding: 0.3rem;
    overflow: hidden;
}

.filter-tab {
    padding: 0.7rem 1.5rem;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    font-weight: 500;
    font-size: 0.9rem;
}

.filter-tab.active {
    background-color: var(--primary);
    color: white;
}

/* Confirmation dialog */
.confirm-dialog {
    max-width: 400px;
    text-align: center;
}

.confirm-dialog-icon {
    font-size: 3rem;
    color: var(--warning);
    margin-bottom: 1rem;
}

.confirm-dialog-message {
    margin-bottom: 1.5rem;
    font-size: 1.1rem;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.page-link {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: var(--white);
    color: var(--text);
    transition: all 0.2s ease;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-link:hover {
    background-color: var(--light);
}

.page-link.active {
    background-color: var(--primary);
    color: white;
}

.decoration {
    position: absolute;
    z-index: 0;
    opacity: 0.15;
    animation: float 5s ease-in-out infinite;
}

.decoration-1 {
    top: 30px;
    right: 40px;
    animation-delay: 0s;
}

.decoration-2 {
    bottom: 20px;
    left: 30px;
    animation-delay: 1s;
}

@keyframes float {
    0% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
    100% {
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .datetime-group {
        grid-template-columns: 1fr;
    }

    .appointment-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .appointment-actions {
        width: 100%;
        margin-top: 1rem;
        justify-content: flex-end;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }
}
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <a href="../accueil.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                <span>MediAssist</span>
            </div>
        </div>
    </header>
    

    <div class="container">
        <h1>Scheduling System</h1>
        <!--<br>-->
        <div class="tabs">
            <div class="tab active" data-tab="appointments" role="tab" aria-selected="true">
                <i class="fas fa-calendar-check"></i> My Appointments
            </div>
            <div class="tab" data-tab="new" role="tab" aria-selected="false">
                <i class="fas fa-calendar-plus"></i> New Appointment
            </div>
        </div>

        <!-- Appointments Tab -->
        <div class="tab-content active" id="appointments-tab" role="tabpanel">
            <div class="search-form">
                <i class="fas fa-search search-icon" aria-hidden="true"></i>
                <input type="text" class="search-input" placeholder="Search appointments..." aria-label="Search appointments">
            </div>

            <div class="filter-tabs">
                <div class="filter-tab active" data-filter="all" role="tab" aria-selected="true">All</div>
                <div class="filter-tab" data-filter="upcoming" role="tab" aria-selected="false">Upcoming</div>
                <div class="filter-tab" data-filter="past" role="tab" aria-selected="false">Past</div>
            </div>

            <div class="appointment-list"></div>

            <div class="pagination">
                <div class="page-link active" data-page="1">1</div>
                <div class="page-link" data-page="2">2</div>
                <div class="page-link" data-page="3">3</div>
                <div class="page-link" data-page="next"><i class="fas fa-chevron-right"></i></div>
            </div>
        </div>

        <!-- New Appointment Tab -->
        <div class="tab-content" id="new-tab" role="tabpanel">
            <div class="appointment-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-plus"></i> New Appointment</h2>
                    <p>Schedule your next medical appointment</p>
                    <div class="pulse-circle"></div>
                    <i class="fas fa-heartbeat decoration decoration-1" style="font-size: 2.5rem;"></i>
                    <i class="fas fa-pills decoration decoration-2" style="font-size: 2rem;"></i>
                </div>

                <div class="form-body">
                    <form id="appointmentForm" action="save_appointment.php" method="POST">
                        <div class="form-group">
                            <label for="title">Appointment Title</label>
                            <div class="input-icon">
                                <i class="fas fa-clipboard-list"></i>
                                <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Cardiology Consultation" required aria-required="true">
                            </div>
                        </div>

                        <div class="datetime-group">
                            <div class="form-group">
                                <label for="date">Date</label>
                                <div class="input-icon">
                                    <i class="fas fa-calendar-day"></i>
                                    <input type="date" class="form-control" id="date" name="date" required aria-required="true">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="time">Time</label>
                                <div class="input-icon">
                                    <i class="fas fa-clock"></i>
                                    <input type="time" class="form-control" id="time" name="time" required aria-required="true">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="location">Location</label>
                            <div class="input-icon">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g. Dr. Martin Clinic, 15 Park Street" required aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <div class="input-icon">
                                <i class="fas fa-comment-medical"></i>
                                <input type="text" class="form-control" id="description" name="description" placeholder="Additional information">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="reminder">
                                <input type="checkbox" id="reminder" name="reminder" checked aria-checked="true">
                                Send a reminder
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="resetForm">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-backdrop" id="deleteModal" role="dialog" aria-labelledby="deleteModalTitle" aria-hidden="true">
        <div class="modal confirm-dialog">
            <div class="modal-header">
                <h3 class="modal-title" id="deleteModalTitle">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Deletion
                </h3>
                <button class="close-modal" id="closeDeleteModal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="confirm-dialog-icon">
                    <i class="fas fa-trash"></i>
                </div>
                <div class="confirm-dialog-message">
                    Are you sure you want to delete this appointment? This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelDelete">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div class="modal-backdrop" id="editModal" role="dialog" aria-labelledby="editModalTitle" aria-hidden="true">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="editModalTitle">
                    <i class="fas fa-edit"></i> Edit Appointment
                </h3>
                <button class="close-modal" id="closeEditModal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="editAppointmentForm">
                    <div class="form-group">
                        <label for="editTitle">Appointment Title</label>
                        <div class="input-icon">
                            <i class="fas fa-clipboard-list"></i>
                            <input type="text" class="form-control" id="editTitle" name="editTitle" required aria-required="true">
                        </div>
                    </div>

                    <div class="datetime-group">
                        <div class="form-group">
                            <label for="editDate">Date</label>
                            <div class="input-icon">
                                <i class="fas fa-calendar-day"></i>
                                <input type="date" class="form-control" id="editDate" name="editDate" required aria-required="true">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="editTime">Time</label>
                            <div class="input-icon">
                                <i class="fas fa-clock"></i>
                                <input type="time" class="form-control" id="editTime" name="editTime" required aria-required="true">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editLocation">Location</label>
                        <div class="input-icon">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" class="form-control" id="editLocation" name="editLocation" required aria-required="true">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <div class="input-icon">
                            <i class="fas fa-comment-medical"></i>
                            <input type="text" class="form-control" id="editDescription" name="editDescription">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="editReminder">
                            <input type="checkbox" id="editReminder" name="editReminder" checked aria-checked="true">
                            Send a reminder
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" id="cancelEdit">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn btn-primary" id="saveEdit">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- Floating add button (mobile only) -->
    <div class="btn-floating" id="addAppointmentBtn" role="button" aria-label="Add new appointment">
        <i class="fas fa-plus"></i>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
    let mockAppointments = [];
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('date').setAttribute('min', today);
    document.getElementById('date').value = today;
    document.getElementById('editDate').setAttribute('min', today);

    // Load appointments from database
    fetch('get_appointments.php')
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch appointments');
            return response.json();
        })
        .then(data => {
            console.log('Fetched appointments:', data); // Debug log
            mockAppointments = data;
            loadAppointments();
        })
        .catch(error => {
            console.error('Error loading appointments:', error);
            alert('Failed to load appointments. Please try again.');
        });

    // Sanitize input to prevent XSS
    const sanitizeInput = (input) => {
        const div = document.createElement('div');
        div.textContent = input;
        return div.innerHTML;
    };

    let filteredAppointments = [];

    // Load appointments with pagination
    function loadAppointments(page = 1, itemsPerPage = 5) {
        const appointmentList = document.querySelector('.appointment-list');
        appointmentList.innerHTML = '';

        // Filter and search logic
        const searchTerm = document.querySelector('.search-input').value.toLowerCase();
        const activeFilter = document.querySelector('.filter-tab.active').getAttribute('data-filter');
        const now = new Date();

        filteredAppointments = mockAppointments.filter(app => {
            const title = app.title.toLowerCase();
            const meta = `${app.date} ${app.time} ${app.location}`.toLowerCase();
            const matchesSearch = title.includes(searchTerm) || meta.includes(searchTerm);

            if (!matchesSearch) return false;

            const appDateTime = new Date(`${app.date}T${app.time}`);
            if (activeFilter === 'upcoming') return appDateTime >= now;
            if (activeFilter === 'past') return appDateTime < now;
            return true;
        });

        if (filteredAppointments.length === 0) {
            appointmentList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>No appointments found</p>
                </div>
            `;
            updatePagination(0, itemsPerPage);
            return;
        }

        // Pagination logic
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const paginatedAppointments = filteredAppointments.slice(start, end);

        paginatedAppointments.forEach((appointment) => {
            const appointmentDate = new Date(appointment.date);
            const formattedDate = appointmentDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            const newItem = document.createElement('div');
            newItem.className = 'appointment-item';
            newItem.dataset.id = appointment.id;
            newItem.innerHTML = `
                <div class="appointment-info">
                    <div class="appointment-title">${sanitizeInput(appointment.title)}</div>
                    <div class="appointment-meta">
                        <span><i class="fas fa-calendar-day"></i> ${formattedDate}</span>
                        <span><i class="fas fa-clock"></i> ${appointment.time}</span>
                        <span><i class="fas fa-map-marker-alt"></i> ${sanitizeInput(appointment.location)}</span>
                    </div>
                </div>
                <div class="appointment-actions">
                    <button class="action-btn" data-id="${appointment.id}" data-action="edit" aria-label="Edit appointment">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn delete" data-id="${appointment.id}" data-action="delete" aria-label="Delete appointment">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            appointmentList.appendChild(newItem);
        });

        updatePagination(filteredAppointments.length, itemsPerPage, page);
    }

    // Update pagination controls
    function updatePagination(totalItems, itemsPerPage, currentPage = 1) {
        const pagination = document.querySelector('.pagination');
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        pagination.innerHTML = '';

        if (totalPages <= 1) return;

        const prevButton = document.createElement('div');
        prevButton.className = `page-link ${currentPage === 1 ? 'disabled' : ''}`;
        prevButton.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevButton.dataset.page = 'prev';
        pagination.appendChild(prevButton);

        for (let i = 1; i <= Math.min(totalPages, 3); i++) {
            const pageLink = document.createElement('div');
            pageLink.className = `page-link ${i === currentPage ? 'active' : ''}`;
            pageLink.dataset.page = i;
            pageLink.textContent = i;
            pagination.appendChild(pageLink);
        }

        const nextButton = document.createElement('div');
        nextButton.className = `page-link ${currentPage === totalPages ? 'disabled' : ''}`;
        nextButton.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextButton.dataset.page = 'next';
        pagination.appendChild(nextButton);
    }

    // Tab switching
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.tab').forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            document.getElementById(`${tab.dataset.tab}-tab`).classList.add('active');
        });
    });

    // Filter tabs
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.filter-tab').forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            tab.classList.add('active');
            tab.setAttribute('aria-selected', 'true');
            loadAppointments();
        });
    });

    // Search input
    document.querySelector('.search-input').addEventListener('input', () => {
        loadAppointments();
    });

    // Pagination click handler
    document.querySelector('.pagination').addEventListener('click', (e) => {
        const target = e.target.closest('.page-link');
        if (!target || target.classList.contains('disabled')) return;

        let currentPage = parseInt(document.querySelector('.page-link.active').dataset.page);
        const action = target.dataset.page;

        if (action === 'prev') currentPage = Math.max(1, currentPage - 1);
        else if (action === 'next') currentPage++;
        else currentPage = parseInt(action);

        loadAppointments(currentPage);
    });

    // Form submission for new appointment
    document.getElementById('appointmentForm').addEventListener('submit', (e) => {
        e.preventDefault();
        console.log('New appointment form submitted'); // Debug log

        const formData = new FormData(e.target);
        for (let [key, value] of formData.entries()) {
            console.log(`FormData ${key}: ${value}`); // Debug log
        }

        fetch('save_appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Save response status:', response.status); // Debug log
            if (!response.ok) throw new Error('Failed to save appointment');
            return response.json();
        })
        .then(data => {
            console.log('Save response data:', data); // Debug log
            if (data.status === 'success') {
                const newAppointment = {
                    id: data.id,
                    title: formData.get('title'),
                    date: formData.get('date'),
                    time: formData.get('time'),
                    location: formData.get('location'),
                    description: formData.get('description') || '',
                    reminder: formData.get('reminder') === 'on'
                };

                mockAppointments.push(newAppointment);
                e.target.reset();
                document.getElementById('date').value = today;
                document.getElementById('reminder').checked = true;

                document.querySelector('.tab[data-tab="appointments"]').click();
                loadAppointments();
            } else {
                alert(`Failed to save appointment: ${data.message || 'Unknown error'}`);
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            alert('An error occurred while saving the appointment.');
        });
    });

    // Reset form and switch to appointments tab
    document.getElementById('resetForm').addEventListener('click', () => {
        document.getElementById('appointmentForm').reset();
        document.getElementById('date').value = today;
        document.getElementById('reminder').checked = true;
        document.querySelector('.tab[data-tab="appointments"]').click();
    });

    // Floating add button
    document.getElementById('addAppointmentBtn').addEventListener('click', () => {
        document.querySelector('.tab[data-tab="new"]').click();
    });

    // Appointment actions (edit/delete)
    document.querySelector('.appointment-list').addEventListener('click', (e) => {
        const button = e.target.closest('.action-btn');
        if (!button) return;

        const id = button.dataset.id;
        const action = button.dataset.action;
        console.log(`Action: ${action}, ID: ${id}`); // Debug log

        if (action === 'edit') {
            // Use loose comparison to handle string/integer mismatch
            const appointment = mockAppointments.find(app => app.id == id);
            console.log('Edit appointment:', appointment); // Debug log
            if (!appointment) {
                console.error('Appointment not found for ID:', id);
                alert('Appointment not found.');
                return;
            }

            // Populate edit modal fields
            document.getElementById('editTitle').value = appointment.title || '';
            document.getElementById('editDate').value = appointment.date || today;
            document.getElementById('editTime').value = appointment.time || '';
            document.getElementById('editLocation').value = appointment.location || '';
            document.getElementById('editDescription').value = appointment.description || '';
            document.getElementById('editReminder').checked = appointment.reminder || false;

            // Show edit modal
            const modal = document.getElementById('editModal');
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');

            // Store appointment ID
            document.getElementById('saveEdit').dataset.appointmentId = id;
        } else if (action === 'delete') {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.getElementById('confirmDelete').dataset.appointmentId = id;
        }
    });

    // Save edit button handler
    document.getElementById('saveEdit').addEventListener('click', () => {
        const id = document.getElementById('saveEdit').dataset.appointmentId;
        console.log('Saving edit for ID:', id); // Debug log

        const formData = new FormData();
        formData.append('id', id);
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('date', document.getElementById('editDate').value);
        formData.append('time', document.getElementById('editTime').value);
        formData.append('location', document.getElementById('editLocation').value);
        formData.append('description', document.getElementById('editDescription').value);
        formData.append('reminder', document.getElementById('editReminder').checked ? 'on' : '');

        for (let [key, value] of formData.entries()) {
            console.log(`Edit FormData ${key}: ${value}`); // Debug log
        }

        fetch('update_appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Update response status:', response.status); // Debug log
            if (!response.ok) throw new Error('Failed to update appointment');
            return response.json();
        })
        .then(data => {
            console.log('Update response data:', data); // Debug log
            if (data.status === 'success') {
                const updatedAppointment = {
                    id: id,
                    title: document.getElementById('editTitle').value,
                    date: document.getElementById('editDate').value,
                    time: document.getElementById('editTime').value,
                    location: document.getElementById('editLocation').value,
                    description: document.getElementById('editDescription').value || '',
                    reminder: document.getElementById('editReminder').checked
                };

                mockAppointments = mockAppointments.map(app =>
                    app.id == id ? updatedAppointment : app // Loose comparison
                );

                const modal = document.getElementById('editModal');
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
                loadAppointments();
            } else {
                alert(`Failed to update appointment: ${data.message || 'Unknown error'}`);
            }
        })
        .catch(error => {
            console.error('Update error:', error);
            alert('An error occurred while updating the appointment.');
        });
    });

    // Confirm delete button handler
    document.getElementById('confirmDelete').addEventListener('click', () => {
        const id = document.getElementById('confirmDelete').dataset.appointmentId;
        console.log('Deleting appointment ID:', id); // Debug log

        const formData = new FormData();
        formData.append('id', id);

        fetch('delete_appointment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to delete appointment');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                mockAppointments = mockAppointments.filter(app => app.id != id);
                const modal = document.getElementById('deleteModal');
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
                loadAppointments();
            } else {
                alert(`Failed to delete appointment: ${data.message || 'Unknown error'}`);
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('An error occurred while deleting the appointment.');
        });
    });

    // Close modals
    document.querySelectorAll('.close-modal, #cancelEdit, #cancelDelete').forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.modal-backdrop');
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        });
    });

    // Close modal on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                modal.setAttribute('aria-hidden', 'true');
            }
        });
    });

    // Initial load
    loadAppointments();
});
    </script>
</body>
</html>