// Replace with your Google API credentials
$clientId = '774534186405-80lv1beh29860ptmbkssn2i03jvvgn66.apps.googleusercontent.com';
$clientSecret = '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';
$redirectUri = 'http://localhost/bulk-email-sender/';

// Create client object
$client = new Google_Client();
$client->setApplicationName("Bulk Email Sender");
$client->setScopes(Google_Service_Gmail::GMAIL_SEND);
$client->setAuthConfig(__DIR__ . '/credentials.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Get the email CSV file and message
$file = $_FILES['csvFile'];
$emailMessage = $_POST['emailMessage'];

if ($file && $emailMessage) {
    // Read the CSV file and send emails
    $fileContent = file_get_contents($file['tmp_name']);
    $emails = str_getcsv($fileContent);

    $service = new Google_Service_Gmail($client);

    foreach ($emails as $email) {
        if ($email) {
            $to = $email;
            $message = new Google_Service_Gmail_Message();
            $raw = base64_url_encode(strtr(
                "To: $to\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n" .
                "Content-Transfer-Encoding: 8bit\r\n\r\n" .
                $emailMessage,
                '+/=', '-_'
            ));
            $message->setRaw($raw);

            try {
                $service->users_messages->send('me', $message);
                echo "Email sent to $to <br>";
            } catch (Exception $e) {
                echo "Error sending email to $to: " . $e->getMessage() . "<br>";
            }
        }
    }

    // Reset the form
    echo "<script>document.getElementById('emailForm').reset();</script>";
} else {
    echo "Error: CSV file or email message is missing.";
}

function base64_url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}