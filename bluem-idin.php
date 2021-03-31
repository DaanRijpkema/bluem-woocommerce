<?php

if (!defined('ABSPATH')) {
    exit;
}

use Bluem\BluemPHP\Integration as Integration;
use Carbon\Carbon;

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    // WooCommerce specific code incoming here
}

function bluem_register_session()
{
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'bluem_register_session');

function bluem_woocommerce_get_idin_option($key)
{
    $options = bluem_woocommerce_get_idin_options();
    if (array_key_exists($key, $options)) {
        return $options[$key];
    }
    return false;
}

function bluem_woocommerce_get_idin_options()
{
    $idinDescriptionTags = (
        function_exists('bluem_get_IDINDescription_tags')?
        bluem_get_IDINDescription_tags() : []
    );
    $idinDescriptionReplaces = (
        function_exists('bluem_get_IDINDescription_replaces')?
        bluem_get_IDINDescription_replaces() : []
    );
    $idinDescriptionTable = "<table><thead><tr><th>Invulveld</th><th>Voorbeeld invulling</th></tr></thead><tbody>";
    foreach ($idinDescriptionTags as $ti => $tag) {
        if (!isset($idinDescriptionReplaces[$ti])) {
            continue;
        }
        $idinDescriptionTable.= "<tr><td><code>$tag</code></td><td>".$idinDescriptionReplaces[$ti]."</td></tr>";
    }

    $idinDescriptionTable.="</tbody></table>";
    $options = get_option('bluem_woocommerce_options');

    if ($options !==false
        && isset($options['IDINDescription'])
    ) {
        $idinDescriptionCurrentValue = bluem_parse_IDINDescription(
            $options['IDINDescription']
        );
    } else {
        $idinDescriptionCurrentValue = bluem_parse_IDINDescription(
            "Identificatie {gebruikersnaam}"
        );
    }

    return [
        'IDINBrandID' => [
            'key' => 'IDINBrandID',
            'title' => 'bluem_IDINBrandID',
            'name' => 'IDIN BrandId',
            'description' => '',
            'default' => ''
        ],



        'idin_scenario_active' => [
            'key' => 'idin_scenario_active',
            'title' => 'bluem_idin_scenario_active',
            'name' => 'IDIN Scenario',
            'description' => "Wil je een leeftijd- of volledige adrescontrole uitvoeren bij Checkout?",
            'type' => 'select',
            'default' => '0',
            'options' => [
                '0' => 'Voer geen identiteitscheck uit voor de checkout procedure',
                '1' => 'Check op de minimumleeftijd door middel van een AgeCheckRequest',
                '2' => 'Voer een volledige identiteitscontrole uit en sla dit op, maar blokkeer de checkout NIET indien minimumleeftijd niet bereikt is',
                '3' => 'Voer een volledige identiteitscontrole uit, sla dit op EN  blokkeer de checkout WEL indien minimumleeftijd niet bereikt is',

            ],
        ],

        'idin_check_age_minimum_age' => [
            'key' => 'idin_check_age_minimum_age',
            'title' => 'bluem_idin_check_age_minimum_age',
            'name' => 'Minimumleeftijd',
            'description' => "Wat is de minimumleeftijd, in jaren? Indien de plugin checkt op leeftijd, wordt deze waarde gebruikt om de check uit te voeren.",
            'type' => 'number',
            'default' => '18',
        ],
        'idin_request_name' => [
            'key' => 'idin_request_name',
            'title' => 'bluem_idin_request_name',
            'name' => 'Naam opvragen?',
            'description' => "Indien je een volledige identiteitscontrole uitvoert, wil je dan de naam opvragen?",
            'type' => 'bool',
            'default' => '1',
        ],
        'idin_request_address' => [
            'key' => 'idin_request_address',
            'title' => 'bluem_idin_request_address',
            'name' => 'Adres opvragen?',
            'description' => "Indien je een volledige identiteitscontrole uitvoert, wil je dan het woonadres opvragen?",
            'type' => 'bool',
            'default' => '1',
        ],
        'idin_request_birthdate' => [
            'key' => 'idin_request_birthdate',
            'title' => 'bluem_idin_request_birthdate',
            'name' => 'Geboortedatum opvragen?',
            'description' => "Indien je een volledige identiteitscontrole uitvoert, wil je dan de geboortedatum opvragen? Dit gegeven wordt ALTIJD opgevraagd indien je ook op de minimumleeftijd controleert",
            'type' => 'bool',
            'default' => '1',
        ],
        'idin_request_gender' => [
            'key' => 'idin_request_gender',
            'title' => 'bluem_idin_request_gender',
            'name' => 'Geslacht opvragen?',
            'description' => "Indien je een volledige identiteitscontrole uitvoert, wil je dan het geslacht opvragen?",
            'type' => 'bool',
            'default' => '0',
        ],
        'idin_request_telephone' => [
            'key' => 'idin_request_telephone',
            'title' => 'bluem_idin_request_telephone',
            'name' => 'Adres opvragen?',
            'description' => "Indien je een volledige identiteitscontrole uitvoert, wil je dan het telefoonnummer opvragen?",
            'type' => 'bool',
            'default' => '1',
        ],
        'idin_request_email' => [
            'key' => 'idin_request_email',
            'title' => 'bluem_idin_request_email',
            'name' => 'E-mailadres opvragen?',
            'description' => "Indien je een volledige identiteitscontrole uitvoert, wil je dan het e-mailadres opvragen?",
            'type' => 'bool',
            'default' => '1',
        ],

        'IDINSuccessMessage' => [
        'key' => 'IDINSuccessMessage',
        'title' => 'bluem_suIDINSuccessMessage',
        'name' => 'Melding bij succesvolle Identificatie via shortcode',
        'description' => 'Een bondige beschrijving volstaat.',
        'default' => 'Uw identificatie is succesvol ontvangen. Hartelijk dank.'
    ],
    'IDINErrorMessage' => [
        'key' => 'IDINErrorMessage',
        'title' => 'bluem_IDINErrorMessage',
        'name' => 'Melding bij gefaalde Identificatie via shortcode',
        'description' => 'Een bondige beschrijving volstaat.',
        'default' => 'Er is een fout opgetreden. De identificatie is geannuleerd.'
    ],
    'IDINPageURL' => [
        'key' => 'IDINPageURL',
        'title' => 'bluem_IDINPageURL',
        'name' => 'URL vanwaar Identificatie gestart wordt',
        'description' => 'van pagina waar het Identificatie proces wordt weergegeven, bijvoorbeeld een accountpagina. De gebruiker komt op deze pagina terug na het proces',
        'default' => 'my-account'
    ],
    // 'IDINCategories' => [
    //     'key' => 'IDINCategories',
    //     'title' => 'bluem_IDINCategories',
    //     'name' => 'Comma separated categories in iDIN shortcode requests',
    //     'description' => 'Opties: CustomerIDRequest, NameRequest, AddressRequest, BirthDateRequest, AgeCheckRequest, GenderRequest, TelephoneRequest, EmailRequest',
    //     'default' => 'AddressRequest,BirthDateRequest'
    // ],

    'IDINShortcodeOnlyAfterLogin' => [
        'key' => 'IDINShortcodeOnlyAfterLogin',
        'title' => 'bluem_IDINShortcodeOnlyAfterLogin',
        'name' => 'Shortcode beperken tot ingelogde gebruikers',
        'description' => "Moet het iDIN formulier via shortcode zichtbaar zijn voor iedereen of alleen ingelogde gebruikers?",
        'type' => 'select',
        'default' => '0',
        'options' => [
            '0' => 'Voor iedereen',
            '1' => 'Alleen voor ingelogde bezoekers'
        ],
    ],
    'IDINDescription' => [
        'key' => 'IDINDescription',
        'title' => 'bluem_IDINDescription',
        'name' => 'Formaat beschrijving request',
        'description' => '

        <div style="width:400px; float:right; margin:10px; font-size: 9pt;
        border: 1px solid #ddd;
        padding: 10pt;
        border-radius: 5pt;">
        Mogelijke invulvelden: '.
        $idinDescriptionTable.
        '<br>Let op: max 128 tekens. Toegestane karakters: <code>-0-9a-zA-ZéëïôóöüúÉËÏÔÓÖÜÚ€ ()+,.@&amp;=%&quot;&apos;/:;?$</code></div>'
        .
        'Geef het format waaraan de beschrijving van
            een identificatie request moet voldoen, met automatisch ingevulde velden.<br>Dit gegeven wordt ook weergegeven in de Bluem portal als de \'Inzake\' tekst.
            <br>Voorbeeld huidige waarde: <code style=\'display:inline-block;\'>'.
            $idinDescriptionCurrentValue.'</code><br>',
        'default' => 'Identificatie {gebruikersnaam}'
    ],

    'idin_add_field_in_order_emails' => [
        'key' => 'idin_add_field_in_order_emails',
        'title' => 'bluem_idin_add_field_in_order_emails',
        'name' => 'Identificatie status in emails',
        'description' => "Moet de status van identificatie worden weergegeven in de order notificatie email naar de klant en naar jezelf?",
        'type' => 'bool',
        'default' => '1',
    ],




    ];
}

