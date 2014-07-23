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
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $mediaBundle;

  /**
   * @var \Drupal\media_entity\MediaBundleInterface
   */
  protected $mediaBundleTwo;

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
    $this->mediaBundle = entity_create('media_bundle', array(
      'id' => 'default',
      'label' => 'Unnamed',
      'type' => 'Unknown',
      'description' => 'Media description',
    ));
    $this->mediaBundle->save();
  }

  /**
   * Tests a media bundle administration.
   */
  public function testMediaBundles() {
    $this->drupalGet('admin/structure/media');
    $this->assertResponse(200);

    $this->assertRaw(String::checkPlain($this->mediaBundle->label()));
    $this->assertRaw(Xss::filterAdmin($this->mediaBundle->getDescription()));
    $this->assertLinkByHref('admin/structure/media/add');
    $this->assertLinkByHref('admin/structure/media/manage/default');
    $this->assertLinkByHref('admin/structure/media/manage/default/fields');
    $this->assertLinkByHref('admin/structure/media/manage/default/form-display');
    $this->assertLinkByHref('admin/structure/media/manage/default/display');
    $this->assertLinkByHref('admin/structure/media/manage/default/delete');

    // Tests media bundle add form.
    $bundle = $this->createMediaBundle();

    $this->assertUrl('admin/structure/media');
    $this->assertRaw(String::checkPlain($bundle['label']));
    $this->assertRaw(Xss::filterAdmin($bundle['description']));

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
    // Assert that media list is empty.
    $this->drupalGet('admin/content/media');
    $this->assertResponse(200);
    $this->assertText('No media items.');

    // Test when adding new media it automatically redirect to new media bundle page
    // Should only happen when there is only 1 media bundle available
    $this->drupalGet('media/add');
    $this->assertResponse(200);
    $this->assertUrl('media/add/' . $this->mediaBundle->id());

    // Tests media item add form.
    $edit = array(
      'name[0][value]' => $this->randomName(),
    );
    $this->drupalPostForm('media/add', $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');
    $media_id = \Drupal::entityQuery('media')->execute();
    $media_id = reset($media_id);

    // Assert that media list contains exactly 1 item.
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
   * Tests the media actions (add/edit/delete) when multiple media bundles exist.
   */
  public function testMediaWithMultipleBundles() {
    // @TODO: Bundle again from scratch, and then check for multiple bundle at "media/add".

    $mediaBundle = $this->createMediaBundle();
  }


  public function createMediaBundle() {
    $this->drupalGet('admin/structure/media/add');

    $edit = array(
      'id' => strtolower($this->randomName()),
      'label' => $this->randomName(),
      'type' => $this->randomName(),
      'description' => $this->randomName(),
    );

    $this->drupalPostForm('admin/structure/media/add', $edit, t('Save media bundle'));


    return $edit;
  }

}
