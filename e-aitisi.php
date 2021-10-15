<?php
    header('Content-Type: text/html; charset=utf-8');
    include 'adodb5/adodb.inc.php';
    require 'emailconf.php';

    $driver = 'mysqli';
    $db = newADOConnection($driver);
    //$db->debug = true;
    $db->debug = false;
    $db->connect($hostname, $dbuser, $dbpassword, $database);

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

    // If in production, login using sch.gr's CAS server
    // (To be able to login via sch.gr's CAS, the app must be whitelisted from their admins)
    // phpCAS simple client, import phpCAS lib (downloaded with composer)
    require_once('phpCAS/CAS.php');
    //initialize phpCAS using SAML
    phpCAS::client(SAML_VERSION_1_1, 'sso-test.sch.gr', 443, '');
    // if logout
    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        phpCAS::logout();
    }

    // no SSL validation for the CAS server, only for testing environments
    phpCAS::setNoCasServerValidation();
    // handle backend logout requests from CAS server
    phpCAS::handleLogoutRequests(array('sso-test.sch.gr'));
    // force CAS authentication
    if (!phpCAS::checkAuthentication())
        phpCAS::forceAuthentication();
    // at this step, the user has been authenticated by the CAS server and the user's login name can be read with phpCAS::getUser().
    $_SESSION['loggedin'] = 1;
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

<?php
    $_SESSION['uid'] = phpCAS::getUser();
    $_SESSION['am'] = phpCAS::getAttribute('employeenumber');
    $_SESSION['name'] = phpCAS::getAttribute('cn');
    $_SESSION['email'] = phpCAS::getAttribute('mail');
    $_SESSION['school_name'] = phpCAS::getAttribute('ou');
    $_SESSION['mobile'] = phpCAS::getAttribute('mobile');
?>