function bluem_woocommerce_idin_settings_section()
{
    $options = get_option('bluem_woocommerce_options'); ?>
    <p><a id="tab_idin"></a>
    Hier kan je alle belangrijke gegevens instellen rondom iDIN (Identificatie).</p>
    <h3>
    <span class="dashicons dashicons-saved"></span>
    Automatische check:
    </h3>
    <p>
    <strong>
    <?php switch ($options['idin_scenario_active']) {

        case 0:
            {
                echo "Er wordt geen automatische check uitgevoerd";
                break;
            }
            case 1:
                {
                    echo "Er wordt een check gedaan op minimum leeftijd bij checkout";
                    break;
                }
                case 2:
                    {
                        echo "Er wordt een volledige identiteitscheck gedaan voor de checkout beschikbaar wordt
                        ";
                        break;
                    }
                    case 3:
                        {
                            echo "Er wordt een volledige identiteitscheck gedaan en op leeftijd gecontroleerd voor de checkout beschikbaar wordt
                            ";
                            break;
                        }
                    } ?>
                    </strong>

       </p>

       <?php if ($options['idin_scenario_active'] >= 1) {
                        ?>
        <p>
            Deze gegevens vraag je op het moment op de volledige identiteitscontrole voor checkout:<br/>
            <code style="display:inline-block;">
            <?php foreach (bluem_idin_get_categories() as $cat) {
                            echo "&middot; ".str_replace("Request", "", $cat)."<br>";
                        } ?>
            </code>
        </p>
           <?php
                    } ?>

    <h3>
    <span class="dashicons dashicons-welcome-write-blog"></span>
       Zelf op een pagina een iDIN verzoek initiëren
    </h3>
    <p>Het iDIN formulier werkt ook een shortcode, welke je kan plaatsen op een pagina, post of in een template. De shortcode is als volgt:
    <code>[bluem_identificatieformulier]</code>.
    </p>
    <p>
        Zodra je deze hebt geplaatst, is op deze pagina een blok zichtbaar waarin de status van de identificatieprocedure staat. Indien geen identificatie is uitgevoerd, zal er een knop verschijnen om deze te starten.
    </p>
    <p>
    Bij succesvol uitvoeren van de identificatie via Bluem, komt men terug op de pagina die hieronder wordt aangemerkt als iDINPageURL (huidige waarde:
    <code>
    <?php
if (isset($options['IDINPageURL'])) {
                        echo($options['IDINPageURL']);
                    } ?></code>).
    </p>
    <h3>
    <span class="dashicons dashicons-editor-help"></span>
       Waar vind ik de gegevens?
    </h3>
    <p>
        Gegevens worden na een identificatie opgeslagen bij het user profile als metadata. Je kan deze velden zien als je bij een gebruiker kijkt.
        Kijk bijvoorbeeld bij <a href="<?php echo admin_url('profile.php');?>" target="_blank">je eigen profiel</a>.
    </p>
    <h3>
    <span class="dashicons dashicons-admin-settings"></span>
       Identity instellingen en voorkeuren
    </h3>
    <?php
}

function bluem_woocommerce_settings_render_IDINSuccessMessage()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('IDINSuccessMessage')
    );
}

function bluem_woocommerce_settings_render_IDINErrorMessage()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('IDINErrorMessage')
    );
}

function bluem_woocommerce_settings_render_IDINPageURL()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('IDINPageURL')
    );
}

function bluem_woocommerce_settings_render_IDINCategories()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('IDINCategories')
    );
}

function bluem_woocommerce_settings_render_IDINBrandID()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('IDINBrandID')
    );
}


function bluem_woocommerce_settings_render_IDINShortcodeOnlyAfterLogin()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('IDINShortcodeOnlyAfterLogin')
    );
}

function bluem_woocommerce_settings_render_IDINDescription()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('IDINDescription')
    );
}

function bluem_woocommerce_settings_render_idin_scenario_active()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_scenario_active')
    );
}

function bluem_woocommerce_settings_render_idin_check_age_minimum_age()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_check_age_minimum_age')
    );
}


function bluem_woocommerce_settings_render_idin_request_address()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_request_address')
    );
}
function bluem_woocommerce_settings_render_idin_request_birthdate()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_request_birthdate')
    );
}
function bluem_woocommerce_settings_render_idin_request_gender()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_request_gender')
    );
}
function bluem_woocommerce_settings_render_idin_request_telephone()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_request_telephone')
    );
}
function bluem_woocommerce_settings_render_idin_request_email()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_request_email')
    );
}
function bluem_woocommerce_settings_render_idin_request_name()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_request_name')
    );
}


function bluem_woocommerce_settings_render_idin_add_field_in_order_emails()
{
    bluem_woocommerce_settings_render_input(
        bluem_woocommerce_get_idin_option('idin_add_field_in_order_emails')
    );
}



