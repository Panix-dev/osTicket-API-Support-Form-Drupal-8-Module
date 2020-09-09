<?php

namespace Drupal\ost_modal_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * OsTicketForm form class.
 */
class OsTicketForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ost_modal_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#ajax' => [
        'callback' => array($this, 'validateNameAjax'),
        'disable-refocus' => TRUE,
        'effect'  =>  'fade',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ],
      '#prefix' => '<div class="ost-field-wrapper ost-field-wrapper-name">',
      '#suffix' => '<div class="valid-message-class name-valid-message"></div></div>'
    );

    $form['email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#ajax' => [
        'callback' => array($this, 'validateEmailAjax'),
        'disable-refocus' => TRUE,
        'effect'  =>  'fade',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ],
      '#prefix' => '<div class="ost-field-wrapper ost-field-wrapper-email">',
      '#suffix' => '<div class="valid-message-class email-valid-message"></div></div>'
    );

    $form['phone'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Phone Number'),
      '#ajax' => [
        'callback' => array($this, 'validatePhoneAjax'),
        'disable-refocus' => TRUE,
        'effect'  =>  'fade',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ],
      '#prefix' => '<div class="ost-field-wrapper ost-field-wrapper-phone">',
      '#suffix' => '<div class="valid-message-class phone-valid-message"></div></div>'
    );

    $form['topic'] = [
      "#type" => "select", 
      "#title" => t("Select Topic"), 
      "#options" => array(
        "1" => t("General Inquiry"),
        "2" => t("Feedback"), 
        "10" => t("Report a Problem"),
        "11" => t("Access Issue"),
        "12" => t("Other"),
      ),
      '#prefix' => '<div class="ost-field-wrapper ost-field-wrapper-topic">',
      '#suffix' => '</div>'
    ];

    $form['message'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Inquiry'),
      '#ajax' => [
        'callback' => array($this, 'validateMessageAjax'),
        'disable-refocus' => TRUE,
        'effect'  =>  'fade',
        'event' => 'change',
        'progress' => array(
          'type' => 'throbber',
          'message' => NULL,
        ),
      ],
      '#prefix' => '<div class="ost-field-wrapper ost-field-wrapper-message">',
      '#suffix' => '<div class="valid-message-class message-valid-message"></div></div>'
    );

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Send Inquiry'),
      '#ajax' => [
        'callback' => '::createTicket',
      ]
    ];

    $form['output_message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>',
    ];

    // $form['#attached']['library'][] = '';

    return $form;
  }

  public function createTicket(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $config = array(
        'url'=>'https://your.domain.com/api/http.php/tickets.json',  // URL to site.tld/api/tickets.json
        'key'=>'YOUR_API_KEY_HERE'  // API Key goes here
    );
    $domain='https://service.hominel.com/api/http.php/tickets.json';

    $output_message = "";

    $data = array(
      'name'      =>  $form_state->getValue('name'),  // from name aka User/Client Name
      'email'     =>  $form_state->getValue('email'),  // from email aka User/Client Email
      'phone'     =>  $form_state->getValue('phone'),  // phone number aka User/Client Phone Number
      'subject'   =>  'Support Ticket from '.$form_state->getValue('name'),  // test subject, aka Issue Summary
      'message'   =>  $form_state->getValue('message')."\r\n \r\n Phone Number: ".$form_state->getValue('phone'),  // test ticket body, aka Issue Details.
      'ip'        =>  $_SERVER['REMOTE_ADDR'], // Should be IP address of the machine thats trying to open the ticket.
      'topicId'   =>  $form_state->getValue('topic'), // the help Topic that you want to use for the ticket 
      'source'   =>  "Web", // the help Topic that you want to use for the ticket 
      //'Agency'  =>  '58', //this is an example of a custom list entry. This should be the number of the entry.
      //'Site'  =>  'Bermuda'; // this is an example of a custom text field.  You can push anything into here you want. 
    );

    if($form_state->getErrors()) {
      $output_message = '<div class="ost-error-submit">Ticket not created. Please make sure everything is correct and try again.</div>';
      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="output-submit-message">' . $output_message . '</div>'
          )
      );
    } else {

      set_time_limit(30);

      #curl post
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $config['url']);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($ch, CURLOPT_USERAGENT, 'osTicket API Client v1.8');
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Expect:', 'X-API-Key: '.$config['key']));
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $result=curl_exec($ch);
      $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($code != 201) {
        $output_message = '<div class="ost-error-submit">Unable to create Ticket. Please make sure you filled everything correctly and try again.</div>';
        // Replace the line above with the one below in order to see what the error is in case of a problem
        // $output_message = 'Unable to create ticket: '.$result;
      } else {
        $ticket_id = (int) $result;
        if(isset($ticket_id) && $ticket_id!='')
        {
          $output_message = '<div class="ost-success-submit">Your Ticket has been created sucessfully</div>';
        }else{
          $output_message = '<div class="ost-error-submit">Your Ticket has not been created. Please try again later.</div>';
        }
      }

      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="output-submit-message">' . $output_message . '</div>'
          )
      );
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
  * Validate functions for form fields.
  */
  protected function validateName(array &$form, FormStateInterface $form_state) {
    if (!ctype_alpha(str_replace(' ', '', $form_state->getValue('name'))) || empty($form_state->getValue('name'))) {
      return FALSE;
    }
    return TRUE;
  }

  protected function validateEmail(array &$form, FormStateInterface $form_state) {
    if (!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      return FALSE;
    }
    return TRUE;
  }

  protected function validatePhone(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('phone')) || preg_match('/[A-Za-z]/', $form_state->getValue('phone'))) {
      return FALSE;
    }
    return TRUE;
  }

  protected function validateMessage(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('message'))) {
      return FALSE;
    }
    return TRUE;
  }

  /**
  * Ajax callback to validate the form fields.
  */
  public function validateNameAjax(array &$form, FormStateInterface $form_state) {
    $valid = $this->validateName($form, $form_state);
    $response = new AjaxResponse();
    if ($valid) {
      $css = ['border' => '1px solid green'];
      $css_message = ['color' => 'green'];
      $message = '';
    }
    else {
      $css = ['border' => '1px solid #ea1212'];
      $css_message = ['color' => '#ea1212'];
      $message = $this->t('A valid name is required.');
    }

    $response->addCommand(new HtmlCommand('.name-valid-message', $message));
    $response->addCommand(new CssCommand('.name-valid-message', $css_message));
    $response->addCommand(new CssCommand('#edit-name', $css));
    
    return $response;
  }

  public function validateEmailAjax(array &$form, FormStateInterface $form_state) {
    $valid = $this->validateEmail($form, $form_state);
    $response = new AjaxResponse();
    if ($valid) {
      $css = ['border' => '1px solid green'];
      $css_message = ['color' => 'green'];
      $message = '';
    }
    else {
      $css = ['border' => '1px solid #ea1212'];
      $css_message = ['color' => '#ea1212'];
      $message = $this->t('A valid email is required.');
    }

    $response->addCommand(new HtmlCommand('.email-valid-message', $message));
    $response->addCommand(new CssCommand('.email-valid-message', $css_message));
    $response->addCommand(new CssCommand('#edit-email', $css));
    
    return $response;
  }

  public function validatePhoneAjax(array &$form, FormStateInterface $form_state) {
    $valid = $this->validatePhone($form, $form_state);
    $response = new AjaxResponse();
    if ($valid) {
      $css = ['border' => '1px solid green'];
      $css_message = ['color' => 'green'];
      $message = '';
    }
    else {
      $css = ['border' => '1px solid #ea1212'];
      $css_message = ['color' => '#ea1212'];
      $message = $this->t('A valid phone number is required.');
    }

    $response->addCommand(new HtmlCommand('.phone-valid-message', $message));
    $response->addCommand(new CssCommand('.phone-valid-message', $css_message));
    $response->addCommand(new CssCommand('#edit-phone', $css));
    
    return $response;
  }

  public function validateMessageAjax(array &$form, FormStateInterface $form_state) {
    $valid = $this->validateMessage($form, $form_state);
    $response = new AjaxResponse();
    if ($valid) {
      $css = ['border' => '1px solid green'];
      $css_message = ['color' => 'green'];
      $message = '';
    }
    else {
      $css = ['border' => '1px solid #ea1212'];
      $css_message = ['color' => '#ea1212'];
      $message = $this->t('A valid message is required.');
    }

    $response->addCommand(new HtmlCommand('.message-valid-message', $message));
    $response->addCommand(new CssCommand('.message-valid-message', $css_message));
    $response->addCommand(new CssCommand('#edit-message', $css));
    
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // This way even if our form gets somehow submitted (programatically or otherwise), the validation will still be run. 

    // Validate name.
    if (!$this->validateName($form, $form_state)) {
      $form_state->setErrorByName('name', $this->t('A valid name is required.'));
    }
    // Validate email.
    if (!$this->validateEmail($form, $form_state)) {
      $form_state->setErrorByName('email', $this->t('A valid email is required.'));
    }
    // Validate phone.
    if (!$this->validatePhone($form, $form_state)) {
      $form_state->setErrorByName('phone', $this->t('A valid phone is required.'));
    }
    // Validate message.
    if (!$this->validateMessage($form, $form_state)) {
      $form_state->setErrorByName('message', $this->t('A valid message is required.'));
    }
    parent::validateForm($form, $form_state);
  }

}
