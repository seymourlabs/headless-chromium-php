<?php
/**
 * @license see LICENSE
 */

namespace HeadlessChromium\Test\Communication;

use HeadlessChromium\Communication\Connection;
use HeadlessChromium\Communication\Message;
use HeadlessChromium\Communication\Response;
use HeadlessChromium\Communication\ResponseReader;
use HeadlessChromium\Communication\Session;
use HeadlessChromium\Communication\Socket\MockSocket;
use PHPUnit\Framework\TestCase;

/**
 * @covers \HeadlessChromium\Communication\Session
 */
class SessionTest extends TestCase
{

    /**
     * @var MockSocket
     */
    protected $mockSocket;

    public function setUp()
    {
        parent::setUp();
        $this->mockSocket = new MockSocket();
    }

    public function testSession()
    {
        $connection = new Connection($this->mockSocket);
        $session = new Session('foo', 'bar', $connection);

        $this->assertEquals('foo', $session->getTargetId());
        $this->assertEquals('bar', $session->getSessionId());
        $this->assertSame($connection, $session->getConnection());
    }

    public function testSendMessage()
    {
        $connection = new Connection($this->mockSocket);
        $connection->connect();
        $session = new Session('foo', 'bar', $connection);

        $message = new Message('baz', ['qux' => 'quux']);

        $responseReader = $session->sendMessage($message);

        $this->assertInstanceOf(ResponseReader::class, $responseReader);
        $this->assertEquals(
            [
                json_encode([
                    'id' => $responseReader->getMessage()->getId(),
                    'method' => 'Target.sendMessageToTarget',
                    'params' => [
                        'message' => json_encode([
                            'id' => $message->getId(),
                            'method' => 'baz',
                            'params' => ['qux' => 'quux']
                        ]),
                        'sessionId' => 'bar'
                    ]

                ])
            ],
            $this->mockSocket->getSentData()
        );
    }

    public function testSendMessageSync()
    {
        $connection = new Connection($this->mockSocket);
        $connection->connect();
        $session = new Session('foo', 'bar', $connection);

        $message = new Message('baz', ['qux' => 'quux']);

        $this->mockSocket->addReceivedData(json_encode(['corge' => 'grault']), true);

        $response = $session->sendMessageSync($message);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(
            [
                json_encode([
                    'id' => $response->getMessage()->getId(),
                    'method' => 'Target.sendMessageToTarget',
                    'params' => [
                        'message' => json_encode([
                            'id' => $message->getId(),
                            'method' => 'baz',
                            'params' => ['qux' => 'quux']
                        ]),
                        'sessionId' => 'bar'
                    ]

                ])
            ],
            $this->mockSocket->getSentData()
        );
    }
}