function bluem_idin_get_categories(int $preset_scenario = null)
{
    $catListObject = new BluemIdentityCategoryList();
    $options = get_option('bluem_woocommerce_options');

    // if you want to infer the scenario from the settings and not override it.
    if (is_null($preset_scenario)) {
        if (isset($options['idin_scenario_active']) && $options['idin_scenario_active']!=="") {
            $scenario = (int) $options['idin_scenario_active'];
        } else {
            $scenario = 0;
        }
    } else {
        $scenario = $preset_scenario;
    }



    // '0' => 'Voer geen identiteitscheck uit voor de checkout procedure', dus we overriden hier geen cats
    // then we don't have to do anything else here.

    // '1' => 'Check op de minimumleeftijd door middel van een AgeCheckRequest',
    if ($scenario == 1) {
        $catListObject->addCat("AgeCheckRequest");
        // return prematurely because we don't even consider the rest of the stuffs.
        return $catListObject->getCats();


    // '2' => 'Voer een volledige identiteitscontrole uit en sla dit op, maar blokkeer de checkout NIET indien minimumleeftijd niet bereikt is',
        // '3' => 'Voer een volledige identiteitscontrole uit, sla dit op EN  blokkeer de checkout WEL indien minimumleeftijd niet bereikt is',
    } elseif ($scenario == 2 || $scenario == 3) {
        // always ask for this
        $catListObject->addCat("CustomerIDRequest");

        if ($scenario == 3) {
            // deze moet verplicht mee
            $catListObject->addCat("BirthDateRequest");
        }
    }
    if (isset($options['idin_request_name']) &&  $options['idin_request_name'] == "1") {
        $catListObject->addCat("NameRequest");
    }
    if (isset($options['idin_request_address']) &&  $options['idin_request_address'] == "1") {
        $catListObject->addCat("AddressRequest");
    }
    if (isset($options['idin_request_address']) &&  $options['idin_request_address'] == "1") {
        $catListObject->addCat("AddressRequest");
    }
    if (isset($options['idin_request_birthdate']) &&  $options['idin_request_birthdate'] == "1") {
        $catListObject->addCat("BirthDateRequest");
    }
    if (isset($options['idin_request_gender']) &&  $options['idin_request_gender'] == "1") {
        $catListObject->addCat("GenderRequest");
    }
    if (isset($options['idin_request_telephone']) &&  $options['idin_request_telephone'] == "1") {
        $catListObject->addCat("TelephoneRequest");
    }
    if (isset($options['idin_request_email']) &&  $options['idin_request_email'] == "1") {
        $catListObject->addCat("EmailRequest");
    }

    return $catListObject->getCats();
    //explode(",", str_replace(" ", "", $bluem_config->IDINCategories));
}




/* ********* RENDERING THE STATIC FORM *********** */
add_shortcode('bluem_identificatieformulier', 'bluem_idin_form');

/**
 * Shortcode: `[bluem_identificatieformulier]`
 *
 * @return void
 */
function bluem_idin_form()
{
    $bluem_config = bluem_woocommerce_get_config();

    if (isset($bluem_config->IDINShortcodeOnlyAfterLogin)
        && $bluem_config->IDINShortcodeOnlyAfterLogin=="1"
        && !is_user_logged_in()
    ) {
        return "";
    }

    // ob_start();

    $r ='';
    $validated = get_user_meta(get_current_user_id(), "bluem_idin_validated", true) == "1";

    if ($validated) {
        if (isset($bluem_config->IDINSuccessMessage)) {
            $r.= "<p>" . $bluem_config->IDINSuccessMessage . "</p>";
        } else {
            $r.= "<p>Uw identificatieverzoek is ontvangen. Hartelijk dank.</p>";
        }

        // $r.= "Je hebt de identificatieprocedure eerder voltooid. Bedankt<br>";
        // $results = bluem_idin_retrieve_results();
        // $r.= "<pre>";
        // foreach ($results as $k => $v) {
        //     if (!is_object($v)) {
        //         $r.= "$k: $v";
        //     } else {
        //         foreach ($v as $vk => $vv) {
        //             $r.= "\t$vk: $vv";
        //             $r.="<BR>";
        //         }
        //     }
        //     $r.="<BR>";
        // }
        // // var_dump($results);
        // $r.= "</pre>";
        // return;
        return $r;
    } else {
        if (isset($_GET['result']) && sanitize_text_field($_GET['result']) == "false") {
            $r.= '<div class="">';

            if (isset($bluem_config->IDINErrorMessage)) {
                $r.= "<p>" . $bluem_config->IDINErrorMessage . "</p>";
            } else {
                $r.= "<p>Er is een fout opgetreden. Uw verzoek is geannuleerd.</p>";
            }

            if (isset($_SESSION['BluemIDINTransactionURL']) && $_SESSION['BluemIDINTransactionURL'] !== "") {
                $retryURL = $_SESSION['BluemIDINTransactionURL'];
                $r.= "<p><a href='{$retryURL}' target='_self' alt='probeer opnieuw' class='button'>Probeer het opnieuw</a></p>";
            } else {
                // $retryURL = home_url($bluem_config->checkoutURL);
            }
            $r.= '</div>';
        } else {
            $r.= "Je hebt de identificatieprocedure nog niet voltooid.<br>";
            $r.= '<form action="' . home_url('bluem-woocommerce/idin_execute') . '" method="post">';
            // @todo add custom fields
            $r.= '<p>';
            $r.= '<p><input type="submit" name="bluem_idin_submitted" class="bluem-woocommerce-button bluem-woocommerce-button-idin" value="Identificeren.."></p>';
            $r.= '</form>';
        }
    }


    return $r;
    //ob_get_clean();
}

add_action('parse_request', 'bluem_idin_shortcode_idin_execute');
/**
 * This function is called POST from the form rendered on a page or post
 *
 * @return void
 */
function bluem_idin_shortcode_idin_execute()
{
    $shortcode_execution_url = "bluem-woocommerce/idin_execute";

    if (strpos($_SERVER["REQUEST_URI"], $shortcode_execution_url) === false) {
        // any other request
        return;
    }

    $goto = false;
    if (array_key_exists('redirect_to_checkout', $_GET)
        && sanitize_text_field($_GET['redirect_to_checkout']) == "true"
    ) {
        // v1.2.6: added cart url instead of static cart as this is front-end language dependent
        // $goto = wc_get_cart_url();
        // v1.2.8: added checkout url instead of cart url :)
        $goto = wc_get_checkout_url();
    }

    bluem_idin_execute(null, true, $goto);
}
/* ******** CALLBACK ****** */
add_action('parse_request', 'bluem_idin_shortcode_callback');
/**
 * This function is executed at a callback GET request with a given mandateId. This is then, together with the entranceCode in Session, sent for a SUD to the Bluem API.
 *
 */
