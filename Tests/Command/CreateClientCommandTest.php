<?php

declare(strict_types=1);

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Command;

use FOS\OAuthServerBundle\Command\CreateClientCommand;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use FOS\OAuthServerBundle\Tests\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use FOS\OAuthServerBundle\Document\Client;

class CreateClientCommandTest extends TestCase
{
    /**
     * @var CreateClientCommand
     */
    private $command;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ClientManagerInterface
     */
    private $clientManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->clientManager = $this->getMockBuilder(ClientManagerInterface::class)->disableOriginalConstructor()->getMock();
        $command = new CreateClientCommand($this->clientManager);

        $application = new Application();
        $application->add($command);

        /** @var CreateClientCommand $command */
        $command = $application->find($command->getName());

        $this->command = $command;
    }

    /**
     * @dataProvider clientProvider
     *
     * @param string $client a fully qualified class name
     */
    public function testItShouldCreateClient($client)
    {
        $this
            ->clientManager
            ->method('createClient')
            ->willReturn(new $client())
        ;

        $commandTester = new CommandTester($this->command);

        $commandTester->execute([
            'command' => $this->command->getName(),
            '--redirect-uri' => ['https://www.example.com/oauth2/callback'],
            '--grant-type' => [
                'authorization_code',
                'password',
                'refresh_token',
                'token',
                'client_credentials',
            ],
        ]);

        $this->assertSame(0, $commandTester->getStatusCode());

        $output = $commandTester->getDisplay();

        $this->assertContains('Client ID', $output);
        $this->assertContains('Client Secret', $output);
    }

    /**
     * @return array
     */
    public function clientProvider(): array
    {
        return [
            [Client::class],
            [\FOS\OAuthServerBundle\Entity\Client::class],
            [\FOS\OAuthServerBundle\Model\Client::class],
            [\FOS\OAuthServerBundle\Propel\Client::class],
        ];
    }
}
