<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    require 'emailconf.php';
    include 'adodb5/adodb.inc.php';

    $driver = 'mysqli';
    $db = newADOConnection($driver);
    //$db->debug = true;
    $db->debug = false;
    $db->connect($hostname, $dbuser, $dbpassword, $database);

    $date = date('Y-m-d H:i:s');

    
    /*if ($db->IsConnected()) {
        echo "Connected";
    }
    else {
        echo "Connection failed";
    }*/

    if (!($db->IsConnected())) {
        echo "Connection failed";
    }

    session_start();
?>

<!doctype html>
<html lang="el">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <title>Αίτηση χορήγησης πιστοποιητικού</title>
</head>
<body class="container pt-3 my-3 border">

<?php

    //print("<pre>" . print_r($_SESSION, true) . "</pre>");
    //print("<pre>" . print_r($_POST, true) . "</pre>");

    //mail data
    $from = $_POST['from'];
    $onoma = $_POST['onoma'];
    $am = $_POST['am'];
    $sendto = $_POST['email'];

    $paralavi = '';
    $pistopoiitika = '';
    $paratiriseis = '';

    //$message = "<h2>Ηλεκτρονική Αίτηση</h2>";

    // Στοιχεία αιτούντος
    $message ="<h3>Στοιχεία αυτούντος</h3>";
    $message .="Ονοματεπώνυμο: <b>".$_POST['onoma']."</b><br>";
    $message .="Αριθμός Μητρώου: <b>".$_POST['am']."</b><br>";
    $message .="Σχολείο οργανικής/προσωρινής τοποθέτησης: <b>".$_POST['school_org']."</b><br>";
    $message .="Σχολείο που υπηρετεί: <b>".$_POST['school_yp']."</b><br>";
    $message .="Τηλέφωνο επικοινωνίας: <b>".$_POST['tel_kinito']."</b><br>";
    $message .="Email επικοινωνίας: <b>".$_POST['email']."</b><br>";
    $message .="<br><hr>";

    //Τρόπος παράδοσης
    $message .="<h3>Τρόπος παράδοσης</h3>";
    if (isset($_POST['apostoli'])) {
        $message .="&#9746 Ηλεκτρονική αποστολή στο email μου ".$_POST['email']."<br>";
        $paralavi .= 'Αποστολή στο email';
    }
    else {
        $message .="&#9744 Ηλεκτρονική αποστολή στο email μου<br>";
    }

    if (isset($_POST['thyrida'])) {
        $message .="&#9746 Στη θυρίδα του σχολείου μου ".$_POST['school_yp']."<br>";
        $paralavi .= '\tΣτη θυρίδα του σχολείου';
    }
    else {
        $message .="&#9744 Στη θυρίδα του σχολείου μου<br>";
    }

    if (isset($_POST['paralavi'])) {
        $message .="&#9746 Παραλαβή απ' τη Δ.Π.Ε. Σερρών (μετά από τηλεφωνική συνεννόηση)<br>";
        $paralavi .= '\tΠαραλαβή στη Δ.Π.Ε.';
    }
    else {
        $message .="&#9744 Παραλαβή απ' τη Δ.Π.Ε. Σερρών (μετά από τηλεφωνική συνεννόηση)<br>";
    }
    $message .="<br><hr>";

    //Είδος πιστοποιητικού
    $message .="<h3>Επιλέξτε το είδος του πιστοποιητικού που επιθυμείτε</h3>";
    if (isset($_POST['ypiresiaki_katastasi'])) {
        $message .="&#9746 Βεβαίωση υπηρεσιακής κατάστασης<br>";
        $pistopoiitika .= 'Βεβαίωση υπηρεσιακής κατάστασης';
    }
    else {
        $message .="&#9744 Βεβαίωση υπηρεσιακής κατάστασης<br>";
    }

    if (isset($_POST['ypiresiakon_metabolon'])) {
        $message .="&#9746 Πιστοποιητικό υπηρεσιακών μεταβολών<br>";
        $pistopoiitika .= '\tΠιστοποιητικό υπηρεσιακών μεταβολών';
    }
    else {
        $message .="&#9744 Πιστοποιητικό υπηρεσιακών μεταβολών<br>";
    }

    if (isset($_POST['miniaies_apodoxes'])) {
        $message .="&#9746 Πρόσφατο ενημερωτικό σημείωμα μηνιαίων αποδοχών<br>";
        $pistopoiitika .= '\tΠρόσφατο ενημερωτικό σημείωμα μηνιαίων αποδοχών';
    }
    else {
        $message .="&#9744 Πρόσφατο ενημερωτικό σημείωμα μηνιαίων αποδοχών<br>";
    }

    if (isset($_POST['apodoxes_2011'])) {
        $message .="&#9746 Βεβαίωση μηνιαίων αποδοχών (Οκτώβριος 2011 - ειδικά για συνταξιοδότηση)<br>";
        $pistopoiitika .= '\tΒεβαίωση μηνιαίων αποδοχών (Οκτώβριος 2011 - ειδικά για συνταξιοδότηση)';
    }
    else {
        $message .="&#9744 Βεβαίωση μηνιαίων αποδοχών (Οκτώβριος 2011 - ειδικά για συνταξιοδότηση)<br>";
    }

    if (isset($_POST['allo'])) {
        $message .="&#9746 Άλλο (γράψτε στις παρατηρήσεις)<br>";
        $pistopoiitika .= '\tΆλλο';
    }
    else {
        $message .="&#9744 Άλλο (γράψτε στις παρατηρήσεις)<br>";
    }

    $message .="<hr>";

    $message .="<h3>Παρατηρήσεις</h3>";
    $message .='<textarea class="form-control" row="10" name="paratiriseis" id="paratiriseis">'.$_POST['paratiriseis']."</textarea><hr>";
    $paratiriseis .= $_POST['paratiriseis'];

    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // insert into database
    $record = array();
    $record['username'] = $_SESSION['uid'];
    $record['name'] = $_SESSION['name'];
    $record['paralavi'] = $paralavi;
    $record['pistopoiitika'] = $pistopoiitika;
    $record['paratiriseis'] = $paratiriseis;
    $record['date'] = $date;
    
    $db->autoExecute('emails',$record,'INSERT');
    $lastId = $db->insert_Id();

    // make and send email
    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER; //Enable verbose debug output

        $mail->isSMTP(); //Send using SMTP
        $mail->Host = $smtpserver; //Set the SMTP server to send through
        $mail->SMTPAuth = true; //Enable SMTP authentication
        $mail->Username = $smtpusername; //SMTP username
        $mail->Password = $smtppassword; //SMTP password
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
        //$mail->Port = 465; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable implicit TLS encryption
        $mail->Port = 587; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`


        //Recipients
        $mail->setFrom($sendfrom, 'Ηλεκτρονική Αίτηση Πιστοποιητικού');
        $mail->addAddress($sendto, $onoma); //Add a recipient
        //$mail->addAddress('ellen@example.com'); //Name is optional
        $mail->addReplyTo($sendto, 'Information');
        $mail->addCC($ccemail);
        $mail->addBCC($bccemail);

        //Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz'); //Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg'); //Optional name

        //Content
        $mail->isHTML(true); //Set email format to HTML
        // embed image
        $mail->AddEmbeddedImage('logo_dipe2.png', 'logo_dipe');
        
        $subject = "Υποβολή ηλεκτρονικής αίτησης από ".$onoma." με Α.Μ. ".$am." (αύξων αριθμός αίτησης: ".$lastId.")";
        $message_to_send = '<img src="cid:logo_dipe" alt="Λογότυπο Δ.Π.Ε. Σερρών"><h2>Αύξων αριθμός αίτησης '.$lastId.'. Ημερομηνία '.$date.'</h2>'.$message;

        $mail->Subject = $subject;
        $mail->Body = $message_to_send;
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        
        echo '<h2>To μήνυμά σας έχει αποσταλεί στις: '.$date.'</h2>';
        echo '<h2>Σας έχει σταλεί αντίγραφο στο '.$sendto.'</h2>';
        echo '<hr>';
        echo '<img src="logo_dipe2.png" alt="Λογότυπο Δ.Π.Ε. Σερρών"><h2>Αύξων αριθμός αίτησης '.$lastId.'. Ημερομηνία '.$date.'</h2>'.$message;
              
        echo '  <form action="e-aitisi.php" method="POST">
                    <div class="form-row">
                        <input type="submit" class="btn  btn-danger btn-lg btn-block" name="logout" value="Έξοδος">
                    </div>
                    <div class="form-row"></div>
                </form>';

    }
    catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

    echo '</body></html>';

?>