function bluem_idin_shortcode_callback()
{
    if (strpos($_SERVER["REQUEST_URI"], "bluem-woocommerce/idin_shortcode_callback") === false) {
        // return;
    } else {
        $bluem_config = bluem_woocommerce_get_config();

        // fallback until this is corrected in bluem-php
        $bluem_config->brandID = $bluem_config->IDINBrandID;
        $bluem = new Integration($bluem_config);



        if (is_user_logged_in()) {
            $entranceCode = get_user_meta(get_current_user_id(), "bluem_idin_entrance_code", true);
            $transactionID = get_user_meta(get_current_user_id(), "bluem_idin_transaction_id", true);
            $transactionURL = get_user_meta(get_current_user_id(), "bluem_idin_transaction_url", true);
        } else {
            // session_start();
            if (isset($_SESSION["bluem_idin_entrance_code"]) && !is_null($_SESSION["bluem_idin_entrance_code"])) {
                $entranceCode = $_SESSION["bluem_idin_entrance_code"];
            } else {
                echo "Error: ".$_SESSION["bluem_idin_entrance_code"]." missing";
                exit;
            }
            if (isset($_SESSION["bluem_idin_transaction_id"]) && !is_null($_SESSION["bluem_idin_transaction_id"])) {
                $transactionID = $_SESSION["bluem_idin_transaction_id"];
            } else {
                echo "Error: ".$_SESSION["bluem_idin_transaction_id"]." missing";
                exit;
            }
            if (isset($_SESSION["bluem_idin_transaction_url"]) && !is_null($_SESSION["bluem_idin_transaction_url"])) {
                $transactionURL = $_SESSION["bluem_idin_transaction_url"];
            } else {
                echo "Error: ".$_SESSION["bluem_idin_transaction_url"]." missing";
                exit;
            }
        }

        $statusResponse = $bluem->IdentityStatus(
            $transactionID,
            $entranceCode
        );

        if ($statusResponse->ReceivedResponse()) {
            $statusCode = ($statusResponse->GetStatusCode());


            $request_from_db = bluem_db_get_request_by_transaction_id($transactionID);

            if ($request_from_db->status !== $statusCode) {

                bluem_db_update_request(
                    $request_from_db->id,
                    [
                        'status'=>$statusCode
                    ]
                );
            }


            if (is_user_logged_in()) {
                update_user_meta(
                    get_current_user_id(), "bluem_idin_validated", false
                );
            } else {
                $_SESSION['bluem_idin_validated'] = false;
            }

            switch ($statusCode) {
            case 'Success': // in case of success...
                // ..retrieve a report that contains the information based on the request type:
                $identityReport = $statusResponse->GetIdentityReport();

                if (is_user_logged_in()) {
                    update_user_meta(get_current_user_id(), "bluem_idin_results", json_encode($identityReport));
                    update_user_meta(get_current_user_id(), "bluem_idin_validated", true);
                } else {
                    $_SESSION['bluem_idin_validated'] = true;
                }

                // update an age check response field if that sccenario is active.
                $verification_scenario = bluem_idin_get_verification_scenario();

                if ($verification_scenario == 1
                    && isset($identityReport->AgeCheckResponse)
                ) {
                    $agecheckresponse = $identityReport->AgeCheckResponse."";
                    if (is_user_logged_in()) {
                        update_user_meta(get_current_user_id(), "bluem_idin_report_agecheckresponse", $agecheckresponse);
                    } else {
                        $_SESSION['bluem_idin_report_agecheckresponse'] = $agecheckresponse;
                    }
                }
                if (isset($identityReport->CustomerIDResponse)) {
                    $customeridresponse = $identityReport->CustomerIDResponse."";
                    if (is_user_logged_in()) {
                        update_user_meta(get_current_user_id(), "bluem_idin_report_customeridresponse", $customeridresponse);
                    } else {
                        $_SESSION['bluem_idin_report_customeridresponse'] = $customeridresponse;
                    }
                }
                if (isset($identityReport->DateTime)) {
                    $datetime = $identityReport->DateTime."";
                    if (is_user_logged_in()) {
                        update_user_meta(get_current_user_id(), "bluem_idin_report_last_verification_timestamp", $datetime);
                    } else {
                        $_SESSION['bluem_idin_report_last_verification_timestamp'] = $datetime;
                    }
                }

                if (isset($identityReport->BirthdateResponse)) {
                    $birthdate = $identityReport->BirthdateResponse."";
                    if (is_user_logged_in()) {
                        update_user_meta(
                            get_current_user_id(),
                            "bluem_idin_report_birthdate",
                            $birthdate
                        );
                    } else {
                        $_SESSION['bluem_idin_report_birthdate'] = $birthdate;
                    }
                }
                if (isset($identityReport->TelephoneResponse)) {
                    $telephone = $identityReport->TelephoneResponse."";
                    if (is_user_logged_in()) {
                        update_user_meta(
                            get_current_user_id(),
                            "bluem_idin_report_telephone",
                            $telephone
                        );
                    }
                }
                if (isset($identityReport->EmailResponse)) {
                    $email = $identityReport->EmailResponse."";
                    if (is_user_logged_in()) {
                        update_user_meta(
                            get_current_user_id(),
                            "bluem_idin_report_email",
                            $email
                        );
                    }
                }


                $min_age = bluem_idin_get_min_age();
                if ($verification_scenario == 3
                    && isset($identityReport->BirthDateResponse)
                ) {
                    $user_age = bluem_idin_get_age_based_on_date(
                        $identityReport->BirthDateResponse
                    );

                    if ($user_age >= $min_age) {
                        if (is_user_logged_in()) {
                            update_user_meta(
                                get_current_user_id(),
                                "bluem_idin_report_agecheckresponse",
                                "true"
                            );
                        } else {
                            $_SESSION['bluem_idin_report_agecheckresponse'] = "true";
                        }
                    }
                }
                // var_dump($request_from_db);
                if (isset($request_from_db) && $request_from_db!==false) {
                    if ($request_from_db->payload!=="") {
                        try {
                            $oldPayload = json_decode($request_from_db->payload);
                        } catch (Throwable $th) {
                            $oldPayload = new Stdclass;
                        }
                    } else {
                        $oldPayload = new Stdclass;
                    }
                    $oldPayload->report = $identityReport;

                    bluem_db_update_request(
                        $request_from_db->id,
                        [
                            'status'=>$statusCode,
                            'payload'=>json_encode($oldPayload)
                            ]
                    );
                }
                
        if (strpos($_SERVER["REQUEST_URI"], "bluem-woocommerce/idin_shortcode_callback/go_to_cart") !== false) {
            $goto = wc_get_checkout_url();

        } else {
            $goto = $bluem_config->IDINPageURL;

            if ($goto == false || $goto == "") {
                $goto = home_url();
            }
        }
        wp_redirect($goto);


                exit;
            break;
            case 'Processing':
                echo "Request has status Processing";

                // @todo: improve this flow
                // no break
            case 'Pending':
                echo "Request has status Pending";

                // @todo: improve this flow
                // do something when the request is still processing (for example tell the user to come back later to this page)
                break;
            case 'Cancelled':
                    echo "Request has status Cancelled";

                    // @todo: improve this flow
                    // do something when the request has been canceled by the user
                break;
            case 'Open':
                    echo "Request has status Open";

                    // @todo: improve this flow
                    // do something when the request has not yet been completed by the user, redirecting to the transactionURL again
                break;
            case 'Expired':
                    echo "Request has status Expired";

                    // @todo: improve this flow
                    // do something when the request has expired
                break;
            // case 'New':
                    //     echo "New request";
                    // break;
            default:
                    // unexpected status returned, show an error
                break;
            }
            wp_redirect(
                home_url($goto) .
                "?result=false&status={$statusCode}"
            );
        } else {
            // no proper response received, tell the user
            wp_redirect(
                home_url($goto) .
                "?result=false&status=no_response"
            );
        }
    }
}


