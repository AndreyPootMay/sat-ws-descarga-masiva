<?php

declare(strict_types=1);

namespace PhpCfdi\SatWsDescargaMasiva\Tests\Unit\Services\Authenticate;

use PhpCfdi\SatWsDescargaMasiva\Internal\Helpers;
use PhpCfdi\SatWsDescargaMasiva\Services\Authenticate\AuthenticateTranslator;
use PhpCfdi\SatWsDescargaMasiva\Shared\DateTime;
use PhpCfdi\SatWsDescargaMasiva\Tests\TestCase;

class AuthenticateTranslatorTest extends TestCase
{
    public function testCreateSoapRequest(): void
    {
        $translator = new AuthenticateTranslator();
        $requestBuilder = $this->createFielRequestBuilderUsingTestingFiles();

        $since = DateTime::create('2019-07-31 22:38:19 CDT');
        $until = DateTime::create('2019-07-31 22:43:19 CDT');
        $securityTokenId = 'uuid-cf6c80fb-00ae-44c0-af56-54ec65decbaa-1';
        $requestBody = $translator->createSoapRequestWithData($requestBuilder, $since, $until, $securityTokenId);
        $this->assertSame(
            $this->xmlFormat(Helpers::nospaces($this->fileContents('authenticate/request.xml'))),
            $this->xmlFormat($requestBody)
        );
    }

    public function testCreateTokenFromSoapResponseWithToken(): void
    {
        $expectedCreated = DateTime::create('2019-08-01T03:38:20.044Z');
        $expectedExpires = DateTime::create('2019-08-01T03:43:20.044Z');

        $translator = new AuthenticateTranslator();
        $responseBody = Helpers::nospaces($this->fileContents('authenticate/response-with-token.xml'));
        $token = $translator->createTokenFromSoapResponse($responseBody);
        $this->assertFalse($token->isValueEmpty());
        $this->assertTrue($token->isExpired());
        $this->assertTrue($token->getCreated()->equalsTo($expectedCreated));
        $this->assertTrue($token->getExpires()->equalsTo($expectedExpires));
        $this->assertFalse($token->isValid());
    }

    public function testCreateTokenFromSoapResponseWithError(): void
    {
        $translator = new AuthenticateTranslator();
        $responseBody = Helpers::nospaces($this->fileContents('authenticate/response-with-error.xml'));
        $token = $translator->createTokenFromSoapResponse($responseBody);
        $this->assertTrue($token->isValueEmpty());
        $this->assertTrue($token->isExpired());
        $this->assertFalse($token->isValid());
        $this->assertSame('', $token->getValue());
    }
}
