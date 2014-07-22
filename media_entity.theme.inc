<?php

/**
 * Prepares variables for list of available media bundles.
 *
 * Default template: media-add-list.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - content: An array of content types.
 */
function template_preprocess_media_add_list(&$variables) {
  $variables['types'] = array();
  if (!empty($variables['content'])) {
    foreach ($variables['content'] as $type) {
      $link_text = '<span class="label">' . $type->label . '</span><div class="description">' . $type->description . '</div>';
      $variables['types'][$type->id] = array(
        'add_link' => l($link_text, 'media/add/' . $type->id, array('html' => TRUE)),
      );
    }
  }
}
