<?php

namespace Give\Tests\Feature\Actions;

use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WPDieException;
use ZipArchive;

/**
 * Tests for add-on ZIP upload and activation AJAX handlers.
 *
 * @since TBD
 *
 * @covers ::give_upload_addon_handler
 * @covers ::give_activate_addon_handler
 */
final class AddonUploadActivateTest extends TestCase
{
	use RefreshDatabase;

	/** @var string[] Directory paths created during tests for cleanup. */
	private $createdDirs = [];

	/** @var string[] File paths created during tests for cleanup. */
	private $createdFiles = [];

	/** @var string[] Slugs of test plugins created via createTestPluginInPluginsDir(). */
	private $testPluginSlugs = [];

	/**
	 * @since TBD
	 */
	public function setUp(): void
	{
		parent::setUp();

		if ( ! defined( 'GIVE_UNIT_TESTS' ) ) {
			define( 'GIVE_UNIT_TESTS', true );
		}

		if ( ! function_exists( 'give_upload_addon_handler' ) ) {
			require_once GIVE_PLUGIN_DIR . 'includes/admin/add-ons/actions.php';
		}
	}

	/**
	 * @since TBD
	 */
	public function tearDown(): void
	{
		foreach ( $this->createdDirs as $dir ) {
			if ( is_dir( $dir ) ) {
				$this->recursiveRemoveDir( $dir );
			}
		}
		$this->createdDirs = [];

		foreach ( $this->createdFiles as $file ) {
			if ( file_exists( $file ) ) {
				@unlink( $file );
			}
		}
		$this->createdFiles = [];

		foreach ( $this->testPluginSlugs as $slug ) {
			$plugin_file = "{$slug}/{$slug}.php";
			deactivate_plugins( $plugin_file, true );
			$dir = WP_PLUGIN_DIR . "/{$slug}";
			if ( is_dir( $dir ) ) {
				$this->recursiveRemoveDir( $dir );
			}
		}
		$this->testPluginSlugs = [];

		unset(
			$_FILES['file'],
			$_POST['_give_upload_addon'],
			$_POST['plugin'],
			$_POST['_wpnonce']
		);

		wp_clean_plugins_cache( true );

		parent::tearDown();
	}

	/**
	 * @since TBD
	 */
	public function testUploadHandlerRequiresUploadPluginsCapability(): void
	{
		$subscriber_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber_id );

		$this->prepareUploadPost( 'some-file.zip' );

