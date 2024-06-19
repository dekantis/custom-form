<?php
declare(strict_types=1);
class CustomForm {

    const NOTICE_ERROR_CLASS = 'notice notice-error';
    const NOTICE_SUCCESS_CLASS = 'notice notice-success';
    const DATA_FIELDS = [
        'firstname',
        'lastname',
        'subject',
        'message',
        'email'
    ];

    /**
     * @var bool
     */
    private static bool $initiated = false;
    /**
     * @var string
     */
    public static string $error_message;

    public static function init() {
        if (!self::$initiated ) {
            self::init_hooks();
        }
    }

    /**
     * Initializes WordPress hooks
     */
    private static function init_hooks(): void
    {
        self::$initiated = true;


        if ($_POST['action'] === CUSTOM_FORM_SEND_FORM_ACTION) {
            self::send_custom_form();
            add_action( 'admin_notices', array('CustomForm', 'custom_email_form_admin_notice'));
        }
    }

    /**
     * @return bool
     */
    private static function validate_form(): bool
    {
        $data = $_POST;

        foreach (self::DATA_FIELDS as $field) {
            $field = trim(htmlspecialchars($field));
            if (!array_key_exists($field, $data) || empty($data[$field])) {
                self::$error_message = "Missing '{$field}' data";

                return  false;

            }

            if (in_array($field, ['firstname', 'lastname']) && !preg_match('/^[a-zA-Zа-яА-Я]+$/u', $data[$field])) {
                self::$error_message = "Field '{$field}' has incorrect symbols";

                return false;
            }

            if ($field == 'email' && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                self::$error_message = "Field '{$field}' has incorrect format";

                return false;
            }
        }

        return true;

    }

    /**
     * @return void
     */
    public static function send_custom_form(): void
    {
        if(!self::validate_form()) {
            return;
        }

        self::send();

        require_once( CUSTOM_FORM_PLUGIN_DIR . 'class.crm-api.php' );

        if (class_exists('CrmApi')) {
            $crmApi = new CrmApi($_POST);
            $crmResponse = $crmApi->createContact();
            if (!empty($crmResponse['status']) && $crmResponse['status'] == 'error') {
                self::$error_message = $crmResponse['message'];
            }
        }
    }

    /**
     * @return void
     */
    public static function custom_email_form_admin_notice(): void
    {
        if (isset(self::$error_message)) {
            $class = self::NOTICE_ERROR_CLASS;
            $message = self::$error_message;

            self::write_error_log();
        } else {
            $class = self::NOTICE_SUCCESS_CLASS;
            $message = 'Form Sent!';
        }

        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }

    /**
     * @return void
     */
    public static function send(): void
    {
        $mail_sent = wp_mail($_POST['email'], $_POST['subject'], $_POST['message']);
        if (!$mail_sent) {
            self::$error_message = 'Email didn\'t send';

            return;
        }

        error_log("Message from custom form sent to {$_POST['email']} successfully.");
    }

    /**
     * @return void
     */
    private static function write_error_log(): void
    {
        if (defined('WP_CONTENT_DIR') && defined('CUSTOM_FORM_LOG_ENABLED') && isset(self::$error_message)) {
            $file = fopen(WP_CONTENT_DIR . "/custom_form_errors.log","a");
            fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . self::$error_message);
            fclose($file);
        }
    }

    /**
     * @return void
     */
    public static function custom_email_form(): void
    {
        ?>
        <div class="wrap">
            <h2>Welcome To Custom Email Sender</h2>
            <form method="post">
                <label for="firstname">First name:</label><br>
                <input type="text" id="firstname" name="firstname" value="<?= $_POST['firstname'] ?? ''?>"><br>

                <label for="lastname">Last name:</label><br>
                <input type="text" id="lastname" name="lastname" value="<?= $_POST['lastname'] ?? ''?>"><br>

                <label for="subject">Subject:</label><br>
                <input type="text" id="subject" name="subject" value="<?= $_POST['subject'] ?? ''?>"><br>

                <label for="message">Message:</label><br>
                <textarea type="text" id="message" name="message"><?= $_POST['message'] ?? ''?></textarea><br>

                <input type="hidden" name="action" value="send_custom_form">

                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" value="<?= $_POST['email'] ?? ''?>"><br><br>

                <input type="submit" value="Submit">
            </form>
        </div>
        <?php
    }
}