add_action('show_user_profile', 'bluem_woocommerce_idin_show_extra_profile_fields');
add_action('edit_user_profile', 'bluem_woocommerce_idin_show_extra_profile_fields');

function bluem_woocommerce_idin_show_extra_profile_fields($user)
{
    ?>
<?php //var_dump($user->ID);
?>
<h2>Bluem iDIN Metadata</h2>
<p>
Ga naar
<a href="<?php echo home_url("wp-admin/options-general.php?page=bluem"); ?>">
Bluem instellingen
</a> om het gedrag van verificatie te wijziggen.</p>
<table class="form-table">
<tr>
<th>
<label for="bluem_idin_entrance_code">
    Bluem iDIN transactiegegevens
</label>
</th>
<td>
<input type="text" name="bluem_idin_entrance_code" id="bluem_idin_entrance_code" value="<?php echo get_user_meta($user->ID, 'bluem_idin_entrance_code', true); ?>" class="regular-text" /><br />
<span class="description">Recentste Entrance code voor Bluem iDIN requests</span>
<br>
<input type="text" name="bluem_idin_transaction_id" id="bluem_idin_transaction_id" value="<?php echo get_user_meta($user->ID, 'bluem_idin_transaction_id', true); ?>" class="regular-text" /><br />
<span class="description">DE meest recente transaction ID: deze wordt gebruikt bij het doen van een volgende identificatie.</span>
<br>
<input type="text" name="bluem_idin_transaction_url" id="bluem_idin_transaction_url" value="<?php echo get_user_meta($user->ID, 'bluem_idin_transaction_url', true); ?>" class="regular-text" /><br />
<span class="description">De meest recente transactie URL</span>
<br>
<input type="text" name="bluem_idin_report_last_verification_timestamp"
id="bluem_idin_report_last_verification_timestamp"
value="<?php echo get_user_meta($user->ID, 'bluem_idin_report_last_verification_timestamp', true); ?>"
class="regular-text" /><br />
<span class="description">Laatste keer dat verificatie is uitgevoerd</span>
</td>
</tr>

<tr>
<th>
<label for="bluem_idin_report_agecheckresponse">
Respons van bank op leeftijdscontrole, indien van toepassing
</label>
</th>


<td>
<?php $ageCheckResponse = get_user_meta($user->ID, 'bluem_idin_report_agecheckresponse', true); ?>
<select class="form-control" name="bluem_idin_report_agecheckresponse" id="bluem_idin_report_agecheckresponse">
<option value="" <?php if ($ageCheckResponse == "") {
    echo "selected='selected'";
} ?>>Leeftijdcheck nog niet uitgevoerd</option>
<option value="false" <?php if ($ageCheckResponse == "false") {
    echo "selected='selected'";
} ?>>Leeftijd niet toereikend bevonden</option>
<option value="true" <?php if ($ageCheckResponse == "true") {
    echo "selected='selected'";
} ?>>Leeftijd toereikend bevonden</option>
</select>

<br>
<span class="description"></span>
</td>

</tr>
<tr>
<th>
    <label for="bluem_idin_report_customeridresponse">
    CustomerID dat terugkomt van de Bank
    </label>
</th>

<td>


<input type="text" name="bluem_idin_report_customeridresponse"
id="bluem_idin_report_customeridresponse"
value="<?php echo get_user_meta($user->ID, 'bluem_idin_report_customeridresponse', true); ?>"
class="regular-text" /><br />
<span class="description"></span>
</td>

</tr>

<tr>
<th>
<label for="bluem_idin_transaction_url">iDIN responses</label>
</th>

<td>
<span class="description">
Status en Resultaten van iDIN requests
</span>

<select class="form-control" name="bluem_idin_validated" id="bluem_idin_validated">
<option value="0" <?php if (get_user_meta($user->ID, 'bluem_idin_validated', true)== "0") {
    echo "selected='selected'";
} ?>>Identificatie nog niet uitgevoerd</option>
<option value="1" <?php if (get_user_meta($user->ID, 'bluem_idin_validated', true)== "1") {
    echo "selected='selected'";
} ?>>Identificatie succesvol uitgevoerd</option>
</select>
</div>
</td>
</tr>
<table class="form-table">
<tr>
<td>
<pre><?php print_r(bluem_idin_retrieve_results()); ?></pre>
</td>
<td>

<h3>
    Checkout blokkeren als iDIN niet is uitgevoerd:
</h3>
<p>
    Ga naar Instellingen - Bluem en stel deze checkout blokkade in onder Identity instellingen.
<!-- Voeg een filter toe voor id <code>bluem_checkout_check_idin_validated_filter</code> als u een filter wilt toevoegen om de checkout procedure te blokkeren op basis van de iDIN validatie procedure die is voltooid.<br>
Als de geïnjecteerde functie true retourneert, wordt de kassa ingeschakeld. Als false wordt geretourneerd, wordt de kassa geblokkeerd en wordt een melding getoond. -->
</p>
<h3>Programmatisch met iDIN resultaten werken</h3>
</p>
<p>

Of de validatie is gelukt, kan je  verkrijgen door in een plug-in of template de volgende PHP code te gebruiken:

<blockquote style="border: 1px solid #aaa;
border-radius:5px; margin:10pt 0 0 0; padding:5pt 15pt;"><pre>if (function_exists('bluem_idin_user_validated')) {
    $validated = bluem_idin_user_validated();

    if ($validated) {
        // validated
    } else {
        // not validated
    }
}</pre>
</blockquote>
</p>
<p>
Deze resultaten zijn als object te verkrijgen door in een plug-in of template de volgende PHP code te gebruiken:
</p>
<p>
<blockquote style="border: 1px solid #aaa; border-radius:5px;
margin:10pt 0 0 0; padding:5pt 15pt;">
<pre>if (function_exists('bluem_idin_retrieve_results')) {
        $results = bluem_idin_retrieve_results();
        // print, show or save the results:
        echo $results->BirthDateResponse; // prints 1975-07-25
        echo $results->NameResponse->LegalLastName; // prints Vries
    }</pre>
    </blockquote>
    </p>
    </td>
    </tr>
    </table>

    <?php
}
add_action('personal_options_update', 'bluem_woocommerce_idin_save_extra_profile_fields');
add_action('edit_user_profile_update', 'bluem_woocommerce_idin_save_extra_profile_fields');

