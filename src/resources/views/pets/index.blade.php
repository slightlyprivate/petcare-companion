<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PetCare Companion</title>
  <link rel="stylesheet" href="{{ asset('css/pets.css') }}">
  <style>
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #ddd;
    }

    .user-info {
      color: #666;
      font-size: 14px;
    }

    .logout-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    .logout-btn:hover {
      background: #c82333;
    }

    .pet-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      background: #f9f9f9;
    }

    .pet-name {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 10px;
      color: #333;
    }

    .pet-info {
      color: #666;
      line-height: 1.5;
    }

    .delete-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
      margin-top: 10px;
    }

    .delete-btn:hover {
      background: #c82333;
    }

    .appointments-section {
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid #ddd;
    }

    .appointment-form {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .appointment-list {
      margin-top: 15px;
    }

    .appointment-item {
      background: #fff;
      border: 1px solid #e9ecef;
      border-radius: 6px;
      padding: 10px;
      margin-bottom: 10px;
    }

    .appointment-title {
      font-weight: bold;
      color: #333;
    }

    .appointment-date {
      color: #666;
      font-size: 14px;
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Header with user info and logout -->
    <div class="header">
      <h1>🐾 PetCare Companion</h1>
      <div class="user-info">
        Logged in as: {{ Auth::user()->email }}
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
          @csrf
          <button type="submit" class="logout-btn">Logout</button>
        </form>
      </div>
    </div>

    @if(session('success'))
    <div class="success-message">
      {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="error-message">
      <ul style="margin: 0; padding-left: 20px;">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
    @endif

    <!-- Add New Pet Form -->
    <div class="section">
      <h2>Add New Pet</h2>
      <form method="POST" action="{{ route('pets.store.web') }}">
        @csrf
        <div class="form-group">
          <label for="name">Pet Name *</label>
          <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
          <label for="species">Species *</label>
          <select id="species" name="species" required>
            <option value="">Select Species</option>
            <option value="Dog" {{ old('species') == 'Dog' ? 'selected' : '' }}>Dog</option>
            <option value="Cat" {{ old('species') == 'Cat' ? 'selected' : '' }}>Cat</option>
            <option value="Bird" {{ old('species') == 'Bird' ? 'selected' : '' }}>Bird</option>
            <option value="Rabbit" {{ old('species') == 'Rabbit' ? 'selected' : '' }}>Rabbit</option>
            <option value="Other" {{ old('species') == 'Other' ? 'selected' : '' }}>Other</option>
          </select>
        </div>

        <div class="form-group">
          <label for="breed">Breed</label>
          <input type="text" id="breed" name="breed" value="{{ old('breed') }}">
        </div>

        <div class="form-group">
          <label for="birth_date">Birth Date</label>
          <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
        </div>

        <div class="form-group">
          <label for="owner_name">Owner Name *</label>
          <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required>
        </div>

        <button type="submit">Add Pet</button>
      </form>
    </div>

    <!-- Pet List -->
    <div class="section">
      <h2>Your Pets ({{ $pets->count() }})</h2>
      @if($pets->count() > 0)
      @foreach($pets as $pet)
      <div class="pet-card">
        <div class="pet-name">
          {{ $pet->name }} ({{ $pet->species }})
          <form method="POST" action="#" style="display: inline; float: right;" onsubmit="return confirm('Are you sure you want to delete this pet?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="delete-btn">Delete</button>
          </form>
        </div>
        <div class="pet-info">
          <strong>Owner:</strong> {{ $pet->owner_name }}<br>
          @if($pet->breed)
          <strong>Breed:</strong> {{ $pet->breed }}<br>
          @endif
          @if($pet->age)
          <strong>Age:</strong> {{ $pet->age }} years<br>
          @endif
          <strong>Added:</strong> {{ $pet->created_at->format('M d, Y') }}
        </div>

        <!-- Appointments Section -->
        <div class="appointments-section">
          <h3 style="margin-bottom: 10px;">Appointments</h3>

          <!-- Add Appointment Form -->
          <div class="appointment-form">
            <form method="POST" action="#" data-pet-id="{{ $pet->id }}" onsubmit="addAppointment(event)">
              @csrf
              <div style="display: flex; gap: 10px; align-items: end;">
                <div style="flex: 1;">
                  <label for="appointment_title_{{ $pet->id }}">Title</label>
                  <input type="text" id="appointment_title_{{ $pet->id }}" required placeholder="e.g., Wellness Check">
                </div>
                <div>
                  <label for="appointment_date_{{ $pet->id }}">Date & Time</label>
                  <input type="datetime-local" id="appointment_date_{{ $pet->id }}" required>
                </div>
                <button type="submit">Add</button>
              </div>
            </form>
          </div>

          <!-- Appointment List -->
          <div class="appointment-list" id="appointments-{{ $pet->id }}">
            <!-- Appointments will be loaded here -->
          </div>
        </div>
      </div>
      @endforeach
      @else
      <p style="color: #666; font-style: italic;">No pets added yet. Add your first pet above!</p>
      @endif
    </div>

    <!-- API Information -->
    <div class="section">
      <h2>API Endpoints</h2>
      <p style="color: #666; margin-bottom: 15px;">
        This application provides REST API endpoints with authentication:
      </p>
      <ul style="color: #666; line-height: 1.6;">
        <li><strong>POST /api/auth/request</strong> - Request OTP for authentication</li>
        <li><strong>POST /api/auth/verify</strong> - Verify OTP and login</li>
        <li><strong>GET /api/auth/me</strong> - Get authenticated user info</li>
        <li><strong>GET /api/pets</strong> - List all pets (authenticated)</li>
        <li><strong>POST /api/pets</strong> - Create a new pet (authenticated)</li>
        <li><strong>GET /api/pets/{id}/appointments</strong> - List pet's appointments</li>
        <li><strong>POST /api/pets/{id}/appointments</strong> - Create new appointment</li>
      </ul>
    </div>
  </div>

  <script>
    // Load appointments for all pets on page load
    document.addEventListener('DOMContentLoaded', function() {
      // Get all pet IDs from the data attributes
      const appointmentForms = document.querySelectorAll('form[data-pet-id]');
      appointmentForms.forEach(function(form) {
        const petId = parseInt(form.getAttribute('data-pet-id'));
        loadAppointments(petId);
      });
    });

    function loadAppointments(petId) {
      fetch(`/api/pets/${petId}/appointments`, {
          headers: {
            'Accept': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          const container = document.getElementById(`appointments-${petId}`);
          if (data.data && data.data.length > 0) {
            container.innerHTML = data.data.map(appointment => `
              <div class="appointment-item">
                <div class="appointment-title">${appointment.title}</div>
                <div class="appointment-date">${appointment.scheduled_at_formatted}</div>
                ${appointment.notes ? `<div style="color: #666; margin-top: 5px;">${appointment.notes}</div>` : ''}
              </div>
            `).join('');
          } else {
            container.innerHTML = '<p style="color: #999; font-style: italic;">No appointments scheduled</p>';
          }
        })
        .catch(error => {
          console.error('Error loading appointments:', error);
        });
    }

    function addAppointment(event) {
      event.preventDefault();

      const form = event.target;
      const petId = form.getAttribute('data-pet-id');
      const title = document.getElementById(`appointment_title_${petId}`).value;
      const scheduledAt = document.getElementById(`appointment_date_${petId}`).value;

      fetch(`/api/pets/${petId}/appointments`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            title: title,
            scheduled_at: scheduledAt
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.data) {
            // Clear form
            document.getElementById(`appointment_title_${petId}`).value = '';
            document.getElementById(`appointment_date_${petId}`).value = '';

            // Reload appointments
            loadAppointments(petId);
          } else {
            alert('Failed to add appointment');
          }
        })
        .catch(error => {
          console.error('Error adding appointment:', error);
          alert('Error adding appointment');
        });
    }
  </script>
</body>

</html>