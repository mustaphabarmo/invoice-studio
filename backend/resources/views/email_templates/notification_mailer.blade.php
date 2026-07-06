<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst(env('APP_NAME')) }} Notification</title>
    <style>
        @media only screen and (min-width: 600px) {
            /* Desktop styles */
            div[style*="flex-direction: column"] {
                flex-direction: row !important;
                justify-content: center !important;
                gap: 16px !important;
            }
            
            a[style*="max-width: 200px"] {
                width: auto !important;
                max-width: none !important;
                min-width: 180px !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f8f9fa; font-family: Arial, sans-serif;">
    <!-- Main Container -->
    <div style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden;">
        <!-- Header -->
        <div style="background-color: #fff; padding: 24px; text-align: center;">
            <img src="{{ env('APP_URL') }}/img/logo.png" alt="{{ ucfirst(env('APP_NAME')) }}" width="60" height="60" style="display: block; margin: 0 auto;">
            <h1 style="color: #ffffff; font-size: 24px; font-weight: bold; margin: 10px 0 0 0;">{{ ucfirst(env('APP_NAME')) }}</h1>
        </div>
        
        <!-- Content -->
        <div style="padding: 24px;">
            <!-- Dynamic Content -->
            <div style="margin-bottom: 25px;">
                <?php echo $body; ?>
            </div>
            
            <!-- App Section -->
            <div style="background-color: #f9fafb; border-radius: 8px; padding: 10px; margin-bottom: 24px;">
                <h2 style="font-size: 20px; font-weight: bold; color: #1f2937; margin: 0 0 8px 0;">Get the {{ ucfirst(env('APP_NAME')) }} app!</h2>
                <p style="color: #6b7280; margin: 0 0 16px 0;">Get the most of {{ ucfirst(env('APP_NAME')) }} by installing the mobile app.</p>
                
                <!-- Responsive App Store Buttons -->
                <div style="margin-top: 24px; display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <!-- App Store Button -->
                    <a href="#" style="display: flex; align-items: center; justify-content: center; background-color: #000000; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-family: Arial, sans-serif; width: 100%; max-width: 200px; box-sizing: border-box;">
                        <div style="margin-right: 2px; font-size: 20px;">📱</div>
                        <div style="text-align: left;">
                            <div style="font-size: 10px; line-height: 1.2;">Download on the</div>
                            <div style="font-weight: bold; font-size: 14px; line-height: 1.2;">App Store</div>
                        </div>
                    </a>
                    
                    <!-- Google Play Button -->
                    <a href="#" style="display: flex; align-items: center; justify-content: center; background-color: #34d399; color: #ffffff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-family: Arial, sans-serif; width: 100%; max-width: 200px; box-sizing: border-box;">
                        <div style="margin-right: 1px; font-size: 18px;">▶️</div>
                        <div style="text-align: left;">
                            <div style="font-size: 10px; line-height: 1.2;">GET IT ON</div>
                            <div style="font-weight: bold; font-size: 14px; line-height: 1.2;">Google Play</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div style="background-color: #1f2937; color: #d1d5db; padding: 24px; text-align: center;">
            <p style="margin: 0 0 8px 0; font-size: 14px;">
                Copyright &copy; {{ date("Y") }} 
                <a href="{{ env('APP_URL') }}" target="_blank" style="color: #93c5fd; text-decoration: none;">{{ ucfirst(env('APP_NAME')) }}</a>.
            </p>
            <p style="margin: 0; font-size: 12px;">
                <a href="#" style="color: #9ca3af; text-decoration: none;">Unsubscribe</a> | 
                <a href="{{ env('APP_URL') }}/privacy" style="color: #9ca3af; text-decoration: none;">Privacy Policy</a>
            </p>
        </div>
    </div>
</body>
</html>