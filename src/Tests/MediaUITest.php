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
  public static $modules = array('media_entity', 'field_ui');

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
    $bundle = array(
      'id' => strtolower($this->randomName()),
      'label' => $this->randomString(),
      'description' => $this->randomString(),
      'type' => $this->randomString(),
    );
    $this->drupalPostForm('admin/structure/media/add', $bundle, t('Save media bundle'));
    $this->assertUrl('admin/structure/media');
    $this->assertRaw(String::checkPlain($bundle['label']));
    $this->assertRaw(Xss::filterAdmin($bundle['description']));

    // Tests media bundle edit form.
    $this->drupalGet('admin/structure/media/manage/' . $bundle['id']);
    $this->assertFieldByName('label', $bundle['label']);
    $this->assertFieldByName('description', $bundle['description']);
    $this->assertFieldByName('type', $bundle['type']);
    $bundle['label'] = $this->randomString();
    $bundle['description'] = $this->randomString();
    $bundle['type'] = $this->randomString();
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


    // Tests media add form.
    $edit = array(
      'name[0][value]' => $this->randomString(),
    );
    $this->drupalPostForm('media/add/default', $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');
    $media_id = \Drupal::entityQuery('media')->execute();
    $media_id = reset($media_id);

    // Tests media edit form.
    $this->drupalGet('media/' . $media_id . '/edit');
    $edit['name[0][value]'] = $this->randomString();
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertTitle($edit['name[0][value]'] . ' | Drupal');

    // Tests media delete form.
    $this->drupalPostForm('media/' . $media_id . '/delete', array(), t('Delete'));
    $media_id = \Drupal::entityQuery('media')->execute();
    $this->assertFalse($media_id);
  }

}
