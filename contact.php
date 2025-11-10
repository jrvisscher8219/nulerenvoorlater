<?php
// Basic contact form handler for Strato (PHP)
// Configure your destination email:
$to = 'info@nulerenvoorlater.nl';

// Security: only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Content-Type: text/plain; charset=UTF-8');
  echo 'Method Not Allowed';
  exit;
}

// Honeypot check
if (!empty($_POST['company'])) {
  // likely spam
  header('Location: contact-bedankt.html');
  exit;
}

// Collect and validate inputs
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Prevent header injection
foreach ([$name, $email] as $v) {
  if (preg_match('/\r|\n|%0A|%0D/i', $v)) {
    http_response_code(400);
    echo 'Invalid input.';
    exit;
  }
}

if ($name === '' || $email === '' || $message === '') {
  http_response_code(400);
  echo 'Vul alle verplichte velden in.';
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo 'Ongeldig e-mailadres.';
  exit;
}

// Build email
$subject = 'Nieuw bericht via contactformulier — Nu leren voor later';
$body = "Naam: {$name}\nE-mail: {$email}\n\nBericht:\n{$message}\n";
$headers = [];
$headers[] = 'From: Nu leren voor later <no-reply@nulerenvoorlater.nl>';
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headersStr = implode("\r\n", $headers);

// Send
$ok = @mail($to, $subject, $body, $headersStr);

if ($ok) {
  header('Location: contact-bedankt.html');
  exit;
} else {
  http_response_code(500);
  ?><!doctype html>
  <html lang="nl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-2XKMZ9VR1Z"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);} 
    gtag('js', new Date());
    gtag('consent', 'default', { ad_storage: 'denied', analytics_storage: 'denied' });
  </script>
  <title>Fout bij verzenden — Nu leren voor later</title>
  <link rel="stylesheet" href="css/styles.css"></head>
  <body>
    <main class="container">
      <h1>Er ging iets mis</h1>
      <p>Je bericht kon niet worden verzonden. Probeer het later opnieuw of mail ons direct op <a href="mailto:info@nulerenvoorlater.nl">info@nulerenvoorlater.nl</a>.</p>
      <p><a class="btn-primary" href="contact.html">Terug naar het formulier</a></p>
    </main>
  </body></html><?php
  exit;
}
