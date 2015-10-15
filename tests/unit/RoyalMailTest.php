<?php

namespace RoyalMail\tests\unit;

use atoum;

class RoyalMail extends atoum {

  use \RoyalMail\tests\lib\TestDataLoader;

  function testHelperFactory() {
    $this
      ->given($this->newTestedInstance)
      ->object($this->testedInstance->getDataHelper())
      ->isInstanceOf('\RoyalMail\Helper\Data');
  }


  function testConnectorFactory() {
    // Development
    $this
      ->given($this->newTestedInstance)
      ->object($connector = $this->testedInstance->getConnector())
      ->isInstanceOf('\RoyalMail\Connector\soapConnector')
      ->object($connector->getSoapClient())
      ->isInstanceOf('\RoyalMail\Connector\MockSoapClient');


    // Remote (would only work with live details)
/*    $interface = new \RoyalMail\RoyalMail([
      'mode'           => 'onboarding',
      'application_id' => '9876543210',
      'transaction_id' => 'order-234',
      'username'       => 'my-username',
      'password'       => 'my-password',
      'soap_client_options' => ['local_cert' => __FILE__ ], // Doesn't do anything, used to check the parameter makes it through.
    ]);

    $this
      ->object($connector = $interface->getConnector())
      ->isInstanceOf('\RoyalMail\Connector\soapConnector')
      ->object($connector->getSoapClient())
      ->isInstanceOf('\RoyalMail\Connector\MockSoapClient');*/
  }


  function testBuildRequest() {
    $this
      ->given($this->newTestedInstance)
      ->array($this->testedInstance->buildRequest('cancelShipment', $this->getRequestParams()))
      ->hasKeys(['cancelShipments', 'integrationHeader'])
      ->hasSize(2);
  }


  function testRequestSending() {
    $this
      ->given($this->newTestedInstance)
      ->array($built = $this->testedInstance->buildRequest('cancelShipment', $this->getRequestParams()))
      ->object($response = $this->testedInstance->send('cancelShipment', $built))
      ->isInstanceOf('\stdClass')
      ->string($response->completedCancelInfo->status->status->statusCode->code)
      ->isEqualTo('Cancelled');
  }


  function testResponseIntepretation() {
    $this
      ->given($this->newTestedInstance)
      ->array($built = $this->testedInstance->buildRequest('cancelShipment', $this->getRequestParams()))
      ->object($response = $this->testedInstance->send('cancelShipment', $built))
      ->object($intepreted = $this->testedInstance->interpretResponse('cancelShipment', $response))
      ->isInstanceOf('\RoyalMail\Response\Interpreter')
      ->boolean($intepreted->succeeded())
      ->isTrue()
      ->string($intepreted['status'])
      ->isEqualTo('Cancelled');
  }


  function testEndtoEndAPICall() {
    $this
      ->given($this->newTestedInstance)
      ->object($intepreted = $this->testedInstance->processAction('cancelShipment', $this->getRequestParams()))
      ->isInstanceOf('\RoyalMail\Response\Interpreter')
      ->boolean($intepreted->succeeded())
      ->isTrue();
  }


  function testSettingAuthDetails() {
    $rm = new \RoyalMail\RoyalMail([
      'application_id' => '9876543210',
      'transaction_id' => 'order-234',
      'username'       => 'my-username',
      'password'       => 'my-password',
      'soap_client_options' => ['local_cert' => __FILE__ ], // Doesn't do anything, used to check the parameter makes it through.
    ]);


    $this
      ->object($rm->cancelShipment(array_diff_key($this->getRequestParams(), ['integrationHeader' => 1])))
      ->string($req_xml = $rm->getConnector()->getAPIFormattedRequest());
  }



  function testGetAvailableActions() {
    $this
      ->given($this->newTestedInstance)
      ->array($this->testedInstance->getAvailableActions())
      ->isNotEmpty();
  }


  function testMagicRequests() {
    $this
      ->given($this->newTestedInstance)
      ->object($intepreted = $this->testedInstance->cancelShipment($this->getRequestParams()))
      ->isInstanceOf('\RoyalMail\Response\Interpreter')
      ->boolean($intepreted->succeeded())
      ->isTrue();
  }


  function getRequestParams() {
    return $this->getSampleRequest('cancelShipment');
  }
}