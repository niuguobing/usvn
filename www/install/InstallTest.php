<?php
/**
 * Installation operations
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.5
 * @package install
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */

// Call InstallTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "InstallTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'www/install/Install.php';
require_once 'www/USVN/autoload.php';

/**
 * Test class for Install.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-20 at 09:07:00.
 */
class InstallTest extends USVN_Test_Test {
	private $db;

	/**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main() {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("InstallTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    public function setUp() {
		parent::setUp();
		$_SERVER['SERVER_NAME'] = "localhost";
		$_SERVER['HTTP_HOST'] = "localhost";
		$_SERVER['SERVER_PORT'] = 80;
		$_SERVER['REQUEST_URI'] = "/test/install/index.php?step=7";
    }

	public function testInstallLanguage()
	{
		Install::installLanguage("tests/tmp/config.ini", "fr_FR");
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("fr_FR", $config->translation->locale);
	}

	public function testInstallSubversion()
	{
		Install::installSubversion("tests/tmp/config.ini", "tests", "http://test.com");
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$this->assertTrue(file_exists("tests/authz"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("tests" . DIRECTORY_SEPARATOR, $config->subversion->path);
		$this->assertEquals("http://test.com/", $config->subversion->url);
	}

	public function testInstallSubversionPathDoesntExist()
	{
		try	{
		Install::installSubversion("tests/tmp/config.ini", "test2", 'http://test.com');
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallSubversionMagicQuoteWindows()
	{
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            mkdir('tests/tmp2');
            Install::installSubversion("tests/tmp2/config.ini", 'tests\\\\tmp2', 'http://test.com');
            $this->assertTrue(file_exists("tests/tmp2/config.ini"));
            $config = new Zend_Config_Ini("tests/tmp2/config.ini", "general");
            $this->assertEquals("tests\\tmp2\\", $config->subversion->path);
        }
	}

	public function testInstallBadLanguage()
	{
		try {
			Install::installLanguage("tests/tmp/config.ini", "fake");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallUrl()
	{
		Install::installUrl("tests/tmp/config.ini", "tests/tmp/.htaccess");
		$this->assertTrue(file_exists("tests/tmp/config.ini"));
		$this->assertTrue(file_exists("tests/tmp/.htaccess"));
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("/test", $config->url->base);
		$this->assertEquals("localhost", $config->url->host);
		$htaccess = file_get_contents("tests/tmp/.htaccess");
		$this->assertContains("RewriteBase /test", $htaccess);
	}

	public function testInstallUrlRoot()
	{
		$_SERVER['REQUEST_URI'] = "/install/index.php?step=7";
		Install::installUrl("tests/tmp/config.ini", "tests/tmp/.htaccess");
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("", $config->url->base);
		$this->assertEquals("localhost", $config->url->host);
		$htaccess = file_get_contents("tests/tmp/.htaccess");
		$this->assertContains("RewriteBase /", $htaccess);
	}

	public function testInstallUrlCantWriteHtaccess()
	{
		try {
			Install::installUrl("tests/tmp/config.ini", "tests/fake/.htaccess");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallUrlCantWriteConfig()
	{
		try {
			Install::installUrl("tests/fake/config.ini", "tests/tmp/.htaccess");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testInstallEnd()
	{
		Install::installEnd("tests/tmp/config.ini");
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("0.6.3", $config->version);
	}

	public function testInstallPossibleNoConfigFile()
	{
		$this->assertTrue(Install::installPossible("tests/tmp/config.ini"));
	}

	public function testInstallPossibleInstallNotEnd()
	{
		Install::installLanguage("tests/tmp/config.ini", "fr_FR");
		$this->assertTrue(Install::installPossible("tests/tmp/config.ini"));
	}

	public function testInstallNotPossible()
	{
		Install::installEnd("tests/tmp/config.ini");
		$this->assertFalse(Install::installPossible("tests/tmp/config.ini"));
	}

	public function testInstallConfiguration()
	{
		Install::installConfiguration("tests/tmp/config.ini", "Noplay");
		$config = new Zend_Config_Ini("tests/tmp/config.ini", "general");
		$this->assertEquals("Noplay", $config->site->title);
		$this->assertEquals("default", $config->template->name);
		$this->assertEquals("medias/default/images/USVN.ico", $config->site->ico);
		$this->assertEquals("medias/default/images/USVN-logo.png", $config->site->logo);
	}

	public function testInstallConfigurationNotTitle()
	{
		try {
			Install::installConfiguration("tests/tmp/config.ini", "");
		}
		catch (USVN_Exception $e) {
			return;
		}
		$this->fail();
	}

	public function testGetApacheConfig()
	{
		file_put_contents("tests/tmp/config.ini", "[general]
subversion.path=tests" .DIRECTORY_SEPARATOR . "tmp
subversion.url=http://exemple/dev/usvn/
site.title=USVN
		");
		$this->assertEquals(
"<Location /dev/usvn/>
	DAV svn
	Require valid-user
	SVNParentPath tests" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "svn
	SVNListParentPath off
	AuthType Basic
	AuthName \"USVN\"
	AuthUserFile tests" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "htpasswd
	AuthzSVNAccessFile tests" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR . "authz
</Location>",
		Install::getApacheConfig("tests/tmp/config.ini"));
	}

	public function testcheckSystem()
	{
		Install::checkSystem();
	}

	public function testcheckSystemSubversionNotInstall()
	{
		$PATH = getenv('PATH');
		try {
			putenv('PATH=');
			Install::checkSystem();
		}
		catch (USVN_Exception $e) {
			putenv("PATH=$PATH");
			return;
		}
		putenv("PATH=$PATH");
		$this->fail();
	}
}

// Call InstallTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "InstallTest::main") {
    InstallTest::main();
}
?>
