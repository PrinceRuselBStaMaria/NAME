class AppointmentSystem {
    // ... existing constructor and methods ...
    

    renderCalendar() {
        const calendarDiv = document.getElementById('calendar');
        const today = new Date();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();

        // Clear existing calendar
        calendarDiv.innerHTML = '';

        // Add month navigation
        const header = document.createElement('div');
        header.className = 'calendar-header';
        header.innerHTML = `
            <button class="prev-month">&lt;</button>
            <h3>${new Date(currentYear, currentMonth).toLocaleString('default', { month: 'long' })} ${currentYear}</h3>
            <button class="next-month">&gt;</button>
        `;
        calendarDiv.appendChild(header);

        // Add weekday headers
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const daysGrid = document.createElement('div');
        daysGrid.className = 'calendar-grid';
        
        weekdays.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'calendar-header-cell';
            dayHeader.textContent = day;
            daysGrid.appendChild(dayHeader);
        });

        // Get first day of month and total days
        const firstDay = new Date(currentYear, currentMonth, 1).getDay();
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

        // Add blank cells for days before start of month
        for (let i = 0; i < firstDay; i++) {
            const blankDay = document.createElement('div');
            blankDay.className = 'calendar-day empty';
            daysGrid.appendChild(blankDay);
        }

        // Add days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'calendar-day';
            dayCell.textContent = day;

            const date = new Date(currentYear, currentMonth, day);
            if (this.isDayAvailable(date)) {
                dayCell.classList.add('available');
                dayCell.addEventListener('click', () => this.showTimeSlots(date));
            } else {
                dayCell.classList.add('unavailable');
            }

            daysGrid.appendChild(dayCell);
        }

        calendarDiv.appendChild(daysGrid);
    }

    isDayAvailable(date) {
        if (!this.selectedDoctor) return false;
        
        const dayName = date.toLocaleDateString('en-US', { weekday: 'short' }).toLowerCase();
        return this.selectedDoctor.schedule[dayName]?.length > 0;
    }

    showTimeSlots(date) {
        const timeSlotsDiv = document.getElementById('timeSlots');
        const availableSlots = this.generateTimeSlots(date);
        
        timeSlotsDiv.innerHTML = '';
        availableSlots.forEach(slot => {
            const timeSlot = document.createElement('div');
            timeSlot.className = 'time-slot';
            timeSlot.textContent = slot;
            timeSlot.addEventListener('click', () => this.bookAppointment(date, slot));
            timeSlotsDiv.appendChild(timeSlot);
        });
    }
}

doctorSelect.addEventListener('change', (e) => {
    const selectedDoctor = appointmentSystem.doctors.find(
        d => d.id === parseInt(e.target.value)
    );
    if (selectedDoctor) {
        appointmentSystem.selectedDoctor = selectedDoctor;
        appointmentSystem.renderCalendar();
    }
});