<body class="container pt-3 my-3 border">
    <img src="logo_dipe2.png" alt="Λογότυπο Δ.Π.Ε. Σερρών">
    <h1 class="display-4">Αίτηση χορήγησης πιστοποιητικού</h1>
    <br>
    <hr>
    <br>
    
    <?php

    // write to log...
    // $fname = "login_log.txt";
    // $fh = fopen($fname, 'a');
    // $data = $_SESSION['uid'] . "\t" . $_SESSION['name'] . "\t" . date('d-m-Y, H:i:s') . "\t" . $_SERVER['HTTP_USER_AGENT'] . "\t" . "LOGIN" . "\n";
    // fwrite($fh, $data);
    // fclose($fh);

    // print("<pre>" . print_r($_SESSION, true) . "</pre>");

    // $sql = 'INSERT INTO logins(username, name, agent) VALUES ("'.$_SESSION['uid'].'", "'.$_SESSION['name'].'", "'.$_SERVER['HTTP_USER_AGENT'].'")';
    
    $record = array();
    $record['username'] = $_SESSION['uid'];
    $record['name'] = $_SESSION['name'];
    $record['agent'] = $_SERVER['HTTP_USER_AGENT'];
    $record['date'] = date('Y-m-d H:i:s');
    $db->autoExecute('logins',$record,'INSERT');

    ?>

    <form action="sendemail.php" method="post" class="needs-validation" novalidate>
        <h4>Στοιχεία αιτούντος</h4>
        <h5>(συμπληρώστε ή τροποποιήστε ανάλογα)</h5>
        <div class="form-row">
            <div class="col">
                <label for="onoma">Ονοματεπώνυμο</label>
                <?php
                echo '<input type="text" class="form-control" id="onoma" placeholder="Όνομα" name="onoma" value="' . $_SESSION['name'] . '" readonly="readonly" required>';
                ?>
            </div>
            <div class="col">
                <label for="am">Αριθμός Μητρώου</label>
                <?php
                echo '<input type="text" class="form-control" id="am" placeholder="Αριθμός Μητρώου" name="am" value="' . $_SESSION['am'] . '" readonly="readonly" required>';
                ?>
            </div>
        </div>
        <br>

        <div class="form-row">
            <div class="col">
                <label for="school">Σχολική Μονάδα Οργανικής/Προσωρινής (κύριας) τοποθέτησης:</label>
                <?php
                    echo '<input type="text" class="form-control" id="school_org" placeholder="Σχολείο" name="school_org" value="' . $_SESSION['school_name'] . '" readonly="readonly">';
                ?>
            </div>
            <div class="col">
                <label for="school">Σχολική Μονάδα που υπηρετείτε:</label>
                <?php
                echo '<input type="text" class="form-control" id="school_yp" placeholder="Σχολείο" name="school_yp" value="' . $_SESSION['school_name'] . '">';
                ?>
            </div>
        </div>
        <br>

        <div class="form-row">
            <div class="col">
                <label for="tel_kinito">Κινητό Τηλέφωνο</label>
                <?php
                    //echo '<input type="text" class="form-control" id="tel_kinito" placeholder="Κινητό Τηλέφωνο" name="tel_kinito" value="'.$_SESSION['mobile'].'"required>';
                    echo '<input type="text" class="form-control" id="tel_kinito" placeholder="Κινητό Τηλέφωνο" name="tel_kinito" required>';
                ?>
                <div class="invalid-feedback">Παρακαλώ δώστε το κινητό σας τηλέφωνο</div>
            </div>
            <div class="col">
                <label for="email">Ηλεκτρονικό Ταχυδρομείο</label>
                <?php
                    echo '<input type="email" class="form-control" id="email" placeholder="Ηλεκτρονικό Ταχυδρομείο" name="email" value="'.$_SESSION['email'].'" required>';
                ?>
                <div class="invalid-feedback">Παρακαλώ δώστε το email σας</div>
            </div>
        </div>
        <br>

        <h4>Τρόπος παράδοσης</h4>
        <div class="form-row">
            <div class="col">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="apostoli" id="apostoli">
                    <label class="custom-control-label" for="apostoli">Ηλεκτρονική αποστολή στο email μου</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="thyrida" id="thyrida">
                    <label class="custom-control-label" for="thyrida">Στη θυρίδα του σχολείου μου</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" name="paralavi" id="paralavi">
                    <label class="custom-control-label" for="paralavi">Παραλαβή απ' τη Δ.Π.Ε. Σερρών (μετά από τηλεφωνική συνεννόηση)</label>
                </div>
                <div>
                </div>
                <br>

                <h4>Επιλέξτε το είδος του πιστοποιητικού που επιθυμείτε</h4>
                <h5>(μπορείτε να επιλέξετε περισσότερα του ενός) </h5>
                <div class="form-row">
                    <div class="col">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="ypiresiaki_katastasi" id="ypiresiaki_katastasi">
                            <label class="custom-control-label" for="ypiresiaki_katastasi">Βεβαίωση υπηρεσιακής κατάστασης</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="ypiresiakon_metabolon" id="ypiresiakon_metabolon">
                            <label class="custom-control-label" for="ypiresiakon_metabolon">Πιστοποιητικό υπηρεσιακών μεταβολών</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="miniaies_apodoxes" id="miniaies_apodoxes">
                            <label class="custom-control-label" for="miniaies_apodoxes">Πρόσφατο ενημερωτικό σημείωμα μηνιαίων αποδοχών</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="apodoxes_2011" id="apodoxes_2011">
                            <label class="custom-control-label" for="apodoxes_2011">Βεβαίωση μηνιαίων αποδοχών (Οκτώβριος 2011 - ειδικά για συνταξιοδότηση)</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" name="allo" id="allo">
                            <label class="custom-control-label" for="allo">Άλλο (γράψτε στις παρατηρήσεις)</label>
                        </div>
                        <div>
                        </div>
                        <br>

                        <h4>Παρατηρήσεις</h4>
                        <div class=form-row>
                            <!--label for="paratiriseis">Παρατηρήσεις</label-->
                            <textarea class="form-control" row="10" name="paratiriseis" id="paratiriseis"></textarea>
                        </div>

                        <br>
                        <h5>Με την αποστολή της παρούσης αίτησης, συναινώ στην αποστολή των άνω προσωπικών μου στοιχείων, ώστε να μου χορηγηθεί το αιτούμενο πιστοποιητικό/βεβαίωση</h5>
                        <br>

                        <button type="submit" class="btn btn-primary btn-lg btn-block">Υποβολή</button>

                        <br>
    </form>

    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>

    <form action='' method='POST'>
        <input type='submit' class="btn btn-danger btn-lg btn-block" name='logout' value='Έξοδος'>
    </form>

    <br>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <!-- Bootstrap 4 Autocomplete -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-4-autocomplete/dist/bootstrap-4-autocomplete.min.js" crossorigin="anonymous"></script>


</body>

</html>