		$response = $this->invokeAndCatchWpDie( function () {
			give_upload_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString(
			'does not have permission',
			$response['data']['errorMsg']
		);
	}

	/**
	 * @since TBD
	 */
	public function testUploadHandlerRejectsNonZipFile(): void
	{
		$this->setAdminAndAddUploaderCap();

		$temp_file = $this->createTempFile( 'not-a-zip.txt', 'plain content' );

		$_FILES['file'] = [
			'name'     => 'document.txt',
			'type'     => 'text/plain',
			'tmp_name' => $temp_file,
			'error'    => UPLOAD_ERR_OK,
			'size'     => filesize( $temp_file ),
		];
		$_POST['_give_upload_addon'] = wp_create_nonce( 'give-upload-addon' );

		$response = $this->invokeAndCatchWpDie( function () {
			give_upload_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'ZIP', $response['data']['errorMsg'] );
	}

	/**
	 * @since TBD
	 */
	public function testUploadHandlerDetectsAlreadyInstalledPlugin(): void
	{
		$this->setAdminAndAddUploaderCap();

		$slug = 'give-existing-plugin';
		$this->createTestPluginInPluginsDir( $slug, 'Existing Plugin' );

		$this->prepareUploadPost( $slug . '.zip' );

		$response = $this->invokeAndCatchWpDie( function () {
			give_upload_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'already installed', $response['data']['errorMsg'] );
	}

	/**
	 * Verifies diff-based detection works when ZIP filename differs from the
	 * internal folder name.
	 *
	 * @since TBD
	 */
	public function testUploadHandlerDiffDetectsNewPluginWithRenamedZip(): void
	{
		if ( ! class_exists( 'ZipArchive' ) ) {
			$this->markTestSkipped( 'ZipArchive extension is required for this test.' );
		}

		$this->setAdminAndAddUploaderCap();

		// Baseline plugin for the pre-upload snapshot.
		$this->createTestPluginInPluginsDir( 'give-baseline-addon', 'Baseline Addon' );

		// Internal folder name different from external ZIP filename.
		$internal_slug = 'give-recurring-donations';
		$zip_filename  = 'give-donations-2.19.0.zip';

		$zip_path = $this->createTestPluginZip( $internal_slug, 'Give Recurring Donations', $zip_filename );

		$_FILES['file'] = [
			'name'     => $zip_filename,
			'type'     => 'application/zip',
			'tmp_name' => $zip_path,
			'error'    => UPLOAD_ERR_OK,
			'size'     => filesize( $zip_path ),
		];
		$_POST['_give_upload_addon'] = wp_create_nonce( 'give-upload-addon' );

		add_filter( 'filesystem_method', fn() => 'direct' );

		$response = $this->invokeAndCatchWpDie( function () {
			give_upload_addon_handler();
		} );

		$this->assertTrue(
			$response['success'],
			isset( $response['data']['errorMsg'] )
				? 'Upload failed: ' . $response['data']['errorMsg']
				: 'Handler did not succeed'
		);
		$this->assertEquals( "{$internal_slug}/{$internal_slug}.php", $response['data']['pluginPath'] );
		$this->assertEquals( 'Give Recurring Donations', $response['data']['pluginName'] );

		// Track extracted directory for cleanup.
		$extracted_dir = WP_PLUGIN_DIR . "/{$internal_slug}";
		if ( is_dir( $extracted_dir ) ) {
			$this->createdDirs[] = $extracted_dir;
		}
	}

	/**
	 * Verifies the error message when both diff and fallback fail.
	 *
	 * @since TBD
	 */
	public function testUploadHandlerReturnsErrorWhenNoPluginDetected(): void
	{
		if ( ! class_exists( 'ZipArchive' ) ) {
			$this->markTestSkipped( 'ZipArchive extension is required for this test.' );
		}

		$this->setAdminAndAddUploaderCap();

		// ZIP containing no valid plugin file.
		$zip_filename = 'some-non-plugin-archive.zip';
		$zip_path     = $this->createNonPluginZip( $zip_filename );

		$_FILES['file'] = [
			'name'     => $zip_filename,
			'type'     => 'application/zip',
			'tmp_name' => $zip_path,
			'error'    => UPLOAD_ERR_OK,
			'size'     => filesize( $zip_path ),
		];
		$_POST['_give_upload_addon'] = wp_create_nonce( 'give-upload-addon' );

		add_filter( 'filesystem_method', fn() => 'direct' );

		$response = $this->invokeAndCatchWpDie( function () {
			give_upload_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'could not detect', $response['data']['errorMsg'] );
	}

	/**
	 * @since TBD
	 */
	public function testUploadHandlerRejectsNonDirectFilesystem(): void
	{
		$this->setAdminAndAddUploaderCap();

		$this->prepareUploadPost( 'test-plugin.zip' );

		add_filter( 'filesystem_method', fn() => 'ftp' );

		$response = $this->invokeAndCatchWpDie( function () {
			give_upload_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'direct access', $response['data']['errorMsg'] );
	}

	/**
	 * @since TBD
	 */
	public function testActivateHandlerRejectsNonAdmin(): void
	{
		$subscriber_id = $this->factory()->user->create( [ 'role' => 'subscriber' ] );
		wp_set_current_user( $subscriber_id );

		$_POST['plugin']   = 'some-plugin/some-plugin.php';
		$_POST['_wpnonce'] = wp_create_nonce( 'give_activate-some-plugin/some-plugin.php' );

		give_activate_addon_handler();

		$this->assertNotContains(
			'some-plugin/some-plugin.php',
			get_option( 'active_plugins', [] ),
			'Subscriber should not be able to activate the plugin.'
		);
	}

	/**
	 * @since TBD
	 */
	public function testActivateHandlerRejectsEmptyPluginPath(): void
	{
		$this->setAdminUser();

		$_POST['plugin']   = '';
		$_POST['_wpnonce'] = wp_create_nonce( 'give_activate-' );

		$response = $this->invokeAndCatchWpDie( function () {
			give_activate_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'No plugin path', $response['data']['errorMsg'] );
	}

	/**
	 * @since TBD
	 */
	public function testActivateHandlerRejectsNonexistentPluginFile(): void
	{
		$this->setAdminUser();

		$slug = 'nonexistent-addon';
		$_POST['plugin']   = "{$slug}/{$slug}.php";
		$_POST['_wpnonce'] = wp_create_nonce( "give_activate-{$slug}/{$slug}.php" );

		$response = $this->invokeAndCatchWpDie( function () {
			give_activate_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'could not be found', $response['data']['errorMsg'] );
	}

	/**
	 * @since TBD
	 */
	public function testActivateHandlerRejectsInvalidPluginHeader(): void
	{
		$this->setAdminUser();

		// PHP file without a WordPress plugin header.
		$slug = 'invalid-header-plugin';
		$this->createTestPluginInPluginsDir( $slug, '' );

		$_POST['plugin']   = "{$slug}/{$slug}.php";
		$_POST['_wpnonce'] = wp_create_nonce( "give_activate-{$slug}/{$slug}.php" );

		$response = $this->invokeAndCatchWpDie( function () {
			give_activate_addon_handler();
		} );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'valid plugin header', $response['data']['errorMsg'] );
	}

	/**
	 * @since TBD
	 */
	public function testActivateHandlerActivatesValidPlugin(): void
	{
		$this->setAdminUser();

		$slug = 'valid-test-addon';
		$this->createTestPluginInPluginsDir( $slug, 'Valid Test Addon' );

		$plugin_path = "{$slug}/{$slug}.php";

		$_POST['plugin']   = $plugin_path;
		$_POST['_wpnonce'] = wp_create_nonce( "give_activate-{$plugin_path}" );

		$response = $this->invokeAndCatchWpDie( function () {
			give_activate_addon_handler();
		} );

		$this->assertTrue( $response['success'] );
		$this->assertArrayHasKey( 'licenseSectionHtml', $response['data'] );
		$this->assertContains( $plugin_path, get_option( 'active_plugins' ) );
	}

	/**
	 * Creates a test plugin directory inside WP_PLUGIN_DIR.
	 *
	 * @since TBD
	 *
	 * @param string $slug Plugin directory slug.
	 * @param string $name Plugin Name header. Empty string omits the header.
	 *
	 * @return string The plugin's relative path (slug/slug.php).
	 */
	private function createTestPluginInPluginsDir( string $slug, string $name ): string
	{
		$dir = WP_PLUGIN_DIR . "/{$slug}";
		wp_mkdir_p( $dir );
		$this->createdDirs[]     = $dir;
		$this->testPluginSlugs[] = $slug;

		if ( '' === $name ) {
			file_put_contents( "{$dir}/{$slug}.php", "<?php\n// Just a comment, no plugin header.\n" );
		} else {
			file_put_contents( "{$dir}/{$slug}.php", "<?php\n/**\n * Plugin Name: {$name}\n * Version: 1.0.0\n */" );
		}

		return "{$slug}/{$slug}.php";
	}

	/**
	 * Creates a valid ZIP file containing a test plugin directory.
	 *
	 * The ZIP's internal folder name is $pluginSlug regardless of the outer
	 * filename, simulating the renamed-ZIP scenario.
	 *
	 * @since TBD
	 */
	private function createTestPluginZip( string $pluginSlug, string $pluginName, ?string $zipFilename = null ): string
	{
		$tmp_dir = sys_get_temp_dir() . '/give-test-zip-src-' . uniqid();
		wp_mkdir_p( $tmp_dir . '/' . $pluginSlug );
		$this->createdDirs[] = $tmp_dir;

		file_put_contents(
			$tmp_dir . '/' . $pluginSlug . '/' . $pluginSlug . '.php',
			"<?php\n/**\n * Plugin Name: {$pluginName}\n * Version: 1.0.0\n */"
		);

		$zip_file = sys_get_temp_dir() . '/give-test-' . ( $zipFilename ?? "{$pluginSlug}.zip" );
		$this->createdFiles[] = $zip_file;

		if ( file_exists( $zip_file ) ) {
			@unlink( $zip_file );
		}

		$zip = new ZipArchive();
		$zip->open( $zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		$zip->addFile( $tmp_dir . '/' . $pluginSlug . '/' . $pluginSlug . '.php', "{$pluginSlug}/{$pluginSlug}.php" );
		$zip->close();

		return $zip_file;
	}

	/**
	 * Creates a ZIP that does NOT contain a valid WordPress plugin file.
	 *
	 * @since TBD
	 */
	private function createNonPluginZip( string $zipFilename ): string
	{
		$tmp_dir = sys_get_temp_dir() . '/give-test-nonplugin-' . uniqid();
		wp_mkdir_p( $tmp_dir . '/random-folder' );
		$this->createdDirs[] = $tmp_dir;

		file_put_contents( $tmp_dir . '/random-folder/readme.txt', 'Not a plugin.' );

		$zip_file = sys_get_temp_dir() . '/give-test-' . $zipFilename;
		$this->createdFiles[] = $zip_file;

		if ( file_exists( $zip_file ) ) {
			@unlink( $zip_file );
		}

		$zip = new ZipArchive();
		$zip->open( $zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE );
		$zip->addFile( $tmp_dir . '/random-folder/readme.txt', 'random-folder/readme.txt' );
		$zip->close();

		return $zip_file;
	}

	/**
	 * Sets up $_FILES and nonce for a valid upload request.
	 *
	 * @since TBD
	 */
	private function prepareUploadPost( string $filename ): void
	{
		$temp_file = $this->createTempFile( $filename, 'fake zip content' );

		$_FILES['file'] = [
			'name'     => $filename,
			'type'     => 'application/zip',
			'tmp_name' => $temp_file,
			'error'    => UPLOAD_ERR_OK,
			'size'     => filesize( $temp_file ),
		];
		$_POST['_give_upload_addon'] = wp_create_nonce( 'give-upload-addon' );
	}

	/**
	 * Creates a temporary file and returns its path.
	 *
	 * @since TBD
	 */
	private function createTempFile( string $name, string $content ): string
	{
		$path = sys_get_temp_dir() . '/give-upload-test-' . uniqid() . '-' . $name;
		file_put_contents( $path, $content );
		$this->createdFiles[] = $path;

		return $path;
	}

	/**
	 * Logs in as admin with upload_plugins and manage_give_settings caps.
	 *
	 * @since TBD
	 */
	private function setAdminAndAddUploaderCap(): void
	{
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );
	}

	/**
	 * Logs in as admin with manage_give_settings cap.
	 *
	 * @since TBD
	 */
	private function setAdminUser(): void
	{
		$admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_id );
	}

	/**
	 * Invokes a callable that triggers wp_die() and returns the parsed JSON response.
	 *
	 * @since TBD
	 *
	 * @param callable $callable The handler invocation.
	 *
	 * @return array The decoded JSON response.
	 */
	private function invokeAndCatchWpDie( callable $callable ): array
	{
		ob_start();
		try {
			$callable();
			$output = ob_get_clean();
			$this->fail( "Expected WPDieException was not thrown. Output: {$output}" );
		} catch ( WPDieException $e ) {
			$output = ob_get_clean();
			if ( empty( $output ) ) {
				$output = $e->getMessage();
			}

			// Strip WP test suite debug output appended by _wp_die_handler.
			$json_end = strpos( $output, "\nwp_die()" );
			if ( false !== $json_end ) {
				$output = substr( $output, 0, $json_end );
			}

			$response = json_decode( $output, true );
			if ( ! is_array( $response ) ) {
				$this->fail( "Handler output is not valid JSON: \"{$output}\"" );
			}

			return $response;
		}
	}

	/**
	 * Recursively removes a directory and all its contents.
	 *
	 * @since TBD
	 */
	private function recursiveRemoveDir( string $dir ): void
	{
		if ( ! is_dir( $dir ) ) {
			return;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \FilesystemIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ( $iterator as $item ) {
			if ( $item->isDir() ) {
				@rmdir( $item->getPathname() );
			} else {
				@unlink( $item->getPathname() );
			}
		}

		@rmdir( $dir );
	}
}
