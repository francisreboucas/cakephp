<?php
/**
 * FolderTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/view/1196/Testing>
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/view/1196/Testing CakePHP(tm) Tests
 * @package       Cake.Test.Case.Utility
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * FolderTest class
 *
 * @package       Cake.Test.Case.Utility
 */
class FolderTest extends CakeTestCase {

/**
 * testBasic method
 *
 * @access public
 * @return void
 */
	public function testBasic() {
		$path = dirname(__FILE__);
		$Folder = new Folder($path);

		$result = $Folder->pwd();
		$this->assertEqual($result, $path);

		$result = Folder::addPathElement($path, 'test');
		$expected = $path . DS . 'test';
		$this->assertEqual($expected, $result);

		$result = $Folder->cd(ROOT);
		$expected = ROOT;
		$this->assertEqual($expected, $result);

		$result = $Folder->cd(ROOT . DS . 'non-existent');
		$this->assertFalse($result);
	}

/**
 * testInPath method
 *
 * @access public
 * @return void
 */
	public function testInPath() {
		$path = dirname(dirname(__FILE__));
		$inside = dirname($path) . DS;

		$Folder = new Folder($path);

		$result = $Folder->pwd();
		$this->assertEqual($result, $path);

		$result = Folder::isSlashTerm($inside);
		$this->assertTrue($result);

		$result = $Folder->realpath('Test/');
		$this->assertEqual($result, $path . DS .'Test' . DS);

		$result = $Folder->inPath('Test' . DS);
		$this->assertTrue($result);

		$result = $Folder->inPath(DS . 'non-existing' . $inside);
		$this->assertFalse($result);
	}

/**
 * test creation of single and mulitple paths.
 *
 * @return void
 */
	public function testCreation() {
		$folder = new Folder(TMP . 'tests');
		$result = $folder->create(TMP . 'tests' . DS . 'first' . DS . 'second' . DS . 'third');
		$this->assertTrue($result);

		rmdir(TMP . 'tests' . DS . 'first' . DS . 'second' . DS . 'third');
		rmdir(TMP . 'tests' . DS . 'first' . DS . 'second');
		rmdir(TMP . 'tests' . DS . 'first');

		$folder = new Folder(TMP . 'tests');
		$result = $folder->create(TMP . 'tests' . DS . 'first');
		$this->assertTrue($result);
		rmdir(TMP . 'tests' . DS . 'first');
	}

/**
 * test that creation of folders with trailing ds works
 *
 * @return void
 */
	public function testCreateWithTrailingDs() {
		$folder = new Folder(TMP);
		$path = TMP . 'tests' . DS . 'trailing' . DS . 'dir' . DS;
		$result = $folder->create($path);
		$this->assertTrue($result);

		$this->assertTrue(is_dir($path), 'Folder was not made');

		$folder = new Folder(TMP . 'tests' . DS . 'trailing');
		$this->assertTrue($folder->delete());
	}

/**
 * test recurisve directory create failure.
 *
 * @return void
 */
	public function testRecursiveCreateFailure() {
		$this->skipIf(DIRECTORY_SEPARATOR === '\\', 'Cant perform operations using permissions on windows.');

		$path = TMP . 'tests' . DS . 'one';
		mkdir($path);
		chmod($path, '0444');

		try {
			$folder = new Folder($path);
			$result = $folder->create($path . DS . 'two' . DS . 'three');
			$this->assertFalse($result);
		} catch (PHPUnit_Framework_Error $e) {
			$this->assertTrue(true);
		}

		chmod($path, '0777');
		rmdir($path);
	}
/**
 * testOperations method
 *
 * @access public
 * @return void
 */
	public function testOperations() {
		$path = CAKE . 'Console' . DS . 'Templates' . DS . 'skel';
		$Folder = new Folder($path);

		$result = is_dir($Folder->pwd());
		$this->assertTrue($result);

		$new = TMP . 'test_folder_new';
		$result = $Folder->create($new);
		$this->assertTrue($result);

		$copy = TMP . 'test_folder_copy';
		$result = $Folder->copy($copy);
		$this->assertTrue($result);

		$copy = TMP . 'test_folder_copy';
		$result = $Folder->copy($copy);
		$this->assertTrue($result);

		$copy = TMP . 'test_folder_copy';
		$result = $Folder->chmod($copy, 0755, false);
		$this->assertTrue($result);

		$result = $Folder->cd($copy);
		$this->assertTrue((bool)$result);

		$mv = TMP . 'test_folder_mv';
		$result = $Folder->move($mv);
		$this->assertTrue($result);

		$mv = TMP . 'test_folder_mv_2';
		$result = $Folder->move($mv);
		$this->assertTrue($result);

		$result = $Folder->delete($new);
		$this->assertTrue($result);

		$result = $Folder->delete($mv);
		$this->assertTrue($result);

		$result = $Folder->delete($mv);
		$this->assertTrue($result);

		$new = APP . 'index.php';
		$result = $Folder->create($new);
		$this->assertFalse($result);

		$expected = $new . ' is a file';
		$result = $Folder->errors();
		$this->assertEqual($result[0], $expected);

		$new = TMP . 'test_folder_new';
		$result = $Folder->create($new);
		$this->assertTrue($result);

		$result = $Folder->cd($new);
		$this->assertTrue((bool)$result);

		$result = $Folder->delete();
		$this->assertTrue($result);

		$Folder = new Folder('non-existent');
		$result = $Folder->pwd();
		$this->assertNull($result);
	}

/**
 * testChmod method
 *
 * @return void
 */
	public function testChmod() {
		$this->skipIf(DIRECTORY_SEPARATOR === '\\', 'Folder permissions tests not supported on Windows.');

		$path = CAKE . 'Console' . DS . 'Templates' . DS . 'skel';
		$Folder = new Folder($path);

		$subdir = 'test_folder_new';
		$new = TMP . $subdir;

		$this->assertTrue($Folder->create($new));
		$this->assertTrue($Folder->create($new . DS . 'test1'));
		$this->assertTrue($Folder->create($new . DS . 'test2'));

		$filePath = $new . DS . 'test1.php';
		$File = new File($filePath);
		$this->assertTrue($File->create());
		$copy = TMP . 'test_folder_copy';

		$this->assertTrue($Folder->chmod($new, 0777, true));
		$this->assertEqual($File->perms(), '0777');

		$Folder->delete($new);
	}

/**
 * testRealPathForWebroot method
 *
 * @access public
 * @return void
 */
	public function testRealPathForWebroot() {
		$Folder = new Folder('files/');
		$this->assertEqual(realpath('files/'), $Folder->path);
	}

/**
 * testZeroAsDirectory method
 *
 * @access public
 * @return void
 */
	public function testZeroAsDirectory() {
		$Folder = new Folder(TMP);
		$new = TMP . '0';
		$this->assertTrue($Folder->create($new));

		$result = $Folder->read(true, true);
		$expected = array('0', 'cache', 'logs', 'sessions', 'tests');
		$this->assertEqual($expected, $result[0]);

		$result = $Folder->read(true, array('.', '..', 'logs', '.svn'));
		$expected = array('0', 'cache', 'sessions', 'tests');
		$this->assertEqual($expected, $result[0]);

		$result = $Folder->delete($new);
		$this->assertTrue($result);
	}

/**
 * test Adding path elements to a path
 *
 * @return void
 */
	public function testAddPathElement() {
		$result = Folder::addPathElement(DS . 'some' . DS . 'dir', 'another_path');
		$this->assertEqual($result, DS . 'some' . DS . 'dir' . DS . 'another_path');

		$result = Folder::addPathElement(DS . 'some' . DS . 'dir' . DS, 'another_path');
		$this->assertEqual($result, DS . 'some' . DS . 'dir' . DS . 'another_path');
	}
/**
 * testFolderRead method
 *
 * @access public
 * @return void
 */
	public function testFolderRead() {
		$Folder = new Folder(TMP);

		$expected = array('cache', 'logs', 'sessions', 'tests');
		$result = $Folder->read(true, true);
		$this->assertEqual($result[0], $expected);

		$Folder->path = TMP . 'non-existent';
		$expected = array(array(), array());
		$result = $Folder->read(true, true);
		$this->assertEqual($expected, $result);
	}

/**
 * testFolderTree method
 *
 * @access public
 * @return void
 */
	public function testFolderTree() {
		$Folder = new Folder();
		$expected = array(
			array(
				CAKE . 'Config',
				CAKE . 'Config' . DS . 'unicode',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding'
			),
			array(
				CAKE . 'Config' . DS . 'config.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0080_00ff.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0100_017f.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0180_024F.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0250_02af.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0370_03ff.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0400_04ff.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0500_052f.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '0530_058f.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '1e00_1eff.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '1f00_1fff.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2100_214f.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2150_218f.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2460_24ff.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2c00_2c5f.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2c60_2c7f.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . '2c80_2cff.php',
				CAKE . 'Config' . DS . 'unicode' .  DS . 'casefolding' . DS . 'ff00_ffef.php'
			)
		);

		$result = $Folder->tree(CAKE . 'Config', false);
		$this->assertSame(array_diff($expected[0], $result[0]), array());
		$this->assertSame(array_diff($result[0], $expected[0]), array());

		$result = $Folder->tree(CAKE . 'Config', false, 'dir');
		$this->assertSame(array_diff($expected[0], $result), array());
		$this->assertSame(array_diff($expected[0], $result), array());

		$result = $Folder->tree(CAKE . 'Config', false, 'files');
		$this->assertSame(array_diff($expected[1], $result), array());
		$this->assertSame(array_diff($expected[1], $result), array());
	}

/**
 * testWindowsPath method
 *
 * @access public
 * @return void
 */
	public function testWindowsPath() {
		$this->assertFalse(Folder::isWindowsPath('0:\\cake\\is\\awesome'));
		$this->assertTrue(Folder::isWindowsPath('C:\\cake\\is\\awesome'));
		$this->assertTrue(Folder::isWindowsPath('d:\\cake\\is\\awesome'));
		$this->assertTrue(Folder::isWindowsPath('\\\\vmware-host\\Shared Folders\\file'));
	}

/**
 * testIsAbsolute method
 *
 * @access public
 * @return void
 */
	public function testIsAbsolute() {
		$this->assertFalse(Folder::isAbsolute('path/to/file'));
		$this->assertFalse(Folder::isAbsolute('cake/'));
		$this->assertFalse(Folder::isAbsolute('path\\to\\file'));
		$this->assertFalse(Folder::isAbsolute('0:\\path\\to\\file'));
		$this->assertFalse(Folder::isAbsolute('\\path/to/file'));
		$this->assertFalse(Folder::isAbsolute('\\path\\to\\file'));

		$this->assertTrue(Folder::isAbsolute('/usr/local'));
		$this->assertTrue(Folder::isAbsolute('//path/to/file'));
		$this->assertTrue(Folder::isAbsolute('C:\\cake'));
		$this->assertTrue(Folder::isAbsolute('C:\\path\\to\\file'));
		$this->assertTrue(Folder::isAbsolute('d:\\path\\to\\file'));
		$this->assertTrue(Folder::isAbsolute('\\\\vmware-host\\Shared Folders\\file'));
	}

/**
 * testIsSlashTerm method
 *
 * @access public
 * @return void
 */
	public function testIsSlashTerm() {
		$this->assertFalse(Folder::isSlashTerm('cake'));

		$this->assertTrue(Folder::isSlashTerm('C:\\cake\\'));
		$this->assertTrue(Folder::isSlashTerm('/usr/local/'));
	}

/**
 * testStatic method
 *
 * @access public
 * @return void
 */
	public function testSlashTerm() {
		$result = Folder::slashTerm('/path/to/file');
		$this->assertEqual($result, '/path/to/file/');
	}

/**
 * testNormalizePath method
 *
 * @access public
 * @return void
 */
	public function testNormalizePath() {
		$path = '/path/to/file';
		$result = Folder::normalizePath($path);
		$this->assertEqual($result, '/');

		$path = '\\path\\\to\\\file';
		$result = Folder::normalizePath($path);
		$this->assertEqual($result, '/');

		$path = 'C:\\path\\to\\file';
		$result = Folder::normalizePath($path);
		$this->assertEqual($result, '\\');
	}

/**
 * correctSlashFor method
 *
 * @access public
 * @return void
 */
	public function testCorrectSlashFor() {
		$path = '/path/to/file';
		$result = Folder::correctSlashFor($path);
		$this->assertEqual($result, '/');

		$path = '\\path\\to\\file';
		$result = Folder::correctSlashFor($path);
		$this->assertEqual($result, '/');

		$path = 'C:\\path\to\\file';
		$result = Folder::correctSlashFor($path);
		$this->assertEqual($result, '\\');
	}

/**
 * testInCakePath method
 *
 * @access public
 * @return void
 */
	public function testInCakePath() {
		$Folder = new Folder();
		$Folder->cd(ROOT);
		$path = 'C:\\path\\to\\file';
		$result = $Folder->inCakePath($path);
		$this->assertFalse($result);

		$path = ROOT;
		$Folder->cd(ROOT);
		$result = $Folder->inCakePath($path);
		$this->assertFalse($result);

		$path = DS . 'lib' . DS . 'Cake' . DS . 'Config';
		$Folder->cd(ROOT . DS . 'lib' . DS . 'Cake' . DS . 'Config');
		$result = $Folder->inCakePath($path);
		$this->assertTrue($result);
	}

/**
 * testFind method
 *
 * @access public
 * @return void
 */
	public function testFind() {
		$Folder = new Folder();
		$Folder->cd(CAKE . 'Config');
		$result = $Folder->find();
		$expected = array('config.php');
		$this->assertSame(array_diff($expected, $result), array());
		$this->assertSame(array_diff($expected, $result), array());

		$result = $Folder->find('.*', true);
		$expected = array('config.php', 'routes.php');
		$this->assertSame($expected, $result);

		$result = $Folder->find('.*\.php');
		$expected = array('config.php');
		$this->assertSame(array_diff($expected, $result), array());
		$this->assertSame(array_diff($expected, $result), array());

		$result = $Folder->find('.*\.php', true);
		$expected = array('config.php', 'routes.php');
		$this->assertSame($expected, $result);

		$result = $Folder->find('.*ig\.php');
		$expected = array('config.php');
		$this->assertSame($expected, $result);

		$result = $Folder->find('config\.php');
		$expected = array('config.php');
		$this->assertSame($expected, $result);

		$Folder->cd(TMP);
		$file = new File($Folder->pwd() . DS . 'paths.php', true);
		$Folder->create($Folder->pwd() . DS . 'testme');
		$Folder->cd('testme');
		$result = $Folder->find('paths\.php');
		$expected = array();
		$this->assertSame($expected, $result);

		$Folder->cd($Folder->pwd() . '/..');
		$result = $Folder->find('paths\.php');
		$expected = array('paths.php');
		$this->assertSame($expected, $result);

		$Folder->cd(TMP);
		$Folder->delete($Folder->pwd() . DS . 'testme');
		$file->delete();
	}

/**
 * testFindRecursive method
 *
 * @access public
 * @return void
 */
	public function testFindRecursive() {
		$Folder = new Folder();
		$Folder->cd(CAKE);
		$result = $Folder->findRecursive('(config|paths)\.php');
		$expected = array(
			CAKE . 'Config' . DS . 'config.php'
		);
		$this->assertSame(array_diff($expected, $result), array());
		$this->assertSame(array_diff($expected, $result), array());

		$result = $Folder->findRecursive('(config|paths)\.php', true);
		$expected = array(
			CAKE . 'Config' . DS . 'config.php'
		);
		$this->assertSame($expected, $result);

		$Folder->cd(TMP);
		$Folder->create($Folder->pwd() . DS . 'testme');
		$Folder->cd('testme');
		$File = new File($Folder->pwd() . DS . 'paths.php');
		$File->create();
		$Folder->cd(TMP . 'sessions');
		$result = $Folder->findRecursive('paths\.php');
		$expected = array();
		$this->assertSame($expected, $result);

		$Folder->cd(TMP . 'testme');
		$File = new File($Folder->pwd() . DS . 'my.php');
		$File->create();
		$Folder->cd($Folder->pwd() . '/../..');

		$result = $Folder->findRecursive('(paths|my)\.php');
		$expected = array(
			TMP . 'testme' . DS . 'my.php',
			TMP . 'testme' . DS . 'paths.php'
		);
		$this->assertSame(array_diff($expected, $result), array());
		$this->assertSame(array_diff($expected, $result), array());

		$result = $Folder->findRecursive('(paths|my)\.php', true);
		$expected = array(
			TMP . 'testme' . DS . 'my.php',
			TMP . 'testme' . DS . 'paths.php'
		);
		$this->assertSame($expected, $result);

		$Folder->cd(CAKE . 'Config');
		$Folder->cd(TMP);
		$Folder->delete($Folder->pwd() . DS . 'testme');
		$File->delete();
	}

/**
 * testConstructWithNonExistantPath method
 *
 * @access public
 * @return void
 */
	public function testConstructWithNonExistantPath() {
		$Folder = new Folder(TMP . 'config_non_existant', true);
		$this->assertTrue(is_dir(TMP . 'config_non_existant'));
		$Folder->cd(TMP);
		$Folder->delete($Folder->pwd() . 'config_non_existant');
	}

/**
 * testDirSize method
 *
 * @access public
 * @return void
 */
	public function testDirSize() {
		$Folder = new Folder(TMP . 'config_non_existant', true);
		$this->assertEqual($Folder->dirSize(), 0);

		$File = new File($Folder->pwd() . DS . 'my.php', true, 0777);
		$File->create();
		$File->write('something here');
		$File->close();
		$this->assertEqual($Folder->dirSize(), 14);

		$Folder->cd(TMP);
		$Folder->delete($Folder->pwd() . 'config_non_existant');
	}

/**
 * testDelete method
 *
 * @access public
 * @return void
 */
	public function testDelete() {
		$path = TMP . 'folder_delete_test';
		$Folder = new Folder($path, true);
		touch(TMP . 'folder_delete_test' . DS . 'file1');
		touch(TMP . 'folder_delete_test' . DS . 'file2');

		$return = $Folder->delete();
		$this->assertTrue($return);

		$messages = $Folder->messages();
		$errors = $Folder->errors();
		$this->assertEquals($errors, array());

		$expected = array(
			$path . ' created',
			$path . DS . 'file1 removed',
			$path . DS . 'file2 removed',
			$path . ' removed'
		);
		$this->assertEqual($expected, $messages);
	}

/**
 * testCopy method
 *
 * Verify that directories and files are copied recursively
 * even if the destination directory already exists.
 * Subdirectories existing in both destination and source directory
 * are skipped and not merged or overwritten.
 *
 * @return void
 * @access public
 */
	public function testCopy() {
		$path = TMP . 'folder_test';
		$folder1 = $path . DS . 'folder1';
		$folder2 = $folder1 . DS . 'folder2';
		$folder3 = $path . DS . 'folder3';
		$file1 = $folder1 . DS . 'file1.php';
		$file2 = $folder2 . DS . 'file2.php';

		new Folder($path, true);
		new Folder($folder1, true);
		new Folder($folder2, true);
		new Folder($folder3, true);
		touch($file1);
		touch($file2);

		$Folder = new Folder($folder1);
		$result = $Folder->copy($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));

