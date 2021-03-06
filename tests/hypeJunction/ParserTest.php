<?php

namespace hypeJunction;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit_Framework_TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-02-07 at 05:31:41.
 */
class ParserTest extends PHPUnit_Framework_TestCase {

	/**
	 * @var Parser
	 */
	protected $object;

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->url = 'http://foo.bar.com/';
		$test_files = dirname(dirname(__FILE__)) . '/test-files/';
		$this->html = file_get_contents($test_files . '/ogp.html');
		$this->xml = file_get_contents($test_files . '/oembed.xml');
		$this->json = file_get_contents($test_files . '/oembed.json');
		$this->jpeg = '';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {

	}

	protected function mock($type = '') {

		switch ($type) {
			default :
				$response = new Response(404);
				break;
			case 'html' :
				$response = new Response(200, [
					'Content-Type' => 'text/html; charset=UTF-8',
					'Content-Length' => strlen($this->html),
						], $this->html);
				break;
			case 'xml' :
				$response = new Response(200, [
					'Content-Type' => 'application/xml+oembed; charset=UTF-8',
					'Content-Length' => strlen($this->xml),
						], $this->xml);
				break;
			case 'json' :
				$response = new Response(200, [
					'Content-Type' => 'application/json+oembed; charset=UTF-8',
					'Content-Length' => strlen($this->json),
						], $this->json);
				break;
			case 'jpeg' :
				$response = new Response(200, [
					'Content-Type' => 'image/jpeg',
					'Content-Length' => strlen($this->jpeg),
						], $this->jpeg);
				break;
		}
		
		$handler = new MockHandler([$response]);
		$client = new Client(['handler' => $handler]);
		return new Parser($client);
	}

	protected function url() {
		return $this->url . mt_rand(10000, 5000000);
	}

	/**
	 * @covers hypeJunction\Parser::parse
	 */
	public function testConstructor() {
		$this->assertNotNull($this->mock());
	}

