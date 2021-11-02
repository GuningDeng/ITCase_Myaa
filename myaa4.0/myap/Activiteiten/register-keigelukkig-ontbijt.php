<?php
include('../../config/vzwvrouwencentrum.modernways.be.php');
include('../../vendor/threepenny/helpers.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../../vendor/PHPMailer/src/Exception.php';
require '../../vendor/PHPMailer/src/PHPMailer.php';
require '../../vendor/PHPMailer/src/SMTP.php';

$model['message'] = array();
$model['fields'] = array(
    'email' => ['name' => 'email', 'display' => 'E-mail', 'pattern' => REGEX_EMAIL, 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'first-name' => ['name' => 'first-name', 'display' => 'Voornaam', 'pattern' => REGEX_LATIN, 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'last-name' => ['name' => 'last-name', 'display' => 'Familienaam', 'pattern' => REGEX_LATIN, 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'street' => ['name' => 'street', 'display' => 'Straatnaam', 'pattern' => REGEX_LATIN, 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'postal-code' => ['name' => 'postal-code', 'display' => 'Postcode', 'pattern' => REGEX_ALPHANUMERIC, 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'city' => ['name' => 'city', 'display' => 'Stad', 'pattern' => REGEX_LATIN, 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'country' => ['name' => 'country', 'display' => 'Land', 'pattern' => REGEX_LATIN, 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'phone' => ['name' => 'phone', 'display' => 'Telefoon', 'pattern' => REGEX_NUMERIC, 'required' => false, 'error' => false, 'message' => '', 'value' => ''],
    'time' => ['name' => 'time', 'display' => 'Tijdstip', 'pattern' => '', 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'choice' => ['name' => 'choice', 'display' => 'Keuze van ontbijt', 'pattern' => '', 'required' => true, 'error' => false, 'message' => '', 'value' => ''],
    'agreement' => ['name' => 'agreement', 'display' => 'Ik ga akkoord met de privacy policy', 'pattern' => '', 'required' => true, 'error' => false, 'message' => '', 'value' => '']
);
$model['valid'] = true;
// var_dump($_POST);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Loop over field names, make sure each one exists and is not empty
    foreach ($model['fields'] as $field) {
        if ($field['name'] === 'file-name') {
            $value = $_FILES['file-name'];
        } else {
            $value = $_POST[$field['name']];
        }

        if (empty($value)) {
            if ($field['required']) {
                $field['message'] = "{$field['display']} mag niet leeg zijn";
                $field['error'] = true;
                $model['valid'] = false;
            }
        } elseif (!empty($field['pattern'])) {
            if (!preg_match($field['pattern'], $_POST[$field['name']])) {
                $field['message'] = "Je hebt een fout getypt in {$field['display']}";
                $field['error'] = true;
                $model['valid'] = false;
            }
        }
        if (!$field['error']) {
            if ($field['name'] === 'file-name') {
                $field['value'] = $_FILES['file-name'];
            } else {
                $field['value'] = Threepenny\Helpers::cleanUp($_POST[$field['name']]);
            }
        }
        $model['fields'][$field['name']] = $field;
        // var_dump($model);
        $model['message'][] = $field['message'];
    }
    if ($model['valid']) {
        $text = '';
        foreach ($model['fields'] as $field) {
            $text .= "<p class=\"{$field['name']}\">{$field['value']}</p>\n";
        }
        $date = date("Y-m-d");
        $time = date("H\hi\ms\s");
        $fileName = $model['fields']['first-name']['value'] . '_' . $model['fields']['last-name']['value'];
        $fileName = Threepenny\Helpers::makeSafe($fileName) . '.html';
        $fullFileName = '../../upload/keigelukkig-ontbijt/' . $date . '_' . $time . '_' . $fileName;
        // echo $fullFileName;
        if (file_put_contents($fullFileName, $text)) {
            $model['message'][] = "$fileName is opgeslagen!";
            // mail versturen: https://github.com/PHPMailer/PHPMailer
            //Instantiation and passing `true` enables exceptions
            $mail = new PHPMailer(true);

            try {
                //Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER; //Enable verbose debug output
                $mail->isSMTP(); //Send using SMTP
                $mail->Host = SMTP_SERVER; //Set the SMTP server to send through
                $mail->SMTPAuth = true;  //Enable SMTP authentication
                $mail->Username = SMTP_USERNAME; //SMTP username
                $mail->Password = SMTP_PASSWORD; //SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                $mail->Port = SMTP_PORT; //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                //Recipients
                $mail->setFrom('jef.inghelbrecht@modernways.be', 'VZWVrouwencentrum Webmaster');
                $mail->addAddress(
                    $model['fields']['email']['value'],
                    $model['fields']['first-name']['value'] . ' ' . $model['fields']['last-name']['value']
                );     //Add a recipient
                // $mail->addReplyTo('jef.inghelbrecht@modernways.be', 'Information');
                $mail->addBCC('vzwvrouwencentrum@skynet.be');
                $mail->addBCC('joseph.inghelbrecht@outlook.be');

                // $mail->addCC('cc@example.com');

                //Attachments
                // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
                // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

                //Content
                $mail->isHTML(true);  //Set email format to HTML
                $mail->Subject = 'Reservering kEIgelukkig ONTBIJT';
                $textBody = "Beste {$model['fields']['first-name']['value']} {$model['fields']['last-name']['value']},<br><br>";
                $textBody .= "We hebben van jou de volgende reservering voor het kEIgelukkig ONTBIJT op zaterdag 20 november 2021 ontvangen:<br>";
                $textBody .= $text;
                $textBody .= "<br><br>Groeten,<br>Team Vrouwencentrum";
                $mail->Body = $textBody;
                $mail->AltBody = $textBody;

                $mail->send();
                $model['message'][] = "We hebben een bevestigingsmail gestuurd naar {$model['fields']['email']['value']}.";
                $model['message'][] = "Dit bericht kan in de map Ongewenste e-mail terecht gekomen zijn.";
                $model['message'][] = "Kijk in de map Ongewenste e-mail als je geen bericht hebt ontvangen.";
            } catch (Exception $e) {
                $model['message'][] = "We konden geen bevestigingsmail sturen naar {$model['fields']['email']['value']}.";
            }
        } else {
            $model['message'][] = "$fileName is niet opgeslagen.";
        }
    } else {
        $model['message'][] = "Onjuiste gegevens ingetypt!";
    }
    // var_dump($model);
    // var_dump($_POST);
}
?>
<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link type="text/css" rel="stylesheet" href="/css/app.css?version=1.0.0">
    <style>
        #sent {
            display: none;
        }

        #sent:target {
            display: block;
        }

        #sent:target+form {
            display: none;
        }
    </style>
    <title>kEIgelukkig ONTBIJT</title>
</head>

<body class="floor">
<header class="control-panel">
        <a href="/index.html"><img class="logo" src="/image/over-ons/logo655x267.jpg"
                alt="Logo van VZW Vrouwencentrum Sint-Niklaas"></a>
        <nav>
            <a href="/index.html">In het nieuws</a>
            <a href="/Cursussen/index.html">Cursussen</a>
            <a href="/Activiteiten/index.html">Activiteiten</a>
            <a href="/Uitstappen/index.html">Uitstappen</a>
            <a href="/Over ons/index.html">Over ons</a>
        </nav>
    </header>
    <section class="show-room">
    <nav class="command-bar-article">
        <a href="javascript:history.back()" class="icon">
            <span class="icon-arrow-left"></span>
            <span class="screen-reader-text">Back</span>
        </a>
    </nav>   
    <article>
        <h1>Reserveren voor het kEIgelukkig ONTBIJT</h1>
         <figure class="picture">
            <img src="/image/activiteiten/10-2021/keigelukkig-ontbijt.jpg" alt="Affiche Keigelukkig ontbijt" />
        </figure>
        <section id="sent">
            <?php
            foreach ($model['message'] as $message) { ?>
                <p class="message">
                    <?php echo $message; ?>
                </p>
            <?php
            }
            ?>
        </section>
       <form class="upload" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '#sent'); ?>" enctype="multipart/form-data">
            <div class="two">
                Tijdstip
            </div>
            <div class="first">
                <input type="radio" name="time" id="9u-10u30" value="9u-10u30" required>
            </div>
            <div class="second">
                <label for="9u-10u30">9u-10u30</label>
            </div>
            <div class="first">
                <input type="radio" name="time" id="11u-12u30" value="11u-12u30">
            </div>
            <div class="second">
                <label for="11u-12u30">11u-12u30</label>
            </div>
            <div class="two">
                Omelet
            </div>
            <div class="first">
                <input type="radio" name="choice" id="omelet-natuur" value="omelet natuur" required>
            </div>
            <div class="second">
                <label for="omelet-natuur">natuur</label>
            </div>
            <div class="first">
                <input type="radio" name="choice" id="omelet-spek" value="omelet spek">
            </div>
            <div>
                <label for="omelet-spek">spek</label>
            </div>
            <div class="first">
                <input type="radio" name="choice" id="omelet-champignons-tomaat" value="omelet champignons + tomaat">
            </div>
            <div class="second">
                <label for="omelet-champignons-tomaat">champignons + tomaat</label>
            </div>
            <div class="first">
                <label for="email">Email</label>
                <span>*</span>
            </div>
            <div class="second">
                <input type="email" name="email" id="email" required>
                <span><?php echo $model['fields']['email']['message'] ?></span>
            </div>

            <div class="first">
                <label for="first-name">Voornaam</label>
                <span>*</span>
            </div>
            <div class="second">
                <input type="text" name="first-name" id="first-name" required>
                <span><?php echo $model['fields']['first-name']['message'] ?></span>
            </div>

            <div class="first">
                <label for="last-name">Familienaam</label>
                <span>*</span>
            </div>
            <div class="second">
                <input type="text" name="last-name" id="last-name" required>
                <span><?php echo $model['fields']['last-name']['message'] ?></span>
            </div>

            <div class="first">
                <label for="street">Straatnaam</label>
                <span>*</span>
            </div>
            <div class="second">
                <input type="text" name="street" id="street" required>
                <span><?php echo $model['fields']['street']['message'] ?></span>
            </div>

            <div class="first">
                <label for="postal-code">Postcode</label>
                <span>*</span>
            </div>
            <div class="second">
                <input type="text" name="postal-code" id="postal-code" required>
                <span><?php echo $model['fields']['postal-code']['message'] ?></span>
            </div>

            <div class="first">
                <label for="city">Stad</label>
                <span>*</span>
            </div>
            <div class="second">
                <input type="text" name="city" id="city" required>
                <span><?php echo $model['fields']['city']['message'] ?></span>
            </div>
            <div class="first">
                <label for="country">Land</label>
                <span>*</span>
            </div>
            <div class="second">
                <input type="text" name="country" id="country" value="België" required>
                <span><?php echo $model['fields']['country']['message'] ?></span>

            </div>
            <div class="first">
                <label for="phone">Telefoon</label>
            </div>
            <div class="second">
                <input type="tel" name="phone" id="phone">
                <span><?php echo $model['fields']['phone']['message'] ?></span>
            </div>
            <div class="two">
                <input type="checkbox" name="agreement" id="agreement" value="Ik ben akkoord met het privacybeleid." required />
                <label for="agreement">Ik ga akkoord met het <a href="/liefdesverklaring-nl/Reglement wedstrijd.pdf" download>privacybeleid</a>.</label>
                <span>*</span>
                <!-- https://heropstarthoreca.be/privacybeleid/ -->
            </div>

            <button type="submit" value="" name="submit">Verzenden</button>
        </form>
    </article>
    </section>
    <footer>
        <div class="contact-info">
            <div class="fn">Nursel Tekin</div>
            <div class="email" href="vzwvrouwencentrum@skynet.be">vzwvrouwencentrum@skynet.be</div>
            <div class="tel">03 777 97 00</div>
            <div class="adr">
                <div class="street-address">Nieuwstraat 34</div>
                <span class="postal-code">9100</span>
                <span class="locality">Sint-Niklaas</span>
                <div class="country-name">België</div>
                <div>
                    <?php echo isset($counterVal) ? $counterVal : '';?>
                </div>
            </div>
        </div>
        <div><img src="/image/over-ons/logo-cultuurlab.png" alt="Logo Culruurlab Vlaanderen" style="height: 8em;;">
        </div>
        <div><img src="/image/over-ons/logo-sint-niklaas.jpg" alt="Logo stad Sint-Niklaas"></div>
    </footer>
</body>

</html>