function bluem_woocommerce_idin_save_extra_profile_fields($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    update_user_meta(
        $user_id,
        'bluem_idin_entrance_code',
        sanitize_text_field($_POST['bluem_idin_entrance_code'])
    );
    update_user_meta(
        $user_id,
        'bluem_idin_transaction_id',
        sanitize_text_field($_POST['bluem_idin_transaction_id'])
    );
    update_user_meta(
        $user_id,
        'bluem_idin_transaction_url',
        sanitize_text_field($_POST['bluem_idin_transaction_url'])
    );

    update_user_meta(
        $user_id,
        'bluem_idin_validated',
        sanitize_text_field($_POST['bluem_idin_validated'])
    );

    update_user_meta(
        $user_id,
        'bluem_idin_report_last_verification_timestamp',
        sanitize_text_field($_POST['bluem_idin_report_last_verification_timestamp'])
    );


    update_user_meta(
        $user_id,
        'bluem_idin_report_customeridresponse',
        sanitize_text_field($_POST['bluem_idin_report_customeridresponse'])
    );

    update_user_meta(
        $user_id,
        'bluem_idin_report_agecheckresponse',
        sanitize_text_field($_POST['bluem_idin_report_agecheckresponse'])
    );
}

function bluem_idin_retrieve_results()
{
    $raw = get_user_meta(get_current_user_id(), "bluem_idin_results", true);

    $obj = json_decode($raw);
    return $obj;
}
function bluem_idin_user_validated()
{
    global $current_user;
    if (is_user_logged_in()) {
        return get_user_meta(get_current_user_id(), "bluem_idin_validated", true) == "1";
    } else {
        // session_start();

        // var_dump($_SESSION);
        if (isset($_SESSION['bluem_idin_validated']) && $_SESSION['bluem_idin_validated'] === true) {
            return true;
        } else {
            return false;
        }
        // return $_SESSION['bluem_idin_validated'];
        // echo "NOT LOGGED IN, checking sessh";
        // var_dump($current_user);

        // die();
        // if (!session_id()) {

        //     return false;
        //     // session_start();
        // } else {

        // }
        // session_start();
    }
}

function bluem_get_IDINDescription_tags()
{
    return [
        '{gebruikersnaam}',
        '{email}',
        '{klantnummer}',
        '{datum}',
        '{datumtijd}'
    ];
}

function bluem_get_IDINDescription_replaces()
{
    global $current_user;

    // @todo: add fallbacks if user is not logged in

    return [
        $current_user->display_name,    //'{gebruikersnaam}',
        $current_user->user_email,      //'{email}',
        $current_user->ID,              // {klantnummer}
        date("d-m-Y"),                  //'{datum}',
        date("d-m-Y H:i")               //'{datumtijd}',
    ];
}
function bluem_parse_IDINDescription($input)
{
    $tags = bluem_get_IDINDescription_tags();
    $replaces = bluem_get_IDINDescription_replaces();


    $result = str_replace($tags, $replaces, $input);
    $invalid_chars = ['[',']','{','}','!','#'];
    // @todo Add full list of invalid chars for description based on XSD
    $result = str_replace($invalid_chars, '', $result);

    $result = substr($result, 0, 128);
    return $result;
}




function bluem_idin_execute($callback=null, $redirect=true, $redirect_page = false)
{
    global $current_user;
    $bluem_config = bluem_woocommerce_get_config();
    if (isset($bluem_config->IDINDescription)) {
        $description = bluem_parse_IDINDescription($bluem_config->IDINDescription);
    } else {
        $description =  "Identificatie " . $current_user->display_name ;
    }

    $debtorReference = $current_user->ID;

    // fallback until this is corrected in bluem-php
    $bluem_config->brandID = $bluem_config->IDINBrandID;
    $bluem = new Integration($bluem_config);

    $cats = bluem_idin_get_categories();
    if (count($cats)==0) {
        exit("Geen juiste iDIN categories ingesteld");
    }

    if (is_null($callback)) {
        $callback = home_url("bluem-woocommerce/idin_shortcode_callback");
    }

    if ($redirect_page!==false) {
        $callback .= "/go_to_cart";
    }

    // To create AND perform a request:
    $request = $bluem->CreateIdentityRequest(
        $cats,
        $description,
        $debtorReference,
        $callback
    );

    $response = $bluem->PerformRequest($request);

    if (!session_id()) {
        session_start();
    }

    if ($response->ReceivedResponse()) {

        $entranceCode = $response->GetEntranceCode();
        $transactionID = $response->GetTransactionID();
        $transactionURL = $response->GetTransactionURL();

        bluem_db_create_request(
            [
                'entrance_code'=>$entranceCode,
                'transaction_id'=>$transactionID,
                'transaction_url'=>$transactionURL,
                'user_id'=> is_user_logged_in() ? $current_user->ID : 0,
                'timestamp'=> date("Y-m-d H:i:s"),
                'description'=>$description,
                'debtor_reference'=>$debtorReference,
                'type'=>"identity",
                'order_id'=>null,
                'payload'=>''
            ]
        );


        // save this in our user meta data store
        if (is_user_logged_in()) {
            update_user_meta(
                get_current_user_id(),
                "bluem_idin_entrance_code",
                $entranceCode
            );
            update_user_meta(
                get_current_user_id(),
                "bluem_idin_transaction_id",
                $transactionID
            );
            update_user_meta(
                get_current_user_id(),
                "bluem_idin_transaction_url",
                $transactionURL
            );
        } else {
            $_SESSION["bluem_idin_entrance_code"] = $entranceCode;
            $_SESSION["bluem_idin_transaction_id"] = $transactionID;
            $_SESSION["bluem_idin_transaction_url"] = $transactionURL;
        }

        if ($redirect) {
            if (ob_get_length()!==false && ob_get_length()>0) {
                ob_clean();
            }
            ob_start();
            wp_redirect($transactionURL);
            exit;
        } else {
            return ['result'=>true,'url'=>$transactionURL];
        }
    } else {
        $msg = "Er ging iets mis bij het aanmaken van de transactie.<br>
        Vermeld onderstaande informatie aan het websitebeheer:";
        //     <br><pre>";
        // bluem_generic_tabler($response);
        // echo "</pre>";
        if ($response->Error() !=="") {
            $msg.= "<br>Response: " .
            $response->Error();
        } else {
            $msg .= "algemene fout";
        }


        bluem_woocommerce_prompt($msg);
        exit;
    }
    exit;
}

// https://www.businessbloomer.com/woocommerce-visual-hook-guide-checkout-page/
// add_action( 'woocommerce_review_order_before_payment', 'bluem_checkout_check_idin_validated' );

