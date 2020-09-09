<?php

namespace Drupal\ost_modal_form\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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