	/**
	 * @covers hypeJunction\Parser::parse
	 */
	public function testParse() {
		$this->assertInternalType('array', $this->mock()->parse($this->url()));
		$this->assertInternalType('array', $this->mock('html')->parse($this->url()));
		$this->assertInternalType('array', $this->mock('xml')->parse($this->url()));
		$this->assertInternalType('array', $this->mock('json')->parse($this->url()));
		$this->assertInternalType('array', $this->mock('jpeg')->parse($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::getImageData
	 */
	public function testGetImageData() {
		$data = $this->mock('jpeg')->getImageData($this->url());
		$this->assertEquals('photo', $data['type']);
		$this->assertNotEmpty($data['thumbnails']);

		$this->assertFalse($this->mock()->getImageData($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::getOEmbedData
	 */
	public function testGetOEmbedDataXML() {
		$data = $this->mock('xml')->getOEmbedData($this->url());
		$this->assertEquals('link', $data['type']);

		$this->assertFalse($this->mock()->getOEmbedData($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::getOEmbedData
	 */
	public function testGetOEmbedDataJSON() {
		$data = $this->mock('json')->getOEmbedData($this->url());
		$this->assertEquals('photo', $data['type']);
	}

	/**
	 * @covers hypeJunction\Parser::getDOMData
	 */
	public function testGetDOMData() {
		$data = $this->mock('html')->getDOMData($this->url());
		$this->assertEquals('website', $data['type']);

		$this->assertFalse($this->mock()->getDOMData($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::exists
	 */
	public function testExists() {
		$this->assertFalse($this->mock()->exists($this->url()));
		$this->assertTrue($this->mock('html')->exists($this->url()));

		$this->assertFalse($this->mock('html')->request('invalid.url'));
	}

	/**
	 * @covers hypeJunction\Parser::request
	 */
	public function testRequest() {
		$this->assertInstanceOf(Response::class, $this->mock()->request($this->url()));
		$this->assertInstanceOf(Response::class, $this->mock('html')->request($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::read
	 */
	public function testRead() {
		$this->assertEquals('', $this->mock()->read($this->url()));
		$this->assertEquals($this->html, $this->mock('html')->read($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::isHTML
	 */
	public function testIsHTML() {
		$this->assertFalse($this->mock()->isHTML($this->url()));
		$this->assertTrue($this->mock('html')->isHTML($this->url()));
		$this->assertFalse($this->mock('xml')->isHTML($this->url()));
		$this->assertFalse($this->mock('json')->isHTML($this->url()));
		$this->assertFalse($this->mock('jpeg')->isHTML($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::isJSON
	 */
	public function testIsJSON() {
		$this->assertFalse($this->mock()->isJSON($this->url()));
		$this->assertFalse($this->mock('html')->isJSON($this->url()));
		$this->assertFalse($this->mock('xml')->isJSON($this->url()));
		$this->assertTrue($this->mock('json')->isJSON($this->url()));
		$this->assertFalse($this->mock('jpeg')->isJSON($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::isXML
	 */
	public function testIsXML() {
		$this->assertFalse($this->mock()->isXML($this->url()));
		$this->assertFalse($this->mock('html')->isXML($this->url()));
		$this->assertTrue($this->mock('xml')->isXML($this->url()));
		$this->assertFalse($this->mock('json')->isXML($this->url()));
		$this->assertFalse($this->mock('jpeg')->isXML($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::isImage
	 */
	public function testIsImage() {
		$this->assertFalse($this->mock()->isImage($this->url()));
		$this->assertFalse($this->mock('html')->isImage($this->url()));
		$this->assertFalse($this->mock('xml')->isImage($this->url()));
		$this->assertFalse($this->mock('json')->isImage($this->url()));
		$this->assertTrue($this->mock('jpeg')->isImage($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::getContentType
	 */
	public function testGetContentType() {
		$this->assertEquals('', $this->mock()->getContentType($this->url()));
		$this->assertEquals('text/html', $this->mock('html')->getContentType($this->url()));
		$this->assertEquals('application/xml+oembed', $this->mock('xml')->getContentType($this->url()));
		$this->assertEquals('application/json+oembed', $this->mock('json')->getContentType($this->url()));
		$this->assertEquals('image/jpeg', $this->mock('jpeg')->getContentType($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::getHTML
	 */
	public function testGetHTML() {
		$this->assertEquals('', $this->mock()->getHTML($this->url()));
		$this->assertEquals($this->html, $this->mock('html')->getHTML($this->url()));
		$this->assertEquals('', $this->mock('xml')->getHTML($this->url()));
		$this->assertEquals('', $this->mock('json')->getHTML($this->url()));
	}

	/**
	 * @covers hypeJunction\Parser::getDOM
	 */
	public function testGetDOM() {
		$this->assertFalse($this->mock()->getDOM($this->url()));

		$url = $this->url();
		$dom = $this->mock('html')->getDOM($url);
		$this->assertInstanceOf(\DOMDocument::class, $dom);
		$this->assertEquals($url, $dom->documentURI);
	}

	/**
	 * @covers hypeJunction\Parser::parseTitle
	 */
	public function testParseTitle() {
		$mock = $this->mock('html');
		$doc = $mock->getDOM($this->url());
		$this->assertEquals('Structured video array', $mock->parseTitle($doc));
	}

	/**
	 * @covers hypeJunction\Parser::parseLinkTags
	 * @covers hypeJunction\Parser::getAbsoluteURL
	 */
	public function testParseLinkTags() {
		$base_url = $this->url();
		$mock = $this->mock('html');
		$doc = $mock->getDOM($base_url);
		$meta = $mock->parseLinkTags($doc);

		$this->assertContains("{$this->url}path/to/icon.ico", $meta['icons']);
		$this->assertContains("{$base_url}/path/to/icon2.ico", $meta['icons']);
		$this->assertContains("http://examples.opengraphprotocol.us/video-array.xml", $meta['oembed_url']);
		$this->assertEquals("http://examples.opengraphprotocol.us/video-array.html", $meta['canonical']);
	}

	/**
	 * @covers hypeJunction\Parser::parseMetaTags
	 */
	public function testParseMetaTags() {
		$base_url = $this->url();
		$mock = $this->mock('html');
		$doc = $mock->getDOM($base_url);
		$meta = $mock->parseMetaTags($doc);
		$this->assertEquals($meta['description'], $meta['metatags']['description']);
		$this->assertInternalType('array', $meta['metatags']['og:video']);
		$this->assertEquals($meta['tags'], ['tag 1', 'tag 2']);
	}

	/**
	 * @covers hypeJunction\Parser::parseImgTags
	 */
	public function testParseImgTags() {
		$base_url = $this->url();
		$mock = $this->mock('html');
		$doc = $mock->getDOM($base_url);
		$meta = $mock->parseImgTags($doc);
		$this->assertContains("http://examples.opengraphprotocol.us/media/images/50.png", $meta['thumbnails']);
	}

}
