<?php
/**
 * Pmclain_AuthorizenetCim extension
 * NOTICE OF LICENSE
 *
 * This source file is subject to the OSL 3.0 License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @category  Pmclain
 * @package   Pmclain_AuthorizenetCim
 * @copyright Copyright (c) 2017-2018
 * @license   Open Software License (OSL 3.0)
 */

namespace Pmclain\AuthorizenetCim\Test\Unit\Gateway\Validator;

use Pmclain\AuthorizenetCim\Gateway\Validator\ResponseValidator\Capture;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use Pmclain\AuthorizenetCim\Gateway\Helper\SubjectReader;
use net\authorize\api\contract\v1\AnetApiResponseType;
use net\authorize\api\contract\v1\MessagesType;
use net\authorize\api\contract\v1\TransactionResponseType;
use net\authorize\api\contract\v1\TransactionResponseType\MessagesAType\MessageAType;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CaptureTest extends \PHPUnit\Framework\TestCase
{
    /** @var Capture */
    private $captureValidator;

    /** @var SubjectReader */
    private $subjectReader;

    /** @var AnetApiResponseType|MockObject */
    private $responseMock;

    /** @var MessagesType|MockObject */
    private $messagesMock;

    /** @var MessageAType|MockObject */
    private $messageMock;

    /** @var TransactionResponseType|MockObject */
    private $transactionResponseMock;

    /** @var ResultInterfaceFactory|MockObject */
    private $resultInterfaceFactoryMock;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->subjectReader = $objectManager->getObject(SubjectReader::class);

        $this->responseMock = $this->getMockBuilder(AnetApiResponseType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMessages', 'getTransactionResponse'])
            ->getMock();

        $this->messagesMock = $this->getMockBuilder(MessagesType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResultCode', 'getMessage'])
            ->getMock();

        $this->messageMock = $this->getMockBuilder(MessageAType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getText', 'getDescription'])
            ->getMock();

        $this->resultInterfaceFactoryMock = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->transactionResponseMock = $this->getMockBuilder(TransactionResponseType::class)
            ->disableOriginalConstructor()
            ->setMethods(['getErrors', 'getMessages'])
            ->getMock();

        $this->responseMock->expects($this->any())
            ->method('getMessages')
            ->willReturn($this->messagesMock);

        $this->captureValidator = $objectManager->getObject(
            Capture::class,
            [
                '_subjectReader' => $this->subjectReader,
                'resultInterfaceFactory' => $this->resultInterfaceFactoryMock
            ]
        );
    }

    public function testValidate()
    {
        $this->messagesMock->expects($this->once())
            ->method('getResultCode')
            ->willReturn('Ok');

        $this->messagesMock->expects($this->once())
            ->method('getMessage')
            ->willReturn([$this->messageMock]);

        $this->messageMock->expects($this->any())
            ->method('getText')
            ->willReturn('Transaction Approved');

        $this->responseMock->expects($this->once())
            ->method('getTransactionResponse')
            ->willReturn($this->transactionResponseMock);

        $this->transactionResponseMock->expects($this->once())
            ->method('getErrors')
            ->willReturn([]);

        $this->transactionResponseMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([$this->messageMock]);

        $this->messageMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('Transaction approved for xxx');

        $this->resultInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'isValid' => true,
                'failsDescription' => []
            ]);

        $subject = ['response' => ['object' => $this->responseMock]];

        $this->captureValidator->validate($subject);
    }

    /** @cover GeneralResponseValidator::validate */
    public function testValidateWithError()
    {
        $this->messagesMock->expects($this->once())
            ->method('getResultCode')
            ->willReturn('Ok');

        $this->messagesMock->expects($this->once())
            ->method('getMessage')
            ->willReturn([$this->messageMock]);

        $this->messageMock->expects($this->any())
            ->method('getText')
            ->willReturn('Transaction Declined');

        $this->responseMock->expects($this->once())
            ->method('getTransactionResponse')
            ->willReturn($this->transactionResponseMock);

        $this->transactionResponseMock->expects($this->once())
            ->method('getErrors')
            ->willReturn([1]);

        $this->transactionResponseMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([$this->messageMock]);

        $this->messageMock->expects($this->once())
            ->method('getDescription')
            ->willReturn('Transaction declined due to xxx');

        $this->resultInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->with([
                'isValid' => false,
                'failsDescription' => ['Transaction declined due to xxx']
            ]);

        $subject = ['response' => ['object' => $this->responseMock]];

        $this->captureValidator->validate($subject);
    }
}