add_action('woocommerce_review_order_before_payment', 'bluem_checkout_idin_notice');
function bluem_checkout_idin_notice()
{
    global $current_user;


    // use a setting if this check has to be incurred
    if (!is_checkout()) {
        return;
    }

    if (!function_exists('bluem_idin_user_validated')) {
        return;
    }
    $identify_button_html = "<br><a href='".
        home_url('bluem-woocommerce/idin_execute?redirect_to_checkout=true')."'
        target='_self' class='button bluem-identify-button' style='margin-top:5px; display:inline-block'>Klik hier om je te identificeren</a><br>";

    $options = get_option('bluem_woocommerce_options');

    if (isset($options['idin_scenario_active']) && $options['idin_scenario_active']!=="") {
        $scenario = (int) $options['idin_scenario_active'];
    }

    if ($scenario > 0) {
        echo "<h3>Identificatie</h3>";

        $validated = bluem_idin_user_validated();
        $validation_message = "Identificatie is vereist alvorens de bestelling kan worden afgerond.";
        $idin_logo_html = bluem_get_idin_logo_html();
        // above 0: any form of verification is required
        if (!$validated) {
            echo "<div style='min-height:130px; display:block; padding:15pt; border:1px solid #50afed; border-radius:4px; margin-top:10px; margin-bottom:10px;'>
            {$idin_logo_html}  {$validation_message} <div style='display:block; text-align:center; clear:both;'>{$identify_button_html}</div></div>";
            return;
        }



        // get report from user metadata
        // $results = bluem_idin_retrieve_results();
        // identified? but is this person OK of age?
        if ($scenario == 1 || $scenario == 3) {
            // we gaan er standaard vanuit dat de leeftijd NIET toereikend is
            $age_valid = false;

            if (is_user_logged_in()) {
                $ageCheckResponse = get_user_meta(
                    $current_user->ID,
                    'bluem_idin_report_agecheckresponse',
                    true
                );
            } else {
                // for debugging
                // $_SESSION['bluem_idin_report_agecheckresponse'] = "true";

                $ageCheckResponse = $_SESSION['bluem_idin_report_agecheckresponse'];
            }
            // var_dump($_SESSION['bluem_idin_report_agecheckresponse']);

            // var_dump($ageCheckResponse);
            // check on age based on response of AgeCheckRequest in user meta
            // if ($scenario == 1)
            // {
            if (isset($ageCheckResponse)) {
                if ($ageCheckResponse == "true") {

                    // TRUE Teruggekregen van de bank
                    $age_valid = true;
                } else {
                    // ERROR KON BIRTHDAY NIET INLEZEN, WEL INGEVULD BIJ DE BANK? nIET VALIDE DUS
                    $validation_message = "Uw leeftijd is niet bekend of niet toereikend. U kan dus niet deze bestelling afronden. Neem bij vragen contact op met de webshop support.";

                    $age_valid = false;
                }
            } else {
                // ERROR KON BIRTHDAY NIET INLEZEN, WEL INGEVULD BIJ DE BANK? nIET VALIDE DUS
                $validation_message = "We hebben uw leeftijd nog niet kunnen opvragen bij de identificatie.<BR>  Neem contact op met de webshop support.";

                $age_valid = false;
            }
            // }

            // check on age based on response of BirthDateRequest in user meta
            // if ($scenario == 3)
            // {
            //     $min_age = bluem_idin_get_min_age();


            //     // echo $results->BirthDateResponse; // prints 1975-07-25
            //     if (isset($results->BirthDateResponse) && $results->BirthDateResponse!=="") {

            //         $user_age = bluem_idin_get_age_based_on_date($results->BirthDateResponse);
            //         if ($user_age < $min_age) {
            //             $validation_message = "Je leeftijd, $user_age, is niet toereikend. De minimumleeftijd is {$min_age} jaar.
            //             <br>Identificeer jezelf opnieuw of neem contact op.";
            //             $age_valid = false;
            //         } else {
            //             $age_valid = true;
            //         }
            //     } else {

            //         // ERROR KON BIRTHDAY NIET INLEZEN, WEL INGEVULD BIJ DE BANK? nIET VALIDE DUS
            //         $validation_message = "We hebben je leeftijd niet kunnen opvragen bij de identificatie.<BR>
            //         Neem contact op met de webshop support.";
            //         $age_valid =false;
            //     }
            // }


            if (!$age_valid) {
                echo "<div style='min-height:130px; display:block; padding:15pt; border:1px solid #50afed; border-radius:4px; margin-top:10px; margin-bottom:10px;'>{$idin_logo_html} {$validation_message} <div style='display:block; text-align:center; clear:both;'>{$identify_button_html}</div></div>";
                return;
            } else {
                echo "<div style='min-height:130px; display:block; padding:15pt; border:1px solid #50afed; border-radius:4px; margin-top:10px; margin-bottom:10px;'>{$idin_logo_html} Je leeftijd is geverifieerd, bedankt.</div>";
                return;
            }
        }
    }

    // <p>Identificatie is vereist alvorens je deze bestelling kan plaatsen</p>";

    if (bluem_checkout_check_idin_validated_filter()==false) {
        echo __(
            "Verifieer eerst je identiteit via de mijn account pagina",
            "woocommerce"
        );
        return;
    }
}


