<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
            color: #333;
        }
        .receipt-container {
            max-width: 600px;
            border: 1px solid #ddd;
            padding: 30px;
            background-color: #f9f9f9;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 14px;
            color: #2c3e50;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .section-content {
            font-size: 13px;
            margin-left: 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
        }
        .label {
            font-weight: 600;
            width: 45%;
        }
        .value {
            width: 55%;
            text-align: right;
        }
        .footer {
            text-align: center;
            border-top: 2px solid #333;
            padding-top: 20px;
            margin-top: 30px;
            font-size: 12px;
            font-style: italic;
        }
        .amount-highlight {
            font-size: 18px;
            font-weight: bold;
            color: #27ae60;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>PETCARE COMPANION</h1>
            <p>GIFT RECEIPT</p>
        </div>

        <div class="section">
            <div class="row">
                <span class="label">Receipt Date:</span>
                <span class="value">{{ $receiptDate }}</span>
            </div>
            <div class="row">
                <span class="label">Gift ID:</span>
                <span class="value">{{ $gift->id }}</span>
            </div>
        </div>

        <div class="section">
            <div class="section-title">DONOR INFORMATION</div>
            <div class="section-content">
                <div class="row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $donorEmail }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">PET INFORMATION</div>
            <div class="section-content">
                <div class="row">
                    <span class="label">Pet Name:</span>
                    <span class="value">{{ $petName }}</span>
                </div>
                <div class="row">
                    <span class="label">Species:</span>
                    <span class="value">{{ $petSpecies }}</span>
                </div>
                <div class="row">
                    <span class="label">Breed:</span>
                    <span class="value">{{ $petBreed }}</span>
                </div>
                <div class="row">
                    <span class="label">Owner:</span>
                    <span class="value">{{ $petOwner }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">GIFT DETAILS</div>
            <div class="section-content">
                <div class="row">
                    <span class="label">Amount:</span>
                    <span class="value amount-highlight">${{ number_format($amountDollars, 2) }}</span>
                </div>
                <div class="row">
                    <span class="label">Currency:</span>
                    <span class="value">USD</span>
                </div>
                <div class="row">
                    <span class="label">Status:</span>
                    <span class="value">{{ $status }}</span>
                </div>
                @if ($completedAt)
                    <div class="row">
                        <span class="label">Completed:</span>
                        <span class="value">{{ $completedAt }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if (!empty($metadata))
            <div class="section">
                <div class="section-title">PAYMENT METHOD</div>
                <div class="section-content">
                    @if (isset($metadata['payment_method']))
                        <div class="row">
                            <span class="label">Method:</span>
                            <span class="value">{{ $metadata['payment_method'] }}</span>
                        </div>
                    @endif
                    @if (isset($metadata['brand']))
                        <div class="row">
                            <span class="label">Card Brand:</span>
                            <span class="value">{{ $metadata['brand'] }}</span>
                        </div>
                    @endif
                    @if (isset($metadata['last4']))
                        <div class="row">
                            <span class="label">Card Last 4:</span>
                            <span class="value">•••• {{ $metadata['last4'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="section">
            <div class="section-title">TRANSACTION INFORMATION</div>
            <div class="section-content">
                @if ($chargeId)
                    <div class="row">
                        <span class="label">Stripe Charge ID:</span>
                        <span class="value" style="font-size: 11px; word-break: break-all;">{{ $chargeId }}</span>
                    </div>
                @endif
                @if ($gift->stripe_session_id)
                    <div class="row">
                        <span class="label">Stripe Session ID:</span>
                        <span class="value" style="font-size: 11px; word-break: break-all;">{{ $gift->stripe_session_id }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your generous gift!</p>
            <p>Your support helps improve pet care and services.</p>
        </div>
    </div>
</body>
</html>
