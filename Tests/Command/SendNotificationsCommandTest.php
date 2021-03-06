<?php
namespace Azine\EmailBundle\Tests\Command;

use Azine\EmailBundle\Command\SendNotificationsCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Application;

/**
 * @author dominik
 */
class SendNotificationsCommandTest extends \PHPUnit_Framework_TestCase
{
    const MAILS_SENT = 10;

    public function testHelpInfo()
    {
        $command = $this->getCommand();
    	$display = $command->getHelp();
        $this->assertContains("Depending on you Swiftmailer-Configuration the email will be send directly or will be written to the spool.", $display);

    }

    public function testSend()
    {
        $command = $this->getCommand();
        $command->setContainer($this->getMockSetup());

        $tester = new CommandTester($command);
        $params = array('');
        $tester->execute($params);
        $display = $tester->getDisplay();
        $this->assertContains(AzineNotifierServiceMock::EMAIL_COUNT." emails have been processed.", $display);

    }

    public function testSendFail()
    {
    	$command = $this->getCommand();
    	$command->setContainer($this->getMockSetup(true));

        $tester = new CommandTester($command);
        $tester->execute(array(''));
        $display = $tester->getDisplay();
        $this->assertContains((AzineNotifierServiceMock::EMAIL_COUNT-1)." emails have been processed.", $display);
        $this->assertContains(AzineNotifierServiceMock::FAILED_ADDRESS, $display);

    }

	/**
	 * @return SendNotificationsCommand
	 */
    private function getCommand(){
    	$application = new Application();
    	$application->add(new SendNotificationsCommand());

    	$command = $application->find('emails:sendNotifications');
    	return $command;
    }

    private function getMockSetup($fail = false)
    {
        $containerMock = $this->getMockBuilder("Symfony\Component\DependencyInjection\ContainerInterface")->disableOriginalConstructor()->getMock();

        $notifierServiceMock = new AzineNotifierServiceMock($fail);

        $containerMock->expects($this->any())->method('get')->with('azine_email_notifier_service')->will($this->returnValue($notifierServiceMock));

        return $containerMock;
    }
}