// add_action('woocommerce_check_cart_items', 'bluem_checkout_check_idin_validated'); // Cart and Checkout
add_action('woocommerce_after_checkout_validation', 'bluem_checkout_check_idin_validated');
function bluem_checkout_check_idin_validated()
{
    global $current_user;


    // use a setting if this check has to be incurred
    if (!is_checkout()) {
        return;
    }

    if (!function_exists('bluem_idin_user_validated')) {
        return;
    }
    $identify_button_html = "<br><a href='".
        home_url('bluem-woocommerce/idin_execute?redirect_to_checkout=true')."'
        target='_self' class='button bluem-identify-button'>Klik hier om je te identificeren</a>";

    $options = get_option('bluem_woocommerce_options');

    if (isset($options['idin_scenario_active']) && $options['idin_scenario_active']!=="") {
        $scenario = (int) $options['idin_scenario_active'];
    }

    if ($scenario > 0) {
        $validated = bluem_idin_user_validated();
        $idin_logo_html = bluem_get_idin_logo_html();
        $validation_message = "Identificatie is vereist alvorens de bestelling kan worden afgerond.";

        // above 0: any form of verification is required
        if (!$validated) {
            wc_add_notice(
                __("{$idin_logo_html} {$validation_message} <div style='display:block; text-align:center; clear:both;'>{$identify_button_html}</div>", "woocommerce"),
                'error'
            );
        } else {

            // get report from user metadata
            // $results = bluem_idin_retrieve_results();
            // identified? but is this person OK of age?
            if ($scenario == 1 || $scenario == 3) {
                // we gaan er standaard vanuit dat de leeftijd NIET toereikend is
                $age_valid = false;

                if (is_user_logged_in()) {
                    $ageCheckResponse = get_user_meta(
                        $current_user->ID,
                        'bluem_idin_report_agecheckresponse',
                        true
                    );
                } else {
                    // for debugging
                    // $_SESSION['bluem_idin_report_agecheckresponse'] = "true";

                    $ageCheckResponse = $_SESSION['bluem_idin_report_agecheckresponse'];
                }
                // var_dump($_SESSION['bluem_idin_report_agecheckresponse']);

                // var_dump($ageCheckResponse);
                // check on age based on response of AgeCheckRequest in user meta
                // if ($scenario == 1)
                // {
                if (isset($ageCheckResponse)) {
                    if ($ageCheckResponse == "true") {

                    // TRUE Teruggekregen van de bank
                        $age_valid = true;
                    } else {
                        // ERROR KON BIRTHDAY NIET INLEZEN, WEL INGEVULD BIJ DE BANK? nIET VALIDE DUS
                        $validation_message = "Uw leeftijd is niet toereikend. U kan dus niet deze bestelling afronden.
                    Neem bij vragen contact op met de webshop support.";

                        $age_valid = false;
                    }
                } else {
                    // ERROR KON BIRTHDAY NIET INLEZEN, WEL INGEVULD BIJ DE BANK? nIET VALIDE DUS
                    $validation_message = "We hebben uw leeftijd niet kunnen opvragen bij de identificatie.<BR>
                    Neem contact op met de webshop support.";

                    $age_valid = false;
                }
                // }

                // check on age based on response of BirthDateRequest in user meta
                // if ($scenario == 3)
                // {
                //     $min_age = bluem_idin_get_min_age();


                //     // echo $results->BirthDateResponse; // prints 1975-07-25
                //     if (isset($results->BirthDateResponse) && $results->BirthDateResponse!=="") {

                //         $user_age = bluem_idin_get_age_based_on_date($results->BirthDateResponse);
                //         if ($user_age < $min_age) {
                //             $validation_message = "Je leeftijd, $user_age, is niet toereikend. De minimumleeftijd is {$min_age} jaar.
                //             <br>Identificeer jezelf opnieuw of neem contact op.";
                //             $age_valid = false;
                //         } else {
                //             $age_valid = true;
                //         }
                //     } else {

                //         // ERROR KON BIRTHDAY NIET INLEZEN, WEL INGEVULD BIJ DE BANK? nIET VALIDE DUS
                //         $validation_message = "We hebben je leeftijd niet kunnen opvragen bij de identificatie.<BR>
                //         Neem contact op met de webshop support.";
                //         $age_valid =false;
                //     }
                // }
                if (!$age_valid) {
                    wc_add_notice(
                        __(
                            "{$idin_logo_html} {$validation_message} <div style='display:block; text-align:center; clear:both;'>{$identify_button_html}</div>",
                            "woocommerce"
                        ),
                        'error'
                    );
                }
            }
        }
    }

    // custom user-based checks:
    if (bluem_checkout_check_idin_validated_filter()==false) {
        wc_add_notice(
            $idin_logo_html . __(
                "Verifieer eerst je identiteit via de mijn account pagina",
                "woocommerce"
            ),
            'error'
        );
    }
    return;
}

add_filter(
    'bluem_checkout_check_idin_validated_filter',
    'bluem_checkout_check_idin_validated_filter_function',
    10,
    1
);
function bluem_checkout_check_idin_validated_filter()
{

    // override this function if you want to add a filter to block the checkout procedure based on the iDIN validation procedure being completed.
    // if you return true, the checkout is enabled. If you return false, the checkout is blocked and a notice is shown.

    // example:
    // if (!bluem_idin_user_validated()) {
    //   return false;
    // }

    return true;
}


function bluem_idin_get_age_based_on_date($birthday_string)
{
    $birthdate_seconds = strtotime($birthday_string);
    $now_seconds = strtotime("now");
    return (int)floor(($now_seconds - $birthdate_seconds) / 60 / 60 / 24 / 365);
}


function bluem_idin_get_verification_scenario()
{
    $options = get_option('bluem_woocommerce_options');
    $scenario = 0;
    if (isset($options['idin_scenario_active']) && $options['idin_scenario_active']!=="") {
        $scenario = (int) $options['idin_scenario_active'];
    }
    return $scenario;
}

function bluem_idin_get_min_age()
{
    $options = get_option('bluem_woocommerce_options');
    if (isset($options['idin_check_age_minimum_age']) && $options['idin_check_age_minimum_age']!=="") {
        $min_age = $options['idin_check_age_minimum_age'];
    } else {
        $min_age = 18;
    }
    return $min_age;
}
class BluemIdentityCategoryList
{
    public $_cats = [];

    public function getCats()
    {
        return $this->_cats;
    }
    public function addCat($cat)
    {
        if (!in_array($cat, $this->_cats)) {
            $this->_cats[] = $cat;
        }
    }
}


// https://wordpress.stackexchange.com/questions/314955/add-custom-order-meta-to-order-completed-email
add_filter('woocommerce_email_order_meta_fields', 'bluem_order_email_identity_meta_data', 10, 3);

function bluem_order_email_identity_meta_data($fields, $sent_to_admin, $order)
{
    global $current_user;

    // if(!is_admin()) {

    // }


    // $fields['bluem_idin_entrance_code'] = [
    //     'label'=>__('bluem_idin_entrance_code','bluem'),
    //     'value'=> get_user_meta($current_user->ID, 'bluem_idin_entrance_code', true)
    // ];
    // $fields['bluem_idin_transaction_id'] = [
    //     'label'=>__('bluem_idin_transaction_id','bluem'),
    //     'value'=> get_user_meta($current_user->ID, 'bluem_idin_transaction_id', true)
    // ];
    // $fields['bluem_idin_transaction_url'] = [
    //     'label'=>__('bluem_idin_transaction_url','bluem'),
    //     'value'=> get_user_meta($current_user->ID, 'bluem_idin_transaction_url', true)
    // ];
    // $fields['bluem_idin_report_last_verification_timestamp'] = [
    //     'label'=>__('bluem_idin_report_last_verification_timestamp','bluem'),
    //     'value'=> get_user_meta($current_user->ID, 'bluem_idin_report_last_verification_timestamp', true)
    // ];
    // $fields['bluem_idin_report_customeridresponse'] = [
    //     'label'=>__('bluem_idin_report_customeridresponse','bluem'),
    //     'value'=> get_user_meta($current_user->ID, 'bluem_idin_report_customeridresponse', true)
    // ];

    $options = get_option('bluem_woocommerce_options');
    if (!array_key_exists('idin_add_field_in_order_emails', $options)
        || (array_key_exists('idin_add_field_in_order_emails', $options)
        && $options['idin_add_field_in_order_emails'] == "1")
    ) {
        $validation_text = "";
        if (get_user_meta($current_user->ID, 'bluem_idin_validated', true)) {
            $validation_text = __('ja', 'bluem');
        // $validation_text .= " (Transactie ". get_user_meta($current_user->ID, 'bluem_idin_transaction_id', true).")";
        } else {
            $validation_text = __('nee', 'bluem') ;
        }

        $fields['bluem_idin_validated'] = [
            'label'=>__('Identiteit geverifieerd', 'bluem'),
            'value'=> $validation_text
        ];
    }
    return $fields;
}


add_action('woocommerce_review_order_before_payment', 'bluem_idin_before_payment_notice');
function bluem_idin_before_payment_notice()
{
}
