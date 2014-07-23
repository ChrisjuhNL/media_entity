<?php

/**
 * @file
 * Definition of Drupal\media_entity\Plugin\views\field\LinkDelete.
 */

namespace Drupal\media_entity\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler to present a link to delete a node.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("media_link_delete")
 */
class LinkDelete extends Link {

  /**
   * Prepares the link to delete a node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The node entity this field belongs to.
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from the view's result set.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($node, ResultRow $values) {
    // Ensure user has access to delete this node.
    if (!$node->access('delete')) {
      return;
    }

    $this->options['alter']['make_link'] = TRUE;
    $this->options['alter']['path'] = "media/" . $node->id() . "/delete";
    $this->options['alter']['query'] = drupal_get_destination();

    $text = !empty($this->options['text']) ? $this->options['text'] : t('Delete');
    return $text;
  }

}
