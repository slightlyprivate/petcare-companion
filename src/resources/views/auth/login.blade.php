<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PetCare Companion</title>
    <link rel="stylesheet" href="{{ asset('css/pets.css') }}">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .otp-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }

        .otp-input {
            letter-spacing: 0.5em;
            font-size: 18px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <h1 style="text-align: center; margin-bottom: 30px;">🐾 PetCare Companion</h1>

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

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email"
                        value="{{ old('email') }}" required
                        placeholder="Enter your email address">
                </div>

                <button type="submit" id="request-btn">Request OTP Code</button>
            </form>

            <div class="otp-section" id="otp-section" style="display: none;">
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <input type="hidden" name="email" id="otp-email">

                    <div class="form-group">
                        <label for="otp_code">Enter OTP Code *</label>
                        <input type="text" id="otp_code" name="otp_code"
                            class="otp-input" maxlength="6" pattern="[0-9]{6}"
                            placeholder="000000" required>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            Check your email for the 6-digit code
                        </small>
                    </div>

                    <button type="submit">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('request-btn').addEventListener('click', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            if (!email) {
                alert('Please enter your email address');
                return;
            }

            // Show loading state
            this.textContent = 'Sending...';
            this.disabled = true;

            // Make API call to request OTP
            fetch('/api/auth/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        // Show OTP input section
                        document.getElementById('otp-section').style.display = 'block';
                        document.getElementById('otp-email').value = email;
                        document.getElementById('otp_code').focus();

                        // Hide the request form
                        this.closest('form').style.display = 'none';
                    } else {
                        alert('Failed to send OTP. Please try again.');
                        this.textContent = 'Request OTP Code';
                        this.disabled = false;
                    }
                })
                .catch(error => {
                    alert('Error sending OTP. Please try again.');
                    this.textContent = 'Request OTP Code';
                    this.disabled = false;
                });
        });
    </script>
</body>

</html>