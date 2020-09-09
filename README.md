# Drupal 8 Module - osTicket API Support Ajax Form

> Create a ticket using an ajax drupal form and the osTicket API.

A Drupal 8 custom module that created an ajax form that on submit calls a callback function to create a ticket using the osTicket API. Each field is validated using ajax once focus is removed from the input field.


## Table of Contents


> Try `clicking` on each of the `anchor links` below so you can get redirected to the appropriate section.

- [Form Action](#form-action)
- [Form Block](#form-block)
- [Form Field](#form-field)
- [Validation Callback](#validation-callback)
- [Create Ticket](#create-ticket)
- [Contact Details](#contact-details)


## Form Action


```php
$form['actions'] = [
  '#type' => 'button',
  '#value' => $this->t('Send Inquiry'),
  '#ajax' => [
    'callback' => '::createTicket',
  ]
];
```


## Form Block


```php
/**
 * Provides a 'osTicket' Block.
 *
 * @Block(
 *   id = "osticket_block",
 *   admin_label = @Translation("osTicket block"),
 *   category = @Translation("osTicket Block"),
 * )
 */
class OsTicketBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\ost_modal_form\Form\OsTicketForm');
    return $form;
  }
}
```


## Form Field


```php
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
```


## Validation Callback


```php
protected function validateName(array &$form, FormStateInterface $form_state) {
  if (!ctype_alpha(str_replace(' ', '', $form_state->getValue('name'))) || empty($form_state->getValue('name'))) {
    return FALSE;
  }
  return TRUE;
}
```


## Create Ticket


```php
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
```


## Contact Details


> :computer: [https://pagapiou.com](https://pagapiou.com)

> :email: [hello@pagapiou.com](mailto:hello@pagapiou.com)

> :iphone: [LinkedIn](https://www.linkedin.com/in/agapiou/)

> :iphone: [Instagram](https://www.instagram.com/panos_agapiou/)

> :iphone: [Facebook](https://www.facebook.com/panagiotis.agapiou)

