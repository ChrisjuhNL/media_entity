<?php

/**
 * @file
 * Contains \Drupal\media_entity\MediaBundleInterface.
 */

namespace Drupal\media_entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a media bundle entity.
 */
interface MediaBundleInterface extends ConfigEntityInterface {

  /**
   * Returns the type of the media bundle.
   *
   * @return string
   *   Returns the type of the media bundle.
   */
  public function type();

  /**
   * Returns the label.
   *
   * @param \Drupal\media_entity\MediaInterface $media
   *   The Media entity.
   *
   * @return string|bool
   *   Returns the label.
   */
  public static function getLabel(MediaInterface $media);

  /**
   * {@inheritdoc}
   */
  public static function exists($id);

  /**
   * Returns description.
   *
   * @return string
   *   Returns description.
   */
  public function getDescription();
}
