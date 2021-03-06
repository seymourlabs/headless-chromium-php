<?php
/**
 * @license see LICENSE
 */

namespace HeadlessChromium\Test;

use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Communication\Session;
use HeadlessChromium\Communication\Socket\MockSocket;
use HeadlessChromium\Page;

/**
 * @covers \HeadlessChromium\Page
 */
class PageTest extends BaseTestCase
{
    
    public function testPage()
    {
        $connection = new Connection(new MockSocket());
        $session = new Session('foo', 'bar', $connection);
        $page = new Page($session);

        $this->assertSame($session, $page->getSession());
    }
    
    public function testNavigate()
    {
        $mockSocket = new MockSocket();
        $connection = new Connection($mockSocket);
        $connection->connect();
        $session = new Session('foo', 'bar', $connection);

        $page = new Page($session);

        $mockSocket->addReceivedData(json_encode([]), true);

        $page->navigate('http://foo.bar');

        $this->assertDataSentEquals(
            [
                $this->sendMessageToTargetArray('bar', [
                    'id' => '%id',
                    'method' => 'Page.navigate',
                    'params' => ['url' => 'http://foo.bar']
                ])
            ],
            $mockSocket->getSentData()
        );
    }
}
