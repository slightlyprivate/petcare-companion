<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PetCare Companion</title>
  <link rel="stylesheet" href="{{ asset('css/pets.css') }}">
</head>

<body>
  <div class="container">
    <h1>🐾 PetCare Companion</h1>

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
        This application also provides REST API endpoints for integration:
      </p>
      <ul style="color: #666; line-height: 1.6;">
        <li><strong>GET /api/pets</strong> - List all pets with pagination</li>
        <li><strong>POST /api/pets</strong> - Create a new pet</li>
        <li><strong>GET /api/pets/{id}/appointments</strong> - List pet's appointments</li>
        <li><strong>POST /api/pets/{id}/appointments</strong> - Create new appointment</li>
      </ul>
    </div>
  </div>
</body>

</html>