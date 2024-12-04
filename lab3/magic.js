let selectedService = '';
let selectedDoctor = '';
let selectedDate = '';
let selectedTime = '';

function selectService(service) {
    if (service === 'Cancellation') {
        window.location.href = 'cancel.html';
        return;
    }
    
    selectedService = service;
    sessionStorage.setItem('selectedService', service);
    
    // Update styling for all cards
    document.querySelectorAll('.service-card, .service-card-cancel').forEach(card => {
        card.style.backgroundColor = '#fff';
    });
    
    // Highlight selected card
    event.currentTarget.style.backgroundColor = '#00AAFF';
}

function selectDoctor(doctor) {
    selectedDoctor = doctor;
    sessionStorage.setItem('selectedDoctor', doctor);
    document.querySelectorAll('.doctor-card').forEach(card => {
        card.style.backgroundColor = '#fff';
    });
    event.currentTarget.style.backgroundColor = '#00AAFF';
}

function nextStep(currentStep) {
    document.getElementById(`step${currentStep}`).classList.remove('active');
    document.getElementById(`step${currentStep + 1}`).classList.add('active');
}

function prevStep(currentStep) {
    document.getElementById(`step${currentStep}`).classList.remove('active');
    document.getElementById(`step${currentStep - 1}`).classList.add('active');
}

function confirmAppointment() {
    alert(`Appointment confirmed!\nService: ${selectedService}\nDoctor: ${selectedDoctor}\nDate: ${selectedDate}\nTime: ${selectedTime}`);
}

// Make calendar globally accessible
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        businessHours: {
            daysOfWeek: [1, 2, 3, 4, 5], // Monday - Friday
            startTime: '07:00',
            endTime: '17:00',
        },
        slotDuration: '01:00:00', // Set 1-hour slots
        selectConstraint: 'businessHours',
        selectMinTime: '07:00:00',
        selectMaxTime: '17:00:00',
        eventSources: [
            {
                url: 'get_appointment.php',
                color: '#378006',
                failure: function(error) {
                    console.error('Appointments loading error:', error);
                }
            },
            {
                url: 'get_blocked_times.php',
                color: '#FF0000',
                display: 'background', // Changed from rendering to display
                failure: function(error) {
                    console.error('Blocked times loading error:', error);
                }
            }
        ],
        eventOverlap: false, // Prevent overlapping appointments
        dateClick: function(info) {
            if (calendar.view.type === 'dayGridMonth') {
                calendar.changeView('timeGridWeek', info.dateStr);
            }
        },
        selectAllow: function(selectInfo) {
            if (calendar.view.type === 'timeGridWeek') {
                // Calculate duration in hours
                const duration = (selectInfo.end - selectInfo.start) / (1000 * 60 * 60);
                // Only allow 1-hour selections
                return duration === 1;
            }
            return false;
        },
        eventDidMount: function(info) {
            console.log('Event mounted:', info.event);
            if (info.event.display === 'background') {
                info.el.style.opacity = '0.7';
            }
        },
        select: function(info) {
            if (calendar.view.type === 'timeGridWeek') {
                // Check for blocked times
                const events = calendar.getEvents();
                const isBlocked = events.some(event => {
                    return event.display === 'background' && 
                           info.start >= event.start && 
                           info.end <= event.end;
                });

                if (isBlocked) {
                    alert('This time slot is not available');
                    return;
                }

                // Create event with default title or use stored patient info
                const patientName = sessionStorage.getItem('patientName') || 'New Appointment';
                const service = sessionStorage.getItem('selectedService') || '';
                const title = `${patientName} - ${service}`;
                
                calendar.addEvent({
                    title: title,
                    start: info.start,
                    end: info.end,
                    color: '#378006'
                });
                
                // Store selected date and time
                sessionStorage.setItem('selectedDate', info.start.toLocaleDateString());
                sessionStorage.setItem('selectedTime', info.start.toLocaleTimeString());
                
                calendar.unselect();
            }
        }
    });

    calendar.render();
});

// Global function for the back button
function calendarBack() {
    calendar.changeView('dayGridMonth');
}

function goBack() {
    const urlParams = new URLSearchParams(window.location.search);
    const step = urlParams.get('step');
    if (step) {
        window.location.href = `app.html#step${step}`;
    } else {
        window.history.back();
    }
}