<?php

/**
 * @file
 * Contains of \Drupal\media_entity\MediaStorage.
 */

namespace Drupal\media_entity;

use Drupal\Core\Entity\ContentEntityDatabaseStorage;

/**
 * Media storage class.
 */
class MediaStorage extends ContentEntityDatabaseStorage implements MediaStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function onBundleRename($bundle, $bundle_new) {
    parent::onBundleRename($bundle, $bundle_new);
    // Update media entities with a new bundle.
    $this->database->update('media')
      ->fields(array('bundle' => $bundle_new))
      ->condition('bundle', $bundle)
      ->execute();
    $this->database->update('media_field_data')
      ->fields(array('bundle' => $bundle_new))
      ->condition('bundle', $bundle)
      ->execute();
  }

}
