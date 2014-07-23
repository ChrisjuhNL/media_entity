<?php

/**
 * @file
 * Contains \Drupal\media_entity\Tests\MediaUITest.
 */

namespace Drupal\media_entity\Tests;

use Drupal\Component\Utility\String;
use Drupal\Component\Utility\Xss;
use Drupal\simpletest\WebTestBase;

/**
 * Ensures that media UI work correctly.
 *
 * @group media
 */
class MediaUITest extends WebTestBase {

  /**
   * The test user.
   *
   * @var string
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('media_entity', 'views', 'field_ui');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser(array(
      'administer media',
      'administer media fields',
      'administer media form display',
      'administer media display',
      // Media entity permissions.
      'view media',
      'create media',
      'update media',
      'delete media',
    ));
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests a media bundle administration.
   */
  public function testMediaBundles() {
    // Test and create one media bundle
    $bundle = $this->createMediaBundle();

    // Check if all pages exist
    // TODO: Add new media item & add new pages: "media/{id}/edit", "media/{id}/delete" & "media/{id}
    $this->assertLinkByHref('media/add/' . $bundle['id']);
    $this->assertLinkByHref('admin/structure/media/add');
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id']);
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/fields');
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/form-display');
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/display');
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/delete');

    // Tests media bundle edit form.
    $this->drupalGet('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);
    $this->assertFieldByName('type', $bundle['type']);

    $bundle['label'] = $this->randomName();
    $bundle['description'] = $this->randomName();
    $bundle['type'] = $this->randomName();
    $this->drupalPostForm(NULL, $bundle, t('Save media bundle'));
    $this->drupalGet('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);
    $this->assertFieldByName('type', $bundle['type']);

    // Tests media bundle delete form.
    $this->assertLinkByHref('admin/structure/media/manage/' . $bundle['id'] . '/delete');
    $this->clickLink(t('Delete'));
    $this->assertUrl('admin/structure/media/manage/' . $bundle['id'] . '/delete');
    $this->drupalPostForm(NULL, array(), t('Delete'));
    $this->assertUrl('admin/structure/media');
    $this->assertRaw(t('The media bundle %name has been deleted.', array('%name' => $bundle['label'])));
    $this->assertNoRaw(Xss::filterAdmin($bundle['description']));
  }

  /**
   * Tests the media actions (add/edit/delete).
   */
  public function testMediaWithOnlyOneBundle() {
    // Test and create one media bundle
    $bundle = $this->createMediaBundle();

    // Assert that media list is empty.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText('No media items.');

    // Test when adding new media it automatically redirect to new media bundle page
    // Should only happen when there is only 1 media bundle available
    $this->drupalGet('media/add');
    $this->assertResponse(200);
    $this->assertUrl('media/add/' . $bundle['id']);

    // Tests media item add form.
    $edit = array(
      'name[0][value]' => $this->randomName(),
    );
    $this->drupalPostForm('media/add', $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');
    $media_id = \Drupal::entityQuery('media')->execute();
    $media_id = reset($media_id);

    // Test if the media list contains exactly 1 media bundle.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText($edit['name[0][value]']);

    // Tests media edit form.
    $this->drupalGet('media/' . $media_id . '/edit');
    $edit['name[0][value]'] = $this->randomName();
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');

    // Assert that the media list updates after an edit.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText($edit['name[0][value]']);

    // Tests media delete form.
    $this->drupalPostForm('media/' . $media_id . '/delete', array(), t('Delete'));
    $media_id = \Drupal::entityQuery('media')->execute();
    $this->assertFalse($media_id);

    // Assert that the media list is empty after deleting the media item.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertNoText($edit['name[0][value]']);
    $this->assertText('No media items.');
  }

  /**
   * Tests if the "media/add" page gives you a selecting option if there are one or more media bundles available.
   */
  public function testMediaWithMultipleBundles() {
    // Tests and creates the first media bundle
    $firstMediaBundle = $this->createMediaBundle();

    // Test and create a second media bundle
    $secondMediaBundle = $this->createMediaBundle();

    // Test if media/add displays two media bundle options
    $this->drupalGet('media/add');

    // Checks for the first media bundle
    $this->assertRaw(String::checkPlain($firstMediaBundle['label']));
    $this->assertRaw(Xss::filterAdmin($firstMediaBundle['description']));

    // Checks for the second media bundle
    $this->assertRaw(String::checkPlain($secondMediaBundle['label']));
    $this->assertRaw(Xss::filterAdmin($secondMediaBundle['description']));

    // Continue testing media bundle filter
    //$this->doTestMediaBundleFilter($firstMediaBundle, $secondMediaBundle);
  }

  /**
   * Creates and tests a new media bundle.
   *
   * @return array
   *   Returns the media bundle fields.
   */
  public function createMediaBundle() {
    // Generates and holds all media bundle fields
    $edit = array(
      'id' => strtolower($this->randomName()),
      'label' => $this->randomName(),
      'type' => $this->randomName(),
      'description' => $this->randomName(),
    );

    // Create new media bundle
    $this->drupalPostForm('admin/structure/media/add', $edit, t('Save media bundle'));

    // Check if media bundle is succesfully created
    $this->drupalGet('admin/structure/media');
    $this->assertResponse(200);
    $this->assertRaw(String::checkPlain($edit['label']));
    $this->assertRaw(Xss::filterAdmin($edit['description']));

    return $edit;
  }

}