		$Folder = new Folder($folder3);
		$Folder->delete();

		$Folder = new Folder($folder1);
		$result = $Folder->copy($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));

		$Folder = new Folder($folder3);
		$Folder->delete();

		new Folder($folder3, true);
		new Folder($folder3 . DS . 'folder2', true);
		file_put_contents($folder3 . DS . 'folder2' . DS . 'file2.php', 'untouched');

		$Folder = new Folder($folder1);
		$result = $Folder->copy($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertEqual(file_get_contents($folder3 . DS . 'folder2' . DS . 'file2.php'), 'untouched');

		$Folder = new Folder($path);
		$Folder->delete();
	}

/**
 * testMove method
 *
 * Verify that directories and files are moved recursively
 * even if the destination directory already exists.
 * Subdirectories existing in both destination and source directory
 * are skipped and not merged or overwritten.
 *
 * @return void
 * @access public
 */
	public function testMove() {
		$path = TMP . 'folder_test';
		$folder1 = $path . DS . 'folder1';
		$folder2 = $folder1 . DS . 'folder2';
		$folder3 = $path . DS . 'folder3';
		$file1 = $folder1 . DS . 'file1.php';
		$file2 = $folder2 . DS . 'file2.php';

		new Folder($path, true);
		new Folder($folder1, true);
		new Folder($folder2, true);
		new Folder($folder3, true);
		touch($file1);
		touch($file2);

		$Folder = new Folder($folder1);
		$result = $Folder->move($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(is_dir($folder3 . DS . 'folder2'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));
		$this->assertFalse(file_exists($file1));
		$this->assertFalse(file_exists($folder2));
		$this->assertFalse(file_exists($file2));

		$Folder = new Folder($folder3);
		$Folder->delete();

		new Folder($folder1, true);
		new Folder($folder2, true);
		touch($file1);
		touch($file2);

		$Folder = new Folder($folder1);
		$result = $Folder->move($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertTrue(is_dir($folder3 . DS . 'folder2'));
		$this->assertTrue(file_exists($folder3 . DS . 'folder2' . DS . 'file2.php'));
		$this->assertFalse(file_exists($file1));
		$this->assertFalse(file_exists($folder2));
		$this->assertFalse(file_exists($file2));

		$Folder = new Folder($folder3);
		$Folder->delete();

		new Folder($folder1, true);
		new Folder($folder2, true);
		new Folder($folder3, true);
		new Folder($folder3 . DS . 'folder2', true);
		touch($file1);
		touch($file2);
		file_put_contents($folder3 . DS . 'folder2' . DS . 'file2.php', 'untouched');

		$Folder = new Folder($folder1);
		$result = $Folder->move($folder3);
		$this->assertTrue($result);
		$this->assertTrue(file_exists($folder3 . DS . 'file1.php'));
		$this->assertEqual(file_get_contents($folder3 . DS . 'folder2' . DS . 'file2.php'), 'untouched');
		$this->assertFalse(file_exists($file1));
		$this->assertFalse(file_exists($folder2));
		$this->assertFalse(file_exists($file2));

		$Folder = new Folder($path);
		$Folder->delete();
	}
}
